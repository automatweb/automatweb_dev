<?php

class ml_list extends aw_template
{
	function ml_list()
	{
		$this->init("automatweb/mlist");
		lc_load("definition");

		$this->dbconf=get_instance("config");
		$this->searchformid=$this->dbconf->get_simple_config("ml_search_form");
	}

	function orb_new($arr)
	{
		is_array($arr)? extract($arr) : $parent=$arr;

		$this->mk_path($parent,"Lisa meililist");

		$this->read_template("list_new.tpl");
		$tb = get_instance("toolbar");
		$tb->add_button(array(
			"name" => "save",
			"tooltip" => "Salvesta",
			"url" => "javascript:document.foo.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif"
		));

		$this->vars(array(
			"toolbar" => $tb->get_toolbar(),
			"name" => "", 
			"comment" => "",
			"vars" => $this->multiple_option_list(array(),$this->get_all_varnames()),
			"ufc" => $this->picker(0, $this->list_objects(array("class" => CL_ML_LIST_CONF, "addempty" => true))),
			"reforb" => $this->mk_reforb("submit_omadused",array("parent" => $parent,"id" => 0))
		));
		return $this->parse();
	}

	////
	//! Listi omaduste vaatamise submit
	function orb_submit_omadused($arr)
	{
		extract($arr);
		if ($id)
		{
			$ob = $this->load_list($id);

			if ($user_form_conf != $ob["meta"]["user_form_conf"])
			{
				// if form has changed, delete all pseudo vars
				$arr = new aw_array($ob["meta"]["vars"]);
				foreach ($arr->get() as $k => $v)
				{
					$this->del_pseudo_var($id,$k);
				};
			}

			if ($this->can("change_variables",$id))
			{
				$vars = $this->make_keys($vars);
				// leia ära võetud muutujad
				$arr = new aw_array($ob["meta"]["vars"]);
				foreach ($arr->get() as $k => $v)
				{
					if (!isset($vars[$k]))
					{
						$this->del_pseudo_var($id,$k);
					};
				};
				// leia lisatud muutujad
				foreach ($vars as $k => $v)
				{
					if (!isset($ob["meta"]["vars"][$k]))
					{
						$this->add_pseudo_var($id,$k);
					};
				};
			};
				
			$this->upd_object(array(
				"name" => $name,
				"comment" => $comment,
				"oid" => $id,
				"metadata" => array(
					"vars" => $vars,
					"user_form_conf" => $user_form_conf,
					"user_folders" => $this->make_keys($user_folders),
					"def_user_folder" => $def_user_folder,
					"automatic_form" => $automatic_form,
				)
			));
			$this->list_ob = $this->get_object($id, true);

			$tr = $this->db_fetch_row("SELECT * FROM ml_list2automatic_form WHERE lid = '$id'");
			if (is_array($tr))
			{
				$this->db_query("UPDATE ml_list2automatic_form SET fid = '$automatic_form'");
			}
			else
			{
				$this->db_query("INSERT INTO ml_list2automatic_form (lid, fid) VALUES('$id','$automatic_form')");
			}
			if ($automatic_form)
			{
				$this->update_automatic_list($id);
			}

			$this->_log(ST_MAILINGLIST, SA_CHANGE,"muutis meililisti $name", $id);
		}
		else
		{
			$id = $this->new_object(array(
				"class_id" => CL_ML_LIST,
				"name" => $name,
				"comment" => $comment,
				"parent" => $parent,
				"metadata" => array(
					"user_form_conf" => $user_form_conf
				)
			));
			$this->_log(ST_MAILINGLIST, SA_ADD,"lisas meililisti $name", $id);
		}
		return $this->mk_my_orb("omadused",array("id" => $id));
	}

	////
	//! Tagastab sinna lehekülje ülesse pandava lingi jaoks
	function _get_lf_path($id,$name="")
	{
		if (!$name)
		{
			$name="Meililist";
		};
		return "<a href=\"".$this->mk_my_orb("change",array("id"=>$id))."\">$name</a>&nbsp;/&nbsp;";
	}

