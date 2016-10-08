<?php

include_once dirname(__FILE__) . '/Constants.php';
include_once dirname(__FILE__) . '/DbConnect.php';

/**
* get list of exercises that are created
* @params
* @response
*/
function getListOfExercises() {
	global $RESPMSG;
	$db = DbConnect::getInstance();
    $query = "select id,exercise_name from exercise ";
    $result = $db->queryDb($query,array());
	if(!isset($result['rows'])) return array('type' => 'failed', 'descr' => $RESPMSG[101]);
	$responseArr = array();
    if($result['rows']) {
        foreach($result['rows'] as $val) {
            $val = (array) $val;
            array_push($responseArr, $val);
        }
    }
    return array('type' => 'success', 'exerciseList' => $responseArr);
}

/**
* get list of workouts that are created
* @params
* @response
*/
function getListOfWorkouts() {
    global $RESPMSG, $MOTIVATION;
    $db = DbConnect::getInstance();
    $query = "select id,plan_name, (TIMESTAMPDIFF(SECOND,modified,now()))/60 as modified from plan order by id desc ";
    $result = $db->queryDb($query,array());
    if(!isset($result['rows'])) return array('type' => 'failed', 'descr' => $RESPMSG[102]);
    $responseArr = array();
    if($result['rows']) {
        foreach($result['rows'] as $val) {
            $val = (array) $val;
			$val['time'] = round($val['modified']);
			$rand = rand(0,count($MOTIVATION)-1);
			$val['message'] = '"'.$MOTIVATION[$rand].'"';
            array_push($responseArr, $val);
        }
    }
    return array('type' => 'success', 'planList' => $responseArr);
}

/**
* get plan details for given planId
* @params
* @response
*/
function getSelectedPlanDetails($planId) {
    global $RESPMSG;
    $db = DbConnect::getInstance();
    $query = "select plan_name from plan where id = ? limit 1";
    $arrParams = array('s',&$planId);
    $result = $db->queryDb($query,$arrParams);
    if(!isset($result['rows'])) return array('type' => 'failed', 'descr' => $RESPMSG[103]);
	$name = $result['rows'][0]->plan_name;
    return array('type' => 'success', 'plan' => array('name' => $name, 'id' => $planId));
}

/**
* get list of exercises selected for given planId
* @params
* @response
*/
function getSelectedPlanExerciseDetails($planId) {
    global $RESPMSG;
    $db = DbConnect::getInstance();
    $query = "select exercise_id,day_name from plan_exercise where plan_id = ? order by day_name";
	$arrParams = array('s',&$planId);
    $result = $db->queryDb($query,$arrParams);
    if(!isset($result['rows'])) return array('type' => 'failed', 'descr' => $RESPMSG[104]);
    $response = array();
    if($result['rows']) {
        foreach($result['rows'] as $val) {
            $val = (array) $val;
			if(!isset($response[$val['day_name']])) $response[$val['day_name']] = array();
			array_push($response[$val['day_name']],$val['exercise_id']);
        }
    }
    return array('type' => 'success', 'planExercise' => $response);
}

/**
* check if plan name is already taken
* @params
* @response
*/
function checkIfPlanAlreadyExists($planName,$planId=0) {
	global $RESPMSG;
    $db = DbConnect::getInstance();
    $query = "select count(1) from plan where plan_name = ? ";
	if(!empty($planId)) $query .= " and id != $planId";
    $arrParams = array('s',&$planName);
    $result = $db->queryDb($query,$arrParams,array('count'));
    if(!isset($result['rows'])) return -1;
    if($result['rows'] && $result['rows'][0]->count > 0) return 0;
    return 1;
}

/**
* add new workout plan
* @params
* @response
*/
function addNewWorkoutPlan($planDetails) {
	global $RESPMSG;
	$db = DbConnect::getInstance();

	$ret = checkIfPlanAlreadyExists($planDetails['name']);
	if($ret === -1) return array('type' => 'failed', 'descr' => $RESPMSG[105]);
	if($ret === 0) return array('type' => 'failed', 'descr' => $RESPMSG[106]);

    $query = "insert into plan (plan_name,plan_difficulty) values(?,1)";
    $arrParams = array('s', &$planDetails['name']);
    $result = $db->queryDb($query,$arrParams);
    if(!$result['insert_id']) return array('type' => 'failed', 'descr' => $RESPMSG[107]);

	$planId = $result['insert_id'];
	$exData = $planDetails['exercises'];
	forEach($exData as $day => $exArr) {
		forEach($exArr as $exId) {
	        $query = "insert into plan_exercise (plan_id,exercise_id,day_name) values(?,?,?)";
    		$arrParams = array('sss', &$planId,&$exId,&$day);
    		$result = $db->queryDb($query,$arrParams);
    		if(!$result['insert_id']) return array('type' => 'failed', 'descr' => $RESPMSG[107]);
		}
	}
	return array('type' => 'success', 'descr' => $RESPMSG[108]);
}

/**
* update workout plan for given planId
* @params
* @response
*/
function updateWorkoutPlan($planId,$planDetails) {
	global $RESPMSG;
	$db = DbConnect::getInstance();

    $ret = checkIfPlanAlreadyExists($planDetails['name'],$planId);
    if($ret === -1) return array('type' => 'failed', 'descr' => $RESPMSG[105]);
    if($ret === 0) return array('type' => 'failed', 'descr' => $RESPMSG[106]);

	$query = "update plan set plan_name = ? where id = ? limit 1";
    $arrParams = array('ss', &$planDetails['name'],&$planId);
    $result = $db->queryDb($query,$arrParams);

	$query = "delete from plan_exercise where plan_id = ?";
	$arrParams = array('s', &$planId);
	$result = $db->queryDb($query,$arrParams);

    $exData = $planDetails['exercises'];
    forEach($exData as $day => $exArr) {
        forEach($exArr as $exId) {
            $query = "insert into plan_exercise (plan_id,exercise_id,day_name) values(?,?,?)";
            $arrParams = array('sss', &$planId,&$exId,&$day);
            $result = $db->queryDb($query,$arrParams);
            if(!$result['insert_id']) return array('type' => 'failed', 'descr' => $RESPMSG[109]);
        }
    }
    return array('type' => 'success', 'descr' => $RESPMSG[110]);
}

