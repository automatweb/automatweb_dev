<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/commune/Attic/commune.aw,v 1.1 2004/06/02 10:21:36 duke Exp $
// commune.aw - Kommuun 
/*

@classinfo syslog_type=ST_COMMUNE relationmgr=yes layout=boxed

@property join_obj type=relpicker reltype=RELTYPE_JOIN_OBJ method=serialize field=meta group=general table=objects
@caption Liitumisvorm

@property my_profile type=callback callback=callback_my_profile no_caption=1 group=profile store=no
@caption Minu profiil

@property my_images type=callback callback=callback_my_images no_caption=1 group=my_images store=no
@caption Minu pildid

@property rateform type=form sclass=applications/commune/image_rate sform=rate group=rate store=no
@caption Hindamine2

@property locations type=callback callback=callback_get_locations no_caption=1 group=locations store=no
@caption Asukohad

@groupinfo profile caption="Minu profiil"
@groupinfo my_images caption="Minu Pildid"
@groupinfo rate caption="Hindamine" no_submit=1
@groupinfo locations caption="Sisuobjektid"
@groupinfo messages caption="Teated"
@groupinfo newmessage caption="Uus teade" parent=messages
@groupinfo inbox caption="Inbox" parent=messages
@groupinfo outbox caption="Outbox" parent=messages

@property newmessage type=form store=no group=newmessage sclass=applications/quickmessage/quickmessage 
@caption Uue teate kirjutamine

@property inbox type=text store=no group=inbox
@caption Inbox

@property outbox type=text store=no group=outbox
@caption Outbox

@groupinfo join caption="Liitumine"
@property join type=callback group=join  callback=callback_get_join store=no


@default table=objects
@default group=general

@reltype CONTENT value=1 clid=CL_PROMO,CL_MENU_AREA
@caption Sisuelement

@reltype LAYOUT_LOGO value=10 clid=CL_IMAGE
@caption Kujunduse logo

@reltype JOIN_OBJ value=2 clid=CL_JOIN_SITE
@caption liitumisvorm

*/

class commune extends class_base
{
	function commune()
	{
		$this->init(array(
			"clid" => CL_COMMUNE,
			"tpldir" => "applications/commune/commune"
		));
		
		$this->fields_from_person = array("firstname","lastname","birthday","gender");
		$this->fields_from_profile = array("user_blob2","user_check2","user_blob1","user_text5","user_text4","user_field1","user_text1","user_text3","user_check1","user_field2","user_text2","height","weight","hair_type","sexual_orientation","eyes_color","hair_color","alcohol","tobacco","body_type");
	}
	
	function callback_on_load($arr)
	{
		//$this->cfgmanager = 701;
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
                $retval = PROP_OK;
                switch($prop["name"])
                {
                        case "tabpanel":
                                $logos = $arr["obj_inst"]->connections_from(array(
                                        "type" => "RELTYPE_LAYOUT_LOGO",
                                ));

				if (sizeof($logos) > 0)
				{
					$first_logo = reset($logos);

					$t = get_instance(CL_IMAGE);
					$prop["vcl_inst"]->set_style("with_logo");
					$prop["vcl_inst"]->configure(array(
						"logo_image" => $t->get_url_by_id($first_logo->prop("to")),
					));
				};

			case "inbox":
				$prop["value"] = $this->create_inbox($arr);
				break;
			
			case "outbox":
				$prop["value"] = $this->create_outbox($arr);
				break;

                        break;
		};
		return $retval;
	}

