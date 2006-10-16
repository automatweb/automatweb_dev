<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/bug_o_matic_3000/bug_tracker.aw,v 1.85 2006/10/16 10:33:26 kristo Exp $
// $Header: /home/cvs/automatweb_dev/classes/applications/bug_o_matic_3000/bug_tracker.aw,v 1.85 2006/10/16 10:33:26 kristo Exp $

// bug_tracker.aw - BugTrack 

define("MENU_ITEM_LENGTH", 20);
define("BUG_STATUS_CLOSED", 5);

/*

@classinfo syslog_type=ST_BUG_TRACKER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@property object_type type=relpicker reltype=RELTYPE_OBJECT_TYPE table=objects field=meta method=serialize
	@caption Bugi objekti t&uuml;&uuml;p

	@property bug_folder type=relpicker reltype=RELTYPE_FOLDER table=objects field=meta method=serialize
	@caption Bugide kataloog

	@property bug_by_class_parent type=relpicker reltype=RELTYPE_BUG table=objects field=meta method=serialize
	@caption Klasside puusse lisatud bugide asukoht


@default group=by_default,by_project,by_who,by_class,by_cust,by_monitor

	@property bug_tb type=toolbar no_caption=1 group=bugs,by_default,by_project,by_who,by_class

	@property cat type=hidden store=no

	@layout bug type=hbox width=15%:85%

		@layout bug_tree type=vbox parent=bug closeable=1 area_caption=Arendus&uuml;lesanded

			@property bug_tree type=treeview parent=bug_tree no_caption=1 

		@layout bug_table type=vbox parent=bug closeable=1

			@property bug_list type=text parent=bug_table no_caption=1 group=by_monitor,bugs,archive,by_default,by_project,by_who,by_class,by_cust


@default group=unestimated_bugs

	@property unset_p type=text store=no 
	@caption Kelle buge n&auml;idata

	@property unset_table type=table store=no no_caption=1

@default group=search

	@property search_tb type=toolbar store=no no_caption=1 
	@caption Otsingu toolbar

@layout s_top_v type=hbox 

	@layout s_name_lay type=vbox closeable=1 area_caption=Sisu parent=s_top_v

		@property s_name type=textbox store=no parent=s_name_lay size=15 captionside=top
		@caption Nimi

		@property s_bug_content type=textbox store=no parent=s_name_lay captionside=top
		@caption Sisu

		@property s_find_parens type=checkbox ch_value=1 store=no parent=s_name_lay no_caption=1 captionside=top
		@caption Leia ka buge, millel on alambuge

@layout s_top type=hbox width=50%:50%

	@layout s_type_lay type=vbox closeable=1 area_caption=Klass parent=s_top

		@property s_bug_type type=textbox store=no parent=s_type_lay size=15 captionside=top
		@caption T&uuml;&uuml;p

		@property s_bug_class type=select store=no parent=s_type_lay captionside=top
		@caption Klass

		@property s_bug_component type=textbox store=no parent=s_type_lay captionside=top
		@caption Komponent

	@layout s_cut_lay type=vbox closeable=1 area_caption=Klient parent=s_top

		@property s_customer type=textbox store=no parent=s_cut_lay size=15 captionside=top
		@caption Klient

		@property s_project type=textbox store=no parent=s_cut_lay captionside=top
		@caption Projekt

		@property s_deadline type=date_select default=-1 store=no parent=s_cut_lay captionside=top
		@caption T&auml;htaeg

@layout s_bott type=hbox width=50%:50%

	@layout s_status_lay type=vbox closeable=1 area_caption=Staatus parent=s_bott

		@property s_bug_status type=select store=no multiple=1 parent=s_status_lay size=3 captionside=top
		@caption Staatus

		@property s_bug_priority type=select store=no parent=s_status_lay captionside=top
		@caption Prioriteet

		@property s_bug_severity type=select store=no parent=s_status_lay captionside=top
		@caption T&otilde;sidus

	@layout s_who_l type=vbox closeable=1 area_caption=Osalejad parent=s_bott

		@layout s_who_empty_l type=hbox parent=s_who_l

			@property s_who type=textbox store=no parent=s_who_empty_l size=15 captionside=top 
			@caption Kellele

			@property s_who_empty type=checkbox ch_value=1 store=no parent=s_who_empty_l captionside=top no_caption=1
			@caption T&uuml;hi

		@property s_monitors type=textbox store=no parent=s_who_l captionside=top
		@caption J&auml;lgijad

		@property s_bug_mail type=textbox store=no parent=s_who_l captionside=top
		@caption Bugmail CC

		@property s_createdby type=textbox store=no size=15 parent=s_who_l captionside=top
		@caption Looja


	@property s_sbt type=submit store=no no_caption=1 
	@caption Otsi
	
	@property search_res type=table store=no no_caption=1 
	@caption Otsingu tulemused

@default group=search_list

	@property saved_searches type=table store=no no_caption=1
	
	@property delete_saved type=submit 
	@caption Kustuta

@default group=charts
@default group=gantt_chart

	@property gantt_p type=text store=no 
	@caption Kelle buge n&auml;idata

	@property gantt type=text store=no no_caption=1

	@property gantt_summary type=text store=no
	@caption Kokkuv&otilde;te

@default group=my_bugs_stat

	@property my_bugs_stat_table type=table no_caption=1
	@caption Minuga seotud bugid

@default group=settings_people

	@property sp_tb type=toolbar store=no no_caption=1

	@property sp_table type=table store=no 
	@caption Valitud isikud

	@property sp_p_name type=textbox store=no
	@caption Isik

	@property sp_p_co type=textbox store=no
	@caption Organisatsioon

	@property sp_sbt type=submit
	@caption Otsi

	@property sp_s_res type=table store=no 
	@caption Otsingu tulemused

@default group=settings_g

	@property def_notify_list type=textbox table=objects field=meta method=serialize
	@caption Bugi kommentaaride CC

@groupinfo bugs caption="Arendus&uuml;lesanded" submit=no
	@groupinfo by_default caption="Arendus&uuml;lesanded" parent=bugs submit=no
	@groupinfo by_project caption="Projektid" parent=bugs submit=no
	@groupinfo by_who caption="Kellele" parent=bugs submit=no
	@groupinfo by_class caption="Klasside puu" parent=bugs submit=no
	@groupinfo by_cust caption="Kliendid" parent=bugs submit=no
	@groupinfo by_monitor caption="J&auml;lgijad" parent=bugs submit=no
	@groupinfo unestimated_bugs caption="Ennustamata bugid" parent=bugs 

@groupinfo search_t caption="Otsing" submit_method=get save=no
	@groupinfo search caption="Otsing" submit_method=get save=no parent=search_t
	@groupinfo search_list caption="Salvestatud otsingud" parent=search_t

@groupinfo archive caption="Arhiiv" submit=no
@groupinfo charts caption="Kaardid" submit=no
	@groupinfo gantt_chart caption="Gantti diagramm" parent=charts submit=no
	@groupinfo my_bugs_stat caption="Minu Bugide stat" parent=charts
@groupinfo settings caption="Seaded" submit=no
	@groupinfo settings_people caption="Isikud" submit=no parent=settings
	@groupinfo settings_g caption="Muud seaded" parent=settings


@reltype MONITOR value=1 clid=CL_CRM_PERSON
@caption J�lgija

@reltype OBJECT_TYPE value=2 clid=CL_OBJECT_TYPE
@caption Objekti t&uuml;&uuml;p

@reltype FOLDER value=3 clid=CL_MENU
@caption Kataloog

@reltype IMP_P value=4 clid=CL_CRM_PERSON
@caption Oluline isik

@reltype BUG value=5 clid=CL_BUG
@caption Bugi

*/

classload("applications/bug_o_matic_3000/bug");
class bug_tracker extends class_base
{
	function bug_tracker()
	{
		$this->init(array(
			"tpldir" => "applications/bug_o_matic_3000/bug_tracker",
			"clid" => CL_BUG_TRACKER
		));
		$this->bug_i = get_instance(CL_BUG);
	}

	function get_property($arr)
	{		
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		if($arr["request"]["group"] == "bugs")
		{
			$arr["request"]["group"] = "by_default";
		}
		switch($arr["request"]["group"])
		{
			case "by_default":
				$this->sort_type = "parent";
				aw_session_set("bug_tree_sort",array("name" => "parent"));
				break;
			case "by_project":
				aw_session_set("bug_tree_sort",array("name" => "project", "class" => CL_PROJECT, "reltype" => RELTYPE_PROJECT));
				break;
			case "by_who":
				aw_session_set("bug_tree_sort",array("name" => "who", "class" => CL_CRM_PERSON, "reltype" => RELTYPE_MONITOR));
				break;
			case "by_class":
				aw_session_set("bug_tree_sort",array("name" => "classes"));
				break;
			case "by_cust":
				aw_session_set("bug_tree_sort",array("name" => "cust"));
				break;
			case "by_monitor":
				aw_session_set("bug_tree_sort",array("name" => "monitor"));
				break;
		}

		if ($prop["name"][0] == "s" && $prop["name"][1] == "_")
		{
			$prop["value"] = $arr["request"][$prop["name"]];
		}

		switch($prop["name"])
		{
			case "unset_table":
				$this->_unestimated_table($arr);
				break;

			case "bug_tb":
				$this->_bug_toolbar($arr);
				break;

			case "bug_tree":
				$this->_bug_tree($arr);
				break;

			case "bug_list":
				$this->_bug_list($arr);
				break;

			case "cat":
				if($this->can("view", $arr["request"]["cat"]))
				{
					$prop["value"] = $arr["request"]["cat"];
				}
				break;

			case "search_res":
				$this->_search_res($arr);
				break;

			case "s_bug_priority":
			case "s_bug_severity":
				$i = get_instance(CL_BUG);
				$prop["options"] = array("" => "") + $i->get_priority_list();
				break;

			case "s_bug_status":
				$i = get_instance(CL_BUG);
				$prop["options"] = array("" => "") + $i->get_status_list();
				break;

			case "s_bug_class":
				$i = get_instance(CL_BUG);
				$prop["options"] = array("" => "") + $i->get_class_list();
				break;

			case "search_tb":
				$this->_search_tb($arr);
				break;

			case "saved_searches":
				$this->_saved_searches($arr);
				break;

			case "gantt":
				$this->_gantt($arr);
				break;

			case "gantt_p":
			case "unset_p":
				if ($this->can("view", $arr["request"]["filt_p"]))
				{
					$p = obj($arr["request"]["filt_p"]);
				}
				else
				{
					$u = get_instance(CL_USER);
					$p = obj($u->get_current_person());
				}
				$co = get_instance(CL_CRM_COMPANY);
				$c = get_instance("vcl/popup_menu");
				$c->begin_menu("bt_g");
				$ppl = $this->get_people_list($arr["obj_inst"]);
				foreach($ppl as $p_id => $p_n)
				{
					$c->add_item(array(
						"text" => $p_n,
						"link" => aw_url_change_var("filt_p", $p_id)
					));
				}
				$prop["value"] = html::obj_change_url($p)." ".$c->get_menu();
				break;

			case "gantt_summary":
				$prop["value"] = sprintf(t("T&ouml;id kokku: %s, tunde %s.<Br>Viimase t&ouml;&ouml; l&otilde;ppt&auml;htaeg %s."), 
					$this->job_count,
					$this->job_hrs / 3600,
					date("d.m.Y H:i", $this->job_end)
				);
				break;

			case "sp_tb":
				$this->_sp_tb($arr);
				break;

			case "sp_table":
				$this->_sp_table($arr);
				break;

			case "sp_s_res":
				$this->_sp_s_res($arr);
				break;
		
			case "sp_p_name":
			case "sp_p_co":
				$prop["value"] = $arr["request"][$prop["name"]];
				$prop["autocomplete_source"] = $this->mk_my_orb($prop["name"] == "sp_p_co" ? "co_autocomplete_source" : "p_autocomplete_source");
				$prop["autocomplete_params"] = array($prop["name"]);
				break;
		};
		return $retval;
	}
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "unset_table":
				$this->_save_estimates($arr);
				break;

