<?php
// $Header: /home/cvs/automatweb_dev/classes/core/class_list.aw,v 1.4 2004/05/17 07:46:47 kristo Exp $
// class_list.aw - Klasside nimekiri 
/*

@classinfo syslog_type=ST_CLASS_LIST relationmgr=yes

@default table=objects
@default group=general

*/

class class_list extends class_base
{
	function class_list()
	{
		$this->init(array(
			"tpldir" => "core/class_list",
			"clid" => CL_CLASS_LIST
		));
	}


	/** registers new class

		@attrib name=register_new_class_id nologin="1"

		@param id optional
		@param def required
		@param name required
		@param file required
		@param can_add optional 
		@param parents optional
		@param alias optional
		@param alias_class optional
		@param old_alias optional
		@param is_remoted optional 
		@param subtpl_handler optional 

	**/
	function register_new_class_id($arr)
	{
		if ($arr["id"])
		{
			$new_id = $arr["id"];
		}
		else
		{
			$new_id = $this->db_fetch_field("SELECT max(id) as id FROM aw_class_list", "id")+1;
		}
		$this->db_query("INSERT INTO aw_class_list(id) VALUES($new_id)");
		$this->update_class_def(array(
			"id" => $new_id
		) + $arr);

		return $new_id;
	}

	/** changes class parameters

		@attrib name=update_class_def

		@param id required type=int

		@param def required
		@param name required
		@param file required
		@param can_add optional type=int
		@param parents optional
		@param alias optional
		@param alias_class optional
		@param old_alias optional
		@param is_remoted optional type=int
		@param subtpl_handler optional 
		
	**/
	function update_class_def($arr)
	{
		$this->db_query("
			UPDATE 
				aw_class_list 
			SET
				def = '$arr[def]',
				name = '$arr[name]',
				file = '$arr[file]',
				can_add = '$arr[can_add]',
				parents = '$arr[parents]',
				alias = '$arr[alias]',
				alias_class = '$arr[alias_class]',
				old_alias = '$arr[old_alias]',
				is_remoted = '$arr[is_remoted]',
				subtpl_handler = '$arr[subtpl_handler]'
			WHERE
				id = '$arr[id]'
		");

		return $id;
	}

	/** shows list of classes

		@attrib name=get_list 

	**/
	function get_list($arr)
	{
		$t = get_instance("vcl/table");
		$t = new aw_table();
		$t->set_layout("generic");
		$t->define_field(array(
			"name" => "id",
			"caption" => "id",
			"sortable" => 1,
			"numeric" => 1
		));
		$t->define_field(array(
			"name" => "def",
			"caption" =>"def",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "name",
			"caption" =>"name",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "file",
			"caption" =>"file",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "can_add",
			"caption" =>"can_add",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "parents",
			"caption" =>"parents",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "alias",
			"caption" =>"alias",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "alias_class",
			"caption" =>"alias_class",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "old_alias",
			"caption" =>"old_alias",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "is_remoted",
			"caption" =>"is_remoted",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "subtpl_handler",
			"caption" => "subtpl_handler",
			"sortable" => 1
		));

		$this->db_query("SELECT * FROM aw_class_list");
		while ($row = $this->db_next())
		{
			$t->define_data($row);
		}		
		
		$t->set_default_sortby("id");
		$t->sort_by();

		return $t->draw();
	}
}
?>
