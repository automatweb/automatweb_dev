<?php
// $Header: /home/cvs/automatweb_dev/const.aw,v 2.61 2002/03/12 23:14:08 duke Exp $
// ---------------------------------------------------------------------------
// (C) O‹ Sruktuur Meedia 2000,2001
// ---------------------------------------------------------------------------

// aw version - [major release].[minor release].[fix number]
define("AW_VERSION","2.0.0");

// here we define basic constants needed by all components
set_magic_quotes_runtime(0);

$pi = "";

if ( isset($PATH_INFO) && (strlen($PATH_INFO) > 1))
{
	$pi = $PATH_INFO;
};
if ( isset($QUERY_STRING) && (strlen($QUERY_STRING) > 1))
{
	$pi .= "?".$QUERY_STRING;
};

global $uid;
$uid1 = $HTTP_SESSION_VARS["uid"];

if ($pi) 
{
	$section = sprintf("%d",substr($pi,1));
	if (preg_match("/[&|=]/",$pi)) 
	{
		// expand and import PATH_INFO
		parse_str(str_replace("?","&",str_replace("/","&",$pi)));
	} 
	else 
	{
		$section = substr($pi,1);
	};
};

// support for crypted urls
if (isset($__udat))
{
	$l = strlen($__udat);
	$ret = "";
	for ($i=0; $i < $l; $i+=2)
	{
		$ret.= chr(hexdec($__udat[$i].$__udat[$i+1]));
	};
	parse_str($ret,$AW_GET_VARS);
	extract($AW_GET_VARS);
}

// NEVER EVER accept and UID argument from url
if ($uid != $uid1)
{
	$uid = $uid1;
};


// right. here comes the error handler! badaboom!
/*function handle_errors($errno, $errmsg, $filename, $linenum,$vars)
{
	// format a nice message and pass it on to core::raise_error
	$errortype = array (
                1   =>  "Error",
                2   =>  "Warning",
                4   =>  "Parsing Error",
                8   =>  "Notice",
                16  =>  "Core Error",
                32  =>  "Core Warning",
                64  =>  "Compile Error",
                128 =>  "Compile Warning",
                256 =>  "User Error",
                512 =>  "User Warning",
                1024=>  "User Notice"
	);
	$not_errors = array(8,2);
	if (!in_array($errno,$not_errors))
	{
		echo "$errno $errmsg $filename $linenum <br>";
		$msg = "PHP ERROR on line $linenum of file $filename: $errmsg (".$errortype[$errno]."). \n Variables: ";
		if (is_array($variables))
		{
			foreach($variables as $k => $v)
			{
				$msg.="$k = $v \n";
			}
		}
		classload("core","aw_template");	
		$co = new core;
		$co->db_init();
		$co->raise_error($msg,false,true);
	}
}*/
//error_reporting(0);
//set_error_handler("handle_errors");

// ---------------------------------------------------------------------------
// do we need that line here?
$tpldirs["stat.struktuur.ee"] = "/www/stat/templates";

if (empty($driver))
{
	$driver = "mysql"; 			// driver
};
if (empty($db_user))
{
	$db_user = "automatweb"; 		// username to connect to the database
	$db_pass = "roobert"; 			// password for the database
	$db_host = "hell"; 			// host to connect to
	$db_base = "automatweb";		// base to use
};
$ext = "aw"; 		          	// filename extension

// $HTTP_HOST pohjal vaatame, millisest kohast nˆ "public"
// templated vıtta
if (empty($tpldirs))
{
	$tpldirs["ebs.elkdata.com"] = $sitedir . "/../templates";
}

// temp kataloogi asukoht. nuh et kui kunagi asi windowsi peal k2ima hakkab, siis saax selle siit 2r muuta.
$tmpdir = "/tmp";

// saidi siseste linkide jaox
if (empty($index_file))
{
	$index_file = "index";
}

if (!defined("AW_PATH"))
{
	define("AW_PATH","/www/automatweb_dev");
};

// kasutatakse saidi juurde kuuluvate objektide idenfitiseerimiseks
if (empty($SITE_ID)) 
{
	$SITE_ID = 1;
};

// Saidi tiitel. Uues menueditis (html) voiks selle ju frameseti tiitliks panna?
if (empty($stitle)) 
{
	$stitle = "Autom@tWeb";
};


if (empty($amenustart)) 
{
	$amenustart = 20;
};

if (empty($rootmenu)) 
{
	$rootmenu = 1;
};

if (empty($admin_rootmenu)) 
{
	$admin_rootmenu = 1;
};

if (empty($admin_rootmenu2)) 
{
	$admin_rootmenu2 = 1;
};

// kui saidi const.aw ei defineeri seda, siis m‰‰rame default v‰‰rtuse
if (empty($basedir))
{
	$basedir = "/www/apache/domains/sam.elkdata.com/htdocs/automatweb_dev"; 								// the root of all evil ;)
	$convert_dir = "/usr/X11R6/bin/convert";
	// piltide identimisex kasutatav proge
	$identify_dir = "/usr/bin/X11/identify";
	// kus zipide lahtipakkija asub
	$unzip_path = "/usr/local/bin/unzip";

	if (!file_exists($basedir."/COPYING"))
	{
		$basedir = "/www/automatweb_dev";
		// koht, kus asub ikoonide convertimisex kasutatav proge
		$convert_dir = "/usr/X11R6/bin/convert";
		// piltide identimisex kasutatav proge
		$identify_dir = "/usr/bin/X11/identify";
		// kus zipide lahtipakkija asub
		$unzip_path = "/usr/bin/unzip";
	}
};

if (empty($site_basedir))
{
	// kui pole defineeritud, siis paneme defauldiks saidi baaskataloogi et seal v2hemalt midagi oleks. 
	$site_basedir = $basedir;
}

// keemia. Kui oleme saidi adminnis sees, siis votame
// templated siit
if (strpos($PHP_SELF,"automatweb")) 
{
	$tpldir = $basedir . "/templates";
// kui saidi "sees", siis votame templated tolle saidi juurest
} 
else 
{
	if (empty($tpldir))
	{
		$tpldir = $tpldirs[$HTTP_HOST];
	};
};

