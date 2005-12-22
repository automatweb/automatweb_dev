<?php
/*

this message will be sent when the contents of the popup search listbox change
so that clients can perform actions based on the change
EMIT_MESSAGE(MSG_POPUP_SEARCH_CHANGE)

*/
class popup_search extends aw_template
{
	function popup_search()
	{
		$this->init("popup_search");
	}

	function init_vcl_property($arr)
	{
		$style = isset($arr['property']['style']) ? $arr['property']['style'] : 'default'; // Options: default, relpicker
		$reltype = "";
	
		$options = array();
		
		if ($style == 'default')
		{
			$name = "popup_search[".$arr["property"]["name"]."]";
			if (is_object($arr["obj_inst"]))
			{
				if (is_array($arr["obj_inst"]->meta($name)))
				{
					$options +=  $arr["obj_inst"]->meta($name);
				}
			}

			if (count($options) > 0)
			{
				$ol = new object_list(array(
					"oid" => $options
				));
				$options = $ol->names();
			}
		}
		else if ($style == 'relpicker')
		{
			if (is_object($arr["obj_inst"]) && isset($arr['property']['reltype']) && isset($arr['relinfo'][$arr['property']['reltype']])  && is_oid($arr['obj_inst']->id()))
			{
				$reltype = $arr['property']['reltype'];
				$conn = $arr['obj_inst']->connections_from(array(
						"type" => $reltype
				));
				foreach($conn as $c)
				{
					$options[$c->prop("to")] = $c->prop("to.name");
				}
			}
		}

		$tmp = $arr["property"];

		$tmp["type"] = "text";
		if (!$tmp["clid"] && $tmp["reltype"])
		{
			$clss = aw_ini_get("classes");
			$clid = new aw_array($arr["relinfo"][$reltype]["clid"]);
			$tmp["clid"] = array();
			foreach($clid->get() as $clid)
			{
				$tmp["clid"][] = $clss[$clid]["def"];
			}
		}

		if (is_object($arr["obj_inst"]))
		{
			$clid = array();
			$awa = new aw_array($tmp["clid"]);
			foreach($awa->get() as $clid_str)
			{
				$clid[] = constant($clid_str);
			}
			$url = $this->mk_my_orb("do_search", array(
				"id" => $arr["obj_inst"]->id(),
				"pn" => $tmp["name"],
				"clid" => $clid,
				"multiple" => $arr["property"]["multiple"]
			));
		}

		if (is_array($tmp["options"]) && count($tmp["options"]))
		{
			$options = $tmp["options"];
		}

		$sel = $arr["property"]["value"];
		if ($arr["property"]["multiple"] == 1)
		{
			if (!(is_array($sel) && count($sel)) && is_object($arr["obj_inst"]))
			{
				$sel =  $arr["obj_inst"]->prop($arr["property"]["name"]);
			}
		}
		else
		if (!$this->can("view", $sel) && is_object($arr["obj_inst"]))
		{
			$sel =  $arr["obj_inst"]->prop($arr["property"]["name"]);
		}

		
		$tmp["value"] = html::select(array(
			"name" => $arr["property"]["name"],
			"options" => array("" => "--Vali--") + $options,
			"selected" => $sel,
			"multiple" => $arr["property"]["multiple"]
		));

		if (is_object($arr["obj_inst"]) && is_oid($arr["obj_inst"]->id()))
		{
			$tmp["value"] .= html::href(array(
				"url" => "javascript:aw_popup_scroll(\"$url\",\"Otsing\",550,500)",
				"caption" => "<img src='".aw_ini_get("baseurl")."/automatweb/images/icons/search.gif' border=0>",
				"title" => t("Otsi")
			));

			if ($this->can("view", ($_id = $arr["obj_inst"]->prop($arr["property"]["name"]))))
			{
				$tmp["value"] .= " ";
				$tmp["value"] .= html::href(array(
					"url" => html::get_change_url($_id, array("return_url" => get_ru())),
					"caption" => "<img src='".aw_ini_get("baseurl")."/automatweb/images/icons/edit.gif' border=0>",
					"title" => t("Muuda")
				));

			}

			// add new
			if ($arr['property']['reltype'])
			{
				$clss = aw_ini_get("classes");
				$clid = new aw_array($arr["relinfo"][$arr['property']['reltype']]["clid"]);
				$rel_val = $arr["relinfo"][$arr['property']['reltype']]["value"];
				if ($clid->count() > 1)
				{
					$pm = get_instance("vcl/popup_menu");
					$pm->begin_menu($arr["property"]["name"]."_relp_pop");
					foreach($clid->get() as $_clid)
					{
						$pm->add_item(array(
							"text" => $clss[$_clid]["name"],
							"link" => html::get_new_url(
								$_clid, 
								$arr["obj_inst"]->id(), 
								array(
									"alias_to" => $arr["obj_inst"]->id(), 
									"reltype" => $rel_val,
									"return_url" => get_ru()
								)
							)
						));
					}
					$tmp["value"] .= " ".$pm->get_menu(array(
						"icon" => "new.gif",
						"alt" => t("Lisa")
					));
				}
				else
				{
					foreach($clid->get() as $cl)
					{
						$tmp["value"] .= " ".html::href(array(
							"url" => html::get_new_url(
								$cl, 
								$arr["obj_inst"]->id(), 
								array(
									"alias_to" => $arr["obj_inst"]->id(), 
									"reltype" => $rel_val,
									"return_url" => get_ru()
								)
							),
							"caption" => "<img src='".aw_ini_get("baseurl")."/automatweb/images/icons/new.gif' border=0>",
							"title" => sprintf(t("Lisa uus %s"), $clss[$cl]["name"])
						));
					}
				}
			}
			// link to unlinkink page if there are any options and if we'ere in relpicker mode i guess
			if (count($options) && isset($arr['property']['style']) && $arr['property']['style'] == 'relpicker' && isset($arr['property']['reltype']))
			{
				$url2 = $this->mk_my_orb("do_unlink", array(
					"id" => $arr["obj_inst"]->id(),
					"pn" => $tmp["name"],
					"clid" => $clid,
				));
				$tmp["value"] .= " ".html::href(array(
					"url" => "javascript:aw_popup_scroll(\"$url2\",\"Vali eemaldamiseks\",550,500)",
					"caption" => "<img src='".aw_ini_get("baseurl")."/automatweb/images/icons/delete.gif' border=0>",
					"title" => t("Eemalda")
				));
			}
		}
		return array(
			$arr["property"]["name"] => $tmp,
		);
	}

