<?

include_once DIR_CLASSES . "databaseObject.php"; 

include_once DIR_CLASSES . "setTemplates.php"; 

include_once DIR_CLASSES . "muscleParts.php"; 

class Exercises extends databaseObjectColection {
	protected static $table_name = "exercises";
	protected static $class_name = "Exercise";
	protected static $table_filtered = false;

	public static function getByUserID($user_id){
		$user_id = (int)$user_id;
		$rows = Database::execute("select id from exercises WHERE user_id=$user_id");
		$ret = array();
		foreach($rows AS $row){
			$ret[] = new Exercise($row['id']);
		}
		return $ret;
	}

	public static function getByMusclePart($muscle_part){
		if($muscle_part instanceof MusclePart){
			$muscle_part_id = $muscle_part->getAttr('id');
		}else{
			$muscle_part_id = $muscle_part;
		}
		$rows = Database::prepareAndExecute("SELECT id FROM exercises WHERE muscle_part_id=?", array($muscle_part_id));
		$ret = array();
		foreach($rows AS $row){
			$ret[] = new Exercise($row['id']);
		}
		return $ret;
	}
}

class Exercise extends databaseObject {
	protected $id;
	protected $muscle_part_id;
	protected $name;
	protected $user_id;
	protected $paused;
	protected $graphable;
	protected $outdor;
	protected $recom;
	protected $results_json;

	private $rel_columns = array();

	public function __construct($id) {
		$this -> table_name = "exercises";
		$this -> gettable = array("id", "muscle_part_id", "name", "user_id", "paused", "graphable", "outdoor", "recom", "results_json");
		$this -> settable = array("muscle_part_id", "name", "user_id", "paused", "graphable", "outdoor", "recom", "results_json");
		$this -> public_gettable = array("id", "muscle_part_id", "name", "user_id", "paused", "graphable", "outdoor", "recom", "results_json");
		$this -> public_settable = array("muscle_part_id", "name", "paused", "graphable", "outdoor", "recom");
		$this -> load($id);
	}

	public function isAccessibleBy($user){
		if($user->getAttr("id")==$this->user_id){
			return true;
		}else{
			return false;	
		}
	}

	public function public_getAttributes(){
		$ret = parent::public_getAttributes();
		$setTemplates = $this->getSetTemplates();
		$ret['setTemplates'] = array();
		foreach($setTemplates AS $setTemplate){
			$order = $setTemplate->getAttr('orderL');
			$ret['setTemplates'][$order] = $setTemplate->public_getAttributes();
		}
		$ret['days_since_last_exercise'] = $this->daysSinceLastSession();
		$ret['type_name'] = $this->getMusclePart()->getAttr('name');
		//$ret['results']=array();
		$ret['results'] = $this->getResults('sum', false);
		$ret['max_score'] = $this->getMaxScore();
		$ret['total_progress'] = $this->getTotalProgress();
		return $ret;
	}

	private function getTotalProgress(){
		$json = $this->results_json;
		$results = json_decode($json);
		if(count($results)<3){
			if(count($results)<2){
				return 0;
			}else{
				return $results[2]/(($results[0]+$results[1])/2) -1;
			}
		}else{
			$begin_avg = ($results[0]+$results[1])/2;
			$end_avg = ($results[count($results)-1]+$results[count($results)-2])/2;
			$avg_avg = $end_avg/$begin_avg;
			return $avg_avg -1 ;
		}
	}

	private function getMaxScore(){
		$query = "SELECT MAX( result ) FROM log_entry LEFT JOIN log_entry_regular ON rel_id = log_entry_regular.id LEFT JOIN sets ON log_entry_regular.id = sets.log_entry_id WHERE TYPE =  'regular' AND exercise_id =?";
		$rows = Database::prepareAndExecute($query, array($this->id));
		return $rows[0][0];
	}

	private function getSetTemplates(){
		return SetTemplates::getForExercise($this);
	}

	public function getLogEntries($count){
		return LogEntries::getByExercise($this, $count);
	}

	public function daysSinceLastSession(){
		$id = $this->id;
		$query = "SELECT DATEDIFF( CURDATE( ) , (SELECT DATE( begin_time ) FROM log_entry LEFT JOIN log_entry_regular ON rel_id = log_entry_regular.id WHERE log_entry.type =  'regular' AND exercise_id =? ORDER BY begin_time DESC LIMIT 0 , 1 )) AS diff";
		$rows = Database::prepareAndExecute($query, array($this->id));
		if(count($rows)==0){
			return null;
		}else{
			return $rows[0]['diff'];
		}
	}

	public function getResults($type='sum', $with_dates = false){
		$self_id = $this->id;
		switch($type){
			case "sum":
				if(!$with_dates){
					if($this->results_json!=null){
						return json_decode($this->results_json);
					}else{
						$query = "SELECT sum(result) AS `sum` FROM log_entry_regular LEFT JOIN sets ON log_entry_id=log_entry_regular.id left join log_entry ON log_entry_regular.id=log_entry.rel_id WHERE exercise_id=? GROUP BY log_entry_id ORDER BY begin_time ASC";
						$rows = Database::prepareAndExecute($query, array($self_id));
						$ret = array();
						foreach($rows AS $row){
							$ret[]=$row['sum'];
						}			
						$this->set(array('results_json'=>json_encode($ret)));		
						return $ret;						
					}
				}
			break;
		}
	}

	public function getMusclePart(){
		return new MusclePart($this->muscle_part_id);
	}
	
}
