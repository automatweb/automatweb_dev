<?php
// $Header: /home/cvs/automatweb_dev/classes/protocols/mail/imap.aw,v 1.33 2006/05/10 14:16:20 tarvo Exp $
// imap.aw - IMAP login 
/*

@classinfo syslog_type=ST_PROTO_IMAP 

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property server type=textbox
@caption Server

@property port type=textbox size=4 default=993
@caption Port

@property user type=textbox
@caption Kasutaja

@property password type=password
@caption Parool

@property use_ssl type=checkbox ch_value=1 default=1
@caption Kasuta SSL-i

@property test type=text group=test
@caption Testi tulemused

@groupinfo test caption=Testi

*/

class imap extends class_base
{
	var $charsets = array("KOI8-R", "iso-8859-4", "windows-1251", "iso-8859-1");
	function imap()
	{
		$this->init(array(
			"clid" => CL_PROTO_IMAP
		));

		$this->connected = false;
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "test":
				$data["value"] = $this->test_connection($arr);
				break;

		};
		return $retval;
	}
	
	/**
	@attrib api=1 params=name
	@param obj_inst required type=object
		Imap object
	@example 
		$imap_i = $imap->instance();
		$imap_i->connect_server(array(
			"obj_inst" => $imap
		));
		echo $imap_i->test_connection(array(
			"obj_inst" => $imap
		))."<br>";
		$fld_c = $imap_i->get_folder_contents(array("from" => 0, "to" => 100000));
		$ms = $imap_i->fetch_message(array("msgid" => $id));
	@return string "things seem to be working okey" if there is connection , "stream is dead" if not
	@comment 
		Tests if connection is alive
	**/
	function test_connection($arr)
	{
		$this->use_mailbox = "INBOX";

		$ob = $arr["obj_inst"];

		$this->connect_server(array(
			"obj_inst" => $ob,
		));	
	
		$errors = imap_errors();
		if (is_array($errors) && sizeof($errors) > 0)
		{
			$rv = join("<br>",$errors);
		}
		else
		{
			if (imap_ping($this->mbox))
			{
				// create sent-mail folder
				imap_createmailbox($this->mbox,imap_utf7_encode($this->servspec . "INBOX.Sent-mail"));
				$rv = "things seem to be working okey";
			}
			else
			{
				$rv = "stream is dead";
			};
		};
		return $rv;
	}

	/*
	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
                {

		}
		return $retval;
	}	
	*/

	////
	// !Connects to server
	/**
	@attrib api=1 params=name
	@param obj_inst required type=object
		Imap object
	@example ${test_connection}
	@return string / errors , if there are.
	@errors 
		This function returns an array of all of the IMAP error messages generated since the last imap_errors() call, or the beginning of the page.
	@comment 
		Connects to server
	**/
	function connect_server($arr)
	{
		if (!$this->connected)
		{
			$obj = $arr["obj_inst"];
			$this->obj_id = $obj->id();

			$server = $obj->prop("server");
			$port = $obj->prop("port");
			$user = $obj->prop("user");
			$password = $obj->prop("password");


			//  cert validating could probably be made an option later on
			$mask = (1 == $obj->prop("use_ssl")) ? "{%s:%d/ssl/novalidate-cert}" : "{%s:%d}";

			$this->servspec = sprintf($mask,$server,$port);
			$mbox = str_replace("*","&",$this->use_mailbox);
			$this->mboxspec = $this->servspec . $mbox;
			$this->mbox = @imap_open($this->mboxspec, $user, $password);
			$err = imap_errors();
			if (is_array($err))
			{
				return join("<br>",$err);
			};
			$this->connected = true;
		}

		// this is where we store _all_ folders for that account
		$this->fldr_cache_id = "imapfld" . md5("imap-acc-folders".$this->servspec.$user.$this->obj_id);

		// headers for a single mailbox
		$this->mbox_cache_id = "imap" . md5("imap-".$this->obj_id.$this->mboxspec.$user);

		// overview information for each folder. it's in separate file because it's kind
		// of expensive (read slow) to scan over all the folders at once, so we do this
		// when a folder is opened
		$this->overview_cache_id = "imap" . md5("imap-over".$this->servspec.$user.$this->obj_id);
	}
	
	/**
	@attrib api=1 params=name
	@return array / folders.
	@example
		$this->drv_inst = get_instance("protocols/mail/imap");
		$this->drv_inst->set_opt("use_mailbox",$this->use_mailbox);
		$this->drv_inst->set_opt("outbox",$this->outbox);
		$errors = $this->drv_inst->connect_server(array("obj_inst" => $_sdat->to()));
		$this->drv_inst->set_opt("messenger_id",$arr["msgr_id"]);
		$this->mbox = $this->drv_inst->get_opt("mbox");
		$this->servspec = $this->drv_inst->get_opt("servspec");
                $this->mboxspec = $this->drv_inst->get_opt("mboxspec");
		$this->mailboxlist = $this->drv_inst->list_folders();
	@comment 
		Returns a list of folders
	**/
	function list_folders($arr = array())
	{
		$cache = get_instance("cache");
		if ($ser = $cache->file_get($this->fldr_cache_id))
		{
			$res = aw_unserialize($ser);

		}
		else
		{
			$list = imap_getmailboxes($this->mbox,$this->servspec,"*");
			$res = array();
			if (is_array($list))
			{
				foreach($list as $item)
				{
					$key = $realname = str_replace(chr(0),"",imap_utf7_decode(substr($item->name,strlen($this->servspec))));
					//$status = imap_status($this->mbox,$item->name,SA_ALL);
					$res[$key] = array(
						"name" => $realname,
						"int_name" => str_replace("&","*",substr($item->name,strlen($this->servspec))),
						"realname" => strpos($realname,".") === false ? $realname : substr($realname, strrpos($realname, '.') + 1),
						"fullname" => substr($item->name,strlen($this->servspec)),
						//"count" => ($status->unseen > 0) ? sprintf("<b>(%d)</b>",$status->unseen) : "",
					);
				};
			};
			$cache->file_set($this->fldr_cache_id,aw_serialize($res));
		};
		return $res;
	}
	
	/**
	@attrib api=1 params=name
	@param to optional type=int or string "*"
		"*" if you want to get all contents, if not set, returns empty array
	@param from optional type=int
		Needed if $to is an integer
	@example ${test_connection}
	@return array/contents
	@comment 
		Gets folder contents
	**/
	function get_folder_contents($arr)
	{
		$cache = get_instance("cache");
		$mboxinf = imap_mailboxmsginfo($this->mbox);

		$ovr = $this->_get_overview();
		$last_check = $ovr[$this->mboxspec];
		$new_check = $this->_get_ovr_checksum($mboxinf);

		$count = $mboxinf->Nmsgs;
		$this->count = $count;
		// mailbox has changed, reload from server
		if ($last_check != $new_check)
		{
			// update ovr

			$ovr[$this->mboxspec] = $new_check;
			$this->_set_overview($ovr);
			$mboxinf = imap_mailboxmsginfo($this->mbox);

			if (!is_array($mbox_over["contents"]))
			{
				$mbox_over["contents"] = array();
			};

			$mbox_over["modified"] = $fmod;
			$mbox_over["count"] = $count;

			$fo = imap_sort($this->mbox,SORTDATE,0,SE_UID && SE_NOPREFETCH);

			$to_fetch = array_diff($fo,array_keys($mbox_over["contents"]));

			$req_msgs = $mbox_over["contents"];

			//$uidlist = join(",",$to_fetch);

			// this will update the message cache ... it has to contain all
			// the message bits in this mailbox
			if ($count > 0)
			{
				$overview = "";

				foreach($to_fetch as $msg_uid)
				{
					//print "fetching message with uid $msg_uid<br>";
					//flush();
					$hdrinfo = @imap_headerinfo($this->mbox,$msg_uid);
					$overview = imap_fetch_overview($this->mbox,$msg_uid,FT_UID);
					$str = imap_fetchstructure($this->mbox,$msg_uid,FT_UID);

					$message = $overview[0];
					$addrinf = $this->_extract_address($message->from);
					$rkey = $message->uid;
					$dinfo = $message->date;
					if(empty($message->date))
					{
						$dinfo = $hdrinfo->udate;
					}
					$req_msgs[$rkey] = array(
						"encoding" => $str->parameters[0]->value,
						"from" => $message->from,
						"froma" => $addrinf["addr"],
						"fromn" => $this->MIME_decode($addrinf["name"]),
						"subject" => $this->_parse_subj($message->subject),
						"date" => $dinfo,
						"tstamp" => strtotime($dinfo),
						"size" => $message->size,
						"seen" => $message->seen,
						"answered" => $message->answered,
						"recent" => $message->recent,
							// 1 is multipart message
							// this needs some tweaking, since multipart
							// doesn't always mean that the message
							// has attachments
						"has_attachments" => ($str->type == 1) ? true : false,
					);
				};
			};
			uasort($req_msgs,array($this,"__date_sort"));

			$mbox_over["contents"] = $req_msgs;
			$cache->file_set($this->mbox_cache_id,aw_serialize($mbox_over));
		}
		else
		{
			$src = $cache->file_get($this->mbox_cache_id);
			$mbox_over = aw_unserialize($src);
		};

		if (is_array($mbox_over["contents"]))
		{
			foreach(array_keys($mbox_over["contents"]) as $rkey => $ritem)
			{
				// * means all messages should be returned. used for filters
				// mostly. IMAP extension uses this syntax so I will too.
				if ("*" != $arr["to"] && !between($rkey+1,$arr["from"],$arr["to"]))
				{
					unset($mbox_over["contents"][$ritem]);
				};
			}
		};
		$rv = $mbox_over["contents"];
		if(strlen($arr["from_filter"]))
		{
			foreach($rv as $k => $v)
			{
				if(!strstr($v["from"], $arr["from_filter"]))
					unset($rv[$k]);
			}
		}
		return $rv;
	}
	
	/**
	@attrib api=1 params=pos
	@param string required type=string
		criteria
	@example 
		$this->drv_inst = get_instance("protocols/mail/imap");
		$matches = $this->drv_inst->search_folder(join(" ",$str));
	@return array of messages matching the given search criteria
	@comment
		the returned array contains UIDs instead of messages sequence numbers
		$this->mbox must be set
	**/
	function search_folder($string)
	{
		$results = imap_search($this->mbox,$string,SE_UID);
		return $results;
	}

	function __date_sort($el1, $el2)
	{
		return (int)($el2["tstamp"] - $el1["tstamp"]);
	}

	/**
	@attrib api=1 params=pos
	@param arr required type=array
		Array of mail id's
	@example 
		$this->drv_inst = get_instance("protocols/mail/imap");
		$this->_connect_server(array(
			"msgr_id" => $arr["id"],
		));
		$this->drv_inst->delete_msgs_from_folder(array_keys($marked));
	@comment
		Deletes listed messages.
		$this->mbox must be set
	**/
	function delete_msgs_from_folder($arr)
	{
		if (is_array($arr))
		{
			foreach($arr as $id)
			{
				imap_delete($this->mbox,$id,FT_UID);
			}
			imap_expunge($this->mbox);
		}
	}
	
	/**
	@attrib api=1 params=name
	@param id required type=array
		id is a range not just message numbers
	@param to required type=string
		specified mailbox
	@return string/the full text of the last IMAP error message that occurred on the current page
	@example 
		$this->drv_inst = get_instance("protocols/mail/imap");
		$this->_connect_server(array(
			"msgr_id" => $arr["id"],
		));
		$rv = $this->drv_inst->move_messages(array(
			"id" => array_keys($marked),
			"to" =>  $arr["move_to_folder"],
		));
	@comment
		Moves mail messages specified by id to specified mailbox to.
		$this->mbox must be set
	**/
	function move_messages($arr)
	{
		$rv = "";
		$ids = join(",",$arr["id"]);
		$to = $arr["to"];
		if (!imap_mail_move($this->mbox,join(",",$arr["id"]),$to,CP_UID))
		{
			$err = imap_last_error();
			var_dump($err);
			$rv .= " &nbsp; &nbsp; <font color='red'>$err</font><br>";
		}
		else
		{
			// expunge any moves messages
			imap_expunge($this->mbox);
		};
		return $rv;
	}

	/**
	@attrib api=1 params=name
	@param msgid required type=int
		Message id
	@return Array([from],[reply_to],[to],[subject],[Predev],[cc],[date],[content])
	@example
		$this->drv_inst = get_instance("protocols/mail/imap");
		$this->_connect_server(array(
			"msgr_id" => $arr["id"],
		));
		$ms = $this->drv_inst->fetch_message(array("msgid" => $id));
	@comment
		Fetches a single message from the currently connected mailbox.
		$this->mbox must be set
		returns something like :
		Array([from] => Anti Veeranna 
			[reply_to] => predev@struktuur.ee
			[to] => Anti Veeranna 
			[subject] => [Predev] Re: cvs commit by markop in	automatweb_dev/classes/applications/mailinglist
			[cc] => predev@struktuur.ee
			[date] =>  4-May-2006 19:25:40 +0300
			[content] =>  18:30 oli meiliserveri järjekorras natuke üle 4000 meili. Huvitav kas sellest?
			Anti
		)
	**/
	function fetch_message($arr)
	{
		// XXX: check whether the message was valid
		$msgid = $arr["msgid"];
		$msg_no = imap_msgno($this->mbox,$arr["msgid"]);
		$hdrinfo = @imap_headerinfo($this->mbox,$msg_no);

		// I should mark the message as "read" in the cache as well	
			
		$cache = get_instance("cache");
		$src = $cache->file_get($this->mbox_cache_id);
		$mbox_over = aw_unserialize($src);

		$mbox_over["contents"][$arr["msgid"]]["seen"] = 1;
		$cache->file_set($this->mbox_cache_id,aw_serialize($mbox_over));
		$msgdata = array(
			"from" => $this->MIME_decode($hdrinfo->fromaddress),
			"reply_to" => $this->MIME_decode($hdrinfo->reply_toaddress),
			"to" => $this->MIME_decode($hdrinfo->toaddress),
			"subject" => $this->_parse_subj($hdrinfo->subject),
			"cc" => $this->MIME_decode($hdrinfo->ccaddress),
			"date" => $hdrinfo->MailDate,
		);

		#$overview = @imap_fetchstructure($this->mbox,$msgid,FT_UID);

		$fq = aw_ini_get("basedir") . "/classes/protocols/mail/MIME/mimeDecode.php";
		require_once "$fq";
		$params = array();
		$params['include_bodies'] = true;
		$params['decode_bodies']  = true;
		$params['decode_headers'] = true;

		//print "funky shit<br>";

		$header = imap_fetchheader($this->mbox,$msgid,FT_UID);
		$body = imap_body($this->mbox,$msgid,FT_UID);

		/*
		print "<pre>";
		print_r($header);
		print_r($body);
		print "</pre>";
		*/
		
		$decoder = new Mail_mimeDecode($header. $body);

		$structure = $decoder->decode($params);


		$rv = "";

		$this->rv = "";
		$this->msgid = $msgid;

		$this->partlist = array();
		$this->attachments = array();
		if (!empty($structure->body))
		{
			$msgdata["content"] = $structure->body;
		}
		elseif (is_array($structure->parts))
		{
			foreach($structure->parts as $key => $val)
			{
				$this->add_parts($key, $val);
			}
		};
		$msgdata["content"] .= $this->msg_content;

		if (sizeof($this->attachments) > 0)
		{
			$msgdata["attachments"] = $this->attachments;
		}
		return $msgdata;
	}

	function add_parts($key, $val)
	{
		static $v;
		list($keyx,) = each($val->parts);
		if($keyx == 0 && isset($keyx))
		{
			$v++;
			foreach($val->parts as $key2 => $val2)
			{
				$this->add_parts($key2, $val2);
			}
		}
		else
		{
			if(strtolower($val->ctype_primary) == "text" && strtolower($val->ctype_secondary) == "plain" && ($val->disposition == "inline" || empty($val->disposition)))
			{
				if(!empty($val->ctype_parameters["charset"]) && in_array(strtolower($val->ctype_parameters["charset"]), $this->charsets))
				{
					$this->charset = $val->ctype_parameters["charset"];
				}
				if(!empty($this->charset))
				{
					//$val->body = iconv($this->charset, "utf-8", $val->body);
					aw_global_set("output_charset", $this->charset);
				}
				$this->msg_content .= $val->body;
			}
			elseif(strtolower($val->ctype_primary) == "text" && strtolower($val->ctype_secondary) == "html" && ($val->disposition == "inline" || empty($val->disposition)))
			{
				// send this one to garbage, because we don't accept html at the moment...
				return;
			}
			elseif(!empty($val->disposition) && $val->disposition == "attachment")
			{
				$this->attachments[$key] = $val->d_parameters["filename"];
				if (!empty($val->headers["content-description"]))
				{
					$this->attachments[$key] .= " : " . $val->headers["content-description"];
				};
			}
			else
			{
				// send this one to garbage also
				return;
				//echo "some other garbage";
				//arr($val);
				//$this->attachments[$key] = $val->ctype_parameters["name"];
			};
		}
	}

	/**
	@attrib api=1 params=name
	@param msgid required type=int
		Message id
	@param arr optional type=bool
		if set, returns @imap_headerinfo instead of @imap_fetchbody
	@return stdClass Object/if arr is set return @imap_headerinfo, if not then returns @imap_fetchbody
	@example
		$this->drv_inst = get_instance("protocols/mail/imap");
		$this->_connect_server(array(
			"msgr_id" => $arr["id"],
		));
		$message = $this->drv_inst->fetch_headers(array(
			"msgid" => $msg_uid,
			"arr" => 1,
		));
	@comment
		This function returns an object of various header elements.
		If arr is not set, it causes a fetch of a particular section of the body of the specified messages as a text string and returns that text string. The section specification is a string of integers delimited by period which index into a body part list as per the IMAP4 specification. Body parts are not decoded by this function.
	**/
	function fetch_headers($arr)
	{
		$msg_no = imap_msgno($this->mbox,$arr["msgid"]);
		if ($arr["arr"])
		{
			return @imap_headerinfo($this->mbox,$msg_no);
		}
		else
		{
			return @imap_fetchbody($this->mbox,$msg_no,0);
		};
	}
	/**
	@attrib api=1 params=pos
	@param str required type=string
		Mail message subject
	@return string / Subject
	@comment
		Parses a mail message subject.
	**/
	function _parse_subj($str)
	{
		$elements = imap_mime_header_decode($str);
		for($i=0; $i<count($elements); $i++)
		{
			$rv .= $elements[$i]->text;
		};
		return $rv;
	}

	function _decode_parameters($arr)
	{
		$rv = array();
		$params = new aw_array($arr);
		foreach($params->get() as $key => $val)
		{
			$rv[$val->attribute] = $val->value;
		}
		return $rv;
	}

	function _get_mime_type($type,$subtype = false)
	{
		$primary_mime_type = array("text", "multipart","message", "application", "audio","image", "video", "other");
	        if ($subtype)
		{
			$rv = $primary_mime_type[(int) $type] . '/' .$subtype;
		}
		else
		{
			$rv = "text/plain";
		}
		return $rv;
	}

	function _decode($text,$encoding)
	{
		if ($encoding == ENCBASE64)
		{
			$rv = imap_base64($text);
		}
		else
		if ($encoding == ENCQUOTEDPRINTABLE)
		{
			$rv = imap_qprint($text);
		}
		else
		{
			$rv = $text;
		};
		return $rv;
	}
	/**
	@attrib api=1 params=pos
	@param msgid required type=int
		Message id
	@param part required type=int
	@param return optional type=bool
		if not set, sets the header and die();
	@return Array("content-type", "name" , "content") if $return is set
	@example 
		$this->drv_inst = get_instance("protocols/mail/imap");
		$this->drv_inst->fetch_part(array(
			"msgid" => $msgid,
			"part" => $arr["part"],
		));
	@comment
		This function causes a fetch of the complete, unfiltered RFC2822  format header of the specified message. If $return is not set, 
	**/
	function fetch_part($arr) 
	{
		$header = imap_fetchheader($this->mbox,$arr["msgid"],FT_UID);
		$body = imap_body($this->mbox,$arr["msgid"],FT_UID);

		$fq = aw_ini_get("basedir") . "/classes/protocols/mail/MIME/mimeDecode.php";
		require_once "$fq";
		$params = array();
		$params['include_bodies'] = true;
		$params['decode_bodies']  = true;
		$params['decode_headers'] = true;
		
		$decoder = new Mail_mimeDecode($header. $body);

		$structure = $decoder->decode($params);

		$part = $structure->parts[$arr["part"]];
		
		$mime_type = strtolower($part->ctype_primary . "/" . $part->ctype_secondary);
		$att_name = $part->d_parameters["filename"];

		if (empty($att_name) && $part->ctype_parameters["name"])
		{
			$att_name = $part->ctype_parameters["name"];
		};

		if (isset($arr["return"]))
		{
			return array(
				"content-type" => $mime_type,
				"name" => $att_name,
				"content" => $part->body,
			);
		}
		else
		{
			header("Content-type: ".$mime_type);
			header("Content-Disposition: filename=$att_name");
			die($part->body);
		}
	}
	/**
	@attrib api=1 params=pos
	@param from optional type=string
		Mail sender's address
	@param to optional type=string
		mail to (adresses)
	@param cc optional type=string
		mail to (adresses)
	@param subject optional type=string
		mail subject
	@param message optional type=string
		mail body

	@example 
		$this->drv_inst = get_instance("protocols/mail/imap");
		$msgr->drv_inst->store_message(array(
			"from" => $from,
			"to" => $arr["mto"],
			"cc" => $arr["cc"],
			"subject" => $arr["name"],
			"message" => $this->awm->bodytext,
		));
	@comment
		Appends a message to the specified mailbox.
		$this->mbox,$this->servspec.$this->outbox must be set
	**/
	function store_message($arr)
	{
		if(!empty($arr["cc"]))
		{
			$sr = "Cc: $arr[cc]\r\n";
		}
		$str = 	"From: $arr[from]\r\n"."To: $arr[to]\r\n".$sr."Subject: $arr[subject]\r\n"."\r\n".$arr["message"] . "\r\n";
		imap_append($this->mbox,$this->servspec.$this->outbox, $str);
	}

	function _get_overview()
	{
		$cache = get_instance("cache");
		$fl = $cache->file_get($this->overview_cache_id);
		$ovr = array();
		if ($fl)
		{
			$ovr = aw_unserialize($fl);
		};
		return $ovr;
	}

	function _set_overview($ovr)
	{
		$cache = get_instance("cache");
		$cache->file_set($this->overview_cache_id,aw_serialize($ovr));
	}

	function _get_ovr_checksum($dat)
	{
		return md5($dat->Nmsgs . $dat->Size . "tambovihunt2");
	}
	
	/**
	@attrib api=1 params=pos
	@param arg required type=string
		sender's name and adress
	@return array([name] , [addr])
	@example 
		$this->drv_inst = get_instance("protocols/mail/imap");
		$addrinf = $this->drv_inst->_extract_address($message->fromaddress);
	@comment
		extraxt address string to an array
	**/
	function _extract_address($arg)
	{
		if (preg_match("/(.*)<(.*)>/",$arg,$m))
		{
			$rv = array(
				"name" => str_replace("\"","",$m[1]),
				"addr" => $m[2],
			);
		}
		else
		{
			$rv = array(
				"name" => $arg,
				"addr" => "",
			);
		}
		return $rv;
	}
	
	////
	// !Dekodeerib MIME encodingus teate
	/**
	@attrib api=1 params=pos
	@param string required type=string
		Message you want to decode
	@return string/decoded message
	@example 
		$this->drv_inst = get_instance("protocols/mail/imap");
		$fromn = $this->drv_inst->MIME_decode($addrinf["name"]);
	@comment
		Decodes message with MIME encoding
	**/
	function MIME_decode($string)
	{
		$pos = strpos($string,'=?');
		if ($pos === false)
		{
			return $string;
		}
		else
		{
			#quoted_printable_decode($string);
		};

		// take out any spaces between multiple encoded words
		$string = preg_replace('|\?=\s=\?|', '?==?', $string);
		$preceding = substr($string, 0, $pos); // save any preceding text

		$search = substr($string, $pos + 2, 75); // the mime header spec says this is the longest a single encoded word can be
		$d1 = strpos($search, '?');
		if (!is_int($d1)) 
		{
			return $string;
		}

		$charset = substr($string, $pos + 2, $d1);
		$search = substr($search, $d1 + 1);

		$d2 = strpos($search, '?');
		if (!is_int($d2)) 
		{
			return $string;
		}

		$encoding = substr($search, 0, $d2);
		$search = substr($search, $d2+1);

		$end = strpos($search, '?=');
		if (!is_int($end)) 
		{
			return $string;
		}

		$encoded_text = substr($search, 0, $end);
		$rest = substr($string, (strlen($preceding . $charset . $encoding . $encoded_text) + 6));

		switch ($encoding) 
		{
			case 'Q':
			case 'q':
				$encoded_text = str_replace('_', '%20', $encoded_text);
				$encoded_text = str_replace('=', '%', $encoded_text);
				$decoded = urldecode($encoded_text);

				if (strtolower($charset) == 'windows-1251') 
				{
					$decoded = convert_cyr_string($decoded, 'w', 'k');
				}
				break;

			case 'B':
			case 'b':
				$decoded = urldecode(base64_decode($encoded_text));

				if (strtolower($charset) == 'windows-1251') 
				{
					$decoded = convert_cyr_string($decoded, 'w', 'k');
				}
				break;

			default:
				$decoded = '=?' . $charset . '?' . $encoding . '?' . $encoded_text . '?=';
				break;
			}
		$retval = $preceding . $decoded . $this->MIME_decode($rest);
		return quoted_printable_decode($retval);
	}
};
?>
