<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
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
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

#[Hidden(['password', 'remember_token'])]
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
}
