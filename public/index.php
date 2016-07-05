<?php

require_once dirname(__FILE__) . '/../vendor/autoload.php';

use Silex\Provider as SilexProvider;

$composerData = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
$dotEnv = new \Dotenv\Dotenv(dirname(__DIR__));
$dotEnv->load();

$app = new Silex\Application();

/**
 * Registers properties and providers
 */
$app['debug'] = $app->factory(function () {
    return getenv('DEBUG') === 'true';
});
$app['google_analytics_id'] = $app->factory(function () {
    return getenv('GOOGLE_ANALYTICS_ID');
});
$app['environment'] = $app->factory(function () {
    $env = getenv('ENVIRONMENT') ?: 'production';
    return strtolower($env);
});
$app['version'] = $composerData['version'];
$app['root_dir'] = dirname(__DIR__);
$app['request'] = $app->factory(function ($app) {
    /**
     * @type $requestStack \Symfony\Component\HttpFoundation\RequestStack
     */
    $requestStack = $app['request_stack'];
    return $requestStack->getCurrentRequest();
});
$app->register(new SilexProvider\SessionServiceProvider());
$app->register(new SilexProvider\FormServiceProvider());
$app->register(new Silex\Provider\LocaleServiceProvider());
$app->register(new SilexProvider\TranslationServiceProvider(), [
    'locale_fallbacks' => array('en')
]);
$app->register(
    new SilexProvider\TwigServiceProvider(),
    [
        'twig.path' => $app['root_dir'] . '/views',
    ]
);
/**
 * Resolves routes to controllers
 */
$app->mount('/', new \App\Controller\AppController());

/**
 * Runs the app
 */
$app->run();