<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/rate/rate.aw,v 1.20 2004/10/27 12:03:46 kristo Exp $
/*

@classinfo syslog_type=ST_RATE relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property top type=textbox size=5 
@caption Mitu topis

@property top_type type=select 
@caption J&auml;rjestatakse

@property objects_from type=select
@caption Millistele objektidele kehtib

@property objects_from_clid type=select
@caption Vali klass

@property objects_from_folder type=relpicker reltype=RELTYPE_RATE_FOLDER multiple=1
@caption Vali kataloogid

@property objects_from_oid type=relpicker reltype=RELTYPE_RATE_OID
@caption Vali objekt

@reltype RATE_FOLDER value=1 clid=CL_MENU
@caption Hinnatavate objektide kataloog

@reltype RATE_OID value=2
@caption Hinnatav objekt


*/
define("OBJECTS_FROM_CLID", 1);
define("OBJECTS_FROM_FOLDER", 2);
define("OBJECTS_FROM_OID", 3);

define("ORDER_HIGHEST",1);
define("ORDER_AVERAGE",2);
define("ORDER_VIEW",3);
define("ORDER_LOWEST_RATE",4);
define("ORDER_LOWEST_VIEW",5);

define("RATING_AVERAGE", 1);
define("RATING_HIGHEST", 2);
define("RATING_VIEW", 3);
define("RATING_LOWEST_RATE",4);
define("RATING_LOWEST_VIEW",5);

class rate extends class_base
{
	function rate()
	{
		$this->init(array(
			'tpldir' => 'contentmgmt/rate',
			'clid' => CL_RATE
		));
		$this->classes = aw_ini_get("classes");
	}
	
	function get_property(&$arr)
	{
		$prop =& $arr["prop"];
		switch($prop['name'])
		{
			case "objects_from":
				$prop['options'] = array(
					OBJECTS_FROM_CLID => "Klassi j&auml;rgi",
					OBJECTS_FROM_FOLDER => "Kataloogi j&auml;rgi",
					OBJECTS_FROM_OID => "Objektist"
				);
				break;

			case "objects_from_clid":
				if ($arr['obj_inst']->prop('objects_from') != OBJECTS_FROM_CLID)
				{
					return PROP_IGNORE;
				}
				classload("aliasmgr");
				$prop['options'] = aliasmgr::get_clid_picker();
				break;

			case "objects_from_folder":
				if ($arr['obj_inst']->prop('objects_from') != OBJECTS_FROM_FOLDER)
				{
					return PROP_IGNORE;
				}
				break;

			case "objects_from_oid":
				if ($arr['obj_inst']->prop('objects_from') != OBJECTS_FROM_OID)
				{
					return PROP_IGNORE;
				}
				break;

			case "top_type":
				$prop['options'] = array(
					ORDER_HIGHEST => "K&otilde;rgeima hinde j&auml;rgi",
					ORDER_AVERAGE => "Keskmise hinde j&auml;rgi",
					ORDER_VIEWS => "Vaatamiste j&auml;rgi",
					ORDER_LOWEST_RATE => "Madalaima hinde j&auml;rgi",
					ORDER_LOWEST_VIEWS => "V&auml;him vaadatud"
				);
				break;
		}
		return PROP_OK;
	}

	function get_rating_for_object($oid, $type = RATING_AVERAGE)
	{
		if (!is_oid($oid))
		{
			return 0;
		}
		
		// we need to cache this shit.
		// so, let's make add_rate write it to the object's metadata, in the rate array
		$ob = obj($oid);
		$rts = $ob->meta("__ratings");
		if (!is_array($rts))
		{
			$avg = $this->db_fetch_field("SELECT AVG(rating) AS avg FROM ratings WHERE oid = '$oid'", "avg");
			$l_rate = $this->db_fetch_field("SELECT MIN(rating) AS min FROM ratings WHERE oid = '$oid'", "min");
			$max = $this->db_fetch_field("SELECT MAX(rating) AS max FROM ratings WHERE oid = '$oid'", "max");
			$views = $this->db_fetch_field("SELECT hits FROM hits WHERE oid = '$oid'", "hits");
			$rts = array(
				RATING_AVERAGE => $avg,
				RATING_HIGHEST => $max,
				RATING_VIEWS => $views,
				RATING_LOWEST_RATE => $l_rate,
				RATING_LOWEST_VIEWS => $views
			);
			$ob->set_meta("__ratings",$rts);
			aw_disable_acl();
			$ob->save();
			aw_restore_acl();
			
			if ($type == RATING_AVERAGE)
			{
				return round($avg,2);
			}
			else
			if ($type == RATING_VIEWS)
			{
				return round($views);
			}
			else
			if ($type == RATING_LOWEST_VIEWS)
			{
				return round($views);
			}
			else
			if ($type == RATING_LOWEST_RATE)
			{
				return round($l_rate);
			}
			else
			{
				return round($max);
			}
		}
		return number_format((float)$rts[$type],2,".",",");
	}

