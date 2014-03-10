<?php
require 'libs/Slim/Slim.php';
require 'libs/JsonAPI/JsonAPI.php';
require 'classes/Test.php';
require 'classes/Berechnungen.php';
require 'classes/DbHandler.php';

\Slim\Slim::registerAutoloader ();

$app = new \Slim\Slim ();
$app->view ( new \JsonAPI () );

$test = new Test ();
$berechnungen = new Berechnungen ();
$dbh = new DbHandler ();

// ------------- GET route -------------
$app->get ( '/', function () use($app) {
	$app->redirect ( '/api6o/libs/swagger' );
} );
$app->get ( '/hello/:name', function ($name) use($app, $test) {
	$app->render ( 200, $test->helloName ( $name ) );
} );
$app->get ( '/dbTest', function () use($app, $dbh) {
	$app->render ( 200, $dbh->dbTest () );
} );
$app->get ( '/leseIndexe', function () use($app, $dbh) {
	$app->render ( 200, $dbh->leseIndexe () );
} );

// ------------- POST route -------------
$app->post ( '/berechneRendite', function () use($app, $berechnungen) {
	$app->render ( 200, $berechnungen->berechneRendite () );
} );
$app->post ( '/db/add', function () use($app, $dbh) {
	$dbh->aktualisiereAlleIndexe ();
} );
$app->post ( '/db/update', function () use($app, $dbh) {
	$dbh->aktualisiereDeltaIndexe ();
} );
$app->post ( '/testPost', function () use($app, $test) {
	$test->testPost ();
} );

// ------------- PUT route -------------
$app->put ( '/put', function () {
	echo 'This is a PUT route';
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