	function process_vcl_property($arr)
	{
		$arr["obj_inst"]->set_prop($arr["prop"]["name"], $arr["prop"]["value"]);
	}
				
				
	/**
		@attrib name=do_unlink

		@param id required type=int acl=view
		@param pn required 
		@param rem optional

		@comment
			With style=relpicker enables unlinking of the relations

	**/
	function do_unlink($arr)
	{
		$ob = obj($arr['id']);
		$props = $ob->get_property_list();
		$prop = $props[$arr['pn']];
		if (isset($prop['style']) && $prop['style'] == 'relpicker' && isset($prop['reltype']))
		{
			$return = "";
			if (isset($arr['id']) && is_oid($arr['id']) && $this->can('view', $arr['id']))
			{
				// If POSTed, handle results
				if ($_SERVER['REQUEST_METHOD'] == 'POST')
				{
				$value = $ob->prop($arr['pn']);
				$possible_value = null;
				$reltype = $prop['reltype'];
				foreach($ob->connections_from(array("type" => $reltype)) as $c)
				{
					$to = $c->to();
					if (isset($arr['rem'][$to->id()]))
					{
						// If unlinkable object is also prop value, set to no value
						if ($value == $to->id())
						{
							$value = null; // Actual saving after the loop
						}
						// Unlink
						$c->delete();
					}
					else if (empty($possible_value))
					{
						$possible_value = $to->id();
					}
				}
				if (empty($value))
				{
					$ob->set_prop($arr['pn'], $possible_value);
					$ob->save();
				}
				
				die("
					<html><body><script language='javascript'>
						window.opener.location.reload();
						window.close();
					</script></body></html>
				");
			}
			else
			{
				classload("cfg/htmlclient");
				$htmlc = new htmlclient(array(
					'template' => "default",
				));
				$htmlc->start_output();
				$htmlc->add_property(array(
					"caption" => t("Vali eemaldatavad objektid"),
				));

				foreach($ob->connections_from(array("type" => $prop['reltype'])) as $c)
				{
					$o = $c->to();
					$htmlc->add_property(array(
						"name" => "rem[".$o->id()."]",
						"type" => "checkbox",
						"caption" => $o->name(),
					));
				}

				$htmlc->add_property(array(
					"name" => "s[submit]",
					"type" => "submit",
					"value" => "Vali",
				));

				$htmlc->finish_output(array(
					"action" => "do_unlink",
					"method" => "POST",
					"data" => array(
						"id" => $arr["id"],
						"pn" => $arr["pn"],
						"append_html" => htmlspecialchars(ifset($arr,"append_html"), ENT_QUOTES),
						"orb_class" => "popup_search",
						"reforb" => 0
					)
				));

				$html = $htmlc->get_result();

				return $html;
			}
		}	
	  }
	}

	/**
		
		@attrib name=do_search

		@param id required type=int acl=view
		@param pn required 
		@param multiple optional
		@param clid required 
		@param s optional
		@param append_html optional

		@comment
			clid - not filtered by, if clid == 0
			append_html - additional html, inserted to tmpl {VAR:append}
	**/
	function do_search($arr)
	{
		$form_html = $this->_get_form($arr);

		$res_html = $this->_get_results($arr);

		return $form_html."<br>".$res_html;
	}

	function _get_form($arr)
	{
		$htmlc = get_instance("cfg/htmlclient");
		$htmlc->start_output();

		$htmlc->add_property(array(
			"name" => "s[name]",
			"type" => "textbox",
			"value" => $arr["s"]["name"],
			"caption" => t("Nimi")
		));

		/*$htmlc->add_property(array(
			"name" => "s[comment]",
			"type" => "textbox",
			"value" => $arr["s"]["comment"],
			"caption" => t("Kommentaar")
		));*/

		$htmlc->add_property(array(
			"name" => "s[oid]",
			"type" => "textbox",
			"value" => $arr["s"]["oid"],
			"caption" => t("Objekti id")
		));

		$htmlc->add_property(array(
			"name" => "s[submit]",
			"type" => "submit",
			"value" => "Otsi",
			"caption" => t("Otsi")
		));

		$htmlc->finish_output(array(
			"action" => "do_search",
			"method" => "GET",
			"data" => array(
				"id" => $arr["id"],
				"pn" => $arr["pn"],
				"multiple" => $arr["multiple"],
				"clid" => $arr["clid"],
				"append_html" => htmlspecialchars(ifset($arr,"append_html"), ENT_QUOTES),
				"orb_class" => "popup_search",
				"reforb" => 0
			)
		));

		$html = $htmlc->get_result();

		return $html;
	}

	function _get_results($arr)
	{
		$this->read_template("table.tpl");

		classload("vcl/table");
		$t = new aw_table(array(
			"layout" => "generic"
		));

		$t->define_field(array(
			"name" => "icon",
			"caption" => t("")
		));

		$t->define_field(array(
			"name" => "oid",
			"caption" => t("OID"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "name",
			"sortable" => 1,
			"caption" => t("Nimi")
		));

		$t->define_field(array(
			"name" => "parent",
			"sortable" => 1,
			"caption" => t("Asukoht")
		));

		$t->define_field(array(
			"name" => "modifiedby",
			"sortable" => 1,
			"caption" => t("Muutja")
		));
		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"sortable" => 1,
			"format" => "d.m.Y H:i",
			"type" => "time"
		));
		$t->define_field(array(
			"name" => "select_this",
			"caption" => t("Vali"),
		));
		$t->define_field(array(
			"name" => "sel",
			"caption" => t("<a href='javascript:void(0)' onClick='aw_sel_chb(document.cf,\"sel\")'>Vali</a>")
		));
		$t->set_default_sortby("name");

		$filter = array();
		if ($arr['clid'] > 0)
		{
			$filter['class_id'] = $arr['clid'];
		}	

		$awa = new aw_array($arr["s"]);
		foreach($awa->get() as $k => $v)
		{
			if ($v != "")
			{
				$filter[$k] = map("%%%s%%", explode(",", $v));
			}
		}
		if (count($filter) > 1 || $_GET["MAX_FILE_SIZE"])
		{
			// Pre-check checkboxes for relpicker
			$checked = array ();
			if (isset($arr['id']) && is_oid($arr['id']) && $this->can('view', $arr['id']))
			{
				$ob = obj($arr['id']);
				$props = $ob->get_property_list();
				$prop = $props[$arr['pn']];
				if (isset($prop['style']) && $prop['style'] == 'relpicker' && isset($prop['reltype']))
				{
					foreach($ob->connections_from(array("type" => $prop['reltype'])) as $c)
					{
						$checked[$c->prop("to")] = 1;
					}
				}
			}
			$filter["lang_id"] = array();
			$filter["site_id"] = array();
			$ol = new object_list($filter);

			$elname = $arr["pn"];
			if ($arr["multiple"] == 1)
			{
				$elname .= "[]";
			}
			classload("core/icons");
			for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
			{
				$t->define_data(array(
					"oid" => $o->id(),
					"name" => html::href(array(
						"url" => $this->mk_my_orb("change", array("id" => $o->id()), $o->class_id()),
						"caption" => $o->name(),
					)),
					"parent" => $o->path_str(array("max_len" => 3)),
					"modifiedby" => $o->modifiedby(),
					"modified" => $o->modified(),
					"sel" => html::checkbox(array(
						"name" => "sel[]",
						"value" => $o->id(),
						"checked" => 0 //isset($checked[$o->id()]) ? $checked[$o->id()] : 0,
					)),
					"select_this" => html::href(array(
						"url" => "javascript:void(0)",
						"caption" => t("Vali see"),
						"onClick" => "el=aw_get_el(\"$elname\",window.opener.document.changeform); sz= el.options.length;el.options.length=sz+1;el.options[sz].value=".$o->id().";el.options[sz].selected = 1;window.opener.document.changeform.submit();window.close()"
					)),
					"icon" => html::img(array("url" => icons::get_icon_url($o->class_id())))
				));
			}
		}

		$t->sort_by();
		$this->vars(array(
			"table" => $t->draw(),
			"reforb" => $this->mk_reforb("final_submit", array(
				"id" => $arr["id"],
				"pn" => $arr["pn"],
				"multiple" => $arr["multiple"],
				"clid" => $arr["clid"],
				"append_html" => htmlspecialchars(ifset($arr,"append_html"), ENT_QUOTES),
			)),
			"append" => ifset($arr,"append_html"),
		));

		return $this->parse();
	}

