<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Notifications\QueuedResetPassword;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'role', 'role_id', 'password', 'last_login_at', 'department_id'];

    protected $hidden = ['password', 'remember_token'];

    protected $attributes = ['approval_status' => 'approved'];

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

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new QueuedResetPassword($token));
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class);
    }

    public function capabilityDefaults(): array
    {
        return match ($this->role) {
            UserRole::DEPARTMENT_MANAGER => ['manage_documents' => true, 'manage_training' => true, 'manage_announcements' => true, 'manage_employees' => false],
            UserRole::LANDLORD => ['view_reports' => true, 'view_documents' => true, 'download_files' => true, 'decide_approvals' => true],
            default => [],
        };
    }

    public function allows(string $capability): bool
    {
        if ($this->role === UserRole::ADMIN) {
            return true;
        }
        $defaults = $this->capabilityDefaults();

        return array_key_exists($capability, $defaults) && (bool) ($this->permission_overrides[$capability] ?? $defaults[$capability]);
    }

    public function canSubmitFinancialReports(): bool
    {
        return $this->role === UserRole::DEPARTMENT_MANAGER && $this->department_id && $this->can_submit_financial_reports;
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
            'permission_overrides' => 'array',
            'suspended_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'can_submit_financial_reports' => 'boolean',
        ];
    }
}
