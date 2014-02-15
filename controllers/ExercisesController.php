<?

require_once "../classes/exercises.php";
require_once "../classes/users.php";
require_once "../classes/logEntries.php";

class ExercisesController{
	
	public function getAction($url_elements, $parameters){
		$data = array();
		if(isset($url_elements[2])){
			$exercise = new Exercise($url_elements[2]);
			if(!$exercise->isAccessibleBy(Users::getCurrentUser())){
				header('HTTP/1.0 403 Forbidden');
				die();
			}
			if(isset($url_elements[3])){
				switch($url_elements[3]){
					case "log":
						if(!isset($parameters['count'])){
							$count = 99999;
						}else{
							$count = $parameters['count'];
						}
						$logs = $exercise->getLogEntries($count);
						foreach($logs AS $log){
							$row=$log->public_getAttributes();
							$results = $log->getResults();
							$res_array = array();
							foreach($results AS $result){
								$res_array[$result->getSetTemplate()->getAttr('orderL')] = $result->getAttr('result');
							}
							$row['result'] = $res_array;
							$data[]=$row;
						}
						break;
					default:
						$data = $exercise->public_getAttributes();
						break;
				}
			}else{
				$data = $exercise->public_getAttributes();
			}
		}else{
			$user = Users::getCurrentUser();
			$exercises = $user->getExercises();
			foreach($exercises AS $exercise){
				$data[]=$exercise->public_getAttributes();	
			}
		}
		return $data;
		
	}
	
	public function postAction($url_elements, $parameters){
		var_dump($parameters);
		if(isset($url_elements[3])){
			switch($url_elements[3]){
				case 'logs':
					$required_parameters = array(
						"begin_time",
						"duration_s",
						"type"
					);
					if(!isset($parameters['type'])){
						die('type not set');
					}
					$type = $parameters['type'];
					switch($type){
						case 'custom':
							$required_parameters[]="name";
							$required_parameters[]="result";
							$required_parameters[]="muscle_part_id";
							break;
						case 'regular':
							$required_parameters[]="exercise_id";
							break;
						default:
							die("unknown type $type");
							break;
					}
					foreach($required_parameters AS $req){
						if(!isset($parameters[$req])){
							die("$req not set");
						}
					}
					break;
			}
		}
		return $data;		
	}
}