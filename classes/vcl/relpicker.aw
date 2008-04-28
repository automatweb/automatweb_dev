<?php
class relpicker extends  core
{
	function relpicker()
	{
		$this->init("");
	}

	/**
		@attrib name=create_relpicker params=name api=1

		@param name required type=string
			String to indetify the relpicker

		@param reltype required type=string
			The reltype the relpicker uses

		@param oid required type=int
			The object's ID the relpicker picks relations for

		@param property required type=int
			The property's name that relpicker picks relations for

		@param multiple optional type=int

		@param no_sel optional type=int

		@param automatic optional type=int

		@param no_edit optional type=int

		@param options optional type=array
			Options to be displayed in the relpicker select box. Array(oid => caption).

		@param buttonspos optional type=string
			Position for buttons. Values: right, bottom. Default: right

		@returns The HTML of the relpicker.

		@examples

		$relpicker = get_instance(CL_RELPICKER);
		$relpicker->create_relpicker(array(
			"name" => "myRelpicker",
			"reltype" => 1,
			"oid" => 123,
			"property" => "myProperty",
		));

		$myOptions = array(
			1 => "Object1",
			2 => "Object2",
			3 => "Object3",
		);
		$relpicker = get_instance(CL_RELPICKER);
		$relpicker->create_relpicker(array(
			"name" => "myRelpicker",
			"reltype" => "RELTYPE_FOO",
			"oid" => 123,
			"property" => "myProperty",
			"options" => $myOptions,
		));

	**/
	function create_relpicker($arr)
	{
		extract($arr);

		if(!$this->can("view", $oid))
		{
			return false;
		}

		$o = new object($oid);
		$relinfo = $o->get_relinfo();
		$clids = $relinfo[$reltype]["clid"];

		if($o->is_property($property))
		{
			$selected = $o->prop($property);
		}

		if(!is_array($options))
		{
			$options = array();
		}

		if($no_sel != 1)
		{
			$options = array(0 => t("--vali--")) + $options;
		}

		// generate option list
		// if automatic is set, then create a list of all properties of that type
		if (isset($automatic))
		{
			foreach($clids as $clid)
			{
				if (!empty($clid))
				{
					$olist = new object_list(array(
						"class_id" => $clid,
						"site_id" => array(),
						"lang_id" => array(),
						"brother_of" => new obj_predicate_prop("id")
					));
					$names = $olist->names();
					asort($names);
					$options = $options + $names;
				}
			}
		}
		else
		{
			$conns = $o->connections_from(array(
				"type" => $reltype
			));

			foreach($conns as $conn)
			{
				$options[$conn->prop("to")] = $conn->prop("to.name");
			}
		}

		$r = html::select(array(
			"name" => $name,
			"options" => $options,
			"selected" => $selected,
		));

		if($buttonspos == "bottom")
		{
			$r .= "<br>";
		}

		$url = $this->mk_my_orb("do_search", array(
			"id" => $oid,
			"pn" => $name,
			"clid" => $clids,
			"multiple" => $multiple
		), "popup_search", false, true);

		if (!$no_edit)
		{
			$r .= " ".html::href(array(
				"url" => "javascript:aw_popup_scroll(\"$url\",\"Otsing\",550,500)",
				"caption" => "<img src='".aw_ini_get("baseurl")."/automatweb/images/icons/search.gif' border=0>",
				"title" => t("Otsi")
			));
		}
		if(!is_array($selected) && $this->can("edit", $selected) && !$no_edit)
		{
			$selected_obj = new object($selected);
			$selected_clid = $selected_obj->class_id();
			$r .= " ".html::href(array(
				"url" => $this->mk_my_orb("change", array(
					"id" => $selected,
					"return_url" => get_ru(),
				), $selected_clid),
//				"url" => html::get_change_url($selected_id, array("return_url" => get_ru())),
				"caption" => "<img src='".aw_ini_get("baseurl")."/automatweb/images/icons/edit.gif' border=0>",
				"title" => t("Muuda")
			));
		}
		if(!$no_edit)
		{
			$clss = aw_ini_get("classes");
			if (count($clid) > 1)
			{
				$pm = get_instance("vcl/popup_menu");
				$pm->begin_menu($name."_relp_pop");
				foreach($clids as $clid)
				{
					$pm->add_item(array(
						"text" => $clss[$clid]["name"],
						"link" => html::get_new_url(
							$clid,
							$oid,
							array(
								"alias_to" => $oid,
								"alias_to_prop" => $property,
								"reltype" => $reltype,
								"return_url" => get_ru()
							)
						)
					));
				}
				$r .= " ".$pm->get_menu(array(
					"icon" => "new.gif",
					"alt" => t("Lisa")
				));
			}
			else
			{
				foreach($clids as $clid)
				{
					$r .= " ".html::href(array(
						"url" => html::get_new_url(
							$clid,
							$oid,
							array(
								"alias_to_prop" => $property,
								"alias_to" => $oid,
								"reltype" => $reltype,
								"return_url" => get_ru()
							)
						),
						"caption" => "<img src='".aw_ini_get("baseurl")."/automatweb/images/icons/new.gif' border=0>",
						"title" => sprintf(t("Lisa uus %s"), $clss[$_clid]["name"])
					));
				}
			}
			return $r;
		}
	}

