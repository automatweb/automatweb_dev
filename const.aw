<?php
// $Header: /home/cvs/automatweb_dev/const.aw,v 2.64 2002/06/13 23:11:34 kristo Exp $

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

if ($pi) 
{
	// uh, why do you use sprintf to do string->int conversion? I mean, it's GOTTA be wayyy slower than $i = (int)$str;

	// I think type-cast had problems in some situations, but right now I can't really reproduce any of those
	$section = (int)substr($pi,1);

	// if $pi contains & or = 
	if (preg_match("/[&|=]/",$pi)) 
	{
		// expand and import PATH_INFO
		// replace ? and / with & in $pi and output the result to HTTP_GET_VARS
		// why so?
		parse_str(str_replace("?","&",str_replace("/","&",$pi)),$HTTP_GET_VARS);
//		echo "$QUERY_STRING , $PATH_INFO , pi = $pi  str = ",str_replace("?","&",str_replace("/","&",$pi))," <br>";
		extract($HTTP_GET_VARS);
	} 
	else 
	{
		$section = substr($pi,1);
	};

	// this here adds support for links like http://bla/index.aw/section=291/lcb=117
	if (($_pos = strpos($pi, "section=")) !== false)
	{
		$t_pi = substr($pi, $_pos+strlen("section="));
		if (($_eqp = strpos($t_pi, "="))!== false)
		{
			$t_pi = substr($t_pi, 0, $_eqp);
			if (($_tpos1 = strpos($t_pi, "?")) !== false || ($_tpos2 = strpos($t_pi, "&")) !== false)
			{
				// if the thing contains ? or & , then section is the part before it
				if ($_tpos1 === false)
				{
					$_tpos = $_tpos2;
				}
				else
				if (!$_tpos2 === false)
				{
					$_tpos = $_tpos1;
				}
				else
				{
					$_tpos = min($_tpos1, $_tpos2);
				}
				$section = substr($t_pi, 0, $_tpos);
			}
			else
			{
				// if not, then te section is the part upto the last /
				$_lslp = strrpos($t_pi, "/");
				if ($_lslp !== false)
				{
					$section = substr($t_pi, 0, $_lslp);
				}
				else
				{
					$section = $t_pi;
				}
			}
		}
		else
		{
			$section = $t_pi;
		}
	}
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

$ext = "aw"; 		          	// filename extension

// veateadete tekstid

if (empty($LC))
{
	$LC="et";
}

@include(aw_ini_get("basedir")."/lang/" . $LC . "/errors.".aw_ini_get("ext"));
@include(aw_ini_get("basedir")."/lang/" . $LC . "/common.".aw_ini_get("ext"));

// other stuff

// stat function fields
define("FILE_SIZE",7);
define("FILE_MODIFIED",9);

// kliendid. 
// hierarhia esimene element on root
//  teisel tasemel on kliendid
//  naiteks "Ajakirjade Kirjastus"
//  voi "StruktuurMeedia"
// menyyd

// mix 69? well mulle meeldib see number :-P
define("MN_CLIENT",69);
// sisurubriik
define("MN_CONTENT",70);
// adminni ylemine menyy
define("MN_ADMIN1",71);
// promo kast
define("MN_PROMO_BOX",73);
// kodukataloog
define("MN_HOME_FOLDER",74);
// kodukataloogi alla tehtud kataloog, et sharetud katalooge olex lihtsam n2idata
define("MN_HOME_FOLDER_SUB",75);
// formi element, mis on samas ka menyy
define("MN_FORM_ELEMENT",76);


// formide tyybid
define("FTYPE_ENTRY",1);
define("FTYPE_SEARCH",2);
define("FTYPE_RATING",3);
define("FTYPE_FILTER_SEARCH",4);
define("FTYPE_CONFIG",5);

// formide alamtyybid
define("FSUBTYPE_JOIN",1);

// objektide subclassid - objects.subclass sees juusimiseks

// for CL_BROTHER_DOCUMENT 
define("SC_BROTHER_DOC_KEYWORD", 1);	// kui dokumendi vend on tehtud t2nu menuu keywordile


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
define("ERR_FILE_WRONG_CLASS", 92);
define("ERR_F_OP_NO_SESSION_FORM", 93);
define("ERR_SCHED_NOTIMEREP", 94);
define("ERR_FG_NOFORMRELS", 95);

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
	ERR_SITEXPORT_NOFOLDER => "ERR_SITEXPORT_NOFOLDER",
	ERR_FILE_WRONG_CLASS => "ERR_FILE_WRONG_CLASS",
	ERR_F_OP_NO_SESSION_FORM => "ERR_F_OP_NO_SESSION_FORM",
	ERR_SCHED_NOTIMEREP => "ERR_SCHED_NOTIMEREP" ,
	ERR_FG_NOFORMRELS => "ERR_FG_NOFORMRELS"
);
?>
