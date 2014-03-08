<?php
require 'libs/Slim/Slim.php';
require 'libs/JsonAPI/JsonAPI.php';
require 'classes/Test.php';
require 'classes/DbHandler.php';

\Slim\Slim::registerAutoloader ();

$app = new \Slim\Slim ();
$json = new \JsonAPI ();
$test = new Test ();
$dbh = new DbHandler ();

$app->view ( $json );

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
$app->get ( '/getSMI', function () use($app, $dbh) {
	$app->render ( 200, $dbh->getSMI () );
} );

// ------------- POST route -------------
$app->post ( '/post', function () {
	echo 'This is a POST route';
} );
$app->post ( '/db/add', function () {
	echo 'addHistoricalPrices';
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