if ($HTTP_HOST == "test.kirjastus.ee")
{
	$baseurl = "http://test.kirjastus.ee"; 	// base url of the site
}
else
{
	if (empty($baseurl))
	{
		$baseurl = "http://www.kirjastus.ee";
	};
};
$classdir = $basedir."/classes";	 	  // where the classes are
// veateadete tekstid

if (empty($LC))
{
	$LC="et";
}

@include("$basedir/lang/" . $LC . "/errors.aw");
@include("$basedir/lang/" . $LC . "/common.aw");

$cachedir = $basedir . "/cache"; 		  // where the file cache is

if (isset($menu_defs) && !is_array($menu_defs))
{
	$menu_defs = array();
}


// sellest saab lahti, kui igalpool include("const.aw") asendada include_once("const.aw")-ga
if (function_exists("classload")) 
{
} 
else 
{
	// loads localization constants
	function lc_load($file)
	{
		global $basedir;
		global $ext,$LC,$admin_lang_lc;
		if (!$admin_lang_lc)
		{
			$admin_lang_lc = "et";
		}
		@include_once("$basedir/lang/" . $admin_lang_lc . "/$file.$ext");
	}

	// loads localization constants from the site's $site_basedir
	function lc_site_load($file,&$obj)
	{
		global $site_basedir;
		global $ext,$LC;
		@include_once("$site_basedir/lang/" . $LC . "/$file.$ext");
		if ($obj)
		{
			// kui objekt anti kaasa, siis loeme tema template sisse muutuja $lc_$file 
			$var = "lc_".$file;
			global $$var;
			if (is_array($$var))
			{
				$obj->vars($$var);
			}
		}
	}

	// nyyd on voimalik laadida ka mitu librat yhe calliga 
	// a la classload("users","groups","someothershit");
	function classload($args)
	{
		$arg_list = func_get_args();
		global $classdir;
		global $ext;
		while(list(,$lib) = each($arg_list))
		{
			// votab stringist ainult selle osa, mis j‰‰b /-i ja stringi lopu vahele
			// v‰ltimaks katseid laadida faile /etc/passwd
			preg_match("/(\w*)$/",$lib,$m);
			$lib = $m[1];
			$lib = "$classdir/$lib.$ext";
			include_once($lib);
		};
	}
	////
	// !A neat little functional programming function
	function not($arg)
	{
		return !$arg;
	}

	function sysload($lib)
	{
		classload($lib);
	}

	function load_vcl($lib)
	{
		global $basedir;
		global $ext;
		include_once("$basedir/vcl/$lib.$ext");
	}
};

// other stuff

// stat function fields
define("FILE_SIZE",7);
define("FILE_MODIFIED",9);

// millisele aadressile saadetakse Alerdid (ebaonnestunud sisselogimised, jne).
define("ALERT_ADDR","log@struktuur.ee");

// mis alerdi subjectiks pannakse
define("ALERT_SUBJECT","%s - Jerk alert!");

// ja mis aadress alerdi From reale pannakse
define("ALERT_FROM","From: AK veebiserver <nobody@www.kirjastus.ee>");

// klasside nimed
define("CL_PSEUDO",1);
define("CL_FORM",2);
define("CL_TABLE",3);
define("CL_CATEGORY",4);
define("CL_PLACE",5);
define("CL_IMAGE",6);
define("CL_DOCUMENT",7);
define("CL_FORM_ENTRY",8);
define("CL_FORM_CATEGORY",9);
define("CL_FORM_ELEMENT",10);
define("CL_STYLE",11);
define("CL_FORM_OUTPUT",12);
define("CL_FILLED_FORM_FOLDER",13);
define("CL_FORM_ACTION",14);

define("CL_MAILINGLIST",15);
define("CL_MAILINGLIST_CATEGORY",16);
define("CL_MAILINGLIST_MEMBER",17);
define("CL_MAILINGLIST_VARIABLE",18);
define("CL_MAILINGLIST_STAMP",19);
define("CL_EMAIL",20);

define("CL_EXTLINK",21);

define("CL_PROMO",22);
define("CL_PROMO_ARTICLE",23);

define("CL_ML_VAR_CAT",24);
define("CL_MAIL_LINK",25);
define("CL_MAIL",26);
define("CL_MAIL_FOLDER",27);

define("CL_GRAPH",28);
define("CL_PERIODIC_SECTION",29);
//define("CL_SECTION_LINK",30);	// deprecated or something?
define("CL_GALLERY",31);
define("CL_POLL",33);
define("CL_MSGBOARD_TOPIC",34);
define("CL_NAGU",35);
define("CL_DEF_SETTINGS",36); // default doc .. ehk default settingud 
														// uute dokude jaoks
define("CL_GROUP",37);
define("CL_USER_GROUP",38);
define("CL_BROTHER",39);
define("CL_BROTHER_DOCUMENT",40);
define("CL_FILE",41);

define("CL_TEST_QUESTION",42);
define("CL_TEST_TEEMA",43);
define("CL_TEST",44);

define("CL_FORM_XML_OUTPUT",45);
define("CL_FORM_XML_INPUT",46); // XML sisend .. yle xml-rpc muutmiseks

define("CL_EVENT",47); // Vibe event

define("CL_GUESTBOOK",49);
define("CL_GUESTBOOK_ENTRY",50);

// module access control objects
define("CL_ACCESSMGR",51);

// messengeri teade
define("CL_MESSAGE",52);

// kalendri event
define("CL_CAL_EVENT",53);

define("CL_BANNER",54);
define("CL_BANNER_CLIENT",55);
define("CL_BANNER_PROFILE",56);	// this actually specifies the location of the banner. legacy code sucks. what can I do.
define("CL_BANNER_BUYER",57);		// well, these are actual clients that buy banner impressions
define("CL_BANNER_SITE",58);		// this is just for grouping banner locations, so we can view the statistics for the site.

define("CL_CALENDAR",59); // kalendri objekt

define("CL_SHOP",60); // pood. w00t!
define("CL_SHOP_ITEM",61); // kaup. poes. duh. 
define("CL_SHOP_STATS",62); // poodide statistika vaatamine k2ib selle kaudu

define("CL_CONTACT_GROUP",63); // kontaktigrupp

