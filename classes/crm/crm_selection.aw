<?php
/*
@classinfo relationmgr=yes
@default table=objects
@default group=general

@default field=meta
@default method=serialize

//	@property pilot type=relpicker reltype=PILOT
//	@caption näitamise pilootobjekt

@property template type=select
@caption Näitamise template

//	@property use_existing_pilot type=checkbox
//	@caption näitamisel kasuta konkreetse objekti pilootobjekti kui on olemas


property active_selection type=textbox group=selectione

@property forms type=relpicker multiple=1 reltype=RELTYPE_BACKFORMS2
@caption Tagasiside vormid

@default group=contents
@groupinfo contents submit=no caption="Objektid"
@property active_selection_objects type=callback callback=callback_obj_list

@default group=preview
@groupinfo preview caption="Näita" submit=no
@property contents type=callback callback=show_selection

*/


/*
@reltype BACKFORMS2 value=1 clid=CL_PILOT
@caption Tagasisidevorm

@reltype RELATED_SELECTIONS value=2 clid=CL_CRM_SELECTION
@caption Seotud valimid


*/

/*
CREATE TABLE `selection` (
  `oid` int(11) NOT NULL default '0',
  `object` int(11) NOT NULL default '0',
  `jrk` int(11) default NULL,
  `status` tinyint(4) default NULL,
  UNIQUE KEY `oid` (`oid`,`object`)
) TYPE=MyISAM;

*/


class crm_selection extends class_base
{
	var $selections_reltype;
	
	function crm_selection()
	{
		$this->init(array(
			'clid' => CL_CRM_SELECTION,
			'tpldir' => 'selection',
		));
		$this->selections_reltype = RELATED_SELECTIONS;
	}


	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;

		// mis FUCK sellega toimub?
		if (!isset($this->selection_args))
		{
			$this->selection_args = $args;
		}

