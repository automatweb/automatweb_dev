<?php
class maakond extends aw_template
{

	function maakond()
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
		if ($id)
		{
			$ob = $this->get_object($id);
			if ($return_url != "")
			{
				$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda maakond");
			}
			$toolbar->add_button(array(
				"name" => "lisa",
				"tooltip" => "lisa maakond",
				"url" => $this->mk_my_orb("change", array("return_url" => urlencode($return_url),"parent" => $ob["parent"],)),
				"imgover" => "new_over.gif",
				"img" => "new.gif",
			));

			$q="select * from kliendibaas_maakond where oid=$id";
			$res=$this->db_query($q);
			$res=$this->db_next();
			@extract($res, EXTR_PREFIX_ALL, "f");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda maakond");
		}

		$this->read_template("maakond_change.tpl");



		$this->vars(array(
			"name"=>$f_name,
			"comment"=>$f_comment,
			"location"=>$f_location,
			"toolbar"=>$toolbar->get_toolbar(),
//			"list"=>$this->maakond_list(),
			"reforb" => $this->mk_reforb("submit", array(
				"id" => $id, 
				"return_url" => urlencode($return_url),
				"parent" => $parent, 
			)),
		));
		return $this->parse();
	}


	function add_maakond($maakond,$id)
	{
		$exclude=array("1","oid","id");
		foreach ($maakond as $key=>$val)
		{
			if(!array_search($key,$exclude))
			{
				$f[]=$key;
				$v[]="'".$val."'";
			}
		}
		$ff=implode(",",$f);
		$vv=implode(",",$v);
		$q="insert into kliendibaas_maakond($ff,oid)values($vv,'$id')";
		$this->db_query($q);
	}

	function change_maakond($maakond,$id)
	{
		foreach($maakond as $key=>$val)
		{
			$f[]=" $key=\"$val\"";
		}
		$vv=implode(" , ",$f);
		$q='update kliendibaas_maakond set '.$vv.' where oid='.$id;
		$this->db_query($q);
	}

	////
	// !this gets called when the user submits the object's form
	// parameters:
	// id - if set, object will be changed, if not set, new object will be created
	function submit($arr)
	{
		extract($arr);//maakond
//		print_r($maakond);
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $maakond["name"],
				"comment" => $maakond["comment"],
				"metadata" => array(
				)
			));
			$this->change_maakond($maakond,$id);
		}
		else
		{
			$id = $this->new_object(array(
				"name" => $maakond["name"],
				"parent" => $parent,
				"class_id" => CL_MAAKOND,
				"comment" => $comment,
				"metadata" => array(

				)
			));
			$this->add_maakond($maakond,$id);
		}

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));
	}
}
?>