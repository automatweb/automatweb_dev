<?php

classload("objects");
classload("config");

class search_conf extends aw_template 
{
	function search_conf()
	{
		$this->tpl_init("search_conf");
		$this->db_init();
	}

	function gen_admin($level)
	{
		global $lang_id,$SITE_ID;

		$ob = new db_objects;
		$c = new db_config;
		$conf = unserialize($c->get_simple_config("search_conf"));

		if (!$level)
		{
			$this->read_template("conf1.tpl");
			$this->vars(array("section"		=> $ob->multiple_option_list($conf[$SITE_ID][$lang_id][sections],$ob->get_list())));
			return $this->parse();
		}
		else
		{
			$sarr = $ob->get_list();

			$this->read_template("conf2.tpl");
			reset($conf[$SITE_ID][$lang_id][sections]);
			while (list(,$v) = each($conf[$SITE_ID][$lang_id][sections]))
			{
				$this->vars(array("section" => $sarr[$v],"section_id" => $v,"section_name" => $conf[$SITE_ID][$lang_id][names][$v],"order" => $conf[$SITE_ID][$lang_id][order][$v]));
				$s.= $this->parse("RUBR");
			}
			$this->vars(array("RUBR" => $s));
			return $this->parse();
		}
	}

	function submit($arr)
	{
		global $lang_id,$SITE_ID;

		extract($arr);

		if (is_array($section))
		{
			reset($section);
			$a = array();
			while (list(,$v) = each($section))
				$a[$v]=$v;
		}

		$c = new db_config;
		$conf = unserialize($c->get_simple_config("search_conf"));

		if (!$level)
		{
			$conf[$SITE_ID][$lang_id][sections] = $a;
			$c->set_simple_config("search_conf",serialize($conf));
			return 1;
		}
		else
		{
			$conf[$SITE_ID][$lang_id][names] = array();
			reset($arr);
			while (list($k,$v) = each($arr))
			{
				if (substr($k,0,3) == "se_")
				{
					$id = substr($k,3);
					$conf[$SITE_ID][$lang_id][names][$id] = $v;
				}
			}

			$conf[$SITE_ID][$lang_id][order] = array();
			reset($arr);
			while (list($k,$v) = each($arr))
			{
				if (substr($k,0,3) == "so_")
				{
					$id = substr($k,3);
					$conf[$SITE_ID][$lang_id][order][$id] = $v;
				}
			}
			$c->set_simple_config("search_conf",serialize($conf));
			return 1;
		}
	}

	function get_search_list()
	{
		$c = new db_config;
		$conf = unserialize($c->get_simple_config("search_conf"));
		if (is_array($conf[$GLOBALS["SITE_ID"]][$GLOBALS["lang_id"]][names]))
		{
			// we must sort the damn thing now
			$tmp = $conf[$GLOBALS["SITE_ID"]][$GLOBALS["lang_id"]][order];
			if (is_array($tmp))
			{
				asort($tmp,SORT_NUMERIC);
				reset($tmp);
				$ret = array();
				while (list($id,) = each($tmp))
					$ret[$id] = $conf[$GLOBALS["SITE_ID"]][$GLOBALS["lang_id"]][names][$id];
			}
			else
				$ret = $conf[$GLOBALS["SITE_ID"]][$GLOBALS["lang_id"]][names];
			return $ret;
		}
		else
			return array();
	}
}
?>