			case "bug_list":
				foreach($arr["request"]["bug_priority"] as $bug_id => $bug_val)
				{
					if($this->can("edit",$bug_id))
					{
						$bug = obj($bug_id);
						$bug->set_prop("bug_priority",$bug_val);
						$bug->save();
					}
				}
				foreach($arr["request"]["bug_severity"] as $bug_id => $bug_val)
				{
					if($this->can("edit",$bug_id))
					{
						$bug = obj($bug_id);
						$bug->set_prop("bug_severity",$bug_val);
						$bug->save();
					}
				}
				break;

			case "saved_searches":
				$ss = safe_array($arr["obj_inst"]->meta("saved_searches"));
				foreach($ss as $idx => $search)
				{
					if (isset($arr["request"]["sel"][$idx]))
					{
						unset($ss[$idx]);
					}
				}
				$arr["obj_inst"]->set_meta("saved_searches", $ss);
				break;
		}
		return $retval;
	}	

	function _get_my_bugs_stat_table($arr)
	{
		classload("core/date/date_calc");

		$t = &$arr['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'name',
			'caption' => t('Nimi')
		));
		$t->define_field(array(
			'name' => 'comment_count',
			'caption' => t('Kommentaaride arv'),
			'width' => '10%',
			'align' => 'center'
		));
		$t->define_field(array(
			'name' => 'working_hours',
			'caption' => t('T&ouml;&ouml;tunnid'),
			'width' => '10%',
			'align' => 'center'
		));

		$bug_comments = new object_list(array(
			"class_id" => CL_BUG_COMMENT,
			"lang_id" => array(),
			"site_id" => array(),
			"created" => new obj_predicate_compare(OBJ_COMP_GREATER, get_week_start()/*-7*3600*24*/),
			"sort_by" => "objects.createdby, objects.created"
		));

		$uid = aw_global_get('uid');
		$bugs = array();
		foreach ($bug_comments->arr() as $id => $bug_comment)
		{
			if ($bug_comment->createdby() == '')
			{
				$text = $bug_comment->comment();
				if (preg_match("/cvs commit by ([^ ]+) in/imsU", $text, $mt))
				{
					if ($uid == $mt[1])
					{
						$bugs[$bug_comment->parent()][$id] = $bug_comment;
					}
				}

			}
			else
			{
				if ($uid == $bug_comment->createdby())
				{
					$bugs[$bug_comment->parent()][$id] = $bug_comment;
				}
			}
		}

		foreach ($bugs as $bug_id => $comments)
		{
			$bug = new object($bug_id);
			$working_hours = 0;
			foreach ($comments as $comment_id => $comment)
			{
				$working_hours += $comment->prop('add_wh');
			}
			$t->define_data(array(
				'name' => html::href(array(
					'url' => $this->mk_my_orb('change', array(
						'id' => $bug_id,
						'return_url' => get_ru()
					), CL_BUG),
					'caption' => $bug->name()
				)),
				'comment_count' => count($comments),
				'working_hours' => $working_hours
			));
			$sum+=$working_hours;
		}

		$t->sort_by();
		$t->set_sortable(false);
		$t->define_data(array(
			"name" => t("Summa"),
			"working_hours" => $sum
		));
		return PROP_OK;
	}

	function _bug_toolbar($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		if ($arr["request"]["group"] == "by_class")
		{
			$pt = $arr["obj_inst"]->prop("bug_by_class_parent");
		}
		else
		{
			$pt = !empty($arr["request"]["b_id"]) ? $arr["request"]["b_id"] : $this->get_bugs_parent($arr["obj_inst"]);
		}

		$tb->add_button(array(
			"name" => "add_bug",
			"tooltip" => t("Lisa"),
			"url" => html::get_new_url(CL_BUG, $pt, array(
				"return_url" => get_ru(),
			)),
			"href_id" => "add_bug_href",
			"img" => "new.gif",
		));

		$tb->add_button(array(
			"name" => "save",
			"tooltip" => t("Salvesta"),
			"action" => "",
			"img" => "save.gif",
		));
		$tb->add_button(array(
			"name" => "delete",
			"tooltip" => t("Kustuta"),
			"img" => "delete.gif",
			"action" => "delete",
			"confirm" => t("Oled kindel, et soovid bugi kustutada?"),
		));

		$base = $this->mk_my_orb("cut_b");

		$cut_js = "
			url = '$base';
			len = document.changeform.elements.length;
			cnt = 0;
			for(i = 0; i < len; i++)
			{
				if (document.changeform.elements[i].name.indexOf('sel') != -1 && document.changeform.elements[i].checked)
				{
					url += '&sel[]='+document.changeform.elements[i].value;
					document.changeform.elements[i].checked=false;
					cnt++;
				}
			}

			if (cnt > 0)
			{
				aw_get_url_contents(url);
				//window.location=url;
				paste_button = document.getElementById('paste_button');
				paste_button.style.visibility='visible';
			}
			else
			{
				paste_button = document.getElementById('paste_button');
				paste_button.style.visibility='hidden';
			}
			return false;
		";

		$tb->add_button(array(
			"name" => "cut",
			"tooltip" => t("L&otilde;ika"),
			"img" => "cut.gif",
//			"onClick" => $cut_js,
			"action" => "cut_b",
		));

		$vis = "hidden;";
		if (is_array($_SESSION["bt"]["cut_bugs"]) && count($_SESSION["bt"]["cut_bugs"]))
		{
			$vis = "visible;";
			$tb->add_button(array(
				"name" => "paste",
				"tooltip" => t("Kleebi"),
				"img" => "paste.gif",
				"action" => "paste_b",
//			"surround_start" => "<span id='paste_button' style='visibility: $vis;'>",
//			"surround_end" => "</span>"
			));
		}
		if ($vis == "visible")
		{
		}

		$tb->add_separator();

		$tb->add_menu_button(array(
			"name" => "assign",
			"tooltip" => t("M&auml;&auml;ra"),
			"img" => "class_38.gif"
		));

		// list all people to assign to
		// list all my co-workers who are important to me, from crm
		$ppl = $this->get_people_list($arr["obj_inst"]);
		foreach($ppl as $p_oid => $p_name)
		{
			$tb->add_menu_item(array(
				"parent" => "assign",
				"text" => $p_name,
				"link" => "#",
				"onClick" => "document.changeform.assign_to.value=$p_oid;submit_changeform('assign_bugs')"
			));
		}

		$tb->add_menu_button(array(
			"name" => "set_status",
			"tooltip" => t("Staatus"),
			"img" => "class_".CL_BUG.".gif"
		));

		// list all people to assign to
		// list all my co-workers who are important to me, from crm
		$dat = get_instance(CL_BUG);
		$ppl = $dat->get_status_list();
		foreach($ppl as $p_oid => $p_name)
		{
			$tb->add_menu_item(array(
				"parent" => "set_status",
				"text" => $p_name,
				"link" => "#",
				"onClick" => "document.changeform.assign_to.value=$p_oid;submit_changeform('set_bug_status')"
			));
		}

		$tb->add_cdata(html::href(array(
			"url" => get_ru(),
			"caption" => t("Bookmarkimise url"),
			"id" => "sync_url"
		)));
	}

	/**
		@attrib name=get_node_cust all_args=1
	**/
	function get_node_cust($arr)
	{	
	    classload("core/icons");
		$node_tree = get_instance("vcl/treeview");
		$node_tree->start_tree (array (
			"type" => TREE_DHTML,
			"tree_id" => "bug_tree",
			"branch" => 1,
		));

				$node_tree->add_item(($dat["parent"] && $dat["parent"] != $pt ? "fld_".$dat["parent"] : 0), array(
					"id" => "fld_".$id,
					"name" => $nm,
					"iconurl" => icons::get_icon_url(CL_MENU),
					"url" => html::get_change_url( $arr["inst_id"], array(
						"id" => $this->self_id,
						"group" => $arr["active_group"],
						"p_fld_id" => $id,
						"p_cls_id" => null
					)),
				));

		die($node_tree->finalize_tree());
	}

	/**
		@attrib name=get_node_class all_args=1
	**/
	function get_node_class($arr)
	{	
	    classload("core/icons");
		$node_tree = get_instance("vcl/treeview");
		$node_tree->start_tree (array (
			"type" => TREE_DHTML,
			"tree_id" => "bug_tree",
			"branch" => 1,
		));

		$f = aw_ini_get("classfolders");
		if (is_oid($arr["parent"]))
		{
			$pt = 0;
		}
		else
		{
			list(,$pt) = explode("_", $arr["parent"]);
		}

		foreach($f as $id => $dat)
		{
			if ($dat["parent"] == $pt || $f[$dat["parent"]]["parent"] == $pt)
			{
				$nm = $this->name_cut($dat["name"]);
				if ($_GET["p_fld_id"] == $id)
				{
					$nm = "<b>".$nm."</b>";
				}
				$node_tree->add_item(($dat["parent"] && $dat["parent"] != $pt ? "fld_".$dat["parent"] : 0), array(
					"id" => "fld_".$id,
					"name" => $nm,
					"iconurl" => icons::get_icon_url(CL_MENU),
					"url" => html::get_change_url( $arr["inst_id"], array(
						"id" => $this->self_id,
						"group" => $arr["active_group"],
						"p_fld_id" => $id,
						"p_cls_id" => null
					)),
					"alt" => $dat["name"]
				));

				$c = aw_ini_get("classes");
				foreach($c as $clid => $dat)
				{
					$parents = explode(",", $dat["parents"]);
					foreach($parents as $parent)
					{
						if ($parent == $id)
						{
							$nm = $this->name_cut($dat["name"]);
							if ($_GET["p_cls_id"] == $clid)
							{
								$nm = "<b>".$nm."</b>";
							}
							$node_tree->add_item("fld_".$id, array(
								"id" => "cls_".$clid,
								"name" => $nm,
								"iconurl" => icons::get_icon_url(CL_OBJECT_TYPE),
								"url" => html::get_change_url( $arr["inst_id"], array(
									"id" => $this->self_id,
									"group" => $arr["active_group"],
									"p_cls_id" => $clid,
									"p_fld_id" => null
								)),
								"alt" => $dat["name"]
							));
						}
					}
				}
			}
		}

		$c = aw_ini_get("classes");
		foreach($c as $clid => $dat)
		{
			$parents = explode(",", $dat["parents"]);
			foreach($parents as $parent)
			{
				if ($parent == $pt)
				{
					$nm = $this->name_cut($dat["name"]);
					if ($_GET["p_cls_id"] == $clid)
					{
						$nm = "<b>".$nm."</b>";
					}
					$node_tree->add_item(0, array(
						"id" => "cls_".$clid,
						"name" => $nm,
						"iconurl" => icons::get_icon_url(CL_OBJECT_TYPE),
						"url" => html::get_change_url( $arr["inst_id"], array(
							"id" => $this->self_id,
							"group" => $arr["active_group"],
							"p_cls_id" => $clid,
							"p_fld_id" => null
						)),
						"alt" => $dat["name"]
					));
				}
			}
		}

		die($node_tree->finalize_tree());
	}

	function get_node_by_who($arr)
	{
	    classload("core/icons");
		$node_tree = get_instance("vcl/treeview");
		$node_tree->start_tree (array (
			"type" => TREE_DHTML,
			"tree_id" => "bug_tree",
			"branch" => 1,
		));
	    
		$obj = new object($arr["parent"]);
		if($obj->class_id() == CL_BUG_TRACKER)
		{
			// list all persons that have bugs
			$c = new connection();
			$ppl = array();
			foreach($c->find(array("from.class_id" => CL_BUG, "to.class_id" => CL_CRM_PERSON)) as $pc)
			{
				$ppl[$pc["to"]] = $pc["to"];
			}

			foreach($ppl as $p_id)
			{
				$p_o = obj($p_id);
				$sn = $p_o->name();
				if ($arr["p_id"] == $p_id)
				{
					$sn = "<b>".$sn."</b>";
				}
				$node_tree->add_item(0, array(
					"id" => $p_id,
					"name" => $sn,
					"iconurl" => icons::get_icon_url(CL_CRM_PERSON),
					"url" => html::get_change_url(
						$arr["inst_id"],
						array(
							"group" => "by_who",
							"b_stat" => null,
							"p_id" => $p_id
						)
					)
				));
				$node_tree->add_item($p_id, array("id" => "stat_".$p_id, "name" => "a"));
			}
		}
		else
		{
			// list statuses
			$bugi = get_instance(CL_BUG);
			foreach($bugi->get_status_list() as $sid => $sn)
			{
				if ($arr["b_stat"] == $sid)
				{
					$sn = "<b>".$sn."</b>";
				}
				$node_tree->add_item(0, array(
					"id" => $arr["parent"]."_".$sid,
					"name" => $sn,
					"url" => html::get_change_url(
						$arr["inst_id"],
						array(
							"group" => "by_who",
							"b_stat" => $sid,
							"p_id" => $arr["parent"]
						)
					)
				));
			}
		}
		die($node_tree->finalize_tree());
	}

	/** to get subtree for who & projects view
	    @attrib name=get_node_other all_args=1
	**/
	function get_node_other($arr)
	{
		if ($arr["active_group"] == "by_who")
		{
			$this->get_node_by_who($arr);
		}
	    classload("core/icons");
		$node_tree = get_instance("vcl/treeview");
		$node_tree->start_tree (array (
			"type" => TREE_DHTML,
			"tree_id" => "bug_tree",
			"branch" => 1,
		));
	    
		$obj = new object($arr["parent"]);
		if($obj->class_id() == CL_BUG_TRACKER)
		{
			$ol = new object_list(array("class_id" => CL_BUG, "bug_status" => new obj_predicate_not(BUG_STATUS_CLOSED)));
			$c = new connection();
			$bug2proj = $c->find(array("from.class_id" => CL_BUG, "to.class_id" => $arr["clid"], "type" => $arr["reltype"], "from" => $ol->ids()));
			foreach($bug2proj as $conn)
			{
				$to[] = $conn["to"];
				$bugs[] = $conn["from"];
				$bug_count[$conn["to"]]++;
			}

			$buglist = new object_list(array(
				"class_id" => CL_BUG,
				"lang_id" => array(),
				"site_id" => array(),
				"oid" => $bugs,
				"bug_status" => new obj_predicate_not(5)
			));

			if ($arr["reltype"] == 1 || $arr["active_group"] == "by_who" || $arr["active_group"] == "by_project")
			{
				$bug_count = array();
				$bugs = array();
				$bug_data = $buglist->arr();
				$prop = $arr["active_group"] == "by_who" ? "who" : "project";
				foreach($bug_data as $bug_obj)
				{
					if ($bug_obj->prop("bug_status") != 5)
					{
						$bug_count[$bug_obj->prop($prop)]++;
					}
				}
			}
			$to_unique = array_unique($to);
			
			foreach($to_unique as $project)
			{
				$obj = new object($project);
				$node_tree->add_item(0, array(
					"id" => $obj->id(),
					"name" => $this->name_cut($obj->name())." (".(int)$bug_count[$project].")",
					"iconurl" => icons::get_icon_url($obj->class_id()),
					"url" => html::get_change_url( $arr["inst_id"], array(
						"id" => $this->self_id,
						"group" => $arr["active_group"],
						"p_id" => $obj->id(),
					)),
					"alt" => $obj->name()
				));
			}
			if ($arr["reltype"] == 1)
			{
				foreach($bug_data as $sub_obj)
				{
					if (!$this->can("view", $sub_obj->prop("who")))
					{
						continue;
					}
					$node_tree->add_item($sub_obj->prop("who") , array(
						"id" => $sub_obj->id(),
						"name" => $sub_obj->name(),
					));
				}
			}
			else
			{
				foreach($bugs as $key => $bug)
				{
					$sub_obj =  new object($bug);
					$node_tree->add_item($to[$key] , array(
						"id" => $sub_obj->id(),
						"name" => $sub_obj->name(),
					));
				}
			}
		}
		else
		{
			if($obj->class_id() == CL_PROJECT)
			{
				$filter = "project";
			}
			elseif($obj->class_id() == CL_CRM_PERSON)
			{
				$filter = "who";
			}
			else
			{
				$filter = "parent";
			}

			$filt = array(
				$filter  => $obj->id(),
				"class_id" => CL_BUG,
				"lang_id" => array(),
				"site_id" => array(),
				"bug_status" => new obj_predicate_not(BUG_STATUS_CLOSED)
			);
			$ol = new object_list($filt);
			$objects = $ol->arr();
			foreach($objects as $obj_id => $object)
			{
				$ol = new object_list(array(
					"parent" => $obj_id, 
					"class_id" => CL_BUG,
					"lang_id" => array(),
					"site_id" => array(),
					/*, "bug_status" => new obj_predicate_not(5)*/));
				$ol_list = $ol->arr();

				$node_tree->add_item(0 ,array(
					"id" => $obj_id,
					"name" => $this->name_cut($object->name()).(count($ol_list)?" (".count($ol_list).")":""),
					"iconurl" => icons::get_icon_url($object->class_id()),
					"url" => html::get_change_url($arr["inst_id"], array(
						"group" => $arr["active_group"],
						"b_id" => $obj_id,
					)),
					"alt" => $object->name()
				));
				foreach($ol_list as $sub_id => $sub_obj)
				{
					$node_tree->add_item( $obj_id, array(
						"id" => $sub_id,
						"name" => $sub_obj->name(),
					));
				}
			}
		}
		die($node_tree->finalize_tree());
	}
	
	/**  to get subtree for default view
		@attrib name=get_node all_args=1

	**/
	function get_node($arr)
	{
		classload("core/icons");
		$node_tree = get_instance("vcl/treeview");
		$node_tree->start_tree (array (
			"type" => TREE_DHTML,
			"tree_id" => "bug_tree",
			"branch" => 1,
		));

		$ol = new object_list(array(
			"parent" => $arr["parent"], 
			"class_id" => array(CL_BUG, CL_MENU), 
			"bug_status" => new obj_predicate_not(BUG_STATUS_CLOSED),
			"sort_by" => "objects.name"
		));

		$arr["set_retu"] = aw_url_change_var("b_id", $arr["parent"], $arr["set_retu"]);

		$objects = $ol->arr();
		foreach($objects as $obj_id => $object)
		{
			$ol = new object_list(array("parent" => $obj_id, "class_id" => array(CL_BUG, CL_MENU), "bug_status" => new obj_predicate_not(BUG_STATUS_CLOSED),));
			$ol_list = $ol->arr();
			$subtree_count = (count($ol_list) > 0)?" (".count($ol_list).")":"";

			$nm = $this->name_cut($object->name()).$subtree_count;
			if (false && $_GET["b_id"] == $obj_id)
			{
				$nm = "<b>".$nm."</b>";
			}

			$node_tree->add_item(0 ,array(
				"id" => $obj_id,
				"name" => $nm."  (".html::get_change_url($obj_id, array("return_url" => $arr["set_retu"]), t("<span style='font-size: 8px;'>Muuda</span>")).")",
				"iconurl" => icons::get_icon_url($object->class_id()),
				"url" => html::get_change_url($arr["inst_id"], array(
					"group" => $arr["active_group"],
					"b_id" => $obj_id,
				)),
				"onClick" => "do_bt_table_switch($obj_id, this);return false;",
				"alt" => $object->name()
			));

			foreach($ol_list as $sub_id => $sub_obj)
			{
				$node_tree->add_item( $obj_id, array(
					"id" => $sub_id,
					"name" => $sub_obj->name()." (".html::get_change_url($sub_id, array("return_url" => $arr["set_retu"]), t("<span style='font-size: 8px;'>Muuda</span>")).")",
					"onClick" => "do_bt_table_switch($sub_id, this);return false;"
				));
			}
		}

		die($node_tree->finalize_tree());
	}

	function _bug_tree($arr)
	{
		classload("core/icons");
		$this->tree = get_instance("vcl/treeview");
		$this->active_group = $arr["request"]["group"];
		$this->sort_type = aw_global_get("bug_tree_sort");	
		$this->self_id = $arr["obj_inst"]->id();
		$this->tree_root_name = "Bug-Tracker";
		switch($this->sort_type["name"])
		{
			case "classes":
				$orb_function = "get_node_class";
				$tid = "_cls";
				break;

			case "cust":
				// add customers to the tree
				$this->_add_custs_to_tree($arr["prop"]["vcl_inst"]);
				return;

			case "project":
				$tid = "_prj";
			case "who":
				$tid = "_who";
				$orb_function = "get_node_other";
				break;

			case "monitor":
				$tid = "_monitor";
				$orb_function = "get_node_monitor";
				break;

			default:
				$tid = "_def";
				$orb_function = "get_node";
				break;
		}

		$root_name = array(
			"by_default" => t("Tavaline"), 
			"by_project"=> t("Projektid"), 
			"by_who" => t("Teostajad"),
			"by_class" => t("Klassid"),
			"by_cust" => t("Kliendid"),
			"by_monitor" => t("J&auml;lgijad"),
		);

		$this->tree->start_tree(array(
			"type" => TREE_DHTML,
			"has_root" => 1,
			"tree_id" => "bug_tree".$tid,
			"persist_state" => 1,
			"root_name" => $root_name[($this->active_group == "bugs")?"by_default":$this->active_group],
			"root_url" => aw_url_change_var("b_id", null),
			"get_branch_func" => $this->mk_my_orb($orb_function, array(
				"type" => $this->sort_type["name"], 
				"reltype" => $this->sort_type["reltype"], 
				"clid"=> $this->sort_type["class"], 
				"inst_id" => $this->self_id,
				"active_group" => $this->active_group,
				"b_id" => $arr["request"]["b_id"],
				"p_fld_id" => $arr["request"]["p_fld_id"],
				"p_cls_id" => $arr["request"]["p_cls_id"],
				"p_cust_id" => $arr["request"]["p_cust_id"],
				"b_mon" => $arr["request"]["b_mon"],
				"b_stat" => $arr["request"]["b_stat"],
				"set_retu" => get_ru(),
				"parent" => " ",
			)),
		));
 
		if($this->sort_type["name"] == "parent")
		{
			$this->generate_bug_tree(array(
				"parent" => $this->get_bugs_parent($arr["obj_inst"]),
			));
		}
	
		if($this->sort_type["name"] == "classes")
		{
			$this->generate_class_bug_tree(array(
				"parent" => $this->get_bugs_parent($arr["obj_inst"]),
			));
		}

		if($this->sort_type["name"] == "cust")
		{
			$this->generate_cust_bug_tree(array(
				"parent" => $this->get_bugs_parent($arr["obj_inst"]),
			));
		}

		if($this->sort_type["name"] == "monitor")
		{
			$this->generate_mon_bug_tree(array(
				"parent" => $this->get_bugs_parent($arr["obj_inst"]),
			));
		}
	
		if ($this->sort_type["name"] == "who")
		{
			$this->tree->add_item(0,array(
				"id" => $this->self_id,
				"name" => $this->tree_root_name,
			));		
		
			$this->tree->add_item($this->self_id,array(
				"id" => "allah",
				"name" => "a",
			));
		}

		if($this->sort_type["name"] == "project")
		{
			$this->gen_tree_other(array(
				"parent" => $this->self_id,
			));
		}

		$arr["prop"]["value"] = $this->tree->finalize_tree();
		$arr["prop"]["type"] = "text";

	}

	function generate_cust_bug_tree($arr)
	{
		$this->tree->add_item(0,array(
			"id" => $this->self_id,
			"name" => $this->tree_root_name,
		));		

		$i = get_instance(CL_CRM_COMPANY);
		$i->active_node = (int)$arr['request']['category'];

		$i->generate_tree(array(
			'tree_inst' => &$this->tree,
			'obj_inst' => $arr['obj_inst'],
			'node_id' => &$node_id,
			'conn_type' => 'RELTYPE_CATEGORY',
			'skip' => array(CL_CRM_COMPANY),
			'attrib' => 'category',
			'leafs' => 'false',
			'style' => 'nodetextbuttonlike',
			"edit_mode" => 1
		));
		
	/*	$f = aw_ini_get("classfolders");
		foreach($f as $id => $dat)
		{
			if (!$dat["parent"])
			{
				$this->tree->add_item($arr["parent"],array(
					"id" => "fld_".$id,
					"name" => $dat["name"],
				));
			}
		}*/
	}

	function generate_class_bug_tree($arr)
	{
		$this->tree->add_item(0,array(
			"id" => $this->self_id,
			"name" => $this->tree_root_name,
		));		
		
		$f = aw_ini_get("classfolders");
		foreach($f as $id => $dat)
		{
			if (!$dat["parent"])
			{
				$this->tree->add_item($arr["parent"],array(
					"id" => "fld_".$id,
					"name" => $dat["name"],
				));
			}
		}
	}

	function gen_tree_other($arr)
	{
		$c = new connection();
		$bug2proj = $c->find(array("from.class_id" => CL_BUG, "to.class_id" => $this->sort_type["class"], "type" => $this->sort_type["reltype"]));

		foreach($bug2proj as $conn)
		{
			$projects[] = $conn["to"];
		}
		$projects = array_unique($projects);

		$this->tree->add_item(0,array(
			"id" => $this->self_id,
			"name" => $this->tree_root_name." (".count($projects).")",
		));		
		
		foreach($projects as $project)
		{
			$obj = new object($project);
			$this->tree->add_item($arr["parent"],array(
				"id" => $obj->id(),
				"name" => $obj->name(),
			));
		}
	}

	function generate_bug_tree($arr)
	{
		$ol = new object_list(array("parent" => $arr["parent"], "class_id" => array(CL_BUG, CL_MENU), "bug_status" => new obj_predicate_not(BUG_STATUS_CLOSED),));
		$objects = $ol->arr();

		$nm = $this->tree_root_name." (".$ol->count().")";
		if (!$_GET["b_id"])
		{
			$nm = "<b>".$nm."</b>";
		}
		$this->tree->add_item(0,array(
				"id" => $this->self_id,
				"name" => $nm,
				"url" => aw_url_change_var("b_id", null)
		));
		
		foreach($objects as $obj_id => $object)
		{
			$nm = $object->name();
			if ($_GET["b_id"] == $obj_id)
			{
				$nm = "<b>".$nm."</b>";
			}
			$this->tree->add_item($arr["parent"] , array(
				"id" => $obj_id,
				"name" => $nm." ".html::get_change_url($obj_id, array("return_url" => get_ru()), t("Muuda")),
				"onClick" => "do_bt_table_switch($obj_id);return false;"
			));
		}
	}
	
	function name_cut($name)
	{
		$pre = substr($name, 0, MENU_ITEM_LENGTH);
		$suf = (strlen($name) > MENU_ITEM_LENGTH)?"...":"";
		return strip_tags($pre.$suf);
	}

	function callb_who($val)
	{
		$name = "";
		if($this->can("view", $val))
		{
			$obj = obj($val);
			$name = $obj->name();
		}
		return $name;
	}
	
	function show_priority($_param)
	{
		if ($_param["obj"]->class_id() == CL_MENU)
		{
			return "";
		}
		$return = html::textbox(array(
			"name" => "bug_priority[".$_param["oid"]."]",
			"size" => 2,
			"value" => $_param["bug_priority"],
		));
		return $return;
	}

	function show_severity($_param)
	{
		if ($_param["obj"]->class_id() == CL_MENU)
		{
			return "";
		}
		$return = html::textbox(array(
			"name" => "bug_severity[".$_param["oid"]."]",
			"size" => 2,
			"value" => $_param["bug_severity"],
		));
		return $return;
	}

	function show_status($_val)
	{
		if ($_val["obj"]->class_id() == CL_MENU)
		{
			return "";
		}
		$values = $this->bug_i->get_status_list();
		return $values[$_val["bug_status"]];
	}
	
	function show_status_no_edit($_val)
	{
		if ($_val["obj"]->class_id() == CL_MENU)
		{
			return "";
		}
		$values = $this->bug_i->get_status_list();
		return $values[$_val["bug_status"]];
	}
	
	function comment_callback($arr)
	{
		if ($arr["obj"]->class_id() == CL_MENU)
		{
			return "";
		}
		return html::img(array("url" => aw_ini_get("baseurl")."/automatweb/images/forum_add_new.gif", "border" => 0))." ".html::get_change_url($arr["oid"] , array("group" => "comments" , "return_url" => get_ru()), $arr["comment_count"]);
	}

	function _init_bug_list_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "icon",
			"caption" => t(""),
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1
		));
		$bugi = get_instance(CL_BUG);
		$t->define_field(array(
			"name" => "bug_status",
			"caption" => t("Staatus"),
			"sortable" => 1,
//			"callback" => array(&$this, "show_status"),
//			"callb_pass_row" => 1,
			"filter" => $bugi->get_status_list()
		));

		$t->define_field(array(
			"name" => "who",
			"caption" => t("Kellele"),
			"sortable" => 1,
		));

		$t->define_field(array(
			"name" => "bug_priority",
			"caption" => t("Prioriteet"),
			"sortable" => 1,
			"numeric" => 1,
			"callback" => array(&$this, "show_priority"),
			"callb_pass_row" => 1,
			"filter" => array(
				t("1"),
				t("2"),
				t("3"),
				t("4"),
				t("5"),
			),
		));
		$t->define_field(array(
			"name" => "bug_severity",
			"caption" => t("T&otilde;sidus"),
			"sortable" => 1,
			"numeric" => 1,
			"callback" => array(&$this, "show_severity"),
			"callb_pass_row" => 1,
			"filter" => array(
				t("1"),
				t("2"),
				t("3"),
				t("4"),
				t("5"),
			),
		));

		$t->define_field(array(
			"name" => "createdby",
			"caption" => t("Looja"),
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "deadline",
			"caption" => t("T&auml;htaeg"),
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y / H:i"
		));

		$t->define_field(array(
			"name" => "comment",
			"caption" => t("K"),
			"sortable" => 1,
			"numeric" => 1,
			"callback" => array(&$this,"comment_callback"),
			"callb_pass_row" => 1,
		));

		$t->define_chooser(array(
			"field" => "id",
			"name" => "sel",
		));
	}

	function _init_bug_list_tbl_no_edit(&$t)
	{
		$t->define_field(array(
			"name" => "icon",
			"caption" => t(""),
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1
		));

		$bugi = get_instance(CL_BUG);
		$t->define_field(array(
			"name" => "bug_status",
			"caption" => t("Staatus"),
			"sortable" => 1,
		/*	"callback" => array(&$this, "show_status_no_edit"),
			"callb_pass_row" => 1,*/
			"filter" => $bugi->get_status_list()
		));

		$t->define_field(array(
			"name" => "who",
			"caption" => t("Kellele"),
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "sort_priority",
			"caption" => t("SP"),
			"sortable" => 1,
		));

		$t->define_field(array(
			"name" => "bug_priority",
			"caption" => t("Prioriteet"),
			"sortable" => 1,
			"numeric" => 1,
			"filter" => array(
				t("1"),
				t("2"),
				t("3"),
				t("4"),
				t("5"),
			),
		));
		$t->define_field(array(
			"name" => "bug_severity",
			"caption" => t("T&otilde;sidus"),
			"sortable" => 1,
			"numeric" => 1,
			"filter" => array(
				t("1"),
				t("2"),
				t("3"),
				t("4"),
				t("5"),
			),
		));

		$t->define_field(array(
			"name" => "createdby",
			"caption" => t("Looja"),
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "deadline",
			"caption" => t("T&auml;htaeg"),
			"chgbgcolor" => "col",
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y / H:i"
		));

		$t->define_field(array(
			"name" => "comment",
			"caption" => t("K"),
			"sortable" => 1,
			"numeric" => 1,
			"callback" => array(&$this,"comment_callback"),
			"callb_pass_row" => 1,
		));

		$t->define_chooser(array(
			"field" => "id",
			"name" => "sel",
		));
	}

	function _bug_list($arr)
	{
		classload("vcl/table");
		$t = new vcl_table;
		$this->_init_bug_list_tbl($t);

		$pt = !empty($arr["request"]["cat"]) ? $arr["request"]["cat"] : $arr["obj_inst"]->id();
		if(($this->can("view", $pt) || 
			$arr["request"]["group"] == "by_class"
		) && $arr["request"]["group"] != "by_who" && $arr["request"]["group"] != "by_project" && $arr["request"]["group"] != "by_cust" )
		{
			// arhiivi tab
			if($arr["request"]["group"] == "archive")
			{
				$ot = new object_tree(array(
					"parent" => $pt,
					"class_id" => array(
						CL_BUG,CL_MENU,
					),
				));

				$ol = new object_list(array(
					"oid" => $ot->ids(),
					"class_id" => CL_BUG,
					"bug_status" => BUG_STATUS_CLOSED,
				));
			}
			// bugid tab
			else
			{
				$filt = array(
					"parent" => $pt,
					"class_id" => array(CL_BUG,CL_MENU),
					"bug_status" => new obj_predicate_not(BUG_STATUS_CLOSED),
				);

				if(strlen($arr["request"]["p_id"]))
				{
					$filt[$this->sort_type["name"]] = $arr["request"]["p_id"];
				}
				elseif(strlen($arr["request"]["b_id"]))
				{
					$filt["parent"] = $arr["request"]["b_id"];
				}
				else
				if ($arr["request"]["p_fld_id"])	// class folder
				{
					// list classes for that folder
					$clss = aw_ini_get("classes");
					$c = array();
					foreach($clss as $clid => $dat)
					{
						foreach(explode(",", $dat["parents"]) as $parent)
						{
							if ($parent == $arr["request"]["p_fld_id"])
							{
								$c[] = $clid;
							}
						}
					}
					$filt["bug_class"] = $c;
					unset($filt["parent"]);
				}
				else
				if ($arr["request"]["p_cls_id"])	// class 
				{
					$filt["bug_class"] = $arr["request"]["p_cls_id"];
					unset($filt["parent"]);
				}

				if ($arr["request"]["b_stat"])
				{
					$filt["bug_status"] = $arr["request"]["b_stat"];
					unset($filt["parent"]);
				}

				if ($arr["request"]["b_mon"])
				{
					//$filt["monitors"] = $arr["request"]["b_mon"];
					$filt["CL_BUG.RELTYPE_MONITOR"] = $arr["request"]["b_mon"];
					unset($filt["parent"]);
				}
				$filt["lang_id"] = array();
				$filt["site_id"] = array();
				$ol = new object_list($filt);
			}
		}
		else
		if ($arr["request"]["group"] == "by_who" && $arr["request"]["p_id"])
		{
			$ol = new object_list(array(
				"class_id" => CL_BUG,
				"lang_id" => array(),
				"site_id" => array(),
				"bug_status" => $arr["request"]["b_stat"] ? $arr["request"]["b_stat"] : new obj_predicate_not(BUG_STATUS_CLOSED),
				"who" => $arr["request"]["p_id"]
			));
		}
		else
		if ($arr["request"]["group"] == "by_cust" && $arr["request"]["cust"])
		{
			$ol = new object_list(array(
				"class_id" => CL_BUG,
				"lang_id" => array(),
				"site_id" => array(),
				"bug_status" => new obj_predicate_not(BUG_STATUS_CLOSED),
				"customer" => $arr["request"]["cust"]
			));
		}
		else
		if ($arr["request"]["group"] == "by_project" && $arr["request"]["p_id"])
		{
			$ol = new object_list(array(
				"class_id" => CL_BUG,
				"lang_id" => array(),
				"site_id" => array(),
				"bug_status" => new obj_predicate_not(BUG_STATUS_CLOSED),
				"project" => $arr["request"]["p_id"]
			));
		}
		else
		{
			$ol = new object_list();
		}

		$this->populate_bug_list_table_from_list($t, $ol, array("bt" => $arr["obj_inst"]));		
		$t->sort_by();
		$arr["prop"]["value"] = "<span id=\"bug_table\">".$t->draw()."</table>";
		if ($arr["request"]["tb_only"] == 1)
		{
			die($t->draw());
		}
	}

	function populate_bug_list_table_from_list(&$t, $ol, $params = array())
	{
		classload("core/icons");
		$u = get_instance(CL_USER);
		$us = get_instance("users");
		$bug_i = get_instance(CL_BUG);
		$states = $bug_i->get_status_list();
		$bug_list = $ol->arr();
		$user_list = array();
		foreach($bug_list as $bug)
		{
			$user_list[] = $bug->createdby();
		}
		$u2p = array();
		if (count($user_list))
		{
			$oid_list = array_flip($us->get_oid_for_uid_list($user_list));
			$c = new connection();
			$u2p_conns = $c->find(array(
				"from.class_id" => CL_USER,
				"from" => array_keys($oid_list),
				"type" => "RELTYPE_PERSON"
			));
			$person_oids = array();
			foreach($u2p_conns as $con)
			{
				$person_oids[] = $con["to"];
				$u2p[$oid_list[$con["from"]]] = $con["to"];
			}

			$person_ol = new object_list(array("class_id" => CL_CRM_PERSON, "oid" => $person_oids, "lang_id" => array(), "site_id" => array()));
			$person_ol->arr();
		}

		$comment_ol = new object_list(array(
			"parent" => $ol->ids(),
			"class_id" => CL_BUG_COMMENT,
			"lang_id" => array(),
			"site_id" => array()
		));
		$comments_by_bug = array();
		foreach($comment_ol->arr() as $comm)
		{
			$comments_by_bug[$comm->parent()]++;
		}

		if ($_GET["action"] == "list_only_fetch")
		{
			$t->set_request_uri($this->mk_my_orb("change", array("id" => $params["bt"]->id(), "group" => "by_default", "b_id" => $_GET["b_id"]), "bug_tracker"));
		}

		foreach($bug_list as $bug)
		{
			$crea = $bug->createdby();
			$p = obj($u2p[$crea]);

			if ($_GET["action"] == "list_only_fetch")
			{
				$nl = html::href(array(
					"url" => html::get_change_url($bug->id(), array(
						"return_url" => $this->mk_my_orb("change", array("id" => $params["bt"]->id(), "group" => "by_default", "b_id" => $bug->parent()), "bug_tracker"),
					)),
					"caption" => parse_obj_name($bug->name())
				));
				$opurl = $this->mk_my_orb("change", array("id" => $params["bt"]->id(), "group" => "by_default", "b_id" => $bug->id()), "bug_tracker");
			}
			else
			{
				$nl = html::obj_change_url($bug);
				$opurl = aw_url_change_var("b_id", $bug->id());
			}
			if ($params["path"])
			{
				$nl = $bug->path_str(array(
					"to" => $params["bt"]->id(),
					"path_only" => true
				))." / ".$nl;
			}

			$col = "";
			$dl = $bug->prop("deadline");
			if ($dl > 100 && time() > $dl)
			{
				$col = "#ff0000";
			}
			else
			if ($dl > 100 && date("d.m.Y") == date("d.m.Y", $dl)) // today
			{
				$col = "#f3f27e";
			}

			$t->define_data(array(
				"name" => $nl." (".html::href(array(
					"url" => $opurl,
					"caption" => t("Sisene")
				)).")",
				"bug_status" => $states[$bug->prop("bug_status")],
				"who" => $bug->prop_str("who"),
				"bug_priority" => $bug->class_id() == CL_MENU ? "" : $bug->prop("bug_priority"),
				"bug_severity" => $bug->class_id() == CL_MENU ? "" : $bug->prop("bug_severity"),
				"createdby" => $p->name(),
				"created" => $bug->created(),
				"deadline" => $bug->prop("deadline"),
				"id" => $bug->id(),
				"oid" => $bug->id(),
				"sort_priority" => $bug_i->get_sort_priority($bug),
				"icon" => icons::get_icon($bug),
				"obj" => $bug,
				"comment_count" => (int)$comments_by_bug[$bug->id()],
				"comment" => (int)$comments_by_bug[$bug->id()],
				"col" => $col
			));
		}
		$t->set_numeric_field("sort_priority");
		$t->set_default_sortby("sort_priority");
		$t->set_default_sorder("desc");
	}

	/**
		@attrib name=delete
		@param cat optional
	**/
	function delete($arr)
	{
		foreach($arr["sel"] as $id)
		{
			if($this->can("view", $id))
			{
				$obj = obj($id);
				$obj->delete();
			}
		}
		return $arr["post_ru"];
	}

	function callback_mod_retval($arr)
	{
		$arr["args"]["sp_p_name"] = $arr["request"]["sp_p_name"];
		$arr["args"]["sp_p_co"] = $arr["request"]["sp_p_co"];
	}

	function callback_mod_reforb($arr)
	{
		$arr["assign_to"] = 0;
		$arr["b_id"] = $_GET["b_id"];
		$arr["save_search_name"] = "";
		$arr["post_ru"] = aw_url_change_var("post_ru", null, post_ru());
	}

	/**
		@attrib name=assign_bugs 
		@param sel optional
		@param post_ru optional
		@param assign_to optional
	**/
	function assign_bugs($arr)
	{
		if ($arr["assign_to"])
		{
			object_list::iterate_list($arr["sel"],"set_prop", "who", $arr["assign_to"]);
		}
		return $arr["post_ru"];
	}

	/**
		@attrib name=set_bug_status
		@param sel optional
		@param post_ru optional
		@param assign_to optional
	**/
	function set_bug_status($arr)
	{
		if ($arr["assign_to"])
		{
			object_list::iterate_list($arr["sel"],"set_prop", "bug_status", $arr["assign_to"]);
		}
		return $arr["post_ru"];
	}

	/** 
		@attrib name=cut_b 
		@param sel optional
		@param post_ru optional
	**/
	function cut_b($arr)
	{
		$_SESSION["bt"]["cut_bugs"] = array();
		foreach(safe_array($arr["sel"]) as $bug_id)
		{
			$_SESSION["bt"]["cut_bugs"][$bug_id] = $bug_id;
		}
		return $arr["post_ru"];
	}

	/**
		@attrib name=paste_b
	**/
	function paste_b($arr)
	{
		object_list::iterate_list($_SESSION["bt"]["cut_bugs"], "set_parent", $arr["b_id"] ? $arr["b_id"] : $arr["id"]);
		$_SESSION["bt"]["cut_bugs"] = null;
		return $arr["post_ru"];
	}

	function get_bugs_parent($tracker)
	{
		if ($this->can("view", $ret = $tracker->prop("bug_folder")))
		{
			return $ret;
		}
		return $tracker->id();
	}

	/**
		@attrib name=fetch_structure_in_xml
		@param id required type=int acl=view
	**/
	function fetch_structure_in_xml($arr)
	{
		header("Content-type: text/xml");
		$xml = "<?xml version=\"1.0\" encoding=\"".aw_global_get("charset")."\" standalone=\"yes\"?>\n<response>\n";

		$bt = obj($arr["id"]);	
		$pt = $this->get_bugs_parent($bt);

		$ot = new object_tree(array(
			"class_id" => array(CL_MENU,CL_BUG),
			"parent" => $pt
		));

		$this->_req_get_struct_xml($pt, $ot, $xml);

		$xml .= "</response>";
		die($xml);
	}

	function _req_get_struct_xml($parent, $ot, &$xml)
	{
		$this->_req_get_struct_xml_level++;
		foreach($ot->level($parent) as $obj)
		{
			$xml .= "<item><value>".$obj->id()."</value><text>".str_repeat("__", $this->_req_get_struct_xml_level).str_replace("<", "&lt;", str_replace(">", "&gt;", str_replace("&", "&amp;", $obj->name())))."</text></item>\n";
			$this->_req_get_struct_xml($obj->id(), $ot, $xml);
		}
		$this->_req_get_struct_xml_level--;
	}

	function _search_res($arr)
	{
		if (!$arr["request"]["MAX_FILE_SIZE"])
		{
			return;
		}
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bug_list_tbl_no_edit($t);

		$search_filt = $this->_get_bug_search_filt($arr["request"]);
		$ol = new object_list($search_filt);

		if ($arr["request"]["s_find_parens"] != 1)
		{
			$bugs = $ol->arr();

			// now, filter out all bugs that have sub-bugs
			$sub_bugs = new object_list(array(
				"class_id" => CL_BUG,
				"parent" => $ol->ids()
			));

			foreach($sub_bugs->arr() as $sub_bug)
			{
				unset($bugs[$sub_bug->parent()]);
			}
			$ol = new object_list();
			$ol->add(array_keys($bugs));
		}
		$this->populate_bug_list_table_from_list($t, $ol, array(
			"path" => true, 
			"bt" => $arr["obj_inst"]
		));
	}

	function _get_bug_search_filt($r)
	{
		$res = array(
			"class_id" => CL_BUG,
			"lang_id" => array(),
			"site_id" => array()
		);

		$txtf = array("name", "bug_url", "bug_component", "bug_mail");
		foreach($txtf as $field)
		{
			if (trim($r["s_".$field]) != "")
			{
				$res[$field] = $this->_get_string_filt($r["s_".$field]);
			}
		}

		$sf = array("bug_status", "bug_class", "bug_severity", "bug_priority");
		foreach($sf as $field)
		{
			if (trim($r["s_".$field]) != "")
			{
				$res[$field] = $r["s_".$field];
			}
		}

		if (trim($r["s_monitors"]) != "")
		{
			$res["CL_BUG.RELTYPE_MONITOR.name"] = $this->_get_string_filt($r["s_monitors"]);
		}

		$cplx = array("who", "bug_type", "customer", "project");
		foreach($cplx as $field)
		{
			if (trim($r["s_".$field]) != "")
			{
				$res["CL_BUG.".$field.".name"] = $this->_get_string_filt($r["s_".$field]);
			}
		}

		if ($r["s_who_empty"] == 1)
		{
			$res["who"] = new obj_predicate_compare(OBJ_COMP_EQUAL, "");
			unset($res["CL_BUG.who.name"]);
		}
	
		if (trim($r["s_bug_content"]) != "")
		{
			$res[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_BUG.RELTYPE_COMMENT.comment" => $this->_get_string_filt($r["s_bug_content"]),
					"bug_content" => $this->_get_string_filt($r["s_bug_content"]),
				)
			));
		}

		if (trim($r["s_createdby"]) != "")
		{
			// map name to possible persons, get users for those and search by that
			$ul = new object_list(array(
				"class_id" => CL_USER,
				"CL_USER.RELTYPE_PERSON.name" => "%".$r["s_createdby"]."%",
				"lang_id" => array(),
				"site_id" => array()
			));
			if ($ul->count())
			{
				$res["createdby"] = $ul->names();
			}
			else
			{
				$res["oid"] = -1;
			}
		}
		return $res;
	}

	function _get_string_filt($s)
	{
		$this->dequote(&$s);
		// separated by commas delimited by "
		$p = array();
		$len = strlen($s);
		for ($i = 0; $i < $len; $i++)
		{
			if ($s[$i] == "\"" && $in_q)
			{
				// end of quoted string
				$p[] = $cur_str;
				$in_q = false;
			}
			else
			if ($s[$i] == "\"" && !$in_q)
			{
				$cur_str = "";
				$in_q = true;
			}
			else
			if ($s[$i] == "," && !$in_q)
			{
				$p[] = $cur_str;
				$cur_str = "";
			}
			else
			{
				$cur_str .= $s[$i];
			}
		}
		$p[] = $cur_str;
		$p = array_unique($p);

		return map("%%%s%%", $p);
	}

	function _search_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		// save search
		$tb->add_button(array(
			"name" => "save",
			"tooltip" => t("Salvesta otsing"),
			"action" => "save_search",
			"onClick" => "document.changeform.save_search_name.value=prompt('Sisesta nimi');",
			"img" => "save.gif",
		));

		// pick saved searches
		$s = safe_array($arr["obj_inst"]->meta("saved_searches"));
		$ss = array($this->mk_my_orb("change", array(
			"id" => $arr["obj_inst"]->id(),
			"group" => "search_t",
			"return_url" => $arr["request"]["return_url"])) => ""
		);
		foreach($s as $idx => $search)
		{
			if ($search["creator"] == aw_global_get("uid"))
			{
				$opr = $search["params"];
				$opr["id"] = $arr["obj_inst"]->id();
				$opr["group"] = "search";
				$opr["MAX_FILE_SIZE"] = 100000000;
				$url = $this->mk_my_orb("change", $opr);
				$ss[$url] = $search["name"];
			}
		}
		$html = html::select(array(
			"options" => $ss,
			"onchange" => "el=document.changeform.go_to_saved_search;window.location=el.options[el.selectedIndex].value",
			"name" => "go_to_saved_search",
			"value" => get_ru()
		));
		$tb->add_cdata($html);

		$tb->add_menu_button(array(
			"name" => "assign",
			"tooltip" => t("M&auml;&auml;ra"),
			"img" => "class_38.gif"
		));

		// list all people to assign to
		// list all my co-workers who are important to me, from crm
		$ppl = $this->get_people_list($arr["obj_inst"]);
		foreach($ppl as $p_oid => $p_name)
		{
			$tb->add_menu_item(array(
				"parent" => "assign",
				"text" => $p_name,
				"link" => "#",
				"onClick" => "document.changeform.assign_to.value=$p_oid;submit_changeform('assign_bugs')"
			));
		}
	}	

	/**
		@attrib name=save_search all_args=1
	**/
	function save_search($arr)
	{
		$search_params = array();
		foreach($arr as $k => $v)
		{
			if ($k[0] == "s" && $k[1] == "_")
			{
				$search_params[$k] = $v;
			}
		}

		$o = obj($arr["id"]);
		$ss = safe_array($o->meta("saved_searches"));
		$ss[count($ss)+1] = array(
			"name" => $arr["save_search_name"],
			"params" => $search_params,
			"creator" => aw_global_get("uid")
		);
		$o->set_meta("saved_searches", $ss);
		$o->save();

		return $arr["post_ru"];
	}

	function _init_saved_searches(&$t)
	{
		$t->define_field(array(
			"name" => "who",
			"caption" => t("Kelle otsing"),
			"sortable" => 1,
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "params",
			"caption" => t("Otsingu parameetrid"),
			"sortable" => 1,
			"align" => "center"
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "idx"
		));
	}

	function _saved_searches($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_saved_searches(&$t);

		$bug_props = $arr["obj_inst"]->get_property_list();
		$bugi = get_instance(CL_BUG);

		$u = get_instance(CL_USER);
		$ss = safe_array($arr["obj_inst"]->meta("saved_searches"));
		foreach($ss as $idx => $search)
		{
			$p = $u->get_person_for_uid($search["creator"]);
			$ps = array();
			foreach(safe_array($search["params"]) as $par_nm => $par_val)
			{
				if ($par_val != "")
				{
					if (is_array($par_val))
					{
						if (count($par_val))
						{
							if ($par_nm == "s_deadline")
							{
								$ts = date_edit::get_timestamp($par_val);
								if ($ts > 300)
								{
									$ps[] = $bug_props[$par_nm]["caption"]." = ".date("d.m.Y", $ts);
								}
							}
							else
							if ($par_nm == "s_bug_status")
							{
								$states = $bugi->get_status_list();
								$tmp = array();
								foreach($par_val as $state)
								{
									$tmp[] = $states[$state];
								}
								$ps[] = $bug_props[$par_nm]["caption"]." = ".join(",", $tmp);
							}
							else
							{
								$ps[] = $bug_props[$par_nm]["caption"]." = ".join(",", $par_val);
							}
						}
					}
					else
					{
						if ($par_nm == "s_who")
						{
							$ps[] = "Kellele = ".$par_val;
						}
						else
						{
							$ps[] = $bug_props[$par_nm]["caption"]." = ".$par_val;
						}
					}
				}
			}

			$opr = $search["params"];
			$opr["id"] = $arr["obj_inst"]->id();
			$opr["group"] = "search";
			$opr["MAX_FILE_SIZE"] = 100000000;
			$url = $this->mk_my_orb("change", $opr);

			$t->define_data(array(
				"name" => html::href(array(
					"url" => $url,
					"caption" => $search["name"]
				)),
				"idx" => $idx,
				"who" => $p->name(),
				"params" => join("<br>", $ps)
			));
		}
	}

	function callback_generate_scripts($arr)
	{
		unset($arr["request"]["class"]);
		unset($arr["request"]["action"]);
		$url = $this->mk_my_orb("list_only_fetch", $arr["request"]);

		$new_url = $this->mk_my_orb("new", array(), CL_BUG);
		$cur_url = aw_url_change_var("b_id", NULL);

		return "
		var last_bold_node;
		var last_bold_node_cont;
		function do_bt_table_switch(bugid, that) 
		{ 
			url = '$url&b_id='+bugid;
			el = document.getElementById('bug_table');
			el.innerHTML=aw_get_url_contents(url);
			document.changeform.b_id.value=bugid;
			new_el = document.getElementById('add_bug_href');
			new_el.href = '$new_url&parent='+bugid+'&return_url='+encodeURIComponent(document.location.href);

			bm_url = document.getElementById('sync_url');
			bm_url.href = '$cur_url&b_id='+bugid;

			if (last_bold_node)
			{
				last_bold_node.innerHTML=last_bold_node_cont;
			}

			last_bold_node = that;
			last_bold_node_cont = that.innerHTML;
			that.innerHTML= '<b>'+that.innerHTML+'</b>';
		}";
	}

	/**
		@attrib name=list_only_fetch all_args=1
	**/
	function list_only_fetch($arr)
	{
		$p = array();
		$val = $this->_bug_list(array(
			"prop" => &$p,
			"request" => $arr,
			"obj_inst" => obj($arr["id"]),
		));
		header("Content-type: text/html; charset=".aw_global_get("charset"));

		echo ($p["value"]);
		aw_shutdown();
		die();
	}

	function __gantt_sort($a, $b)
	{
		$a_pri = $this->bug_i->get_sort_priority($a);
		$b_pri = $this->bug_i->get_sort_priority($b);
		return $a_pri == $b_pri ? 0 : ($a_pri > $b_pri ? -1 : 1);
	}

	function get_last_estimation_over_deadline_bugs()
	{
		return $this->over_deadline;
	}

	function get_estimated_end_time_for_bug($bug)
	{
		$this->over_deadline = array();
		$p = $bug->prop("who");
		if (!$p)
		{
			return null;
		}
		return $this->_gantt(array(
			"request" =>  array(
				"filt_p" => $p,
			),
			"ret_b_time" => $bug->id(),
			"ret_b" => $bug
		));
	}

	function _gantt($arr)
	{
		$chart = get_instance ("vcl/gantt_chart");

		$columns = 7;

		if ($this->can("view", $arr["request"]["filt_p"]))
		{
			$p = obj($arr["request"]["filt_p"]);
		}
		else
		{
			$u = get_instance(CL_USER);
			$p = obj($u->get_current_person());
		}

		classload("core/date/date_calc");
		$range_start = get_day_start();
		$range_end = time() + 24*3600*14;

		$subdivisions = 1;

		$has = false;
		$gt_list = $this->get_undone_bugs_by_p($p);
		foreach($gt_list as $gt)
		{
			$chart->add_row (array (
				"name" => $gt->id(),
				"title" => $gt->name(),
				"uri" => html::get_change_url(
					$gt->id(),
					array("return_url" => get_ru())
				)
			));
			if ($arr["ret_b"] && $gt->id() == $arr["ret_b"]->id())
			{
				$has = true;
			}
		}

		if (!$has && $arr["ret_b"])
		{
			$gt_list[] = $arr["ret_b"];
			usort($gt_list, array(&$this, "__gantt_sort"));
		}
		$day2wh = $this->get_person_whs($p);

		$start = $this->get_next_avail_time_from(time(), $day2wh);

		$this->job_count = count($gt_list);
		foreach ($gt_list as $gt)
		{
			$start = $this->get_next_avail_time_from($start, $day2wh);
			if ($gt->prop("num_hrs_guess") > 0)
			{
				$length = $gt->prop("num_hrs_guess") * 3600;
			}
			else
			{
				$length = 7200;
			}
			$this->job_hrs += $length;
			$day_info = $day2wh[date("w", $start)];
			$day_start = $day_info[0];
			$day_end = $day_info[1];

			if (date("H", $start+$length) > $day_end || ($length > (3600 * 7)))
			{
				// split into parts
				$wd_end = mktime($day_end, 0, 0, date("m", $start), date("d", $start), date("Y", $start));
				$tot_len = $length;
				$length = $wd_end - $start;
				$remaining_len = $tot_len - $length;
				$title = $gt->name()."<br>( ".date("d.m.Y H:i", $start)." - ".date("d.m.Y H:i", $start + $length)." ) ";

				$bar = array (
					"id" => $gt->id (),
					"row" => $gt->id (),
					"start" => $start,
					"length" => $length,
					"title" => $title,
				);

				$chart->add_bar ($bar);
				$start += $length;

				while($remaining_len > 0)
				{
					$length = min($remaining_len, 8*3600);
					$remaining_len -= $length;
					$start = $this->get_next_avail_time_from($start, $day2wh);

					$title = $gt->name()."<br>( ".date("d.m.Y H:i", $start)." - ".date("d.m.Y H:i", $start + $length)." ) ";

					$bar = array (
						"id" => $gt->id (),
						"row" => $gt->id (),
						"start" => $start,
						"length" => $length,
						"title" => $title,
					);

					$chart->add_bar ($bar);
					$start += $length;
				}
			}
			else
			{
				$title = $gt->name()."<br>( ".date("d.m.Y H:i", $start)." - ".date("d.m.Y H:i", $start + $length)." ) ";

				$bar = array (
					"id" => $gt->id (),
					"row" => $gt->id (),
					"start" => $start,
					"length" => $length,
					"title" => $title,
				);

				$chart->add_bar ($bar);
				$start += $length;
			}

			if ($gt->prop("deadline") > 300 && $start > $gt->prop("deadline"))
			{
				$this->over_deadline[$gt->id()] = $gt;
			}

			if ($gt->id() == $arr["ret_b_time"])
			{
				return $start;
			}
		}
		$this->job_end = $start;
		$chart->configure_chart (array (
			"chart_id" => "bt_gantt",
			"style" => "aw",
			"start" => $range_start,
			"end" => $range_end,
			"columns" => $columns,
			"subdivisions" => $subdivisions,
			"timespans" => $subdivisions,
			"width" => 850,
			"row_height" => 10,
		));

		### define columns
		$i = 0;
		$days = array ("P", "E", "T", "K", "N", "R", "L");

		while ($i < $columns)
		{
			$day_start = (get_day_start() + ($i * 86400));
			$day = date ("w", $day_start);
			$date = date ("j/m/Y", $day_start);
			$uri = aw_url_change_var ("mrp_chart_length", 1);
			$uri = aw_url_change_var ("mrp_chart_start", $day_start, $uri);
			$chart->define_column (array (
				"col" => ($i + 1),
				"title" => $days[$day] . " - " . $date,
				"uri" => $uri,
			));
			$i++;
		}

		$arr["prop"]["value"] = $chart->draw_chart ();
		
	}

	function _sp_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "save",
			"tooltip" => t("Salvesta"),
			"action" => "add_s_res_to_p_list",
			"img" => "save.gif",
		));
		$tb->add_button(array(
			"name" => "delete",
			"tooltip" => t("Kustuta"),
			"img" => "delete.gif",
			"action" => "remove_p_from_l_list",
		));
	}

	function _init_p_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "co",
			"caption" => t("Organisatsioon"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "phone",
			"caption" => t("Telefon"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "email",
			"caption" => t("E-mail"),
			"align" => "center",
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _sp_table($arr)
	{	
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_p_tbl($t);

		foreach($this->get_people_list($arr["obj_inst"]) as $p_id => $p_nm)
		{
			$p = obj($p_id);
			$t->define_data(array(
				"name" => html::obj_change_url($p),
				"co" => html::obj_change_url($p->prop("work_contact")),
				"phone" => $p->prop("phone.name"),
				"email" => $p->prop("email.name"),
				"oid" => $p->id()
			));
		}
	}

	function _sp_s_res($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_p_tbl($t);

		if ($arr["request"]["sp_p_name"] != "" || $arr["request"]["sp_p_co"] != "")
		{
			$param = array(
				"class_id" => CL_CRM_PERSON,
				"lang_id" => array(),
				"site_id" => array(),
				"name" => "%".$arr["request"]["sp_p_name"]."%"
			);
			if ($arr["request"]["sp_p_co"] != "")
			{
				$param["CL_CRM_PERSON.work_contact.name"] = "%".$arr["request"]["sp_p_co"]."%";
			}
			$ol = new object_list($param);
			foreach($ol->arr() as $p)
			{
				$t->define_data(array(
					"name" => html::obj_change_url($p),
					"co" => html::obj_change_url($p->prop("work_contact")),
					"phone" => $p->prop("phone.name"),
					"email" => html::href(array("url" => "mailto:".$p->prop("email.mail"),"caption" => $p->prop("email.mail"))),
					"oid" => $p->id()
				));
			}
		}
	}

	/**
		@attrib name=add_s_res_to_p_list
	**/
	function add_s_res_to_p_list($arr)
	{	
		$o = obj($arr["id"]);
		$persons = $o->meta("imp_p");
		foreach(safe_array($arr["sel"]) as $p_id)
		{
			$persons[aw_global_get("uid")][$p_id] = $p_id;
		}
		$o->set_meta("imp_p", $persons);
		$o->save();
		return $arr["post_ru"];
	}

	/**
		@attrib name=remove_p_from_l_list
	**/
	function remove_p_from_l_list($arr)
	{	
		$o = obj($arr["id"]);
		$persons = $o->meta("imp_p");
		foreach(safe_array($arr["sel"]) as $p_id)
		{
			unset($persons[aw_global_get("uid")][$p_id]);
		}
		$o->set_meta("imp_p", $persons);
		$o->save();
		return $arr["post_ru"];
	}

	/**
		@attrib name=co_autocomplete_source
		@param sp_p_co optional
	**/
	function co_autocomplete_source($arr)
	{
		$ac = get_instance("vcl/autocomplete");
		$arr = $ac->get_ac_params($arr);

		$ol = new object_list(array(
			"class_id" => CL_CRM_COMPANY,
			"name" => $arr["sp_p_co"]."%",
			"lang_id" => array(),
			"site_id" => array(),
			"limit" => 100
		));
		return $ac->finish_ac($ol->names());
	}

	/**
		@attrib name=p_autocomplete_source
		@param sp_p_p optional
	**/
	function p_autocomplete_source($arr)
	{
		$ac = get_instance("vcl/autocomplete");
		$arr = $ac->get_ac_params($arr);

		$ol = new object_list(array(
			"class_id" => CL_CRM_PERSON,
			"name" => $arr["sp_p_p"]."%",
			"lang_id" => array(),
			"site_id" => array(),
			"limit" => 200
		));
		return $ac->finish_ac($ol->names());
	}

	function get_people_list($bt)
	{
		$ret = array();
		$persons = $bt->meta("imp_p");
		$persons = safe_array($persons[aw_global_get("uid")]);

		if (!count($persons))
		{
			return array();
		}

		$ol = new object_list(array(
			"oid" => $persons,
			"lang_id" => array(),
			"site_id" => array()
		));
		return $ol->names();
	}

	function generate_mon_bug_tree($arr)
	{
		$this->tree->add_item(0,array(
			"id" => $this->self_id,
			"name" => $this->tree_root_name,
		));	

		// list all monitors
		$c = new connection();
		$conns = $c->find(array(
			"from.class_id" => CL_BUG,
			"type" => "RELTYPE_MONITOR"
		));	
		foreach($conns as $con)
		{
			$this->tree->add_item($this->self_id, array(
				"id" => $con["to"],
				"name" => $con["to.name"]
			));
		}
	}

	/**
		@attrib name=get_node_monitor all_args=1
	**/
	function get_node_monitor($arr)
	{	
	    classload("core/icons");
		$node_tree = get_instance("vcl/treeview");
		$node_tree->start_tree (array (
			"type" => TREE_DHTML,
			"tree_id" => "bug_tree",
			"branch" => 1,
		));
		$bugi = get_instance(CL_BUG);

		$po = obj();
		if ($this->can("view", $arr["parent"]))
		{
			$po = obj($arr["parent"]);
		}
		if ($po->class_id() == CL_CRM_PERSON)
		{
			// only statuses
			foreach($bugi->get_status_list() as $sid => $sn)
			{
				if ($arr["b_stat"] == $sid)
				{
					$sn = "<b>".$sn."</b>";
				}
				$node_tree->add_item(0, array(
					"id" => $arr["parent"]."_".$sid,
					"name" => $sn,
					"url" => html::get_change_url(
						$arr["inst_id"],
						array(
							"group" => "by_monitor",
							"b_stat" => $sid,
							"b_mon" => $arr["parent"]
						)
					)
				));
			}
			die($node_tree->finalize_tree());
		}

		// list all monitors
		$c = new connection();
		$conns = $c->find(array(
			"from.class_id" => CL_BUG,
			"type" => "RELTYPE_MONITOR"
		));	
		$mons = array();
		foreach($conns as $con)
		{
			$mons[$con["to"]] = $con["to.name"];
		}
		foreach($mons as $_to => $_to_name)
		{
			if ($_to == $arr["b_mon"])
			{
				$_to_name = "<b>".$_to_name."</b>";
			}
			$node_tree->add_item(0, array(
				"id" => $_to,
				"name" => $_to_name,
				"iconurl" => icons::get_icon_url(CL_CRM_PERSON),
				"url" => html::get_change_url(
					$arr["inst_id"],
					array(
						"group" => "by_monitor",
						"b_mon" => $_to
					)
				)
			));

			// add statuses under ppl
			
			foreach($bugi->get_status_list() as $sid => $sn)
			{
				if ($arr["b_stat"] == $sid && $_to == $arr["b_mon"])
				{
					$sn = "<b>".$sn."</b>";
				}
				$node_tree->add_item($_to, array(
					"id" => $_to."_".$sid,
					"name" => $sn,
					"url" => html::get_change_url(
						$arr["inst_id"],
						array(
							"group" => "by_monitor",
							"b_stat" => $sid,
							"b_mon" => $_to
						)
					)
				));
			}
		}

		die($node_tree->finalize_tree());
	}

	function get_person_whs($p)
	{
		$whs = $p->prop("work_hrs");
		$ret = array();
		if ($whs == "")
		{
			return array(
				0 => array(0,0),
				1 => array(9, 17),
				2 => array(9, 17),
				3 => array(9, 17),
				4 => array(9, 17),
				5 => array(9, 17),
				6 => array(0, 0),
			);
		}
		else
		{
			$lines = explode("\n", $whs);
			$lut = array(
				"E" => 1,
				"T" => 2,
				"K" => 3,
				"N" => 4,
				"R" => 5,
				"L" => 6,
				"P" => 0
			);
			foreach($lines as $l)
			{
				$l = trim($l);
				if ($l == "")
				{
					continue;
				}
				list($d, $hrs) = explode(":", $l);
				$ret[$lut[$d]] = explode("-", trim($hrs));
			}
		}
		return $ret;
	}

	function get_next_avail_time_from($tm, $day2wh)
	{
		if ($this->_rq_lev > 7)
		{
			error::raise(array(
				"id" => "ERR_TIME_LOOP",
				"msg" => t("bug_tracker::get_next_avail_time_from(): time is in loop!")
			));
			die();
		}
		$this->_rq_lev++;
		$hr = date("H", $tm);
		$day = date("w", $tm);
		$day_start = $day2wh[$day][0];
		$day_end = $day2wh[$day][1];
		if ($day_start == $day_end || $hr >= $day_end)
		{
			$rv = $this->get_next_avail_time_from(mktime(0,0,0, date("m", $tm), date("d", $tm)+1, date("Y", $tm)), $day2wh);
			$this->_rq_lev--;
			return $rv;
		}

		if ($hr < $day_start)
		{
			$this->_rq_lev--;
			return mktime($day_start, 0, 0, date("m", $tm), date("d", $tm), date("Y", $tm));
		}
		$this->_rq_lev--;
		return $tm;
	}

	function get_undone_bugs_by_p($p)
	{
		// get all goals/tasks
		$ft = array(
			"class_id" => CL_BUG,
			"bug_status" => array(BUG_OPEN,BUG_INPROGRESS,BUG_FATALERROR),
			"CL_BUG.who.name" => $p->name(),
			"lang_id" => array(),
			"site_id" => array()
		);
		$ot = new object_tree($ft);
		$gt_list = $ot->to_list();

		$bugs = $gt_list->arr();

		// now, filter out all bugs that have sub-bugs
		$sub_bugs = new object_list(array(
			"class_id" => CL_BUG,
			"parent" => $gt_list->ids(),
			"lang_id" => array(),
			"site_id" => array()	
		));

		foreach($sub_bugs->arr() as $sub_bug)
		{
			unset($bugs[$sub_bug->parent()]);
		}
		$gt_list = new object_list();
		$gt_list->add(array_keys($bugs));
		$gt_list->sort_by_cb(array(
			&$this, "__gantt_sort"
		));

		$rv = $gt_list->arr();		
		foreach($rv as $idx => $bug)
		{
			if ($bug->prop("bug_predicates") != "")
			{
				$preds = explode(",", $bug->prop("bug_predicates"));
				$preds_done = true;
				foreach($preds as $pred_id)
				{
					$pred_id = str_replace("#", "", $pred_id);
					if ($this->can("view", $pred_id))
					{
						$predo = obj($pred_id);
						if ($predo->prop("bug_status") < 3 || $predo->prop("bug_status") == 11)
						{
							$preds_done = false;
						}
					}
				}
				if (!$preds_done)
				{
					unset($rv[$idx]);
				}
			}
		}
		return $rv;
	}

	/**
		@attrib name=nag_about_unestimated_bugs nologin=1
	**/
	function nag_about_unestimated_bugs($arr)
	{
		// auth as me
		aw_switch_user(array("uid" => "kix"));

		// get list of all users to whom bugs are assigned
		$c = new connection();
		$users = array();
		foreach($c->find(array("from.class_id" => CL_BUG, "type" => "RELTYPE_MONITOR")) as $c)
		{
			$users[$c["to"]] = $c["to"];
		}

		$us = array();		
		foreach($users as $user)
		{
			// list&sort bugs for that user
			// get first 10 and nag him about their estimated lengths
			$p = obj($user);
			$us[$p->name()] = $p;
		}

		foreach($us as $p)
		{
			echo "user ".$p->name()." <br>";

			$nag_about = $this->get_unestimated_bugs_by_p($p);

			if (count($nag_about) > 0)
			{
				$mail = sprintf(t("Tere!\nMina olen AW Bugtrack. Sul (%s) on prognoositavad ajad m22ramata nendele bugidele, palun tee seda kohe!\n\n"), $p->name());
				foreach($nag_about as $nb)
				{
					$mail .= $nb->name()." ".obj_link($nb->id())."\n";
				}
				$mail .= "\n\nLihtsalt saad seda teha siit:\n";
				$mail .= "http://intranet.automatweb.com/automatweb/orb.aw?class=bug_tracker&action=change&id=142821&group=unestimated_bugs";
				echo "send to ".$p->prop("email.mail")."<br>".nl2br($mail)." <br><br><br><br>";
				send_mail($p->prop("email.mail"), t("Bugtracki M22ramata ajad"), $mail);
			}
		}
echo "<hr>";
		get_instance(CL_BUG);
		// get all bugs that are needs feedback and send mail to their creators
		$ol = new object_list(array(
			"class_id" => CL_BUG,
			"lang_id" => array(),
			"site_id" => array(),
			"bug_status" => BUG_FEEDBACK,
		));

		$bug2uid = array();
		foreach($ol->arr() as $o)
		{
			if ($this->can("view", $o->prop("bug_feedback_p")))
			{
				$bug2uid[$o->prop("bug_feedback_p")][] = $o;
			}
			else
			{
				$bug2uid[$o->createdby()][] = $o;
			}
		}

		$u = get_instance("users");
		foreach($bug2uid as $b_uid => $bugs)
		{
			if (is_oid($b_uid))
			{
				$b_person = obj($b_person);
				$eml = $b_person->prop_str("email");
			}
			else
			{
				$u_oid = $u->get_oid_for_uid($b_uid);
				if (!$this->can("view", $u_oid))
				{
					continue;
				}			
				$uo = obj($u_oid);
			
				$eml = $uo->prop("email");
			}

			$ct = "Tere!\nMina olen AW Bugtrack. Sul on vastamata vajab tagasisidet buge:\n";
			foreach($bugs as $bug)
			{
				$ct .= obj_link($bug->id())." ".$bug->name()."\n";
			}
			$ct .= "\n\nEdu vastamisel!\n";

			echo "send to ".$eml."<br>".nl2br($ct)." <br><br><br><br>";
			send_mail($eml, t("Bugtracki vastamata bugid"), $ct);
		}
		die(t("all done"));
	}

	function get_unestimated_bugs_by_p($p)
	{
		$bugs = $this->get_undone_bugs_by_p($p);
		$cnt = 0;
		$nag_about = array();
		foreach($bugs as $bug)
		{
			if ($cnt > 9)
			{
				break;
			}

			if ($bug->prop("num_hrs_guess") == 0)
			{
				$cnt++;
				$nag_about[] = $bug;
			}
		}

		return $nag_about;
	}

	function _init_unestimated_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Arendus&uuml;lesanne"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "time",
			"caption" => t("Prognoositud aeg"),
			"align" => "center"
		));
	}

	function _unestimated_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_unestimated_table($t);
		$p = get_current_person();
		if ($arr["request"]["filt_p"])
		{
			$p = obj($arr["request"]["filt_p"]);
		}

		foreach($this->get_unestimated_bugs_by_p($p) as $bug)
		{
			$t->define_data(array(
				"name" => html::obj_change_url($bug),
				"time" => html::textbox(array(
					"name" => "bugs[".$bug->id()."]",
					"value" => $bug->prop("num_hrs_guess"),
					"size" => 5
				))
			));
		}
		$t->set_sortable(false);
	}

	function _save_estimates($arr)
	{
		foreach(safe_array($arr["request"]["bugs"]) as $bid => $est)
		{
			$bo = obj($bid);
			$bo->set_prop("num_hrs_guess", $est);
			$bo->save();
		}
	}

	/**
		@attrib name=f
	**/
	function disp_wh($arr)
	{
		classload("core/date/date_calc");
		$coms = new object_list(array(
			"class_id" => CL_BUG_COMMENT,
			"lang_id" => array(),
			"site_id" => array(),
			"created" => new obj_predicate_compare(OBJ_COMP_GREATER, get_week_start()/*-7*3600*24*/),
			"sort_by" => "objects.createdby, objects.created"
		));
//		echo "com count = ".$coms->count()." <br>";
echo "<div style='font-size: 10px;'>";
		$i = array("marko" => "", "dragut" => "", "tarvo" => "", "sander" => "");
		foreach($coms->arr() as $com)
		{
			if ($com->createdby() == "")
			{
				// parse from cvs
				$tx = $com->comment();
				if (preg_match("/cvs commit by ([^ ]+) in/imsU", $tx, $mt))
				{
					$uid = $mt[1];
					if ($uid == "kristo")
					{
						$uid = "kix";
					}
					if ($uid == "markop")
					{
						$uid = "marko";
					}
					$com_by_p[$uid][] = $com;
				}
				else
				{
					echo "error, comment $tx no uid <br>";
				}
			}
			else
			{
				$com_by_p[$com->createdby()][] = $com;
				$uid = $com->createdby();
			}
			if (!isset($i[$uid]))
			{
				continue;
			}

			//echo date("d.m.Y H:i", $com->created())." ".$uid."<br>".substr(nl2br($com->comment()), 0, 200)."<hr>";
			$bs[$uid][] = array(
				"t" => $com->created(),
				"c" => $com->comment(),
				"p" => $com->parent()
			);
			$wh[$uid] += $com->prop("add_wh");
		}
		asort($bs);
		foreach($bs as $uid => $bgs)
		{
			echo "$uid has ".count($bgs)." comments wh = ".$wh[$uid]."<br><br>";
			foreach($bgs as $bg)
			{
				$o = obj($bg["p"]);
				echo "bug ".$o->name()." - ".nl2br($bg["c"])."<hr>";
			}
		}
die();
		// calc work hrs per p
		foreach($com_by_p as $uid => $coms)
		{
			$tm = 0;
			foreach($coms as $com)
			{
				$tm += $com->prop("add_wh");
			}
			echo "tot wh for $uid => $tm <br>";
		}
		die();
	}

	function _add_custs_to_tree(&$t)
	{
		$co = get_current_company();
		$this->_req_cust_tree($t, $co, 0);
	}

	function _req_cust_tree(&$t, $co, $pt)
	{
		foreach($co->connections_from(array("type" => "RELTYPE_CATEGORY")) as $c)
		{
			$t->add_item($pt, array(
				"id" => $c->prop("to"),
				"name" => $this->name_cut($c->prop("to.name")),
				"url" => aw_url_change_var("cust", null)
			));
			$this->_req_cust_tree($t, $c->to(), $c->prop("to"));
		}

		foreach($co->connections_from(array("type" => "RELTYPE_CUSTOMER")) as $c)
		{
			$t->add_item($pt, array(
				"id" => $c->prop("to"),
				"name" => $this->name_cut($c->prop("to.name")),
				"url" => aw_url_change_var("cust", $c->prop("to")),
				"iconurl" => icons::get_icon_url(CL_CRM_COMPANY)
			));
		}
	}
}
?>
