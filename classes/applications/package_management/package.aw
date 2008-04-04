<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/package_management/package.aw,v 1.2 2008/04/04 11:50:46 markop Exp $
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

@groupinfo dependencies caption="S&otilde;ltuvused" no_submit=1
@default group=dependencies

	@property dep_toolbar type=toolbar no_caption=1
	@caption Seoste toolbar

	@property dependencies type=table no_caption=1
	@caption S&otilde;ltuvused

@reltype DEPENDENCY value=1 clid=CL_PACKAGE
@caption S&otilde;ltuvus

@reltype FILE value=2 clid=CL_FILE
@caption Fail

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

	function _get_dep_toolbar($arr)
	{
	
		$t = &$arr['prop']['vcl_inst'];
		
		$t->add_button(array(
			'name' => 'new',
			'img' => 'new.gif',
			'tooltip' => t('Uus pakk'),
			'url' => $this->mk_my_orb('new', array(
				'parent' => $arr['obj_inst']->parent(),
				'return_url' => get_ru(),
				"alias_to" => $arr["obj_inst"]->id(),
				"reltype" => 1,
			), CL_PACKAGE),
		));

		$t->add_button(array(
			'name' => 'delete',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta s&otilde;ltuvused'),
			'action' => 'remove_dep_packages',
			'confirm' => t('Oled kindel et soovid valitud s&otilde;ltuvused kustutada?')
		));

		return PROP_OK;
	}

	/**
		@attrib name=remove_dep_packages
	**/
	function remove_dep_packages($arr)
	{
		$obj = obj($arr["id"]);
		foreach($arr["sel"] as $dep)
		{
			$obj->disconnect(array("from" => $dep));
		}
		return $arr['post_ru'];
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
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid",
			"width" => "20px",
		));
		$dep_list = $arr["obj_inst"]->get_dependencies();
		foreach($dep_list->arr() as $dep)
		{
			$t->define_data(array(
				"name" => $dep -> name(),
				"version" => $dep -> prop("version"),
				"description" => $dep -> prop("description"),
				"oid" => $dep->id(),
			));
		}
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
