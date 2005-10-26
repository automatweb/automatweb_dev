<?php
// $Header: /home/cvs/automatweb_dev/classes/common/country/country.aw,v 1.3 2005/10/26 15:37:36 voldemar Exp $
// country.aw - Riik v2
/*

@classinfo syslog_type=ST_COUNTRY relationmgr=yes no_comment=1 no_status=1

@groupinfo grp_settings caption="Seaded"


@default table=objects
@default field=meta
@default method=serialize
@default group=general

@default group=grp_settings
	@property administrative_structure type=relpicker reltype=RELTYPE_ADMINISTRATIVE_STRUCTURE clid=CL_COUNTRY_ADMINISTRATIVE_STRUCTURE
	@caption Haldusjaotuse struktuur


// --------------- RELATION TYPES ---------------------

@reltype ADMINISTRATIVE_STRUCTURE value=1 clid=CL_COUNTRY_ADMINISTRATIVE_STRUCTURE
@caption Haldusjaotuse struktuur

*/

### address system settings
define ("NEWLINE", "<br />");

class country extends class_base
{
	var $admin_unit_type_classes = array (
		CL_COUNTRY_ADMINISTRATIVE_UNIT,
		CL_COUNTRY_CITYDISTRICT,
		CL_COUNTRY_CITY,
		CL_ADDRESS_STREET,
	);

	function country()
	{
		$this->init(array(
			"tpldir" => "common/country",
			"clid" => CL_COUNTRY
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "":
				$addresses_using_this = "";

				if ( ($addresses_using_this > 0) and (is_oid ($prop["value"])) )
				{
					$prop["error"] = sprintf (t("%s aadressi kasutab hetkel valitud haldusjaotust! Muudatuste salvestamisel ..."), $addresses_using_this);//!!! t2psustada mis juhtub kui uus struktuur m22rata.
				}
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

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
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

/* public methods */

/**
    @attrib name=add_adminunit
	@param name required
	@param parent required
	@param type required
	@param return_object optional
	@returns Created unit object/oid (depending on whether return_object is true or false). If existing unit with name was found that will be returned.
	@comment type is object or oid of object from class CL_COUNTRY_ADMINISTRATIVE_UNIT_TYPE or "street" in case a street is to be added
**/
	function add_adminunit ($arr)
	{
		if (is_object ($arr["type"]))
		{
			$admin_unit_type =& $arr["type"];
		}
		elseif ($this->can ("view", $arr["type"]))
		{
			$admin_unit_type = obj ($arr["type"]);
		}

		if (is_object ($admin_unit_type))
		{
			if ($admin_unit_type->class_id () != CL_COUNTRY_ADMINISTRATIVE_UNIT_TYPE)
			{
/* dbg */ if ($_GET["address_dbg"] == 1) { $tmp = $admin_unit_type->class_id (); echo "adminunittype class wrong [{$tmp}]".NEWLINE; }
				return false;
			}

			$class_id = $admin_unit_type->prop ("unit_type");
			$subclass = $admin_unit_type->id ();
		}
		elseif ((string) $arr["type"] == "street")
		{
			$class_id = CL_ADDRESS_STREET;
			$subclass = 0;
		}
		else
		{
/* dbg */ if ($_GET["address_dbg"] == 1) { echo "adminunittype undefined [{$arr["type"]}]".NEWLINE; }
			return false;
		}

		$name = trim ($arr["name"]);
		$parent = is_object ($arr["parent"]) ? $arr["parent"]->id () : $arr["parent"];
		$return_object = (boolean) $arr["return_object"];
		$arr["return_object"] = 1;
		$arr["type"] = $class_id;
		$o = $this->get_adminunit_by_name ($arr);

		if ($o === false)
		{
/* dbg */ if ($_GET["address_dbg"] == 1) { echo "existing unit search fail".NEWLINE; }
			return false;
		}
		elseif (!isset ($o))
		{ ### add new
			$o =& new object ();
			$o->set_class_id ($class_id);
			$o->set_parent ($parent);
			$o->set_subclass ($subclass);
			$o->set_name ($name);
			$o->save ();
/* dbg */ if ($_GET["address_dbg"] == 1) { echo "added object [{$name}] under [{$parent}] with subclass [{$subclass}]".NEWLINE; }
		}

		if ($return_object)
		{
			return $o;
		}
		else
		{
			return $o->id ();
		}
	}

/**
    @attrib name=get_adminunit_by_name
	@param name required
	@param parent required
	@param type required
	@param return_object optional
	@returns Unit object/oid (depending on whether return_object is true or false) corresponding to name.
**/
	function get_adminunit_by_name ($arr)
	{
		$name = trim ($arr["name"]);
		$parent = is_object ($arr["parent"]) ? $arr["parent"]->id () : $arr["parent"];
		$return_object = (boolean) $arr["return_object"];
		$class_id = (int) $arr["type"];

		if (empty ($name) or !in_array ($class_id, $this->admin_unit_type_classes))
		{
/* dbg */ if ($_GET["address_dbg"] == 1) { echo "name [{$name}] empty or type [{$class_id}] wrong".NEWLINE; }
			return false;
		}

		### search for existing
		$list = new object_list (array (
			"class_id" => $class_id,
			"parent" => $parent,
			"name" => array ($name),
			"site_id" => array (),
			"lang_id" => array (),
		));

		if ($list->count () == 1)
		{
			$o = $list->begin ();
		}
		elseif ($list->count () > 1)
		{ ### structure contains duplicates
/* dbg */ if ($_GET["address_dbg"] == 1) { echo "duplicates found for name [{$name}] under parent [{$parent}]".NEWLINE; }
			### move everything from under redundant admin units unto one, selected randomly (?)
			$o = $list->begin ();
			$list->remove ($o->id ());
			$redundant_unit = $list->begin ();

			while (is_object ($redundant_unit))
			{
				$child_list = new object_list (array (
					"parent" => $redundant_unit->id (),
					"site_id" => array (),
					"lang_id" => array (),
				));
				$child_list->set_parent ($o->id ());
				$child_list->save ();
				$redundant_unit = $list->next ();
			}

			### delete redundant admin units
			$list->delete ();
		}
		else
		{
/* dbg */ if ($_GET["address_dbg"] == 1) { echo "no objects found for name [{$name}]".NEWLINE; }
			return;
		}

		if ($return_object)
		{
			return $o;
		}
		else
		{
			return $o->id ();
		}
	}
/* END public methods */
}

?>
