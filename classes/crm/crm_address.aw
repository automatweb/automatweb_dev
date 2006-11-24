<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_address.aw,v 1.20 2006/11/24 14:27:47 kristo Exp $
// crm_address.aw - It's not really a physical address but a collection of data required to 
// contact a person.
/*
	@classinfo relationmgr=yes syslog_type=ST_CRM_ADDRESS
	@tableinfo kliendibaas_address index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property name type=text
	@caption Nimi
	
	@default table=kliendibaas_address
	
	@property aadress type=textbox size=50 maxlength=100
	@caption Tänav/Küla
	
	@property postiindeks type=textbox size=5 maxlength=10
	@caption Postiindeks
	
	@property linn type=relpicker reltype=RELTYPE_LINN automatic=1
	@caption Linn/Vald/Alev

	@property maakond type=relpicker reltype=RELTYPE_MAAKOND automatic=1
	@caption Maakond

	@property piirkond type=relpicker reltype=RELTYPE_PIIRKOND automatic=1
	@caption Piirkond

	@property riik type=relpicker reltype=RELTYPE_RIIK automatic=1
	@caption Riik
	
	@property comment type=textarea cols=65 rows=3 table=objects field=comment
	@caption Kommentaar
	
	@classinfo no_status=1
	@groupinfo settings caption=Seadistused
*/

/*

CREATE TABLE `kliendibaas_address` (
  `oid` int(11) NOT NULL default '0',
  `name` varchar(200) default NULL,
  `tyyp` int(11) default NULL,
  `riik` int(11) default NULL,
  `linn` int(11) default NULL,
  `piirkond` int(11) default NULL,
  `maakond` int(11) default NULL,
  `postiindeks` varchar(5) default NULL,
  `telefon` varchar(20) default NULL,
  `mobiil` varchar(20) default NULL,
  `faks` varchar(20) default NULL,
  `piipar` varchar(20) default NULL,
  `aadress` text,
  `e_mail` varchar(255) default NULL,
  `kodulehekylg` varchar(255) default NULL,
  PRIMARY KEY  (`oid`),
  UNIQUE KEY `oid` (`oid`)
) TYPE=MyISAM;

*/

/* 
@reltype LINN value=1 clid=CL_CRM_CITY
@caption Linn

@reltype RIIK value=2 clid=CL_CRM_COUNTRY
@caption Riik

@reltype MAAKOND value=3 clid=CL_CRM_COUNTY
@caption Maakond

@reltype PIIRKOND value=4 clid=CL_CRM_AREA
@caption Piirkond

@reltype BELONGTO value=4 clid=CL_CRM_PERSON,CL_CRM_COMPANY
@caption Seosobjekt
*/

