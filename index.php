<?php
/*    
	This file is part of STFC.
	Copyright 2006-2007 by Michael Krauss (info@stfc2.de) and Tobias Gafner
		
	STFC is based on STGC,
	Copyright 2003-2007 by Florian Brede (florian_brede@hotmail.com) and Philipp Schmidt
	
    STFC is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version.

    STFC is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

define ("GALAXY1_NAME", 'Brown Bobby');
define ("GALAXY2_NAME", 'Fried Egg (Dev)');
define ("GALAXY3_NAME", 'Forge');

define ("GALAXY1_IMG", 'gfx/ngc7742.jpg');
define ("GALAXY2_IMG", 'gfx/m64.jpg');
define ("GALAXY3_IMG", 'gfx/m64.jpg');

define ("GALAXY1_BG", 'gfx/ngc7742bg.jpg');
define ("GALAXY2_BG", 'gfx/m64bg.jpg');
define ("GALAXY3_BG", 'gfx/m64bg.jpg');

define('GAME_EXE', 'index.php');

include('config.inc.php');
include('locale.php');
include('game/include/global.php');
include('game/include/sql.php');
include('game/include/functions.php');

error_reporting(E_ALL);

function display_message($header,$message,$bg) {
    global $main_html;
    $main_html .= '
<table align="center" border="0" cellpadding="2" cellspacing="2" width="500" class="border_grey" style=" background-color:#000000; background-position:left; background-repeat:no-repeat;">
  <tr>
    <td width="100%">
      <center><span class="sub_caption">'.$header.'</span></center>
      <table width="100%" border="0" cellpadding="0" cellspacing="0" style=" background-image:url(\''.$bg.'\'); background-position:left; background-repeat:yes;">
        <tr>
          <td width="100%" valign="top"><span class="sub_caption2"><br>'.$message.'<br><br></span></td>
        </tr>
      </table>
    </td>
  </tr>
</table>';
}

function send_mail($myname, $myemail, $contactname, $contactemail, $subject, $message) {
    $headers = 'MIME-Version: 1.0'.NL.
               'Content-type: text/plain; charset=iso-8859-1'.NL.
               'X-Priority: 1'.NL.
               'X-MSMail-Priority: High'.NL.
               'X-Mailer: php'.NL.
               'From: "'.$myname.'" <'.$myemail.'>'.NL;

    return mail('"'.$contactname.'" <'.$contactemail.'>', $subject, $message, $headers);
}


$db = new sql($config['server'].":".$config['port'], $config['game_database'], $config['user'], $config['password']); // create sql-object for db-connection

$action = htmlspecialchars((!empty($_GET['a'])) ? $_GET['a'] : 'home');

$title_html = 'Star Trek - Galaxy War';
$meta_descr = 'STGW is a free browser based multi-player game by playing the role of different races and peoples of the universe and rewrite history.';
$main_html = '';

if(strstr($action, '.')) {
    $main_html = '<br><br><center><span style="font-size: 20px;">Die ausgew&auml;hlte Seite "'.$action.'" existiert nicht.</span></center><br><br>';
}

if(!file_exists('pages/'.$action.'.php')) {
    $main_html = '<br><br><center><span style="font-size: 20px;">Die ausgew&auml;hlte Seite "'.$action.'" existiert nicht.</span></center><br><br>';
}
else
    include('pages/'.$action.'.php');

//News
/*
$sql = ' SELECT *
         FROM page_news
         ORDER BY date DESC
         LIMIT 0, 2';

if(($q_news = $db->query($sql)) === false) {
    $news_html = $locale['nonews'].'<br><br><img src="./gfx/header_newsitem.jpg"><br><br>';
}
else {
    $news_html = '';

    while($news = $db->fetchrow($q_news)) {
        $news_day = (int)gmdate('d', $news['date']);
        $today = (int)gmdate('d', time());

        if($news_day == $today) {
            $date_str = $locale['today'].', '.gmdate('H:i', $news['date']);
        }
        elseif($news_day == ($today - 1)) {
            $date_str = $locale['yesterday'].', '.gmdate('H:i', $news['date']);
        }
        else {
            $date_str = gmdate('d.m.y', $news['date']);
        }

        $news_html .= '<span style="font-size: 10px;">'.$date_str.'</span><br><a href="index.php?a=news&show='.$news['id'].'"><b>'.$news['header'].'</b></a><br><br>';
    }
}
*/
//Ende News

