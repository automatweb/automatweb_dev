<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/quickmessage/contact_list.aw,v 1.3 2004/11/18 17:21:47 ahti Exp $
// contact_list.aw - Aadressiraamat 
/*

@classinfo syslog_type=ST_CONTACT_LIST relationmgr=yes 

@default table=objects
@default group=general


@groupinfo contact_list caption="Aadressiraamat" submit=no

@property contact_list_toolbar type=toolbar group=contact_list no_caption=1
@property Aadressiraamatu toolbar

@property contact_list type=table group=contact_list no_caption=1
@caption Aadressiraamat


//@groupinfo search caption="Otsing"

//@property search_form type=text group=search
//@caption Otsinguvorm


//@groupinfo addnew caption="Lisa uus"

//@property fake type=text group=addnew
//@caption asd


@reltype LIST_OWNER value=1 clid=CL_USER
@caption Aadressiraamatu omanik

@reltype ADDED_PERSON value=2 clid=CL_CRM_PERSON
@caption Lisatud aadress

@reltype LIST_PROFILE_SEARCH value=3 cl=CL_CB_SEARCH
@caption Profiilide otsing

*/

class contact_list extends class_base
{
	function contact_list()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/quickmessage/",
			"clid" => CL_CONTACT_LIST
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "contact_list_toolbar":
				//$tb = &;
				/*
				$tb->add_button(array(
            		"name" => "add",
            		"tooltip" => "Lisa aadressiraamatusse",
            		"img" => "new.gif",
            		"url" => $this->mk_my_orb(
						"change", array("group" => "addnew", "id" => $arr["obj_inst"]->id()), CL_CONTACT_LIST),
        		));
				$tb->add_separator();
				$tb->add_button(array(
					"name" => "search",
				"tooltip" => "Otsi kontakte",
            		"img" => "search.gif",
            		"action" => "",
        		));
				$tb->add_separator();
				*/
				$prop["vcl_inst"]->add_button(array(
            		"name" => "delete",
            		"tooltip" => "Kustuta kontakte",
            		"img" => "delete.gif",
            		"action" => "delete",
					"confirm" => "Oled kindel, et tahad valitud eemaldada?",
        		));
				break;
			case "contact_list":
				$q = new object_list(array(
					"class_id" => CL_COMMUNE,
				));
				//arr($q);
				$a = $q->begin();
				$vars = array(
					"obj_inst" => &$arr["obj_inst"],
					"commune" => $a->id(),
					"vcl_inst" => &$arr["prop"]["vcl_inst"],
				);
				$this->show_contact_list($vars);
				break;
		};
		return $retval;
	}
	
	function show_contact_list($arr)
	{
		/*
		$c = new connection();
		$asd = $c->find(array("to" => $arr["id"], "type" => 1));
		arr($asd);
		*/
		//arr($arr["obj_inst"]);
		$contacts = array();
		$owner = &$arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_LIST_OWNER");
		if(is_object($owner))
		{
			
			// now, first we sort out the things we need -- ahz
			$person = $owner->get_first_obj_by_reltype("RELTYPE_PERSON");
			$conts = $person->connections_from(array(
				"type" => "RELTYPE_FRIEND",
				//"sort_by" => "",
				//"sort_dir" => "desc",
			));
			foreach($conts as $cont)
			{
				$a_person = $cont->to();
				$a_profile = $a_person->get_first_obj_by_reltype("RELTYPE_PROFILE");
				$a_user = $a_person->createdby();
				$contacts[$a_user->id()] = array(
					"id" => $a_person->id(),
					"name" => $a_user->name(),
					"email" => $a_person->prop("email"),
					"profile" => $a_profile->id(),
				);
			}
			
		}
		$persons = &$arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_ADDED_PERSON",
		));
		//arr($users);
		foreach($persons as $pers)
		{
			$b_person = $pers->to();
			$b_user = $b_person->createdby();
			$b_profile = $person->get_first_obj_by_reltype("RELTYPE_PROFILE");
			$contacts[] = array(
				"id" => $b_user->id(),
				"name" => $b_user->name(),
				"email" => $b_user->prop("email"),
				"profile" => is_object($b_profile) ? $b_profile->id() : "",
			);
		}
		$t = &$arr["vcl_inst"];
		$r_on_page = 40;
		$t->table_header = $t->draw_text_pageselector(array(
			"records_per_page" => $r_on_page, // rows per page
			"d_row_cnt" => count($contacts), // total rows 
		));
		$ft_page = $arr["request"]["ft_page"] ? $arr["request"]["ft_page"] : 0;
		$contacts = array_slice($contacts, ($ft_page * $r_on_page), $r_on_page);
		$t->define_field(array(
			"name" => "id",
			"caption" => "ID",
		));
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
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));
		//arr($contacts);
		foreach($contacts as $contact)
		{
			// h4x0rD stuff -- ahz
			if($arr["include"])
			{
				$profile = $this->mk_my_orb("change",array(
					"id" => $arr["commune"],
					"group" => "friend_details",
					"profile" => $contact["profile"],
				), "commune");
				$message = $this->mk_my_orb("change",array(
					"id" => $arr["commune"],
					"cuser" => $contact["name"],
					"group" => "newmessage",
				),"commune");
			}
			else
			{
				$profile = $this->mk_my_orb("change",array(
					"id" => $contact["profile"],
				), CL_PROFILE);
				$message = $this->mk_my_orb("change",array(
					"cuser" => $contact["name"],
					"group" => "newmessage",
				), CL_QUICKMESSAGE);
			}
			$t->define_data(array(
				"id" => $contact["id"],
				"name" => html::href(array(
					"url" => $profile,
					"caption" => $contact["name"],
				)),
				"email" => html::href(array(
					"url" => "mailto:".$contact["email"],
					"caption" => $contact["email"],
				)),
				"sendmessage" => html::href(array(
					"url" => $message,
					"caption" => "Saada sõnum",
				)),
			));
		}
	}
	
	/**	
		@attrib name=delete
		
		@param id required type=int acl=view
		@param group optional
		@param sel required
	**/
	function delete($arr)
	{
		$obj = obj($arr["id"]);
		if(is_array($arr["sel"]))
		{
			foreach($arr["sel"] as $id)
			{
				$obj->disconnect(array(
					"from" => $id,
					"reltype" => "RELTYPE_ADDED_PERSON",
					"errors" => false,
				));
			}
		}
		return html::get_change_url($arr["id"], array("group" =>  $arr["group"]));
	}
	
	/**	
		@attrib name=show_list
		
		@param id required type=int acl=view
	**/
	function show_list($arr)
	{
		$this->read_template("show_list.tpl");
		echo dbg::process_backtrace(debug_backtrace());
		return $this->parse();
	}
	
	
	/*
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		}
		return $retval;
	}
	*/
}
?>
