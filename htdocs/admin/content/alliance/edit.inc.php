<?PHP
	
		if (isset($_GET['alliance_id']))
			$id = $_GET['alliance_id'];
		if (isset($_GET['id']))
			$id = $_GET['id'];
		
		
		if (isset($_POST['info_save']) && $_POST['info_save']!="")
		{
			//  Bild löschen wenn nötig
			$img_sql="";
			if (isset($_POST['alliance_img_del']))
			{
				$res = dbquery("SELECT alliance_img FROM alliances WHERE alliance_id=".$id.";");
				if (mysql_num_rows($res)>0)
				{
					$arr=mysql_fetch_array($res);
					if (file_exists('../'.ALLIANCE_IMG_DIR."/".$arr['alliance_img']))
					{
						unlink('../'.ALLIANCE_IMG_DIR."/".$arr['alliance_img']);
					}
					$img_sql=",alliance_img=''";
				}
			}
			
			// Daten speichern
			dbquery("
			UPDATE 
				alliances 
			SET 
				alliance_name='".$_POST['alliance_name']."',
				alliance_tag='".$_POST['alliance_tag']."',
				alliance_text='".addslashes($_POST['alliance_text'])."',
				alliance_application_template='".addslashes($_POST['alliance_application_template'])."',
				alliance_url='".$_POST['alliance_url']."',
				alliance_founder_id='".$_POST['alliance_founder_id']."' 
				".$img_sql."
			WHERE 
				alliance_id='".$id."'
			;");
		}
		elseif (isset($_POST['member_save']) && $_POST['member_save']!="")
		{
			// Mitgliederänderungen
			if (isset($_POST['member_kick']) && count($_POST['member_kick'])>0)
				foreach($_POST['member_kick'] as $k=>$v)
					dbquery("UPDATE 
						users
					SET
						user_alliance_id=0,
						user_alliance_rank_id=0
					WHERE 
						user_id='$k';");
			if (count($_POST['member_rank'])>0)
				foreach($_POST['member_rank'] as $k=>$v)
					dbquery("UPDATE 
						users 
					SET 
						user_alliance_rank_id=$v 
					WHERE 
						user_id='$k';");				
			// Ränge speichern
			if (isset($_POST['rank_del']) && count($_POST['rank_del'])>0)
				foreach($_POST['rank_del'] as $k=>$v)
				{
					dbquery("DELETE FROM alliance_ranks WHERE rank_id='$k';");
					dbquery("DELETE FROM alliance_rankrights WHERE rr_rank_id='$k';");
				}
			if (count($_POST['rank_name'])>0)
				foreach($_POST['rank_name'] as $k=>$v)
					dbquery("UPDATE 
						alliance_ranks 
					SET 
						rank_name='".addslashes($v)."',
						rank_level='".$_POST['rank_level'][$k]."' 
					WHERE 
						rank_id='$k';");
		}
		elseif (isset($_POST['bnd_save']) && $_POST['bnd_save']!="")
		{
			// Bündnisse / Kriege speichern
			if (isset($_POST['alliance_bnd_del']) && count($_POST['alliance_bnd_del'])>0)
				foreach($_POST['alliance_bnd_del'] as $k=>$v)
					dbquery("DELETE FROM alliance_bnd WHERE alliance_bnd_id='$k';");
			if (count($_POST['alliance_bnd_level'])>0)
			{
				foreach($_POST['alliance_bnd_level'] as $k=>$v)
				{
					dbquery("UPDATE 
						alliance_bnd 
					SET 						
						alliance_bnd_level='".$_POST['alliance_bnd_level'][$k]."',
						alliance_bnd_name='".$_POST['alliance_bnd_name'][$k]."' 
					WHERE 
						alliance_bnd_id='$k';");
				}
			}
		}
		elseif (isset($_POST['res_save']) && $_POST['res_save']!="")
		{
			dbquery("
					UPDATE
						alliances
					SET
						alliance_res_metal='".nf_back($_POST['res_metal'])."',
						alliance_res_crystal='".nf_back($_POST['res_crystal'])."',
						alliance_res_plastic='".nf_back($_POST['res_plastic'])."',
						alliance_res_fuel='".nf_back($_POST['res_fuel'])."',
						alliance_res_food='".nf_back($_POST['res_food'])."',
						alliance_res_metal=alliance_res_metal+'".nf_back($_POST['res_metal_add'])."',
						alliance_res_crystal=alliance_res_crystal+'".nf_back($_POST['res_crystal_add'])."',
						alliance_res_plastic=alliance_res_plastic+'".nf_back($_POST['res_plastic_add'])."',
						alliance_res_fuel=alliance_res_fuel+'".nf_back($_POST['res_fuel_add'])."',
						alliance_res_food=alliance_res_food+'".nf_back($_POST['res_food_add'])."'
					WHERE
						alliance_id='".$id."'
					LIMIT 1;");
		}
		
		$res = dbquery("SELECT * FROM alliances WHERE alliance_id='".$id."';");
		$arr = mysql_fetch_assoc($res);
		
		echo "<h2>Details <span style=\"color:#0f0;\">[".$arr['alliance_tag']."] ".$arr['alliance_name']."</span></h2>";
		echo "<div id=\"test\">&nbsp;</div>";
		echo "<form action=\"?page=$page&amp;sub=edit&amp;id=".$id."\" method=\"post\">
				<input type=\"hidden\" id=\"tabactive\" name=\"tabactive\" value=\"\" />";
		
		
		$ures = dbquery("SELECT 
							user_id,
							user_nick,
							user_points,
							user_alliance_rank_id
						FROM 
							users 
						WHERE 
							user_alliance_id=".$id." 
						ORDER BY 
							user_points DESC,
							user_nick;");
		$members = array();
		if (mysql_num_rows($ures)>0)
		{
			while($uarr=mysql_fetch_array($ures))
			{
				$members[$uarr['user_id']] = $uarr;
			}
		}
		$rres = dbquery("
				SELECT 
					rank_id,
					rank_level,
					rank_name
				FROM 
					alliance_ranks 
				WHERE 
					rank_alliance_id=".$id." 
				ORDER BY 
					rank_level DESC;");
		$ranks = array();
		if (mysql_num_rows($rres)>0)
		{
			while($rarr=mysql_fetch_array($rres))
			{
				$ranks[$rarr['rank_id']] = $rarr;
			}
		}

						
				
		$tc = new TabControl("userTab",array(
			"Info",
			"Mitglieder",
			"Krieg/BND",
			"Geschichte",
			"Rohstoffe",
			"Einzahlungen",
			"Gebäude",
			"Technologien"
			),
			0,
			'100%',
			0
			);
			
			/**
			* Info
			*/								
			$tc->open();
			echo "<form action=\"?page=$page&sub=base\" method=\"post\">";
			tableStart();
			echo "<tr><th>ID</th><td>".$arr['alliance_id']."</td></tr>";
			echo "<tr><th>[Tag] Name</th><td>
					[<input type=\"text\" name=\"alliance_tag\" value=\"".$arr['alliance_tag']."\" size=\"6\" maxlength=\"6\" />]
					<input type=\"text\" name=\"alliance_name\" value=\"".$arr['alliance_name']."\" size=\"30\" maxlength=\"25\" />
				</td></tr>";					
			echo "<tr><th>Gr&uuml;nder</th><td><select name=\"alliance_founder_id\">";
			echo "<option value=\"0\">(niemand)</option>";
			foreach ($members as $uid=>$uarr)
			{
				echo "<option value=\"$uid\"";
				if ($arr['alliance_founder_id']==$uarr['user_id']) 
					echo " selected=\"selected\"";
				echo ">".$uarr['user_nick']."</option>";
			}			
			echo "</select></td></tr>";				
			echo "<tr><th>Text</th><td><textarea cols=\"45\" rows=\"10\" name=\"alliance_text\">".stripslashes($arr['alliance_text'])."</textarea></td></tr>";
			echo "<tr><th>Gr&uuml;ndung</th><td>".date("Y-m-d H:i:s",$arr['alliance_foundation_date'])."</td></tr>";
			echo "<tr><th>Website</th><td><input type=\"text\" name=\"alliance_url\" value=\"".$arr['alliance_url']."\" size=\"40\" maxlength=\"250\" /></td></tr>";
			echo "<tr><th>Bewerbungsvorlage</th><td><textarea cols=\"45\" rows=\"10\" name=\"alliance_application_template\">".stripslashes($arr['alliance_application_template'])."</textarea></td></tr>";
			echo "<tr><th>Bild</th><td>";
			if ($arr['alliance_img']!="")
			{
	        	echo '<img src="'.ALLIANCE_IMG_DIR.'/'.$arr['alliance_img'].'" alt="Profil" /><br/>';
	        	echo "<input type=\"checkbox\" value=\"1\" name=\"alliance_img_del\"> Bild l&ouml;schen<br/>";
			}
			else
			{
	      		echo "Keines";
			}
			echo "</td></tr>";
			echo "<tr>
					<td style=\"text-align:center;\" colspan=\"2\">
						<input type=\"submit\" name=\"info_save\" value=\"&Uuml;bernehmen\" />
					</td>
				</tr>";
			echo "</table>";
			echo "</form>";
			$tc->close();
					
			/*
			* Mitglieder
			**/
			$tc->open();
			tableStart();
			echo "<tr>
					<th>Mitglieder</th>
				<td>";
			if (count($members)>0)
			{
				echo "<table class=\"tb\">
					<tr>
						<th>Name</th>
						<th>Punkte</th>
						<th>Rang</th>
						<th>Mitgliedschaft beenden</th></tr>";
					foreach ($members as $uid => $uarr)
					{
						echo "<tr>
						<div id=\"uifo".$uarr['user_id']."\" style=\"display:none;\"><a href=\"?page=user&amp;sub=edit&amp;id=".$uarr['user_id']."\">Daten</a><br/>
						".popupLink("sendmessage","Nachricht senden","","id=".$uarr['user_id'])."</div>
						<td><a href=\"?page=user&amp;sub=edit&amp;id=".$uarr['user_id']."\" ".cTT($uarr['user_nick'],"uifo".$uarr['user_id']."").">".$uarr['user_nick']."</a></td>
						<td>".nf($uarr['user_points'])." Punkte</td>
						<td><select name=\"member_rank[$uid]\"><option value=\"0\">-</option>";
						foreach ($ranks as $k=>$v)
						{
							echo "<option value=\"$k\"";
							if ($uarr['user_alliance_rank_id']==$k)
								echo " selected=\"selected\"";
							echo ">".$v['rank_name']."</option>";
						}						
						echo "</select></td>";
						echo "<td><input type=\"checkbox\" name=\"member_kick[".$uid."]\" value=\"1\" /></td></tr>";
					}
					echo "</table>";
				}
				else
					echo "<b>KEINE MITGLIEDER!</b>";
				echo "</td></tr>";
				echo "<tr><th>R&auml;nge</th><td>";
				
				if (count($ranks)>0)
				{
					echo "<table class=\"tb\">";
					echo "<tr><th>Name</th><th>Level</th><th>L&ouml;schen</th></tr>";
					foreach($ranks as $rid => $rarr)
					{
						echo "<tr><td><input type=\"text\" size=\"35\" name=\"rank_name[".$rarr['rank_id']."]\" value=\"".$rarr['rank_name']."\" /></td>";
						echo "<td><select name=\"rank_level[".$rarr['rank_id']."]\">";
						for($x=0;$x<=9;$x++)
						{
							echo "<option value=\"$x\"";
							if ($rarr['rank_level']==$x) echo " selected=\"selected\"";
							echo ">$x</option>";
						}
						echo "</select></td>";
						echo "<td><input type=\"checkbox\" name=\"rank_del[".$rarr['rank_id']."]\" value=\"1\" /></td></tr>";
					}
					echo "</table>";
				}
				else
					echo "<b>Keine R&auml;nge vorhanden!</b>";
				echo "</td></tr>";
				echo "<tr>
					<td style=\"text-align:center;\" colspan=\"2\">
						<input type=\"submit\" name=\"member_save\" value=\"&Uuml;bernehmen\" />
					</td>
				</tr>";
				tableEnd();
				$tc->close();
						
				/*
				* Krieg/Bündnisse
				*/
				$tc->open();
				tableStart();
				echo "<tr><th>B&uuml;ndnisse/Kriege</th><td>";
				$bres = dbquery("
				SELECT 
					alliance_bnd_id,
					alliance_bnd_alliance_id1 as a1id,
					alliance_bnd_alliance_id2 as a2id,
					a1.alliance_name as a1name,
					a2.alliance_name as a2name,
					alliance_bnd_level as lvl,
					alliance_bnd_name as name,
					alliance_bnd_date as date
				FROM 
					alliance_bnd 
				LEFT JOIN
					alliances a1 on alliance_bnd_alliance_id1 = a1.alliance_id
				LEFT JOIN
					alliances a2 on alliance_bnd_alliance_id2 = a2.alliance_id
				WHERE 
					alliance_bnd_alliance_id1=".$arr['alliance_id']."
					OR alliance_bnd_alliance_id2=".$arr['alliance_id']."
				ORDER BY 
					alliance_bnd_level DESC,
					alliance_bnd_date DESC;");
				if (mysql_num_rows($bres)>0)
				{
					echo "<table style=\"width:100%\">";
					echo "<tr>
					<th>Allianz</th>
					<th>Bezeichnung</th>
					<th>Status / Datum</th>
					<th>L&ouml;schen</th></tr>";
					while($barr=mysql_fetch_array($bres))
					{
						$opId = ($id==$barr['a2id']) ? $barr['a1id'] : $barr['a2id'];
						$opName = ($id==$barr['a2id']) ? $barr['a1name'] : $barr['a2name'];
						echo "<tr>
							<td><a href=\"?page=alliances&amp;action=edit&amp;id=".$opId."\">".$opName."</a></td>
							<td><input type=\"text\" value=\"".$barr['name']."\" name=\"alliance_bnd_name[".$barr['alliance_bnd_id']."]\" /></td>";
						echo "<td>
						<select name=\"alliance_bnd_level[".$barr['alliance_bnd_id']."]\">";
						echo "<option value=\"0\">Bündnisanfrage</option>";
						echo "<option value=\"2\"";
						if ($barr['lvl']==2) echo " selected=\"selected\"";
						echo ">B&uuml;ndnis</option>";
						echo "<option value=\"3\"";
						if ($barr['lvl']==3) echo " selected=\"selected\"";
						echo ">Krieg</option>";
						echo "<option value=\"3\"";
						if ($barr['lvl']==4) echo " selected=\"selected\"";
						echo ">Frieden</option>";
						echo "</select>";
						echo " &nbsp; ".df($barr['date'])."</td>";
						echo "<td valign=\"top\"><input type=\"checkbox\" name=\"alliance_bnd_del[".$barr['alliance_bnd_id']."]\" value=\"1\" /></td></tr>";
					}
					echo "</table>";
				}
				else
					echo "<b>Keine B&uuml;ndnisse/Kriege vorhanden!</b>";
				echo "</td></tr>";
				echo "<tr>
					<td style=\"text-align:center;\" colspan=\"2\">
						<input type=\"submit\" name=\"bnd_save\" value=\"&Uuml;bernehmen\" />
					</td>
				</tr>";
				tableEnd();
				$tc->close();

					
			/**
			* Geschichte
			*/								
			$tc->open();
			tableStart();
			echo "<tr>
					<th style=\"width:120px;\">Datum / Zeit</th>
					<th>Ereignis</th
				></tr>";
			$hres=dbquery("
						SELECT 
							* 
						FROM 
							alliance_history 
						WHERE 
							history_alliance_id=".$arr['alliance_id']." 
						ORDER BY 
							history_timestamp
						DESC;");
			if (mysql_num_rows($hres)>0)
			{
				while ($harr=mysql_fetch_array($hres))
				{
					echo "<tr><td>".date("d.m.Y H:i",$harr['history_timestamp'])."</td><td class=\"tbldata\">".text2html($harr['history_text'])."</td></tr>";
				}				
			}
			else
			{
				echo "<tr><td colspan=\"3\" class=\"tbldata\"><i>Keine Daten vorhanden!</i></td></tr>";
			}
			tableEnd();
			$tc->close();
				
			/**
			* Rohstoffe
			*/								
			$tc->open();
			tableStart("Rohstoffe");
			echo "<tr>
					<th class=\"resmetalcolor\">Titan</th>
					<td>
						<input type=\"text\" name=\"res_metal\" id=\"res_metal\" value=\"".nf($arr['alliance_res_metal'])."\" size=\"12\" maxlength=\"20\" autocomplete=\"off\" onfocus=\"this.select()\" onclick=\"this.select()\" onkeyup=\"FormatNumber(this.id,this.value,'','','');\" onkeypress=\"return nurZahlen(event)\"/><br/>
					+/-: <input type=\"text\" name=\"res_metal_add\" id=\"res_metal_add\" value=\"0\" size=\"8\" maxlength=\"20\" autocomplete=\"off\" onfocus=\"this.select()\" onclick=\"this.select()\" onkeyup=\"FormatNumber(this.id,this.value,'','','');\" onkeypress=\"return nurZahlen(event)\"/></td>";
			echo "<th class=\"rescrystalcolor\">Silizium</th>
					<td><input type=\"text\" name=\"res_crystal\" id=\"res_crystal\" value=\"".nf($arr['alliance_res_crystal'])."\" size=\"12\" maxlength=\"20\" autocomplete=\"off\" onfocus=\"this.select()\" onclick=\"this.select()\" onkeyup=\"FormatNumber(this.id,this.value,'','','');\" onkeypress=\"return nurZahlen(event)\"/><br/>
					+/-: <input type=\"text\" name=\"res_crystal_add\" id=\"res_crystal_add\" value=\"0\" size=\"8\" maxlength=\"20\" autocomplete=\"off\" onfocus=\"this.select()\" onclick=\"this.select()\" onkeyup=\"FormatNumber(this.id,this.value,'','','');\" onkeypress=\"return nurZahlen(event)\"/></td></tr>";
			echo "<tr><th class=\"resplasticcolor\">PVC</th>
					<td><input type=\"text\" name=\"res_plastic\" id=\"res_plastic\" value=\"".nf($arr['alliance_res_plastic'])."\" size=\"12\" maxlength=\"20\" autocomplete=\"off\" onfocus=\"this.select()\" onclick=\"this.select()\" onkeyup=\"FormatNumber(this.id,this.value,'','','');\" onkeypress=\"return nurZahlen(event)\"/><br/>
					+/-: <input type=\"text\" name=\"res_plastic_add\" id=\"res_plastic_add\" value=\"0\" size=\"8\" maxlength=\"20\" autocomplete=\"off\" onfocus=\"this.select()\" onclick=\"this.select()\" onkeyup=\"FormatNumber(this.id,this.value,'','','');\" onkeypress=\"return nurZahlen(event)\"/></td>";
			echo "<th class=\"resfuelcolor\">Tritium</th>
					<td><input type=\"text\" name=\"res_fuel\" id=\"res_fuel\" value=\"".nf($arr['alliance_res_fuel'])."\" size=\"12\" maxlength=\"20\" autocomplete=\"off\" onfocus=\"this.select()\" onclick=\"this.select()\" onkeyup=\"FormatNumber(this.id,this.value,'','','');\" onkeypress=\"return nurZahlen(event)\"/><br/>
					+/-: <input type=\"text\" name=\"res_fuel_add\" id=\"res_fuel_add\" value=\"0\" size=\"8\" maxlength=\"20\" autocomplete=\"off\" onfocus=\"this.select()\" onclick=\"this.select()\" onkeyup=\"FormatNumber(this.id,this.value,'','','');\" onkeypress=\"return nurZahlen(event)\"/></td></tr>";
			echo "<tr><th class=\"resfoodcolor\">Nahrung</th>
					<td><input type=\"text\" name=\"res_food\" id=\"res_food\" value=\"".nf($arr['alliance_res_food'])."\" size=\"12\" maxlength=\"20\" autocomplete=\"off\" onfocus=\"this.select()\" onclick=\"this.select()\" onkeyup=\"FormatNumber(this.id,this.value,'','','');\" onkeypress=\"return nurZahlen(event)\"/><br/>
					+/-: <input type=\"text\" name=\"res_food_add\" id=\"res_food_add\" value=\"0\" size=\"8\" maxlength=\"20\" autocomplete=\"off\" onfocus=\"this.select()\" onclick=\"this.select()\" onkeyup=\"FormatNumber(this.id,this.value,'','','');\" onkeypress=\"return nurZahlen(event)\"/></td><td colspan=\"2\">";
			echo "<tr>
					<td style=\"text-align:center;\" colspan=\"4\">
						<input type=\"submit\" name=\"res_save\" value=\"Übernehmen\" />
					</td>
				</tr>";
			tableEnd();
			$tc->close();
					
			/**
			* Einzahlungen
			*/								
			$tc->open();
  			
			echo "<form id=\"filterForm\">";
			tableStart("Filter");
			echo "<tr>
  					<th>Ausgabe:</th>
  					<td>
  						<input type=\"radio\" name=\"output\" id=\"output\" value=\"0\" checked=\"checked\"/> Einzeln / <input type=\"radio\" name=\"output\" id=\"output\" value=\"1\"/> Summiert
  					</td>
  				</tr><tr>
  					<th>Einzahlungen:</th>
  					<td> 
		  				<select id=\"limit\" name=\"limit\">
							<option value=\"0\" checked=\"checked\">alle</option>
							<option value=\"1\">die letzte</option>
							<option value=\"5\">die letzten 5</option>
							<option value=\"20\">die letzten 20</option>
						</select>
					</td>
				</tr><tr>
  					<th>Von User:</th>
  					<td>
  						<select id=\"user_spends\" name=\"user_spends\">
							<option value=\"0\">alle</option>";
					  	// Allianzuser
							foreach($members as $mid => $data)
							{
					  		echo "<option value=\"".$mid."\">".$data['user_nick']."</option>";
					  	}
  			echo 		"</select>
  					</td>
	  			</tr><tr>
	  				<td style=\"text-align:center;\" colspan=\"2\">
  						<input type=\"button\" onclick=\"xajax_showSpend(".$arr['alliance_id'].",xajax.getFormValues('filterForm'))\" value=\"Anzeigen\"\"/>
  					</td>
  				</tr>";
			 tableEnd();
			 echo "</form>";
			 
			 echo "<div id=\"spends\">&nbsp;</div>";

			$tc->close();
			
					
			/**
			* Gebäude
			*/								
			$tc->open();
			$res = dbquery("
						SELECT
							alliance_buildlist.*,
							alliance_buildings.alliance_building_name
						FROM
							alliance_buildlist
						INNER JOIN
							alliance_buildings
						ON
							alliance_buildings.alliance_building_id=alliance_buildlist.alliance_buildlist_building_id
							AND	alliance_buildlist_alliance_id='".$id."';");
			tableStart();
			echo "<tr>
					<th>Gebäude</th><th>Stuffe</th><th>Useranzahl</th><th>Status</th>
				</tr>";
			if (mysql_num_rows($res)>0)
			{
				while ($arr = mysql_fetch_assoc($res))
				{
					echo "<tr><td>".$arr['alliance_building_name']."</td><td>".$arr['alliance_buildlist_current_level']."</td><td>".$arr['alliance_buildlist_member_for']."</td><td>";
					if ($arr['alliance_buildlist_build_end_time']>time()) echo "Bauen";
					elseif ($arr['alliance_buildlist_build_end_time']>0) echo "Bau abgeschlossen";
					else echo "Untätig";
					echo "</td><td>".edit_button("javascript:;","document.getElementById(buildlist_".$arr['alliance_buildlist_id'].").style.display=''")."</td></tr>";
					echo "<tr id=\"buildlist_".$arr['alliance_buildlist_id']."\" style=\"display: none;\"><td>test</td></tr>";
				}
			}
			else
				echo "<tr><td colspan=\"4\">Keine Gebäude vorhanden!</td></tr>";
			tableEnd();
			$tc->close();
			
			/**
			* Technologien
			*/								
			$tc->open();
			$res = dbquery("
						SELECT
							alliance_techlist.*,
							alliance_technologies.alliance_tech_name
						FROM
							alliance_techlist
						INNER JOIN
							alliance_technologies
						ON
							alliance_technologies.alliance_tech_id=alliance_techlist.alliance_techlist_tech_id
							AND	alliance_techlist_alliance_id='".$id."';");
			tableStart();
			echo "<tr>
					<th>Gebäude</th><th>Stuffe</th><th>Useranzahl</th><th>Status</th>
				</tr>";
			if (mysql_num_rows($res)>0)
			{
				while ($arr = mysql_fetch_assoc($res))
				{
					echo "<tr><td>".$arr['alliance_tech_name']."</td><td>".$arr['alliance_techlist_current_level']."</td><td>".$arr['alliance_techlist_member_for']."</td><td>";
					if ($arr['alliance_buildlist_build_end_time']>time()) echo "Forschen";
					elseif ($arr['alliance_techlist_build_end_time']>0) echo "Forschen abgeschlossen";
					else echo "Untätig";
					echo "</td><td>".edit_button("javascript:;","document.getElementById(techlist_".$arr['alliance_techlist_id'].").style.display='block'")."</td></tr>";
				}
			}
			else
				echo "<tr><td colspan=\"4\">Keine Technologien vorhanden!</td></tr>";
			tableEnd();
			$tc->close();
			

?>