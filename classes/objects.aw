<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/objects.aw,v 2.61 2004/06/25 19:16:20 kristo Exp $
// objects.aw - objektide haldamisega seotud funktsioonid
class db_objects extends aw_template 
{
	function db_objects() 
	{
		$this->init("");
		$this->lc_load("objects","lc_objects");
		lc_load("definition");
	}

	////
	// !Genereerib mingit klassi objektide nimekirja, rekursiivselt alates $start_from-ist
	// Eeliseks järgneva funktsiooni ees on see, et ei loeta koiki menüüsid sisse
	// see versioon ei prindi objektide nimekirja v2lja ka. mix seda yldse vaja oli printida?!?
	// ja tagastatav array on kujul array($oid => $row)
	function gen_rec_list_noprint($args = array())
	{
		extract($args);
		// vaatame ainult seda tüüpi objekte
		$this->class_id = 1;
		$this->spacer = 0;
		// moodustame 2mootmelise array koigist objektidest
		// parent -> child1,(child2,...childn)
		$this->rec_list = array(); // siia satuvad koik need objektid
		$this->no_parent_rel = true;
		$this->_gen_rec_list(array("$start_from"));
		return $this->rec_list;
	}

	////
	// !Rekursiivne funktsioon, kutsutakse välja gen_rec_list seest
	function _gen_rec_list($parents = array())
	{
		$this->save_handle();
		$plist = join(",",$parents);
		if($plist == "")
		{
			$this->restore_handle();
			return;
		}
		$q = sprintf("SELECT * FROM objects WHERE class_id = '%d' AND parent IN (%s)",
				$this->class_id,
				$plist);
		$this->db_query($q);
		$_parents = array();
		while($row = $this->db_next())
		{
			$_parents[] = $row["oid"];
			if ($this->no_parent_rel)
			{
				$this->rec_list[$row["oid"]] = $row;
			}
			else
			{
				$this->rec_list[$row["parent"]][$row["oid"]] = $row;
			}
		};
		if (sizeof($_parents) > 0)
		{
			$this->_gen_rec_list($_parents);
		};
		$this->restore_handle();
	}

	function orb_get_list($arr)
	{
		if (is_array($arr))
		{
			extract($arr);
		}
		if (!isset($rootobj))
		{
			$rootobj = -1;
		}
		$ret = $this->get_menu_list($ignore_langmenus,$empty,$rootobj);
		return $ret;
	}

	function get_list($ignore_langmenus = false,$empty = false,$rootobj = -1) 
	{
		return $this->get_menu_list($ignore_langmenus,$empty,$rootobj);
	}
	
	function count_by_parent($parent,$typearr = "") 
	{
		if (is_array($typearr))
		{
			$typestr = "AND class_id IN (".join(",",$typearr).") ";
		}
		else
		{
			$typestr = "";
		}
		$q = "SELECT count(*) as cnt
			FROM objects
			WHERE parent = '$parent' $typestr";
		return $this->db_fetch_field($q,"cnt");
	}

	function listall_types($parent,$typearr)
	{
		$tstr = join(",", $typearr);
		$this->db_query("SELECT * FROM objects WHERE parent = $parent AND class_id IN ($tstr) ");
	}
};


class objects extends db_objects
{
	function objects()
	{
		$this->db_objects();
	}

