<?php
/*

//@classinfo syslog_type=
//@classinfo relationmgr=yes
@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

	@default field=meta
	@default method=serialize

//	@property output_as type=select
//	@caption kuidas näidatakse objekte dokumendi sees

//	@property templates type=select
//	@caption objektide näitamise templiidid

//	@property aw_table type=select
//	@caption määra tabeli näitamise objekt

//	@property object_show type=select
//	@caption millega näidatakse kui objektis näitamine puudub

//	@property personal_contact type=relpicker reltype=TEMPLATE
//	@caption

	@property template type=select
	@caption Tagasisidevorm


*/

//define('TEMPLATE',1);
class pilot_object extends class_base
{
	function pilot_object()
	{
		// change this to the folder under the templates folder, where this classes templates will be,
	    // if they exist at all. the default folder does not actually exist, 
	    // it just points to where it should be, if it existed
		$this->init(array(
			'tpldir' => 'pilot_object',
			'clid' => CL_PILOT
		));
	}

//	function callback_get_rel_types()
//	{
//		return array(
//			TEMPLATE => 'objekti valimis näitamise templiit',
//		);
//	}

	function form($args)
	{
		$form = $this->get_object($args['id']);
		$obj = get_instance($args['tagasiside_class']);

		if (method_exists($obj,'fetch_all_data'))
		{
			$data = $obj->fetch_all_data($args['tagasiside']);
		}
		else
		{
			$data = $this->get_object($args['tagasiside']);
		}

		//arr($data,1);
		//return localparse(implode('', file(aw_ini_get('tpldir').'/pilot_object/templs/'.$form['meta']['template'])),$data);
		return localparse(implode('', file($this->cfg['tpldir'].'/pilot_object/templs/'.$form['meta']['template'])),$data);
	}

	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = PROP_OK;
		$meta = $args['obj']['meta'];
		//arr($args,1);
		switch($data["name"])
		{
/*			case 'output_as':

				$data['options'] = array(
					'templates' => 'templiidiga',
					'aw_table' => 'aw tabeliga',
					'object_show' => 'vastava objekti väljund',
				);

			break;
*/
			case 'template':
				$tpls = $this->get_directory(array('dir' => $this->cfg['tpldir'].'/pilot_object/templs'));
				//die;
				$data['options'] = $tpls;
			break;
/*			case 'aw_table':
				if (($meta['output_as'] == 'aw_table'))
				{
				}
				else
				$retval = PROP_IGNORE;
			break;
			case 'object_show':
				if (($meta['output_as'] == 'object_show'))
//				if (!isset($meta['']))
				{
					$data['options'] = array(
						'templates' => 'templiidiga',
						'aw_table' => 'aw tabeliga',
					);
				}
				else
				{
					$retval = PROP_IGNORE;
				}

			break;*/



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
	/*function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		$this->read_template('show.tpl');

		$this->vars(array(
			'name' => $ob['name']
		));

		return $this->parse();
	*/
	
	function obj_parse($val)
	{

		$data = $this->get_object($val['object']);
		
		$data['class_file'] =  (isset($this->cfg['classes'][$data['class_id']]['alias_class'])) ?
		$this->cfg['classes'][$data['class_id']]['alias_class']	:
 		$this->cfg['classes'][$data['class_id']]['file'];

		$inst = get_instance($data['class_file']);
		$edata = array();
		if (method_exists($this,'get_extra_data'))
		{
			$edata = $inst->get_extra_data($val['object']);
		}
		$data = array_merge($data, $edata);

		$this->vars($data);
		return $this->parse('object');
	}
	
	function obj_tabl()
	{

	}
	function obj_self_pilot()
	{

	}


	function show($args)
	{
		if (isset($args['id']))
		{
			$args['obj']['oid'] = $args['id'];
		}
		
		$obj = $this->get_object($args['obj']['oid']);

		if (!is_numeric($obj['meta']['pilot']))
		{
			return 'valimi pilootobjekt määramata!';
		}

		$pilot = $this->get_object($obj['meta']['pilot']);
		//arr($pilot,1);
		$se = get_instance('kliendibaas/selection');
		$arr = $se->get_selection($args['obj']['oid'],'active');

		if (count($arr) < 1)
		{
			return ' valim tühi, või objekte pole aktiivseks tehtud';
		}
		$str = '';
		//sorteerime jrk järgi
		$this->sortby = 'jrk';
		uasort($arr, array ($this, 'cmp_obj'));


		if (!$pilot['meta']['output_as'])
		{
			return '2pilootobjekti väljund määramata!';

		}
		elseif($pilot['meta']['output_as'] == 'templates')
		{
			$this->read_template('templs/'.$pilot['meta']['templates']);
			foreach($arr as $key => $val)
			{
				$str .= $this->obj_parse($val);
			}
		}
		else return 'somsing bad';
		/*
		elseif($obj['meta']['output_as'] == 'aw_table')
		{


		}
		elseif($obj['meta']['output_as'] == 'object_show')
		{

		}*/
/*
		if (isset($obj['meta']['templates']))
		{
			$tpl = 'templs/'.$obj['meta']['templates'];
		}
		else
		{

		}
		$this->read_template($tpl);*/
/*
		if (method_exists($inst,'show_in_selection'))
		{
			$str .= $inst->show_in_selection(array('id' =>$val['object'],'obj' => $data));
		}*//*
		else
		{
			$str .= $this->show_in_selection(array('id' =>$val['object'],'obj' => $data));
		}*/

		$this->vars(array('object' => $str));
		return $this->parse();

	}
/*
	function show_in_selection($args)
	{
		//$this->read_template();
		//$obj['class_file']

		//siin võib teha alampringud jne mida veel vaja objekti juures näidata


	}*/

}
?>
