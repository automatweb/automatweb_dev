<?php
class tegevusala extends aw_template
{

	function tegevusala()
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
		$this->read_template("tegevusala_change.tpl");

		if ($id)
		{
			$q="select * from kliendibaas_tegevusala where oid=$id";
			$res=$this->db_query($q);
			$res=$this->db_next();
			@extract($res, EXTR_PREFIX_ALL, "f");
			$ob = $this->get_object($id);
			if ($return_url != "")
			{
				$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa tegevusala");
			}
			else
			{
				$this->mk_path($ob["parent"], "Muuda tegevusala");
			}

			$toolbar->add_button(array(
				"name" => "lisa_tegevusala",
				"tooltip" => "lisa uus tegevusala",
				"url" => $this->mk_my_orb("new", array("return_url" => urlencode($return_url),"parent" => $ob["parent"],),"tegevusala"), //parent tuleb panna selleks mida confist määran!!
				"imgover" => "new_over.gif",
				"img" => "new.gif",
			));

		}		
		else
		{
			$this->mk_path($ob["parent"], "Muuda tegevusala");
		}


		$this->vars(array(
			"name"=>$ob["name"],
			"kood"=>$f_kood,
			"tegevusala_ik"=>$f_tegevusala_ik,
			"tegevusala_et"=>$f_tegevusala_et,
			"kirjeldus"=>$f_kirjeldus,
			"sourcefile"=>$f_sourcefile,
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
		
		if ($id)
		{

			$this->upd_object(array(
				"oid" => $id,
				"name" => $tegevusala_et,
				"comment" => $comment,
				"metadata" => array(
				)
			));
			$q='update kliendibaas_tegevusala set  kood="'.$kood.'", tegevusala_ik="'.$tegevusala_ik.'", tegevusala_et="'.$tegevusala_et.'", kirjeldus="'.$kirjeldus.'" where oid='.$id;
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $tegevusala_et,
				"class_id" => CL_TEGEVUSALA,
				"comment" => $comment,
				"metadata" => array(
				)
			));
			$q="insert into kliendibaas_tegevusala (kood, oid, tegevusala_ik, tegevusala_et, kirjeldus)
			values ('$kood','$id','$tegevusala_ik', '$tegevusala_et','$kirjeldus')";
		}

$this->db_query($q);

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));
	}


}
?>