	function callback_my_images($arr)
	{
		$users = get_instance("users");
		$user = new object($users->get_oid_for_uid(aw_global_get("uid")));
		
		$t = get_instance(CL_CRM_PERSON);
		$t->init_class_base();
		$props = $t->get_property_group(array(
			"group" => "show",
		));

		// check whether a person object exists for her
		$persons = $user->connections_from(array(
			"type" => "RELTYPE_PERSON",
		));

		$prof_obj = $this->_get_profile_obj();
		if (!$prof_obj)
		{
			die("Viga, sellel kasutaja pole profiili");
		};

		$images = $prof_obj->connections_from(array(
			"type" => 12,
		));

		// kõik isiku objektid?

		/*
		if (sizeof($persons) > 0)
		{
			$po = reset($persons);
			$poo = $po->to();

			$conns = $poo->connections_from(array(
				"type" => "RELTYPE_PICTURE",
			));

			if (sizeof($conns) > 0)
			{
				$img_c = reset($conns);
				$rt = get_instance(CL_RATE);
				$h1 = $rt->get_rating_for_object($img_c->prop("from"));
				$hx = $img_c->prop("from");
				$q = "SELECT hits FROM hits WHERE oid = '$hx'";
				$this->db_query($q);
				$row = $this->db_next();
				$h2 = $row["hits"];
			}
		};
		*/

		$rv = array();
		$n = 5;
		$icount = sizeof($images);
		if ($icount > $n)
		{
			$icount = $n;
		};

		$ims = array_values($images);

		$ti = get_instance(CL_IMAGE);

		for ($i = 1; $i <= 5; $i++)
		{
			$name3 = "s" . $i;
			$rv[$name3] = array(
				"type" => "text",
				"name" => $name3,
				"caption" => "Pilt $i",
				"subtitle" => 1,
			);
			$key = $i;
			$new = false;
			if (is_object($ims[$i-1]))
			{
				$new = true;
				$target = $ims[$i-1]->to();
				$name4 = "st" . $i;
				$rv[$name4] = array(
					"type" => "text",
					"name" => $name4,
					"caption" => "Pilt",
					"value" => html::img(array(
						"url" => $ti->get_url_by_id($ims[$i-1]->prop("to")),
					)),
				);
				$key = $ims[$i-1]->prop("id");
				$name1 = "myimage_file". $i;
				$name2 = "myimage_comment" . $i;
				// but the key _needs_ to be unique!
				$rv[$name1] = array(
					"name" => "myimage[$key][file]",
					"type" => "fileupload",
					"caption" => "Vali uus",
				);
				$rv[$name2] = array(
					"name" => "myimage[$key][comment]",
					"type" => "textarea",
					"caption" => "Pildi kommentaar",
					"value" => $target->comment(),
				);
				$name5 = "d" . $i;
				$rv[$name5] = array(
					"name" => "delete[" . $key . "]",
					"type" => "checkbox",
					"caption" => "Kustuta",
				);
			}
			else
			{
				$name1 = "newimage_file". $i;
				$name2 = "newimage_comment" . $i;
				$rv[$name1] = array(
					"name" => "newimage[$i][file]",
					"type" => "fileupload",
					"caption" => "Vali uus",
				);
				$rv[$name2] = array(
					"name" => "newimage[$i][comment]",
					"type" => "textarea",
					"caption" => "Pildi kommentaar",
				);
			};

		};

		// how tha FUCK do i do this?

		// riiight, I need to return an arbitrary number of releditor and btw, later on I also
		// need to keep the order

		//$rv = array("images" => $props["images"]);
		/*
		$x = $t->parse_properties(array(
			"properties" => $rv,
			"obj_inst" => $poo,
		));
		$x["ht"] = array(
			"name" => "ht",
			"type" => "text",
			"value" => "<h3>Vaatamisi: $h2, Hinne: $h1</h3>",
		);
		*/
		return $rv;
	}

	function callback_rate($arr)
	{
		$q = "SELECT profile2image.* FROM profile2image LEFT JOIN objects ON (profile2image.img_id = objects.oid) WHERE objects.status = 2 ORDER BY rand()";
		$this->db_query($q);
		$row = $this->db_next();

		$victim = new object($row["prof_id"]);

		/*
		$persons = new object_list(array(
			"class_id" => CL_CRM_PERSON,
			"lang_id" => array(),
		));
		*/

		/*
		$ids = $persons->ids();

		$xrand = array_rand($ids);

		$victim = new object($ids[$xrand]);
		*/

		$rv = "";
		$rv .=  "<h3>" . $victim->prop("firstname") . " " . $victim->prop("lastname") . "</h3>";
	
		/*
		$conns = $victim->connections_from(array(
			"type" => "RELTYPE_PICTURE",
		));
		*/

		$img_id = $row["img_id"];
		$i = get_instance(CL_IMAGE);
		$imgdata = $i->get_image_by_id($row["img_id"]);

		$rv .= html::img(array(
			"url" => $imgdata["url"],
		));
		$this->add_hit($victim->id());

		/*
		if (sizeof($conns) > 0)
		{
			$img_c = reset($conns);
			$i = get_instance(CL_IMAGE);
			$imgdata = $i->get_image_by_id($img_c->prop("to"));
			$rv .= html::img(array(
				"url" => $imgdata["url"],
			));
			$this->add_hit($victim->id());
			$img_id = $img_c->prop("to");
		};
		*/

		$rs = new object(598);

		$scale = array(
			"type" => "chooser",
			"options" => array(
				"1" => "1",
				"2" => "2",
				"3" => "3",
				"4" => "4",
				"5" => "5",
			),
			"name" => "scale",
		);

		$pic_id = array(
			"type" => "hidden",
			"name" => "pic_id",
			"value" => $img_id,
		);
		$prop = array(
			"name" => "rate",
			"type" => "text",
			"value" => $rv,
		);
		return array("name" => $prop, "scale" => $scale, "pic_id" => $pic_id);
	}

