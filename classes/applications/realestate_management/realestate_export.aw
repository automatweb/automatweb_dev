<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/realestate_management/realestate_export.aw,v 1.2 2005/11/22 16:50:49 voldemar Exp $
// realestate_export.aw - Kinnisvaraobjektide eksport
/*

@classinfo syslog_type=ST_REALESTATE_EXPORT relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize
	@property realestate_manager type=relpicker reltype=RELTYPE_OWNER clid=CL_REALESTATE_MANAGER automatic=1
	@comment Kinnisvarahalduskeskkond, mille objekte soovitakse eksportida
	@caption Kinnisvarahalduskeskkond

	@property city24export_xsl type=textbox
	@caption City24 ekspordi xsl faili url

	@property city24export_encoding type=relpicker reltype=RELTYPE_ENCODING clid=CL_COUNTRY_ADMINISTRATIVE_STRUCTURE_ENCODING automatic=1
	@caption Koodid aadresside City24 ekspordi jaoks

	@property last_city24export type=text datatype=int
	@caption Viimase ekspordi staatus City24

	@property last_city24export_time type=text
	@caption Viimane eksport City24

	@property last_city24export_error type=text
	@caption Viimasel ekspordil esinenud vead

// --------------- RELATION TYPES ---------------------

@reltype OWNER clid=CL_REALESTATE_MANAGER value=1
@caption Kinnisvaraobjektide halduskeskkond

@reltype ENCODING clid=CL_COUNTRY_ADMINISTRATIVE_STRUCTURE_ENCODING value=2
@caption Aadresside vastavuskoodid

*/

define ("REALESTATE_TIME_FORMAT", "j/m/Y H.i.s");

class realestate_export extends class_base
{
	var $realestate_manager;
	var $export_objlist;
	var $from_date;

	function realestate_export()
	{
		$this->init(array(
			"tpldir" => "applications/realestate_management/realestate_export",
			"clid" => CL_REALESTATE_EXPORT
		));
	}

	function callback_on_load ($arr)
	{
	}

	// @attrib name=init_local
	// @param id required type=int
	function init_local ($arr)
	{
		$this_object = obj ($arr["id"]);

		if (is_oid ($this_object->prop ("realestate_manager")) and $this->can ("view", $this_object->prop ("realestate_manager")))
		{
			$this->realestate_manager = obj ($this_object->prop ("realestate_manager"));
		}
		else
		{
			return t("Kinnisvarahalduskeskkond m22ramata v6i puudub juurdep22su6igus.\n");
		}
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "last_city24export_time":
				$prop["value"] = date (REALESTATE_TIME_FORMAT, $prop["value"]);
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

	// @attrib name=get_objects
	function get_objects ($arr)
	{
		$realestate_classes = array (
			CL_REALESTATE_HOUSE,
			CL_REALESTATE_ROWHOUSE,
			CL_REALESTATE_COTTAGE,
			CL_REALESTATE_HOUSEPART,
			CL_REALESTATE_APARTMENT,
			CL_REALESTATE_COMMERCIAL,
			CL_REALESTATE_GARAGE,
			CL_REALESTATE_LAND,
		);
		$realestate_folders = array (
			$this->realestate_manager->prop ("houses_folder"),
			$this->realestate_manager->prop ("rowhouses_folder"),
			$this->realestate_manager->prop ("cottages_folder"),
			$this->realestate_manager->prop ("houseparts_folder"),
			$this->realestate_manager->prop ("apartments_folder"),
			$this->realestate_manager->prop ("commercial_properties_folder"),
			$this->realestate_manager->prop ("garages_folder"),
			$this->realestate_manager->prop ("land_estates_folder"),
		);

		$this->export_objlist = new object_list (array (
			"class_id" => $realestate_classes,
			"parent" => $realestate_folders,
			"modified" => new obj_predicate_compare (OBJ_COMP_GREATER, $this->from_date),
		));
	}

	// @param from_date required type=int
/**
	@attrib name=city24export
	@param id required type=int
**/
	function city24export ($arr)
	{
		$this->time = time ();
		$errors = "";
		$errors .= $this->init_local ($arr);
		$this_object = obj ($arr["id"]);
		$this->from_date = (int) $this_object->prop ("last_city24export_time");

		$this->get_objects ($arr);
		$objects = $this->export_objlist->arr ();
		$xml = array ();
		$xml[] = '<?xml version="1.0" encoding="iso-8859-4" ?>';
		$xml[] = '<objects>';

		foreach ($objects as $o)
		{
			$cl_realestate = $o->instance ();
			$o_xml = $cl_realestate->export_xml (array (
				"id" => $o->id (),
				"no_declaration" => true,
				"address_encoding" => $this_object->prop ("city24export_encoding"),
			));

			if (empty ($cl_realestate->export_errors))
			{
				$xml[] = $o_xml;
			}
			else
			{
				$errors .= sprintf (t("Viga objekti ekspordil. AW id: %s.\n<blockquote>%s</blockquote>\n"), $o->id (), $cl_realestate->export_errors);
			}
		}

		$xml[] = '</objects>';
		$xml = implode ("\n", $xml);

/* dbg */ if ($_GET["show_input"] == 1) { header ("Content-Type: text/xml"); echo $xml; exit;}

		$tmpname = tempnam (aw_ini_get("server.tmpdir"), "realestateimport");
		$tmp = fopen ($tmpname, "w");
		fwrite ($tmp, $xml);
		fclose($tmp);
		unset($xml);

		$xslt_processor = xslt_create ();
		$export_xml = xslt_process ($xslt_processor, $tmpname, $this_object->prop ("city24export_xsl"));

		if ($errors or xslt_errno ($xslt_processor))
		{
			$this_object->set_prop ("last_city24export_time", $this->time);
			$this_object->set_prop ("last_city24export", 0);
		}
		else
		{
			$this_object->set_prop ("last_city24export", 1);
		}

		$errors = sprintf ("AW errors: <pre>%s</pre> <hr> XSLT error: <pre>%s</pre> <hr> XSLT error code: %s", $errors, xslt_error ($xslt_processor), xslt_errno ($xslt_processor));
		$this_object->set_prop ("last_city24export_error", $errors);
		$this_object->save ();
		xslt_free($xslt_processor);
		unlink($tmpname);

		header ("Content-Type: text/xml");
		echo $export_xml;
		exit;
	}


/**
	@attrib name=city24export_status
	@param id required type=int
**/
	function city24export_status ($arr)
	{
		$this_object = obj ($arr["id"]);
		echo $this_object->prop ("last_city24export");
		exit;
	}
}

?>
