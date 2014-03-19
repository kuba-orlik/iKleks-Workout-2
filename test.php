<pre>
<?

/*$query = "lol  UPDATE users SET login_token=5c38b71b66ca87ed9c1279bde5aebe53 WHERE id=26 ";

echo strtoupper($query);

var_dump(strpos(strtoupper($query), "SET"));

$date = "2013-12-31";
//$date1 = str_replace('-', '/', $date);
var_dump(strtotime($date1));
$tomorrow = date('Y-m-d',strtotime($date . "-2 days"));

echo $tomorrow;*/

//phpinfo();

include 'config.php';

include "classes/points.php";
//include "classes/exercises.php";

//echo Points::cmpDates('2013-12-25', '2013-12-24');


//Points::recalculateForUser(26); 

$exercise = new Exercise(180); 
$exercise->rebuildResultsJSON();