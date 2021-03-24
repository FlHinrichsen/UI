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



function ZeitDetailShort($seconds) {
    $minutes=0;

    while($seconds >= 60) {
        $minutes++;
        $seconds -= 60;
    }

    return round($minutes, 0).'m '.round($seconds, 0).'s';
}





$game->init_player();

$game->out('<span class="caption">'.$BUILDING_NAME[$game->player['user_race']][6].':</span><br><br>');


// Check if spacedock is available
if($game->planet['building_7'] < 1) {
    message(NOTICE, constant($game->sprache("TEXT27")).' '.$BUILDING_NAME[$game->player['user_race']][6].' '.constant($game->sprache("TEXT28")));
}


// #############################################################################
// Execute ships repairing
// #############################################################################
if(!empty($_POST['repair_ships_start'])) {

    // #############################################################################
    // $_POST['ships'] parser
    $ship_ids = array();

    for($i = 0; $i < count($_POST['ships']); ++$i) {
        $_temp = (int)$_POST['ships'][$i];

        if(!empty($_temp)) {
            $ship_ids[] = $_temp;
        }
    }

    // Check if the ships are presents
    if(empty($ship_ids)) {
        message(NOTICE, constant($game->sprache("TEXT29")));
    }

    $changed_ships = count($ship_ids);


    // #############################################################################
    // Ship list creation:

    $sql = 'SELECT s.*,t.value_5,t.name,t.buildtime,t.resource_1,t.resource_2,t.resource_3
            FROM (ships s) LEFT JOIN (ship_templates t) ON s.template_id=t.id
            WHERE s.ship_id IN ('.implode(',', $ship_ids).') AND s.ship_untouchable=0 AND s.hitpoints<t.value_5';

    if(($repairable_ships = $db->queryrowset($sql)) === false || count($repairable_ships)<1) {
        message(NOTICE, constant($game->sprache("TEXT30")));
    }


    // New: Table locking
    $db->lock('ships');
    //$game->init_player();  // ?????? AGAIN ??????

    foreach($repairable_ships as $id => $ship) {
        $costs[0]=round(0.6*($ship['resource_1']/$ship['value_5']*($ship['value_5']-$ship['hitpoints'])),0);
        $costs[1]=round($ship['resource_2']/$ship['value_5']*($ship['value_5']-$ship['hitpoints']),0);
        $costs[2]=round($ship['resource_3']/$ship['value_5']*($ship['value_5']-$ship['hitpoints']),0);

        if ($game->planet['resource_1']>=$costs[0] && $game->planet['resource_2']>=$costs[1] && $game->planet['resource_3']>=$costs[2])
        {
            $game->planet['resource_1']-=$costs[0];
            $game->planet['resource_2']-=$costs[1];
            $game->planet['resource_3']-=$costs[2];

            $sql = 'UPDATE ships
                    SET ship_untouchable=1, ship_repair='.($ACTUAL_TICK+ceil($ship['buildtime']*0.6/$ship['value_5']*($ship['value_5']-$ship['hitpoints']))).'
                    WHERE ship_id='.$ship['ship_id'];
            if(!$db->query($sql)) {
                message(DATABASE_ERROR, 'Could not update ship data');
            }
        }
    }


    $sql = 'UPDATE planets
            SET resource_1='.$game->planet['resource_1'].', resource_2='.$game->planet['resource_2'].', resource_3='.$game->planet['resource_3'].'
            WHERE planet_id='.$game->planet['planet_id'];
    if(!$db->query($sql)) {
        message(DATABASE_ERROR, 'Critical: Could not update planets data');
    }

    $db->unlock();

    redirect('a=spacedock');
}
// #############################################################################
// Display selected ships for repairing
// #############################################################################
elseif(!empty($_POST['repair_ships'])) {
    $game->out('<script language="JavaScript">
var costs = new Array(0,0,0,0,0,0,0,0,0);

function Change(val1,val2,val3,val4,val5,val6,val7,val8,val9,add)
{
  if (add==0)
    Sub(val1,val2,val3,val4,val5,val6,val7,val8,val9);
  else
    Add(val1,val2,val3,val4,val5,val6,val7,val8,val9);
}


function Add(val1,val2,val3,val4,val5,val6,val7,val8,val9)
{
  costs[0]+=val1;
  costs[1]+=val2;
  costs[2]+=val3;
  costs[3]+=val4;
  costs[4]+=val5;
  costs[5]+=val6;
  costs[6]+=val7;
  costs[7]+=val8;
  costs[8]+=val9;

  document.getElementById( "costs0" ).firstChild.nodeValue = costs[0];
  document.getElementById( "costs1" ).firstChild.nodeValue = costs[1];
  document.getElementById( "costs2" ).firstChild.nodeValue = costs[2];
  /*document.getElementById( "costs3" ).firstChild.nodeValue = costs[3];
  document.getElementById( "costs4" ).firstChild.nodeValue = costs[4];
  document.getElementById( "costs5" ).firstChild.nodeValue = costs[5];
  document.getElementById( "costs6" ).firstChild.nodeValue = costs[6];
  document.getElementById( "costs7" ).firstChild.nodeValue = costs[7];
  document.getElementById( "costs8" ).firstChild.nodeValue = costs[8];*/
}


function Sub(val1,val2,val3,val4,val5,val6,val7,val8,val9)
{
  costs[0]-=val1;
  costs[1]-=val2;
  costs[2]-=val3;
  costs[3]-=val4;
  costs[4]-=val5;
  costs[5]-=val6;
  costs[6]-=val7;
  costs[7]-=val8;
  costs[8]-=val9;

  document.getElementById( "costs0" ).firstChild.nodeValue = costs[0];
  document.getElementById( "costs1" ).firstChild.nodeValue = costs[1];
  document.getElementById( "costs2" ).firstChild.nodeValue = costs[2];
  /*document.getElementById( "costs3" ).firstChild.nodeValue = costs[3];
  document.getElementById( "costs4" ).firstChild.nodeValue = costs[4];
  document.getElementById( "costs5" ).firstChild.nodeValue = costs[5];
  document.getElementById( "costs6" ).firstChild.nodeValue = costs[6];
  document.getElementById( "costs7" ).firstChild.nodeValue = costs[7];
  document.getElementById( "costs8" ).firstChild.nodeValue = costs[8];*/
}
</script>');


    // #############################################################################
    // $_POST['ships'] parser
    $ship_ids = array();

    for($i = 0; $i < count($_POST['ships']); ++$i) {
        $_temp = (int)$_POST['ships'][$i];

        if(!empty($_temp)) {
            $ship_ids[] = $_temp;
        }
    }

    // Check if the ships are presents
    if(empty($ship_ids)) {
        message(NOTICE, constant($game->sprache("TEXT39")));
    }

    $changed_ships = count($ship_ids);


    // #############################################################################
    // Ship list creation:

    $sql = 'SELECT s.*,t.value_5,t.name,t.buildtime,t.resource_1,t.resource_2,t.resource_3
            FROM (ships s) LEFT JOIN (ship_templates t) ON s.template_id=t.id
            WHERE s.ship_id IN ('.implode(',', $ship_ids).') AND s.ship_untouchable=0 AND s.hitpoints<t.value_5';

    if(($repairable_ships = $db->queryrowset($sql)) === false || count($repairable_ships)<1) {
        message(NOTICE, constant($game->sprache("TEXT30")));
    }

    $game->out('
<table width="90%" align="center" border="0" cellpadding="2" cellspacing="2" class="style_outer">
  <tr>
    <td>
      <table width="100%" align="center" border="0" cellpadding="2" cellspacing="2" class="style_inner">
      <form name="mfleet_repair_form" method="post" action="'.parse_link('a=spacedock').'">
        <tr>
          <td>'.constant($game->sprache("TEXT31")).'</td>
        </tr>
        <tr>
          <td align="center">
            <table width="92%" align="center" border="0" cellpadding="1" cellspacing="1">
              <tr>
                <td width="27%"><b>'.constant($game->sprache("TEXT32")).'</b></td>
                <td width="20%"><b>'.constant($game->sprache("TEXT33")).'</b></td>
                <td width="39%"><b>'.constant($game->sprache("TEXT34")).'</b></td>
                <td width="14%"><b>'.constant($game->sprache("TEXT35")).'</b></td>
              </tr>');

    foreach($repairable_ships as $id => $ship) {
        $costs='';
        $costs.='<img src="'.$game->GFX_PATH.'menu_metal_small.gif">&nbsp;'.round(0.6*($ship['resource_1']/$ship['value_5']*($ship['value_5']-$ship['hitpoints'])),0).'&nbsp;&nbsp;';
        $costs.='<img src="'.$game->GFX_PATH.'menu_mineral_small.gif">&nbsp;'.round($ship['resource_2']/$ship['value_5']*($ship['value_5']-$ship['hitpoints']),0).'&nbsp;&nbsp;';
        $costs.='<img src="'.$game->GFX_PATH.'menu_latinum_small.gif">&nbsp;'.round($ship['resource_3']/$ship['value_5']*($ship['value_5']-$ship['hitpoints']),0).'';

        $tcost[0]+=round(0.6*($ship['resource_1']/$ship['value_5']*($ship['value_5']-$ship['hitpoints'])),0);
        $tcost[1]+=round($ship['resource_2']/$ship['value_5']*($ship['value_5']-$ship['hitpoints']),0);
        $tcost[2]+=round($ship['resource_3']/$ship['value_5']*($ship['value_5']-$ship['hitpoints']),0);

        /* 04/06/08 - AC: Use real ship name if it exist */
        $ship_name = empty($ship['ship_name'])? $ship['name'] : $ship['ship_name'];
        $game->out('
              <tr>
                <td width="27%"><b><input type="checkbox" name="ships[]" value="'.$ship['ship_id'].'" checked="checked" onClick ="return Change('.round(0.6*($ship['resource_1']/$ship['value_5']*($ship['value_5']-$ship['hitpoints'])),0).','.round($ship['resource_2']/$ship['value_5']*($ship['value_5']-$ship['hitpoints']),0).','.round($ship['resource_3']/$ship['value_5']*($ship['value_5']-$ship['hitpoints']),0).',0,0,0,0,0,0,this.checked);">&nbsp;'.$ship_name.'</b></td>
                <td width="20%"><b>'.round(100/$ship['value_5']*$ship['hitpoints'],0).'% ('.$ship['hitpoints'].'/'.$ship['value_5'].')</b></td>
                <td width="39%"><b>'.$costs.'</b></td>
                <td width="14%"><b>'.ceil($ship['buildtime']*0.6/$ship['value_5']*($ship['value_5']-$ship['hitpoints'])*5).' '.constant($game->sprache("TEXT36")).'</b></td>
              </tr>');
    }

    $game->out('
            </table>
            <br>
            <table width="92%" align="center" border="0" cellpadding="1" cellspacing="1">
              <tr>
                <td width="27%"><u><b>'.constant($game->sprache("TEXT37")).'</b></u></td>
                <td width="73%">
                  <img src="'.$game->GFX_PATH.'menu_metal_small.gif">&nbsp;<b id="costs0">'.$tcost[0].'</b>&nbsp;&nbsp;
                  <img src="'.$game->GFX_PATH.'menu_mineral_small.gif">&nbsp;<b id="costs1">'.$tcost[1].'</b>&nbsp;&nbsp;
                  <img src="'.$game->GFX_PATH.'menu_latinum_small.gif">&nbsp;<b id="costs2">'.$tcost[2].'</b>&nbsp;&nbsp;
                  <script language="JavaScript">
                    costs[0]='.$tcost[0].';
                    costs[1]='.$tcost[1].';
                    costs[2]='.$tcost[2].';
                  </script>
                </td>
              </tr>
            </table>
            <br>
            <input class="button" type="submit" name="repair_ships_start" value="'.constant($game->sprache("TEXT38")).'">
          </td>
        </tr>
      </form>
      </table>
    </td>
  </tr>
</table>');
}
// #############################################################################
// Execute ships crew un/loading
// #############################################################################
elseif(!empty($_POST['man_ships_start'])) {

    // #############################################################################
    // $_POST['ships'] parser
    $ship_ids = array();

    for($i = 0; $i < count($_POST['ships']); ++$i) {
        $_temp = (int)$_POST['ships'][$i];

        if(!empty($_temp)) {
            $ship_ids[] = $_temp;
        }
    }

    // Check if the ships are presents
    if(empty($ship_ids)) {
        message(NOTICE, constant($game->sprache("TEXT39")));
    }

    $changed_ships = count($ship_ids);


    // #############################################################################
    // Ship list creation:

    $sql = 'SELECT s.*,t.value_5,t.name,t.max_unit_1,t.max_unit_2,t.max_unit_3,t.max_unit_4,t.min_unit_1,t.min_unit_2,t.min_unit_3,t.min_unit_4
            FROM (ships s) LEFT JOIN (ship_templates t) ON s.template_id=t.id
            WHERE s.ship_id IN ('.implode(',', $ship_ids).') AND s.ship_untouchable=0';

    if(($man_ships = $db->queryrowset($sql)) === false || count($man_ships)<1) {
        message(NOTICE, constant($game->sprache("TEXT30")));
    }


    // New: Table locking
    $db->lock('ships');
    //$game->init_player(); // ?????? ARE YOU SURE? AGAIN? ??????

    $unit[0]=$game->planet['unit_1'];
    $unit[1]=$game->planet['unit_2'];
    $unit[2]=$game->planet['unit_3'];
    $unit[3]=$game->planet['unit_4'];


    foreach($man_ships as $id => $ship) {
        $units='';

        $_POST['ship_'.$ship['ship_id'].'_unit_1']=(int)$_POST['ship_'.$ship['ship_id'].'_unit_1'];
        $_POST['ship_'.$ship['ship_id'].'_unit_2']=(int)$_POST['ship_'.$ship['ship_id'].'_unit_2'];
        $_POST['ship_'.$ship['ship_id'].'_unit_3']=(int)$_POST['ship_'.$ship['ship_id'].'_unit_3'];
        $_POST['ship_'.$ship['ship_id'].'_unit_4']=(int)$_POST['ship_'.$ship['ship_id'].'_unit_4'];

        if ($_POST['ship_'.$ship['ship_id'].'_unit_1']>=$ship['min_unit_1'] && $_POST['ship_'.$ship['ship_id'].'_unit_1']<=$ship['max_unit_1'] && ($unit[0]>=($_POST['ship_'.$ship['ship_id'].'_unit_1']-$ship['unit_1']) || $ship['unit_1']>$_POST['ship_'.$ship['ship_id'].'_unit_1']) )
        if ($_POST['ship_'.$ship['ship_id'].'_unit_2']>=$ship['min_unit_2'] && $_POST['ship_'.$ship['ship_id'].'_unit_2']<=$ship['max_unit_2'] && ($unit[1]>=($_POST['ship_'.$ship['ship_id'].'_unit_2']-$ship['unit_2']) || $ship['unit_1']>$_POST['ship_'.$ship['ship_id'].'_unit_2']) )
        if ($_POST['ship_'.$ship['ship_id'].'_unit_3']>=$ship['min_unit_3'] && $_POST['ship_'.$ship['ship_id'].'_unit_3']<=$ship['max_unit_3'] && ($unit[2]>=($_POST['ship_'.$ship['ship_id'].'_unit_3']-$ship['unit_3']) || $ship['unit_1']>$_POST['ship_'.$ship['ship_id'].'_unit_3']) )
        if ($_POST['ship_'.$ship['ship_id'].'_unit_4']>=$ship['min_unit_4'] && $_POST['ship_'.$ship['ship_id'].'_unit_4']<=$ship['max_unit_4'] && ($unit[3]>=($_POST['ship_'.$ship['ship_id'].'_unit_4']-$ship['unit_4']) || $ship['unit_1']>$_POST['ship_'.$ship['ship_id'].'_unit_4']) )
        {
            $sql = 'UPDATE ships SET
                           unit_1='.$_POST['ship_'.$ship['ship_id'].'_unit_1'].',
                           unit_2='.$_POST['ship_'.$ship['ship_id'].'_unit_2'].',
                           unit_3='.$_POST['ship_'.$ship['ship_id'].'_unit_3'].',
                           unit_4='.$_POST['ship_'.$ship['ship_id'].'_unit_4'].'
                    WHERE ship_id='.$ship['ship_id'];

            if(!$db->query($sql)) {
                message(DATABASE_ERROR, 'Could not update ship data');
            }

            $unit[0]-=$_POST['ship_'.$ship['ship_id'].'_unit_1']-$ship['unit_1'];
            $unit[1]-=$_POST['ship_'.$ship['ship_id'].'_unit_2']-$ship['unit_2'];
            $unit[2]-=$_POST['ship_'.$ship['ship_id'].'_unit_3']-$ship['unit_3'];
            $unit[3]-=$_POST['ship_'.$ship['ship_id'].'_unit_4']-$ship['unit_4'];
        }
    }

    $sql = 'UPDATE planets SET unit_1='.$unit[0].',unit_2='.$unit[1].',unit_3='.$unit[2].',unit_4='.$unit[3].'
            WHERE planet_id='.$game->planet['planet_id'];

    if(!$db->query($sql)) {
        message(DATABASE_ERROR, 'Critical: Could not update planets data');
    }

    $db->unlock();

    redirect('a=spacedock');
}
// #############################################################################
// Display selected ships for un/loading crew
// #############################################################################
elseif(!empty($_POST['man_ships'])) {

    // #############################################################################
    // $_POST['ships'] parser
    $ship_ids = array();

    for($i = 0; $i < count($_POST['ships']); ++$i) {
        $_temp = (int)$_POST['ships'][$i];

        if(!empty($_temp)) {
            $ship_ids[] = $_temp;
        }
    }

    // Check if the ships are presents
    if(empty($ship_ids)) {
        message(NOTICE, constant($game->sprache("TEXT39")));
    }

    $changed_ships = count($ship_ids);


    // #############################################################################
    // Ship list creation:

    $sql = 'SELECT s.*,t.value_5,t.name,t.max_unit_1,t.max_unit_2,t.max_unit_3,t.max_unit_4,t.min_unit_1,t.min_unit_2,t.min_unit_3,t.min_unit_4
            FROM (ships s) LEFT JOIN (ship_templates t) ON s.template_id=t.id
            WHERE s.ship_id IN ('.implode(',', $ship_ids).') AND s.ship_untouchable=0';

    if(($man_ships = $db->queryrowset($sql)) === false || count($man_ships)<1) {
        message(NOTICE, constant($game->sprache("TEXT30")));
    }

    $game->out('
<table width="90%" align="center" border="0" cellpadding="2" cellspacing="2" class="style_outer">
  <tr>
    <td>
      <table width="100%" align="center" border="0" cellpadding="2" cellspacing="2" class="style_inner">
      <form name="mfleet_repair_form" method="post" action="'.parse_link('a=spacedock').'">
        <tr>
          <td>'.constant($game->sprache("TEXT40")).'</td>
        </tr>
        <tr>
          <td align="center">
            <table width="92%" align="center" border="0" cellpadding="1" cellspacing="1">
              <tr>
                <td width="26%"><b>'.constant($game->sprache("TEXT32")).'</b></td>
                <td width="37%"><b>'.constant($game->sprache("TEXT41")).'</b></td>
                <td width="37%"><b>'.constant($game->sprache("TEXT42")).'</b></td>
              </tr>
    ');


    $unit[0]=$game->planet['unit_1'];
    $unit[1]=$game->planet['unit_2'];
    $unit[2]=$game->planet['unit_3'];
    $unit[3]=$game->planet['unit_4'];


    foreach($man_ships as $id => $ship) {
        $units='';
        $units.='<img src="'.$game->GFX_PATH.'menu_unit1_small.gif">&nbsp;'.$ship['unit_1'].'/('.$ship['min_unit_1'].'-'.$ship['max_unit_1'].')&nbsp;&nbsp;';
        $units.='<img src="'.$game->GFX_PATH.'menu_unit2_small.gif">&nbsp;'.$ship['unit_2'].'/('.$ship['min_unit_2'].'-'.$ship['max_unit_2'].')<br>';
        $units.='<img src="'.$game->GFX_PATH.'menu_unit3_small.gif">&nbsp;'.$ship['unit_3'].'/('.$ship['min_unit_3'].'-'.$ship['max_unit_3'].')&nbsp;&nbsp;';
        $units.='<img src="'.$game->GFX_PATH.'menu_unit4_small.gif">&nbsp;'.$ship['unit_4'].'/('.$ship['min_unit_4'].'-'.$ship['max_unit_4'].')';

        if ($ship['max_unit_1']-$ship['unit_1']>$unit[0]) $ship['max_unit_1']=$ship['unit_1']+$unit[0];
        if ($ship['max_unit_2']-$ship['unit_2']>$unit[1]) $ship['max_unit_2']=$ship['unit_2']+$unit[1];
        if ($ship['max_unit_3']-$ship['unit_3']>$unit[2]) $ship['max_unit_3']=$ship['unit_3']+$unit[2];
        if ($ship['max_unit_4']-$ship['unit_4']>$unit[3]) $ship['max_unit_4']=$ship['unit_4']+$unit[3];

        $unit[0]-=$ship['max_unit_1']-$ship['unit_1'];
        $unit[1]-=$ship['max_unit_2']-$ship['unit_2'];
        $unit[2]-=$ship['max_unit_3']-$ship['unit_3'];
        $unit[3]-=$ship['max_unit_4']-$ship['unit_4'];


        $man='';
        $man.='<img src="'.$game->GFX_PATH.'menu_unit1_small.gif">&nbsp;<input class="field"  style="width: 60px;" type="text" name="ship_'.$ship['ship_id'].'_unit_1" value="'.$ship['max_unit_1'].'">&nbsp;&nbsp;';
        $man.='<img src="'.$game->GFX_PATH.'menu_unit2_small.gif">&nbsp;<input class="field"  style="width: 60px;" type="text" name="ship_'.$ship['ship_id'].'_unit_2" value="'.$ship['max_unit_2'].'"><br>';
        $man.='<img src="'.$game->GFX_PATH.'menu_unit3_small.gif">&nbsp;<input class="field"  style="width: 60px;" type="text" name="ship_'.$ship['ship_id'].'_unit_3" value="'.$ship['max_unit_3'].'">&nbsp;&nbsp;';
        $man.='<img src="'.$game->GFX_PATH.'menu_unit4_small.gif">&nbsp;<input class="field"  style="width: 60px;" type="text" name="ship_'.$ship['ship_id'].'_unit_4" value="'.$ship['max_unit_4'].'">';


        /* 04/06/08 - AC: Use real ship name if it exist */
        $ship_name = empty($ship['ship_name'])? $ship['name'] : $ship['ship_name'];
        $game->out('
              <tr>
                <td><b><input type="checkbox" name="ships[]" value="'.$ship['ship_id'].'" checked="checked">&nbsp;'.$ship_name.'</b></td>
                <td>'.$units.'</td>
                <td>'.$man.'</td>
              </tr>
            ');
    }

    $game->out('
            </table>
            <br>
            <input class="button" type="submit" name="man_ships_start" value="'.constant($game->sprache("TEXT43")).'">
          </td>
        </tr>
      </form>
      </table>
    </td>
  </tr>
</table>
    ');

}
// #############################################################################
// Execute ships scrapping
// #############################################################################
elseif(!empty($_POST['scrap_ships_start'])) {

    // #############################################################################
    // $_POST['ships'] parser
    $ship_ids = array();

    for($i = 0; $i < count($_POST['ships']); ++$i) {
        $_temp = (int)$_POST['ships'][$i];

        if(!empty($_temp)) {
            $ship_ids[] = $_temp;
        }
    }

    // Check if the ships are presents
    if(empty($ship_ids)) {
        message(NOTICE, constant($game->sprache("TEXT39")));
    }

    $changed_ships = count($ship_ids);


    // #############################################################################
    // Ship list creation:

    $sql = 'SELECT s.*,t.value_5,t.name,t.buildtime,t.resource_1,t.resource_2,t.resource_3
            FROM (ships s) LEFT JOIN (ship_templates t) ON s.template_id=t.id
            WHERE s.ship_id IN ('.implode(',', $ship_ids).') AND s.ship_untouchable=0';

    if(($scrapable_ships = $db->queryrowset($sql)) === false || count($scrapable_ships)<1) {
        message(NOTICE, constant($game->sprache("TEXT30")));
    }


    // New: Table locking
    $db->lock('ships');

    foreach($scrapable_ships as $id => $ship) {
        $sql = 'UPDATE ships SET ship_untouchable=1, ship_scrap='.($ACTUAL_TICK+ceil($ship['buildtime']*0.2)).'
                WHERE ship_id='.$ship['ship_id'].' AND fleet_id = -'.$game->planet['planet_id'];
        if(!$db->query($sql)) {
            message(DATABASE_ERROR, 'Could not update ship data');
        }
    }

    $db->unlock();

    redirect('a=spacedock');
}
// #############################################################################
// Display selected ships for scrapping
// #############################################################################
elseif(!empty($_POST['scrap_ships'])) {

    $game->out('<script language="JavaScript">
var costs = new Array(0,0,0,0,0,0,0,0,0);

function Change(val1,val2,val3,val4,val5,val6,val7,val8,val9,add)
{
  if (add==0)
    Sub(val1,val2,val3,val4,val5,val6,val7,val8,val9);
  else
    Add(val1,val2,val3,val4,val5,val6,val7,val8,val9);
}


function Add(val1,val2,val3,val4,val5,val6,val7,val8,val9)
{
  costs[0]+=val1;
  costs[1]+=val2;
  costs[2]+=val3;
  costs[3]+=val4;
  costs[4]+=val5;
  costs[5]+=val6;
  costs[6]+=val7;
  costs[7]+=val8;
  costs[8]+=val9;

  document.getElementById( "costs0" ).firstChild.nodeValue = costs[0];
  document.getElementById( "costs1" ).firstChild.nodeValue = costs[1];
  document.getElementById( "costs2" ).firstChild.nodeValue = costs[2];
  document.getElementById( "costs3" ).firstChild.nodeValue = costs[3];
  document.getElementById( "costs4" ).firstChild.nodeValue = costs[4];
  document.getElementById( "costs5" ).firstChild.nodeValue = costs[5];
  document.getElementById( "costs6" ).firstChild.nodeValue = costs[6];
  document.getElementById( "costs7" ).firstChild.nodeValue = costs[7];
  document.getElementById( "costs8" ).firstChild.nodeValue = costs[8];
}


function Sub(val1,val2,val3,val4,val5,val6,val7,val8,val9)
{
  costs[0]-=val1;
  costs[1]-=val2;
  costs[2]-=val3;
  costs[3]-=val4;
  costs[4]-=val5;
  costs[5]-=val6;
  costs[6]-=val7;
  costs[7]-=val8;
  costs[8]-=val9;

  document.getElementById( "costs0" ).firstChild.nodeValue = costs[0];
  document.getElementById( "costs1" ).firstChild.nodeValue = costs[1];
  document.getElementById( "costs2" ).firstChild.nodeValue = costs[2];
  document.getElementById( "costs3" ).firstChild.nodeValue = costs[3];
  document.getElementById( "costs4" ).firstChild.nodeValue = costs[4];
  document.getElementById( "costs5" ).firstChild.nodeValue = costs[5];
  document.getElementById( "costs6" ).firstChild.nodeValue = costs[6];
  document.getElementById( "costs7" ).firstChild.nodeValue = costs[7];
  document.getElementById( "costs8" ).firstChild.nodeValue = costs[8];
}
</script>');

    // #############################################################################
    // $_POST['ships'] parser
    $ship_ids = array();

    for($i = 0; $i < count($_POST['ships']); ++$i) {
        $_temp = (int)$_POST['ships'][$i];

        if(!empty($_temp)) {
            $ship_ids[] = $_temp;
        }
    }

    if(empty($ship_ids)) {
        message(NOTICE, constant($game->sprache("TEXT39")));
    }

    $changed_ships = count($ship_ids);


    // #############################################################################
    // Ship list creation:

    $sql = 'SELECT s.*,t.value_5,t.name,t.buildtime,t.resource_1,t.resource_2,t.resource_3,t.unit_5,t.unit_6
            FROM (ships s) LEFT JOIN (ship_templates t) ON s.template_id=t.id
            WHERE s.ship_id IN ('.implode(',', $ship_ids).') AND s.ship_untouchable=0';

    if(($scrapable_ships = $db->queryrowset($sql)) === false || count($scrapable_ships)<1) {
        message(NOTICE, constant($game->sprache("TEXT30")));
    }

    $game->out('
<form name="mfleet_repair_form" method="post" action="'.parse_link('a=spacedock').'">
<table width="90%" align="center" border="0" cellpadding="2" cellspacing="2" class="style_outer">
  <tr>
    <td>
      <table width="100%" align="center" border="0" cellpadding="2" cellspacing="2" class="style_inner">
        <tr>
          <td>'.constant($game->sprache("TEXT44")).'</td>
        </tr>
        <tr>
          <td align="center">
            <table width="92%" align="center" border="0" cellpadding="1" cellspacing="1">
              <tr>
                <td width="24%"><b>'.constant($game->sprache("TEXT32")).'</b></td>
                <td width="29%"><b>'.constant($game->sprache("TEXT45")).'</b></td>
                <td width="35%"><b>'.constant($game->sprache("TEXT41")).'</b></td>
                <td width="12%"><b>'.constant($game->sprache("TEXT35")).'</b></td>
              </tr>
    ');


    foreach($scrapable_ships as $id => $ship) {
        $reward='';
        $reward.='<img src="'.$game->GFX_PATH.'menu_metal_small.gif">&nbsp;'.round(0.7*($ship['resource_1']-$ship['resource_1']/$ship['value_5']*($ship['value_5']-$ship['hitpoints'])),0).'&nbsp;&nbsp;';
        $reward.='<img src="'.$game->GFX_PATH.'menu_mineral_small.gif">&nbsp;'.round(0.7*($ship['resource_2']-$ship['resource_2']/$ship['value_5']*($ship['value_5']-$ship['hitpoints'])),0).'&nbsp;&nbsp;';
        $reward.='<img src="'.$game->GFX_PATH.'menu_latinum_small.gif">&nbsp;'.round(0.7*($ship['resource_3']-$ship['resource_3']/$ship['value_5']*($ship['value_5']-$ship['hitpoints'])),0).'';


        $units='';
        $units.='<img src="'.$game->GFX_PATH.'menu_unit1_small.gif">&nbsp;'.$ship['unit_1'].'&nbsp;&nbsp;';
        $units.='<img src="'.$game->GFX_PATH.'menu_unit2_small.gif">&nbsp;'.$ship['unit_2'].'&nbsp;&nbsp;';
        $units.='<img src="'.$game->GFX_PATH.'menu_unit3_small.gif">&nbsp;'.$ship['unit_3'].'&nbsp;&nbsp;';
        $units.='<img src="'.$game->GFX_PATH.'menu_unit4_small.gif">&nbsp;'.$ship['unit_4'].'&nbsp;&nbsp;';
        $units.='<img src="'.$game->GFX_PATH.'menu_unit5_small.gif">&nbsp;'.$ship['unit_5'].'&nbsp;&nbsp;';
        $units.='<img src="'.$game->GFX_PATH.'menu_unit6_small.gif">&nbsp;'.$ship['unit_6'].'&nbsp;&nbsp;';


        $tcost[0]+=round(0.7*($ship['resource_1']-$ship['resource_1']/$ship['value_5']*($ship['value_5']-$ship['hitpoints'])),0);
        $tcost[1]+=round(0.7*($ship['resource_2']-$ship['resource_2']/$ship['value_5']*($ship['value_5']-$ship['hitpoints'])),0);
        $tcost[2]+=round(0.7*($ship['resource_3']-$ship['resource_3']/$ship['value_5']*($ship['value_5']-$ship['hitpoints'])),0);
        $tcost[3]+=$ship['unit_1'];
        $tcost[4]+=$ship['unit_2'];
        $tcost[5]+=$ship['unit_3'];
        $tcost[6]+=$ship['unit_4'];
        $tcost[7]+=$ship['unit_5'];
        $tcost[8]+=$ship['unit_6'];


        /* 04/06/08 - AC: Use real ship name if it exist */
        $ship_name = empty($ship['ship_name'])? $ship['name'] : $ship['ship_name'];

        $game->out('
              <tr>
                <td><b><input type="checkbox" name="ships[]" value="'.$ship['ship_id'].'" checked="checked"  onClick ="return Change('.round(0.7*($ship['resource_1']-$ship['resource_1']/$ship['value_5']*($ship['value_5']-$ship['hitpoints'])),0).','.round(0.7*($ship['resource_2']-$ship['resource_2']/$ship['value_5']*($ship['value_5']-$ship['hitpoints'])),0).','.round(0.7*($ship['resource_3']-$ship['resource_3']/$ship['value_5']*($ship['value_5']-$ship['hitpoints'])),0).','.$ship['unit_1'].','.$ship['unit_2'].','.$ship['unit_3'].','.$ship['unit_4'].','.$ship['unit_5'].','.$ship['unit_6'].',this.checked);">&nbsp;'.$ship_name.'</b></td>
                <td><b>'.$reward.'</b></td>
                <td><b>'.$units.'</b></td>
                <td><b>'.(ceil($ship['buildtime']*0.2)*5).' '.constant($game->sprache("TEXT36")).'</b></td>
              </tr>');
    }

    $game->out('
            </table>
            <br>
            <table width="92%" align="center" border="0" cellpadding="1" cellspacing="1">
              <tr>
                <td width="24%"><u><b>'.constant($game->sprache("TEXT46")).'</b></u></td>
                <td width="34%">
                  <img src="'.$game->GFX_PATH.'menu_metal_small.gif">&nbsp;<b id="costs0">'.$tcost[0].'</b>&nbsp;&nbsp;
                  <img src="'.$game->GFX_PATH.'menu_mineral_small.gif">&nbsp;<b id="costs1">'.$tcost[1].'</b>&nbsp;&nbsp;
                  <img src="'.$game->GFX_PATH.'menu_latinum_small.gif">&nbsp;<b id="costs2">'.$tcost[2].'</b>
                </td>
                <td width="42%">
                  <img src="'.$game->GFX_PATH.'menu_unit1_small.gif">&nbsp;<b id="costs3">'.$tcost[3].'</b>&nbsp;&nbsp;
                  <img src="'.$game->GFX_PATH.'menu_unit2_small.gif">&nbsp;<b id="costs4">'.$tcost[4].'</b>&nbsp;&nbsp;
                  <img src="'.$game->GFX_PATH.'menu_unit3_small.gif">&nbsp;<b id="costs5">'.$tcost[5].'</b>&nbsp;&nbsp;
                  <img src="'.$game->GFX_PATH.'menu_unit4_small.gif">&nbsp;<b id="costs6">'.$tcost[6].'</b>&nbsp;&nbsp;
                  <img src="'.$game->GFX_PATH.'menu_unit5_small.gif">&nbsp;<b id="costs7">'.$tcost[7].'</b>&nbsp;&nbsp;
                  <img src="'.$game->GFX_PATH.'menu_unit6_small.gif">&nbsp;<b id="costs8">'.$tcost[8].'</b>
                </td>
                <script language="JavaScript">
                  costs[0]='.$tcost[0].';
                  costs[1]='.$tcost[1].';
                  costs[2]='.$tcost[2].';
                  costs[3]='.$tcost[3].';
                  costs[4]='.$tcost[4].';
                  costs[5]='.$tcost[5].';
                  costs[6]='.$tcost[6].';
                  costs[7]='.$tcost[7].';
                  costs[8]='.$tcost[8].';
                </script>
              </tr>
            </table>
            <br>
            <input class="button" type="submit" name="scrap_ships_start" value="'.constant($game->sprache("TEXT47")).'">
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>');
}
// #############################################################################
// Move selected ships in a new fleet
// #############################################################################
elseif(!empty($_POST['new_fleet'])) {

    if(empty($_POST['ships'])) {
        message(NOTICE, constant($game->sprache("TEXT39")));
    }

    if(empty($_POST['new_fleet_name'])) {
        message(NOTICE, constant($game->sprache("TEXT48")));
    }


    $new_fleet_name = htmlspecialchars($_POST['new_fleet_name']);


    // #############################################################################
    // $_POST['ships'] parser

    $ship_ids = array();

    for($i = 0; $i < count($_POST['ships']); ++$i) {
        $_temp = (int)$_POST['ships'][$i];

        if(!empty($_temp)) {
            $ship_ids[] = $_temp;
        }
    }

    // Check if the ships are presents
    if(empty($ship_ids)) {
        message(NOTICE, constant($game->sprache("TEXT39")));
    }

    
    // #############################################################################
    // Ship check

    $sql = 'SELECT fleet_id
            FROM ships
            WHERE ship_id IN ('.implode(',', $ship_ids).') AND
                  ship_untouchable = 0 AND fleet_id < 0 AND user_id = '.$game->player['user_id'].'';

    if(!$q_ships = $db->query($sql)) {
       message(DATABASE_ERROR, 'Could not query ships data');
    }

    if ($db->num_rows($q_ships) > 0) {

        $cur_ship = $db->fetchrow($q_ships);

        $n_ships = 1;

        $planet_id = $cur_ship['fleet_id'];

        while($cur_ship = $db->fetchrow($q_ships)) {
            if($cur_ship['fleet_id'] != $planet_id) {
                message(NOTICE, constant($game->sprache("TEXT49")));
            }
            $n_ships++;
        }
    } else { 
        message(NOTICE, constant($game->sprache("TEXT39")));
    }


    // #############################################################################
    // New grounds fleet

    $sql = 'INSERT INTO ship_fleets (fleet_name, user_id, planet_id, move_id, n_ships, homebase, resource_1, resource_2, resource_3, resource_4, unit_1, unit_2, unit_3, unit_4, unit_5, unit_6)
            VALUES ("'.$new_fleet_name.'", '.$game->player['user_id'].', '.( (-1) * $planet_id ).', 0, '.$n_ships.', '.( (-1) * $planet_id ).', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)';

    if(!$db->query($sql)) {
        message(DATABASE_ERROR, 'Could not insert new fleets data');
    }

    $new_fleet_id = $db->insert_id();

    if(empty($new_fleet_id)) {
        message(GENERAL, constant($game->sprache("TEXT50")), '$new_fleet_id = empty');
    }


    // #############################################################################
    // Updating the ships's data

    $sql = 'UPDATE ships
            SET fleet_id = '.$new_fleet_id.',
                last_refit_time = '.$game->TIME.'
            WHERE ship_id IN ('.implode(',', $ship_ids).') AND ship_untouchable=0 AND fleet_id<0';

    if(!$db->query($sql)) {
        message(DATABASE_ERROR, 'Could not update ships fleet data');
    }

    redirect('a=spacedock');
}
// #############################################################################
// Move selected ships in an existing fleet
// #############################################################################
elseif(!empty($_POST['change_fleet'])) {

    if(empty($_POST['ships'])) {
        message(NOTICE, constant($game->sprache("TEXT39")));
    }

    $new_fleet_id = (int)$_POST['change_fleet_to'];


    // #############################################################################
    // Query fleet data

    $sql = 'SELECT fleet_id, user_id, planet_id
            FROM ship_fleets
            WHERE fleet_id = '.$new_fleet_id.' AND user_id = '.$game->player['user_id'].'';

    if(($new_fleet = $db->queryrow($sql)) === false) {
        message(DATABASE_ERROR, 'Could not query fleet data');
    }

    if(empty($new_fleet['fleet_id'])) {
        message(NOTICE, constant($game->sprache("TEXT51")));
    }

    if($new_fleet['user_id'] != $game->player['user_id']) {
        message(NOTICE, constant($game->sprache("TEXT51")));
    }

    if($new_fleet['planet_id'] != $game->planet['planet_id']) {
        message(NOTICE, constant($game->sprache("TEXT52")));
    }


    // #############################################################################
    // $_POST['ships'] parser

    $ship_ids = array();

    for($i = 0; $i < count($_POST['ships']); ++$i) {
        $_temp = (int)$_POST['ships'][$i];

        if(!empty($_temp)) {
            $ship_ids[] = $_temp;
        }
    }

    // Check if the ships are presents
    if(empty($ship_ids)) {
        message(NOTICE, constant($game->sprache("TEXT39")));
    }


    // #############################################################################
    // Ship check

    $sql = 'SELECT fleet_id
            FROM ships
            WHERE ship_id IN ('.implode(',', $ship_ids).') AND
                  ship_untouchable = 0 AND fleet_id < 0 AND user_id = '.$game->player['user_id'].'';

    if(!$q_ships = $db->query($sql)) {
        message(DATABASE_ERROR, 'Could not query ships data');
    }

    $cur_ship = $db->fetchrow($q_ships);

    $n_ships = 1;

    $planet_id = $cur_ship['fleet_id'];


    while($cur_ship = $db->fetchrow($q_ships)) {
        if($cur_ship['fleet_id'] != $planet_id) {
            message(NOTICE, constant($game->sprache("TEXT49")));
        }
        $n_ships++;
    }

    if($n_ships == 0) {
        message(NOTICE, constant($game->sprache("TEXT53")));
    }


    // #############################################################################
    // Updating the ships's data

    $sql = 'UPDATE ships
            SET fleet_id = '.$new_fleet_id.',
                last_refit_time = '.$game->TIME.'
            WHERE ship_id IN ('.implode(',', $ship_ids).') AND ship_untouchable=0';

    if(!$db->query($sql)) {
        message(DATABASE_ERROR, 'Could not update ships fleet data');
    }


    // #############################################################################
    // Updating the ships's data

    $sql = 'UPDATE ship_fleets SET n_ships = n_ships + '.$n_ships.'
            WHERE fleet_id = '.$new_fleet_id;

    if(!$db->query($sql)) {
        message(DATABASE_ERROR, 'Could not update fleets data');
    }

    redirect('a=spacedock');
}
// #############################################################################
// Rename the selected ship
// #############################################################################
elseif(!empty($_POST['rename_ship'])) {

    if(empty($_POST['ships'])) {
        message(NOTICE, constant($game->sprache("TEXT39")));
    }

    if(empty($_POST['new_ship_name'])) {
        message(NOTICE, constant($game->sprache("TEXT63")));
    }

    $new_ship_name = htmlspecialchars($_POST['new_ship_name']);

    $ship_id = (int)$_POST['ships'][0];

    $sql = 'UPDATE ships SET ship_name = "'.$new_ship_name.'"
            WHERE ship_id = '.$ship_id;

    if(!$db->query($sql)) {
        message(DATABASE_ERROR, 'Could not update ship data');
    }

    redirect('a=spacedock');
}
// #############################################################################
// Set Naval Contract Code (NCC) to the selected ship
// #############################################################################
elseif(!empty($_POST['change_ship_ncc'])) {

    if(empty($_POST['ships'])) {
        message(NOTICE, constant($game->sprache("TEXT39")));
    }

    if(empty($_POST['new_ship_ncc'])) {
        message(NOTICE, constant($game->sprache("TEXT64")));
    }

    $new_ship_ncc = htmlspecialchars($_POST['new_ship_ncc']);

    $ship_id = (int)$_POST['ships'][0];

    $sql = 'UPDATE ships SET ship_ncc = "'.$new_ship_ncc.'"
            WHERE ship_id = '.$ship_id;

    if(!$db->query($sql)) {
        message(DATABASE_ERROR, 'Could not update ship data');
    }

    redirect('a=spacedock');
}
// #############################################################################
// Display the main page of the Spacedock
// #############################################################################
else {

    $game->out('<script language="JavaScript">
function UpdateShipData()
{
  var ships = document.getElementById( "ships" );
  var n=ships.options[ships.selectedIndex].id.split(",");

  document.getElementById( "ship_name" ).value = ships.options[ships.selectedIndex].title;
  document.getElementById( "ship_ncc" ).value = n[0];
}

function ShipSelection(cSelectType) {
    var ships = document.getElementById("ships");

    for (var i=0; i<ships.length; i++) {
        if (cSelectType == "All") {
            ships.options[i].selected = true;
        } else if (cSelectType == "Damaged") {
            var n=ships.options[i].id.split(",");

            if (n[1] == -1) {
                ships.options[i].selected = true;
            } else {
                ships.options[i].selected = false;
            }
        } else if (cSelectType == "Intact") {
            var n=ships.options[i].id.split(",");

            if (n[1] == 0) {
                ships.options[i].selected = true;
            } else {
                ships.options[i].selected = false;
            }
        } else if (cSelectType == "None") {
            ships.options[i].selected = false;
        }
    }
}
</script>');

    $sql = 'SELECT s.ship_id, s.hitpoints, s.ship_repair, s.ship_scrap, s.ship_untouchable,
                   s.unit_1,s.unit_2,s.unit_3,s.unit_4, s.ship_name, s.ship_ncc,
                   st.max_unit_1,st.max_unit_2,st.max_unit_3,st.max_unit_4,
                   st.name AS template_name, st.value_5 AS max_hitpoints
            FROM (ships s)
            INNER JOIN (ship_templates st) ON st.id = s.template_id
            WHERE s.fleet_id = -'.$game->planet['planet_id'].' ORDER BY s.ship_untouchable DESC,s.ship_scrap,s.ship_repair,st.name,s.ship_name ASC';

    if(!$q_ships = $db->query($sql)) {
        message(DATABASE_ERROR, 'Could not query ships data');
    }

    $select_size = ($db->num_rows($q_ships) + 1);

    $null = 10;


    if($select_size == 1) $null = 0;

    if($select_size < 3) $select_size = 3;

    if($select_size > 10) $select_size = 10;


    $game->out('
<table width="90%" align="center" border="0" cellpadding="2" cellspacing="2" class="style_outer">
  <tr>
    <td>
      <table width="100%" align="center" border="0" cellpadding="2" cellspacing="2" class="style_inner">
      <form name="mfleet_form" method="post" action="'.parse_link('a=spacedock').'">
        <tr>
          <td>'.constant($game->sprache("TEXT56")).'</td>
        </tr>
        <tr>
          <td>
            <select name="ships[]" id="ships" style="width: 100%;" multiple="multiple" size="'.$select_size.'" onclick="UpdateShipData()" onkeyup="UpdateShipData()">'.( ($null==0) ? '<option></option>' : '' ).'
    ');



    while($ship = $db->fetchrow($q_ships)) {
        $repair='';

        if ($ship['ship_repair']>0) $repair=' R ('.ZeitDetailShort($NEXT_TICK+3*60*($ship['ship_repair']-$ACTUAL_TICK)).')';

        $scrap='';

        if ($ship['ship_scrap']>0) $scrap=' S ('.ZeitDetailShort($NEXT_TICK+3*60*($ship['ship_scrap']-$ACTUAL_TICK)).')';

        $b_title='';

        if (($ship['max_unit_1']+$ship['max_unit_2']+$ship['max_unit_3']+$ship['max_unit_4'])*($ship['unit_1']+$ship['unit_2']+$ship['unit_3']+$ship['unit_4'])>0)
        {
            $besatzung=100/($ship['max_unit_1']+$ship['max_unit_2']+$ship['max_unit_3']+$ship['max_unit_4'])*($ship['unit_1']+$ship['unit_2']+$ship['unit_3']+$ship['unit_4']);

            if ($besatzung!=100) $b_title=' B='.round($besatzung,0).'%';
        }

        $status = $ship['hitpoints'] < $ship['max_hitpoints'] ? -1 : 0;

        /* 07/04/08 - AC: If present, show also ship's name */
        $game->out('<option value="'.$ship['ship_id'].'" title="'.addslashes($ship['ship_name']).'" id="'.addslashes($ship['ship_ncc']).','.$status.'">'.(($ship['ship_name'] != '')? $ship['ship_name'].' - '.$ship['template_name'] : $ship['template_name']).' ('.$ship['hitpoints'].'/'.$ship['max_hitpoints'].')'.( ($ship['ship_untouchable']) ? ' U' : '' ).''.$repair.''.$scrap.''.$b_title.'</option>');
    }


    $sql = 'SELECT fleet_id, fleet_name
            FROM ship_fleets
            WHERE planet_id = '.$game->planet['planet_id'].' AND
                  user_id = '.$game->player['user_id'];

    if(!$q_fleets = $db->query($sql)) {
        message(DATABASE_ERROR, 'Could not query fleets data');
    }

    $n_fleets = $db->num_rows($q_fleets);


    $game->out('
            </select>
            <br><br>
            '.constant($game->sprache("TEXT67")).'
            <input class="button" style="width: 20%;" type="button" name="select_all" value="'.constant($game->sprache("TEXT68")).'" onClick="ShipSelection(\'All\')">
            <input class="button" style="width: 20%;" type="button" name="select_damaged" value="'.constant($game->sprache("TEXT69")).'" onClick="ShipSelection(\'Damaged\')">
            <input class="button" style="width: 20%;" type="button" name="select_intact" value="'.constant($game->sprache("TEXT71")).'" onClick="ShipSelection(\'Intact\')">
            <input class="button" style="width: 20%;" type="button" name="select_none" value="'.constant($game->sprache("TEXT70")).'" onClick="ShipSelection(\'None\')"><br><br>
            <input class="button" type="submit" name="repair_ships" value="'.constant($game->sprache("TEXT38")).'">&nbsp;
            <input class="button" type="submit" name="man_ships" value="'.constant($game->sprache("TEXT43")).'">&nbsp;
            <input class="button" type="submit" name="scrap_ships" value="'.constant($game->sprache("TEXT47")).'">&nbsp;
            <input class="button" type="submit" name="view_detail" value="'.constant($game->sprache("TEXT57")).'" onClick="return document.mfleet_form.action = \''.parse_link('a=ship_fleets_ops&ship_details').'\'"><br><br>
            <table width="100%" border="0" cellpadding="2" cellspacing="0">
              <tr>
                <td width="33%" rowspan="2" valign="middle">'.constant($game->sprache("TEXT58")).'<br></td>
                <td width="33%"><input class="field"  style="width: 100%;" type="text" name="new_fleet_name"></td>
                <td width="33%"><input class="button" style="width: 100%;" type="submit" name="new_fleet" value="'.constant($game->sprache("TEXT59")).'"></td>
              </tr>
              <tr>');


    if($n_fleets > 0) {
        $game->out('<td><select style="width: 120px;" name="change_fleet_to">');

        while($fleet = $db->fetchrow($q_fleets)) {
            $game->out('<option value="'.$fleet['fleet_id'].'">'.$fleet['fleet_name'].'</option>');
        }


        $game->out('
                  </select>
                </td>
                <td><input class="button" style="width: 100%;" type="submit" name="change_fleet" value="'.constant($game->sprache("TEXT60")).'"></td>
        ');
    }
    else {
        $game->out('
                <td><select style="width: 100%;" disabled="disabled"><option value="0">-</option></select></td>
                <td><input class="button" style="width: 100%;" type="submit" name="change_fleet" value="'.constant($game->sprache("TEXT60")).'" disabled="disabled"></td>
        ');
    }

    $game->out('
              </tr>
              <tr>
                <td>'.constant($game->sprache("TEXT32")).'</td>
                <td><input class="field" type="text" name="new_ship_name" id="ship_name" value="" maxlength="25" size="25"></td>
                <td><input class="button" style="width: 100%;" type="submit" name="rename_ship" value="'.constant($game->sprache("TEXT65")).'"></td>
              </tr>
              <tr>
                <td>'.constant($game->sprache("TEXT61")).'</td>
                <td><input class="field" type="text" name="new_ship_ncc" id="ship_ncc" value="" maxlength="12" size="25"></td>
                <td><input class="button" style="width: 100%;" type="submit" name="change_ship_ncc" value="'.constant($game->sprache("TEXT66")).'"></td>
              </tr>
            </table>
            <br>
          </td>
        </tr>
      </form>
      </table>
    </td>
  </tr>
</table>');

}



?>

