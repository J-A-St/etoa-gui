<?PHP
	class DBException extends Exception
	{
		public function __toString()
		{
			global $cu;

			$str = "Datenbankfehler\nDatei: ".parent::getFile().", Zeile: ".parent::getLine()."\nAbfrage:".parent::getMessage()."\nFehlermeldung: ".mysql_error()."\nStack-Trace: ".parent::getTraceAsString()."";

			if (defined('ERROR_LOGFILE'))
			{
				if (!file_exists(DBERROR_LOGFILE))
				{
					touch(DBERROR_LOGFILE);
					chmod(DBERROR_LOGFILE,0662);
				}
				$f = fopen(DBERROR_LOGFILE,"a+");
				fwrite($f,date("d.m.Y H:i:s").", ".(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR']:'local').", ".$cu."\n".$str."\n\n");
				fclose($f);
			}

			if (!(defined('ETOA_DEBUG') && ETOA_DEBUG==1))
				return "<div class=\"errorBox\" style=\"text-align:left;\"><h2>Datenbankfehler</h2>Die gewünschte Abfrage konnte nicht durchgeführt werden!<br/>
					Bitte versuchen Sie es später nochmals und <a href=\"".DEVCENTER_PATH."\" onclick=\"".DEVCENTER_ONCLICK.";return false;\">melden</a> Sie diesen Fehler falls er weiterhin auftritt!</div>";

			if (!defined('USE_HTML') || USE_HTML)
			{
				if (!headers_sent())
				{
					$str = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">	
					<head><title>Datebankfehler</title><link rel="stylesheet" type="text/css" href="'.RELATIVE_ROOT.'css/simple.css" /></head><body>
					<div><img src="'.RELATIVE_ROOT.'images/game_logo.jpg" alt="Logo" /></div>';
				}
				else
					$str = "";
				$str.= "<div class=\"errorBox\" style=\"text-align:left;\"><h2>Datenbankfehler</h2>
				<b>Datei:</b> ".parent::getFile().", <b>Zeile:</b> ".parent::getLine()."<br/>
				<b>Abfrage:</b> ".nl2br(parent::getMessage())."<br/>
				<b>Fehlermeldung:</b> ".nl2br(mysql_error())."<br/>				";
				$str.="<div style=\"text-align:left;border-top:1px solid #000;\">
				<b>Stack-Trace:</b><br/>".nl2br(parent::getTraceAsString())."<br/>";
				if (defined('BUGREPORT_URL'))
					$str.="<a href=\"".BUGREPORT_URL."\" target=\"_blank\">Fehler melden</a>";
				$str.="</div>
				<br/>
				<a href=\"http://dev.etoa.ch\" target=\"_blank\">Fehler melden</a>
				</div>";		
				if (!headers_sent())
				{
					$str .= "</body></html>";
				}
				
				return $str;
			}
			return $str;
		}
	}


?>