<?php

class mailinglist_join_obj extends _int_object
{
	public function get_mailinglist()
	{
		$ol = new object_list(array(
			"class_id" => CL_ML_LIST,
			"site_id" => array(),
			"lang_id" => array(),
			"CL_ML_LIST.RELTYPE_JOIN" => $this->id(),
 		));
		if($ol->count())
		{
			return $ol->begin();
		}
		else
		{
			return null;
		}
	}

	public function get_menu_options()
	{
		$ml = $this->get_mailinglist();
		if(is_object($ml))
		{
			$ol = new object_list();
			$ol->add($ml->prop("choose_menu"));
			return $ol->names();
		}
		else
		{
			return array();
		}
	}

	public function get_menus()
	{
		$ol = new object_list();
		$choose_menu = $this->prop("choose_menus");
		$langid = aw_global_get("lang_id");	
		foreach($choose_menu as $folder)
		{
			$folder_obj = obj($folder);
			$folders = $folder_obj->connections_from(array(
				"type" => "RELTYPE_LANG_REL",
				"to.lang_id" => $langid,
			));

			if($langid == $folder_obj -> lang_id())
			{
				$ol->add($folder_obj->id());
			}
			else
			{
				if(count($folders)>1)
				{
					foreach($folders as $folder_conn)
					{
						$conn_fold_obj = obj($folder_conn->prop("to"));
						if(($langid == $conn_fold_obj->lang_id()) && ($folder_conn->prop("from") == $folder))
						{
							$ol->add($folder_conn->prop("to"));
						}
					}
				}
				else
				{
					$conns_to_orig = $folder_obj->connections_to(array(
						"type" => 22,
					));
					foreach($conns_to_orig as $conn)
					{
						if($conn->prop("from.lang_id") == $langid)
						{
							$ol->add($conn->prop("from"));
						}
						else 
						{
							$from_obj = obj($conn->prop("from"));
							$conns = $from_obj->connections_from(array(
								"type" => "RELTYPE_LANG_REL",
								"to.lang_id" => $user_lang,
							));
							foreach($conns as $conn)
							{
								$ol->add($conn->prop("to"));
							}			
						}
					}						
				}
			}
		}
		return $ol;
	}

