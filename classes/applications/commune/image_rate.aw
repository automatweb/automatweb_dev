<?php
// selle me teeme täpselt nii nagu kliendibaasi otsingu. ehk, see lihtsalt genereerib ühe vormi
// and that's it. and that's that!
/*
@default table=objects
@default field=meta
@default method=serialize
@default store=no

@default group=general
@default form=rate

@property rate type=chooser store=no
@caption Hinda

@property vsbt type=submit store=no
@caption Hinda

@property image type=text store=no
@caption Pilt

@property image_comment type=text store=no
@caption Pildi allkiri

@property current type=text store=no
@caption Hinne

@property name type=text store=no
@caption Profiil

@property add_friend_link type=text store=no
@caption Lisa sõber

@property add_ignored_link type=text store=no
@caption Ignoreeri

@property add_blocked_link type=text store=no
@caption Blokeeri

@property send_message_link type=text store=no
@caption Saade teade

@property contact_list_link type=text store=no
@caption Lisa aadressiraamatusse

@property comments type=comments store=no
@caption Kommentaarium

@property vsbt2 type=submit store=no
@caption Kirjuta

@property prof_id type=hidden store=no
@caption Profiili id

@property img_id type=hidden store=no
@caption Pildi id

@forminfo rate onload=init_rate onsubmit=test method=post

*/

class image_rate extends class_base
{
	function image_rate()
	{
		$this->init();
	}

	function callback_on_load($arr)
	{
		//echo " callback_on_load:";
	}

