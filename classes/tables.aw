<?php

global $orb_defs;
$orb_defs["tables"] = array("add"		=> array("function"	=> "add_table",			"params"	=> array("parent")),
														"new"		=> array("function"	=> "add_table",			"params"	=> array("parent")),
													  "delete" => array("function" => "delete",	"params"	=> array("parent","id")),
													  "list"		=> array("function"	=> "gen_list",		"params"	=> array("parent")),
													  "submit"	=> array("function"	=> "submit")
													 );
	
	class tables extends aw_template
	{
		function tables()
		{
			$this->tpl_init("table_gen");
			$this->sub_merge = 1;
			$this->db_init();
		}

		function gen_list($arr)
		{
			extract($arr);
			$this->mk_path($parent,"Tabelid");

			$this->read_template("table_list.tpl");

			$this->db_query("SELECT aw_tables.*, objects.* FROM objects LEFT JOIN aw_tables ON objects.oid = aw_tables.id
											 WHERE objects.status != 0 AND objects.lang_id = ".$GLOBALS["lang_id"]."	AND objects.parent = $parent AND objects.class_id = ".CL_TABLE);
			while ($row = $this->db_next())
			{
				$this->vars(array("table_name" 	=> $row[name], 
													"table_id"		=> $row[id],
													"change"			=> $this->mk_orb("change", array("id" => $row[id]),"table"),
													"delete"			=> $this->mk_orb("delete", array("parent" => $parent, "id" => $row[id])),
													"show"				=> $this->mk_orb("view", array("parent" => $parent, "id" => $row[id]),"table")));

				$this->parse("LINE");
			}
			$this->vars(array("add" => $this->mk_orb("add", array("parent" => $parent))));
			return $this->parse();
		}		
		
		function add_table($arr)
		{
			extract($arr);
			$this->mk_path($parent,"<a href='".$this->mk_orb("list", array("parent" => $parent))."'>Tabelid</a> / Lisa");
			$this->read_template("table_add.tpl");
		  $this->vars(array("reforb" => $this->mk_reforb("submit", array("parent" => $parent))));
			return $this->parse();
		}
		
		function submit($arr)
		{
			$this->quote(&$arr);
			extract($arr);
			
			$id = $this->new_object(array("parent" => $parent, "name" => $name, "class_id" => CL_TABLE, "comment" => $comment));
			$this->db_query("INSERT INTO aw_tables(id) VALUES($id)");

			return $this->mk_orb("change", array("id" => $id), "table");
		}
		
		function delete($arr)
		{
			extract($arr);

			$this->delete_object($id);
			header("Location: ".$this->mk_orb("list", array("parent" => $parent)));
		}
	};
?>
