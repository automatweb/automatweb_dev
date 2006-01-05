<?php
/*
	@tableinfo kliendibaas_tegevusala index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property tegevusala type=textbox size=55 table=kliendibaas_tegevusala
	@caption Tegevusala nimetus

	@property tegevusala_en type=textbox size=55 table=kliendibaas_tegevusala
	@caption Inglise keelne nimetus
		
	@property comment type=textarea field=comment
	@caption Kirjeldus

	@property kood type=textbox size=8 table=kliendibaas_tegevusala
	@caption Tegevusala kood

	@classinfo no_status=1 syslog_type=ST_CRM_SECTOR


@groupinfo transl caption=T&otilde;lgi
@default group=transl
	
	@property transl type=callback callback=callback_get_transl
	@caption T&otilde;lgi

*/

/*

CREATE TABLE `kliendibaas_tegevusala` (
  `oid` int(11) NOT NULL default '0',
  `kood` varchar(30) default NULL,
  `tegevusala` text,
  `tegevusala_en` text,
  `kirjeldus` text,
  PRIMARY KEY  (`oid`),
  UNIQUE KEY `oid` (`oid`),
  KEY `kood_i` (`kood`)
) TYPE=MyISAM;
*/

class crm_sector extends class_base
{
	function crm_sector()
	{
		$this->init(array(
			'clid' => CL_CRM_SECTOR,
		));

		$this->trans_props = array(
			"tegevusala", "comment"
		);
	}

	function get_property($arr)
	{
		$data = &$arr['prop'];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case 'name':
				$retval = PROP_IGNORE;
				break;
			
		}
		return  $retval;
	}
	
	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		$form = &$arr["request"];
		switch($data["name"])
		{
			case "transl":
				$this->trans_save($arr, $this->trans_props);
				break;

			case 'kood':
				$arr["obj_inst"]->set_name(($form['kood'] ? ''.$form['kood'].' ' : '').$form['tegevusala']);
				break;
		};
		return $retval;
	}	

	function callback_get_transl($arr)
	{
		return $this->trans_callback($arr, $this->trans_props);
	}

	function callback_mod_tab($arr)
	{
		if ($arr["id"] == "transl" && aw_ini_get("user_interface.content_trans") != 1)
		{
			return false;
		}
		return true;
	}
}
?>
