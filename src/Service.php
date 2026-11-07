<?php

namespace Youvision\ThinkSalting;

class Service extends \think\Service
{
    public function register()
    {
        $configFile = dirname(__DIR__) . '/config/salting.php';

        if (!is_file($configFile)) {
            return;
        }

        $default = include $configFile;
        $current = (array) $this->app->config->get('salting');

        $this->app->config->set(array_merge($default, $current), 'salting');
    }

    public function boot()
    {
    }
}