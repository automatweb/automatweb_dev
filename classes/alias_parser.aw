<?php

class alias_parser extends core
{
	function alias_parser()
	{
		$this->init();
	}

	////
	// !Parses all embedded objects inside another document
	// arguments:
	// oid(int) - document id
	// source - document content
	// args[meta][aliases] - optional, if set, result of get_oo_aliases for object $oid
	function parse_oo_aliases($oid,&$source,$args = array())
	{
		// should eliminate 99% of the texts that don't contain aliases -- ahz
		if(strpos($source, "#") === false)
		{
			return;
		}
		$_res = preg_match_all("/(#)(\w+?)(\d+?)(v|k|p|)(#)/i",$source,$matches,PREG_SET_ORDER);
		if (!$_res)
		{
			// if no aliases are in text, don't do nothin
			return;
		}
		extract($args);

		$this->tmp_vars = array();

		$o = obj($oid);
		if ($o->is_brother())
		{
			$oid = $o->get_original();
		}
		$aliases = $this->get_oo_aliases(array("oid" => $oid));

		$by_idx = $by_alias = array();

		$tmp = aw_ini_get("classes");
		foreach($tmp as $clid => $cldat)
		{
			if (isset($cldat["alias"]) || isset($cldat["old_alias"]))
			{
				$li = explode(",", $cldat["alias"]);
				foreach($li as $lv)
				{
					if (isset($cldat["alias_class"]))
					{
						$by_alias[$lv]["file"] = $cldat["alias_class"];
					}
					else
					{
						$by_alias[$lv]["file"] = $cldat["file"];
					}
					$by_alias[$lv]["class_id"] = $clid;
				}

				$li = explode(",", $cldat["old_alias"]);
				foreach($li as $lv)
				{
					if (isset($cldat["alias_class"]))
					{
						$by_alias[$lv]["file"] = $cldat["alias_class"];
					}
					else
					{
						$by_alias[$lv]["file"] = $cldat["file"];
					}
					$by_alias[$lv]["class_id"] = $clid;
				}
			}
		}

		$classlist = aw_ini_get("classes");

		// try to find aliases until we no longer find any. 
		// why is this? well, to enable the user to add aliases bloody anywhere. like in files that are to be shown right away
		enter_function("aliasmgr::parse_oo_aliases::loop");
		while (1)
		{

			$_cnt++;
			if ($_cnt > 20)
			{
				// make sure we don't end up in an endless loop
				break;
			}

			$_res = preg_match_all("/(#)(\w+?)(\d+?)(v|k|p|)(#)/i",$source,$matches,PREG_SET_ORDER);
			if (!$_res)
			{
				// if no more aliases are found, then break out of the loop.
				break;
			}

			if (is_array($matches))
			{
				// we gather all aliases in here, grouped by class so we gan give them to parse_alias_list()
				$toreplace = array();
				foreach ($matches as $key => $val)
				{
					$clid = $by_alias[$val[2]]["class_id"];
					// dammit, this sucks. I need some way to figure out
					// whether there is a correct idx set in the aliases, and if so
					// use that, instead of the one in the list.
					//$idx = $val[3] - 1;
					$idx = $val[3];
					$target = $aliases[$clid][$idx]["to"];

					$toreplace[$clid][$val[0]] = $aliases[$clid][$idx];
					$toreplace[$clid][$val[0]]["val"] = $val;

				}

				// here we do the actual parse/replace bit

				foreach($toreplace as $clid => $claliases)
				{
					$emb_obj_name = "emb" . $clid;
					$cldat = $classlist[$clid];
					$class_name = $cldat["alias_class"] != "" ? $cldat["alias_class"] : $cldat["file"];

					if ($class_name)
					{
						// load and create the class needed for that alias type
						$$emb_obj_name = get_instance($class_name);
						$$emb_obj_name->embedded = true;
					}


					// if not, then parse all the aliases one by one
					foreach($claliases as $avalue => $adata)
					{
						// if there is no object, then we just skip it -- ahz
						if(!is_oid($adata["target"]) || !$GLOBALS["object_loader"]->ds->can("view", $adata["target"]))
						{
							$source = str_replace($avalue, "", $source);
							continue;
						}
						$replacement = false;
						if (method_exists($$emb_obj_name,"parse_alias"))
						{
							$parm = array(
								"oid" => $oid,
								"matches" => $adata["val"],
								"alias" => $adata,
								"tpls" => &$args["templates"],
							);
							enter_function("aliasmgr::parse_oo_aliases::loop::do_palias");
							$repl = $$emb_obj_name->parse_alias($parm);
							exit_function("aliasmgr::parse_oo_aliases::loop::do_palias");

							$inplace = false;
							if (is_array($repl))
							{
								$replacement = $repl["replacement"];
								$inplace = $repl["inplace"];
							}
							else
							{
								$replacement = $repl;
							}

							if ($inplace)
							{
								$this->tmp_vars[$inplace] = $replacement;
								$replacement = "";
							};
						}

						$source = str_replace($avalue,$replacement,$source);
					}
				}
			}
		}	// while (1)
		exit_function("aliasmgr::parse_oo_aliases::loop");
	}

	////
	// !Gets all aliases for an object
	// params:
	//   oid - the object whose aliases we must return
	function get_oo_aliases($args = array())
	{
		extract($args);

		if (!$oid)
		{
			return array();
		}

		// lets' remove this for now. If there is a problem with alias enumeration
		// somewhere, then it should be fixed case by case basis instead of doing
		// it blindly over and over and over and over again
		//$this->recover_idx_enumeration($oid);

		$obj = obj($oid);
		$als = $obj->meta("aliaslinks");

		$ids = array();
		foreach($obj->connections_from() as $c)
		{
			$ids[] = $c->prop("to");
		}

		// fetch objs in object_list, it's fastah
		if (count($ids))
		{
			$ol = new object_list(array("oid" => $ids));
			$ol->arr();
		}

		foreach($obj->connections_from() as $c)
		{
			$tp = $c->prop();
			$tp["aliaslink"] = $als[$c->prop("to")];
			$tp["source"] = $tp["from"];
			$to = $c->to();

			$tp["target"] = $to->id();
			$tp["to"] = $to->id();
			$tp["class_id"] = $tp["to.class_id"];
			$tp["name"] = $tp["to.name"];
			$retval[$c->prop("to.class_id")][$c->prop("idx")] = $tp;
		}
		return $retval;
	}

	////
	// Returns the variables created by parse_oo_alias
	function get_vars()
	{
		return (is_array($this->tmp_vars)) ? $this->tmp_vars : array();
	}
}
?>
