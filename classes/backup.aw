<?php

class backup extends aw_template
{
	function backup()
	{
		$this->init("backup");
	}

	function orb_backup($arr)
	{
		extract($arr);
		$this->read_template("backup.tpl");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_backup", array()),
			"folder" => $this->get_cval("backup::folder")
		));
		return $this->parse();
	}

	function submit_backup($arr)
	{
		extract($arr);
		classload("config");
		$cf = new config;
		$cf->set_simple_config("backup::folder", $folder);

		set_time_limit(0);
		// ookay. first, the database dump.
		$tmpnam = aw_ini_get("server.tmpdir")."/".$this->gen_uniq_id();
		mkdir($tmpnam,0777);
		
		$cmd = aw_ini_get("server.mysqldump_path")." --add-drop-table --user=".aw_ini_get("db.user")." --host=".aw_ini_get("db.host")." --password=".aw_ini_get("db.pass")." --quick ".aw_ini_get("db.base")." > ".$tmpnam."/db_dump.sql";
		echo "Creating backup - this might take a long time<br>\n";
		flush();
		echo "creating database dump ...<br>\n";
		flush();
		$res = `$cmd`;

		// now, backup aw code dir.
		$cmd = aw_ini_get("server.tar_path")." -c -z -C ".$this->cfg["basedir"]." -f ".$tmpnam."/aw_code.tar.gz ".$this->cfg["basedir"];
		echo "backing up AW code ...<br>\n";
		flush();
		$res = `$cmd`;

		// backup site dir
		$cmd = aw_ini_get("server.tar_path")." -c -z -C ".$this->cfg["site_basedir"]." -f ".$tmpnam."/site_code.tar.gz ".$this->cfg["site_basedir"];
		echo "backing up site code ...<br>\n";
		flush();
		$res = `$cmd`;

		// now pack them all together
		$bn = date("Y")."-".date("m")."-".date("d");
		$cmd = aw_ini_get("server.tar_path")." -c -z -C $tmpnam -f ".$folder."/backup-".$bn.".tar.gz ".$tmpnam;
		echo "creating backup file ...<br>\n";
		flush();
		$res = `$cmd`;

		echo "deleting temporary files...<br>\n";
		flush();
		// now delete tmp files
		unlink($tmpnam."/db_dump.sql");
		unlink($tmpnam."/aw_code.tar.gz");
		unlink($tmpnam."/site_code.tar.gz");
		rmdir($tmpnam);
		echo "finished! <br><br>\n\n";
		echo "backup file created as ".$folder."/backup-".$bn.".tar.gz <br>\n";
		flush();
		die();
	}
}
?>