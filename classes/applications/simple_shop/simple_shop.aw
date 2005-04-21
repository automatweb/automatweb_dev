<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/simple_shop/Attic/simple_shop.aw,v 1.1 2005/04/21 09:40:49 ahti Exp $
// simple_shop.aw - Lihtne tootekataloog 
/*

@classinfo syslog_type=ST_SIMPLE_SHOP relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property folder type=relpicker reltype=RELTYPE_FOLDER
@caption Toodete kaust

@property import_file type=fileupload store=no
@caption Andmed failist

@property replace_prods type=checkbox ch_value=1 store=no
@caption Asenda tooted

@groupinfo orders caption="Tellimused"
@default group=orders

@property orders_toolbar type=toolbar no_caption=1

@property orders_table type=table no_caption=1

@reltype WAREHOUSE value=1 clid=CL_SHOP_WAREHOUSE
@caption Tootekataloog

@reltype FOLDER value=2 clid=CL_MENU
@caption Toodete kaust

*/

class simple_shop extends class_base
{
	function simple_shop()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/simple_shop/simple_shop",
			"clid" => CL_SIMPLE_SHOP
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "import_file":
			case "replace_prods":
				if($arr["new"])
				{
					return PROP_IGNORE;
				}
				$fld = $arr["obj_inst"]->prop("folder");
				if(is_oid($fld) && $this->can("view", $fld))
				{
					return $retval;
				}
				return PROP_IGNORE;
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
			case "import_file":
				$this->_import_file($arr);
				break;
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		//tootekood, nimetus, mõõtühik, hind
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function _import_file($arr)
	{
		$file = $_FILES["import_file"]["tmp_name"];
		if(is_uploaded_file($file))
		{
			$fc = $this->get_file(array(
				"file" => $file,
			));
		}
		else
		{
			return PROP_IGNORE;
		}
		$fc = explode("\n", $fc);
		$fld = $arr["obj_inst"]->prop("folder");
		if($arr["request"]["replace_prods"] == 1)
		{
			enter_function("simple_shop::replace_prods");
			$prods = new object_list(array(
				"class_id" => CL_SIMPLE_SHOP_PRODUCT,
				"parent" => $fld,
			));
			echo sprintf(t("kustutan %d objekti"), $prods->count())."<br />";
			$prods->delete();
			exit_function("simple_shop::replace_prods");
			echo t("kustutatud")."<br />";
		}
		
		$count = 0;
		obj_set_opt("no_cache", 1);
		echo sprintf(t("impordin %d objekti"), count($fc))."<br />";
		enter_function("simple_shop::prod_import");
		foreach($fc as $row)
		{
			$row = explode("\t", $row);
			
			// kill some overkills.. ehehehehe... :(
			if($count == 0 || $count > 17000)
			{
				$count++;
				continue;
			}
			$count++;
			$obj = obj();
			$obj->set_class_id(CL_SIMPLE_SHOP_PRODUCT);
			$obj->set_parent($fld);
			$obj->set_prop("name", $row[0]);
			$obj->set_prop("prod_code", $row[1]);
			$obj->set_prop("unit", $row[2]);
			$obj->set_prop("price", $row[3]);
			$obj->save();
		}
		$cache = get_instance("cache");
		$cache->full_flush();
		exit_function("simple_shop::prod_import");
		echo t("imporditud")."<br />";
	}
	function callback_post_save($arr)
	{
		if($arr["new"])
		{
			$this->db_query("create table simple_shop_$arr[id] (prod_code VARCHAR(70) NOT NULL default 0, descr VARCHAR(255) NULL, unit VARCHAR(70), price INT(11) NOT NULL, key(prod_code));");
		}
	}
}
?>