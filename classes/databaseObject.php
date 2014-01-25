<?

include_once "database.php";

class databaseObjectColection{
	
	//protected static $table_name;
	//protected static $class_name;
	protected static $filtered; //boolean
	protected static $filter_column;
	protected static $filter_value;
	
	public static function getAll($sort_attr=array()){
		/*
		 * $sort_attr = array(
		 * 	sorted=>true,
		 * 	sort_column=>'last_modified',
		 * 	sort_order=>'ASC'
		 * )
		 * 
		 */
		if(self::$filtered){
			return self::getFiltered(static::$filter_column, static::$filter_value);
		}else{
			return self::getUnfiltered($sort_attr);
		}
	}
	
	protected static function getUnfiltered($sort_attr){		
		$db = Database::connectPDO();
		$query = "SELECT id FROM " . static::$table_name;
		if(isset($sort_attr['sorted']) && $sort_attr['sorted']){
			$query.=" ORDER BY " . $sort_attr->sort_column . " " . $sort_attr->sort_order;
		}
		$rows = $db->query($query );//->fetchAll();
		$ret = array();
		foreach($rows AS $row){
			$ret[] = new static::$class_name($row["id"]);
		}
		return $ret;
	}
	
	protected static function getFiltered($column, $value, $class_name=null){
		if($class_name==null) $class_name=static::$class_name;
		$db = Database::connectPDO();
		$query = "SELECT id FROM " . static::$table_name . " WHERE $column = ?";
		if(isset($sort_attr['sorted']) && $sort_attr['sorted']){
			$query.=" ORDER BY " . $sort_attr->sort_column . " " . $sort_attr->sort_order;
		}
		/*$prp = $db->prepare($query);
		$prp->execute(array($value));
		$rows = $prp->fetchAll();*/
		$rows = Database::prepareAndExecute($query, array($value));
		$ret = array();
		foreach($rows AS $row){
			$ret[] = new $class_name($row["id"]);
		}
		return $ret;
	}
	
	public static function create(){
		$query = "INSERT INTO " . static::$table_name . " VALUES ()";
		$db = Database::connectPDO();
		$rows = $db->query($query);
		$id = $db->lastInsertId();
		return new static::$class_name($id);
	}

	public static function delete($object_id){
		$query = "DELETE FROM " . static::$table_name . " WHERE id=?";
		/*$db = Database::connectPDO();
		$prp = $db->prepare($query);
		$prp->execute(array($object_id));*/
		Database::prepareAndExecute($query, array($object_id));
		return array();
	}	

	public static function exists($id){
		$query = "SELECT id FROM " . static::$table_name . " WHERE id=?";
		$rows = Database::prepareAndExecute($query, array($id));
		return count($rows)!=0;
	}

	public static function put($attributes, $public=true){
		$needs_creating = false;
		$id = $attributes['id'];
		if(!isset($id) || $id==null || $id==0){
			$needs_creating=true;
		}
		if($needs_creating){
			$object = static::create();
		}else{
			$object = new static::$class_name($id);
		}
		if($public){
			$object->public_set($attributes);
		}else{
			$object->set($attributes); 
		}
		return $object;
	}
}

class databaseObject{
	protected $table_name;
	protected $settable = array();
	protected $gettable = array();
	
	protected function load($id){
		$db = Database::connectPDO();
		$id = (int)$id;
		$query = "SELECT * FROM ". $this->table_name . " WHERE id=$id";
		/*$stmt = $db->prepare($query);
		$stmt->execute(array($id));
		$rows = $stmt->fetchAll();*/
		$rows = Database::execute($query);
		foreach($this AS $key=>$value){
			if(isset($rows[0][$key]) && $key!="id"){
			 	$this->$key = $rows[0][$key];
			}
				
		}	
		$this->id = $id;
		if(sizeof($rows)==0){
			return false;
		}else{
			return true;
		}
	}
	
	public function getAttr($attr){
		if(in_array($attr, $this->gettable) && isset($this->$attr)){
			return $this->$attr;			
		}else{
			return NULL;
		}
	}
	
	public function set($array){
		$table = $this->table_name;
		foreach($array AS $key=>$value){
			if(in_array($key, $this->settable)){
				$this->$key = $array[$key];
				$query = "UPDATE " . $table . " SET $key=? WHERE id=?";
				/*$db = Database::connectPDO();
				$stmt = $db->prepare($query);
				$stmt->execute(array($value, $this->id));*/
				Database::prepareAndExecute($query, array($value, $this->id));
			}
		}
	}
	
	public function public_set($array){
		$allowed = array();
		foreach($array AS $key=>$value){
			if(in_array($key, $this->public_settable)){
				$allowed[$key] = $value;
			}
		}
		$this->set($allowed);
	}
	
	public function public_getAttributes(){
		$ret = array();
		foreach($this->public_gettable AS $attr){
			$ret[$attr] = $this->getAttr($attr);
		}
		return $ret;
	}
	
	public function getAttributes(){
		$ret = array();
		foreach($this->gettable AS $attr){
			$ret[$attr] = $this->getAttr($attr);
		}
		return $ret;
	}
}

