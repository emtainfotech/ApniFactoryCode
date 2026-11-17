<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Shield Livewire UMD bundle against conflicting browser extensions or AMD loaders (RequireJS)
        Filament::registerRenderHook(
            'scripts.start',
            fn (): HtmlString => new HtmlString(<<<'HTML'
<script>
    if (window.__filament_temp_define === undefined && typeof window.define === 'function') {
        window.__filament_temp_define = window.define;
        window.define = undefined;
    }
    if (window.__filament_temp_exports === undefined && typeof window.exports === 'object') {
        window.__filament_temp_exports = window.exports;
        window.exports = undefined;
    }
</script>
HTML)
        );

        Filament::registerRenderHook(
            'scripts.end',
            fn (): HtmlString => new HtmlString(<<<'HTML'
<script>
    if (window.__filament_temp_define !== undefined) {
        window.define = window.__filament_temp_define;
        delete window.__filament_temp_define;
    }
    if (window.__filament_temp_exports !== undefined) {
        window.exports = window.__filament_temp_exports;
        delete window.__filament_temp_exports;
    }
</script>
HTML)
        );

        // Self-healing schema check: ensures required product columns exist in production database
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('products')) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('products', 'home_image') ||
                    !\Illuminate\Support\Facades\Schema::hasColumn('products', 'show_on_home')) {
                    \Illuminate\Support\Facades\Schema::table('products', function (\Illuminate\Database\Schema\Blueprint $table) {
                        if (!\Illuminate\Support\Facades\Schema::hasColumn('products', 'home_image')) {
                            $table->string('home_image')->nullable()->after('multipleimages');
                        }
                        if (!\Illuminate\Support\Facades\Schema::hasColumn('products', 'show_on_home')) {
                            $table->boolean('show_on_home')->default(true)->after('home_image');
                        }
                    });
                }
            }
        } catch (\Throwable $e) {
            // Silently suppress if DB is unreachable during early artisan or composer lifecycle
        }
    }
}
