<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/quickmessage/contact_list.aw,v 1.2 2004/10/20 09:36:04 ahti Exp $
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

@reltype ADDED_USER value=2 clid=CL_USER
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
			"tpldir" => "applications/commune/contact_list",
			"clid" => CL_CONTACT_LIST
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	
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
			$o_person = $owner->get_first_obj_by_reltype("RELTYPE_PERSON");
			$o_profile = $o_person->get_first_obj_by_reltype("RELTYPE_PROFILE");
			$conts = $o_profile->connections_from(array(
				"type" => "RELTYPE_FRIEND",
				"sort_by" => "objects.desc",
			));
			foreach($conts as $cont)
			{
				$profile = $cont->to();
				$person = $profile->get_first_obj_by_reltype("RELTYPE_PERSON");
				$creator = $profile->createdby();
				$contacts[$creator->id()] = array(
					"name" => $creator->name(),
					"email" => $person->prop("email"),
					"profile" => $profile->id(),
				);
			}
		}
		$users = &$arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_ADDED_USER",
			"sort_by" => "objects.desc",
		));
		//arr($users);
		foreach($users as $user)
		{
			$user_o = $user->to();
			$person = $user_o->get_first_obj_by_reltype("RELTYPE_PERSON");
			$profile = $person->get_first_obj_by_reltype("RELTYPE_PROFILE");
			$contacts[$user_o->id()] = array(
				"name" => $user_o->name(),
				"email" => $user_o->prop("email"),
				"profile" => is_object($profile) ? $profile->id() : "",
			);
		}
		$t = &$arr["vcl_inst"];
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
		foreach($contacts as $id => $contact)
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
				),"profile");
				$message = $this->mk_my_orb("change",array(
					"cuser" => $contact["name"],
					"group" => "newmessage",
				),"quickmessage");
			}
			$t->define_data(array(
				"id" => $id,
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
		@param sel required
	**/
	function delete($arr)
	{
		$obj = obj($arr["id"]);
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
		return $this->mk_my_orb("change",array("group" =>  $arr["group"], "id" => $arr["id"]));
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
	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}
?>
