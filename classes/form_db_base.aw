<?php

// ok. what is this, you ask?
//
// well, lemme tell ya a story:
//
// once upon a time in a software not so far away, there was this component called FormGen
// and this component could create and mantain database tables of it's own kind nicely
// but it severely lacked an interface through which it could easily and transparently
// use database tables created not by itself, but by the strange people from the worlds beyond the computer screen.
// after some pondering there was a loud TWANG! and lo and behold - a class by the name of form_db_base was created
// to magically swipe away all the worries of the FormGen.
// it had functions to read and write data - to query the deeply mythical structures of the Database Tables - and even
// modify them, so great was it's power!
// so it fulfilled the void in the heart of FormGen and is still happily doing it until today.
//
// right. a simple rule - all functions in this class MUST be wrapped in save_handle() / restore_handle() calls so
// that they will not interfere with their callers - even if it's pretty freaking obvious they do something with
// the database.
//   
// everything here assumes that the form is already loaded - having to load the form would be too high level
// for this class - it's here to handle the dirty little details and not used from outside formgen - that's also
// the reason why we should look lightly at calling functions defined in classes derived from this 
//
// - terryf

// Documentation is like sex: when it is good, it is very, very good; and
// when it is bad, it is better than nothing.
//
// - duke


class form_db_base extends aw_template
{
	function form_db_base()
	{
		$this->form_base();
	}

	////
	// !returns the id of the form with what the entry ($entry_id) was created
	function get_form_for_entry($entry_id)
	{
		$this->save_handle();
		if (!($ret = aw_cache_get("form_for_entry_cache", $entry_id)))
		{
			$ret = $this->db_fetch_field("SELECT form_id FROM form_entries WHERE id = $entry_id","form_id");
			aw_cache_set("form_for_entry_cache", $entry_id, $ret);
		}
		$this->restore_handle();
		return $ret;
	}

	////
	// !checks if objects are to be created for this form and if they are, then creates the object based 
	// on the parameters in $arr, assumes form is loaded already
	// if objects are not to be created tries to generate a new id in one of the tables and returns that
	// in this case it also completely ignores the arguments passed to this function
	function create_entry_object($arr)
	{
		$this->save_handle();
		$entry_id = 0;
		if ($this->arr["save_table"] == 1)
		{
			// here we must figure out if we need to create any objects for the tables 
			if ($this->arr["save_tables_obj_tbl"] != "")
			{
				// if we get here, we must create an object in the object table and also add a row in the corresponding table
				$entry_id = $this->new_object($arr);
			}
			else
			{
				// if we got here then no object has to be created - but we do have to come up with
				// a new unique id that identifies the entry
				// of course this will create a problem if we save data to several tables, cause then just one id won't be enough
				// instead we need one for every table. 
				// so, now we gotta figure out where the hell do we save those so we can find them later
				// well. but. maybe we don't need several id's after all, cause all the forms have to be related anyway
				// so that we could save data to them and be able to pick it apart later.
				// so what we must now do - is build a whole lotta cyclicity checking and integrity checking where you create the relations
				// right. did that. now when you successfully save the table relations ( you shouldn't be able to save them if they
				// don't add up ) it takes a guess at what would be the best table to start from and writes that in
				// $this->arr["save_table_start_from"] so we could easily use it here

				// so. we start by creating a new row in the first table and return it's id - then we can later use that
				// to find or create the other necessary rows. right? well, yeah sounds kinda fishy, I know, but it sould
				// work..
				$tbl = $this->arr["save_table_start_from"];
				$index_col = $this->arr["save_tables"][$tbl];
				$entry_id = $this->db_fetch_field("SELECT MAX(".$index_col.") as id FROM $tbl","id")+1;	
				// yeah, yeah, I know, race condition, bla-bla, yadda yada. just fuck off, will ya.
				$q = "INSERT INTO $tbl($index_col) VALUES('$entry_id')";
				$this->db_query($q);
			}
		}
		else
		{
			// if we are not saving in precreated tables we always create a new object for entry forms
			// and we should have an option for search forms so that their entries could be saved in the session
			// so we won't be creating all kinds of useless search-objects and slow down the system.
			$entry_id = $this->new_object($arr);
		}
		$this->restore_handle();
		return $entry_id;
	}

	////
	// !if objects are used in this form it updates the properies for object $oid
	// if not, it doesn't do anything
	function update_entry_object($arr)
	{
		$this->save_handle();
		if (!($this->arr["save_table"] == 1 && $this->arr["save_tables_obj_tbl"] == ""))
		{
			$this->upd_object($arr);
		}
		$this->restore_handle();
	}

