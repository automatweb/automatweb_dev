<?php
/*
/@classinfo syslog_type=

@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

@default field=meta
@default method=serialize

////////////////////////

@default group=tconfig
@groupinfo tconfig caption=visio

@property table_conf type=text callback=conf_table

@property tconf type=textbox

@property new_column type=textbox

////////////////////////

	@default group=visio
	@groupinfo visio caption=dghdfg
	@property visio type=text callback=visio

*/

class action_table extends class_base
{
	function action_table()
	{
		// change this to the folder under the templates folder, where this classes templates will be,
	    // if they exist at all. the default folder does not actually exist,
	    // it just points to where it should be, if it existed
		$this->init(array(
			'tpldir' => 'action_table',
			'clid' => CL_ACTION_TABLE
		));
	}


	function concon()
	{
		return array(
		'talign' => array('desc' => 'title alignment','type' => 'select', 'options' => array('left', 'center' , 'right'), 'selected' => 'left',),
		'name' => array('desc' =>'veeru nimi', 'type' => 'hidden', 'size' => 5),
		'caption' => array('desc' =>'veeru pealkiri', 'type' => 'textbox', 'size' => 8),
		'align' => array('desc' =>'align', 'type' => 'select', 'options' => array('left', 'center', 'right'), 'selected' => 'left',),
		'nowrap' => array('desc' =>'nowrap', 'type' => 'checkbox', 'value' => '1',),
		'sortable' => array('desc' =>'sortable', 'type' => 'checkbox', 'value' => '1',),
		'numeric' => array('desc' =>'numeric', 'type' => 'checkbox', 'value' => '1',),
		'type' => array('desc' =>'tüüp', 'type' => 'textbox', 'size' => 5),
		'width' => array('desc' =>'width', 'type' => 'textbox', 'size' => 5),
		'format' => array('desc' =>'formaat (d.m.y / H:i))', 'type' => 'textbox', 'size' => 5),
		'strformat' => array('desc' =>'strformat', 'type' => 'textbox', 'size' => 8),
		);
	}


