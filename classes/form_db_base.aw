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
// - terryf

class form_db_base
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
	// !creates the query for loading the form entry
	// uses the correct table defined in the form
	function do_load_query($entry_id)
	{

	}

	////
	// !reads the data from the query created by the do_load_query function
	// maps the data to the right form as well - so that it gets loaded into the correct elements
	function read_data()
	{
	}

	function do_search_query()
	{
	}
}
?>