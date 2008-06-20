<?php
/*
@classinfo syslog_type=ST_PACKAGE_CLIENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop
@tableinfo aw_package_client master_index=brother_of master_table=objects index=aw_oid

@default table=aw_package_client
@default group=general

@property packages_server type=textbox table=aw_package_client field=packages_server
@caption Pakiserveri url

@groupinfo packages caption="Pakid" no_submit=1
@default group=packages

	@property toolbar type=toolbar no_caption=1
	@caption T&ouml;&ouml;riistariba

	@layout packages_frame type=hbox width=20%:80%

		@layout packages_search type=vbox parent=packages_frame 

			@property search_name type=textbox size=20 store=no captionside=top parent=packages_search
			@caption Nimi

			@property search_version type=textbox size=20 store=no captionside=top parent=packages_search
			@caption Versioon

			@property search_file type=textbox size=20 store=no captionside=top parent=packages_search
			@caption Fail

			@property search_button type=submit no_caption=1 parent=packages_search
			@caption Otsi

		@layout packages_list type=vbox parent=packages_frame

			@property list type=table no_caption=1 parent=packages_list store=no
			@caption Pakkide nimekiri

			@property files_list type=text no_caption=1 parent=packages_list store=no
			@caption Failide nimekiri

*/

class package_client extends class_base
{
	function package_client()
	{
		$this->init(array(
			"tpldir" => "applications/package_management/package_client",
			"clid" => CL_PACKAGE_CLIENT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case 'search_name':
			case 'search_version':
			case 'search_file':
				$prop['value'] = $arr['request'][$prop['name']];
				break;
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function callback_mod_retval($arr)
	{
		if (!empty($arr['request']['search_button']))
		{
			$arr['args']['search_name'] = $arr['request']['search_name'];
			$arr['args']['search_version'] = $arr['request']['search_version'];
			$arr['args']['search_file'] = $arr['request']['search_file'];
		}
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_package_client(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "packages_server":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "varchar(255)"
				));
				return true;
		}
	}

	function _get_files_list($arr)
	{
		$ret = t("Failid:")."<br>";
		if($arr["request"]["show_files"])
		{
			$files = $arr["obj_inst"]-> get_files_list($arr["request"]["show_files"]);
			$arr['prop']["value"] = $ret.join("<br>", $files);
			return PROP_OK;
		}
		return PROP_IGNORE;
	}

	/**
	@attrib name=download_package api=1 params=name
		@param client_id required type=oid
			packaging client
		@param package_id required type=oid
			package file id in server
		@param return_url required type=string
			Url to return
	**/
	function download_package($arr)
	{
		$client = obj($arr["client_id"]);
		$client->download_package($arr["package_id"]);
		return $arr["return_url"];
	}

	function _get_list($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->set_caption(t('Pakkide nimekiri'));

		$t->define_chooser(array(
			'name' => 'selected_ids',
			'field' => 'select',
			'width' => '5%'
		));

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

		$t->define_field(array(
			'name' => 'dep',
			'caption' => t('S&otilde;ltuvused'),
		));

		$t->define_field(array(
			'name' => 'down',
			'caption' => t('x'),
		));

		$filter = array(
			'search_name' => $arr['request']['search_name'],
			'search_version' => $arr['request']['search_version'],
			'search_file' => $arr['request']['search_file'],
		);
		$packages = $arr["obj_inst"]->get_packages($filter);

		foreach ($packages as $data)
		{
			$down_url = $this->mk_my_orb("download_package", array(
				"client_id" => $arr["obj_inst"]->id(),
				"package_id" =>  $data["id"],
				"return_url" => get_ru(),
			));
			$t->define_data(array(
				'select' => $data["id"],
				'name' => html::href(array("caption"=> $data["name"] , "url" => aw_url_change_var("show_files" , $data["id"]))),
				'version' => $data["version"],
				'down' => html::href(array("caption"=> t("Download") , "url" => $down_url)),
			));
		}
		return PROP_OK;
	}

}

?>
