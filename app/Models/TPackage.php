<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TPackage extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 't_packages';

    protected $fillable = [
        'name',
        'code',
        'price',
        'year_price',
        'features',
        'sort',
        'status',
        'trial_days',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price' => 'decimal:2',
            'year_price' => 'decimal:2',
            'sort' => 'integer',
            'status' => 'integer',
            'trial_days' => 'integer',
        ];
    }

    public function getFeaturesArrayAttribute(): array
    {
        if (is_array($this->features)) {
            return $this->features;
        }

        if (blank($this->features)) {
            return [];
        }

        $decoded = json_decode((string) $this->features, true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function formatFeaturesForTextarea(mixed $value): string
    {
        return implode(PHP_EOL, static::normalizeFeaturesInput($value));
    }

    public static function normalizeFeaturesInput(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map(function ($item) {
                return trim((string) $item);
            }, $value), function ($item) {
                return $item !== '';
            }));
        }

        if (blank($value)) {
            return [];
        }

        $string = trim((string) $value);
        $decoded = json_decode($string, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return static::normalizeFeaturesInput($decoded);
        }

        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $string) ?: []), function ($item) {
            return $item !== '';
        }));
    }
}
