<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/commune/Attic/profile.aw,v 1.3 2004/08/25 07:13:38 ahti Exp $
// profile.aw - Profiil 
/*


@classinfo syslog_type=ST_PROFILE relationmgr=yes

@groupinfo settings caption=Seaded
@groupinfo comments caption=Kommentaarid
@groupinfo friends caption=Sõbrad

@default group=general
@default table=objects


// ------------------------------------------------------------
// general (Üldine) tabi alla asuvad asjad
// ------------------------------------------------------------
@property cfgmanager type=relpicker reltype=RELTYPE_CFG_MANAGER method=serialize table=objects field=meta group=general
@caption Seadete haldur

@property avatar_image type=relpicker reltype=RELTYPE_CFG_MANAGER method=serialize table=objects field=meta group=general
@caption Avatar

//isikul peaks olema üks profiil, mis on vaikimisi aktiivne, mida sisse logimisel esimesena näidatakse
//lihtsam oleks, kui see oleks isiku küljes, aga praegu on ta siin, sest isikut ei tohi puutuda.
@property default_active type=checkbox ch_value=1 rel=1 group=general method=serialize table=objects field=meta
@caption Vaikimisi aktiivne
-------------------------------------------------------

@property friend_groups type=classificator reltype=RELTYPE_FRIEND_GROUPS store=no group=general
@caption Sõbragrupid


// ------------------------------------------------------------
// setting (Seaded) tabi all asuvad asjad
// ------------------------------------------------------------
@default group=settings
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


@groupinfo settings_yldandmed caption="Üldandmed" parent=settings
@groupinfo settings_valimus caption="Välimus" parent=settings
@groupinfo settings_harrastused caption="Harrastused" parent=settings
@groupinfo settings_harjumused caption="Harjumused" parent=settings
@groupinfo settings_kool_too caption="Kool/Töö" parent=settings

@default group=settings_yldandmed
@property online type=text store=no
@caption Online

@property age type=text store=no 
@caption Vanus

@property user_field1 type=date_select year_from=1918 year_to=2004 default=-1
@caption Sünniaeg

@property user_check1 type=checkbox value=1 ch_value=1 
@caption E-post varjatud

@property user_check2 type=checkbox value=1 ch_value=1
@caption Kinnine postkast

@property user_text1 type=textbox 
@caption Telefon

@property user_text2 type=textbox
@caption ICQ

@property user_text3 type=textbox
@caption MSN

@property user_blob1 type=textbox
@caption Lisainfo

@property user_blob2 type=textbox
@caption Lisainfo sõpradele


@default group=settings_valimus

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


@default group=settings_harrastused

@property user_text5 type=textbox
@caption Koduleht


@default group=settings_harjumused

@property sexual_orientation type=classificator reltype=RELTYPE_PRF_SEX_ORIENT orient=vertical 
@caption Seksuaalne orientatsioon

@property alcohol type=classificator reltype=RELTYPE_PRF_ALCOHOL orient=vertical  
@caption Alkoholi tarbimine

@property tobacco type=classificator reltype=RELTYPE_PRF_TOBACCO orient=vertical 
@caption Tubaka tarbimine


@default group=settings_kool_too

@property user_field2 type=classificator 
@caption Haridustase

@property occupation type=classificator field=meta method=serialize table=objects
@caption Tegevusala

@property user_text4 type=textbox
@caption Elukutse


// ------------------------------------------------------------
// friends (Sõbrad) tabi all asuvad asjad
// ------------------------------------------------------------
//@property friends type=callback callback=callback_friends group=friends
//@caption Sõbrad 

@property friends_tbl type=table group=friends no_caption=1
@caption Minu sõbrad


// ------------------------------------------------------------
// comments (Kommentaarid) tabi all asuvad asjad
// ------------------------------------------------------------
@property comments type=comments group=comments store=no
@caption Kommentaarid


----------------RELTYPES----------------------------------------------
// this I do not need
@reltype TASK_COMMENT value=1 clid=CL_COMMENT
@caption Kommentaar

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

----------------------

@reltype CFG_MANAGER value=15 clid=CL_CFGMANAGER
@caption seadete haldur
----------------------
@reltype FRIEND value=20 clid=CL_PROFILE
@caption sõber

@reltype FAVOURITE value=21 clid=CL_PROFILE
@caption lemmik

@reltype MATCH value=23 clid=CL_PROFILE
@caption väljavalitu

@reltype FRIEND_GROUPS value=24 clid=CL_META
@caption Sõbragrupid

----------------------
*/

class profile extends class_base
{
	function profile()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "profile/profile",
			"clid" => CL_PROFILE
		));
	}
		

	function callback_on_load($arr)
	{
		//echo ' profile::callback_on_load ';
		/*Array
		(
		    [request] => Array
		        (
		            [class] => profile
		            [action] => change
		            [id] => 1310
		        )
		)
		*/
		
		
		//profiili loomisel tuleb see seos ka tekitada
		//võtab kommuunilt seose.. aga kuidas? - profiil ei ole kuidagi kommuuniga seotud
		
		//$this->cfgmanager = 701;
	}

	function callback_pre_edit($arr)
	{
		//echo ' profile::callback_pre_edit ';
	}
	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "friends_tbl":
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
				// siin on viga! prop("lastaction") ei vasta tõele - põhjus teadmata..
				//debug:
				/*get_lc_date
				$users = get_instance("users");
				$user = new object($users->get_oid_for_uid(aw_global_get("uid")));
				echo ($user->prop("lastaction")); //1088851544   03. juuli 04   WTF?!!
				*/
				
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
				
				if ((time() - $lastaction) < $timeout)
				{
					return TRUE;
				}
				return FALSE;
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
			"name" => "selected",
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
