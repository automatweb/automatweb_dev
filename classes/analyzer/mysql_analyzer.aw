<?php
class mysql_analyzer extends aw_template
{
	function mysql_analyzer()
	{
		$this->init("");
		$this->logfile = "/www/log/mysql.log";
	}

	function get_status($init = false)
	{
		$mode = ($init) ? "w" : "a";
		$fp = fopen($this->logfile,$mode);
		flock($fp, LOCK_EX);
		$q = "SHOW STATUS";
		$this->db_query($q);
		if ($init)
		{
			$els[] = "tm";
		}
		else
		{
			$els[] = time();
		};
		while($row = $this->db_next())
		{
			$key = ($init) ? "Variable_name" : "Value";
			$els[] = $row[$key];
		};
		$line = join(";",$els);
		fwrite($fp,$line . "\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}
};
?>
