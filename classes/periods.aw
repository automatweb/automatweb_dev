<?php
// a$Header: /home/cvs/automatweb_dev/classes/Attic/periods.aw,v 2.8 2002/02/18 13:45:32 kristo Exp $
lc_load("periods");	
class db_periods extends aw_template 
{
	function db_periods($oid) 
	{
		$this->db_init();
		$this->tpl_init("automatweb/periods");
		$this->oid = $oid;
		lc_load("definition");
		global $lc_periods;
		if (is_array($lc_periods))
		{
			$this->vars($lc_periods);
		}
	}
	
	function clist($arc_only = -1) 
	{
		$oid = $this->oid;
		$sufix = ($arc_only > -1) ? " AND archived = 1 " : "";
		$ochain = $this->get_object_chain($this->oid);
		$valid_period = 0;
		if (is_array($ochain)) 
		{
			// hm, but we must make sure we go from bottom to top always
			$parent = $this->oid;
			while ($parent > 1)
			{
				if ($ochain[$parent]["active_period"])
				{
					$valid_period = $parent;
					break;
				}
				$parent = $ochain[$parent]["parent"];
			}
		}
		$q = "SELECT * FROM periods
			WHERE oid = '$valid_period' $sufix ORDER BY jrk DESC";
		$this->oid = $valid_period;
		$this->db_query($q);
	}

	function get_next($id,$oid) 
	{
		$q = "SELECT * FROM periods WHERE oid = '$oid' ORDER BY jrk";
		$this->db_query($q);
		$select = 0;
		while($row = $this->db_next()) {
			if ($select == 1) {
				$next = $row["id"];
				$select = 0;
			};
			if ($row["id"] == $id) {
				$select = 1;
			};
		};
		return $next;
	}
	
	function get_prev($id,$oid) {
		$q = "SELECT * FROM periods WHERE oid = '$oid' ORDER BY jrk DESC";
		$this->db_query($q);
		$select = 0;
		while($row = $this->db_next()) {
			if ($select == 1) {
				$prev = $row["id"];
				$select = 0;
			};
			if ($row["id"] == $id) {
				$select = 1;
			};
		};
		return $prev;
	}

	function add($archived,$description) 
	{
		$aflag = ($archived == "on") ? 1 : 0;
		$t = time();
		$oid = $this->oid;
		$q = "INSERT INTO periods (archived,description,created,oid)
			VALUES('$aflag','$description','$t','$oid')";
		$this->db_query($q);
	}

	function get($id) 
	{
		$q = "SELECT * FROM periods WHERE id = '$id'";
		$this->db_query($q);
		return $this->db_fetch_row();
	}

	function savestatus($data) 
	{
		// checkboxid, mis näitavad perioodi arhiveeritust
		$arc_flags = $data["arc"];
		// eelmised väärtused
		$old_arc_flags = $data["oldarc"];

		// salvestame flagid, mis naitavad perioodide arhiveeritust
		while(list($k,$v) = each($old_arc_flags)) {
			// teeme kindlaks, kas staatust on vaja muuta
			$newstatus = ($arc_flags[$k] == "on") ? 1 : 0;
			if ($newstatus != $v) {
				print "#";
				$q = "UPDATE periods SET archived = '$newstatus'
					WHERE id = '$k'";
				$this->db_query($q);
			};
		};

		if ($data["oldactiveperiod"] != $data["activeperiod"]) {
			$this->activate_period($data["activeperiod"],$this->oid);
			$this->_log("period",sprintf(LC_PERIODS_ACTIVATED_PERIOD,$data[activeperiod]));
			print "#";
		};

		$oldjrk = $data["oldjrk"];
		$jrk = $data["jrk"];

		// salvestame jarjekorranumbrid
		while(list($k,$v) = each($oldjrk)) 
		{
			print "#";
			$newjrk = $jrk[$k];
			if ($v != $newjrk) 
			{
				$q = "UPDATE periods SET jrk = '$newjrk' WHERE id = '$k'";
				$this->db_query($q);
			};
		};
	}

	function toggle_arc_flag($id) 
	{
		$old = $this->get($id);
		$new = ($old["archived"] == 1) ? "0" : "1";
		$q = "UPDATE periods SET archived = '$new'
			WHERE id = '$id'";
		$this->db_query($q);
	}

	function activate_period($id,$oid) 
	{
		$q = "UPDATE menu SET active_period = '$id' WHERE id = '$oid'";
		$this->db_query($q);
		$this->flush_cache();
	}

	function get_active_period($oid = 0) 
	{
		if (!$oid) 
		{
			$oid = $this->oid;
		};
		$q = "SELECT active_period FROM menu WHERE id = "  . $oid;
		$this->db_query($q);
		$row = $this->db_fetch_row();
		return $row["active_period"];
	}

	// ee, v6ib ju nii olla, et sellel sektsioonil pole aktiivset perioodi m22ratud, aga tema parentil on, niiet tuleb see otsida...
	function rec_get_active_period($oid = -1) 
	{
		$oid = $oid == -1 ? $this->oid : $oid;
		do {
			$q = "SELECT menu.active_period as active_period,objects.parent as parent FROM menu left join objects on objects.oid = menu.id WHERE id = "  . $oid;
			$this->db_query($q);
			$row = $this->db_fetch_row();
			$oid = $row["parent"];
		} while (!$row["active_period"] && $row["parent"] > 1);

		return $row["active_period"];
	}

	// see funktsioon tagastab kõigi eksisteerivate perioodide nimekirja
	// kujul <option val=$id>$description</option>
	// $active muutujaga saab ette anda selle, milline periood peaks olema aktiivne
	// kui $active == 0, siis on selected see option, mis parajasti aktiivne on
	// kui $active == 'somethingelse', siis on selectitud vastava id-ga element
	function period_olist($active = 0) 
	{
		if ($active == 0) 
		{
			$active = $this->get_cval("activeperiod");
		};
		$this->clist();
		$elements = array();
		while($row = $this->db_next()) 
		{
			$elements[$row["id"]] = $row["description"];
		};
		return $this->option_list($active,$elements);
	}

	// @desc: sama mis period_olist, aga multiple select boxi jaox
	function period_mlist($active) 
	{
		if ($active == 0) 
		{
			$active = $this->get_cval("activeperiod");
		};
		$this->clist();
		$elements = array();
		while($row = $this->db_next()) 
		{
			$elements[$row["id"]] = $row["description"];
		};
		return $this->multiple_option_list($active,$elements);
	}

	function save($data) 
	{
		$this->quote($data);
		extract($data);
		$q = "UPDATE periods
			SET description = '$description',
			    archived = '$archived'
			WHERE id = '$id'";
		$this->db_query($q);
	}

};
?>
