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

//@property profile_link type=text store=no
//@caption Profiil

//@property commune_profile_link type=text store=no
//@caption Profiil kommuunis

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

	function test($arr)
	{
		//echo "siia siis ei tulegi? :(";
	}
	function init_rate($arr)
	{
		$this->inst->kala = "tursk";
		$q = "SELECT profile2image.* FROM profile2image LEFT JOIN objects ON (profile2image.img_id = objects.oid) WHERE objects.status > 0";
		$this->db_query($q);
		
		while($value = $this->db_next())
		{
		//arr($value);
			$var[] = $value;
		}
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
		/*
		$q2 = "select count(*) as total from profile2image left join objects on (profile2image.img_id=objects.oid) where objects.status=2";
		$q2 = $this->db_query($q2);
		$q3 = $this->db_next();
		echo $q3["total"]."<br>";
		$row = $this->db_fetch_row($q);
		$this->db_query($q);
		$row = $this->db_next();
		echo $q;
		$w = mysql_query($q);
		$row = mysql_fetch_assoc($w);
		
		$w = mysql_query($q,$newqhandle);
		while($row2 = mysql_fetch_assoc($w))
		{
			arr($row);
			$row=$row2;
		}
		mysql_close($newqhandle);
		*/
		// see siin on VÄGA AJUTINE workaround :|
		//for($i=1;$i<=2;$i++)
		//{
			//$row = $this->db_fetch_row($q);
		//}

		// müstika: _admin_keskkonnas!!_ hakkab mõnikord üks ja sama pilt korduma - siit saabub kogu aeg üks ja sama kirje.
		//arr($row); echo time();
		// kas on tegemist mingi query cachemisega?

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
			// all this crap get's out of here, rating should be only for rating, misc things
			// go under profile view -- ahz
			/*
			case "profile_link":
				$prop["value"] = $this->person_data->prop("firstname") . " " . $this->person_data->prop("lastname").
					" " . html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"profile" => $this->profile_data->id(),
						"group" => "friend_details",
					), "commune"),
					"caption" => "profiil",
				));
				break;
			case "commune_profile_link":
				$prop["value"] = $this->person_data->prop("firstname") . " " . $this->person_data->prop("lastname").
					" " . html::href(array(
					"url" => $this->mk_my_orb("commaction", array(
						"id" => $GLOBALS["id"],
						"profile" => $this->profile_data->id(),
						"commact" => "profile",
						"group" => "friends",
					), "commune"),
					"caption" => "profiil",
				));
				break;
			// THIS should certainly NOT be here, goes under profile view -- ahz
			*/
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
				$id = $this->image_data->id();
				$prop["use_parent"] = $id;
				break;

		};
		return PROP_OK;
	}


};
?>
