<?php

class su_exec extends class_base
{
	function su_exec()
	{
		$this->init();
		$this->fc = array();
	}

	function open_file()
	{
		$this->fc = array();
	}

	function add_cmd($cmd)
	{
		$this->fc[] = $cmd;
	}

	function exec()
	{
		$fn = tempnam(aw_ini_get("server.tmpdir"), "aw_su_exec");
		echo "su_exec cmd fn = $fn <br>\n";
		chmod($fn, 0666);
		$fp = fopen($fn, "w");
		fwrite($fp, count($this->fc)."\n");

		$keys = $this->_make_keys();

		fwrite($fp, "Orig_key: ".$keys[0]."\n");
		fwrite($fp, "Crypt_key: ".$keys[1]."\n");

		foreach($this->fc as $cmd)
		{
			fwrite($fp, $cmd."\n");
		}
		fclose($fp);

		$cmdline = $this->cfg['basedir']."/scripts/install/su_exec/su_exec $fn";
		$res = `$cmdline`;
	
//		unlink($fn);

		return $res;
	}

	function _make_keys()
	{
		$nr = rand(1,100000000);
		$c_nr = ((($nr * 2) + 13) / 2);
		return array($nr, $c_nr);
	}
}
?>
