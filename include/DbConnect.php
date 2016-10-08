<?php

include_once dirname(__FILE__) . '/Constants.php';

/**
* Database connect class
*/
class DbConnect {

    //database connection varibale
    private $con;
	
    //instance variable
    private static $instance; 

    /**
    * private class constructor method
    * @params
    * @response
    */
    private function __construct() {
		$this->connect();
    }

    /**
    * get instance of the database connect class
    * @params
    * @response
    */
	public static function getInstance() {
		if(!self::$instance) self::$instance = new self();
		return self::$instance;
	}

    /**
    * database connect
    * @params
    * @response
    */
    public function connect() {
		$this->con = mysqli_connect('p:'.DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
        if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }

    /**
    * database query (select/update/insert/delete)
    * @params
    * @response
    */
    public function queryDb($sql, $arrParams, $arrBindNames=false) {
        $result = new stdClass();
        if ($stmt = $this->con->prepare($sql)) {
            $method = new ReflectionMethod('mysqli_stmt', 'bind_param');
            if(count($arrParams) > 0) $method->invokeArgs($stmt, $arrParams);
            $stmt->execute();
            $meta = $stmt->result_metadata();
            if (!$meta) {
                $result->affected_rows = $stmt->affected_rows;
                $result->insert_id = $stmt->insert_id;
            } else {
                $stmt->store_result();
                $params = array();
                $row = array();
                if ($arrBindNames) {
                    for ($i=0,$j=count($arrBindNames); $i<$j; $i++) $params[$i] = &$row[$arrBindNames[$i]];
                } else {
                    while ($field = $meta->fetch_field()) $params[] = &$row[$field->name];
                }
                $meta->close();
                $method = new ReflectionMethod('mysqli_stmt', 'bind_result');
                $method->invokeArgs($stmt, $params);
                $result->rows = array();
                while ($stmt->fetch()) {
                    $obj = new stdClass();
                    foreach($row as $key => $val) $obj->{$key} = $val;
                    $result->rows[] = $obj;
                }
                $stmt->free_result();
            }
            $stmt->close();
        }
        $result = (array) $result;
        return $result;
    }
}

