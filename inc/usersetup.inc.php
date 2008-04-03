<?PHP

	define(GALAXY_MAP_DOT_RADIUS,3);
	define(GALAXY_MAP_WIDTH,500);
	define(GALAXY_MAP_LEGEND_HEIGHT,40);
	
	$sx_num=$conf['num_of_sectors']['p1'];
	$sy_num=$conf['num_of_sectors']['p2'];
	$cx_num=$conf['num_of_cells']['p1'];
	$cy_num=$conf['num_of_cells']['p2'];

	echo "<h1>Willkommen in Andromeda</h1>";


	if (isset($_POST['submit_chooseplanet']) && $_POST['choosenplanetid']>0 && checker_verify())
	{
		$tp = new Planet($_POST['choosenplanetid']);
		$tp->assignToUser($cu->id(),1);
		$mode = "finished";
	}
	elseif (isset($_GET['setup_sx']) && isset($_GET['setup_sy']) && $_GET['setup_sx']>0 && $_GET['setup_sy']>0 && $_GET['setup_sx']<=$sx_num && $_GET['setup_sy']<=$sy_num)
	{
		if ($pid = Planets::getFreePlanet($_GET['setup_sx'],$_GET['setup_sy']))
		{
			$mode = "checkplanet";
		}		
		else
		{
			echo "Leider konnte kein geeigneter Planet in diesem Sektor gefunden werden.<br/>
			Bitte wähle einen anderen Sektor!<br/><br/>";
			$mode = "choosesector";	
		}		
	}
	
	elseif ($s['user']['race_id']>0 && !$c)
	{
		$mode = "choosesector";	
	}
	elseif (isset($_POST['submit_setup1']) && $_POST['register_user_race_id']>0 && checker_verify())
	{
		$cu->setRace($_POST['register_user_race_id']);
		$mode = "choosesector";	
	}
	elseif ($s['user']['race_id']==0)
	{
		$mode = "race";
	}
	
	if ($mode=="checkplanet")
	{
		echo "<form action=\"?\" method=\"post\">";
		checker_init();

		echo "<h2>Planetenwahl bestätigen</h2>";
		$tp = new Planet($pid);
		$p_img = IMAGE_PATH."/".IMAGE_PLANET_DIR."/planet".$tp->image."_middle.gif";

		echo "<input type=\"hidden\" name=\"choosenplanetid\" value=\"".$pid."\" />";
		echo "Folgender Planet wurde für Euch ausgewählt:<br/><br/>
		<table class=\"tb\" style=\"width:300px;\">";
		echo "<tr><th>Koordinaten:</th><td>".$tp->getString()."</td></tr>";
		echo "<tr>
			<th>Sonnentyp:</th>
			<td>".$tp->type->name."</td></tr>";
		echo "<tr>
			<th>Planettyp:</th>
			<td>".$tp->sol->type->name."</td></tr>";
		echo "<tr>
			<th>Felder:</td>
			<td>".$tp->fields." total</td></tr>";
		echo "<tr>
			<th>Temperatur:</td>
			<td>".$tp->temp_from."&deg;C bis ".$tp->temp_to."&deg;C";
		echo "</td></tr>";		
		echo "<tr><th>Ansicht:</th><td style=\"background:#000;text-align:center;\"><img src=\"$p_img\" style=\"border:none;\" alt=\"planet\" /></td></tr>
		</table><br/>
		<input type=\"submit\" name=\"submit_chooseplanet\" value=\"Auswählen\" />
		<input type=\"submit\" name=\"redo\" value=\"Einen neuen Planeten auswählen\" />";
		echo "</form>";
	}	
	elseif ($mode=="choosesector")
	{
		echo "<form action=\"?\" method=\"post\">";
		checker_init();
		echo "<h2>Heimatsektor auswählen</h2>";
		

		echo "Anzeigen: <select onchange=\"document.getElementById('img').src='misc/map.image.php'+this.options[this.selectedIndex].value;\">
		<option value=\"?t=".time()."\">Normale Galaxieansicht</option>
		<option value=\"?type=populated&t=".time()."\">Bev&ouml;lkerte Systeme</option>
		
		</select><br/><br/>";
		echo "<img src=\"misc/map.image.php\" alt=\"Galaxiekarte\" id=\"img\" usemap=\"#Galaxy\" style=\"border:none;\"/>";
		
		echo "<map name=\"Galaxy\">\n";
		$sec_x_size=GALAXY_MAP_WIDTH/$sx_num;
		$sec_y_size=GALAXY_MAP_WIDTH/$sy_num;
		$xcnt=1;
		$ycnt=1;
		for ($x=0;$x<GALAXY_MAP_WIDTH;$x+=$sec_x_size)
		{
		 	$ycnt=1;
			for ($y=0;$y<GALAXY_MAP_WIDTH;$y+=$sec_y_size)
			{
				$res = dbquery("
				SELECT
					COUNT(cell_id),
					SUM(cell_solsys_num_planets) 
				FROM
					space_cells
				WHERE
					cell_sx=".$xcnt."
					AND cell_sy=".$ycnt."
					AND cell_solsys_num_planets>0;
				;
				");
				$arr = mysql_fetch_row($res);
				$res = dbquery("
				SELECT
					COUNT(planet_id) 
				FROM
					space_cells
				INNER JOIN
					planets
					ON planet_solsys_id=cell_id
					AND	cell_sx=".$xcnt."
					AND cell_sy=".$ycnt."
					AND planet_user_id>0;
				");
				$arr2 = mysql_fetch_row($res);

				
				$tt = new Tooltip();
				$tt->addTitle("Sektor $xcnt/$ycnt");
				$tt->addText("Sternensysteme: ".$arr[0]);
				$tt->addText("Planeten: ".$arr[1]);
				$tt->addGoodCond("Bewohnte Planeten: ".$arr2[0]);
				$tt->addComment("Klickt hier um euren Heimatplaneten in Sektor <b>".$xcnt."/".$ycnt."</b> anzusiedeln!");
		  	echo "<area shape=\"rect\" coords=\"$x,".(GALAXY_MAP_WIDTH-$y).",".($x+$sec_x_size).",".(GALAXY_MAP_WIDTH-$y-$sec_y_size)."\" href=\"?setup_sx=".$xcnt."&amp;setup_sy=".$ycnt."\" alt=\"Sektor $xcnt / $ycnt\" ".$tt.">\n";
		  	$ycnt++;
		  }
		  $xcnt++;
		}
		echo "</map>\n";		
		
		
		echo "</form>";
	}
	elseif ($mode=="race")
	{
		echo "<form action=\"?\" method=\"post\">";
		checker_init();
		echo "<h2>Rasse auswählen</h2>
		Bitte wählt die Rasse eures Volkes aus.<br/>
		Jede Rasse hat Vor- und Nachteile sowie einige Spezialeinheiten:<br/><br/>";
	
		echo "<select name=\"register_user_race_id\" 
		onchange=\"showLoader('raceInfo');xajax_setupShowRace(this.options[this.selectedIndex].value)\"
		onkeyup=\"showLoader('raceInfo');xajax_setupShowRace(this.options[this.selectedIndex].value)\" >
		<option>Bitte wählen...</option>";
		$res = dbquery("
		SELECT
			race_id,
			race_name
		FROM
			races
		WHERE
			race_active=1
		ORDER BY
			race_name;
		");       
		while ($race = mysql_fetch_array($res))
		{
			echo "<option value=\"".$race['race_id']."\"";
			if ($race['race_id']==$raceId) 
			{
				echo " selected=\"selected\"";
			}
			echo ">".$race['race_name']."</option>";
		}
		echo "</select>";
	
		// xajax content will be placed in the following cell
		echo "<br/><br/><div id=\"raceInfo\">
		</div>";
		echo "</form>";
	}
	elseif ($mode=="finished")
	{
		echo "<h2>Einrichtung abgeschlossen</h2>";
		infobox_start("Willkommen");
		echo text2html($conf['welcome_message']['v']);
		infobox_end();
		echo '<input type="button" value="Zum Heimatplaneten" onclick="document.location=\'?page=planetoverview\'" />';
		if (!isset($s['allow_planet_change_counter']) || $s['allow_planet_change_counter']==0)
		{
			send_msg($s['user']['id'],USER_MSG_CAT_ID,'Willkommen',$conf['welcome_message']['v']);
		}
	}
	else
	{
		echo "Fehler";
	}

?>