<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/commune/Attic/profile.aw,v 1.8 2005/04/21 08:48:47 kristo Exp $
// profile.aw - Profiil 
/*
@classinfo syslog_type=ST_PROFILE relationmgr=yes

@default table=objects
@default group=general

@property online type=text store=no
@caption Online

@property age type=text store=no
@caption Vanus

@groupinfo settings caption="Seaded"

------------------------- settings --------------------------------

------------------------- general -----------------------------------

@groupinfo settings_general caption="Üldandmed" parent=settings
@default group=settings_general

@property avatar_image type=releditor reltype=RELTYPE_AVATAR rel_id=first use_form=emb method=serialize field=meta
@caption Avatar

//isikul peaks olema üks profiil, mis on vaikimisi aktiivne, mida sisse logimisel esimesena näidatakse
//lihtsam oleks, kui see oleks isiku küljes, aga praegu on ta siin, sest isikut ei tohi puutuda.

------------------------- general : end -----------------------------------

@tableinfo aw_profiles index=id master_table=objects master_index=brother_of
@default table=aw_profiles

explain aw_profiles;
+--------------------+---------------------+------+-----+---------+-------+
| Field              | Type                | Null | Key | Default | Extra |
+--------------------+---------------------+------+-----+---------+-------+
| id                 | int(11)             |      |     | 0       |       |
| height             | int(10) unsigned    |      |     | 0       |       |+
| weight             | int(10) unsigned    |      |     | 0       |       |+
| eyes_color         | int(10) unsigned    |      |     | 0       |       |+
| hair_color         | int(10) unsigned    |      |     | 0       |       |+
| hair_type          | int(10) unsigned    |      |     | 0       |       |+
| body_type          | int(10) unsigned    |      |     | 0       |       |+
| tobacco            | int(10) unsigned    |      |     | 0       |       |+
| alcohol            | int(10) unsigned    |      |     | 0       |       |+
| sexual_orientation | int(10) unsigned    |      |     | 0       |       |+
| user_check1        | tinyint(4)          | YES  |     | 0       |       |+
| user_check2        | tinyint(4)          | YES  |     | 0       |       |+
| user_text1         | varchar(255)        | YES  |     | NULL    |       |+
| user_text2         | varchar(255)        | YES  |     | NULL    |       |+
| user_text3         | varchar(255)        | YES  |     | NULL    |       |+
| user_text4         | varchar(255)        | YES  |     | NULL    |       |+
| user_text5         | varchar(255)        | YES  |     | NULL    |       |+
| user_blob1         | text                | YES  |     | NULL    |       |+
| user_blob2         | text                | YES  |     | NULL    |       |+
| user_field1        | bigint(20) unsigned | YES  |     | NULL    |       |+
| user_field2        | bigint(20) unsigned | YES  |     | NULL    |       |+
+--------------------+---------------------+------+-----+---------+-------+

------------------------- üldandmed -----------------------------------

@property user_text1 type=textbox datatype=int
@caption Telefon

@property user_text2 type=textbox datatype=int
@caption ICQ

@property user_text3 type=textbox
@caption MSN

@property user_blob1 type=textarea cols=50 rows=10
@caption Lisainfo

@property user_blob2 type=textarea cols=50 rows=10
@caption Lisainfo sõpradele

@property user_check1 type=checkbox ch_value=1 default=1
@caption E-post varjatud

------------------------- üldandmed : end -----------------------------------

------------------------- välimus -----------------------------------

@groupinfo settings_outlook caption="Välimus" parent=settings
@default group=settings_outlook

@property height type=classificator reltype=RELTYPE_PRF_HEIGHT orient=vertical
@caption Kasv

@property weight type=classificator reltype=RELTYPE_PRF_WEIGHT orient=vertical
@caption Kaal

@property eyes_color type=classificator reltype=RELTYPE_PRF_EYES_COLOR orient=vertical
@caption Silmade värv

@property hair_color type=classificator reltype=RELTYPE_PRF_HAIR_COLOR orient=vertical
@caption Juuksevärv

@property hair_type type=classificator reltype=RELTYPE_PRF_HAIR_TYPE orient=vertical
@caption Juuste tüüp

@property body_type type=classificator reltype=RELTYPE_PRF_BODY_TYPE orient=vertical
@caption Keha tüüp

------------------------- välimus : end -----------------------------------

------------------------- harrastused -----------------------------------

@groupinfo settings_hobbies caption="Harrastused" parent=settings
@default group=settings_hobbies

@property user_text5 type=textbox
@caption Koduleht

@property hobbies type=textarea cols=50 rows=15 table=objects field=meta method=serialize
@caption Huvid/harrastused

------------------------- harrastused : end -----------------------------------

------------------------- harjumused -----------------------------------

@groupinfo settings_habits caption="Harjumused" parent=settings
@default group=settings_habits

@property sexual_orientation type=classificator reltype=RELTYPE_PRF_SEX_ORIENT orient=vertical
@caption Seksuaalne orientatsioon

@property alcohol type=classificator reltype=RELTYPE_PRF_ALCOHOL orient=vertical
@caption Alkoholi tarbimine

@property tobacco type=classificator reltype=RELTYPE_PRF_TOBACCO orient=vertical
@caption Tubaka tarbimine

------------------------- harjumused : end -----------------------------------

------------------------- kool_too -----------------------------------

@groupinfo settings_occupation caption="Kool/Töö" parent=settings
@default group=settings_occupation

@property user_field2 type=classificator orient=vertical 
@caption Haridustase

@property occupation type=classificator table=objects field=meta method=serialize
@caption Tegevusala

@property user_text4 type=textbox
@caption Elukutse

------------------------- kool_too : end -----------------------------------

------------------------- comments -----------------------------------

@groupinfo comments caption=Kommentaarid

@property comments type=comments group=comments store=no
@caption Kommentaarid

----------------RELTYPES----------------------------------------------

@reltype PRF_EYES_COLOR value=2 clid=CL_META
@caption Silmade värv

@reltype PRF_HAIR_COLOR value=3 clid=CL_META
@caption Juuksevärv
 
@reltype PRF_HAIR_TYPE value=4 clid=CL_META
@caption Juuksetüüp
 
@reltype PRF_BODY_TYPE value=5 clid=CL_META
@caption Keha tüüp
 
@reltype PRF_SEX_ORIENT value=6 clid=CL_META
@caption Seksuaalne kalduvus
 
@reltype PRF_ALCOHOL value=7 clid=CL_META
@caption Alkoholi tarbimine

@reltype PRF_TOBACCO value=8 clid=CL_META
@caption Tubaka tarbimine

@reltype PERSON value=9 clid=CL_CRM_PERSON
@caption Isikuobjekt

@reltype PRF_HEIGHT value=10 clid=CL_META
@caption Pikkus

@reltype PRF_WEIGHT value=11 clid=CL_META
@caption Kaal

@reltype IMAGE value=12 clid=CL_IMAGE
@caption Pilt

@reltype AVATAR value=26 clid=CL_IMAGE
@caption Avatar

@reltype CFG_MANAGER value=15 clid=CL_CFGMANAGER
@caption Seadete haldur

*/