define("CL_SHOP_ITEM_TYPE",64); // poe kaupa tyyp
define("CL_SHOP_EQUASION",65);  // poe kauba hinna arvutamise valem

define("CL_FORM_TABLE",66);  // otsinguformi tulemuste kuvamise tabel

define("CL_CURRENCY",67);  // otsinguformi tulemuste kuvamise tabel

define("CL_FORM_CHAIN",68);  // formi p2rg
define("CL_FORUM",69); // 68 sest siis ei teki probleeme kui dev versioon siia ymber t6sta, mida ma p2rast teen ka, aga pearu ei j6ua

define("CL_OBJECT_VOTE",70); // mingi tunnuse alusesl grupeeritud objektide poolt h‰‰letamine

define("CL_CSS",71); // CSS objekt

define("CL_ML_LIST",72);
define("CL_ML_MEMBER",73);
define("CL_ML_STAMP",74);
define("CL_SHOP_TABLE",75);
define("CL_CSS_GROUP",76); // Oh yeah, I know it should be next to CL_CSS

define("CL_ML_RULE",77);//ıumaigaad, someone has taken 75:)

define("CL_SEARCH_FILTER",78);

define("CL_TEMPLATE",79); // metaobjekt templatefaili jaoks, used to keep track of changes
				// and archives
define("CL_HTML_POPUP",80); // html popup
define("CL_LINK_COLLECTION",81); // lingikogu (dokualiase jaoks)

define("CL_KEYWORD",82); // for keywords
define("CL_KEYWORD_DB", 83); // keywordide andmebaas

define("CL_MENU_CHAIN",84); // men¸¸de p‰rg

define("CL_PULLOUT",85); // pullout - promo kast doku sees

define("CL_OBJECT_CHAIN",86); // objektide p2rg - acli jaoks
define("CL_ROLE",87); // roll - acli jaoks
define("CL_ACL",88); // acli - seob objekti p2rga, rolli ja gruppe

define("CL_MENU_ALIAS",89); // men¸¸ alias .. doku/tabeli sisse men¸¸ kutsumiseks

define("CL_AW_LOGIN",90); // login objekt
define("CL_AW_TEST",91); // testkomplekt

define("CL_CHAIN_ENTRY",92);	// formi p2rja sisestuse objekt

// can_add m‰‰rab ‰ra kas, seda klassi n‰idatakse Lisa listboxis