	/**

		@attrib name=final_submit all_args="1" 

	**/
	function final_submit($arr)
	{
		// available options are in metadata, selected option value of the property
		$o = obj($arr["id"]);
		$o->set_meta("popup_search[".$arr["pn"]."]", $this->make_keys($arr["sel"]));
		if (is_array($arr["sel"]) && count($arr["sel"]) == 1)
		{
			$o->set_prop($arr["pn"], $arr["sel"][0]);
		}
		$o->save();
		
		// if relpicker, define relations
		$props = $o->get_property_list();
		$prop = $props[$arr['pn']];
		if (isset($prop['style']) && $prop['style'] == 'relpicker' && isset($prop['reltype']))
		{
			$reltype = $prop['reltype'];
			foreach($o->connections_from(array("type" => $reltype)) as $c)
			{
				$c->delete();
			}
			if (isset($arr['sel']) && is_array($arr['sel']))
			{
				foreach($arr['sel'] as $i => $id)
				{
					if (is_oid($id) && $this->can("view", $id))
					{
						$object = obj($id);
						$o->connect(array(
							"to" => $object->id(),
							"reltype" => $reltype,
						));
					}
				}
			}
		}
		
		// emit message so objects can update crap
		post_message_with_param(MSG_POPUP_SEARCH_CHANGE, $o->class_id(), array(
			"oid" => $o->id(),
			"prop" => $arr["pn"],
			"options" => $this->make_keys($arr["sel"]),
			"arr" => $arr,
		));
		if ($arr["multiple"] == 1)
		{
			$str = "
				<html><body><script language='javascript'>
function aw_get_el(name,form)
{
    if (!form)
	{
        form = document.changeform;
	}
    for(i = 0; i < form.elements.length; i++)
	{
        el = form.elements[i];
        if (el.name.indexOf(name) != -1)
		{
			return el;
		}
	}
}

					el = aw_get_el('".$arr["pn"]."[]', window.opener.document.changeform);
					//el.selectedIndex = 0;
			";
			foreach(safe_array($arr["sel"]) as $idx => $val)
			{
				$str .= "sz = el.options.length;";
				$str .= "el.options.length=sz+1;";
				$str .= "el.options[sz].value = $val;el.options[sz].selected = 1;";
			}
			$str .= "window.opener.document.changeform.submit();
					window.close()
				</script></body></html>
			";
			die($str);
		}
		else
		{
			die("
				<html><body><script language='javascript'>
				if(window.opener.document.changeform.".$arr["pn"].")
				{
					window.opener.document.changeform.".$arr["pn"].".selectedIndex=0;
					window.opener.document.changeform.".$arr["pn"].".options[0].value=\"".$arr["sel"][0]."\";
				}	
					window.opener.document.changeform.submit();
					window.close()
				</script></body></html>
			");
		}
	}

	/** sets the options for the given objects given popup search property
		
		@param obj required
		@param prop required
		@param opts required

		@comment
			obj - the object whose options to set
			prop - the property's options in that object to set
			opts - array of object id's that the user can select from that property
	**/
	function set_options($arr)
	{
		$arr["obj"]->set_meta("popup_search[".$arr["prop"]."]", $this->make_keys($arr["opts"]));
		if (count($arr["opts"]) == 1)
		{
			$first = reset($arr["opts"]);
			$arr["obj"]->set_prop($arr["prop"], $first);
		}
		$arr["obj"]->save();
	}
}
?>
