<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/commune/Attic/profile.aw,v 1.6 2004/11/01 14:58:00 ahti Exp $
// profile.aw - Profiil 
/*


@classinfo syslog_type=ST_PROFILE relationmgr=yes

@default group=general
@default table=objects

------------------------- general -----------------------------------

@property avatar_image type=relpicker reltype=RELTYPE_AVATAR method=serialize field=meta
@caption Avatar

//isikul peaks olema üks profiil, mis on vaikimisi aktiivne, mida sisse logimisel esimesena näidatakse
//lihtsam oleks, kui see oleks isiku küljes, aga praegu on ta siin, sest isikut ei tohi puutuda.

@property default_active type=checkbox ch_value=1 rel=1 method=serialize field=meta
@caption Vaikimisi aktiivne

@property friend_groups type=classificator reltype=RELTYPE_FRIEND_GROUPS store=no
@caption Sõbragrupid

------------------------- general : end -----------------------------------

------------------------- settings -----------------------------------

@groupinfo settings caption=Seaded

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

//@groupinfo settings_general caption="Üldandmed" parent=settings
//@default group=settings_general

@property online type=text store=no group=settings
@caption Online

@property age type=text store=no group=settings
@caption Vanus

@property user_field1 type=date_select year_from=1918 year_to=2004 default=-1 group=settings
@caption Sünniaeg

@property user_check1 type=checkbox value=1 ch_value=1 group=settings
@caption E-post varjatud

@property user_check2 type=checkbox value=1 ch_value=1 group=settings
@caption Kinnine postkast

@property user_text1 type=textbox group=settings
@caption Telefon

@property user_text2 type=textbox group=settings
@caption ICQ

@property user_text3 type=textbox group=settings
@caption MSN

@property user_blob1 type=textbox group=settings
@caption Lisainfo

@property user_blob2 type=textbox group=settings
@caption Lisainfo sõpradele

------------------------- üldandmed : end -----------------------------------

------------------------- välimus -----------------------------------

//@groupinfo settings_outlook caption="Välimus" parent=settings
//@default group=settings_outlook

@property height type=classificator reltype=RELTYPE_PRF_HEIGHT orient=vertical group=settings
@caption Kasv

@property weight type=classificator reltype=RELTYPE_PRF_WEIGHT orient=vertical group=settings
@caption Kaal

@property eyes_color type=classificator reltype=RELTYPE_PRF_EYES_COLOR orient=vertical group=settings
@caption Silmade värv

@property hair_color type=classificator reltype=RELTYPE_PRF_HAIR_COLOR orient=vertical group=settings
@caption Juuksevärv

@property hair_type type=classificator reltype=RELTYPE_PRF_HAIR_TYPE orient=vertical group=settings
@caption Juuste tüüp

@property body_type type=classificator reltype=RELTYPE_PRF_BODY_TYPE orient=vertical group=settings
@caption Keha tüüp

------------------------- välimus : end -----------------------------------

------------------------- harrastused -----------------------------------

//@groupinfo settings_hobbies caption="Harrastused" parent=settings
//@default group=settings_hobbies

@property user_text5 type=textbox group=settings
@caption Koduleht

------------------------- harrastused : end -----------------------------------

------------------------- harjumused -----------------------------------

//@groupinfo settings_habits caption="Harjumused" parent=settings
//@default group=settings_habits

@property sexual_orientation type=classificator reltype=RELTYPE_PRF_SEX_ORIENT orient=vertical group=settings
@caption Seksuaalne orientatsioon

@property alcohol type=classificator reltype=RELTYPE_PRF_ALCOHOL orient=vertical group=settings
@caption Alkoholi tarbimine

@property tobacco type=classificator reltype=RELTYPE_PRF_TOBACCO orient=vertical group=settings
@caption Tubaka tarbimine

------------------------- harjumused : end -----------------------------------

------------------------- kool_too -----------------------------------

//@groupinfo settings_occupation caption="Kool/Töö" parent=settings
//@default group=settings_occupation

@property user_field2 type=classificator group=settings orient=vertical 
@caption Haridustase

@property occupation type=classificator table=objects field=meta method=serialize group=settings
@caption Tegevusala

@property user_text4 type=textbox group=settings
@caption Elukutse

------------------------- kool_too : end -----------------------------------

------------------------- friends -----------------------------------

@groupinfo friends caption=Sõbrad

@property friends type=table group=friends no_caption=1
@caption Minu sõbrad

------------------------- friends : end -----------------------------------

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

----------------------

@reltype CFG_MANAGER value=15 clid=CL_CFGMANAGER
@caption Seadete haldur

----------------------

@reltype FRIEND value=20 clid=CL_PROFILE
@caption Sõber

@reltype FAVOURITE value=21 clid=CL_PROFILE
@caption Lemmik

@reltype MATCH value=23 clid=CL_PROFILE
@caption Väljavalitu

@reltype FRIEND_GROUPS value=24 clid=CL_META
@caption Sõbragrupid

----------------------
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
		switch($prop["name"])
		{
			case "friends":
				$this->do_friends_tbl($arr);
				break;

			case "age":
				$prop["value"] = $this->get_age($arr);
				break;

			case "online":
				$prop["value"] = $this->is_online($arr) ? LC_YES : LC_NO;
				break;
		};
		return $retval;
	}
	

	/*
	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
                {

		}
		return $retval;
	}	
	*/

	function is_online($arr)
	{
		if (!$profile = $arr["obj_inst"])
		{
			return NULL;
		}
		
		if ($person = $profile->get_first_obj_by_reltype("RELTYPE_PERSON"))
		{
			if ($c_user_to_person = reset($person->connections_to(array("type" => 2))))
			{
				$user = obj(aw_global_get("uid_oid"));
				$lastaction = $user->prop("lastaction");
				$timeout = 600;
				/*
				$timeout = 600;
				$user = $c_user_to_person->from();
				$uid = $user->prop("uid");
				
				// kui nõuga ei saa, siis võtame jõuga:
				$q = "SELECT online, lastaction FROM users WHERE uid = '$uid'";
				//$q = "SELECT max(tm) as lastaction FROM syslog where uid = '$uid'";
				$this->db_query($q);
				$row = $this->db_next();
				$lastaction = $row["lastaction"];
				//$online = $row["online"];
				*/
				if ((time() - $lastaction) < $timeout)
				{
					return true;
				}
				return false;
			}
		}
		return NULL;
	}

	function make_birthday($var)
	{
		//19840719
		if(!strlen($var)<8)
		{
			return "";
		}
		$birthday = $var{7}.$var{6}.".".$var{5}.$var{4}.".".$var{3}.$var{2}.$var{1}.$var{0};
		return $birthday;
	}

	function get_age2($var)
	{
		if($var == 0)
		{
			return 0;
		}
		$v = $var;
		$m = $v{4}.$v{5};
		$d = $v{6}.$v{7};
		$y = $v{0}.$v{1}.$v{2}.$v{3};
		$var = mktime(0,0,0,$m,$d,$y);
		$age = "";
		if($var == -1)
		{
			return $age;
		}
		$birthday_rec = getdate($var);
		$now_rec = getdate();
		$age = $now_rec["year"] - $birthday_rec["year"];
		if ($now_rec["mon"] < $birthday_rec["mon"])
		{
			$age--;
		}
		elseif ($now_rec["mon"] == $birthday_rec["mon"])
		{
			if ($now_rec["mday"] <= $birthday_rec["mday"])
			{
				$age--;
			}
		}
		return $age;
	}

	function get_age($arr)
	{
		$age = NULL;
		if (!isset($arr["obj_inst"]))
		// saidiga liitumisel juhtub, et seda ei ole, sest salvestatud pole veel ühtegi profiili, kuid
		// juba hakatakse (tühje) profiili välju näitama. Kuigi 'age' propertit ei tohiks näidata seal.
		{
			return $age;
		}
		
		if ($person = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_PERSON"))
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
			elseif ($now_rec["mon"] == $birthday_rec["mon"])
			{
				if ($now_rec["mday"] <= $birthday_rec["mday"])
				{
					$age--;
				}
			}
		}
		return $age;
	}

	function add_friend($vars)
	{
		$arr = $vars["arr"];
		$friends_profile = $vars["friends_profile"];
		
		$arr["obj_inst"]->connect(array(
			"to" => $friends_profile,
			"reltype" => "RELTYPE_FRIEND",
		));
	}
	
	function do_friends_tbl($arr)
	{
		$prop = &$arr["prop"];
		$t = &$prop["vcl_inst"];
		
		$t->define_field(array(
			"name" => "id",
			"caption" => "ID",
			"sortable" => "1",
		));
		$t->define_field(array(
			"name" => "image",
			"caption" => "Pilt",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"sortable" => "1",
		));
		$t->define_field(array(
			"name" => "profile",
			"caption" => "Profiil",
		));

		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));

		//siin on viga, kui kutsutakse välja teisest klassist, et seal tabelit joonistada
		$cons_to_friends = $arr['obj_inst']->connections_from(array(
			"type" => 'RELTYPE_FRIEND',
		));
		
		foreach($cons_to_friends as $conn)
		{
			/*
			$actcheck = checked($row["id"] == $active);
			$act_html = "<input type='radio' name='activeperiod' $actcheck value='$row[id]'>";
			$row["active"] = $act_html;
			$table->define_data($row);
			*/
			
			$profile = $conn->to();
			if ($image_c = $profile->get_first_conn_by_reltype('RELTYPE_IMAGE'))
			{
				//$image_i = $image_o->instance();
				$i = get_instance(CL_IMAGE);
				$img_url = $i->get_url_by_id($image_c->prop('to'));
			}
			else
			{
				$img_url = "http://epood.primeframe.ee/img/products/puudub.gif";
			}

			//$i = get_instance(CL_IMAGE);
			//$imgdata = $i->get_image_by_id($this->image_data->id());
			
			//ainus võimalus saada kätte sõbra profiili isik on seos sõbra_profiil->sõbra_isik
			//$user = $this->get_user();
			if ($person = $this->get_person_for_profile($profile))
			{
				$name = $person->prop('name');
			}
			else
			{
				$name = '';
			}
			
			$item =& $profile;
			$t->define_data(array(
				"id" => $item->id(),
				"image" => html::img(array(
						"url" => $img_url,
						"width" => "80",
						"height" => "100",
						"border" => "0",
				)),
				"name" => $name,
				"profile" => html::href(array(
					"url" => $this->mk_my_orb("view",array("id" => $item->id()), CL_PROFILE),
					//"group" => "friends",
					"caption" => "Vaata profiili",
				)),
			));
		}
	}
	
	// et leida persoon ka siis, kui otseühendust (profile -> crm_person) ei eksisteeri
	//UPDATE! sellist olukorda ei tohi lasta tekkida! ühendus PEAB olema mõlemat pidi.
	function get_person_for_profile($profile)
	{
		if (!$person = $profile->get_first_obj_by_reltype("RELTYPE_PERSON"))
		{
			//seost (profiil -> isik) ei olnud, otsin vastupidist (isik -> profiil):
			$conns = $profile->connections_to(array(
				"type" => 14, //RELTYPE_PROFILE, //"RELTYPE_PROFILE", 
				//paistab et connections_from toetab stringe, connections_to ei toeta
			));
			if (sizeof($conns) > 0)
			{
				//leidsime vastupidise sose
				$conn_to_profile = reset($conns);
				$person = $conn_to_profile->from();
			}
			//return false; //ei tee seda sest $person == false enivei.

		}
		return $person;
	}
}
?>
