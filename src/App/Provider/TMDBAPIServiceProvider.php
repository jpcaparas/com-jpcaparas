<?php

namespace App\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;

class TMDBAPIServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    use Application\MonologTrait;

    public function register(Container $app)
    {
        $app['tmdb.token'] = null;
        $app['tmdb.options'] = [];
    }

    public function boot(Application $app)
    {
        $app['tmdb.client'] = $app->protect(function ($token = null) use ($app) {
            $token = $token ?: $app['tmdb.token'];

            $tokenInstance = new \Tmdb\ApiToken($token);

            return new \Tmdb\Client($tokenInstance, $app['tmdb.options']);
        });
    }
}
