<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/shortcut.aw,v 1.1 2003/07/31 08:13:17 axel Exp $
// shortcut.aw - Shortcut 
/*

@classinfo syslog_type=ST_SHORTCUT relatiomgr=yes

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

*/

class shortcut extends class_base
{
	function shortcut()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
	    // if they exist at all. the default folder does not actually exist, 
	    // it just points to where it should be, if it existed
		$this->init(array(
			'tpldir' => 'admin/shortcut',
			'clid' => CL_SHORTCUT
		));
	}

	function view($args)
	{
		$val = $this->get_object($args['id']);
		$val2 = $this->get_object($val['brother_of']);
		$cldat = $this->cfg['classes'][$val2['class_id']];

		if ($cldat['alias_class'])
		{
			$cldat['file'] = $cldat['alias_class'];
		}

		header('Location:'.$this->mk_my_orb('view', array('id' => $val['brother_of']), $cldat['file']));
		die;
	}
	
	function mk_shortcut($args)
	{
		$val = $this->get_object($args['id']);
		
		$noid = $this->new_object(array(
			"parent" => $val['parent'],
			"class_id" => CL_SHORTCUT,
			"status" => $val['status'],
			"brother_of" => $val[OID],
			"name" => $val["name"].' (kiirviide)',
			"comment" => $val["comment"],
			"jrk" => $val['jrk'],
		));
		header('Location:'.aw_global_get('HTTP_REFERER'));
		die;
		//return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period));
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
