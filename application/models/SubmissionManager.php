<?php

class Application_Model_SubmissionManager extends Application_Model_Manager{
	
	public static $table = "submission";
	
	public static function add($values){
		return parent::add(array(
			"problem" => $values['problem'],
			"user" => $values['user'],
			"language" => $values['language']
		));
	}
	
	public static function set($values, $id){
		return parent::set(array(
			"problem" => $values['problem'],
			"user" => $values['user'],
			"time" => $values['time'],
			"date_judge" => $values['date_judge'],
			"language" => $values['language'],
			"state" => $values['state']
		), $id);
	}
	
	public static function getNext(){
		$db = Zend_Db_Table::getDefaultAdapter();
		$select = 'SELECT * FROM submission WHERE (TIMEDIFF(NOW(),date_judge) > "00:05:00" OR date_judge IS NULL) AND state = 0 ORDER BY id LIMIT 1';
		return $db->query($select)->fetch();
	}
	
}

