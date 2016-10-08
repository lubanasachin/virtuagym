<?php

require_once '../include/Common.php';
require '.././libs/Slim/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

/*** init SLIM API */
$app = new \Slim\Slim();
/*** set content type */
$app->contentType('application/json');


/*** GET: list of exercices */
$app->get('/exercises', function () use ($app) {
	$ret = getListOfExercises();
	echoResponse(201, $ret);
});

/*** GET: list of workouts */
$app->get('/workout', function () use ($app) {
	$ret = getListOfWorkouts();
	echoResponse(201, $ret);
});

/*** GET: workout details for given planId */
$app->get('/workout/:id', function ($planId) use ($app) {
	$resp = getSelectedPlanDetails($planId);
	$ret = getSelectedPlanExerciseDetails($planId);
	$ret['plan'] = $resp['plan'];
	echoResponse(201, $ret);
});

/*** POST: add new workout details */
$app->post('/workout', function () use ($app) {
    $json = $app->request->getBody();
	$planData = json_decode($json,true);
	$ret = addNewWorkoutPlan($planData);
	echoResponse(201, $ret);
});

/*** PUT: modify workout details for given planId */
$app->put('/workout/:id', function ($planId) use ($app) {
    $json = $app->request->getBody();
	$planData = json_decode($json,true);
	$ret = updateWorkoutPlan($planId,$planData);
	echoResponse(201, $ret);
});

/*** send response to client */
function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    $app->status($status_code);
    $app->contentType('application/json');
    echo json_encode($response);
}

$app->run();
