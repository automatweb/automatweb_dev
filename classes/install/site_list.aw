<?php

class site_list extends class_base
{
	function site_list()
	{
		$this->init("automatweb/site_list");
		$this->check_db();
	}

	function orb_list($arr)
	{
		extract($arr);
		$this->mk_path(0,"AW Saitide list");

		load_vcl('table');
		$t = new aw_table(array('prefix' => 'site_list'));
		$t->parse_xml_def($this->cfg['basedir'] . '/xml/generic_table.xml');

		$t->define_field(array(
			'name' => 'id',
			'caption' => 'ID',
			'sortable' => 1,
			'numeric' => 1,
		));
		$t->define_field(array(
			'name' => 'name',
			'caption' => 'Nimi',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'url',
			'caption' => 'URL',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'server_name',
			'caption' => 'Server',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'site_used',
			'caption' => 'Kasutusel',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'code_branch',
			'caption' => 'Koodi versioon',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'change',
			'caption' => 'Muuda',
		));

		$cnt = $cnt_used = 0; 
		$this->db_query("
			SELECT aw_site_list.*, aw_server_list.name as server_name 
			FROM aw_site_list
				LEFT JOIN aw_server_list ON aw_server_list.id = aw_site_list.server_id
			ORDER BY id
		");
		while ($row = $this->db_next())
		{
			$row['url'] = html::href(array(
				'url' => $row['url'],
				'caption' => $row['url']
			));
			$row["change"] = html::href(array(
				'url' => $this->mk_my_orb("change_site", array("id" => $row["id"])),
				'caption' => "Muuda"
			));
			if ($row["site_used"])
			{
				$cnt_used ++;
			}

			$row["site_used"] = $row["site_used"] == 1 ? "Jah" : "Ei";
			$t->define_data($row);
			$cnt++;
		}

		$t->set_default_sortby('id');
		$t->set_default_sorder('asc');
		$t->sort_by();
		
		$str = $t->draw();

		$str .= "Kokku $cnt saiti<br />\n";
		$str .= "Kasutusel $cnt_used saiti <br />\n";
		$str .= "Serverite kaupa: <br />\n";
		$str .= $this->_get_server_stats();
		$str .= "Koodiversioonide kaupa: <br />\n";
		$str .= $this->_get_cver_stats();

		return $str;
	}

	function _get_server_stats()
	{
		load_vcl('table');
		$t = new aw_table(array('prefix' => 'site_list_bs'));
		$t->parse_xml_def($this->cfg['basedir'] . '/xml/generic_table.xml');

		$t->define_field(array(
			'name' => 'name',
			'caption' => 'Server',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'cnt',
			'caption' => 'Mitu',
			'sortable' => 1,
			'numeric' => 1
		));

		$this->db_query("SELECT s.name as name, count(*) as cnt FROM aw_site_list l left join aw_server_list s on s.id = l.server_id WHERE site_used = 1 GROUP BY l.server_id order by cnt desc");
		while($row = $this->db_next())
		{
			$t->define_data($row);
		}
		$t->set_default_sortby("cnt");
		$t->set_default_sorder("desc");
		$t->sort_by();

		return $t->draw();
	}

	function _get_cver_stats()
	{
		load_vcl('table');
		$t = new aw_table(array('prefix' => 'site_list_cv'));
		$t->parse_xml_def($this->cfg['basedir'] . '/xml/generic_table.xml');

		$t->define_field(array(
			'name' => 'name',
			'caption' => 'Koodiversioon',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'cnt',
			'caption' => 'Mitu',
			'sortable' => 1,
			'numeric' => 1
		));

		$this->db_query("SELECT l.code_branch as name, count(*) as cnt FROM aw_site_list l WHERE site_used = 1 GROUP BY l.code_branch order by cnt desc");
		while($row = $this->db_next())
		{
			$t->define_data($row);
		}
		$t->set_default_sortby("cnt");
		$t->set_default_sorder("desc");
		$t->sort_by();

		return $t->draw();
	}

	function orb_server_list($arr)
	{
		extract($arr);
		$this->mk_path(0,"AW Serverite list");

		load_vcl('table');
		$t = new aw_table(array('prefix' => 'server_list'));
		$t->parse_xml_def($this->cfg['basedir'] . '/xml/generic_table.xml');

		$t->define_field(array(
			'name' => 'id',
			'caption' => 'ID',
			'sortable' => 1,
			'numeric' => 1,
		));

		$t->define_field(array(
			'name' => 'name',
			'caption' => 'Nimi',
			'sortable' => 1,
		));

		$t->define_field(array(
			'name' => 'ip',
			'caption' => 'IP aadress',
			'sortable' => 1,
		));

		$t->define_field(array(
			'name' => 'change',
			'caption' => 'Muuda',
		));

		$this->db_query("SELECT * FROM aw_server_list");
		while ($row = $this->db_next())
		{
			$row["change"] = html::href(array(
				'url' => $this->mk_my_orb("change_server", array("id" => $row["id"])),
				'caption' => "Muuda"
			));
			$t->define_data($row);
		}

		$t->set_default_sortby('id');
		$t->set_default_sorder('asc');

		$t->sort_by();

		return $t->draw();
	}

	function check_db()
	{
		if (!aw_global_get("site_list::tables_checked"))
		{
			if (!$this->db_table_exists("aw_site_list"))
			{
				$this->db_query("CREATE TABLE aw_site_list (
					id int primary key, 
					name varchar(255),
					url varchar(255), 
					server_id int, 
					ip varchar(255),
					site_used int default 1,
					code_branch varchar(255),
					data mediumtext
				)");
			}

			if (!$this->db_table_exists("aw_server_list"))
			{
				$this->db_query("CREATE TABLE aw_server_list (id int primary key, name varchar(255), ip varchar(255), comment mediumtext)");
			}
			aw_global_set("site_list::tables_checked", true);
		}
	}

	////
	// !adds or updates site
	// parameters:
	//   id - site id
	//   name - site name
	//   url - site url
	//   server_id - aw_server_list.id
	//   ip - site ip address
	//   site_used - boolean - whether the site is active
	//   code_branch - the code that the site runs
	//   data - random data
	//
	// if site_id is not specified, then a new unique id will be created and entered to the database
	function orb_update_site($arr)
	{
		extract($arr);

		if ($id)
		{
			$dat = $this->db_fetch_row("SELECT * FROM aw_site_list WHERE id = '$id'");
			if ($dat)
			{
				unset($arr['id']);
				$sets = join(",", map2("%s = '%s'", $arr));
				$q = "UPDATE aw_site_list SET $sets WHERE id = '$id'";
//				echo "updateq = $q <br />";
				$this->db_query($q);
			}
			else
			{
				$keys = join(",",array_keys($arr));
				$vals = join(",", map("'%s'",array_values($arr)));
				$q = "INSERT INTO aw_site_list($keys) VALUES($vals)";
//				echo "insert q = $q <br />";
				$this->db_query($q);
			}
		}
		else
		{
			// now. to find an unused id we select all the id's
			// from the db and then find the smallest number that is not in the list
			$ids = array();
			$this->db_query("SELECT id FROM aw_site_list");
			while ($row = $this->db_next())
			{
				$ids[$row["id"]] = $row["id"];
			}
	
			$id = 1;
			while ($ids[$id] == $id)
			{
				$id++;
			}

			$arr['id'] = $id;
			$keys = join(",",array_keys($arr));
			$vals = join(",", map("'%s'",array_values($arr)));
			$q = "INSERT INTO aw_site_list($keys) VALUES($vals)";
//			echo "insert q = $q <br />";
			$this->db_query($q);
		}
		return $id;
	}

	////
	// !adds or updates a server
	// parameters:
	//   id - server id
	//   name - server name
	//   ip - server ip address
	//   comment - user comment for site
	//
	// if site_id is not specified, then a new unique id will be created and entered to the database
	function orb_update_server($arr)
	{
		extract($arr);

		if ($id)
		{
			$dat = $this->db_fetch_row("SELECT * FROM aw_server_list WHERE id = '$id'");
			if ($dat)
			{
				unset($arr['id']);
				$sets = join(",", map2("%s = '%s'", $arr));
				$q = "UPDATE aw_server_list SET $sets WHERE id = '$id'";
//				echo "updateq = $q <br />";
				$this->db_query($q);
			}
			else
			{
				$keys = join(",",array_keys($arr));
				$vals = join(",", map("'%s'",array_values($arr)));
				$q = "INSERT INTO aw_server_list($keys) VALUES($vals)";
//				echo "insert q = $q <br />";
				$this->db_query($q);
			}
		}
		else
		{
			$id = $this->db_fetch_field("SELECT MAX(id) AS max FROM aw_server_list", "max")+1;
			$arr['id'] = $id;
			$keys = join(",",array_keys($arr));
			$vals = join(",", map("'%s'",array_values($arr)));
			$q = "INSERT INTO aw_server_list($keys) VALUES($vals)";
//			echo "insert q = $q <br />";
			$this->db_query($q);
		}
	}

	////
	// !returns a list of sites matching filter
	// params:
	//   server_id - filter by server id
	function orb_get_site_list($arr)
	{
		extract($arr);
		$ret = array();
		$filt = array();
		if ($server_id)
		{
			$filt[] = "server_id = '$server_id'";
		}
		$fs = join(" AND ", $filt);
		if ($fs != "")
		{
			$fs = " WHERE $fs ";
		}
		$q = "SELECT * FROM aw_site_list".$fs;
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$ret[$row['id']] = $row;
		}
		return $ret;
	}

