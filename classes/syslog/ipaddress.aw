<?php

/*

@classinfo syslog_type=ST_IPADDRESS

@groupinfo general caption=Üldine

@tableinfo ipaddresses index=id master_table=objects master_index=id

@default table=objects
@default group=general

@property addr type=textbox table=ipaddresses field=ip
@caption IP Aadress

*/

class ipaddress extends class_base
{
	function ipaddress()
	{
		$this->init(array(
			'tpldir' => 'syslog/IP Aadress',
			'clid' => CL_IPADDRESS
		));

		$this->do_check_tables();
	}

	////
	// !creates or returns the object that corresponds to the specified ip
	// parameters:
	//	ip - the address, required
	//	parent - where to create the object, optional, if not specified, the folrder is read from config
	function get_obj_from_ip($arr)
	{
		extract($arr);
		$id = $this->db_fetch_field("SELECT id FROM ipaddresses LEFT JOIN objects ON objects.oid = ipaddresses.id WHERE ip = '$ip' AND objects.status != 0", "id");
		if (!$id)
		{
			if (!$parent)
			{
				$parent = $this->get_cval("ipaddresses::default_folder");
				if (!$parent)
				{
					$parent = $this->cfg['rootmenu'];
				}
			}

			$id = $this->new_object(array(
				'parent' => $parent,
				'name' => $ip,
				'class_id' => CL_IPADDRESS
			));
			$this->db_query("INSERT INTO ipaddresses (id, ip) VALUES('$id','$ip')");
		}
		$ob = $this->get_object($id);
		// fake this. 
		$ob['meta']['ip'] = $ip;
		return $ob;
	}


	////
	// !returns the ip address associated with the object $oid
	function get_ip_from_obj($oid)
	{
		return $this->db_fetch_field("SELECT ip FROM ipaddresses LEFT JOIN objects ON objects.oid = ipaddresses.id WHERE id = '$oid' AND objects.status != 0","ip");
	}

	////
	// !checks whether the tables required for this object exist in the database and creates them if necessary
	function do_check_tables()
	{
		if (!aw_global_get("ipaddress::tables_checked"))
		{
			if (!$this->db_table_exists("ipaddresses"))
			{
				echo "creating table! <br />";
				$this->db_query("CREATE TABLE ipaddresses (id int primary key, ip varchar(30))");
				$this->db_query("ALTER TABLE ipaddresses ADD INDEX ip (ip)");
			}
			aw_global_set("ipaddress::tables_checked", true);
		}
	}
}
?>
