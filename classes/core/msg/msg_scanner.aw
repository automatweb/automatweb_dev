<?php

// this class generates the message dispatch tables from the class files

class msg_scanner extends class_base
{
	function msg_scanner()
	{
		aw_global_set("no_db_connection", true);
		$this->init(array(
			"no_db" => 1
		));
		$this->folder = $this->cfg["basedir"]."/xml/msgmaps";
	}

	function scan()
	{
		// generate list of all class files in aw
		$parser = get_instance("parser");
		$files = array();
		$parser->_get_class_list(&$files, $this->cfg["classdir"]);

		// scan them for all dispatched / recieved messages
		list($messages, $recievers) = $this->_scan_files($files);

		// check the maps for validity
		$this->_check_message_maps($messages, $recievers);

		// generate one xml file for each message that lists the recievers of that message
		$this->_save_message_maps($messages, $recievers);
	}

	function _scan_files($files)
	{
		$messages = array();
		$recievers = array();
		foreach($files as $file)
		{
			$blen = strlen($this->cfg["basedir"]."/classes")+1;
			$class = substr($file, $blen, strlen($file) - (strlen(".".$this->cfg["ext"])+$blen));

			$fc = $this->get_file(array("file" => $file));
			if (preg_match_all("/EMIT_MESSAGE\((.*)\)/U",$fc, $mt, PREG_SET_ORDER))
			{
				foreach($mt as $m)
				{
					$messages[] = trim($m[1]);
				}
			}

			if (preg_match_all("/HANDLE_MESSAGE\((.*),(.*)\)/U",$fc, $mt, PREG_SET_ORDER))
			{
				foreach($mt as $m)
				{
					if (isset($recievers[trim($m[1])][$class]))
					{
						die("ERROR: function ".$recievers[trim($m[1])][$class]." already defined as message handler\n       for message $m[1], can not define several recievers\n       for one message in the same class!\n\n");
					}
					$recievers[trim($m[1])][$class] = trim($m[2]);
				}
			}
		}
		return array($messages, $recievers);
	}

	function _check_message_maps($messages, $recievers)
	{
		foreach($recievers as $msg => $cldat)
		{
			if (!in_array($msg, $messages))
			{
				$clstr = join(",",array_keys($cldat));
				if (count(array_keys($cldat)) > 1)
				{
					$mul = "es:";
				}
				echo "ERROR: message $msg is not defined, but recieved by class$mul $clstr!\n\n";
				die();
			}

			foreach($cldat as $class => $handler)
			{
				$inst = get_instance($class);
				if (!method_exists($inst, $handler))
				{
					echo "ERROR: class $class defines function $handler as message handler for message $msg,\n       but the function does not exist in that class!\n\n";
					die();
				}
			}
		}
	}

	function _save_message_maps($messages, $recievers)
	{
		$this->_delete_old_maps();

		foreach($messages as $msg)
		{
			// find all recievers for this message
			$m_recvs = new aw_array($recievers[$msg]);
			$r = array();
			foreach($m_recvs->get() as $class => $func)
			{
				$r[] = array("class" => $class, "func" => $func);
			}

			// serialize
			$xml = aw_serialize($r, SERIALIZE_XML);
			// write file
			$file = $this->folder."/".$msg.".xml";
			$this->put_file(array(
				"file" => $file,
				"content" => $xml
			));
			echo "\t.. generated $file\n";
		}
	}

	function _delete_old_maps()
	{
		$fs = array();
		if (($dir = @opendir($this->folder)))
		{
			while (($file = readdir($dir)) !== false)
			{
				$fn = $this->folder."/".$file;

				if (is_file($fn) && substr($file,strlen($file)-4) == ".xml")
				{
					if (!is_writable($fn))
					{
						die("ERROR: no write access to file $fn!\n\n");
					}

					$fs[] = $fn;
				}
			}
		}
		else
		{
			die("ERROR: folder $folder where message maps are stored, does not exist!\n\n");
		}

		foreach($fs as $fn)
		{
			if (!@unlink($fn))
			{
				die("ERROR: no write access to file $fn!\n\n");
			}
		}
	}
}
?>
