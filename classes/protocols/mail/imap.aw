<?php
// $Header: /home/cvs/automatweb_dev/classes/protocols/mail/imap.aw,v 1.3 2003/09/12 11:44:05 duke Exp $
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

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "test":
				$data["value"] = $this->test_connection($args);
				break;

		};
		return $retval;
	}

	function test_connection($arr)
	{
		$this->use_mailbox = "INBOX";

		$this->connect_server(array(
			"id" => $arr["obj"][OID],
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

	function connect_server($arr)
	{
		if (!$this->connected)
		{
			$obj = new object($arr["id"]);

			$server = $obj->prop("server");
			$port = $obj->prop("port");
			$user = $obj->prop("user");
			$password = $obj->prop("password");

			$this->servspec = sprintf("{%s:%d/ssl/novalidate-cert}",$server,$port);
			$this->mboxspec = $this->servspec . $this->use_mailbox;
			$this->mbox = @imap_open($this->mboxspec, $user, $password);
			$this->connected = true;
		}
	}

	function list_folders()
	{
		$list = imap_getmailboxes($this->mbox,$this->servspec,"*");
                $res = array();
                if (is_array($list))
                {
                        foreach($list as $item)
                        {
                                $realname = substr($item->name,strlen($this->servspec));
                                $res[$realname] = $realname;
                        };
                };
		return $res;
	}

	function get_folder_contents($arr)
	{
		$mboxinf = imap_mailboxmsginfo($this->mbox);
		$count = $mboxinf->Nmsgs;

		extract($arr);

		$this->count = $count;
		$sorted_array=imap_sort($this->mbox,SORTDATE,1,SE_UID);

		$req_msgs = array_slice($sorted_array,$from-1,($to-$from)+1);
	
		// now I have the message ID-s exactly how I want them
		// .. in correct order as values in the res array
		$seq = join(",",$req_msgs);

		// but imap_fetch_overview does not care about the order, so I have reorder the 
		// the messages myself using the req_id array
		$req_msgs = array_flip($req_msgs);

		$overview = imap_fetch_overview($this->mbox,$seq,FT_UID);
		if (is_array($overview))
                {
                        foreach($overview as $key => $message)
                        {
                                $rkey = $message->uid;
				$req_msgs[$rkey] = array(
					"from" => $message->from,
					"subject" => $this->_parse_subj($message->subject),
                                        "date" => $message->date,
                                        "size" => $message->size,
                                        "seen" => $message->seen,
                                        "answered" => $message->answered,
                                        "recent" => $message->recent,
                                );
                        };
                };

		return $req_msgs;
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

	////
	// !Fetches a single message from the currently connected mailbox
	function fetch_message($arr)
	{
		$msgid = $arr["msgid"];
		$msg_no = imap_msgno($this->mbox,$arr["msgid"]);
		$hdrinfo = imap_headerinfo($this->mbox,$msg_no);

		$msgdata = array(
			"from" => $hdrinfo->fromaddress,
			"reply_to" => $hdrinfo->reply_toaddress,
			"to" => $hdrinfo->toaddress,
			"subject" => $this->_parse_subj($hdrinfo->subject),
			"date" => $hdrinfo->MailDate,
		);
		
		$overview = imap_fetchstructure($this->mbox,$msgid,FT_UID);

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

};
?>
