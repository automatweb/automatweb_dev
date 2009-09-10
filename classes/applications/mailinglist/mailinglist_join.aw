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

@property multiple_folders type=checkbox ch_value=1
@caption Lase kausta valida

@property multiple_languages type=checkbox ch_value=1
@caption Lase valida keelt

@property redir_obj type=relpicker reltype=RELTYPE_REDIR_OBJECT rel=1
@caption Dokument millele suunata

@property mail type=relpicker reltype=RELTYPE_SUBSCRIBE_MAIL store=connect
@caption Liitumise kirja templeit

@reltype REDIR_OBJECT value=1 clid=CL_DOCUMENT
@caption &Uuml;mbersuunamine

@reltype SUBSCRIBE_MAIL value=2 clid=CL_MESSAGE_TEMPLATE
@caption Liitumise kirja templeit
*/

class mailinglist_join extends class_base
{
	function mailinglist_join()
	{
		$this->init(array(
			"tpldir" => "applications/mailinglist/mailinglist_join",
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
		$object = obj($args["alias"]["target"]);
		enter_function("ml_list_join::parse_alias");
		$tpl = ($object->prop("sub_form_type") == 0) ? "subscribe.tpl" : "unsubscribe.tpl";
		$this->read_template($tpl);
		lc_site_load("ml_list", &$this);
		if ($this->is_template("FOLDER") && $tobj->prop("multiple_folders") == 1 && $sub_form_type == 0)
		{
			$langid = aw_global_get("lang_id");
 			$c = "";
		}

// 		
// 
// 		
// 		if ($this->is_template("FOLDER") && $tobj->prop("multiple_folders") == 1 && $sub_form_type == 0)
// 		{
// 			$langid = aw_global_get("lang_id");
// 			$c = "";
// 			$choose_menu = $targ->prop("choose_menu");	
// 			foreach($choose_menu as $folder)
// 			{
// 				$folder_obj = obj($folder);
// 				$folders = $folder_obj->connections_from(array(
// 					"type" => "RELTYPE_LANG_REL",
// 					"to.lang_id" => $langid,
// 				));
// 
// 				if($langid == $folder_obj -> lang_id())
// 				{
// 					$this->vars(array(
// 						"folder_name" => $folder_obj -> trans_get_val("name"),
// 						"folder_id" => $folder_obj -> id(),
// 					));
// 					$c .= $this->parse("FOLDER");
// 				}
// 				
// 				else
// 				{
// 					if(count($folders)>1)
// 					{
// 						foreach($folders as $folder_conn)
// 						{
// 							$conn_fold_obj = obj($folder_conn->prop("to"));
// 							if(($langid == $conn_fold_obj->lang_id()) && ($folder_conn->prop("from") == $folder))
// 							{
// 								$this->vars(array(
// 									"folder_name" => $conn_fold_obj->trans_get_val("name"),
// //									"folder_name" => $folder_conn->prop("to.name"),
// 									"folder_id" => $folder_conn->prop("to"),
// 								));
// 								$c .= $this->parse("FOLDER");
// 							}
// 						}
// 					}
// 					else
// 					{
// 						$conns_to_orig = $folder_obj->connections_to(array(
// 							"type" => 22,
// //							"to.lang_id" => $langid,
// 						));
// 						foreach($conns_to_orig as $conn)
// 						{
// 							if($conn->prop("from.lang_id") == $langid)
// 							{
// 								$this->vars(array(
// 								"folder_name" => $conn->prop("from.name"),
// 								"folder_id" => $conn->prop("from"),
// 								));
// 								$c .= $this->parse("FOLDER");
// 							}
// 							else 
// 							{
// 								$from_obj = obj($conn->prop("from"));
// 								$conns = $from_obj->connections_from(array(
// 									"type" => "RELTYPE_LANG_REL",
// 									"to.lang_id" => $user_lang,
// 								));
// 								foreach($conns as $conn)
// 								{
// 									$this->vars(array(
// 									"folder_name" => $conn_fold_obj->trans_get_val("name"),
// //"folder_name" => $conn->prop("to.name"),
// 									"folder_id" => $conn->prop("to"),
// 									));
// 									$c .= $this->parse("FOLDER");
// 								}			
// 							}
// 						}						
// 					}
// 				}
// 			}
// 			$this->vars(array(
// 				"FOLDER" => $c,
// 			));	
// 		}
// 		if ($this->is_template("LANGFOLDER") && $tobj->prop("multiple_languages") == 1)
// 		{
// 			$lg = get_instance("languages");
// 			$langdata = array();
// 			$langdata = $lg->get_list();
// 			$c = "";
// 			$choose_languages = $targ->prop("choose_languages");
// 			foreach($langdata as $id => $lang)
// 			{
// 				if(in_array($id, $choose_languages))
// 				{	
// 					$this->vars(array(
// 						"lang_name" => $lang,
// 						"lang_id" => $id,
// 					));
// 					$c .= $this->parse("LANGFOLDER");
// 				}	
// 			}			
// 			$this->vars(array(
// 				"LANGFOLDER" => $c,
// 			));	
// 		}
// 		
// 		if ($this->is_template("FOLDER") && $tobj->prop("multiple_folders") == 1 && $sub_form_type == 1)
// 		{
// 			$this->parse_unsubscribe($tobj);
// 		}
// 
// 		$this->vars(array(
// 			"listname" => $tobj->name(),
// 			"cb_errmsg" => $cb_errmsg,
// 			"reforb" => $this->mk_reforb("subscribe",array(
// 				"id" => $targ->id(),
// 				"rel_id" => $relobj->id(),
// 				"section" => aw_global_get("section"),
// 			)),
// 		));
		exit_function("ml_list_join::parse_alias");
		return $this->parse();

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
