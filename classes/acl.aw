<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/acl.aw,v 2.7 2002/07/17 20:33:39 kristo Exp $
// acl.aw - Access Control Lists

class acl extends aw_template 
{
	function acl() 
	{
		$this->init("automatweb/acl");
		lc_load("definition");
	}

	////
	// !Loeb ja parsib ACLi vormi XML deffi
	// file - failinimi
	function get_acl_def($args = array())
	{
		extract($args);
		$contents = $this->get_file(array("file" => $args["file"]));
		if (!$contents)
		{
			// siia ei tohiks me tegelikult mitte kunagi sattuda
			$this->raise_error(ERR_ACL_ERR,"smth bad just happened. Please report to dev@struktuure.ee immediately",true);
		};
		list($tags,) = parse_xml_def(array(
			"xml" => $contents,
		));
		$fields = array();
		foreach ($tags as $key => $val)
		{
			if ( ($val["tag"] == "field") && ($val["type"] == "complete") )
			{
				$fields[] = $val["attributes"];
			};
		};
		return $fields;

	}

	////
	// !Kuvab ACL-i muutmisvormi. Orb compatible
	function gen_edit_form($args)
	{
		extract($args);
		// hiljem tuleb siia ehitada mingi deeper voodoo oige faili valimiseks soltuvalt 
		// objekti klassist
		$xmldef = "site.xml";
		$fname = $this->cfg["basedir"] .  "/xml/acl/" . $xmldef;
		$fields = $this->get_acl_def(array(
			"file" => $fname,
		));
		// andmed väljade kohta käes, nüüd kuvame vormi
		// dump_struct($fields);
		$bld = new aw_template;
    $bld->tpl_init("automatweb/acl");
    $bld->read_template("cells.tpl");

		$this->read_template("editacl.tpl");

		while(list($k,$v) = each($fields))
		{
			$bld->vars(array(
				"colspan" => 1,
				"align" => "left",
        "content" => $v["caption"]
			));
      $c.= $bld->parse("title");
 
      if ($v["special"])
      {
				$this->vars(array(
          "caption" => $v["caption"],
          "help"    => $v["help"],
          "key"     => $v["value"],
        ));
        $help .= $this->parse("help");
        $keys .= $this->parse("xfield");
      };
      $count++;
    };
		$this->vars(array(
			"header" => $c,
			"colspan" => $count+2,
		));

		$content = "";
		$prar = $this->get_object_chain($oid,true);
		reset($prar);
		while (list(,$row) = each($prar))
		{
			$oid = $row["oid"];
			$objstr = "";
			$objar = $this->get_object_chain($oid,true);
			reset($objar);
			while (list(,$row) = each($objar))
			{
				$objstr=" / ".$row["name"].$objstr;
			}
			$objstr = substr($objstr,3);
			$aclarr = $this->get_acl_groups_for_obj($oid);
			while(list(,$arr) = each($aclarr))
			{
				reset($fields);
				$c = "";
				while(list($k,$v) = each($fields))
				{
					if ($v["special"] == "1")
					{
						$tpl = "check";
						$bld->vars(array(
							"gid"      => $arr["gid"],
							"oid"      => $oid,
							"key"      => $v["value"],
							"checked"  => ($arr[$v["value"]] == $this->cfg["allowed"]) ? "checked" : ""
						));
						$c .= $bld->parse("check");
					}
					else
					{
						$bld->vars(array("content" => $arr[$v["value"]]));
						$c .= $bld->parse("text");
					}; // end if
				};
 
				$this->vars(array(
					"cline" => $c,
					"name"  => $objstr,
					"oid" => $oid,
					"gid"   => $arr["gid"]
				));
				$content .= $this->parse("line");
			};
		}

		$objdata = $this->get_object($args["oid"]);
		// tean jah, et siia ei tohiks html-i panna. But would you PLEASE shut up?
		$this->vars(array(
			"line" => $content,
			"help" => $help,
			"object" => "<b>" . $objdata["name"] . " (" . $objdata["oid"] . ")</b> ",
			"oid" => $objdata["oid"],
			"xfield" => $keys,
			"reforb" => $this->mk_reforb("submit_acl", array("oid" => $args["oid"],"user" => $user)),
			"file"  => $def
		));
		return $this->parse();
	}

	////
	// !Salvestab ACL vormi sisu
	function submit_acl($args = array())
	{
		$this->ui_save_acl($args);
		$parent = $this->get_object($args["oid"]);
		$parent = $parent["parent"];
		$retval = $this->mk_orb("obj_list", array("parent" => $parent), "",1);
		return $retval;
	}
		
	function xml_start_element($parser,$name,$attrs) 
	{ 
		$temp = "";
		if ($name == "FIELD") {
			while(list($k,$v) = each($attrs)) {
				$temp[$k] = $v;
			};
			$this->data[] = $temp;
		};
	}
	
