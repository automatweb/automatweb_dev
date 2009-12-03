<?php
/*
@classinfo syslog_type=ST_MAILINGLIST_JOIN relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=smeedia
@tableinfo aw_mailinglist_join master_index=brother_of master_table=objects index=aw_oid

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property sub_form_type type=select rel=1
@caption Vormi t&uuml;&uuml;p

@property choose_languages type=select multiple=1
@caption Keeled millega v&otilde;ib liituda

@property choose_menus type=select multiple=1
@caption Kaustad millega liituda

@property multiple_folders type=checkbox ch_value=1
@caption Lase kausta valida

@property multiple_languages type=checkbox ch_value=1
@caption Lase valida keelt

@property redir_obj type=relpicker reltype=RELTYPE_REDIR_OBJECT rel=1
@caption Dokument millele suunata

@property mail type=relpicker reltype=RELTYPE_SUBSCRIBE_MAIL store=connect
@caption Liitumise kirja templeit

@property confirm type=checkbox ch_value=1 
@caption On vaja kinnitust

@property confirm_msg type=relpicker reltype=RELTYPE_ADM_MESSAGE
@caption Kinnituseks saadetav kiri

@reltype REDIR_OBJECT value=1 clid=CL_DOCUMENT
@caption &Uuml;mbersuunamine

@reltype SUBSCRIBE_MAIL value=2 clid=CL_MESSAGE_TEMPLATE
@caption Liitumise kirja templeit

@reltype ADM_MESSAGE value=3 clid=CL_MESSAGE
@caption administratiivne teade 
*/

class mailinglist_join extends class_base
{
	function mailinglist_join()
	{
		$this->init(array(
			"tpldir" => "automatweb/mlist",
			"clid" => CL_MAILINGLIST_JOIN
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "sub_form_type":
				$prop["options"] = array(
					"0" => t("liitumine"),
					"1" => t("lahkumine"),
				);
				break;
			case "choose_languages":
				$lg = get_instance("languages");
				$langdata = array();
				$prop["options"] = $lg->get_list();
				break;
			case "choose_menus":
				$prop["options"] = $arr["obj_inst"]->get_menu_options();
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

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}


	function parse_alias($args = array())
	{
		enter_function("ml_list_join::parse_alias");
		$object = obj($args["alias"]["target"]);
		$mailinglist = $object->get_mailinglist();
		$tpl = ($object->prop("sub_form_type") == 0) ? "subscribe.tpl" : "unsubscribe.tpl";
		$this->read_template($tpl);
		lc_site_load("ml_list", &$this);

		if ($this->is_template("FOLDER") && $object->prop("multiple_folders") == 1)
		{
			$langid = aw_global_get("lang_id");
 			$c = "";
			$menus = $object->get_menus();
			foreach($menus->arr() as $menu)
			{
				$this->vars(array(
					"folder_id" => $menu->id(),
					"folder_name" => $menu->name()
				));
				$c.= $this->parse("FOLDER");
			}

 			$this->vars(array(
 				"FOLDER" => $c,
 			));	

		}


 		if ($this->is_template("LANGFOLDER") && $object->prop("multiple_languages") == 1)
 		{
 			$lg = get_instance("languages");
 			$langdata = array();
 			$langdata = $lg->get_list();
 			$c = "";
 			$choose_languages = $object->prop("choose_languages");
			foreach($langdata as $id => $lang)
 			{
 				if(in_array($id, $choose_languages))
 				{	
 					$this->vars(array(
 						"lang_name" => $lang,
 						"lang_id" => $id,
 					));
 					$c .= $this->parse("LANGFOLDER");
 				}	
 			}			
 			$this->vars(array(
 				"LANGFOLDER" => $c,
 			));	
 		}

 		$this->vars(array(
 			"listname" => $mailinglist->name(),
// 			"cb_errmsg" => $cb_errmsg,
 			"reforb" => $this->mk_reforb("subscribe",array(
 				"id" => $object->id(),
// 				"rel_id" => $relobj->id(),
 				"section" => aw_global_get("section"),
 			)),
 		));
		exit_function("ml_list_join::parse_alias");
		return $this->parse();

	}

	/** (un)subscribe an address from(to) a list 
		@attrib name=subscribe nologin="1" 
		@param id required type=int 
		@param rel_id required type=int 
	**/
	function subscribe($args = array())
	{
		$join_object = obj($args["id"]);
		$join_object->subscribe($args);
		return $this->cfg["baseurl"] . "/" . $join_object->prop("redir_obj");
	}




	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_mailinglist_join(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => ""
				));
				return true;
		}
	}
}

?>
