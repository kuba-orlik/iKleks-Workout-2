<?

include_once DIR_CLASSES . "databaseObject.php"; 

include_once DIR_CLASSES . "setTemplates.php"; 

include_once DIR_CLASSES . "setResults.php"; 

include_once DIR_CLASSES . "users.php";

include_once DIR_CLASSES . "exercises.php";

class LogEntries extends databaseObjectColection {
	protected static $table_name = "log_entry";
	protected static $class_name = "LogEntry";
	protected static $table_filtered = false;

	public static function getForUser($user, $count=30){
		if($user instanceof User){
			$user_id = $user->getAttr('id');
		}else{
			$user_id = $user;
		}
		$rows = Database::prepareAndExecute('SELECT id, type FROM log_entry WHERE user_id=? ORDER BY begin_time DESC', array($user_id));
		$ret = array();
		foreach($rows AS $row){
			$ret[] = self::getByID($row['id']);
			if(count($ret)>$count){
				break;
			}
		}
		return $ret;
	}

	public static function getByID($id){
		$rows = Database::prepareAndExecute("SELECT type FROM log_entry WHERE id=?", array($id));
		$type = $rows[0]['type'];
		switch($type){
			case 'regular':
				$ret =  new LogEntry_regular($id);
				break;
			case 'custom':
				$ret = new LogEntry_custom($id); //should be custom
		}
		return $ret;
	}

	public static function getByExercise($exercise, $count=30, $order='DESC'){
		if($exercise instanceof Exercise){
			$exercise_id = $exercise->getAttr('id');
		}else{
			$exercise_id = $exercise;
		}
		if($order!='DESC' && $order!='ASC'){
			$order = 'DESC';
		}
		$rows = Database::prepareAndExecute("SELECT log_entry.id  AS id FROM log_entry LEFT JOIN log_entry_regular ON log_entry.rel_id=log_entry_regular.id WHERE log_entry_regular.exercise_id=? ORDER BY begin_time $order LIMIT 0, ?", array($exercise_id, $count));
		$ret = array();
		foreach($rows AS $row){
			$ret[] = new LogEntry_regular($row['id']);
		}
		return $ret;
	}
}

abstract class LogEntry extends databaseObject {
	protected $id;
	protected $begin_time;
	protected $duration_s;
	protected $type;
	protected $rel_id;
	protected $legacy;
	protected $user_id;

	private $rel_columns = array();

	public function __construct($id) {
		$this -> table_name = "log_entry";
		$this -> gettable = array("id", "begin_time", "duration_s", "type", "rel_id", "legacy", 'user_id');
		$this -> settable = array("begin_time", "duration_s", "type", "rel_id", "legacy", 'user_id');
		$this -> public_gettable = array("id", "begin_time", "duration_s", "type", "legacy", 'user_id');
		$this -> public_settable = array("begin_time", "duration_s", "legacy");
		$this -> load($id);
	}
	
	abstract public function getResults();

	public function public_getAttributes(){
		$ret = parent::public_getAttributes();
		//$ret['result'] = $this->getResults();
		//var_dump($this->getResults());
		$ret['begin_timestamp'] = $this->getBeginTimestamp();
		return $ret;
	}

	public function getBeginTimestamp(){
		$query = "SELECT UNIX_TIMESTAMP(begin_time) FROM log_entry WHERE id=?";
		$rows = Database::prepareAndExecute($query, array($this->id));
		return  $rows[0][0];
	}

	public function isAccessibleBy($user){
		if($user instanceof User){
			$user_id = $user->getAttr('id');
		}else{
			$user_id = $user;
		}
		if($user_id==$this->user_id){
			return true;
		}else{
			return false;
		}
	}
}

class LogEntry_regular extends LogEntry{

	protected $exercise_id;

	public function __construct($logEntry_id){
		parent::__construct($logEntry_id);
		$this->gettable[] = 'exercise_id';
		$this->public_gettable[] = 'exercise_id';

		$rows = Database::prepareAndExecute("SELECT exercise_id FROM log_entry_regular WHERE id=?", array($this->rel_id));
		$exercise_id = $rows[0]['exercise_id'];

		//$this->results = $this->getResults();

		$this->exercise_id = $exercise_id;
	}

	public function getResults(){
		return SetResults::getForLogEntry($this->id);
	}

	private function getResultsAttributes(){
		$ret = array();
		foreach($this->getResults() AS $result){
			$ret[] = $result->public_getAttributes();
		}
		//var_dump($ret);
		return $ret;
	}

	public function public_getAttributes(){
		$ret = parent::public_getAttributes();
		$ret['results'] = $this->getResultsAttributes();
		return $ret;
	}

}

class LogEntry_custom extends LogEntry{
	protected $name;
	protected $result;
	protected $muscle_part_id;

	public function __construct($id){
		parent::__construct($id);
		$rows = Database::prepareAndExecute('SELECT name, result, muscle_part_id FROM log_entry_custom WHERE id=?', array($this->rel_id));
		$toSetArr = array('name', 'result', 'muscle_part_id');
		foreach($toSetArr AS $toSet){
			$this->settable[]=$toSet;
			$this->gettable[]=$toSet;
			$this->public_settable[]=$toSet;
			$this->public_gettable[]=$toSet;
			$this->$toSet = $rows[0][$toSet];
		}
	}

	public function getResults(){
		return array(
			'name'			=>$this->name,
			'result'		=>$this->result,
			'muscle_part_id'=>$this->muscle_part_id
		);
	}

	public function public_getAttributes(){
		$ret = parent::public_getAttributes();
		unset($ret['name']);
		unset($ret['result']);
		unset($ret['muscle_part_id']);
		$ret['result'] = $this->getResults();
		return $ret;
	}

}