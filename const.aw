<?php
// $Header: /home/cvs/automatweb_dev/const.aw,v 2.79 2002/10/31 09:23:30 kristo Exp $
error_reporting(E_ALL ^ E_NOTICE);
// here we define basic constants needed by all components
set_magic_quotes_runtime(0);

$pi = "";

global $section;
// register_globals should be off!
if (is_array($_SERVER))
{
	// alltho we only need PATH_INFO and QUERY_STRING
	extract($_SERVER);
};

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
	// if $pi contains & or = 
	if (preg_match("/[&|=]/",$pi)) 
	{
		// expand and import PATH_INFO
		// replace ? and / with & in $pi and output the result to HTTP_GET_VARS
		// why so?
		parse_str(str_replace("?","&",str_replace("/","&",$pi)),$HTTP_GET_VARS);
//		echo "gv = <pre>", var_dump($HTTP_GET_VARS),"</pre> <br>";
		extract($HTTP_GET_VARS);
		$GLOBALS["fastcall"] = $HTTP_GET_VARS["fastcall"];
	} 

	if (($_pos = strpos($pi, "section=")) === false)
	{
		// ok, we need to check if section is followed by = then it is not really the section but 
		// for instance index.aw/set_lang_id=1
		// we check for that like this:
		// if there are no / or ? chars before = then we don't prepend

		$qpos = strpos($pi, "?");
		$slpos = strpos($pi, "/");
		$eqpos = strpos($pi, "=");
		$qpos = $qpos ? $qpos : 20000000;
		$slpos = $slpos ? $slpos : 20000000;

		if (!$eqpos || ($eqpos > $qpos || $slpos > $qpos))
		{
			// if no section is in url, we assume that it is the first part of the url and so prepend section = to it
			$pi = str_replace("?", "&", "section=".substr($pi, 1));
		}
	}

	if (($_pos = strpos($pi, "section=")) !== false)
	{
		// this here adds support for links like http://bla/index.aw/section=291/lcb=117
		$t_pi = substr($pi, $_pos+strlen("section="));
		if (($_eqp = strpos($t_pi, "="))!== false)
		{
			$t_pi = substr($t_pi, 0, $_eqp);
			$_tpos1 = strpos($t_pi, "?");
			$_tpos2 = strpos($t_pi, "&");
			if ($_tpos1 !== false || $_tpos2 !== false)
			{
				// if the thing contains ? or & , then section is the part before it
				if ($_tpos1 === false)
				{
					$_tpos = $_tpos2;
				}
				else
				if ($_tpos2 === false)
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

// siin oli aw_global_set("section",$section); mida EI TOHI siin olla

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

// other stuff

// hmmz. meeza thinks whe should only read/define those constants if we actually
// _need_ them. -- duke
// yeah. please to be findink the common ground between tpledit.aw and document.aw -- terryf

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
// public method
define("MN_PMETHOD",77);

// formide tyybid
define("FTYPE_ENTRY",1);
define("FTYPE_SEARCH",2);
define("FTYPE_FILTER_SEARCH",4);
define("FTYPE_CONFIG",5);

// formide alamtyybid
// subtype voiks bitmask olla tegelikult
define("FSUBTYPE_JOIN",1);

// kas seda vormi saab kasutada eventite sisestamiseks
// mingisse kalendrisse?
define("FSUBTYPE_EV_ENTRY",2);

// kas seda vormi saab kasutada vormi baasil e-maili
// actionite tegemiseks?
define("FSUBTYPE_EMAIL_ACTION",4);

// kas seda vormi kasutatakse kalendri ajavahemike defineerimiseks?
define("FSUBTYPE_CAL_CONF",8);

// kui see on otsinguvorm, siis kas otsingutulemusi filtreeritakse
// läbi kalendri?
define("FSUBTYPE_CAL_SEARCH",16);

// like CAL_CONF, but data is entered directly 
define("FSUBTYPE_CAL_CONF2",32);

// sum of all form & calendar settings, used to figure out
// whether a form has any relation to a calendar
define("FORM_USES_CALENDAR",58);


// object flags - bitmask
define("OBJ_HAS_CALENDAR",1);

// objektide subclassid - objects.subclass sees juusimiseks

// for CL_BROTHER_DOCUMENT 
define("SC_BROTHER_DOC_KEYWORD", 1);	// kui dokumendi vend on tehtud t2nu menuu keywordile

?>