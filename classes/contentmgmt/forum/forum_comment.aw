<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/forum/forum_comment.aw,v 1.3 2003/07/01 15:18:42 duke Exp $
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

@classinfo syslog_type=ST_COMMENT
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
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. the default folder does not actually exist, 
		// it just points to where it should be, if it existed
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
				if (empty($args["obj"]["oid"]))
				{
					$data["value"] = $_COOKIE["aw_mb_name"];
					$this->dequote($data["value"]);
				};
				break;
			case "uemail":
				if (empty($args["obj"]["oid"]))
				{
					$data["value"] = $_COOKIE["aw_mb_mail"];
					$this->dequote($data["value"]);

				};
				break;
			case "status":
			case "comment":
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
			case "status":
				$data["value"] = STAT_ACTIVE;
				break;

			case "remember":
				if (!empty($data["value"]) && !headers_sent())
				{
					$t = time();
					setcookie("aw_mb_name",$args["form_data"]["uname"],time()+24*3600*1000);
					setcookie("aw_mb_mail",$args["form_data"]["uemail"],time()+24*3600*1000);
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
		$q = sprintf("SELECT oid,name,created,createdby,commtext FROM objects
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

	////////////////////////////////////
	// object persistance functions - used when copying/pasting object
	// if the object does not support copy/paste, don't define these functions
	////////////////////////////////////

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row['parent'] = $parent;
		unset($row['brother_of']);
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		$this->read_template('show.tpl');

		$this->vars(array(
			'name' => $ob['name']
		));

		return $this->parse();
	}
}
?>
