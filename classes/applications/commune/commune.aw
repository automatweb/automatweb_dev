<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/commune/Attic/commune.aw,v 1.9 2004/11/07 11:47:38 kristo Exp $
// commune.aw - Kommuun
/*

@classinfo syslog_type=ST_COMMUNE relationmgr=yes layout=boxed
-------------------------------------------------------------------------------------

@default group=general
@default table=objects
@default field=meta
@default method=serialize
-------------------------------------------------------------------------------------

@property locations type=callback callback=callback_get_locations no_caption=1 group=locations
@caption Asukohad

----------------------- MINA --------------------------

@groupinfo me caption="Mina" submit=no

----------------------- profiil --------------------------

@groupinfo profile caption="Profiil" parent=me tabgroup=left submit=no


@groupinfo profile_view caption="Vaata" parent=profile tabgroup=left submit=no

@property profile_view type=callback callback=callback_profile_view group=profile_view no_caption=1 store=no
@caption Vaata profiili


@groupinfo profile_change caption="Muuda" parent=profile tabgroup=left

@property profile_change type=callback callback=callback_profile_change no_caption=1 group=profile_change store=no
@caption Minu profiil


@groupinfo all_profiles caption="Kõik profiilid" parent=profile tabgroup=left submit=no

@property all_profiles_toolbar type=toolbar no_caption=1 group=all_profiles store=no
@caption Profiilide haldus

@property all_profiles type=table group=all_profiles no_caption=1 store=no
@caption Kõik minu profiilid

----------------------- pildid --------------------------

@groupinfo images caption="Pildid" parent=me tabgroup=left


@groupinfo my_images caption="Minu pildid" parent=images tabgroup=left

@property my_images type=callback callback=callback_my_images no_caption=1 group=my_images store=no
@caption Minu pildid


@groupinfo switch_profile caption="Vaheta profiili" parent=images tabgroup=left submit=no

@property switch_profile type=table group=switch_profile no_caption=1 store=no
@caption Vaheta profiili piltide jaoks


@groupinfo show_image_comments parent=images tabgroup=left submit=no

@property image_comments_toolbar type=toolbar no_caption=1 group=show_image_comments store=no
@caption Kommentaaride haldus

@property show_image_comments type=table group=show_image_comments store=no no_caption=1 store=no
@caption Pildi kommentaarid

----------------------- sätted --------------------------

@groupinfo settings caption="Sätted" parent=me tabgroup=left submit=no


@groupinfo browsing_conditions caption="Sirvimise sätted" parent=settings tabgroup=left

@property head1 type=text subtitle=1 group=browsing_conditions store=no
@caption Muuda kasutajate sirvimise sätteid

@property browsing_conditions type=table group=browsing_conditions no_caption=1 store=no


@groupinfo view_conditions caption="Kuvamise sätted" parent=settings tabgroup=left

@property head2 type=text subtitle=1 group=view_conditions store=no
@caption Muuda kuvamise sätteid

@property view_conditions type=table group=view_conditions no_caption=1 store=no


@groupinfo message_conditions caption="Teadete sätted" parent=settings tabgroup=left

@property head3 type=text subtitle=1 group=message_conditions store=no
@caption Muuda teadete saamise/saatmise sätteid

@property message_conditions type=table group=message_conditions no_caption=1 store=no

 ----------------------- listid --------------------------

@groupinfo lists caption="Listid" parent=me tabgroup=left submit=no


@groupinfo friend_list caption="Sõbralist" parent=lists tabgroup=left submit=no

@property head4 type=text subtitle=1 group=friend_list store=no
@caption Sõbralist

@property friend_list_toolbar type=toolbar no_caption=1 group=friend_list store=no
@caption Sõprade haldus

@property friend_list type=table group=friend_list no_caption=1 store=no
@caption Sõbralist


@groupinfo ignored_list caption="Ignoreeritute list" parent=lists tabgroup=left submit=no

@property head5 type=text subtitle=1 group=ignored_list store=no
@caption Ignoreeritute list

@property ignored_list_toolbar type=toolbar no_caption=1 group=ignored_list store=no
@caption Ignoreeritute haldus

@property ignored_list type=table group=ignored_list no_caption=1 store=no
@caption Ignoreeritute list


@groupinfo blocked_list caption="Blokeeritute list" parent=lists tabgroup=left submit=no

@property head6 type=text subtitle=1 group=blocked_list store=no
@caption Blokeeritute list

@property blocked_list_toolbar type=toolbar no_caption=1 group=blocked_list store=no
@caption Blokeeritute haldus

@property blocked_list type=table group=blocked_list no_caption=1 store=no
@caption Blokeeritute list


@groupinfo l_switch_profile caption="Vaheta profiili" parent=lists tabgroup=left submit=no

@property l_switch_profile type=table group=l_switch_profile no_caption=1 store=no
@caption Vaheta profiili listide jaoks

@groupinfo profile_search parent=lists tabgroup=left submit=no caption="Otsing"


@property profile_search type=callback callback=callback_profile_search group=profile_search no_caption=1 store=no
@caption Profiili otsing

----------------------- kommentaarid --------------------------

@groupinfo comments caption="Kommentaarid" parent=me tabgroup=left


@groupinfo prof_comments caption="Kommentaarid" parent=comments tabgroup=left submit=no

@property prof_comments_toolbar type=toolbar no_caption=1 group=prof_comments store=no
@caption Kommentaaride toolbar

@property prof_comments type=table group=prof_comments no_caption=1 store=no
@caption Kommentaarid


@groupinfo c_switch_profile caption="Vaheta profiili" parent=comments tabgroup=left submit=no

@property c_switch_profile type=table group=c_switch_profile no_caption=1 store=no
@caption Vaheta profiili kommentaaride jaoks

----------------------- horoskoop --------------------------

@groupinfo horoscope caption="Horoskoop" parent=me tabgroup=left submit=no

@property fake10 type=text group=horoscope store=no
@caption Horoskoop

----------------------- sobivad kasutajad --------------------------

@groupinfo matching_profiles caption="Sobivad kasutajad" parent=me tabgroup=left submit=no

@property fake1 type=text group=matching_profiles store=no
@caption Sobivad kasutajad

----------------------- MINA : END --------------------------

----------------------- SÕBRAD --------------------------

@groupinfo friends caption="Sõbrad" submit=no


@groupinfo prof_friends caption="Minu sõbrad" parent=friends tabgroup=left submit=no

@property friends_panel type=text group=prof_friends no_caption=1 store=no
@caption Sõbrad


@groupinfo address_book caption="Aadressiraamat" parent=friends tabgroup=left submit=no

@property address_book_toolbar type=toolbar no_caption=1 group=address_book store=no
@caption Aadressiraamatu toolbar

@property address_book type=table group=address_book no_caption=1 store=no
@caption Aadressiraamat


@groupinfo friend_groups caption="Sõbragrupid" parent=friends tabgroup=left submit=no

@property friend_groups_head type=text subtitle=1 group=friend_groups store=no

@property friend_groups_toolbar type=toolbar no_caption=1 group=friend_groups store=no
@caption Sõbragruppide toolbar

@property friend_groups type=table group=friend_groups no_caption=1 store=no
@caption Sõbragrupid


@groupinfo search_n_add caption="Otsi ja lisa" parent=friends tabgroup=left submit=no
@property search_n_add type=callback callback=callback_profile_search group=search_n_add no_caption=1 store=no
@caption Otsi ja lisa

@groupinfo f_switch_profile caption="Vaheta profiili" parent=friends tabgroup=left submit=no

@property f_switch_profile type=table group=f_switch_profile no_caption=1 store=no
@caption Vaheta profiili sõprade jaoks


@groupinfo friend_details parent=friends tabgroup=left 

@property friend_details type=callback callback=callback_friend_details group=friend_details no_caption=1 store=no
@caption Vaata profiili

@property profile_comments type=comments group=friend_details store=no

----------------------- SÕBRAD : END --------------------------

----------------------- HINDAMINE --------------------------

@groupinfo ratings caption="Hindamine"


@groupinfo rate caption="Hinda" no_submit=1 parent=ratings tabgroup=left

@property rate type=callback callback=callback_rate group=rate no_caption=1 store=no
@caption Hindamine või vaatamine


@groupinfo top_men caption="Top-mehed" parent=ratings tabgroup=left submit=no

@property top_men type=callback callback=callback_top_men group=top_men no_caption=1 store=no
@caption Top-mehed list


@groupinfo top_women caption="Top-naised" parent=ratings tabgroup=left submit=no

@property top_women type=callback callback=callback_top_women group=top_women no_caption=1 store=no
@caption Top-naised list


@groupinfo last_added caption="Viimati lisatud" parent=ratings tabgroup=left submit=no

@property last_added type=callback callback=callback_last_added group=last_added no_caption=1 store=no
@caption Viimati lisatud pildid

----------------------- HINDAMINE : END --------------------------

----------------------- POSTKAST --------------------------

@groupinfo messages caption="Postkast"


@groupinfo inbox caption="Inbox" submit=no parent=messages tabgroup=left

@property inbox type=callback callback=callback_inbox group=inbox no_caption=1 store=no
@caption Inbox


@groupinfo outbox caption="Outbox" submit=no parent=messages tabgroup=left

@property outbox type=callback callback=callback_outbox group=outbox no_caption=1 store=no
@caption Outbox


@groupinfo archive caption="Arhiiv" submit=no parent=messages tabgroup=left

@property archive type=callback callback=callback_archive group=archive store=no
@caption Arhiiv


@groupinfo newmessage caption="Uus teade" parent=messages tabgroup=left

@property newmessage type=callback callback=callback_newmessage group=newmessage store=no
@caption Uue teate kirjutamine

----------------------- POSTKAST : END --------------------------

----------------------- FOORUM --------------------------

@groupinfo forumtab caption="Foorum" submit=no


@groupinfo forum caption="Foorum" parent=forumtab submit=no tabgroup=left

@property forum type=text group=forum no_caption=1 store=no
@caption Foorum


@groupinfo my_forum_topics caption="Minu teemad" parent=forumtab submit=no tabgroup=left

@property my_forum_topics type=table group=my_forum_topics no_caption=1 store=no
@caption Minu foorumiteemad


@groupinfo forum_settings caption="Sätted" parent=forumtab tabgroup=left

@property forum_settings type=callback callback=callback_forum_settings group=forum_settings no_caption=1 store=no
@property Sätted

----------------------- FOORUM : END --------------------------

----------------------- KOGUKONNAD --------------------------

@groupinfo communities caption="Kogukonnad"


@groupinfo my_communities caption="Minu kogukonnad" parent=communities tabgroup=left submit=no

@property my_communities_toolbar type=toolbar group=my_communities no_caption=1
@caption Minu kogukondade toolbar

@property my_communities type=table group=my_communities store=no no_caption=1
@caption Minu kogukonnad


@groupinfo categories caption="Kategooriad" parent=communities tabgroup=left submit=no

@property categories type=treeview group=categories no_caption=1
@caption Kategooriad

@groupinfo community_search caption="Otsing" parent=communities tabgroup=left submit=no

@property community_search type=text group=community_search store=no no_caption=1
@caption Otsing

----------------------- KOGUKONNAD : END --------------------------

----------------------- STATISTIKA --------------------------

@groupinfo statistics caption="Statistika" submit=no


@groupinfo my_statistics caption="Minu statistika" parent=statistics tabgroup=left submit=no

@property my_statistics type=text group=my_statistics store=no no_caption=1
@caption Minu statistika

@groupinfo general_statistics caption="Üldstatistika" parent=statistics tabgroup=left submit=no

@property general_statistics type=text group=general_statistics store=no no_caption=1
@caption Üldstatistika

----------------------- STATISTIKA : END --------------------------

----------------------- LIITUMINE --------------------------

@groupinfo join caption="Liitumine"


@groupinfo join_form caption="Liitu kasutajaks" parent=join tabgroup=left submit=no

@property join_form type=callback group=join_form callback=callback_join_form store=no no_caption=1
@caption Liitu kasutajaks


@groupinfo forgot_password caption="Unustasid salasõna?" parent=join tabgroup=left submit=no

@property forgot_password type=text group=forgot_password store=no no_captioN=1
@caption Unustasid salasõna?

----------------------- LIITUMINE : END --------------------------

----------------------- SISUOBJEKTID --------------------------

@groupinfo locations caption="Sisuobjektid"

----------------------- SISUOBJEKTID : END --------------------------

------------------- properties -------------------------

@property join_obj type=relpicker reltype=RELTYPE_JOIN_OBJ method=serialize field=meta group=general table=objects
@caption Liitumisvorm

@property cfgmanager type=relpicker reltype=RELTYPE_CFG_MANAGER method=serialize field=meta group=general table=objects
@caption Seadete haldur

------------------- properties : end -------------------------

--------- folders ----------------------

@property pic_folder type=relpicker reltype=RELTYPE_PIC_FOLDER method=serialize field=meta group=general table=objects 
@caption Piltide kataloog

@property persons_folder type=relpicker reltype=RELTYPE_PERSONS_FOLDER method=serialize field=meta group=general table=objects 
@caption Isikute kataloog

@property communities_folder type=relpicker reltype=RELTYPE_COMMUNITIES_FOLDER method=serialize field=meta group=general table=objects 
@caption Kogukondade kataloog

@property organizations_folder type=relpicker reltype=RELTYPE_ORGANIZATIONS_FOLDER method=serialize field=meta group=general table=objects 
@caption Organisatsioonide kataloog

@property profiles_folder type=relpicker reltype=RELTYPE_PROFILES_FOLDER method=serialize field=meta group=general table=objects 
@caption Profiilide kataloog

--------- folders : end ----------------------

--------- reltypes --------------------------

//@siia veel igast muid reltype'e, moderaator, sõbragrupid,kogukondade kategooriad, kogukondade otsing, 3 vaadet

@reltype PROF_SEARCH value=21 clid=CL_CB_SEARCH
@caption profiilide otsing

@reltype FORUM value=16 clid=CL_FORUM_V2
@caption Kommuuni foorum

@reltype CONTENT value=1 clid=CL_PROMO,CL_MENU_AREA
@caption Sisuelement

@reltype LAYOUT_LOGO value=10 clid=CL_IMAGE
@caption Kujunduse logo

@reltype JOIN_OBJ value=2 clid=CL_JOIN_SITE
@caption liitumisvorm

@reltype CFG_MANAGER value=15 clid=CL_CFGMANAGER
@caption seadete haldur

@reltype VAR_META value=19 clid=CL_META
@caption isikute kataloog

@reltype USER_GROUPS value=20 clid=CL_GROUP
@caption kasutajagrupid

--------- reltypes : end --------------------------

--------- folders ----------------------
@reltype PIC_FOLDER value=25 clid=CL_MENU
@caption Piltide kataloog

@reltype PERSONS_FOLDER value=11 clid=CL_MENU
@caption Isikute kataloog

@reltype COMMUNITIES_FOLDER value=12 clid=CL_MENU
@caption Kogukondade kataloog

@reltype ORGANIZATIONS_FOLDER value=13 clid=CL_MENU
@caption Organisatsioonide kataloog

@reltype PROFILES_FOLDER value=14 clid=CL_MENU
@caption Profiilide kataloog

--------- folders : end ----------------------

*/

class commune extends class_base
{
	var $common;
	
	function commune()
	{
		$this->init(array(
		
			"clid" => CL_COMMUNE,
			"tpldir" => "applications/commune/commune"
		));
		
		$this->common = array();
		
		$this->fields_from_person = array(
			"firstname",
			"lastname",
			"gender",
			"nickname",
			"social_status",
		);
		$this->change_fields_from_profile = array(
			"user_field1",
			"sexual_orientation",
			"height",
			"weight",
			"body_type",
			"hair_color",
			"hair_type",
			"eyes_color",
			"tobacco",
			"alcohol",
			"user_text1",
			"user_text3",
			"user_text2",
			"user_text5",
			"user_blob2",
			"user_blob1",
			"user_text4",
			"occupation",
			"occupation",
			"user_field2",
			"user_check1",
			"user_check2",
		);
		$this->show_fields_from_profile = array(
			"user_field1",
			"age",
			"sexual_orientation",
			"height",
			"weight",
			"body_type",
			"hair_color",
			"hair_type",
			"eyes_color",
			"tobacco",
			"alcohol",
			"user_text1",
			"user_text3",
			"user_text2",
			"user_text5",
			"user_field2",
			"user_text4",
			"user_blob1",
			"occupation",
			"user_field2",
			"user_blob2",
		);
		$this->msg_vars = array(
			"pcom" => array(false,false,"Minu piltidele lisatud kommentaarid"),
			"pacpt" => array(false,false,"Pildi aktsepteerimine moderaatori poolt"),
			"pacom" => array(false,false,"Minu profiilile lisatud kommentaarid"),
			"nfriend" => array(false,false,"Uus sõber"),
			"avgr" => array(false,false,"Keskmise hinde saatmine iga pildi kohta 1 kord päevas"),
			"fpa" => array(false,false,"Minu foorumipostitustele vastamised"),
			"nfm" => array(true,true,"mittesõpradelt laekunud teated"),
			"fm" => array(true,true,"sõpradelt laekunud teated")
		);
	}
	
	function callback_on_load($arr)
	{
		//echo aw_global_get("uid");
		//arr($arr);
		// $arr["obj_inst"] pole määratud siin...
		$this->common["id"] = $arr["request"]["id"];
		
		//miks ma seda nii teen? sest arr sisaldab ainult 'request'-i
		/*
		$cfgmanager_id = $commune_o->prop("cfgmanager");
		if (isset($cfgmanager_id))
		{
			$this->cfgmanager = $cfgmanager_id;
		}
		*/
		$this->common["obj_inst"] = obj($arr["request"]["id"]);
		$this->common["profile"] = false;
		$this->common["my_profile"] = false;
		$this->common["f_group"] = false;
		if ($this->check_rights($arr["request"]["profile"]))
		{
			$this->common["profile"] = obj($arr["request"]["profile"]);
		}
		if ($this->check_rights($arr["request"]["my_profile"]))
		{
			$this->common["my_profile"] = obj($arr["request"]["my_profile"]);
		}
		if ($this->check_rights($arr["request"]["f_group"]))
		{
			$this->common["f_group"] = $arr["request"]["f_group"];
		}
	}
	
	function callback_mod_reforb($arr)
	{
		if($this->common["profile"])
		{
			$arr["profile"] = $this->common["profile"]->id();
		}
		if($this->common["my_profile"])
		{
			$arr["my_profile"] = $this->common["my_profile"]->id();
		}
		if($this->common["f_group"])
		{
			$arr["f_group"] = $this->common["f_group"];
		}
		//arr($arr);
		
		// do a thing or few -- ahz
	}
	