	function callback_my_profile($arr)
	{
		$prop = $arr["prop"];
		$rv = array();
		$t = get_instance(CL_CRM_PERSON);
		$t->init_class_base();
		$props = $t->get_property_group(array(
			"group" => "general",
		));
		
		// first I need the object of active user
		$users = get_instance("users");
		$user = new object($users->get_oid_for_uid(aw_global_get("uid")));

		// check whether a person object exists for her
		$persons = $user->connections_from(array(
			"type" => "RELTYPE_PERSON",
		));
		
		if (sizeof($persons) > 0)
		{
			// use existing
			$person_id = reset($persons);
			$o = new object($person_id->prop("to"));

			$po = $o->connections_from(array(
				"type" => 14,
			));
			if (sizeof($po) > 0)
			{
				$pox = reset($po);
				$po_object = $pox->to();
			};
		};

		foreach($props as $prop)
		{
			if (in_array($prop["name"],$this->fields_from_person))
			{
				$rv[$prop["name"]] = $prop;
			};
		};

		$rv = $t->parse_properties(array(
			"properties" => $rv,
			"obj_inst" => $o,
		));

		$t2 = get_instance(CL_PROFIIL);
		$t2->init_class_base();
		$props = $t2->get_property_group(array(
			"group" => "settings",
		));

		$rv2 = array();

		foreach($props as $prop)
		{
			if (in_array($prop["name"],$this->fields_from_profile))
			{
				$rv2[$prop["name"]] = $prop;
			};
		};

		$res = $t2->parse_properties(array(
			"properties" => $rv2,
			"obj_inst" => $po_object,
		));

		foreach($res as $item)
		{
			$rv[$item["name"]] = $item;
		};

		return $rv;


	}

