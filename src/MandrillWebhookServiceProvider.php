<?php

namespace EventHomes\Api\Webhooks;

use Illuminate\Support\ServiceProvider;

class MandrillWebhookServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        $this->publishes([__DIR__ . '/config/config.php' => config_path('mandrill-webhooks.php')]);
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/config.php', 'mandrill-webhooks');
    }
}
