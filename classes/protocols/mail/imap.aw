<?php
// $Header: /home/cvs/automatweb_dev/classes/protocols/mail/imap.aw,v 1.1 2003/09/08 14:37:04 duke Exp $
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

	function get_folder_contents()
	{
		$mboxinf = imap_mailboxmsginfo($this->mbox);
		$count = $mboxinf->Nmsgs;
		$overview = imap_fetch_overview($this->mbox,"1:$count",0);

		$res = array();

		if (is_array($overview))
                {
                        foreach($overview as $key => $message)
                        {
                                $rkey = $message->uid;
				$res[$rkey] = array(
					"from" => $message->from,
					"subject" => $message->subject,
                                        "date" => $message->date,
                                        "size" => $message->size,
                                        "seen" => $message->seen,
                                        "answered" => $message->answered,
                                        "recent" => $message->recent,
                                );
                        };
                };

		return $res;
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
};
?>
