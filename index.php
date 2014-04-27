<?php
require 'libs/Slim/Slim.php';
require 'libs/JsonAPI/JsonAPI.php';
require 'classes/Test.php';
require 'classes/Berechnungen.php';
require 'classes/YahooDaten.php';
require 'classes/DbHandler.php';

require_once 'includes/config.php';

\Slim\Slim::registerAutoloader ();

$app = new \Slim\Slim ();
$app->view ( new \JsonAPI () );

$test = new Test ();
$dbh = new DbHandler ( $app );
$yahooDaten = new YahooDaten ( $dbh, $app );
$berechnungen = new Berechnungen ( $dbh, $app );

// ------------- GET route -------------
$app->get ( '/', function () use($app) {
	$app->redirect ( SERVER_ROOT . 'libs/swagger' );
} );
$app->get ( '/hello/:name', function ($name) use($app, $test) {
	$app->render ( 200, $test->helloName ( $name ) );
} );
$app->get ( '/dbTest', function () use($app, $dbh) {
	$app->render ( 200, $dbh->dbTest () );
} );
$app->get ( '/leseIndexe', function () use($dbh) {
	$dbh->leseIndexe ();
} );
$app->get ( '/leseDatenstand', function () use($yahooDaten) {
	$yahooDaten->leseDatenstand ();
} );
$app->get ( '/db/update', function () use($app, $yahooDaten) {
	$yahooDaten->aktualisiereIndexe ( "delta" );
} );

// ------------- POST route -------------
$app->post ( '/berechneRendite', function () use($berechnungen) {
	$berechnungen->berechneRendite ();
} );
$app->post ( '/testPost', function () use($app, $test) {
	$test->testPost ();
} );

// ------------- PUT route -------------
$app->put ( '/aktualisiereDatenstandManuell', function () use($app, $yahooDaten) {
	$yahooDaten->aktualisiereDatenstandManuell();
} );

// ------------- PATCH route -------------
$app->patch ( '/patch', function () {
	echo 'This is a PATCH route';
} );

// ------------- DELETE route -------------
$app->delete ( '/delete', function () {
	echo 'This is a DELETE route';
} );

$dbh = null;
$app->run ();
?>
