<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/mysql.aw,v 2.3 2001/06/28 18:04:18 kristo Exp $
// mysql.aw - MySQL draiver
include("$classdir/root.$ext");
class db_connector extends root {
	var $dbh; #database handle
	var $db_base; #name of the database
	var $qID; # query ID
	var $errmsg; # where we keep our error messages
	var $rec_count;
	function db_init() {
		global $db_core;
		$this->dbh = $db_core->dbh;
		$this->db_base = $db_core->db_base;
		$this->watch = 1;
	}
		
	function db_connect($server,$base,$username,$password) {
		$this->dbh = mysql_pconnect($server,$username,$password);
		if (!$this->dbh) {
			echo "Can't connect to database";
			exit;
		};
		@mysql_select_db($base,$this->dbh);
		$this->db_base = $base;
	}

	function db_query($qtext,$errors = true) 
	{
		global $awt;
		global $qcount,$qarr;
		global $SITE_ID;
		$qcount++; $qarr[] = $qtext;
		if (is_object($awt)) 
		{
			$awt->start("querys");
		};
		$this->qID = @mysql_query($qtext, $this->dbh);
		if (!$this->qID ) 
		{
			if (!$errors)
			{
				return false;
			}
			echo "Vigane päring";
			// lühendame päringu. Ntx failide lisamisel voib paring olla yle mega pikk
			// ja selle ekraanile pritsimine ei anna mitte midagi.

			if (strlen($qtext) > 5000)
			{
				$qtext = substr($qtext,0,5000) . "....(truncated)";
			};

			echo $qtext . "\n";
			echo "<br>\n";
			echo mysql_error();
		} 
		else 
		{
			$this->num_rows = @mysql_num_rows($this->qID);
			$this->num_fields = @mysql_num_fields($this->qID);
		};
		$this->rec_count = 0;
		if (is_object($awt)) 
		{
			$awt->stop("querys");
		};
		return true;
	}

	function db_next($deq = true) 
	{
		# this function cannot be called before a query is made
		global $awt;
		if (is_object($awt)) 
		{
			$awt->start("db_next");
			$awt->count("db_next");
		};
		$res = @mysql_fetch_array($this->qID);
		if ($res) 
		{
			$this->rec_count++;
			if ($deq)
			{
				$this->dequote($res);
			}
			$res["rec"] = $this->rec_count;
		};
		if (is_object($awt)) 
		{
			$awt->stop("db_next");
		};
		return $res;
	}

	function db_list_tables()
	{
		$this->tID = mysql_list_tables($this->db_base);
		$this->tablecount = mysql_num_rows($this->tID);
	}

	function db_next_table()
	{
		static $cnt = 0;
		$res = ($cnt < $this->tablecount) ? mysql_tablename($this->tID,$cnt) : false;
		$cnt++;
		return $res;
	}
	
	function db_get_fields()
	{
		$retval = array();
		print $this->num_fields;
		for ($i = 0; $i < $this->num_fields; $i++)
		{
			$retval[] = mysql_fetch_field($this->qID);
		}
		return $retval;
	}

	function db_last_insert_id() {
		$res = mysql_insert_id();
		return $res;
	}

	function db_fetch_row() {
		return $this->db_next();
	}
	
	# seda voib kasutada, kui on vaja teada saada mingit kindlat välja
	# a 'la cval tabelist config
	# $cval = db_fetch_field("SELECT cval FROM config WHERE ckey = '$ckey'","cval")
	function db_fetch_field($qtext,$field) {
		$this->db_query($qtext);
		$row = $this->db_fetch_row();
		$val = $row[$field];
		$this->dequote($val);
		return $val;
	}

	# need 2 funktsiooni oskavad käituda nii array-de kui ka stringidega
	function quote(&$arr) {
		if (is_array($arr)) {
			while(list($k,$v) = each($arr)) {
				if (is_array($arr[$k])) {
					// do nothing
				} else {
					$arr[$k] = addslashes($arr[$k]);
				};
			};
			reset($arr);
		} else {
			$arr = addslashes($arr);
			return $arr;
		};
	}

	function dequote(&$arr) 
	{
		if (is_array($arr)) 
		{
			while(list($k,$v) = each($arr)) 
			{
				if (is_array($arr[$k])) 
				{
					$this->dequote(&$arr[$k]);
				} 
				else 
				{
					$arr[$k] = stripslashes($arr[$k]);
				};
			};
			reset($arr);
		} 
		else 
		{
			$arr = stripslashes($arr);
		};
	}

	function num_rows()
	{
		return mysql_num_rows($this->qID);
	}
};
?>
