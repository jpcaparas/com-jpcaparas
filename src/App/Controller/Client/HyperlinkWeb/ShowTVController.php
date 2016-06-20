<?php

namespace App\Controller\Client\HyperlinkWeb;

use App\Provider;
use Madcoda\Youtube;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;

class ShowTVController implements ControllerProviderInterface {
	use Application\UrlGeneratorTrait;

	private $showId;

	public function connect( Application $app ) {
		// Register
		$app->register(
			new Provider\TMDBAPIServiceProvider(),
			[
				'tmdb.token' => getenv( 'TMDB_TOKEN' )
			]
		);
		$app->register(
			new Provider\YouTubeAPIServiceProvider(),
			[
				'youtube.token' => getenv( 'YOUTUBE_TOKEN' )
			]
		);

		// Set twig path
		$app['twig.path'] = $app['root_dir'] . '/views/showtv';

		/**
		 * @var ControllerCollection $controllers
		 */
		$controllers = $app['controllers_factory'];

		$paths = [
			'/'                => 'indexAction',
			'/about'           => 'aboutAction',
			'/api_information' => 'apiInformationAction',
			'/airing_today'    => 'airingTodayAction',
			'/top_rated'       => 'topRatedAction',
			'/popular'         => 'popularAction',
			'/trailers'        => 'trailersAction',
			'/reviews'         => 'reviewsAction',
			'/news'            => 'newsAction',
			'/contact'         => 'contactAction'
		];

		foreach ( $paths as $path => $method ) {
			$controllers->get( $path, [ $this, $method ] );
		}

		$controllers->get( '/shows/{id}', function ( $id ) use ( $app ) {
			return $this->showsAction( $id, $app );
		} )->value( 'id', null );

		return $controllers;
	}

	/**
	 * @param Application $app
	 * @param null|int    $id
	 */
	public function showsAction( $id, Application $app ) {
		/**
		 * @var \Tmdb\Client $tmdbClient
		 * @var Youtube      $youtubeClient
		 * @var Request      $request
		 */
		$tmdbClient    = $app['tmdb.client']();
		$youtubeClient = $app['youtube.client']();
		$request       = $app['request'];
		$config        = $tmdbClient->getConfigurationApi();
		$tv            = $tmdbClient->getTvApi();

		$searchTerm = $request->query->get( 's' );
		$pageNumber = $request->query->get( 'page' ) ?: 1;
		$data       = [ ];

		if ( $searchTerm ) {
			$search = $tmdbClient->getSearchApi();

			$data = [
				'searchTerm' => $searchTerm,
				'search'     => $search->searchTv( $searchTerm, [ 'page' => $pageNumber ] ),
				'route'      => $request->get( '_route' ),
			];

			return $app['twig']->render( 'shows_search.html.twig', $data );
		} else {
			if ( is_null( $id ) ) {
				$request = $app->get( 'request' );

				$data = [
					'on_the_air'   => $tv->getOnTheAir(),
					'airing_today' => $tv->getAiringToday(),
					'top_rated'    => $tv->getTopRated(),
					'popular'      => $tv->getPopular(),
				];

				return $app['twig']->render( 'shows_list.html.twig', $data );
			} else {
				$data = [
					'config'          => $tmdbClient->getConfigurationApi(),
					'meta'            => $tv->getTvshow( $id ),
					'content_ratings' => $tv->getContentRatings( $id ),
					'credits'         => $tv->getCredits( $id ),
					'images'          => $tv->getImages( $id ),
					'keywords'        => $tv->getKeywords( $id ),
					'similar'         => $tv->getSimilar( $id ),
					'videos'          => $tv->getVideos( $id ),
				];

				$trailers = $youtubeClient->search( $data['meta']['name'] . ' tv trailer' );

				$trailers = array_filter( $trailers, function ( $obj ) {
					return isset( $obj->id->videoId );
				} );

				$trailers = array_slice( $trailers, 0, 5 );

				$data['trailers'] = json_decode( json_encode( $trailers ), true );

				return $app['twig']->render( 'shows_single.html.twig', $data );
			}
		}
	}

	/**
	 * @param Application $app
	 *
	 * @return mixed
	 */
	public function aboutAction(
		Application $app
	) {
		return $app['twig']->render( 'about.html.twig' );
	}

	/**
	 * @param Application $app
	 *
	 * @return mixed
	 */
	public function apiInformationAction(
		Application $app
	) {
		return $app['twig']->render( 'api-information.html.twig' );
	}

	public function indexAction(
		Application $app
	) {
		/**
		 * @var \Tmdb\Client $tmdbClient
		 */
		$tmdbClient = $app['tmdb.client']();
		$tv         = $tmdbClient->getTvApi();

		$limit = 8;
		$data  = [
			'shows' => [
				'airing_today' => array_slice( $tv->getAiringToday()['results'], 0, $limit ),
				'top_rated'    => array_slice( $tv->getTopRated()['results'], 0, $limit ),
				'popular'      => array_slice( $tv->getPopular()['results'], 0, $limit )
			]
		];

		return $app['twig']->render( 'home.html.twig', $data );
	}

	public function airingTodayAction(
		Application $app
	) {
		return $app['twig']->render( 'airing_today.html.twig' );
	}

	public function topRatedAction(
		Application $app
	) {
		return $app['twig']->render( 'top_rated.html.twig' );
	}

	public function popularAction(
		Application $app
	) {
		return $app['twig']->render( 'popular.html.twig' );
	}

	public function trailersAction(
		Application $app
	) {
		return $app['twig']->render( 'trailers.html.twig' );
	}

	public function reviewsAction(
		Application $app
	) {
		return $app['twig']->render( 'reviews.html.twig' );
	}

	public function newsAction(
		Application $app
	) {
		return $app['twig']->render( 'news.html.twig' );
	}

	public function contactAction(
		Application $app
	) {
		return $app['twig']->render( 'contact.html.twig' );
	}
}