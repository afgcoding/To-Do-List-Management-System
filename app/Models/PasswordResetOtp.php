<?php

declare(strict_types=1);

namespace App\Models;

use App\Mail\PasswordResetOtpMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetOtp extends Model
{
    public const UPDATED_AT = null;

    public const EXPIRES_AFTER_MINUTES = 10;

    protected $fillable = [
        'email',
        'otp_code',
        'created_at',
        'expires_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public static function issue(string $email): string
    {
        $normalized = strtolower($email);

        self::query()->where('email', $normalized)->delete();

        $code = str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);

        self::query()->create([
            'email' => $normalized,
            'otp_code' => Hash::make($code),
            'created_at' => now(),
            'expires_at' => now()->addMinutes(self::EXPIRES_AFTER_MINUTES),
        ]);

        return $code;
    }

    public static function sendTo(User $user): void
    {
        Mail::to($user->email)->send(new PasswordResetOtpMail(
            code: self::issue($user->email),
            recipientName: $user->name,
            companyName: (string) setting('company_name', config('app.name')),
        ));
    }

    public static function codeIsValid(string $email, string $code): bool
    {
        $record = self::query()
            ->where('email', strtolower($email))
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();

        if (! $record instanceof self) {
            return false;
        }

        $normalized = preg_replace('/\s+/', '', $code) ?? '';

        if (! preg_match('/^\d{6}$/', $normalized)) {
            return false;
        }

        return Hash::check($normalized, $record->otp_code);
    }

    public static function consume(string $email): void
    {
        self::query()->where('email', strtolower($email))->delete();
    }
}
