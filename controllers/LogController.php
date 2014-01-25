<?

require_once "../classes/logEntries.php";
require_once "../classes/users.php";


class logController{
	
	public function getAction($url_elements, $parameters){
		$user = Users::getCurrentUser();	
		$data = array();
		if(isset($url_elements[2])){
			$log_entry = LogEntries::getByID($url_elements[2]);
			if(!$log_entry->isAccessibleBy($user)){
				die();
			}
			$data = $log_entry->public_getAttributes();
		}else{
			$log_entries = $user->getLogEntries();
			foreach($log_entries AS $log_entry){
				$data[] = $log_entry->public_getAttributes();
			}
		}
		return $data;
		
	}
	
	public function postAction($url_elements, $parameters){
		$user = Users::getUser();
		return $data;		
	}
}