// nimekiri k6ikidest klassidest ikoonide jaox
$class_defs = array(	CL_PSEUDO => array("name" => LC_CONST_MENU,"file" => "menuedit","can_add" => 0),
			CL_DOCUMENT => array("name" => LC_CONST_DOCUMENT, "file" => "document", "can_add" => 1), 
			CL_BROTHER_DOCUMENT => array("name" => LC_CONST_DOCUMENT_BROTH, "file" => "document", "can_add" => 1), 
			CL_FORM		=> array("name" => LC_CONST_FORM, "file" => "form","can_add" => 1),
			CL_SHOP => array("name" => LC_CONST_SHOP, "file" => "shop", "can_add" => 1),
			CL_SHOP_ITEM => array("name" => LC_CONST_GOODS_ART, "file" => "shop_item", "can_add" => 1),
			CL_TABLE	=> array("name" => LC_CONST_TABLE, "file" => "table","can_add" => 1), 
			CL_IMAGE	=> array("name" => LC_CONST_PICT, "file" => "image", "can_add" => 1), 
			CL_FORM_ENTRY => array("name" => LC_CONST_FORM_ENTRY, "file" => "form_entry"),
			CL_FORM_ELEMENT => array("name" => LC_CONST_FORM_ELEMENT, "file" => "form_element_vis"),
			CL_STYLE	=> array("name" => LC_CONST_STYLE, "file" => "style","class" => "style", "can_add" => 1), 
			CL_FORM_OUTPUT => array("name" => LC_CONST_FORM_OUTPUT, "file" => "form_output","can_add" => 1),
			CL_FORM_ACTION => array("name" => LC_CONST_FORM_ACTION, "file" => "forms"),
			CL_MAILINGLIST => array("name" => LC_CONST_MAILLIST, "file" => "lists","can_add" => 0),
			CL_MAILINGLIST_MEMBER => array("name" => LC_CONST_LIST_MEMBER, "file" => "list"),
			CL_MAILINGLIST_VARIABLE => array("name" => LC_CONST_LIST_VARIABLE, "file" => "variables","can_add" => 0),
			CL_MAILINGLIST_STAMP => array("name" => LC_CONST_LIST_STAMP, "file" => "list","can_add" => 0), 
			CL_EMAIL => array("name" => LC_CONST_LIST_MAIL, "file" => "list","can_add" => 0),
			CL_EXTLINK => array("name" => LC_CONST_LINK, "file" => "links","can_add" => 1),
			CL_MAIL_LINK => array("name" => LC_CONST_EMAIL_LINK, "file" => "extrlinks","can_add" => 0),
			CL_MAIL => array("name" => LC_CONST_EMAIL, "file" => "mail","can_add" => 0),
			CL_GRAPH => array("name" => LC_CONST_GRAPH, "file" => "graph","can_add" => 1),
			CL_PERIODIC_SECTION => array("name" => LC_CONST_DOCUMENT_P, "file" => "document","can_add" => 0),
	//		CL_SECTION_LINK => array("name" => LC_CONST_SECTION_LINK, "file" => "links"),
			CL_GALLERY => array("name" => LC_CONST_GALLERY, "file" => "gallery","can_add" => 1),
			CL_POLL => array("name" => LC_CONST_POLL, "file" => "poll","can_add" => 0),
			CL_MSGBOARD_TOPIC => array("name" => LC_CONST_BOARD_TOPIC, "file" => "board"),
			CL_NAGU => array("name" => LC_CONST_WEEK_FACE,"file" => "nagu"),
			CL_GROUP => array("name" => LC_CONST_GROUP, "file" => "groups","can_add" => 0), 
			CL_USER_GROUP => array("name" => LC_CONST_USER,"file" => "users","can_add" => 0),
			CL_FILE => array("name" => LC_CONST_FILE,"file" => "file","can_add" => 1),
			CL_GUESTBOOK => array("name" => LC_CONST_GUESTBOOK,"file" => "guestbook","can_add" => 1),
			CL_CALENDAR => array("name" => LC_CONST_CALENDER,"file" => "planner","can_add" => 1),
			CL_BANNER => array("name" => LC_CONST_BANNER, "file" => "banner", "can_add" => 1),
			CL_BANNER_CLIENT => array("name" => LC_CONST_BANNER_LOCATION, "file" => "banner_client", "can_add" => 1),
			CL_BANNER_PROFILE => array("name" => LC_CONST_BANNER_PROFILE, "file" => "banner_profile", "can_add" => 1),
			CL_BANNER_BUYER => array("name" => LC_CONST_BANNER_CLIENT, "file" => "banner_buyer", "can_add" => 1),
			CL_BANNER_SITE => array("name" => LC_CONST_BANNER_SITE, "file" => "banner_site", "can_add" => 1),
			CL_MESSAGE => array("name" => LC_CONST_MESSAGE, "file" => "messenger", "can_add" => 0),
			CL_SHOP_STATS => array("name" => LC_CONST_SHOP_STAT, "file" => "shop_stat", "can_add" => 1),
			CL_SHOP_ITEM_TYPE => array("name" => LC_CONST_GOODS_TYPE, "file" => "item_type", "can_add" => 1),
			CL_SHOP_EQUASION => array("name" => LC_CONST_GOODS_PRICE_FORMULA, "file" => "shop_eq", "can_add" => 1),
			CL_FORM_TABLE => array("name" => LC_CONST_FORM_TABLE, "file" => "form_table", "can_add" => 1),
			CL_CURRENCY => array("name" => LC_CONST_RATE_OF_EXCHANGE, "file" => "currency", "can_add" => 1),
			CL_FORM_CHAIN => array("name" => LC_CONST_FORM_WREATH, "file" => "form_chain", "can_add" => 1),
			CL_PROMO => array("name" => LC_CONST_PROMOBOX, "file" => "promo", "can_add" => 0),
			CL_FORM_XML_OUTPUT => array("name" => "XML v‰ljund", "file" => "form_output", "can_add" => 0),
			CL_FORM_XML_INPUT => array("name" => "XML sisend", "file" => "form_input", "can_add" => 1),
			CL_FORUM => array("name" => "Foorum", "file" => "forum", "can_add" => 1),
			CL_OBJECT_VOTE => array("name" => "Objektide h‰‰letus","file" => "object_vote","can_add" => 1),
			CL_CSS => array("name" => "CSS stiil","file" => "css","can_add" => 1),
			CL_ML_LIST => array("name" => "Meililist","file" => "ml_list","can_add" => 1),
			CL_ML_MEMBER => array("name" => "Meililisti liige","file" => "ml_member","can_add" => 1),
			CL_ML_STAMP => array("name" => "Meililisti stamp","file" => "ml_stamp","can_add" => 1),
			CL_ML_RULE => array("name" => "Meililisti ruul","file" => "ml_rule","can_add" => 1),
			CL_SHOP_TABLE => array("name" => "Kaupade tabel", "file" => "shop_table", "can_add" => 1),
			CL_SEARCH_FILTER => array("name" => "Otsimise filter", "file" => "search_filter", "can_add" => 1),
			CL_HTML_POPUP => array("name" => "HTML popup", "file" => "html_popup", "can_add" => 1),
			CL_KEYWORD => array("name" => "AW vıtmesına", "file" => "keywords", "can_add" => 1),
			CL_KEYWORD_DB => array("name" => "V&otilde;tmes&otilde;nade baas", "file" => "keyword_db", "can_add" => 1),
			CL_MENU_CHAIN => array("name" => "Men¸¸p‰rg", "file" => "menu_chain", "can_add" => 1),
			CL_PULLOUT => array("name" => "Pullout", "file" => "pullout", "can_add" => 1),
			CL_OBJECT_CHAIN => array("name" => "Objektip&auml;rg", "file" => "object_chain", "can_add" => 1),
			CL_ROLE => array("name" => "Roll", "file" => "role", "can_add" => 1),
			CL_ACL => array("name" => "ACL", "file" => "acl_class", "can_add" => 1),
			CL_LINK_COLLECTION => array("name" => "Lingikogu", "file" => "link_collection", "can_add" => 0),
			CL_CAL_EVENT => array("name" => "Kalendri event", "file" => "cal_event", "can_add" => 1),
			CL_AW_LOGIN => array("name" => "AW login", "file" => "remote_login", "can_add" => 1),
			CL_AW_TEST => array("name" => "AW test", "file" => "aw_test", "can_add" => 1),
			CL_CHAIN_ENTRY => array("name" => "P&auml;rja sisestus", "file" => "form_chain", "can_add" => 0)
);
// kliendid. 
// hierarhia esimene element on root
//  teisel tasemel on kliendid
//  naiteks "Ajakirjade Kirjastus"
//  voi "StruktuurMeedia"

// esimesele tasemele saab lisada ainult kliente
// teisele tasemele kliente voi dokumente

// mix 69? well mulle meeldib see number :-P
define("CL_CLIENT",69);

// menyyd

// eventi folder
define("MN_EVENT_FOLDER",68);

define("MN_CLIENT",69);
// sisurubriik
define("MN_CONTENT",70);
// adminni ylemine menyy
define("MN_ADMIN1",71);
// adminni dokumenty
define("MN_ADMIN_DOC",72);

// promo kast
define("MN_PROMO_BOX",73);

// kodukataloog
define("MN_HOME_FOLDER",74);
// kodukataloogi alla tehtud kataloog, et sharetud katalooge olex lihtsam n2idata
define("MN_HOME_FOLDER_SUB",75);

// formi element, mis on samas ka menyy
define("MN_FORM_ELEMENT",76);

define("MN_ML_LIST",77);