?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $title_html; ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="format-detection" content="telephone=no">
<meta name="format-detection" content="date=no">
<link rel="stylesheet" type="text/css" href="css/lcars-classic-min.css">
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
<div class="wrap">
	<div class="left-frame-top">
		<div class="panel-1"><a href="">LCARS</a></div>
		<div class="panel-2">02<span class="hop">-262000</span></div>
	</div>
	<div class="right-frame-top">
		<div class="banner">
			Star Trek &#149; Galaxy War
		</div>
			<div class="data-cascade-button-group">
				<div class="cascade-wrapper">
					<div class="data-cascade">
						<div class="row-1"> <div class="grid">101</div> <div class="grid-2">7109</div> <div class="grid">1966</div> <div class="grid">1221</div> <div class="grid">2020</div> <div class="grid-4">1444</div> <div class="grid-2">102</div> <div class="grid-2">1103</div> <div class="grid-2">1935</div> <div class="grid-1">1940</div> <div class="grid-1">708</div> <div class="grid-1">M113</div> <div class="grid-3">1956</div> <div class="grid-3">1209</div> </div> <div class="row-2"> <div class="grid">102</div> <div class="grid-2">8102</div> <div class="grid">1987</div> <div class="grid">044</div> <div class="grid">0051</div> <div class="grid-4">607</div> <div class="grid-2">1976</div> <div class="grid-2">1031</div> <div class="grid-2">1984</div> <div class="grid-1">1954</div> <div class="grid-1">1103</div> <div class="grid-1">415</div> <div class="grid-3">1045</div> <div class="grid-3">1864</div> </div> <div class="row-3"> <div class="grid">103</div> <div class="grid-2">714</div> <div class="grid">1993</div> <div class="grid">0222</div> <div class="grid">052</div> <div class="grid-4">1968</div> <div class="grid-2">514</div> <div class="grid-2">746</div> <div class="grid-2">56</div> <div class="grid-1">47</div> <div class="grid-1">716</div> <div class="grid-1">8719</div> <div class="grid-3">417</div> <div class="grid-3">602</div> </div> <div class="row-4"> <div class="grid">104</div> <div class="grid-2">6104</div> <div class="grid">1995</div> <div class="grid">322</div> <div class="grid">89</div> <div class="grid-4">1931</div> <div class="grid-2">1701</div> <div class="grid-2">51</div> <div class="grid-2">29</div> <div class="grid-1">218</div> <div class="grid-1">908</div> <div class="grid-1">2114</div> <div class="grid-3">85</div> <div class="grid-3">3504</div> </div> <div class="row-5"> <div class="grid">105</div> <div class="grid-2">08</div> <div class="grid">2001</div> <div class="grid">713</div> <div class="grid">079</div> <div class="grid-4">1940</div> <div class="grid-2">LV</div> <div class="grid-2">426</div> <div class="grid-2">105</div> <div class="grid-1">10</div> <div class="grid-1">1206</div> <div class="grid-1">1979</div> <div class="grid-3">402</div> <div class="grid-3">795</div> </div> <div class="row-6"> <div class="grid">106</div> <div class="grid-2">31</div> <div class="grid">2017</div> <div class="grid">429</div> <div class="grid">65</div> <div class="grid-4">871</div> <div class="grid-2">1031</div> <div class="grid-2">541</div> <div class="grid-2">656</div> <div class="grid-1">764</div> <div class="grid-1">88</div> <div class="grid-1">001</div> <div class="grid-3">27</div> <div class="grid-3">05</div> </div>
					</div>
				</div>
			<div class="button-col-1">
				<div class="button" id="top-left"><a href="<?php echo parse_link() ?>">Home</a></div>
				<div class="button" id="bottom-left"><a href="<?php echo parse_link('a=menu') ?>">Menu</a></div>
			</div>
			<div class="button-col-2">
				<div class="button" id="top-right"><a href="<?php echo parse_link('a=register') ?>">Anmelden</a></div>
				<div class="button" id="bottom-right"><a href="<?php echo parse_link('a=login') ?>">Einloggen</a></div>
			</div>
		</div> 
		<div class="top-corner-bg">
			<div class="top-corner"></div>
		</div>
		<div class="bar-panel">
			<div class="bar-1"></div>
			<div class="bar-2"></div>
			<div class="bar-3"></div>
			<div class="bar-4"></div>
			<div class="bar-5"></div>
		</div>
	</div>
</div> 
<div class="wrap" id="gap">
	<div class="left-frame">
		<div>
			<div class="panel-3">03<span class="hop">-111968</span></div>
			<div class="panel-4">04<span class="hop">-041969</span></div>
			<div class="panel-5">05<span class="hop">-1701D</span></div>
			<div class="panel-6">06<span class="hop">-071984</span></div>
			<div class="panel-7">07<span class="hop">-081940</span></div>
			<div class="panel-8">08<span class="hop">-47148</span></div>
			<div class="panel-9">09<span class="hop">-081966</span></div>
		</div>
		<div>
			<div class="panel-10">10<span class="hop">-31</span></div>
			<div class="panel-11"><a href="">LCARS</a></div>
		</div>
	</div>
	<div class="right-frame">
		<div class="bar-panel">
		<div class="bar-6"></div>
		<div class="bar-7"></div>
		<div class="bar-8"></div>
		<div class="bar-9"></div>
		<div class="bar-10"></div>
		</div>
		<div class="corner-bg">
			<div class="corner"></div>
		</div>
		<div class="content">

			<!-- Start your content here. -->

			<?php echo $main_html; ?>

			<!-- End content area. -->

			<div class="lcars-bar-slice-top"></div>
			<div class="lcars-bar">
				<div class="lcars-bar-inner">
					<div class="lcars-bar-cutout"></div>
				</div>
				</div>
			<div class="lcars-bar-slice-bottom"></div>
			<div class="footer">
				<p class="go-center">LCARS Inspired Website Template by <a href="https://www.thelcars.com">www.TheLCARS.com</a>.</p>
			</div>
		</div>
	</div>
</div>
<script>
document.addEventListener("touchstart", function() {},false);

        function contractall(){
        if (document.getElementById){
        var inc=0
        while (document.getElementById("dropmsg"+inc)){
        document.getElementById("dropmsg"+inc).style.display="none"
        inc++
        }
        }
        }


        function expandone(){
        if (document.getElementById){
        var selectedItem=document.register.user_race.selectedIndex
        contractall()
        document.getElementById("dropmsg"+selectedItem).style.display="block"
        }
        }

        if (window.addEventListener)
        window.addEventListener("load", expandone, false)
        else if (window.attachEvent)
        window.attachEvent("onload", expandone)

        function myFunction() {
          var x = document.getElementById("myTopnav");
          if (x.className === "topnav") {
            x.className += " responsive";
          } else {
            x.className = "topnav";
          }
        }
</script>
<script type="text/JavaScript" src="overlib.js"></script>
</body>
</html>
