<?php

/*

	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

	@property status type=status
	@caption Staatus

	@property aliases type=select multiple=1 size=10
	@caption Vali aliased

	@property delete type=checkbox value=1
	@caption Kas kustutame valitud

	@property repeater_obj type=objpicker clid=CL_REPEATER_OBJ
	@caption Vali korduste objekt

	@property del_cache type=checkbox value=1
	@caption Kas kordusel kustutatakse cache

	@property regen_cache type=checkbox value=1
	@caption Kas kordusel tehakse uus cache
	
*/

class cache_manager extends class_base
{
	function cache_manager()
	{
		$this->init(array(
			'tpldir' => 'cache_manager',
			'clid' => CL_CACHE_MGR
		));
	}

	function get_property(&$arr)
	{
		if ($arr['prop']['name'] == 'aliases')
		{
			$ol = $this->get_menu_list();

			$this->db_query("
				SELECT 
					target_o.name as target_name,
					source_o.name as source_name,
					aliases.id as id
				FROM aliases 
					LEFT JOIN objects AS target_o ON target_o.oid = aliases.target
					LEFT JOIN objects AS source_o ON source_o.oid = aliases.source
				WHERE cached='1' AND target_o.status != 0 AND source_o.status != 0
			");

			$ret = array();
			while($row = $this->db_next())
			{
				$ret[$row['id']] = $row['source_name']."::".$row['target_name'];
			}
			$arr['prop']['options'] = $ret;
		}
		else
		if ($arr['prop']['name'] == "delete")
		{
			$arr['prop']['value'] = 0;
		}
		return PROP_OK;
	}

	function callback_post_save($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		$delete = $ob['meta']['delete'];

		if ($delete == 1)
		{
			$cache_inst = get_instance("cache");
			// go over all selected aliases and flush their caches
			$alist = new aw_array($ob['meta']['aliases']);
			$this->db_query("SELECT * FROM aliases WHERE id IN (".$alist->to_sql().")");
			while ($row = $this->db_next())
			{
				$cache_inst->file_invalidate_regex('alias_cache::source::'.$row['source'].'::target::'.$row['target'].'.*');
			}
		}

		if ($ob['meta']['repeater_obj'])
		{
			$sched = get_instance('scheduler');
			$sched->remove(array(
				'event' => str_replace('/automatweb','',$this->mk_my_orb('update_cache', array('id' => $id))),
			));

			$sched->add(array(
				'event' => str_replace('/automatweb','',$this->mk_my_orb('update_cache', array('id' => $id))),
				'rep_id' => $ob['meta']['repeater_obj']
			));
		}
	}

	function update_cache($arr)
	{
		extract($arr);

		$ob = $this->get_object($id);

		die("yeah, all done");
	}
}
?>