	////
	//! Listi omaduste vaatamine
	function orb_omadused($ar)
	{
		extract($ar);
		$this->read_template("list_omadused.tpl");
		$row = $this->load_list($id);
		$this->mk_path($row["parent"],"Muuda");

		$allvars=$this->get_all_varnames();
		foreach ($allvars as $k => $name)
		{
			$this->vars(array(
				"name" => $name,
				"checked" => checked($row["meta"]["vars"][$k]),
				"acl" => (isset($row["meta"]["vars"][$k]))? "ACL" : "",
				"vid" => $k,
				"l_acl" => (isset($row["meta"]["vars"][$k])) ? ("editacl.".$this->cfg["ext"]."?oid=".$this->get_pseudo_var($id,$k)."&file=ml_var.xml") : "",
			));
			$varparse.=$this->parse("variable");
		};

		$tb = get_instance("toolbar");
		$tb->add_button(array(
			"name" => "save",
			"tooltip" => "Salvesta",
			"url" => "javascript:document.foo.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif"
		));

		$fl = array("0" => "");
		$ll = new aw_array($this->get_forms_for_list($id));
		$llstr = join(",",$ll->get());
		if ($llstr != "")
		{
			$this->db_query("SELECT oid, name FROM objects WHERE oid IN(".$llstr.")");
			while ($_row = $this->db_next())
			{
				$fl[$_row["oid"]] = $_row["name"];
			}
		}
		$this->vars(array(
			"toolbar" => $tb->get_toolbar(),
			"name" => $row["name"],
			"comment" => $row["comment"],
			"variable" => $varparse,
			"automatic_form" => $this->picker($row["meta"]["automatic_form"], $fl),
			"ufc" => $this->picker($row["meta"]["user_form_conf"], $this->list_objects(array("class" => CL_ML_LIST_CONF, "addempty" => true))),
			"user_folders" => $this->mpicker($row["meta"]["user_folders"], $this->get_all_user_folders_defined()),
			"def_user_folder" => $this->picker($row["meta"]["def_user_folder"], $this->get_all_user_folders_defined()),
			"reforb" => $this->mk_reforb("submit_omadused",array("id" => $id)),
		));

		return $this->parse();
	}

	////
	//! Annab kõik listi grupid
	// tagastab array lgroup => name
	function get_list_groups($id)
	{
		$this->load_list($id);
		$far = $this->get_all_user_folders();
		$ret = array();
		foreach($far as $fid => $fn)
		{
			$ret += $this->get_objects_below(array(
				"parent" => $fid,
				"class" => CL_PSEUDO,
				"full" => true,
				"ret" => ARR_NAME
			));
		}
		return $ret;
	}

	////
	//! Tagastab igasuguste picker funktsioonide jaoks listide ja gruppide valiku
	// id näitab, mis listi näidatakse esimesena
	// checkacl- kas kasutajal peab olema send õigus
	// fullnames- kas näidata alamgruppe nii "list:grupp", muidu näitab ainult "grupp"
	// new- kas näidata iga listi all -- uus grupp -- 
	// spacer- mis lisada iga grupi ette
	// prefix-mix lisada iga rea ette
	function get_lists_and_groups($args=array()/*$checkacl=0,$fullnames=0,$id=0,$new=0,$spacer=" "*/)
	{
		extract($args);
		$ar=array();
		$lists=$this->get_all_lists($checkacl);
		
		if ($id)
		{
			//reorder array, so that $id is first
			$l2[$id]=$lists[$id];
			unset($lists[$id]);
			$lists=$l2+$lists;
		};
		//echo("<pre>");print_r($lists);echo("</pre>");//dbg

		foreach($lists as $id => $name)
		{
			$g=$this->get_list_groups($id);
			$ar["$id:0"]=$prefix.$name;
			
			if (is_array($g))
			{
				foreach ($g as $k => $v)
				if ($k)
				{
					$fn=$fullnames ? "$name:":"";
					$ar["$id:$k"]="$spacer$spacer$prefix$fn$v";
				};
			};
			
			if ($new)
			{
				$ar["$id:n"]="$spacer$spacer-- uus grupp --";
			};
			
		};
		return $ar;
	}

