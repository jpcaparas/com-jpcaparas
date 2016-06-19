<?php

require_once( dirname( __FILE__ ) . '/../vendor/autoload.php' );

use App\Controllers;
use Silex\Provider as SilexProvider;
use App\Provider as AppProvider;

$dotEnv = new \Dotenv\Dotenv( dirname( __DIR__ ) );
$dotEnv->load();

$app = new Silex\Application();

$app['root_dir'] = dirname( __DIR__ );
$app['request']  = $app->factory( function ( $app ) {
	return $app['request_stack']->getCurrentRequest();
} );

// Provider
if ( getenv( 'DEBUG' ) ) {
	// Set debugging
	$app['debug'] = true;

	// Register pimple
	$app->register(
		new Sorien\Provider\PimpleDumpProvider(),
		[
			$app['pimpledump.output_dir'] = dirname( __DIR__ ) . '/tmp',
			$app['pimpledump.trigger_route_pattern'] = '/_dump_pimple'
		]
	);
}

// Register providers
$app->register(
	new SilexProvider\TwigServiceProvider(),
	[
		'twig.path' => $app['root_dir'] . '/views'
	]
);

$app->match( '/', function () use ( $app ) {
	return 'test';
} );

$app->mount( '/showtv', new \App\Controller\Client\HyperlinkWeb\ShowTVController() );

$app->run();