	function init_vcl_property($arr)
	{
		$prop = &$arr["property"];
		$this->obj = $arr["obj_inst"];
		if ($prop["mode"] == "autocomplete")
		{
			return $this->init_autocomplete_relpicker($arr);
		}

		$val = &$arr["property"];
		if($prop["no_sel"] == 1)
		{
			$options = array();
		}
		else
		{
			$options = array("0" => t("--vali--"));
		}
		$reltype = $prop["reltype"];
		// generate option list
		if (is_array($prop["options"]))
		{
			$val["type"] = "select";
		}
		else
		{
			// if automatic is set, then create a list of all properties of that type
			if (isset($prop["automatic"]))
			{
				$clid = $arr["relinfo"][$reltype]["clid"];
				$val["type"] = "select";
				if (!empty($clid))
				{
					$olist = new object_list(array(
						"class_id" => $clid,
						"site_id" => array(),
						"lang_id" => array(),
						"brother_of" => new obj_predicate_prop("id")
					));
					$names = $olist->names();
					asort($names);
					$val["options"] = $options + $names;
					/*if ($arr["id"])
					{
						$o = obj($arr["id"]);
						$conn = $o->connections_from(array(
							"type" => $reltype
						));
						$sel = array();
						foreach($conn as $c)
						{
							$sel[$c->prop("to")] = $c->prop("to");
						}
						$val["value"] = $sel;
					};*/
					// since when do automatic relpickers get all relations selected?!?!
				};
			}
			else
			{
				if ($arr["id"])
				{
					$o = obj($arr["id"]);
					$conn = array();

					if (!empty($val["clid"]))
					{
						$clids = (array) $val["clid"];

						foreach ($clids as $key => $clid)
						{
							$clids[$key] = ((strlen((int) $clid)) === strlen($clid)) ? (int) $clid : constant($clid);
							$error = empty($clids[$key]) ? true : $error;
						}

						if (!$error)
						{
							$conn = $o->connections_from(array(
								"to.class_id" => $clids,
								"type" => $reltype
							));
						}
					}
					else
					{
						$conn = $o->connections_from(array(
							"type" => $reltype
						));
					}

					foreach($conn as $c)
					{
						$options[$c->prop("to")] = $c->prop("to.name");
					}
					$val["options"] = $options;
				}
			}
		}
		$val["type"] = ($val["display"] == "radio") ? "chooser" : "select";

		if ($val["type"] == "select" /*&& is_object($this->obj)*/)
		{
			$clid = (array)$arr["relinfo"][$reltype]["clid"];
			$url = $this->mk_my_orb("do_search", array(
				"id" => is_object($arr["obj_inst"]) ? $arr["obj_inst"]->id() : null,
				"pn" => $arr["property"]["name"],
				"clid" => $clid,
				"multiple" => $arr["property"]["multiple"]
			), "popup_search", false, true);
			
			// I only want the search button. No edit or new buttons!
			if (/*is_oid($this->obj->id()) &&*/ !$val["no_edit"] || $val["search_button"])
			{
				$val["post_append_text"] .= " ".html::href(array(
					"url" => "javascript:aw_popup_scroll(\"$url\",\"Otsing\",550,500)",
					"caption" => "<img src='".aw_ini_get("baseurl")."/automatweb/images/icons/search.gif' border=0>",
					"title" => t("Otsi")
				));
			}
		}

		if ( $val["type"] == "select" && is_object($this->obj) && ((is_oid($val["value"]) && $this->can("edit", $val["value"])) ||
			(is_object($this->obj) && is_oid($this->obj->id()) && $this->obj->is_property($val["name"]) && is_oid($this->obj->prop($val["name"])) && $this->can("edit", $this->obj->prop($val["name"]))) ) && !$val["no_edit"])
		{
			$val["post_append_text"] .= " ".html::href(array(
				"url" => html::get_change_url($this->obj->prop($val["name"]), array("return_url" => get_ru())),
				"caption" => "<img src='".aw_ini_get("baseurl")."/automatweb/images/icons/edit.gif' border=0>",
				"title" => t("Muuda")
			));
		}
		
		if($val["delete_button"] || $val["delete_rels_button"])
		{
			$oids = array();
			$allow_delete = true;

			if(is_oid($val["value"]))
			{
				$oids[] = $val["value"];
			}
			else
			if(is_array($val["value"]))
			{
				$oids = $val["value"];
			}
			else
			if(is_object($this->obj) && is_oid($this->obj->id()) && $this->obj->is_property($val["name"]) && $this->can("edit", $this->obj->prop($val["name"])))
			{
				$oids[] = $this->obj->prop($val["name"]);
			}
			else
			if(is_object($this->obj) && is_oid($this->obj->id()) && $this->obj->is_property($val["name"]) && is_array($this->obj->prop($val["name"])))
			{
				$oids = $this->obj->prop($val["name"]);
			}
			else
			{
				$allow_delete = false;
			}

			foreach($oids as $oid)
			{
				if(!$this->can("edit", $oid))
				{
					$allow_delete = false;
					break;
				}
			}
		}
		if($val["type"] == "select" && is_object($this->obj) && $allow_delete)
		{
			if($val["delete_button"])
			{
				foreach($oids as $i => $oid)
				{
					$awucv["sel[".$i."]"] = $oid;
				}
				$awucv["action"] = "delete_objects";
				$awucv["post_ru"] = post_ru();
				$val["post_append_text"] .= " ".html::href(array(
					"url" => aw_url_change_var($awucv),
					//"url" => "javascript:submit_change_form('delete_objects')",
					"caption" => "<img src='".aw_ini_get("baseurl")."/automatweb/images/icons/delete.gif' border=0>",
					"title" => t("Kustuta valitud objektid"),
					"onclick" => "if(!alert('".t("Oled kindel, et soovid valitud objektid kustutada?")."')) { return false; };",
				));
			}
			if($val["delete_rels_button"])
			{
				foreach($oids as $i => $oid)
				{
					$awucv["sel[".$i."]"] = $oid;
				}
				$awucv["action"] = "delete_rels";
				$awucv["post_ru"] = post_ru();
				$val["post_append_text"] .= " ".html::href(array(
					"url" => aw_url_change_var($awucv),
					//"url" => "javascript:submit_change_form('delete_rels')",					
					"caption" => "<img src='".aw_ini_get("baseurl")."/automatweb/images/icons/delete.gif' border=0>",
					"title" => t("Kustuta valitud seosed"),
					"onclick" => "if(!alert('".t("Oled kindel, et soovid valitud seosed kustutada?")."')) { return false; };",
				));
			}
		}
		if ($val["type"] == "select" && is_object($this->obj) && is_oid($this->obj->id()) && !$val["no_edit"])
		{
			$clid = (array)$arr["relinfo"][$reltype]["clid"];
			$rel_val = $arr["relinfo"][$reltype]["value"];

			$clss = aw_ini_get("classes");
			if (count($clid) > 1)
			{
				$pm = get_instance("vcl/popup_menu");
				$pm->begin_menu($arr["property"]["name"]."_relp_pop");
				foreach($clid as $_clid)
				{
					$pm->add_item(array(
						"text" => $clss[$_clid]["name"],
						"link" => html::get_new_url(
							$_clid,
							$arr["prop"]["parent"] == "this.parent" ? $this->obj->parent() : $this->obj->id(),
							array(
								"alias_to" => $this->obj->id(),
								"alias_to_prop" => $arr["prop"]["name"],
								"reltype" => $rel_val,
								"return_url" => get_ru()
							)
						)
					));
				}
				$val["post_append_text"] .= " ".$pm->get_menu(array(
					"icon" => "new.gif",
					"alt" => t("Lisa")
				));
			}
			else
			{
				foreach($clid as $_clid)
				{
					$val["post_append_text"] .= " ".html::href(array(
						"url" => html::get_new_url(
							$_clid,
							$arr["prop"]["parent"] == "this.parent" ? $this->obj->parent() : $this->obj->id(),
							array(
								"alias_to_prop" => $arr["prop"]["name"],
								"alias_to" => $this->obj->id(),
								"reltype" => $rel_val,
								"return_url" => get_ru()
							)
						),
						"caption" => "<img src='".aw_ini_get("baseurl")."/automatweb/images/icons/new.gif' border=0>",
						"title" => sprintf(t("Lisa uus %s"), $clss[$_clid]["name"])
					));
				}
			}
		}
		return array($val["name"] => $val);
	}

