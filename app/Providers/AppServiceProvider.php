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
    }
}