	function server_picker()
	{
		$res = array();
		$ret = $this->orb_get_server_list(array());
		foreach($ret as $id => $dat)
		{
			$res[$id] = $dat["name"];
		}
		return $res;
	}

	function orb_get_server_list($arr)
	{
		extract($arr);
		$ret = array();
		$this->db_query("SELECT * FROM aw_server_list");
		while ($row = $this->db_next())
		{
			$ret[$row['id']] = $row;
		}
		return $ret;
	}

	function init_list($arr)
	{
		extract($arr);
		// the way this works is, it reads from the fg created table and sends the data to register.aw.com via xmlrpc

//		aw_global_set("xmlrpc_dbg",1);
		// first, the servers
		$srvc = 0;
		$this->db_query("
			SELECT 
				id AS id,
				ev_37693 AS name,
				ev_37695 AS ip,
				ev_37697 AS comment
			FROM form_37691_entries
			LEFT JOIN objects ON objects.oid = form_37691_entries.id
			WHERE objects.status != 0
		");
		while ($row = $this->db_next())
		{
			// send it via xmlrpc to site list server
			echo "id = $row[id] name = $row[name] ip = $row[ip] comment = $row[comment] <br />";
			unset($row['rec']);
			$this->do_orb_method_call(array(
				"class" => "site_list", 
				"action" => "update_server", 
//				"method" => "xmlrpc",
//				"server" => "register.automatweb.com",
				"params" => $row
			));
			$srvc++;
		}

		$sic = 0;
		$this->db_query("
			SELECT 
				ev_37700 AS id,
				ev_37686 AS name,
				ev_37688 AS url,
				form_37691_entries.id AS server_id,
				el_41423 AS site_used,
				ev_48054 AS code_branch
			FROM form_37683_entries
			LEFT JOIN objects ON objects.oid = form_37683_entries.id
			LEFT JOIN form_37691_entries ON form_37691_entries.el_37693 = form_37683_entries.ev_37690
			WHERE objects.status != 0
		");
		while ($row = $this->db_next())
		{
			// send it via xmlrpc to site list server
			echo "id = $row[id] name = $row[name] url = $row[url] server_id = $row[server_id] site_used = $row[site_used] code_branch = $row[code_branch]  <br />";
			unset($row['rec']);
			$this->do_orb_method_call(array(
				"class" => "site_list", 
				"action" => "update_site", 
//				"method" => "xmlrpc",
//				"server" => "register.automatweb.com",
				"params" => $row
			));
			$sic++;
		}
		echo "sent $srvc serverit ja $sic saiti <br />";
	}

	////
	// !returns the id of the server that is marked as serving on ip address $ip
	function get_server_id_by_ip($arr)
	{
		extract($arr);
		return $this->db_fetch_field("SELECT id FROM aw_server_list WHERE ip LIKE '%$ip%'","id");
	}

	////
	// !returns the id of the site that has the url $url
	function get_site_id_by_url($arr)
	{
		extract($arr);
		return $this->db_fetch_field("SELECT id FROM aw_site_list WHERE url LIKE '%$url%'","id");
	}

	////
	// !returns all data that we have on the site
	// parameters:
	//   site_id - the id of the site whose data is returned
	function get_site_data($arr)
	{
		extract($arr);
		return $this->db_fetch_row("SELECT * FROM aw_site_list WHERE id = '$site_id'");
	}

	////
	// !returns all data that we have on the server
	// parameters:
	//   server_id - the id of the server whose data is returned
	function get_server_data($arr)
	{
		extract($arr);
		return $this->db_fetch_row("SELECT * FROM aw_server_list WHERE id = '$server_id'");
	}

	function change_site($arr)
	{
		extract($arr);
		$this->mk_path(0, html::href(array(
			'url' => $this->mk_my_orb("site_list"),
			'caption' => "AW Saitide list"
		))." / Muuda saiti ");
		$this->read_template("change.tpl");
		$sd = $this->get_site_data(array("site_id" => $id));
		$this->vars($sd);
		$this->vars(array(
			"server_id" => $this->picker($sd["server_id"], $this->server_picker()),
			"site_used" => checked($sd["site_used"]),
			"reforb" => $this->mk_reforb("submit_change_site", array("id" => $id))
		));
		return $this->parse();
	}

	function submit_change_site($arr)
	{
		extract($arr);

		$this->db_query("UPDATE aw_site_list SET name = '$name', url = '$url', server_id = '$server_id', site_used = '$site_used', code_branch = '$code_branch' where id = '$id'");

		return $this->mk_my_orb("change_site", array("id" => $id));
	}

	function change_server($arr)
	{
		extract($arr);
		$this->mk_path(0, html::href(array(
			'url' => $this->mk_my_orb("server_list"),
			'caption' => "AW Serverite list"
		))." / Muuda serverit ");
		$this->read_template("change_server.tpl");
		$sd = $this->get_server_data(array("server_id" => $id));
		$this->vars($sd);
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_change_server", array("id" => $id))
		));
		return $this->parse();
	}

	function submit_change_server($arr)
	{
		extract($arr);

		$this->db_query("UPDATE aw_server_list SET name = '$name', ip = '$ip', comment = '$comment' where id = '$id'");

		return $this->mk_my_orb("change_server", array("id" => $id));
	}
}
?>
