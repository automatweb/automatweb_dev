<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/commune/Attic/rate_moderation.aw,v 1.5 2005/04/01 11:52:22 kristo Exp $
// rate_moderation.aw - Hindamise modereerimine 
/*

@classinfo syslog_type=ST_RATE_MODERATION relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general
@default method=serialize
@default field=meta

@property expiration type=text
@caption Kui mitme sekundi pärast modereerimismärge aegub

//@property expiration type=textbox datatype=int
//@caption Kui mitme sekundi pärast modereerimismärge aegub

@property commune type=relpicker reltype=RELTYPE_COMMUNE
@caption Seotud kommuun

@property pic_folder type=relpicker reltype=RELTYPE_PIC_FOLDER multiple=1
@caption Piltide kataloog

@groupinfo new_pictures caption="Uued pildid" submit=no

@property mod_toolbar type=toolbar store=no no_caption=1 group=new_pictures,mod_pictures
@caption Tuulbaar

@property mod_table type=table store=no no_caption=1 group=new_pictures,mod_pictures
@caption Tabel

@groupinfo mod_pictures caption="Muudetud pildid" submit=no

@reltype PIC_FOLDER value=1 clid=CL_MENU
@caption Piltide kataloog

@reltype COMMUNE value=2 clid=CL_COMMUNE
@caption Seotud kommuun

*/

define("M_OBJ_IS_SELECTED", 1);
define("M_OBJ_IS_MSELECTED", 2);
define("M_OBJ_IS_MODIFIED", 3);
define("M_OBJ_IS_DONE", 4);

class rate_moderation extends class_base
{
	function rate_moderation()
	{
		$this->init(array(
			"tpldir" => "applications/commune/rate_moderation",
			"clid" => CL_RATE_MODERATION
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "expiration":
				$prop["value"] = 600;
				break;
			case "mod_table":
				$this->show_mod_table($arr);
				break;
			case "mod_toolbar":
				$tb = &$prop["vcl_inst"];
				$tb->add_button(array(
					"name" => "active",
					"tooltip" => t("Salvesta muudatused"),
					"img" => "save.gif",
					"action" => "mod_images",
				));
				break;
		}
		return $retval;
	}
	
	function show_mod_table($arr)
	{
		$age = 600;
		$group = $arr["request"]["group"];
		if($group == "new_pictures")
		{
			$mask = M_OBJ_IS_SELECTED;
			$mask2 = 0;
			$tm = "nw_time";
			$pcs = "nw_pics";
		}
		else
		{
			$mask = M_OBJ_IS_MSELECTED;
			$mask2 = M_OBJ_IS_MODIFIED;
			$tm = "mod_time";
			$pcs = "mod_pics";
		}
		$objs = new object_list(array(
			"parent" => $arr["obj_inst"]->prop("pic_folder"),
			"subclass" =>  $mask,
			"status" => STAT_NOTACTIVE,
			"modified" => new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, (time() - $age)),
		));
		foreach($objs->arr() as $obj)
		{
			$obj->set_subclass($mask2);
			$obj->save();
		}
		$array = array(
			"id" => "ID",
			"img" => t("Pilt"),
			"added" => t("Lisatud"),
			"user" => t("Kasutaja"),
			"name" => t("Nimi"),
			"desc" => t("Kirjeldus"),
			"comment" => t("Kommentaar"),
			"status" => t("Staatus"),
		);
		$t = &$arr["prop"]["vcl_inst"];
		$t->set_sortable(false);
		foreach($array as $key => $value)
		{
			$t->define_field(array(
				"name" => $key,
				"caption" => $value,
			));
		}
		$time = aw_global_get($tm);
		$ids = aw_global_get($pcs);
		$props = array(
			"parent" => $arr["obj_inst"]->prop("pic_folder"),
			"status" => STAT_NOTACTIVE,
			"sort_by" => "objects.created DESC",
			"limit" => 15,
		);
		if($time >= (time() - $age) && !empty($ids) && is_array($ids))
		{
			$props["oid"] = $ids;
		}
		else
		{
			$props["subclass"] = $mask2;
		}
		$imgs = new object_list($props);
		$img_i = get_instance(CL_IMAGE);
		$array = array(
			0 => t("Ei muuda"),
			STAT_ACTIVE => t("Aktiveeri"),
			STAT_NOTACTIVE => t("Lükka tagasi")
		);
		aw_session_set($pcs, $this->make_keys($imgs->ids()));
		aw_session_set($tm, time());
		foreach($imgs->arr() as $img)
		{
			$img->set_subclass($mask);
			$img->save();
			$profile = reset($img->connections_to(array(
				"type" => 12, //RELTYPE_IMAGE
				"from.class_id" => CL_PROFILE,
			)));
			$imgdata = $img_i->get_image_by_id($img->id());
			$user = $img->createdby();
			$comment = $img->get_first_obj_by_reltype("RELTYPE_MOD_COMMENT");
			$t->define_data(array(
				"id" => $img->id(),
				"img" => html::popup(array(
					"width" => 400,
					"height" => 400,
					"url" => $this->mk_my_orb("show_image", array(
						"img_id" => $img->id(),
					)),
					"caption" => html::img(array(
						"url" => $imgdata["url"],
						"border" => 0,
					)),
				)),
				"name" => $img->name(),
				"desc" => $img->comment(),
				"added" => get_lc_date($img->created(), 7),
				"user" => html::popup(array(
					"url" => str_replace("automatweb/orb.aw", "", html::get_change_url($arr["obj_inst"]->prop("commune"), array(
						"group" => "friend_details",
						"profile" => is_object($profile) ? $profile->prop("from") : "",
					))),
					"caption" => $user,
					"toolbar" => true,
					"directories" => true,
					"status" => true,
					"location" => true,
					"resizable" => true,
					"scrollbars" => true,
					"menubar" => true,
				)),
				"comment" => html::textarea(array(
					"name" => "com[".$img->id()."]",
					"cols" => 25,
					"rows" => 8,
					"value" => is_object($comment) ? $comment->prop("commtext") : "",
				)),
				"status" => html::select(array(
					"name" => "sel[".$img->id()."]",
					"options" => $array,
				)),
			));
		}
	}
	
