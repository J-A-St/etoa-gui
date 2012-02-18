#! /usr/bin/php -q
<?PHP

	/////////////////////////////////////////////////
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
	// www.nicu.ch | mail@nicu.ch								 		//
	// als Maturaarbeit '04 am Gymnasium Oberaargau	//
	//////////////////////////////////////////////////
	// 
	// 	Dateiname: backup.php
	// 	Topic: Datenbank-Wiederherstellung
	// 	Autor: Nicolas Perrenoud alias MrCage
	// 	Erstellt: 01.12.2004
	// 	Bearbeitet von: Nicolas Perrenoud alias MrCage
	// 	Bearbeitet am: 07.03.2006
	// 	Kommentar: 	Diese Date erstellt ein Backup einer Datenbank mit dem Datum im Dateinamen

	// Gamepfad feststellen
	define('USE_HTML',false);

	// Gamepfad feststellen
	$grd = chdir(realpath(dirname(__FILE__)."/../"));

	// Initialisieren
	if (include("inc/bootstrap.inc.php"))
	{	
		if ($_SERVER['argv'][1]!="")
		{
			Backup::restore($_SERVER['argv'][1]);
		}
		else
		{
			echo "Usage: ".$_SERVER['argv'][0]." restorepoint\n\n";
			echo "Available restorepoints:\n\n";
			$dates = Backup::listImages();
			foreach ($dates as $f)
			{
				echo "$f\n";
			}
		}
	}
	else
	{
		throw new EException("Could not load bootstrap file ".getcwd()."/inc/bootstrap.inc.php\n");
	}
?>