<?PHP
class ResourceBoxDrawer
{
	public static function getHTML(Planet $p, $small = false)
	{
		return $small ? self::getHTMLSmall($p) : self::getHTMLNormal($p);
	}

	/** 
	* Displays a box with resources, power and population
	*/
	public static function getHTMLNormal(Planet $p)
	{
		$style0 = "";
		$style1 = "";
		$style2 = "";
		$style3 = "";
		$style4 = "";
		$style5 = "";
		
		$store_err = array();

		$store_msg = false;
		$power_msg = false;
		$place_msg = false;

		if ($p->storeMetal<=floor($p->resMetal) && floor($p->resMetal)>0)
		{
			$style0="resfullcolor";
			$store_msg=true;
		}
		else
			$style0="resmetalcolor";
		if ($p->storeCrystal<=floor($p->resCrystal) && floor($p->resCrystal)>0)
		{
			$style1="resfullcolor";
			$store_msg=true;
		}
		else	
			$style1="rescrystalcolor";
		if ($p->storePlastic<=floor($p->resPlastic) && floor($p->resPlastic)>0)
		{
			$style2="resfullcolor";
			$store_msg=true;
		}
		else
			$style2="resplasticcolor";
		if ($p->storeFuel<=floor($p->resFuel) && floor($p->resFuel)>0)
		{
			$style3="resfullcolor";
			$store_msg=true;
		}
		else
			$style3="resfuelcolor";
		if ($p->storeFood<=floor($p->resFood) && floor($p->resFood)>0)
		{
			$style4="resfullcolor";
			$store_msg=true;
		}
		else
			$style4="resfoodcolor";
		if ($p->people_place<=floor($p->people) && floor($p->people)>0)
		{
			$style5="resfullcolor";
			$place_msg=true;
		}
		else
			$style5="respeoplecolor";
		if(floor($p->prodPower)-floor($p->usePower)<0)
		{
			$style6="resfullcolor";
			$power_msg=true;
			$power_rest = floor($p->prodPower)-floor($p->usePower);
		}
		else
		{
			$style6="respowercolor";
			$power_rest = floor($p->prodPower)-floor($p->usePower);
		}
		
		$rtn = tableStart("Ressourcen", 0, '', '', true)."<tr>
		<th class=\"resBoxTitleCell\"><div class=\"resmetal\">".RES_METAL."</div></th>
		<th class=\"resBoxTitleCell\"><div class=\"rescrystal\">".RES_CRYSTAL."</div></th>
		<th class=\"resBoxTitleCell\"><div class=\"resplastic\">".RES_PLASTIC."</div></th>
		<th class=\"resBoxTitleCell\"><div class=\"resfuel\">".RES_FUEL."</div></th>
		<th class=\"resBoxTitleCell\"><div class=\"resfood\">".RES_FOOD."</div></th>
		<th class=\"resBoxTitleCell\"><div class=\"respeople\">Bewohner</div></th>
		<th class=\"resBoxTitleCell\"><div class=\"respower\">Energie</div></th>
		</tr><tr>
		<td class=\"$style0\" ".mTT(RES_METAL,"<img width=\"40px\" height=\"40px\" src=\"images/resources/metal.png\" style=\"float:left;margin-right:5px;\"/> <b>Vorhanden:</b> ".nf($p->resMetal)."<br/><b>Speicher:</b> ".nf($p->storeMetal)."<br style=\"clear:both;\"/>").">".nf(floor($p->resMetal))."</td>
		<td class=\"$style1\" ".mTT(RES_CRYSTAL,"<img width=\"40px\" height=\"40px\" src=\"images/resources/crystal.png\" style=\"float:left;margin-right:5px;\"/> <b>Vorhanden:</b> ".nf($p->resCrystal)."<br/><b>Speicher:</b> ".nf($p->storeCrystal)."<br style=\"clear:both;\"/>").">".nf(floor($p->resCrystal))."</td>
		<td class=\"$style2\" ".mTT(RES_PLASTIC,"<img width=\"40px\" height=\"40px\" src=\"images/resources/plastic.png\" style=\"float:left;margin-right:5px;\"/> <b>Vorhanden:</b> ".nf($p->resPlastic)."<br/><b>Speicher:</b> ".nf($p->storePlastic)."<br style=\"clear:both;\"/>").">".nf(floor($p->resPlastic))."</td>
		<td class=\"$style3\" ".mTT(RES_FUEL,"<img width=\"40px\" height=\"40px\" src=\"images/resources/fuel.png\" style=\"float:left;margin-right:5px;\"/> <b>Vorhanden:</b> ".nf($p->resFuel)."<br/><b>Speicher:</b> ".nf($p->storeFuel)."<br style=\"clear:both;\"/>").">".nf(floor($p->resFuel))."</td>
		<td class=\"$style4\" ".mTT(RES_FOOD,"<img width=\"40px\" height=\"40px\" src=\"images/resources/food.png\" style=\"float:left;margin-right:5px;\"/> <b>Vorhanden:</b> ".nf($p->resFood)."<br/><b>Speicher:</b> ".nf($p->storeFood)."<br style=\"clear:both;\"/>").">".nf(floor($p->resFood))."</td>
		<td class=\"$style5\" ".mTT("Bevölkerung","<img width=\"40px\" height=\"40px\" src=\"images/resources/people.png\" style=\"float:left;margin-right:5px;\"/> <b>Vorhanden:</b> ".nf($p->people)."<br/><b>Platz:</b> ".nf($p->people_place)."<br style=\"clear:both;\"/>").">".nf(floor($p->people))."</td>
		<td class=\"$style6\" ".mTT(RES_POWER,"<img width=\"40px\" height=\"40px\" src=\"images/resources/power.png\" style=\"float:left;margin-right:5px;\"/> <b>Produktion:</b> ".nf($p->prodPower)."<br/><b>Verfügbar:</b> ".nf($power_rest)."<br/><b>Verbrauch:</b> ".nf($p->usePower)."<br style=\"clear:both;\"/>").">".nf($power_rest)."</td>
		</tr></table>";
		
		return $rtn;
	}
	
