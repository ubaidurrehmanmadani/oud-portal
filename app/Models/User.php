<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'role', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_DEPARTMENT_MANAGER = 'department_manager';

    public const ROLE_EMPLOYEE = 'employee';

    public const ROLE_LANDLORD = 'landlord';

    /**
     * @return array<string, string>
     */
    public static function roles(): array
    {
        return [
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_DEPARTMENT_MANAGER => 'Department Manager',
            self::ROLE_EMPLOYEE => 'Employee',
            self::ROLE_LANDLORD => 'Landlord',
        ];
    }

    public function dashboardRouteName(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'dashboard.admin',
            self::ROLE_DEPARTMENT_MANAGER => 'dashboard.manager',
            self::ROLE_LANDLORD => 'dashboard.landlord',
            default => 'dashboard.employee',
        };
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
            'password' => 'hashed',
        ];
    }
}
