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

	////
	// !returns true if ip addressesd match. can compare either ip or name addresses, does masks like *.ee, 255.255.*
	// only the first parameter can be a mask, the other must be a complete address ip/name,
	// but you can mix ip/name types like this: match(foo.ee, 1.2.3.4) / match(*.foo.ee, 1.2.3.4) / match(1.2.*, www.ee)
	function match($a1, $a2)
	{
		if ($a1 == "*")
		{
			return true;
		}

		if (inet::is_ip($a1) && inet::is_ip($a2))
		{
			return ($a1 == $a2);
		}

		$a1e = explode(".", $a1);
		$a1_is_num = true;
		$a1_is_num_complete = true;
		$a1_is_string_complete = true;
		foreach($a1e as $pt)
		{
			if (!(is_number($pt) || $pt == "*"))
			{
				$a1_is_num = false;
			}
			if (!is_number($pt))
			{
				$a1_is_num_complete = false;
			}
			if ($pt == "*")
			{
				$a1_is_string_complete = false;
			}
		}

		$a2e = explode(".", $a2);
		$a2_is_num = true;
		$a2_is_num_complete = true;
		foreach($a2e as $pt)
		{
			if (!(is_number($pt) || $pt == "*"))
			{
				$a2_is_num = false;
			}
			if (!is_number($pt))
			{
				$a2_is_num_complete = false;
			}
		}

		if (!$a2_is_num_complete)
		{
			error::throw(array(
				"id" => ERR_IP,
				"msg" => "ipaddress::match($a1, $a2): the second parameter must be a complete ip address, it can not be a mask!"
			));
		}

		if ($a1_is_num && !$a1_is_num_complete && !$a2_is_num)
		{
			$a2 = inet::name2ip($a2);
		}
	
		if (!$a1_is_num && $a2_is_num)
		{
			list($a2) = inet::gethostbyaddr($a2);
		}

		if ($a1_is_string_complete)
		{
			return (trim($a1) == trim($a2));
		}

		// a1 is mask, a2 is the same type as a1
		$a1pts = explode(".", $a1);
		$a2pts = explode(".", $a1);

		$match = true;
		if (!$a1_is_num)
		{
			// if we are comparing name mask with name, reverse the parts, then ew can compare them
			// as ip address parts
			$a1pts = array_reverse($a1pts);
			$a2pts = array_reverse($a2pts);
		}

		foreach($a1pts as $idx => $pt)
		{
			if ($pt == "*")
			{
				continue;
			}
			if ($pt != $a2pts[$idx])
			{
				$match = false;
			}
		}

		return $match;
	}
}
?>
