
#include "BattleHandler.h"

void BattleHandler::battle(Fleet* fleet, Entity* entity, Log* log)
{
	Config &config = Config::instance();
	std::time_t time = std::time(0);

    // BEGIN SKRIPT //
	alliancesHaveWar = 0;
	
	BattleReport * report = new BattleReport(fleet->getUserId(),
											 entity->getUserId(),
											 fleet->getEntityTo(),
											 fleet->getEntityFrom(),
											 fleet->getLandtime(),
											 fleet->getId());
	std::string users = fleet->getUserIds();
	report->setUser(users);
	size_t found=users.find_first_of(",");
	while (found!=std::string::npos) {
		users = users.substr(found+1);
		found=users.find_first_of(",");
		report->addUser((int)etoa::s2d(users.substr(0,found)));
	}
	
	users = entity->getUserIds();
	report->setEntityUser(users);
	found=users.find_first_of(",");
	while (found!=std::string::npos) {
		users = users.substr(found+1);
		found=users.find_first_of(",");
		report->addUser((int)etoa::s2d(users.substr(0,found)));
	}

   	// Kampf abbrechen falls User gleich 0
	if (entity->getUserId()==0
		|| (fleet->getLeaderId() > 0 && (fleet->fleetUser->getAllianceId()==entity->getUser()->getAllianceId() &&fleet->fleetUser->getAllianceId()!=0))
		|| (fleet->getLeaderId() == 0 && fleet->getUserId()==entity->getUserId())) {
		report->setSubtype("battlefailed");
		
		report->setResult(0);
		
		this->returnV = 4;
		this->returnFleet = true;
		
		log->addText("Action failed: Opponent error");
	}

  	// Kampf abbrechen und Flotte zum Startplanet schicken wenn Kampfsperre aktiv ist
  	else if ((int)config.nget("battleban",0)!=0 && (int)config.nget("battleban",1)<=time && (int)config.nget("battleban",2)>time) {
		report->setSubtype("battleban");	
		
		report->setResult(0);
		
		this->returnV = 4;
		this->returnFleet = true;

		log->addText("Action failed: Battleban error");
	}
	else {
		report->setSubtype("battle");
		My &my = My::instance();
		mysqlpp::Connection *con_ = my.get();

		mysqlpp::Query query = con_->query();

		// Prüft, ob Krieg herrscht
		this->alliancesHaveWar = false;
		if(fleet->fleetUser->getAllianceId()!=0 && entity->getUser()->getAllianceId()!=0)
		{
			query << "SELECT ";
			query << "	alliance_bnd_id ";
			query << "FROM ";
			query << "	alliance_bnd ";
			query << "WHERE ";
			query << "	(alliance_bnd_alliance_id1='" << fleet->fleetUser->getAllianceId() << "' ";
			query << "	AND alliance_bnd_alliance_id2='" << entity->getUser()->getAllianceId() << "') ";
			query << "OR ";
			query << "	(alliance_bnd_alliance_id1='" << entity->getUser()->getAllianceId() << "' ";
			query << "	AND alliance_bnd_alliance_id2='" << fleet->fleetUser->getAllianceId() << "') ";
			query << "	AND alliance_bnd_level='3';";
			RESULT_TYPE warCheckRes = query.store();
			query.reset();

			if (warCheckRes)
			{
				int warCheckSize = warCheckRes.size();

				if (warCheckSize > 0)
				{
					this->alliancesHaveWar = true;
				}
			}
		}
		
		//Report
		report->setShield(fleet->getShield(true));
		report->setStructure(fleet->getStructure(true));
		
		report->setEntityShield(entity->getShield(true));
		report->setEntityStructure(entity->getStructure(true));
		
		report->setWeaponTech(fleet->getWeaponTech());
		report->setShieldTech(fleet->getShieldTech());
		report->setStructureTech(fleet->getStructureTech());
		
		report->setEntityWeaponTech(entity->getWeaponTech());
		report->setEntityShieldTech(entity->getShieldTech());
		report->setEntityStructureTech(entity->getStructureTech());
		
		report->setShips(fleet->getShipString());
		report->setEntityShips(entity->getShipString());
		report->setEntityDef(entity->getDefString());
		
		//
		//Kampf Daten errechnen
		//
		//init... = wert vor dem kampf (wird nicht verändert) und c... aktueller Wert
		double initAttWeapon = fleet->getWeapon(true);
		double initDefWeapon = entity->getWeapon(true);

		double initAttStructure = fleet->getStructure(true);
		double initDefStructure = entity->getStructure(true);

		double initAttShield = fleet->getShield(true);
		double initDefShield = entity->getShield(true);

		//Schild + Strukturstärke
        double initAttStructureShield = fleet->getStructShield(true);
        double initDefStructureShield = entity->getStructShield(true);

		double cAttStructureShield = initAttStructureShield;
		double cDefStructureShield = initDefStructureShield;

		//
		//Der Kampf!
		//
        for (int bx = 1; bx <= config.nget("battle_rounds",0); bx++) {
			report->setRounds(bx);
			report->setWeapon(fleet->getWeapon(true));
			report->setCount(fleet->getCount(true));
			report->setEntityWeapon(entity->getWeapon(true));
			report->setEntityCount(entity->getCount(true));

            cAttStructureShield -= entity->getWeapon(true);
			cDefStructureShield -= fleet->getWeapon(true);

			cAttStructureShield = std::max(0.0,cAttStructureShield);
			cDefStructureShield = std::max(0.0,cDefStructureShield);

			attPercent = (cAttStructureShield==0) ? 0 : cAttStructureShield/initAttStructureShield;
			defPercent = (cDefStructureShield==0) ? 0 : cDefStructureShield/initDefStructureShield;

			fleet->setPercentSurvive(attPercent,true);
			entity->setPercentSurvive(defPercent,true);
			
			report->setHeal(fleet->getHeal());
			report->setEntityHeal(entity->getHeal());

            if (fleet->getHeal() > 0) {
                cAttStructureShield += fleet->getHeal();
                if (cAttStructureShield > initAttStructureShield)
                    cAttStructureShield = initAttStructureShield;
				
				fleet->setPercentSurvive(cAttStructureShield/initAttStructureShield,true);
            }

            if (entity->getHeal() > 0) {
                cDefStructureShield += entity->getHeal();
                if (cDefStructureShield > initDefStructureShield)
                    cDefStructureShield = initDefStructureShield;
				
				entity->setPercentSurvive(cDefStructureShield/initDefStructureShield,true);
            }
			
            if (cAttStructureShield <= 0 || cDefStructureShield <= 0)
                break;
        }

		//
		//Daten nach dem Kampf
		//

		//
		//überlebende Schiffe errechnen
		//

		//Erfahrung für die Spezialschiffe errechnen
        fleet->addExp(entity->getExp() / 100000);
        entity->addExp(fleet->getExp() / 100000);
		report->setExp(fleet->getAddedExp());
		report->setEntityExp(entity->getAddedExp());


		//Das entstandene Trümmerfeld erstellen/hochladen
		entity->addWfMetal(fleet->getWfMetal(true) + entity->getObjectWfMetal(true));
		entity->addWfCrystal(fleet->getWfCrystal(true) + entity->getObjectWfCrystal(true));
		entity->addWfPlastic(fleet->getWfPlastic(true) + entity->getObjectWfPlastic(true));

		//
		//Der Angreifer hat gewonnen!
		//
		std::vector<double> raid (5);
		raid[0] = 0;
		raid[1] = 0;
		raid[2] = 0;
		raid[3] = 0;
		raid[4] = 0;

		if (cDefStructureShield == 0 && cAttStructureShield > 0) {
			this->returnV = 1;
			
			double percent = std::min(fleet->getBountyBonus(),(fleet->getCapacity(true) / entity->getResSum()));
			raid[0] = entity->removeResMetal(fleet->addMetal(entity->getResMetal(percent),true));
			raid[1] = entity->removeResCrystal(fleet->addCrystal(entity->getResCrystal(percent),true));
			raid[2] = entity->removeResPlastic(fleet->addPlastic(entity->getResPlastic(percent),true));
			raid[3] = entity->removeResFuel(fleet->addFuel(entity->getResFuel(percent),true));
			raid[4] = entity->removeResFood(fleet->addFood(entity->getResFood(percent),true));
			
			report->setRes(raid[0],
						   raid[1],
						   raid[2],
						   raid[3],
						   raid[4],
						   0);
		}


		//
		//Der Verteidiger hat gewonnen
		//
		else if (cAttStructureShield==0 && cDefStructureShield>0)
			this->returnV = 2;

		//
		//Der Kampf endete unentschieden
		//
		else {

			//
			//	Unentschieden, beide Flotten wurden zerstört
			//
			if (cAttStructureShield==0 && cDefStructureShield==0)
        		this->returnV = 3;

			//
			//	Unentschieden, beide Flotten haben überlebt
			//
			else
				this->returnV = 4;
		}
		
		report->setWf(entity->getAddedWfMetal(),
					  entity->getAddedWfCrystal(),
					  entity->getAddedWfPlastic());
		
		//
		//Auswertung
		//
		report->setShipsEnd(fleet->getShipString());
		report->setEntityShipsEnd(entity->getShipString());
		report->setEntityDefEnd(entity->getDefString(true));
		report->setRestore(round((config.nget("def_restore_percent",0) + entity->getUser()->getSpecialist()->getSpecialistDefRepair() - 1)*100));

        //Log schreiben
        query << "INSERT INTO "
			<< "	logs_battle "
			<< "("
			<< "	fleet_id, "
			<< "	user_id, "
			<< "	entity_user_id, "
			<< "	user_alliance_id, "
			<< "	entity_user_alliance_id, "
			<< "	war, "
			<< "	entity_id, "
			<< "	action, "
			<< "	result, "
			<< "	fleet_ships_cnt, "
			<< "	entity_ships_cnt, "
			<< "	entity_defs_cnt, "
			<< "	fleet_weapon, "
			<< "	fleet_shield, "
			<< "	fleet_structure, "
			<< "	fleet_weapon_bonus, "
			<< "	fleet_shield_bonus, "
			<< "	fleet_structure_bonus, "
			<< "	entity_weapon, "
			<< "	entity_shield, "
			<< "	entity_structure, "
			<< "	entity_weapon_bonus, "
			<< "	entity_shield_bonus, "
			<< "	entity_structure_bonus, "
			<< "	fleet_win_exp, "
			<< "	entity_win_exp, "
			<< "	win_metal, "
			<< "	win_crystal, "
			<< "	win_pvc, "
			<< "	win_tritium, "
			<< "	win_food, "
			<< "	tf_metal, "
			<< "	tf_crystal, "
			<< "	tf_pvc, "
			<< "	timestamp, "
			<< "	landtime "
			<< ")"
			<< "VALUES"
			<< "("
			<< "	" << fleet->getId() << ", "
			<< "	" << mysqlpp::quote << fleet->getUserIds() << ", "
			<< "	" << mysqlpp::quote << entity->getUserIds() << ", "
			<< "	" << fleet->fleetUser->getAllianceId() << ", "
			<< "	" << entity->getUser()->getAllianceId() << ", "
			<< "	" << this->alliancesHaveWar << ", "
			<< "	" << entity->getId() << ", "
			<< "	" << mysqlpp::quote << fleet->getAction() << ", "
			<< "	" << returnV << ", "
			<< "	" << fleet->getInitCount(true) << ", "
			<< "	" << entity->getInitCount(true) - entity->getInitDefCount() << ", "
			<< "	" << entity->getInitDefCount() << ", "
			<< "	" << initAttWeapon << ", "
			<< "	" << initAttShield << ", "
			<< "	" << initAttStructure << ", "
			<< "	" << fleet->getWeaponBonus() * 100 << ", "
			<< "	" << fleet->getShieldBonus() * 100 << ", "
			<< "	" << fleet->getStructureBonus() * 100 << ", "
			<< "	" << initDefWeapon << ", "
			<< "	" << initDefShield <<", "
			<< "	" << initDefStructure << ", "
			<< "	" << entity->getWeaponBonus() * 100 << ", "
			<< "	" << entity->getShieldBonus() * 100 << ", "
			<< "	" << entity->getStructureBonus() * 100 << ", "
			<< "	" << fleet->getAddedExp() << ", "
			<< "	" << entity->getAddedExp() << ", "
			<< "	" << raid[0] << ", "
			<< "	" << raid[1] << ", "
			<< "	" << raid[2] << ", "
			<< "	" << raid[3] << ", "
			<< "	" << raid[4] << ", "
			<< "	" << entity->getAddedWfMetal() << ", "
			<< "	" << entity->getAddedWfCrystal() << ", "
			<< "	" << entity->getAddedWfPlastic() << ", "
			<< "	" << std::time(0) << ", "
			<< "	" << fleet->getLandtime() << ");";
		query.store();

		std::cerr << query.str();

		log->addText(("Battle id: " + etoa::d2s(query.insert_id())));
		
		switch (returnV)
		{
			case 1:	//angreifer hat gewonnen
				this->returnFleet = true;
				this->attPoints = 3;
				this->defPoints = 0;
				this->attResult = 2;
				this->defResult = 0;
				break;
			case 2:	//agreifer hat verloren
				this->returnFleet = false;
				this->attPoints = 1;
				this->defPoints = 2;
				this->attResult = 0;
				this->defResult = 2;
				break;
			case 3:	//beide flotten sind kaputt
				this->returnFleet = false;
				this->attPoints = 1;
				this->defPoints = 1;
				this->attResult = 1;
				this->defResult = 1;
				break;
			case 4: //beide flotten haben überlebt
				this->returnFleet = true;
				this->attPoints = 1;
				this->defPoints = 1;
				this->attResult = 1;
				this->defResult = 1;
				break;
		}
		report->setResult(returnV);
		
		//Battlepoints
		int user;
		std::string attReason = "Angriff gegen" + entity->getUserNicks();
		std::string defReason = "Verteidigung gegen" + fleet->getUserNicks();
		
		if (initAttWeapon>0) {
			std::string users = fleet->getUserIds();
			found=users.find_first_of(",");
			while (found!=std::string::npos) {
				users = users.substr(found+1);
				found=users.find_first_of(",");
				user = (int)etoa::s2d(users.substr(0,found));
				etoa::addBattlePoints(user,this->attPoints,this->attResult,attReason);
			}
			
			users = entity->getUserIds();
			found=users.find_first_of(",");
			while (found!=std::string::npos) {
				users = users.substr(found+1);
				found=users.find_first_of(",");
				user = (int)etoa::s2d(users.substr(0,found));
				etoa::addBattlePoints(user,this->defPoints,this->defResult,attReason);
			}
		}
		
		fleet->addRaidedRes();
	}
	delete report;
}
