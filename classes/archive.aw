<?php
// archive.aw - Archive class
// $Header: /home/cvs/automatweb_dev/classes/Attic/archive.aw,v 2.14 2002/11/07 10:52:16 kristo Exp $

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
class archive extends aw_template 
{
	var $arc_dir; // millises kataloogis faile hoitakse?

	function archive($args = array())
	{
		extract($args);

		$this->init("");

		// since most archive functions use serialization anyway
		// we can load and use that module from here anyway
		classload("php");
		$this->serializer = new php_serializer();

		if (not($this->cfg["depth"]))
		{
			$this->raise_error(ERR_ARC_NODEPTH,"archive.depth is not specified, cannot continue",true);
		};

		$this->arc_dir = sprintf("%s/%s",$this->cfg["site_basedir"],"archive");

		// this might be slow
		if (not(is_writable($this->arc_dir)))
		{
			$this->raise_error(ERR_ARC_NOWRITE,"Archive directory is not writable",true);
		};
	}

	////
	// !Leiab pathi arhiivi objektini
	function _calc_path($args = array())
	{
		$this->id = sprintf("%04d",$args["oid"]);

		$this->path_parts = array();

		for ($i = 0; $i < $this->cfg["depth"]; $i++ )
		{
			$this->path_parts[] = substr($this->id,$i * 2,2);
		};

		$path = join("/",$this->path_parts);
		$this->fullpath = $this->arc_dir . "/" . $path . "/" . sprintf("%04d",$this->id);
	}

	////
	// !Lisab objekti arhiivi, luues vajadusel ka kataloogid
	// argumendid
	// oid(int) - objekti ID, millest koopia teha
	function add($args = array())
	{
		if (not($this->cfg["use"]))
		{
			return false;
		};

		extract($args);
		$this->_calc_path($args);
		foreach($this->path_parts as $key => $val)
		{
			$path .= "/$val";
			// silence all possible error messages
			@mkdir($this->arc_dir . $path,0700);
		};

		// create the actual directory.
		@mkdir($this->arc_dir . $path . "/" . sprintf("%04d",$oid),0700);
	}

	////
	// !Teeb objekti hetkeseisust koopia arhiivi
	// argumendid
	// oid(int) - objekti ID, millest koopia teha
	// data(array) - väljad, mis on vaja andmebaasitabelisse salvestada
	function commit($args = array())
	{
		if (not($this->cfg["use"]))
		{
			return false;
		};
		extract($args);
		$this->_calc_path($args);

		// if version is defined, then existing revision is updated
		// otherwise a new version is created
		if ($version)
		{
			$tstamp = $version;
			// if we update an existing version then we have to 
			// replace the data in the archive table, not 
			// insert a new record
			//
			$replace = 1;
		}
		else
		{
			$tstamp = time();
		};

		// calculate the full path to archive file
		$fname = $this->fullpath . "/$tstamp";
		// and create the contents for the file
		if ($ser_content)
		{
			$ser = aw_serialize($args["ser_content"],SERIALIZE_XML);
		}
		else
		{
			$ser = $args["content"];
		};

		// and finally store it to the database
		$this->put_file(array(
			"file" => $fname,
			"content" => $ser,
		));

		// update the revision information in the object metadata
		$meta = $this->obj_get_meta(array("oid" => $oid));
		$arc = $meta["archive"];
		$arc[$tstamp] = array(
			"timestamp" => $tstamp,
			"uid" => aw_global_get("uid"),
			"name" => $name,
			"comment" => $comment,
		);

		$this->obj_set_meta(array(
			"oid" => $oid,
			"meta" => array("archive" => $arc),
		));

		// store information for later indexing and searching
		if (is_array($data))
		{
			// siia salvestame vastavate väljade nimed ja sisu sisu
			foreach($data as $key => $val)
			{
				$raw_value = strip_tags($val);
				$this->quote($raw_value);
				if ($replace)
				{
					$q = "UPDATE archive SET
						name = '$key'
						contents = '$raw_value'
						WHERE oid = '$oid' AND version = '$tstamp'";
				}
				else
				{
					$q = "INSERT INTO archive (oid,version,name,contents,class_id)
						VALUES ('$oid','$tstamp','$key','$raw_value','$class_id')";
				};
				$this->db_query($q);
			};
		};
				

		// we return the timestamp
		return $tstamp;
	}

	////
	// !Listib arhiivi sisu
	function get($args = array())
	{
		$this->_calc_path($args);
		$res = array();
		if ($dir = @opendir($this->fullpath)) 
		{
			while ($file = readdir($dir)) 
			{
				$full = $this->fullpath . "/$file";
				if (is_file($full))
				{
					$res[$file] = stat($full);
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
		return aw_unserialize($retval);
	}

	////
	// !Checks whether a given archive exists
	function exists($args = array())
	{
		$this->_calc_path($args);
		// more checks are needed
		$retval = is_dir($this->fullpath);
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
