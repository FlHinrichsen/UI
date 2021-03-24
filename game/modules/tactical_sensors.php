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


$game->init_player();

include('include/static/static_components_9.php');
$filename = 'include/static/static_components_9_'.$game->player['language'].'.php';
if (file_exists($filename)) include($filename);


$game->out('<span class="caption">'.constant($game->sprache("TEXT0")).'</span><br><br>[<a href="'.parse_link('a=tactical_cartography').'">'.constant($game->sprache("TEXT1")).'</a>]&nbsp;&nbsp;[<a href="'.parse_link('a=tactical_moves').'">'.constant($game->sprache("TEXT2")).'</a>]&nbsp;&nbsp;[<a href="'.parse_link('a=tactical_player').'">'.constant($game->sprache("TEXT3")).'</a>]&nbsp;&nbsp;[<a href="'.parse_link('a=tactical_kolo').'">'.constant($game->sprache("TEXT4")).'</a>]&nbsp;&nbsp;[<a href="'.parse_link('a=tactical_known').'">'.constant($game->sprache("TEXT4a")).'</a>]&nbsp;&nbsp;[<b>'.constant($game->sprache("TEXT5")).'</b>]<br>[<a href="'.parse_link('a=tactical_sensors&view_attack').'">'.constant($game->sprache("TEXT32")).'</a>]&nbsp;[<a href="'.parse_link('a=tactical_sensors&delete_ferengi').'">'.constant($game->sprache("TEXT33")).'</a>]&nbsp;[<a href="'.parse_link('a=tactical_sensors').'">'.constant($game->sprache("TEXT34")).'</a>]&nbsp;[<a href="'.parse_link('a=tactical_sensors&fleets_sensors').'">'.constant($game->sprache("TEXT37")).'</a>]<br><br>');

$filter_stream = '(11, 12, 13, 14, 21, 23, 24, 25, 26, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 51, 54, 55)';

$planets_selection = 'p2.system_id IN (SELECT DISTINCT system_id FROM planets WHERE planet_owner = ' . $game->player['user_id'] . ' ) AND';
$fleets_sensors = false;

if (isset($_GET['delete_ferengi']))
{
    $filter_stream = '(11, 12, 13, 14, 21, 23, 24, 25, 26, 27, 31, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 51, 54, 55)';
}
elseif (isset($_GET['view_attack']))
{
    $filter_stream = '(40, 41, 42, 43, 44, 45, 46, 51, 54, 55)';
}

if (isset($_GET['fleets_sensors']))
{
    $sql = 'SELECT fleet_id, fleet_name, n_ships, planet_id
            FROM ship_fleets
            WHERE user_id = '.$game->player['user_id'].' AND
                  move_id = 0';

    if(!$q_fleets = $db->query($sql)) {
        message(DATABASE_ERROR, 'Could not query moves fleets data!');
    }

    while($_fl = $db->fetchrow($q_fleets)) {
        $fleet_ids[$_fl['planet_id']] = $_fl['fleet_id'];
        $fleet_planets[] = $_fl['planet_id'];
        $fleet_names[$_fl['planet_id']] = $_fl['fleet_name'];
        //$n_ships[] = $_fl['n_ships'];
    }

    if(count($fleet_ids) == 0) {
        message(NOTICE, constant($game->sprache("TEXT38")));
    }

    $fleet_planets_str = implode(',', $fleet_planets);

    $planets_selection = 'p2.system_id IN (SELECT DISTINCT system_id FROM planets p, ship_fleets sf WHERE p.planet_id = sf.planet_id AND sf.move_id = 0 AND sf.user_id ='.$game->player['user_id'].') AND';

    $fleets_sensors = true;
}



$start = (!empty($_GET['start'])) ? (int)$_GET['start']:0;

$dest = (!empty($_GET['dest'])) ? (int)$_GET['dest']:0;



$sql = 'SELECT ss.*, 
            p1.planet_name AS start_planet_name,
            u1.user_id AS start_owner_id, 
            u1.user_name AS start_owner_name,
            u2.user_name as owner_name,
            p2.planet_name AS dest_planet_name,
            u3.user_name AS dest_owner_name,
            p2.system_id AS dest_id
        FROM (scheduler_shipmovement ss, planets p2)
            LEFT JOIN (planets p1) ON p1.planet_id = ss.start
            LEFT JOIN (user u1) ON u1.user_id = p1.planet_owner
            LEFT JOIN (user u2) ON u2.user_id = ss.user_id
            LEFT JOIN (user u3) ON u3.user_id = p2.planet_owner
        WHERE p2.planet_id = ss.dest AND
              '.$planets_selection.'
              ss.move_begin <= ' . $ACTUAL_TICK . ' AND
              ss.move_finish >= ' . $ACTUAL_TICK . ' AND
              ss.user_id<>' . $game->player['user_id'] . ' AND
              ss.move_status = 0 AND ss.action_code IN '.$filter_stream.'' . (($start) ?
    ' AND ss.start = ' . $start:'') . (($dest) ? ' AND ss.dest = ' . $dest:'') . '
        ORDER BY ss.move_finish ASC';



