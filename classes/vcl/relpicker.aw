<?php
class relpicker extends  core
{
	function relpicker()
	{
		$this->init("");
	}

	function init_vcl_property($arr)
	{
		$prop = &$arr["property"];
		$this->obj = $arr["obj_inst"];
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
			$val["options"] = $prop["options"];
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
						"lang_id" => array()
					));
					$names = $olist->names();
					asort($names);            
					$val["options"] = $options + $names;
					if ($arr["id"])
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
					};
				};
			}
			else
			{
				if ($arr["id"])
				{
					$o = obj($arr["id"]);
					$conn = $o->connections_from(array(
						"type" => $reltype
					));
					foreach($conn as $c)
					{
						$options[$c->prop("to")] = $c->prop("to.name");
					}
					$val["options"] = $options;
				};
			}
		}
		$val["type"] = ($val["display"] == "radio") ? "chooser" : "select";

		if ($val["type"] == "select" && is_object($this->obj))
		{
			$clid = (array)$arr["relinfo"][$reltype]["clid"];
			$url = $this->mk_my_orb("do_search", array(
				"id" => $arr["obj_inst"]->id(),
				"pn" => $arr["property"]["name"],
				"clid" => $clid,
				"multiple" => $arr["property"]["multiple"]
			), "popup_search");
	
			$val["post_append_text"] .= " ".html::href(array(
				"url" => "javascript:aw_popup_scroll(\"$url\",\"Otsing\",550,500)",
				"caption" => "<img src='".aw_ini_get("baseurl")."/automatweb/images/icons/search.gif' border=0>",
				"title" => t("Otsi")
			));
		}
		if ($val["type"] == "select" && is_object($this->obj) && is_oid($this->obj->prop($val["name"])) && $this->can("edit", $this->obj->prop($val["name"])))
		{
			$val["post_append_text"] .= " ".html::href(array(
				"url" => html::get_change_url($this->obj->prop($val["name"]), array("return_url" => get_ru())),
				"caption" => "<img src='".aw_ini_get("baseurl")."/automatweb/images/icons/edit.gif' border=0>",
				"title" => t("Muuda")
			));
		}
		if ($val["type"] == "select" && is_object($this->obj) && is_oid($this->obj->id()))
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
};
?>
