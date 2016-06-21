<?php

require_once dirname(__FILE__).'/../vendor/autoload.php';

use Silex\Provider as SilexProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$dotEnv = new \Dotenv\Dotenv(dirname(__DIR__));
$dotEnv->load();

$app = new Silex\Application();
$app['root_dir'] = dirname(__DIR__);
$app['request'] = $app->factory(function ($app) {
    return $app['request_stack']->getCurrentRequest();
});
$app['environment'] = $app->factory(function ($app) {
    $env = getenv('ENVIRONMENT') ?: 'production';

    return strtolower($env);
});

// Provider
if (getenv('DEBUG')) {
    // Set debugging
    $app['debug'] = true;

    // Register pimple
    $app->register(
        new Sorien\Provider\PimpleDumpProvider(),
        [
            $app['pimpledump.output_dir'] = dirname(__DIR__).'/tmp',
            $app['pimpledump.trigger_route_pattern'] = '/_dump_pimple',
        ]
    );
}

// Register global providers
$app->register(
    new SilexProvider\TwigServiceProvider(),
    [
        'twig.path' => $app['root_dir'].'/views',
    ]
);

// Robots
$app->after(function (Request $request, Response $response) use ($app) {
    if ($app['environment'] != 'production') {
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
    }
});

$app->match('/', function () use ($app) {
    phpinfo();

    return;
});

if ($app['environment'] !== 'production') {
    $app->mount('/showtv', new \App\Controller\Client\HyperlinkWeb\ShowTVController());
}

$app->mount('/sandbox/goutte', new \App\Controller\GoutteController());

$app->run();