	function callback_post_save($arr)
	{
		if($this->common["my_profile"])
		{
			$params = array(
				"group" => $arr["request"]["group"],
				"my_profile" => $this->common["my_profile"]->id(),
			);
			if($this->common["profile"])
			{
				$params["profile"] = $this->common["profile"]->id();
			}
			if($this->common["f_group"])
			{
				$params["f_group"] = $this->common["f_group"];
			}
			/*
			return $this->mk_comm_orb(array(
				"group" => $arr["request"]["group"],
				"profile" => $this->common["profile"]->id(),
			));
			*/
			// kids, don't try this at home -- ahz
			header("location:".$this->mk_comm_orb($params));
			die();
		}
		//arr($this->common["profile"]);
	}
	
	function callback_mod_tab($args = array())
	{
		// this be cool -- ahz
		/*
		//if ($args["id"] == "add_event" && empty($this->event_id))
		if ($args["activegroup"] != "add_event" && $args["id"] == "add_event")
		{
			return false;
		};

		if ($args["activegroup"] == "add_event" && $args["id"] == "add_event")
		{
			$link = &$args["link"];
			$link = $this->mk_my_orb("change",$args["request"]);
		};
		*/

	}
	
	function callback_pre_edit($arr)
	{
		$commune_view_actions = array("profile","friends","communities","pictures");
		//if (method_exists($this, "show_".$commact))
		if ($commact = $arr["request"]["commact"] and in_array($commact, $commune_view_actions))
		{
			$this->common["commact"] = $commact;
		}
		//preparings to show some particular tab:
		switch ($arr["request"]["group"])
		{
			case "rate":
			case "ratings":
				//shouldn't be used here, unnecessary coupling with other class
				//$ro = aw_global_get("rated_objs"); //set by rate::add_rate() .. $ro[$oid] = $rating;
				//var_dump($ro); //false rsk, ei saa kätte - ilmselt jälle see sessioonide bug.
				//$last_rated = end($ro);
				$last_rated_oid = aw_global_get("last_rated_oid");
				//echo 'last_rated:'.$last_rated_oid;//.'='.key($last_rated).' ';
				//seda ei peagi ju siin tegema, see võtab, kel vaja näidata,
				//siin on tarvis ainult määrata, et "seda" mis näitab, näidataks.
			break;
		}
		//arr($this->common);
		//echo dbg::process_backtrace(debug_backtrace());
	}
	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "fake10":
				$prop["value"] = "siia tuleb horoskoop";
				break;
				
			case "my_forum_topics":
				$this->my_forum_topics($arr);
				break;
				
			case "tabpanel":
				if (is_oid($arr["obj_inst"]->id()))
				{
					$logos = &$arr["obj_inst"]->connections_from(array(
						"type" => "RELTYPE_LAYOUT_LOGO",
					));
				}

				if (sizeof($logos) > 0)
				{
					$first_logo = reset($logos);

					$t = get_instance(CL_IMAGE);
					$prop["vcl_inst"]->set_style("with_logo");
					$prop["vcl_inst"]->configure(array(
						"logo_image" => $t->get_url_by_id($first_logo->prop("to")),
					));
				};
				
				$prop["vcl_inst"]->vars(array(
					"context_panel" => $this->render_context_panel($arr),
					"action_menu" => '',//$this->render_actions($arr),
				));
				break;
				
			case "inbox_toolbar":
			case "outbox_toolbar":
			case "archive_toolbar":
				$tb = get_instance(CL_QUICKMESSAGEBOX);
				$tb->get_property($arr);
				break;
				
			case "archive":
			case "inbox":
			case "outbox":
				$box = get_instance(CL_QUICKMESSAGEBOX);
				$box_o = $box->get_message_box_for_user($this->get_user());
				switch($prop["name"])
				{
					case "outbox":
						$boxprop = array("user_from");
						$archive = false;
					break;
					
					case "inbox":
						$boxprop = array("user_to");
						$archive = false;
					break;
					
					case "archive":
						$boxprop = array("user_from", "user_to");
						$archive = true;
					break;
				}
				$vars = array(
					"group" => $prop["name"],
					"boxprop" => $boxprop,
					"archive" => $archive,
					"vcl_inst" => &$prop["vcl_inst"],
					"id" => $box_o->id(),
					"class" => $this->common["obj_inst"]->class_id(),
					"class_id" => $this->common["obj_inst"]->id(),
				);
				if($o = $box->is_that_class(array("id" => $arr["request"]["mid"], "class" => CL_QUICKMESSAGE)))
				{
					$args = array(
						"o" => $o,
						"vcl_inst" => &$prop["vcl_inst"],
					);
					$box->show_message($args);
				}
				else
				{
					$box->create_box($vars);
				}
				break;
			
			case "prof_comments_toolbar":
				$tb = &$prop["vcl_inst"];
				$tb->add_button(array(
					"name"		=> "hide",
					"tooltip"	=> "Muuda valitud kommentaaride staatust",
					"img"		=> "import.gif",
					"action"	=> "hide_prof_com",
				));
				$tb->add_button(array(
            		"name" => "delete",
            		"tooltip" => "Kustuta valitud kommentaarid",
            		"img" => "delete.gif",
            		"action" => "delete_prof_com",
					"confirm" => "soovid tõesti valitud kommentaarid kustutada?",
        		));
				break;
				
			case "friend_groups_toolbar":
				$tb = &$prop["vcl_inst"];
				if($this->common["f_group"])
				{
					// this i gotta think through, to avoid confusion -- ahz
				}
				break;
				
			case "friend_groups_head":
				if($this->common["f_group"])
				{
					$obj = obj($this->common["f_group"]);
					$groupname = $obj->name();
				}
				else
				{
					$groupname = "üldine";
				}
				$prop["value"] = $groupname;
				break;
				
			case "friend_groups":
				$this->show_friend_groups($arr);
				break;
				
			case "address_book_toolbar":
				$tb = &$prop["vcl_inst"];
				$tb->add_button(array(
            		"name" => "delete",
            		"tooltip" => "Kustuta kontakte",
            		"img" => "delete.gif",
            		"action" => "delete_contact",
					"confirm" => "Oled kindel, et tahad valitud eemaldada?",
        		));
				$tb->add_button(array(
            		"name" => "search",
            		"tooltip" => "Otsi kontaktidesse",
            		"img" => "search.gif",
            		"url" => $this->mk_comm_orb(array(
							"group" => "search_n_add",
						)),
        		));
				break;
				
			case "address_book":
				if($list = $this->get_contact_list())
				{
					$contact_list = get_instance(CL_CONTACT_LIST);
					$vars = array(
						"obj_inst" => $list,
						"commune" => $this->common["obj_inst"]->id(),
						"vcl_inst" => &$arr["prop"]["vcl_inst"],
						"include" => true,
					);
					$contact_list->show_contact_list($vars);
				}
				break;
				
			case "blocked_list_toolbar":
			case "ignored_list_toolbar":
			case "friend_list_toolbar":
				$vars = array(
					"blocked_list_toolbar" => array(
						"blokeeritute ", "remove_blocked", "blokeeritavaid", "blocked",
					),
					"ignored_list_toolbar" => array(
						"ignoreeritute ", "remove_ignored", "ignoreeritavaid", "ignored",
					),
					"friend_list_toolbar" => array(
						"sõbra", "remove_friend", "sõpru", "friend",
					),
				);
				$prop["name"];
				$tb = &$prop["vcl_inst"];
				$tb->add_button(array(
            		"name" => "delete",
            		"tooltip" => "Eemalda valitud ".$vars[$prop["name"]][0]."listist",
            		"img" => "delete.gif",
            		"action" => $vars[$prop["name"]][1],
					"confirm" => "Soovid tõesti valitud ".$vars[$prop["name"]][0]."listist eemaldada?",
					/*
					"url" => $this->mk_comm_orb(array(
						"action" => "delete_obj", 
						"del_type" => $vars[$prop["name"]][1],
					)),
					*/
        		));
				$tb->add_button(array(
            		"name" => "search",
            		"tooltip" => "Otsi ".$vars[$prop["name"]][2],
            		"img" => "search.gif",
            		"url" => $this->mk_comm_orb(array(
							"group" => "profile_search",
							"s_group" => $vars[$prop["name"]][3], 
						)),
        		));
				break;
				
			/*
			case "profile_search":
				$this->profile_search($arr);
				break;
			*/
			
			case "prof_comments":
				$this->show_prof_comments($arr);
				break;
				
			case "blocked_list":
			case "ignored_list":
			case "friend_list":
				$this->show_list($arr);
				break;
				
			case "browsing_conditions":
				$this->browsing_conditions($arr);
				break;
				
			case "message_conditions":
				$this->message_conditions($arr);
				break;
				
			case "view_conditions":
				$this->view_conditions($arr);
				break;
				
			case "f_switch_profile":
			case "l_switch_profile":
			case "c_switch_profile":
			case "switch_profile":
				$prop["value"] = $this->show_switch_profile($arr);
				break;
			
			case "friends_panel":
				if (!$profile = $this->common["my_profile"])
				{
					if (!$profile = $this->get_active_profile())
					{
						$retval = PROP_FATAL_ERROR;
						break;
					}
				}
				//siin ei tule saidi poolel ft_page parameeter kaasa - class_base puudused
				//töötab ainult admin poolel
				//echo dbg::process_backtrace(debug_backtrace());
				$prop["value"] = $this->render_friends_panel($profile, $arr["request"]["ft_page"]);
				break;
				
			case "all_profiles":
				$this->do_tbl_my_profiles($arr);
				break;
				
			case "all_profiles_toolbar":
				$tb = &$prop["vcl_inst"];
				
				//$o = &$arr["obj_inst"];
				//$folder = $o->prop("profiles_folder");
				
				$tb->add_button(array(
					"name"		=> "add",
					"tooltip"	=> "Uus profiil",
					"img"		=> "new.gif",
					/*
					"url"		=> $this->mk_my_orb("new", array(
										"parent" => $folder, 
										"return_url" => urlencode(aw_global_get('REQUEST_URI'))), 
										CL_PROFILE),
					*/
					"action" => "new_profile",
				));
				$tb->add_button(array(
					"name"		=> "save",
					"tooltip"	=> "Salvesta muutused",
					"img"		=> "save.gif",
					"action"	=> "",
				));
				$tb->add_button(array(
            		"name" => "delete",
            		"tooltip" => "Kustuta valitud profiilid",
            		"img" => "delete.gif",
            		"action" => "delete_profile",
					"confirm" => "Oled kindel, et soovid valitud profiilid kustutada?",
        		));
				
				break;
				
			case "show_image_comments":
				$this->do_tbl_image_comments($arr);
				break;
				
			case "image_comments_toolbar":
				$tb = &$prop["vcl_inst"];
				$tb->add_button(array(
            		"name" => "delete",
            		"tooltip" => "Kustuta valitud kommentaarid",
            		"img" => "delete.gif",
            		"action" => "delete_comments",
					"confirm" => "Oled kindel, et soovid valitud kommentaarid kustutada?",
        		));
				$person = $this->get_person();
				$person->set_meta("img_id",$arr["request"]["img_id"]);
				$person->save();
				break;
				
			/*
			case "prof_comments":
				$profile = $this->get_active_profile();
				$prop["use_parent"] = $profile->id();
				$person = $this->get_person();
				$prop["heading"] = $person->prop("firstname")." ".$person->prop("lastname");
				break;
			*/
			
			case "profile_change_toolbar":
				$tb = &$prop["vcl_inst"];
				$tb->add_button(array(
					"name" => "new_profile",
					"tooltip" => "Lisa uus profiil",
					"img" => "new.gif",
					"action" => "new_profile",
				));

				$tb->add_separator();
				$tb->add_cdata("<small>Vali profiil:</small>");
				$connected_profiles = $this->get_connections_to_profiles();
				$active_profile = $this->get_active_profile();
				$selected_profile = $this->common["my_profile"] ? $this->common["my_profile"]->id() : $active_profile->id();
				$sel_options = array();
				foreach($connected_profiles as $conns)
				{
					$sel_options[$conns->prop("to")] = $conns->prop("to.name");
				}
				$tb->add_cdata(html::select(array(
					"name" => "profile_oid",
					"options" => $sel_options,
					"selected" => $selected_profile,
					"onchange" => "window.location = '".html::get_change_url($arr["obj_inst"]->id(), array(
						"my_profile" => ""))."&my_profile=' + this.options[this.selectedIndex].value;",
						"group" => "me",
				)));
				$tb->add_separator();
				if($prop["group"] == "profile_change")
				{
					$tb->add_button(array(
						"name" => "save_changes",
						"tooltip" => "Salvesta muutused oma profiilis",
						"img" => "save.gif",
						"action" => "",
					));
				}
				$tb->add_button(array(
					"name" => "delete_profile",
					"tooltip" => "Kustuta käesolev profiil",
					"img" => "delete.gif",
					"action" => "",
					"confirm" => "Olete Te kindel, et soovite kustutada käesolevat profiili?",
				));
				break;
				
			case "profile_comments":
				if(!$this->common["profile"])
				{
					return PROP_FATAL_ERROR;
				}
				$profile = $this->common["profile"];
				$prop["use_parent"] = $profile->id();
				$person = $profile->get_first_obj_by_reltype("RELTYPE_PERSON");
				//$person = $this->get_person();
				$prop["heading"] = $person->prop("firstname")." ".$person->prop("lastname");
				break;
				
			case "profile_header":
				$prop["value"] = $this->render_profile_header($arr);
				break;
				
			case "forum":
				$prop["value"] = $this->show_forum($arr);
				break;
				
			case "my_communities_toolbar":
				$tb = &$prop["vcl_inst"];
				$tb->add_button(array(
					"name"		=> "hide",
					"tooltip"	=> "Eemalda ennast valitud kogukondades",
					"img"		=> "delete.gif",
					"action"	=> "remove_from_community",
					"confirm"	=> "oled ikka kindel?",
				));
				break;
				
			case "my_communities":
				$this->show_communities($arr);
				break;
				
