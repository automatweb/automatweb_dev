<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/forum/forum_comment.aw,v 1.13 2005/04/01 11:52:21 kristo Exp $
// forum_comment.aw - foorumi kommentaar
/*

@default table=objects
@default group=general

@property name type=textbox 
@caption Pealkiri

@default table=forum_comments

@property uname type=textbox
@caption Nimi

@property uemail type=textbox 
@caption E-post

@property remember type=checkbox store=no 
@caption Jäta nimi ja e-post meelde

@property commtext type=textarea 
@caption Kommentaar

@property ip type=textbox 
@caption IP

@classinfo syslog_type=ST_COMMENT no_status=1
@tableinfo forum_comments index=id master_table=objects master_index=oid
*/


        /*
		mysql> describe forum_comments;
		+-------------+---------------------+------+-----+---------+-------+
		| Field       | Type                | Null | Key | Default | Extra |
		+-------------+---------------------+------+-----+---------+-------+
		| id          | bigint(20) unsigned |      | PRI | 0       |       |
		| comm_parent | bigint(20) unsigned |      |     | 0       |       |
		| uname       | varchar(255)        | YES  |     | NULL    |       |
		| uemail      | varchar(255)        | YES  |     | NULL    |       |
		| commtext    | text                | YES  |     | NULL    |       |
		| ip          | varchar(255)        | YES  |     | NULL    |       |
		+-------------+---------------------+------+-----+---------+-------+
        */



class forum_comment extends class_base
{
	function forum_comment()
	{
		$this->init(array(
			'tpldir' => 'forum',
			'clid' => CL_COMMENT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "uname":
				if (is_object($arr["obj_inst"]) && !is_oid($arr["obj_inst"]->id()))
				{
					$prop["value"] = $_COOKIE["aw_mb_name"];
					$this->dequote($prop["value"]);
				};
				break;
			case "uemail":
				if (is_object($arr["obj_inst"]) && !is_oid($arr["obj_inst"]->id()))
				{
					$prop["value"] = $_COOKIE["aw_mb_mail"];
					$this->dequote($prop["value"]);

				};
				break;
			case "comment":
			case "ip":
				$retval = PROP_IGNORE;
				break;

		}
		return $retval;
	}

	function set_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "remember":
				if (!empty($prop["value"]) && !headers_sent())
				{
					$t = time();
					setcookie("aw_mb_name",$arr["request"]["uname"],time()+24*3600*1000);
					setcookie("aw_mb_mail",$arr["request"]["uemail"],time()+24*3600*1000);
				};
				break;

			case "ip":
				if (empty($prop["value"]))
				{
					$prop["value"] = aw_global_get("REMOTE_ADDR");
				};
				break;
	
		};
		return $retval;
	}


	////
	// !Returns a list of comments
	function get_comment_list($arr)
	{
		//arr($arr);
		$clist = new object_list(array(
			"parent" => $arr["parent"],
			"class_id" => $this->clid,
			"period" => $arr["period"],
			"sort_by" => !empty($arr["sort_by"]) ? $arr["sort_by"] : "created",
		));
		//arr($clist);
		$retval = array();
		foreach($clist->arr() as $comment)
		{
			$row = $comment->properties();
			$row["created"] = $comment->created();
			$row["createdby"] = $comment->createdby();
			$row["oid"] = $comment->id();
			$retval[$comment->id()] = $row;
		};

		return $retval;
	}
}
?>
