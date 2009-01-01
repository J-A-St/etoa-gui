#include "Fleet.h"

	Fleet::Fleet(mysqlpp::Row &fleet) {
		this->action = std::string(fleet["action"]);
		this->fId = (int)fleet["id"];
		this->userId = (int)fleet["user_id"];
		this->leaderId = (int)fleet["leader_id"];
		this->entityFrom = (int)fleet["entity_from"];
		this->entityTo = (int)fleet["entity_to"];
		this->nextId = (int)fleet["next_id"];
		this->launchtime = (int)fleet["launchtime"];
		this->landtime = (int)fleet["landtime"];
		this->nextactiontime = (int)fleet["nextactiontime"];
		this->status = (short)fleet["status"];
		this->pilots = (double)fleet["pilots"];
		this->usageFuel = (int)fleet["usage_fuel"];
		this->usageFood = (int)fleet["usage_food"];
		this->usagePower = (int)fleet["usage_power"];
		this->supportUsageFuel = (int)fleet["support_usage_fuel"];
		this->supportUsageFood = (int)fleet["support_usage_food"];
		this->resMetal = (double)fleet["res_metal"];
		this->resCrystal = (double)fleet["res_crystal"];
		this->resPlastic = (double)fleet["res_plastic"];
		this->resFuel = (double)fleet["res_fuel"];
		this->resFood = (double)fleet["res_food"];
		this->resPower = (double)fleet["res_power"];
		this->resPeople = (double)fleet["res_people"];
		this->fetchMetal = (double)fleet["fetch_metal"];
		this->fetchCrystal = (double)fleet["fetch_crystal"];
		this->fetchPlastic = (double)fleet["fetch_plastic"];
		this->fetchFuel = (double)fleet["fetch_fuel"];
		this->fetchFood = (double)fleet["fetch_food"];
		this->fetchPower = (double)fleet["fetch_power"];
		this->fetchPeople = (double)fleet["fetch_people"];
		
		this->initResMetal = this->resMetal;
		this->initResCrystal = this->resCrystal;
		this->initResPlastic = this->resPlastic;
		this->initResFuel = this->resFuel;
		this->initResFood = this->resFood;
		this->initResPower = this->resPower;
		this->initResPeople = this->resPeople;
		
		this->capacity = 0;
		this->peopleCapacity = 0;
		this->actionCapacity = 0;
		
		this->actionAllowed = false;
		this->shipsLoaded = false;
		this->entityLoaded = false;
		this->shipsChanged = false;
		this->entityToUserId = 0;
		
		this->logFleetShipStart = "0";
	}
	
	int Fleet::getId() {
		return this->fId;
	}
	
	int Fleet::getUserId() {
		return this->userId;
	}
	
	int Fleet::getEntityFrom() {
		return this->entityFrom;
	}
	
	int Fleet::getEntityTo() {
		return this->entityTo;
	}
	
	int Fleet::getNextId() {
		return this->nextId;
	}
	
	int Fleet::getLandtime() {
		return this->landtime;
	}
	
	int Fleet::getLaunchtime() {
		return this->launchtime;
	}
	
	int Fleet::getNextactiontime() {
		return this->nextactiontime;
	}
	
	std::string Fleet::getAction() {
		return this->action;
	}
	
	short Fleet::getStatus() {
		return this->status;
	}
	
	double Fleet::getPilots() {
		return this->pilots;
	}
	
	double Fleet::getResMetal() {
		return this->resMetal;
	}
	
	double Fleet::getResCrystal() {
		return this->resCrystal;
	}
	
	double Fleet::getResPlastic() {
		return this->resPlastic;
	}
	
	double Fleet::getResFuel() {
		return this->resFuel;
	}
	
	double Fleet::getResFood() {
		return this->resFood;
	}
	
	double Fleet::getResPower() {
		return this->resPower;
	}
	
	double Fleet::getResPeople() {
		return this->resPeople;
	}
	
	double Fleet::getResLoaded() {
		return this->getResMetal()
				+ this->getResCrystal()
				+ this->getResPlastic()
				+ this->getResFuel()
				+ this->getResFood();
	}
	
	double Fleet::getCapacity() {
		if (!this->shipsLoaded)
			this->loadShips();
		return this->capacity - this->getResLoaded() - this->usageFuel - this->usageFood - this->supportUsageFuel - this->supportUsageFood;
	}
	
	double Fleet::getActionCapacity() {
		if (!this->shipsLoaded)
			this->loadShips();
		return std::min(this->actionCapacity,this->getCapacity());
	}
	
	double Fleet::getPeopleCapacity() {
		if (!this->shipsLoaded)
			this->loadShips();
		return this->peopleCapacity;
	}
	
	double Fleet::addMetal(double metal) {
		this->changedData = true;
		metal = round(metal);
		if (metal>=this->getCapacity()) 
			metal = this->getCapacity();
		this->resMetal += metal;
		return metal;
	}
	
	double Fleet::addCrystal(double crystal) {
		crystal = round(crystal);
		this->changedData = true;
		if (crystal>=this->getCapacity()) 
			crystal = this->getCapacity();
		this->resCrystal += crystal;
		return crystal;
	}
	
	double Fleet::addPlastic(double plastic) {
		plastic = round(plastic);
		this->changedData = true;
		if (plastic>=this->getCapacity()) 
			plastic = this->getCapacity();
		this->resPlastic += plastic;
		return plastic;
	}
	
	double Fleet::addFuel(double fuel) {
		fuel = round(fuel);
		this->changedData = true;
		if (fuel>=this->getCapacity()) 
			fuel = this->getCapacity();
		this->resFuel += fuel;
		return fuel;
	}
	
	double Fleet::addFood(double food) {
		food = round(food);
		this->changedData = true;
		if (food>=this->getCapacity()) 
			food = this->getCapacity();
		this->resFood += food;
		return food;
	}
	
	double Fleet::addPower(double power) {
		power = round(power);
		this->changedData = true;
		if (power>=this->getCapacity()) 
			power = this->getCapacity();
		this->resPower += power;
		return power;
	}
	
	double Fleet::addPeople(double people) {
		people = round(people);
		this->changedData = true;
		if (people>=this->getCapacity()) 
			people = this->getCapacity();
		this->resPower += people;
		return people;
	}
	
	double Fleet::unloadResMetal() {
		this->changedData = true;
		double metal = this->resMetal;
		this->resMetal = 0;
		return metal;
	}
	
	double Fleet::unloadResCrystal() {
		this->changedData = true;
		double crystal = this->resCrystal;
		this->resCrystal = 0;
		return crystal;
	}
	
	double Fleet::unloadResPlastic() {
		this->changedData = true;
		double plastic = this->resPlastic;
		this->resPlastic = 0;
		return plastic;
	}
	
	double Fleet::unloadResFuel(bool land) {
		this->changedData = true;
		double fuel = this->resFuel;
		if (land) {
			fuel += this->usageFuel + this-> supportUsageFuel;
			this->usageFuel = 0;
			this->supportUsageFuel = 0;
		}
		this->resFuel = 0;
		return fuel;
	}
		
	double Fleet::unloadResFood(bool land) {
		this->changedData = true;
		double food = this->resFood;
		if (land) {
			food = this->usageFood + this->supportUsageFood;
			this->usageFood = 0;
			this->supportUsageFood = 0;
		}
		this->resFood = 0;
		return food;
	}
		
	double Fleet::unloadResPower() {
		this->changedData = true;
		double power = this->resPower;
		this->resPower = 0;
		return power;
	}
	
	double Fleet::unloadResPeople(bool land) {
		this->changedData = true;
		double people = this->resPeople;
		if (land) {
			people += this->pilots;
			this->pilots = 0;
		}
		this->resPeople = 0;
		return people;
	}
	
	double Fleet::getWfMetal() {
		double wfMetal = 0;
		std::vector<Object*>::iterator it;
		for (it=objects.begin() ; it < objects.end(); it++)
			wfMetal += (*it)->getWfMetal();
		
		return wfMetal;
	}
		
	double Fleet::getWfCrystal() {
		double wfCrystal = 0;
		std::vector<Object*>::iterator it;
		for (it=objects.begin() ; it < objects.end(); it++)
			wfCrystal += (*it)->getWfCrystal();
		
		return wfCrystal;
	}
	
	double Fleet::getWfPlastic() {
		double wfPlastic = 0;
		std::vector<Object*>::iterator it;
		for (it=objects.begin() ; it < objects.end(); it++)
			wfPlastic += (*it)->getWfPlastic();
		
		return wfPlastic;
	}
	
	void Fleet::setPercentSurvive(double percentage) {
		std::vector<Object*>::iterator it;
		for (it = this->objects.begin() ; it < this->objects.end(); it++)
			(*it)->setPercentSurvive(percentage);
	}
	
	int Fleet::getEntityToUserId() {
		if(!this->entityToUserId)
			this->entityToUserId = functions::getUserIdByPlanet(this->getEntityTo());
		return this->entityToUserId;
	}
	
	void Fleet::setReturn() {
		int entity = this->entityFrom;
		this->entityFrom = this->entityTo;
		int duration;
		
		if (this->getStatus() == 3 && this->getNextactiontime() > 0) {
			duration = this->getNextactiontime();
			int entityTo = this->getNextId();
		}
		else {
			duration = this->getLandtime() - this->getLaunchtime();
			this->entityTo = entity;
		}
		this->launchtime = this->getLandtime();
		this->landtime = this->getLaunchtime() + duration;
		
		this->status = 1;
	}
	
	void Fleet::setSupport() {
		int flyingHomeTime = this->getLandtime() - this->getLaunchtime();
		this->launchtime = this->getLandtime();
		this->landtime = this->getLandtime() + this->getNextactiontime();
		this->status = 3;
		
		this->nextId = this->entityFrom;
		this->entityFrom = this->entityTo;
	}
	
	std::string Fleet::getEntityToUserString() {
		return functions::getUserNick(this->getEntityToUserId());
	}
	
	std::string Fleet::getActionString() {
		return this->getAction();
	}
	
	std::string Fleet::getLandtimeString() {
		return functions::formatTime(this->getLandtime());
	}
	
	std::string Fleet::getLaunchtimeString() {
		return  functions::formatTime(this->getLaunchtime());
	}
	
	std::string Fleet::getEntityToString(short type) {
		return functions::formatCoords(this->getEntityFrom(),type);
	}
	
	std::string Fleet::getEntityFromString(short type) {
		return functions::formatCoords(this->getEntityTo(),type);
	}
	
	std::string Fleet::getDestroyedShipString(std::string reason) {
		std::string destroyedString = "";
		
		DataHandler &DataHandler = DataHandler::instance();
		std::vector<Object*>::iterator it;
		for (it = this->objects.begin() ; it < this->objects.end(); it++) {
			if ((*it)->getCount() < (*it)->getInitCount()) {
				ShipData::ShipData *data = DataHandler.getShipById((*it)->getTypeId());	
				destroyedString +=  functions::d2s((*it)->getInitCount() - (*it)->getCount())
								+ " "
								+ data->getName()
								+ "\n";
			}
		}
		
		if (destroyedString.length()>0)
			destroyedString = reason + destroyedString;
		
		return destroyedString;
	}
	
	std::string Fleet::getResCollectedString() {
		std::string msgRes = "\n\n\n[b]ROHSTOFFE:[/b]\n\nTitan: "
							+ functions::nf(functions::d2s(this->getResMetal() - this->initResMetal))
							+ "\nSilizium: "
							+ functions::nf(functions::d2s(this->getResCrystal() - this->initResCrystal))
							+ "\nPVC: "
							+ functions::nf(functions::d2s(this->getResPlastic() - this->initResPlastic))
							+ "\nTritium: "
							+ functions::nf(functions::d2s(this->getResFuel() - this->initResFuel))
							+ "\nNahrung: "
							+ functions::nf(functions::d2s(this->getResFood() - this->initResFood))
							+ "\nBewohner: "
							+ functions::nf(functions::d2s(this->getResPeople() - this->initResPeople))
							+ "\n";
		return msgRes;
	}
				
	bool Fleet::actionIsAllowed() {
		if (!this->shipsLoaded)
			this->loadShips();
		else if (this->shipsChanged)
			this->recalcShips();
		return this->actionAllowed;
	}
	
	void Fleet::loadShips() {
		Config &config = Config::instance();
		
		My &my = My::instance();
		mysqlpp::Connection *con = my.get();
		
		mysqlpp::Query query = con->query();
		query << "SELECT ";
		query << "	* ";
		query << "FROM ";
		query << "	fleet_ships ";
		query << "WHERE ";
		query << "	fs_fleet_id='" << this->getId() << "' ";
		query << "	AND fs_ship_faked='0';";
		mysqlpp::Result fsRes = query.store();
		query.reset();
		
		if (fsRes) {
			int fsSize = fsRes.size();
			
			if (fsSize>0) {
				this->shipsLoaded = true;
				this->logFleetShipStart = "";
				
				DataHandler &DataHandler = DataHandler::instance();
				mysqlpp::Row fsRow;
				
				for (int i=0; i<fsSize; i++) {
					fsRow = fsRes.at(0);
					
					if (config.idget("MARKET_SHIP_ID")!=(int)fsRow["fs_ship_id"]) {
						Object* object = ObjectFactory::createObject(fsRow, 'f'); 
						ShipData::ShipData *data = DataHandler.getShipById(object->getTypeId());
						
						this->capacity += object->getCount() * data->getCapacity();
						this->peopleCapacity += object->getCount() * data->getPeopleCapacity();
						
						if (data->getActions(this->action)) {
							this->actionAllowed = true;
							this->actionCapacity += object->getCount() * data->getCapacity();
						}
						
						this->logFleetShipStart += functions::d2s(object->getTypeId())
												+ ":"
												+ functions::d2s(object->getCount())
												+ ",";
						
						objects.push_back(object);
					}
				}
			}
		}
	}
	
	void Fleet::recalcShips() {
		this->actionCapacity = 0;
		this->capacity = 0;
		this->peopleCapacity = 0;
		
		this->actionAllowed = false;
		
		DataHandler &DataHandler = DataHandler::instance();
		
		std::vector<Object*>::iterator it;
		for (it=objects.begin() ; it < objects.end(); it++) {
			ShipData::ShipData *data = DataHandler.getShipById((*it)->getTypeId());
			
			this->capacity += (*it)->getCount() * data->getCapacity();
			this->peopleCapacity += (*it)->getCount() * data->getPeopleCapacity();
			
			if (data->getActions(this->action)) {
				this->actionAllowed = true;
				this->actionCapacity += (*it)->getCount() * data->getCapacity();
			}
		}
	}
	
	void Fleet::save() {
		int sum = 0;
		while (!objects.empty()) {
			Object* object = objects.back();
			sum += object->getCount();
			std::cout << object->getCount() << "\n";
			delete object;
			objects.pop_back();
		}

		My &my = My::instance();
		mysqlpp::Connection *con = my.get();
		mysqlpp::Query query = con->query();
		
		if (sum>0) {
			query << "UPDATE ";
			query << "	fleet ";
			query << "SET ";
			query << "	entity_from='" << this->getEntityFrom() << "', ";
			query << "	entity_to='" << this->getEntityTo() << "', ";
			query << "	next_id='" << this->getNextId() << "', ";
			query << "	launchtime='" << this->getLaunchtime() << "', ";
			query << "	landtime='" << this->getLandtime() << "', ";
			query << "	nextactiontime='" << this->getNextactiontime() << "', ";
			query << "	status='" << this->getStatus() << "', ";
			query << "	pilots='" << this->getPilots() << "', ";
			query << "	usage_fuel='" << this->usageFuel << "', ";
			query << "	usage_food='" << this->usageFood << "', ";
			query << "	usage_power='" << this->usagePower << "', ";
			query << "	support_usage_fuel='" << this->supportUsageFuel << "', ";
			query << "	support_usage_food='" << this->supportUsageFood << "', ";
			query << "	res_metal='" << this->getResMetal() << "', ";
			query << "	res_crystal='" << this->getResCrystal() << "', ";
			query << "	res_plastic='" << this->getResPlastic() << "', ";
			query << "	res_fuel='" << this->getResFuel() << "', ";
			query << "	res_food='" << this->getResFood() << "', ";
			query << "	res_power='" << this->getResPower() << "', ";
			query << "	res_people='" << this->getResPeople() << "', ";
			query << "	fetch_metal='0', ";
			query << "	fetch_crystal='0', ";
			query << "	fetch_plastic='0', ";
			query << "	fetch_fuel='0', ";
			query << "	fetch_food='0', ";
			query << "	fetch_power='0', ";
			query << "	fetch_people='0' ";
			query << "WHERE ";
			query << "	id='" << this->getId() << "' ";
			query << "LIMIT 1;";
			mysqlpp::Result fsRes = query.store();
			query.reset();
		}
		else {
			query << "DELETE FROM ";
			query << "	fleet ";
			query << "WHERE ";
			query << "	id='" << this->getId() << "' ";
			query << "LIMIT 1;";
			query.store();
			query.reset();
		}
	}
	
	std::string Fleet::getLogResStart() {
		std::string log = ""
						+ functions::d2s(this->initResMetal)
						+ ":"
						+ functions::d2s(this->initResCrystal)
						+ ":"
						+ functions::d2s(this->initResPlastic)
						+ ":"
						+ functions::d2s(this->initResFuel)
						+ ":"
						+ functions::d2s(this->initResFood)
						+ ":"
						+ functions::d2s(this->initResPeople)
						+ ":"
						+ functions::d2s(this->initResPower)
						+ ",f,"
						+ functions::d2s(this->fetchMetal)
						+ ":"
						+ functions::d2s(this->fetchCrystal)
						+ ":"
						+ functions::d2s(this->fetchPlastic)
						+ ":"
						+ functions::d2s(this->fetchFuel)
						+ ":"
						+ functions::d2s(this->fetchFood)
						+ ":"
						+ functions::d2s(this->fetchPower)
						+ ":"
						+ functions::d2s(this->fetchPeople);
		return log;
	}
	
	std::string Fleet::getLogResEnd() {
		std::string log = ""
						+ functions::d2s(this->resMetal)
						+ ":"
						+ functions::d2s(this->resCrystal)
						+ ":"
						+ functions::d2s(this->resPlastic)
						+ ":"
						+ functions::d2s(this->resFuel)
						+ ":"
						+ functions::d2s(this->resFood)
						+ ":"
						+ functions::d2s(this->resPeople)
						+ ":"
						+ functions::d2s(this->resPower)
						+ ",f,0:0:0:0:0:0:0";
		return log;
	}
	
	std::string Fleet::getLogShipsStart() {
		return this->logFleetShipStart;
	}
	
	std::string Fleet::getLogShipsEnd() {
		if (this->shipsLoaded) {
			std::string log = "";
			std::vector<Object*>::iterator it;
			for (it=objects.begin() ; it < objects.end(); it++) {
				log += functions::d2s((*it)->getTypeId())
					+ ":"
					+ functions::d2s((*it)->getCount())
					+ ",";
			}
			return log;
		}
		else
			return "0";
	}
