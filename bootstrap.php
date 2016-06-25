<?php

require_once "vendor/autoload.php";

use Doctrine\ORM\EntityManager;

function getEntityManager() {
	$paths     = [
		__DIR__ . '/src/JobListingBundle/model'
	];
	$isDevMode = false;


	$dbParams = [
		'driver' => 'pdo_sqlite',
		'path'   => __DIR__ . '/config/db/app.db'
	];

	$config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration( $paths, $isDevMode );

	$entityManager = EntityManager::create( $dbParams, $config );

	return $entityManager;
}

