<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    // Helper to get a setting value
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    // Helper to set a setting
    public static function set($key, $value)
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}