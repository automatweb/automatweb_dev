<?php

class site_logger extends core
{
	function site_logger()
	{
		$this->init();
	}

	////
	// !writes a pageview event to the aw log
	function add($arr)
	{
		$this->_log(
			ST_MENUEDIT, 
			SA_PAGEVIEW, 
			$this->get_log_message(),
			aw_global_get("section")
		);
	}

	function get_log_message()
	{
		$sec_o = obj(aw_global_get("section"));
		$path_str = $sec_o->path_str();

		// now also, if we are in some fg incremental search, 
		// log the "address" of that as well.
		if ($GLOBALS["tbl_sk"] != "")
		{
			$names = array();
			$tbld = aw_global_get("fg_table_sessions");
			$ar = new aw_array($tbld[$GLOBALS["tbl_sk"]]);
			foreach($ar->get() as $url)
			{
				preg_match("/restrict_search_val=([^&$]*)/",$url,$mt);
				$names[] = urldecode($mt[1]);
			}
			$path_str .= "/".join("/".$names);
		}

		// evil e-mail link tracking code

		global $artid,$sid,$mlxuid;
		if ($artid)	// tyyp tuli meilist, vaja kirja panna
		{
			if (is_number($artid))
			{
				$sid = (int)$sid;
				$ml_msg = $this->db_fetch_row("SELECT * FROM ml_mails WHERE id = '$sid'");

				$this->db_query("SELECT ml_users.*,objects.name as name FROM ml_users LEFT JOIN objects ON objects.oid = ml_users.id WHERE id = '$artid'");
				if (($ml_user = $this->db_next()))
				{
					$msg = $ml_user["name"]." (".$ml_user["mail"].") tuli lehele $path_str meilist ".$ml_msg["subj"];

					// and also remember the guy
					// set a cookie, that expires in 3 years
					setcookie("mlxuid",$artid,time()+3600*24*1000,"/");
				}
			}
		}
		else
		if ($mlxuid)
		{
			$this->db_query("SELECT ml_users.*,objects.name as name FROM ml_users LEFT JOIN objects ON objects.oid = ml_users.id WHERE id = '$mlxuid'");
			if (($ml_user = $this->db_next()))
			{
				$msg = $ml_user["name"]." (".$ml_user["mail"].") vaatas lehte $path_str";
			}
		}
		else
		{
			$msg = $path_str;
		}

		return $msg;
	}
}

?>
