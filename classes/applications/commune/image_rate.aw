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

@property check type=callback callback=callback_check no_caption=1
@caption Kontrollib asju

@property rate type=chooser store=no
@caption Hinda

@property image type=text store=no no_caption=1
@caption Pilt

@property image_name type=text store=no
@caption Pildi nimi

@property image_comment type=text store=no
@caption Pildi kommentaar

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
	var $no_picture;
	function image_rate()
	{
		$this->init();
		$this->no_picture = false;
	}
	
	function init_rate($arr)
	{
		//arr(aw_global_get("rated_objs"));
		$user_id = aw_global_get("uid_oid");
		if(!empty($user_id))
		{
			$user = obj($user_id);
			$person = $user->get_first_obj_by_reltype("RELTYPE_PERSON");
			//$view = $person->meta("view_conditions");
			$browse = $person->meta("browsing_conditions");
		}
		//aw_session_del("rated_objs");
		//arr($view);
		//arr($browse);
		$w = "";
		$x = array();
		if(is_array($browse["sexorient"]))
		{
			$w .= " AND aw_profiles.sexual_orientation IN (".implode(", ", $browse["sexorient"]).")"; 
		}
		if(!empty($browse["gender"]))
		{
			$w .= " AND isik.gender = '".$browse["gender"]."'";
		}
		
		if(!empty($browse["age_s"]))
		{
			$w .= " AND isik.birthday < '".mktime(0, 0, 0, 0, 0, (date("Y") - $browse["age_s"]))."'";
		}
		if(!empty($browse["age_e"]))
		{
			$w .= " AND isik.birthday > '".mktime(0, 0, 0, 0, 0, (date("Y") - $browse["age_s"]))."'";
		}
		if(!empty($GLOBALS["img_id"]))
		{
			$w .= " AND profile2image.img_id = '".$GLOBALS["img_id"]."'";
		}
		$ro = aw_global_get("rated_objs");
		if(is_array($ro))
		{
			$w .= " AND profile2image.img_id NOT IN (".implode(", ", array_keys($ro)).")";
		}
		/*
		if($user_group)
		{
			$w .= " AND prof2g = '$user_group'";
		}
		else
		{
			$w .= " AND isik.active_profile = aw_profiles.id";
		}
		*/
		/*
		sinu kasutaja			vaadatav kasutaja
		user				<-	ei ole blokitud
		ei ole ignoreeritud	->	user
		sex_orient			->	profile
		vanus				->	profile
		sugu				->	person
		group				<-	active_profile
		
		prof2g.group = '$user_group'
		
		*/;
		//arr(aw_cache_get("get_gid_for_uid", $user_id));
		$q2 = "
			SELECT 
				profile2image.*, users.oid, isik.oid AS person_id 
			FROM 
				profile2image 
				LEFT JOIN aw_profiles ON (profile2image.prof_id = aw_profiles.id)
				LEFT JOIN profile2group prof2g ON (prof2g.profile = aw_profiles.id)
				LEFT JOIN aliases prof2person ON (prof2person.source = aw_profiles.id AND prof2person.reltype = 9)
				
				LEFT JOIN kliendibaas_isik isik ON (prof2person.target = isik.oid)
				LEFT JOIN aliases isik2user ON (isik2user.target = isik.oid AND isik2user.reltype = 2)
				LEFT JOIN users ON (isik2user.source = users.oid)
				
				LEFT JOIN objects img_o ON (profile2image.img_id = img_o.oid)
				LEFT JOIN objects prof_o ON (aw_profiles.id = prof_o.oid)
				LEFT JOIN objects isik_o ON (isik.oid = isik_o.oid)
				LEFT JOIN objects user_o ON (users.oid = user_o.oid)
			WHERE
				img_o.status = 2 AND
				prof_o.status != 0 AND 
				isik_o.status != 0 AND
				user_o.status != 0 AND
				user_o.oid != '$user_id'
				$w
				LIMIT 100
		";
		$this->db_query($q2);
		$var = array();
		while($value = $this->db_next())
		{
			$var[] = $value;
		}
		//arr($var);
		$true = false;
		$count = count($var);
		shuffle($var);
		//arr($var);
		while(!$true)
		{
			$count--;
			if($count < 0)
			{
				$this->inst->no_picture = true;
				return;
			}
			$row = $var[$count];
			$query = "SELECT count(*) AS num FROM aliases WHERE ((source = '".$person->id()."' AND target = '".$row["person_id"]."') AND reltype = '39') OR ((source = '".$row["person_id"]."' and target = '".$person->id()."') AND reltype = '38')";
			$this->db_query($query);
			$q = $this->db_next();
			if($this->can("view", $row["img_id"]) && is_oid($row["img_id"]) && $q["num"] == 0)
			{
				$true = true;
			}
			else
			{
				$this->inst->no_picture = true;
			}
		}
		$rt = get_instance(CL_RATE);
		$this->inst->profile_data = obj($row["prof_id"]);
		$this->inst->image_data = obj($row["img_id"]);
		$this->inst->user_data = obj($row["oid"]);
		$this->inst->person_data = obj($row["person_id"]);
		$this->inst->current = $rt->get_rating_for_object($row["img_id"]);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		if($prop["name"] == "first1" && $this->no_picture)
		{
			return PROP_OK;
		}
		if($this->no_picture)
		{
			return PROP_IGNORE;
		}
		$params = array(
			"id" => $GLOBALS["id"], // commune'i id! ($arr["request"]["id"] ei anna midagi, sest form)
			"person" => $this->person_data->id(),
		);
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
				$smparams = $params + array(
					"cuser" => $this->user_data->name(),
					"group" => "newmessage",
				);
				unset($smparams["person"]);
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("change", $smparams, "commune"),
					"caption" => "Saada sõnum",
				));
				break;
			case "add_friend_link":
				$afparams = $params + array(
					"commact" => "add_friend",
					"group" => "friends",
				);
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("commaction", $afparams, "commune"),
					"caption" => "lisa sõprade hulka",
				));
				break;
			case "add_ignored_link":
				$aiparams = $params + array(
					"commact" => "add_ignored",
					"group" => "ignored_list",
				);
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("commaction", $aiparams, "commune"),
					"caption" => "Ignoreeri",
				));
				break;
			case "add_blocked_link":
				$abparams = $params + array(
					"commact" => "add_blocked",
					"group" => "blocked_list",
				);
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("commaction", $abparams, "commune"),
					"caption" => "Blokeeri",
				));
				break;
			case "contact_list_link":
				$clparams = $params + array(
					"commact" => "add_contact",
					"group" => "address_book",
				);
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("commaction", $clparams, "commune"),
					"caption" => "Lisa kontaktidesse",
				));
				break;
				
			case "image":
				$i = get_instance(CL_IMAGE);
				$imgdata = $i->get_image_by_id($this->image_data->id());

				$prop["value"] .= html::img(array(
					"url" => $imgdata["big_url"],
				));
				break;
				
			case "image_name":
				$prop["value"] = $this->image_data->name();
				break;
				
			case "image_comment":
				$prop["value"] = $this->image_data->comment();
				break;
				
			case "rate":
				$scale = get_instance(CL_RATE_SCALE);
				$scl = $scale->get_scale_for_obj($this->image_data->id());
				$commune = obj($GLOBALS["id"]);
				$me = obj(aw_global_get("uid_oid"));
				$q = "SELECT SUM(sum) AS kokku FROM rate_sum WHERE oid = '".$me->id()."'";
				$val = $this->db_fetch_field($q, "kokku");
				if((int)$val >= $commune->prop("rate_sum"))
				{
					$scl[7] = 7;
				}
				$prop["options"] = $scl;
				$prop["onclick"] = "this.form.submit()";
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
				$prop["head_text"] = "pildi";
				break;

		};
		return PROP_OK;
	}
	
	function callback_check($arr)
	{
		if($this->no_picture)
		{
			return array("one" => array(
				"name" => "first1",
				"type" => "text",
				"no_caption" => 1,
				"value" => "Ei õnnestunud pilti kuvada.",
			));
		}
	}
};
?>