	/**  
		
		@attrib name=rate params=name nologin="1" default="0"
		
		@param oid required type=int
		@param return_url required
		@param rate required
		
		@returns
		
		
		@comment

	**/
	function add_rate($arr)
	{

		extract($arr);
		$ro = aw_global_get("rated_objs");

		//if (!isset($ro[$oid]))
		if (true)
		{
			$this->db_query("
				INSERT INTO ratings(oid, rating, tm, uid, ip) 
				VALUES('$oid','$rate',".time().",'".aw_global_get("uid")."','".aw_global_get("REMOTE_ADDR")."')
			");
			$ro[$oid] = $rating;

			$stat_query = "SELECT MIN(rating) AS min,MAX(rating) AS max,AVG(rating) AS avg FROM ratings WHERE oid = '$oid'";
			$this->db_query($stat_query);
			$row = $this->db_next();

			$rs = array(
				RATING_AVERAGE => $row["avg"],
				RATING_HIGHEST => $row["max"],
				RATING_VIEWS => $this->db_fetch_field("SELECT hits FROM hits WHERE oid = '$oid'", "hits"),
				RATING_LOWEST_VIEWS => $this->db_fetch_field("SELECT hits FROM hits WHERE oid = '$oid'", "hits"),
				RATING_LOWEST_RATE => $row["min"],
			);
			$o = obj($oid);
			$o->set_meta("__ratings",$rs);
			$o->save();
		}

		if ($arr["no_redir"])
		{
			return true;
		}
		else
		{
			aw_session_set("rated_objs", $ro);
			header("Location: $return_url");
			die();
		};
	}

	/**  
		
		@attrib name=show params=name nologin="1" default="0"
		
		@param id required type=int
		@param gallery_id optional type=int
		
		@returns
		
		
		@comment

	**/
	function show(&$arr)
	{
		extract($arr);
		$this->read_any_template("show.tpl");
		
		$ob = obj($id);

		if (!empty($from_oid))
		{
			// we we need to show results in the gallery, read objects from that
			$ob->set_meta('objects_from',OBJECTS_FROM_OID);
			$ob->set_meta('objects_from_oid',$from_oid);
			$ob->save();
		}

		// get list of all objects that this rating applies to
		$oids = array();
		switch($ob->meta("objects_from"))
		{
			case OBJECTS_FROM_CLID:
				$where = "objects.class_id = ".$ob->meta('objects_from_clid');
				break;

			case OBJECTS_FROM_FOLDER:
				// need to get a list of all folders below that one.
				$mn = array();
				//$pts = new aw_array($ob['meta']['objects_from_folder']);
				$_parent_list = new object_list(array(
					"parent" => $ob->meta("objects_from_folder"),
					"class_id" => CL_MENU,
				));
				$mn = array($fld => $fld) + $_parent_list->ids();
				$where = "objects.parent IN (".join(",",$mn).")";
				break;

			case OBJECTS_FROM_OID:
				$c_oid = $ob->meta('objects_from_oid');
				$c_obj = obj($c_oid);
				$c_inst = $c_obj->instance();
				if (method_exists($c_inst, "get_contained_objects"))
				{
					$c_objs = $c_inst->get_contained_objects(array(
						"oid" => $c_oid
					));
				}
				else
				{
					$c_objs = array($c_oid => $c_oid);
				}
				$_tar = new aw_array(array_keys($c_objs));
				$where = "objects.oid IN (".$_tar->to_sql().")";
				break;
		}

		// query the max/avg for those. 
		$order = "DESC";
		switch($ob->meta("top_type"))
		{
			case ORDER_HIGHEST:
				$fun = "rating";
				break;

			case ORDER_AVERAGE:
				$fun = "AVG(rating)";
				break;

			case ORDER_VIEWS:
				$fun = "hits.hits";
				break;

			case ORDER_LOWEST_RATE:
				$fun = "rating";
				$order = "ASC";
				break;

			case ORDER_LOWEST_VIEWS:
				$fun = "hits.hits";
				$order = "ASC";
				break;
		}

		$this->img = get_instance("image");

		$cnt = 1;

		$sql = "
			SELECT 
				objects.oid as oid, 
				$fun as val ,
				objects.name as name,
				objects.class_id as class_id,
				hits.hits as hits,
				images.file as img_file
			FROM 
				objects 
				LEFT JOIN ratings ON ratings.oid = objects.oid
				LEFT JOIN g_img_rel ON objects.oid = g_img_rel.img_id
				LEFT JOIN hits ON hits.oid = objects.oid
				LEFT JOIN images ON images.id = objects.oid
			WHERE
				$where
			GROUP BY 
				objects.oid
			ORDER BY val $order
			LIMIT ".(int)($ob->meta('top'))."
		";
		$this->db_query($sql);
		if (!empty($from_oid))
		{
			$imorder = array();
			while($row = $this->db_next())
			{
				$imorder[$row["oid"]] = $row["oid"];
			}
			// Maybe I should split this function in 2 instead,
			// but the mere thought of that makes my head hurt, so I'm
			// not touching this right now.
			return $imorder;
		}
		else
		{
			while ($row = $this->db_next())
			{
				$this->vars(array(
					"rating" => $row['val'],
					"view" => $this->_get_link($row),
					"hits" => $row['hits'],
					"place" => $cnt++
				));
				$l .= $this->parse("LINE");
			}
			$this->vars(array(
				"LINE" => $l,
				"name" => $ob->name(),
				"count" => $ob->meta('top')
			));
			return $this->parse();
		}
	}

	function _get_link($dat)
	{
		if ($dat["class_id"] == CL_IMAGE)
		{
			return image::make_img_tag($this->img->get_url($dat['img_file']));
		}
		return $this->mk_my_orb("change", array("id" => $dat["oid"]), basename($this->classes[$dat["class_id"]]["file"]));
	}
}
?>
