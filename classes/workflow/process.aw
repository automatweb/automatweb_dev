<?php

/*
	@classinfo syslog_type=ST_PROCESS
	@classinfo relationmgr=yes

	@groupinfo general caption=�ldine

	@default table=objects
	@default group=general

	@property ptype type=select field=meta method=serialize
	@caption Protsessi t��p

	@property description type=textarea field=meta method=serialize
	@caption Kirjeldus

	@property goal type=textarea field=meta method=serialize
	@caption Eesm�rk


	@property root_action type=relpicker reltype=RELTYPE_ACTION field=meta method=serialize group=general
	@caption Juurfunktsioon

	@property id type=hidden table=objects field=oid group=actions

	@property action_list type=text group=actions store=no
	@caption Tegevused

	@groupinfo actions caption=Tegevused

*/

define(ROOT_ACTION,1);
define(RELTYPE_ACTION,10);

classload("workflow/workflow_common");
class process extends workflow_common
{
	function process()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
	    // if they exist at all. the default folder does not actually exist, 
	    // it just points to where it should be, if it existed
		$this->init(array(
			'tpldir' => 'workflow',
			'clid' => CL_PROCESS
		));
	}

	function callback_get_rel_types()
	{
		$common = array();
		$common[RELTYPE_ACTION] = "tegevus";
		$common = $common + parent::callback_get_rel_types();
		return $common;
	}

	function _get_classes_for_relation($reltype)
	{
		$retval = false;
		switch($reltype)
		{
			case RELTYPE_ACTION:
				$retval = array(CL_ACTION,CL_PROCESS);
				break;
		};
		return $retval;
	}

	function callback_get_classes_for_relation($args = array())
	{
		$retval = parent::callback_get_classes_for_relation($args);
		return $retval;
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$name = $data["name"];
		$retval = PROP_OK;
		if ($name == "alias" || $name == "jrk" || $name == "comment")
		{
			return PROP_IGNORE;
		};
		
		switch($name)
		{
			case "action_list":
				$retval = $this->action_list(&$data,&$args);
				break;

			case "ptype":
				$data["options"] = array(
					"0" => "--vali--",
					"1" => "p�hiprotsess",
					"2" => "tugiprotsess",
				);
				break;
		}
		return $retval;
	}

	function set_property($args)
	{
		$retval = PROP_OK;
		$data = &$args["prop"];
		switch($data["name"])
		{
			case "action_list":
				$this->set_actions(&$args);
				break;
		};
		return $retval;
	}

	function action_list(&$data,&$args)
	{
		// this is where we let the user create a list of actions
		// and let him choose the root action for example

		$this->read_template("actions.tpl");

		// phase 1, kuvame objekti juurfunktsiooni.
		// ja sinna juurde lingi "defineeri j�rgmine tegevus"
		$root_action = $this->get_object(array(
			"oid" => $args["obj"]["meta"]["root_action"],
			"class_id" => CL_ACTION,
		));

		if (empty($root_action))
		{
			$data["error"] = "Juurtegevus on valimata";
			return PROP_ERROR;
		};

		$action_info = $args["obj"]["meta"]["action_info"];
		$this->action_info = $action_info;

		$alias_reltype = $args["obj"]["meta"]["alias_reltype"];
		if (!is_array($alias_reltype))
		{
			$data["error"] = "Objektil puuduvad 'tegevus' t��pi seosed";
			return PROP_ERROR;
		};


		// list all non-root actions
		$actions = array();
		foreach($alias_reltype as $key => $val)
		{
			if ( ($key != $root_action["oid"]) && ($val == RELTYPE_ACTION) )
			{
				$actions[] = $key;
			};
		}
		
		if (sizeof($actions) == 0)
		{
			$data["error"] = "Objektil pole piisavalt (>1)'tegevus' t��pi seoseid";
			return PROP_ERROR;
		};

		$actiondata[$root_action["oid"]] = $root_action["name"];

		$q = sprintf("SELECT * FROM objects WHERE oid IN (%s)",join(",",$actions));
		$this->db_query($q);
		$el = $line = "";
		while($row = $this->db_next())
		{
			$actiondata[$row["oid"]] = $row["name"];
		};

		$this->actiondata = $actiondata;

		$line .= $this->_draw_action_line(array($root_action["oid"]));
	
		$next = $root_action["oid"];	
		$this->root_action_id = $root_action["oid"];
		if (is_array($action_info))
		{
			foreach($action_info as $key => $val)
			{
				$line .= $this->_draw_action_line($action_info[$next]);
				unset($action_info[$next]);
				$next = $this->next;
			};
		};


		$this->vars(array(
			"line" => $line,
		));


		$data["value"] = $this->parse();
		$data["no_caption"] = 1;

		return PROP_OK;
	}

	function _draw_action_line($list = array())
	{
		$data = new aw_array($list);
		$el = "";
		$this->next = "";
		$retval = false;
		while(list(,$val) = $data->next())
		{
			// I can not have the action itself in the list
			$tmp = array();
			foreach($this->actiondata as $_key => $_val)
			{
				if (($_key != $val) && ($_key != $this->root_action_id))
				{
					$tmp[$_key] = $_val;
				};
			};

			if (sizeof($tmp) > 0)
			{

				$this->vars(array(
					"caption" => $this->actiondata[$val],
					"id" => $val,
					"actlist" => $this->mpicker($this->action_info[$val],$tmp),
				));

				$this->next = $val;

				$el .= $this->parse("element");
			};
		};

		if (sizeof($tmp) > 0)
		{
			$this->vars(array(
				"element" => $el,
			));

			$retval = $this->parse("line");
		};

		return $retval;

	}

	function set_actions($args = array())
	{
		$next_data = $args["form_data"]["next"];
		// and now that I have that information, I quite simply have to create
		// relations between the key in the next array and the values of the
		// next array.

		// aha. but what IF an action can be point to a different next action
		// in another process? That would mean we are screwed, or not?
		$writeout = array();
		if (is_array($next_data))
		{
			foreach($next_data as $el => $values)
			{
				$writeout[$el] = $this->make_keys($values);
			};
		};
		$metadata = &$args["metadata"];
		$metadata["action_info"] = $writeout;
	}

	function callback_pre_save($args = array())
	{
		if ($args["form_data"]["action_order"])
		{
			$coredata = &$args["coredata"];
			$coredata["metadata"]["action_order"] = $args["form_data"]["action_order"];
		};
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
