<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/keywords.aw,v 2.3 2001/05/18 16:29:55 duke Exp $
// keywords.aw - dokumentide võtmesõnad
class keywords extends aw_template {
	function keywords($args = array())
	{
		$this->db_init();
		$this->tpl_init("automatweb/keywords");
	}


	////
	// !Tagastab mingi objekti juurde lisatud võtmesõnad
	// argumendid:
	// oid (int) - objekti id
	function get_keywords($args = array())
	{
		extract($args);
		$q = "SELECT * FROM keywords2objects WHERE oid = '$oid'";
		$this->db_query($q);
		$idlist = array();
		$result = "";
		while($row = $this->db_next())
		{
			$idlist[] = $row["keyword_id"];
		};
		
		if (sizeof($idlist) > 0)
		{
			$ids = join(",",$idlist);
			$q = sprintf("SELECT keyword FROM keywords WHERE id IN ('%s')",$ids);
			$this->db_query($q);
			$resarray = array();
			while($row = $this->db_next())
			{
				$resarray[] = $row["keyword"];
			};
			$result = join(",",$resarray);
		};
		return $result;
	}

	////
	// !Tagastab koik registreeritud votmesonad
	// argumendid: pole
	function get_all_keywords($args = array())
	{
		$q = "SELECT keyword FROM keywords ORDER BY keyword";
		$this->db_query($q);
		$resarray = array();
		while($row = $this->db_next())
		{
			$resarray[] = $row["keyword"];
		};
		return $resarray;
	}
			
			
	////
	// !Seda kutsutakse dokude salvestamise juurest välja.
	// Uuendab mingi dokuga (objektiga) seotud keywordide nimekirja
	// argumendid:
	// keywords (string) - komadega eraldatud märksõnade nimekiri
	// oid (int) - objekti (dokumendi id) millega märksõnad siduda
	function update_keywords($args = array())
	{
		extract($args);
		$this->quote($keywords);
		$keywordlist = explode(",",$keywords);
		$klist = array();
		$ids = array();
		// vaja leida koigi votmesõnade ID-d. Kui ei ole, siis tekitame uue
		foreach($keywordlist as $val)
		{
			$keyword = trim($val);
			$klist[] = $keyword;
			$arg = join(",",$klist);
		};
		$q = sprintf("SELECT * FROM keywords WHERE keyword IN ('%s')",$arg);
		$this->db_query($q);	
		while($row = $this->db_next())
		{
			$ids[$row["keyword"]] = $row["id"];
		};
		
		// teeme kindlaks koik votmesonad, millel polnud ID-d (uued)
		// loome ka uue listi votmesona jaoks
		classload("lists");
		$lists = new lists();

		foreach($klist as $val)
		{
			if (!$ids[$val])
			{
				// well, it looks almost like mysql_insert_id does not work always, so we screw around a little
				$q = "SELECT MAX(id) AS id FROM keywords";
				$this->db_query($q);
				$row = $this->db_next();
				$newid = $row["id"];
				$newid++;
				$this->save_handle();
				$list_id = $lists->create_list(array(
								"parent" => KEYWORD_LISTS,
								"name" => $val,
								"comment" => "automaagiliselt loodud list",
							));
				$this->restore_handle();
				$q = "INSERT INTO keywords (id,list_id,keyword) VALUES ('$newid','$list_id','$val')";
				$this->db_query($q);
				$ids[$val] = $newid;

			};
		};

		// nüüd peaksid koik votmesonad baasis kajastatud olema

		// votame vanad seosed maha
		$q = "DELETE FROM keywords2objects WHERE oid = '$oid'";
		$this->db_query($q);

		// ja loome uued

		foreach($klist as $val)
		{
			$q = sprintf("INSERT INTO keywords2objects (oid,keyword_id) VALUES ('%d','%s')",$oid,$ids[$val]);
			$this->db_query($q);
		}

		// and we should be done now
	}
		

		
		
		
};
?>
