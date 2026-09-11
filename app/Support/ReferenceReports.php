<?php

namespace App\Support;

use App\Models\WorkspaceItem;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Collection;

class ReferenceReports
{
    public const PROPERTIES = ['reserve' => 'OUD Reserve', 'square' => 'OUD Square', 'dunes' => 'OUD Dunes', 'east' => 'La Perle East', 'west' => 'La Perle West'];

    private function document(string $slug, int $month, string $locale = 'en'): DOMDocument
    {
        if (! array_key_exists($slug, self::PROPERTIES) || $month < 1 || $month > 12 || ! in_array($locale, ['en', 'ar'], true)) {
            throw new \InvalidArgumentException('Unknown reference report.');
        }
        $path = resource_path('reports/'.$slug.'_'.sprintf('%02d', $month).($locale === 'ar' ? '_ar' : '').'.html');
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.file_get_contents($path), LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return $document;
    }

    private function number(string $value): ?float
    {
        $value = str_replace([',', '%'], '', trim($value));

        return is_numeric($value) ? (float) $value : null;
    }

    public function figures(string $slug, int $month): array
    {
        $document = $this->document($slug, $month);
        $xpath = new DOMXPath($document);
        $data = ['reference' => $slug, 'source_name' => 'Landlord Investor Dashboard.xlsx', 'components' => [], 'monthly_rows' => [], 'annual_rows' => []];
        $fields = ['Actual Sizes / Sqm' => 'area', 'Detailed Average Base Rate / Sqm' => 'rate', 'Detailed Base Rental Revenue' => 'rent', 'Detailed Service Charge Revenue' => 'service'];
        $totals = ['Average Office Occupancy %' => 'office_occupancy', 'Average Retail Occupancy %' => 'retail_occupancy', 'Rent Collection Due this month' => 'rent_due', 'Amount Collected' => 'collected', 'Annual Office Predicted Revenue' => 'office_forecast', 'Annual Retail Predicted Revenue' => 'retail_forecast', 'Grand Total' => 'saved_total'];
        foreach ($xpath->query('//table') as $table) {
            $heading = $xpath->query('.//tr[1]', $table)->item(0)?->textContent ?? '';
            if (! str_contains($heading, 'Excel metric')) {
                continue;
            }
            foreach ($xpath->query('.//tr', $table) as $row) {
                $cells = $xpath->query('./td|./th', $row);
                $first = $cells->item(0);
                if (! $first) {
                    continue;
                }
                $reference = trim($xpath->query('.//small', $first)->item(0)?->textContent ?? '');
                if ($reference === '') {
                    continue;
                }
                $label = trim(str_replace($reference, '', $first->textContent));
                $values = [];
                foreach ($cells as $index => $cell) {
                    if ($index > 0) {
                        $values[] = trim($cell->textContent);
                    }
                }
                $annual = str_contains($reference, 'Summary');
                $data[$annual ? 'annual_rows' : 'monthly_rows'][] = compact('label', 'reference', 'values');
                if ($annual) {
                    continue;
                }
                if (isset($fields[$label])) {
                    foreach (FinancialReport::COMPONENTS as $index => $component) {
                        $data['components'][$component][$fields[$label]] = $this->number($values[$index] ?? '');
                    }
                }
                if (isset($totals[$label])) {
                    $data[$totals[$label]] = $this->number($values[0] ?? '');
                }
            }
        }
        $demoRows = $xpath->query('//section[contains(@class,"demo-pl")]//table//tr');
        $demoCells = $demoRows->item($month) ? $xpath->query('./td|./th', $demoRows->item($month)) : null;
        if ($demoCells && $demoCells->length >= 3) {
            $data['profit_revenue'] = $this->number($demoCells->item(1)->textContent);
            $expenses = $this->number($demoCells->item(2)->textContent);
            $data['operations'] = $expenses * .5;
            $data['maintenance'] = $expenses * .3;
            $data['administration'] = $expenses * .2;
        }
        $data['source_notes'] = trim($xpath->query('//details[contains(@class,"source-notes")]')->item(0)?->textContent ?? '');
        if (count($data['monthly_rows']) < 15 || ! isset($data['components']['office']['rent'])) {
            throw new \RuntimeException('Reference figures could not be parsed: '.$slug.' month '.$month);
        }

        return $data;
    }

    public function content(WorkspaceItem $record, Collection $properties, string $route): ?string
    {
        $slug = data_get($record->financial_data, 'reference');
        if (! isset(self::PROPERTIES[$slug ?? '']) || ! $record->report_month) {
            return null;
        }
        $document = $this->document($slug, $record->report_month->month, app()->getLocale());
        $xpath = new DOMXPath($document);
        $main = $xpath->query('//main')->item(0);
        foreach ($xpath->query('.//script|.//*[contains(concat(" ",normalize-space(@class)," ")," language-switch ")]|.//*[contains(concat(" ",normalize-space(@class)," ")," oud-user-role ")]', $main) as $node) {
            $node->parentNode->removeChild($node);
        }
        foreach (iterator_to_array($xpath->query('.//a[@href]', $main)) as $link) {
            $href = $link->getAttribute('href');
            if (preg_match('/^excel_report_([a-z]+)_(\d{2})(?:_ar)?\.html$/', $href, $match)) {
                $property = $properties->firstWhere('name', self::PROPERTIES[$match[1]] ?? '');
                if (! $property) {
                    $link->parentNode->removeChild($link);

                    continue;
                }
                $link->setAttribute('href', route($route, ['property' => $property->id, 'year' => 2027, 'month' => (int) $match[2]]));
            } elseif ($href === 'landlord_dashboard.html') {
                $link->setAttribute('href', route(auth()->user()->dashboardRouteName()));
            }
        }
        foreach ($xpath->query('.//*[@src]', $main) as $element) {
            if (str_starts_with($element->getAttribute('src'), 'assets/')) {
                $element->setAttribute('src', asset('oud/'.$element->getAttribute('src')));
            }
        }
        $html = '';
        foreach ($main->childNodes as $child) {
            $html .= $document->saveHTML($child);
        }

        return $html;
    }
}
