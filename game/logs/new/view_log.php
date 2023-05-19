<?php

include('../../config.inc.php');
include('../include/sql.php');

define("LOG_LEVEL_FATAL",0);
define("LOG_LEVEL_ERROR",1);
define("LOG_LEVEL_WARN",2);
define("LOG_LEVEL_INFO",3);
define("LOG_LEVEL_DEBUG",4);
define("LOG_LEVEL_JOB",5);

$sql = "SELECT * FROM log ";

$where = "";
if(isset($_GET['module']))
{
	if($where != ""){
		$where = $where." AND ";
	}
	$where = $where.' module = "'.$_GET['module'].'"';
}

if($where != ""){
	$where = $where." AND ";
}
	
if(isset($_GET['start']))
{	
	$where = $where.' at > "'.$_GET['start'].'"';
}
else
{
	$timestamp = time();
	$beginning_of_day = strtotime("midnight", $timestamp);
	$where = $where.' at > "'.date('Y-m-d H:i:s', $beginning_of_day).'"';
}

if(isset($_GET['end']))
{
	if($where != ""){
		$where = $where." AND ";
	}
	
	$where = $where.' at < "'.$_GET['end'].'"';
}

if($where != "")
{
	$sql = $sql . ' WHERE ' . $where;
}

echo $sql."<br>";

$db = new sql($config['server'].":".$config['port'], $config['game_database'], $config['user'], $config['password']); // create sql-object for db-connection
$data = $db->query($sql." ORDER BY at ASC;");
echo '<table><tr><th>id</th><th>time</th><th>level</th><th>module</th><th>job</th><th>message</th></tr>';

while($log = $db->fetchrow($data))
{
	echo '<tr>';
	switch($log['level'])
	{
		case LOG_LEVEL_FATAL:
			$color= '<font color="ff0000">';
			break;
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
	}
	echo '<td>'.$color.$log['id'].'</font></td>';
	echo '<td>'.$color.$log['at'].'</font></td>';
	echo '<td>'.$color.$log['level'].'</font></td>';
	echo '<td>'.$color.$log['module'].'</font></td>';
	echo '<td>'.$color.$log['job'].'</font></td>';
	echo '<td>'.$color.$log['message'].'</font></td>';
	echo '</tr>';
}
?>
