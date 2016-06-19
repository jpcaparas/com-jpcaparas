<?php

namespace App\Controller;

use GuzzleHttp\Client;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\Serializer\Serializer;

class TVShowsController implements ControllerProviderInterface {
	public function connect( Application $app ) {
		$controllers = $app['controllers_factory'];

		$controllers->get( '/', array( $this, 'indexAction' ) );

		return $controllers;
	}

	public function indexAction( Application $app ) {
		$client     = new Client();
		$serializer = new Serializer();

		$req     = $client->request( 'GET', 'http://api.tvmaze.com/search/shows?q=girls' );
		$body    = $req->getBody();
		$dataRaw = $body->getContents();

		$data = \GuzzleHttp\json_decode( $dataRaw );

		dump( $data );

		return null;
	}
}