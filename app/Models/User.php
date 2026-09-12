<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Support\TotpAuthenticator;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'job_title',
        'role',
        'department_id',
        'status',
        'avatar',
        'password',
        'last_login_at',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'employee',
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
        ];
    }

    /**
     * @return Attribute<string, never>
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if (filled($this->avatar)) {
                return Storage::disk('public')->url($this->avatar);
            }

            return 'https://ui-avatars.com/api/?name='.urlencode($this->name ?: 'User').'&background=4F46E5&color=fff&size=128';
        });
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(['Super Admin', 'Admin']);
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function syncWorkspaceRole(string $roleName): void
    {
        $this->syncRoles([$roleName]);
        $this->forceFill([
            'role' => UserRole::fromSpatieName($roleName),
        ])->save();
    }

    public function isAssignedTo(Task $task): bool
    {
        if ($task->relationLoaded('assignedUsers')) {
            return $task->assignedUsers->contains('id', $this->id);
        }

        return $task->assignedUsers()->where('users.id', $this->id)->exists();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'creator_id');
    }

    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_user')->withTimestamps();
    }

    public function assignedSubtasks(): HasMany
    {
        return $this->hasMany(Subtask::class, 'assigned_to');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class)->withPivot('assigned_at');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function hasTwoFactorEnabled(): bool
    {
        return filled($this->two_factor_secret) && $this->two_factor_confirmed_at !== null;
    }

    public function twoFactorSecretPlain(): ?string
    {
        if (blank($this->two_factor_secret)) {
            return null;
        }

        return Crypt::decryptString($this->two_factor_secret);
    }

    public function startTwoFactorSetup(): string
    {
        $secret = TotpAuthenticator::generateSecret();

        $this->forceFill([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return $secret;
    }

    /**
     * @return list<string>|false
     */
    public function confirmTwoFactor(string $code): array|false
    {
        $secret = $this->twoFactorSecretPlain();

        if ($secret === null || ! TotpAuthenticator::verify($secret, $code)) {
            return false;
        }

        $codes = $this->makeRecoveryCodes();

        $this->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode(
                array_map(fn (string $recoveryCode): string => Hash::make($recoveryCode), $codes),
            )),
        ])->save();

        return $codes;
    }

    /**
     * @return list<string>
     */
    public function freshRecoveryCodes(): array
    {
        $plain = $this->makeRecoveryCodes();

        $this->forceFill([
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode(
                array_map(fn (string $code): string => Hash::make($code), $plain),
            )),
        ])->save();

        return $plain;
    }

    public function disableTwoFactor(): void
    {
        $this->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }

    public function consumeTwoFactorCode(string $code): bool
    {
        $secret = $this->twoFactorSecretPlain();

        if ($secret !== null && TotpAuthenticator::verify($secret, $code)) {
            return true;
        }

        return $this->consumeRecoveryCode($code);
    }

    public function twoFactorOtpAuthUrl(): ?string
    {
        $secret = $this->twoFactorSecretPlain();

        if ($secret === null) {
            return null;
        }

        return TotpAuthenticator::otpAuthUrl(
            (string) setting('company_name', config('app.name')),
            $this->email,
            $secret,
        );
    }

    public function twoFactorQrImageUrl(): ?string
    {
        $otpAuthUrl = $this->twoFactorOtpAuthUrl();

        return $otpAuthUrl === null ? null : TotpAuthenticator::qrImageUrl($otpAuthUrl);
    }

    /**
     * @return list<string>
     */
    private function makeRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn (): string => strtoupper(Str::random(4).'-'.Str::random(4)))
            ->all();
    }

    private function consumeRecoveryCode(string $code): bool
    {
        if (blank($this->two_factor_recovery_codes)) {
            return false;
        }

        $normalized = strtoupper(str_replace(' ', '', $code));
        /** @var list<string> $hashes */
        $hashes = json_decode(Crypt::decryptString($this->two_factor_recovery_codes), true) ?: [];

        foreach ($hashes as $index => $hash) {
            if (Hash::check($normalized, $hash) || Hash::check($code, $hash)) {
                unset($hashes[$index]);
                $this->forceFill([
                    'two_factor_recovery_codes' => Crypt::encryptString(json_encode(array_values($hashes))),
                ])->save();

                return true;
            }
        }

        return false;
    }
}