	////
	// !this maps the data entered through the form to the necessary form (ev_555 => documents.title for instance) 
	// and writes it to the correct tables in the database or if we ever do session saving of form entries then there too
	// $entry_id = the entry's id that is to be created - this sould be the id returned from form_db_base::create_entry_object
	// $entry_data = array of element_id => element_data pairs
	// $chain_entry_id = if the form is a part of a chain entrym then here's how you can have it written to the database
	// function create_entry_data($entry_id,$entry_data,$chain_entry_id = 0)
	function create_entry_data($args = array())
	{
		extract($args);
		// set to 0 if not set
		$chain_entry_id = (int)$chain_entry_id;
		$cal_id = (int)$cal_id;
		$entry_data = $entry;

		$this->save_handle();
		if ($this->arr["save_table"] == 1)
		{
			// here we must write the data to the forms as specified in the form
			// we must start from the table secified in $this->arr[save_table_start_from] and then follow the relations from that
			// and create rows in the other tables with the correct data
			$this->req_create_entry_data($this->arr["save_table_start_from"],$entry_id,$entry_data,$chain_entry_id,true);
		}
		else
		{
			// add new entry - just so that we can determine the form id from the entry's id when we need to
			$this->db_query("INSERT INTO form_entries(id,form_id,cal_id) VALUES($entry_id, $this->id,$cal_id)");

			// create sql 
			reset($entry_data);
			$ids = "id"; 
			$vals = "$entry_id";
			if ($chain_entry_id)
			{
				$ids.=",chain_id";
				$vals.=",".$chain_entry_id;
			}

			$first = true;
			while (list($k, $v) = each($entry_data))
			{
				$el = $this->get_element_by_id($k);
				if (is_object($el))
				{
					$ev = $el->get_value();

					$ids.=",el_$k,ev_$k";
					// see on pildi uploadimise elementide jaoks
					if (is_array($v))
					{
						$v = aw_serialize($v,SERIALIZE_NATIVE);
					}
					$this->quote(&$v);
					$this->quote(&$ev);
					$vals.=",'$v','$ev'";
				};
			}

			$sql = "INSERT INTO form_".$this->id."_entries($ids) VALUES($vals)";
			$this->db_query($sql);
		}
		$this->restore_handle();
	}

	////
	// !takes the data entered by the user and writes it to the database table $tbl and then recursively follows relations 
	// so that all the data gets written to the necessary tables
	// parameters: 
	// $tbl - the table to write the data in
	// $entry_id - the id of the entry
	// $entry_data - the data that the suer entered
	// $chain_entry_id - just in case we figure out a way to use random tables in form chains
	function req_create_entry_data($tbl,$entry_id,$entry_data,$chain_entry_id = 0,$first = false)
	{
		$this->save_handle();
		$els = $this->get_elements_for_table($tbl);

		// now try and piece together a query that sticks the data in the table
		$idx_col = $this->arr["save_tables"][$tbl];
		$colnames = array();
		$colnames[] = $idx_col;	// index column in table
		$elvalues = array();
		$elvalues[] = $entry_id;

		foreach($els as $el)
		{
			$colnames[] = $el->get_save_col();
			$ev = $el->get_value();
			$this->quote(&$ev);
			$elvalues[] = $ev;
		}

		if ($first && $this->arr["save_tables_obj_tbl"] == "")	
		{
			// we do this, because we already created a row in the first table to get the entry_id if we don't create objects for entries
			// convert the arrays into the correct form
			$dat = array();
			foreach($colnames as $_id => $_colname)
			{
				if ($colname != $idx_col)
				{
					$dat[$_colname] = $elvalues[$_id];
				}
			}
			$q = "UPDATE $tbl SET ".join(",",$this->map2("%s = '%s'",$dat))." WHERE $idx_col = '$entry_id'";
		}
		else
		{
			// here we must insert a new row in the correct table
			$q = "INSERT INTO $tbl (".join(",",$colnames).") VALUES(".join(",",$this->map("'%s'",$elvalues)).")";
		}
		$this->db_query($q);

		// we have managed to write the data, now we must recurse with the next table in line
		$_tmp = $this->arr["save_tables_rels"][$tbl];
		if (is_array($_tmp))
		{
			// go through the tables related to this one one by one and have them write their data
			foreach($_tmp as $r_tbl => $r_tbl)
			{
				$this->req_create_entry_data($r_tbl,$entry_id,$entry_data,$chain_entry_id);
			}
		}
		$this->restore_handle();
	}

