<?php

/*

@classinfo syslog_type=ST_ACTION
@classinfo relationmgr=yes

@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property name type=textbox
@caption Nimi

@property description type=textarea field=meta method=serialize
@caption Kirjeldus

@property goal type=textarea field=meta method=serialize
@caption Eesmärk

*/

classload("workflow/workflow_common");
class action extends workflow_common
{
	function action()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. the default folder does not actually exist, 
		// it just points to where it should be, if it existed
		$this->init(array(
			"tpldir" => "action",
			"clid" => CL_ACTION
		));
	}
	
	function callback_get_rel_types()
	{
		return parent::callback_get_rel_types();
	}
	
	function get_property($args)
        {
                $data = &$args["prop"];
		$name = $data["name"];
                $retval = PROP_OK;
		if ($name == "comment" || $name == "alias" || $name == "jrk")
		{
			return PROP_IGNORE;
		};

                switch($data["name"])
                {

		}
	}

	function edit_relation($args = array())
	{
		// now I need to retrieve the id-s of both _ends_ of the relation.
		// how? Since an relation object can be used in multiple places
		// at once.

		// I'll think about that later.
		$q = "SELECT id,source,target FROM aliases WHERE relobj_id = '$args[id]'";
		$this->db_query($q);
		$row = $this->db_next();


		$source_obj = $this->get_object(array(
			"oid" => $row["source"],
			"class_id" => CL_PROCESS,
		));

		$target_id = $row["target"];

		$target_obj = $this->get_object(array(
			"oid" => $target_id,
			"class_id" => CL_ACTION,
		));

		$alias_reltype = $source_obj["meta"]["alias_reltype"];
                if (!is_array($alias_reltype))
                {
                        die("Objektil puuduvad 'tegevus' tüüpi seosed");
                };

                $root_action = $source_obj["meta"]["root_action"];
                // list all non-root actions
                $actions = array();
                foreach($alias_reltype as $key => $val)
                {
                        if ( ($key != $root_action) && ($val == RELTYPE_ACTION) && ($key != $target_id) )
                        {
                                $actions[] = $key;
                        };
                }

		// what would be the best interface for this crap.

		// what about an interface with 3 listboxes, on the left are things that
		// I can come from

		// on the right are things that I can go to

		// and in the middle is unused stuff.

		// this really seems the best interface
		$q = sprintf("SELECT target,reltype FROM aliases WHERE source = '%d' AND reltype IN (%d,%d)",
			$args["id"],RELTYPE_NEXT_ACTION,RELTYPE_PREV_ACTION);
		$this->db_query($q);

		$prev = $next = array();
		while($row = $this->db_next())
		{
			if ($row["reltype"] == RELTYPE_NEXT_ACTION)
			{
				$next[$row["target"]] = 1;
			}
			if ($row["reltype"] == RELTYPE_PREV_ACTION)
			{
				$prev[$row["target"]] = 1;
			}
		};

		$this->read_template("picker.tpl");
	
		$action_objects = array();

		if (sizeof($actions) > 0)
		{
			$q = sprintf("SELECT oid,name,class_id,metadata FROM objects WHERE oid IN (%s)",join(",",$actions));
			$this->db_query($q);
			while($row = $this->db_next())
			{
				$action_objects[$row["oid"]] = $row["name"];
			};

		};

		$prevlist = $nextlist = "";

		foreach($action_objects as $key => $val)
		{
			$el = html::checkbox(array(
				"name" => "prev[$key]",
				"caption" => $val,
				"checked" => (isset($prev[$key])),
			));

			$el .= "<br>";
			
			$el2 = html::checkbox(array(
				"name" => "next[$key]",
				"caption" => $val,
				"checked" => (isset($next[$key])),
			));

			$el2 .= "<br>";

			$prevlist .= $el;
			$nextlist .= $el2;
		};


		$title = html::href(array(
                                "url" => $args["return_url"],
                                "caption" => "Tagasi",
		));

		$title .= "/ Muuda seost";

		$this->mk_path(-1,$title);
		
		$this->vars(array(
			"previous" => $prevlist,
			"next" => $nextlist,
			"thisaction" => $target_obj["name"],
			"reforb" => $this->mk_reforb("submit_relation",array("id" => $args["id"],"return_url" => urlencode($args["return_url"]))),
		));
		

		return $this->parse();

	}

	function submit_relation($args = array())
	{
		// so now what. do I create relations from this action to other actions?

		// but - even if an action is inside one process, I can very well have
		// the same action in other 

		extract($args);

		// first I need to delete all old relations
		$q = sprintf("DELETE FROM aliases WHERE source = '%d' AND reltype IN (%d,%d)",
			$args["id"],RELTYPE_NEXT_ACTION,RELTYPE_PREV_ACTION);
		$this->db_query($q);


		if (is_array($prev) && sizeof($prev) > 0)
		{
			foreach($prev as $act_id => $val)
			{
				$q = sprintf("INSERT INTO aliases (source,target,type,reltype) VALUES (%d,%d,%d,%d)",$args["id"],$act_id,CL_ACTION,RELTYPE_PREV_ACTION);
				$this->db_query($q);
			};
		};

		if (is_array($next) && sizeof($next) > 0)
		{
			foreach($next as $act_id => $val)
			{
				$q = sprintf("INSERT INTO aliases (source,target,type,reltype) VALUES (%d,%d,%d,%d)",$args["id"],$act_id,CL_ACTION,RELTYPE_NEXT_ACTION);
				$this->db_query($q);
	
			};
		};

		return $this->mk_my_orb("edit_relation",array("id" => $args["id"],"return_url" => $args["return_url"]));

	}

	// Now I have an action. and I need to figure out which actions can be gone to as next
	// .. I think that's quite easy, I load the relation object for this process and this
	// action and check out what the next links are

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
