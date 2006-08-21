<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/procurement_center/purchase.aw,v 1.1 2006/08/21 15:18:36 markop Exp $
// purchase.aw - Ost 
/*

@classinfo syslog_type=ST_PURCHASE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property date type=date_select
@caption Kuupäev

@property buyer type=relpicker reltype=RELTYPE_BUYER
@caption Ostja

@property bargainer type=relpicker reltype=RELTYPE_BUYER
@caption Hankija

@property buyer type=select
@caption Staatus

@groupinfo offers
@caption Pakkumised 
@default group=offers

@property offers type=table no_caption=1

@groupinfo files
@caption Failid 
@default group=files

@property files type=table no_caption=1

@groupinfo purchases
@caption Ostud 
@default group=purchases

@property purchases type=table no_caption=1


Ost on selline objektitüüp, mis seob omavahel Ostja (reeglina meie ise), Hankija ning mingid pakkumised. Esialgu piisab järgmistest propertytest (edaspidi on võimalik Ostu juures Pakkumise ridade kuvamine ja kuupäevade valimine, millal mingi Ostu osa täideti):
Nimetus (siia kirjutatakse Ostu number)
Kuupäev
Ostja (vaikimisi minu organisatsioon, kuid saab otsida ka teisi)
Hankija (seos tarne teinud organisatsiooniga) 
Staatus (aktiivne, arhiveeritud)
Pakkumised (eraldi TAB, mille all on näha kõik pakkumised, mis on selle Ostuga seotud). Tabelis pakkumise kuupäev, pakkumise failid, vali. Toolbaril otsing, mille abil saab seostada teisi sama Hankija pakkumisi selle Ostu juurde. Samuti kustutamine (saab juba seostatud pakkumise eemaldada Ostu küljest).
Failid (eraldi TAB) ? saab uploadida samamoodi faile nagu Pakkumise juurde
Ostud (kuvatakse pakkumise read, mis on selle ostuga seotud)

@reltype BUYER value=1 clid=CL_CRM_PERSON,CL_CRM_COMPANY
@caption Ostja 

*/
class purchase extends class_base
{
	function purchase()
	{
		$this->init(array(
			"tpldir" => "applications/procurement_center/purchase",
			"clid" => CL_PURCHASE
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

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

//-- methods --//
}
?>