	function process_vcl_property($arr)
	{
		if ($arr["prop"]["mode"] == "autocomplete")
		{
			return $this->process_autocomplete_relpicker($arr);
		}
		$property = $arr["prop"];
		if ($property["type"] == "relpicker" && $property["automatic"] == 1)
		{
			$obj_inst = $arr["obj_inst"];
			$conns = array();
			$rt = $arr["relinfo"][$property["reltype"]]["value"];
			if (!$arr["new"])
			{
				$rt = $arr["relinfo"][$property["reltype"]]["value"];
				$conns = $obj_inst->connections_from(array(
					"type" => $property["reltype"],
				));
			};
			// no existing connection, create a new one
			if ($arr["new"] || sizeof($conns) == 0)
			{
				if (is_array($property["value"]))
				{
					foreach($property["value"] as $pval)
					{
						$obj_inst->connect(array(
							"to" => $pval,
							"reltype" => $rt,
						));
					}
				}
				else
				if ($property["value"] != 0)
				{
					$obj_inst->connect(array(
						"to" => $property["value"],
						"reltype" => $rt,
					));
				};
			}
			else
			{
				if (is_array($property["value"]))
				{
					foreach($conns as $conn)
					{
						if (!in_array($conn->prop("to"),$property["value"]))
						{

						//	$conn->delete();
						};
					}
				}
				else
				{
					list(,$existing) = each($conns);
					if ($property["value"] == 0)
					{
						$existing->delete();
					}
					else
					{
						$existing->change(array(
							"to" => $property["value"],
						));
					};
				};
			};
		};
	}

