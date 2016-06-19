<?php

namespace App\Provider;

use League\Flysystem\Exception;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Madcoda\Youtube;

class YouTubeAPIServiceProvider implements ServiceProviderInterface {

	public function register( Application $app ) {
		$app['youtube.options'] = [];

		$youtube = new Youtube($app['youtube.options']);

		return $youtube;
	}

	public function boot( Application $app ) {

	}
}