	function callback_get_locations($arr)
	{
		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => RELTYPE_CONTENT,
		));

		$old = $arr["obj_inst"]->meta("location");

		$rv = array();
		foreach($conns as $conn)
		{
			$target = $conn->to();
			$name = $target->name();
			$id = $target->id();
			$rv["title_" . $id] = array(
				"caption" => "Objekt",
				"type" => "text",
				"name" => "title_" . $id,
				"value" => $name,
			);

			$rv["location_" . $id] = array(
				"caption" => "Asukoht",
				"type" => "chooser",
				"name" => "location[" . $id . "]",
				"options" => array("top" => "üleval","left" => "vasakul","right" => "paremal","bottom" => "all"),
				"value" => $old[$id],
			);
		};

		return $rv;
	}

	function update_locations($arr)
	{
		$arr["obj_inst"]->set_meta("location",$arr["request"]["location"]);
		// now I have got saving working properly .. I only need to add those elements to the classbase
		// generated form. How?
	}

	function set_property($arr)
	{
		$prop = $arr["prop"];
		$rv = PROP_OK;
		switch($prop["name"])
		{
			case "my_profile":
				$this->update_profile($arr);
				break;

			case "my_images":
				$this->update_my_images($arr);
				break;

			case "rateform":
				$this->add_rate($arr);
				break;

			case "locations":
				$this->update_locations($arr);
				break;

			case "newmessage":
				$this->create_message($arr);
				break;

			case "join":
				$j_oid = $arr["obj_inst"]->prop("join_obj");
				if ($j_oid)
				{
					$tmp = $arr["request"];
					$tmp["id"] = $j_oid;
					if (aw_global_get("uid") == "")
					{
						$ji = get_instance("contentmgmt/join/join_site");
						$url = $ji->submit_join_form($tmp);
						if ($url != "")
						{
							header("Location: $url");
							die();
						}
					}
					else
					{
						$ji = get_instance("contentmgmt/join/join_site");
						$ji->submit_update_form($tmp);
					}
				}
				break;
		};
		return $rv;
	}

	function add_rate($arr)
	{
		if (!empty($arr["request"]["rateform"]["img_id"]))
		{
			// XXX: check whether this is a valid image to be voted for
			$rt = get_instance(CL_RATE);
			$rt->add_rate(array(
				"oid" => $arr["request"]["rateform"]["img_id"],
				"rate" => $arr["request"]["rateform"]["rate"],
				"no_redir" => 1,
			));
			$this->add_hit($arr["request"]["rateform"]["img_id"]);
		};

		if (!empty($arr["request"]["rateform"]["comments"]["comment"]))
		{
			$commdata = $arr["request"]["rateform"]["comments"];
			$comm = get_instance(CL_COMMENT);
			$nc = $comm->submit(array(
				"parent" => $commdata["obj_id"],
				"commtext" => $commdata["comment"],
				"return" => "id",
			));
		};
	}

	function update_my_images($arr)
	{
		$to_replace = $_FILES["myimage"]["tmp_name"];

		$to_delete = $arr["request"]["delete"];

		$to_add = $_FILES["newimage"]["tmp_name"];


		// images are connected to the profile

		// I need connection id-s for existing images, no?
		
		// check whether a person object exists for her

		$profile_obj = $this->_get_profile_obj();

		if (is_object($profile_obj) && is_oid($profile_obj->id()))
		{
			/*
			print "found a valid profile object";
			print "<pre>";
			print_r($profile_obj->properties());
			print "</pre>";
			*/
		}
		else
		{
			// since we could not find a valid profile object, then
			// do nothing

			// XXX: make it so that we cannot reach this point if there is no valid profile
			return false;
		}

		$t = get_instance(CL_IMAGE);


		// okey, now I need to submit things


		//$tmp_file_inf = $_FILES["myimage"]["tmp_name"];


		// XXX: should I check the error information in $_FILES?
		if (is_array($to_add))
		{
			foreach($to_add as $key => $tmp_name)
			{
				$tn = $tmp_name["file"];
				if (is_uploaded_file($tn))
				{
					// only add an image if a file is present
					$argblock = array(
						"file" => array(
							"name" => $_FILES["newimage"]["name"][$key]["file"],
							"contents" => base64_encode(file_get_contents($tn)),
							"type" => $_FILES["newimage"]["type"][$key]["file"],
						),
						"comment" => $arr["request"]["newimage"][$key]["comment"],
						"parent" => $profile_obj->id(),
						"return" => "id",
					);

					// need on uued pildid
					$img_id = $t->submit($argblock);
					$profile_obj->connect(array(
						"to" => $img_id,
						"reltype" => 12, // RELTYPE_IMAGE
					));
					$prof_id = $profile_obj->id();
					$q = "INSERT INTO profile2image VALUES ($prof_id,$img_id)";
					$this->db_query($q);
				};
			};
		};

		$images = $profile_obj->connections_from(array(
			"type" => 12,
		));

	
		if (is_array($to_replace))
		{
			foreach($to_replace as $key => $tmp_name)
			{
				$tn = $tmp_name["file"];

				if ($to_delete[$key])
				{
					$o = new object($images[$key]->prop("to"));
					$o->delete();
				}
				else
				{
					$argblock = array(
						"id" => $images[$key]->prop("to"),
						"comment" => $arr["request"]["myimage"][$key]["comment"],
						"return" => "id",
					);

					if (is_uploaded_file($tn))
					{
						$argblock["file"] = array(
								"name" => $_FILES["myimage"]["name"][$key]["file"],
								"contents" => base64_encode(file_get_contents($tn)),
								"type" => $_FILES["myimage"]["type"][$key]["file"],
						);
					};
					$img_id = $t->submit($argblock);
				};
			};
		};
		return false;
	}

	function update_profile($arr)
	{
		// first I need the object of active user
		$users = get_instance("users");
		$user = new object($users->get_oid_for_uid(aw_global_get("uid")));

		// check whether a person object exists for her
		$persons = $user->connections_from(array(
			"type" => 2,
		));

		$new = false;

		if (sizeof($persons) > 0)
		{
			// use existing
			$person_id = reset($persons);
			$o = new object($person_id->prop("to"));
		}
		else
		{
			// create new
			$new = true;
			$o = new object();
			$o->set_class_id(CL_CRM_PERSON);
			$o->set_parent($user->parent());
			$o->set_status(STAT_ACTIVE);
		}

		foreach($this->fields_from_person as $field)
		{
			$o->set_prop($field,$arr["request"][$field]);
		};
		$o->save();

		if ($new)
		{
			$user->connect(array(
				"to" => $o->id(),
				"reltype" => 2,
			));
		};

		$profs = $o->connections_from(array(
			"type" => 14,
		));

		$new = false;
		$clinst = get_instance(CL_PROFIIL);
		$vars = array();
		$vars["group"] = "settings";
		$vars["return"] = "id";
		if (sizeof($profs) > 0)
		{
			$prof_id = reset($profs);
			$po = new object($prof_id->prop("to"));
			$vars["id"] = $po->id();
		}
		else
		{
			//$po = new object();
			$vars["parent"] = $user->parent();
			$vars["status"] = STAT_ACTIVE;
			$new = true;
		};

		foreach($this->fields_from_profile as $field)
		{
			$vars[$field] = $arr["request"][$field];
		}

		$id = $clinst->submit($vars);

		if ($new)
		{
			$o->connect(array(
				"to" => $id,
				"reltype" => 14,
			));
		};
		
		$o->save();
			
		// now I need to submit the god damn thing

	}

	function get_content_elements($arr)
	{
		$obj_inst = $arr["obj_inst"];
		$els = $obj_inst->connections_from(array(
			"type" => RELTYPE_CONTENT,
		));
		$locations = $obj_inst->meta("location");
		$rv = array();
		foreach($els as $el)
		{
			$to = $el->prop("to");
			if ($locations[$to])
			{
				//$rv[$to] = $locations[$to];
				$to_obj = $el->to();
				$ct = "";
				if (CL_PROMO == $to_obj->class_id())
				{
					$clinst = get_instance(CL_PROMO);
					$ct = $clinst->parse_alias(array(
						"alias" => array(
							"target" => $to,
						),
					));
				};
				if (CL_MENU_AREA == $to_obj->class_id())
				{
					$ss = get_instance("contentmgmt/site_show");
					$rf = $to_obj->prop("root_folder");
					$ct = $ss->do_show_menu_template(array(
						"template" => "menus.tpl",
						"mdefs" => array(
							$rf => "YLEMINE"
						)
                               		 ));
				};
				$rv[$locations[$to]] .=  $ct;
			};

			// now, how do I get that thing?
		};
		return $rv;
	}

	function _get_profile_obj()
	{
		$users = get_instance("users");
		$user = new object($users->get_oid_for_uid(aw_global_get("uid")));

		$persons = $user->connections_from(array(
			"type" => "RELTYPE_PERSON",
		));

		$profile_obj = false;

		if (sizeof($persons) > 0)
		{
			list(,$tmp) = each($persons);
			$person_obj = $tmp->to();

			$prof_connections = $person_obj->connections_from(array(
				"type" => "RELTYPE_PROFILE",
			));

			if (sizeof($prof_connections) > 0)
			{
				list(,$tmp) = each($prof_connections);
				$profile_obj = $tmp->to();
			};

		};

		return $profile_obj;
	}

	function callback_get_join($arr)
	{
		aw_global_set("no_cache", 1);
		$j_oid = $arr["obj_inst"]->prop("join_obj");
		if ($j_oid)
		{
			$join = obj($j_oid);
	
			$ji = get_instance("contentmgmt/join/join_site");
			$pps = $ji->get_elements_from_obj($join, array(
				"err_return_url" => aw_ini_get("baseurl").aw_global_get("REQUEST_URI")
			));
			if (aw_global_get("uid") == "")
			{	
				$pps["join_butt"] = array(
					"name" => "join_butt",
					"type" => "submit",
					"caption" => "Liitu!"
				);
			}
			else
			{
				$pps["upd_butt"] = array(
					"name" => "upd_butt",
					"type" => "submit",
					"caption" => "Uuenda andmed!"
				);
			}
			return $pps;
		}
		return array();
	}

	function create_message($arr)
	{
		$msgdata = $arr["prop"]["value"];
		$users = get_instance("users");
		$u_id = $users->get_oid_for_uid(aw_global_get("uid"));
		$t_id = $users->get_oid_for_uid($msgdata["user_to"]);
		if (empty($t_id))
		{
			die("aga sellist kasutajat pole üldse olemas");
		};
		$user = new object($u_id);
		$o = new object();
		$o->set_class_id(CL_QUICKMESSAGE);
		$o->set_parent($u_id);
		$o->set_status(STAT_ACTIVE);
		// need to resolve it!
		$o->set_prop("user_from",$u_id);
		$o->set_prop("user_to",$t_id);
		$o->set_prop("subject",$msgdata["subject"]);
		$o->set_prop("content",$msgdata["content"]);
		$o->save();
		// now, I need a parent! for that I take a profile

	}


	/**

		@attrib name=change nologin=1 all_args=1

	**/
	function change($arr)
	{
		return parent::change($arr);
	}

	/**

		@attrib name=submit nologin=1

	**/
	function submit($arr)
	{
		return parent::submit($arr);
	}

	function create_inbox($arr)
	{
		$ti = get_instance(CL_QUICKMESSAGE);
		$users = get_instance("users");
		$msgs = $ti->get_inbox_for_user(array(
			"user_to" => $users->get_oid_for_uid(aw_global_get("uid")),
		));
		$this->read_template("show_mailbox.tpl");
		$rv = "";
		if (is_array($msgs))
		{
			foreach($msgs as $msg)
			{
				$this->vars(array(
					"from" => $users->get_uid_for_oid($msg["user_from"]),
					"subject" => $msg["subject"],
					"content" => nl2br($msg["content"]),
				));
				$rv .= $this->parse("message");
			}
		};

		$this->vars(array(
			"message" => $rv,
		));
		return $this->parse();
	}

	function create_outbox($arr)
	{
		$ti = get_instance(CL_QUICKMESSAGE);
		$users = get_instance("users");
		$msgs = $ti->get_outbox_for_user(array(
			"user_from" => $users->get_oid_for_uid(aw_global_get("uid")),
		));
		$rv = "";
		$this->read_template("show_outbox.tpl");
		if (is_array($msgs))
		{
			foreach($msgs as $msg)
			{
				$this->vars(array(
					"to" => $users->get_uid_for_oid($msg["user_to"]),
					"subject" => $msg["subject"],
					"content" => nl2br($msg["content"]),
				));
				$rv .= $this->parse("message");
			}

		};

		$this->vars(array(
			"message" => $rv,
		));
		return $this->parse();
	}
	/**

		@attrib name=show_profile

		@param id required type=int acl=view
	**/
	function show_profile($arr)
	{
		$this->read_template("show_profile.tpl");

		$p_o = obj($arr["id"]);
		foreach($p_o->properties() as $pn => $pd)
		{
			$this->vars(array(
				"person.".$pn => $p_o->prop($pn)
			));
		}
		
		$po = $p_o->connections_from(array(
			"type" => 14,
		));
		if (sizeof($po) > 0)
		{
			$pox = reset($po);
			$po_object = $pox->to();
			list($properties, $tableinfo, $relinfo) = $GLOBALS["object_loader"]->load_properties(array(
				"clid" => CL_PROFIIL
			));
			
			foreach($properties as $pn => $pd)
			{
				$v = $po_object->prop($pn);
				if ($pd["type"] == "classificator" && $pd["store"] == "connect")
				{
					// get the first connection of that type
					$c = reset($po_object->connections_from(array("type" => $pd["reltype"])));
					$v = $c->prop("to.name");
				}
				else
				if ($pd["type"] == "classificator" && $v)
				{
					$tmp = obj($v);
					$v = $tmp->name();
				}
				$this->vars(array(
					"profile.".$pn => $v
				));
			}

			$i = get_instance("image");
			$img = "";
			foreach($po_object->connections_from(array("type" => "RELTYPE_IMAGE")) as $c)
			{
				$imgo = $c->to();
				$url = $i->get_url_by_id($c->prop("to"));
				$this->vars(array(
					"image_url" => $url,
					"image" => image::make_img_tag($url, $imgo->prop("alt")),
					"comment" => $imgo->comment(),
				));
				$img .= $this->parse("IMAGE");
			}
			$this->vars(array(
				"IMAGE" => $img
			));
		};

		return $this->parse();
	}
}
?>
