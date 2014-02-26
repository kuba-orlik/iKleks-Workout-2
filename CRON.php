<?
require "config.php";
require "classes/users.php";

class EmailReminder{

	private static function prepareHtmlBody($user, $exercise){
		$user_name = $user->getAttr("name");
		$current_points = $user->getScores(0, 1)[0]["me"];
		$current_combo = $user->getCurrentStreak();
		$exercise_id = $exercise->getAttr("id");
		$exercise_name = $exercise->getAttr("name");
		$exercise_daysAgo = $exercise->daysSinceLastSession();
		ob_start();
		?>
		<div style='background-color: rgb(238,236,236); font-family:Roboto,Calibri,Helvetica,sans-serif'>
			<div style="text-align:left; font-size:2rem">
				Hello, <?=$user_name?>
			</div>
			<div style="padding:10px; margin:10px; color:rgb(49,49,49); background-color:white">
				<div>
					Recommended exercise for today:
				</div>
				<div style="font-size:5rem; text-align:center">
					<a href="<?=DOMAIN?>/#exercise/<?=$exercise_id?>">Exercise name</a>
				</div>
				<div style='text-align:center;'>
					last exercised 2 days ago
				</div>
				<div style='text-align:center'>
					<button style="background-color: #4f7296;border: none;color: white;font-size: 1rem;padding: 6px 19px;border-radius: 2px;box-shadow: inset -1px -2px 0px 1px rgba(0, 0, 0, 0.3);cursor: pointer;text-decoration: none !important;-webkit-touch-callout: none;-webkit-user-select: none;-khtml-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;outline: none;transition: all 200ms;"> 
						exercise!
					</button>
				</div>
			</div>
		</div>

		<?
		$html = ob_get_clean();
		return $html;
	}

	private static function prepareHtmlTitle($user, $exercise){
		$user_name = $user->getAttr("name");
		$current_points = $user->getScores(0, 1)[0]["me"];
		$title = "Hello, $user_name, you currently have $current_points workout points!";
		return $title;
	}

	private static function sendEmail($address, $subject, $content){
		echo "sending email to $address</br>";
		//$domain = str_replace("http://", "", DOMAIN);
		$domain = "interpress.pl";
		$newsubject='=?UTF-8?B?'.base64_encode($subject).'?=';
		$header = 'FROM: kuba@' . $domain . "\r\n" .
    		'Reply-To: kuba@' . $domain . "\r\n" .
    		'Content-Type:text/html;charset=utf-8' . "\r\n".
    		'X-Mailer: PHP/' . phpversion();
    	echo $header;
   		$success = mail($address, $newsubject, $content, $header, "-fkuba@interpress.pl");
		var_dump($success);
		if(!$success){
			echo "FAIL";
		}
	}

	private static function send_email_if_needed($user){
		if($user->getAttr("reminder_hour")!=null){
			$recommendations = $user->getRecommendations();
			$recom = $recommendations['recom'];
			$email_body = self::prepareHtmlBody($user, $recom);		
			$email_title = self::prepareHtmlTitle($user, $recom);	
			self::sendEmail($user->getAttr('email'), $email_title, $email_body);
		}
	}

	public static function process(){
		$all_users = Users::getAll();
		foreach($all_users AS $user){
			self::send_email_if_needed($user);
		}
	}
}

EmailReminder::process();