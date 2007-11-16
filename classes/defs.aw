<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/defs.aw,v 2.246 2007/11/16 09:31:36 kristo Exp $
// defs.aw - common functions
if (!defined("DEFS"))
{
	define("DEFS",1);
	classload("php");
	define("SERIALIZE_PHP",1);
	define("SERIALIZE_XML",2);
	define("SERIALIZE_NATIVE",3);
	define("SERIALIZE_PHP_NOINDEX",4);
	define("SERIALIZE_XMLRPC", 5);
	define("SERIALIZE_PHP_FILE",6);

	function aw_register_ps_event_handler($class, $method, $params, $to_class)
	{
		$inf = array($class, $method, $params, $to_class);
		$id = md5(serialize($inf));
		$_SESSION["ps_event_handlers"][$id] = $inf;
		return $id;
	}

	/** returns the object of the currently active person
		@attrib api=1
	**/
	function get_current_person()
	{
		static $curp;
		if (!$curp)
		{
			$i = get_instance(CL_USER);
			$tmp = $i->get_current_person();
			if (is_oid($tmp) && !$i->can("view", $tmp))
			{
				$i->create_obj_access($tmp);
			}
			aw_disable_acl();
			$curp = obj($tmp);
			aw_restore_acl();
		}
		return $curp;
	}

	/** returns the object of the currently active company
		@attrib api=1
	**/
	function get_current_company()
	{
		static $curc;
		if (!$curc)
		{
			$i = get_instance(CL_USER);
			$tmp = $i->get_current_company();
			if ($tmp == false)
			{
				return false;
			}
			$curc = obj($i->get_current_company());
		}
		return $curc;
	}

	/** use this to get the correct return_url argument for GET requests

		@attrib api=1

	**/
	function get_ru()
	{
		return aw_ini_get("baseurl").aw_global_get("REQUEST_URI");
	}

	/** use this to get the correct return_url argument for POST requests

		@attrib api=1

	**/
	function post_ru()
	{
		return aw_url_change_var("post_ru", NULL, aw_ini_get("baseurl").aw_global_get("REQUEST_URI"));
	}

	// oh sweet lord what crappy ideas one has when time is short
	function convert_unicode($source)
	{
		$retval = str_replace(chr(195).chr(181), "&otilde;", $source);
		$retval = str_replace(chr(195).chr(149), "&Otilde;", $retval);

		$retval = str_replace(chr(195).chr(164), "&auml;", $retval);
		$retval = str_replace(chr(195).chr(132), "&Auml;", $retval);

		$retval = str_replace(chr(195).chr(188), "&uuml;", $retval);
		$retval = str_replace(chr(195).chr(156), "&Uuml;", $retval);

		$retval = str_replace(chr(195).chr(182), "&ouml;", $retval);
		$retval = str_replace(chr(195).chr(150), "&Ouml;", $retval);

		$retval = str_replace(chr(197).chr(161), "&scaron;", $retval);
		$retval = str_replace(chr(197).chr(160), "&Scaron;", $retval);

		// Zcaron;
		$retval = str_replace(chr(197).chr(189), chr(174), $retval);
		$retval = str_replace(chr(197).chr(190), chr(190), $retval);

		/*if (aw_global_get("uid") == "struktuur")
		{
			echo "source = $source <br>";
			for ($i = 0; $i < strlen($source); $i++)
			{
				echo "char at pos $i = ".ord($source{$i})." let = ".$source{$i}." <br>";
			}

		}*/

		$retval = str_replace(chr(154), "&Scaron;", $retval);

		return $retval;
	}

	/** returns a list of class id's that are "container" classes
		@attrib api=1

		@comment
			container classes are classes that in the admin interface, when you
			click on them, you go beneath them, not to their edit interface

		@returns
			array of class_id's
	**/
	function get_container_classes()
	{
		// classes listed here will be handled as containers where applicable
		return array(CL_MENU,CL_BROTHER,CL_PROMO,CL_GROUP,CL_MSGBOARD_TOPIC);
	}

	/** returns a link to the given object
		@attrib api=1

		@param oid required type=oid

		@comment
			when you need to present the user with a link that displays an object
			then give this function the oid and you get the link
	**/
	function obj_link($oid)
	{
		return aw_ini_get("baseurl")."/".$oid;
	}

	/** creates links from e-mail addresses in the given text
		@attrib api=1

		@param str required type=string

		@returns
			the given string, with e-mail aadresses replaced with <a href='mailto:address'>address</a>
	**/
	function create_email_links($str)
	{
		if (!aw_ini_get("menuedit.protect_emails"))
		{
			$str = preg_replace("/([-.a-zA-Z0-9_]*)@([-.a-zA-Z0-9_]*)/","<a href='mailto:\\1@\\2'>\\1@\\2</a>", $str);
		}
		return preg_replace("/((\s|^))((http(s?):\/\/)|(www\.))([a-zA-Z0-9\.\-\/_\?\&=;]+)/im", "$2<a href=\"http$5://$6$7\" target=\"_blank\">$4$6$7</a>", $str);
	}

	/** posts an AW message
		@attrib api=1

		@param msg required type=int
			The message to post

		@param params required type=any
			The parameters to pass to the message handler

		@comment
			The complete documentation regarding AW messages can be found at
			$AW_ROOT/docs/tutorials/components/aw_messaging

		@examples
			post_message(MSG_USER_LOGIN, array("uid" => $uid));
			// now all handlers that are subscribed to the message just got called
	**/
	function post_message($msg, $params)
	{
		if (aw_global_get("__in_post_message") > 0 && !aw_global_get("__allow_rec_msg"))
		{
			return;
		}

		aw_disable_messages();

		$inst = get_instance("core/msg/msg_dispatch");
		$inst->post_message(array(
			"msg" => $msg,
			"params" => $params
		));

		aw_restore_messages();
	}

	/** disables aw message sending
		@attrib api=1

		@comment
			When messages are disabled, calls to post_message and post_message_with_param do nothing.
			The calls to disable_messages / restore_messages can be nested
	**/
	function aw_disable_messages()
	{
		aw_global_set("__in_post_message", aw_global_get("__in_post_message")+1);
	}

	/** restores the previous aw message sending status
		@attrib api=1

		@comment
			Restores the previous state of the message sending flag.

		@examples
			aw_disable_messages();
			aw_disable_messages();
			post_message(MSG_USER_LOGIN, array());	// this will not get sent
			aw_restore_messages();
			post_message(MSG_USER_LOGIN, array());	// this will not get sent either
			aw_restore_messages();
			post_message(MSG_USER_LOGIN, array());	// this WILL get sent
	**/
	function aw_restore_messages()
	{
		aw_global_set("__in_post_message", aw_global_get("__in_post_message")-1);
	}

	/** enables sending recursive messages
		@attrib api=1

		@comment
			The default behaviour for aw messages is such, that when a message
			gets posted, while the execution is already inside a message handler, then
			the message is ignored.

			Using this function you can enable messages get posted from message handlers.
			The reason for theis behaviour is, that it is VERY easy to create message handlers
			that will trigger loops, so use this very carefully.

			The calls to aw_allow_recursive_messages() / aw_restore_recursive_messages() can be restored
	**/
	function aw_allow_recursive_messages()
	{
		aw_global_set("__allow_rec_msg", aw_global_get("__allow_rec_msg")+1);
	}

	/** restores the previous setting regarding recursive message sending
		@attrib api=1

		@comment
			Read the comment for the aw_allow_recursive_messages() function
	**/
	function aw_restore_recursive_messages()
	{
		aw_global_set("__allow_rec_msg", aw_global_get("__allow_rec_msg")-1);
	}

	function get_lc_date($time=0, $format=3)
	{
		$inst = get_instance("core/locale/".aw_global_get("LC")."/date", array(), false);
		if(!is_object($inst))
		{
			$inst = get_instance("core/locale/en/date");
		}
		return $inst->get_lc_date($time, $format);
	}

	/** posts an AW message with a message parameter
		@attrib api=1

		@param msg required type=int
			The message to post

		@param param required type=int
			The class id to pass as the message parameter

		@param params required type=any
			The parameters to pass to the message handler

		@comment
			The complete documentation regarding AW messages can be found at
			$AW_ROOT/docs/tutorials/components/aw_messaging

		@examples
			post_message_with_param(MSG_USER_LOGIN, array("uid" => $uid));
			// now all handlers that are subscribed to the message just got called
	**/
	function post_message_with_param($msg, $param, $params)
	{
		if (aw_global_get("__in_post_message") > 0 && !aw_global_get("__allow_rec_msg"))
		{
			return;
		}

		aw_disable_messages();

		$inst = get_instance("core/msg/msg_dispatch");
		$inst->post_message_with_param(array(
			"msg" => $msg,
			"param" => $param,
			"params" => $params
		));

		aw_restore_messages();
	}

	/** sends e-mail
		@attrib api=1

		@param to required type=string
			The address to send to

		@param subject required type=string
			The subject of the e-mail

		@param msg required type=string
			The content of the e-mail

		@param headers optional type=string
			The headers to add to the message

		@param arguments optional type=string
			The arguments to pass to sendmail

		@comment
			Replacement for php's mail(), so that we can always add headers of parameters to sendmail for every message sent via aw.
			So use this instead of mail()

		@examples
			send_mail("example@example.com", "example", "foo!");
	**/
	function send_mail($to,$subject,$msg,$headers="",$arguments="")
	{
		
		if (!is_email($to))
		{
			return;
		}
		if (empty($arguments))
		{
			$arguments = aw_ini_get("mail.arguments");
		};
		// from the PHP manual: Since PHP 4.2.3 this parameter is disabled in safe_mode  and the mail()
		// function will expose a warning message and return FALSE if you're trying to use it.
		if ((bool)ini_get("safe_mode"))
		{
			mail($to,$subject,$msg,$headers);
		}
		else
		{
			mail($to,$subject,$msg,$headers,$arguments);

		};
	}

	/** returns an array of all classes defined in the system
		@attrib api=1 params=name

		@param addempty optional type=bool
			Whether to add and empty element to the returned array or not. defaults to false.

		@param only_addable optional type=boool
			If true, only classes that can be added by the user are listed, if false, all classes. defaults to false

		@returns
			returns an array of all classes defined in the system, index is class id, value is class name and path

		@examples
			html::select(array(
				"name" => "select_class",
				"options" => get_class_picker(array("addempty" => true, "only_addable" => true))
			));
	**/
	function get_class_picker($arr = array())
	{
		extract($arr);
		$cls = aw_ini_get("classes");
		$clfs = aw_ini_get("classfolders");

		$ret = array();
		if ($addempty)
		{
			$ret = array(0 => "");
		}

		$field = ($field) ? $field : "name";

		$trans = array_flip(get_html_translation_table(HTML_ENTITIES));

		foreach($cls as $clid => $cld)
		{

			// what field? it's file
			//if (isset($cld['field']) && ($cld['field'] != ""))
			if (isset($cld['file']) && ($cld['file'] != ""))
			{
				$clname = strtr($cld[$field], $trans);
				if (isset($index))
				{
					$ret[$cld[$index]] = $clname;
				}
				else
				if ($only_addable)
				{
					if ($cld["can_add"] == 1)
					{
						$ret[$clid] = $clname;
					}
				}
				else
				{
					$ret[$clid] = $clname;
				};
			}
		}
		asort($ret);
		return $ret;
	}

	/** adds or changes a variable in the current or given url
		@attrib api=1

		@returns
			the url with variables changed as the parameters indicate

		@comment
			This function is probably the most versatile function ever in terms of the parameters it accepts.

		@examples
			$url = aw_url_change_var("a", "b"); // reads the current url and changes variable a to have value b
			$url = aw_url_change_var("c", "d", $url); // changes the value for variable d to d in the url in the variable $url
			$url = aw_url_change_var(array(
				"e" => "f",
				"g" => NULL
			)); // changes the variable e to f and removes the variable g from the current url and returns it
			$url = aw_url_change_var(array(
				"h" => "i",
				"j" => "k"
			), false, $url); // changes h to value j j to value k in the url in variable $url

	**/
	function aw_url_change_var($arg1, $arg2 = false, $url = false)
	{
		$arg_list = func_get_args();
		if (sizeof($arg_list) > 1 && $arg2 !== false)
		{
			$arg_list[0] = array($arg1 => $arg2);
		};
		if (!$url)
		{
			$url = aw_global_get("REQUEST_URI");
		};
		foreach($arg_list[0] as $arg1 => $arg2)
		{
			// remove old
			$url = preg_replace("/".preg_quote($arg1)."=[^&]*/","", $url);
			if (!empty($arg2))
			{
				$url .= (strpos($url,"?") === false ? "?" : "&" ).$arg1."=".urlencode($arg2);
				$url = preg_replace("/&{2,}/","&",$url);
			};
		};
		$url = str_replace('&&','&',$url);
		if ($url[strlen($url)-1] == "&")
		{
			$url = substr($url, 0, strlen($url)-1);
		}
		return $url;
	}

	/** generates a password with length $length
		@attrib api=1 params=name

		@param length optional type=int
			The length of the password to generate

		@returns
			The generated password. It can contain lower/uppercase letters, numbers and -_ chars

	**/
	function generate_password($arr = array())
	{
		extract($arr);
		if (!$length)
		{
			$length = 8;
		}
		if (empty($chars))
		{
			$chars = "1234567890-qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM_";
		}
		$pwd = "";
		for ($i = 0; $i < $length; $i++)
		{
			$pwd .= $chars{rand(0,strlen($chars)-1)};
		}
		return $pwd;
	}

	function create_safe_links($src)
	{
		// create link if it already is not part of an <a tag
		// but how the f*** do I do that
		$src = preg_replace("/(([a-zA-Z0-9 >]))((http(s?):\/\/)|(www\.))([a-zA-Z0-9\.\/%-]+)/im", "$2<a href=\"http$5://$6$7\" target=\"_blank\">$4$6$7</a>", $src);
		return $src;
	}

	/** places <a href tags around urls and e-mail addresses in text $src
		@attrib api=1

		@param src required type=text
			The text to create the links in

		@returns
			The given text with http://www.ee replaced with <a href="http://www.ee">http://www.ee</a> and
			foo@mail.ee replaced with <a href='mailto:foo@mail.ee'>foo@mail.ee</a>

	**/
	function create_links($src)
	{
		$src = preg_replace("/((\W|^))((http(s?):\/\/)|(www\.))(\S+)/im", "$2<a href=\"http$5://$6$7\" target=\"_blank\">$4$6$7</a>", $src);
		if (!aw_ini_get("menuedit.protect_emails"))
		{
			$src = preg_replace("/([\w*|\.|\-]*?)@([\w*|\.]*?)/imsU","<a href='mailto:$1@$2'>$1@$2</a>",$src);
		}
		return $src;
	}

	////
	// !parses template source in $src, and replaces variables in it with variables from $vars
	function localparse($src = "",$vars = array())
	{
		// kogu asendus tehakse �he reaga
		// "e" regexpi l�pus t�hendab seda, et teist parameetrit k�sitletakse php koodina,
		// mis eval-ist l�bi lastakse.
		$src = @preg_replace("/{VAR:(.+?)}/e","\$vars[\"\\1\"]",$src);
		$src = @preg_replace("/{DATE:(.+?)\|(.+?)}/e","((is_numeric(\$vars[\"\\1\"]) && \$vars[\"\\1\"] > 1 )? date(\"\\2\",\$vars[\"\\1\"]) : \"\")",$src);
		return @preg_replace("/{INI:(.+?)}/e","aw_ini_get(\"\\1\")",$src);
	}

	/** gives the given string to xml_parse_into_struct and returns the result
		@attrib api=1 params=name

		@param xml required type=string
			The xml to parse

		@returns
			array of the values and tags given by the xml parser

		@examples
			list($values, $tags) = parse_xml_def(array("xml" => $xml));

	**/
	function parse_xml_def($args)
	{
		// loome parseri
		$parser = xml_parser_create();

		// turn off the case folding:
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);

		$values = array();
		$tags = array();

		// xml data arraysse
		xml_parse_into_struct($parser,$args["xml"],&$values,&$tags);

		// R.I.P. parser
		xml_parser_free($parser);

		return array($values,$tags);
	};

	function get_lc_month($id)
	{
		if (aw_global_get("LC") == "et")
		{
			$mnames = explode("|","jaanuar|veebruar|m�rts|aprill|mai|juuni|juuli|august|september|oktoober|november|detsember");
			return $mnames[(int)$id-1];
		}
		$mnames = explode("|",LC_MONTH);
		return $mnames[(int)$id];
	}

	////
	// !tagastab lokaliseeritud p�evanime
	function get_lc_weekday($id)
	{
		return locale::get_lc_weekday($id);
	}

	/** creates html linebreaks in text
		@attrib api=1

		@param text required type=string
			The text to format

		@comment
			the difference with nl2br is, that nl2br inserts html tags before linebreaks
			but this replaces linebreaks with html tags

		@returns
			The formatted text

		@examples
			echo format_text("a\nb\n\nc"); // prints a<br />b<p>c
	**/
	function format_text($text)
	{
		$text = str_replace("\n\n","<p>",$text);
		$text = str_replace("\n","<br />",$text);
		return $text;
	}

	/** checks if the parameter is an oid
		@attrib api=1

		@param oid required type=any
			The value to check if it is a valid oid

		@comment
			This does NOT check if the object actually exists, it just checks if the parameter could be an object id.
			Valid object id's are integers that are greater than 0

		@returns
			true if the given value is a valid oid, false if not
	**/
	function is_oid($oid)
	{
		return is_numeric($oid) && $oid > 0;
	}

	/** checks if the parameter is a valid class_id
		@attrib api=1

		@param clid required type=int
			The value to check for a valid class_id

		@returns
			true if the parameter is a valid class id
			false if not
	**/
	function is_class_id($clid)
	{
		if (!is_numeric($clid))
		{
			return false;
		}
		return true;
	}

	/** checks if the string $string is a valid $set
		@attrib api=1

		@param set required type=string
			The type of string to check for - one of "password", "url", "uid"

		@param string required type=string
			The string to check for validity

		@returns
			true if the string is a valid string for the given set, false if not

		@examples
			if (is_valid("password",$pass_entered_in_a_form))
	**/
	function is_valid($set,$string)
	{
		if ($set == "password")
		{
			return true;
		}
		$sets = array(
			'password' => array(
				'content' => '1234567890qwertyuiopasdfghjklzxcvbnm_QWERTYUIOPASDFGHJKLZXCVBNM@',
				'min' => aw_ini_get("users.min_password_length"),
				'max' => 32),
			'url' => array(
				'content' => '1234567890qwertyuiopasdfghjklzxcvbnm-QWERTYUIOPASDFGHJKLZXCVBNM._',
				'min' => 3,
				'max' => 255),
			'uid'	=> array(
				'min' => 2,
				'content'	=> '1234567890qwertyuiopasdfghjklzxcvbnm_QWERTYUIOPASDFGHJKLZXCVBNM.@',
				'max' => 30)
			);
		// defineerimata character set, bail out
		if (!isset($sets[$set]))
		{
			return false;
		};
		$len = strlen($string);
		if ($len < $sets[$set]['min'] || $len > $sets[$set]['max'])
		{
			return false;
		}
		if (strspn($string,$sets[$set]['content']) != $len)
		{
			return false;
		}
		return true;
	}

	////
	// !kontrollime, kas parameeter on kuup�ev (kujul pp-kk-aaaa)
	function is_date($param)
	{
		$valid = preg_match("/^(\d{1,2}?)-(\d{1,2}?)-(\d{4}?)$/",$param,$parts);
		// p�evade arv < 0 ?
		if ($parts[1] < 0)
		{
			$valid = false;
		}
		// p�evi rohkem, kui selles kuus?
		else
		if ($parts[1] > date("t",mktime(0,0,0,$parts[2],1,$parts[3])) )
		{
			$valid = false;
		}
		else
		if ( ($parts[2] < 1) || ($parts[2] > 12) )
		{
			$valid = false;
		}
		return $valid;
	};

	////
	// !if a is between z and y, return true else return false
	// y < z
	function between($a,$y,$z, $onTrue = true, $onFalse = false)
	{
		if (($a >= $y) && ($a <= $z))
		{
			return $onTrue;
		}
		else
		{
			return $onFalse;
		}
	}

	/** check if the parameter is an e-mail address
	// !Kas argument on e-maili aadress?
	// Courtesy of martin@linuxator.com ;)**/
	function is_email ($address = "")
	{
		return preg_match('/([a-z0-9-]*((\.|_)?[a-z0-9]+)+@([a-z0-9]+(\.|-)?)+[a-z0-9]\.[a-z]{2,})/i',$address);
	}

	function is_admin()
	{
		return (stristr(aw_global_get("REQUEST_URI"),"/automatweb")!=false);
	}

	////
	// !Genereerib md5 hashi kas parameetrist voi suvalisest arvust.
	function gen_uniq_id($param = "")
	{
		return md5(uniqid('',true)); 
	};

	////
	// !Kasutamiseks vormides checkboxide kuvamise juures, vastavalt argumendi t�ev��rtusele
	// tagastab kas stringi "checked" voi t�hja stringi.
	function checked($arg)
	{
		return ($arg) ? "CHECKED" : "";
	}

	////
	// !Kasutamiseks vormides listboxide juures, vastavalt argumendi t�ev��rtusele
	// tagastab kas stringi "selected" voi t�hja stringi
	function selected($arg)
	{
		return ($arg) ? "SELECTED" : "";
	}

	////
	// !Kasutamiseks vormides elementide juures, mis v�ivad olla disabled olekus
	function disabled($arg)
	{
		return ($arg) ? "DISABLED" : "";
	}

	function arr($arr,$die=false,$see_html=false)
	{
		echo '<hr/>';
		$tmp = '';
		ob_start();
		print_r($arr);
		$tmp = ob_get_contents();
		ob_end_clean();
		echo '<pre style="text-align: left;">';
		echo $see_html?htmlspecialchars($tmp):$tmp;
		echo '</pre>';
		echo '<hr/>';
		if ($die)
		{
			die(t(''));
		}
		return $arr;
	}

	function d($arg)
	{
		arr($arg);
	}

        function ld($msg)
        {
                $core = new core();
                $msg =  is_array($msg)?$msg:array("dbg" => $msg);
                $msg["type"] = "REMOTE_DEBUG";
                $msg["origin"] = $_SERVER["SERVER_NAME"];
                $ret = $core->do_orb_method_call(array(
                        "class" => "file",
                        "action" => "handle_remote_dbg",
                        "params" => $msg,
                        "method" => "xmlrpc",
                        "server" => "tarvo.dev.struktuur.ee"
               ));
        }

	////
	// !j�rgmine funktsioon on inspireeritud perlist ;)
	// kasutusn�ide:
	//       print $object->map("--- %s ---\n",array("1","2","3"));
	// tulemus:
	//      --- 1 ---
	//      --- 2 ---
	//      --- 3 ---
	function map($format,$array)
	{
		$retval = array();
		if (is_array($array))
		{
			foreach($array as $val)
			{
				$retval[]= sprintf($format,$val,$val,$val);
			};
		}
		else
		{
			$retval[]= sprintf($format,$array);
		};
		return $retval;
	}

	////
	// !sama, mis eelmine, ainult et moodustuvad paarid
	// array iga elemendi indeksist ja v��rtusest
	// format peab siis sisaldama v�hemalt kahte kohta muutujate jaoks
	// kui $type != 0, siis p��ratakse array n� ringi ... key ja val vahetatakse �ra
	function map2($format,$array,$type = 0,$empty = false)
	{
		$retval = array();
		if (is_array($array))
		{
			while(list($key,$val) = each($array))
			{
				if ($type == 0)
				{
					$v1 = $key;
					$v2 = $val;
				}
				else
				{
					$v1 = $val;
					$v2 = $key;
				};
				if ((strlen($v1) > 0) && (strlen($v2) > 0) || $empty)
				{
					$retval[] = sprintf($format,$v1,$v2);
				};
			};
		}
		else
		{
			if ($array)
			{
				$retval[] = sprintf($format,$array);
			};
		};
		return $retval;
	}

	////
	// !returns the ip address of the current user. tries to bypass the users cache if it can
	function get_ip()
	{
		$ip = aw_global_get("HTTP_X_FORWARDED_FOR");
		if (!inet::is_ip($ip))
		{
			$ip = aw_global_get("REMOTE_ADDR");
		}
		return $ip;
	}

	////
	// !Let's deal with no name objects in one place
	function parse_obj_name($name)
	{
		$name = trim($name);
		$rv = empty($name) ? t("(nimetu)") : $name;
		$rv = str_replace('"',"&quot;", $rv);
		return $rv;
	}

	function parse_obj_name_ref(&$name)
	{
		$name = trim($name);
		$name = empty($name) ? t("(nimetu)") : $name;
		$name = str_replace('"',"&quot;", $name);
	}

	function aw_serialize($arr,$type = SERIALIZE_PHP, $flags = array())
	{
		switch($type)
		{
			case SERIALIZE_PHP:
				classload("php");
				$ser = new php_serializer;
				foreach($flags as $fk => $fv)
				{
					$ser->set($fk, $fv);
				}
				$str = $ser->php_serialize($arr);
				break;
			case SERIALIZE_PHP_FILE:
				classload("php_file");
				$ser = new php_serializer_file;
				foreach($flags as $fk => $fv)
				{
					$ser->set($fk, $fv);
				}
				$str = $ser->php_serialize($arr);
				break;
			case SERIALIZE_PHP_NOINDEX:
				classload("php");
				$ser = new php_serializer;
				$ser->set("no_index",1);
				$str = $ser->php_serialize($arr);
				break;

			case SERIALIZE_XML:
				classload("core/serializers/xml");
				$ser = new xml($flags);
				$str = $ser->xml_serialize($arr);
				break;

			case SERIALIZE_NATIVE:
				$str = serialize($arr);
				break;

			case SERIALIZE_XMLRPC:
				$ser = get_instance("core/orb/xmlrpc");
				$str = $ser->xmlrpc_serialize($arr);
		}

		return $str;
	}

	function aw_unserialize($str,$dequote = 0)
	{
		$retval = false;
		if ($dequote)
		{
			$str = stripslashes($str);
		};

		$magic_bytes = substr($str,0,6);

		if ($magic_bytes == "<?xml ")
		{
			classload("core/serializers/xml");
			$x = new xml;
			$retval = $x->xml_unserialize(array("source" => $str));
		}
		else
		if ($magic_bytes == "\$arr =")
		{
			// php serializer
			$p = new php_serializer;
			$retval = $p->php_unserialize($str);
		}
		else
		if ((strlen($str) > 0) && ($str{0} == "<"))
		{
			$ser = get_instance("core/orb/xmlrpc");
			$retval = $ser->xmlrpc_unserialize($str);
		}
		elseif (!empty($str))
		{

			$retval = unserialize($str);
		}
		return $retval;
	}


	/// I think we should just use the PHP superglobal $GLOBALS for storing
	// those variables instead of messing with our own objects. Empty it
	// first and then put variables we need into it.

	// oh, dammit. Shouldn't the aw_globals also be initalized and accesed
	// through the aw_dir/init.aw - ?

	// well. our own stuff kinda.. I dunno, feels better. but yeah, it also feels a lot slower.
	// and yeah. we shouldn't need these before aw_startup() and we could init it in there.. - terryf

	// .. and now they are.
	function _aw_global_init()
	{
		// reset aw_global_* function globals
		$GLOBALS["__aw_globals"] = array();

		// import CGI spec variables and apache variables

		// but we must do this in a certain order - first the global vars, then the session vars and then the server vars
		// why? well, then you can't override server vars from the url.

		// known variables - these can be modified by the user and are not to be trusted, so we get them first
		$impvars = array("lang_id","tafkap","DEBUG","no_menus","section","class","action","fastcall","reforb","set_lang_id","admin_lang","admin_lang_lc","LC","period","oid","print","sortby","sort_order","cal","date", "project", "view");
		foreach($impvars as $k)
		{
			if (isset($GLOBALS[$k]))
			{
				aw_global_set($k,$GLOBALS[$k]);
			}
		}


		// why don't we just use $_SESSION everywhere in the code where session variables ar used?

		// SESSION vars - these cannot be modified by the user except through aw, so they are relatively trustworthy
		if (is_array($_SESSION))
		{
			foreach($_SESSION as $k => $v)
			{
				aw_global_set($k,$v);
			}
		}
		aw_global_set("uid", isset($_SESSION["uid"]) ? $_SESSION["uid"] : "");

		// server vars - these can be trusted pretty well, so we do these last
		$server = array("SERVER_SOFTWARE", "SERVER_NAME", "GATEWAY_INTERFACE", "SERVER_PROTOCOL", "SERVER_PORT","REQUEST_METHOD",  "PATH_TRANSLATED","SCRIPT_NAME", "QUERY_STRING", "REMOTE_ADDR", "REMOTE_HOST", "HTTP_ACCEPT","HTTP_ACCEPT_CHARSET", "HTTP_ACCEPT_ENCODING", "HTTP_ACCEPT_LANGUAGE", "HTTP_CONNECTION", "HTTP_HOST", "HTTP_REFERER", "HTTP_USER_AGENT","REMOTE_PORT","SCRIPT_FILENAME", "SERVER_ADMIN", "SERVER_PORT", "SERVER_SIGNATURE", "PATH_TRANSLATED", "SCRIPT_NAME", "REQUEST_URI", "PHP_SELF", "DOCUMENT_ROOT", "PATH_INFO", "SERVER_ADDR", "HTTP_X_FORWARDED_FOR");

		// why don't we just use $_SERVER where needed?
		foreach($server as $var)
		{
			aw_global_set($var,isset($_SERVER[$var]) ? $_SERVER[$var] : null);
		}

		if (isset($_COOKIE["lang_id"]) && !isset($_SESSION["lang_id"]))
		{
			aw_global_set("lang_id", $_COOKIE["lang_id"]);
		}
		if (isset($_REQUEST))
		{
			aw_global_set("request",$_REQUEST);
		};
		$GLOBALS["__aw_globals_inited"] = true;
	}

	/**
		@attrib params=pos
		@param time
		time in seconds
		@comment
		sets the reload time for current page(in seconds)
	**/
	function set_page_reload($arg)
	{
		header("Refresh: ".$arg);
	}

	////
	// !this function replaces php's GLOBAL - it keeps global variables in a global object instance
	// why is this? well, because then they can't be set from the url, overriding the default values
	// and causing potential security problems
	function aw_global_get($var)
	{
		/*if ($var == "lang_id")
		{
			echo "$var is ".(isset($GLOBALS["__aw_globals"][$var]) ? $GLOBALS["__aw_globals"][$var] : false)."<br>";
		}*/
		return isset($GLOBALS["__aw_globals"][$var]) ? $GLOBALS["__aw_globals"][$var] : false;
	}

	function aw_global_set($var,$val)
	{
		/*if ($var == "lang_id")
		{
			echo "setto $val from ".dbg::process_backtrace(debug_backtrace());
		}*/
		$GLOBALS["__aw_globals"][$var] = $val;
	}

	////
	// !this replaces global caches - if you use this function, then cache contents cannot be overriden from the url
	function aw_cache_get($cache,$key)
	{
		if (is_array($key))
		{
			return false;
		}
		if (!isset($GLOBALS['__aw_cache']) || !is_array($GLOBALS['__aw_cache']))
		{
			$GLOBALS['__aw_cache'] = array();
			return false;
		}
		if (!isset($GLOBALS["__aw_cache"][$cache]) || !is_array($GLOBALS["__aw_cache"][$cache]))
		{
			return false;
		}
		return isset($GLOBALS["__aw_cache"][$cache][$key]) ? $GLOBALS["__aw_cache"][$cache][$key] : false;
	}

	function aw_cache_set($cache,$key,$val = "")
	{
		if (is_array($key))
		{
			return false;
		}
		if (!is_array($GLOBALS["__aw_cache"]))
		{
			$GLOBALS["__aw_cache"] = array($cache => array($key => $val));
		}
		else
		{
			// init it, if empty - kills warning
			if (empty($GLOBALS["__aw_cache"][$cache]))
			{
				$GLOBALS["__aw_cache"][$cache] = "";
			};

			if (!is_array($GLOBALS["__aw_cache"][$cache]))
			{
				$GLOBALS["__aw_cache"][$cache] = array($key => $val);
			}
			else
			{
				$GLOBALS["__aw_cache"][$cache][$key] = $val;
			}
		};
	}

	function aw_cache_flush($cache)
	{
		if (!is_array($GLOBALS["__aw_cache"]))
		{
			$GLOBALS["__aw_cache"] = array();
		}
		$GLOBALS["__aw_cache"][$cache] = false;
	}

	////
	// !this returns the entire cache array - this is useful for instance if you want to iterate over the cache
	function aw_cache_get_array($cache)
	{
		if (!is_array($GLOBALS["__aw_cache"]))
		{
			$GLOBALS["__aw_cache"] = array();
			return false;
		}
		return isset($GLOBALS["__aw_cache"][$cache]) ? $GLOBALS["__aw_cache"][$cache] : false;
	}

	////
	// !this is for initializing the cache
	function aw_cache_set_array($cache,$arr)
	{
		if (!is_array($GLOBALS["__aw_cache"]))
		{
			$GLOBALS["__aw_cache"] = array($cache => $arr);
		}
		else
		{
			$GLOBALS["__aw_cache"][$cache] = $arr;
		}
	}

	////
	// !this replaces global caches - if you use this function, then cache contents cannot be overriden from the url
	function aw_session_cache_get($cache,$key)
	{
		if (!is_array($_SESSION["__aw_sess_cache"]))
		{
			return false;
		}
		if (!isset($_SESSION["__aw_sess_cache"][$cache]) || !is_array($_SESSION["__aw_sess_cache"][$cache]))
		{
			return false;
		}
		return isset($_SESSION["__aw_sess_cache"][$cache][$key]) ? $_SESSION["__aw_sess_cache"][$cache][$key] : false;
	}

	function aw_session_cache_set($cache,$key,$val = "")
	{
		// if $key is array, we will just stick it into the cache.
		// NO!! that's what aw_cache_set_array() is for!!! - terryf
		if (!is_array($_SESSION["__aw_sess_cache"]))
		{
			$_SESSION["__aw_sess_cache"] = array($cache => array($key => $val));
		}
		else
		if (!is_array($_SESSION["__aw_sess_cache"][$cache]))
		{
			$_SESSION["__aw_sess_cache"][$cache] = array($key => $val);
		}
		else
		{
			$_SESSION["__aw_sess_cache"][$cache][$key] = $val;
		}
	}

	function aw_session_cache_flush($cache)
	{
		if (!is_array($_SESSION["__aw_sess_cache"]))
		{
			$_SESSION["__aw_sess_cache"] = array();
		}
		$_SESSION["__aw_sess_cache"][$cache] = false;
	}

	////
	// !saves a local variable's value to the session - there is no session_get, because
	// session vars are automatically registered as globals as well, so for retrieval you can use aw_global_get()
	function aw_session_set($name,$value)
	{
		if (headers_sent())
		{
			return false;
		};
		if (empty($value))
		{
			return false;
		};
		$GLOBALS[$name] = $value;
		$_SESSION[$name] = $value;
		aw_global_set($name,$value);
	}

	////
	// !deletes the variable $name from the session
	function aw_session_del($name, $leave_global = false)
	{
		unset($_SESSION[$name]);
		if (!$leave_global)
		{
			aw_global_set($name, "");
			unset($GLOBALS[$name]);
		}
	}

	////
	// !deletes all variables from the session that match preg pattern $pattern
	function aw_session_del_patt($pattern)
	{
		foreach($GLOBALS["HTTP_SESSION_VARS"] as $vn => $vv)
		{
			if (preg_match($pattern, $vn))
			{
				aw_session_del($vn);
			}
		}
	}

	function aw_register_default_class_member($class, $member, $value)
	{
		$members = aw_cache_get("__aw_default_class_members", $class);
		$members[$member] = $value;
		aw_cache_set("__aw_default_class_members", $class, $members);
	}

	/** temporarily switches the current user to $arr[uid]


	**/
	function aw_switch_user($arr)
	{
		$old_uids = aw_global_get("old_uids");
		if (!is_array($old_uids))
		{
			$old_uids = array();
		}
		array_push($old_uids, aw_global_get("uid"));
		aw_global_set("old_uids", $old_uids);

		__aw_int_do_switch_user($arr["uid"]);
	}

	function __aw_int_do_switch_user($uid)
	{
		aw_global_set("uid", $uid);
		$us = get_instance("users");
		$us->request_startup();
		// also, flush acl cache !
		aw_cache_flush("aclcache");
		aw_cache_flush("__aw_acl_cache");
	}

	function aw_restore_user()
	{
		$old_uids = aw_global_get("old_uids");
		if (!is_array($old_uids))
		{
			$old_uids = array();
		}
		__aw_int_do_switch_user(array_pop($old_uids));
		aw_global_set("old_uids", $old_uids);
	}

	function aw_disable_acl()
	{
		$GLOBALS["__aw_disable_acl"] = $GLOBALS["cfg"]["acl"]["no_check"];
		$GLOBALS["cfg"]["acl"]["no_check"] = 1;
	}

	function aw_restore_acl()
	{
		$GLOBALS["cfg"]["acl"]["no_check"] = $GLOBALS["__aw_disable_acl"];
	}

	function clid_for_name($class_name)
	{
		return aw_ini_get("class_lut.".$class_name);
	}

	function aw_register_header_text_cb($cb)
	{
		aw_global_set("__aw.header_text_cb", $cb);
	}

	function aw_call_header_text_cb()
	{
		$cb = aw_global_get("__aw.header_text_cb");
		if (is_array($cb))
		{
			return $cb[0]->$cb[1]();
		}
		else
		if ($cb != "")
		{
			return $cb();
		}
		return "";
	}

	function warning_prop($level = false, $oid = false, $prop = false)
	{
		static $prop_warnings;
		if(!$level && !$oid && !$prop)
		{
			return $prop_warnings;
		}
		//$GLOBALS["prop_warnings"][$oid][$prop] = $level;
		$prop_warnings[$oid][$prop] = $level;
	}

	function warning($msg = false, $level = 1)
	{
		static $gen_warnings;
		if(!$msg)
		{
			return $gen_warnings;
		}
		//$GLOBALS["general_warnings"][$level][] = $msg;
		$gen_warnings[$level][] = $msg;
	}

	function get_active($clid)
	{

                $pl = new object_list(array(
                        "class_id" => $clid,
                ));
		if(!$pl->count())
		{
			return false;
		}
                for($o = $pl->begin(); !$pl->end(); $o = $pl->next())
                {
                        if($o->flag(OBJ_FLAG_IS_SELECTED))
                        {
                                break;
                        }
                }
		return $o;
	}

	////
	// !all network functions go in here, all must be static
	class inet
	{
		////
		// !resolvib ip aadressiks. cacheb kiiruse huvides tulemusi
		// voib kasutada ntx syslogi juures
		// tagastab 2 elemendiga array, esimene on lahendatud
		// nimi, teine aadress, mis ette anti voi stringist v�lja
		// parsiti
		function gethostbyaddr($addr)
		{
			// *wink terryf*, kena regexp mis?
			// idee on selles, et parsib lahti ntx syslogis olevad
			// aadressid kujul host.ee / 1.2.3.4
			if (preg_match("/^(.*?)\s*?\/\s+?([0-9\.]+?)$/",$addr,$parts))
			{
				$addr = $parts[2];
			};
			if (!($ret = aw_cache_get("solved",$addr)))
			{
				$ret = gethostbyaddr($addr);
				aw_cache_set("solved",$addr,$ret);
			};
			return array($ret,$addr);
		}

		////
		// !returns the ip address that resolves to $name
		function name2ip($name)
		{
			if (!($ret = aw_cache_get("name2ip_solved",$name)))
			{
				$ret = gethostbyname($name);
				aw_cache_set("nam2ip_solved",$name,$ret);
			};
			return $ret;
		}

		////
		// !kontrollime, kas parameeter on ikka IP aadress
		function is_ip($addr)
		{
			// match 1 to 3 digits
			$oct = "(\d{1,3}?)";
			$valid = preg_match("/^$oct\.$oct\.$oct\.$oct$/",$addr,$parts);
			// kontrollime, ega ei ole tegemist bcast aadressiga
			if (isset($parts[4]) && ( ($parts[4] == 0) || ($parts[4] == 255) ))
			{
				// ongi.
				$valid = false;
			};

			if (isset($parts[1]) && $parts[1] == 0)
			{
				$valid = false;
			};

			if ($valid)
			{
				// kontrollime, kas koik oktetid on ikka lubatud vahemikus
				for ($i = 1; $i <= 4; $i++)
				{
					if ( ($parts[$i] < 0) || ($parts[$i] > 255) )
					{
						$valid = false;
					};
				};
			};
			return $valid;
		}
	}

	class dbg
	{
		// debuukimisel on see funktsioon abiks
		function dump($data)
		{
			ob_start();
			print "<pre>";
			var_dump($data);
			print "</pre>";
			$ret = ob_get_contents();
			ob_end_clean();
			return $ret;
		}

		function str_dbg($str)
		{
			echo "str = $str <br>";
			for($i = 0; $i < strlen($str); $i++)
			{
				echo "at pos $i: ".$str{$i}." nr = ".ord($str{$i})." <br>";
			}
			echo "---<br>";
		}

		////
		// !prints the message if $GLOBALS["DEBUG"] == 1, basically the same as core::dmsg, but with this you don't need to fiddle
		// around with sessions
		function p($msg)
		{
			if (aw_global_get("DEBUG") == 1)
			{
				echo $msg."<br />\n";
			}
		}

		// prints if the user has a cookie named debug1
		function p1($msg)
		{
			if ($_COOKIE["debug1"])
			{
				arr($msg);
			}
		}

		// prints if the user has a cookie named debug2
		function p2($msg)
		{
			if ($_COOKIE["debug2"])
			{
				echo $msg."<br />\n";
			}
		}

		// prints if the user has a cookie named debug3
		function p3($msg)
		{
			if ($_COOKIE["debug3"])
			{
				echo $msg."<br />\n";
			}
		}

		// prints if the user has a cookie named debug4
		function p4($msg)
		{
			if ($_COOKIE["debug4"])
			{
				echo $msg."<br />\n";
			}
		}

		// prints if the user has a cookie named debug5
		function p5($msg)
		{
			if ($_COOKIE["debug5"])
			{
				print "<pre>";
				var_dump($msg);
				print "</pre>";
			}
		}

		function process_backtrace($bt, $skip = -1)
		{
			$msg .= "<br><br> Backtrace: \n\n<Br><br>";
			for ($i = count($bt)-1; $i > $skip; $i--)
			{
				if ($bt[$i+1]["class"] != "")
				{
					$fnm = "method <b>".$bt[$i+1]["class"]."::".$bt[$i+1]["function"]."</b>";
				}
				else
				if ($bt[$i+1]["function"] != "")
				{
					$fnm = "function <b>".$bt[$i+1]["function"]."</b>";
				}
				else
				{
					$fnm = "file ".$bt[$i]["file"];
				}

				$msg .= $fnm." on line ".$bt[$i]["line"]." called <br>\n";

				if ($bt[$i]["class"] != "")
				{
					$fnm2 = "method <b>".$bt[$i]["class"]."::".$bt[$i]["function"]."</b>";
				}
				else
				if ($bt[$i]["function"] != "")
				{
					$fnm2 = "function <b>".$bt[$i]["function"]."</b>";
				}
				else
				{
					$fnm2 = "file ".$bt[$i]["file"];
				}

				$msg .= $fnm2." with arguments ";

				$awa = new aw_array($bt[$i]["args"]);
				$msg .= "<font size=\"-1\">(".htmlentities(join(",", $awa->get())).") file = ".$bt[$i]["file"]."</font>";

				$msg .= " <br><br>\n\n";
			}
			return $msg;
		}

		function short_backtrace()
		{
			$msg = "";
			if (function_exists("debug_backtrace"))
			{
				$bt = debug_backtrace();
				for ($i = count($bt); $i >= 0; $i--)
				{
					if ($bt[$i+1]["class"] != "")
					{
						$fnm = $bt[$i+1]["class"]."::".$bt[$i+1]["function"];
					}
					else
					if ($bt[$i+1]["function"] != "")
					{
						if ($bt[$i+1]["function"] != "include")
						{
							$fnm = $bt[$i+1]["function"];
						}
						else
						{
							$fnm = "";
						}
					}
					else
					{
						$fnm = "";
					}

					$msg .= $fnm.":".$bt[$i]["line"]."->";
				}
			}

			return $msg;
		}

		function q($q)
		{
			$first = true;
			$GLOBALS["__aw_globals"]["db::DBMAIN"]->db_query($q);
			while ($row = $GLOBALS["__aw_globals"]["db::DBMAIN"]->db_next())
			{
				echo "********** Row nr ".++$cnt." *****************\n";
				foreach($row as $k => $v)
				{
					echo "$k: $v\n";
				}
			}
			echo "\n";
		}
	}

	// wrapper for localization classes
	class locale
	{
		var $default_locale = "en";
		var $lc_date_inst = false;
		function locale()
		{
			$this->lc_date_inst = @get_instance("core/locale/".aw_global_get("LC")."/date", array(), false);
			if(!is_object($this->lc_date_inst))
			{
				$this->lc_date_inst = get_instance("core/locale/" . ($this->default_locale ? $this->default_locale : "en") . "/date");
			};
		}

		function get_lc_weekday($num, $short = false, $ucfirst = false)
		{
			static $lc_date_inst;
			$lc_date_inst = @get_instance("core/locale/".aw_global_get("LC")."/date", array(), false);
			if(!is_object($lc_date_inst))
			{
				$lc_date_inst = get_instance("core/locale/en/date");
			};
			if (method_exists($lc_date_inst,"get_lc_weekday"))
			{
				return $lc_date_inst->get_lc_weekday($num,$short,$ucfirst);
			}
			else
			{
				return "";
			};
		}

		function get_lc_month($num)
		{
			static $lc_date_inst;
			$lc_date_inst = @get_instance("core/locale/".aw_global_get("LC")."/date", array(), false);
			if(!is_object($lc_date_inst))
			{
				$lc_date_inst = get_instance("core/locale/en/date");
			};
			if (method_exists($lc_date_inst,"get_lc_month"))
			{
				return $lc_date_inst->get_lc_month($num);
			}
			else
			{
				return "";
			};
		}

		function get_lc_date($timestamp,$format)
		{
			static $lc_date_inst;
			$lc_date_inst = @get_instance("core/locale/".aw_global_get("LC")."/date", array(), false);
			if(!is_object($lc_date_inst))
			{
				$lc_date_inst = get_instance("core/locale/en/date");
			};
			if (method_exists($lc_date_inst,"get_lc_date"))
			{
				return $lc_date_inst->get_lc_date($timestamp,$format);
			}
			else
			{
				return "";
			};
		}

		function get_lc_number($number)
		{
			static $lc_date_inst;
			$lc_date_inst = @get_instance("core/locale/".aw_global_get("LC")."/number", array(), false);
			if(!is_object($lc_date_inst))
			{
				$lc_date_inst = get_instance("core/locale/en/number");
			};
			if (method_exists($lc_date_inst,"get_lc_number"))
			{
				return $lc_date_inst->get_lc_number($number);
			}
			else
			{
				return $number;
			};
		}

		function get_lc_money_text($number, $currency, $lc = NULL)
		{
			if (!$lc)
			{
				$lc = aw_global_get("LC");
			}

			static $lc_date_inst;
			$lc_date_inst[$lc] = @get_instance("core/locale/".$lc."/number", array(), false);
			if(!is_object($lc_date_inst[$lc]))
			{
				$lc_date_inst[$lc] = get_instance("core/locale/" .$lc. "/number");
			};
			if (method_exists($lc_date_inst[$lc],"get_lc_money_text"))
			{
				return $lc_date_inst[$lc]->get_lc_money_text($number, $currency);
			}
			else
			{
				return $number;
			};
		}

	}


	/**
		The class is defined in the base class framework, so it is always available and therefore you cannot use get_instance to instance it, but must use
		$inst = new aw_array();
		The class is meant to make array handling a bit easier and to avoid statements like
		if (is_array($arr))
		// do_something

		wrapper for arrays - helps to get rid of numerous is_array checks
		in code and reduces the amount of indenting
	**/
	class aw_array
	{
		/** aw_array is a class provided by the AutomatWeb base system and that simplifies php's array management.
			@attrib api=1

			@param arr optional type=array
				if this argument is specified, it is assumed to be a php array that the aw_array object will contain

			@comment
				The constructor, can initialize the class with an array

			@examples
				$arr = new aw_array($arr["request"]["some_array"]);
				foreach($arr->get() as $key => $value)
				{
				...
				}
		**/
		function aw_array($arg = false)
		{
			if (is_array($arg))
			{
				$this->arg = $arg;
			}
			elseif ($arg)
			{
				$this->arg = array($arg);
			}
			else
			{
				$this->arg = array();
			};
			reset($this->arg);
		}

		/** returns the contents of the aw_array as a php array
			@attrib api=1
		**/
		function &get()
		{
			return $this->arg;
		}

		/** returns the value in the array at the position specified by $key
			@attrib api=1

			@examples
				$tmpmatch = new aw_array();
				$this->db_query("SELECT DISTINCT(objects.oid) AS oid FROM objects WHERE $wis ");
				while($match = $this->db_next())
				{
					 $tmpmatch->set($match["oid"]);
				};

				// now we need to make sure that we get only one list member per form entry
				$usedeids = new aw_array();
				$this->db_query("SELECT entry_id, member_id FROM ml_member2form_entry WHERE member_id IN(".$tmpmatch->to_sql().")");
				while ($row = $this->db_next())
				{
					 if (!$usedeids->get_at($row["entry_id"]))
					 {
					  $matches[$id][]=$row["member_id"];
					  $usedeids->set_at($row["entry_id"], true);
					 }
				}

		**/
		function get_at($key)
		{
			return $this->arg[$key];
		}

		/** adds the value of $val to the end of the array (does what the php expression $arr[] = $val would do if $arr was a regular php array)
			@attrib api=1

			@examples
				$tmpmatch = new aw_array();
				$this->db_query("SELECT DISTINCT(objects.oid) AS oid FROM objects WHERE $wis ");
				while($match = $this->db_next())
				{
					 $tmpmatch->set($match["oid"]);
				};
		**/
		function set($val)
		{
			$this->arg[] = $val;
		}

		/** sets the value at position $key in the array to $val
			@attrib api=1

			@examples
				$tmpmatch = new aw_array();
				$this->db_query("SELECT DISTINCT(objects.oid) AS oid FROM objects WHERE $wis ");
				while($match = $this->db_next())
				{
					 $tmpmatch->set($match["oid"]);
				};

				// now we need to make sure that we get only one list member per form entry
				$usedeids = new aw_array();
				$this->db_query("SELECT entry_id, member_id FROM ml_member2form_entry WHERE member_id IN(".$tmpmatch->to_sql().")");
				while ($row = $this->db_next())
				{
					 if (!$usedeids->get_at($row["entry_id"]))
					 {
						  $matches[$id][]=$row["member_id"];
						  $usedeids->set_at($row["entry_id"], true);
					 }
				}
		**/
		function set_at($key, $val)
		{
			$this->arg[$key] = $val;
		}

		/** returns the next array(key, value) from the array. Only one iteration per aw_array instance can be active at one time. if there are no more members, false is returned
			@attrib api=1
		**/
		function next()
		{
			return each($this->arg);
		}

		/** resets the internal iterator for the current aw_array instance, after this, next() will return the first key/value pair of the aw_array
			@attrib api=1
		**/
		function reset()
		{
			reset($this->arg);
		}

		function key_exists($key)
		{
			return isset($this->arg[$key]);
		}

		function first()
		{
			$this->reset();
			return $this->next();
		}

		/** returns the contents of the aw_array in a form suitable to be inserted into an SQL IN() clause. basically it returns a string that contains all the values of the array separated by commas. if the array is empty, the string NULL will be returned, so that no value will be matched by the resulting SQL statement
			@attrib api=1

			@example
				$tmpmatch = new aw_array();
				$this->db_query("SELECT DISTINCT(objects.oid) AS oid FROM objects WHERE $wis ");
				while($match = $this->db_next())
				{
					 $tmpmatch->set($match["oid"]);
				};

				// now we need to make sure that we get only one list member per form entry
				$usedeids = new aw_array();
				$this->db_query("SELECT entry_id, member_id FROM ml_member2form_entry WHERE member_id IN(".$tmpmatch->to_sql().")");
				while ($row = $this->db_next())
				{
				 if (!$usedeids->get_at($row["entry_id"]))
				 {
				  $matches[$id][]=$row["member_id"];
				  $usedeids->set_at($row["entry_id"], true);
				 }
				}

		**/
		function to_sql()
		{
			$data = array_values($this->arg);
                        foreach($data as $k => $v)
                        {
                                $data[$k] = addslashes($v);
                        }

			$str = join(",",map("'%s'", $data));
			if ($str == "")
			{
				return "NULL";
			}
			return $str;
		}

		function count()
		{
			return count($this->arg);
		}
	};

	function do_nothing()
	{

	}

	/** returns the parameter or an array if the parameter is not an array

		@attrib api=1
	**/
	function safe_array($var)
	{
		if (is_array($var))
		{
			return $var;
		}
		return array();
	}

	/**
		@comment
			Works like php's array_merge, with a little difference. when array_merge reindexes numeric array keys, then aw_merge doens't
	**/
	function aw_merge()
	{
	     	if(($argc = func_num_args()) < 1)
	     	{
	     		return false;
	     	}
         	foreach(func_get_args() as $k => $array)
		{
                 	foreach($array as $k => $v)
                 	{
                        	$retval[$k] = $v;
                        }
		}
		return $retval;
 	}
 
	/**
		@comment
			Same as aw_merge, but does the same thing recursevly through array
	**/	
 	function req_aw_merge()
 	{
        	if(($argc = func_num_args()) < 1)
                {
                	return false;
                }
         	foreach(func_get_args() as $k => $array)
         	{
                	foreach($array as $k => $v)
                	{
                        	if(is_array($v))
                        	{
                                	$retval[$k] = aw_merge($retval[$k], req_aw_merge($v));
                         	}
                         	else
                         	{
                                	$retval[$k] = $v;
                                }
                        }
                }
		return $retval;
	}

	/** returns admin_rootmenu2 setting - always an integer, even if it is an array

		@attrib api=1
	**/
	function cfg_get_admin_rootmenu2()
	{
		$ret = $GLOBALS["cfg"]["admin_rootmenu2"];
		if (is_array($ret))
		{
			return reset($ret);
		}
		return $ret;
	}

	if (!function_exists("cal_days_in_month"))
	{
		function cal_days_in_month($type, $month, $year)
		{
			return date("j",mktime(0,0,0,$month+1,0,$year));
		}
	}

	if (!function_exists("strptime"))
	{
		function strptime($string, $format)
		{
			$hour = $minute = $second = $month = $day;
			$year = date('Y');

			if (preg_match('=(%[a-zA-Z]%[a-zA-Z])=', $format))
			{
				trigger_error("format string needs to have delimiting special chars between format chars");
				return 0;
			}

			$format = str_replace(
				array('%%', '%r',             '%R',       '%T'),
				array('',   '%I:%M:%S %p',    '%H:%M',    '%H:%M:%S'),
				$format
			);
			$string = str_replace('%', '', $string);

			// %b - abbreviated month name
			if ($tmp = strptime_extract($format, $string, 'b'))
			{
				for($i = 1, $months = array(); $i <= 12; $i++)
				{
					$months[$i] = strftime('%b', mktime(0, 0, 0, $i));
				}
				$month = array_search($tmp, $months);
			}

			// %B - full month name
			if ($tmp = strptime_extract($format, $string, 'B'))
			{
				for($i = 1, $months = array(); $i <= 12; $i++)
				{
					$months[$i] = strftime('%B', mktime(0, 0, 0, $i));
				}
				$month = array_search($tmp, $months);
			}
																		// %d - day of month, two digits 01-31
			if ($tmp = strptime_extract($format, $string, 'd'))
			{
				$day = $tmp;
			}
			// %H - hour, two digits 00-23
			if ($tmp = strptime_extract($format, $string, 'H'))
			{
				$hour = $tmp;
			}
			// %I - hour, two digits 01-12
			if ($tmp = strptime_extract($format, $string, 'I'))
			{
				$hour = $tmp;
				if (strptime_extract($format, $string, 'p') == strftime('%p', mktime(13)))
				{
					$hour += 12;
				}
			}

			// %m - month number, two digits 01,12
			if ($tmp = strptime_extract($format, $string, 'm'))
			{
				$month = $tmp;
			}

			// %M - minute, two digits 00-59
			if ($tmp = strptime_extract($format, $string, 'M'))
			{
				$minute = $tmp;
			}
			// %S - second, two digits 00-61
			if ($tmp = strptime_extract($format, $string, 'S'))
			{
				$second = $tmp;
			}

			// %y - year as a decimal number without a century (range 00 to 99)
			if ($tmp = strptime_extract($format, $string, 'y'))
			{
				$year = $tmp;
				if ($year >= 70)
				{
					$year += 1900;
				}
				else
				{
					$year += 2000;
				}
			}

			// %Y - year as a decimal number including the century
			if ($tmp = strptime_extract($format, $string, 'Y'))
			{
				$year = $tmp;
			}
			return mktime($hour, $minute, $second, $month, $day, $year);
		}

		function strptime_extract($format, $string, $char)
		{
			if (!$pos = strpos($format, $char))
			{
				return false;
			}

			$tmp = substr($format, 0, $pos);
			if (preg_match_all('=([^a-zA-Z0-9%])=', $tmp, $m))
			{
				while($char = array_shift($m[1]))
				{
					$string = substr($string, strpos($string, $char) + 1);
				}
			}

			list($val) = preg_split('=([^a-zA-Z0-9%])=', $string, 2);
			return $val;
		}
	}
};
?>
