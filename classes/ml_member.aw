<?php

	global $orb_defs;
	$orb_defs["ml_member"] ="xml";

	classload("config","form_base","ml_list");
	class ml_member extends aw_template
	{
		////
		//! 
		function ml_member()
		{
			$this->tpl_init("automatweb/mlist");
			$this->db_init();
			lc_load("definition");

			$this->dbconf=new db_config();
			$this->formid=$this->dbconf->get_simple_config("ml_form");
		}


		////
		//! Näitab uue liikme lisamist
		function orb_new($arr)
		{
			is_array($arr)? extract($arr) : $parent=$arr;

			$this->mk_path($parent,"Lisa meililisti liige");
			$f=new form();
			$fparse=$f->gen_preview(array(
				"id" => $this->formid,
				"reforb" => $this->mk_reforb("submit_new",array(
					"parent" => $parent,
					"id" => $this->formid))
				));
			
			return $fparse;
		}

		////
		//! Händleb uue liikme lisamist
		function orb_submit_new($arr)
		{
			extract($arr);

			$arr["parent"]=$parent;
			$arr["redirect_after"]="boo";
			$f=new form();
			$f->process_entry($arr);
			// siit läheb veidi ümber nurga et saaks obj tüübiks CL_ML_MEMBER
			$entry_id=$f->entry_id;
			$this->db_query("UPDATE objects set class_id=".CL_ML_MEMBER." where oid='$entry_id'");

			$this->_log("mlist","lisas liikme $f->entry_name");
			return $this->mk_my_orb("change",array("id" => $entry_id, "parent" => $parent));
		}

		////
		//! Händleb muutmist
		function orb_submit_change($arr)
		{
			$this->quote(&$arr);
			extract($arr);
			$arr["redirect_after"]="boo";
			$f=new form();
			$f->process_entry($arr);

			$this->_log("mlist","muutis liiget $f->entry_name");
			return $this->mk_my_orb("change",array("id" => $entry_id,"parent" => $parent, "lid" => $lid));
		}

		////
		//! Händleb liikme omaduste vaatamises "vali listid kus liige on" muutusi
		// $id=liikme id
		function orb_submit_change_lists($arr)
		{
			$this->quote(&$arr);
			extract($arr);
			$ml=new ml_list();
			
			$mname = $this->db_fetch_field("SELECT name FROM objects WHERE oid = $id","name");

			if (!is_array($lists))
			{
				$flists=$lists=array();
			} else
			{
				$flists=array_flip($lists);
			};

			$oldlists=$ml->get_lists_of_member(array("mid" => $id));

			// leia erinevus vana ja uue nimekirja vahel
			
//			echo("<pre>");print_r($oldlists);echo("</pre>");//dbg
			foreach ($oldlists as $k => $v)
			{
				if (!isset($flists[$k]))
				{
					if ($this->can("delete_users",$k))
					{
						$name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = $k","name");
						$this->_log("mlist","eemaldas liikme $mname listist $name");
						$ml->remove_member_from_list(array("mid" => $id,"lid" => $k));
					};
				};
			};
			
//			echo("<pre>");print_r($flists);echo("</pre>");//dbg
			foreach ($flists as $k => $v)
			{
				if (!isset($oldlists[$k]))
				{
					if ($this->can("add_users",$k))
					{
						$name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = $k","name");
						$this->_log("mlist","lisas liikme $mname listi $name");
						$ml->add_member_to_list(array("mid" => $id,"lid" => $k));
					};
				};
			};

			return $this->mk_my_orb("change",array("id" => $id, "lid" => $lid));
		}

		////
		//! Näitab liikme muutmist
		function orb_change($ar)
		{
			is_array($ar) ? extract($ar) : $id=$ar;
			
			// kui tuldi listi juurest siis näita teed listi folderini
			$o=$this->get_object($id);
			$ml=new ml_list();
			if ($lid)
			{
				$this->mk_path($o["parent"],$ml->_get_lf_path($lid)."Muuda meililisti liiget");
			} else
			{
				$this->mk_path($o["parent"],"Muuda meililisti liiget");				
			};

			$f=new form();
			$fparse=$f->gen_preview(array(
				"id" => $this->formid,
				"entry_id" => $id,
				"reforb" => $this->mk_reforb("submit_change",array(
					"parent" => $parent,
					"id" => $this->formid,
					"lid" => $lid))
				));

			$this->read_template("member_change.tpl");

			
			$sellists=$ml->get_lists_of_member(array("mid" => $id));
//			echo("<pre>");print_r($sellists);echo("</pre>");//dbg

			$alllists=$ml->get_all_lists();



			$this->vars(array(
				"editform" => $fparse,
				"listsel" => $this->multiple_option_list($sellists,$alllists),
				"l_sent" => $this->mk_my_orb("sent",array("id" => $id,"lid" => $lid)),
				"reforb" => $this->mk_reforb("submit_change_lists",array(
					"parent" => $parent,
					"id" => $id,
					"lid" => $lid))
			));

			return $this->parse();
		}

		////
		//! Näitab liikmele saadetud meile
		function orb_sent($arr)
		{
			extract($arr);
			$o=$this->get_object($id);
			$ml=new ml_list();
			$link="<a href=\"".$this->mk_my_orb("change",array("id" => $id,"lid" => $lid))."\">Muuda meililisti liiget</a> / Saadetud meilid";

			if ($lid)
			{
				$this->mk_path($o["parent"],$ml->_get_lf_path($lid).$link);
			} else
			{
				$this->mk_path($o["parent"],$link);
			};

			load_vcl("table");
			global $PHP_SELF;
			
			$t = new aw_table(array(
				"prefix" => "ml_member",
				"self" => $PHP_SELF,
				"imgurl" => $baseurl . "/automatweb/images",
			));
			
			$t->set_header_attribs(array(
				"class" => "ml_member",
				"action" => "sent",
				"lid" => $lid
			));

			$t->define_header("Saadetud meilid",array());
			$t->parse_xml_def($this->basedir . "/xml/mlist/sentmails.xml");

			$q="SELECT * FROM ml_sent_mails WHERE member='$id'";
			$this->db_query($q);

			while ($row = $this->db_next())
			{
				$this->save_handle();
				
				$this->save_handle();
				$row["eid"]=$row["id"];
				$row["mail"] = $this->db_fetch_field("SELECT name FROM objects WHERE oid='".$row["mail"]."'","name")."(".$row["mail"].")";
				$this->restore_handle();
				$t->define_data($row);
			};

			if ($sortby)
			{
				$t->sort_by(array("field"=>$sortby));
			} else
			{
				$t->sort_by(array());
			};

			return $t->draw();
		}
	
		////
		//! Näitab täpsemalt ühte liikmele saadetud meili $id
		function orb_sent_show($arr)
		{
			extract($arr);
			$this->read_template("sent_show.tpl");
			
			$q="SELECT * FROM ml_sent_mails WHERE id='$id'";
			$this->db_query($q);

			$r=$this->db_next();

			//	id,mail,member,uid,tm,vars,message,subject,mailfrom
			$r["tm"]=$this->time2date($r["tm"],2);
			$r["message"]=str_replace("\n","<br>",$r["message"]);
			
			$this->vars($r);
			return $this->parse();
		}

		////
		//! Kustutab liikmele saadetud meili logi tablast
		function orb_sent_delete($arr)
		{
			extract($arr);
			$this->db_query("DELETE FROM ml_sent_mails WHERE id='$id'");
			return "<script language='javascript'>opener.history.go(0);window.close();</script>";
		}

		////
		//! Hmm??
		// miks menuedit.aw ei kutsu orb funktsiooni objekti kustutamisel??
		function orb_delete($ar)
		{
			is_array($ar) ? extract($ar) : $id=$ar;

			$name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = $id","name");
			$this->delete_object($id);

			$this->db_query("DELETE FROM form_".$this->formid."_entries where id='$id'");
			
			$ml = new ml_list();
			$ml->remove_member_from_list(array("lid" => $id));
	
			if (!$ar["_inner_call"])		
			{
				$this->_log("mlist","kustutas liikme $name");
				$url= $this->mk_my_orb("obj_list",array("parent" => $parent),"menuedit");
				header("Location:$url");
			};
		}


	};
?>
