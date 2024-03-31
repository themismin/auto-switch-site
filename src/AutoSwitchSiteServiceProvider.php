<?php

namespace ThemisMin\AutoSwitchSite;

use Illuminate\Support\ServiceProvider;
use ThemisMin\AutoSwitchSite\Middleware\AutoSwitchSite;

class AutoSwitchSiteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     * @return void
     */
    public function boot()
    {
        // 配置文件
        $this->publishes([
            __DIR__ . '/../config/auto_switch_site.php' => config_path('auto_switch_site.php'),
        ]);

        $this->mergeConfigFrom(__DIR__ . '/../config/auto_switch_site.php', 'auto_switch_site');

        $router = $this->app['router'];
        $method = method_exists($router, 'aliasMiddleware') ? 'aliasMiddleware' : 'middleware';
        $router->$method('auto.switch.site', AutoSwitchSite::class);

    }
}
