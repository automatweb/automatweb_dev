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
// the reason why we should look lightly at calling functions defined on classes derived from this 
//
// - terryf

class form_db_base extends aw_template
{
	function form_db_base()
	{
		$this->form_base();
	}

	////
	// !adds the necessary columns for the element $el_id in the table $table
	// this gets called when an element is added to a form and it checks if the table already contains this element
	// and if it doesn't then it adds the element
	function add_element($table,$el_id)
	{
	}

	////
	// !removes the columns for element $el_id from form table $table
	function remove_element($table, $el_id)
	{
	}

	////
	// !returns the id of the form with what the entry ($entry_id) was created
	function get_form_for_entry($entry_id)
	{
		$this->save_handle();
		$ret = $this->db_fetch_field("SELECT form_id FROM form_entries WHERE id = $entry_id","form_id");
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
			// and we sould have an option for search forms so that their entries could be saved in the session
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
	function create_entry_data($entry_id,$entry_data,$chain_entry_id = 0)
	{
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
			$this->db_query("INSERT INTO form_entries(id,form_id) VALUES($entry_id, $this->id)");

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

				// häkk
				// yeah, FIXME: make this go away - it's ugly as fuck and start using file class for file uploads
				// I'll leave it like this just now, cause I don't wanna break lots of forms that have file uploading in them
				if (is_object($el))
				{
					$ev = $el->get_value();

					$ids.=",el_$k,ev_$k";
					// see on pildi uploadimise elementide jaoks
					if (is_array($v))
					{
						$v = serialize($v);
					}
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
			$elvalues[] = $el->get_value();
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
			$dat[$el->get_save_col()] = $el->get_value();
		}

		// and now just turn it into a query - hey, this is easy
		$idx_col = $this->arr["save_tables"][$tbl];
		$q = "UPDATE $tbl SET ".join(",",$this->map2("%s = '%s'",$dat,0,true))." WHERE $idx_col = '$entry_id'";
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
	// !reads the data for the entry from it's designated place, maps it to elements and bundles it in an array or
	// $el_id => $el_value pairs which it returns
	function read_entry_data($entry_id,$silent_errors = false)
	{
		$this->save_handle();

		// we gather the el_id => el_value pairs here
		$ret = array();
		if ($this->arr["save_table"] == 1)
		{
			// start from the first and crawl through the relations and load all the entries
			$this->req_read_entry_data($this->arr["save_table_start_from"],&$ret,$entry_id,$silent_errors);
		}
		else
		{
			$row = $this->do_form_db_load($entry_id,$silent_errors,&$ret,$this->id);
			$this->entry_created = $row["created"];
			$this->chain_entry_id = (int)$row["chain_id"];
		}

		// now go through the form's relation elements
		// and load the related entries through them
		// we don't load related entries for search forms though, cause that doesn't have a use right now.
		$this->temp_ids = array();
		if ($this->type != FTYPE_SEARCH)
		{
			$this->req_load_relations($this->id,&$ret,$row);
		}

		$this->restore_handle();
		return $ret;
	}

	////
	// !recurses through the table relations and reads all the entries from the tables
	function req_read_entry_data($tbl,&$ret,$entry_id,$silent_errors)
	{
		$this->save_handle();

		$tbl_col = $this->arr["save_tables"][$tbl];
		$q = "SELECT ".$tbl.".* FROM $tbl WHERE ".$tbl.".".$tblcol." = '$entry_id' ";
		$this->db_query($q);
		$row = $this->db_next();

		// now we must map the table columns to elements in the form. 
		$els = $this->get_elements_for_table($tbl);
		foreach($els as $el)
		{
			$ret[$el->get_id()] = $row[$el->get_save_col()];
		}

		// and now recurse to the other tables
		$_tmp = $this->arr["save_tables_rels"][$tbl];
		if (is_array($_tmp))
		{
			// go through the tables related to this one one by one and have them read their data
			foreach($_tmp as $r_tbl => $r_tbl)
			{
				$this->req_update_entry_data($r_tbl,&$ret,$entry_id,$silent_errors);
			}
		}

		$this->restore_handle();
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
	// !this tries to find all the related entries of the current entry and reads them in and then proceeds to recursively
	// apply itself to all those loaded entries so we'll be loading in entire databases in no time at all.
	// $row contains the data loaded from the database for the current entry - so we could get to the value of relation elements quickly
	function req_load_relations($id,&$res,$row)
	{
		// we keep a list of all the forms we have already checked so we won't get stuck in a loop if somebody has created
		// a cyclic dependency on relation elements
		$this->temp_ids[$id] = $id;
		$this->save_handle();

		// all form relation elements are registered in form_relations table 
		// between what forms and what elements the relation is
		//
		// so that means that if we have a simple entry form with a textbox in it
		// and then we have another form that contains a relation listbox that is connected to the previous form's textbox
		//
		// basically this meand that the relation creates a one to many relation between the forms
		// the one end is the first form and the many end is the form with the relation element
		//
		// so, this means that we can unambiguously find the first form's entry, given the second form's entry
		// the first form (the one with the textbox) is always written in the column form_from	(one)
		// and the second form (the one with the listbox) is written in column form_to					(many)
		//
		// and since we realized that we can only unambiguously load the relation if we start with the
		// many (listbox) entry, that means that we need only try and load relations if this form contains
		// some relation elements (listboxes)
		//
		// so we do a query asking for all the relation elements in this form
		$this->db_query("SELECT * FROM form_relations WHERE form_to = $id ");
		while ($r_row = $this->db_next())
		{
			// now we take each relation element in turn and try and load the corresponging entry from the related form
			$related_form = $r_row["form_from"];
			$related_element = $r_row["el_from"];

			$relation_element = $r_row["el_to"];

			// right - now we need to get the value of the relation element - and since we "just accidentally" passed
			// the data read from the database when loading the current entry - w get it from there
			$relation_element_value = $row["ev_".$relation_element];
			// so now we can perform a query that reads in the data for the related entry
			$this->save_handle();

			// uh. can't figure out how to integrate other table based forms here - we would need to load the other form
			// so we could know which columns are for which elements, so we just do this:
			// FIXME: figure out a way that you could use existing-table-saving forms and relation elements

			// so we just support relation elements in form_table based forms
			// since we don't know the entry's id, we set it as zero and specify our own where clause with the relation
			$n_row = $this->do_form_db_load(0,true,&$res,$related_form,"ev_".$related_element." = '".$relation_element_value."'");

			// now we have loaded another form's entry into this form's element array - oh well - let's just hope they
			// will use different elements, not the same ones in several forms - things will get reeeal ugly if they do. 
			// can't see a way around it though either...
			//
			// but now we have to check if the just-loaded entry has any relations to some other forms - so we recurse
			// but to avoid endless looping w check if we have touched the just loaded entry's form yet and if we have
			// we don't recurse, cause that means that we got a cyclic dependency
			if (!in_array($relation_form,$this->temp_ids))
			{
				$this->req_load_relations($related_form,&$res,$n_row);
			}

			// and that should be it. mkay. weird.
			$this->restore_handle();
		}
		$this->restore_handle();
	}

	////
	// !does the db queries and result mapping that is necessary to read a form entry from the db
	// it returns the data for the form entry that was read from the database
	// parameters:
	// $entry_id - the entry to load
	// $silent_errors - if true, errors are logged but not shown to the user
	// &$ret - a reference to the array where el_id => value pairs are gathered from the database
	// $form_id - the form for which we are loading the entry
	// $where - an optional where clause - this will be used in relation element loading
	function do_form_db_load($entry_id,$silent_errors,&$ret,$form_id,$where = "")
	{
		$this->save_handle();
		
		if ($where == "")
		{
			$where = "id = ".$entry_id;
		}

		// load the entry from the form table 
		$ft = "form_".$form_id."_entries";
		$q = "SELECT ".$ft.".*,objects.created as created FROM $ft LEFT JOIN objects ON objects.oid = ".$ft.".id WHERE ".$where;
		$this->db_query($q);

		if (!($row = $this->db_next()))
		{
			if ($silent_errors)
			{
				$this->raise_error(sprintf("No such entry %d for form %d",$entry_id,$form_id),false,true);
			}
			else
			{
				$this->raise_error(sprintf("No such entry %d for form %d",$entry_id,$form_id),true);
			}
		};

		if (!$entry_id)
		{
			$entry_id = $row["id"];
		}

		// map the loaded data to form elements in the array
		$this->read_elements_from_q_result($row,&$ret);

		// and if the entry is a part of a chain entry, load all the other form entries that are a part of the chain entry as well 
		if ($row["chain_id"])
		{
			$char = $this->get_chain_entry($row["chain_id"]);
			foreach($char as $cfid => $ceid)
			{
				if ($ceid != $entry_id)
				{
					$this->db_query("SELECT * FROM form_".$cfid."_entries WHERE id = $ceid");
					$this->read_elements_from_q_result($this->db_next(),&$ret);
				}
			}
		}
		$this->restore_handle();
		return $row;
	}
}
?>