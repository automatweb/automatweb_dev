<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/forum/forum_comment.aw,v 1.1 2003/06/04 13:57:13 duke Exp $
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
			case "comment":
				$retval = PROP_IGNORE;
				break;
		}
		return $retval;
	}

	function show_add_tpl($arr)
	{
		$this->read_template("add_comment.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit",array("parent" => $arr["parent"],"retval" => $arr["returl"],"status" => STAT_ACTIVE,"period" => aw_global_get("act_per_id"))),

		));
		return $this->parse();
	}

	////
	// !Returns a list of comments
	function get_comment_list($arr)
	{
		$retval = "";
		$qparts = array(
			"parent" => $arr["parent"],
			"class_id" => $this->clid,
			"status" => STAT_ACTIVE,
		);
		if (!empty($arr["period"]))
		{
			$qparts["period"] = $arr["period"];
		};
		$q = sprintf("SELECT name,created,createdby,commtext FROM objects
				LEFT JOIN forum_comments ON (objects.oid = forum_comments.id)
				WHERE (%s) ORDER BY created",join(" AND ",map2("%s='%s'",$qparts)));

		$this->db_query($q);

		// don't read the template/show anything if there are no comments,
		// perhaps this should be configurable though
		if (sizeof($this->num_rows()) > 0)
                {
			$this->sub_merge = 1;
			$this->read_template("comment_list.tpl");
			while($row = $this->db_next())
			{
				$this->vars(array(
					"user" => $row["createdby"],
					"date" => date("d-M-y H:i",$row["created"]),
					"commtext" => $row["commtext"],
					"name" => $row["name"],
				));
				$this->parse("one_comment");
			};
			$retval = $this->parse();
                }
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