class crm_address extends class_base
{
	function crm_address()
	{
		$this->init(array(
			"tpldir" => "crm/address",
			"clid" => CL_CRM_ADDRESS,
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;

		switch($data["name"])
		{
			case "postiindeks":
				$oncl = "window.open('http://www.post.ee/?id=1069&op=sihtnumbriotsing&tanav='+document.changeform.aadress.value.replace(/[0-9]+/, '')+'&linn='+document.changeform.linn.options[document.changeform.linn.selectedIndex].text+'&x=30&y=6');";
				$data["post_append_text"] = sprintf(" <a href='#' onClick=\"$oncl\">%s</a>", t("Otsi postiindeksit"));

				break;
		};
		return $retval;
	}

	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		$form = &$arr["request"];

		switch($data["name"])
		{
			case 'name':
				// generate a name for the object
				$name = array();	
				if (!empty($form["aadress"]))
				{
					$name[] = $form['aadress'];
				};

				if (!empty($form["linn"]))
				{
					$city_obj = new object($form["linn"]);
					$name[] = $city_obj->name();
				};
				if (!empty($form["maakond"]))
				{
					$county_obj = new object($form["maakond"]);
					$name[] = $county_obj->name();
				};
				
				if (count($name) < 1)
				{
					if (!empty($form["email"]))
					{
						$name[] = $form["email"];
					};
				}
				
				if (count($name) < 1)
				{
					if (!empty($form["telefon"]))
					{
						$name[] = t('tel:').$form["telefon"];
					};
				}

				if (sizeof($name) > 0)
				{
					$arr["obj_inst"]->set_name(join(", ",$name));
				};
				$retval = PROP_IGNORE;
				break;

		};
		return $retval;
	}	

	function request_execute($obj)
	{
		$this->read_template("show.tpl");
		$this->vars(array(
			"address" => $obj->prop("aadress"),
			"postiindeks" => $obj->prop("postiindeks"),
			"linn" => $this->_get_name_for_obj($obj->prop("linn")),
			"maakond" => $this->_get_name_for_obj($obj->prop("maakond")),
			"country" => $this->_get_name_for_obj($obj->prop("riik")),
		));
		return $this->parse();
	}

	function _get_name_for_obj($id)
	{
		if (empty($id))
		{
			$rv = "";
		}
		else
		{
			$obj = new object($id);
			$rv = $obj->name();
		};
		return $rv;
	}

	function callback_on_load($arr)
	{
		if ($arr["request"]["action"] == "new")
		{
			$o = obj();
			$o->set_parent($arr["request"]["parent"]);
			$o->set_class_id(CL_CRM_ADDRESS);
			$o->save();
			
			if ($this->can("view", $arr["request"]["alias_to"]))
			{
				$at = obj($arr["request"]["alias_to"]);
				$reltype = $arr["request"]["reltype"];

				$bt = $this->get_properties_by_type(array(
					"type" => array("relpicker","relmanager", "popup_search"),
					"clid" => $at->class_id(),
				));

				$symname = "";
				// figure out symbolic name for numeric reltype
				foreach($this->relinfo as $key => $val)
				{
					if (substr($key,0,7) == "RELTYPE")
					{
						if ($reltype == $val["value"])
						{
							$symname = $key;
						};
					};
				};

				// figure out which property to check
				foreach($bt as $item_key => $item)
				{
					// double check just in case
					if (!empty($symname) && ($item["type"] == "popup_search" || $item["type"] == "relpicker" || $item["type"] == "relmanager") && ($item["reltype"] == $symname))
					{
						$target_prop = $item_key;
					};
				};


				// now check, whether that property has a value. If not,
				// set it to point to the newly created connection
				if (!empty($symname) && !empty($target_prop))
				{
					$conns = $at->connections_from(array(
						"type" => $symname,
					));
					$conn_count = sizeof($conns);
				};

				// this is after the new connection has been made
				if ($target_prop != "" && ($conn_count == 1 || !$bt[$target_prop]["multiple"] ))
				{
					$at->set_prop($target_prop,$o->id());
					$at->save();
				}
				
				$at->connect(array(
					"to" => $o->id(),
					"type" => $arr["request"]["reltype"]
				));
			}
			header("Location: ".html::get_change_url($o->id(), array("return_url" => $arr["request"]["return_url"])));
			die();
		}
	}

	function do_db_upgrade($table, $field, $query, $error)
	{
		if (empty($field))
		{
			$this->db_query('CREATE TABLE '.$table.' (oid INT PRIMARY KEY NOT NULL)');
			return true;
		}

		switch ($field)
		{
			case 'tyyp':
			case 'riik':
			case 'linn':
			case 'piirkond':
			case 'maakond':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'int'
				));
				return true;
			case 'name':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'varchar(200)'
				));
				return true;
			case 'postiindeks':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'varchar(5)'
				));
				return true;
			case 'telefon':
			case 'mobiil':
			case 'faks':
			case 'piipar':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'varchar(20)'
				));
				return true;
			case 'e_mail':
			case 'kodulehekylg':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'varchar(255)'
				));
				return true;
			case 'aadress':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'text'
				));
				return true;
                }

		return false;
	}


	function get_name_from_adr($o)
	{
		$name = array();	
		if ($o->prop("aadress") != "")
		{
			$name[] = $o->prop("aadress");
		}

		if ($this->can("view", $o->prop("linn")))
		{
			$city_obj = new object($o->prop("linn"));
			$name[] = $city_obj->name();
		};
		if ($this->can("view", $o->prop("maakond")))
		{
			$county_obj = new object($o->prop("maakond"));
			$name[] = $county_obj->name();
		};
		if ($this->can("view", $o->prop("riik")))
		{
			$name[] = $o->prop("riik.name");
		};
				
		return join(", ",$name);
	}

	function get_country_list()
	{
		return array(
			"AD" => "Andorra",
			"AE" => "United Arab Emirates",
			"AF" => "Afghanistan",
			"AG" => "Antigua and Barbuda",
			"AI" => "Anguilla",
			"AL" => "Albania",
			"AM" => "Armenia",
			"AN" => "Netherlands Antilles",
			"AO" => "Angola",
			"AQ" => "Antarctica",
			"AR" => "Argentina",
			"AS" => "American Samoa",
			"AT" => "Austria",
			"AU" => "Australia",
			"AW" => "Aruba",
			"AZ" => "Azerbaijan",
			"BA" => "Bosnia and Herzegovina",
			"BB" => "Barbados",
			"BD" => "Bangladesh",
			"BE" => "Belgium",
			"BF" => "Burkina Faso",
			"BG" => "Bulgaria",
			"BH" => "Bahrain",
			"BI" => "Burundi",
			"BJ" => "Benin",
			"BM" => "Bermuda",
			"BN" => "Brunei Darussalam",
			"BO" => "Bolivia",
			"BR" => "Brazil",
			"BS" => "Bahamas",
			"BT" => "Bhutan",
			"BV" => "Bouvet Island",
			"BW" => "Botswana",
			"BY" => "Belarus",
			"BZ" => "Belize",
			"CA" => "Canada",
			"CC" => "Cocos (Keeling) Islands",
			"CF" => "Central African Republic",
			"CG" => "Congo",
			"CH" => "Switzerland",
			"CI" => "Cote D'Ivoire (Ivory Coast)",
			"CK" => "Cook Islands",
			"CL" => "Chile",
			"CM" => "Cameroon",
			"CN" => "China",
			"CO" => "Colombia",
			"CR" => "Costa Rica",
			"CS" => "Czechoslovakia (former)",
			"CU" => "Cuba",
			"CV" => "Cape Verde",
			"CX" => "Christmas Island",
			"CY" => "Cyprus",
			"CZ" => "Czech Republic",
			"DE" => "Germany",
			"DJ" => "Djibouti",
			"DK" => "Denmark",
			"DM" => "Dominica",
			"DO" => "Dominican Republic",
			"DZ" => "Algeria",
			"EC" => "Ecuador",
			"EE" => "Estonia",
			"EG" => "Egypt",
			"EH" => "Western Sahara",
			"ER" => "Eritrea",
			"ES" => "Spain",
			"ET" => "Ethiopia",
			"FI" => "Finland",
			"FJ" => "Fiji",
			"FK" => "Falkland Islands (Malvinas)",
			"FM" => "Micronesia",
			"FO" => "Faroe Islands",
			"FR" => "France",
			"FX" => "France, Metropolitan",
			"GA" => "Gabon",
			"GB" => "Great Britain (UK)",
			"GD" => "Grenada",
			"GE" => "Georgia",
			"GF" => "French Guiana",
			"GH" => "Ghana",
			"GI" => "Gibraltar",
			"GL" => "Greenland",
			"GM" => "Gambia",
			"GN" => "Guinea",
			"GP" => "Guadeloupe",
			"GQ" => "Equatorial Guinea",
			"GR" => "Greece",
			"GS" => "S. Georgia and S. Sandwich Isls.",
			"GT" => "Guatemala",
			"GU" => "Guam",
			"GW" => "Guinea-Bissau",
			"GY" => "Guyana",
			"HK" => "Hong Kong",
			"HM" => "Heard and McDonald Islands",
			"HN" => "Honduras",
			"HR" => "Croatia (Hrvatska)",
			"HT" => "Haiti",
			"HU" => "Hungary",
			"ID" => "Indonesia",
			"IE" => "Ireland",
			"IL" => "Israel",
			"IN" => "India",
			"IO" => "British Indian Ocean Territory",
			"IQ" => "Iraq",
			"IR" => "Iran",
			"IS" => "Iceland",
			"IT" => "Italy",
			"JM" => "Jamaica",
			"JO" => "Jordan",
			"JP" => "Japan",
			"KE" => "Kenya",
			"KG" => "Kyrgyzstan",
			"KH" => "Cambodia",
			"KI" => "Kiribati",
			"KM" => "Comoros",
			"KN" => "Saint Kitts and Nevis",
			"KP" => "Korea (North)",
			"KR" => "Korea (South)",
			"KW" => "Kuwait",
			"KY" => "Cayman Islands",
			"KZ" => "Kazakhstan",
			"LA" => "Laos",
			"LB" => "Lebanon",
			"LC" => "Saint Lucia",
			"LI" => "Liechtenstein",
			"LK" => "Sri Lanka",
			"LR" => "Liberia",
			"LS" => "Lesotho",
			"LT" => "Lithuania",
			"LU" => "Luxembourg",
			"LV" => "Latvia",
			"LY" => "Libya",
			"MA" => "Morocco",
			"MC" => "Monaco",
			"MD" => "Moldova",
			"MG" => "Madagascar",
			"MH" => "Marshall Islands",
			"MK" => "Macedonia",
			"ML" => "Mali",
			"MM" => "Myanmar",
			"MN" => "Mongolia",
			"MO" => "Macau",
			"MP" => "Northern Mariana Islands",
			"MQ" => "Martinique",
			"MR" => "Mauritania",
			"MS" => "Montserrat",
			"MT" => "Malta",
			"MU" => "Mauritius",
			"MV" => "Maldives",
			"MW" => "Malawi",
			"MX" => "Mexico",
			"MY" => "Malaysia",
			"MZ" => "Mozambique",
			"NA" => "Namibia",
			"NC" => "New Caledonia",
			"NE" => "Niger",
			"NF" => "Norfolk Island",
			"NG" => "Nigeria",
			"NI" => "Nicaragua",
			"NL" => "Netherlands",
			"NO" => "Norway",
			"NP" => "Nepal",
			"NR" => "Nauru",
			"NT" => "Neutral Zone",
			"NU" => "Niue",
			"NZ" => "New Zealand (Aotearoa)",
			"OM" => "Oman",
			"PA" => "Panama",
			"PE" => "Peru",
			"PF" => "French Polynesia",
			"PG" => "Papua New Guinea",
			"PH" => "Philippines",
			"PK" => "Pakistan",
			"PL" => "Poland",
			"PM" => "St. Pierre and Miquelon",
			"PN" => "Pitcairn",
			"PR" => "Puerto Rico",
			"PT" => "Portugal",
			"PW" => "Palau",
			"PY" => "Paraguay",
			"QA" => "Qatar",
			"RE" => "Reunion",
			"RO" => "Romania",
			"RU" => "Russian Federation",
			"RW" => "Rwanda",
			"SA" => "Saudi Arabia",
			"Sb" => "Solomon Islands",
			"SC" => "Seychelles",
			"SD" => "Sudan",
			"SE" => "Sweden",
			"SG" => "Singapore",
			"SH" => "St. Helena",
			"SI" => "Slovenia",
			"SJ" => "Svalbard and Jan Mayen Islands",
			"SK" => "Slovak Republic",
			"SL" => "Sierra Leone",
			"SM" => "San Marino",
			"SN" => "Senegal",
			"SO" => "Somalia",
			"SR" => "Suriname",
			"ST" => "Sao Tome and Principe",
			"SU" => "USSR (former)",
			"SV" => "El Salvador",
			"SY" => "Syria",
			"SZ" => "Swaziland",
			"TC" => "Turks and Caicos Islands",
			"TD" => "Chad",
			"TF" => "French Southern Territories",
			"TG" => "Togo",
			"TH" => "Thailand",
			"TJ" => "Tajikistan",
			"TK" => "Tokelau",
			"TM" => "Turkmenistan",
			"TN" => "Tunisia",
			"TO" => "Tonga",
			"TP" => "East Timor",
			"TR" => "Turkey",
			"TT" => "Trinidad and Tobago",
			"TV" => "Tuvalu",
			"TW" => "Taiwan",
			"TZ" => "Tanzania",
			"UA" => "Ukraine",
			"UG" => "Uganda",
			"UK" => "United Kingdom",
			"UM" => "US Minor Outlying Islands",
			"US" => "United States",
			"UY" => "Uruguay",
			"UZ" => "Uzbekistan",
			"VA" => "Vatican City State (Holy See)",
			"VC" => "Saint Vincent and the Grenadines",
			"VE" => "Venezuela",
			"VG" => "Virgin Islands (British)",
			"VI" => "Virgin Islands (U.S.)",
			"VN" => "Viet Nam",
			"VU" => "Vanuatu",
			"WF" => "Wallis and Futuna Islands",
			"WS" => "Samoa",
			"YE" => "Yemen",
			"YT" => "Mayotte",
			"YU" => "Yugoslavia",
			"ZA" => "South Africa",
			"ZM" => "Zambia",
			"ZR" => "Zaire",
			"ZW" => "Zimbabwe",
		);
	}
};
?>