	function init_autocomplete_relpicker($arr)
	{
		$prop = array(
			"name" => $arr["prop"]["name"],
			"type" => "textbox",
			"store" => "no",
			"caption" => $arr["prop"]["caption"],
		);
		$ri = $arr["obj_inst"]->get_relinfo();
		$clids = $ri[$arr["prop"]["reltype"]]["clid"];

		if ($this->can("view", $arr["obj_inst"]->prop($arr["prop"]["name"])))
		{
			$prop["value"] = $arr["obj_inst"]->prop($arr["prop"]["name"].".name");
		}
		$prop["autocomplete_source"] = $this->mk_my_orb("get_relp_opts", array("clids" => $clids));
		$prop["autocomplete_params"] = array($arr["prop"]["name"]);
		return array($arr["prop"]["name"] => $prop);
	}

	/**
		@attrib name=get_relp_opts all_args="1"
	**/
	function get_relp_opts($arr)
	{
		if (is_admin())
		{
			$ol = new object_list(array(
				"class_id" => $arr["clids"],
				"lang_id" => array(),
				"site_id" => array(),
				"name" => $arr[$arr["requester"]]."%"
			));
		}
		else
		{
			$ol = new object_list();
		}
		$cl_json = get_instance("protocols/data/json");

		$errorstring = "";
		$error = false;
		$autocomplete_options = array();

		$option_data = array(
			"error" => &$error,// recommended
			"errorstring" => &$errorstring,// optional
			"options" => &$autocomplete_options,// required
			"limited" => false,// whether option count limiting applied or not. applicable only for real time autocomplete.
		);


		header ("Content-Type: text/html; charset=" . aw_global_get("charset"));
		$autocomplete_options = $ol->names();
		foreach($autocomplete_options as $key=>$val)
		{
			$autocomplete_options[$key] = iconv(aw_global_get("charset"),"UTF-8",  $autocomplete_options[$key]);
		}
		exit ($cl_json->encode($option_data));
	}

	function process_autocomplete_relpicker($arr)
	{
		// if the name is not empty, find item with that name. if not found, create new
		if (trim($arr["prop"]["value"]) != "")
		{
			$ri = $arr["obj_inst"]->get_relinfo();
			$clids = $ri[$arr["prop"]["reltype"]]["clid"];

			$ol = new object_list(array(
				"class_id" => $clids,
				"lang_id" => array(),
				"site_id" => array(),
				"name" => $arr["prop"]["value"]
			));
			if ($ol->count())
			{
				$item = $ol->begin();
			}
			else
			{
				$item = obj();
				$item->set_class_id(reset($clids));
				$item->set_parent(is_oid($arr["obj_inst"]->id()) ? $arr["obj_inst"]->id() : $arr["obj_inst"]->parent());
				$item->set_name($arr["prop"]["value"]);
				$item->save();
			}

			if ($arr["prop"]["store"] == "connect")
			{
				foreach($arr["obj_inst"]->connections_from(array("type" => $arr["prop"]["reltype"])) as $c)
				{
					$c->delete();
				}
			}

			$arr["obj_inst"]->connect(array(
				"to" => $item->id(),
				"type" => $arr["prop"]["reltype"]
			));
			$arr["prop"]["value"] = $item->id();
			$arr["obj_inst"]->set_prop($arr["prop"]["name"], $item->id());
		}
	}
};

?>