	////
	// !returns an array that contains the id's => column of the loaded form's elements that sould be written to table $tbl 
	function get_elements_for_table($tbl)
	{
		$ret = array();
		$els = $this->get_all_els();
		foreach($els as $el)
		{
			if ($el->get_save_table() == $tbl)
			{
				$ret[] = $el;
			}
		}
		return $ret;
	}

	////
	// !updates the data in the correct storage medium from the data gathered from the POST data
	function update_entry_data($entry_id,$entry_data)
	{
		$this->save_handle();
		if ($this->arr["save_table"] == 1)
		{
			// update all the tables recursively following the relations
			$this->req_update_entry_data($this->arr["save_table_start_from"],$entry_id,$entry_data);
		}
		else
		{
			// create sql 
			reset($entry_data);
			$ids = "id = $entry_id";
			$first = true;

			while (list($k, $v) = each($entry_data))
			{
				$el = $this->get_element_by_id($k);
				if ($el)
				{
					$ev = $el->get_value();
					if (is_array($v))
					{
						$v = serialize($v);
					}
					$ids.=",el_$k = '$v',ev_$k = '$ev'";
				}
			}

			$sql = "UPDATE form_".$this->id."_entries SET $ids WHERE id = $entry_id";
			$this->db_query($sql);
		}
		$this->restore_handle();
	}

	////
	// !recursively writes the data to the correct tables and maps it from elements to table columns
	function req_update_entry_data($tbl,$entry_id,$entry_data)
	{
		$this->save_handle();
		$els = $this->get_elements_for_table($tbl);

		// now iterate over the elemnents and do the correct column mappings
		$dat = array();
		foreach($els as $el)
		{
			$ev = $el->get_value();
			$this->quote(&$ev);
			$dat[$el->get_save_col()] = $ev;
		}

		// and now just turn it into a query - hey, this is easy
		$idx_col = $this->arr["save_tables"][$tbl];
		$q = "UPDATE $tbl SET ".join(",",$this->map2("%s = '%s'",$dat,0,true))." WHERE $idx_col = '$entry_id'";
//		echo "q = $q <br>";
		$this->db_query($q);

		// and now recurse to the other tables
		$_tmp = $this->arr["save_tables_rels"][$tbl];
		if (is_array($_tmp))
		{
			// go through the tables related to this one one by one and have them write their data
			foreach($_tmp as $r_tbl => $r_tbl)
			{
				$this->req_update_entry_data($r_tbl,$entry_id,$entry_data);
			}
		}
		$this->restore_handle();
	}

	////
	// !deletes $entry_id of form $id and redirects to hexbin($after) 
	// before deleting it checks if the form has objects created - if not, no entry is deleted
	function delete_entry($arr)
	{
		extract($arr);
		if ($this->id != $id)
		{
			$this->load($id);
		}
		if (($this->arr["save_table"] == 1 && $this->arr["save_tables_obj_tbl"] != "") || $this->arr["save_table"] != 1)
		{
			$this->delete_object($entry_id);
			$this->_log("form","Kustutas formi $this->name sisestuse $entry_id");
			$after = $this->hexbin($after);
			header("Location: ".$after);
			die();
		}
	}

	////
	// !reads the data for the entry from it's designated place, maps it to elements and bundles it in an array of
	// $el_id => $el_value pairs which it returns
	function read_entry_data($entry_id,$silent_errors = false)
	{
		$this->save_handle();

		// we gather the el_id => el_value pairs here
		$ret = array();

		// now put all the joins into sql
		$sql_join = $this->get_sql_joins_for_search(false,$this->id);

		// now get fetch data part
		$sql_data = $this->get_sql_fetch_for_search($this->_joins, $this->id);

//			echo "sql join = $sql_join , <br><br><br> $sql_data = $sql_data <br>";
		if ($this->arr["save_table"])
		{
			$idx_tbl = $this->arr["save_table_start_from"];
			$idx_col = $this->arr["save_tables"][$idx_tbl];
		}
		else
		{
			$idx_tbl = "form_".$this->id."_entries";
			$idx_col = "id";
		}
		$sql = "SELECT $sql_data FROM $sql_join WHERE $idx_tbl.$idx_col = $entry_id";
//		echo "sql = $sql <br>";
		$this->db_query($sql);
		$row = $this->db_next();

		$this->read_elements_from_q_result($row, &$ret);
//			echo "sql = $sql <br>";

		$this->restore_handle();
		return $ret;
	}

