<?php

	global $orb_defs;
	$orb_defs["ml_list"] = "xml";

	classload("config","form");
	class ml_list extends aw_template
	{
		function ml_list()
		{
			$this->tpl_init("automatweb/mlist");
			$this->db_init();
			lc_load("definition");

			$this->dbconf=new db_config();
			$this->formid=$this->dbconf->get_simple_config("ml_form");
			$this->searchformid=$this->dbconf->get_simple_config("ml_search_form");
		}

		function orb_new($arr)
		{
			is_array($arr)? extract($arr) : $parent=$arr;

			$this->mk_path($parent,"Lisa meililist");

			$this->read_template("list_new.tpl");
			$this->vars(array(
				"name" => "", 
				"comment" => "",
				"vars" => $this->multiple_option_list(array(),$this->get_all_varnames()),
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
				if (!$subop)
				{
					$arr=array(
						"name" => $name,
						"comment" => $comment,
						"oid" => $id
						);

					if ($this->can("change_variables",$id))
					{
						$ol=$this->get_last($id);
						if (!is_array($vars))
						{
							$vars=array();
						};
						$vars=array_flip($vars);

						$ol["vars"]=unserialize($ol["vars"]);
/*						echo("<pre>olvars=");print_r($ol["vars"]);echo("</pre>");//dbg
						echo("<pre>vars=");print_r($vars);echo("</pre>");//dbg*/
						// leia ära võetud muutujad
						foreach ($ol["vars"] as $k => $v)
						{
							if (!isset($vars[$k]))
							{
								$this->del_pseudo_var($id,$k);
							};
						};
						// leia lisatud muutujad
						foreach ($vars as $k => $v)
						{
							if (!isset($ol["vars"][$k]))
							{
								$this->add_pseudo_var($id,$k);
							};
						};

						$ol["vars"]=serialize($vars);
						$arr["last"]=serialize($ol);;
					};
					
					$this->upd_object($arr);
				} else
				if ($subop=="delete")
				{
					//if ($this->can("",$id))
					$this->del_list_group($id,$lgroup);
				} else
				if ($subop=="rename")
				{
					//if ($this->can("",$id))
					$this->ren_list_group($id,$lgroup,$gname);
				} else 
				if ($subop=="new")
				{
					$this->add_list_group($id,$gname);
				};
				$this->_log("mlist","muutis meililisti $name");
			}
			else
			{
				$id = $this->new_object(
					array(
						"class_id" => CL_ML_LIST,
						"name" => $name,
						"comment" => $comment,
						"parent" => $parent,
						"last" => serialize(array("vars"=>serialize(array_flip(is_array($vars) ? $vars : array() ))
						))
					));
			
				$this->_log("mlist","lisas meililisti $name");
			}
			return $this->mk_my_orb("omadused",array("id"=>$id));
		}

		////
		//! Tagastab sinna lehekülje ülesse pandava lingi jaoks
		function _get_lf_path($id,$name="")
		{
			if (!$name)
			{
				$name="Meililist";
			};
			return "<a href=\"".$this->mk_orb("change",array("id"=>$id))."\">$name</a>&nbsp;/&nbsp;";
		}

		////
		//! Listi omaduste vaatamine
		function orb_omadused($ar)
		{
			global $ext;
			is_array($ar) ? extract($ar) : $id=$ar;

			$this->read_template("list_omadused.tpl");

			$row = $this->get_object($id);

			$this->mk_path($row["parent"],$this->_get_lf_path($id,$row["name"])."Omadused");

			$last=unserialize($row["last"]);
			$vars=unserialize($last["vars"]);

			global $back;
			$back=$this->mk_my_orb("omadused",array("id" => $id));
			session_register("back");

			$varacl=$this->can("change_variable_acl",$id);
			$allvars=$this->get_all_varnames();
			foreach ($allvars as $k => $name)
			{
				$this->vars(array(
					"name" => $name,
					"checked" => isset($vars[$k]) ? "checked" : "",
					"acl" => (isset($vars[$k]) && $varacl )? "ACL" : "",
					"vid" => $k,
					"l_acl" => (isset($vars[$k]) && $varacl ) ? ("editacl.$ext?oid=".$this->get_pseudo_var($id,$k)."&file=ml_var.xml") : "",
				));
				$varparse.=$this->parse("variable");
			};
			$groups=$this->get_list_groups($id);
			unset($groups[0]);
			foreach($groups as $k => $v)
			{
				$this->vars(array(
					"name" => $v,
					"lgroup" => $k,
					));
				$g.=$this->parse("group");
			};

			$this->vars(array(
				"name" => $row["name"],
				"id" => $id,
				"comment" => $row["comment"],
				"group" => $g,
				"variable" => $varparse,
				"reforb" => $this->mk_reforb("submit_omadused",array("id" => $id ,"subop"=> "0","lgroup"=>"0"))
			));

			return $this->parse();
		}

		// Iga objekt peaks saama võimaluse end ise kustutada
		// aga menuedit.aw ei kutsu orbi kaudu kustutust välja
		////
		//! hm, mis see küll võix olla
		function orb_delete($ar)
		{
			is_array($ar) ? extract($ar) : $id=$ar;

			$name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = '$id'","name");
			$this->delete_object($id);
			$this->remove_member_from_list(array("lid" => $id));

			$this->_log("mlist","kustutas meililisti $name");
			$url=$this->mk_my_orb("change",array("id" => $id));
			header("Location:$url");
		}
	
		////
		//! Näitab listi all olevaid liikmeid
		function orb_change($arr)
		{
			extract($arr);
			global $ext;
			$this->read_template("lf_folder.tpl");
			$id=(int)$id;
			$name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = '$id'","name");


			load_vcl("table");
			global $PHP_SELF;

			$t = new aw_table(array(
				"prefix" => "ml_list",
				"self" => $PHP_SELF,
				"imgurl" => $baseurl . "/automatweb/images",
			));

			$t->set_header_attribs(array(
				"class" => "ml_list",
				"action" => "change",
				"id" => $id
			));

			global $back,$queue_back;
			$back=$queue_back=$this->mk_my_orb("change",array("id" => $id));
			session_register("back","queue_back");

			$can_add=$this->can("add_users",$id);
			$can_del=$this->can("delete_users",$id);

			
			$headerarray=array(
				$this->mk_my_orb("omadused",array("id" => $id)) => "Omadused",
				"editacl.".$ext."?oid=$id&file=ml_list.xml" => "ACL",
				$this->mk_my_orb("queue",array("fid" => $id,"show" => "list"),"ml_queue") => "Queue",
				);

			if ($can_add)
			{
				$headerarray[$this->mk_my_orb("lf_addmember",array("lid" => $id))] = "Lisa liige";
			};

			if ($can_del)
			{
				$headerarray['javascript:Do("lf_cut");'] = "Cut";
			};

			if ($can_add)
			{
				$headerarray['javascript:Do("lf_copy");'] = "Copy";
			};

			
			$this->db_query("SELECT COUNT(*) AS count FROM ml_list2member WHERE is_copied='1' OR is_cut='1'");
			$r= $this->db_next();
			if ($r["count"] > 0)
			{
				$headerarray['javascript:Do("lf_paste");'] = "Paste";
			};

			if ($can_del)
			{
				$headerarray['javascript:Do("lf_remove");'] = "Eemalda";
			};
			
			
			$headerarray["extra"]="<td class='fgtitle_new' colspan='2'><select name='listsel' align='left' class='fgtitle_button'>".$this->picker(array(),$this->get_lists_and_groups(array("id" => $id, "new" => 1, "spacer" => "&nbsp;")))."</select>&nbsp;&nbsp;<a href='javascript:Liiguta();' class='fgtitle_link'><strong>Liiguta</strong></a></td>";
			$headerarray["extrasize"]=2;
			$t->parse_xml_def($this->basedir."/xml/mlist/lf_folder.xml");
			$t->define_header("MEILILIST",$headerarray);

			$groups=$this->get_list_groups($id);

			$this->db_query("SELECT ml_list2member.*,objects.* FROM ml_list2member,objects WHERE ml_list2member.lid = '$id' AND ml_list2member.mid=objects.oid AND objects.status != '0' ORDER BY lgroup ASC");

			while ($r = $this->db_next())
			{
				// cut & copied halli taustaga
				if ($r["is_cut"] || $r["is_copied"])
				{
					$r["_bcol"]="#eeeeee";
				};

				$r["nimi"]="<!-- ".$r["name"]."--><a href=\"".$this->mk_my_orb("change",array("id" => $r["mid"], "lid" => $id),"ml_member")."\">".$r["name"]."</a>";
				$r["vali"]="<input type='checkbox' NAME='sel[]' value='".$r["mid"]."'>";
				$r["lgroup"]=$groups[$r["lgroup"]];
				$t->define_data($r);
			}


			if ($sortby)
			{
				$t->sort_by(array("field"=>$sortby));
			} else
			{
				$t->sort_by(array());
			};

			$this->vars(array(
				"table" => $t->draw(),
				"reforb" => $this->mk_reforb("something",array("lid" => $id))
				));
			return $this->parse();
		}

		////
		//! Händleb listi liikmete vaatest cut linki
		function orb_lf_cut($arr)
		{
			extract($arr);
			if (!$this->can("delete_users",$lid))
			{
				return $this->acl_error("delete_users",$lid);
			};

			$this->db_query("UPDATE ml_list2member SET is_cut='0'");
			$this->db_query("UPDATE ml_list2member SET is_copied='0'");
			if (is_array($sel))
			{
				foreach ($sel as $k => $v)
				{
					$sel[$k]=(int)$v;
				};
				$v=join(",",$sel);
				$this->db_query("UPDATE ml_list2member SET is_cut='1' WHERE mid IN ($v) AND lid = '$lid'");
			};
			return $this->mk_my_orb("change",array("id" => $lid));
		}

		////
		//! Händleb listi liikmete vaatest copy linki (lf) on nigu list_folder
		function orb_lf_copy($arr)
		{
			extract($arr);
			$this->db_query("UPDATE ml_list2member SET is_cut='0'");
			$this->db_query("UPDATE ml_list2member SET is_copied='0'");
			if (is_array($sel))
			{
				foreach ($sel as $k => $v)
				{
					$sel[$k]=(int)$v;
				};
				$v=join(",",$sel);
				$this->db_query("UPDATE ml_list2member SET is_copied='1' WHERE mid IN ($v) AND lid = '$lid'");
			};
			return $this->mk_my_orb("change",array("id" => $lid));
		}

		////
		//! Händleb listi liikmete vaatest peist linki
		function orb_lf_paste($arr)
		{
			extract($arr);
			if (!$this->can("add_users",$lid))
			{
				return $this->acl_error("add_users",$lid);
			};


			$this->db_query("SELECT * FROM ml_list2member WHERE is_copied = '1' or is_cut = '1'");
			while ($r = $this->db_next())
			{
				$this->save_handle();
				$this->add_member_to_list(array("lid" => $lid, "mid" => $r["mid"]));
				$this->restore_handle();
			};
			$this->db_query("UPDATE ml_list2member SET is_copied = '0'");
			$this->db_query("DELETE FROM ml_list2member WHERE is_cut = '1'");
			return $this->mk_my_orb("change",array("id" => $lid));
		}

		////
		//! Händleb listi liikmete vaatest Liiguta linki.
		// sel on tsekpoxidega valitud liikmed
		function orb_lf_movemembers($arr)
		{
			extract($arr);
			if (!$this->can("add_users",$target))
			{
				return $this->acl_error("add_users",$lid);
			};
			
			$target=(int)$target;
			$lid=(int)$lid;
			// kui valiti uus grupp, siis tee see
			if ($lgroup=="n")
			{
				$lgroup=$this->add_list_group($target,$newgroupname);
			};

			// siin saaks teha lihtsalt UPDATE , , set lid='' , lgroup='' aga iga muutus on ju vaja logida !!
			if (is_array($sel))
			{
				$fname=$this->db_fetch_field("SELECT name FROM objects WHERE oid = '$lid'","name");
				$tname=$this->db_fetch_field("SELECT name FROM objects WHERE oid = '$target'","name");
				$tgroup=$this->get_list_groups($target);
				$tgroup=$tgroup[$lgroup];
				foreach($sel as $v)
				{
					$v=(int)$v;
					$row=$this->get_object($v);
					if ($row["class_id"] == CL_ML_MEMBER)
					{
						$this->remove_member_from_list(array("lid" => $lid,"mid" => $v));
						$this->add_member_to_list(array("lid" => $target,"mid" => $v,"grp" => $lgroup));
						$mname=$row["mname"];
						$this->_log("mlist","liigutas liikme $mname listist $fname listi $tname:$tgroup");
					};
				};
			};
			return $this->mk_my_orb("change",array("id" => $lid));
		}

		////
		//! Händleb listi liikmete vaatest eemalda linki
		function orb_lf_remove($arr)
		{
			extract($arr);
			if (!$this->can("delete_users",$lid))
			{
				return $this->acl_error("delete_users",$lid);
			};
			if (is_array($sel))
			{
				$lname=$this->db_fetch_field("SELECT name FROM objects WHERE oid = '$lid'","name");
				foreach($sel as $v)
				{
					$row=$this->get_object($v);
					if ($row["class_id"] == CL_ML_MEMBER)
					{
						$this->remove_member_from_list(array("lid" => $lid,"mid" => $v));
						$mname=$row["name"];
						$this->_log("mlist","eemaldas liikme $mname listist $lname");
					};
				};
			};
			return $this->mk_my_orb("change",array("id" => $lid));
		}

		////
		//! Siia tuleb siis, kui listi liikete lisamise juures vajutada OTSI
		function orb_lf_addmember_s($arr)
		{
			$f=new form();
			$arr["redirect_after"]="boo";

			// kas tingimata peab selle otsingu vahepeal entryks tegema
			// search võiks ju seda inffi arraydest ka sisse lugeda
			$f->process_entry($arr);
			$shoot_me=(int)$f->entry_id;
			$tulemus=$f->search($shoot_me);

			// Ohh, lahe, minumeelest oli kuskil kirjas, et form::search tagastab
			// array kujul formid => array(match,match), jne
			// Aga oh imet! ära muudetud
			$ids=array();
			if (is_array($tulemus)/* && is_array($ar= $tulemus[$this->formid] )*/)
			{
				$ids=$tulemus;
			};

			// võta otsinguformi entry ära, muidu koguneb neid liiga palju
			$this->delete_object($shoot_me);
			$this->db_query("DELETE FROM form_".$this->searchformid."_entries WHERE id = '$shoot_me'");

			// annab lihtsalt liikmete lisamise funktsioonile näidatavad id-d. lame lame
			return $this->mk_my_orb("lf_addmember",array(
				"lid" => $arr["lid"],
				"search" => 1,
				"ids" => join("|",$ids)
				));
		}

		////
		//! Näitab listi liikmete lisamise lehte
		function orb_lf_addmember($arr)
		{
			global $class_defs;
			extract($arr);
			$row = $this->get_object($lid);
			$this->mk_path($row["parent"],$this->_get_lf_path($lid)."Lisa liikmeid");

			$this->read_template("lf_addmember.tpl");
			
			$f=new form();
			//echo("searchformid=$this->searchformid");//dbg
			$fparse=$f->gen_preview(array(
				"id" => $this->searchformid,
				"reforb" => $this->mk_reforb("lf_addmember_s",array(
					"parent" => $parent,
					"id" => $this->searchformid,
					"lid" => $lid,
					))
				));
	
			
			// kui on otsitud siis näita ainult leitud liikmeid

			if ($search)
			{
				$lisa=" AND oid IN (";
				$koma=0;
				$ids=explode("|",$ids);
				if (is_array($ids))
				{
					foreach($ids as $v)
					{
						$lisa.=($koma?",":"")."'".(int)$v."'";
						if (!$koma)
						{
							$koma=1;
						};
					};
				};
				$lisa.=")";
			} else
			{
				$lisa="";
			};
			// võta liikmed ja näita neid templates menüüde kaupa
			$this->db_query("SELECT name,oid,parent,modified,modifiedby,class_id FROM objects WHERE class_id ='".CL_ML_MEMBER."' AND status != '0' $lisa ORDER BY parent");
			$prevfid=0;
			$line="";

			while ($r = $this->db_next())
			{
				// kui algas uus folder
				if ($r["parent"]!=$prevfid)
				{
					// kui vanas folderis oli liikmeid siis näita vanat folderit
					if ($line != "")
					{
						// leiab folderi tee
						$this->save_handle();
						$tee = $this->get_object_chain($prevfid);
						reset($tee);
						$path="";
						global $ext;
						while (list(,$rx) =each($tee))
						{
							$path="<a target='_blank' href='menuedit.$ext?parent=".$rx["oid"]."&type=objects&period=".$period."' alt='".$rx["oid"]."'>".$rx["name"]."</a> / ".$path;
						}
						$this->restore_handle();
						$this->vars(array(
							"name" => $path,
							"type" => $class_defs[CL_PSEUDO]["name"],
							"pid" => $prevfid,
							"LINEX" => $line
							));
						
						$folder.=$this->parse("FOLDER");
					};
					$line="";
					$prevfid=$r["parent"];
				};
				$this->vars(array(
					"name" => $r["name"],
					"id" => $r["oid"],
					"pid" => $r["parent"],
					"changedby" => $r["modifiedby"],
					"modified" => $this->time2date($r["modified"],2),
					"type" => $class_defs[$r["class_id"]]["name"],
					"chlink" => $this->mk_my_orb("change",array("id" => $r["oid"],"parent" => $r["parent"]),"ml_member"),
					));
				$line.=$this->parse("LINE");
			};

			// viimane folder
			if ($line != "")
			{
				// leiab folderi tee
				
				$tee = $this->get_object_chain($prevfid);
				reset($tee);
				$path="";
				global $ext;
				while (list(,$rx) =each($tee))
				{
					$path="<a target='_blank' href='menuedit.$ext?parent=".$rx["oid"]."&type=objects&period=".$period."' alt='".$rx["oid"]."'>".$rx["name"]."</a> / ".$path;
				}	
				
				$this->vars(array(
					"name" => $path,
					"type" => $class_defs[CL_PSEUDO]["name"],
					"pid" => $prevfid,
					"LINEX" => $line,
					));
				$folder.=$this->parse("FOLDER");
			};

			// tee kõikide liikmete näitamise link
			if ($search)
			{
				$this->vars(array(
					"l_showall" => $this->mk_my_orb("lf_addmember",array("lid" => $lid))
					));
				$lshowall=$this->parse("SHOWALL");
			};
			$this->vars(array(
				"FOLDER" => $folder,
				"SEARCHFORM" => $fparse,
				"SHOWALL" => $lshowall,
				"SUBMIT" => $this->can("add_users",$lid) ? $this->parse("SUBMIT") :"",
				"reforb" => $this->mk_reforb("submit_lf_addmember",array("lid" => $lid)),
				));
			return $this->parse();
		}

		////
		//! Händleb liikmete lisamise lehekülje posti
		function orb_sumbit_lf_addmember($arr)
		{
			extract($arr);
			if (!$this->can("add_users",$lid))
			{
				return $this->acl_error("add_users",$lid);
			};
			if (is_array($sel))
			{
				$lname=$this->db_fetch_field("SELECT name FROM objects WHERE oid = '$lid'","name");
				foreach($sel as $v)
				{
					$this->add_member_to_list(array(
						"lid" => $lid,
						"mid" => $v
						));
					$mname=$this->db_fetch_field("SELECT name FROM objects WHERE oid = '$v'","name");
					$this->_log("mlist","lisas liikme $mname listi $lname");
				};
			};
			return $this->mk_my_orb("change",array("id" => $lid));
		}

		// funktsioonid operatsioonide lihtsustamiseks

		////
		//! Nagu nimi püüab väljendada, eemaldab see liikme listist
		// $mid- member id, kui pole defineeritud, siis eemaldab listist kõik liikmed
		// $lid- list id, kui pole defineeritud, siis eemaldab liikme kõigist listidest
		function remove_member_from_list($arr)
		{
			extract($arr);
			unset($w);
			if ($mid)
			{
				$mid=(int)$mid;
				$w="mid='$mid'";
			};
			if ($lid)
			{
				$lid=(int)$lid;
				$w.=($w?" AND ":"")." lid='$lid'";
			};
			return	$this->db_query("DELETE FROM ml_list2member WHERE $w");
		}

		////
		//! Lisab liikme listi
		// $mid - member id
		// $lid - list id
		// $lgroup - listi grupi id (optional kui pole, läheb ilma grupita)
		function add_member_to_list($arr)
		{
			extract($arr);
			$lid=(int)$lid;
			$mid=(int)$mid;
			if ($grp)
			{
				$w1=",lgroup";
				$w2=",'".(int)$grp."'";
			} else
			{
				$w1=$w2="";
			};
			// vaata, et üks liige 2x samasse listi ei läheks
			// delete on sellepärast, et selle funciga saaks liikme gruppi listis vahetada
			$this->db_query("DELETE FROM ml_list2member WHERE lid = '$lid' AND mid = '$mid'");
			
			$ret=$this->db_query("INSERT INTO ml_list2member (lid,mid $w1) VALUES ('$lid','$mid' $w2)");
			// vaata ruule
			if (!$arr["__norules"])
			{
				if (!isset($this->mlrule))
				{
					classload("ml_rule");
					$this->mlrule=new ml_rule();
				};
				$this->mlrule->check_inlist("$lid:".(int)$grp,array($mid));
			};
			return $ret;
		}

		////
		//! Annab kõik listi liikmed
		// tagastab array mid => name
		function get_members_in_list($arr)
		{
			extract($arr);
			$lid=(int)$lid;
			$this->db_query("SELECT ml_list2member.mid,objects.name AS name FROM ml_list2member,objects WHERE ml_list2member.lid='$lid' AND objects.oid=ml_list2member.mid AND objects.status != '0'");
			$ret=array();
			while ($r = $this->db_next())
			{
				$ret[$r["mid"]]=$r["name"];
			};
			return $ret;
		}

		////
		//! Tagastab kõik listid, kus liige on
		// tagastab array lid => name
		function get_lists_of_member($arr)
		{
			extract($arr);
			$mid=(int)$mid;
			$this->db_query("SELECT ml_list2member.lid,objects.name AS name FROM ml_list2member,objects WHERE ml_list2member.mid='$mid' AND objects.oid=ml_list2member.lid AND objects.status != '0'");
			$ret=array();
			while ($r = $this->db_next())
			{
				$ret[$r["lid"]]=$r["name"];
			};
			return $ret;
		}

		////
		//! Annab kõik listi grupid
		// tagastab array lgroup => name
		function get_list_groups($id,$xmlmdata=0)
		{
			$args=array("key" => "groups");
			if (is_string($xmlmdata))
			{
				$args["metadata"]=$xmlmdata;
			} else
			{
				$args["oid"] = $id;
			};
			$arr=$this->get_object_metadata($args);
			// kustutatud gruppe ei näita
			if (is_array($arr))
			{
				foreach ($arr as $k => $v)
				{
					if ($v=="Ÿ#")
					{
						unset($arr[$k]);
					};
				};
			} else
			{
				$arr=array();
			};
			return $arr;
		}

		////
		//! Lisab listi grupi
		function add_list_group($id,$name)
		{
			$old=$this->get_object_metadata(array("oid" => $id ,"key" => "groups"));
			if (!is_array($old))
			{
				$old=array(0=>"&nbsp;");// 0 tähistab kogu listi
			};
			$old[]=$name;
			$this->set_object_metadata(array("oid" => $id, "key" => "groups", "value" => $old));
			return sizeof($old)-1;
		}

		////
		//! Muudab listi grupi nime
		// gname on uus nimi
		function ren_list_group($id,$gid,$gname)
		{
			$old=$this->get_object_metadata(array("oid" => $id ,"key" => "groups"));
			//echo("id=$id lgroup=$lgroup gname=$gname<pre>");print_r($old);//dbg
			if (!is_array($old))
			{
				$old=array(0=>"&nbsp;");// 0 tähistab kogu listi
			};
			$old[$gid]=$gname;
			//print_r($old);echo("</pre>");//dbg
			$this->set_object_metadata(array("oid" => $id, "key" => "groups", "value" => $old));
		}

		////
		//! Kustutab listi grupi
		function del_list_group($id,$gid)
		{
			$id=(int)$id;
			$gid=(int)$gid;
			$old=$this->get_object_metadata(array("oid" => $id ,"key" => "groups"));
			if (!is_array($old))
			{
				return;
			};
			// siit vahelt ei tohi midagi ära võtta, kuna siis läheb järjekord segamini (vist)
			$old[$gid]="Ÿ#";
			$this->set_object_metadata(array("oid" => $id, "key" => "groups", "value" => $old));
			$this->db_query("UPDATE ml_list2member SET lgroup='0' WHERE lid='$id' AND lgroup='$gid'");
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
		function get_all_varnames()
		{
			$fb=new form_base();
			
			return $fb->get_elements_for_forms(array($this->formid));
		}

		// --------------------------------------------------------------------
		// messengerist saatmise osa

		////
		//! Messenger kutsub välja kui on valitud liste targetiteks
		// vajab targets ja id
		function route_post_message($args = array())
		{
			extract($args);
			//echo("in ml_list::route_post_message<pre>");//dbg
			//print_r($args);
			//echo("</pre>");

			$url=$this->mk_my_orb("post_message",array("id" => $id, "targets" => $targets),"",1);
			//echo("redirect to=$url");//dbg
			return $url;
		}

		////
		//! saadab teate $id listidesse $targets(array stringidest :listinimi:grupinimi)
		function post_message($args)
		{
			extract($args);

			$GLOBALS["site_title"] = "<a href='".$GLOBALS["route_back"]."'>Tagasi</a>&nbsp;/&nbsp;Saada teade";

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

			$id=(int)$id;//teate id

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
			$q="SELECT name,oid,metadata FROM objects WHERE class_id=".CL_ML_LIST." AND NAME IN($_names)";
			unset($names);
			unset($_names);
			$this->db_query($q);
			while ($listdta = $this->db_next())
			{
				$listdata[$listdta["name"]]=array(
					"id" => $listdta["oid"],
					"groups" => array_flip($this->get_list_groups(0,$listdta["metadata"]))
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
					"lists" => serialize($lists),
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
			//echo(stripslashes($lists));//dbg
			$lists=unserialize(stripslashes($lists));
			//echo("<pre>");//dbg
			//print_r($lists);//dbg
			// siin tekib nüüd see küsimus, et kas valitud list tähendab kogu listi või siis ainult neid liikmeid,
			// millele ei ole täpsustavat gruppi määtatud. praegu on nii, et tähendab kogu listi

			// kui on veel valitud täpsustavaid gruppe ka, siis 0 muudetakse eelmise funktsiooni poolt
			// ülejäänud gruppideks ehk kokkuvõttes saadetakse kogu grupile meil, ainut osale gruppidele on eritingimused

			unset($aid);
			$total=0;
			foreach($lists as $lid => $v)
			{
				foreach($v["c"] as $gid => $gname)
				{
					$key="$lid:$gid";
					//echo($lid." -".$v["name"].":$gid- $gname key=$key<br>");//dbg
					$_start_at=mktime($start_at[$key]["hour"],$start_at[$key]["minute"],0,$start_at[$key]["month"],$start_at[$key]["day"],$start_at[$key]["year"]);
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

					$count=$this->db_fetch_field("SELECT COUNT(*) AS count FROM ml_list2member,objects WHERE lid = '$lid' $lgroup AND ml_list2member.mid=objects.oid AND objects.status != '0'","count");
					//echo("count=$count<br><br>");//dbg

					if (!isset($aid))
					{
						// tee sisestus avoidmids tabelisse
						$this->db_query("INSERT INTO ml_avoidmids (avoidmids) VALUES ('')");
						$aid=$this->db_last_insert_id();
					};
					$total++;
					$this->db_query("INSERT INTO ml_queue (lid,mid,gid,uid,aid,status,start_at,last_sent,patch_size,delay,position,total)
						VALUES ('$lid','$id','$gid','".$GLOBALS["uid"]."','$aid','0','$_start_at','0','$_patch_size','$_delay','0','$count')");
					
					$this->_log("mlist","saatis meili $id listi ".$v["name"].":$gname");
				};
			};
				
			$this->db_query("UPDATE ml_avoidmids SET usagec='$total' WHERE aid='$aid'");

			return $GLOBALS["route_back"];
		}
	};
?>
