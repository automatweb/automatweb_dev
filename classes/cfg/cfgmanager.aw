<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/cfgmanager.aw,v 1.8 2002/11/12 16:52:42 duke Exp $
// cfgmanager.aw - Object configuration manager
// deals with drawing add and change forms and submitting data

/*
	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

	@property priobj type=objpicker clid=CL_PRIORITY
	@caption Prioriteedi objekt

	@property cfgform type=generated generator=callback_get_groups
	@caption Konfivormid

	// cfgform on miski array vist

*/
class cfgmanager extends aw_template
{
	function cfgmanager($args = array())
	{
		$this->init(array(
			'clid' => CL_CFGMANAGER,
		));
	}

	////
	// !Metainfo for the class
	function get_metainfo($key)
	{
		// XXX: figure out a better way to load those strings
		$params = array(
			"title_add" => "Lisa konfiguratsioonihaldur",
			"title_change" => "Muuda konfiguratsioonihaldurit",
			"class_id" => CL_CFGMANAGER,
		);

		return $params[$key];
	}

	function callback_get_groups($args = array())
	{
		$fields = array();
		// now, if the object is loaded AND has a priority object assigned to it, 
		// generate fields for each member group of the priority object
		$priobj = (int)$args['obj']['meta']['priobj'];
		if (!$priobj)
		{
			return false;
		};
	
		$ginst = get_instance('users');
		$gdata= $ginst->get_group_picker(array('type' => array(GRP_REGULAR,GRP_DYNAMIC)));
		// $gdata now contains a list of gid => name pairs

		$pri = get_instance('priority');
		$grps = new aw_array($pri->get_groups($priobj));
		// $grps now contains a list of gid => priority pairs

		// now we need to create a select element for each
		// member of the group. haha. god dammit, I love this

		// and I also need a list of all configuration forms.
		$cfgforms = $this->list_objects(array('class' => CL_CFGFORM,'addempty' => true));
		$keycount = 0;

		$fields[] = array('caption' => 'Vali gruppide konfiguratsioonivormid');

		foreach($grps->get() as $gid => $pri)
		{
			$keycount++;
			$fields[] = array(
					'name' => "cfgform[$gid]",
					'type' => 'select',
					'options' => $cfgforms,
					'caption' => $gdata[$gid],
					'selected' => $args['prop']['value'][$gid],
			);
		};
		return $fields;
	}
	
	function get_active_cfg_object($id)
	{
		$ob = $this->get_object($id);
		$gidlist = aw_global_get("gidlist");

		$root_id = 0;
	
		$max_pri = 0;
		$max_gid = 0;
		$pri_inst = get_instance("priority");
		$grps = $pri_inst->get_groups($ob["meta"]["priobj"]);
		foreach($gidlist as $ugid)
		{
			if ($grps[$ugid])
			{
				if ($max_pri < $grps[$ugid])
				{
					$max_pri = $grps[$ugid];
					$max_gid = $ugid;
				}
			}
		}
		// now we have the gid with max priority
		if ($max_gid)
		{
			// find the root menu for this gid
			$max_obj = $ob["meta"]["cfgform"][$max_gid];
		}
		return $max_obj;
	}
};
?>