	////
	//! Annab kõik listid
	function get_all_lists($checkacl=0)
	{
		$alllists=array();
		$this->db_query("SELECT name,oid FROM objects WHERE class_id ='".CL_ML_LIST."' AND status != '0'");
		while ($r = $this->db_next())
		{
			if (!$checkacl || $this->can("send",$r["oid"]) )
			{
				$alllists[$r["oid"]]=$r["name"];
			};
		};
		return $alllists;
	}

	// pseudomuutujate funktsioonid

	////
	//! Lisab pseudomuutuja objekti
	// klass on vist vale sest see klass on menüüde oma aga suva tgelt
	function add_pseudo_var($lid,$vid)
	{
		return $this->new_object(array("name" => "pseudo_var_$vid","parent"=>$lid, "class_id" => CL_PSEUDO, "last" => $vid),1);
	}

	////
	//! Kustutab pseudomuutuja
	function del_pseudo_var($lid,$vid)
	{
		$shoot_me=$this->get_pseudo_var($lid,$vid);
		$this->delete_object($shoot_me);
	}

	////
	//! Tagastab $vid formi elemendile vastava pseudomuutuja $lid listi all
	function get_pseudo_var($lid,$vid)
	{
		$lid=(int)$lid;
		$vid=(int)$vid;
		return $this->db_fetch_field("SELECT oid FROM objects WHERE objects.parent = '$lid' AND objects.last = '$vid' AND objects.status != 0 AND objects.class_id =".CL_PSEUDO,"oid");
	}

	// nii, see peab siis kuidagi formi käest variabled küsima

	////
	//! Tagastab kõik formi elemendid id => name 
	function get_all_varnames($id = false, $conf = false, $all_vars = true)
	{
		$ret = array();
		$fb = get_instance("formgen/form_base");
		if (!is_array($this->list_ob) && $id)
		{
			$this->load_list($id);
		}
		if ($conf)
		{
			$ufc_inst = get_instance("mailinglist/ml_list_conf");
			$this->formid = $ufc_inst->get_forms_by_id($conf);
		}

		$ar = new aw_array($this->formid);
		foreach($ar->get() as $fid)
		{
			$ml = $fb->get_form_elements(array("id" => $fid, "key" => "id", "all_data" => false));
			foreach($ml as $k => $v)
			{
				if ($this->list_ob['meta']['vars'][$k] || $all_vars)
				{
					$ret[$k] = $v;
				}
			}
		}
		return $ret;
	}

	// --------------------------------------------------------------------
	// messengerist saatmise osa

	////
	//! Messenger kutsub välja kui on valitud liste targetiteks
	// vajab targets ja id
	function route_post_message($args = array())
	{
		extract($args);
		$url=$this->mk_my_orb("post_message",array("id" => $id, "targets" => $targets),"",1);
		$sched = get_instance("scheduler");
		$sched->add(array(
			"event" => $this->mk_my_orb("process_queue", array(), "ml_queue", false, true),
			"time" => time()+120,	// every 2 minutes
		));
		return $url;
	}