	public static function getHTMLSmall(Planet $p)
	{
		$style0 = "";
		$style1 = "";
		$style2 = "";
		$style3 = "";
		$style4 = "";
		$style5 = "";
		
		$store_err = array();

		$store_msg = false;
		$power_msg = false;
		$place_msg = false;
		
		if ($p->storeMetal<=floor($p->resMetal) && floor($p->resMetal)>0)
		{
			$style0="resfullcolor";
			$store_msg[1] = tm("Speicher voll","Produktion gestoppt, bitte Speicher ausbauen!");
			$store_err[1]=true;
		}
		if ($p->storeCrystal<=floor($p->resCrystal) && floor($p->resCrystal)>0)
		{
			$style1="resfullcolor";
			$store_msg[2] = tm("Speicher voll","Produktion gestoppt, bitte Speicher ausbauen!");
			$store_err[2]=true;
		}
		if ($p->storePlastic<=floor($p->resPlastic) && floor($p->resPlastic)>0)
		{
			$style2=" resfullcolor";
			$store_msg[3] = tm("Speicher voll","Produktion gestoppt, bitte Speicher ausbauen!");
			$store_err[3]=true;
		}
		if ($p->storeFuel<=floor($p->resFuel) && floor($p->resFuel)>0)
		{
			$style3=" resfullcolor";
			$store_msg[4] = tm("Speicher voll","Produktion gestoppt, bitte Speicher ausbauen!");
			$store_err[4]=true;
		}
		if ($p->storeFood<=floor($p->resFood) && floor($p->resFood)>0)
		{
			$style4=" resfullcolor";
			$store_msg[5] = tm("Speicher voll","Produktion gestoppt, bitte Speicher ausbauen!");
			$store_err[5]=true;
		}
		if ($p->people_place<=floor($p->people) && floor($p->people)>0)
		{
			$style5=" resfullcolor";
			$store_msg[6] = tm("Wohnraum voll","Wachstum gestoppt, bitte Wohnraum ausbauen!");
			$store_err[6]=true;
		}
		if(floor($p->prodPower)-floor($p->usePower)<0)
		{
			$style6=" resfullcolor";
			$store_msg[7] = tm("Zuwenig Energie","Produktion verringert, bitte Kraftwerk ausbauen!");
			$store_err[7] = true;
			$power_rest = floor($p->prodPower)-floor($p->usePower);
		}
		else
		{
			$style6="";
			$store_msg[7] = "";
			$store_err[7] = "";
			$power_rest = floor($p->prodPower)-floor($p->usePower);
		}				
		$rtn = "<div id=\"resbox\">
		<div id=\"resboxheader\">Resourcen</div>
		<div id=\"resboxcontent\">
		<span class=\"resmetal ".$style0."\" ".mTT(RES_METAL,"<img src=\"images/resources/metal.png\" style=\"float:left;margin-right:5px;\"/> <b>Vorhanden:</b> ".nf($p->resMetal)."<br/><b>Speicher:</b> ".nf($p->storeMetal)."<br style=\"clear:both;\"/>").">".nf($p->resMetal,0,1)."</span>
		<span class=\"rescrystal ".$style1."\" ".mTT(RES_CRYSTAL,"<img src=\"images/resources/crystal.png\" style=\"float:left;margin-right:5px;\"/> <b>Vorhanden:</b> ".nf($p->resCrystal)."<br/><b>Speicher:</b> ".nf($p->storeCrystal)."<br style=\"clear:both;\"/>").">".nf($p->resCrystal,0,1)."</span>
		<span class=\"resplastic ".$style2."\" ".mTT(RES_PLASTIC,"<img src=\"images/resources/plastic.png\" style=\"float:left;margin-right:5px;\"/> <b>Vorhanden:</b> ".nf($p->resPlastic)."<br/><b>Speicher:</b> ".nf($p->storePlastic)."<br style=\"clear:both;\"/>").">".nf($p->resPlastic,0,1)."</span>
		<span class=\"resfuel ".$style3."\" ".mTT(RES_FUEL,"<img src=\"images/resources/fuel.png\" style=\"float:left;margin-right:5px;\"/> <b>Vorhanden:</b> ".nf($p->resFuel)."<br/><b>Speicher:</b> ".nf($p->storeFuel)."<br style=\"clear:both;\"/>").">".nf($p->resFuel,0,1)."</span>
		<span class=\"resfood ".$style4."\" ".mTT(RES_FOOD,"<img src=\"images/resources/food.png\" style=\"float:left;margin-right:5px;\"/> <b>Vorhanden:</b> ".nf($p->resFood)."<br/><b>Speicher:</b> ".nf($p->storeFood)."<br style=\"clear:both;\"/>").">".nf($p->resFood,0,1)."</span>
		<span class=\"respeople ".$style5."\" ".mTT("Bevölkerung","<img src=\"images/resources/people.png\" style=\"float:left;margin-right:5px;\"/> <b>Vorhanden:</b> ".nf($p->people)."<br/><b>Platz:</b> ".nf($p->people_place)."<br style=\"clear:both;\"/>").">".nf($p->people,0,1)."</span>
		<span class=\"respower ".$style6."\" ".mTT(RES_POWER,"<img src=\"images/resources/power.png\" style=\"float:left;margin-right:5px;\"/> <b>Produktion:</b> ".nf($p->prodPower)."<br/><b>Verfügbar:</b> ".nf($power_rest)."<br/><b>Verbrauch:</b> ".nf($p->usePower)."<br style=\"clear:both;\"/>").">".nf($power_rest,0,1)."</span>
		</div>
		</div>";
		
		return $rtn;
	}
}
?>