	/** Displays an object. Any object. 
		
		@attrib name=show params=name nologin="1" default="0"
		
		@param id required type=int
		
		@returns
		
		
		@comment
		and yes, it's not very smart. all the functionality to generate a preview of an object
		should be inside the correspondending class

	**/
	function show($args = array())
	{
		extract($args);
		$classes = aw_ini_get("classes");
		$ret = "";

		$o =&obj($id);

		$clid = $o->class_id();
		$i = get_instance($classes[$clid]["file"]);
		if (method_exists($i, "parse_alias"))
		{
			$ret = $i->parse_alias(array(
				"oid" => $id,
				"alias" => array("target" => $id)
			));
			if (is_array($ret))
			{
				$ret = $ret["replacement"];
			}
		}

		return $ret;

		/*$obj = $this->get_object($id);
		if (not($obj))
		{
			return false;
		};

		$this->name = strip_tags($obj["name"]);
		$this->has_output = true;
		// shouldn't we check ACL here?
		switch($obj["class_id"])
		{
			case CL_EXTLINK:
				$t = get_instance("links");
				list($url,$target,$caption) = $t->draw_link($obj["oid"]);
				$replacement = sprintf("<a href='%s' %s>%s</a>",$url,$target,$caption);
				break;

			case CL_IMAGE:
				$t = get_instance("image");
				$idata = $t->get_image_by_id($obj["oid"]);
				$replacement = sprintf("<img src='%s'><br />%s",$idata["url"],$idata["comment"]);
				break;
			case CL_TABLE:
				$t = get_instance("table");
				$replacement = $t->show(array("id" => $obj["oid"],"align" => $align));
				break;

			case CL_FORM_ENTRY:
				$t = get_instance("formgen/form");
				$frm = $t->get_form_for_entry($obj["oid"]);
				$ops = $t->get_op_list($frm);
				list($x,$y) = each($ops);
				if (is_array($y))
				{
					list($id,$name) = each($y);
				};

				$replacement = $t->show(array(
					"id" => $frm,
					"entry_id" => $obj["oid"],
					"op_id" => $id,
				));
				break;

			case CL_FORM_OUTPUT:
				$t = get_instance("formgen/form");
				$frm = $t->get_op_forms($obj["oid"]);
				$x = $y = 0;
				if (is_array($frm))
				{
					list($x,$y) = each($frm);
				};
				
				$replacement = $t->show(array(
					"id" => $x,
					"op_id" => $id,
				));

				break;

			
			case CL_FORM:
				$t = get_instance("formgen/form");
				$replacement = $t->gen_preview(array(
					"id" => $obj["oid"],
					"form_action" => "/reforb.".$this->cfg["ext"],
				));
				break;
			
			case CL_FORM_CHAIN:
				$t = get_instance("formgen/form_chain");
				$replacement = $t->show(array(
					"id" => $obj["oid"],
				));
				break;

			case CL_GRAPH:
				$replacement = "<img src='".$this->mk_my_orb("show", array("id" => $obj["oid"]),"graph",false,true)."'>";
				break;

			case CL_GALLERY:
				$t = get_instance("gallery");
				$t->load($obj["oid"],$GLOBALS["page"]);
				$replacement = $t->show($GLOBALS["page"]);
				break;

			case CL_FILE:
				$t = get_instance("file");
				$fi = $t->get_file_by_id($obj["oid"]);
				if ($fi["showal"] == 1)
				{
					// n2itame kohe
					// kontrollime koigepealta, kas headerid on ehk väljastatud juba.
					// dokumendi preview vaatamisel ntx on.
					if ($fi["type"] == "text/html")
					{
						if (!headers_sent())
						{
							header("Content-type: text/html");
						};

						$replacement = $fi["content"];
					}
					else
					{
						header("Content-type: ".$fi["type"]);
						die($fi["content"]);
					}
				}
				else
				{
					if ($fi["newwindow"])
					{
						$ss = "target=\"_new\"";
					}

					$comment = $fi["comment"];
					if ($comment == "")
					{
						$comment = $fi["name"];
					}

					classload("file");
					$replacement = "<a $ss class=\"sisutekst\" href='".file::get_url($obj["oid"],$fi["name"])."'>$comment</a>";
				}	
				break;


			case CL_DOCUMENT:
				$t = get_instance("document");
				$replacement = $t->gen_preview(array("docid" => $obj["oid"]));
				break;

			case CL_PSEUDO:
				$replacement = "<a href='/index.".$this->cfg["ext"]."?section=$obj[oid]'>$obj[name]</a>";
				break;

			case CL_CALENDAR:
				$cal = get_instance("planner");
				$cform = $args["form"];
				$ctrl = 0;

				// chain entry id.
				$ceid = $cform->current_chain_entry;

				if ($cform && $cform->arr["has_calendar"] && $cform->arr["cal_controller"])
				{
					$ctrl = $cform->id;
				};

				//$curl = $this->mk_my_orb("view",array("type" => "week","id" => $obj["oid"],"ctrl" => $ctrl,"ctrle" => $ceid,"chain_id" => $cform->id),"planner",false,true);
				$curl = $this->mk_my_orb("view",array("type" => "week","id" => $ceid),"planner",false,true);
				if (not($caption))
				{
					$caption = "View calendar";
				};
				$replacement = "<a target='new' href='$curl'>$caption</a>";
				break;

			default:
				$this->has_output = false;
				$replacement = $obj["class_id"] . " This object class has no output yet<br />";
		}
		return $replacement;
		*/
	}

	/**  
		
		@attrib name=db_query params=name default="0"
		
		@param sql required
		
		@returns
		
		
		@comment

	**/
	function orb_db_query($arr)
	{
		extract($arr);
		$ret = array();
		$this->db_query($sql);
		while ($row = $this->db_next())
		{
			$ret[] = $row;
		}
		return $ret;
	}

	/**  
		
		@attrib name=delete_object params=name default="0"
		
		@param oid required
		
		@returns
		
		
		@comment

	**/
	function orb_delete_object($arr)
	{
		extract($arr);
		aw_disable_acl();
		$tmp = obj($oid);
		$tmp->delete();
		aw_restore_acl();
	}

	function on_site_init($dbi, $site, &$ini_opts)
	{
		// create a few objects to init the db struct
		$mned = get_instance("menuedit");
		$mned->dc = $dbi->dc;	// fake the db connection
		
		if ($site['site_obj']['use_existing_database'])
		{
			$client_id = $site['site_obj']['select_parent_folder'];
			//echo "got client id as $client_id <br />\n";
			flush();
		}
	}

	/**  
		
		@attrib name=get_db_pwd params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
	function orb_get_db_pwd($arr)
	{
		extract($arr);
		return array(
			'base' => aw_ini_get("db.base"),
			'host' => aw_ini_get("db.host"),
			'user' => aw_ini_get("db.user"),
			'pass' => aw_ini_get("db.pass")
		);
	}

	/**  
		
		@attrib name=aw_ini_get_mult params=name nologin="1" default="0"
		
		@param vals required
		
		@returns
		
		
		@comment

	**/
	function aw_ini_get_mult($arr)
	{
		extract($arr);
		$ret = array();
		foreach($vals as $vn)
		{
			$ret[$vn] = aw_ini_get($vn);
		}
		return $ret;
	}

	/** Object list
		
		@attrib name=get_list params=name default="0" nologin="1" all_args="1"
		
		@param ignore_langmenus optional
		@param empty optional
		@param rootobj optional type=int
		
		@returns
		
		
		@comment
			returns list of id => name pairs for all menus
	**/
	function orb_get_list($arr)
	{
		return parent::orb_get_list($arr);
	}

	/** serialize
		
		@attrib name=serialize params=name default="0" nologin="1" 
		
		@param oid required
		
		@returns
		
		@comment
			serializes an object
	**/
	function orb_serialize($arr)
	{
		return parent::serialize($arr);
	}
}

?>
