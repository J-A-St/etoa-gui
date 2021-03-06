<?PHP
	//////////////////////////////////////////////////
	//		 	 ____    __           ______       			//
	//			/\  _`\ /\ \__       /\  _  \      			//
	//			\ \ \L\_\ \ ,_\   ___\ \ \L\ \     			//
	//			 \ \  _\L\ \ \/  / __`\ \  __ \    			//
	//			  \ \ \L\ \ \ \_/\ \L\ \ \ \/\ \   			//
	//	  		 \ \____/\ \__\ \____/\ \_\ \_\  			//
	//			    \/___/  \/__/\/___/  \/_/\/_/  	 		//
	//																					 		//
	//////////////////////////////////////////////////
	// The Andromeda-Project-Browsergame				 		//
	// Ein Massive-Multiplayer-Online-Spiel			 		//
	// Programmiert von Nicolas Perrenoud				 		//
	// als Maturaarbeit '04 am Gymnasium Oberaargau	//
	// www.etoa.ch | mail@etoa.ch								 		//
	//////////////////////////////////////////////////
	//
	//
	
	$umod = false;

		//
		// Urlaubsmodus einschalten
		//
		
		if (isset($_POST['hmod_on']) && checker_verify())
		{
			if ($cu->activateUmode())
			{
				success_msg("Du bist nun im Urlaubsmodus bis [b]".df(time())."[/b].");
                $cu->addToUserLog("settings","{nick} ist nun im Urlaub.",1);
                $umod = true;
			}
			else
			{
                error_msg("Es sind noch Flotten unterwegs!");
			}
		}
	
		//
		// Urlaubsmodus aufheben
		//
	
		if (isset($_POST['hmod_off']) && checker_verify())
		{
            if($cu->deleted == 0 && $cu->removeUmode()) {
				foreach ($planets as $pid) {
					BackendMessage::updatePlanet($pid);
				}

				success_msg("Urlaubsmodus aufgehoben! Denke daran, auf allen deinen Planeten die Produktion zu überprüfen!");
				$cu->addToUserLog("settings","{nick} ist nun aus dem Urlaub zurück.",1);
				
				echo '<input type="button" value="Zur Übersicht" onclick="document.location=\'?page=overview\'" />';
			}
			else
			{
				error_msg("Urlaubsmodus kann nicht aufgehoben werden!");
			}
		}
	
		//
		// Löschbestätigung
		//
		elseif (isset($_POST['remove']) && checker_verify())
		{
				echo "<form action=\"?page=$page&amp;mode=misc\" method=\"post\">";
	    	iBoxStart("Löschung bestätigen");
				echo "Soll dein Account wirklich zur Löschung vorgeschlagen werden?<br/><br/>";
				echo "<b>Passwort eingeben:</b> <input type=\"password\" name=\"remove_password\" value=\"\" />";
				iBoxEnd();
				echo "<input type=\"button\" value=\"Abbrechen\" onclick=\"document.location='?page=$page&mode=misc'\" /> 
				<input type=\"submit\" name=\"remove_submit\" value=\"Account l&ouml;schen\" />";
				echo "</form>";
		}

		//
		// User löschen
		//	
		elseif (isset($_POST['remove_submit']))
		{
			if ($cu->deleteRequest($_POST['remove_password']))
            {
				$s=Null;
				session_destroy();
				success_msg("Deine Daten werden am ".df(time() + ($conf['user_delete_days']['v']*3600*24))." Uhr von unserem System gelöscht! Wir w&uuml;nschen weiterhin viel Erfolg im Netz!");
				$cu->activateUmode(true);
				$cu->addToUserLog("settings","{nick} hat seinen Account zur Löschung freigegeben.",1);
				echo '<input type="button" value="Zur Startseite" onclick="document.location=\''.getLoginUrl().'\'" />';
			}
			else
			{
				error_msg("Falsches Passwort!");
				echo '<input type="button" value="Weiter" onclick="document.location=\'?page=userconfig&mode=misc\'" />';
			}
		}

		//
		// Löschantrag aufheben
		//
		elseif (isset($_POST['remove_cancel']) && checker_verify())
		{
		    $cu->revokeDelete();
		    success_msg("Löschantrag aufgehoben!");
			$cu->addToUserLog("settings","{nick} hat seine Accountlöschung aufgehoben.",1);
			echo '<input type="button" value="Weiter" onclick="document.location=\'?page=userconfig&mode=misc\'" />';
		}

		//
		// Auswahl
		//
		else
		{
			echo "<form action=\"?page=$page&amp;mode=misc\" method=\"post\">";		
	    	checker_init();
	    	tableStart("Sonstige Accountoptionen");
			
	    	// Urlaubsmodus
            if ($cu->deleted == 0) {
                echo "<tr><th style=\"width:150px;\">Urlaubsmodus</th>
                <td>Im Urlaubsmodus kannst du nicht angegriffen werden, aber deine Produktion steht auch still. </br> Dauer: mindestens ".MIN_UMOD_TIME." Tage, nach ".MAX_UMOD_TIME." Tagen Urlaubsmodus wird der Account inaktiv und kann wieder angegriffen werden.</td>
                <td>";

                if ($cu->hmode_from>0 && $cu->hmode_from<time() && $cu->hmode_to<time())
                {
                    echo "<input type=\"submit\" style=\"color:#0f0\" name=\"hmod_off\" value=\"Urlaubsmodus deaktivieren\" />";
                }
                elseif ($cu->hmode_from>0 && $cu->hmode_from<time() && $cu->hmode_to>=time() || $umod)
                {
                  echo "<span style=\"color:#f90\">Urlaubsmodus ist aktiv bis mindestens <b>".df($cu->hmode_to)."</b>!</span>";
                }
                else
                {
                  echo "<input type=\"submit\" value=\"Urlaubsmodus aktivieren\" name=\"hmod_on\" onclick=\"return confirm('Soll der Urlaubsmodus wirklich aktiviert werden?')\" />";
                }
                echo "</td></tr>";
            }
            else {
                echo "<tr><th style=\"width:150px;\">Urlaubsmodus</th>
                <td>Um den Urlaubsmodus zu beenden, musst du erst die Accountlöschung aufheben</td>
                <td>";
                echo "</td></tr>";
            }
	
			// Account löschen
	    	echo "<tr><th>Account l&ouml;schen</th>
	    	<td>Hier kannst du deinen Account mitsamt aller Daten löschen. Dafür wird der Account automatisch in den Urlaubsmodus gesetzt und nach ".$conf['user_delete_days']['v']." Tagen gelöscht.</td>
	    	<td>";
	    	if ($cu->deleted>0)
	    	{
	    		echo "<input type=\"submit\" name=\"remove_cancel\" value=\"Löschantrag aufheben\"  style=\"color:#0f0\" />";
	    	}
	    	else
	    	{
	    		echo "<input type=\"submit\" name=\"remove\" value=\"Account l&ouml;schen\" />";
	    	}
	    	echo "</td></tr>";
	    	
			tableEnd();
			echo "</form>";
		}
	
?>