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

$proverbs = array(
    'Among the weapons mention the laws.',
    'The truth is usually only an excuse for lack of imagination.',
    'Betrayal is like the beauty in the eye of the beholder.',
    'From enemies can be very dangerous friends.',
    'A true victory consists of that the enemy becomes clear, how wrong it was, offering at all resistance.',
    'The luck favours the brave.',
    'If we are strong,<br>this is not the signal for war?',
    'Those who do not learn from history,<br>are condemned to repeat it.<br><br>Those that do not learn from history,<br>are simply doomed.',
    'That is all I know about the war:<br>One wins, one loses<br>and nothing is so afterwards,<br>as it was before.',
    'Wealth is too valuable to him<br>the rich alone.',
    'Life?<br>DThe life is like a knifing in a completely misprinted Bar:<br>If one is forced on the soil,<br>is best that rises fast again.',
    'Absolute power corrupts absolutely.<br>What is a problem.<br>If you do not have power.',
    'One can not always escape from death. But one can make it very difficult for the pig dog.',
    'Before the knowledge come understanding. Before understanding seeing comes. Before seeing recognizing comes. Before recognizing the knowledge comes.',
    'Success is the sum of correct decisions.',
    'The most unfair peace is still better than the fairest war.',
    'Which seems to have a use for nobody, does not become also the goal for desires other one.',
    'Liberty is not throwing chains off - this is only their condition.<br>Liberty is the fortune to decide and to these go for a way.<br><br>But this way should prove as wrong, then again a choice is to be made.<br>Who continues nevertheless it, puts into new chains.',
    'The beginner of a combat art cannot fight, however often does it.<br><br>The master can fight, does it however only rarely.',
    'It is better to accomplish imperfect decisions,<br>to look all the time for perfect decisions which it will never come.',
    'If a lion comes from right, one should run to the left.<br><br>In this situation it does not help to be creative, it you will kill.',
    'The winner in a fight man against man is the derjeninge, who has a ball more in the magazine.',
    'One loses most of the time so that you will gain time...',
    'Who want war, only wants peace in accordance with its terms.'
);

$n_proverbs = count($proverbs);


$err_title = $locale['activate_error_title'];
$title_html = $locale['activate_title'];
$meta_descr = $locale['activate_descr'];
$main_html = '<div class="caption">'.$locale['account_activation'].'</div>';


if( (!isset($_GET['galaxy'])) || (empty($_GET['user_id'])) || (empty($_GET['key']))) {
    display_message($err_title,$locale['error_missing_info'],GALAXY1_BG);
    return 1;
}

$galaxy = (int)$_GET['galaxy'];

$user_id = (int)$_GET['user_id'];

$gkey=$_GET['key'];
$key = md5( pow( $user_id ,2) );

switch($galaxy)
{
    case 0:
        $galaxyname = GALAXY1_NAME;
        $bg = GALAXY1_BG;
        $mydb = $db;
    break;
    case 1:
        $galaxyname = GALAXY2_NAME;
        $bg = GALAXY2_BG;
        $mydb = $db2;
    break;
}


if($gkey != $key) {
    display_message($err_title,$locale['error_mismatched_code'],$bg);
    return 1;
}

$sql = 'SELECT user_active
        FROM user
        WHERE user_id = '.$user_id;


if(($user_data = $mydb->queryrow($sql)) === false) {
    display_message($err_title,$locale['error_mysql_select'],$bg);
    return 1;
}

if(empty($user_data['user_active'])) {
    display_message($err_title,$locale['error_account_missing'],$bg);
    return 1;
}

if($user_data['user_active'] != 2) {
    display_message($err_title,$locale['error_already_activated'],$bg);
    return 1;
}

$sql = 'UPDATE user
        SET user_active = 1, last_active='.time().'
        WHERE user_id = '.$user_id;

if(!$mydb->query($sql)) {
    display_message($err_title,$locale['error_mysql_update'],$bg);
    return 1;
}


mt_srand((double)microtime()*1000000);

$current_proverb = $proverbs[mt_rand(0, ($n_proverbs - 1))];

display_message($locale['activate_ok_title'],$locale['account_activated'].'<br><br><table width="100%" border="0" align="center"><tr><td width="10%">&nbsp;</td><td width="90%"><i>'.$current_proverb.'</i></td></tr></table>',$bg);

?>
