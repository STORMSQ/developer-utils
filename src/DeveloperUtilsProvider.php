<?php

namespace STORMSQ\DeveloperUtils;

use Illuminate\Support\ServiceProvider;
use File;
class DeveloperUtilsProvider extends ServiceProvider
{
    
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/developer-utils.php' => config_path('developer-utils.php'),
        ],'developer-utils');
        /*$this->commands([
            GenerateService::class,
        ]);*/
        /*if (File::exists(__DIR__ . '/../helper/helpers.php')) {
            require __DIR__ . '/../helper/helpers.php';
        }*/
    
    }

    // 註冊套件函式
    public function register()
    {

        /*$this->app->singleton('ServiceBuilder', function ($app) {
            return new ServiceBuilder();
        });*/
    }
}