<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/shortcut.aw,v 1.7 2004/12/01 13:21:57 kristo Exp $
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

		$clss = aw_ini_get("classes");
		$cldat = $clss[$val2->class_id()];

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
		
		$o = obj();
		$o->set_name($val->name().t(' (kiirviide)'));
		$o->set_parent($val->parent());
		$o->set_class_id(CL_SHORTCUT);
		$o->set_status($val->status());
		$o->set_comment($val->comment());
		$o->set_ord($val->jrk());
		$noid = $o->save();

		header('Location:'.aw_global_get('HTTP_REFERER'));
		die;
	}	
}
?>
