<?php

namespace App\Provider;

use Madcoda\Youtube;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;

class YouTubeAPIServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    public function register(Container $app)
    {
        $app['youtube.token'] = null;
    }

    public function boot(Application $app)
    {
        $app['youtube.client'] = $app->protect(function ($token = null) use ($app) {
            $token = $token != '' ?: $app['youtube.token'];

            return new Youtube(['key' => $token]);
        });
    }
}
