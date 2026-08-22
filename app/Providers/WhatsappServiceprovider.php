<?php

namespace App\Providers;
use App\Services\WhatsappService;
use Illuminate\Support\ServiceProvider;

class WhatsappServiceprovider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Bind the concrete MyService class into the container
        $this->app->bind(WhatsappService::class, function ($app) {
            return new WhatsappService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