	////
	// !this function basically copies data from one array to another except that it just copies entries
	// whose key starts with el_ and is followed by a number
	// it reads the number after ev_ and puts it on $res and assigns the same value to it
	function read_elements_from_q_result($row,&$res)
	{
		if (is_array($row))
		{
			foreach($row as $k => $v)
			{
				if (substr($k,0,3) == "el_")
				{
					$res[substr($k,3)] = $v;
				}
			}
		}
	}

	////
	// !returns the sql query that will perform the search, based on the loaded form and the loaded entry
	// parameters:
	//	$used_els - if it is omitted or is an empty array, then all elements from all the forms are returned
	//	if it contains some element id's, then only those elements are returned
	function get_search_query($arr)
	{
		extract($arr);
		if (!is_array($used_els))
		{
			$used_els = array();
		}

		// ugh. this is the complicated bit again. 

		// now put all the joins into sql
		$sql_join = $this->get_sql_joins_for_search($used_els,$this->arr["start_search_relations_from"]);

//		echo "used_els = <pre>", var_dump($used_els),"</pre> <br>";
//		echo "got sql join = $sql_join <br>";

		// now get fetch data part
		$sql_data = $this->get_sql_fetch_for_search($this->_joins,$this->arr["start_search_relations_from"]);
	
//		echo "got sql data = $sql_data <br>";
		// and finally the where part 
		$sql_where = $this->get_sql_where_clause();
		if ($sql_where != "")
		{
			$sql_where = "WHERE ".$sql_where;
		}
		
		$sql = "SELECT ".$sql_data." FROM ".$sql_join." ".$sql_where;
		dbg("sql = $sql <br>");
		return $sql;
	}

	////
	// !returns an instance of form $fid - caches the instances as well
	function &cache_get_form_instance($fid)
	{
		if (!is_object($this->form_instance_cache[$fid]))
		{
			$this->form_instance_cache[$fid] = new form;
			$this->form_instance_cache[$fid]->load($fid);
		}

		return $this->form_instance_cache[$fid];
	}

	////
	// !returns an array of db tables for this form - 
	//		if it's a normal form then it's just one table - form_[id]_entries
	//		but if the form writes to other tables then returns all the names of the tables and the info on how to join them
	function get_tables_for_form()
	{
		if ($this->arr["save_table"] == 1)
		{
			return array(
				"from" => $this->arr["save_table_start_from"],
				"joins" => $this->arr["save_tables_rels"],
				"join_via" => $this->arr["save_tables_rel_els"],
				"table_indexes" => $this->arr["save_tables"]
			);
		}
		else
		{
			$ftn = "form_".$this->id."_entries";
			$fta = array();
			$fta[$ftn][$ftn] = $ftn;
			return array(
				"from" => $ftn, 
				"joins" => $fta,
				"join_via" => array(),
				"table_indexes" => array($ftn => "id")
			);
		}
	}

	////
	// !takes the form to table join map and builds sql joins from those 
	// it follows the relations between the forms to search from and the relations between the form tables for 
	// each form are described in $form2table 
	function get_sql_joins_for_search($used_els, $start_relations_from)
	{
		// recurse through the selected search form relations. boo-ya!
		$this->_joins = array();
		$this->_used_forms_map = array();
		$this->_used_tables_map = array();

		$this->build_form_relation_tree($start_relations_from);

		// if used_els is not an array, that means all elements from all forms should be included
		if (!is_array($used_els))
		{
			$used_els = array();
			if (is_array($this->form_rel_tree))
			{
				foreach($this->form_rel_tree as $fid => $frels)
				{
					$used_els[$fid] = array();
					foreach($frels as $frfid => $frdat)
					{
						$used_els[$frfid] = array();
					}
				}
			}
		}

		$srfi =& $this->cache_get_form_instance($start_relations_from);

		if ($srfi->arr["save_tables"])
		{
			$tn = $srfi->arr["save_table_start_from"];
		}
		else
		{
			$tn = "form_".$start_relations_from."_entries";
		}
		// add the start table to the join map
		$this->_joins[] = array(
			"from_tbl" => $tn, 
			"from_el" => false,
			"to_tbl" => false,
			"to_el" => false
		);
		if (!$srfi->arr["save_tables"])
		{
			$this->_joins[] = array(
				"from_tbl" => $tn, 
				"from_el" => "id",
				"to_tbl" => "objects",
				"to_el" => "oid"
			);
		}
		$this->table2form_map[$tn] = $start_relations_from;


		// here's how we're gonna do this:
		// loop over all forms used in the query and for each form
		// find the path to it from the starting form, via relations
		// always start from the form markerd as the one to start the searches from

		// first the elements that are used in the table
		foreach($used_els as $fid => $els)
		{
			if ($fid != $start_relations_from)
			{
//				echo "used el $fid <br>";
				$jp = $this->get_join_path($start_relations_from, $fid);
//				echo "join path from ",$start_relations_from," to $fid = <pre>", var_dump($jp),"</pre> <br>";
				$this->build_join_rels_from_path($jp);
			}
		}
		// now the elements that are used in the where part
		$forms_queried = $this->get_forms_used_in_where();
		foreach($forms_queried as $fid)
		{
			if ($fid != $start_relations_from)
			{
//				echo "used el $fid <br>";
				$jp = $this->get_join_path($start_relations_from, $fid);
//				echo "join path from ",$start_relations_from," to $fid = <pre>", var_dump($jp),"</pre> <br>";
				// now build the correct relations from the path
				$this->build_join_rels_from_path($jp);
			}
		}

//		echo "joins = <pre>", var_dump($this->_joins),"</pre> <br>";
//		die();
		// then merge the path trees and create the query
//		$this->merge_join_paths();	// this puts the merged joins into $this->_joins

		// ok, so we can assume that we have all the necessary relations in $this->_joins, so convert that into sql
		$sql = "";
		$first = true;
		$this->join_sql_used = array();
		foreach($this->_joins as $jdata)
		{
			if ($first)
			{
				$sql = $jdata["from_tbl"];
				$prev = $jdata;
			}
			else
			{
				if (!isset($this->join_sql_used[$jdata["to_tbl"]]))
				{
					$sql.=" LEFT JOIN ".$jdata["to_tbl"]." ON ".$jdata["from_tbl"].".".$jdata["from_el"]." = ".$jdata["to_tbl"].".".$jdata["to_el"];
					$this->join_sql_used[$jdata["to_tbl"]] = $jdata["to_tbl"];
				}
			}
			$first = false;
		}
//		echo " sql = $sql <br>";
		return $sql;
	}