// nini. siin tuleb siis nyyd see koht, kus on kirjas k6ik erinevad "alamprogrammid" , mis aw sees olemas on
// mix nii? well, sest neile peab saama ikoone m22rata ja neid uude menyyeditori teha.
// oid v2li on sellex, et sinna tuleb panna kirja see objekt, mille kaudu neile 6igusi jagataxe.
// st et seda praegu pole veel, aga see tyulex vist siia panna ju ?
// welp, seda objekti ei pea siia kirja panema k2sici, see genereeritaxe automaagiliselt. 
// eh-puh. symboolsed konstandid siis ka progedele
define("PRG_MENUEDIT",1);
define("PRG_DOCLIST",2);
define("PRG_USERS",3);
define("PRG_GROUPS",4);
define("PRG_CONFIG",5);
define("PRG_LANG",6);
define("PRG_BUGTRACK",7);
define("PRG_FORMGEN",8);
define("PRG_GRAPH",9);
define("PRG_FACE",10);
define("PRG_POLL",11);
define("PRG_SEARCH",12);
define("PRG_PERIODS",13);
define("PRG_TESTS",14);
define("PRG_LISTS",15);
define("PRG_VARS",16);
define("PRG_STAMPS",17);
define("PRG_CODESTAT",18);
// 19 used to be PRG_AWMAIL, which is obsolete now. Feel free to grab it
define("PRG_QUIZ",20);
define("PRG_EVENTS",21);
define("PRG_EVENT_PLACES",22);
define("PRG_GALERII",23);
// 24 and 25 were used for the old Kroonika, both are free now
define("PRG_JOINFORM",26);
define("PRG_ICONDB",27);
define("PRG_CLASS_ICONS",28);
define("PRG_FILE_ICONS",29);
define("PRG_PROGRAM_ICONS",30);
define("PRG_OTHER_ICONS",31);
define("PRG_IMPORT_ICONS",32);
define("PRG_EXPORT_ICONS",33);
define("PRG_ACCESSMGR",34);
define("PRG_BANNERS",35);
define("PRG_SEARCH_OBJS",36);
define("PRG_SITE_BANNER_ADMIN",37);
define("PRG_SITE_BANNER_STATS",38);
define("PRG_BANNER_USERS",39);
define("PRG_BANNER_PROFILES",40);
define("PRG_DOCEDIT",41);
define("PRG_KEYWORD",42);
define("PRG_CONF_JOIN_MAIL",43);
define("PRG_CSS_EDITOR",44);
define("PRG_LOGIN_MENU",45); // vastavalt grupile login men¸¸ m‰‰ramine
define("PRG_ML_MANAGER",47);
define("PRG_CONFIG_ERRORS",48);	// sisselogimist vajavate veateadete konfimine
define("PRG_CSS_SYS_EDITOR",49); // s¸steemsete stiilide editor
define("PRG_SYSCONF",50); // konfiguratsioonieditor
define("PRG_CONFIG_REDIRECT",51);	// sisselogimist vajavate veateadete konfimine
define("PRG_TPLEDIT",52); // templateeditor
define("PRG_DOCMGR",53); // document manager
define("PRG_HTML_POPUP",54); // html popip
define("PRG_CONFIG_SITES",55);// saitide tegemine
define("PRG_CONFIG_DOCFOLDERS",56); // dokumendi liigutamsie kataloogide vailimine
define("PRG_CONFIG_FORMS",57);	// formi elementide tyypide config
define("PRG_AUTOMATED_TEST",58); // interface for automated test suite
define("PRG_AIP_PDF", 59);	// aipi jaoks pdf failide upload / automaatne synkro
define("PRG_BACKUP", 60); // baasist / saidist backupi tegemise progis
define("PRG_SITE_EXPORT",61); // saidist staatilise koopia tegemine

