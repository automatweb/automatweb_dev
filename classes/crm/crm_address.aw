<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_address.aw,v 1.5 2004/01/13 14:14:23 duke Exp $
// crm_address.aw - It's not really a physical address but a collection of data required to 
// contact a person.
/*
	@classinfo relationmgr=yes
	@tableinfo kliendibaas_address index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property name type=text
	@caption Nimi
	
	@default table=kliendibaas_address
	
	@property email type=textbox store=no editonly=1
	@caption Meiliaadress
	
	@property aadress type=textbox size=50 maxlength=100
	@caption Tänav/Küla
	
	@property postiindeks type=textbox size=5 maxlength=5
	@caption Postinindex
	
	@property linn type=relpicker reltype=RELTYPE_LINN 
	@caption Linn/Vald/Alev

	@property maakond type=relpicker reltype=RELTYPE_MAAKOND 
	@caption Maakond

	@property riik type=relpicker reltype=RELTYPE_RIIK 
	@caption Riik
	
	@property telefon type=relpicker reltype=RELTYPE_TELEFON
	@caption Telefon

	@property mobiil type=relpicker reltype=RELTYPE_MOBIIL
	@caption Mobiiltelefon

	@property faks type=relpicker reltype=RELTYPE_FAKS
	@caption Faks

	@property piipar type=textbox size=20 maxlength=20
	@caption Piipar
	

	@property kodulehekylg type=relpicker reltype=RELTYPE_WWW
	@caption Kodulehekülg
			
	@property comment type=textarea cols=65 rows=3 table=objects field=comment
	@caption Kommentaar
	
	property primary_mail type=relpicker reltype=RELTYPE_EMAIL table=kliendibaas_address field=e_mail group=settings
	caption Primaarne meiliaadress 

	@property xmail type=relmanager table=kliendibaas_address field=e_mail group=settings reltype=RELTYPE_EMAIL props=mail
	@caption Meiliaadressid

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

@reltype EMAIL value=5 clid=CL_ML_MEMBER
@caption E-mail

@reltype WWW value=6 clid=CL_EXTLINK
@caption Koduleht

@reltype TELEFON value=7 clid=CL_CRM_PHONE
@caption Telefon

@reltype MOBIIL value=8 clid=CL_CRM_PHONE
@caption Mobiil

@reltype FAKS value=9 clid=CL_CRM_PHONE
@caption Faks
*/

class crm_address extends class_base
{
	function crm_address()
	{
		$this->init(array(
			"clid" => CL_CRM_ADDRESS,
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;

		switch($data["name"])
		{
			case "email":
				$pm = $arr["obj_inst"]->prop("primary_mail");
				if ($pm)
				{
					$obj = new object($pm);
					$data["value"] = $obj->prop("mail");
				}
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
						$name[] = 'tel:'.$form["telefon"];
					};
				}

				$arr["obj_inst"]->set_name(join(", ",$name));
				$retval = PROP_IGNORE;
				break;

			case "email":
				//$this->create_email_obj($arr);
				break;

			case "xmail":
				$this->process_xmail($arr);
				break;



				
		};
		return $retval;
	}	

	function create_email_obj($arr)
	{
		$this->set_email_addr(array(
			"obj_id" => $arr["obj_inst"]->id(),
			"email" => $arr["prop"]["value"],
		));
	}

	////
	// !Sets the email address (and creates any required objects)
	// obj_id - object id of address object
	// email - email address
	function set_email_addr($arr)
	{
		$obj_inst = new object($arr["obj_id"]);
		$primary_mail = $obj_inst->prop("primary_mail");
		if (is_oid($primary_mail))
		{
			// update the e-mail address in that object
			$pc = new object($primary_mail);
			$val = $arr["email"];
			$pc->set_name($val);
			$pc->set_prop("mail",$val);
			$pc->save();
		}
		else
		{
			// create a new one and connect it to the current object
			$pc = new object();
			$pc->set_class_id(CL_ML_MEMBER);
			// aw.. the thing does not have an address eh?
			$pc->set_parent($obj_inst->parent());
			$pc->set_name($arr["email"]);
			$pc->set_prop("mail",$arr["email"]);
			$pc->save();

			$obj_inst->connect(array(
				"to" => $pc->id(),
				"reltype"=> RELTYPE_EMAIL,
			));

			$obj_inst->set_prop("primary_mail",$pc->id());
		};
	}

	function process_xmail($arr)
	{
		/*
			[cb_emb] => Array
			(
			    [xmail] => Array
				(
				    [new] => Array
					(
					    [mail] => eleleeleeee
					)

				)

			)
		*/

		// parent comes from the form. I might want to validate it though
		// Now I need to figure out the class .. action is submit.

		$prop = $arr["prop"];

		$target_reltype = constant($prop["reltype"]);
		$clid = $arr["relinfo"][$target_reltype]["clid"][0];

		// now get a bloody instance of that object.

		$inst = get_instance($clid);
		$inst->id_only = true;
		
		$req = $arr["request"]["cb_emb"]["xmail"];

		if (empty($req["new"]["mail"]))
		{
			return false;
		}

		$member_id = $inst->submit(array(
			"name" => $req["new"]["mail"],
			"mail" => $req["new"]["mail"],
			"parent" => $req["new"]["parent"],
		));
			
		$arr["obj_inst"]->connect(array(
			"to" => $member_id,
			"reltype"=> RELTYPE_EMAIL,
		));

		// now connect it.
		/*

		print_r($inst);

		print "teeheee";

		print "<pre>";
		print_r($clid);
		print "--";
		print_r($arr["relinfo"]);
		print "</pre>";

		$prop = $arr["prop"];
		
		print "processing xmail";
		print "<pre>";
		print_r($req);
		print "---<br>";
		print_r($prop);
		print "</pre>";
		*/

	}

};
?>