	function get_sql_fetch_for_search($joins, $start_relations_from)
	{
		// return all elements from all tables, map them to el_[id] values
		$sql = "";
		$usedtbls = array();
		foreach($joins as $jdata)
		{
			// find the form for the table and get all elements of the form and find out what columns they map to in the table
			$tbl = $jdata["from_tbl"];
			if (!$usedtbls[$tbl])
			{
				$usedtbls[$tbl] = 1;
				$fid = $this->table2form_map[$tbl];
				$form =& $this->cache_get_form_instance($fid);

				if ($sql == "")
				{
					$_tf =& $this->cache_get_form_instance($fid);
					$_tftbls = $_tf->get_tables_for_form();

					$sql=$tbl.".".$_tftbls["table_indexes"][$tbl]." AS entry_id "; 
					if ($this->arr["search_chain"])
					{
						// if we are doing a chain search, then also get the chain id
						$sql.=",".$tbl.".chain_id AS chain_entry_id "; 
					}
				}

				$els = $form->get_all_els();
				foreach($els as $el)
				{
					$s_t = $el->get_save_table();
					if ($s_t == $tbl)
					{
						// if this element gets written to the current table, include it in the sql
						$sql.=", ".$tbl.".".$el->get_save_col()." AS ev_".$el->get_id();
						$sql.=", ".$tbl.".".$el->get_save_col2()." AS el_".$el->get_id();
					}
				}
			}
			$tbl = $jdata["to_tbl"];
			if (!$usedtbls[$tbl])
			{
				$usedtbls[$tbl] = 1;
				$fid = $this->table2form_map[$tbl];
				if ($fid)
				{
					$form =& $this->cache_get_form_instance($fid);

					if ($sql == "")
					{
						$_tf =& $this->cache_get_form_instance($fid);
						$_tftbls = $_tf->get_tables_for_form();

						$sql=$tbl.".".$_tftbls["table_indexes"][$tbl]." AS entry_id "; 
						if ($this->arr["search_chain"])
						{
							// if we are doing a chain search, then also get the chain id
							$sql.=",".$tbl.".chain_id AS chain_entry_id "; 
						}
					}

					$els = $form->get_all_els();
					foreach($els as $el)
					{
						$s_t = $el->get_save_table();
						if ($s_t == $tbl)
						{
							// if this element gets written to the current table, include it in the sql
							$sql.=", ".$tbl.".".$el->get_save_col()." AS ev_".$el->get_id();
							$sql.=", ".$tbl.".".$el->get_save_col2()." AS el_".$el->get_id();
						}
					}
				}
			}
		}

		// now if the start search from form is not written to table it will get objects table
		// joined and then we can fetch creator/modifier and other fields
		$srfi =& $this->cache_get_form_instance($start_relations_from);
		if (!$srfi->arr["save_tables"])
		{
			$sql.=", objects.modified as modified, objects.created as created, objects.modifiedby as modifiedby ";
		}

//		echo "sql = $sql <br>";
		return $sql;
	}

