<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    public static function getBool(string $key, bool $default = false): bool
    {
        $record = static::where('key', $key)->first();
        if (!$record) {
            return $default;
        }
        $val = strtolower(trim((string) $record->value));
        return in_array($val, ['1','true','yes','on'], true);
    }

    public static function setBool(string $key, bool $value): void
    {
        static::updateOrCreate(['key' => $key], [
            'value' => $value ? '1' : '0',
            'type' => 'bool',
        ]);
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $record = static::where('key', $key)->first();
        return $record ? (string) $record->value : $default;
    }
}


