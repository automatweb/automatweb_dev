<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_conference_value_days.aw,v 1.3 2007/05/21 11:09:26 markop Exp $
// crm_conference_value_days.aw - Konverentsi kalendrivaade 
/*

@classinfo syslog_type=ST_CRM_CONFERENCE_VALUE_DAYS relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

#GENERAL
	@property hotel_code type=textbox
	@caption Hotelli kood
	
	@property show_codes type=checkbox ch_value=1
	@caption N&auml;ita koode
	
*/

class crm_conference_value_days extends class_base
{
	function crm_conference_value_days()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_conference_value_days",
			"clid" => CL_CRM_CONFERENCE_VALUE_DAYS
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////


	/** Change the realestate object info.
		
		@attrib name=parse_alias is_public="1" caption="Change"
	
	**/
	function parse_alias($arr)
	{
		enter_function("value_days::parse_alias");
//		$tpl = "kolm.tpl";
//		$this->read_template($tpl);
//		lc_site_load("room", &$this);
		
//		$data = array("joga" => "jogajoga");
//		$this->vars($data);
		//property väärtuse saatmine kujul "property_nimi"_value
		$html = "";
		$months = 3;
		$n = 0;
		$calendar_object = obj($arr["id"]);

                include_once(aw_ini_get("site_basedir")."/code/soapclient.aw");

                $revalBookingServiceURL = "https://195.250.171.36/RevalORSService/RRCServices.asmx";
                $revalBookingServiceNamespace = "http://revalhotels.com/ORS/webservices/";
	        $urlData = parse_url($revalBookingServiceURL);
                $soapclient = new C_SoapClient($revalBookingServiceURL);
                $soapclient->namespace = $revalBookingServiceNamespace;
                $soapclient->debug = 0;
                $soapclient->ns_end = "/";
                $parameters = array();
			
		$parameters["Resort"] = '';
         	$parameters["FirstDate"] = '2007-05-21T00:00:00.0000000+03:00';
		$parameters["LastDate"] =  '2007-05-24T00:00:00.0000000+03:00';
         	$return = $soapclient->call("GetConferenceDayTypes" , $parameters);
         	$codes = array();
		$bg_colors = array();
		$colors = array("H" => "red" , "M" => "yellow" , "L" => "lime");
		foreach($return["GetConferenceDayTypesResult"]["ConferenceDayTypeClass"] as $data)
		{
			$codes[$data["Resort"]] = $data["Resort"];
			if($data["Resort"] == $calendar_object->prop("hotel_code"))
			{
				$bg_colors[substr($data["DayTypeDate"], 0, 10)] = $data["ConferenceDayType"];
			}
		}
		
		while($n < $months)
		{
			$month_start = mktime(0, 0, 0, date("n",(time() + $n*30*24*3600)), 1, date("Y",(time() + $n*30*24*3600)));
			$month_end = mktime(0, 0, 0, date("n",(time() + ($n + 1)*30*24*3600)), 1, date("Y",(time() + $n*30*24*3600)));

			$day_of_the_week = date("w",($month_start));
			if($day_of_the_week == 0) $day_of_the_week = 7;
			
			$html.='<table class="type4">
				<tr class="subheading">	
					<th colspan="7">'.date("F Y",(time() + $n*30*24*3600)).'</th>
				</tr>
				<tr>
					<th>M</th>
					<th>T</th>
					<th>W</th>
					<th>T</th>
					<th>F</th>
					<th>S</th>
					<th>S</th>
				</tr>';
			$day_start = $month_start - 3600*24*($day_of_the_week - 1);
			
			
			$w = 0;
			while($w < 6)
			{
				$d = 0;
				$html.='<tr>';
				while($d < 7)
				{
					$html.='<td class="disabled" bgcolor="'.$colors[$bg_colors[date("Y-m-d" ,$day_start)]].'">';

					if($day_start >= $month_end  || $day_start < $month_start)
					{
						$html.='<font color="white">';
					}
					elseif($day_start < time())
					{
						$html.='<a href="#"><font color="grey">';
					}
					else
					{
						$html.='<a href="#"><font color="black">';
					}
					$html.=date("d",$day_start);
					$html.='</font">';
					$html.='</a></td>';
					$d++;
					$day_start = $day_start + 3600*24;
				}
				$html.='</tr>';
				$w++;
				if($w == 5 && date("d",$day_start) < 10) $w++;
			}
			$html.='</table>';
			$n++;
		}
		
		if($calendar_object->prop("show_codes"))
		{
			$html.= "<br>Hotellide koodid: <br>";
			$html.= join(", " , $codes);
		}
		
		return $html;
		exit_function("value_days::parse_alias");
		return $this->parse();
	}

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		return $this->parse_alias($arr);
		
		
		
		
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $this->parse_alias($arr),
		));
		
		return $this->parse();
	}

	function request_execute ($this_object)
	{
		return $this->show (array (
			"id" => $this_object->id(),
		));
	}
//-- methods --//
}
?>
