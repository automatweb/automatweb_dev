<?php
// $Header: /home/cvs/automatweb_dev/classes/protocols/mail/imap.aw,v 1.2 2003/09/11 13:12:50 duke Exp $
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
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. the default folder does not actually exist, 
		// it just points to where it should be, if it existed
		$this->init(array(
			"tpldir" => "protocols/mail/imap",
			"clid" => CL_PROTO_IMAP
		));

		$this->connected = false;
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

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
	// !This should move to the IMAP class, but hey .. I have enough time to do that
	// needs 2 arguments, mailbox name and msgid .. arr,arr
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

		$this->dissect_part($overview,1);

		$rv = $this->rv;

		$msgdata["content"] = $rv;

		return $msgdata;
	}

	function dissect_part($this_part,$part_no,$rv = "")
	{
		if ($this_part->ifdisposition)
		{
			// See if it has a disposition
			// The only thing I know of that this
			// would be used for would be an attachment
			// Lets check anyway
			if ($this_part->disposition == "attachment")
			{
				// If it is an attachment, then we let people download it
				// First see if they sent a filename
				$att_name = "unknown";
            			for ($lcv = 0; $lcv < count($this_part->parameters); $lcv++)
				{
                			$param = $this_part->parameters[$lcv];
                			if ($param->attribute == "name")
					{
                    				$att_name = $param->value;
                    				break;
	            			}
	        		}
				$size = $this_part->bytes;
				$this->rv .= "Attachment: $att_name ($size bytes)\n";
				// You could give a link to download the attachment here....
        		}
			else
			{
				// disposition can also be used for images in HTML (Inline)
			}
		}
		else
		{
			// Not an attachment, lets see what this part is...
			switch ($this_part->type)
			{
				case TYPETEXT:
					$mime_type = "text";
					break;	

				case TYPEMULTIPART:
					$mime_type = "multipart";
					// Hey, why not use this function to deal with all the parts
					// of this multipart part :)
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
			
			// Decide what you what to do with this part
			// If you want to show it, figure out the encoding and echo away


			//print "printing part $full_mime_type / $part_no<br>";
			/*
			print "part_no = $part_no, mime = $full_mime_type<br>";
			*/

			if (substr($part_no,-1) == ".")
			{
				$part_no = substr($part_no,0,-1);
			};

			$body = imap_fetchbody($this->mbox,$this->msgid,$part_no,FT_UID);

			switch ($this_part->encoding)
			{
				case ENCBASE64:
					// use imap_base64 to decode
					$this->rv .= $body;
					break;
				case ENCQUOTEDPRINTABLE:
					$this->rv .= quoted_printable_decode($body);
					break;
				case ENCOTHER:
				default:
					$this->rv .= quoted_printable_decode($body);
					#$this->rv .= $body;
					break;
					// it is either not encoded or we don't know about it
			}
		}
	}

	function _parse_subj($str)
	{
		$elements=imap_mime_header_decode($str);
		for($i=0; $i<count($elements); $i++)
		{
			$rv .= $elements[$i]->text;
		};
		$rv = wordwrap($rv,20,"\n",1);
		return $rv;
	}

};
?>
