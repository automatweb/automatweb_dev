<?php
// $Header: /home/cvs/automatweb_dev/const.aw,v 2.13 2001/05/31 21:26:48 kristo Exp $
// ---------------------------------------------------------------------------
// (C) OÜ Sruktuur Meedia 2000,2001
// ---------------------------------------------------------------------------

// aw version - [major release].[minor release].[fix number]
define(AW_VERSION,"2.0.0");

// here we define basic constants needed by all components
set_magic_quotes_runtime(0);
// ---------------------------------------------------------------------------
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

// $HTTP_HOST pohjal vaatame, millisest kohast nö "public"
// templated võtta
if (empty($tpldirs))
{
	$tpldirs["ebs.elkdata.com"] = $sitedir . "/../templates";
}

// et siis selline muutuja
$siteconfig["uus.anne.ee"] = "/www/aw_anne/public/const.aw";

// temp kataloogi asukoht. nuh et kui kunagi asi windowsi peal k2ima hakkab, siis saax selle siit 2r muuta.
$tmpdir = "/tmp";

// saidi siseste linkide jaox
if (empty($index_file))
{
$index_file = "index";
}

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

// kui saidi const.aw ei defineeri seda, siis määrame default väärtuse
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
		$basedir = "/www/automatweb_dev/public";
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

include("$basedir/lang/" . $LC . "/errors.aw");
include("$basedir/lang/" . $LC . "/common.aw");

$cachedir = $basedir . "/cache"; 		  // where the file cache is
$pi = $PATH_INFO.$QUERY_STRING;
if ($pi) 
{
	 if (preg_match("/[&|=]/",$pi)) 
	 {
		parse_str(str_replace("/","&",$pi)); 	// expand and import PATH_INFO
	 } 
	 else 
	 {
		$section = substr($pi,1);
	};
};

// menu definitions, max - maximum depth of menu, can_l3 - whether you can insert "level 3" items in the menu
// umm. imho on see vale lähenemine
// miskid hardcoded id-d. BAD.
// njah. a selle v6ib dynaamilisex teha. et andmebaasi kirjutada ja yle webi konffida nyyd. kuna menyyeditor v6tab nyyd
// alamtemplated selle j2rgi, mis siin kirjas on. 
if (!is_array($menu_defs))
{
	$menu_defs = array();
}
									 									 									 
//------------------------------------------------------------------------------
// alfabeet - user for various tables
$alfa = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O",
                "P","Q","R","S","T","U","V","W","X","Y","Z");
// ----------------------------------------------------------------------------

// nyyd on voimalik laadida ka mitu librat yhe calliga 
// a la classload("users","groups","someothershit");

