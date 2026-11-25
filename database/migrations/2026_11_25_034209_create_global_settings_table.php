<?php

use App\Support\GlobalSettingDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('global_settings')) {
            return;
        }

        Schema::create('global_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('website_name');
            $table->string('website_url');
            $table->string('company_email');
            $table->string('support_email');
            $table->string('phone_number');
            $table->text('company_address');
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('country');
            $table->string('currency', 3);
            $table->string('timezone');
            $table->string('date_format');
            $table->string('default_language');
            $table->string('copyright_text');
            $table->string('logo_path')->nullable();
            $table->string('wallpaper_path')->nullable();
            $table->string('wallpaper_source_path')->nullable();
            $table->string('wallpaper_position')->default('center');
            $table->string('favicon_path')->nullable();
            $table->string('footer_copyright');
            $table->text('footer_description')->nullable();
            $table->longText('footer_html')->nullable();
            $table->json('footer_links');
            $table->string('primary_colour', 7);
            $table->string('secondary_colour', 7);
            $table->string('accent_colour', 7);
            $table->string('background_colour', 7);
            $table->string('text_colour', 7);
            $table->string('button_colour', 7);
            $table->timestamps();
        });

        $attributes = GlobalSettingDefaults::attributes();
        $attributes['footer_links'] = json_encode($attributes['footer_links'], JSON_THROW_ON_ERROR);
        $attributes['created_at'] = now();
        $attributes['updated_at'] = now();

        DB::table('global_settings')->insert($attributes);
    }

    public function down(): void
    {
        Schema::dropIfExists('global_settings');
    }
};