// MN_* konstandid on defineeritud $basedir/lang/$lc/common.aw sees
// $lc = keelekood, vaikimisi "ee"
$programs = array(
PRG_MENUEDIT					=> array("name" => MN_MENUEDIT,					"url" => "menuedit.html"),
PRG_DOCLIST						=> array("name" => MN_DOCLIST,					"url" => "orb.$ext?class=docmgr&action=search"),
PRG_USERS							=> array("name" => MN_USERS,						"url" => "orb.$ext?class=users&action=gen_list"),
PRG_GROUPS						=> array("name" => MN_GROUPS,						"url" => "orb.$ext?class=groups&action=mk_grpframe&parent=0"),
PRG_CONFIG						=> array("name" => MN_CONFIG,						"url" => "config.$ext"),
PRG_LANG							=> array("name" => MN_LANG,							"url" => "languages.$ext"),
PRG_BUGTRACK					=> array("name" => MN_BUGTRACK,					"url" => "orb.aw?action=list&class=bugtrack"),
PRG_FORMGEN						=> array("name" => MN_FORMGEN,					"url" => "forms.$ext"),
PRG_GRAPH							=> array("name" => MN_GRAPH,						"url" => "graph.$ext"),
PRG_FACE							=> array("name" => MN_FACE,							"url" => "nagu.$ext"),
PRG_POLL							=> array("name" => MN_POLL,							"url" => "orb.aw?class=poll&action=list"),
PRG_SEARCH						=> array("name" => MN_SEARCH,						"url" => "orb.aw?class=search_conf&action=change"),
PRG_PERIODS						=> array("name" => MN_PERIODS,					"url" => "periods.$ext"),
PRG_TESTS							=> array("name" => MN_TESTS,						"url" => "orb.$ext?class=tests&action=list_testid"),
PRG_LISTS							=> array("name" => MN_LISTS,						"url" => "orb.$ext?class=lists&action=gen_list"),
PRG_VARS							=> array("name" => MN_VARS,							"url" => "orb.$ext?class=variables&action=gen_list"),
PRG_STAMPS						=> array("name" => MN_STAMPS,						"url" => "list.$ext?type=list_stamps"),
PRG_CODESTAT					=> array("name" => MN_CODESTAT,					"url" => "showstats.$ext"),
PRG_QUIZ							=> array("name" => MN_QUIZ,							"url" => "orb.$ext?class=quiz&action=upload"),
PRG_EVENTS						=> array("name" => MN_EVENTS,						"url" => "orb.$ext?class=events&action=list_events"),
PRG_EVENT_PLACES			=> array("name" => MN_EVENT_PLACES,			"url" => "orb.$ext?class=events&action=list_places"),
PRG_GALERII						=> array("name" => MN_GALERII,					"url" => "galerii.aw"),
PRG_LOGIN_MENU				=> array("name" => "Login men¸¸d", 			"url" => "orb.$ext?class=config&action=login_menus"),
PRG_JOINFORM					=> array("name" => MN_JOIN_FORM,				"url" => "config.aw?type=join_form"),
PRG_ICONDB						=> array("name" => MN_ICON_DB,					"url" => "config.aw?type=icon_db"),
PRG_CLASS_ICONS				=> array("name" => MN_CLASS_ICONS,			"url" => "config.aw?type=class_icons"),
PRG_FILE_ICONS				=> array("name" => MN_FILE_ICONS,				"url" => "config.aw?type=file_icons"),
PRG_PROGRAM_ICONS			=> array("name" => MN_PROGRAM_ICONS,		"url" => "config.aw?type=program_icons"),
PRG_OTHER_ICONS				=> array("name" => MN_OTHER_ICONS,			"url" => "config.aw?type=other_icons"),
PRG_IMPORT_ICONS			=> array("name" => MN_IMPORT_ICONS,			"url" => "config.aw?type=import"),
PRG_EXPORT_ICONS			=> array("name" => MN_EXPORT_ICONS,			"url" => "config.aw?type=export"),
PRG_ACCESSMGR					=> array("name" => MN_ACCESSMGR,				"url" => "orb.aw?class=accessmgr&action=list_access"),
PRG_BANNERS						=> array("name" => MN_BANNERS,					"url" => "orb.aw?class=banner&action=config"),
PRG_SEARCH_OBJS				=> array("name" => MN_SEARCH_OBJS,			"url" => "orb.aw?class=objects&action=search"),
PRG_SITE_BANNER_ADMIN	=> array("name" => MN_SITE_BANNER_ADMIN,"url" => "orb.aw?class=banner_buyer&action=sel_buyer_redirect&fun=buyer_banners&r_class=banner"),
PRG_SITE_BANNER_STATS	=> array("name" => MN_SITE_BANNER_STATS,"url" => "orb.aw?class=banner_buyer&action=sel_buyer_redirect&fun=buyer_banner_stats&r_class=banner_buyer"),
PRG_BANNER_USERS			=> array("name" => MN_BANNER_USERS,			"url" => "orb.aw?class=banner&action=show_users"),
PRG_BANNER_PROFILES		=> array("name" => MN_BANNER_PROFILES,	"url" => "orb.aw?class=banner&action=show_profiles"),
PRG_DOCEDIT						=> array("name" => MN_EKOMAR,           "url" => "orb.$ext?class=ekomar&action=list_files"),
PRG_KEYWORD						=> array("name" => MN_KEYWORD,					"url" => "orb.aw?class=keywords&action=list"),
PRG_CONF_JOIN_MAIL		=> array("name" => MN_JOIN_MAIL,				"url" => "orb.aw?class=config&action=join_mail"),
PRG_CSS_EDITOR				=> array("name" => "Kasutaja CSS editor","url" => "orb.aw?class=css&action=list"),
PRG_CSS_SYS_EDITOR		=> array("name" => "S¸steemi CSS editor","url" => "orb.aw?class=css&action=syslist"),
PRG_ML_MANAGER				=> array("name" => "Meililistid",				"url" => "orb.aw?class=ml_queue&action=queue&manager=1"),
PRG_SYSCONF						=> array("name" => "Automatweb config", "url" => "orb.aw?class=sysconf&action=edit"),
PRG_CONFIG_ERRORS			=> array("name" => "Config/Veateated",	"url" => "orb.aw?class=config&action=errors"),
PRG_CONFIG_REDIRECT		=> array("name" => "Config/suunamine",	"url" => "orb.aw?class=config&action=grp_redirect"),
PRG_TPLEDIT						=> array("name" => "TemplateEditor",		"url" => "orb.aw?class=tpledit&action=browse"),
PRG_DOCMGR						=> array("name" => "Document manager",	"url" => "orb.aw?class=docmgr&action=search"),
PRG_CONFIG_SITES			=> array("name" => "Config/saidid",			"url" => "orb.aw?class=config&action=sites"),
PRG_CONFIG_DOCFOLDERS	=> array("name" => "Config/dokumendi kataloogid",			"url" => "orb.aw?class=config&action=docfolders"),
PRG_CONFIG_FORMS			=> array("name" => "Config/FormGen",		"url" => "orb.aw?class=form_config&action=config"),
PRG_AUTOMATED_TEST		=> array("name" => "Automated testsuite",		"url" => "orb.aw?class=remote&action=config"),
PRG_AIP_PDF						=> array("name" => "AIP pdf upload",		"url" => "orb.aw?class=aip_pdf&action=listfiles"),
PRG_BACKUP						=> array("name" => "Backup",						"url" => "orb.aw?class=backup&action=backup"),
PRG_SITE_EXPORT				=> array("name" => "Saidi staatiline koopia",  "url" => "orb.aw?class=export&action=export")
);

// formide tyybid
	define("FTYPE_ENTRY",1);
	define("FTYPE_SEARCH",2);
	define("FTYPE_RATING",3);
	define("FTYPE_FILTER_SEARCH",4);

// formide alamtyybid
	define("FSUBTYPE_JOIN",1);

// lingikogus et mitu menyyd rea peal 
	define("LINKC_MENUSPERLINE",3);
// lingikogus et mitu linki per line
	define("LINKC_LINKSPERLINE",3);


// here we configure the necessary stuff for adding sites
$AW_SITES_basefolder = "/www";
$AW_SITES_vhost_folder = "/etc/apache/vhosts";
$AW_SITES_server_ip = "194.204.30.123";
$AW_SITES_admin_dir = "/www/automatweb_dev/automatweb";

$smtp_server = "www.kirjastus.ee";

// objektide subclassid - objects.subclass sees juusimiseks

// for CL_BROTHER_DOCUMENT 
define("SC_BROTHER_DOC_KEYWORD", 1);	// kui dokumendi vend on tehtud t2nu menuu keywordile

$error_log_site = "aw.struktuur.ee";

