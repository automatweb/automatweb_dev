<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/package_management/package.aw,v 1.1 2008/01/25 10:33:30 dragut Exp $
// package.aw - Pakk 
/*

@classinfo syslog_type=ST_PACKAGE relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=dragut
@tableinfo aw_packages index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

	@property version type=textbox table=aw_packages field=version
	@caption Versioon

	@property description type=textarea rows=10 cols=40 table=aw_packages field=description
	@caption Kirjeldus

	@property dependencies type=table no_caption=1
	@caption S&otilde;ltuvused

@reltype DEPENDENCY value=1 clid=CL_PACKAGE
@caption S&otilde;ltuvus

*/

class package extends class_base
{
	function package()
	{
		$this->init(array(
			"tpldir" => "applications/package_management/package",
			"clid" => CL_PACKAGE
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

	function _get_dependencies($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->set_caption(t('Pakkide nimekiri'));

		$t->define_field(array(
			'name' => 'name',
			'caption' => t('Nimi')
		));
		$t->define_field(array(
			'name' => 'version',
			'caption' => t('Versioon'),
			'width' => '5%'
		));
		$t->define_field(array(
			'name' => 'description',
			'caption' => t('Kirjeldus')
		));

		return PROP_OK;
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

	function callback_pre_save($arr)
	{
		
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

	function do_db_upgrade($table, $field, $query, $error)
	{

		if (empty($field))
		{
			// db table doesn't exist, so lets create it:
			$this->db_query('CREATE TABLE '.$table.' (
				oid INT PRIMARY KEY NOT NULL,

				version varchar(255),
				description text
			)');
			return true;
		}

		switch ($field)
		{
			case 'version':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'varchar(255)'
				));
                                return true;
			case 'description':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'text'
				));
                                return true;
                }

		return false;
	}
}
?>
