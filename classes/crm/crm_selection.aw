<?php
/*
@classinfo relationmgr=yes
@default table=objects
@default group=general

@default field=meta
@default method=serialize

//	@property pilot type=relpicker reltype=PILOT
//	@caption n�itamise pilootobjekt

@property template type=select
@caption N�itamise templiit

//	@property use_existing_pilot type=checkbox
//	@caption n�itamisel kasuta konkreetse objekti pilootobjekti kui on olemas


@property active_selection type=textbox group=selectione

@property forms type=checkbox
@caption N�ita tagasiside linke

@property forms type=relpicker reltype=RELTYPE_BACKFORMS2
@caption tagasiside vormid

////////////////////////////////////////////////////////////

@default group=selectione
@groupinfo selectione submit=no caption="Seotud valimid"

@property active_selection_objects type=text callback=callback_obj_list

/////////////////////////////////////////////////////////////

@default group=shou
@groupinfo shou caption="N�ita"
@property dokus type=text callback=show_selection

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

//define ('PILOT', 1);
/*
@reltype BACKFORMS2 value=1 clid=CL_PILOT
@caption Tagasisidevorm

@reltype RELATED_SELECTIONS value=2 clid=CL_CRM_SELECTION
@caption Seotud valimid


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
			case 'forms':
				$data['multiple'] = 1;
			break;
			case 'template':
				$tpls = $this->get_directory(array('dir' => $this->cfg['tpldir'].'/selection/templs/'));
				$data['options'] = $tpls;
			break;
			case 'active_selection':
				$retval = PROP_IGNORE;
			break;

			/*case 'selections':
				if (isset($data['value']) && is_array($data['value']))
				{
				//$data['value']=(array)$data['value'];
					array_unshift($data['value'],$args['obj'][OID]);
				}
				else
				{
					$data['value'][$args['obj'][OID]]=$args['obj'][OID];
//				$data['value'] = array_unique($data['value']);
				}

			break;*/
		}
		return  $retval;
	}

	function callback_obj_list($args)
	{
		$arg2['obj'][OID] = isset($args['obj']['meta']['active_selection']) ? 
		$args['obj']['meta']['active_selection'] : $args['obj'][OID];
		$arg2['obj']['parent'] = $args['obj']['parent'];
		$arg2['obj']['meta']['active_selection'] = $arg2['obj'][OID] ? $arg2['obj'][OID] : $args['obj']['meta']['selections'][0];
		$arg2['sel']['oid'] = $args['obj'][OID];
		return $dat = $this->obj_list($arg2);
	}


	function show_selection($args)
	{
		$retval = $this->show($args);
		$nodes = array();
		$nodes[] = array(
			"value" => $retval,
		);
		return $nodes;
	}

	function obj_list($args)
	{
		//arr($args);	
		$ob = $args['obj'];
		$meta = $ob['meta'];

		$arr = $this->get_selection($ob[OID]);

		load_vcl('table');
		$t = new aw_table(array(
			'prefix' => 'selection_',
		));
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
			'caption' => "<a href='javascript:selall(\"status\")' title='muuda k�ikide objektide aktiivsust'>aktiivne</a>",
			'width' => '20',
			'callback' => array(&$this, 'callb_active'),
			'callb_pass_row' => true,
		));

		$t->define_field(array(
			'name' => 'class_id',
			'caption' => 't��p',
			'sortable' => '1',
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

//arr($this->cfg,1);
		if (is_array($arr))
		{
			foreach ($arr as $key => $val)
			{
				$data = $this->get_object($val['object']);
				$data['status'] = $val['status']; //it has to be this way
				$data['jrk'] = $val['jrk']; //it has to be this way
				$clid = $data['class_id'];
				$data['clid'] = $clid; //it has to be this way
				$data['select'] = html::checkbox(array('name' => 'sel['.$val['object'].']'));

				$data['class_id'] = $this->cfg["classes"][$clid]['name'];

				$t->define_data(
					$data
				);
			}
		}
		$t->sort_by();
		$nodes = array();

		$nodes['manager'] = array(
			"value" =>
			$this->mk_toolbar(array(
				'selection' => $args['sel'][OID],
				'parent' => $args['obj']['parent'],
				'selected' => $meta['active_selection'],
				'show_buttons' => array('activate','add','change','save','delete'),
			)).
			$t->draw().
			html::hidden(array('name' => 'this_selection', 'value' => $args['obj'][OID])).
			html::hidden(array('name' => 'active_selection', 'value' => $meta['active_selection'])),
		);
		return $nodes;
	}

	function callb_name($args)
	{
		return html::href(array(
			'caption' => $args['name'],
			'url' => $this->mk_my_orb('change', array(
					'id' => $args[OID],
					'return_url' => urlencode(aw_global_get('REQUEST_URI')),
				),basename($this->cfg['classes'][$args['clid']]['file'])
			),
		));
	}


	function callb_jrk($args)
	{
		return  html::textbox(array(
			'size' => 4,
			'maxlength' => 4,
			'name' => 'jrk['.$args[OID].']',
			'value' => (int)$args['jrk'],
		));/**/
	}

	function callb_active($args)
	{//arr($args,1);
		return html::checkbox(array(
			'size' => 4,
			'maxlength' => 4,
			'name' => 'status['.$args[OID].']',
			'value' => 1,
			'checked' => ((int)$args['status']==1)
		));
	}
/**/


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

			$selections = $this->get_object_metadata(array(OID => $args['id'],'key' => 'selections'));
			$selections = array_merge($selections,array($newoid => $newoid));
			$this->set_object_metadata(array(
				OID => $args['id'],
				"key" => 'selections',
				"value" => $selections,
			));

		
$data = $this->get_object($args['id']);
$data['class_file'] =  (isset($this->cfg['classes'][$data['class_id']]['alias_class'])) ? $this->cfg['classes'][$data['class_id']]['alias_class'] : $this->cfg['classes'][$data['class_id']]['file'];
$ins = get_instance($data['class_file']);

			
			
			$this->addalias(array(
				'id' => $args['id'],
				'alias' => $newoid,
				'no_cache' => true,
				'reltype' => $ins->selections_reltype,
			));
			

//arr($args,1);
//		p�him�tteliselt siin v�iks minna uue objekti muutmise peale, ja tagasi nupuga saaks tagasi minna
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
		$str = '';
		extract($args);
		$delbutton = isset($delbutton)?$delbutton:true;
		$align = isset($align)?$align:'left';
		$toolbar = get_instance("toolbar",array("imgbase" => "/automatweb/images/icons"));

		/*if (is_array($args['arr']))
		{
			foreach ($args['arr'] as $key => $val)
			{
				$dat = $this->get_object($val);
				$ops[$val] = $dat['name'];
			}
		}*/
		
		$arr = $this->get_aliases(array(
			'oid' => $args['selection'],
			'type' => CL_SELECTION,
		));
		
		$this_obj = $this->get_object($args['selection']);
		if ($this_obj['class_id'] == CL_SELECTION)
		{
			$arr[] = $this_obj;
		}
		
		
				
		if (is_array($arr))
		foreach ($arr as $key => $val)
		{
			$ops[$val[OID]] = $val['name'];
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
			'onClick' => "JavaScript: if (document.changeform.add_to_selection.value < 1){return false}; url='".$this->mk_my_orb('change',array(),'selection')."&id=' + document.changeform.add_to_selection.value; window.open(url);",
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
		if ($a[$this->sortby] == $b[$this->sortby]) return 0;
		return ($a[$this->sortby] > $b[$this->sortby]) ? +1 : -1;
	}

/*
	function speciffic_object_show()
	{

		$ob = $this->get_object($args['id']);

		//$ = get_instance($);
		arr($this->cfg[$ob['class_id']]['file'],1);
		$inst = get_instance($this->cfg[$ob['class_id']]['file']);

		if (method_exists($inst,'show'))
			return $inst->show $args['id']
		else
			return $this->show $args['id']
	}
*/


	function show($args)
	{

		if (isset($args['id']))
		{
			$args['obj'][OID] = $args['id'];
		}

		$obj = $this->get_object($args['obj'][OID]);
		$arr = $this->get_selection($args['obj'][OID],'active');

		//echo $this->sel_tpl = implode('',file($this->cfg['tpldir'].'/selection/templs/default.tpl'));

		if (!isset($obj['meta']['template']))
		{
			return 'templiit m��ramata';
		}

		if (empty($obj['meta']['template']))
		{
			$this->sel_tpl = '{VAR:name}<br />';
		}
		else
		{
			$tpl = $this->cfg['tpldir'].'/selection/templs/'.$obj['meta']['template'];
			$this->sel_tpl = implode('',file($tpl));
		}

		$str = '';

		if (is_array($arr))
		{

			$this->default_forms = $args['obj']['meta']['forms'];
			//sorteerime jrk j�rgi
			$this->sortby = 'jrk';
			uasort($arr, array ($this, 'cmp_obj'));
			foreach ($arr as $key => $val)
			{
/*				if ($cb['get_data'] && method_exists($this,$cb['get_data']))
				{
					$data = call_user_func (array($this,$cb['get_data']), $val['object']);
				}
				else*/
				{//arr($this->cfg,1);

					$data = $this->get_object($val['object']);
					$data['class_file'] =  (isset($this->cfg['classes'][$data['class_id']]['alias_class'])) ? $this->cfg['classes'][$data['class_id']]['alias_class'] : $this->cfg['classes'][$data['class_id']]['file'];
//$str .= $data['name'];
					//switch($meta){}

//					if (!isset($obj['meta']['tpdfggg']))
					{
						if (!isset($inst[$data['class_file']]))
						{
							$inst[$data['class_file']] = get_instance($data['class_file']);
						}

						if (method_exists($inst[$data['class_file']],'show_in_selection'))
						{
							$inst[$data['class_file']]->deafult_forms = $this->default_forms;
							$str .= $inst[$data['class_file']]->show_in_selection(array('id' =>$val['object'],'obj' => $data));
						}
						else
						{
							$str .= $this->show_in_selection(array('id' =>$val['object'],'obj' => $data, 'class_file' => $data['class_file']));
						}
					}
/*					else
					{
						$str .= $this->show_in_selection(array('id' =>$val['object'],'obj' => $data));
					}
*/

/*					$data['pilot'] = html::href(array('caption' => 'tagasiside', 'url' =>
					$this->mk_my_orb('form',array('id' => $data[OID],),'pilot_object'),
					));
					$this->vars($data);

					$str .= $this->parse('object');
					*/
				}

			}
		}
		else
		{
			$str = ' valim t�hi, v�i objekte pole aktiivseks tehtud';
		}
		//$this->vars(array('object' => $str));
		exit_function("selection::");
		exit_function("selection::");
		return $str;
		//$this->parse();
	}

	function show_in_selection($args)
	{
		//$this->read_template();
		//$obj['class_file']
		//siin v�ib teha alampringud jne mida veel vaja objekti juures n�idata


		$forms = '';
		if (is_array($this->default_forms))
		{
			foreach($this->default_forms as $val)
			{
				if (!$val)
				continue;

				$form = $this->get_object($val);
				$args['obj']['tagasisidevormid'] .= html::href(array(
				'target' => $form['meta']['open_in_window']? '_blank' : NULL,
				'caption' => $form['name'], 'url' => $this->mk_my_orb('form', array(
					'id' => $form[OID],
					'feedback' => $args['obj'][OID],
					'feedback_cl' => rawurlencode($data['class_file']),
					),'pilot_object'))).'<br />';
			}
		}
		return localparse($this->sel_tpl, $args['obj']);
	}


/*
	function show($args)
	{

		if (isset($args['id']))
		{
			$args['obj'][OID] = $args['id'];
		}

		$obj = $this->get_object($args['obj'][OID]);
		//if (!isset($obj['meta']['output_as']))
		//{
		//	return 'valimi v�ljundi t��p m��ramata';
		//}
		//arr($arr);
		$arr = $this->get_selection($args['obj'][OID],'active');
//		if (!strval($obj['meta']['output_as']))
//		{
		$tpl = 'templs/default.tpl';
//		}
//		elseif($obj['meta']['output_as'] == 'templates')
//		{
//		}
///		elseif($obj['meta']['output_as'] == 'aw_table')
//		{
//		}
//		elseif($obj['meta']['output_as'] == 'object_show')
//		{
//		}

//			if (isset($obj['meta']['templates']))
//			{
//				$tpl = 'templs/'.$obj['meta']['templates'];
//			}
//			else
//			{
//
//			}
//		}
		$str = '';
		$this->read_template($tpl);

		if (is_array($arr))
		{
			//sorteerime jrk j�rgi
			$this->sortby = 'jrk';
			uasort($arr, array ($this, 'cmp_obj'));
			foreach ($arr as $key => $val)
			{
//				if ($cb['get_data'] && method_exists($this,$cb['get_data']))
//				{
//					$data = call_user_func (array($this,$cb['get_data']), $val['object']);
//				}
//				else
//				{//arr($this->cfg,1);





					$data = $this->get_object($val['object']);
//					$data['class_file'] =  (isset($this->cfg['classes'][$data['class_id']]['alias_class'])) ? $this->cfg['classes'][$data['class_id']]['alias_class'] : $this->cfg['classes'][$data['class_id']]['file'];
//$str .= $data['name'];
					//switch($meta){}

					if (!isset($inst))
					{
						$inst = get_instance($data['class_file']);
					}

					if (method_exists($inst,'show_in_selection'))
					{
						$str .= $inst->show_in_selection(array('id' =>$val['object'],'obj' => $data));
					}
					else
					{
						$str .= $this->show_in_selection(array('id' =>$val['object'],'obj' => $data));
					}
					$data['pilot'] = html::href(array('caption' => 'tagasiside', 'url' =>
					$this->mk_my_orb('form',array('id' => $data[OID],),'pilot_object'),
					));
					$this->vars($data);


					$str .= $this->parse('object');
//				}
			}
		}
		else
		{
			$str = ' valim t�hi, v�i objekte pole aktiivseks tehtud';
		}
		$this->vars(array('object' => $str));
		return //$str;
		$this->parse();
	}
*/




	function set_property($args = array())
	{
		$data = &$args["prop"];
		$form = &$args["form_data"];
		$retval = PROP_OK;

		if (isset($form['del']))
		{
			$this->remove_from_selection($args['obj'][OID],$form['sel']);
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
