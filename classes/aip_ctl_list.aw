<?php

class aip_ctl_list extends aw_template
{
	function aip_ctl_list()
	{
		$this->init("aip_ctl_list");
	}

	function orb_list($arr)
	{
		extract($arr);
		$this->read_template("list.tpl");

		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_FILE." AND status != 0");
		while ($row = $this->db_next())
		{
			$meta = $this->get_object_metadata(array(
				"metadata" => $row["metadata"]
			));

			$this->vars(array(
				"name" => $row["name"],
				"time" => $this->time2date($meta["act_time"], 2),
				"j_time" => $this->time2date($meta["j_time"], 2),
				"change" => $this->mk_my_orb("change", array(
					"id" => $row["oid"], 
					"return_url" => urlencode($this->mk_my_orb("list"))
				),"file")
			));

			$l.= $this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $l,
			"upload" => $this->mk_my_orb("upload")
		));
		return $this->parse();
	}

	function upload($arr)
	{
		extract($arr);
		$this->read_template("upload.tpl");

		$this->mk_path(0,"<a href='".$this->mk_my_orb("list")."'>Nimekiri</a> / Uploadi fail");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_upload")
		));
		return $this->parse();
	}

	function submit_upload($arr)
	{
		extract($arr);

		if (is_uploaded_file($file))
		{
			$fc = file($file);

			$cfs = array();

			$pre = "";
			foreach($fc as $line)
			{
				if (strpos($line,"%") !== false)
				{
					$res = explode("%", $line);
					$cfs[] = array();
				}
				else
				{
					$pre = trim($line);
				}
			}
		}
		return $this->mk_my_orb("upload");
	}
}
?>