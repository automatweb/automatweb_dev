<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/icons.aw,v 2.34 2005/01/20 20:58:51 kristo Exp $

class icons extends aw_template
{
	function icons()
	{
		$this->init("automatweb/config");
		$this->sub_merge = 1;
		lc_load("definition");
	}

	function get($id)
	{
		if (function_exists("aw_cache_get") && is_array(aw_cache_get("icon_cache",$id)))
		{
			return aw_cache_get("icon_cache",$id);
		}

		$this->db_query("SELECT * FROM icons WHERE id = $id");
		$ret = $this->db_next(false);
		if ($ret == false)
		{
			return false;
		}

		$ob = $this->db_fetch_row("SELECT * FROM objects WHERE oid = '$id'");
		if (is_array($ob))
		{
			$ret += $ob;
		}

		$ret["url"] = $this->cfg["baseurl"]."/automatweb/icon.".$this->cfg["ext"]."?id=$id";

		aw_cache_set("icon_cache",$id,$ret);

		return $ret;
	}

	/**  
		
		@attrib name=show params=name default="0"
		
		@param id required type=int
		
		@returns
		
		
		@comment

	**/
	function show()
	{
		$arg = func_get_arg(0);
		if (is_array($arg))
		{
			extract($arg);
		}
		else
		{
			$id = $arg;
		};

		if (!$id)
		{
			header("Location: ".$this->cfg["baseurl"]."/automatweb/images/icon_aw.gif");
			die();
		}

		$ic = $this->get($id);
		if (!is_array($ic))
		{
			header("Location: ".$this->cfg["baseurl"]."/automatweb/images/icon_aw.gif");
			die();
		}
		header("Content-type: ".$ic["file_type"]);
		echo $ic["file"];
	}

	////
	// !Tagastab mingile klassile vastava ikooni
	function get_icon_url($arg1,$name = "")
	{
		if (is_object($arg1))
		{
			$clid = $arg1->class_id();
			$done = false;
			$done = $arg1->flags() & OBJ_IS_DONE;
		}
		else
		{
			$clid = $arg1;
		};

		if ($clid == CL_FILE)
		{
			$pi = pathinfo($name);
			return aw_ini_get("icons.server")."/ftype_".$pi["extension"].".gif";
		}
		else
		if (in_array($clid,array("promo_box","brother","conf_icon_other","conf_icon_programs","conf_icon_classes","conf_icon_ftypes","conf_icons","conf_jf","conf_users","conf_icon_import","conf_icon_db","homefolder","hf_groups")))
		{
			return aw_ini_get("icons.server")."/iother_".$clid.".gif";
		}
		else
		{
			$sufix = $done ? "_done" : "";
			return aw_ini_get("icons.server")."/class_".$clid.$sufix.".gif";
		}

		return aw_ini_get("baseurl")."/automatweb/images/icon_aw.gif";
	}
	
	function get_feature_icon_url($fid)
	{
		return aw_ini_get("icons.server")."/prog_".$fid.".gif";
	}
}
?>
