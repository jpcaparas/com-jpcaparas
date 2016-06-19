<?php

namespace App\Controller;

use Bolandish\Instagram;
use Silex\Application;
use Silex\ControllerProviderInterface;

class InstagramController implements ControllerProviderInterface {
	public function connect( Application $app ) {
		$controllers = $app['controllers_factory'];

		$controllers->get( '/', array( $this, 'indexAction' ) );

		return $controllers;
	}

	public function indexAction( Application $app ) {
		$hash = 'bikeakl';
		$data = Instagram::getMediaByHashtag( 'bikeakl', 30 );
		var_dump( $data );

		return '';
	}
}