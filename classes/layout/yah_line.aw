<?php

/*

@classinfo syslog_type=ST_YAH_LINE maintainer=kristo

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property separator type=textbox size=5
@caption Eraldaja

@property show_nosubs type=checkbox ch_value=1 
@caption Kas n&auml;idata men&uuml;&uuml;sid, mille all pole dokumente

*/

class yah_line extends class_base
{
	function yah_line()
	{
		$this->init(array(
			'clid' => CL_YAH_LINE
		));
	}

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}

	function show($arr)
	{
		extract($arr);
		$ob = new object($id);

		$pd = get_instance("layout/active_page_data");
		$path = $pd->get_active_path();

		$show = false;

		$names = array();
		$mc = get_instance("menu_cache");
		foreach($path as $oid)
		{
			if ($show)
			{
				$mn = $mc->get_cached_menu($oid);
				$check_subs = ($mc->subs[$mn["oid"]] > 0) || $ob->prop('show_nosubs') == 1;

				if ($mn["clickable"] == 1 && $check_subs)
				{
					$names[] = $mn["name"];
				}
			}
			if ($oid == $this->cfg["rootmenu"])
			{
				$show = true;
			}
		}

		return join($ob->prop('separator'), map(' %s ',$names));
	}
}
?>
