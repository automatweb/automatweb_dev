<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/forum/forum_comment.aw,v 1.8 2004/07/02 16:22:10 duke Exp $
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

	function get_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "uname":
				if (is_object($args["obj_inst"]) && !is_oid($args["obj_inst"]->id()))
				{
					$data["value"] = $_COOKIE["aw_mb_name"];
					$this->dequote($data["value"]);
				};
				break;
			case "uemail":
				if (is_object($args["obj_inst"]) && !is_oid($args["obj_inst"]->id()))
				{
					$data["value"] = $_COOKIE["aw_mb_mail"];
					$this->dequote($data["value"]);

				};
				break;
			case "comment":
			case "ip":
				$retval = PROP_IGNORE;
				break;

		}
		return $retval;
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "remember":
				if (!empty($data["value"]) && !headers_sent())
				{
					$t = time();
					setcookie("aw_mb_name",$args["request"]["uname"],time()+24*3600*1000);
					setcookie("aw_mb_mail",$args["request"]["uemail"],time()+24*3600*1000);
				};
				break;
	
		};
		return $retval;
	}


	////
	// !Returns a list of comments
	function get_comment_list($arr)
	{
		$retval = "";
		$qparts = array(
			"parent" => $arr["parent"],
			"class_id" => $this->clid,
			//"status" => STAT_ACTIVE,
		);
		if (!empty($arr["period"]))
		{
			$qparts["period"] = $arr["period"];
		};
		$q = sprintf("SELECT oid,uname,name,created,createdby,commtext FROM objects
				LEFT JOIN forum_comments ON (objects.oid = forum_comments.id)
				WHERE (%s) ORDER BY created",join(" AND ",map2("%s='%s'",$qparts)));

		$this->db_query($q);

		$retval = array();

		while($row = $this->db_next())
		{
			$retval[$row["oid"]] = $row;
		};

		return $retval;
	}
}
?>
