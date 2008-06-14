<?PHP

	class FleetActionFlight extends FleetAction
	{

		function FleetActionFlight()
		{
			$this->code = "flight";
			$this->name = "Flug";
			$this->desc = "Fliegt zum Ziel, kehrt dort sofort um und fliegt wieder zurück.";
			
			$this->attitude = 0;
			
			$this->allowPlayerEntities = true;
			$this->allowOwnEntities = true;
			$this->allowNpcEntities = true;
			$this->allowSourceEntity = false;
		}

		function startAction() {} 
		function cancelAction() {}		
		function targetAction() {} 
		function returningAction() {}		
		
	}

?>