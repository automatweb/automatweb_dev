<?php

/*
	@default table=objects
	@default group=general

	@default field=meta
	@default method=serialize

////////////////////////////////////////////////////////////

	@default group=objects
	@groupinfo objects caption=objectid

	@property objects type=popup_objmgr multiple=1 method=serialize field=meta table=objects width=500
//	@property objects type=text
	@caption objektid valimis


*/
class selection extends class_base
{

	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = PROP_OK;
		$meta=$args['obj']['meta'];

		switch($data["name"])
		{
			case 'jrk':
				$retval=PROP_IGNORE;
			break;
			case 'alias':
				$retval=PROP_IGNORE;
			break;
//			case 'objects':
//arr($data,1);
//				$data['value']=$this->obj_list($args['obj']).'mida nendega peale hakata on su oma deela';
//			break;

		}

		return  $retval;
	}

	function obj_list($ob)
	{
		$meta=$ob['meta'];
		$arr=$this->get_selection($ob['oid']);
		if (is_array($arr))
		{
			foreach ($arr as $key => $val) // val [type][nstuff]
			{
				$sel_obj=$this->get_object($key);
				$objs.=$sel_obj['name'].'<br>';
			}
		}
		return $objs;
	}


	function get_selection($oid)
	{
		$this->db_query('select oid,object from selection where oid='.$oid);
		while ($row = $this->db_next())
		{
			$arr[$row['object']] = 1;
		}
		return $arr;
	}

	function set_selection($oid,$arr,$replace=true)
	{
		foreach($arr as $key => $val)
		{
			$values[]='('.$oid.','.$key.')';
		}
		if ($replace)
		{
			$this->db_query('delete from selection where oid='.$oid);
		}
		if (count($arr)>0)
		return $this->db_query("insert into selection(oid,object) values ".implode(',',$values));
	}

	function selection()
	{
		$this->init(array(
			'clid' => CL_SELECTION,
		));
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$form = &$args["form_data"];
		$retval = PROP_OK;
		switch($data['name'])
		{

		};
		return $retval;
	}
}
?>
