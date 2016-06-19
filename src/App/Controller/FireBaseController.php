<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

class FireBaseController implements ControllerProviderInterface {
	public function connect( Application $app ) {
		$controllers = $app['controllers_factory'];

		$controllers->get( '/', array( $this, 'indexAction' ) );

		return $controllers;
	}

	public function indexAction( Application $app ) {
		try {
			$firebaseUrl   = getenv( 'FIREBASE_URL' );
			$firebaseToken = getenv( 'FIREBASE_TOKEN' );
			$firebasePath  = getenv( 'FIREBASE_PATH' );

			$firebase = new \Firebase\FirebaseLib( $firebaseUrl, $firebaseToken );

			$data = $firebase->get( $firebasePath . '/web', 'Debugs' );

			return $data;
		}

		catch
		( Exception $e ) {
			return $e->getMessage();
		}
	}
}

