<?php

namespace App\Utils;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str as SupportStr;

class Str {
    public static function slug(string $text): string {
        return SupportStr::slug($text) . '-' . rand(111, 999) . time();
    }

    public static function invoice(): string {
        $now = Carbon::now();
        return 'INV/' . $now->format('Y/m/d') . '-' . rand(111, 999) . time();
    }

    public static function paymentCode(): string {
        return 'PY-' . rand(111111111, 999999999) . time();
    }
}
