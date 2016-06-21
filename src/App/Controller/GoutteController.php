<?php

namespace App\Controller;

use Goutte\Client;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\DomCrawler\Crawler;

class GoutteController implements ControllerProviderInterface {
	public function connect( Application $app ) {
		/**
		 * @var ControllerCollection
		 */
		$controllers = $app['controllers_factory'];

		$controllers->get( '/', function () use ( $app ) {
			$converter = new CssSelectorConverter();
			$baseUrl   = 'http://www.madison.co.nz/job-search/';
			$jobs      = [ ];
			$positions = [
				'PHP Developer',
				'Java Developer',
				'Python Developer',
			];

			// Iterate through positions and populate jobs array
			for ( $i = 0; $i < count( $positions ); ++ $i ) {
				$position               = $positions[ $i ];
				$url                    = $baseUrl . '?' . http_build_query( [ 'keyword' => $position ] );
				$jobs[ $i ]['position'] = $position;

				// Simulate browser-kit
				$client   = new Client();
				$crawler  = $client->request( 'GET', $url );
				$selector = $converter->toXPath( '.job-list .job-title' );
				$crawler->filterXPath( $selector )->each( function ( Crawler $node, $j ) use ( &$jobs, $i ) {
					$jobs[ $i ]['listings'][ $j ]['vacancy_name'] = trim( $node->text() );

					$node->filter( 'a' )->each( function ( Crawler $node ) use ( &$jobs, $i, $j ) {
						$jobs[ $i ]['listings'][ $j ]['url'] = $node->attr( 'href' );
					} );
				} );
			}

			dump( $jobs );

			return '';
		} );

		return $controllers;
	}
}