	function get_sql_where_clause()
	{
		$els = $this->get_all_els();

		$ch_q = array();
		reset($els);
		// loop through all the elements of this form 
		while( list(,$el) = each($els))
		{
			if ($el->arr["linked_form"] && $el->arr["linked_element"])	
			{
				$relf =& $this->cache_get_form_instance($el->arr["linked_form"]);
				$linked_el = $relf->get_element_by_id($el->arr["linked_element"]);

				if (is_object($linked_el))
				{
					$elname = $linked_el->get_save_table().".".$linked_el->get_save_col2();
				}

				if (trim($el->get_value()) != "")	
				{
					if ($el->get_type() == "multiple")
					{
						if ($query != "")
						{
							$query.=" AND ";
						}
						$query.=" (";
						$ec=explode(",",$el->entry);
						reset($ec);
						$qpts = array();
						while (list(, $v) = each($ec))
						{
							$qpts[] = " ".$elname." like '%".$el->arr["multiple_items"][$v]."%' ";
						}

						$query.= join("OR",$qpts).")";
					}
					else
					if ($el->get_type() == "checkbox")
					{	
						//checkboxidest ocime aint siis kui nad on tshekitud
						if ($el->get_value(true) == 1)
						{
							// grupeerime p2ringus nii et checkboxi gruppide vahel on AND ja grupi sees OR
							$ch_q[$el->get_ch_grp()][] = " ".$elname." like '%".$el->get_value()."%' ";
						}
					}
					else
					if ($el->get_type() == "radiobutton")
					{
						if ($el->get_value(true) == 1)
						{
							if ($query != "")
							{
								$query.=" AND ";
							}
							$query.=" (".$elname." LIKE '%".$el->get_value()."%')";
						}
					}
					else
					if ($el->get_type() == "listbox")
					{
						$elname2 = $linked_el->get_save_table().".".$linked_el->get_save_col();
						if ($query != "")
						{
							$query.=" AND ";
						}
						$query.=" (".$elname2." LIKE '%".$el->get_value()."%')";
					}
					else
					if ($el->get_type() == "date")
					{
						if ($query != "")
						{
							$pre = " AND";
						}
						if ($el->get_subtype() == "from")
						{
							$query.= $pre." (".$elname." >= ".$this->entry[$el->get_id()].")";
						}
						else
						if ($el->get_subtype() == "to")
						{
							$query.= $pre." (".$elname." <= ".$this->entry[$el->get_id()].")";
						}
						else
						{
							$query.= $pre." (".$elname." = ".$this->entry[$el->get_id()].")";
						}
					}
					else
					if ( ($el->get_type() == "textbox") && ($el->get_subtype() == "count") )
					{
						// count is special, we don't want to search in that field
						// think calendar!


					}
					else
					{
						$value = $el->get_value();

						// now split it at the spaces
/*						if (preg_match("/\"(.*)\"/",$value,$matches))
						{
							$qstr = " $elname LIKE '%$matches[1]%' ";
						}
						else
						{
							$pieces = explode(" ",$value);
							if (is_array($pieces))
							{
								$qstr = join (" OR ",map("$elname LIKE '%%%s%%'",$pieces));
							}
							else
							{
								$qstr = " $elname LIKE '%$value%' ";
							};
						};*/
						$qstr = " $elname LIKE '%$value%' ";

						if ($query != "")
						{
							$query .= "AND ";
						}
						$query.= "($qstr)";
					}
				}
			}
		}

		// k2ime l2bi erinevad checkboxide grupid ja paneme gruppide vahele AND ja checkboxide vahele OR
		foreach($ch_q as $chgrp => $ch_ar)
		{
			$chqs = join(" OR ", $ch_ar);
			if ($chqs !="")
			{
				if ($query != "")
				{
					$query.=" AND ";
				}
				$query.=" ($chqs)";
			}
		}

		if ($query != "")
		{
			$query.=" AND ";
		}
		$query.=" objects.status != 0 ";
		return $query;
	}

