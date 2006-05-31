<?php

class form_ctl_alias extends core
{
	function form_ctl_alias()
	{
		$this->init("forms");
	}

	////
	// !loads controller, replaces vars - thevars are taken from the current controller, not the linked controller scope
	function _load_ctrl_eq($id)
	{
		$co = $this->load_controller($id);
		return $co["meta"]["eq"];
	}

	function _incl_file($file)
	{
		$fn = aw_ini_get("site_basedir")."/".$file.".".aw_ini_get("ext");
		$fc = $this->get_file(array("file" => $fn));
		$fc = preg_replace("/{include:(.*)}/eU","\$this->_incl_file(\\1)",$fc);
		return $fc;
	}

	function parse_alias($args = array())
	{
		$id = $args["alias"]["target"];
		$ret = obj($id);
		$co = $ret->fetch();
		$this->loaded_controller = $co;

		$eq = preg_replace("/{load:(\d*)}/e","\$this->_load_ctrl_eq(\\1)",$co["meta"]["eq"]);

		// include files
		$eq = preg_replace("/{include:(.*)}/eU","\$this->_incl_file(\"\\1\")",$eq);

		$eq = "\$res = ".$eq.";\$contr_finish = true;";
		eval($eq);
		if (!$contr_finish)
		{
			$this->dequote(&$eq);
			eval($eq);
		}
		return $res;
	}
}