<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_meeting.aw,v 1.1 2003/11/10 20:10:14 duke Exp $
// kohtumine.aw - Kohtumine 
/*

@classinfo syslog_type=ST_CRM_MEETING relationmgr=yes

@default table=objects
@default group=general

@property start1 type=datetime_select field=start table=planner 
@caption Algab 

@property duration type=time_select field=end table=planner 
@caption Kestab

@property content type=textarea cols=60 rows=30 table=documents
@caption Sisu

@property summary type=textarea cols=60 rows=30 table=planner field=description
@caption Kokkuvõte

@tableinfo documents index=docid master_table=objects master_index=brother_of
@tableinfo planner index=id master_table=objects master_index=brother_of

*/

class crm_meeting extends class_base
{
	function crm_meeting()
	{
		$this->init(array(
			"clid" => CL_CRM_MEETING,
		));
	}

	
	function __change($args)
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
};
?>
