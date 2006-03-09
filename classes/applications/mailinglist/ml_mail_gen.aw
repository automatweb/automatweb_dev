<?php
/*

@default table=objects
@default field=meta
@default method=serialize

@default group=general

@property background_status_display type=text
@property background process control type=text

*/
classload("core/run_in_background");
class ml_mail_gen extends run_in_background
{
	function ml_mail_gen()
	{
		$this->init();
	}
	
	function bg_checkpoint($o)
	{
		$o->set_meta("checkpointed_var", $this->state);
	}
	
	function bg_run_step($o)
	{
		// process step
		$this->preprocess_messages($o);
	
		//	if ($this->should_run_more_steps())
		//	{
		//	return BG_OK;
		//	}
		return BG_DONE;
	}

	function bg_run_continue($o)
	{
		// restore variables from stored checkpoint
		$this->state = $o->meta("checkpointed_var");
	}
	
	function preprocess_messages($o)
	{
		$arr = $o->meta("mail_data");
		if (!isset($this->d))
		{
			$this->d = get_instance(CL_MESSAGE);
		};

		$msg = $this->d->msg_get(array("id" => $arr["mail_id"]));

		if($arr["mfrom"])
		{
			$msg["mfrom"] = $arr["mfrom"];
		}
		$ml_list_inst = get_instance(CL_ML_LIST);
		$list_obj = new object($arr["list_id"]);
		$member_list = $ml_list_inst->get_members_ol($msg);
		set_time_limit(0);
//		$ret = "";
		$sents = array();
		foreach($member_list->arr() as $member)
		{
			// skip used addresses
			if(in_array($member->prop("mail"), $sents))
			{
				continue;
			}
			$sents[] = $member->prop("mail");
			$this->preprocess_one_message(array(
				"name" => $member->prop("name"),
				"mail" => $member->prop("mail"),
				"mail_id" => $arr["mail_id"],
				"member_id" => $member->id(),
				"list_id" => $arr["list_id"],
				"msg" => $msg,
				"qid" => $arr["qid"],
			));
//			$ret .= $this->parse("item");
		};
	}

	function preprocess_one_message($arr)
	{
		$users = get_instance("users");
		// 1) replaces variables in the message
		// 2) store to ml_sent_mails (which has a default value of '0' in mail_sent values
		// use all variables. 
		//print "<tr><td>".$arr["name"]."</td><td>".$arr["mail"]."</td></tr>\n";
		$vars = md5(uniqid(rand(), true));
		$data = array(
			"name" => $arr["name"],
			"mail" => $arr["mail"],
			"member_id" => $arr["member_id"],
			"mail_id" => $arr["mail_id"],
			"subject" => $arr["msg"]["subject"],
			"traceid" => "?t=$vars",
		);
		$this->used_variables = array();
		$obj = obj($arr["member_id"]);
		$user = reset($obj->connections_to(array(
			"type" => 6,
			"from.class_id" => CL_USER,
		)));
		if(is_object($user))
		{
			$data["username"] = $user->prop("from.name");
			$uo = $user->from();
			$data["name"] = $uo->prop("real_name");
		}
		$unsubscribe_link = $this->mk_my_orb(
			"unsubscribe",
			array(
				"list_source" => $arr["msg"]["meta"]["list_source"],
				"usr" => $arr["member_id"] ,
				"list" => $arr["list_id"]
			),
			"ml_list",
			false,
			true
		);
		$html_mail_unsubscribe = array();
		$mail_obj = obj($arr["mail_id"]);
		if ($mail_obj->prop("html_mail") > 0)
		{
			$html_mail_unsubscribe = array("<a href=\"".$unsubscribe_link."\">" , "</a>");
		}
		$message = preg_replace("#\#pea\#(.*?)\#/pea\##si", '<div class="doc-title">\1</div>', $arr["msg"]["message"]);
		$message = preg_replace("#\#ala\#(.*?)\#/ala\##si", '<div class="doc-titleSub">\1</div>', $message);
		$message = str_replace("#lahkumine#" , $html_mail_unsubscribe[0].$unsubscribe_link.$html_mail_unsubscribe[1] , $message);
		$message = $this->replace_tags($message, $data);
		$subject = $this->replace_tags($arr["msg"]["subject"], $data);
		$from = $address = $arr["msg"]["mfrom"];
		if(is_oid($from) && $this->can("view", $from))
		{
			$adr = obj($from);
			$address = $adr->prop("mail");
		}
		
		//$mailfrom = $this->replace_tags($address, $data);
		$mailfrom = trim($address);
		$subject = trim($subject);
		$mailfrom = $arr["msg"]["meta"]["mfrom_name"] . ' <' . $mailfrom . '>';
		//$used_vars = array_keys($this->used_variables);
		$mid = $arr["mail_id"];
		$member_id = $arr["member_id"];
		$lid = $arr["list_id"];
		
		$this->quote($message);
		$this->quote($subject);
		//$vars = join(",", $used_vars);
		//$this->quote($vars);
		$qid = $arr["qid"];
		$target = $arr["name"] . " <" . $arr["mail"] . ">";
		$this->quote($target);

		$mid = $arr["mail_id"];
		// there is an additional field mail_sent in that table with a default value of 0
		$this->db_query("INSERT INTO ml_sent_mails (mail,member,uid,lid,tm,vars,message,subject,mailfrom,qid,target) VALUES ('$mid','$member','".aw_global_get("uid")."','$lid','".time()."','$vars','$message','$subject','$mailfrom','$qid','$target')");
		//arr($mid.' '.$member.' '.aw_global_get("uid").' '.$lid.' '.time().' '.$vars.' '.$message.' '.$subject.' '.$mailfrom.' '.$qid.' '.$target);
		// 3) process queue then only retrieves messages from that table where mail_sent is set
		// to 0
	}
	
	function replace_tags($text,$data)
	{
		$nohtml = $text;
		preg_match_all("/#(.+?)#/e", $nohtml, $matches);
		if (is_array($matches) && is_array($matches[1]))
		{
			foreach($matches[1] as $v)
			{
				$this->used_variables[$v] = 1;
				$text = preg_replace("/#$v#/", $data[$v] ? $data[$v] : "", $text);
				//decho("matced $v<br />");
			};
		};
		return $text;
	}
}
?>