	////
	// !returns an array of entry_id => entry_name pairs for form 
	// $id - if specified, otherwise the loaded form
	// $parent - if specified, only entries under these folders are returned
	// $all_data - if specified, all data for entry is returned
	// $addempty - if true, empty element is prepended
	// $user - if set, only that user's entries are returned
	// $max_lines - if set, only that many lines are returned
	// $chain_id - if set, only entried with that chain_id are returned
	function get_entries($args = array())
	{
		$this->save_handle();
		extract($args);
		$ret = array();
		if ($addempty)
		{
			$ret[""] = "";
		}
		$fid = ($id) ? $id : $this->id;
		$form =& $this->cache_get_form_instance($fid);

		// filter by parent if specified
		$pstr = ($parent) ? " AND objects.parent IN (" . join(",",map("'%s'",$parent)) . ")" : "";

		if ($user != "")
		{
			$pstr.=" AND objects.createdby = '$user' ";
		}

		if ($max_lines != "")
		{
			$lim = " LIMIT $max_lines ";
		}

		// if the form writes to tables, get entries from there
		if ($form->arr["save_table"] == 1)
		{
			// here we ought to find the elements that are used to name the entries and select their values from 
			// the tables that they are in. 

			// ok, we crap out and to the easy version only, this will be a 
			// FIXME : implement this
		}
		else
		{
			$table = sprintf("form_%d_entries",$fid);
			if ($chain_id)
			{
				$ch = " AND ".$table.".chain_id = '".$chain_id."' ";
			}
			$q = "SELECT objects.oid as oid,$table.id as entry_id,objects.name as name,objects.parent as parent, $table.* FROM $table LEFT JOIN objects ON ($table.id = objects.oid) WHERE objects.status != 0 $pstr $ch ORDER BY objects.oid $lim";
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				if ($all_data)
				{
					$ret[] = $row;
				}
				else
				{
					$ret[$row["oid"]] = $row["name"];
				}
			}
		}
		$this->restore_handle();
		return $ret;
	}

	////
	// !finds the path from form $f_from to $f_to, via relations (chain or element)
	function get_join_path($f_from, $f_to)
	{
		// we do this in a pretty slow way - 
		// search through the form relations tree ($this->form_rel_tree)
		// it is assumed, that the root element for the tree id $f_from
		// and the tree contains all the relations for the forms in it. 
		$this->_clear_stack("join_path");
		$this->rgjp_beenthere = array();
//		echo "form_rel_tree = <pre>", var_dump($this->form_rel_tree),"</pre> <br>";
//		echo "entering gjp $f_from, $f_to <br>";
//		flush();
		if ($this->req_get_join_path($f_from, $f_to))
		{
			return $this->_clear_stack("join_path");
		}
		$this->raise_error(ERR_FG_NOFORMRELS, "Ei suuda leida seost formide $f_from ja $f_to  vahel!", true);
	}

	function req_get_join_path($f_root, $f_to)
	{
//		echo "req_get_join_path($f_root, $f_to) <br>";
//		flush();
		if ($this->rgjp_beenthere[$f_root])
		{
			return;
		}
		$this->rgjp_beenthere[$f_root] = $f_root;
		$this->_push($f_root,"join_path");
//		echo "push $f_root <br>";
		if (is_array($this->form_rel_tree[$f_root]))
		{
			foreach($this->form_rel_tree[$f_root] as $r_fid => $r_data)
			{
				if ($r_fid == $f_to)	// we found the end, get out of here
				{
					$this->_push($f_to,"join_path");
//					echo "push $f_to <br>";
//					echo "found end! return true <br>";
//		flush();
					return true;
				}

				if ($this->req_get_join_path($r_fid, $f_to) == true)
				{
//					echo "req found end , return true <br>";
//		flush();
					return true;
				}
			}
		}
		$this->_pop("join_path");
//		echo "pop $f_root <br>";
//		echo " not found, return false; <Br>";
//		flush();
		return false;
	}

	////
	// !this creates the form relations tree, starting from $f_root and puts it in $this->form_rel_tree
	// if $chain is specified, $f_root is assumed to be a member of chain $chain and others are ignored
	// $this->form_rel_tree is an array - index is the form id and value is an array of relations 
	// from the key form index is the related form id and value is an array of the relation data
	function build_form_relation_tree($f_root, $chain = 0)
	{
		$this->_fr_forms_used = array();
		$this->form_rel_tree = array();
		$this->req_build_form_relation_tree($f_root);
//		echo "built form relations tree, starting from $f_root: <br><pre>", var_dump($this->form_rel_tree),"</pre> <Br>";
	}

	function req_build_form_relation_tree($f_root)
	{
		$this->save_handle();

		$this->_fr_forms_used[$f_root] = true;

		// check for any relation elements in form
		$form =& $this->cache_get_form_instance($f_root);
		$rels = $form->get_element_by_type("listbox","relation",true);
		foreach($rels as $el)
		{
			$this->form_rel_tree[$f_root][$el->get_related_form()] = array(
				"form_from" => $f_root, 
				"el_from" => $el->get_id(), 
				"form_to" => $el->get_related_form(), 
				"el_to" => $el->get_related_element()
			);

			// now recurse to the to form if it is not already used
			if (!$this->_fr_forms_used[$el->get_related_form()])
			{
				$this->req_build_form_relation_tree($el->get_related_form());
			}
		}

		// check if the form is a member of any chain
		$this->db_query("SELECT * FROM form2chain WHERE form_id = '$f_root'");
		while ($row = $this->db_next())
		{
			// if it is, then load all forms for the chain
			$chain_forms = $this->get_forms_for_chain($row["chain_id"]);
			foreach($chain_forms as $chfid)
			{
				if ($chfid == $f_root)
				{
					continue;
				}

				// and for each form, mark the relation 
				$this->form_rel_tree[$f_root][$chfid] = array("form_from" => $f_root, "el_from" => "chain_id", "form_to" => $chfid, "el_to" => "chain_id");

				// and recurse if the form is not used already
				if (!$this->_fr_forms_used[$chfid])
				{
					$this->req_build_form_relation_tree($chfid);
				}
			}
		}

		$this->restore_handle();
	}

	function get_forms_used_in_where()
	{
		$ret = array();

		$els = $this->get_all_els();

		reset($els);
		// loop through all the elements of this form 
		while( list(,$el) = each($els))
		{
			if ($el->arr["linked_form"] && $el->arr["linked_element"])	
			{
				if (trim($el->get_value()) != "")	
				{
					$ret[$el->arr["linked_form"]] = $el->arr["linked_form"];
				}
			}
		}
		return $ret;
	}

	function build_join_rels_from_path($path)
	{
		if (!is_array($path))
		{
			return;
		}
		reset($path);
		while(list(,$fid) = each($path))
		{
			// get next from path
			if (!list(,$n_fid) = each($path))
			{
				// we are at the last form - so exit
				return;
			}

			// now move pointer back one, so we can build the nex rel next time in the loop
			prev($path);

			$f_inst =& $this->cache_get_form_instance($fid);
			$t_inst =& $this->cache_get_form_instance($n_fid);
		
			$f_el = $f_inst->get_element_by_id($this->form_rel_tree[$fid][$n_fid]["el_from"]);
			$t_el = $t_inst->get_element_by_id($this->form_rel_tree[$fid][$n_fid]["el_to"]);

			if ($this->form_rel_tree[$fid][$n_fid]["el_from"] == "chain_id")
			{
				$from_tbl = "form_".$fid."_entries";
				$from_el = "chain_id";
			}
			else
			{
				$from_tbl = $f_el->get_save_table();
				$from_el = $f_el->get_save_col();
			}

			if ($this->form_rel_tree[$fid][$n_fid]["el_to"] == "chain_id")
			{
				$to_tbl = "form_".$n_fid."_entries";
				$to_el = "chain_id";
			}
			else
			{
				$to_tbl = $t_el->get_save_table();
				$to_el = $t_el->get_save_col();
			}

//			echo "from table = ", $f_el->get_save_table()," from el = ", $f_el->get_save_col()," to table = ", $t_el->get_save_table()," to col = ",$t_el->get_save_col()," <br>";
			// and mark down the join
			$this->_joins[] = array(
				"from_tbl" => $from_tbl, 
				"from_el" =>  $from_el,
				"to_tbl" => $to_tbl,
				"to_el" => $to_el
			);

			if ($f_el)
			{
				$this->table2form_map[$f_el->get_save_table()] = $fid;
			}
			else
			{
				$this->table2form_map["form_".$fid."_entries"] = $fid;
			}

			if ($t_el)
			{
				$this->table2form_map[$t_el->get_save_table()] = $n_fid;
			}
			else
			{
				$this->table2form_map["form_".$n_fid."_entries"] = $n_fid;
			}
		}
	}

	////
	// !returns all distinct values for element $element of form $form
	function get_distinct_entries_for_element($arr)
	{
		extract($arr);
		$this->save_handle();

		$ret = array();

		$f =& $this->cache_get_form_instance($form);
		$el = $f->get_element_by_id($element);

		$sql = "SELECT DISTINCT(".$el->get_save_table().".".$el->get_save_col().") AS val FROM ".$el->get_save_table();
		$this->db_query($sql);
		while ($row = $this->db_next())
		{
			$ret[] = $row["val"];
		}
		$this->restore_handle();
		return $ret;
	}
}
?>
