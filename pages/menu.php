<?PHP
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

$title_html = 'Star Trek - Galaxy War: menu';
$meta_descr = 'STFC: Pagina relativa ai credits del gioco, con i nomi ed i nick degli operatori ed i contatti email.';
$main_html='
		<h1>MENU</h1>
			<div class="buttons">
				<a href="'.parse_link().'" class="gold">Home</a>
				<a href="'.parse_link('a=stats').'" class="green">Statistiken</a>
				<a href="'.parse_link('a=faq').'" class="violet">FAQ(Hilfe)</a>
				<!--<a href="https://discord.gg/fbzafjkMp4" target="blank">Discord</a>-->
				<a href="https://forum.fam-hinrichsen.de" target="blank">Forum</a>
				<a href="'.parse_link('a=imprint').'" class="almond">Impressum</a>
				<!--<a href="https://github.com/saboti" target="blank_" class="lilac">Developer</a>-->
			</div>
';
?>