	////
	//! saadab teate $id listidesse $targets(array stringidest :listinimi:grupinimi)
	function post_message($args)
	{
		extract($args);
		$this->mk_path(0,"<a href='".aw_global_get("route_back")."'>Tagasi</a>&nbsp;/&nbsp;Saada teade");
		$this->read_template("post_message.tpl");

		load_vcl("date_edit");
		$date_edit = new date_edit(time());
		$date_edit->configure(array(
			"day" => "",
			"month" => "",
			"year" => "",
			"hour" => "",
			"minute" => "",
			"classid" => "small_button",
		));

		$id = (int)$id;//teate id

		// siin tee selline trikk, et järjesta arrayd vähe teise süsteemi järgi ringi 
		$names=array();
		$_names="";
		foreach($targets as $v)
		{
			list($shit,$lname,$gname)=explode(":",$v);
			if (!isset($names[$lname]))
			{
				$_names.=($_names!=""?",":"")."'$lname'";
			};
			$names[$lname]=1;
		};

		
		$listdata=array();
		$q="SELECT name,oid,metadata FROM objects WHERE class_id=".CL_ML_LIST." AND NAME IN($_names) AND status != 0";
		unset($names);
		unset($_names);
		$this->db_query($q);
		while ($listdta = $this->db_next())
		{
			$listdata[$listdta["name"]]=array(
				"id" => $listdta["oid"],
				"groups" => array_flip($this->get_list_groups($listdta["oid"]))
			);
		};
		

		$lists=array();
		foreach($targets as $v)
		{
			$item=explode(":",$v);

			$lid=$listdata[$item[1]]["id"];
			$gid=$item[2] ? $listdata[$item[1]]["groups"][$item[2]] : 0;

			$lists[(int)$lid]["name"]=$item[1];
			$lists[(int)$lid]["c"][(int)$gid]=$item[2]?$item[2]:" ";
		};

		// Kui on määratud mingi listi grupid ja ka list ise, siis tegelikult mõeldakse
		// listi enda all listi kõiki ülejäänud gruppe. Niisiis tuleb see muutus siin teha

		//echo("<pre>lists=");print_r($lists);echo("</pre>");//dbg
		foreach($lists as $lid => $v)
		{
			/*echo("doing $lid s=".sizeof($v["c"])."0=*".isset($v["c"]["0"])."*<pre>");print_r($v);echo("</pre><br>");*/
			if ($v["c"][0] && sizeof($v["c"])>1)//kui on list ja on ka gruppe sellest
				{
					/*echo("bljää!$lid<br>");//dbg*/
					// võta kõik grupid ja leia need, mida pole määratud
					//$allgrps=$this->get_list_groups($lid);
					$allgrps=array_flip($listdata[$v["name"]]["groups"]);

					foreach ($v["c"] as $del => $jura)
					{
						/*echo("unsetting $del<br>");*/
						unset($allgrps[$del]);
					};
					/*echo("after=");print_r($allgrps);//dbg*/
					unset($lists[$lid]["c"][0]);
					$gid=join("|",array_keys($allgrps));
					if ($gid)
						$lists[$lid]["c"][$gid]=join(",",array_values($allgrps));
				};
		};
		/*echo("<pre>lists=");print_r($lists);echo("</pre>");//dbg*/

		foreach($lists as $lid => $v)
		{
			foreach($v["c"] as $gid => $gname)
			{
				$this->vars(array(
					"lidgid" => "$lid:$gid",
					"title" => $v["name"].($gname?":".$gname :""),
					"date_edit" => $date_edit->gen_edit_form("start_at[$lid:$gid]",time()-13)
					));
				$listrida.=$this->parse("listrida");
			};
		};
		


		$this->vars(array(
			"listrida" => $listrida,
			"reforb" => $this->mk_reforb("submit_post_message",array(
				"id" => $id,
				"lists" => htmlentities(serialize($lists)),
				))
		));

		return $this->parse();
	}

