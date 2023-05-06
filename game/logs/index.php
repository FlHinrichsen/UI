<?php


?>

<HTML>
	<head>
		<TITLE>
			Star Trek: Frontline Combat - Logs page 
		</TITLE>
		<script src="jquery-3.6.4.min.js"></script>  
		<script src="app.js"></script>
	</head>
	<BODY>
		<H1>Here you can display STFC daily logs.</H1>
		<table>
			<tr>
				<th>
					Available logs:
				</th>
				<th id="content-title">
					Content:
				</th>
			</tr>
			<tr>
				<td>
					<table>
						<tr>
							<td>
								<a href="view_log.php">Scheduler</a>
							</td>
							<td>							
								<a name="tail" href="view_log.php">tail</a>
							</td>
						</tr>
						<tr>
							<td>
								<a href="view_moves_log.php">Moves</a>
							</td>
							<td>							
								<a name="tail" href="view_moves_log.php">tail</a>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								Non Playing Components
							</td>
						</tr>
						<tr>
							<td>
								<a href="view_log.php?file=NPC_BOT_FERENGI_tick_<?php echo date('d-m-Y', time()); ?>">Ferengi</a>
							</td>
							<td>							
								<a name="tail" href="view_log.php?file=NPC_BOT_FERENGI_tick_<?php echo date('d-m-Y', time()); ?>">tail</a>
							</td>
						</tr>
						<tr>
							<td>
								<a href="view_log.php?file=NPC_BOT_BORG_tick_<?php echo date('d-m-Y', time()); ?>">Borg</a>
							</td>
							<td>							
								<a name="tail" href="view_log.php?file=NPC_BOT_BORG_tick_<?php echo date('d-m-Y', time()); ?>">tail</a>
							</td>
						</tr>
						<tr>
							<td>
								<a href="view_log.php?file=NPC_BOT_SETTLERS_tick_<?php echo date('d-m-Y', time()); ?>">Settlers</a>
							</td>
							<td>							
								<a name="tail" href="view_log.php?file=NPC_BOT_SETTLERS_tick_<?php echo date('d-m-Y', time()); ?>">tail</a>
							</td>
						</tr>
						<tr>
							<td>
								<a HREF="fixall/view_log.php">Fix all</a>
							</td>
							<td>							
								<a name="tail" HREF="fixall/view_log.php">tail</a>
							</td>
						</tr>
						<tr>
							<td>
								<a HREF="sixhours/view_log.php">Six hours</a>
							</td>
							<td>							
								<a name="tail" HREF="sixhours/view_log.php">tail</a>
							</td>
						</tr>
						<tr>
							<td>
								<a HREF="view_log.php?file=daily">Daily</a>
							</td>
							<td>							
								<a name="tail" HREF="view_log.php?file=daily">tail</a>
							</td>
						</tr>
						<tr>
							<td>
								<a HREF="view_log.php?file=NPC_installation">NPC installation</a>
							</td>
							<td>							
								<a name="tail" HREF="view_log.php?file=NPC_installation">tail</a>
							</td>
						</tr>
					</table>
				</td>
				<td>			
					<div id="loading" >
					   <img src="./pfz9h9qFok1s5sodbo7_1280.gif" style="height:800px; width:1000px">
					</div>
					<div id="mainpage-load" style="overflow-y: scroll; height:800px; width:1000px">		
					</div>
				</td>
			</tr>
		</table>
	</BODY>
</HTML>
