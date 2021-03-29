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

define ("GER_NEW_THREAD",
'Hallo member,

username hat einen neuen Thread mit dem Titel <a href="link"><b>subject</b></a> in der Allianz-Forum er&#353;ffnet.
Klicken Sie auf den Titel, um direkt dorthin fahren.


---
Dies ist eine automatische Nachricht, bitte nicht darauf antworten. Danke.');

define ("GER_NEW_POST",
'Hallo member,

username hat einen neuen Beitrag in dem Thread mit dem Titel <a href="link"><b>subject</b></a> in der Allianz-Forum gesendet.
Klicken Sie auf den Titel, um direkt dorthin fahren.


---
Dies ist eine automatische Nachricht, bitte nicht darauf antworten. Danke.');

define ("ITA_NEW_THREAD",
'Ciao member,

username ha aperto un nuovo topic nel forum alleanza con il titolo <a href="link"><b>subject</b></a>.
Clicca sul titolo per andare direttamente ad esso. 


---
Questo &egrave; un messaggio automatico, si prega di non rispondere. Grazie.');

define ("ITA_NEW_POST",
'Ciao member,

username ha scritto un nuovo post nel forum alleanza nel topic con il titolo <a href="link"><b>subject</b></a>.
Clicca sul titolo per andare direttamente ad esso. 


---
Questo &egrave; un messaggio automatico, si prega di non rispondere. Grazie.');

define ("ENG_NEW_THREAD",
'Hello member,

username has opened a new thread titled <a href="link"><b>subject</b></a> in the alliance forum.
Click on the title to go directly to it.


---
This is an automatic message, please do not reply to it. Thank you.');

define ("ENG_NEW_POST",
'Hello member,

username has sent a new post in the thread titled <a href="link"><b>subject</b></a> in the alliance forum.
Click on the title to go directly to it.


---
This is an automatic message, please do not reply to it. Thank you.');


function SendNotification($subject,$id,$isThread)
{
    global $db,$game;

    $sql = 'SELECT user_id,user_name,language
            FROM user
            WHERE user_alliance = '.$game->player['user_alliance'];

    if(!$q_members = $db->query($sql)) {
        message(DATABASE_ERROR, 'Could not query alliance user data');
    }

    // If it's a post don't care the post's title and use the thread's one
    if(!$isThread) {
        $sql = 'SELECT thread_title FROM alliance_bthreads
                WHERE thread_id = '.$id;

        if(($thread = $db->queryrow($sql)) === false) {
            message(DATABASE_ERROR, 'Could not query thread data');
        }

        // This's probably an extra check
        if(empty($thread['thread_title'])) {
            message(NOTICE, constant($game->sprache("TEXT9")));
        }

        $subject = $thread['thread_title'];
    }

    $patterns = array();
    $patterns[0] = '/member/';
    $patterns[1] = '/username/';
    $patterns[2] = '/link/';
    $patterns[3] = '/subject/';

    $replacements = array();
    $replacements[3] = 'tmp';
    $replacements[2] = 'tmp';
    $replacements[1] = parse_link('a=alliance_board&show_thread='.$id);
    $replacements[0] = $subject;

    $i = 0;
    $user_ids = array();

    while($member = $db->fetchrow($q_members)) {
        if($member['user_id'] == $game->player['user_id']) continue;

        // It's a new thread?
        if ($isThread) {
            switch($member['language']) {
                case 'GER':
                    $mail_subject = 'Neuer Thread im Forum Allianz';
                    $message_string = GER_NEW_THREAD;
                break;
                case 'ITA':
                    $mail_subject = 'Nuovo topic nel forum alleanza';
                    $message_string = ITA_NEW_THREAD;
                break;
                default:
                    $mail_subject = 'New thread in alliance forum';
                    $message_string = ENG_NEW_THREAD;
                break;
            }
        }
        else {
            switch($member['language']) {
                case 'GER':
                    $mail_subject = 'Neuer Beitrag im Bundnis-Forum';
                    $message_string = GER_NEW_POST;
                break;
                case 'ITA':
                    $mail_subject = 'Nuovo post nel forum alleanza';
                    $message_string = ITA_NEW_POST;
                break;
                default:
                    $mail_subject = 'New post in alliance forum';
                    $message_string = ENG_NEW_POST;
                break;
            }
        }

        $replacements[3] = $member['user_name'];
        $replacements[2] = $game->player['user_name'];
        $mail_text = preg_replace($patterns, $replacements, $message_string);

        $sql = 'INSERT INTO message (sender, receiver, subject, text, rread, time)
                VALUES ('.SUPPORTUSER.',
                        '.$member['user_id'].',
                        "'.$mail_subject.'",
                        "'.htmlspecialchars($mail_text).'", 0, '.time().')';

        if(!$db->query($sql)) {
            message(DATABASE_ERROR, 'Could not insert message data #'.$i);
        }

        ++$i;
        $user_ids[] = $member['user_id'];
    }

    // If there are more the one member in the alliance...
    // Yeah I could've checked this before, maybe in a future version
    if($i > 1) {
        $sql = 'UPDATE user
                SET unread_messages = unread_messages + 1
                WHERE user_id IN ('.implode(',', $user_ids).')';

        if(!$db->query($sql)) {
            message(DATABASE_ERROR, 'Could not update user unread messages');
        }
    }
}


$THREADS_PER_PAGE = 15;
$POSTS_PER_PAGE = 15;

$game->init_player();
$game->out('<span class="caption">'.constant($game->sprache("TEXT0")).':</span><br><br>');


// This Override is not 100% stable
if(!empty($_GET['override_aid'])) {
    if($game->player['user_auth_level'] != STGC_DEVELOPER) {
        message(GENERAL, 'Critical security breach');
    }

    $game->player['user_alliance'] = (int)$_GET['override_aid'];
    $game->player['alliance_name'] = 'Overriden';

    $override_str = '&override_aid='.(int)$_GET['override_aid'];
}
else {
    $override_str = '';
}

if(empty($game->player['alliance_name'])) {
    message(NOTICE, constant($game->sprache("TEXT1")));
}


if(!empty($_POST['new_thread_submit'])) {
    if(empty($_POST['post_title'])) {
        message(NOTICE, constant($game->sprache("TEXT2")));
    }

    $post_title = addslashes($_POST['post_title']);

    if(empty($_POST['post_text'])) {
        message(NOTICE, constant($game->sprache("TEXT3")));
    }

    $th_flags_high = 0;
    $th_flags_tactical = 0;
    $th_flags_diplomacy = 0;

    if(!empty($_POST['thflags1'])) $th_flags_high = 1;

    if(!empty($_POST['thflags2'])) $th_flags_tactical = 1;

    if(!empty($_POST['thflags3'])) $th_flags_diplomacy = 1;

    $post_text = str_replace("\n", '<br>', htmlspecialchars($_POST['post_text']));

    $sql = 'INSERT INTO alliance_bthreads (alliance_id, user_id, thread_replies, thread_title, thread_priority, thread_tactical, thread_diplomacy, thread_last_post_date)
            VALUES ('.$game->player['user_alliance'].', '.$game->player['user_id'].', 0, "'.$post_title.'",  '.$th_flags_high.', '.$th_flags_tactical.', '.$th_flags_diplomacy.', '.$game->TIME.')';

    if(!$db->query($sql)) {
        message(DATABASE_ERROR, 'Could not insert new thread data');
    }

    $new_thread_id = $db->insert_id();

    if(empty($new_thread_id)) {
        message(GENERAL, 'Could not get new thread id', '$new_thread_id == empty');
    }

    $sql = 'INSERT INTO alliance_bposts (alliance_id, thread_id, user_id, post_deleted, post_title, post_date, post_text)
            VALUES ('.$game->player['user_alliance'].', '.$new_thread_id.', '.$game->player['user_id'].', 0, "'.$post_title.'", '.$game->TIME.', "'.$post_text.'")';
            
    if(!$db->query($sql)) {
        message(DATABASE_ERROR, 'Could not insert new post data');
    }
    
    $new_post_id = $db->insert_id();
    
    $sql = 'UPDATE alliance_bthreads
            SET thread_first_post_id = '.$new_post_id.',
                thread_last_post_id = '.$new_post_id.'
            WHERE thread_id = '.$new_thread_id;
            
    if(!$db->query($sql)) {
        message(DATABASE_ERROR, 'Could not update thread first post data');
    }

    // 14/07/12 - AC: Send a notification of the new thread to all the
    //            others members of the alliance
    SendNotification($post_title,$new_thread_id,true);

    redirect('a=alliance_board&show_thread='.$new_thread_id.$override_str);
}
elseif(isset($_GET['new_thread'])) {
    $game->out('
<table class="style_outer" width="500" align="center" border="0" cellpadding="2" cellspacing="2">
  <tr>
    <td align="center">
      <span style="font-size: 12pt; font-weight: bold;">'.$game->player['alliance_name'].' ['.$game->player['alliance_tag'].']</span><br><br>
      <table width="480" align="center" border="0" cellpadding="2" cellspacing="2">
        <tr><td width="480" align="left"><a href="'.parse_link('a=alliance_board'.$override_str).'"><i>'.constant($game->sprache("TEXT4")).'</i></a> &raquo; <i>'.constant($game->sprache("TEXT5")).'</i></td></tr>
      </table>

      <table class="style_inner" width="480" align="center" border="0" cellpadding="2" cellspacing="2">
        <form method="post" action="'.parse_link('a=alliance_board'.$override_str).'">
        <tr height="5"><td></td></tr>
        <tr>
          <td rowspan="8" width="40">&nbsp;</td>
          <td width="100">'.constant($game->sprache("TEXT6")).'</td>
          <td width="340"><input class="field" type="text" name="post_title" size="30" maxlength="30" style="width: 320px;"></td>
        </tr>
        <tr>
          '.( ($game->player['user_alliance_status'] == 2 || $game->player['user_alliance_status'] == 3 || $game->player['user_alliance_status'] == 4) ? '<td width="100">'.constant($game->sprache("TEXT25")).' </td><td><input type="checkbox" name="thflags1" value="1"></td>' : '&nbsp').'
        </tr>
        <tr>
          '.( ($game->player['user_alliance_rights3'] == 1) ? '<td width="100">'.constant($game->sprache("TEXT26")).' </td><td><input type="checkbox" name="thflags2" value="1"></td>' : '&nbsp').'
        </tr>
        <tr>
          '.( ($game->player['user_alliance_rights5'] == 1) ? '<td width="100">'.constant($game->sprache("TEXT27")).' </td><td><input type="checkbox" name="thflags3" value="1"></td>' : '&nbsp').'
        </tr>
        <tr height="5"><td></td></tr>
        <tr>
          <td width="100" valign="top">'.constant($game->sprache("TEXT7")).'</td>
          <td width="340"><textarea name="post_text" cols="50" rows="10" style="width: 320px;"></textarea></td>
        </tr>
        <tr height="5"><td></td></tr>
        <tr>
          <td colspan="2" width="400" align="center"><input class="button" type="submit" name="new_thread_submit" value="'.constant($game->sprache("TEXT8")).'"></td>
        </tr>
        <tr height="5"><td></td></tr>
        </form>
      </table>
    </td>
  </tr>
</table>
    ');
}
elseif(!empty($_POST['new_post_submit'])) {
    $thread_id = (int)$_POST['thread_id'];
    
    $post_title = addslashes($_POST['post_title']);

    if(empty($_POST['post_text'])) {
        message(NOTICE, constant($game->sprache("TEXT3")));
    }
    
    $post_text = str_replace("\n", '<br>', htmlspecialchars($_POST['post_text']));
    
    $sql = 'INSERT INTO alliance_bposts (alliance_id, thread_id, user_id, post_deleted, post_title, post_date, post_text)
            VALUES ('.$game->player['user_alliance'].', '.$thread_id.', '.$game->player['user_id'].', 0, "'.$post_title.'", '.$game->TIME.', "'.$post_text.'")';

    if(!$db->query($sql)) {
        message(DATABASE_ERROR, 'Could not insert new post data');
    }

    $new_post_id = $db->insert_id();

    $sql = 'UPDATE alliance_bthreads
            SET thread_replies = thread_replies + 1,
                thread_last_post_id = '.$new_post_id.',
                thread_last_post_date = '.$game->TIME.'
            WHERE thread_id = '.$thread_id;

    if(!$db->query($sql)) {
        message(DATABASE_ERROR, 'Could not update thread data');
    }

    // 14/07/12 - AC: Send a notification of the new post to all the
    //            others members of the alliance
    SendNotification($post_title,$thread_id,false);

    redirect('a=alliance_board&show_thread='.$thread_id.$override_str);
}
elseif(!empty($_GET['new_post'])) {
    $thread_id = (int)$_GET['new_post'];

    $sql = 'SELECT thread_id, alliance_id, thread_title
            FROM alliance_bthreads
            WHERE thread_id = '.$thread_id;

    if(($thread = $db->queryrow($sql)) === false) {
        message(DATABASE_ERROR, 'Could not query thread data');
    }

    if(empty($thread['thread_id'])) {
        message(NOTICE, constant($game->sprache("TEXT9")));
    }

    if($thread['alliance_id'] != $game->player['user_alliance']) {
        message(NOTICE, constant($game->sprache("TEXT9")));
    }

    $game->out('
<table class="style_outer" width="500" align="center" border="0" cellpadding="2" cellspacing="2">
  <tr>
    <td align="center">
      <span style="font-size: 12pt; font-weight: bold;">'.$game->player['alliance_name'].' ['.$game->player['alliance_tag'].']</span><br><br>
      <table width="480" align="center" border="0" cellpadding="2" cellspacing="2">
        <tr><td width="480" align="left"><a href="'.parse_link('a=alliance_board'.$override_str).'"><i>'.constant($game->sprache("TEXT8")).'</i></a> &raquo; <a href="'.parse_link('a=alliance_board&show_thread='.$thread_id.$override_str).'"><i>'.htmlspecialchars(stripslashes($thread['thread_title'])).'</i></a> &raquo; <i>'.constant($game->sprache("TEXT10")).'</i></td></tr>
      </table>

      <table class="style_inner" width="480" align="center" border="0" cellpadding="2" cellspacing="2">
        <form method="post" action="'.parse_link('a=alliance_board'.$override_str).'">
        <tr height="5"><td></td></tr>
        <tr>
          <td rowspan="8" width="40">&nbsp;</td>
          <td width="100">'.constant($game->sprache("TEXT6")).'</td>
          <td width="340"><input class="field" type="text" name="post_title" size="30" maxlength="30" style="width: 320px;"></td>
        </tr>
        <tr height="5"><td></td></tr>
        <tr>
          <td width="100" valign="top">'.constant($game->sprache("TEXT7")).'</td>
          <td width="340"><textarea name="post_text" cols="50" rows="10" style="width: 320px;"></textarea></td>
        </tr>
        <tr height="5"><td></td></tr>
        <tr>
          <td colspan="2" width="400" align="center"><input class="button" type="submit" name="new_post_submit" value="'.constant($game->sprache("TEXT8")).'"></td>
        </tr>
        <tr height="5"><td></td></tr>
        <input type="hidden" name="thread_id" value="'.$thread_id.'">
        </form>
      </table>
    </td>
  </tr>
</table>
    ');
}
elseif(!empty($_POST['edit_post_submit'])) {
    if(empty($_POST['post_id'])) {
        message(NOTICE, constant($game->sprache("TEXT19")));
    }

    $post_id = (int)$_POST['post_id'];
    
    $post_title = addslashes($_POST['post_title']);

    if(empty($_POST['post_text'])) {
        message(NOTICE, constant($game->sprache("TEXT3")));
    }

    $post_text = str_replace("\n", '<br>', htmlspecialchars($_POST['post_text']));

    $sql = 'SELECT p.post_id, p.user_id, p.alliance_id, p.post_deleted,
                   t.thread_id, t.thread_first_post_id
            FROM (alliance_bposts p, alliance_bthreads t)
            WHERE p.post_id = '.$post_id.' AND
                  t.thread_id = p.thread_id';

    if(($post = $db->queryrow($sql)) === false) {
        message(DATABASE_ERROR, 'Could not query post/thread data');
    }

    if(empty($post['post_id'])) {
        message(NOTICE, constant($game->sprache("TEXT11")));
    }

    if($post['alliance_id'] != $game->player['user_alliance']) {
        message(NOTICE, constant($game->sprache("TEXT9")));
    }
    
    if($post['post_deleted'] != 0) {
        message(NOTICE, constant($game->sprache("TEXT9")));
    }

    if( ($game->player['user_alliance_status'] < ALLIANCE_STATUS_ADMIN) && ($post['user_id'] != $game->player['user_id']) ) {
        message(NOTICE, constant($game->sprache("TEXT12")));
    }
    
    $sql = 'UPDATE alliance_bposts
            SET post_title = "'.$post_title.'",
                post_text = "'.$post_text.'"
            WHERE post_id = '.$post_id;
            
    if(!$db->query($sql)) {
        message(DATABASE_ERROR, 'Could not update post data');
    }
    
    if($post['thread_first_post_id'] == $post_id) {
        $sql = 'UPDATE alliance_bthreads
                SET thread_title = "'.$post_title.'"
                WHERE thread_id = '.$post['thread_id'];
                
        if(!$db->query($sql)) {
            message(DATABASE_ERROR, 'Could not update thread title data');
        }
    }
    
    redirect('a=alliance_board&show_thread='.$post['thread_id'].$override_str);
}
elseif(!empty($_GET['edit_post'])) {
    $post_id = (int)$_GET['edit_post'];

    $sql = 'SELECT p.post_id, p.alliance_id, p.thread_id, p.user_id, p.post_title, p.post_text,
                   t.thread_title,
                   u.user_name
            FROM (alliance_bposts p, alliance_bthreads t)
            INNER JOIN (user u) ON u.user_id = p.user_id
            WHERE p.post_id = '.$post_id.' AND
                  t.thread_id = p.thread_id';
                  
    if(($post = $db->queryrow($sql)) === false) {
        message(DATABASE_ERROR, 'Could not query post data');
    }

    if(empty($post['post_id'])) {
        message(NOTICE, constant($game->sprache("TEXT11")));
    }

    if($post['alliance_id'] != $game->player['user_alliance']) {
        message(NOTICE, constant($game->sprache("TEXT9")));
    }

    $game->out('
<table class="style_outer" width="500" align="center" border="0" cellpadding="2" cellspacing="2">
  <tr>
    <td align="center">
      <span style="font-size: 12pt; font-weight: bold;">'.$game->player['alliance_name'].' ['.$game->player['alliance_tag'].']</span><br><br>
      <table width="480" align="center" border="0" cellpadding="2" cellspacing="2">
        <tr><td width="480" align="left"><a href="'.parse_link('a=alliance_board'.$override_str).'"><i>'.constant($game->sprache("TEXT4")).'</i></a> &raquo; <a href="'.parse_link('a=alliance_board&show_thread='.$post['thread_id'].$override_str).'"><i>'.htmlspecialchars(stripslashes($post['thread_title'])).'</i></a> &raquo; <i>'.constant($game->sprache("TEXT13")).'</i></td></tr>
      </table>

      <table class="style_inner" width="480" align="center" border="0" cellpadding="2" cellspacing="2">
        <form method="post" action="'.parse_link('a=alliance_board'.$override_str).'">
        <tr height="5"><td></td></tr>
        <tr>
          <td rowspan="8" width="40">&nbsp;</td>
          <td width="100">'.constant($game->sprache("TEXT14")).'</td>
          <td width="340"><a href="'.parse_link('a=stats&a2=viewplayer&id='.$post['user_id']).'"><b>'.$post['user_name'].'</b></a></td>
        </tr>
        <tr height="5"><td></td></tr>
        <tr>
          <td width="100">'.constant($game->sprache("TEXT6")).'</td>
          <td width="340"><input class="field" type="text" name="post_title" size="30" maxlength="30" value="'.$post['post_title'].'" style="width: 320px;"></td>
        </tr>
        <tr height="5"><td></td></tr>
        <tr>
          <td width="100" valign="top">'.constant($game->sprache("TEXT7")).'</td>
          <td width="340"><textarea name="post_text" cols="50" rows="10" style="width: 320px;">'.str_replace('<br>', "\n", $post['post_text']).'</textarea></td>
        </tr>
        <tr height="5"><td></td></tr>
        <tr>
          <td colspan="2" width="400" align="center"><input class="button" type="submit" name="edit_post_submit" value="'.constant($game->sprache("TEXT10")).'"></td>
        </tr>
        <tr height="5"><td></td></tr>
        <input type="hidden" name="post_id" value="'.$post_id.'">
        </form>
      </table>
    </td>
  </tr>
</table>
    ');
}
elseif(!empty($_POST['delete_post_confirm'])) {
    if(empty($_POST['post_id'])) {
        message(NOTICE, constant($game->sprache("TEXT19")));
    }

    $post_id = (int)$_POST['post_id'];

    $sql = 'SELECT p.post_id, p.user_id, p.alliance_id, p.post_deleted,
                   t.thread_id, t.thread_first_post_id, t.thread_last_post_id
            FROM (alliance_bposts p, alliance_bthreads t)
            WHERE p.post_id = '.$post_id.' AND
                  t.thread_id = p.thread_id';

    if(($post = $db->queryrow($sql)) === false) {
        message(DATABASE_ERROR, 'Could not query post/thread data');
    }

    if(empty($post['post_id'])) {
        message(NOTICE, constant($game->sprache("TEXT11")));
    }

    if($post['alliance_id'] != $game->player['user_alliance']) {
        message(NOTICE, constant($game->sprache("TEXT9")));
    }

    if($post['post_deleted'] != 0) {
        message(NOTICE, constant($game->sprache("TEXT9")));
    }

    $first_post = ($post['thread_first_post_id'] == $post_id) ? true : false;

    // This can be sure summarized yet,
    // But tonight I have no idea how
    // I should do it ...
    $allowed = false;

    if($game->player['user_alliance_status'] >= ALLIANCE_STATUS_ADMIN) {
        $allowed = true;
    }
    elseif( ($post['user_id'] == $game->player['user_id']) && (!$first_post) ) {
        $allowed = true;
    }

    if(!$allowed) {
        message(NOTICE, constant($game->sprache("TEXT12")));
    }

    if($first_post) {
        $sql = 'UPDATE alliance_bposts
                SET post_deleted = 1
                WHERE thread_id = '.$post['thread_id'];

        if(!$db->query($sql)) {
            message(DATABASE_ERROR, 'Could not update post delete data');
        }
        
        $sql = 'DELETE FROM alliance_bthreads
                WHERE thread_id = '.$post['thread_id'];

        if(!$db->query($sql)) {
            message(DATABASE_ERROR, 'Could not delete thread data');
        }

        redirect('a=alliance_board'.$override_str);
    }
    else {
        $sql = 'UPDATE alliance_bposts
                SET post_deleted = 1
                WHERE post_id = '.$post_id;

        if(!$db->query($sql)) {
            message(DATABASE_ERROR, 'Could not update post delete data');
        }

        $last_post = ($post['thread_last_post_id'] == $post_id) ? true : false;

        if($last_post) {
            $sql = 'SELECT post_id, post_date
                    FROM alliance_bposts
                    WHERE thread_id = '.$post['thread_id'].' AND
                          post_deleted = 0
                    ORDER BY post_date DESC
                    LIMIT 1';

            if(($new_last_post = $db->queryrow($sql)) === false) {
                message(DATABASE_ERROR, 'Could not query new last post data');
            }

            $sql = 'UPDATE alliance_bthreads
                    SET thread_replies = thread_replies - 1,
                        thread_last_post_id = '.$new_last_post['post_id'].',
                        thread_last_post_date = '.$new_last_post['post_date'].'
                    WHERE thread_id = '.$post['thread_id'];

            if(!$db->query($sql)) {
                message(DATABASE_ERROR, 'Could not update thread data');
            }
        }
        else {
            $sql = 'UPDATE alliance_bthreads
                    SET thread_replies = thread_replies - 1
                    WHERE thread_id = '.$post['thread_id'];

            if(!$db->query($sql)) {
                message(DATABASE_ERROR, 'Could not update thread replies count data');
            }
        }

        redirect('a=alliance_board&show_thread='.$post['thread_id'].$override_str);
    }


}
elseif(!empty($_GET['delete_post'])) {
    $game->out('
<table class="style_inner" width="300" align="center" border="0" cellpadding="2" cellspacing="2">
  <form method="post" action="'.parse_link('a=alliance_board'.$override_str).'">
  <tr height="5"><td></td></tr>
  <tr>
    <td align="center">
      '.constant($game->sprache("TEXT15")).'<br><br>(<i>'.constant($game->sprache("TEXT16")).'</i>)<br><br>
      <input class="button" type="button" value="'.constant($game->sprache("TEXT17")).'" onClick="return window.history.back();">&nbsp;&nbsp;
      <input class="button" type="submit" name="delete_post_confirm" value="'.constant($game->sprache("TEXT18")).'">
    </td>
  </tr>
  <tr height="5"><td></td></tr>
  <input type="hidden" name="post_id" value="'.(int)$_GET['delete_post'].'">
  </form>
</table>
    ');
}
elseif(!empty($_GET['show_thread'])) {
    $thread_id = (int)$_GET['show_thread'];

    $start = (!empty($_GET['start'])) ? $_GET['start'] : 0;

    $sql = 'SELECT *
            FROM alliance_bthreads
            WHERE thread_id = '.$thread_id;

    if(($thread = $db->queryrow($sql)) === false) {
        message(DATABASE_ERROR, 'Could not query thread data');
    }

    if(empty($thread['thread_id'])) {
        message(NOTICE, constant($game->sprache("TEXT9")));
    }

    if($thread['alliance_id'] != $game->player['user_alliance']) {
        message(NOTICE, constant($game->sprache("TEXT9")));
    }

    $n_posts = $thread['thread_replies'] + 1;

    $sql = 'SELECT p.*,
                   u.user_id, u.user_name, u.user_avatar, u.user_alliance_status, u.user_alliance
            FROM (alliance_bposts p)
            LEFT JOIN (user u) ON u.user_id = p.user_id
            WHERE p.thread_id = '.$thread_id.' AND
                  p.post_deleted = 0
            LIMIT '.$start.', '.$POSTS_PER_PAGE;

    if(!$q_posts = $db->query($sql)) {
        message(DATABASE_ERROR, 'Could not query posts data');
    }

    $n_pages = ceil($n_posts / $POSTS_PER_PAGE);
    $current_page = ($start > 0) ? ( ($start / $POSTS_PER_PAGE) + 1) : 1;
    $next_start = 0;

    $game->out('
<table class="style_outer" width="500" align="center" border="0" cellpadding="2" cellspacing="2">
  <tr>
    <td align="center">
      <span style="font-size: 12pt; font-weight: bold;">'.$game->player['alliance_name'].' ['.$game->player['alliance_tag'].']</span><br><br>
      <table width="480" align="center" border="0" cellpadding="2" cellspacing="2">
        <tr><td width="480" align="left"><a href="'.parse_link('a=alliance_board'.$override_str).'"><i>'.constant($game->sprache("TEXT4")).'</i></a> &raquo; <i>'.htmlspecialchars(stripslashes($thread['thread_title'])).'</i></td></tr>
      </table>
    ');
    
    $first_post = 1;;
    
    $is_mod = ($game->player['user_alliance_status'] >= ALLIANCE_STATUS_ADMIN) ? true : false;
    
    include_once('include/libs/images.php');

    while($post = $db->fetchrow($q_posts)) {
        if($first_post == 0) $game->out('<br>');
        else $first_post = 0;

        if(empty($post['user_id'])) {
            $post['user_id'] = 0;
            $post['user_name'] = constant($game->sprache("TEXT20"));
            $post['user_avatar'] = '';
        }
        else {
            $post['user_name'] = '<b>'.$post['user_name'].'</b>';
        }

        $own_post = ($post['user_id'] == $game->player['user_id']) ? true : false;

        $game->out('
      <table class="style_inner" width="480" align="center" border="0" cellpadding="2" cellspacing="2">
        <tr>
          <tr>
            <td width="100"><a href="'.( ($post['user_id'] != 0) ? parse_link('a=stats&a2=viewplayer&id='.$post['user_id']) : 'javascript:void(0)' ).'" name="'.$post['post_id'].'">'.$post['user_name'].'</a></td>
            <td width="335"><i>'.date('d.m.y H:i:s', $post['post_date']).'</i>'.( (!empty($post['post_title'])) ? ' - <b>'.htmlspecialchars(stripslashes($post['post_title'])).'</b>' : '' ).'</td>
            <td width="45" align="right">'.( ( ($is_mod) || ($own_post && ($post['post_id'] != $thread['thread_first_post_id'])) ) ? '[<a href="'.parse_link('a=alliance_board&delete_post='.$post['post_id'].$override_str).'">X</a>]' : '' ).'&nbsp;'.( ($is_mod || $own_post) ? '[<a href="'.parse_link('a=alliance_board&edit_post='.$post['post_id'].$override_str).'">E</a>]' : '' ).'</td>
          </tr>
          <tr height="1"><td colspan="3"></td></tr>
          <tr>
            <td width="70" valign="top">');

        if (!empty($post['user_avatar']))
        {
            $info = scale_image($post['user_avatar'],100*0.6,166*0.6);
            if ($info[0]>0 && $info[1]>0)
                $game->out('<img src="'.$post['user_avatar'].'" width="'.$info[0].'" height="'.$info[1].'">');
            else $game->out('&nbsp;');
        }
        else $game->out('&nbsp;');


        // 24/10/08 - AC: Ok, check status but also check if he's still in the alliance
        if($post['user_alliance'] == $post['alliance_id']) {
            if($post['user_alliance_status'] == 2)
                $game->out('<br><font color="yellow">'.constant($game->sprache("TEXT28")).'</font>');
            elseif ($post['user_alliance_status'] == 3)
                $game->out('<br><font color="red">'.constant($game->sprache("TEXT29")).'</font>');
            elseif ($post['user_alliance_status'] == 4)
                $game->out('<br><font color="#FFA500">'.constant($game->sprache("TEXT30")).'</font>');
        }
        else
            $game->out('<br><font color="#AAAAAA"><u>'.constant($game->sprache("TEXT31")).'</u></font>');

        $game->out('</td>
            <td width="410" valign="top" colspan="2">'.$post['post_text'].'</td>
        </tr>
      </table>

        ');
    }


    $left_html = $center_html = $right_html = '';

    if($current_page > 1) {
        $left_html =  '[<a href="'.parse_link('a=alliance_board&show_thread='.$thread_id.'&start=0'.$override_str).'">&lt;&lt;</a>]&nbsp;'.
                      '[<a href="'.parse_link('a=alliance_board&show_thread='.$thread_id.'&start='.($start - $POSTS_PER_PAGE).$override_str).'">&lt;</a>]';
    }

    if($n_pages == 1) {
        $center_html = '<select style="width: 50px;" disabled="disabled"><option value="0">1</option></select>&nbsp;<input type="submit" name="submit" class="button" style="width: 30px;" value="OK" disabled="disabled">';
    }
    else {
        $center_html = '<select style="width: 50px;" name="start">';

        for($i = 1; $i <= $n_pages; ++$i) {
            $center_html .= '<option value="'.$next_start.'"'.( ($i == $current_page) ? ' selected="selected"' : '' ).'>'.$i.'</a>';
            $next_start = ($next_start + $POSTS_PER_PAGE);
        }

        $center_html .= '</select>&nbsp;<input type="submit" class="button" style="width: 30px;" value="OK">';
    }

    if($current_page < $n_pages) {
        $right_html = '[<a href="'.parse_link('a=alliance_board&show_thread='.$thread_id.'&start='.($start + $POSTS_PER_PAGE).$override_str).'">&gt;</a>]&nbsp;'.
                      '[<a href="'.parse_link('a=alliance_board&show_thread='.$thread_id.'&start='.(($n_pages - 1) * $POSTS_PER_PAGE).$override_str).'">&gt;&gt;</a>]';
    }

    $game->out('
      <table width="480" align="center" border="0" cellpadding="2" cellspacing="2">
        <tr><td width="480" align="right">
          [<a href="'.parse_link('a=alliance_board&new_thread'.$override_str).'">'.constant($game->sprache("TEXT21")).'</a>]&nbsp;
          [<a href="'.parse_link('a=alliance_board&new_post='.$thread_id.$override_str).'">'.constant($game->sprache("TEXT10")).'</a>]
        </td></tr>
      </table>
      <table width="380" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr height="15"><td colspan="3"></td></tr>
        <form method="get" action="">
        <input type="hidden" name="a" value="alliance_board">
        <input type="hidden" name="show_thread" value="'.$thread_id.'">
        <tr>
          <td width="140" align="center">'.$left_html.'</td>
          <td width="100" align="center">'.$center_html.'</td>
          <td width="140" align="center">'.$right_html.'</td>
        </tr>
        </form>
        <tr height="5"><td colspan="3"></td></tr>
      </table>
    </td>
  </tr>
</table>
    ');
}
else {
    $start = (!empty($_GET['start'])) ? $_GET['start'] : 0;

    $sql = 'SELECT COUNT(thread_id) AS num
            FROM alliance_bthreads
            WHERE alliance_id = '.$game->player['user_alliance'];

    if(($tcount = $db->queryrow($sql)) === false) {
        message(DATABASE_ERROR, 'Could not query thread count data');
    }

    $n_threads = $tcount['num'];

    if($n_threads == 0) {
        $n_pages = $current_page = 1;
        $next_start = 0;
    }
    else {
        $sql = 'SELECT t.*,
                       u.user_id, u.user_name
                FROM (alliance_bthreads t)
                LEFT JOIN (user u) ON u.user_id = t.user_id
                WHERE t.alliance_id = '.$game->player['user_alliance'].' AND
                      t.thread_tactical IN ('.(($game->player['user_alliance_rights3'] == 1) ? '0, 1' : '0').') AND
                      t.thread_diplomacy IN ('.(($game->player['user_alliance_rights5'] == 1) ? '0, 1' : '0').')
                ORDER BY thread_priority DESC, thread_last_post_date DESC
                LIMIT '.$start.', '.$THREADS_PER_PAGE;

        if(!$q_threads = $db->query($sql)) {
            message(DATABASE_ERROR, 'Could not query thread data');
        }

        $n_pages = ceil($n_threads / $THREADS_PER_PAGE);
        $current_page = ($start > 0) ? ( ($start / $THREADS_PER_PAGE) + 1) : 1;
        $next_start = 0;
    }

    $game->out('
<table class="style_outer" width="500" align="center" border="0" cellpadding="2" cellspacing="2">
  <tr>
    <td align="center">
      <span style="font-size: 12pt; font-weight: bold;">'.$game->player['alliance_name'].' ['.$game->player['alliance_tag'].']</span><br><br>
      <table width="480" align="center" border="0" cellpadding="2" cellspacing="2">
        <tr><td width="480" align="left"><i>'.constant($game->sprache("TEXT4")).'</i></td></tr>
      </table>
      <table class="style_inner" width="480" align="center" border="0" cellpadding="2" cellspacing="2">
        <tr>
          <td width="235">'.constant($game->sprache("TEXT22")).'</td>
          <td width="100">'.constant($game->sprache("TEXT23")).'</td>
          <td width="25" align="center"><b>#</b></td>
          <td width="120" align="center">'.constant($game->sprache("TEXT24")).'</td>
        </tr>
    ');
    
    if($n_threads > 0) {
        while($thread = $db->fetchrow($q_threads)) {
            if ($thread['thread_tactical'] == 1 && $game->player['user_alliance_rights3'] != 1) continue;

            if ($thread['thread_diplomacy'] == 1 && $game->player['user_alliance_rights5'] != 1) continue;

            if(empty($thread['user_id'])) {
                $thread['user_id'] = 0;
                $thread['user_name'] = constant($game->sprache("TEXT20"));
            }

            $thr_n_pages = ceil($thread['thread_replies'] / $POSTS_PER_PAGE);

            if($thr_n_pages == 0) $start_str = '';
            else $start_str = '&start='.( ($thr_n_pages - 1) * $POSTS_PER_PAGE);

            $threadtitle = '';

            if ($thread['thread_diplomacy'] == 1) $threadtitle = '<font color="#FFA500"><b>.D. </b></font>';
            if ($thread['thread_tactical'] == 1) $threadtitle .= '<font color="yellow"><b>.T. </b></font>';

            if($thread['thread_priority'] == 1) {
                $threadtitle .= '<b><font color="yellow">'.htmlspecialchars(stripslashes($thread['thread_title'])).'</font></b>';
            }
            else
            {
                $threadtitle .= htmlspecialchars(stripslashes($thread['thread_title']));
            }

            $game->out('
        <tr>
          <td width="235"><a href="'.parse_link('a=alliance_board&show_thread='.$thread['thread_id'].$override_str).'">'.$threadtitle.'</a></td>
          <td width="100"><a href="'.( ($thread['user_id'] != 0) ? parse_link('a=stats&a2=viewplayer&id='.$thread['user_id']) : 'javascript:void(0)' ).'">'.$thread['user_name'].'</a></td>
          <td width="25" align="center">'.$thread['thread_replies'].'</td>
          <td width="120" align="center">'.date('d.m.y H:i', $thread['thread_last_post_date']).'&nbsp;<a href="'.parse_link('a=alliance_board&show_thread='.$thread['thread_id'].$start_str.$override_str.'#'.$thread['thread_last_post_id']).'">&gt;</a></td>
        </tr>
            ');
        }
    }

    $game->out('
      </table>
      <table width="480" align="center" border="0" cellpadding="2" cellspacing="2">
        <tr><td width="480" align="right">[<a href="'.parse_link('a=alliance_board&new_thread'.$override_str).'">'.constant($game->sprache("TEXT21")).'</a>]</td></tr>
      </table>

    ');
    
    $left_html = $center_html = $right_html = '';

    if($current_page > 1) {
        $left_html =  '[<a href="'.parse_link('a=alliance_board&start=0'.$override_str).'">&lt;&lt;</a>]&nbsp;'.
                      '[<a href="'.parse_link('a=alliance_board&start='.($start - $THREADS_PER_PAGE).$override_str).'">&lt;</a>]';
    }

    if($n_pages == 1) {
        $center_html = '<select style="width: 50px;" disabled="disabled"><option value="0">1</option></select>&nbsp;<input type="submit" name="submit" class="button" style="width: 30px;" value="OK" disabled="disabled">';
    }
    else {
        $center_html = '<select style="width: 50px;" name="start">';

        for($i = 1; $i <= $n_pages; ++$i) {
            $center_html .= '<option value="'.$next_start.'"'.( ($i == $current_page) ? ' selected="selected"' : '' ).'>'.$i.'</a>';
            $next_start = ($next_start + $THREADS_PER_PAGE);
        }

        $center_html .= '</select>&nbsp;<input type="submit" class="button" style="width: 30px;" value="OK">';
    }

    if($current_page < $n_pages) {
        $right_html = '[<a href="'.parse_link('a=alliance_board&start='.($start + $THREADS_PER_PAGE).$override_str).'">&gt;</a>]&nbsp;'.
                      '[<a href="'.parse_link('a=alliance_board&start='.(($n_pages - 1) * $THREADS_PER_PAGE).$override_str).'">&gt;&gt;</a>]';
    }
    
    $game->out('
      <table width="380" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr height="15"><td colspan="3"></td></tr>
        <form method="get" action="">
        <input type="hidden" name="a" value="alliance_board">
        <tr>
          <td width="140" align="center">'.$left_html.'</td>
          <td width="100" align="center">'.$center_html.'</td>
          <td width="140" align="center">'.$right_html.'</td>
        </tr>
        </form>
        <tr height="5"><td colspan="3"></td></tr>
      </table>
    </td>
  </tr>
</table>
    ');
}

?>