// sellest saab lahti, kui igalpool include("const.aw") asendada include_once("const.aw")-ga
if (function_exists(classload)) 
{
} 
else 
{
	// loads localization constants
	function lc_load($file)
	{
		global $basedir;
		global $ext,$LC;
		include_once("$basedir/lang/" . $LC . "/$file.$ext");
	}

	// loads localization constants from the site's $site_basedir
	function lc_site_load($file)
	{
		global $site_basedir;
		global $ext,$LC;
		@include_once("$site_basedir/lang/" . $LC . "/$file.$ext");
	}

	function classload($args)
	{
		$arg_list = func_get_args();
		global $classdir;
		global $ext;
		while(list(,$lib) = each($arg_list))
		{
			// votab stringist ainult selle osa, mis jääb /-i ja stringi lopu vahele
			// vältimaks katseid laadida faile /etc/passwd
			preg_match("/(\w*)$/",$lib,$m);
			$lib = $m[1];
			$lib = "$classdir/$lib.$ext";
			include_once($lib);
		};
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

// millisele aadressile saadetakse Alerdid (ebaonnestunud sisselogimised, jne).
define(ALERT_ADDR,"log@struktuur.ee");

// mis alerdi subjectiks pannakse
define(ALERT_SUBJECT,"%s - Jerk alert!");

// ja mis aadress alerdi From reale pannakse
define(ALERT_FROM,"From: AK veebiserver <nobody@www.kirjastus.ee>");

// klasside nimed
define(CL_PSEUDO,1);
define(CL_FORM,2);
define(CL_TABLE,3);
define(CL_CATEGORY,4);
define(CL_PLACE,5);
define(CL_IMAGE,6);
define(CL_DOCUMENT,7);
define(CL_DEF_SETTINGS,36);
define(CL_FORM_ENTRY,8);
define(CL_FORM_CATEGORY,9);
define(CL_FORM_ELEMENT,10);
define(CL_STYLE,11);
define(CL_FORM_OUTPUT,12);
define(CL_FILLED_FORM_FOLDER,13);
define(CL_FORM_ACTION,14);
define(CL_MAILINGLIST,15);
define(CL_MAILINGLIST_CATEGORY,16);
define(CL_MAILINGLIST_MEMBER,17);
define(CL_MAILINGLIST_VARIABLE,18);
define(CL_MAILINGLIST_STAMP,19);
define(CL_EMAIL,20);
define(CL_EXTLINK,21);
define(CL_PROMO,22);
define(CL_PROMO_ARTICLE,23);
define(CL_ML_VAR_CAT,24);
define(CL_MAIL_LINK,25);
define(CL_MAIL,26);
define(CL_MAIL_FOLDER,27);
define(CL_GRAPH,28);
define(CL_PERIODIC_SECTION,29);
define(CL_SECTION_LINK,30);
define(CL_GALLERY,31);
define(CL_POLL,33);
define(CL_MSGBOARD_TOPIC,34);
define(CL_NAGU,35);
define(CL_DEF_SETTINGS,36); // default doc .. ehk default settingud 
														// uute dokude jaoks
define(CL_GROUP,37);
define(CL_USER_GROUP,38);
define(CL_BROTHER,39);
define(CL_BROTHER_DOCUMENT,40);
define(CL_FILE,41);

define(CL_TEST_QUESTION,42);
define(CL_TEST_TEEMA,43);
define(CL_TEST,44);

// Vibe ürituste jaoks
define(CL_EVENT,45);
define(CL_LOCATION,46);
define(CL_EVENT_TYPE,47);
define(CL_LOCATION_TYPE,48);

// yuck.
define(CL_GUESTBOOK,49);
define(CL_GUESTBOOK_ENTRY,50);

// module access control objects
define(CL_ACCESSMGR,51);

// messengeri teade
define(CL_MESSAGE,52);

// kalendri event
define(CL_CAL_EVENT,53);



define(CL_BANNER,54);
define(CL_BANNER_CLIENT,55);
define(CL_BANNER_PROFILE,56);	// this actually specifies the location of the banner. legacy code sucks. what can I do.
define(CL_BANNER_BUYER,57);		// well, these are actual clients that buy banner impressions
define(CL_BANNER_SITE,58);		// this is just for grouping banner locations, so we can view the statistics for the site.

define(CL_CALENDAR,59); // kalendri objekt

define(CL_SHOP,60); // pood. w00t!
define(CL_SHOP_ITEM,61); // kaup. poes. duh. 
define(CL_SHOP_STATS,62); // poodide statistika vaatamine k2ib selle kaudu

// can_add määrab ära kas, seda klassi näidatakse Lisa listboxis

// nimekiri k6ikidest klassidest ikoonide jaox
$class_defs = array(	CL_PSEUDO => array("name" => "Men&uuml","file" => "menuedit","can_add" => 0),
			CL_DOCUMENT => array("name" => "Dokument", "file" => "document", "can_add" => 1), 
			CL_BROTHER_DOCUMENT => array("name" => "Dokument(vend)", "file" => "document", "can_add" => 1), 
			CL_FORM		=> array("name" => "Form", "file" => "form","can_add" => 1),
			CL_SHOP => array("name" => "Pood", "file" => "shop", "can_add" => 1),
			CL_SHOP_ITEM => array("name" => "Kaubaartikkel", "file" => "shop_item", "can_add" => 1),
			CL_TABLE	=> array("name" => "Tabel", "file" => "table","can_add" => 1), 
			CL_IMAGE	=> array("name" => "Pilt", "file" => "images", "can_add" => 0), 
			CL_FORM_ENTRY => array("name" => "Formi sisestus", "file" => "form_entry"),
			CL_FORM_ELEMENT => array("name" => "Formi element", "file" => "forms"),
			CL_STYLE	=> array("name" => "Stiil", "file" => "style","class" => "style", "can_add" => 1), 
			CL_FORM_OUTPUT => array("name" => "Formi v&auml;ljund", "file" => "forms"),
			CL_FORM_ACTION => array("name" => "Formi action", "file" => "forms"),
			CL_MAILINGLIST => array("name" => "Meilinglist", "file" => "lists","can_add" => 0),
			CL_MAILINGLIST_MEMBER => array("name" => "Listi liige", "file" => "list"),
			CL_MAILINGLIST_VARIABLE => array("name" => "Listi muutuja", "file" => "variables","can_add" => 0),
			CL_MAILINGLIST_STAMP => array("name" => "Listi stamp", "file" => "list","can_add" => 0), 
			CL_EMAIL => array("name" => "Listi mail", "file" => "list","can_add" => 0),
			CL_EXTLINK => array("name" => "Link", "file" => "links","can_add" => 1),
			CL_MAIL_LINK => array("name" => "Emaili link", "file" => "extrlinks","can_add" => 0),
			CL_MAIL => array("name" => "Email", "file" => "mail","can_add" => 0),
			CL_GRAPH => array("name" => "Graafik", "file" => "graph","can_add" => 1),
			CL_PERIODIC_SECTION => array("name" => "Dokument(p)", "file" => "document","can_add" => 0),
			CL_SECTION_LINK => array("name" => "Sektsiooni link", "file" => "links"),
			CL_GALLERY => array("name" => "Galerii", "file" => "gallery","can_add" => 1),
			CL_POLL => array("name" => "Poll", "file" => "poll","can_add" => 0),
			CL_MSGBOARD_TOPIC => array("name" => "Boardi topic", "file" => "board"),
			CL_NAGU => array("name" => "N&auml;dala n&auml;gu","file" => "nagu"),
			CL_GROUP => array("name" => "Grupp", "file" => "groups","can_add" => 0), 
			CL_USER_GROUP => array("name" => "Kasutaja","file" => "users","can_add" => 0),
			CL_FILE => array("name" => "Fail","file" => "file","can_add" => 1),
			CL_GUESTBOOK => array("name" => "Guestbook","file" => "guestbook","can_add" => 1),
			CL_PROMO => array("name" => "Promo kast", "file" => "promo", "can_add" => 1),
			CL_CALENDAR => array("name" => "Kalender","file" => "planner","can_add" => 1),
			CL_BANNER => array("name" => "Banner", "file" => "banner", "can_add" => 1),
			CL_BANNER_CLIENT => array("name" => "Banneri asukoht", "file" => "banner_client", "can_add" => 1),
			CL_BANNER_PROFILE => array("name" => "Banneri profiil", "file" => "banner_profile", "can_add" => 1),
			CL_BANNER_BUYER => array("name" => "Banneri klient", "file" => "banner_buyer", "can_add" => 1),
			CL_BANNER_SITE => array("name" => "Banneri sait", "file" => "banner_site", "can_add" => 1),
			CL_MESSAGE => array("name" => "Message", "file" => "messenger", "can_add" => 0),
			CL_SHOP_STATS => array("name" => "Poe statistika", "file" => "shop_stat", "can_add" => 1)
);
// kliendid. 
// hierarhia esimene element on root
//  teisel tasemel on kliendid
//  naiteks "Ajakirjade Kirjastus"
//  voi "StruktuurMeedia"

// esimesele tasemele saab lisada ainult kliente
// teisele tasemele kliente voi dokumente

// mix 69? well mulle meeldib see number :-P
define(CL_CLIENT,69);

// menyyd
define(MN_CLIENT,69);
// sisurubriik
define(MN_CONTENT,70);
// adminni ylemine menyy
define(MN_ADMIN1,71);
// adminni dokumenty
define(MN_ADMIN_DOC,72);

// promo kast
define(MN_PROMO_BOX,73);

// kodukataloog
define(MN_HOME_FOLDER,74);
// kodukataloogi alla tehtud kataloog, et sharetud katalooge olex lihtsam n2idata
define(MN_HOME_FOLDER_SUB,75);

// formi element, mis on samas ka menyy
define(MN_FORM_ELEMENT,76);

// nini. siin tuleb siis nyyd see koht, kus on kirjas k6ik erinevad "alamprogrammid" , mis aw sees olemas on
// mix nii? well, sest neile peab saama ikoone m22rata ja neid uude menyyeditori teha.
// oid v2li on sellex, et sinna tuleb panna kirja see objekt, mille kaudu neile 6igusi jagataxe.
// st et seda praegu pole veel, aga see tyulex vist siia panna ju ?
// welp, seda objekti ei pea siia kirja panema k2sici, see genereeritaxe automaagiliselt. 
// eh-puh. symboolsed konstandid siis ka progedele
define(PRG_MENUEDIT,1);
define(PRG_DOCLIST,2);
define(PRG_USERS,3);
define(PRG_GROUPS,4);
define(PRG_CONFIG,5);
define(PRG_LANG,6);
define(PRG_BUGTRACK,7);
define(PRG_FORMGEN,8);
define(PRG_GRAPH,9);
define(PRG_FACE,10);
define(PRG_POLL,11);
define(PRG_SEARCH,12);
define(PRG_PERIODS,13);
define(PRG_TESTS,14);
define(PRG_LISTS,15);
define(PRG_VARS,16);
define(PRG_STAMPS,17);
define(PRG_CODESTAT,18);
define(PRG_AWMAIL,19);
define(PRG_QUIZ,20);
define(PRG_EVENTS,21);
define(PRG_EVENT_PLACES,22);
define(PRG_GALERII,23);
define(PRG_KROONIKA_BANNER,24);
define(PRG_KROONIKA_ESIKAAS,25);
define(PRG_JOINFORM,26);
define(PRG_ICONDB,27);
define(PRG_CLASS_ICONS,28);
define(PRG_FILE_ICONS,29);
define(PRG_PROGRAM_ICONS,30);
define(PRG_OTHER_ICONS,31);
define(PRG_IMPORT_ICONS,32);
define(PRG_EXPORT_ICONS,33);
define(PRG_ACCESSMGR,34);
define(PRG_BANNERS,35);
define(PRG_SEARCH_OBJS,36);
define(PRG_SITE_BANNER_ADMIN,37);
define(PRG_SITE_BANNER_STATS,38);
define(PRG_BANNER_USERS,39);
define(PRG_BANNER_PROFILES,40);
define(PRG_EKOMAR,41);
define(PRG_KEYWORD,42);


// MN_* konstandid on defineeritud $basedir/lang/$lc/common.aw sees
// $lc = keelekood, vaikimisi "ee"
$programs = array(
PRG_MENUEDIT					=> array("name" => MN_MENUEDIT,					"url" => "menuedit.html"),
PRG_DOCLIST						=> array("name" => MN_DOCLIST,					"url" => "list_docs.$ext?search=1"),
PRG_USERS							=> array("name" => MN_USERS,						"url" => "orb.$ext?class=users&action=gen_list"),
PRG_GROUPS						=> array("name" => MN_GROUPS,						"url" => "orb.$ext?class=groups&action=mk_grpframe&parent=0"),
PRG_CONFIG						=> array("name" => MN_CONFIG,						"url" => "config.$ext"),
PRG_LANG							=> array("name" => MN_LANG,							"url" => "languages.$ext"),
PRG_BUGTRACK					=> array("name" => MN_BUGTRACK,					"url" => "orb.aw?action=list&class=bugtrack&filt=all"),
PRG_FORMGEN						=> array("name" => MN_FORMGEN,					"url" => "forms.$ext"),
PRG_GRAPH							=> array("name" => MN_GRAPH,						"url" => "graph.$ext"),
PRG_FACE							=> array("name" => MN_FACE,							"url" => "nagu.$ext"),
PRG_POLL							=> array("name" => MN_POLL,							"url" => "poll.$ext"),
PRG_SEARCH						=> array("name" => MN_SEARCH,						"url" => "search_conf.$ext"),
PRG_PERIODS						=> array("name" => MN_PERIODS,					"url" => "periods.$ext"),
PRG_TESTS							=> array("name" => MN_TESTS,						"url" => "orb.$ext?class=tests&action=list_testid"),
PRG_LISTS							=> array("name" => MN_LISTS,						"url" => "list.$ext"),
PRG_VARS							=> array("name" => MN_VARS,							"url" => "list.$ext?type=list_vars"),
PRG_STAMPS						=> array("name" => MN_STAMPS,						"url" => "list.$ext?type=list_stamps"),
PRG_CODESTAT					=> array("name" => MN_CODESTAT,					"url" => "showstats.$ext"),
PRG_AWMAIL						=> array("name" => MN_AWMAIL,						"url" => "mail.html"),
PRG_QUIZ							=> array("name" => MN_QUIZ,							"url" => "orb.$ext?class=quiz&action=upload"),
PRG_EVENTS						=> array("name" => MN_EVENTS,						"url" => "orb.$ext?class=events&action=list_events"),
PRG_EVENT_PLACES			=> array("name" => MN_EVENT_PLACES,			"url" => "orb.$ext?class=events&action=list_places"),
PRG_GALERII						=> array("name" => MN_GALERII,					"url" => "galerii.aw"),
PRG_KROONIKA_BANNER		=> array("name" => MN_KROONIKA_BANNER,	"url" => "banner.aw?op=banner"),
PRG_KROONIKA_ESIKAAS	=> array("name" => MN_KROONIKA_ESIKAAS, "url" => "banner.aw?op=kaas"),
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
PRG_BANNER_USERS			=> array("name" => MN_BANNER_USERS,"url" => "orb.aw?class=banner&action=show_users"),
PRG_BANNER_PROFILES		=> array("name" => MN_BANNER_PROFILES,"url" => "orb.aw?class=banner&action=show_profiles"),
PRG_EKOMAR						=> array("name" => MN_EKOMAR,            "url" => "orb.$ext?class=ekomar&action=list_files"),
PRG_KEYWORD						=> array("name" => MN_KEYWORD,					"url" => "orb.aw?class=keywords&action=list"),
);

// formide tyybid
	define(FTYPE_ENTRY,1);
	define(FTYPE_SEARCH,2);
	define(FTYPE_RATING,3);

// formide alamtyybid
	define(FSUBTYPE_JOIN,1);

// lingikogus et mitu menyyd rea peal 
	define(LINKC_MENUSPERLINE,3);
// lingikogus et mitu linki per line
	define(LINKC_LINKSPERLINE,3);
?>