		switch($data["name"])
		{
			case 'template':
				$tpls = $this->get_directory(array('dir' => $this->cfg['tpldir'].'/selection/templs/'));
				$data['options'] = $tpls;
				break;

			case 'active_selection':
				$retval = PROP_IGNORE;
				break;

		}
		return  $retval;
	}
	
	function set_property($arr)
	{
		$data = &$arr["prop"];
		$form = &$arr["request"];
		$retval = PROP_OK;
		// see on küll täiesti vale koht selleks, damn I need to add a lot to the 
		// classbase documentation
		if (isset($form['del']))
		{
			$this->remove_objects_from_selection($arr['obj_inst']->id(),$form['sel']);
		}
		switch($data['name'])
		{

		};
		return $retval;
	}

	function callback_obj_list($args)
	{
		// this is invoked directly by class_base .. which in turn invokes the stupid
		// obj_list function. why the fuck does this have to be this way?
		$list_arg["obj"]["id"] = $args["obj_inst"]->meta("active_selection") != "" ?  $args["obj_inst"]->meta("active_selection") : $args["obj_inst"]->id();
		$list_arg['obj']['parent'] = $args['obj_inst']->parent();
		$list_arg['obj']['meta']['active_selection'] = $list_arg['obj']['id'] ? $list_arg['obj']['id'] : $args['obj']['meta']['selections'][0];
		$list_arg['sel']['oid'] = $args['obj']['id'];
		return $dat = $this->obj_list(array(
			"id" => $args["obj_inst"]->id(),
		));
	}


	function show_selection($args)
	{
		$retval = $this->show(array(
			"id" => $args["obj_inst"]->id(),
		));
		$nodes = array();
		$nodes[] = array(
			"value" => $retval,
		);
		return $nodes;
	}

	////
	// !Generates a list of objects in a selection
	// id - id of the selection
	function obj_list($arr)
	{
		$objects = $this->get_selection($arr["id"]);

		load_vcl('table');
		$t = new aw_table();
		$t->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');
		$t->set_default_sortby('jrk');

		$t->define_field(array(
			'name' => 'name',
			'caption' => 'nimi',
			'sortable' => '1',
			'callback' => array(&$this, 'callb_name'),
			'callb_pass_row' => true,
		));

		$t->define_field(array(
			'name' => 'jrk',
			'caption' => 'jrk',
			'width' => '20',
			'sortable' => '1',
			'callback' => array(&$this, 'callb_jrk'),
			'callb_pass_row' => true,
		));
		$t->define_field(array(
			'name' => 'active',
			'caption' => "<a href='javascript:selall(\"status\")' title='muuda kõikide objektide aktiivsust'>aktiivne</a>",
			'width' => '20',
			'callback' => array(&$this, 'callb_active'),
			'callb_pass_row' => true,
		));

		$t->define_field(array(
			'name' => 'class_id',
			'caption' => 'tüüp',
			'sortable' => '1',
		));

		$t->define_field(array(
			'name' => 'comment',
			'caption' => 'kommentaar',
		));

		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid",
		));

		if (is_array($objects))
		{
			foreach ($objects as $object)
			{
				$item = new object($object["object"]);
				$t->define_data(array(
					"id" => $item->id(),
					"name" => $item->name(),
					"status" => $object["status"],
					"jrk" => $object["jrk"],
					"clid" => $item->class_id(),
					"class_id" => $this->cfg["classes"][$item->class_id()]["name"],
				));
			}
		}
		$t->sort_by();
		$nodes = array();

		$nodes['manager'] = array(
			"value" =>
			$this->mk_toolbar(array(
				// whatta fuck?
				'id' => $arr["id"],
				'selection' => $args['sel'][OID],
				'parent' => $args['obj']['parent'],
				'selected' => $meta['active_selection'],
				'show_buttons' => array('activate','add','change','save','delete'),
			)).
			$t->draw().
			html::hidden(array('name' => 'this_selection', 'value' => $arr["id"])).
			// what the fuck is going on with that active_selection?
			html::hidden(array('name' => 'active_selection', 'value' => $meta['active_selection'])),
		);
		return $nodes;
	}

	function callb_name($arr)
	{
		return html::href(array(
			'caption' => $arr['name'],
			'url' => $this->mk_my_orb('change', array(
					'id' => $arr["id"],
					'return_url' => urlencode(aw_global_get('REQUEST_URI')),
				),$arr['clid']),
		));
	}


	function callb_jrk($arr)
	{
		return  html::textbox(array(
			'size' => 4,
			'maxlength' => 4,
			'name' => 'jrk['.$arr["id"].']',
			'value' => (int)$arr['jrk'],
		));
	}

	function callb_active($arr)
	{
		return html::checkbox(array(
			'size' => 4,
			'maxlength' => 4,
			'name' => 'status['.$arr["id"].']',
			'value' => 1,
			'checked' => ((int)$arr['status']==1)
		));
	}

	function save_selection($args)
	{
		//arr($args,1);
		$uri=$args['return_url'];
		if(is_array($args['jrk']))
		{
		$arr = $this->get_selection($args['this_selection']);

		foreach($args['jrk'] as $key => $val)
		{
			if (($arr[$key]['jrk'] != $key) || ((int)$arr['status'][$key] != (int)$args['status'][$key]))
			{
				$q = 'update selection set jrk="'.$val.'" , status="'.$args['status'][$key].'" where oid='.$args['this_selection'].' and object='.$key;
				$this->db_query($q);
			}
		}
		}
		header('Location: '.$uri);
		die;
	}


	function add_to_selection($args)
	{
		// see on siis mingi sitt, mis teeb kas uue selektsiooni või liigutab asju 
		// ühest kohast teise .. geezas christ.
		$uri = $args['return_url'];
		if ($args['add_to_selection'])
		{
			if (is_array($args['sel']))
			{
				$this->set_selection($args['add_to_selection'], $args['sel'],false);
			}

		}
		else
		{
			$o = new object;
			$o->set_class_id($this->clid);
			$o->set_name($args["new_selection_name"]);
			$o->set_status(STAT_NOTACTIVE);
			$o->set_parent($args["parent"]);
			$o->save();


			if (is_array($args['sel']))
			{
				$this->set_selection($o->id(), $args['sel'],false);
			}

		
			$data = $this->get_object($args['id']);
			$data['class_file'] =  (isset($this->cfg['classes'][$data['class_id']]['alias_class'])) ? $this->cfg['classes'][$data['class_id']]['alias_class'] : $this->cfg['classes'][$data['class_id']]['file'];
			$ins = get_instance($data['class_file']);

			$source_object = new object($args["id"]);
			$source_object->connect(array(
				"to" => $o->id(),
				"reltype" => $ins->selections_reltype,
			));
		};
			

		header('Location: '.$uri);
		die;

	}

	//arr - array of selection id-s
	//selected - selected selection
	//parent - parent
	//align - toolbar align (left|center|right)
	//show_buttons - list of buttons to show
	function mk_toolbar($args)
	{
		$str = '';
		extract($args);
		$delbutton = isset($delbutton)?$delbutton:true;
		$align = isset($align)?$align:'left';
		$toolbar = get_instance("toolbar");

		// ah et mõni teine objekt saab ka siis selektsionina käituda jah? god fucking dammit
		if ($args["connection_source"])
		{
			$source_object = new object($args["connection_source"]);
		}
		else
		{
			$source_object = new object($args["id"]);
		};

		$conns = $source_object->connections_from(array(
			"class" => CL_CRM_SELECTION,
		));

		// lisame aktiivse objekti ka, kui ta on valim
		if ($source_object->class_id() == CL_CRM_SELECTION)
		{
			$arr[$source_object->id()] = $source_object->name();
		}
	
		$ops = array();
		foreach($conns as $conn)
		{
			$ops[$conn->prop("to")] = $conn->prop("to.name");
		};
		
		$ops[0] = '- lisa uude valimisse -';
		$str .= html::select(array(
			'name' => 'add_to_selection',
			'options' => $ops,
			'selected' => $selected,
		));

		if ($selected)
		{
			$toolbar->add_cdata('<u><b><small>'.$ops[$selected].'</small></b></u>');
		}
		else
		{
			$toolbar->add_cdata('<small>Vali valim</small>');
		}

		$toolbar->add_cdata($str);
		$REQUEST_URI = aw_global_get("REQUEST_URI");
		foreach ($show_buttons as $button)
		{
			switch ($button)
			{
				case 'activate':
					$toolbar->add_button(array(
					"name" => 'activate',
						"tooltip" => 'aktiveeri',
						"url" => "#",
						"img" => "refresh.gif",
						// siit nagu midagi hargneks juba. urk
						'onClick' => 'document.changeform.active_selection.value = document.changeform.add_to_selection.value;document.changeform.submit()',
					));
					break;
				case 'add':
					$toolbar->add_button(array(
						"name" => 'go_add',
						"tooltip" => "Lisa valitud valimisse",
						"url" => "#",
						"img" => "import.gif",
						'onClick' => "go_manage_selection(document.changeform.add_to_selection.value,'".$REQUEST_URI."','add_to_selection','".$parent."');return true;",
					));
					break;
				case 'change':
					$toolbar->add_button(array(
						"name" => 'change_it',
						"tooltip" => 'Muuda valimit',
						"url" => "#",
						"img" => "edit.gif",
			'onClick' => "JavaScript: if (document.changeform.add_to_selection.value < 1){return false}; url='".$this->mk_my_orb('change',array(),'crm_selection')."&id=' + document.changeform.add_to_selection.value; window.open(url);",
					));
					break;
				case 'save':
					$toolbar->add_button(array(
						"name" => "save",
						"tooltip" => "Salvesta",
						"url" => "#",
						"img" => "save.gif",
						'onClick' => "go_manage_selection(document.changeform.active_selection.value,'".$REQUEST_URI."','save_selection','".$parent."');return true;",
					));
					break;

				case 'delete':
					$toolbar->add_button(array(
						"name" => "delete",
						"tooltip" => "Kustuta valitud objektid valimist",
						"url" => "#",
						"img" => "delete.gif",
						'onClick' => "go_manage_selection(document.changeform.this_selection.value,'".$REQUEST_URI."','delete_from_selection','".$parent."');return true;",
					));
					break;
			}
		}

		$str = html::hidden(array('name' => 'del'));
		$str .= html::hidden(array('name' => 'new_selection_name'));
		$str .= $this->get_file(array("file" => $this->cfg['tpldir'].'/kliendibaas/selall.script'));
		$str .= $this->get_file(array("file" => $this->cfg['tpldir'].'/selection/go_add_to_selection.script'));
		$toolbar->align = $align;
		$toolbar->add_cdata($str);
		return $toolbar->get_toolbar();
	}

	function get_selection($oid, $activs_only = false)
	{
		if (!isset($oid))
		{
			return array();
		}

		if ($activs_only)
		{
			$q = 'select * from selection where status="1" and oid="'.$oid.'"';
		}
		else
		{
			$q = 'select * from selection where oid="'.$oid.'" order by jrk';
		}

		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$arr[$row['object']] = $row;
		}
		return $arr;
	}

	function set_selection($oid,$arr,$replace=true)
	{
		if (count($arr)>0)
		{
			foreach($arr as $key => $val)
			{
				$values[]='('.$oid.','.$key.')';
			}

			// kui on vaja valimi sisu asendada, siis viska kõik minema
			// samas mulle tundub, et seda replace asja ei kasutata kusagil.
			if ($replace)
			{
				$this->db_query('delete from selection where oid='.$oid);
			}
			// muidu ainult valitud itemid
			else
			{
				$q ="delete from selection where oid='$oid' and object in (".implode(' , ',array_keys($arr)).")";
			};
			$this->db_query($q);

			// ja siis lisame uued
			return $this->db_query("insert into selection(oid,object) values ".implode(',',$values));
		}
	}

	////
	// !oid - selection id
	// objects - array of object id's to be removed:
	function remove_objects_from_selection($oid,$arr=array())
	{
		$items = new aw_array($arr);
		$this->db_query("DELETE FROM selection WHERE oid = '$oid AND object IN (" . $items->to_sql() . ")");
	}


	function cmp_obj($a, $b)
	{
		if ($a[$this->sortby] == $b[$this->sortby]) return 0;
		return ($a[$this->sortby] > $b[$this->sortby]) ? +1 : -1;
	}

	////
	// !Displays active items from selection using a template 
	function show($arr)
	{
		$obj = new object($arr["id"]);
		$arr = $this->get_selection($obj->id(),"active");
		if ("" == $obj->prop("template"))
		{
			return 'templiit määramata';
		}

		$this->tpl_init($this->tpldir . "/selection/templs");
		$this->read_template($obj->prop("template"));

		$str = "";

		if (is_array($arr))
		{

			$this->default_forms = $obj->prop("forms");
			//sorteerime jrk järgi
			$this->sortby = 'jrk';
			uasort($arr, array ($this, 'cmp_obj'));

			foreach ($arr as $key => $val)
			{
				$item = new object($val["object"]);
				// figure out which class processes the aliases.. dunno, really
				$classfile = isset($this->cfg['classes'][$item->class_id()]['alias_class']) ? $this->cfg['classes'][$item->class_id()]['alias_class'] : $this->cfg['classes'][$item->class_id()]['file'];
				$inst = get_instance($classfile);

				if (method_exists($inst,'show_in_selection'))
				{
					$inst->default_forms = $this->default_forms;
					$str .= $inst->show_in_selection(array(
						"id" => $item->id(),
					));
				}
				else
				{
					$str .= $this->show_in_selection(array(
						"id" => $item->id(),
					));
				}

			}
		}
		else
		{
			$str = ' valim tühi, või objekte pole aktiivseks tehtud';
		}
		return $str;
	}

	function show_in_selection($args)
	{
		$forms = "";
		$tagasisidevormid = "";
		if (is_array($this->default_forms))
		{
			foreach($this->default_forms as $val)
			{
				$form = new object($val);
				$tagasisidevormid .= html::href(array(
				'target' => $form->meta('open_in_window') != "" ? '_blank' : NULL,
				'caption' => $form->name(), 'url' => $this->mk_my_orb('form', array(
					'id' => $form->id(),
					'feedback' => $args['id'],
					),'pilot_object'))).'<br />';
			}
		}
		$obj = new object($args["id"]);
		$this->vars(array(
			"name" => $obj->name(),
			"parent" => $obj->parent(),
			"id" => $obj->id(),
		));
		$this->vars(array(
			"object" => $this->parse("object"),
		));
		return $this->parse();
	}


	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}
}
?>
