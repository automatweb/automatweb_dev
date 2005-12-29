<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_db_search.aw,v 1.1 2005/12/29 17:23:56 ekke Exp $
// crm_db_search.aw - Kliendibaasi otsingu grupp 
/*

@classinfo syslog_type=ST_CRM_DB_SEARCH relationmgr=yes no_comment=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property crm_db type=relpicker reltype=RELTYPE_CRM_DB automatic=1 field=meta method=serialize
@caption Kliendibaas

@property url type=textbox
@caption Url, millele lisatakse org. objekti id

@reltype CRM_DB value=1 clid=CL_CRM_DB
@caption Organisatsioonide andmebaas

*/

class crm_db_search extends class_base
{
	function crm_db_search()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/crm/crm_db_search",
			"clid" => CL_CRM_DB_SEARCH
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them
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

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
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

	// For site search content
	function scs_get_search_results($arr)
	{
		$searcher = $arr['obj'];
		$oid = $arr['group'];
		$o = obj($oid);
		if (!is_oid($o->prop('crm_db')))
		{
			return;
		}
		
		$wvinst = get_instance(CL_CRM_COMPANY_WEBVIEW);
		$list = $wvinst->_list_companies(array(
			'crm_db' => $o->prop('crm_db'),
			'limit_plaintext' => $arr['str'],
		));
		return $list;
	}

	function scs_display_search_results($arr)
	{
		$ob = obj($arr['group']);
		$url = $ob->prop('url');
		if (empty($url))
		{
			$url = '/org?org=';
		}
		$c = count($arr['results']);
		$out .= sprintf(t("Otsisid '<b>%s</b>', "), $arr['str']);
		
		$out .= sprintf( $c == 1 ? t('Leiti %s asutus.') : t('Leiti %s asutust.'), count($arr['results']));
		$out .= '<br>';
	
		$wvinst = get_instance(CL_CRM_COMPANY_WEBVIEW);
		$wvinst->read_template("default.tpl");
		$out .=  $wvinst->_get_companies_list_html(array(
			'list' => $arr['results'],
			'do_link' => true,
			'url' => $url,
		));
		return $out;
	}
		
}

?>
