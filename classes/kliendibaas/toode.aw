<?php
class toode extends aw_template
{

	function toode()
	{
		// change this to the folder under the templates folder, where this classes templates will be 
		$this->init("kliendibaas");
	}

	////
	// !this gets called when the user clicks on change object 
	// parameters:
	// id - the id of the object to change
	// return_url - optional, if set, "back" link should point to it
	function change($arr)
	{

		extract($arr);

			$toolbar = get_instance("toolbar",array("imgbase" => "/automatweb/images/blue/awicons"));
				$toolbar->add_button(array(
				"name" => "save",
				"tooltip" => "salvesta",
				"url" => "javascript:document.add.submit()",
				"imgover" => "save_over.gif",
				"img" => "save.gif",
			));
		$this->read_template("toode_change.tpl");

		if ($id)
		{
			$q="select * from kliendibaas_toode where oid=$id";
			$res=$this->db_query($q);
			$res=$this->db_next();
			@extract($res, EXTR_PREFIX_ALL, "f");
			$ob = $this->get_object($id);
			if ($return_url != "")
			{
				$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa toode");
			}
			else
			{
				$this->mk_path($ob["parent"], "Muuda toode");
			}

			$toolbar->add_button(array(
				"name" => "lisa_toode",
				"tooltip" => "lisa uus toode",
				"url" => $this->mk_my_orb("new", array("return_url" => urlencode($return_url),"parent" => $ob["parent"],),"toode"), //parent tuleb panna selleks mida confist määran!!
				"imgover" => "new_over.gif",
				"img" => "new.gif",
			));

		}		
		else
		{
			$this->mk_path($ob["parent"], "Muuda toode");
		}


		$this->vars(array(
			"name"=>$ob["name"],
			"kood"=>$f_kood,
			"toode"=>$f_toode,
			"toode_en"=>$f_toode_en,
			"kirjeldus"=>$f_kirjeldus,
			"toolbar"=>$toolbar->get_toolbar(),
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "parent"=>$parent, "return_url" => urlencode($return_url))),
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
		@extract($toode);
		if ($id)
		{

			$this->upd_object(array(
				"oid" => $id,
				"name" => $toode,
				"comment" => $comment,
				"metadata" => array(
				)
			));

			$q='update kliendibaas_toode set  kood="'.$kood.'", toode="'.$toode.
			'", toode_en="'.$toode_en.'", kirjeldus="'.$kirjeldus.'" where oid='.$id;
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $toode,
				"class_id" => CL_TOODE,
				"comment" => $comment,
				"metadata" => array(
				)
			));
			$q="insert into kliendibaas_toode (kood, oid, toode_en, toode, kirjeldus)
			values ('$kood','$id','$toode_en', '$toode','$kirjeldus')";
		}

$this->db_query($q);

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));
	}



	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row['parent'] = $parent;
		unset($row['brother_of']);
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}




}
?>