			case "categories":
				$this->show_categories($arr);
				break;

		};
		return $retval;
	}
	
	function set_property($arr)
	{
		//arr($arr);
		$prop = $arr["prop"];
		$rv = PROP_OK;
		switch($prop["name"])
		{
			case "message_conditions":
				$this->update_message_conditions($arr);
				break;
				
			case "forum_settings":
					$user = $this->get_user();
					$user->set_meta("forum_view", $arr["request"]["days"]);
					$user->save();
				break;
				
			case "browsing_conditions":
				$this->update_browsing_conditions($arr);
				break;
				
			case "view_conditions":
				$this->update_view_conditions($arr);
				break;
				
			case "profile_change":
				$this->update_profile($arr);
				break;

			case "my_images":
				$this->update_my_images($arr);
				break;

			case "rate_content":
			case "rateform":
			case "rate":
				$this->add_rate($arr);
				break;

			case "locations":
				$this->update_locations($arr);
				break;

			case "newmessage":
				$this->create_message($arr);
				break;

			case "join_form":
				$j_oid = &$arr["obj_inst"]->prop("join_obj");
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
				
			case "all_profiles":
				//miks ei tööta????
				//aw_session_set("active_profile_id", $active_profile_id);
				//debug:
				//echo 'aw_global_get:'.aw_global_get('active_profile_id');
				
				//sessioone ei saa kasutada. nt see ei tööta:
				//aw_session_set('kala', 1);
				//echo 'aw_global_get:'.aw_global_get('kala');
				$person = $this->get_person();
				$person->set_meta("active_profile", $arr["request"]["active_profile"]);
				$person->save();
				break;
		};
		return $rv;
	}
	
	function show_categories($arr)
	{
		$tree = &$arr["prop"]["vcl_inst"];
		/*
		$tree->start_tree(array(
			"type" => TREE_DHTML,
			"root_name" => "Kategooriad",
			"root_url" => "",
		));
		*/
		$ot = get_instance(CL_OBJECT_TYPE);
		$ff = $ot->get_obj_for_class(array(
			"clid" => clid_for_name("community"),
		));
		if(!empty($ff))
		{
			$fo = obj($ff);
			if($com = $fo->get_first_obj_by_reltype("RELTYPE_META_ELEMENTS"))
			{
				$tree->add_item(0, array(
					"name" => $com->name(),
					"id" => $com->id(),
					//"url" => "tiit",
				));
				$this->_make_categories_tree($com->id(), &$tree);
				return;
			}
		}
		$tree->add_item(0, array(
			"name" => "Ühtegi kategooriat pole veel defineeritud",
			"id" => 1,
		));
	}
	
	function _make_categories_tree($parent, $tree)
	{
		$childs = new object_list(array(
			"parent" => $parent,
			"class_id" => CL_META,
		));
		foreach($childs->arr() as $child)
		{
			// as an idea, here should be the counter how many communities fall in that category,
			// but frankly it seems, that it is currently impossible to do :/ -- ahz
			$cons = $child->connections_to(array(
				"type" => 6,
				"from.class_id" => CL_COMMUNITY,
			));
			//echo count($cons);
			$asd = count($cons);
			//$asd = rand(0, 100);
			$tree->add_item($parent, array(
			"name" => $child->name()."($asd)",
			"id" => $child->id(),
		));
			$this->_make_categories_tree($child->id(), &$tree);
		}
	}
	
	function show_communities($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "id",
			"caption" => "ID",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
		));
		$t->define_field(array(
			"name" => "category",
			"caption" => "Kategooriad",
		));
		$t->define_field(array(
			"name" => "members",
			"caption" => "Liikmeid",
			"type" => "int",
		));
		$t->define_field(array(
			"name" => "status",
			"caption" => "Sinu staatus",
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));
		$types = array(
			4 => "tavaliige", //4
			3 => "moderaator", //3
		);
		$user = $this->get_user();
		$communities = $user->connections_to(array(
			"type" => array(3, 4),
			"from.class_id" => CL_COMMUNITY,
		));
		foreach($communities as $community)
		{
			$cats = array();
			$com = $community->from();
			$members = $com->connections_from(array(
				"type" => array(3, 4),
			));
			foreach($com->prop("category") as $cat)
			{
				$cat_o = obj($cat);
				$cats[] = $cat_o->name();
			}
			$t->define_data(array(
				"id" => $com->id(),
				"name" => html::href(array(
					"caption" => $com->name(),
					"url" => html::get_change_url($com->id(), array(
						"group" => "content",
					)),
				)),
				"category" => implode(", ", $cats),
				"members" => count($members),
				"status" => $types[$community->prop("reltype")],
			));
		}
	}
	
	function show_forum($arr)
	{
		$forums = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_FORUM",
		));
		foreach($forums as $frm)
		{
			$cforum = &$frm;
		}
		$forumi = get_instance(CL_FORUM_V2);
		return $forumi->change(array(
			"id" => $cforum->prop("to"),
			"action" => isset($arr["request"]["action"]) ? $arr["request"]["action"] : "view",
			"rel_id" => $cforum->prop("relobj_id"),
			"folder" => $_GET["folder"],
			"topic" => $_GET["topic"],
			"page" => $_GET["page"],
			"c" => $_GET["c"],
			"cb_part" => 1,
			"fxt" => 1,
			"group" => "contents",
				//"group" => isset($_GET["group"]) ? $_GET["group"] : "contents",
		));
	}
	
	function callback_profile_search($arr)
	{
		enter_function("commune::callback_profile_search");
		$ob = $this->common["obj_inst"]->get_first_obj_by_reltype("RELTYPE_PROF_SEARCH");
		$search = $ob->instance();
		$request = array("s" => $arr["request"]["s"]);
		if ($arr["request"]["search_butt"])
		{
			$request["search_butt"] = $arr["request"]["search_butt"];
		}
		if ($arr["request"]["ft_page"])
		{
			$request["ft_page"] = $arr["request"]["ft_page"];
		}

		list($props, $clid, $relinfo) = $search->get_props_from_obj($ob);
		
		$props = $search->callback_gen_search(array(
			"obj_inst" => $ob,
			"request" => $request
		));

		$htmlc = get_instance("cfg/htmlclient");
		$htmlc->start_output();
		foreach($props as $pn => $pd)
		{
			$htmlc->add_property($pd);
		}
		$htmlc->add_property(array(
			"name" => "search",
			"caption" => "Otsi",
			"type" => "submit",
			"store" => "no"
		));
		$htmlc->finish_output();

		$html = $htmlc->get_result(array(
			"raw_output" => 1
		));

		classload("vcl/table");
		$t = new aw_table(array(
			"layout" => "generic"
		));
		$search->mk_result_table(array(
			"prop" => array(
				"vcl_inst" => &$t
			),
			"obj_inst" => &$ob,
			"request" => $request,
		));
		// now we gonna do some serious hacking shit -- ahz
		$s_group = $arr["request"]["s_group"];
		if($s_group == "friend")
		{
			$t->rowdefs[] = array(
				"name" => "flist",
				"caption" => "",
			);
		}
		elseif($s_group == "ignored")
		{
			$t->rowdefs[] = array(
				"name" => "ilist",
				"caption" => "",
			);
		}
		elseif($s_group == "blocked")
		{
			$t->rowdefs[] = array(
				"name" => "blist",
				"caption" => "",
			);
		}
		elseif($arr["request"]["group"] == "search_n_add")
		{
			$t->rowdefs[] = array(
				"name" => "clist",
				"caption" => "",
			);
		}
		foreach($t->data as $id => $data)
		{
			//arr($data);
			$obj = obj($data["oid"]);
			$creator = $obj->createdby();
			$t->data[$id]["flist"] = html::href(array(
				"caption" => "lisa sõbraks",
				"url" => $this->mk_comm_orb(array(
					"commact" => "add_friend", 
					"group" => "friend_list", 
					"profile" => $data["oid"],
				), 0, "commaction"),
			));
			$t->data[$id]["clist"] = html::href(array(
				"caption" => "lisa kontaktidesse",
				"url" => $this->mk_comm_orb(array(
					"commact" => "add_contact", 
					"group" => "address_book", 
					"cuser" => $creator->id(),
				), 0, "commaction"),
			));
			$t->data[$id]["blist"] = html::href(array(
				"caption" => "blokeeri",
				"url" => $this->mk_comm_orb(array(
					"commact" => "add_blocked",
					"group" => "blocked_list",
					"cuser" => $creator->id(),
				), 0, "commaction"),
			));
			$t->data[$id]["ilist"] = html::href(array(
				"caption" => "ignoreeri",
				"url" => $this->mk_comm_orb(array(
					"commact" => "add_ignored",
					"group" => "ignored_list",
					"cuser" => $creator->id(),
				), 0, "commaction"),
			));
		}
		//arr($t);
		$table = $t->draw();
		$this->read_template("profile_search.tpl");
		$this->vars(array(
			"form" => $html,
			"section" => "orb.aw",
			"table" => $table,
			"ref" => $this->mk_reforb("change",array(
				"id" => $this->common["obj_inst"]->id(),
				"group" => $arr["request"]["group"],
				"s_group" => $arr["request"]["s_group"],
				"no_reforb" =>  1,
			), $arr["request"]["class"]),
			
		));
		//arr($html);
		$rval["el1"] = array(
			"name" => "el1",
			"type" => "text",
			"value" => $this->parse(),
			"no_caption" => 1,
		);
		exit_function("commune::callback_profile_search");
		return $rval;
	}
	
	function my_forum_topics($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "name",
			"caption" => "Teema",
		));
		$t->define_field(array(
			"name" => "posts",
			"caption" => "Postitusi",
			"type" => "int",
		));
		$t->define_field(array(
			"name" => "date",
			"caption" => "Loomisaeg",
		));
		$t->define_field(array(
			"name" => "lastpost",
			"caption" => "Viimane postitus",
		));
		
		$frm = reset($arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_FORUM",
		)));
		$forum = $frm->to();
		$topics = new object_list(array(
			"parent" => $forum->prop("topic_folder"),
			"class_id" => CL_MSGBOARD_TOPIC,
		));
		
		$user = $this->get_user();
		$days = $user->meta("forum_view");
		$needed_topics = array();
		
		// we gonna nicely travel thru every single topic and their comment to find the right ones -- ahz
		foreach($topics->arr() as $topic)
		{
			$tcreator = $topic->createdby();
			$comments = new object_list(array(
				"parent" => $topic->id(),
				"class_id" => CL_COMMENT,
			));
			$comments->sort_by(array(
				"prop" => "created", 
				"order" => "desc",
			));
			// saves as a s**tload on travelling -- ahz
			if($tcreator->name == aw_global_get("uid"))
			{
				if($comments->count() == 0)
				{
					$lastpost = $topic->created();
				}
				else
				{
					$mob = $comments->begin();
					$lastpost = $mob->created();
				}
				$needed_topics[$topic->id()] = array(
					"count" => $comments->count(),
					"obj" => $topic,
					"lastpost" => $lastpost,
				);
			}
			else
			{
				if($comments->count() == 0)
				{
					$lastpost = $topic->created();
				}
				else
				{
					$mob = $comments->begin();
					$lastpost = $mob->created();
				}
				foreach($comments->arr() as $comment)
				{
					$ccreator = $comment->createdby();
					if($ccreator->name() == aw_global_get("uid"))
					{
						$needed_topics[$topic->id()] = array(
							"count" => $comments->count(),
							"obj" => $topic,
							"lastpost" => $lastpost,
						);
						break;
					}
				}
			}
		}
		
		foreach($needed_topics as $tpc)
		{
			// if forum_view setting is added, the additional filter will be put upon thee -- ahz
			if(!empty($days))
			{
				$view = strtotime("-$days days");
				if($tpc["lastpost"] < $view)
				{
					continue;
				}
			}
			$t->define_data(array(
				"name" => $tpc["obj"]->name(),
				"date" => $this->time2date($tpc["obj"]->created(), 2),
				"posts" => $tpc["count"],
				"lastpost" => $this->time2date($tpc["lastpost"], 2),
			));
		}
	}
		
	function callback_forum_settings($arr)
	{
		$user = $this->get_user();
		$view = $user->meta("forum_view");
		$opts = array(10, 30, 60, 100, 200, 365);
		$ropts = array(0 => "näita kõiki");
		foreach($opts as $opt)
		{
			$ropts[$opt] = $opt." päeva";
		}
		$rval = array(
			"days" => array(
				"name" => "days",
				"type" => "select",
				"caption" => "Ära näita vanemaid teemasid, kui",
				"options" => $ropts,
				"selected" => $view,
			),
		);
		return $rval;
	}
	
	// show the content of a friend group -- ahz
	function show_group_cont($arr, $profile, $group = "general")
	{
		//$vars = $group == 0 ?  : array("to" => $group);
		//$cur_group = ;$profile->connections_from($vars)
		$t = &$arr["prop"]["vcl_inst"];
		/*
		$t->define_field(array(
			"name" => "id",
			"caption" => "ID",
		));
		*/
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
		));
		$t->define_field(array(
			"name" => "email",
			"caption" => "E-post",
		));
		$t->define_field(array(
			"name" => "sendmessage",
			"caption" => "",
		));
		/*
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));
		*/
		if($group == "general")
		{
			$group_cont = $profile->connections_from(array("type" => "RELTYPE_FRIEND"));
			foreach($group_cont as $group_item_c)
			{
				$obj = $group_item_c->to();
				$creator = $obj->createdby();
				$person = $obj->get_first_obj_by_reltype("RELTYPE_PERSON");
				//arr($person->properties());
				$t->define_data(array(
					"id" => $obj->id(),
					"name" => html::href(array(
						"url" => $this->mk_comm_orb(array(
							"group" => "friend_details",
							"profile" => $obj->id(),
						)),
						"caption" => $creator->name(),
					)),
					"email" => html::href(array(
						"url" => "mailto:".$person->prop("email"),
						"caption" => $person->prop("email"),
					)),
					"sendmessage" => html::href(array(
						"url" => $this->mk_comm_orb(array(
							"cuser" => $creator->name(),
							"group" => "newmessage",
						)),
						"caption" => "Saada sõnum",
					)),
				));
			}
		}
		else
		{
			$group = obj($group);
			$profiles = $group->meta("friends");
			foreach($profiles as $profile)
			{
				$obj = obj($profile);
				$creator = $obj->createdby();
				$person = $obj->get_first_obj_by_reltype("RELTYPE_PERSON");
				//arr($person->properties());
				$t->define_data(array(
					"id" => $obj->id(),
					"name" => html::href(array(
						"url" => $this->mk_comm_orb(array(
							"group" => "friend_details",
							"profile" => $obj->id(),
						)),
						"caption" => $creator->name(),
					)),
					"email" => html::href(array(
						"url" => "mailto:".$person->prop("email"),
						"caption" => $person->prop("email"),
					)),
					"sendmessage" => html::href(array(
						"url" => $this->mk_comm_orb(array(
							"cuser" => $creator->name(),
							"group" => "newmessage",
						)),
						"caption" => "Saada sõnum",
					)),
				));
			}
		}
	}
	
	// show the groups for friends -- ahz
	function show_groups($arr, $profile, $groups)
	{
		// ok, lets count ALL the friends -- ahz
		$person = $this->get_person();
		$profiles = $person->connections_from(array(
			"type" => "RELTYPE_PROFILE",
		));
		$all_friends = 0;
		foreach($profiles as $prof)
		{
			$profile = $prof->to();
			$friends = $profile->connections_from(array(
				"type" => "RELTYPE_FRIEND",
			));
			$all_friends += count($friends);
		}
		$filter = array();
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "name",
			"caption" => "Grupp",
		));
		$t->define_field(array(
			"name" => "count",
			"caption" => "Sõpru grupis",
			"type" => "int",
		));
		foreach($groups as $gr)
		{
			$group = obj($gr->prop("relobj_id"));
			$group_obj = $gr->to();
			$count = $group->meta("friends");
			$all_friends -= count($count);
			//arr($group);
			$t->define_data(array(
				"name" => html::href(array(
					"caption" => $group_obj->prop("name"),
					"url" => html::get_change_url($arr["obj_inst"]->id(), array(
						"f_group" =>$group->id(),
						"group" => "friend_groups",
					)),
				)),
				"count" => count($count),
			));
		}
		$t->define_data(array(
			"name" => html::href(array(
				"caption" => "üldine",
				"url" => html::get_change_url($arr["obj_inst"]->id(), array(
						"f_group" =>$group->id(),
						"group" => "general",
					)),
			)),
			"count" => $all_friends,
		));
	}
	
	// wrapper for friend groups showing -- ahz
	function show_friend_groups($arr)
	{
		$profile = $this->common["my_profile"] ? $this->common["my_profile"] : $this->get_active_profile();
		if(isset($arr["request"]["f_group"]))
		{
			$this->show_group_cont($arr, $profile, $arr["request"]["f_group"]);
		}
		else
		{
			$groups = $profile->connections_from(array("type" => "RELTYPE_FRIEND_GROUPS"));
			if(empty($groups))
			{
				$this->show_group_cont($arr, $profile);
			}
			else
			{
				$this->show_groups($arr, $profile, $groups);
			}
		}
	}

	function show_prof_comments($arr)
	{
		$profile = $this->common["my_profile"] ? $this->common["my_profile"] : $this->get_active_profile();
		//arr($arr);
		$prop = &$arr["prop"];
		$t = &$prop["vcl_inst"];
		$t->define_field(array(
			"name" => "id",
			"caption" => "ID",
			"sortable" => "1",
		));
		$t->define_field(array(
			"name" => "uname",
			"caption" => "Nimi",
			"sortable" => "1",
		));
		$t->define_field(array(
			"name" => "ctime",
			"caption" => "Aeg",
			"sortable" => "1",
		));
		$t->define_field(array(
			"name" => "comment",
			"caption" => "Kommentaar",
		));
		$t->define_field(array(
			"name" => "status",
			"caption" => "Staatus",
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));
		$clist = new object_list(array(
			"parent" => $profile->id(),
			"class_id" => CL_COMMENT,
			"sort_by" => "created",
		));
		$t->draw_text_pageselector(array(
			"records_per_page" => 25, // rows per page
			"d_row_cnt" => count($clist->arr()), // total rows 
		));
		foreach($clist->arr() as $comment)
		{
			//arr($this->get_active_profile());
			//arr($comment->createdby());
			$creator_prof = obj($comment->createdby());
			$creator = $creator_prof->get_first_obj_by_reltype("RELTYPE_PERSON");
			$prof = $creator->get_first_obj_by_reltype("RELTYPE_PROFILE");
			$t->define_data(array(
				"id" => $comment->id(),
				"uname" => html::href(array(
					"url" => $this->mk_comm_orb(array(
						"profile" => $prof->id(),
						"group" => "friend_details",
					)),
					"caption" => $creator_prof->name(),
				)),
				"ctime" =>$this->time2date($comment->created(), 2),
				"comment" => $comment->prop("commtext"),
				"status" => $comment->status() == STAT_ACTIVE ? "nähtav" : "peidetud",
			));
		}
	}
	
	function show_list($arr)
	{
		$loc = $arr["request"]["group"];
		if($loc == "lists" || $loc == "friend_list")
		{
			$obj = $this->common["my_profile"] == true ? $this->common["my_profile"] : $this->get_active_profile();
		}
		else
		{
			$obj = $this->get_user();
		}
		//arr($arr);
		$prop = &$arr["prop"];
		$t = &$prop["vcl_inst"];
		$t->define_field(array(
			"name" => "id",
			"caption" => "ID",
			"sortable" => "1",
		));
		$t->define_field(array(
			"name" => "uname",
			"caption" => "Nimi",
			"sortable" => "1",
		));
		$t->define_chooser(array(
			 "name" => "sel",
			"field" => "id",
		));
		$vars = array(
			"lists" => "RELTYPE_FRIEND",
			"friend_list" => "RELTYPE_FRIEND",
			"blocked_list" => "RELTYPE_BLOCKED",
			"ignored_list" => "RELTYPE_IGNORED",
		);
		//$profile = $this->get_active_profile();
		$clist = $obj->connections_from(array(
			"type" => $vars[$arr["request"]["group"]],
		));
		/*
		$clist = new object_list(array(
			"parent" => $profile_o->id(),
			"reltype" => "RELTYPE_FRIEND",
			"sort_by" => "created",
		*/
		//arr($clist);
		foreach($clist as $citem)
		{
			
			/*
			$pers = $u_prof->get_first_obj_by_reltype("RELTYPE_PERSON");
			*/
			if($loc == "lists" || $loc == "friend_list")
			{
				$u_prof = $citem->to();
				$user = $this->get_user_for_profile($u_prof);
				$id = $u_prof->id();
			}
			else
			{
				$user = $citem->to();
				$person = $user->get_first_obj_by_reltype("RELTYPE_PERSON");
				$u_prof = $person->get_first_obj_by_reltype("RELTYPE_PROFILE");
				$id = $user->id();
			}
			$t->define_data(array(
				"id" => $id,
				"uname" => html::href(array(
					"url" => $this->mk_comm_orb(array(
					"group" => "friend_details",
					"profile" => $u_prof->id(),
					)),
					"caption" => $user->name(),
				)),
			));
		}
	}
	
	function show_switch_profile($arr)
	{
		//arr($arr);
		$gnames = array("switch_profile","l_switch_profile","c_switch_profile", "f_switch_profile");
		$groupinfo = array(
			$gnames[0] => array(
				"caption" => "Pilte",
				"group" => "my_images",
				),
			$gnames[1] => array(
				"caption" => "Sõpru",
				"group" => "friend_list",
			),
			$gnames[2] => array(
				"caption" => "Kommentaare",
				"group" => "prof_comments",
			),
			$gnames[3] => array(
				"caption" => "Sõpru",
				"group" => "prof_friends",
			),
		);
		$group = $arr["request"]["group"];
		$prop = &$arr["prop"];
		$t = &$prop["vcl_inst"];
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"sortable" => "1",
		));
		$t->define_field(array(
			"name" => "objects",
			"caption" => $groupinfo[$group]["caption"],
			"sortable" => "1",
			"type" => "int",
		));
		if($group == $gnames[1])
		{
		$t->define_field(array(
			"name" => "ignored",
			"caption" => "Ignoreerituid",
			"sortable" => "1",
			"type" => "int",
		));
		$t->define_field(array(
			"name" => "blocked",
			"caption" => "Blokeerituid",
			"sortable" => "1",
			"type" => "int",
		));
		}
		$t->define_field(array(
			"name" => "sel",
			"sortable" => "1",
		));
		//hangime ühendused kõigile profiilidele
		if ($person = $this->get_person())
		{
		
			$cons_to_profile = $person->connections_from(array(
				"type" => "RELTYPE_PROFILE",  //14,
			));
			$active_profile_id = $person->meta("active_profile");
			foreach($cons_to_profile as $conn)
			{
				
				$item = $conn->to();
				
				if($group == $gnames[0])
				{
					$objects = $item->connections_from(array(
						"type" => "RELTYPE_IMAGE", //12,
					));
				 }
				elseif($group == $gnames[1])
				{
					$objects = $item->connections_from(array(
						"type" => "RELTYPE_FRIEND",
					));
					$ignored = $item->connections_from(array(
						"type" => "RELTYPE_IGNORED",
					));
					$blocked = $item->connections_from(array(
						"type" => "RELTYPE_BLOCKED",
					));
				}
				elseif($group == $gnames[2])
				{
					$objs = new object_list(array(
						"parent" => $item->id(),
						"class_id" => CL_COMMENT,  
					));
					$objects = $objs->arr();
				}
				elseif($group == $gnames[3])
				{
					$objects = $item->connections_from(array(
						"type" => "RELTYPE_FRIEND",
					));
				}
				$vars = array(
					"name" => html::href(array(
						"url" => $this->mk_comm_orb(array(
							"group" => $groupinfo[$group]["group"],
							"my_profile" => $item->id(),
						)),
						"caption" => $item->name(),
					)),
					"objects" => count($objects),
					"selected" => ($item->id() == $active_profile_id ? "aktiivne" : ""),
				);
				if($group == $gnames[1])
				{
					$vars["ignored"] = count($ignored);
					$vars["blocked"] = count($blocked);
				}
				$t->define_data($vars);
			}
		}
	}
	
	function on_get_subtemplate_content($arr)
	{
		$objs = new object_list(array(
			"class_id" => CL_COMMUNE,
		
		));
		$obj = reset($objs->arr());
		$arr["inst"]->vars(array(
			"MYAVATAR" => $this->render_context_panel($obj),
			"MYPANEL" => $this->render_my_panel($obj),
		));
	}
	
	function render_my_panel($obj)
	{
		$this->sub_merge = 1;
		$this->read_template("my_panel.tpl");
		if($profile = $this->get_active_profile())
		{
			$inst = get_instance(CL_IMAGE);
			$friends = $profile->connections_from(array(
				"type" => "RELTYPE_FRIEND",
				//"sort_by" => 
			));
			//$this->common["obj_inst"]->id()
			$friends = array_slice($friends, 0, 3);
			//arr($friends);
			$cont = array();
			foreach($friends as $friend)
			{
				$prof = $friend->to();
				$img = $prof->get_first_obj_by_reltype("RELTYPE_IMAGE");
				if(!is_object($img))
				{
					$url = "http://epood.primeframe.ee/img/products/puudub.gif";
				}
				else
				{
					$imgdata = $inst->get_image_by_id($img->id());
					$url = $imgdata["url"];
				}
				$cont[] = html::get_change_url($obj->id(), array(
					"group" => "friend_details",
					"profile" => $prof->id(),
				), html::img(array(
					"url" => $url,
					"border" => 0,
				)));
			}
			$this->vars(array(
				"link" => html::get_change_url($obj->id(), array("group" => "prof_friends")),
				"title" => "Minu sõbrad",
				"content" => implode(" ", $cont),
			));
			$this->parse("item");
			$user = $this->get_user();
			$comms = $user->connections_to(array(
				"type" => array(3, 4),
				"from.class_id" => CL_COMMUNITY,
			));
			//arr($comms);
			$comms = array_slice($comms, 0, 3); 
			$cont = array();
			foreach($comms as $comm)
			{
				$com = $comm->from();
				$cont[] = html::get_change_url($com->id(), array("group" => "content"), $com->name());
			}
			$this->vars(array(
				"title" => "Minu kogukonnad",
				"link" => html::get_change_url($obj->id(), array("group" => "my_communities")),
				"content" => implode("<br />\n", $cont),
			));
			$this->parse("item");
		}
		return $this->parse();
		//return "sõbrad ja kogukonnad";
	}
	
	function render_context_panel($arr)
	{
		$this->read_template("context_panel.tpl");
		$content = "";
		/*
		if (!$profile = $this->common["profile"])
		{
		*/
			if (!$profile = $this->get_active_profile())
			{
				$this->vars(array("content" => $this->parse("dev")));
				return $this->parse();
			}
		//}
		//got profile..
		
		//siin vaja selgeks teha, mida näidata.
		
		//oletame et on vaja pilti näidata.
		//ok. prooviks siis pilti näidata..
		
		if (!$avatar_image_id = $profile->prop("avatar_image"))
		{
			if ($profile_c = $profile->get_first_conn_by_reltype("RELTYPE_IMAGE"))
			{
				$avatar_image_id = $profile_c->prop("to");
			}
		}
		if ($avatar_image_id)
		{
			$img_i = get_instance(CL_IMAGE);
			$img_url = $img_i->get_url_by_id($avatar_image_id);
			$person = $profile->get_first_obj_by_reltype("RELTYPE_PERSON");
			$avatar_caption = aw_global_get("uid"); //person->prop("firstname")." ".$person->prop("lastname");
			$this->vars(array(
				"avatar_img_url" => $img_url,
				"avatar_caption" => $avatar_caption,
			));
			$content = $this->parse("avatar");
		}
		else
		{
			$content = $this->parse("empty_box");
		}

		//ja lõpuks renderdame sisu.
		$this->vars(array(
			//"content" => $this->parse("dev"),
			"content" => $content,
		));
		return $this->parse();
	}
	
	function do_tbl_image_comments($arr)
	{
		$prop = &$arr["prop"];
		$t = &$prop["vcl_inst"];
		$t->define_field(array(
			"name" => "id",
			"caption" => "ID",
			"sortable" => "1",
		));
		$t->define_field(array(
			"name" => "uname",
			"caption" => "Nimi",
			"sortable" => "1",
		));
		$t->define_field(array(
			"name" => "ctime",
			"caption" => "Aeg",
			"sortable" => "1",
		));
		$t->define_field(array(
			"name" => "comment",
			"caption" => "Kommentaar",
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));
		$clist = new object_list(array(
			"parent" => $arr["request"]["img_id"],
			"class_id" => CL_COMMENT,
			"sort_by" => "created",
		));
		foreach($clist->arr() as $comment)
		{
			//arr($this->get_active_profile());
			//arr($comment->createdby());
			$creator_prof = obj($comment->createdby());
			$creator = $creator_prof->get_first_obj_by_reltype("RELTYPE_PERSON");
			$prof = $creator->get_first_obj_by_reltype("RELTYPE_PROFILE");
			$t->define_data(array(
				"id" => $comment->id(),
				"uname" => html::href(array(
					"url" => $this->mk_comm_orb(array(
						"profile" => $prof->id(),
						"group" => "friend_details",
					)),
					"caption" => $creator_prof->name(),
				)),
				"ctime" =>$this->time2date($comment->created(), 2),
				"comment" => $comment->prop("commtext"),
			));
		
		}
	}
	
	function get_connections_to_profiles()
	{
		if($person = $this->get_person())
		{
			$cons = $person->connections_from(array(
				"type" => "RELTYPE_PROFILE",  //14,
			));
			return $cons;
		}
	}

	function do_tbl_my_profiles($arr)
	{
		$prop = &$arr["prop"];
		$t = &$prop["vcl_inst"];
		$t->define_field(array(
			"name" => "id",
			"caption" => "ID",
			"sortable" => "1",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"sortable" => "1",
		));
		
		$t->define_field(array(
			"name" => "pics",
			"caption" => "Pildid",
		));
		
		$t->define_field(array(
			"name" => "active_prof",
			"caption" => "Vaikeprofiil",
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));

		//hangime ühendused kõigile profiilidele
		if($cons_to_profile = $this->get_connections_to_profiles())
		{
			//arr($cons_to_profile);
			$active_profile_id = $this->get_active_profile();
			foreach($cons_to_profile as $conn)
			{
				$item = $conn->to();
				$images = $item->connections_from(array(
						"type" => "RELTYPE_IMAGE", //12,
				));
				$t->define_data(array(
					"name" => html::href(array(
								"url" => $this->mk_comm_orb(array(
								"group" => "profile_view",
								"my_profile" => $item->id(),
								)),
								"caption" => $item->name(),
						)),
					"id" => $item->id(),
					"pics" => html::href(array(
								"url" => $this->mk_comm_orb(array(
									"group" => "my_images",
									"my_profile" => $item->id(),
								)),
								"caption" => "pildid (".count($images).")",
						)),
					"active_prof" => html::radiobutton(array(
						"name" => "active_profile",
						"value" => $item->id(),
						"checked" => (($item->id() == $active_profile_id->id()) ? true : false),
					)),
				));
			}
		}
	}
	
	function render_actions($arr)
	{
		$this->read_template("action_menu.tpl");
		$html = "<br />\n";
		
		$actions = array(
			//"tee sõbraks" => array("act" => "add_friend", "profile" => "111")
		);
		
		$basic_params = array(
			"id" => $arr["obj_inst"]->id(),
			"group" => $arr["request"]["group"],
			"action" => "commaction",
		);
			
		foreach ($actions as $caption => $action_params)
		{
			$params = array_merge($basic_params, $action_params);
			$this->vars(array(
				"action_link" => html::href(array(
								"url" => $this->mk_comm_orb($params),
								"caption" => $caption,
						)),
			));
			$html .= $this->parse("actions");
		}
		$this->vars(array("actions" => $html));
		return $this->parse();
	}
	
	function render_online($online)
	{
		$this->read_template("show_profile.tpl");
		$this->vars(array(
			"online_light" => $online ? "green" : "red",
			"online_caption" => $online ? "online" : "offline",
		));
		return $this->parse("online");
	}

	function render_karma($karma)
	{
		$this->read_template("show_profile.tpl");
		$this->vars(array(
			"karma_smiley" => $karma ? "happy" : ($karma < 0 ? "sad" : "normal"),
			"alt_karma" => $karma,
		));
		return $this->parse("karma");
	}

	function render_profile_header($vars)
	{
		$arr = $vars["arr"];
		$profile = $vars["profile"];
		
		$prof_i = get_instance(CL_PROFILE);
		$online = $prof_i->is_online(array("obj_inst" => $profile));
		$karma = 0; //pos, neg või 0 //not implemented yet.
		
		$this->read_template("show_profile.tpl");

		$sub_online = $this->render_online($online);
		$sub_karma = $this->render_karma($karma);
		
		// stupid "muuda" design element
		$prof_person = $profile->get_first_obj_by_reltype("RELTYPE_PERSON");
		$my_person = $this->get_person();
		if ($my_person->id() == $prof_person->id())
		{
			$this->vars(array(
				"my_profile_switch" => $this->render_link("MUUDA", $this->mk_comm_orb(array("group" => "profile_change",
				"my_profile" => $profile->id(),
				))),
			));
			$this->vars(array(
				"muuda" => $this->parse("muuda"),
			));
		}
		
		$this->vars(array(
			"online" => $sub_online,
			"karma" => $sub_karma,
		));
		return $this->parse("header");
	}
	
	// "this is not my profile, you got it all wrong!!!!"
	// in other words, a function to check whether this is your profile or not -- ahz
	function is_not_my_profile($id)
	{
		$person = $this->get_person();
		$profiles = $person->connections_from(array(
			"type" => "RELTYPE_PROFILE",
		));
		foreach($profiles as $profile)
		{
			// we well test your innosence here upon -- ahz
			if($profile->prop("to") == $id or !is_oid($id))
			{
				return false;
			}
		}
		return true;
	}
	
	// a check to check that it's not me.. don't ask -- ahz
	function is_not_me($id)
	{
		if($id == aw_global_get("uid_oid"))
		{
			return false;
		}
		return true;
	}
	
	function add_connection($vars)
	{
		//arr($vars);
		// this is the place where you can add all sort of connections -- ahz
		$rels = array("FRIEND", "IGNORED", "BLOCKED");
		$my_user = $this->get_user();
		if($vars["profile"])
		{
			$my_profile = $this->common["my_profile"] ? $this->common["my_profile"] : $this->get_active_profile();
			$f_profile_id = $vars["profile"];
			if($this->is_not_my_profile($f_profile_id))
			{
				// remove the block and ignore, if added as friend -- ahz
				$user = $this->get_user_for_profile(obj($f_profile_id));
				if($my_user->is_connected_to(array(
					"to" => $user->id(),
				)))
				{
					$my_user->disconnect(array(
						"from" => $user->id(),
					));
				}
				$my_profile->connect(array(
					"to" => $f_profile_id,
					"reltype" => "RELTYPE_".$rels[$vars["type"]],
				));
			}
		}
		else
		{
			$f_user = $vars["user"];
			if($this->is_not_me($f_user))
			{
				$my_user->connect(array(
					"to" => $f_user,
					"reltype" => "RELTYPE_".$rels[$vars["type"]],
				));
			}
		}
		/*
		
		// this is a cruel check, that i couldn't possibly be my own friend -- ahz 
		if($this->is_not_my_profile($f_profile_id))
		{
			
			$my_profile->connect(array(
				"to" => $f_profile_id,
				"reltype" => "RELTYPE_".$rels[$vars["type"]],
			));
		}
		*/
	}
	
	function add_contact($arr)
	{
		//arr($arr);
		if($this->can("view", $arr["cuser"]))
		{
			$user = $this->get_user();
			$contact_list = $user->connections_to(array(
				"type" => 1, //RELTYPE_LIST_OWNER
				"from.class_id" => 811,
			));
			foreach($contact_list as $tlist)
			{
				$list = $tlist->from();
			}
			$list->connect(array(
				"to" => $arr["cuser"],
				"reltype" => "RELTYPE_ADDED_USER",
			));
		}
	}
	
	function mk_comm_orb($params, $obj_inst_id = 0, $action = "change")
	{
		if(empty($obj_inst_id))
		{
			$obj_inst_id = $this->common["obj_inst"]->id();
		}
		$def_params = array(
			"id" => $obj_inst_id,
			"group" => "general",
			//"section" => $this->common["arr"]["request"]["section"],
		);
		//$params will override $def_params
		return $this->mk_my_orb($action, $params + $def_params, CL_COMMUNE);
	}
	
	function render_link($caption, $url)
	{
		return html::href(array(
			"url" => $url,
			"caption" => $caption,
		));
	}
	
	function get_link_for_obj($obj)
	{
		$str = "";
		$params = array("id" => $obj->id());
		
		$str .= html::href(array(
			"url" => $this->mk_my_orb($params),
			"caption" => $obj->name() ? $obj->name(): $obj->id(),
		)).
		$str .= " ";
		
		$params = array("parent" => $obj->parent());
		
		$str .= html::href(array(
			"url" => $this->mk_my_orb("right_frame", $params, "admin_menus"),
			"caption" => "[at]",
		));
		return $str;
	}
	
	function update_message_conditions($arr)
	{
		$person = $this->get_person();
		$person->set_meta("message_conditions", $arr["request"]["ms"]);
		$person->save();
	}
	
	function message_conditions($arr)
	{
		$person = $this->get_person();
		$prop = &$arr["prop"];
		$t = &$prop["vcl_inst"];
		$t->define_field(array(
			"name" => "email",
			"caption" => "e-mailile",
			"sorted" => 0,
		));
		$t->define_field(array(
			"name" => "inbox",
			"caption" => "postkasti",
			"sorted" => 0,
		));
		$t->define_field(array(
			"name" => "type",
			"caption" => "",
			"sorted" => 0,
		));
		if(!$person_ms = $person->meta("message_conditions"))
		{
			$person_ms = $this->new_message_conditions($person);
		}
		//arr($person_ms);
		foreach($this->msg_vars as $key => $value)
		{
			$t->define_data(array(
				"email"=> html::checkbox(array(
				"name" => "ms[".$key."][0]",
				"value" => "true",
				"checked" => $person_ms[$key][0],
				)),
				"inbox" => html::checkbox(array(
				"name" => "ms[".$key."][1]",
				"value" => "true",
				"checked" => $person_ms[$key][1],
				)),
				"type" => $value[2],
			));
		}
	}
	
	function new_message_conditions($person)
	{
		//if person does not already have message settings, then create them
		foreach($this->msg_vars as $key => $value)
		{
			$rval[$key][0]=$value[0];
			$rval[$key][1]=$value[1];
		}
		$person->set_meta("message_conditions",$rval);
		$person->save();
		return $rval;
	}
	
	function browsing_conditions($arr)
	{
		$person_o = $this->get_person();
		if(!($values = $person_o->meta("browsing_conditions")))
		{
			$values["sexorient"] = array();
		}
		//arr($values);
		$profile_o = $this->get_active_profile();
		$prof_cls_i = get_instance(CL_PROFILE);
		$prof_cls_i->init_class_base();
		/*
		$props = $prof_cls_i->get_property_group(array(
			"group" => "settings",
		));
		*/
		$props = $this->get_properties_by_name(array(
			"clfile" => "profile",
			"props" => "sexual_orientation",
		));
		// selle asemel et näha hullumoodi vaeva, otsides andmebaasist
		// maksimaalset ja minimaalset vanust, teeb hoopis 1 - 100
		// vanusevahemiku ja kui selliseid ei leidu, annab veateate
		
		/*	$v = $o_inst->get_age(array("obj_inst" => $obj));	*/
		
		// stringidel ei maksa eriti min/max otsingut teha, võib väga lambi vastuseid saada :|
		
		$query = "select max(user_field1) as max,min(user_field1) as min from aw_profiles";
		
		//$query = "select o.oid,v.oid left from kliendibaas_isik o left join kliendibaas_isik v on where  min(v.birthday) and max(o.birthday)";
		//from where max(o.birthday) 
		//$q = "SELECT profile2image.* FROM profile2image LEFT JOIN objects ON (profile2image.img_id = objects.oid) WHERE objects.status = 2 ORDER BY rand() LIMIT 1
		$row = $this->db_fetch_row($query);
		//$row2 = $this->db_fetch_row($query2);
		//arr($row2);
		$min = $prof_cls_i->get_age2($row["min"]);
		$max = $prof_cls_i->get_age2($row["max"]);
		$aoptions = array("--- vali ---");
		if($min == 0)
		{
			$min++;
		}
		for($i = $min; $i <= $max; $i++)
		{
			$aoptions[$i] = $i;
		}
		// asd, get ages argh :(
		$var["sexual_orientation"] = $props["sexual_orientation"];
		$so = $prof_cls_i->parse_properties(array(
			"properties" => $var,
			"obj_inst" => $profile_o,
		));
		$options = array(
			"0" => "Mõlemad",
			"1" => "Mees",
			"2" => "Naine",
		);
		foreach($options as $key => $value)
		{
			$opts.=" ".html::radiobutton(array(
				"name" => "bc[gender]",
				"checked" => ($values["gender"]==$key ? true : false),
				"value" => $key,
				"caption" => $value,
			));
		}
		//arr($so["sexual_orientation"]["options"]);
		$sel_arr = array();
		foreach($so["sexual_orientation"]["options"] as $key => $value)
		{
			$sopt.=" ".html::checkbox(array(
				"name" => "bc[sexorient][$key]",
				"checked" => (array_key_exists($key, $values["sexorient"]) ? true : false),
				"value" => $key,
				"caption" => $value,
			));
		}
		$prop = &$arr["prop"];
		$t = &$prop["vcl_inst"];
		$t->define_field(array(
			"name" => "setting",
			"caption" => "Seade",
		));
		$t->define_field(array(
			"name" => "settings",
		));
		$t->define_data(array(
			"setting" => "Sugu",
			"settings" => $opts
		));
		$t->define_data(array(
			"setting" => "Vanus",
			"settings" => html::select(array(
				"name" => "bc[age_s]",
				"options" => $aoptions,
				"selected" => $values["age_s"],
			))." kuni ".html::select(array(
				"name" => "bc[age_e]",
				"options" => $aoptions,
				"selected" => $values["age_e"],
			)),
		));
		$t->define_data(array(
			"setting" => "Seksuaalne orientatsioon",
			"settings" => $sopt,
		));
	}
	
	function update_browsing_conditions($arr)
	{
		$person = $this->get_person();
		$person->set_meta("browsing_conditions", $arr["request"]["bc"]);
		$person->save();
	}
	
	function view_conditions($arr)
	{
		if ($person = $this->get_person())
		{	
			$prop = &$arr["prop"];
			$t = &$prop["vcl_inst"];
			$t->define_field(array(
				"name" => "group",
				"caption" => "Grupp",
				"sorted" => 0,
			));
			$t->define_field(array(
				"name" => "p_show_settings",
				"caption" => "Profiili kuvamise seaded",
				"sorted" => 0,
			));
			$options = array("default" => "aktiivne profiil");
			$profs = $person->connections_from(array(
				"type" => "RELTYPE_PROFILE",
			));
			foreach($profs as $prof)
			{
				$prof_o = $prof->to();
				$options[$prof_o->id()] = $prof_o->name();
				//echo $prof_o->name();
			}
			$options[0] = "ei näita antud grupile";
			$groups = $this->common["obj_inst"]->connections_from(array(
				"type" => "RELTYPE_USER_GROUPS",
			));
			//arr($groups);
			//$groups = $groups->arr();
			$selopts = $person->meta("view_conditions");
			foreach($groups as $group)
			{
				$group_o = $group->to();
				//arr($group_o->properties());
				$t->define_data(array(
					"group" => $group_o->name(),
					"p_show_settings" => html::select(array(
						"name" => "profset[".$group_o->id()."]",
						"options" => $options,
						"selected" => $selopts[$group_o->id()],
					)),
				));
			}
		}
	}
	
	function update_view_conditions($arr)
	{
		$profsets = $arr["request"]["profset"];
		if(is_array($profsets))
		{
			foreach($profsets as $pid => $prof)
			{
				if($prof == "default")
				{
					unset($profsets[$pid]);
				}
			}
		}
		$person = $this->get_person();
		$person->set_meta("view_conditions", $profsets);
		$person->save();
	}
	
	function cb_get_last_added_items_for_page($vars)
	{
		$offset = $vars["offset"];
		$length = $vars["length"];
		$ol = $vars["params"]["object_list_for_page_view"];

		$result = array();
		$image_key_value_pairs = $ol->arr();
		$showed_imgs = array_slice($image_key_value_pairs, $offset, $length);
		
		foreach ($showed_imgs as $image)
		{
			// image --------------------------------------------
			$img_i = get_instance(CL_IMAGE);
			$img_url = $img_i->get_url_by_id($image->id());
			
			//pean otsima vastupidise seose img->profile->person
			$conn_prof_to_img = reset($image->connections_to(array("type"=>12)));
			$profile = $conn_prof_to_img->from();
			
			// name & profile -----------------------------------
			$prof_i = get_instance(CL_PROFILE);
			if ($person = $prof_i->get_person_for_profile($profile))
			{
				$name = $person->prop("firstname")." ".$person->prop("lastname"); //$person->prop("name");
			}
			else
			{
				$name = "(isikut pole)";
			}

			// props --------------------------------------------
			$prof_view_url = $this->mk_comm_orb(array(
				"profile" => $profile->id(),
				"commact" => "profile",
				"group" => "friends",
			));
			$name_link = $this->render_link($name, $prof_view_url);
			$created = $this->time2date($image->created(), 2);
			
			$rate = get_instance(CL_RATE);
			$rating = $rate->get_rating_for_object($image->id());
			
			$result["items"][] = array(
				"img_url" => $img_url,
				"img_link" => $prof_view_url,
				"name_link" => $name_link,
				"prop1" => $person->prop("gender") == 2 ? "Naine" : "Mees",
				"prop2" => "lisatud: ".$created,
				"prop3" => "hinne: ".$rating,
				"prop4" => $this->render_online($prof_i->is_online(array("obj_inst" => $profile))),
			);
			
		}	
		return $result;
	}
	
	function render_page_view($vars)
	{
		$item_count = $vars["item_count"]; //ei pruugi olla antud, kuna ei teata seda veel. callback peab leidma selle. ta peab toetama siis teisi parameetreid.
		$params = $vars["params"];
		
		$all_items = $vars["items"];
		//ülemine vs. alumised
		$get_data_obj = $vars["get_data_obj"];
		$get_data_method = $vars["get_data_method"];
		//$callback_get_data = $vars["callback_get_data"];
		//arr($arr);
		$ft_page = $vars["ft_page"];
		//=============================================
		
		$first_page_nr = 0;
		$page = is_numeric($ft_page) ? ($ft_page - $first_page_nr) : $first_page_nr;
		//$page = zero based page nr
		$items_in_page = 10;
		
		$this->read_template("page_view.tpl");

		// hangime $showed_items:
		$showed_items = array();
		//if (isset($get_data_obj) and isset($get_data_method) and is_callable(array($get_data_obj, $get_data_method)))
		if (isset($get_data_obj) and isset($get_data_method) and method_exists($get_data_obj, $get_data_method))
		{
			if (isset($item_count))
			{
				$offset = $page * $items_in_page;
				$length = ($item_count - $offset < $items_in_page) ? ($item_count - $offset) : $items_in_page;

				$data = $get_data_obj->$get_data_method(array(
					"offset" => $offset,
					"length" => $length,
					"params" => $params,
				));
			}
			else
			{
				$data = $get_data_obj->$get_data_method(array(
					"page" => $page,
					"items_in_page" => $items_in_page,
					"params" => $params,
				));
				$item_count = $data["item_count"];
			}
			$showed_items = empty($data["items"]) ? array() : $data["items"]; //juhuks kui $data["items"] pole määratud
		}
		else
		//andsin kogu array kaasa callbacki (cb_*) asemel,
		//tavaliselt aga seda ei tasu teha, sest iga itemi jaoks tuleb arvutada mingeid täiendavaid
		//infoelemente, mida kõigi itemite jaoks ei tasu teha, sest neist näidatakse ainult 1 lk jagu
		if (is_array($all_items))
		{
			$showed_items = array_slice($all_items, ($page - $first_page_nr) * $items_in_page, $items_in_page);
			
		}
		//arr($showed_items);
		// beg: maname navbari -----------
		$navbar_sub = "navbar";
		//--- beg: table -----------
		load_vcl("table");
		$t = new aw_table();
		//see saab requestist teada lehe, millel parajasti ollakse:
		$navbar = $t->draw_text_pageselector(array(
			"records_per_page" => $items_in_page,
			"d_row_cnt" => $item_count,
		));
		//--- end: table -----------
		$this->vars(array(
			$navbar_sub => $navbar,
		));
		// end: maname navbari ------------
		
		// templeidist sõltuv kuvamine:
		$parsed_items = array();
		$item_nr = 0;
		
		foreach ($showed_items as $item)
		{
			$this->vars(array(
				"img_url" => $item["img_url"],
				"img_link" => $item["img_link"],
				"name_link" => $item["name_link"],
				"prop1" => $item["prop1"],
				"prop2" => $item["prop2"],
				"prop3" => $item["prop3"],
				"prop4" => $item["prop4"],
			));

			$parsed_items["item$item_nr"] = $this->parse("item");
			$item_nr++;
		}
		$this->vars($parsed_items);
		
		return $this->parse();
	}
	
	function cb_get_friend_items_for_page($vars)
	{
		$offset = $vars["offset"];
		$length = $vars["length"];
		$profile = $vars["params"]["profile"];
		$result = array();
		$conns_to_friends = $profile->connections_from(array(
			"type" => "RELTYPE_FRIEND",
		));
		$showed_conns = array_slice($conns_to_friends, $offset, $length);
		
		foreach($showed_conns as $conn)
		{
			$friend_profile = $conn->to();
			$friend_i = $friend_profile->instance();

			// image --------------------------------------------
			if ($image_id = $friend_profile->prop("avatar_image"))
			{
				$img_i = get_instance(CL_IMAGE);
				$img_url = $img_i->get_url_by_id($image_id);
			}
			elseif ($image_c = $friend_profile->get_first_conn_by_reltype("RELTYPE_IMAGE"))
			{
				$img_i = get_instance(CL_IMAGE);
				$img_url = $img_i->get_url_by_id($image_c->prop("to"));
			}
			else
			{
				$img_url = "http://epood.primeframe.ee/img/products/puudub.gif";
			}
			
			// name & profile -----------------------------------
			if ($person = $friend_i->get_person_for_profile($friend_profile))
			{
				$name = $person->prop("firstname")." ".$person->prop("lastname"); //
			}
			else
			{
				$name = "(isikut pole)";
			}

			// props --------------------------------------------
			$prof_view_url = $this->mk_comm_orb(array(
				"profile" => $friend_profile->id(),
				//"commact" => "profile",
				"group" => "friend_details",
			));
			$name_link = $this->render_link($name, $prof_view_url);
			$friends_count = count($friend_profile->connections_from(array("type" => "RELTYPE_FRIEND")));
			
			// kõik propid itemisse ------------------------------------
			$result["items"][] = array(
				"img_url" => $img_url,
				"img_link" => $prof_view_url,
				"name_link" => $name_link,
				"prop1" => "vanus: ".$friend_i->get_age(array("obj_inst" => $friend_profile)),
				"prop2" => "sõpru: ".$friends_count,
				"prop3" => $this->render_online($friend_i->is_online(array("obj_inst" => $friend_profile))),
				"prop4" => $this->render_karma(0),
			);
			
		}
		return $result;
	}
	
	function check_rights($id)
	{
		$retval = false;
		if ($this->can("view", $id) and is_oid($id))
		{
			$retval = true;
		}
		return $retval;
	}
	
	function callback_my_images($arr)
	{
		//arr($arr);
		$rv = array();
		// siia tulevad ainult minu profiilide piltide vaated, teiste vaated lähevad kuskile mujale
		// kuidas ma näitan mingi profiili pilte, kui see profiil peab olema samas ka aktiivne?
		// niimoodi näitangi
 		/*arr($this->common);
		$this->common["profile"]->id();
		$prof_obj = is_object($this->common["profile"]) ? $this->common["profile"] : $this->get_active_profile();
			$prof_obj = obj($arr["request"]["profile"]);
			$rv["midavittu"] = array(
				"name" => "profile",
				"type" => "text",
				"caption" => "sae pekki",
				//"display" => "none",
				"value" => $arr["request"]["profile"],
			);
		if(is_object($["profile"]))
		{
			$prof_obj = $this->common["profile"];
		}
		*/
		if(!$person = $this->get_person())
		{
			return $rv;
		}
		if(is_object($this->common["my_profile"]))
		{
			$prof_obj = $this->common["my_profile"];
		}
		elseif(!$prof_obj = $this->get_active_profile())
		{
			return $rv;
		}
		$images = $prof_obj->connections_from(array(
			"type" => "RELTYPE_IMAGE", //12,
		));
		$img_ids = array();
		foreach($images as $imgc)
		{
			$img_ids[] = $imgc->prop("to");
		}
		//arr($images);
		$n = 5;
		$icount = sizeof($images);
		if ($icount > $n)
		{
			$icount = $n;
		};
		$ims = array_values($images);
		$rt = get_instance(CL_RATE);
		$ti = get_instance(CL_IMAGE);
		$options = array("-- vali pilt --");
		$prof_to_person = $person->connections_from(array(
			"type" => "RELTYPE_PROFILE",
		));
		foreach($prof_to_person as $prof)
		{
			$item = $prof->to();
			if($item != $prof_obj)
			{
			$images2 = $item->connections_from(array(
					"type" => "RELTYPE_IMAGE", //12,
				));
				foreach($images2 as $img_c)
				{
					// kui pilt on mitmes profiilis ja valitud profiiliga ka ühendatud
					if(!in_array($img_c->prop("to"),$img_ids))
					{
						$img = $img_c->to();
						$com = $img->comment();
						$options[$img->id()] = (strlen($com) <= 15 ? $com : substr($com,0,15)."...");
					}
				}
			}
		}
		for ($i = 1; $i <= 5; $i++)
		{
			$rv["s".$i] = array(
				"type" => "text",
				"name" => "s".$i,
				"caption" => "Pilt $i",
				"subtitle" => 1,
			);
			$key = $i;
			$new = false;
			if (is_object($ims[$i-1]))
			{;
				$nm = "myimage";
				$new = true;
				$target = $ims[$i-1]->to();
				$comment = $target->comment();
				$imgdata = $ti->get_image_by_id($target->id());
				$rv["st".$i] = array(
					"type" => "text",
					"name" => "st".$i,
					"caption" => "Pilt",
					"value" => html::img(array(
						"url" => $imgdata["big_url"],
					)),
				);
				$key = $target->id();
				// but the key _needs_ to be unique!
				$rv[$nm."_name".$i]= array(
					"name" => $nm."[$key][name]",
					"type" => "text",
					"caption" => "Pildi nimi",
					"value" => (strlen($comment)<=15?$comment:substr($comment,0,15)."..."),
					
				);
				$rv[$nm."_comment".$i] = array(
					"name" => $nm."[$key][comment]",
					"type" => "textarea",
					"caption" => "Pildi kommentaar",
					"value" => $comment,
				);
				$rv[$nm."_file".$i] = array(
					"name" => $nm."[$key][file]",
					"type" => "fileupload",
					"caption" => "Vali uus",
				);
				
				$rv[$nm."_con".$i]= array(
					"name" => "replace[".$key."]",
					"type" => "select",
					"caption" => "Vali olemasolev",
					"options" => $options,
				);
				
				$img_oid = $target->id();
				$rv["rating".$i] = array(
					"type" => "text",
					"name" => "rating".$i,
					"caption" => "Hinne",
					"value" => $rt->get_rating_for_object($img_oid),
				);
				
				$q = "SELECT hits FROM hits WHERE oid = '$img_oid'";
				$this->db_query($q);
				$row = $this->db_next();
				$hits = $row["hits"];
				$rv["hits".$i] = array(
					"type" => "text",
					"name" => "hits".$i,
					"caption" => "Vaatamisi",
					"value" => $hits,
				);
				$rv["d".$i] = array(
					"name" => "delete[".$key."]",
					"type" => "checkbox",
					"caption" => "Kustuta",
				);
				$clist = new object_list(array(
					"parent" => $img_oid,
					"class_id" => CL_COMMENT,
					"sort_by" => "created",
				));
				$clist_arr = $clist->arr();
				$rv["comments".$i] = array(
					"name" => "comments".$i,
					"type" => "text",
					"value" => html::href(array(
						"url" => $this->mk_comm_orb(array(
							"group" => "show_image_comments",
							"img_id" => $target->id(),
						)),
						"caption" => "Kommentaarid (".count($clist_arr).")",
					)),
				);
			}
			else
			{
				$nm = "newimage";
				$rv[$nm."_file".$i] = array(
					"name" => $nm."[$i][file]",
					"type" => "fileupload",
					"caption" => "Vali uus",
				);
				$rv[$nm."_con".$i]= array(
					"name" => "nlink[$i]",
					"type" => "select",
					"caption" => "Vali olemasolev",
					"options" => $options,
				);
				$rv[$nm."_comment".$i] = array(
					"name" => $nm."[$i][comment]",
					"type" => "textarea",
					"caption" => "Pildi kommentaar",
				);
			};
		};
		//arr($rv);
		return $rv;
	}

	function callback_top_men($arr)
	{
		$retval = array();
		$retval["el1"] = array(
			"type" => "text",
			"value" => $this->render_page_view(array(
				"ft_page" => $arr["request"]["ft_page"],
				//"item_count" => 10, //dont know here. cb_get_top_list_items_for_page gets it.
				"get_data_obj" => $this,
				"get_data_method" => 'cb_get_top_list_items_for_page',
				"params" => array("gender" => 1),
			)),
			"no_caption" => 1,
		);
	    return $retval;
	}

	function callback_top_women($arr)
	{
		$retval = array();
		$retval["el1"] = array(
			"type" => "text",
			"value" => $this->render_page_view(array(
				"ft_page" => $arr["request"]["ft_page"],
				//"item_count" => 10, //dont know here. cb_get_top_list_items_for_page gets it.
				"get_data_obj" => $this,
				"get_data_method" => 'cb_get_top_list_items_for_page',
				"params" => array("gender" => 2),
			)),
			"no_caption" => 1,
		);
	    return $retval;
	}

	function cb_get_top_list_items_for_page($vars)
	{
		//$offset = $vars["offset"];
		//$length = $vars["length"];
		$gender = $vars["params"]["gender"];
		$page = $vars["page"];
		$items_in_page = $vars["items_in_page"];
		
		// find all men from commune persons folder:
		$person_list = new object_list(array(
			"class_id" => CL_CRM_PERSON,
			"parent" => $this->common["obj_inst"]->prop("persons_folder"),
			"gender" => $gender, //men:1 women:2
		));
		// find all their profiles:
		$prof_list = new object_list();
		for ($o = $person_list->begin(); !$person_list->end(); $o = $person_list->next())
		{
			$conns_to_profile = $o->connections_from(array(
				"type" => "RELTYPE_PROFILE",
			));
			foreach ($conns_to_profile as $profile_c) {
				$prof_list->add($profile_c->to());
			}
		}
		
		// find all their images:
		// and add image oid's with rating's to an array for sorting
		$ratings_arr = array();
		$rate = get_instance(CL_RATE);
		for ($o = $prof_list->begin(); !$prof_list->end(); $o = $prof_list->next())
		{
			$conns_to_img = $o->connections_from(array(
				"type" => "RELTYPE_IMAGE",
			));
			foreach ($conns_to_img as $img_c) {
				$oid = $img_c->prop("to");
				$ratings_arr[$oid] = $rate->get_rating_for_object($oid);
			}
		}
		
		arsort($ratings_arr);
		$result["item_count"] = count($ratings_arr);
		
		$arr_oid_and_rating = array();
		foreach ($ratings_arr as $oid => $rating) {
			$arr_oid_and_rating[] = array("oid"=>$oid, "rating"=>$rating);
		}
		$limited_oid_and_rating = array_slice($arr_oid_and_rating, $page * $items_in_page, $items_in_page);

		
		foreach ($limited_oid_and_rating as $oid_and_rating)
		{
			$id = $oid_and_rating["oid"];
			$rating = $oid_and_rating["rating"];
			$image = obj($id);
			
			// image --------------------------------------------
			//if ($image_c = $friend_profile->get_first_conn_by_reltype("RELTYPE_IMAGE"))
			
			$img_i = get_instance(CL_IMAGE);
			$img_url = $img_i->get_url_by_id($id);
			
			//pean otsima vastupidise seose img->profile->person
			$conn_prof_to_img = reset($image->connections_to(array("type"=>12)));
			$profile = $conn_prof_to_img->from();
			
			// name & profile -----------------------------------
			$prof_i = get_instance(CL_PROFILE);
			if ($person = $prof_i->get_person_for_profile($profile))
			{
				$name = $person->prop("firstname")." ".$person->prop("lastname"); //$name = $person->prop("name");
			}
			else
			{
				$name = "(isikut pole)";
			}

			// props --------------------------------------------
			$prof_view_url = $this->mk_comm_orb(array(
				"profile" => $profile->id(),
				"group" => "friend_details",
			));
			$name_link = $this->render_link($name, $prof_view_url);

			$created = $this->time2date($image->created(), 2);

			$result["items"][] = array(
				"img_url" => $img_url,
				"img_link" => $prof_view_url,
				"name_link" => $name_link,
				"prop1" => $person->prop("gender") == 2 ? "Naine" : "Mees",
				"prop2" => "hinne: ".$rating,
				"prop3" => $this->render_online($prof_i->is_online(array("obj_inst" => $profile))),
				"prop4" => "lisatud: ".$created, 
			);
			
		}	
		return $result;
	}
	
	function get_img_list($arr)
	{
		$prof_list = new object_list(array(
			"class_id" => CL_PROFILE,
			"parent" => $arr["obj_inst"]->prop("profiles_folder"),
			//"parent" => $this->common["obj_inst"]->prop("profiles_folder"),
		));
		
		//$connection_list = $prof_list->connections_from(array("type" => "RELTYPE_IMAGE"));
		//$img_list = $connection_list->to();
		//kahjuks ei saa nii teha, aga võiks saada.. ideid tulevastele p6lvedele
		
		$prof_ids = $prof_list->ids();
		$c = new connection();
		$image_rels = $c->find(array("from" => $prof_ids, "type" => 12)); // => RELTYPE_IMAGE));
		$images = array();
		foreach($image_rels as $rel)
		{
			$images[$rel["to"]] = obj($rel["to"]);
		}
		$img_list = object_list::from_arr($images);

		return $img_list;
	}

	function callback_last_added($arr)
	{
		$img_list = $this->get_img_list($arr);
		$img_list->sort_by(array(
			"prop" => "created",
			"order" => "desc",
		));
		
		$retval = array();
		$retval["el1"] = array(
			"type" => "text",
			"value" => $this->render_page_view(array(
				"ft_page" => $arr["request"]["ft_page"],
				"item_count" => $img_list->count(),
				"get_data_obj" => $this,
				"get_data_method" => 'cb_get_last_added_items_for_page',
				"params" => array("object_list_for_page_view" => $img_list),
			)),
			"no_caption" => 1,
		);
	    return $retval;
	}

	function callback_rate($arr)
	{
		//aw_session_del("last_rated_oid");
		$retval = array();
		$retval["rateform"] = array(
			"type" => "form",
			"name" => "rateform",
			"caption" => "Pildi hindamine",
			"sclass" => "applications/commune/image_rate",
			"sform" => "rate",
		);
	
		$last_rated_oid = aw_global_get("last_rated_oid");
		if(!empty($last_rated_oid))
		{
			$this->read_template("last_rated.tpl");
			$i = get_instance(CL_IMAGE);
			$imgdata = $i->get_image_by_id($last_rated_oid);
			$img_o = obj($last_rated_oid);
			$prof = reset($img_o->connections_to(array(
				"type" => 12,
				"from.class_id" => CL_PROFILE,
			)));
			$profile = $prof->from();
			$person = $this->get_person_for_profile($profile);
			$name = $person->prop("firstname")." ".$person->prop("lastname");
			$prof_view_url = $this->mk_comm_orb(array(
				"profile" => $profile->id(),
				//"commact" => "profile",
				"group" => "friend_details",
			));
			$rt = get_instance(CL_RATE);
			$last_rated_mark = aw_global_get("last_rated_mark");
			$last_rated_comment = aw_global_get("last_rated_comment");
			//echo $last_rated_mark;
			$rows = 3;
			$type = "";
			if(!empty($last_rated_mark))
			{
				$this->vars(array(
					"mark" => $last_rated_mark,
				));
				$type = $this->parse("rated");
				$rows++;
			}
			if(!empty($last_rated_comment))
			{
				$this->vars(array(
					"comment" => $last_rated_comment,
				));
				$rows++;
				$type .= $this->parse("commented");
			}
			if(empty($last_rated_mark) && empty($last_rated_comment))
			{
				$type = $this->parse("void");
				$rows = 4;
			}
			$prop = array(
				"image" => html::img(array(
					"url" => $imgdata["url"],
				)),
				"rows" => $rows,
				"name" => $this->render_link($name, $prof_view_url),
				"title" => $img_o->comment(),
				"rating" => $rt->get_rating_for_object($last_rated_oid),
				"type" => $type,
			);
			$this->vars($prop);
			$retval["lastrate"] = array(
				"type" => "text",
				"no_caption" => 1,
				"value" => $this->parse(),
			);
		}
		//sclass=applications/commune/image_rate sform=rate
	    return $retval;
	}

	// praegu ei kasutata, kuna hindamine on formis. vbl tulevikus vaja hindamine formist välja võtta.
	function callback_rate2($arr)
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

	// returns the object of active user
	function get_user()
	{
		//kui kasutaja ei ole sisse loginud, siis aw_global_get("uid") ei ole määratud.
		if($oid = aw_global_get("uid_oid"))
		{
			return obj($oid);
		}
		return false;
	}
	
	function get_user_for_person($pers_o)
	{
		//arr($pers_o->properties());
		/*
		$cons = $pers_o->connections_from(array(
			//"class" => CL_USER,
		));
		arr($cons);
		*/
		if ($user = obj($pers_o->createdby()))
		{
			return $user;
		}
		return FALSE;
	}
	
	function get_person_for_profile($profile_o)
	{
		if($person = $profile_o->get_first_obj_by_reltype("RELTYPE_PERSON"))
		{
			return $person;
		}
		return FALSE;
	}
	
	function get_user_for_profile($profile_o)
	{
		if($person = $profile_o->get_first_obj_by_reltype("RELTYPE_PERSON"))
		{
			if ($user = $this->get_user_for_person($person))
			{
				return $user;
			}
		}
		return FALSE;
	}
	
	// returns person object of current user. Creates new, if doesn't exist jet.
	function get_person()
	{
		//echo dbg::process_backtrace(debug_backtrace());
		if (!$user = $this->get_user())
		{
			return FALSE;
		}
		// check whether a person object exists for her
		if (!$person = $user->get_first_obj_by_reltype("RELTYPE_PERSON"))
		{
			// create new person
			$person = new object();
			$person->set_class_id(CL_CRM_PERSON);
			$person->set_parent($this->common["obj_inst"]->prop("persons_folder"));
			$person->set_status(STAT_ACTIVE);
			$person->save();
			$user->connect(array(
				"to" => $person->id(),
				"reltype" => "RELTYPE_PERSON", //2,
			));
		}
		return $person;
		
		//seda saab teha ka nii: viga ei pea kontrollima ka, see teeb isiku 2ra kui pole sellist. Kristo.
		//problem on ainult selles, kuhu ta selle isiku teeb - nimelt samasse kataloogi, kus user.
		//$us = get_instance(CL_USER);
		//$person = obj($us->get_current_person());
		//return $person;
	}
	
	function get_contact_list()
	{
		if($user = $this->get_user())
		{
			/*$list = reset(new object_list(array(
				
			)));*/
			$contact_list = $user->connections_to(array(
				"type" => 1, //RELTYPE_LIST_OWNER
				"from.class_id" => 811,
			));
			foreach($contact_list as $tlist)
			{
				//arr($tlist);
				$list = $tlist->from();
			}
			if(is_object($list))
			{
				return $list;
			}
			else
			{
				// create new person
				$list = new object();
				$list->set_class_id(CL_CONTACT_LIST);
				$list->set_parent($this->common["obj_inst"]->prop("persons_folder"));
				$list->set_status(STAT_ACTIVE);
				$list->save();
				$list->connect(array(
					"to" => $user->id(),
					"reltype" => "RELTYPE_OWNER", //2,
				));
				return $list;
			}
		}
		return false;
	}
	
	function get_active_profile()
	{
		if ($person = $this->get_person())
		{
			$active_profile_id = $person->meta("active_profile");
			//[duke] if ($this->can("view",$oid)) { $o = new object($oid) } else { print "seda ei saa laadida"; };
			if (is_oid($active_profile_id) and $this->can("view", $active_profile_id))
			{
				return obj($active_profile_id);
			}
			// active not set. make one active and give it.
			if ($profile = $person->get_first_obj_by_reltype("RELTYPE_PROFILE"))
			{
				$person->set_meta("active_profile", $profile->id());
				$person->save();
				return $profile;
			}
			else //-> pole yhtegi profiili sellel persoonil
			{
				//vbl peaks analoogselt user::get_person_for_user()-iga looma uue, kui ühtegi veel pole?
				//esimene profiil peaks olema loodud liitumisel, kui kasutaja suvatseb väljad täita,
				//kui ta seda ei tee, jääb profiil salvestamata.. praeguse korralduse järgi..
				//Nii et loome uue, kui vaja on.
				
				$profiles_folder_id = $this->common["obj_inst"]->prop("profiles_folder");
				if (is_oid($profiles_folder_id))
				{
					$profile = $this->make_profile($profiles_folder_id);
					$person->set_meta("active_profile", $profile->id());
					$person->save();
					return $profile;
				}
			}
			
		}
		return false;
	}
	
	function callback_profile_change($arr)
	{
		$prop = $arr["prop"];
		
		$prsn_cls_i = get_instance(CL_CRM_PERSON);
		$prsn_cls_i->init_class_base();
		$props = $prsn_cls_i->get_property_group(array(
			"group" => "general",
		));
		//arr($props);
		$person_o = $this->get_person();
		//arr($props);
		//echo $person_o->id();
		$profile_o = $this->common["my_profile"] == true ? $this->common["my_profile"] : $this->get_active_profile();
		//nüüd kopeeritakse sama struktuuriga uude array-sse ainult need propid, mida tahan
		$wanted_props = array();
		foreach ($this->fields_from_person as $wanted_field)
		{
			if (isset($props[$wanted_field]))
			{
				$wanted_props[$wanted_field] = $props[$wanted_field];
			}
		}
		//propid saavad väärtuse $person_o-st - iga prop saab lisa elemente
		$ret_props_person = $prsn_cls_i->parse_properties(array(
			"properties" => $wanted_props,
			"obj_inst" => $person_o,
		));
		foreach ($ret_props_person as $key => $prop)
		{
			$ret_props_person[$key]["name"] = "persondata[".$prop["name"]."]";
		}
		//nüüd sama PROFIILI propidega
		
		$prof_cls_i = get_instance(CL_PROFILE);
		$prof_cls_i->init_class_base();
		$props = $prof_cls_i->get_all_properties();
		//arr($props);
		//nüüd kopeeritakse sama struktuuriga uude array-sse ainult need propid, mida tahan
		$wanted_props = array();
		foreach ($this->change_fields_from_profile as $wanted_field)
		{
			if (isset($props[$wanted_field]))
			{
				$wanted_props[$wanted_field] = $props[$wanted_field];
			};
		};
		//propid saavad väärtuse $profile_o-st - iga prop lisa elemente
		$ret_props_profile = $prof_cls_i->parse_properties(array(
			"properties" => $wanted_props,
			"obj_inst" => $profile_o,
		));
		foreach ($ret_props_profile as $key => $prop)
		{
			$ret_props_profile[$key]["name"] = "profdata[".$prop["name"]."]";
			if($key == "user_field1")
			{
				$v = $prop["value"];
				$m = $v{4}.$v{5};
				$d = $v{6}.$v{7};
				$y = $v{0}.$v{1}.$v{2}.$v{3};
				$var = mktime(0,0,0,$m,$d,$y);
				$ret_props_profile["user_field1"]["value"] = ($var == -1) ? "" : $var;
			}
		}
		$ret_toolbar = array();
		$ret_toolbar["profile_change_toolbar"] = array(
			"name" => "profile_change_toolbar",
			"group" => "profile_change",
			"type" => "toolbar",
			"no_caption" => 1,
			"caption" => "ToolBar",
		);
		//arr($ret_props_profile);
		$rv = $ret_toolbar + $ret_props_person + $ret_props_profile;
		return $rv;
	}

	function callback_get_locations($arr)
	{
		$conns =&$arr["obj_inst"]->connections_from(array(
			"type" => RELTYPE_CONTENT,
		));

		$old = &$arr["obj_inst"]->meta("location");

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
	
	function callback_friend_details($arr)
	{
		if (!$profile = $this->common["profile"])
		{
			return PROP_FATAL_ERROR;
		}
		$my_user = $this->get_user();
		$my_profile = $this->common["my_profile"] == true ? $this->common["my_profile"] : $this->get_active_profile(); 
		$person = $profile->get_first_obj_by_reltype("RELTYPE_PERSON");
		$rval["view"] = array(
			"no_caption" => 1,
			"type" => "text",
			"name" => "view",
			"value" => $this->render_profile_view($profile),
		);
		$params = array(
			"group" => "friends",
		);
		$cons = $my_profile->connections_from(array(
			"type" => "RELTYPE_FRIEND_GROUPS",
		));
		$selected = -1;
		/*
		if(is_array($cons))
		{
			foreach($cons as $con)
			{
				if(in_array($profile->id(),$con->meta("users")))
				{
					$selected = $con->prop("to");
					break;
				}
			}
		}
		*/
		$cl = get_instance(CL_CLASSIFICATOR);
		$opts = $cl->get_options_for(array(
			"name" => "friend_groups", 
			"clid" => CL_PROFILE,
		));
		$sopts = array(-1 => "-- vali --", 0 => "üldine");
		$sopts = $sopts + $opts;
		$rval["friendgroups"] = array(
			"caption" => "Pane sõbragruppi",
			"type" => "text",
			"name" => "friendgroups",
			"value" => html::select(array(
				"name" => "selgroup",
				"options" => $sopts,
				"selected" => $selected,
				"onchange" => "window.location = '".
				$this->mk_comm_orb(array(
					"profile" => $profile->id(),
					"my_profile" => $my_profile->id(),
					"group" => $arr["request"]["group"],
				), 0, "addtogroup")
				."&f_group=' + this.options[this.selectedIndex].value;"
			)),
		);
		if($my_profile->is_connected_to(array(
			"to" => $profile->id(),
			"type" => "RELTYPE_FRIEND",
		)))
		{
			$params["sel[".$profile->id()."]"] = $profile->id();
			$rval["removefriend"] = array(
				"type" => "text",
				"name" => "removefriend",
				"value" => html::href(array(
					"url" => $this->mk_comm_orb($params, 0, "remove_friend"),
					"caption" => "Eemalda sõprade hulgast",
				)),
			);
		}
		else
		{
			$afparams = $params;
			$afparams["commact"] = "add_friend";
			$afparams["profile"] = $profile->id();
			$rval["addfriend"] = array(
				"type" => "text",
				"name" => "addfriend",
				"value" => html::href(array(
					"url" => $this->mk_comm_orb($afparams, 0, "commaction"),
					"caption" => "lisa sõprade hulka",
				)),
			);
			$user = $this->get_user_for_profile($profile);
			if($my_user->is_connected_to(array(
				"to" => $user->id(),
				"type" => "RELTYPE_IGNORED",
			)))
			{
				$riparams = $params;
				$riparams["sel[".$user->id()."]"] = $user->id();
				$rval["removeignored"] = array(
					"type" => "text",
					"name" => "removeignored",
					"value" => html::href(array(
						"url" => $this->mk_comm_orb($riparams, 0, "remove_ignored"),
						"caption" => "Eemalda ignoreeritute hulgast",
					)),
				);
			}
			else
			{
				$aiparams = $params;
				$aiparams["cuser"] = $user->id();
				$aiparams["commact"] = "add_ignored";
				$rval["addignored"] = array(
					"type" => "text",
					"name" => "addignored",
					"value" => html::href(array(
						"url" => $this->mk_comm_orb($aiparams, 0, "commaction"),
						"caption" => "lisa ignoreeritute hulka",
					)),
				);
			}
			if($my_user->is_connected_to(array(
				"to" => $user->id(),
				"type" => "RELTYPE_BLOCKED",
			)))
			{
				$params["sel[".$user->id()."]"] = $user->id();
				$rval["removeblocked"] = array(
					"type" => "text",
					"name" => "removeblocked",
					"value" => html::href(array(
						"url" => $this->mk_comm_orb($params, 0, "remove_blocked"),
						"caption" => "Eemalda blokeeritute hulgast",
					)),
				);
			}
			else
			{
				$params["cuser"] = $user->id();
				$params["commact"] = "add_blocked";
				$rval["addblocked"] = array(
					"type" => "text",
					"name" => "addblocked",
					"value" => html::href(array(
						"url" => $this->mk_comm_orb($params, 0, "commaction"),
						"caption" => "lisa blokeeritute hulka",
					)),
				);
			}
		}
		/*
		$rval["comments"] = array(
			"no_caption" => 1,
			"type" => "comments",
			"name" => "profile_comments",
			"heading" => $person->prop("firstname")." ".$person->prop("lastname"),
			"use_parent" => $profile->id(),
		);
		$rval["submit"] = array(
			"caption" => "Kommenteeri!",
			"type" => "submit",
			"name" => "kommenteeri",
		);
		*/
		return $rval;
	}
	
	function render_friends_panel($profile, $ft_page)
	{
			switch ($this->common["commact"])
			{
				case "profile":
					return $this->render_profile_view($profile);
					break;
				case "pictures":
					
					break;
				case "communities":
					
					break;
				case "friends":
				default:
					return $this->render_friends_page($profile, $ft_page);
			}
	}
	
	function render_friends_page($profile, $ft_page)
	{
		return $this->render_page_view(array(
			"ft_page" => $ft_page,
			"item_count" => count($profile->connections_from(array("type" => "RELTYPE_FRIEND"))),
			"get_data_obj" => $this,
			"get_data_method" => 'cb_get_friend_items_for_page',
			"params" => array("profile" => $profile),
		));
	}
	
	function update_locations($arr)
	{
		$arr["obj_inst"]->set_meta("location", $arr["request"]["location"]);
		// now I have got saving working properly .. I only need to add those elements to the classbase
		// generated form. How?
	}
	
	function add_rate($arr)
	{
		aw_session_del("last_rated_mark");
		aw_session_del("last_rated_comment");
		$mark = "";
		$comment = "";
		if (!empty($arr["request"]["rateform"]["rate"]))
		{
			// XXX: check whether this is a valid image to be voted for
			$rt = get_instance(CL_RATE);
			$rt->add_rate(array(
 				"oid" => $arr["request"]["rateform"]["img_id"],
				"rate" => $arr["request"]["rateform"]["rate"],
				"no_redir" => 1,
			));

			//from core. gets param oid - that object gets one hit plus:
			$this->add_hit($arr["request"]["rateform"]["img_id"]);
			$mark = $arr["request"]["rateform"]["rate"];
			//echo $mark;
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
			$comment = $arr["request"]["rateform"]["comments"]["comment"];
			//echo $comment;
		};
		aw_session_set("last_rated_oid", $arr["request"]["rateform"]["img_id"]);
		aw_session_set("last_rated_mark", $mark);
		aw_session_set("last_rated_comment", $comment);
	}

	function update_my_images($arr)
	{
		//arr($arr);
		if(!$person = $this->get_person())
		{
			return $rv;
		}
		if($this->check_rights($arr["request"]["my_profile"]))
		{
			$profile_obj = obj($arr["request"]["my_profile"]);
		/*
		$xxx = $this->mk_my_orb("change", array(
			"id" => $arr["obj_inst"]->id(),
			"group" => "general",
			"group" => "my_images",
// 			"profile" => $arr["request"]["profile"],
		), CL_COMMUNE);
		*/
		}
		else if (!$profile_obj = $this->get_active_profile())
		{
			return $rv;
		}
		//arr($profile_obj);
		$to_replace = $_FILES["myimage"]["tmp_name"];
		$to_delete = $arr["request"]["delete"];
		$to_add = $_FILES["newimage"]["tmp_name"];
		$to_olink = $arr["request"]["replace"];
		$to_nlink = $arr["request"]["nlink"];
		$t = get_instance(CL_IMAGE);
		//arr($to_olink);
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
					$file = array(
							"name" => $_FILES["newimage"]["name"][$key]["file"],
							"contents" => base64_encode(file_get_contents($tn)),
							"type" => $_FILES["newimage"]["type"][$key]["file"],
					);
					// only add an image if a file is present
					$argblock = array(
						"file" => $file,
						"file2" => $file,
						"comment" => $arr["request"]["newimage"][$key]["comment"],
						"parent" => $arr["obj_inst"]->prop("pic_folder"),
						"return" => "id",
					);
					
					// need on uued pildid
					$img_id = $t->submit($argblock);
					$profile_obj->connect(array(
						"to" => $img_id,
						"reltype" => "RELTYPE_IMAGE", // RELTYPE_IMAGE
					));
					$prof_id = $profile_obj->id();
					$q = "INSERT INTO profile2image VALUES ($prof_id,$img_id)";
					$this->db_query($q);
				}
			}
		}

		$images = $profile_obj->connections_from(array(
			"type" => "RELTYPE_IMAGE" //12,
		));
		//arr($images);
		// asendab olemasoleva pildi ühes profiilis olemasoleva pildiga teisest profiilist
		if(is_array($to_olink))
		{
			foreach($to_olink as $key => $value)
			{
				if(!empty($value))
				{
					// kui juba kuskil korra ühendati need omavahel
					if(!array_key_exists($value, $images) and is_oid($value))
					{
						$img_obj = obj($key);
						$num = count($img_obj->connections_to(array(
							//"type" => RELTYPE_PROFILE, // millegipärast ei tunne pildi ja profiili vahel seosetüüpi ära... aga iseenest võib praegu nii olla, pärast vajaks ülevaatamist
						)));
						if($num == 0)
						{
							$img_obj->delete();
						}
						else
						{
							$profile_obj->disconnect(array(
								"from" => $key,
							));
						}
						$profile_obj->connect(array(
							"reltype" => "RELTYPE_IMAGE",
							"to" => $value,
						));
					}
				}
			}
		}
		// lisab pildi ühest profiilist teise profiili
		if(is_array($to_nlink))
		{
			foreach($to_nlink as $key => $value)
			{
				if(!empty($value))
				{
					if(!array_key_exists($value, $images) and is_oid($value))
					{
						$profile_obj->connect(array(
							"reltype" => "RELTYPE_IMAGE",
							"to" => $value,
						));
					}
				}
			}
		}
		if (is_array($to_replace))
		{
			foreach($to_replace as $key => $tmp_name)
			{
				$tn = $tmp_name["file"];
				$argblock = array(
					"comment" => $arr["request"]["myimage"][$key]["comment"],
					"return" => "id",
				);
				if ($to_delete[$key])
				{
					$img_obj = obj($key);
					$num = count($img_obj->connections_to(array(
						//"type" => RELTYPE_PROFILE, // millegipärast ei tunne pildi ja profiili vahel seosetüüpi ära... aga iseenest võib praegu nii olla, pärast vajaks ülevaatamist
					)));
					if($num == 0)
					{
						$img_obj->delete();
					}
					else
					{
						$profile_obj->disconnect(array(
							"reltype" => "RELTYPE_IMAGE",
							"from" => $key,
						));
					}
				}
				
				elseif (is_uploaded_file($tn))
				{
					$img_obj = obj($key);
					$num = count($img_obj->connections_to(array(
						//"type" => RELTYPE_PROFILE, // millegipärast ei tunne pildi ja profiili vahel seosetüüpi ära... aga iseenest võib praegu nii olla, pärast vajaks ülevaatamist
					)));
					if($num == 0)
					{
						$img_obj->delete();
					}
					else
					{
						$profile_obj->disconnect(array(
							"reltype" => "RELTYPE_IMAGE",
							"from" => $key,
						));
					}
					$argblock["file"] = array(
							"name" => $_FILES["myimage"]["name"][$key]["file"],
							"contents" => base64_encode(file_get_contents($tn)),
							"type" => $_FILES["myimage"]["type"][$key]["file"],
						);
					$argblock["parent"] = $profile_obj->id();
						// need on uued pildid
					
					$img_id = $t->submit($argblock);
					
					$profile_obj->connect(array(
						"to" => $img_id,
						"reltype" => "RELTYPE_IMAGE", // RELTYPE_IMAGE
					));
					$prof_id = $profile_obj->id();
					
					$q = "INSERT INTO profile2image VALUES ($prof_id,$img_id)";
					$this->db_query($q);
					}
				else
				{
					$argblock["id"] = $key;
					$img_id = $t->submit($argblock);
				}
			}
		}
	}

	/**  
		
		@attrib name=new_profile all_args="1"
		@param id required type=int acl=view
		@param group optional
		@param return_url optional
	
	**/
	function new_profile($arr)
	{
		$commune_o = new object($arr["id"]);
		$profiles_folder_id = $commune_o->prop("profiles_folder");
		
		if (is_oid($profiles_folder_id))
		{
			$person = $this->get_person();
			$profile = $this->make_profile($profiles_folder_id);
			//$person->set_meta("active_profile", $profile->id());
			$person->save();
		}
		return $this->mk_comm_orb(array("group" => $arr["group"]), $arr["id"]);
		//return $this->mk_comm_orb(array("group" => $arr["group"]));
	}
	
	function make_profile($folder_id)
	{
		$profile = new object();
		$profile->set_parent($folder_id);
		$profile->set_class_id(CL_PROFILE);

		if (!$person = $this->get_person())
		{
			//vbl on kusagil vaja saada lihtsalt mingi sidumata profiili obj..
			$profile->save();
			return $profile;
		}
		
		$nr = count($person->connections_from(array("type" => "RELTYPE_PROFILE"))) + 1;
		$profile->set_name($person->name().' profiil '.$nr);
		$prof_id = $profile->save();
		
		$person->connect(array(
			"to" => $prof_id,
			"reltype" => "RELTYPE_PROFILE", //14,
		));
		$profile->connect(array(
			"to" => $person->id(),
			"reltype" => "RELTYPE_PERSON", //9,
		));
		
		return $profile;
	}

	function update_profile($arr)
	{
		$person = $this->get_person();
		//arr($arr);
		// save props for person:
		$person_i = $person->instance();
		$person_props = $arr["request"]["persondata"];
		$person_props["return"] = "id";
		$person_props["id"] = $person->id();
		$person_id = $person_i->submit($person_props);

		// save props for profile:
		$new = false;
		$profile_props = $arr["request"]["profdata"];
		//arr($profile_props);
		//arr($arr);
		//arr($profile_props);
		$usr = &$profile_props["user_field1"];
		foreach($usr as $key => $value)
		{
			if($value < 10)
			{
				$usr[$key] = "0".$value;
			}
		}
 		$profile_props["user_field1"] = $usr["year"].$usr["month"].$usr["day"];
		//$groups = array("yldandmed", "valimus", "harrastused", "harjumused", "kool_too");
		$profile_props["return"] = "id";
		
		if($this->common["my_profile"])
		{
			$profile = $this->common["my_profile"];
			$profile_props["id"] = $profile->id();
		}
		elseif($profile = $this->get_active_profile())
		{
			$profile_props["id"] = $profile->id();
		}
		else
		{
			if ($profile = $person->get_first_obj_by_reltype("RELTYPE_PROFILE"))
			{
				$profile_props["id"] = $profile->id();
			}
			else 
			{
				$profile_props["parent"] = &$arr["obj_inst"]->prop("profiles_folder"); //$user->parent();
				$profile_props["status"] = STAT_ACTIVE;
				$new = true;
			}
		}
		$profile_i = get_instance(CL_PROFILE);
		//arr($profile_props);
		$profile->set_meta("occupation", $profile_props["occupation"]);
		/*
		// there has GOT TO BE BETTER WAY then THIS :| -- ahz
		foreach($groups as $group)
		{
			
		}
		*/
		//$profile_props["group"] = "settings";
		$profile_props["cb_existing_props_only"] = 1;
		$profile_id = $profile_i->submit($profile_props);

		if ($new)
		{
			$person->connect(array(
				"to" => $profile_id,
				"reltype" => "RELTYPE_PROFILE", //14,
			));
			$profile = obj($profile_id);
			$profile->connect(array(
				"to" => $person->id(),
				"reltype" => "RELTYPE_PERSON", //9,
			));

		}
		$profile->save();
		
		//arr($profile->properties());
	}

	function get_content_elements($arr)
	{
		$obj_inst = &$arr["obj_inst"];
		$rv = array();

		//lets check that object can be loaded
		//used to throw error on creating new commune: object::connections_from(): no current object loaded!
		if (!is_oid($obj_inst->id()))
		{
			return $rv;
		}
		
		$els = $obj_inst->connections_from(array(
			"type" => RELTYPE_CONTENT,
		));
		$locations = $obj_inst->meta("location");
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

	function callback_join_form($arr)
	{
		aw_global_set("no_cache", 1);
		$j_oid = &$arr["obj_inst"]->prop("join_obj");
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
	
	function callback_inbox($arr)
	{
		$rval = array(
			"inbox_toolbar" => array(
				"name" => "inbox_toolbar",
				"type" => "toolbar",
				"caption" => "Inboxi toolbar",
				"no_caption" => 1,
			),
			"inbox" => array(
				"name" => "inbox",
				"type" => "table",
				"caption" => "Sissetulnud kirjad",
				"no_caption" => 1,
			),
		);
		return $rval;
	}
	
	function callback_outbox($arr)
	{
		$rval = array(
			"outbox_toolbar" => array(
				"name" => "outbox_toolbar",
				"type" => "toolbar",
				"caption" => "Outboxi toolbar",
				"no_caption" => 1,
			),
			"outbox" => array(
				"name" => "outbox",
				"type" => "table",
				"caption" => "Väljasaadetud kirjad",
				"no_caption" => 1,
			),
		);
		return $rval;
	}
	
	function callback_archive($arr)
	{
		$rval = array(
			"archive_toolbar" => array(
				"name" => "archive_toolbar",
				"type" => "toolbar",
				"caption" => "Arhiivi toolbar",
				"no_caption" => 1,
			),
			"archive" => array(
				"name" => "archive",
				"type" => "table",
				"caption" => "Arhiveeritud kirjad",
				"no_caption" => 1,
			),
		);
		return $rval;
	}
	
	// so why the should i rewrite the code here? i better take it from the messagebox class...
	// and add a little flavor -- ahz
	function callback_newmessage($arr)
	{
		$box = get_instance(CL_QUICKMESSAGEBOX);
		$box->get_message_box_for_user($this->get_user());
		$abox = array(
			"group" => array(
			"name" => "group",
			"type" => "textbox",
			"caption" => "Grupp",
		));
		$bbox = $box->callback_new_message($arr);
		$rval = $abox + $bbox;
		
		return $rval;
	}
	
	function create_message($arr)
	{
		$box = get_instance(CL_QUICKMESSAGEBOX);
		$box_o = $box->get_message_box_for_user($this->get_user());
		$vars = array(
			"obj_inst" => $box_o,
			"request" => $arr["request"],
		);
		$box->save_new_message($vars);
		/*
		$users = get_instance("users");
		$u_id = $users->get_oid_for_uid(aw_global_get("uid"));
		$t_id = $users->get_oid_for_uid($arr["user_to"]);
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
		$o->set_prop("subject",$arr["subject"]);
		$o->set_prop("content",$arr["content"]);
		$o->save();
		*/
	}
	
	function callback_profile_view($arr)
	{
		//$pers = $this->get_person();
		//echo $pers->id();
		$retval = array();
		// oh, yes you do!! -- ahz
		if (($profile = $this->common["my_profile"]) 
		|| ($profile = $this->common["profile"]) 
		|| ($profile = $this->get_active_profile())) 
		{
			$val = $this->render_profile_view($profile);
		}
		$retval["profile_change_toolbar"] = array(
			"name" => "profile_change_toolbar",
			"type" => "toolbar",
			"no_caption" => 1,
		);
		$retval["el1"] = array(
			"type" => "text",
			"value" => $val,
			"no_caption" => 1,
		);
		/*
		if (!$profile = $this->common["profile"]) //momendil ei ole seda vaja, sest ma ei kavatse suunata siia võõraste profiilide vaatamist
		{
			if ($profile = $this->get_active_profile())
			{
				//arr($profile);
				$retval["el1"] = array(
					"type" => "text",
					"value" => $this->render_profile_view($profile),
					"no_caption" => 1,
				);
			}
		}
		*/
	    return $retval;
	}
	
	function render_profile_view($profile)
	{
		$rendered_value = "";
		//show header
		$rendered_value .= $this->render_profile_header(array(
			"arr" => $arr,
			"profile" => $profile,
		));
		//show person props and profile props
		$props = $this->get_profile_props_for_view(array(
			"arr" => $arr,
			"profile" => $profile,
		));
		//arr($props);
		$rendered_value .= $this->render_property_view(array(
			"items" => $props,
		));
		return $rendered_value;
	}
	
	
	function render_property_view($vars)
	{
		$items = $vars["items"]; // tuleb get_profile_props_for_view()-st
		//arr($items);
		//neid ei anna parameetritega sisse, pole nagu mõtet praegu.
		//$template = $vars["template"];
		//$container_sub = $vars["container_sub"];
		//$item_sub = $vars["item_sub"];
		$template = "show_profile.tpl";
		$container_sub = "property_list";
		$item_sub = "property_item";
		
		//aga kui on erinevates tpl failides? 
		//Ei ole..
		//$container_template = $vars["container_template"];
		//$item_template = $vars["item_template"];
		
		$this->read_template($template);

		$row_counter = 0;
		$rendered_str = "";
		foreach($items as $pn => $pd)
		{
			$row_counter++;
			$this->vars(array(
				"prop_caption" => $pd["caption"].":",
				"prop_value" => $pd["value"],
				"evenodd" => $row_counter % 2 ? "odd" : "even",
			));
			$rendered_str .= $this->parse($item_sub);
		}
		$this->vars(array(
			$item_sub => $rendered_str,
		));
		
		if ($container_sub)
		{
			return $this->parse($container_sub);
		}
		else
		{
			return $this->parse();
		}
	}
	
	function get_profile_props_for_view($vars)
	{
		$arr = $vars["arr"];
		$profile = $vars["profile"];
		
		$person_props = $this->get_parsed_viewable_props_for(array(
			"obj" => $profile->get_first_obj_by_reltype("RELTYPE_PERSON"),
			"fields" => $this->fields_from_person,
		));
		$profile_props = $this->get_parsed_viewable_props_for(array(
			"obj" => $profile,
			"fields" => $this->show_fields_from_profile,
		));
		//siin peaks key-d vbl märgistama prefiksitega "person." ja "profile.", et samu ei oleks
		$user = $this->get_user_for_profile($profile);
		$item["username"] = array(
			"caption" => "Kasutajanimi",
			"value" => $user->name(),
		);
		return $item + $person_props + $profile_props;
	}
	
	
	// Annab väärtustatud propertid
	// siin võiks olla optional, array of fields, mida tahan
	// väljastab array, mille elemendiks: field_name => array( 'caption'=>'blaa', 'value'=>'muu');
	function get_parsed_viewable_props_for($vars)
	{
		$obj = $vars["obj"];
		//arr($obj->properties());
		//$occupation = $obj->meta("occupation");
		$wanted_fields = $vars["fields"];
			//arr($obj->class_id());
			list($all_props, $tableinfo, $relinfo) = $GLOBALS["object_loader"]->load_properties(array(
				"clid" => $obj->class_id(),
			));
			//arr($all_props);
			//filtreerime välja need propid, mida vaja on,
			$props = array();
			//echo $obj->id();
			//arr($wanted_fields);
			//arr($all_props);
			if (count($wanted_fields))
			{
				foreach ($wanted_fields as $field_name)
				{
					$props[$field_name] = $all_props[$field_name];
				}
			}
			else //.. kui me just kõiki ei taha:
			{
				$props =& $all_props;
			}
			//arr($props);
			//$o_inst = $obj->instance(); //igatahes ei anna ta chooseri puhul option'eid..
			/* n: vaja oleks sellist:
			[gender] => Array
			(
				[name] => gender
				[table] => kliendibaas_isik
				[group] => general2
				[type] => chooser
				[field] => gender
				[caption] => Sugu
				[value] => 1
				[options] => Array
				(
					[1] => mees
					[2] => naine
				)
				[_parsed] => 1
				[orig_type] => chooser
			)
			*/
			//see aga annab optionid:
			//eelmise asemel äkki säästab ühest päringust?:
			$o_inst = get_instance($obj->class_id());
			$o_inst->init_class_base();
			//arr($obj->class_id());
			//propid saavad väärtuse $obj-st - array struktuur jääb, aga täieneb, asendub
			$parsed_props = $o_inst->parse_properties(array(
				"properties" => $props,
				"obj_inst" => $obj,
			));
			//arr($parsed_props);
			$result_props = array();
			foreach($props as $pn => $pd)
			{
				$ppd = $parsed_props[$pn];
				$v = $obj->prop($pn);

				if ($pd["type"] == "classificator" && $pd["store"] == "connect")
				{
					// get the first connection of that type
					$c = reset($obj->connections_from(array("type" => $pd["reltype"])));
					$v = $c->prop("to.name");
				}
				else
				if ($pd["type"] == "classificator") // && $v) //et kas propertil on ikki väärtus ($v)
				{
					if(is_oid($v))
					{
						$tmp = obj($v);
						$v = $tmp->name();
					}
					elseif($pn == "occupation")
					{
						if(is_array($v))
						{
							$num = count($v);
							foreach($ppd["options"] as $key => $value)
							{
								
								if(in_array($key,$v))
								{
									$num--;
									$val.=$value;
									if($num!=0)
									{
										$val.=", ";
									}
								}
							}
						}
						$v = $val;
					}
					else 
					{
						$v = "";
					}
				}
				else
				if($pd["type"] == "text")
				{
					if($pn == "age")
					{
						$tmp = $o_inst->get_age2($obj->prop("user_field1"));
						$v = $tmp != 0 ? $tmp : ""; 
						//echo $v;
					}
				}
				else 
				if ($pd["type"] == "checkbox")
				{
					$v = $v ? LC_YES : LC_NO;
				}
				else 
				if ($pd["type"] == "chooser")
				{
					if (count($ppd["options"]))
					{
						$v = $ppd["options"][$v];
					}
				}
				else
				if ($pd["type"] == "date_select")
				{
					if($pn = "user_field1")
					{
						$m = $v{4}.$v{5};
						$d = $v{6}.$v{7};
						$y = $v{0}.$v{1}.$v{2}.$v{3};
						$var = mktime(0,0,0,$m,$d,$y);
						//$v = $o_inst->make_birthday($pd);
						$v = ($var == -1) ? "" : get_lc_date($var);
					}
				}
				/*
				else
				if ($pn == "age")
				{
						$v = $o_inst->get_age(array("obj_inst" => $obj));
				}
				*/
				$value = $v;
				$result_props[$pn] = array("caption" => $ppd["caption"], "value" => $value);
			}
		//arr($result_props);
		return $result_props;
	}
	
	/**
		@attrib name=submit nologin=1
	**/
	function submit($arr)
	{
		return parent::submit($arr);
	}
		
	/**
		@attrib name=delete_profile all_args="1"
		@param id required type=int acl=view
		@param group optional
		@param sel required
		@param return_url optional
	**/
	function delete_profile($arr)
	{
		arr($arr);
		$r_url = $this->mk_my_orb(array("group" => $arr["group"]), $arr["id"]);
		if(!$person = $this->get_person())
		{
			return $r_url;
		}
		if(is_array($arr["sel"]))
		{
			$ol = new object_list();
			$ol->add($arr["selected"]);
			$ol->delete();
		}
		if ($profile = $person->get_first_obj_by_reltype("RELTYPE_PROFILE"))
		{
			$person->set_meta("active_profile", $profile->id());
			$person->save();
		}
		else //-> pole yhtegi profiili sellel persoonil
		{
			//vbl peaks analoogselt user::get_person_for_user()-iga looma uue, kui ühtegi veel pole?
			//esimene profiil peaks olema loodud liitumisel, kui kasutaja suvatseb väljad täita,
			//kui ta seda ei tee, jääb profiil salvestamata.. praeguse korralduse järgi..
			//Nii et loome uue, kui vaja on.
				
			$profiles_folder_id = $this->common["obj_inst"]->prop("profiles_folder");
			if (is_oid($profiles_folder_id))
			{
				$profile = $this->make_profile($profiles_folder_id);
				$person->set_meta("active_profile", $profile->id());
				$person->save();
			}
		}
		return $r_url;
	}
	
	/**
		@attrib name=remove_friend all_args="1"
		@param sel required
		@param group optional
		@param return_url optional
	**/
	function remove_friend($arr)
	{
	// type=int acl=view
		$profile = $this->common["my_profile"] ? $this->common["my_profile"] : $this->get_active_profile();
		$selected = $arr["sel"];
		foreach($selected as $sel)
		{
			$profile->disconnect(array(
				"from" => $sel,
			));
		}
		return $this->mk_comm_orb(array("group" => $arr["group"]), $arr["id"]);
	}
	
	/**
		@attrib name=remove_ignored all_args="1"
		@param sel required type=int acl=view
		@param group optional
		@param return_url optional
	**/
	function remove_ignored($arr)
	{
		$user = $this->get_user();
		foreach($arr["sel"] as $sel)
		{
			$user->disconnect(array(
				"from" => $sel,
				"reltype" => "RELTYPE_IGNORED",
			));
		}
		return $this->mk_comm_orb(array("group" => $arr["group"]),  $arr["id"]);
	}
	
	/**
		@attrib name=remove_blocked all_args="1"
		@param sel required type=int acl=view
		@param group optional
		@param return_url optional
	**/
	function remove_blocked($arr)
	{
		$user = $this->get_user();
		foreach($arr["sel"] as $sel)
		{
			$user->disconnect(array(
				"from" => $sel,
				"reltype" => "RELTYPE_BLOCKED",
			));
		}
		return $this->mk_comm_orb(array("group" => $arr["group"]),  $arr["id"]);
	}
	
	/**
		@attrib name=addtogroup all_args="1"
		@param profile required
		@param f_group required
		@param my_profile optional
		@group group optional
		@param return_url optional
	**/
	function addtogroup($arr)
	{
		//arr($arr);
		if(is_oid($arr["f_group"]))
		{
			$profile = $this->common["my_profile"] ? $this->common["my_profile"] : $this->get_active_profile();
			if(!$conn = $profile->connections_from(array(
				"to" => $arr["f_group"],
				"type" => "RELTYPE_FRIEND_GROUPS",
			)))
			{
				$profile->connect(array(
					"to" => $arr["f_group"],
					"reltype" => "RELTYPE_FRIEND_GROUPS",
				));
				$conn = $profile->connections_from(array(
					"to" => $arr["f_group"],
					"type" => "RELTYPE_FRIEND_GROUPS",
				));
			}
			foreach($conn as $conx)
			{
				$con = $conx;
			}
			$con_o = obj($con->prop("relobj_id"));
			
			$friends = $con_o->meta("friends");
			$friends[$arr["profile"]] = $arr["profile"];
			$con_o->set_meta("friends", $friends);
			$con_o->save();
			// siit jätkame homme -- ahz
			// asd, siiski veel mitte...
		}
		$params = array(
			"group" => $arr["group"],
			"profile" => $arr["profile"],
		);
		if(!$this->is_not_my_profile($arr["my_profile"]))
		{
			$params["my_profile"] = $arr["my_profile"];
		}
		return $this->mk_comm_orb($params, $arr["id"]);
	}
	
	/**
		@attrib name=hide_prof_com all_args="1"
		@param sel required type=int acl=view
		@param profile_id required
		@param group optional
		@param return_url optional
	**/
	function hide_prof_com($arr)
	{
		foreach($arr["sel"] as $sel)
		{
			$com_o = obj($sel);
			if($com_o->status() == STAT_NOTACTIVE)
			{
				$stat = STAT_ACTIVE;
			}
			else
			{
				$stat = STAT_NOTACTIVE;
			}
			$com_o->set_status($stat);
			$com_o->save();
		}
		return $this->mk_comm_orb(array("group" => $arr["group"]), $arr["id"]);
	}
	
	/**
		@attrib name=delete_prof_com all_args="1"
		@param sel required type=int acl=view
		@param profile_id required
		@param group optional
		@param return_url optional
	**/
	function delete_prof_com($arr)
	{
		if(is_array($arr["sel"]))
		{
			$ol = new object_list();
			$ol->add($arr["sel"]);
			$ol->delete();
		}
		return $this->mk_comm_orb(array("group" => $arr["group"]), $arr["id"]);
	}
	
	/**
		@attrib name=delete_comments all_args="1"
		@param sel required type=int acl=view
		@param img_id required
		@param group optional
		@param return_url optional
	**/
	function delete_comments($arr)
	{
		$r_url = $this->mk_comm_orb(array("group" => $arr["group"]), $arr["id"]);
		$vars = array(
			"id" => $arr["id"], 
			"group" => $arr["group"],
		);
		if(!$person = $this->get_person())
		{
			return $r_url;
		}
		
		if(is_array($arr["sel"]))
		{
			$ol = new object_list();
			$ol->add($arr["sel"]);
			$ol->delete();
		}
		
		$vars["img_id"] = $person->meta("img_id");
		$person->set_meta("img_id","");
		$person->save();
		return $r_url;
	}

	/**
		@attrib name=delete_contact
		@param sel required
	**/
	function delete_contact($arr)
	{
		$user = $this->get_user();
		$contact_list = $user->connections_to(array(
			"type" => 1, //RELTYPE_LIST_OWNER
			"from.class_id" => 811,
		));
		foreach($contact_list as $tlist)
		{
			//arr($tlist);
			$obj = $tlist->from();
		}
		//$obj = obj($arr["id"]);
		if(is_array($arr["sel"]))
		{
			foreach($arr["sel"] as $id)
			{
				if($obj->is_connected_to(array(
					"to" => $id,
					"type" => "RELTYPE_ADDED_USER",
				)))
				{
					$obj->disconnect(array(
						"from" => $id,
						"reltype" => "RELTYPE_ADDED_USER",
					));
				}
			}
		}
		return $this->mk_comm_orb(array("group" =>  $arr["group"], "id" => $arr["id"]), obj($arr["id"]));
	}

	/**
		@attrib name=hit_that_shit
	**/
	function hit_that_shit($arr)
	{
		aw_session_del("last_rated_oid");
		return "sessioon läinud";
	}
	
	/**
		@attrib name=delete_message
		@param sel required type=int acl=delete
	**/
	function delete_message($arr)
	{
		$ins = get_instance(CL_QUICKMESSAGEBOX);
		return $ins->delete_message($arr);
	}
	
	/**
		@attrib name=commaction

		@param id required type=int acl=view
		@param commact required
		@param profile optional type=int acl=view
		@param cuser optional type=int acl=view
		@param group optional
	**/
	function commaction($arr)
	{
		//arr($arr);
		/*
		Array
		(
			[id] => 536
			[commact] => add_friend
			[profile] => 111
			[user] => 1
		)
		*/
		$this->callback_on_load(array("request" => $arr));
		
		switch($arr["commact"])
		{
			case "add_contact":
				$this->add_contact($arr);
				return $this->mk_comm_orb(array("group" => $arr["group"]));
				break;
			case "add_blocked":
			case "add_ignored":
			case "add_friend":
				$names = array("add_friend", "add_ignored", "add_blocked");
				$vars = array(
					$names[0] => array(
						"type" => 0,
						"group" => "friend_list",
					 ),
					$names[1] => array(
						"type" => 1,
						"group" => "ignored_list",
					 ),
					$names[2] => array(
						"type" => 2,
						"group" => "blocked_list",
					 ),
				);
				if($arr["commact"] == "add_friend")
				{
					$vars[$arr["commact"]]["profile"] = $arr["profile"];
				}
				else
				{
					$vars[$arr["commact"]]["user"] = $arr["cuser"];
				}
				//confirmation page??
				arr($vars[$arr["commact"]]);
				$this->add_connection($vars[$arr["commact"]]);
				return $this->mk_comm_orb(array("group" => $vars[$arr["commact"]]["group"]));
				break;
			/*
			case "new_profile":
				$this->new_profile($arr);
				return $this->mk_comm_orb(array("group" => "friends"));
				break;
			*/
			case "do_a_thing":
				$this->do_a_thing($arr);
				break;
			
			case "profile":
			
				return $this->mk_comm_orb(array(
					"id" => $arr["id"],
					"commact" => "profile",
					"profile" => $arr["profile"],
					"group" => $arr["group"],
				));

				switch ($arr["group"])
				{
					case "profile":
					case "profile_view":
					case "profile_change":
						break;
				}
				
				break;
		}
		//echo aw_global_get('REQUEST_URI'));
		return aw_global_get("HTTP_REFERER");
	}
	
	/**
		@attrib name=archive_message
		@param sel required type=int acl=view
	**/
	function archive_message($arr)
	{
		// ..and why shouldn't i push all the stuff into other class? saves a LOT of rewrite in case of class rewrite -- ahz
		$ins = get_instance(CL_QUICKMESSAGEBOX);
		return $ins->archive_message($arr);
	}
	
	/**
		@attrib name=remove_from_community all_args="1"
		@param id required type=int
		@param sel required type=int acl=view
		@param group optional
		@param return_url optional
	**/
	function remove_from_community($arr)
	{
		$user = $this->get_user();
		if(is_array($arr["sel"]))
		{
			foreach($arr["sel"] as $sel)
			{
				$conn = $user->connections_to(array(
					"from" => $sel,
					"class_id" => CL_COMMUNITY,
					"type" => array(3, 4, 5),
				));
				
				// as an idea, there should be only one connections to the user, 
				// but then again, who knows.. -- ahz
				if(count($conn) > 0)
				{
					$community = get_instance(CL_COMMUNITY);
					foreach($conn as $con)
					{
						//arr($con);
						$args = $arr;
						unset($args["sel"]);
						$args["sel"][] = $user->id();
						$args["group"] = $con->prop("reltype");
						$args["id"] = $sel;
						$community->remove_con($args);
					}
				}
			}
		}
		return html::get_change_url($arr["id"], array("group" => $arr["group"]));
	}

	/**  
		
		@attrib name=change params=name all_args=1 nologin=1 is_public=1 caption="Kommuuni sisu"
		
		@param id optional type=int acl=view
		@param group optional
		@param period optional
		@param alias_to optional
		@param return_url optional
		
		@returns
		
		@comment

	**/
	function change($args = array())
	{
		if(strpos($_SERVER["REQUEST_URI"],"/automatweb") !== false)
		{
			return parent::change($args);
        }
		enter_function("cb-change");
		$this->init_class_base();

		$this->subgroup = $this->reltype = "";
		$this->is_rel = false;

		$this->orb_action = $args["action"];
		
		$this->is_translated = 0;

		if (empty($args["action"]))
		{
			$args["action"] = "change";
		};
		
		if (method_exists($this->inst,"callback_on_load"))
		{
			$this->inst->callback_on_load(array(
				"request" => $args,
			));
		}

		if ($args["no_active_tab"])
		{
			$this->no_active_tab = 1;
		};

		if (empty($args["form"]))
		{
			if (($args["action"] == "change") || ($args["action"] == "view"))
			{
				$this->load_storage_object($args);
				if ($this->obj_inst->class_id() == CL_RELATION)
				{
					// this is a relation!
					$this->is_rel = true;
					$def = $this->_ct[$this->clid]["def"];
					$meta = $this->obj_inst->meta("values");
					$this->values = $meta[$def];
					$this->values["name"] = $this->obj_inst->name();
				};

			};
		}

		$this->use_form = $use_form;

		$filter = array(
			"clid" => $this->clid,
			"clfile" => $this->clfile,
			"group" => $args["group"],
		);

		$properties = $this->get_property_group($filter);
		
		if(array_key_exists("name", $properties))
		{
			header("location:".aw_ini_get("baseurl"));
			die();
		}

		$this->set_classinfo(array("name" => "hide_tabs","value" => 1));
		$this->set_classinfo(array("name" => "layout", "value" => ""));
		
		if (!empty($args["form"]))
		{
			$onload_method = $this->forminfo(array(
				"form" => $args["form"],
				"attr" => "onload",
			));

			if (method_exists($this->inst, $onload_method))
			{
				$this->inst->$onload_method($args);
			}
		};
	
		$this->request = $args;

		if(method_exists($this->inst,"callback_pre_edit"))
		{
			$fstat = $this->inst->callback_pre_edit(array(
				"id" => $this->id,
				"request" => $this->request,
				"obj_inst" => &$this->obj_inst,
				"group" => $this->use_group,
			));

			if (is_array($fstat) && !empty($fstat["error"]))
			{
				$properties = array();
				$properties["error"] = array(
					"type" => "text",
					"error" => $fstat["errmsg"],
				);
				$gdata["submit"] = "no";
			}
		}
		
		$resprops = $this->parse_properties(array(
			"properties" => &$properties,
		));
		if(array_key_exists("submit", $this->groupinfo[$args["group"]]))
		{
			if($this->groupinfo[$args["group"]]["submit"] == "no")
			{
				$lm = 1;
			}
		}
		foreach($resprops as $prop)
		{
			if($prop["type"] == "toolbar")
			{
				$lm = 1;
				break;
			}
		}

		if (!empty($lm))
		{
			$gdata["submit"] = "no";
		};
		
		$template = $this->forminfo(array(
			"form" => $args["form"],
			"attr" => "template",
		));
		$o_arr = array(
			"tpldir" => "applications/commune/commune", 
			"tabs" => false,
		);
		if (!empty($template))
		{
			$o_arr["template"] = $template;
		}
		$cli = get_instance("cfg/htmlclient", $o_arr);

		if (is_array($this->layoutinfo) && method_exists($cli,"set_layout"))
		{
			$tmp = array();
			// export only layout information for the current group
			foreach($this->layoutinfo as $key => $val)
			{
				if ($val["group"] == $this->use_group)
				{
					$tmp[$key] = $val;


				};
			};
			$cli->set_layout($tmp);
		};

		$this->inst->relinfo = $this->relinfo;

		enter_function("parse-properties");

		exit_function("parse-properties");
		enter_function("add-property");

		foreach($resprops as $val)
		{
			$cli->add_property($val);
		};
		exit_function("add-property");
		
		$argblock = array(
			"id" => $this->id,
			"group" => isset($this->request["group"]) ? $this->request["group"] : $this->use_group,
			"orb_class" => "commune",
			"section" => $_REQUEST["section"],
		);

		if (method_exists($this->inst,"callback_mod_reforb"))
		{
			$this->inst->callback_mod_reforb(&$argblock,$this->request);

		};

		$submit_action = "submit";

		$form_submit_action = $this->forminfo(array(
			"form" => $use_form,
			"attr" => "onsubmit",
		));

		if (!empty($form_submit_action))
		{
			$submit_action = $form_submit_action;
		}

		// forminfo can override form post method
		$form_submit_method = $this->forminfo(array(
			"form" => $use_form,
			"attr" => "method",
		));

		$method = "POST";
		if (!empty($form_submit_method))
		{
			$method = "GET";
		};

		if (!empty($gdata["submit_method"]))
		{
			$method = "GET";
			$submit_action = $args["action"];
		};

		if (!empty($gdata["submit_action"]))
		{
			$submit_action = $gdata["submit_action"];
		}	

		if ($method == "GET")
		{
			$argblock["no_reforb"] = 1;
		};

		enter_function("final-bit");
		
		$cli->finish_output(array(
			"method" => $method,
			"action" => $submit_action,
			// hm, dat is weird!
			"submit" => isset($gdata["submit"]) ? $gdata["submit"] : "",
			"data" => $argblock,
		));
		$rv = $cli->get_result();
		
		exit_function("final-bit");
		exit_function("cb-change");
		return $rv;
	}
}
?>
