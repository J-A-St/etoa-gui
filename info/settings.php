<?php
	echo "<h2>Einstellungen</h2>";
	Help::navi(array("Einstellungen","settings"));

	$item['game_name']['p1']="Spielversion";
	$item['enable_register']['p2']="Max. Spieler";
	$item['hmode_days']['v']="Urlaubsmodus Mindestdauer";
	$item['points_update']['p1']="Einheiten/Userpunkt";
	$item['points_update']['p2']="Userpunkte/Allypunk";
	$item['user_delete_days']['v']="Tage bis zur endgültigen Löschung eines Accounts";
	$item['user_inactive_days']['v']="Spieler werde inaktiv nach (in Tagen)";
	$item['user_inactive_days']['p1']="Löschung wegen Inaktivität nach (in Tagen)";
	$item['user_timeout']['v']="Timeout in Sekunden";

	$item['global_time']['v']="Globaler Bauzeitfaktor";
	$item['flight_start_time']['v']="Startzeitfaktor";
	$item['flight_land_time']['v']="Landezeitfaktor";
	$item['flight_flight_time']['v']="Flugzeitfaktor";
	$item['def_build_time']['v']="Verteidigungsbauzeitfaktor";
	$item['build_build_time']['v']="Gebäudebauzeitfaktor";
	$item['res_build_time']['v']="Forschungszeitfaktor";
	$item['ship_build_time']['v']="Schiffbauzeitfaktor";
	$item['planet_temp']['p1']="Minimale Planetentemperatur";
	$item['planet_temp']['p2']="Maximale Planetentemperatur";
	$item['planet_fields']['p1']="Minimale Feldanzahl";
	$item['planet_fields']['p2']="Maximale Feldanzahl";
	$item['num_planets']['p1']="Minimale Planetenanzahl";
	$item['num_planets']['p2']="Maximale Planetenanzahl";
	$item['user_max_planets']['v']="Max Planeten/User";

	$item['def_restore_percent']['v']="Verteidigungswiederherstellung";
	$item['def_wf_percent']['v']="Verteidigung ins Trümmerfeld";
	$item['ship_wf_percent']['v']="Schiffe ins Trümmerfeld";
	$item['user_attack_min_points']['v']="Noobschutz: Min Punkte";
	$item['user_attack_percentage']['v']="Noobschutz: Verhältnis %";
		
	$item['people_food_require']['v']="Nahrungsverbrauch pro Arbeiter";
	$item['people_multiply']['v']="Bevölkerungswachstum";

		infobox_start("Grundeinstellungen",1);
		echo "<tr><td class=\"tbltitle\">Name</td>";
		echo "<td class=\"tbltitle\">Wert</td></tr>";
		if (UNIX)
		{
			echo "<tr><td class=\"tbldata\">Revision</td>";
			echo "<td class=\"tbldata\">";
			passthru("svnversion");
			echo "</td></tr>";
			
		}
		foreach ($item as $conf_name => $a)
		{
			foreach ($a as $par => $val)
			{
			echo "<tr><td class=\"tbldata\">".$val."</td>";
			echo "<td class=\"tbldata\">".$conf[$conf_name][$par]."</td></tr>";
			}
		}
		infobox_end(1);
?>