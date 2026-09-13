<?php

namespace App\Support;

use App\Models\WorkspaceItem;

class FinancialReport
{
    public const COMPONENTS = ['office', 'mezzanine', 'lobby', 'terrace', 'retail', 'outdoor'];

    public const COMPONENT_FIELDS = ['area', 'rate', 'rent', 'service'];

    public const FIELDS = ['rent_due', 'collected', 'office_occupancy', 'retail_occupancy', 'office_forecast', 'retail_forecast', 'saved_total', 'profit_revenue', 'operations', 'maintenance', 'administration'];

    public function __construct(public ?WorkspaceItem $record) {}

    public function value(string $key): ?float
    {
        $value = data_get($this->record?->financial_data, $key);

        return $value === null || $value === '' ? null : (float) $value;
    }

    public function sum(array $values): ?float
    {
        $values = array_filter($values, fn ($value) => $value !== null);

        return $values === [] ? null : array_sum($values);
    }

    public function componentTotal(string $field, array $components = self::COMPONENTS): ?float
    {
        return $this->sum(array_map(fn ($component) => $this->value('components.'.$component.'.'.$field), $components));
    }

    public function totalRevenue(): ?float
    {
        $rent = $this->componentTotal('rent');
        $service = $this->componentTotal('service');

        return $rent === null || $service === null ? null : $rent + $service;
    }

    public function collectionRate(): ?float
    {
        $due = $this->value('rent_due');
        $collected = $this->value('collected');

        return $due > 0 && $collected !== null ? $collected / $due * 100 : null;
    }

    public function expenses(): ?float
    {
        return $this->sum(array_map(fn ($field) => $this->value($field), ['operations', 'maintenance', 'administration']));
    }

    public function profit(): ?float
    {
        $revenue = $this->value('profit_revenue');
        $expenses = $this->expenses();

        return $revenue !== null && $expenses !== null ? $revenue - $expenses : null;
    }

    public static function format(?float $value, string $suffix = ''): string
    {
        return $value === null ? '—' : number_format($value, 2).$suffix;
    }

    public static function axisLabel(float $value): string
    {
        foreach ([1000000000000 => 'T', 1000000000 => 'B', 1000000 => 'M', 1000 => 'K'] as $divisor => $suffix) {
            if ($value >= $divisor) {
                return number_format($value / $divisor, 1).$suffix;
            }
        }

        return number_format($value, $value > 0 && $value < 1 ? 2 : 0);
    }
}
