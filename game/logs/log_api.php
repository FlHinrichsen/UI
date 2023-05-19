<?php

include('../include/sql.php');
include('../../config.inc.php');

define("LOG_LEVEL_FATAL",0);
define("LOG_LEVEL_ERROR",1);
define("LOG_LEVEL_WARN",2);
define("LOG_LEVEL_INFO",3);
define("LOG_LEVEL_DEBUG",4);
define("LOG_LEVEL_JOB",5);

$sql = "SELECT * FROM log ";

$where = "";
if(isset($_POST['module']))
{
	if($where != ""){
		$where = $where." AND ";
	}
	$where = $where.' module = "'.$_POST['module'].'"';
}

if($where != ""){
	$where = $where." AND ";
}
	
$where = $where.' id = "'.$_POST['id'].'"';

$sql = $sql . ' WHERE ' . $where;

$db = new sql($config['server'].":".$config['port'], $config['game_database'], $config['user'], $config['password']); // create sql-object for db-connection
$data = $db->query($sql);

while($log = $db->fetchrow($data))
{
	switch($log['level'])
	{
		case LOG_LEVEL_ERROR:
			$color= '<font color="aa0000">';
			break;
		case LOG_LEVEL_WARN:
			$color= '<font color="bbbb00">';
			break;
		case LOG_LEVEL_INFO:
			$color= '<font color="000000">';
			break;
		case LOG_LEVEL_DEBUG:
			$color= '<font color="999999">';
			break;
		case LOG_LEVEL_JOB:
			$color= '<font color="0000ff">';
			break;
		case LOG_LEVEL_FATAL:
		default:
			$color= '<font color="ff0000">';
			break;
	}
	
	$result = array(
		$color.$log['id'].'</font>',
		$color.$log['at'].'</font>',
		$color.$log['level'].'</font>',
		$color.$log['module'].'</font>',
		$color.$log['job'].'</font>',
		$color.$log['message'].'</font>'
	);
	
	echo json_encode($result);
}
?>
