<?php

class site_list extends class_base
{
	function site_list()
	{
		$this->init("automatweb/site_list");
	}

	/**  
		
		@attrib name=site_list params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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
			'name' => 'last_update',
			'caption' => 'Viimane uuendus',
			'sortable' => 1,
			'type' => 'time',
			'numberic' => 1,
			'format' => "d.m.Y / H:i"
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

	function get_site_list()
	{
		$ret = array();

		$this->db_query("
			SELECT aw_site_list.*, aw_server_list.name as server_name 
			FROM aw_site_list
				LEFT JOIN aw_server_list ON aw_server_list.id = aw_site_list.server_id
			ORDER BY id
		");
		while ($row = $this->db_next())
		{
			$ret[$row["id"]] = $row;
		}

		return $ret;
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

	/**  
		
		@attrib name=server_list params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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

	/** adds or updates site 
		
		@attrib name=update_site params=name all_args="1" default="0"
		
		
		@returns
		
		
		@comment
		parameters:
		id - site id
		name - site name
		url - site url
		server_id - aw_server_list.id
		ip - site ip address
		site_used - boolean - whether the site is active
		code_branch - the code that the site runs
		data - random data
		
		if site_id is not specified, then a new unique id will be created and entered to the database

	**/
	function orb_update_site($arr)
	{
		extract($arr);

		if ($id)
		{
			$dat = $this->db_fetch_row("SELECT * FROM aw_site_list WHERE id = '$id'");
			if ($dat)
			{
				unset($arr['id']);
				$sets = join(",", map2("%s = '%s'", $arr["fields"]));
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

	/** adds or updates a server 
		
		@attrib name=update_server params=name all_args="1" default="0"
		
		
		@returns
		
		
		@comment
		parameters:
		id - server id
		name - server name
		ip - server ip address
		comment - user comment for site
		
		if site_id is not specified, then a new unique id will be created and entered to the database

	**/
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

	/** returns a list of sites matching filter 
		
		@attrib name=get_site_list params=name default="0"
		
		@param server_id optional
		
		@returns
		
		
		@comment
		params:
		server_id - filter by server id

	**/
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

	/** returns the id of the server that is marked as serving on ip address $ip 
		
		@attrib name=get_server_id_by_ip params=name all_args="1" default="0"
		
		@param ip required
		
		@returns
		
		
		@comment

	**/
	function get_server_id_by_ip($arr)
	{
		extract($arr);
		return $this->db_fetch_field("SELECT id FROM aw_server_list WHERE ip LIKE '%$ip%'","id");
	}

	/** returns the id of the site that has the url $url 
		
		@attrib name=get_site_id_by_url params=name all_args="1" default="0"
		
		@param url required
		
		@returns
		
		
		@comment

	**/
	function get_site_id_by_url($arr)
	{
		extract($arr);
		return $this->db_fetch_field("SELECT id FROM aw_site_list WHERE url LIKE '$url'","id");
	}

	/** returns all data that we have on the site 
		
		@attrib name=get_site_data params=name default="0"
		
		@param site_id required
		
		@returns
		
		
		@comment
		parameters:
		site_id - the id of the site whose data is returned

	**/
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

	/**  
		
		@attrib name=change_site params=name default="0"
		
		@param id required type=int
		
		@returns
		
		
		@comment

	**/
	function change_site($arr)
	{
		extract($arr);


		#$htmlc = get_instance("cfg/htmlclient",array("template" => "webform.tpl"));
		$htmlc = get_instance("cfg/htmlclient");
		$htmlc->start_output();
		
		$sd = $this->get_site_data(array("site_id" => $id));

		$htmlc->add_property(array(
			"name" => "id",
			"type" => "text",
			"caption" => "ID",
			"value" => $id,
		));
		
		$htmlc->add_property(array(
			"name" => "name",
			"type" => "textbox",
			"caption" => "Nimi",
			"size" => 50,
			"value" => $sd["name"],
		));
		
		$htmlc->add_property(array(
			"name" => "url",
			"type" => "textbox",
			"caption" => "URL",
			"size" => 50,
			"value" => $sd["url"],
		));
		
		$htmlc->add_property(array(
			"name" => "server_id",
			"type" => "select",
			"caption" => "Server",
			"value" => $sd["server_id"],
			"options" => $this->server_picker(),
		));
		
		$htmlc->add_property(array(
			"name" => "site_used",
			"type" => "checkbox",
			"caption" => "Used?",
			"value" => 1,
			"ch_value" => $sd["site_used"],
		));
		
		$htmlc->add_property(array(
			"name" => "code_branch",
			"type" => "textbox",
			"caption" => "Code branch",
			"value" => $sd["code_branch"],
		));
		
		$htmlc->add_property(array(
			"name" => "basedir",
			"type" => "text",
			"caption" => "Basedir",
			"value" => $sd["basedir"],
		));
		
		$htmlc->add_property(array(
			"name" => "updater_uid",
			"type" => "text",
			"caption" => "Updater",
			"value" => $sd["updater_uid"],
		));
		
		$htmlc->add_property(array(
			"name" => "last_update",
			"type" => "text",
			"caption" => "Last update",
			"value" => !empty($sd["last_update"]) ? date("d.m.Y / H:i",$sd["last_update"]) : "n/a",
		));

		$htmlc->finish_output(array("data" => array(
				"class" => get_class($this),
				"action" => "submit_change_site",
				"id" => $id,
			),
		));

		return $htmlc->get_result(array(
			"form_only" => 1
		));

	}

	/**  
		
		@attrib name=submit_change_site params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_change_site($arr)
	{
		extract($arr);

		arr($arr);

		$this->db_query("UPDATE aw_site_list SET name = '$name', url = '$url', server_id = '$server_id', site_used = '$site_used', code_branch = '$code_branch' where id = '$id'");

		return $this->mk_my_orb("change_site", array("id" => $id));
	}

	/**  
		
		@attrib name=change_server params=name default="0"
		
		@param id required type=int
		
		@returns
		
		
		@comment

	**/
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

	/**  
		
		@attrib name=submit_change_server params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_change_server($arr)
	{
		extract($arr);

		$this->db_query("UPDATE aw_server_list SET name = '$name', ip = '$ip', comment = '$comment' where id = '$id'");

		return $this->mk_my_orb("change_server", array("id" => $id));
	}

	/** returns data about the current site 

		@attrib name=get_site_info params=name

		@comment
			
			returns an array with the current site's data:

				server => ip of the server it is running on
				code_path => the path of the aw installation it is running on
				site_path => the path of the site installation it is running on
				url => the url the site is accessible from
	**/
	function get_site_info($arr)
	{
		list($servname) = explode("/",str_replace("http://", "", str_replace("https://", "", aw_ini_get("baseurl"))));
		
		$servip = inet::name2ip($servname);
		return array(
			"server" => $servip,
			"code_path" => aw_ini_get("basedir"),
			"site_path" => aw_ini_get("site_basedir"),
			"url" => aw_ini_get("baseurl")
		);
	}

	/**

		@attrib name=fetch_site_data
	**/
	function fetch_site_data($arr)
	{
		foreach($this->get_site_list() as $sid => $sd)
		{
			if ($sd["site_used"] != 1)
			{
				continue;
			}
			list($servname) = explode("/",str_replace("http://", "", str_replace("https://", "", $sd["url"])));
			$res = $this->do_orb_method_call(array(
				"class" => "site_list",
				"action" => "get_site_info",
				"server" => $servname,
				"method" => "xmlrpc",
				"no_errors" => 1
			));
			echo "server = $servname , res = ".(is_array($res) ? "yeah!" : "mkm")." <br>";
			echo "\n";
			$suc += is_array($res) ? 1 : 0;
			flush();
		}
		echo "sucess = $suc <br>";
	}

	/** creates a new session key for the given site, if it does not already exist

		@attrib name=create_session_key

		@param site_id required type=int

	**/
	function create_session_key($arr)
	{
		// check if the site exists
		$row = $this->db_fetch_row("SELECT * FROM aw_site_list WHERE id = '$arr[site_id]'");
		if (!is_array($row) || $row["id"] != $arr["site_id"])
		{
			return -1;
		}

		// check that it already does not have a session key
		if (trim($row["session_key"]) != "")
		{
			return -2;
		}

		// create new key
		$key = gen_uniq_id();

		$td = mcrypt_module_open('rijndael-256', '', 'ofb', '');
		$iv = base64_encode(mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM));
		mcrypt_module_close($td);

		// save to database
		$this->db_query("UPDATE aw_site_list SET session_key = '$key', iv = '$iv' WHERE id = '$arr[site_id]'");

		// return it
		return array($key, $iv);
	}

	/**

		@attrib name=do_auto_update

		@param site_id required type=int
		@param data required

	**/
	function do_auto_update($arr)
	{
		$data = aw_unserialize($this->_decrypt(base64_decode($arr["data"]), $arr["site_id"]));

		if ($data["id"] != $arr["site_id"])
		{
			return -1;
		}

		// save to database

		// get url from baseurl
		$url = trim(preg_replace("/(.*)\:\/\/(.*)/imsU", "\\2", $data["baseurl"]));
		if ($url == "")
		{
			$url = $data["baseurl"];
		}

		if ($url == "")
		{
			return -2;
		}

		// resolve url to ip
		$ip = @gethostbyname($url);
		$server_url = @gethostbyaddr($ip);

		// check if such server exists
		if (!($serv_id = $this->get_server_id_by_ip(array("ip" => $ip))))
		{
			// if not, add it
			$serv_id = $this->db_fetch_field("SELECT MAX(id) as id FROM aw_server_list", "id")+1;
			$this->db_query("INSERT INTO aw_server_list(id,name,ip) values($serv_id,'$ip','$url')");
		}

		// url, used => 1, code path, basedir, updater uid, time of update, server_id
		$this->db_query("
			UPDATE
				aw_site_list
			SET
				url = '$data[baseurl]',
				site_used = 1,
				code_branch = '".$server_url.":".$data["code"]."',
				basedir = '$data[site_basedir]',
				updater_uid = '$data[uid]',
				last_update = '".time()."',
				server_id = '$serv_id'
			WHERE
				id = '$arr[site_id]'
		");
		return true;
	}

	function _decrypt($data, $site_id)
	{
		$row = $this->db_fetch_row("SELECT session_key,iv FROM aw_site_list WHERE id = '$site_id'");
		if (!$row)
		{
			return false;
		}

		$td = mcrypt_module_open('rijndael-256', '', 'ofb', '');
		mcrypt_generic_init($td, $row["session_key"], base64_decode($row["iv"]));
		$decrypted = mdecrypt_generic($td, $data);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);

		return $decrypted;
	}

	/** returns the baseurl for the given site id

		@attrib api=1

	**/
	function get_url_for_site($id)
	{
		// get the record from the local list
		$row = $this->db_fetch_row("SELECT * FROM aw_site_list WHERE id = '$id'");
		if ($row["last_update"] < (time()-24*3600))
		{
			$this->_do_update_list_cache();
			$row = $this->db_fetch_row("SELECT * FROM aw_site_list WHERE id = '$id'");
		}
		return $row["url"];
	}

	function _do_update_list_cache()
	{
		if (aw_ini_get("site_id") == 33)
		{
			return; // never ever update register.aw.com site list :)
		}

		$existing = array();
		$this->db_query("SELECT id FROM aw_site_list");
		while ($row = $this->db_next())
		{
			$existing[$row["id"]] = $row["id"];
		}

		$list = $this->do_orb_method_call(array(
			"class" => "site_list",
			"action" => "get_site_list",
			"method" => "xmlrpc",
			"server" => "register.automatweb.com",
			"no_errors" => true
		));
		foreach($list as $id => $row)
		{
			if ($existing[$id])
			{
				$this->db_query("
					UPDATE 
						aw_site_list 
					SET 
						url = '$row[url]',
						name = '$row[name]',
						server_id = '$row[server_id]',
						last_update = '".time()."'
					WHERE
						id = $id
				");						
			}
			else
			{
				$this->db_query("
					INSERT INTO aw_site_list(id,url,name,server_id, last_update)
						VALUES($id,'$row[url]','$row[name]','$row[server_id]','".time()."')
				");
			}
		}
	}
}
?>
