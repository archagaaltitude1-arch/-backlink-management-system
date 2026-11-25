<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('global_settings')->update([
            'primary_colour' => '#152029',
            'secondary_colour' => '#D63A10',
            'accent_colour' => '#FF4917',
            'background_colour' => '#FFFFFF',
            'text_colour' => '#152029',
            'button_colour' => '#FF4917',
        ]);

        Cache::forget('global-settings.public.v1');
    }

    public function down(): void
    {
        DB::table('global_settings')->update([
            'primary_colour' => '#172B4D',
            'secondary_colour' => '#F97316',
            'accent_colour' => '#F97316',
            'background_colour' => '#FFFFFF',
            'text_colour' => '#111827',
            'button_colour' => '#F97316',
        ]);

        Cache::forget('global-settings.public.v1');
    }
};
