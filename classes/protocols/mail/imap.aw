<?php
// $Header: /home/cvs/automatweb_dev/classes/protocols/mail/imap.aw,v 1.9 2003/10/28 15:18:49 duke Exp $
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

@property use_ssl type=checkbox value=1 ch_value=1 default=1
@caption Kasuta SSL-i

@property test type=text group=test
@caption Testi tulemused

@groupinfo test caption=Testi

*/

class imap extends class_base
{
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
			$this->mboxspec = $this->servspec . $this->use_mailbox;
			$this->mbox = @imap_open($this->mboxspec, $user, $password);
			$err = imap_errors();
			if (is_array($err))
			{
				die(join("<br>",$err));
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
					$key = $realname = substr($item->name,strlen($this->servspec));
					//$status = imap_status($this->mbox,$item->name,SA_ALL);
					$res[$key] = array(
						"name" => $realname,
						"realname" => strpos($realname,".") === false ? $realname : substr($realname, strrpos($realname, '.') + 1),
						//"count" => ($status->unseen > 0) ? sprintf("<b>(%d)</b>",$status->unseen) : "",
					);
				};
			};
			$cache->file_set($this->fldr_cache_id,aw_serialize($res));
		};
		return $res;
	}

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
		if (1 || $last_check != $new_check)
		{
			// update ovr

			$ovr[$this->mboxspec] = $new_check;
			$this->_set_overview($ovr);
			$mboxinf = imap_mailboxmsginfo($this->mbox);

			$src = $cache->file_get($this->mbox_cache_id);
			#$mbox_over = aw_unserialize($src);

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
					$overview = imap_fetch_overview($this->mbox,$msg_uid,FT_UID);
					$addrinf = $this->_extract_address($message->from);
					//print "fetch done, processing<br>";
					//flush();
					$message = $overview[0];
					$rkey = $message->uid;
					$req_msgs[$rkey] = array(
						"from" => $message->from,
						"froma" => $addrinf["addr"],
						"fromn" => $addrinf["name"],
						"subject" => $this->_parse_subj($message->subject),
						"date" => $message->date,
						"tstamp" => strtotime($message->date),
						"size" => $message->size,
						"seen" => $message->seen,
						"answered" => $message->answered,
						"recent" => $message->recent,
					);
					//print ".";
					//flush();
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
				if (!between($rkey+1,$arr["from"],$arr["to"]))
				{
					unset($mbox_over["contents"][$ritem]);
				};
			}
		};
		$rv = $mbox_over["contents"];
		return $rv;
	}

	function __date_sort($el1, $el2)
	{
		return (int)($el2["tstamp"] - $el1["tstamp"]);
	}

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

	function move_messages($arr)
	{
		$rv = "";
		$ids = join(",",$arr["id"]);
		$to = $arr["to"];
		if (!imap_mail_move($this->mbox,join(",",$arr["id"]),$arr["to"],CP_UID))
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

	////
	// !Fetches a single message from the currently connected mailbox

	function fetch_message($arr)
	{
		$msgid = $arr["msgid"];
		$msg_no = imap_msgno($this->mbox,$arr["msgid"]);
		$hdrinfo = @imap_headerinfo($this->mbox,$msg_no);

		// XXX: check whether the message was valid

		$msgdata = array(
			"from" => $hdrinfo->fromaddress,
			"reply_to" => $hdrinfo->reply_toaddress,
			"to" => $hdrinfo->toaddress,
			"subject" => $this->_parse_subj($hdrinfo->subject),
			"date" => $hdrinfo->MailDate,
		);
		
		$overview = @imap_fetchstructure($this->mbox,$msgid,FT_UID);

		$rv = "";

		$this->rv = "";
		$this->msgid = $msgid;

		$this->partlist = array();
		$this->attachments = array();

		$this->dissect_part($overview,"");

		$rv = $this->rv;
		$msgdata["content"] = $rv;

		if (sizeof($this->attachments) > 0)
		{
			$msgdata["attachments"] = $this->attachments;
		}

		return $msgdata;
	}

	function dissect_part($this_part,$part_no)
	{
		switch ($this_part->type)
		{
			case TYPETEXT:
				$mime_type = "text";
				break;	

			case TYPEMULTIPART:
				$mime_type = "multipart";
				for ($i = 0; $i < count($this_part->parts); $i++)
				{
					if ($part_no != "")
					{
						$part_no = $part_no.".";
					}
					for ($i = 0; $i < count($this_part->parts); $i++)
					{
						$this->dissect_part($this_part->parts[$i], $part_no.($i + 1));
					}
				}
				break;

			case TYPEMESSAGE:
				$mime_type = "message";
				break;
			case TYPEAPPLICATION:
				$mime_type = "application";
				break;
			case TYPEAUDIO:
				$mime_type = "audio";
				break;
			case TYPEIMAGE:
				$mime_type = "image";
				break;
			case TYPEVIDEO:
				$mime_type = "video";
				break;
			case TYPEMODEL:
				$mime_type = "model";
				break;
			default:
				$mime_type = "unknown";
				// hmmm....
		}

		$full_mime_type = $mime_type."/".$this_part->subtype;
			
		$this->partlist[$part_no] = $full_mime_type;

		// fetching body with no part_no retrieves the raw contents of the message,
		// I don't want that
		$body = imap_fetchbody($this->mbox,$this->msgid,empty($part_no) ? 1 : $part_no,FT_UID);
			
		$params = $this->_decode_parameters($this_part->parameters);

		if ($mime_type == "text")
		{
			$tmp = $this->_decode($body,$this_part->encoding);
			if ($params["charset"] == "UTF-8")
			{
				$tmp = utf8_decode($tmp);
			};
			$this->rv .= $tmp;
		}
		else
		{
			if ($this_part->ifdisposition)
			{
				$att_name = isset($params["name"]) ? $params["name"] : "unknown";
				$size = $this_part->bytes;
				$caption = "$att_name ($size bytes)\n";
				$this->attachments[$part_no] = $caption;
        		}
		};
	}

	function _parse_subj($str)
	{
		$elements=imap_mime_header_decode($str);
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

	function fetch_part($arr) 
	{
		$struct = imap_bodystruct($this->mbox, imap_msgno($this->mbox, $arr["msgid"]), $arr["part"]);
		$mime_type = $this->_get_mime_type($struct->type,$struct->subtype);
		$params = $this->_decode_parameters($struct->parameters);
		$att_name = isset($params["name"]) ? $params["name"] : "unknown";
		$body = imap_fetchbody($this->mbox,$arr["msgid"],$arr["part"],FT_UID);
		$decbody = $this->_decode($body,$struct->encoding);
		header("Content-type: ".$mime_type);
                header("Content-Disposition: filename=$att_name");
		die($decbody);
	}

	function store_message($arr)
	{
		imap_append($this->mbox,$this->servspec . $this->outbox,
			"From: $arr[from]\r\n"
			."To: $arr[to]\r\n"
			."Subject: $arr[subject]\r\n"
			."\r\n"
			.$arr[message] . "r\n"
		);
	}

	function _get_overview()
	{
		$this->overview_cache_id = "imap" . md5("imap-over".$this->id);
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

	function _extract_address($arg)
	{
		if (preg_match("/(.*)<(.*)>/",$arg,$m))
		{
			$rv = array(
				"name" => $m[1],
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

};
?>
