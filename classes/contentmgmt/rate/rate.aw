<?php

/*

@classinfo syslog_type=ST_RATE relationmgr=yes

@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

@property top type=textbox size=5 field=meta method=serialize
@caption Mitu topis

@property top_type type=select field=meta method=serialize
@caption J&auml;rjestatakse

@property objects_from type=select field=meta method=serialize
@caption Millistele objektidele kehtib

@property objects_from_clid type=select field=meta method=serialize
@caption Vali klass

@property objects_from_folder type=relpicker field=meta method=serialize reltype=RELTYPE_FOLDER multiple=1
@caption Vali kataloogid

@property objects_from_oid type=relpicker field=meta method=serialize reltype=RELTYPE_OID
@caption Vali objekt


*/

define("RELTYPE_FOLDER",1);
define("RELTYPE_OID",2);

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
	}

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row['parent'] = $parent;
		unset($row['brother_of']);
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}
	
	function callback_get_rel_types()
	{
		return array(
			RELTYPE_FOLDER => "hinnatavate objektide kataloog",
			RELTYPE_OID => "hinnatav objekt",
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		if ($args["reltype"] == RELTYPE_FOLDER)
		{
			return array(CL_PSEUDO);
		}
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
				if ($arr['obj']['meta']['objects_from'] != OBJECTS_FROM_CLID)
				{
					return PROP_IGNORE;
				}
				$prop['options'] = aliasmgr::get_clid_picker();
				break;

			case "objects_from_folder":
				if ($arr['obj']['meta']['objects_from'] != OBJECTS_FROM_FOLDER)
				{
					return PROP_IGNORE;
				}
				break;

			case "objects_from_oid":
				if ($arr['obj']['meta']['objects_from'] != OBJECTS_FROM_OID)
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
		// we need to cache this shit.
		// so, let's make add_rate write it to the object's metadata, in the rate array
		$ob = $this->get_object($oid);
		if (!is_array($ob['meta']['__ratings']) || !isset($ob['meta']['__ratings'][$type]))
		{
			$avg = $this->db_fetch_field("SELECT AVG(rating) AS avg FROM ratings WHERE oid = '$oid'", "avg");
			$l_rate = $this->db_fetch_field("SELECT MIN(rating) AS min FROM ratings WHERE oid = '$oid'", "min");
			$max = $this->db_fetch_field("SELECT MAX(rating) AS max FROM ratings WHERE oid = '$oid'", "max");
			$views = $this->db_fetch_field("SELECT hits FROM hits WHERE oid = '$oid'", "hits");
			$this->set_object_metadata(array(
				"oid" => $oid,
				"key" => "__ratings",
				"value" => array(
					RATING_AVERAGE => $avg,
					RATING_HIGHEST => $max,
					RATING_VIEWS => $views,
					RATING_LOWEST_RATE => $l_rate,
					RATING_LOWEST_VIEWS => $views
				)
			));
			
			if ($type == RATING_AVERAGE)
			{
				return rount($avg,2);
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
		return number_format((float)$ob['meta']['__ratings'][$type],2,".",",");
	}

	function add_rate($arr)
	{
		extract($arr);
		$ro = aw_global_get("rated_objs");

		if (!isset($ro[$oid]))
		{
			$this->db_query("
				INSERT INTO ratings(oid, rating, tm, uid, ip) 
				VALUES('$oid','$rate',".time().",'".aw_global_get("uid")."','".aw_global_get("REMOTE_ADDR")."')
			");
			$ro[$oid] = $rating;

			$rs = array(
				RATING_AVERAGE => $this->db_fetch_field("SELECT AVG(rating) AS avg FROM ratings WHERE oid = '$oid'", "avg"),
				RATING_HIGHEST => $this->db_fetch_field("SELECT MAX(rating) AS max FROM ratings WHERE oid = '$oid'", "max"),
				RATING_VIEWS => $this->db_fetch_field("SELECT hits FROM hits WHERE oid = '$oid'", "hits"),
				RATING_LOWEST_VIEWS => $this->db_fetch_field("SELECT hits FROM hits WHERE oid = '$oid'", "hits"),
				RATING_LOWEST_RATE => $this->db_fetch_field("SELECT MIN(rating) AS min FROM ratings WHERE oid = '$oid'", "min"),
			);
			$this->set_object_metadata(array(
				"oid" => $oid,
				"key" => "__ratings",
				"value" => $rs
			));
		}
		
		aw_session_set("rated_objs", $ro);
		header("Location: $return_url");
		die();
	}

	function show(&$arr)
	{
		extract($arr);
		$this->read_any_template("show.tpl");
		
		$ob = $this->get_object($id);

		// get list of all objects that this rating applies to
		$oids = array();
		if ($ob['meta']['objects_from'] == OBJECTS_FROM_CLID)
		{
			$where = "objects.class_id = ".$ob['meta']['objects_from_clid'];
		}
		else
		if ($ob['meta']['objects_from'] == OBJECTS_FROM_FOLDER)
		{
			// need to get a list of all folders below that one.
			$mn = array();
			$pts = new aw_array($ob['meta']['objects_from_folder']);
			foreach($pts->get() as $fld)
			{
				$mn += $this->get_objects_below(array(
					'parent' => $fld,
					'class' => CL_PSEUDO,
					'full' => true,
					'ignore_lang' => true,
					'ret' => ARR_NAME
				)) + array($fld => $fld);
			}
			$where = "objects.parent IN (".join(",",array_keys($mn)).")";
		}
		else
		if ($ob['meta']['objects_from'] == OBJECTS_FROM_OID)
		{
			$c_oid = $ob['meta']['objects_from_oid'];
			$c_obj = $this->get_object($c_oid);
			$c_inst = get_instance($this->cfg['classes'][$c_obj['class_id']]['file']);
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
			$where = "objects.oid IN (".join(",", array_keys($c_objs)).")";
		}

		// query the max/avg for those. 
		$order = "DESC";
		if ($ob['meta']['top_type'] == ORDER_HIGHEST)
		{
			$fun = "rating";
		}
		else
		if ($ob['meta']['top_type'] == ORDER_AVERAGE)
		{
			$fun = "AVG(rating)";
		}
		else
		if ($ob['meta']['top_type'] == ORDER_VIEWS)
		{
			$fun = "hits.hits";
		}
		else
		if ($ob['meta']['top_type'] == ORDER_LOWEST_RATE)
		{
			$fun = "rating";
			$order = "ASC";
		}
		else
		if ($ob['meta']['top_type'] == ORDER_LOWEST_VIEWS)
		{
			$fun = "hits.hits";
			$order = "ASC";
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
				ratings
				LEFT JOIN objects ON ratings.oid = objects.oid
				LEFT JOIN hits ON hits.oid = ratings.oid
				LEFT JOIN images ON images.id = ratings.oid
			WHERE
				$where
			GROUP BY 
				objects.oid
			ORDER BY val $order
			LIMIT ".(int)($ob['meta']['top'])."
		";
		$this->db_query($sql);
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
			"name" => $ob['name'],
			"count" => $ob['meta']['top']
		));
		return $this->parse();
	}

	function _get_link($dat)
	{
		if ($dat["class_id"] == CL_IMAGE)
		{
			return image::make_img_tag($this->img->get_url($dat['img_file']));
		}
		return $this->mk_my_orb("change", array("id" => $dat["oid"]), basename($this->cfg["classes"][$dat["class_id"]]["file"]));
	}
}
?>
