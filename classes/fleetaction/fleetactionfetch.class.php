<?PHP

	class FleetActionFetch extends FleetAction
	{

		function FleetActionFetch()
		{
			$this->code = "fetch";
			$this->name = "Waren abholen";
			$this->desc = "Fliegt zum Ziel und holt dort Waren ab.";
			$this->longDesc = "Die Transportflotte fliegt zu einem eigenen Ziel und holt dort die aufgelisteten Waren ab, falls sie dort vorhanden sind. Diese Aktion
			kann nur f�r Flotten, die auch Transporter beinhalten, ausgew�hlt werden.";
			$this->visible = true;
			$this->exclusive = false;								
			$this->attitude = 1;
			
			$this->allowPlayerEntities = false;
			$this->allowOwnEntities = true;
			$this->allowNpcEntities = false;
			$this->allowSourceEntity = false;
		}

		function startAction() {} 
		function cancelAction() {}		
		function targetAction() {} 
		function returningAction() {}		
		
	}

?>