if (!$q_moves = $db->query($sql))
{
    message(DATABASE_ERROR, 'Could not query moves data');
}



$n_moves = $db->num_rows($q_moves);



if ($n_moves == 0)
{
    message(NOTICE, constant($game->sprache("TEXT6")));
}



// For the arrival timer

$i = 2;


// DC Lasciamo i codici dei trasporti Ferengi come "visibili"
// DC La mossa di attacco Borg � sempre visibile
$visible_actions = array(32, 33, 46);

// Number of fleets displayed
$fleets_displayed = 0;

while ($move = $db->fetchrow($q_moves))
{
    $visibility = 0;

    //array('n_ships', 'sum_sensors', 'sum_cloak', 'status', 'torso' => array(0...9) )
    $sensor1 = get_move_ship_details($move['move_id']);

    if (!in_array($move['action_code'], $visible_actions))
    {
        // Todo: Fleets queries, calculate values:
        //array('n_ships', 'sum_sensors', 'sum_cloak')
        /* 30/06/08 - AC: Planet sensors depends on target planet NOT on currently active planet!  */
        //$sensor2 = get_friendly_orbit_fleets($move['dest_id']);
        $sensor2['sum_sensors'] = 0;
        $sensor2['sum_cloak'] = 0;
        
        if($fleets_sensors)
            $sensor3['sum_sensors'] = get_system_fleet_sensors($move['dest_id']);
        else
        {
            // $sensor3['sum_sensors'] = ($move['dest_sensors'] + 1) * PLANETARY_SENSOR_VALUE;
            $sensor3['sum_sensors'] = get_system_planetary_sensors($move['dest_id']) * PLANETARY_SENSOR_VALUE;
            //$sensor3['sum_cloak'] = 0;
        }

        /* 25/11/08 - AC: Spacedock sensors must be added to orbit fleets NOT to incoming fleets... */
        $flight_duration = $move['move_finish'] - $move['move_begin'];
        $visibility = GetVisibility($sensor1['sum_sensors'], $sensor1['sum_cloak'], $sensor1['n_ships'],
            $sensor2['sum_sensors'], $sensor2['sum_cloak'], $sensor3['sum_sensors'],$flight_duration);
        $travelled = 100 / $flight_duration * ($ACTUAL_TICK - $move['move_begin']);
    }
    else
    {
        // Ferengi(NPC) doesn't have ships templates stored in the DB
        if($move['move_id'] != 46) $sensor1['torso'][1] = $move['n_ships'];
    }


    if ($travelled >= $visibility || in_array($move['action_code'], $visible_actions))
    {
        $game->out('

<table border="0" width="200" align="center" cellpadding="2" cellspacing="2" class="style_outer">
  <tr>
    <td>
      <table border="0" width="200" align="center" cellpadding="2" cellspacing="2" class="style_inner">
        <tr>
          <td>
        ');



        if ($move['start'] == $move['dest'])
        {
            $game->out(constant($game->sprache("TEXT7")).' <a href="' . parse_link('a=tactical_cartography&planet_id=' .
                encode_planet_id($move['start'])) . '"><b>' . $move['start_planet_name'] .
                '</b></a><br>');
        }
        else
        {
            $game->out(constant($game->sprache("TEXT8")).' ' . (isset($move['owner_name']) ? '<a href="' .
                parse_link('a=stats&a2=viewplayer&id=' . $move['user_id']) . '"><b>' . $move['owner_name'] .
                '</b></a>':'<b>'.constant($game->sprache("TEXT9")).'</b>') . '<br>');

            if (!empty($move['start']))
            {
                if (empty($move['start_owner_id']))
                    $start_owner_str = ' <i>'.constant($game->sprache("TEXT10")).'</i>';
                elseif ($move['start_owner_id'] != $game->player['user_id'])
                    $start_owner_str = ' '.constant($game->sprache("TEXT11")).' <a href="' . parse_link('a=stats&a2=viewplayer&id=' . $move['start_owner_id']) .
                        '"><b>' . $move['start_owner_name'] . '</b></a>';
                else
                    $start_owner_str = '';


                $game->out(constant($game->sprache("TEXT12")).' <a href="' . parse_link('a=tactical_cartography&planet_id=' .
                    encode_planet_id($move['start'])) . '"><b>' . $move['start_planet_name'] .
                    '</b></a>' . $start_owner_str . '<br>');
            }
            else
            {
                $game->out(constant($game->sprache("TEXT12")).' <i>'.constant($game->sprache("TEXT13")).'</i><br>');
            }

            $game->out(constant($game->sprache("TEXT14")).' <a href="' . parse_link('a=tactical_cartography&planet_id=' .
                encode_planet_id($move['dest'])) . '"><b>' . $move['dest_planet_name'] .
                '</b></a><br>');
        }


        $commands = array(11 => constant($game->sprache("TEXT15")), 12 => constant($game->sprache("TEXT16")),
            13 => constant($game->sprache("TEXT16")), 14 => constant($game->sprache("TEXT17")),
            21 => constant($game->sprache("TEXT15")), 22 => constant($game->sprache("TEXT18")),
            23 => constant($game->sprache("TEXT19")), 24 => constant($game->sprache("TEXT20")),
            25 => constant($game->sprache("TEXT20")), 26 => constant($game->sprache("TEXT36")),
            31 => constant($game->sprache("TEXT17")),
            32 => constant($game->sprache("TEXT21")), 33 => constant($game->sprache("TEXT22")),
            34 => constant($game->sprache("TEXT23")), 35 => constant($game->sprache("TEXT23")),
            36 => constant($game->sprache("TEXT23")), 37 => constant($game->sprache("TEXT23")),
            38 => constant($game->sprache("TEXT23")), 39 => constant($game->sprache("TEXT23")),
            40 => constant($game->sprache("TEXT24")), 41 => constant($game->sprache("TEXT24")),
            42 => constant($game->sprache("TEXT24")), 43 => constant($game->sprache("TEXT25")),
            44 => constant($game->sprache("TEXT26")), 45 => constant($game->sprache("TEXT20")),
            46 => constant($game->sprache("TEXT35")),
            51 => constant($game->sprache("TEXT24")), 53 => constant($game->sprache("TEXT25")),
            54 => constant($game->sprache("TEXT26")), 55 => constant($game->sprache("TEXT20")), );


        if (in_array($move['action_code'], $visible_actions) || $travelled >= $visibility +
            ((100 - $visibility) / 4))
        {
            $game->out('<br>'.constant($game->sprache("TEXT27")).' <b>' . $sensor1['n_ships'] . '</b>');
        }

        if (in_array($move['action_code'], $visible_actions) || $travelled >= $visibility +
            2 * ((100 - $visibility) / 4))
        {
            $game->out('<br>'.constant($game->sprache("TEXT28")).' <b>' . $commands[$move['action_code']] . '</b>');
        }

        if (in_array($move['action_code'], $visible_actions) || $travelled >= $visibility +
            3 * ((100 - $visibility) / 4))
        {
            $game->out('<br>'.constant($game->sprache("TEXT29")));

            for ($t = 0; $t < 14; $t++)
            {
                if (isset($sensor1['torso'][$t]) && $sensor1['torso'][$t] > 0)
                    if (!isset($SHIP_TORSO[$game->player['user_race']][$t][0]))
                        if ($SHIP_TORSO[9][6][0])
                        {
                            $game->out('<br><b>' . $sensor1['torso'][$t] . 'x</b> '.constant($game->sprache("TEXT30")).' ' .
                                (7) . ')');
                        }
                        else
                        {
                            $game->out('<br><b>' . $sensor1['torso'][$t] . '</b> Typ ' . ($t + 1));
                        }
                    else
                        $game->out('<br><b>' . $sensor1['torso'][$t] . 'x</b> ' . ($SHIP_TORSO[$game->
                            player['user_race']][$t][29]));
            }
        }


        $ticks_left = $move['move_finish'] - $ACTUAL_TICK;

        if ($ticks_left < 0)
            $ticks_left = 0;


        $game->out('

          <br><br>

          '.constant($game->sprache("TEXT31")).' ' . (($i < 10) ? '<b id="timer' . $i . '" title="time1_' . (($ticks_left *
            TICK_DURATION * 60) + $NEXT_TICK) . '_type2_2">&nbsp;</b>':format_time($ticks_left *
            TICK_DURATION)) . '

        ');

        if($fleets_sensors)
            $game->out('<br><br>'.constant($game->sprache("TEXT39")).' <a href="'.parse_link('a=ship_fleets_display&pfleet_details='.$fleet_ids[$move['dest_id']].'').'"><b>'.$fleet_names[$move['dest_id']].'</b></a>');

        $game->out('

          </td>
        </tr>
      </table>
    </td>
  </tr>
</table><br>

        ');


        ++$i;
        $fleets_displayed++;
    }
}

// Some fleets are present, but not revealed by player's sensors
if($fleets_displayed == 0)
    message(NOTICE, constant($game->sprache("TEXT6")));


?>

