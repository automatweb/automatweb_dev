<?php
// $Header: /home/cvs/automatweb_dev/classes/kliendibaas/Attic/kohtumine.aw,v 1.2 2003/10/06 14:32:27 kristo Exp $
// kohtumine.aw - Kohtumine 
/*

@classinfo syslog_type=ST_KOHTUMINE relationmgr=yes

@default table=objects
@default group=general

@property start1 type=datetime_select field=start table=planner group=calendar
@caption Algab 

@property duration type=time_select field=end table=planner group=calendar
@caption Kestab

@property content type=textarea cols=60 rows=30
@caption Sisu

@property summary type=textarea cols=60 rows=30
@caption Kokkuvõte

@tableinfo documents index=docid master_table=objects master_index=brother_of
@tableinfo planner index=id master_table=objects master_index=brother_of

*/

class kohtumine extends class_base
{
	function kohtumine()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. the default folder does not actually exist, 
		// it just points to where it should be, if it existed
		$this->init(array(
			"tpldir" => "kliendibaas/kohtumine",
			"clid" => KOHTUMINE,
		));
	}

	
	function change($args)
	{
		$args['strs'] = array(
			'type' => 'KOHTUMINE',
			'typeStr' => 'Kohtumine',
			'typestr' => 'kohtumine',
			'typestrs' => 'kohtumise',
		);
		$this->_change($args);
	}
		
	function _change($args)
	{			
		extract($args['strs']);
		
		$kb_oid = aw_global_get('kliendibaas');		
		$cal_id = aw_global_get('user_calendar');

		if ($cal_id)
		{	
			$obj = $this->get_object($args['alias_to']);

			$kliendibaas = $this->get_object($kb_oid);	
			$user_calendar = $this->get_object($cal_id);
			$parents[$type] = $user_calendar['meta']['event_folder'];

			$cfgform[$type] = $kliendibaas['meta'][$typestr.'_form'] ? $kliendibaas['meta'][$typestr.'_form'] : $kliendibaas['meta']['default_form'];

			if ($cfgform[$type])
			{
				$uri = $this->mk_my_orb('',array(
					'alias_to_org' => $args['alias_to'], 
					'reltype_org' => $args['reltype'],
					'class' => 'planner',
					'id' => $cal_id,
					'group' => 'add_event',
					'action' => 'change',
					'title' => urlencode($typeStr.' : '.$obj['name']),
					'parent' => $parents[$type],
					'return_url' => urlencode($args['return_url']),
					'cfgform_id' => $cfgform[$type],
				),'planner');
			}
			else
			{
				if (!$kb_oid)
				{
					echo 'ei saa seost lisada, kasutajal ei ole määratud vaikimisi kliendibaas!<br />';
					$uri = $args['return_url'];
				die('<a href="'.$uri.'">tagasi</a><br />
				<a href="'.$this->mk_my_orb('search',array('return_url' => urlencode($uri)),'search').'">Otsi</a>
				');
				}
				else
				{
					echo 'ei saa seost lisada, puudub '.$typestrs.' sisestusvorm!<br />';
				$uri = $args['return_url'];
					die('<a href="'.$uri.'">tagasi</a><br />
					<a href="'.$this->mk_my_orb('change',array('id' => $kb_oid, 'group' => 'settings','return_url' => urlencode($uri)),'kliendibaas').'">määra kliendibaasis '.$typestrs.' sisestusvorm</a>
					');
				}
			}
		}
		else
		{

			echo 'ei saa seost lisada, kasutajal ei ole määratud vaikimisi kalender!<br />';
			$uri = $args['return_url'];
			die('<a href="'.$uri.'">tagasi</a><br />
			<a href="'.$this->mk_my_orb('search',array('return_url' => urlencode($uri)),'search').'">Otsi</a>
			');

		}
	
	
	
	
		header('Location: '.$uri);
		die;

			
	}


/*	
	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

		};
		return $retval;
	}
*/

	/*
	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
                {

		}
		return $retval;
	}	
	*/

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
		return $this->show(array("id" => $alias["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = new object($id);

		$this->read_template("show.tpl");

		$this->vars(array(
			"name" => $ob->prop("name"),
		));

		return $this->parse();
	}
}
?>
