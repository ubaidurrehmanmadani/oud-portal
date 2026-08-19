<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case DEPARTMENT_MANAGER = 'department_manager';
    case EMPLOYEE = 'employee';
    case LANDLORD = 'landlord';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::DEPARTMENT_MANAGER => 'Department Manager',
            self::EMPLOYEE => 'Employee',
            self::LANDLORD => 'Landlord',
        };
    }

    public function dashboardRouteName(): string
    {
        return match ($this) {
            self::ADMIN => 'dashboard.admin',
            self::DEPARTMENT_MANAGER => 'dashboard.manager',
            self::EMPLOYEE => 'dashboard.employee',
            self::LANDLORD => 'dashboard.landlord',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role): array => [$role->value => $role->label()])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
