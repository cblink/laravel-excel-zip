<?php


namespace Cblink\ExcelZip;


use Illuminate\Support\ServiceProvider;

class ExcelZipServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->publishes([__DIR__ . '/config/' => config_path()]);
    }
}