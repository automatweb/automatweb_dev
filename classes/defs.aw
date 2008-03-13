<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/defs.aw,v 2.258 2008/03/13 13:26:22 kristo Exp $
// defs.aw - common functions

/*
EMIT_MESSAGE(MSG_MAIL_SENT)
@classinfo maintainer=kristo
*/

if (!defined("DEFS"))
{
	define("DEFS",1);
	define("SERIALIZE_PHP",1);
	define("SERIALIZE_XML",2);
	define("SERIALIZE_NATIVE",3);
	define("SERIALIZE_PHP_NOINDEX",4);
	define("SERIALIZE_XMLRPC", 5);
	define("SERIALIZE_PHP_FILE",6);

	/** registers a post-submit handler callback
		@attrib api=1 params=pos

		@param class required type=string or class_id
			The name of the class the callback belongs to

		@param method requierd type=string
			The method in the class to call
			
		@param params required type=array
			Additional parameters to pass to the handler method

		@param to_class required type=class_id
			The class of object that should be added before the ps event handler is called


		@comment
			Sometimes you need to provide a link for the user to add some object and after the user has added it, you need to do something with it. With this you can. 
			You can register an action with this method and recieve a handler id. Then you must add it to the url, as the parameter "pseh". Now, when the user clicks the link, fills in the form and thus creates the object, the callback will be called with the new data object as the first parameter and the array you gave as the second parameter

		@examples
			class handler 
			{
				function handler($obj_inst, $params)
				{
					$obj_inst->set_name($params["name"]);
					$obj_inst->save();
				}		
			}
			
			.... somewhere else...
			echo html::href(array(
				"caption" => t("Click me"),
				"url" => $this->mk_my_orb("new", array(
					"parent" => 6,
					"pseh" => aw_register_ps_event_handler(
						"handler", 
						"handler", 
						array("name" => "allah", 
						CL_MENU
					)
				), CL_MENU)
			));

			now, all menus that are created via this link, get the name "allah", in spite of what the user types in the name box
	**/
	function aw_register_ps_event_handler($class, $method, $params, $to_class)
	{
		$inf = array($class, $method, $params, $to_class);
		$id = md5(serialize($inf));
		$_SESSION["ps_event_handlers"][$id] = $inf;
		return $id;
	}

	/** returns the object of the currently active person
		@attrib api=1

		@comment
			When a user is logged in, then this method never fails - if the person for the current user does not exist yet, it gets created automatically.
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

		@comment
			When a user is logged in, then this method never fails - if the company for the current user does not exist yet, it gets created automatically.
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

		@examples
			// after clicking this, the "back" link in the admin interface is created correctly
			echo html::href(array(
				"caption" => t("Loo uus objekt"),
				"url" => $this->mk_my_orb("new", array("parent" => 6, "return_url" => get_ru()), CL_MENU)
			));
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

	// DEPRECATED - DO NOT USE!
	// oh sweet lord what crappy ideas one has when time is short
	// crickey, mate! this really is terrible. - terryf
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

		@comment
			If the text already contains <a href='mailto: 's then it will double them, thus breaking html
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
			The reason for this behaviour is, that it is VERY easy to create message handlers
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

			This also posts the MSG_MAIL_SENT aw message, so you can do stuff when mails get sent

		@examples
			send_mail("example@example.com", "example", "foo!");
	**/
	function send_mail($to,$subject,$msg,$headers="",$arguments="")
	{
//	echo "enter send to = $to , subj = $subject , msg:<pre>$headers\n$msg</pre>";
		if (!is_email($to))
		{
			return;
		}
		preg_match("/From\: (.*)/im", $headers, $mt);
		$from = $mt[1];

		if (aw_ini_get("mail.use_smtp"))
		{
			$smtp = get_instance("protocols/mail/smtp");
			$smtp->send_message(
				aw_ini_get("mail.smtp_server"),
				$from,
				$to,
				trim($headers)."\nX-Mailer: AutomatWeb\nTo: $to\nSubject: ".$subject."\n\n".$msg
			);
		}
		else
		{
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

		$bt = debug_backtrace();
		// find the sender app from the backtrace
		$app = $bt[1]["class"];
		if ($app == "aw_mail")
		{
			$app = $bt[2]["class"];
		}

		if ($app == "")
		{
			$app = $bt[1]["file"].":".$bt[1]["line"];
		}
		post_message(MSG_MAIL_SENT, array(
			"from" => $from,
			"to" => $to,
			"subject" => $subject,
			"headers" => $headers,
			"arguments" => $arguments,
			"content" => $msg,
			"app" => $app
		));
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
		$url = str_replace("?&", "?", $url);
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

	/** places <a href tags around urls in text $src
		@attrib api=1

		@param src required type=text
			The text to create the links in

		@comment
			the difference between this, and create_links is, that this tries to make sure, that if there already is an <a href around the url, it will not double it. 
			It does fail quite miserably though. 

		@returns
			The given text with http://www.ee replaced with <a href="http://www.ee">http://www.ee</a>
	**/
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
		$src = preg_replace("/((\W|^))((http(s?):\/\/)|(www\.))([^\s\)]+)/im", "$2<a href=\"http$5://$6$7\" target=\"_blank\">$4$6$7</a>", $src);
		if (!aw_ini_get("menuedit.protect_emails"))
		{
			$src = preg_replace("/([\w*|\.|\-]*?)@([\w*|\.]*?)/imsU","<a href='mailto:$1@$2'>$1@$2</a>",$src);
		}
		return $src;
	}

	/** Replace template variables in the given string
		@attrib api=1 params=pos

		@param src optional type=string
			The template string

		@param vars optional type=array
			The list of variable values

		@comment
			The aw_template parser uses this to actually insert values into templates

		@examples
			$str = "a = {VAR:value} ";
			echo localparse($str, array("value" => 5));	// echoes "a = 5"
	**/
	function localparse($src = "",$vars = array())
	{
		// kogu asendus tehakse ühe reaga
		// "e" regexpi lõpus tähendab seda, et teist parameetrit käsitletakse php koodina,
		// mis eval-ist läbi lastakse.
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
				'content'	=> '1234567890qwertyuiopasdfghjklzxcvbnm_QWERTYUIOPASDFGHJKLZXCVBNM.@-',
				'max' => 100,
				'min' => 2)
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

	/** checks if the parameter is a valid date in the format of dd-mm-yyyy
		@attrib api=1 params=pos

		@param param required type=string
			The string to check for date validity

		@returns
			true if the strin contains a valid date, false if not

	**/
	function is_date($param)
	{
		$valid = preg_match("/^(\d{1,2}?)-(\d{1,2}?)-(\d{4}?)$/",$param,$parts);
		// päevade arv < 0 ?
		if ($parts[1] < 0)
		{
			$valid = false;
		}
		// päevi rohkem, kui selles kuus?
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

	/** cheks if the parameter is in the given range
		@attrib api=1 params=pos

		@param a required type=int
			The value to check

		@param y requierd type=int
			The beginning of the range

		@param z required type=int
			The end of the range

		@param onTrue optional type=mixed
			The value to return if the parameter is in the range, defaults to true

		@param onFalse optional type=mixed
			The value to return if the parameter is not in the range, defaults to false

		@returns
			The value if the parameter onTrue, if the value is in the range (inclusive), the value if the onFalse parameter if not.

	**/
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
		@attrib api=1 params=pos

		@param address optional type=string
			The string to check for e-mailiness

		@returns
			true if the value seems to be an valid e-mail, false if not

		@comment Courtesy of martin@linuxator.com ;)
	**/
	function is_email ($address = "")
	{
		return preg_match('/([a-z0-9-]*((\.|_)?[a-z0-9]+)+@([a-z0-9]+(\.|-)?)+[a-z0-9]\.[a-z]{2,})/i',$address);
	}

	/** checks if the current page is in the admin interface
		@attrib api=1 

		@returns
			true if the current page is displayed in the admin interface, false if not
	**/
	function is_admin()
	{
		return (stristr(aw_global_get("REQUEST_URI"),"/automatweb")!=false);
	}

	/** Generates a random and pretty unique id, based on md5
		@attrib api=1 

		@returns
			a random, hard-to-predict 32 character string of numbers and letters
	**/
	function gen_uniq_id()
	{
		return md5(uniqid('',true)); 
	};

	/** helper for generating html checkboxes
		@attrib api=1 params=pos

		@param arg required type=bool
			
		@returns
			"CHECKED" if the argument evaluates to true, "" if not

		@comment
			You can use this to generate checked checkboxes in html
	**/
	function checked($arg)
	{
		return ($arg) ? "CHECKED" : "";
	}

	/** helper for generating html selectbox options
		@attrib api=1 params=pos

		@param arg required type=bool
			
		@returns
			"selected" if the argument evaluates to true, "" if not

		@comment
			You can use this to generate the selected listbox item in html
	**/
	function selected($arg)
	{
		return ($arg) ? "SELECTED" : "";
	}

	/** helper for generating disabled html elements
		@attrib api=1 params=pos

		@param arg required type=bool
			
		@returns
			"DISABLED" if the argument evaluates to true, "" if not

		@comment
			You can use this to generate html elements that can be disabled or not
	**/
	function disabled($arg)
	{
		return ($arg) ? "DISABLED" : "";
	}

	/** Debug print variable values
		@attrib api=1 params=pos

		@param arr required type=mixed
			The value to output

		@param die optional type=bool
			If set to true, script execution is stopped after outputting the value, defaults to false

		@param see_html optional type=bool
			If set to true, the value displayed is fed through htmlspecialchars, so you can see the html tags in yer browser

		@comment
			Use this to output any value to the user in a pretty way, basically wraps print_r. The value is printed directly to the browser, not returned. 

	**/
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

	/** Shortcut for arr()
		@attrib api=1 params=pos
		@comment
			See arr(), this is jsut a shorter form of that
	**/
	function d($arg)
	{
		arr($arg);
	}

	/** Formats array values as specified
		@attrib api=1 params=pos

		@param format required type=string
			The format string (same format as sprintf )

		@param array required type=array
			The array whose contents you want to reformat

		@returns 
			The array, with the format string applied to each value

		@examples
			foreach(map("--- %s ---\n",array("1","2","3")) as $entry)
			{
				echo $entry;
			}
			result:
				--- 1 ---
				--- 2 ---
				--- 3 ---
	**/
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

	/** Formats array keys and values as specified
		@attrib api=1 params=pos

		@param format required type=string
			The format string (same format as sprintf ) must contain two string replacement marks

		@param array required type=array
			The array whose contents you want to reformat

		@param type optional type=bool
			If set to true, the array keys and values get flipped before formatting, defaults to false

		@param empty optional type=bool
			If set to true, empty values are included in the result, else they are discarded. defaults to false

		@returns 
			The array, with the format string applied to each key and value pair. 

		@examples
			foreach(map2("%s: %s ---\n",array("a" => "1","b" => "2","c" => "3")) as $entry)
			{
				echo $entry;
			}
			result:
				a: 1 ---
				b: 2 ---
				c: 3 ---
	**/
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

	/** Returns the ip address of the current user. tries to bypass the users cache if it can
		@attrib api=1
	**/
	function get_ip()
	{
		/*$ip = aw_global_get("HTTP_X_FORWARDED_FOR");
		if (!inet::is_ip($ip))
		{*/
			$ip = aw_global_get("REMOTE_ADDR");
		//}
		return $ip;
	}

	/** Use this to display object names.
		@attrib api=1 params=pos

		@param name required type=string
			The name of the object to process

		@returns
			The name given if it is not empty, (nimetu) if not. 

		@comment
			The idea here is, that if the object's name is not empty, the user still sees something to click on for example
	**/
	function parse_obj_name($name)
	{
		$name = trim($name);
		$rv = empty($name) ? t("(nimetu)") : $name;
		$rv = str_replace('"',"&quot;", $rv);
		return $rv;
	}

	/** Use this to display object names.
		@attrib api=1 params=pos

		@param name required type=string
			The name of the object to process. The name is changed in place (reference argument)

		@comment
			The idea here is, that if the object's name is not empty, the user still sees something to click on for example
	**/
	function parse_obj_name_ref(&$name)
	{
		$name = trim($name);
		$name = empty($name) ? t("(nimetu)") : $name;
		$name = str_replace('"',"&quot;", $name);
	}

	/** Serializes the given array to a string
		@attrib api=1 params=pos

		@param arr required type=array
			The array to serialize

		@param type optional type=int
			The type of serializer to use, can be one of (SERIALIZE_PHP, SERIALIZE_PHP_FILE, SERIALIZE_PHP_NOINDEX, SERIALIZE_XML, SERIALIZE_XMLRPC), defaults to SERIALIZE_PHP

		@param flags optional type=array
			An array of settings to pass to the serializer

		@returns
			A string that contains the array in a serialized form. can be turned back to an array by aw_unserialize

		@comment
			This can only handle arrays, not objects or any other type of values
	**/
	function aw_serialize($arr,$type = SERIALIZE_PHP, $flags = array())
	{
		switch($type)
		{
			case SERIALIZE_PHP:
				$ser = new php_serializer;
				foreach($flags as $fk => $fv)
				{
					$ser->set($fk, $fv);
				}
				$str = $ser->php_serialize($arr);
				break;
			case SERIALIZE_PHP_FILE:
				$ser = new php_serializer_file;
				foreach($flags as $fk => $fv)
				{
					$ser->set($fk, $fv);
				}
				$str = $ser->php_serialize($arr);
				break;
			case SERIALIZE_PHP_NOINDEX:
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

	/** unserializes a string to an array
		@attrib api=1 params=pos

		@param str required type=string
			The string to unserialize

		@param dequote optional type=bool
			Whether to run dequote() on the string before unserializing or not. defaults to false

		@returns
			The array, as unserialized from the string. If the string is not a valid serialization, returns null

		@comment
			Use this to unserialize strings created by aw_serialize or php's serialize(), it autodetects the serializer type from the beginning of the string. 

	**/
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

	/** return global variable value
		@attrib api=1 params=pos

		@param var required type=string
			The variable whose value you want to fetch

		@returns
			The aw global variable value for the given variable

		@comment
			This fetches values from the request, cookie and session and those set from aw_global_set. 
			this function replaces php's GLOBAL - it keeps global variables in a global object instance
			why is this? well, because then they can't be set from the url, overriding the default values
			and causing potential security problems
	**/
	function aw_global_get($var)
	{
		/*if ($var == "lang_id")
		{
			echo "$var is ".(isset($GLOBALS["__aw_globals"][$var]) ? $GLOBALS["__aw_globals"][$var] : false)."<br>";
		}*/
		return isset($GLOBALS["__aw_globals"][$var]) ? $GLOBALS["__aw_globals"][$var] : false;
	}

	/** Sets an aw global variable value
		@attrib api=1 params=pos

		@param var required type=string
			The name of the variable to set the value for

		@param val required type=string
			The value of the variable to set

		@comment
			Values set by this can be fetched by aw_global_get()
	**/
	function aw_global_set($var,$val)
	{
		/*if ($var == "recievers_name")
		{
			echo "setto ".dbg::dump($val)." from ".dbg::process_backtrace(debug_backtrace());
		}*/
		$GLOBALS["__aw_globals"][$var] = $val;
	}

	/** read value from a memory cache
		@attrib api=1 params=pos

		@param cache required type=string
			The name of the cache to read from

		@param key required type=string
			The name of the key to read from the cache

		@returns
			The value fo the key in the given cache

		@comment
			Use this method instead of $GLOBALS["cache_name"][$key] = foo; because if you do that, then the values can be inserted via the request. This method protects you from that. 
			Or use static members or static variables. 
	**/
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

	/** write value to a memory cache
		@attrib api=1 params=pos

		@param cache required type=string
			The name of the cache to write to

		@param key required type=string
			The name of the key in the cache to write to

		@param val optional type=mixed
			The value of the cache key, defaults to ""

		@examples
			aw_cache_set("lookup", "v1", calc_complicated_thing());
			aw_cache_set("lookup", "v2", calc_complicated_thing2());
			...
			....
			$val = aw_cache_get("lookup", "v1");
	**/
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
	
	/** clears the contents of the given memory cache
		@attrib api=1 params=pos

		@param cache required type=string
			The name of the cache to clear

		@exmples
			aw_cache_set("a", "b", "c");
			aw_cache_flush("a");
			echo aw_cache_get("a", "b");	// echoes ""
	**/
	function aw_cache_flush($cache)
	{
		if (!is_array($GLOBALS["__aw_cache"]))
		{
			$GLOBALS["__aw_cache"] = array();
		}
		$GLOBALS["__aw_cache"][$cache] = false;
	}

	/** this returns the entire cache array - this is useful for instance if you want to iterate over the cache
		@attrib api=1 params=pos

		@param cache required type=string
			The name of the cache to return

		@returns 
			Array { key => value } for the given cache

		@examples:
			 $this->file_cache->file_set($this->cf_name,aw_serialize(aw_cache_get_array("languages")));
	**/
	function aw_cache_get_array($cache)
	{
		if (!is_array($GLOBALS["__aw_cache"]))
		{
			$GLOBALS["__aw_cache"] = array();
			return false;
		}
		return isset($GLOBALS["__aw_cache"][$cache]) ? $GLOBALS["__aw_cache"][$cache] : false;
	}

	/** initializes the given cache from an array
		@attrib api=1 params=pos

		@param cache required type=string
			The name of the cache to initialize

		@param arr required type=array
			The value to initialize the cache with
	**/
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

	/** saves a local variable's value to the session 
		@attrib api=1 params=pos

		@param name required type=string
			The name of the variable to write the value for

		@param value required type=mixed
			The value of the variable to set

		@comment
			there is no session_get, because session vars are automatically registered as globals as well, so for retrieval you can use aw_global_get().
			Also, if the value is empty, this will not do anything for some weird reason. 
			Just use $_SESSION directly. 
			This was useful in the days, when there was no $_SESSION. 
	**/
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

	/** deletes a variable from the session 
		@attrib api=1 params=pos

		@param name required type=string
			The name of the variable to write the value for

		@param leave_global optional type=bool
			If set to true, the global variable with the same name is not cleared

		@comment
			Just use $_SESSION directly. 
	**/
	function aw_session_del($name, $leave_global = false)
	{
		unset($_SESSION[$name]);
		if (!$leave_global)
		{
			aw_global_set($name, "");
			unset($GLOBALS[$name]);
		}
	}

	/** deletes all variables from the session that match preg pattern $pattern
		@attrib api=1 params=pos

		@param pattern required type=string
			The regex to match with the variable names
		
		@examples
			 aw_session_del_patt("/form_rel_tree(.*)/"); 
	**/
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

	/** Registers default values for class member variables
		@attrib api=1 params=pos

		@param class required type=string
			The class to put the member variable in

		@param member required type=string
			The name of the variable

		@param value required type=mixed
			The varaible value

		@comment
			Use this, when you want to pass data to instances of some class, that get created after the call to this.
		
		@examples
			aw_register_default_class_member("document", "shown_document", $docid);
			// now, wherever a new document class instance is created, it has a memver variable named shown_document with the value in the variable $docid
	**/
	function aw_register_default_class_member($class, $member, $value)
	{
		$members = aw_cache_get("__aw_default_class_members", $class);
		$members[$member] = $value;
		aw_cache_set("__aw_default_class_members", $class, $members);
	}

	/** temporarily switches the current user to $arr[uid]
		@attrib api=1 params=name

		@param uid required type=string
			The username to switch to. 

		@comment	
			This can be used to elevate privileges to some other user, so be really careful with it!
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

	/** restores the original username the request was running under
		@attrib api=1 

		@comment
			Switches the user back to the one the request was running as, before any calls to aw_switch_user();

	**/
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

	/** disables all acl checks 
		@attrib api=1

		@comment
			This should only be used just before savin something, not before reading something. The reason for this is, that if you keep this on for a bit loner, then aw will start throwing errors about nonexisting objects. This is because object deletion checks are also done via acl and since acl is turned off, things start to go wrong if anything is deleted. 
			This behaviour is intentional, to make it really hard to use this function, because you really shouldn't. Acls should be set correctly. 

		@examples
			$o = obj($oid);
			aw_disable_acl();	
			$o->save();			// this is how this is meant to be used. 
			aw_restore_acl();
	**/
	function aw_disable_acl()
	{
		$GLOBALS["__aw_disable_acl"] = $GLOBALS["cfg"]["acl"]["no_check"];
		$GLOBALS["cfg"]["acl"]["no_check"] = 1;
	}

	/** restores acl checks that were turned off by aw_disable_acl()
		@attrib api=1

		@examples
			${aw_disable_acl}
	**/
	function aw_restore_acl()
	{
		$GLOBALS["cfg"]["acl"]["no_check"] = $GLOBALS["__aw_disable_acl"];
	}

	/** Finds the class id, given the class name
		@attrib api=1 params=pos

		@param class_name required type=string
			The name of the class to look up

		@returns
			The class_id of the given class or null if no id is assigned to that class

		@examples
			$o = obj();
			$o->set_class_id(clid_for_name("bug"));
	**/
	function clid_for_name($class_name)
	{
		return aw_ini_get("class_lut.".$class_name);
	}

	/** deprecated - do not use. or, rather - needs thinking about. does not work anyway **/
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
	
	function xml_job_offerings_temp_function($xml)
	{
		$backfile = "http://www.cvkeskus.ee/xml_jobs.php?id=rev01h"; 
		$ct = file_get_contents($xml?$xml:$backfile);
		//$ct = html_entity_decode($ct, ENT_QUOTES, "utf-8");
		$parser = xml_parser_create("utf-8");
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		if (xml_parse_into_struct($parser,$ct,&$values) === 0)
		{
			echo "xml error ".xml_error_string(xml_get_error_code($parser))." on line ".xml_get_current_line_number($parser)." <br>";
		}
		xml_parser_free($parser);

		$l = get_instance("languages");
		$ld = $l->fetch(aw_global_get("ct_lang_id"));

		$job_ad_data = array();
		$opens = array();
		$to_arrays = array(
			 "jobType", "workTime", "category","region"
		);
		foreach($values as $node)
		{
			$$node["tag"] = ($node["type"] == "open" && $node["tag"] == "jobAd")?$node["attributes"]["id"]:false;
			if($jobAd)
			{
				switch($node["type"])
				{
					case "open":
						$opens[$node["tag"]] = ($node["tag"] == "jobAd")?false:true;
						break;
					case "complete":
						if(!strlen(trim($node["value"])))
						{
							continue;
						}
						$str = "\$job_ad_data[".$jobAd."]";
						foreach($opens as $t => $v)
						{
							$str .= $v?"[\"".$t."\"]":"";
						}
						$to_array = in_array($node["tag"], $to_arrays)?"[]":"";
						//$node["value"] = html_entity_decode($node["value"], ENT_COMPAT, "utf-8");
						$node["value"] = iconv("utf-8", $ld["charset"]."//IGNORE", $node["value"]);
						$str .= "[\"".$node["tag"]."\"]".$to_array." = \"".str_replace("\"", "&quot;", $node["value"])."\";";
						eval($str);
						break;
					case "close":
						$opens[$node["tag"]] = false;
						break;
				}
			}
		}
		uasort($job_ad_data,"__sort_job_xml");
		return $job_ad_data;
	}

		$GLOBALS["__reval_hotel_list"] = array(
			"Reval Hotel Olümpia",
			"Reval Hotel Central",
			"Reval Park Hotel  Casino",
			"Reval Inn Tallinn",
			"Reval Hotel Latvija",
			"Reval Hotel R?dzene",
			"Reval Hotel Lietuva",
			"Reval Inn Vilnius",
			"Reval Inn Klaipeda",
		);
	function __sort_job_xml($a, $b)
	{
		global $__reval_hotel_list;
		return array_search(str_replace("&", "", $a["employer"]["name"]), $sort_order) - array_search(str_replace("&", "", $b["emplyer"]["name"]), $__reval_hotel_list);
		return 0;
	}
	
	function xml_job_temp_list($job_ad_data, $job_doc, $region = null)
	{
		$tpl = new aw_template();
		$tpl->init(array(
			 "tpl_dir" => "/",
		));
		$lut = array(
			"et" => "ee",
			"lt" => "lt",
			"en" => "en",
			"lv" => "lv",
			"ru" => "ru"
		);
		$tpl->read_template("temp_jobs.tpl");
		lc_site_load("reval_cv", $tpl);
		foreach($job_ad_data as $jobId => $data)
		{
//		echo "dl = $data[language] , lut = ".$lut[aw_global_get("ct_lang_lc")]." <br>";
			if ($data["language"] != $lut[aw_global_get("ct_lang_lc")])
			{
				continue;
			}
			if ($region !== null && $data["region"][0] != $region)
			{
				continue;
			}
			$tpl->vars(array(
				 "id" => $jobId,
				"title" => $data["title"],
				 "location" => $data["location"],
				 "employer" => $data["employer"]["name"],
				"nr" => ++$count,
				"expires" => $data["expires"],
				"url" => aw_ini_get("baseurl")."/".$job_doc."?job_id=".$jobId,
				"class" => (($count % 2) == 0)?"even":"odd",
				 //"url" => aw_url_change_var("job_id", $jobId),
			));
			$rows .= $tpl->parse("ROW");
		}
		$tpl->vars(array(
			 "ROW" => $rows,
		));
		$tpl->vars($vars);
		
		return $tpl->parse();
	}
	
	function xml_job_temp_job($job_ad_data)
	{
		$job = $job_ad_data[$_GET["job_id"]];
		$job["job_id"] = $_GET["job_id"];
		$tpl = new aw_template();
		$tpl->init(array(
			 "tpl_dir" => "/",
		));
		$tpl->read_template("temp_job.tpl");
		lc_site_load("reval_cv", $tpl);
// set the job data to vars
		 $vars = $job;
	     // now lets override some shit
		 $arrs = array(
			 "description", "requirements", "offers"
		);
		 foreach($arrs as $elem)
		 {
			 foreach(split("[*]", $job[$elem]) as $value)
			 {
				 $value = trim($value, "*\n\r");
				 if(!strlen($value))
				 {
					 continue;
				 }
				 $tpl->vars(array(
					$elem => $value,
				 ));
				 $vars[strtoupper($elem)] .= $tpl->parse(strtoupper($elem));
			 }
		 }

		// for cat, worktime & jobtype
		$arrs = array(
			 "category" => &$job["categories"]["category"],
			 "worktime" => &$job["timeOfWork"]["workTime"],
			 "jobtype" => &$job["jobArrangements"]["jobType"]
		);
		 foreach($arrs as $k => $vals)
		 {
			 unset($_for_join);
			 foreach($vals as $value)
			 {
				 $tpl->vars(array(
					$k."_value" => $value,
				 ));
				 $_for_join[$k][] = $value;
				 $vars[strtoupper($k)] .= $tpl->parse(strtoupper($k));
			 }
			 if(count($_for_join[$k]))
			 {
				 $vars[$k."_joined"] = join(", ", $_for_join[$k]);
				 $tpl->vars(array(
					strtoupper($k) => $vars[strtoupper($k)],
				 ));
				 $vars["HAS_".strtoupper($k)] = $tpl->parse("HAS_".strtoupper($k));
			 }
			 else
			 {
				 $vars[$k."_joined"] = "-";
			 }
		 }
		 
		// employer sub
		 $tpl->vars($job["employer"]);
		 $vars["EMPLOYER"] = $tpl->parse("EMPLOYER");
		 $vars["EMPLOYER_2"] = $tpl->parse("EMPLOYER_2");

		 $tpl->vars($vars);
		return $tpl->parse();
	} 

	/** internal **/
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

	/** Set a warning that the user can see if he has the correct level set
		@attrib api=1 params=pos

		@param msg optional type=string
			The warning text

		@param level optional type=int
			The level for which to display this warning, defaults to 1

		@comment
			Warnings are informational for the user, for instance if something is not configured, that should be, you can give a warning, so that the user sees the problem.
	**/
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

	/** returns the object for which the active flag is set for the current language and site
		@attrib api=1 params=pos

		@param clid required type=class_id
			The class id to fetch the active object for

	**/
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

	/** all network functions go in here, all must be static **/
	class inet
	{
		/** Resolves an ip address to it's dns name. caches results
			@attrib api=1 params=pos

			@param addr required type=string
				The ip to resolve, can also be in the format foozah / 1.2.3.4, then the first bit is ignored

			@returns
				array { resolved name, ip address }
		**/
		function gethostbyaddr($addr)
		{
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

		/** returns the ip address that resolves to $name
			@attrib api=1 params=pos

			@param name required type=string
				The dns address to resolve

			@comment
				wrapper for gethostbyname, caches results for speed
		**/
		function name2ip($name)
		{
			if (!($ret = aw_cache_get("name2ip_solved",$name)))
			{
				$ret = gethostbyname($name);
				aw_cache_set("nam2ip_solved",$name,$ret);
			};
			return $ret;
		}

		/** checks if the argument is a valid ip address
			@attrib api=1 params=pos

			@param addr required type=string
				This is the string to check for ip-ness

			@comment
				The format is 4 numbers, separated by . each must be between 1 and 255
		
			@returns 
				true, if the argument is a valid ip address, false if not
		**/
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

	/** class for debug helpers **/
	class dbg
	{

		/** dumps the parameter to a human-readable string and returns the string
			@attrib api=1 params=pos

			@param data required type=mixed
				The value to dump

			@returns
				string with human-readable representation of the given value. Useful for debugging. Wrapper for var_dump()

			@examples
				$s = array(1, 2);
				echo dbg::dump($s);
		**/
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

		/** prints detailed information about string contents
			@attrib api=1 params=pos

			@param str required type=string
				The string to dump

			@comment
				Echoes the full string and then for each character, it's character code and position in the string. 
				Useful for debugging character set problems.
		**/
		function str_dbg($str)
		{
			echo "str = $str <br>";
			for($i = 0; $i < strlen($str); $i++)
			{
				echo "at pos $i: ".$str{$i}." nr = ".ord($str{$i})." <br>";
			}
			echo "---<br>";
		}

		/** prints the given message to the user, if $GLOBALS["DEBUG"] is set
			@attrib api=1 params=pos

			@param msg required type=string
				The message to print
		**/
		function p($msg)
		{
			if (aw_global_get("DEBUG") == 1)
			{
				echo $msg."<br />\n";
			}
		}

		/** prints the given message to the user, if a cookie with the name debug1 is set
			@attrib api=1 params=pos

			@param msg required type=string
				The message to print

			@comment 
				Useful for printing debug data, so that just you can see it. cookiemonster class can be used for setting cookies
		**/
		function p1($msg)
		{
			if ($_COOKIE["debug1"])
			{
				arr($msg);
			}
		}

		/** prints the given message to the user, if a cookie with the name debug2 is set
			@attrib api=1 params=pos

			@param msg required type=string
				The message to print

			@comment 
				Useful for printing debug data, so that just you can see it. cookiemonster class can be used for setting cookies
		**/
		function p2($msg)
		{
			if ($_COOKIE["debug2"])
			{
				echo $msg."<br />\n";
			}
		}

		/** prints the given message to the user, if a cookie with the name debug3 is set
			@attrib api=1 params=pos

			@param msg required type=string
				The message to print

			@comment 
				Useful for printing debug data, so that just you can see it. cookiemonster class can be used for setting cookies
		**/
		function p3($msg)
		{
			if ($_COOKIE["debug3"])
			{
				echo $msg."<br />\n";
			}
		}

		/** prints the given message to the user, if a cookie with the name debug4 is set
			@attrib api=1 params=pos

			@param msg required type=string
				The message to print

			@comment 
				Useful for printing debug data, so that just you can see it. cookiemonster class can be used for setting cookies
		**/
		function p4($msg)
		{
			if ($_COOKIE["debug4"])
			{
				echo $msg."<br />\n";
			}
		}

		/** prints the given message to the user, if a cookie with the name debug5 is set
			@attrib api=1 params=pos

			@param msg required type=string
				The message to print

			@comment 
				Useful for printing debug data, so that just you can see it. cookiemonster class can be used for setting cookies
		**/
		function p5($msg)
		{
			if ($_COOKIE["debug5"])
			{
				print "<pre>";
				var_dump($msg);
				print "</pre>";
			}
		}

		/** formats a given backtrace to a human-readable format
			@attrib api=1 params=pos

			@param bt required type=array
				The backtrace data to process

			@param skip required type=int
				Number of levels to skip from the end

			@returns
				html with the backtrace formatted to usr-readable format

			@examples
				echo process_backtrace(debug_backtrace());
		**/
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
				$str = array();
				foreach($awa->get() as $e)
				{
					if (is_object($e))
					{
						$str[] = "Object ".get_class($e);
					}
					else
					{
						$str[] = "".$e;
					}
				}
				$msg .= "<font size=\"-1\">(".htmlentities(join(",", $str)).") file = ".$bt[$i]["file"]."</font>";

				$msg .= " <br><br>\n\n";
			}
			return $msg;
		}

		/** formats a one-line user-readable string from the current backtrace
			@attrib api=1 

			@returns
				One-line string with a human-readable backtrace
		**/
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

		/** prints the results of a database query
			@attrib api=1 params=pos

			@param q required type=string
				The sql query to perform
		**/
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

	// deprecated - use locale::get_lc_date instead
	function get_lc_date($time=0, $format=3)
	{
		$inst = get_instance("core/locale/".aw_global_get("LC")."/date", array(), false);
		if(!is_object($inst))
		{
			$inst = get_instance("core/locale/en/date");
		}
		return $inst->get_lc_date($time, $format);
	}

	/** all localization functions are grouped here **/
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

		/** returns the name of the weekday in the current language
			@attrib api=1 params=pos

			@param num required type=int
				The number of the weekday to return. 

			@param short optional type=bool
				If true, the short name of the weekday is returned. Defaults to false

			@param ucfirst optional type=bool
				If true, the first characters are uppercased. ddefaults to false

			@comment
				The number of the weekday is 0-7 inclusive. 0 and 7 both are for sunday.
		**/
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

		/** returns the name of the month in the current language
			@attrib api=1 params=pos

			@param num required type=int
				The number of the month to return the name for

		**/
		function get_lc_month($num)
		{
			static $lc_date_inst;
			if(!class_exists("date", false))
			{
				//$lc_date_inst = @get_instance("core/locale/".aw_global_get("ct_lang_lc")."/date", array(), false);
				//$lc_date_inst = get_instance("core/locale/".aw_global_get("ct_lang_lc")."/date");
			}	
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

		/** returns a localized date in the current language
			@attrib api=1 params=pos 

			@param timestamp required type=int
				The unix timestamp to return the date for

			@param format required type=int
				One of the defined date formats

			@comment 
				The date formats are:
					LC_DATE_FORMAT_SHORT = For example: 20.06.88 or 05.12.98
					LC_DATE_FORMAT_SHORT_FULLYEAR = For example: 20.06.1999 or 05.12.1998
					LC_DATE_FORMAT_LONG = For example: 20. juuni 99
					LC_DATE_FORMAT_LONG_FULLYEAR = For example: 20. juuni 1999
		**/					
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

		/** returns a readable string for the number given
			@attrib api=1 params=pos

			@param number required type=int
				The number to stringify

			@returns
				the text version of the number. 

			@examples
				if the language is english, then
					locale::get_lc_number(7); 
				returns "seven"
		**/
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
	
		/** returns the given amount of money as text with the currency name n the right place
			@attrib api=1 params=pos

			@param number required type=double
				The sum to stringify

			@param currency required type=object
				The currency object to use for the sum.

			@param lc optional type=string
				The locale code, defaults to the current one

			@comment
				Does the same, as get_lc_number, but appends/prepends the currency name and unit names as needed. Used for writing the amount on bills as text.
		**/
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

		/** checks if the given key exists in the current array
			@attrib api=1 params=pos

			@param key required type=string
				The key to check

			@returns 
				true if the given key exists in the array, false if not
		**/
		function key_exists($key)
		{
			return isset($this->arg[$key]);
		}

		/** returns the first element in the array
			@attrib api=1
		**/
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


	/** returns the parameter or an array if the parameter is not an array
		@attrib api=1 params=pos

		@param var required type=mixed
			The value to check for array-ness

		@examples
			foreach(safe_array($request["yeah"]) as $k => $v)
			...
	**/
	function safe_array($var)
	{
		if (is_array($var))
		{
			return $var;
		}
		return array();
	}

	/** Merges arrays
		@attrib api=1 params=pos

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
 
	/** Merges arrays recursively
		@attrib api=1 params=pos

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
	class defs {};
};
?>
