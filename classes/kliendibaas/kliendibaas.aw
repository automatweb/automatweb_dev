<?php
class kliendibaas extends aw_template
{

	function kliendibaas()
	{
		// change this to the folder under the templates folder, where this classes templates will be 
		$this->init("kliendibaas");
	}


	function add($arr)
	{
		extract($arr);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa .....");
		}
		else
		{
			$this->mk_path($parent,"Lisa linklist");
		}
		$this->read_template("kliendibaas_add.tpl");

		$toolbar = get_instance("toolbar",array("imgbase" => "/automatweb/images/blue/awicons"));
		$toolbar->add_button(array(
			"name" => "save",
			"tooltip" => "salvesta",
			"url" => "javascript:document.add.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif",
		));


		$this->vars(array(
			"name" => "uus kliendibaas",
			"toolbar"=>$toolbar->get_toolbar(),
			"reforb" => 	$this->mk_reforb("submit", 
				array(
					"parent" => $parent, 
					"alias_to" => $alias_to, 
					"return_url" => $return_url,
				)),
		));
		return $this->parse();
	}



	////
	// !this gets called when the user clicks on change object 
	// parameters:
	// id - the id of the object to change
	// return_url - optional, if set, "back" link should point to it
	function change($arr)
	{

		extract($arr);

		$ob = $this->get_object($id);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda kliendibaas");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda kliendibaas");
		}
		$this->read_template("kliendibaas_change.tpl");

		$toolbar = get_instance("toolbar",array("imgbase" => "/automatweb/images/blue/awicons"));
		$toolbar->add_button(array(
			"name" => "save",
			"tooltip" => "salvesta",
			"url" => "javascript:document.add.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif",
		));
		$toolbar->add_button(array(
			"name" => "lisa_firma",
			"tooltip" => "lisa firma",
			"url" => $this->mk_my_orb("new", array("return_url" => urlencode($return_url),"parent" => $ob["parent"],),"firma"), //parent tuleb panna selleks mida confist määran!!
			"imgover" => "new_over.gif",
			"img" => "new.gif",
		));
		$toolbar->add_button(array(
			"name" => "lisa_tegevusala",
			"tooltip" => "lisa tegevusala",
			"url" => $this->mk_my_orb("new", array("return_url" => urlencode($return_url),"parent" => $ob["parent"],),"tegevusala"), //parent tuleb panna selleks mida confist määran!!
			"imgover" => "new_over.gif",
			"img" => "new.gif",
		));

		$this->vars(array(
			"name"=>$ob["name"],
			"comment"=>$ob["comment"],
			"toolbar"=>$toolbar->get_toolbar(),
			"id"=>$firma["id"],
			"vorm"=>$form,

			"reforb" => $this->mk_reforb("submit", array("id" => $id, "return_url" => urlencode($return_url))),
		));
		return $this->parse();
	}


	////
	// !this gets called when the user submits the object's form
	// parameters:
	// id - if set, object will be changed, if not set, new object will be created
	function submit($arr)
	{
		extract($arr);
		
		if ($id)
		{

			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
				"metadata" => array(
				)
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_KLIENDIBAAS,
				"comment" => $comment,
				"metadata" => array(
				)
			));
		}

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));
	}

	////
	// !
	// 
	//
	function show($arr)
	{
		extract($arr); // cd = current directory

		$this->vars(array(

			"abix" => $tase,
		));

		return $this->parse();
	}



	////
	// !called, when adding a new object 
	// parameters:
	// parent - the folder under which to add the object
	// return_url - optional, if set, the "back" link should point to it
	// alias_to - optional, if set, after adding the object an alias to the object with oid alias_to should be created


}
?>