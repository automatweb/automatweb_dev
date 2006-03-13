<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_address.aw,v 1.17 2006/03/13 13:42:14 kristo Exp $
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
	
	@property postiindeks type=textbox size=5 maxlength=5
	@caption Postiindeks
	
	@property linn type=relpicker reltype=RELTYPE_LINN automatic=1
	@caption Linn/Vald/Alev

	@property maakond type=relpicker reltype=RELTYPE_MAAKOND automatic=1
	@caption Maakond

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
};
?>
