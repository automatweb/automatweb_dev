<?php
// $Header: /home/cvs/automatweb_dev/classes/common/country/country.aw,v 1.1 2005/10/23 17:17:15 voldemar Exp $
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


class country extends class_base
{
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
}

?>