class profile extends class_base
{
	function profile()
	{
		$this->init(array(
			"tpldir" => "profile/profile",
			"clid" => CL_PROFILE
		));
	}

	function callback_on_load($arr)
	{
		if($arr["cfgform"])
		{
			$this->cfgmanager = $arr["cfgform"];
		}
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		if($arr["new"])
		{
			return $retval;
		}
		switch($prop["name"])
		{
			case "age":
				$prop["value"] = $this->get_age($arr["obj_inst"]);
				break;

			case "online":
				$prop["value"] = $this->is_online($arr["obj_inst"]) ? LC_YES : LC_NO;
				break;
			case "user_text1":
			case "user_text2":
				if($prop["value"] == 0)
				{
					$prop["value"] = "";
				}
				break;
			case "user_text5":
				if(empty($prop["value"]))
				{
					$prop["value"] = "http://";
				}
				break;
		};
		return $retval;
	}
	
	function is_online($profile)
	{
		if(!is_object($profile))
		{
			return PROP_IGNORE;
		}
		if ($person = $profile->get_first_obj_by_reltype("RELTYPE_PERSON"))
		{
			if ($user_c = reset($person->connections_to(array("type" => 2))))
			{
				//$lastaction = $user->prop("lastaction");
				$timeout = 600;
				$q = "SELECT lastaction FROM users WHERE uid = '".$user_c->prop("from.name")."'";
				$this->db_query($q);
				
				$row = $this->db_next();
				if ((time() - $row["lastaction"]) < $timeout)
				{
					return true;
				}
			}
		}
		return false;
	}

	function get_age($obj_inst)
	{
		$age = null;
		if (!is_object($obj_inst))
		{
			return $age;
		}
		
		if ($person = $obj_inst->get_first_obj_by_reltype("RELTYPE_PERSON"))
		{
			$birthday = $person->prop("birthday"); // gives "-1" on unselcted date_edit ("---")
			//if(!isset($birthday)) //doesn't work!
			//why the hell isn't there a common way to check that prop("xxx") is not set??
			if ($birthday == -1)
			{
				return $age;
			}
			$birthday_rec = getdate($birthday);
			$now_rec = getdate();

			$age = $now_rec["year"] - $birthday_rec["year"];
			if ($now_rec["mon"] < $birthday_rec["mon"])
			{
				$age--;
			}
			elseif($now_rec["mon"] == $birthday_rec["mon"])
			{
				if($now_rec["mday"] <= $birthday_rec["mday"])
				{
					$age--;
				}
			}
		}
		return $age;
	}
	
	function get_person_for_profile($profile)
	{
		if (!$person = $profile->get_first_obj_by_reltype("RELTYPE_PERSON"))
		{
			return false;
		}
		return $person;
	}
}
?>