	function xml_end_element($parser,$name) 
	{
	}

	function __get_config($content) 
	{
		$this->data = array();
		$xml_parser = xml_parser_create();
		xml_set_object($xml_parser,&$this);
		xml_set_element_handler($xml_parser,"xml_start_element","xml_end_element");
		if (!xml_parse($xml_parser,$content)) 
		{
			echo(sprintf("XML error: %s at line %d",
                                      xml_error_string(xml_get_error_code($xml_parser)),
                                      xml_get_current_line_number($xml_parser)));
    };
		return $this->data;
	}

	////
	// !Kuvab ACL muutmise vormi mingi objekti jaoks
	// user - kas vormi kuvatakse saidi sees? (kodukataloogis)
	function gen_acl_form($oid,$def = -1,$user = 0) 
	{
		if ($user == 1)
		{
			$fname = "site.xml";
		}
		elseif ($def == -1) 
		{
			$fname = "default.xml";
		}
		else
		{
			$fname = $def;
		};
		$name = $this->cfg["basedir"]."/xml/acl/$fname";
		$xmldata = $this->get_file(array("file" => $name));
		$fields = $this->__get_config($xmldata);

		$bld = new aw_template;
		$bld->tpl_init("automatweb/acl");
	  $bld->read_template("cells.tpl");

	  $c = "";
		$help = "";
		$keys = "";
		$count = 0;
		$r_oid = $oid;
		$this->read_template("editacl.tpl");
		while(list($k,$v) = each($fields)) 
		{
			$bld->vars(array(
				"colspan" => 1,
				"align"   => "left",
				"content" => $v["CAPTION"]
			));
    	$c.= $bld->parse("title");

			if ($v["SPECIAL"]) 
			{
				$this->vars(array(
					"caption" => $v["CAPTION"],
					"help"    => $v["HELP"],
					"key"     => $v["VALUE"],
				));
				$help .= $this->parse("help");
				$keys .= $this->parse("xfield");
			};
			$count++;
		};

		$this->vars(array(
			"header" => $c,
			"colspan" => $count+2,
		));	
		$content = "";

		$prar = $this->get_object_chain($oid,true);
		reset($prar);
		while (list(,$row) = each($prar))
		{
			$oid = $row["oid"];
			$objstr = "";
			$objar = $this->get_object_chain($oid,true);
			reset($objar);
			while (list(,$row) = each($objar))
			{
				$objstr=" / ".$row["name"].$objstr;
			}
			$objstr = substr($objstr,3);
			$aclarr = $this->get_acl_groups_for_obj($oid);
			while(list(,$arr) = each($aclarr)) 
			{
				reset($fields);
				$c = "";
				while(list($k,$v) = each($fields)) 
				{
					if ($v["SPECIAL"] == "1") 
					{
						$tpl = "check";
						$bld->vars(array(
							"gid"			=> $arr["gid"],	
							"oid"			=> $oid, 
							"key"			=> $v["VALUE"],
							"checked"	=> ($arr[$v["VALUE"]] == $this->cfg["allowed"]) ? "checked" : ""
						));
						$c .= $bld->parse("check");
					} 
					else 
					{
						$bld->vars(array("content" => $arr[$v["VALUE"]])); 
						$c .= $bld->parse("text");
					}; // end if
				};

				$this->vars(array(
					"cline" => $c,
					"name"	=> $objstr,
					"oid" => $oid,
					"gid"   => $arr["gid"]
				));
				$content .= $this->parse("line");
			};
		}
		$this->vars(array(
			"line" => $content,
			"help" => $help,
			"oid" => $r_oid,
			"xfield" => $keys,
			"file"	=> $def
		));
		return $this->parse();
	}

	function submit_acl_groups($arr)
	{
		extract($arr);
		reset($arr);
		while (list($k,$v) = each($arr))
		{
			if (substr($k,0,3) == "gb_")
			{
				$gid = substr($k,3);
				if ($v != $arr["ga_".$gid])	// if group membership has changed
				{
					if ($arr["ga_".$gid])
					{
						$this->add_acl_group_to_obj($gid,$arr["oid"]);
					}
					else
					{
						$this->remove_acl_group_from_obj($gid,$arr["oid"]);
					}
				}
			}
		}
		return $from;
	}

	function ui_save_acl($arr)
	{
		extract($arr);

		$p_oid = $oid;
		reset($facl);
		while (list($oid, $far) = each($facl))
		{
			reset($far);
			while(list($gid,$acl) = each($far)) 
			{
				$this->save_acl($oid,$gid,$acl);
			}
		}
		return "editacl.".$this->cfg["ext"]."?oid=".$p_oid."&file=".$file;
	}

	function check_environment(&$sys, $fix = false)
	{
		$ret = $sys->check_admin_templates("automatweb/acl", array("cells.tpl","editacl.tpl"));
		return $ret;
	}
};
?>
