<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/shortcut.aw,v 1.3 2004/06/11 08:52:02 kristo Exp $
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

	/**  
		
		@attrib name=view params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function view($args)
	{
		$val = obj($args['id']);
		$val2 = obj($val->brother_of());
		$cldat = $this->cfg['classes'][$val2->class_id()];

		if ($cldat['alias_class'])
		{
			$cldat['file'] = $cldat['alias_class'];
		}

		header('Location:'.$this->mk_my_orb('view', array('id' => $val->brother_of()), $cldat['file']));
		die;
	}
	
	/**  
		
		@attrib name=mk_shortcut params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function mk_shortcut($args)
	{
		$val = obj($args['id']);
		
		$noid = $this->new_object(array(
			"parent" => $val->parent(),
			"class_id" => CL_SHORTCUT,
			"status" => $val->status(),
			"brother_of" => $val->id(),
			"name" => $val->name().' (kiirviide)',
			"comment" => $val->comment(),
			"jrk" => $val->jrk(),
		));
		header('Location:'.aw_global_get('HTTP_REFERER'));
		die;
		//return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period));
	}	
}
?>