define("ERR_ML_VAR_NO_VAR",1);
define("ERR_ML_VAR_NO_STAMP",2);
define("ERR_ML_VAR_NO_CAT",3);
define("ERR_TBL_NO_TBL",4);
define("ERR_TBL_IMPORT_NOFILE",5);
define("ERR_DBSYNC_NOSERVER",6);
define("ERR_STYLE_WTYPE",7);
define("ERR_CORE_NO_OID",8);
define("ERR_CORE_WTYPE",9);
define("ERR_CORE_NOFILE",10);
define("ERR_CORE_NOFILENAME",11);
define("ERR_CORE_NOP_OPEN_FILE",12);
define("ERR_CORE_NOTPL",74);
define("ERR_SMTP_WSERVER",13);
define("ERR_SMTP_HELO",14);
define("ERR_SMTP_MFROM",15);
define("ERR_SMTP_RCPT",16);
define("ERR_SMTP_DATA",17);
define("ERR_SMTP_MSG",18);
define("ERR_SMTP_QUIT",19);
define("ERR_SMTP_CONNECT",20);
define("ERR_SHOP_NMITEM",21);
define("ERR_SHOP_NDATEEL",22);
define("ERR_PROMO_NOBOX",23);
define("ERR_POP3_INVUSER",24);
define("ERR_POP3_INVPWD",25);
define("ERR_POP3_STAT",26);
define("ERR_POP3_UIDL",27);
define("ERR_POP3_CONNECT",28);
define("ERR_POP3_RETR",29);
define("ERR_ORB_NOCLASS",30);
define("ERR_ORB_NOTFOUND",31);
define("ERR_ORB_LOGIN",32);
define("ERR_ORB_AUNDEF",33);
define("ERR_ORB_CAUNDEF",34);
define("ERR_ORB_MNOTFOUND",35);
define("ERR_ORB_CPARM",36);
define("ERR_ORB_NINT",37);
define("ERR_MSGB_NOLOGIN",38);
define("ERR_MSGB_NOCOMM",39);
define("ERR_MNEDIT_NOACL",40);
define("ERR_MNEDIT_NOFOLDER",41);
define("ERR_MNEDIT_CMDREDIR",42);
define("ERR_MNEDIT_NOCONF",43);
define("ERR_MNEDIT_UCLASS",44);
define("ERR_MNEDIT_ACL_NOADD",45);
define("ERR_MNEDIT_ACL_NOCHANGE",46);
define("ERR_MNEDIT_ACL_NODEL",47);
define("ERR_MNEDIT_NOMENU",48);
define("ERR_LISTS_NOMENU",49);
define("ERR_LIST_NOUSER",50);
define("ERR_ICONS_EOPEN",51);
define("ERR_ICONS_NOTEMP",52);
define("ERR_ICONS_WTYPE",53);
define("ERR_GRP_NOGRP",54);
define("ERR_GRAPH_IMP",55);
define("ERR_GAL_NOGAL",56);
define("ERR_FORUM_LOGIN",57);
define("ERR_FG_NOELEMENT",58);
define("ERR_FG_NOFILE",59);
define("ERR_FG_ETYPE",60);
define("ERR_FG_ACL_NOACESS",61);
define("ERR_FG_NOFORM",62);
define("ERR_FG_NOOP",63);
define("ERR_FG_NOACTION",64);
define("ERR_FG_NOTABLE",65);
define("ERR_FG_NOTARGETS",66);
define("ERR_FG_NOTBLELS",67);
define("ERR_FG_EMETAINFO",68);
define("ERR_EMAIL_NOEMAIL",69);
define("ERR_EMAIL_NOEMAIL",70);
define("ERR_EMAIL_NOUSER",71);
define("ERR_EMAIL_NOEMAIL",72);
define("ERR_CSS_EGRP",73);
define("ERR_CONTACT_NOFORM",75);
define("ERR_CONF_NLOGIN",76);
define("ERR_CONFIG_IMPORT",77);
define("ERR_BT_NOKEYS",78);
define("ERR_BT_NOGRP",79);
define("ERR_BT_EADD",80);
define("ERR_BT_EREPLICATE",81);
define("ERR_BANNER_NOFORM",82);
define("ERR_TPL_NOTPL",83);
define("ERR_ARC_NODEPTH",84);
define("ERR_ARC_NOWRITE",85);
define("ERR_ACL_EHIER",86);
define("ERR_ACL_NOGRP",87);
define("ERR_ACL_ERR",88);
define("ERR_MNEDIT_TXTIMP",89);
define("ERR_MNEDIT_TXTIMP_PARENT",90);
define("ERR_SITEXPORT_NOFOLDER",91);

