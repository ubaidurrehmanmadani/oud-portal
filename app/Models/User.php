<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'role', 'role_id', 'password', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = UserRole::ADMIN->value;

    public const ROLE_DEPARTMENT_MANAGER = UserRole::DEPARTMENT_MANAGER->value;

    public const ROLE_EMPLOYEE = UserRole::EMPLOYEE->value;

    public const ROLE_LANDLORD = UserRole::LANDLORD->value;

    /**
     * @return array<string, string>
     */
    public static function roles(): array
    {
        return UserRole::options();
    }

    public function dashboardRouteName(): string
    {
        return $this->role instanceof UserRole
            ? $this->role->dashboardRouteName()
            : UserRole::tryFrom((string) $this->role)?->dashboardRouteName() ?? UserRole::EMPLOYEE->dashboardRouteName();
    }

    public function accountRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function loginEvents(): HasMany
    {
        return $this->hasMany(LoginEvent::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }
}