	////
	//! See händleb juba õiget postitust, siis kui on valitud saatmise ajavahemikud
	function submit_post_message($args)
	{
		extract($args);
		
		
		$id=(int)$id;
		//		echo($lists);//dbg
		$lists=unserialize($lists);
		//		dbg::dump($lists);
		//echo("<pre>");//dbg
		//print_r($lists);//dbg
		// siin tekib nüüd see küsimus, et kas valitud list tähendab kogu listi või siis ainult neid liikmeid,
		// millele ei ole täpsustavat gruppi määtatud. praegu on nii, et tähendab kogu listi

		// kui on veel valitud täpsustavaid gruppe ka, siis 0 muudetakse eelmise funktsiooni poolt
		// ülejäänud gruppideks ehk kokkuvõttes saadetakse kogu grupile meil, ainut osale gruppidele on eritingimused

		load_vcl('date_edit');
		unset($aid);
		$total=0;
		$_lists = new aw_array($lists);
		foreach($_lists->get() as $lid => $v)
		{
			foreach($v["c"] as $gid => $gname)
			{
				$key="$lid:$gid";
				//echo($lid." -".$v["name"].":$gid- $gname key=$key<br>");//dbg
				$_start_at=date_edit::get_timestamp($start_at[$key]);
				$_delay=$delay[$key] * 60;
				$_patch_size=$patch_size[$key];
				//echo("$_start_at $_delay $_patch_size<br>");//dbg

				$lgroupa=explode("|",$gid);
				foreach($lgroupa as $_k => $_v)
				{
					$lgroupa[$_k]="'".(int)$_v."'";
				};
				$lgroup=join(",",$lgroupa);

				$lgroup=($lgroup && $lgroup!="'0'")?"AND lgroup IN ($lgroup)":"";
				//echo("lgroup=$lgroup");//dbg

				$count = $this->get_member_count($lid);
				//echo("count=$count<br><br>");//dbg

				if (!isset($aid))
				{
					// tee sisestus avoidmids tabelisse
					$this->db_query("INSERT INTO ml_avoidmids (avoidmids) VALUES ('')");
					$aid=$this->db_last_insert_id();
				};
				$total++;
				$this->db_query("INSERT INTO ml_queue (lid,mid,gid,uid,aid,status,start_at,last_sent,patch_size,delay,position,total)
					VALUES ('$lid','$id','$gid','".aw_global_get("uid")."','$aid','0','$_start_at','0','$_patch_size','$_delay','0','$count')");
				
				$this->_log(ST_MAILINGLIST, SA_SEND,"saatis meili $id listi ".$v["name"].":$gname", $lid);
			};
		};
			
		$this->db_query("UPDATE ml_avoidmids SET usagec='$total' WHERE aid='$aid'");

		return aw_global_get("route_back");
	}

	function load_list($id, $nocache = false)
	{
		$this->list_ob = $this->get_object($id, $nocache);
		$ufc_inst = get_instance("mailinglist/ml_list_conf");
		$this->formid = $ufc_inst->get_forms_by_id($this->list_ob["meta"]["user_form_conf"]);
		return $this->list_ob;
	}

	function get_all_user_folders()
	{
		$ar = new aw_array($this->list_ob["meta"]["user_folders"]);
		return $ar->get();
	}

	function get_all_user_folders_defined()
	{
		$ufc_inst = get_instance("mailinglist/ml_list_conf");
		return $ufc_inst->get_folders_by_id($this->list_ob["meta"]["user_form_conf"]);
	}

	function get_mailto_element($lid = 0)
	{
		if ($lid && !is_array($this->list_ob))
		{
			$this->load_list($lid);
		}
		$ufc_inst = get_instance("mailinglist/ml_list_conf");
		$ret = $ufc_inst->get_mailto_element($this->list_ob["meta"]["user_form_conf"]);
		return $ret;
	}

	function flush_member_cache($id = false)
	{
        if ($id === false)
        {
          aw_cache_flush("ml_list::get_members");
        }
        else
        {
          aw_cache_set("ml_list::get_members", $id, false);
        }
	}

	function get_members($id)
	{
/*		if (is_array($ret = aw_cache_get("ml_list::get_members", $id)))
		{
			return $ret;
		}*/
		$ret = array();

		$ob = $this->load_list($id);
		$ar = new aw_array($this->list_ob["meta"]["user_folders"]);
		foreach($ar as $prnt)
		{
			$ret+=$this->get_objects_below(array(
				"parent" => $prnt,
				"class" => CL_ML_MEMBER,
				"full" => true
			));
		}
		aw_cache_set("ml_list::get_members", $id, $ret);
		return $ret;
	}	

	function get_member_count($id)
	{
		return count($this->get_members($id));
	}

	function get_forms_for_list($id)
	{
		$this->load_list($id);
		$ufc_inst = get_instance("mailinglist/ml_list_conf");
		return $ufc_inst->get_forms_by_id($this->list_ob["meta"]["user_form_conf"]);
	}

	function get_all_folders_for_list($id)
	{
		$this->load_list($id);
		$far = $this->get_all_user_folders();
		$ret = array();
		foreach($far as $fid => $fn)
		{
			$ret[$fid] = $fn;
			$ret += $this->get_objects_below(array(
				"parent" => $fid,
				"class" => CL_PSEUDO,
				"full" => true,
				"ret" => ARR_NAME
			));
		}
		return $ret;
	}

	////
	// !adds a brother to the list member to the list
	function add_member_to_list($arr)
	{
		extract($arr);
		$this->load_list($lid);
		$folder = $this->list_ob["meta"]["def_user_folder"];

//		echo "try add user for list $lid base on $mid <br>";

		// check if this member exists in this list already
		$members = $this->get_members($lid);
//		echo "members for list eq ".join(",", array_keys($members))." checking new member $mid<br>";
		$found = false;
		foreach($members as $_mmid => $mdat)
		{
			$checkoid = $mdat["oid"];
			if ($mdat["brother_of"])
			{
				$checkoid = $mdat["brother_of"];
			}
			if ($checkoid == $mid)
			{
//				echo "found $mid for $checkoid <Br>";
				$found = true;
			}
		}
		$mdat = $this->get_object($mid);

		if (!$found)
		{
			$id = $this->new_object(array(
				"parent" => $folder,
				"name" => $mdat["name"],
				"class_id" => CL_ML_MEMBER,
				"brother_of" => ($mdat["brother_of"] ? $mdat["brother_of"] : $mdat["oid"])
			));
//			echo "not found, adding $mdat[name] <Br>";
			$mlm = get_instance("mailinglist/ml_member");
			$mlm->update_member_name($id);
		}
	}

	function remove_member_from_list($arr)
	{
		extract($arr);
		$this->load_list($lid);
		$members = $this->get_members($lid);
		foreach($members as $_mid => $mdat)
		{
			if ($mdat["brother_of"] == $mid)
			{
				$this->delete_object($mdat["oid"]);
			}
		}
	}

	function get_list_ids_by_name($name)
	{
		$ret = array();
		$lns = explode(",",$name);
		foreach($lns as $ln)
		{
			$name = substr($ln, 1);
			$ret[] = $this->db_fetch_field("SELECT oid FROM objects WHERE class_id = ".CL_ML_LIST." AND status != 0 AND name = '".$name."'","oid");
		}
		return $ret;
	}

	function update_automatic_list($id)
	{
		// get all members for the list
	    $this->list_ob = false;
		$mem = $this->get_members($id);

		$automatic_form = $this->list_ob["meta"]["automatic_form"];

		$meminf = array();
		// get all correct form entries for list
		$memstr = join(",", array_keys($mem));
		if ($memstr != "")
		{
			$this->db_query("SELECT * FROM ml_member2form_entry LEFT JOIN objects ON objects.oid = member_id WHERE member_id IN($memstr) AND form_id = '$automatic_form' AND objects.status != 0");
			while ($row = $this->db_next())
			{
				$meminf[$row["entry_id"]] = $row;
			}
		}

		// get all form entries
		$finst = get_instance("formgen/form");
		$entries = $finst->get_entries(array("id" => $automatic_form));

		$mem_inst = get_instance("mailinglist/ml_member");

		// now make members from all the form entries that are already not members
		$cr = false;
		foreach($entries as $eid => $ename)
		{
			if (!isset($meminf[$eid]))
			{
				$mem_inst->create_member(array(
					"parent" => $this->list_ob["meta"]["def_user_folder"],
					"entries" => array(
						$automatic_form => $eid
					),
					"conf" => $this->list_ob["meta"]["user_form_conf"]
				));
				$cr = true;
			}
		}
	}

	function get_default_user_folder($id)
	{
		$this->load_list($id);
		return $this->list_ob["meta"]["def_user_folder"];
	}

	function get_conf_id($id)
	{
		$this->load_list($id);
		return $this->list_ob["meta"]["user_form_conf"];
	}
};
?>