$error_types = array(
	ERR_ML_VAR_NO_VAR => "ERR_ML_VAR_NO_VAR",
	ERR_ML_VAR_NO_STAMP => "ERR_ML_VAR_NO_STAMP",
	ERR_ML_VAR_NO_CAT => "ERR_ML_VAR_NO_CAT",
	ERR_TBL_NO_TBL => "ERR_TBL_NO_TBL",
	ERR_TBL_IMPORT_NOFILE => "ERR_TBL_IMPORT_NOFILE",
	ERR_DBSYNC_NOSERVER => "ERR_DBSYNC_NOSERVER",
	ERR_STYLE_WTYPE => "ERR_STYLE_WTYPE",
	ERR_CORE_NO_OID => "ERR_CORE_NO_OID",
	ERR_CORE_WTYPE => "ERR_CORE_WTYPE",
	ERR_CORE_NOFILE => "ERR_CORE_NOFILE",
	ERR_CORE_NOFILENAME => "ERR_CORE_NOFILENAME",
	ERR_CORE_NOP_OPEN_FILE => "ERR_CORE_NOP_OPEN_FILE",
	ERR_CORE_NOTPL => "ERR_CORE_NOTPL",
	ERR_SMTP_WSERVER => "ERR_SMTP_WSERVER",
	ERR_SMTP_HELO => "ERR_SMTP_HELO",
	ERR_SMTP_MFROM => "ERR_SMTP_MFROM",
	ERR_SMTP_RCPT => "ERR_SMTP_RCPT",
	ERR_SMTP_DATA => "ERR_SMTP_DATA",
	ERR_SMTP_MSG => "ERR_SMTP_MSG",
	ERR_SMTP_QUIT => "ERR_SMTP_QUIT",
	ERR_SMTP_CONNECT => "ERR_SMTP_CONNECT",
	ERR_SHOP_NMITEM => "ERR_SHOP_NMITEM",
	ERR_SHOP_NDATEEL => "ERR_SHOP_NDATEEL",
	ERR_PROMO_NOBOX => "ERR_PROMO_NOBOX",
	ERR_POP3_INVUSER => "ERR_POP3_INVUSER",
	ERR_POP3_INVPWD => "ERR_POP3_INVPWD",
	ERR_POP3_STAT => "ERR_POP3_STAT",
	ERR_POP3_UIDL => "ERR_POP3_UIDL",
	ERR_POP3_CONNECT => "ERR_POP3_CONNECT",
	ERR_POP3_RETR => "ERR_POP3_RETR",
	ERR_ORB_NOCLASS => "ERR_ORB_NOCLASS",
	ERR_ORB_NOTFOUND => "ERR_ORB_NOTFOUND",
	ERR_ORB_LOGIN => "ERR_ORB_LOGIN",
	ERR_ORB_AUNDEF => "ERR_ORB_AUNDEF",
	ERR_ORB_CAUNDEF => "ERR_ORB_CAUNDEF",
	ERR_ORB_MNOTFOUND => "ERR_ORB_MNOTFOUND",
	ERR_ORB_CPARM => "ERR_ORB_CPARM",
	ERR_ORB_NINT => "ERR_ORB_NINT",
	ERR_MSGB_NOLOGIN => "ERR_MSGB_NOLOGIN",
	ERR_MSGB_NOCOMM => "ERR_MSGB_NOCOMM",
	ERR_MNEDIT_NOACL => "ERR_MNEDIT_NOACL",
	ERR_MNEDIT_NOFOLDER => "ERR_MNEDIT_NOFOLDER",
	ERR_MNEDIT_CMDREDIR => "ERR_MNEDIT_CMDREDIR",
	ERR_MNEDIT_NOCONF => "ERR_MNEDIT_NOCONF",
	ERR_MNEDIT_UCLASS => "ERR_MNEDIT_UCLASS",
	ERR_MNEDIT_ACL_NOADD => "ERR_MNEDIT_ACL_NOADD",
	ERR_MNEDIT_ACL_NOCHANGE => "ERR_MNEDIT_ACL_NOCHANGE",
	ERR_MNEDIT_ACL_NODEL => "ERR_MNEDIT_ACL_NODEL",
	ERR_MNEDIT_NOMENU => "ERR_MNEDIT_NOMENU",
	ERR_LISTS_NOMENU => "ERR_LISTS_NOMENU",
	ERR_LIST_NOUSER => "ERR_LIST_NOUSER",
	ERR_ICONS_EOPEN => "ERR_ICONS_EOPEN",
	ERR_ICONS_NOTEMP => "ERR_ICONS_NOTEMP",
	ERR_ICONS_WTYPE => "ERR_ICONS_WTYPE",
	ERR_GRP_NOGRP => "ERR_GRP_NOGRP",
	ERR_GRAPH_IMP => "ERR_GRAPH_IMP",
	ERR_GAL_NOGAL => "ERR_GAL_NOGAL",
	ERR_FORUM_LOGIN => "ERR_FORUM_LOGIN",
	ERR_FG_NOELEMENT => "ERR_FG_NOELEMENT",
	ERR_FG_NOFILE => "ERR_FG_NOFILE",
	ERR_FG_ETYPE => "ERR_FG_ETYPE",
	ERR_FG_ACL_NOACESS => "ERR_FG_ACL_NOACESS",
	ERR_FG_NOFORM => "ERR_FG_NOFORM",
	ERR_FG_NOOP => "ERR_FG_NOOP",
	ERR_FG_NOACTION => "ERR_FG_NOACTION",
	ERR_FG_NOTABLE => "ERR_FG_NOTABLE",
	ERR_FG_NOTARGETS => "ERR_FG_NOTARGETS",
	ERR_FG_NOTBLELS => "ERR_FG_NOTBLELS",
	ERR_FG_EMETAINFO => "ERR_FG_EMETAINFO",
	ERR_EMAIL_NOEMAIL => "ERR_EMAIL_NOEMAIL",
	ERR_EMAIL_NOEMAIL => "ERR_EMAIL_NOEMAIL",
	ERR_EMAIL_NOUSER => "ERR_EMAIL_NOUSER",
	ERR_EMAIL_NOEMAIL => "ERR_EMAIL_NOEMAIL",
	ERR_CSS_EGRP => "ERR_CSS_EGRP",
	ERR_CONTACT_NOFORM => "ERR_CONTACT_NOFORM",
	ERR_CONF_NLOGIN => "ERR_CONF_NLOGIN",
	ERR_CONFIG_IMPORT => "ERR_CONFIG_IMPORT",
	ERR_BT_NOKEYS => "ERR_BT_NOKEYS",
	ERR_BT_NOGRP => "ERR_BT_NOGRP",
	ERR_BT_EADD => "ERR_BT_EADD",
	ERR_BT_EREPLICATE => "ERR_BT_EREPLICATE",
	ERR_BANNER_NOFORM => "ERR_BANNER_NOFORM",
	ERR_TPL_NOTPL => "ERR_TPL_NOTPL",
	ERR_ARC_NODEPTH => "ERR_ARC_NODEPTH",
	ERR_ARC_NOWRITE => "ERR_ARC_NOWRITE",
	ERR_ACL_EHIER => "ERR_ACL_EHIER",
	ERR_ACL_NOGRP => "ERR_ACL_NOGRP",
	ERR_ACL_ERR => "ERR_ACL_ERR",
	ERR_MNEDIT_TXTIMP => "ERR_MNEDIT_TXTIMP",
	ERR_MNEDIT_TXTIMP_PARENT => "ERR_MNEDIT_TXTIMP_PARENT",
	ERR_SITEXPORT_NOFOLDER => "ERR_SITEXPORT_NOFOLDER"
);

$mysqldump_path = "/usr/local/bin/mysqldump";
$gzip_path = "/bin/gzip";
$tar_path = "/bin/tar";
$zip_path = "/usr/bin/zip";

?>