	function callback_pre_edit($arr)
	{
		//echo " callback_pre_edit:";
	}
	function init_rate($arr)
	{
		$user_i = get_instance(CL_USER);
		$user = obj($user_i->get_current_user());
		$person = $user->get_first_obj_by_reltype("RELTYPE_PERSON");
		$view = $person->meta("view_conditions");
		$message = $person->meta("message_conditions");
		$browse = $person->meta("browsing_conditions");
		//arr($browse);

		// this is the place where aw-s functionality fails in speed, and hack comes along
		// and a really ugly hack that is... -- ahz
		$q = "count(*) as total";
		$q = "profile2image.*";
		$q = "SELECT $q FROM profile2image LEFT JOIN objects ON (profile2image.img_id = objects.oid) WHERE objects.status > 0";
		if(!empty($browse["sexorient"]))
		{
			$w = " and aw_profiles.sexual_orientation = '".$browse["sexorient"]."'"; 
		}
		if(!empty($browse["gender"]))
		{
			$w = " and kliendibaas_isik.gender = '".$browse["gender"]."'";
		}
		if(!empty($browse["age_s"]))
		{
			$y = (date("Y")-$browse["age_s"])."0000";
			$w = " and aw_profiles.user_field1 < '$y'";
		}
		if(!empty($browse["age_e"]))
		{
			$y = (date("Y")-$browse["age_e"])."0000";
			$w = "and aw_profiles.user_field > '$y'";
		}
		$usah = get_instance("users");
		//echo $usah->get_oid_for_uid(aw_global_get("uid"));
		// that one hasn't ignored other and the other hasn't blocked one
		$w = "
			and ((aliases.source='$sorts' and aliases.target='$target') and reltype!='$rel1')
			and ((aliases.source='$target' and aliases.target='$sorts') and reltype!='$rel2')
		";
		/*
		$q = "
		select profile2image.* from profile2image 
		left join objects on (profile2image.img_id=objects.oid) 
		left join aliases on (aliases.target=profile2image.prof_id and aliases.reltype=2) 
		left join kliendibaas_isik on (kliendibaas_isik.oid = aliases.source) 
		left join objects as pobject on (kliendibaas_isik.oid = pobject.oid)
		left join aliases as ualias on (pobject.createdby=ualias.source and ualias.reltype=2)
		left join aw_profiles on (aw_profiles.id=profile2image.prof_id)
		where 
		objects.status > 0 and 
		aw_profiles.sexual_orientation='' and 
		kliendibaas_isik.gender='1' and 
		aw_profiles.user_field1 > '1984000' and 
		aw_profiles.user_field1 < '20030000'"; and 
		((aliases.source='1656' and aliases.target=pobject.createdby) and aliases.reltype!='442')
		and ((aliases.source=pobject.createdby and aliases.target='1656') and aliases.reltype!='332')";
		*/
		/*
		$q = "SELECT profile2image.* FROM profile2image LEFT JOIN objects ON (profile2image.img_id = objects.oid) WHERE objects.status > 0";
		*/
		$this->db_query($q);
		
		while($value = $this->db_next())
		{
		//arr($value);
			$var[] = $value;
		}
		//arr($var);
		$count = 0;
		while(!$true)
		{
			$count++;
			$true = false;
			shuffle($var);
			$row = $var[0];
			if($this->can("view", $row["img_id"]))
			{
				$true = true;
			}
			// a little check against endless loop
			if($count > 100)
			{
				break;
			}
		}
		//	$q = "SELECT profile2image.* FROM profile2image LEFT JOIN objects ON (profile2image.img_id = //objects.oid) WHERE objects.status > 0 ORDER BY rand() LIMIT 1";
		// see võtab praegu kõik süsteemis olevad kommuunide poolt tekitatud pildid,
		// ta ei arvesta $commune->prop("profiles_folder")-ga
		$this->inst->profile_data = new object($row["prof_id"]);

		// figure out person data
		if ($conn = reset($this->inst->profile_data->connections_to(array("type" => 14))))
		{
			$this->inst->person_data = $conn->from();
		}
		else
		{  
			//$this->inst_person_data = new object(); //mis see siin on?
			$this->inst->person_data = new object();
		};

		$this->inst->image_data = new object($row["img_id"]);
		$rt = get_instance(CL_RATE);

		$this->inst->current = $rt->get_rating_for_object($row["img_id"]);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		switch($prop["name"])
		{
			case "name":
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $GLOBALS["id"],
						"profile" => $this->profile_data->id(),
						"group" => "friend_details",
					), "commune"),
					"caption" => $this->person_data->prop("firstname") . " " . $this->person_data->prop("lastname"),
				));
				break;
			case "send_message_link":
				$user = $this->profile_data->createdby();
				$params = array(
					"id" => $GLOBALS["id"], // commune'i id! ($arr["request"]["id"] ei anna midagi, sest form)
					"user" => $user->name(),
					"group" => "newmessage",
				);
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("change", $params, "commune"),
					"caption" => "Saada sõnum",
				));
				break;
			case "add_friend_link":
				$params = array(
					"id" => $GLOBALS["id"], // commune'i id! ($arr["request"]["id"] ei anna midagi, sest form)
					"profile" => $this->profile_data->id(),
					"commact" => "add_friend",
					"group" => "friends",
				);
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("commaction", $params, "commune"),
					"caption" => "lisa sõprade hulka",
				));
				break;
			case "add_ignored_link":
				$user = $this->profile_data->createdby();
				$params = array(
					"id" => $GLOBALS["id"], // commune'i id! ($arr["request"]["id"] ei anna midagi, sest form)
					"user" => $user->id(),
					"commact" => "add_ignored",
					"group" => "ignored_list",
				);
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("commaction", $params, "commune"),
					"caption" => "Ignoreeri",
				));
				break;
			case "add_blocked_link":
				$user = $this->profile_data->createdby();
				$params = array(
					"id" => $GLOBALS["id"], // commune'i id! ($arr["request"]["id"] ei anna midagi, sest form)
					"user" => $user->id(),
					"commact" => "add_blocked",
					"group" => "blocked_list",
				);
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("commaction", $params, "commune"),
					"caption" => "Blokeeri",
				));
				break;
			case "contact_list_link":
				$user = $this->profile_data->createdby();
				$params = array(
					"id" => $GLOBALS["id"], // commune'i id! ($arr["request"]["id"] ei anna midagi, sest form)
					"user" => $user->id(),
					"commact" => "add_contact",
					"group" => "address_book",
				);
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("commaction", $params, "commune"),
					"caption" => "Lisa kontaktidesse",
				));
				break;
				
			case "image":
				$i = get_instance(CL_IMAGE);
				$imgdata = $i->get_image_by_id($this->image_data->id());

				$prop["value"] .= html::img(array(
					"url" => $imgdata["url"],
				));
				break;
				
			case "image_comment":
				$prop["value"] = $this->image_data->comment();
				break;
				
			case "rate":
				$prop["options"] = array(
					"1" => "1",
					"2" => "2",
					"3" => "3",
					"4" => "4",
					"5" => "5",
				);
				break;

			case "prof_id":
				$prop["value"] = $this->profile_data->id();
				break;

			case "img_id":
				$prop["value"] = $this->image_data->id();
				//echo $prop["value"];
				break;

			case "current":
				$prop["value"] = $this->current;
				break;

			case "comments":
				//var_dump(aw_global_get("uid")); //mitte sisse logituna: bool(false)
				$prop["use_parent"] = $this->image_data->id();
				break;

		};
		return PROP_OK;
	}


};
?>
