<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/document_statistics.aw,v 1.2 2004/03/25 09:40:40 kristo Exp $
// document_statistics.aw - Dokumentide vaatamise statistika 
/*

@classinfo syslog_type=ST_DOCUMENT_STATISTICS relationmgr=yes no_status=1

@default table=objects
@default group=general

@property timespan type=select field=meta method=serialize
@caption Ajavahemik

@property count type=textbox field=meta method=serialize
@caption Mitu esimest

@property stats type=table store=no
@caption TOP

*/

class document_statistics extends class_base
{
	function document_statistics()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/document_statistics",
			"clid" => CL_DOCUMENT_STATISTICS
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "stats":
				$st = $this->get_stat_arr(array(
					"timespan" => $arr["obj_inst"]->prop("timespan"),
					"count" => $arr["obj_inst"]->prop("count"),
				));
				
				$data["vcl_inst"]->define_field(array(
					"name" => "docid",
					"caption" => "Dokument"
				));

				$data["vcl_inst"]->define_field(array(
					"name" => "hits",
					"caption" => "Vaatamisi",
					"align" => "center"
				));

				foreach($st as $did => $hc)
				{
					$o = obj($did);
					$a = array(
						"docid" => $o->name(),
						"hits" => $hc
					);
					$data["vcl_inst"]->define_data($a);
				}

				$data["vcl_inst"]->set_default_sortby("hits");
				$data["vcl_inst"]->set_default_sorder("desc");
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

		}
		return $retval;
	}	

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);

		classload("vcl/table");
		$t = new aw_table();

		$st = $this->get_stat_arr(array(
			"timespan" => $ob->prop("timespan"),
			"count" => $ob->prop("count"),
		));
				

		$this->read_template("show.tpl");

		$l = "";

		foreach($st as $did => $hc)
		{
			$o = obj($did);
			$this->vars(array(
				"doc_name" => $o->name(),
				"docid" => $did,
				"hits" => $hc
			));

			$l .= $this->parse("LINE");
		}

		$this->vars(array(
			"LINE" => $l
		));
		return $this->parse();
	}

	/** adds a hit to the document hit list to the document $docid
	**/
	function add_hit($docid)
	{
		// open and lock file
		list($fp, $size) = $this->_open_and_lock_stat_file();

		// get contents
		$fc = explode("\n", fread($fp, $size));

		// modify the correct line
		$nf = "";
		$found = false;
		foreach($fc as $line)
		{
			if ($line == "")
			{
				continue;
			}
			list($did, $hc) = explode(",", $line);
			if ($did == $docid)
			{
				$line = $docid.",".($hc+1);
				$found = true;
			}
			$nf .= trim($line)."\n";
		}

		if (!$found)
		{
			$nf .= $docid.",1\n";
		}
	
		// write file
		ftruncate($fp, 0);
		fwrite($fp, $nf);

		// unlock & close
		$this->_close_and_unlock_stat_file($fp);
	}

	function _open_and_lock_stat_file()
	{
		$fld = $this->cfg["site_basedir"]."/files/docstats";
		if (!is_dir($fld))
		{
			mkdir($fld, 0777);
			@chmod($fld, 0777);
		}
		$fp = $fld."/".date("Y-m-d").".txt";
		$ret = fopen($fp, "a+");
		flock($ret, LOCK_EX);
		return array($ret, filesize($fp));
	}

	function _close_and_unlock_stat_file($fp)
	{
		fflush($fp);
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	/** returns an array of statistics for document views

		@param timespan required

		@param count required type=int

		@comment

			returns an array of document id => hit count 
			for the top $count documents in the timespan
	**/
	function get_stat_arr($arr)
	{
		extract($arr);
		$fp = $this->cfg["site_basedir"]."/files/docstats/".date("Y-m-d").".txt";
		$fc = explode("\n", $this->get_file(array("file" => $fp)));

		$ds_arr = array();

		foreach($fc as $line)
		{
			if ($line == "")
			{
				continue;
			}
			list($did, $hc) = explode(",", $line);
			$ds_arr[$did] = $hc;
		}
		
		arsort($ds_arr);
		$ret = array();
		$i = 0;
		foreach($ds_arr as $did => $hc)
		{
			if ($i > $count)
			{
				return $ret;
			}
		
			$ret[$did] = $hc;
			$i++;
		}
		return $ret;
	}
}
?>