	/**
		@attrib name=mod_images
		
		@param id required type=int acl=view
		@param group optional
		@param com optional
		@param mod optional
	**/
	function mod_images($arr)
	{
		$obj_inst = obj($arr["id"]);
		$rval = html::get_change_url($arr["id"], array("group" => $arr["group"]));
		$age = 600;
		if($arr["group"] == "new_pictures")
		{
			$tm = "nw_time";
			$pcs = "nw_pics";
		}
		else
		{
			$tm = "mod_time";
			$pcs = "mod_pics";
		}
		$time = aw_global_get($tm);
		if($time < time() - $age)
		{
			aw_session_del($tm);
			aw_session_del($pcs);
			return $rval;
		}
		aw_session_set($tm, time());
		if(is_array($arr["com"]))
		{
			foreach($arr["com"] as $id => $com)
			{
				if(is_oid($id) && $this->can("view", $id) && !empty($com))
				{
					$img = obj($id);
					$c = $img->get_first_obj_by_reltype("RELTYPE_MOD_COMMENT");
					if(!is_object($c))
					{
						$c = obj();
						$c->set_class_id(CL_COMMENT);
						$c->set_parent($id);
						$c->save();
						$img->connect(array(
							"to" => $c->id(),
							"reltype" => "RELTYPE_MOD_COMMENT",
						));
					}
					$c->set_prop("commtext", $com);
					$c->save();
				}
			}
		}
		if(is_array($arr["sel"]))
		{
			$disp = false;
			$qmb = get_instance(CL_QUICKMESSAGEBOX);
			$commune = $obj_inst->prop("commune");
			if(is_oid($commune) && $this->can("view", $commune))
			{
				$disp = true;
			}
			$u = get_instance("users");
			foreach($arr["sel"] as $id => $stat)
			{
				if(is_oid($id) && $this->can("view", $id))
				{
					if($stat == STAT_ACTIVE)
					{
						$pname = "pacpt";
						$img = obj($id);
						$user = obj($u->get_oid_for_uid($img->createdby()));
						$person = $user->get_first_obj_by_reltype("RELTYPE_PERSON");
						$props = $person->meta("message_conditions");
						if($disp && ($props[$pname][0] == true || $props[$pname][1] == true))
						{
							$qmb->dispatch_message(array(
								"from" => aw_global_get("uid_oid"),
								"msg1" => $props[$pname][0],
								"msg2" => $props[$pname][1],
								"commune" => obj($commune),
								"to" => $user,
								"prop" => $pname,
								//"message" => "",
							));
						}
						$img->set_status(STAT_ACTIVE);
						$img->set_subclass(0);
						$img->save();
						$com = $img->get_first_obj_by_reltype("RELTYPE_MOD_COMMENT");
						if(is_object($com))
						{
							$com->delete();
						}
						$ids = aw_global_get($pcs);
						unset($ids[$id]);
						if(empty($ids))
						{
							aw_session_del($pcs);
						}
						else
						{
							aw_session_set($pcs, $ids);
						}
					}
					elseif($stat == STAT_NOTACTIVE)
					{
						$img = obj($id);
						$img->set_subclass(M_OBJ_IS_DONE);
						$img->save();
						$ids = aw_global_get($pcs);
						unset($ids[$id]);
						if(empty($ids))
						{
							aw_session_del($pcs);
						}
						else
						{
							aw_session_set($pcs, $ids);
						}
					}
				}
			}
		}
		return $rval;
	}
	
	/**
		@attrib name=show_image
		@param img_id required type=int acl=view clid=CL_IMAGE
	**/
	function show_image($arr)
	{
		$img_i = get_instance(CL_IMAGE);
		$imgdata = $img_i->get_image_by_id($arr["img_id"]);
		return html::href(array(
			"url" => "javascript:void(0);",
			"onClick" => "window.close();",
			"caption" => html::img(array(
				"url" => $imgdata["big_url"],
				"border" => 0,
			)),
		));
	}
}
?>
