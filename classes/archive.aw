<?php
// archive.aw - Archive class

// arhiivide jaoks tuleb luua eraldi kataloog (check), sinna sisse 
// kahetasemeline (peaks siiski muudetav olema) kataloogipuu, igal
// arhiveeritud objektil on selle objekti ID nimeline kataloog, mille
// sees voivad on failid (nimeks timestamp), selle objekti varasematest
// versioonidest. This class will rely heavily on object serialization
// functions.

// This class has no public interface, it is used internally

// saidi const.aw peab defineerima 2 konstanti
// ARC_DEPTH = n (mitu astet katalooge arhiivi tehakse)
// ARC_ACTIVE = boolean (kas arhiivi kasutatakse)

// I think this class really should be independent, since archiving objects
// can be quite complex
class archive extends aw_template {
	var $arc_dir; // millises kataloogis faile hoitakse?
	function archive($args = array())
	{
		extract($args);

		$this->tpl_init();
		$this->db_init();

		if (not(defined("ARC_DEPTH")))
		{
			$this->raise_error("ARC_DEPTH is not defined, cannot continue",true);
		};

		global $site_basedir; // *cringe*
		$this->arc_dir = sprintf("%s/%s",$site_basedir,"archive");

		// this might be slow
		if (not(is_writable($this->arc_dir)))
		{
			$this->raise_error("Archive directory is not writable",true);
		};

	}

	////
	// !Leiab pathi arhiivi objektini
	function _calc_path($args = array())
	{
		$this->id = sprintf("%04d",$args["oid"]);

		$this->path_parts = array();

		for ($i = 0; $i < ARC_DEPTH; $i++ )
		{
			$this->path_parts[] = substr($this->id,$i * 2,2);
		};

		$path = join("/",$this->path_parts);
		$this->fullpath = $this->arc_dir . "/" . $path . "/" . $this->id;

	}

	////
	// !Lisab objekti arhiivi, luues vajadusel ka kataloogid
	// argumendid
	// oid(int) - objekti ID, millest koopia teha
	function add($args = array())
	{
		extract($args);
		$this->_calc_path($args);
		foreach($this->path_parts as $key => $val)
		{
			$path .= "/$val";
			// silence all possible error messages
			@mkdir($this->arc_dir . $path,0700);
		};

		// create the actual directory.
		@mkdir($this->arc_dir . $path . "/" . $oid,0700);
	}

	////
	// !Teeb objekti hetkeseisust koopia arhiivi
	// argumendid
	// oid(int) - objekti ID, millest koopia teha
	function commit($args = array())
	{
		extract($args);
		$this->_calc_path($args);
		$tstamp = time();
		$fname = $this->fullpath . "/$tstamp";
		$this->put_file(array(
			"file" => $fname,
			"content" => $args["content"],
		));
		$meta = $this->obj_get_meta(array("oid" => $oid));

		$arc = $meta["archive"];

		$arc[$tstamp] = array(
			"timestamp" => $tstamp,
			"uid" => UID,
			"name" => $name,
			"comment" => $comment,
		);

		$this->obj_set_meta(array(
			"oid" => $oid,
			"meta" => array("archive" => $arc),
		));

		return $timestamp;
	}

	////
	// !Listib arhiivi sisu
	function get($args = array())
	{
		$this->_calc_path($args);
		$res = array();
                if ($dir = @opendir($this->fullpath)) {
                        while ($file = readdir($dir)) {
				$full = $this->fullpath . "/$file";
				if (is_file($full))
				{
					$res[] = stat($full);
				};
                        }
                        closedir($dir);
                };
		return $res;
	}
	
	////
	// !Tagastab arhiivist mingi objekti mingi versioon
	// argumendid:
	// oid (int) - objekti ID, mille arhiivi lugeda
	// version (timestamp) - millist konkreetset versiooni lugeda
	function checkout($args = array())
	{
		extract($args);
		$this->_calc_path($args);
		$fname = $this->fullpath . "/$version";
		$retval = $this->get_file(array(
			"file" => $fname,
		));
		return $retval;
	}

	////
	// !Eemaldab arhiivist ühe arhiiviobjekti voi terve kategooria
	function remove($args = array())
	{
		// yet to be written
		extract($args);


	}
};
?>