	public function subscribe($arr)
	{
		$erx = array();
		$ml_member = get_instance(CL_ML_MEMBER);
		$use_folders = array();
		$allow = false;

		$choose_menu = $this->prop("choose_menu");	
		// need to check those folders
		foreach($choose_menu as $menu_id => $menu)
		{
			if(!is_oid($menu) || !$GLOBALS["object_loader"]->cache->can("add" , $menu))
			{
				unset($choose_menu[$menu_id]);	
			}
		}

//liitutavad kaustad
		if (!empty($args["subscr_folder"]))
		{
			// check the list of selected folders against the actual connections to folders
			// and ignore ones that are not connected - e.g. don't take candy from strangers
			foreach($args["subscr_folder"] as $ml_connect=>$ml_id)
			{ 
				if (in_array($ml_connect , $choose_menu))
				{
					$use_folders[] = $ml_connect;
				}
			}
			if (sizeof($use_folders) > 0)
			{
				$allow = true;
			}
		}
		else
		{
			$use_folders = $this->prop("choose_menu");
			$allow = true;
		};

//valitud keelte j2rgi teeb ka korrektuurid
		if(is_array($args["subscr_lang"]))
		{
			$lang_id = aw_global_get("lang_id");
			$temp_use_folders = array();
			foreach ($args["subscr_lang"] as $user_lang => $user_lang_id)
			{
				foreach ($use_folders as $folder_id => $val)
				{
					if ($user_lang == $lang_id)
					{
						$temp_use_folders[] = $val;
					}
					else
					{
						$o = obj($val);
						$conns = $o->connections_from(array(
							"type" => "RELTYPE_LANG_REL",
							"to.lang_id" => $user_lang,
						));
						
						if(count($conns)<1)
						{
							$conns_to_orig = $o->connections_to(array(
								"type" => 22,
							));
						}
						
						foreach($conns_to_orig as $conn)
						{
							if($conn->prop("from.lang_id") == $user_lang)
							{
							 $temp_use_folders[] = $conn->prop("from");
							}
							else {
								$from_obj = obj($conn->prop("from"));
								$conns = $from_obj->connections_from(array(
									"type" => "RELTYPE_LANG_REL",
									"to.lang_id" => $user_lang,
								));
								foreach($conns as $conn)
								{
									$temp_use_folders[] = $conn->prop("to");
								}			
							}
						}						
						foreach($conns as $conn)
						{
							$temp_use_folders[] = $conn->prop("to");
						}			
					}
				}
			}
			$use_folders = $temp_use_folders;
		}

//kontrollib kas mailiaadress on ikka normaalne
		if(!is_email($args["mail"]))
		{
			if(empty($args["name"]) && empty($args["firstname"]) && empty($args["lastname"]))
			{
				$allow = false;
				$erx["XXX"]["msg"] = t("Liitumisel vaja ka nime");
			}
		}

		$request = $args;
		if (is_array($args["udef_txbox"]))
		{
			foreach($args["udef_txbox"] as $key => $val)
			{
				$request["udef_txbox" . $key] = $val;
			}
		}

		$list_obj = $this->get_mailinglist();
		$cfgform = $list_obj->prop("member_config");
		$errors = $ml_member->validate_data(array(
			"request" => $request,
			"cfgform_id" => $cfgform,
		));

		
		foreach($use_folders as $key => $folder)
		{
			$members = $this->get_all_members(array($folder));
			if(in_array($args["mail"], $members) || in_array($args["email"], $members))
			{
				unset($use_folders[$key]);
			}
		}
	
		if(count($use_folders) < 1)
		{
			$allow = false;
			$erx["XXX"]["msg"] = t("Sellise aadressiga inimene on juba valitud listidega liitunud");
		}
		
		if(empty($args["name"]))
		{
			$args["name"] = $args["firstname"].' '.$args["lastname"];
		}
		
		if(empty($args["name"]) && empty($args["firstname"]) && empty($args["lastname"]))
		{
			$allow = false;
			$erx["XXX"]["msg"] = t("Liitumisel vaja ka nime");
		}
		
		if(empty($args["email"]))
		{
			$allow = false;
			$erx["XXX"]["msg"] = t("Liitumisel vaja t&auml;ita aadressi v&auml;li");
		}
		
		if (sizeof($errors) > 0 || !$allow)
		{
			$errors = $errors + $erx;
			$errmsg = "";
			foreach($errors as $errprop)
			{
				$errmsg .= $errprop["msg"] . "<br>";
			}

			aw_session_set("no_cache", 1);
			//arr($errmsg);
			//* fsck me plenty
			$request["mail"] = $_POST["mail"];
			aw_session_set("cb_reqdata", $request);
			aw_session_set("cb_errmsg", $errmsg);
			aw_session_set("cb_errmsgs", $errors);
			return aw_global_get("HTTP_REFERER");
		};

		if ($allow === true)
		{
			if ($this->prop("sub_form_type") == 1)
			{
				$retval = $ml_member->unsubscribe_member_from_list(array(
					"use_folders" => array_keys($args["subscr_folder"]),
					"email" => $args["email"],
					"list_id" => $list_obj->id(),
					"confirm_message" => $this->prop("confirm_msg"),
				));
			}
			else
			{
				$retval = $ml_member->subscribe_member_to_list(array(
					"firstname" => $args["firstname"],
					"lastname" => $args["lastname"],
					"name" => $args["name"],
					"email" => $args["mail"],
					"use_folders" => $use_folders,
					"list_id" => $list_obj->id(),
					"confirm_subscribe" => $this->prop("confirm"),
					"confirm_message" => $this->prop("confirm_msg"),
					"udef_fields" => $udef_fields,
				));
			}
		}
	}

	private function get_all_members($id)
	{
		$member_list = array();
		$mem_list = new object_list(array(
			"parent" => $id,
			"class_id" => CL_ML_MEMBER,
		));
		foreach($mem_list->arr() as $mem)
		{
			$member_list[$mem->prop("name")] = $mem->prop("mail");
		}
		return $member_list;
	}


}

?>