	function conf_table($args)
	{
		//arr($args,1);
		$meta = $args['obj']['meta'];
		extract($args);
		
		if (isset($meta['new_column']) && (strlen($meta['new_column']) > 0))
		{
			$meta['tconf'][$meta['new_column']]['caption'] = $meta['new_column'];
			$meta['tconf'][$meta['new_column']]['name'] = $meta['new_column'];
		}

		$req = $args['request'];

		if (isset($req['swap']))
		{
			$abi = $req['arg1'];
			$meta['tconf'][$req['arg1']] = $meta['tconf'][$req['arg2']];
			$meta['tconf'][$req['arg2']] = $abi;
		}
		
		if (isset($req['delete']))
		{
			unset($meta['tconf'][$req['delete']]);
		}



		$conarr = $this->concon();
		load_vcl('table');

		$t = new aw_table(array('prefix' => 'table_conf'));
		$t->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');


		$t->define_field(array(
			'name' => 'prop',
			'caption' => 'prop',
			'width'=> 140,
		));

		//if ()

		//if (is_array($meta['columns']))
		$meta['tconf']['uus']['name'] = 'uus';
		$meta['tconf']['uus']['caption'] = 'uus';
		$meta['tconf']['uus2']['name'] = 'uus2';
		$meta['tconf']['uus2']['caption'] = 'uus2';

		//$meta['tconf']['new'] = array('name' => 'new', 'caption' => 'new');

		{
			foreach($meta['tconf'] as $colk => $colv)
			{
				$t->define_field(array(
					'name' => $colv['name'],
					'caption' => $colv['caption'].

					html::href(array(
						'url' => aw_global_get("REQUEST_URI").'&delete='.$colk,
						'caption' => ' - ',
					)),
					'width'=> 80,
					//'sortable' => 1,
				));

				$cell = array();
				foreach($conarr as $key => $val)
				{
					$elem = $val['type'];
					$desc = $val['desc'];
					$val['value'] = $meta['tconf'][$colk][$key];
					//arr($meta['tconf'][$colk]);
					//$name = $val['name'];
					$val['name'] = 'tconf['.$colk.']['.$key.']';
//					unset($val['desc']);
//					unset($val['type']);
					//unset($val['']);
					$cells[$key][$colk] = call_user_func(array('html', $elem),$val).'<br />';
				}
			}
		}
//arr($cell,1);

				$t->define_field(array(
					'name' => '',
					'caption' => '',//html::href(array('caption' => 'add', 'url' => $this->mk_my_orb('change', array('id' => $args['obj']['oid'],'group' => 'tconfig')))),
				));


				foreach($cells as $key => $val)
				{
					$val['prop'] = $conarr[$key]['desc'];
					$t->define_data(
						$val
					);
				}


		//$t->sort_by();

		$nodes = array();
		$nodes[] = array(
			"value" => $t->draw().
//			html::hidden(array('name' => 'add_new')).
			html::textbox(array('name' => 'new_column','value' => '',)).
			html::button(array('value' => 'lisa veerg', 'onclick' =>'document.changeform.submit()')),

		);
		return $nodes;




	}




/*

	function get_tbl_fields($args = array())
	{
		return array(
			'test1' => array('caption' => 'assdf'),
			'test2' => array('caption' => 'asdf'),
			'test3' => array('caption' => 'assdfdf'),
			'test4' => array('caption' => 'sdfasdf'),
			'test5' => array('caption' => 'asdfsdf'),
		);
	}


	function get_tbl_actions($args = array())
	{
		return array(
			'new' => array('caption' => 'uus','d' => 'ch'),
			'change' => array('caption' => 'muuda','d' => 'ch'),
			'delete' => array('caption' => 'kustuta','d' => 'ch'),
			'active' => array('caption' => 'aktiivsus','d' => 'ch'),
			'jrk' => array('caption' => 'järjekord','d' => 'ch'),
			'select' => array('caption' => 'vali','d' => 'ch'),
			'action_button' => array('caption' => 'tegevuste nupp','d' => 'ch'),
			'icon' => array('caption' => 'ikoon','d' => 'ch'),
			'none' => array('caption' => 'ei tee midagi','d' => 'ch'),
		);
	}

	function get_tbl_rules()
	{
		return array(
			 'che2' => array('caption' => 'tegevus', 'type' => 'radio', 'data' => $this->get_tbl_actions()),
			 'che1' => array('caption' => 'rasvane', 'type' => 'check', 'applies_to' => array('new','change')),
			 'che3' => array('caption' => 'alla joonitud', 'type' => 'check', 'applies_to' => array('change')),
			 'che4' => array('caption' => 'kursiiv', 'type' => 'check', 'applies_to' => array('change')),
			 'che4' => array('caption' => 'kursiiv', 'type' => 'check', 'applies_to' => array('change')),
		);
	}


	function visio($args = array())
	{


		$nodes = array();
		$nodes['visio'] = array(
			"value" => $this->action_editor(),
		);
		return $nodes;
	}


	function action_editor($args = array())
	{
		$cols = $this->get_tbl_fields();
		$rows = $this->get_tbl_actions();
		$rules = $this->get_tbl_rules();
		load_vcl('table');

		$t = new aw_table(array(
			'prefix' => 'action_table',
			'tbgcolor' => '#00ff00',
		));
		$t->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');

			$t->define_field(array(
				'name' => 'action',
				'caption' => 'tegevus',
				'width'=> 50,
			));
$cols['man'] = array('caption' => 'man');
		foreach ($cols as $key => $val)
		{

			$t->define_field(array(
				'name' => $key,
				'caption' => 
				html::href(array(
						'caption' => ' V ',
						'url' => $this->mk_my_orb('modi', array('id' => $args['id'], 'add' => 'left','ckey' => $key)),
				)).
				$val['caption'].
				html::href(array(
						'caption' => ' V ',
						'url' => $this->mk_my_orb('modi', array('id' => $args['id'], 'add' => 'right','ckey' => $key)),
				)),

				'width'=> 50,
			));
		}

		//foreach ($rows as $action => $val1)
		{
			$data = array();
			foreach ($cols as $field => $val)//actions
			{
				$str='';
				foreach ($rules as $rulename => $rule)
				{
					if ($rule['type'] == 'check')
					{
						$str.=html::checkbox(array('name' => $rulename.'['.$field.']['.$action.']' , 'caption' => $rule['caption']));
					}
					elseif ($rule['type'] == 'radio')
					{
						$cont = '';
						foreach ($rule['data'] as $rnamn => $rbutton)
						{
							$cont.=html::radiobutton(array('name' => $rbutton.'['.$field.']', 'value' =>$rnamn, 'caption' => $rbutton['caption'])).'<br />';
						}
						$str.=html::fieldset(array('caption' => $rule['caption'], 'content' => $cont));
					}
					$str.='<br />';
				}
				$data[$field] = $str;
			}
			$data['action'] = $val1['caption'];
			//			arr($data,1);
			$t->define_data(
				$data
			);
		}
		//$t->sort_by();
		return $t->draw();
	}



	function tabelize($args)
	{

		$cols = $this->get_tbl_fields();
		$rows = $this->get_tbl_actions();

		load_vcl('table');

		$t = new aw_table(array(
			'prefix' => 'action_table',
		));
		$t->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');

		foreach ($cols as $key => $val)
		{

			$t->define_field(array(
				'name' => $key,
				'caption' => $val['caption'],
				'width'=> 50,
			));
		}

		foreach ($data as $key => $val)
		{
			switch ($key)
			{
				case 'change':
					$data[$key] = $val['oid']. $val['name']. $val['parent']. $val['class_id'] ;
				break;
				case 'new':
					$data[$key] = $val['oid']. $val['name']. $val['parent']. $val['class_id'] ;
				break;
				case 'show':
				$data[$key] = $val['oid']. $val['name']. $val['parent']. $val['class_id'] ;
				break;
				case 'delete':
					$data[$key] = $val['oid']. $val['name']. $val['parent']. $val['class_id'] ;
				break;

			}


			$t->define_data(array(
				$data
			));
		}

		$t->sort_by();

		return $t->draw();

	}
*/
/*
change, muuda
new, uus
show näita
delete kustuta

user defined function

sortable sorteeritav

väljad,,,




actionid

checkbox
radio
textbox
active
*/


	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = PROP_OK;
		$meta = $args['obj']['meta'];

		switch($data["name"])
		{
			case 'tconf':
			//arr($args,1);
				$retval = PROP_IGNORE;
			break;
			case 'new_column':
				$retval = PROP_IGNORE;
			break;
		}
		return  $retval;
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
