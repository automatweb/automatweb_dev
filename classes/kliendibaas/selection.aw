<?php
/*
	@default table=objects
	@default group=general

	@default field=meta
	@default method=serialize

	@property selections type=popup_objmgr clid=CL_SELECTION multiple=1 method=serialize field=meta table=objects width=500
	@caption majanda valimeid

	@property active_selection type=textbox group=objects,selectione

////////////////////////////////////////////////////////////

	@default group=objects
	@groupinfo objects caption=objectid submit=no

	@property obj_list type=text callback=obj_list

/////////////////////////////////////////////////////////////

	@default group=selectione
	@groupinfo selectione caption=valimid submit=no

	@property active_selection_objects type=text callback=callback_obj_list


/////////////////////////////////////////////////////////////

	@default group=shou
	@groupinfo shou caption=shõu
	@property dokus type=text callback=show_selection

*/
class selection extends class_base
{
	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = PROP_OK;
		$meta = $args['obj']['meta'];

		if (!isset($this->selection_args))
		{
			$this->selection_args = $args;
		}

		switch($data["name"])
		{
			case 'active_selection':
				$retval=PROP_IGNORE;
				break;

			case 'jrk':
				$retval = PROP_IGNORE;
			break;
			case 'alias':
				$retval = PROP_IGNORE;
			break;
			case 'selections':
				$data['value']=(array)$data['value'];
				array_unshift($data['value'],$args['obj']['oid']);
				array_unique($data['value']);
			break;
		}
		return  $retval;
	}

	function callback_obj_list($args)
	{
		$arg2['obj']['oid'] = $args['obj']['meta']['active_selection'];
		$arg2['obj']['parent'] = $args['obj']['parent'];
		$arg2['obj']['meta']['active_selection'] = $args['obj']['meta']['active_selection'] ? $args['obj']['meta']['active_selection'] : $args['obj']['meta']['selections'][0];
		$arg2['obj']['meta']['selections'] = $args['obj']['meta']['selections'];
		return $dat = $this->obj_list($arg2);
	}


	function show_selection($args)
	{
		$arg2['obj']['oid'] = $args['obj']['meta']['active_selection'];
		$meta['active_selection'];

		$nodes = array();
		$nodes[] = array(
			"value" => $this->show($arg2),
		);
		return $nodes;
	}


	function obj_list($args)
	{

		$ob = $args['obj'];
		$meta = $ob['meta'];

		$arr = $this->get_selection($ob['oid']);

		load_vcl('table');
		$t = new aw_table(array(
			'prefix' => 'selection_',
		));
		$t->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');

		$t->define_field(array(
			'name' => 'name',
			'caption' => 'nimi',
			'sortable' => 1,
		));

		$t->define_field(array(
			'name' => 'jrk',
			'caption' => 'jrk',
			'width' => '20',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'active',
			'caption' => "<a href='javascript:selall(\"status\")'>aktiivne</a>",
			'width' => '20',
		));

		$t->define_field(array(
			'name' => 'class_id',
			'caption' => 'tüüp',
			'sortable' => 1,
		));

		$t->define_field(array(
			'name' => 'comment',
			'caption' => 'kommentaar',
		));

		$t->define_field(array(
			'name' => 'select',
			'caption' => "<a href='javascript:selall(\"sel\")'>Vali</a>",
			'width' => '20',
		));


		if (is_array($arr))
		{
			foreach ($arr as $key => $val)
			{
				$data = $this->get_object($val['object']);

				$data['select'] = html::checkbox(array('name' => 'sel['.$val['object'].']'));
				$data['class_id'] = $this->cfg["classes"][$data['class_id']]['name'];
				$data['name'] = html::href(array(
					'caption' => $data['name'],
					'url' => $this->mk_my_orb('change',array('id' => $data['oid']),basename($this->cfg["classes"][$data['class_id']]["file"]))
				));
				$data['active'] = html::checkbox(array(
					'size' => 4,
					'maxlength' => 4,
					'name' => 'status['.$val['object'].']',
					'value' => 1,
					'checked' => ((int)$val['status']==1),
				));
				$data['jrk'] = html::textbox(array(
					'size' => 4,
					'maxlength' => 4,
					'name' => 'jrk['.$val['object'].']',
					'value' => (int)$val['jrk'],
				));

				$t->define_data(
					$data
				);
			}
		}

		$t->sort_by();
		$nodes = array();
		$nodes['manager'] = array(
			"value" =>
			//$active_selection.
			$this->mk_toolbar(array(
				'arr' =>$meta['selections'],
				'parent' => $args['obj']['parent'],
				'selected' => $meta['active_selection'],
				'show_buttons' => array('activate','add','change','save','delete'),
			)).
			$t->draw().
			html::hidden(array('name' => 'this_selection', 'value' => $args['obj']['oid'])).
			html::hidden(array('name' => 'active_selection', 'value' => $meta['active_selection'])),

		);
		return $nodes;
	}

	function delete_from_selection($args)
	{
		//arr($args,1);
		$uri=$args['return_url'];

		if ($args['active_selection'])
		{
			if (is_array($args['sel']))
			{
				$this->remove_from_selection($args['active_selection'],$args['sel']);
			}
		}
		header('Location: '.$uri);
		die;
	}

	function save_selection($args)
	{
		//arr($args,1);
		$uri=$args['return_url'];
		$arr = $this->get_selection($args['this_selection']);

		foreach($args['jrk'] as $key => $val)
		{
			if (($arr[$key]['jrk'] != $key) || ((int)$arr['status'][$key] != (int)$args['status'][$key]))
			{
				$q = 'update selection set jrk="'.$val.'" , status="'.$args['status'][$key].'" where oid='.$args['this_selection'].' and object='.$key;
				$this->db_query($q);
			}
		}

		header('Location: '.$uri);
		die;
	}

	function add_to_selection($args)
	{
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
			$newoid = $this->new_object(array(
				'name' => $args['new_selection_name'],
				'status' => 1,
				'parent' => $args['parent'],
				'class_id' => CL_SELECTION
				),false);
			if (is_array($args['sel']))
			{
				$this->set_selection($newoid, $args['sel'],false);

			}

			$selections = $this->get_object_metadata(array('oid' => $args['id'],'key' => 'selections'));
			$selections = array_merge($selections,array($newoid => $newoid));
			$this->set_object_metadata(array(
				"oid" => $args['id'],
				"key" => 'selections',
				"value" => $selections,
			));
//arr($args,1);
//		põhimõtteliselt siin võiks minna uue objekti muutmise peale, ja tagasi nupuga saaks tagasi minna
//			$newuri = $this->mk_my_orb('change',array('id' => $newoid, 'return_url' => $uri),'selection');

//			header('Location: '.$newuri);
//			die;
		}

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
		extract($args);
		$delbutton = isset($delbutton)?$delbutton:true;
		$align = isset($align)?$align:'left';
		$toolbar = get_instance("toolbar",array("imgbase" => "/automatweb/images/icons"));

		if (is_array($arr))
		{
			foreach ($arr as $key => $val)
			{
				$dat = $this->get_object($val);
				$ops[$val] = $dat['name'];
			}
		}
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
			$toolbar->add_cdata('<small>vali valim</small>');
		}

		$toolbar->add_cdata($str);

		foreach ($show_buttons as $button)
		{
			switch ($button)
			{
				case 'activate':
					$toolbar->add_button(array(
					"name" => 'activate',
						"tooltip" => 'aktiveeri',
						"url" => "#",
						"imgover" => "refresh_over.gif",
						"img" => "refresh.gif",
						'onClick' => 'document.changeform.active_selection.value = document.changeform.add_to_selection.value;document.changeform.submit()',
					));
				break;
				case 'add':
					$toolbar->add_button(array(
						"name" => 'go_add',
						"tooltip" => "lisa valitud valimisse",
						"url" => "#",
						"imgover" => "import_over.gif",
						"img" => "import.gif",
						'onClick' => "go_manage_selection(document.changeform.add_to_selection.value,'".$_SERVER['REQUEST_URI']."','add_to_selection','".$parent."');return true;",
					));
				break;
				case 'change':
					$toolbar->add_button(array(
						"name" => 'change_it',
						"tooltip" => 'muuda valimit',
						"url" => "#",
						"imgover" => "edit_over.gif",
						"img" => "edit.gif",
			'onClick' => "url='".$this->mk_my_orb('change',array(),'selection')."&id=' + document.changeform.add_to_selection.value; window.open(url);",
					));
				break;
				case 'save':
					$toolbar->add_button(array(
						"name" => "save",
						"tooltip" => "salvesta",
						"url" => "#",
						"imgover" => "save_over.gif",
						"img" => "save.gif",
						'onClick' => "go_manage_selection(document.changeform.active_selection.value,'".$_SERVER['REQUEST_URI']."','save_selection','".$parent."');return true;",
					));
				break;
				case 'delete':
					$toolbar->add_button(array(
						"name" => "delete",
						"tooltip" => "kustuta valitud",
						"url" => "#",
						"imgover" => "delete_over.gif",
						"img" => "delete.gif",
						'onClick' => "go_manage_selection(document.changeform.this_selection.value,'".$_SERVER['REQUEST_URI']."','delete_from_selection','".$parent."');return true;",
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
			$q = 'select * from selection where oid="'.$oid.'"';
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
			//$arr=$this->db_fetch_array("select object from selection where oid='".$oid."'");
			foreach($arr as $key => $val)
			{
				$values[]='('.$oid.','.$key.')';
			}

			if ($replace)
			{
				$this->db_query('delete from selection where oid='.$oid);
			}
			//echo "insert into selection(oid,object) values ".implode(',',$values);
			$q ="delete from selection where oid='$oid' and object in (".implode(' , ',array_keys($arr)).")";
			$this->db_query($q);
			return $this->db_query("insert into selection(oid,object) values ".implode(',',$values));
		}
	}

	//oid - selection oid
	//objects - array of objects in selection
	function remove_from_selection($oid,$arr=array())
	{
		if (is_array($arr))
		{
			foreach($arr as $key => $val)
			{
				$q='delete from selection where oid='.$oid.' and object='.$key;
				$this->db_query($q);
				$c++; //just count
			}
		}
		return $c;
	}


	function cmp_obj($a, $b)
	{
		if ($a['jrk'] == $b['jrk']) return 0;
		return ($a['jrk'] > $b['jrk']) ? +1 : -1;
	}


	function show($args)
	{

		if (isset($args['id']))
		{
			$args['obj']['oid'] =$args['id'];
		}

		$arr = $this->get_selection($args['obj']['oid'],'active');

		if (method_exists($this, 'callback'))
		{
			$cb = $this->callback_selection_setup();
		}

		if (isset($cb['obj_templ']))
		{
			$this->read_template($cb['obj_templ']);
		}
		else
		{
			$this->read_template('show.tpl');
		}

		if (is_array($arr))
		{
			//sorteerime jrk järgi
			uasort($arr, array ($this, 'cmp_obj'));
			foreach ($arr as $key => $val)
			{
				if ($cb['get_data'] && method_exists($this,$cb['get_data']))
				{
					$data = call_user_func (array($this,$cb['get_data']), $val['object']);
				}
				else
				{
					$data = $this->get_object($val['object']);
				}

				$this->vars($data);
				$str .= $this->parse('obj_templ');
			}
		}
		else
		{
			$str = ' valim tühi';
		}
		$this->vars(array('obj_templ' => $str));
		return $this->parse();
	}

	function selection()
	{
		$this->init(array(
			'clid' => CL_SELECTION,
			'tpldir' => 'selection',
		));
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$form = &$args["form_data"];
		$retval = PROP_OK;

		if ($form['del'])
		{
			$this->remove_from_selection($args['obj']['oid'],$form['sel']);
		}
		switch($data['name'])
		{

		};
		return $retval;
	}

	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}
}
?>
