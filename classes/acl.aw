<?php
// acl.aw - Access Control Lists

global $orb_defs;
$orb_defs["acl"] = "xml";

class acl extends aw_template {

	function acl() 
	{
		$this->db_init();
		$this->tpl_init("automatweb/acl");
	}

	// these are placeholders, so that everything that uses these, will still work
	function query_parent($oid=-1) {} 
	function query($oid = -1) {} 
	function sql() { return "acl";}
	function get($tp) {	return true;}
	function add($oid = -1) {}

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
			print "smth bad just happened. Please report to dev@struktuure.ee immediately";
			die();
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
		global $basedir;
		$xmldef = "site.xml";
		$fname = $basedir .  "/xml/acl/" . $xmldef;
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
                                "align"   => "left",
                                "content" => $v["caption"]));
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
							"checked"  => ($arr[$v["value"]] == ALLOWED) ? "checked" : ""));
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
					"gid"   => $arr["gid"]));
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
				"file"  => $def));
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
		

	////
	// !Kuvab ACL muutmise vormi mingi objekti jaoks
	// user - kas vormi kuvatakse saidi sees? (kodukataloogis)
	function gen_acl_form($oid,$def = -1,$user = 0) 
	{
		sysload("config");
		$config = new db_config;

		global $basedir;
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
		$name = "$basedir/xml/acl/$fname";
		$xmldata = $this->get_file(array("file" => $name));
		$fields = $config->__get_config($xmldata);

		$bld = new aw_template;
		$bld->tpl_init("automatweb/acl");
	  	$bld->read_template("cells.tpl");

	  	global $PHP_SELF;
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
				"content" => $v["CAPTION"]));
      			$c.= $bld->parse("title");

			if ($v[SPECIAL]) 
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
			$oid = $row[oid];
			$objstr = "";
			$objar = $this->get_object_chain($oid,true);
			reset($objar);
			while (list(,$row) = each($objar))
			{
				$objstr=" / ".$row[name].$objstr;
			}
			$objstr = substr($objstr,3);
			$aclarr = $this->get_acl_groups_for_obj($oid);
			while(list(,$arr) = each($aclarr)) 
			{
				reset($fields);
				$c = "";
				while(list($k,$v) = each($fields)) 
				{
					if ($v[SPECIAL] == "1") 
					{
						$tpl = "check";
						$bld->vars(array("gid"			=> $arr[gid],	
														 "oid"			=> $oid, 
														 "key"			=> $v[VALUE],
														 "checked"	=> ($arr[$v[VALUE]] == ALLOWED) ? "checked" : ""));
						$c .= $bld->parse("check");
					} 
					else 
					{
						$bld->vars(array("content" => $arr[$v[VALUE]])); 
						$c .= $bld->parse("text");
					}; // end if
				};

				$this->vars(array("cline" => $c,
													"name"	=> $objstr,
													"oid" => $oid,
													"gid"   => $arr[gid]));
				$content .= $this->parse("line");
			};
		}
		$this->vars(array("line" => $content,
										  "help" => $help,
											"oid" => $r_oid,
										  "xfield" => $keys,
											"file"	=> $def));
		return $this->parse();
	}

	function submit_acl_groups($arr)
	{
		reset($arr);
		while (list($k,$v) = each($arr))
		{
			if (substr($k,0,3) == "gb_")
			{
				$gid = substr($k,3);
				if ($v != $arr["ga_".$gid])	// if group membership has changed
				{
					if ($arr["ga_".$gid])
						$this->add_acl_group_to_obj($gid,$arr[oid]);
					else
						$this->remove_acl_group_from_obj($gid,$arr[oid]);
				}
			}
		}
	}

	function ui_save_acl($arr)
	{
		extract($arr);
	
		reset($facl);
		while (list($oid, $far) = each($facl))
		{
			reset($far);
			while(list($gid,$acl) = each($far)) 
			{
				$this->save_acl($oid,$gid,$acl);
			}
		}
	}
};
?>
