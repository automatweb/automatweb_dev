<?php
class ettevotlusvorm extends aw_template
{

	function ettevotlusvorm()
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
				$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda ettevotlusvorm");
			}
			$toolbar->add_button(array(
				"name" => "lisa",
				"tooltip" => "lisa ettevotlusvorm",
				"url" => $this->mk_my_orb("change", array("return_url" => urlencode($return_url),"parent" => $ob["parent"],)),
				"imgover" => "new_over.gif",
				"img" => "new.gif",
			));

			$q="select * from kliendibaas_ettevotlusvorm where oid=$id";
			$res=$this->db_query($q);
			$res=$this->db_next();
			@extract($res, EXTR_PREFIX_ALL, "f");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda ettevotlusvorm");
		}

		$this->read_template("ettevotlusvorm_change.tpl");



		$this->vars(array(
			"name"=>$f_name,
			"comment"=>$ob["comment"],
			"name_en"=>$f_name_en,
			"name_native"=>$f_name_native,
			"languages"=>$f_languages,
			"location"=>$f_location,
			"lyhend"=>$f_lyhend,
			"toolbar"=>$toolbar->get_toolbar(),
			"list"=>$this->ettevotlusvorm_list(),
			"reforb" => $this->mk_reforb("submit", array(
				"id" => $id, 
				"return_url" => urlencode($return_url),
				"parent" => $parent, 
			)),
		));
		return $this->parse();
	}


	function add_ettevotlusvorm($ettevotlusvorm,$id)
	{
		$exclude=array("1","oid","id");
		foreach ($ettevotlusvorm as $key=>$val)
		{
			if(!array_search($key,$exclude))
			{
				$f[]=$key;
				$v[]="'".$val."'";
			}
		}
		$ff=implode(",",$f);
		$vv=implode(",",$v);
		$q="insert into kliendibaas_ettevotlusvorm($ff,oid)values($vv,'$id')";
		$this->db_query($q);
	}

	function change_ettevotlusvorm($ettevotlusvorm,$id)
	{
		foreach($ettevotlusvorm as $key=>$val)
		{
			$f[]=" $key=\"$val\"";
		}
		$vv=implode(" , ",$f);
		$q='update kliendibaas_ettevotlusvorm set '.$vv.' where oid='.$id;
		$this->db_query($q);
	}

	function delete($arr)
	{
		extract($arr);
		$q='delete from kliendibaas_ettevotlusvorm where oid='.$id;
		$this->db_query($q);
	}


	////
	// !this gets called when the user submits the object's form
	// parameters:
	// id - if set, object will be changed, if not set, new object will be created
	function submit($arr)
	{
		extract($arr);//ettevotlusvorm
//		print_r($ettevotlusvorm);
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $ettevotlusvorm["name"],
				"comment" => $comment,
				"metadata" => array(
				)
			));
			$this->change_ettevotlusvorm($ettevotlusvorm,$id);
		}
		else
		{
			$id = $this->new_object(array(
				"name" => $ettevotlusvorm["name"],
				"parent" => $parent,
				"class_id" => CL_ettevotlusvorm,
				"comment" => $comment,
				"metadata" => array(

				)
			));
			$this->add_ettevotlusvorm($ettevotlusvorm,$id);
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
//select * from kliendibaas_firma where oid=oid
		$this->vars(array(
			"abix" => $tase,
		));
		return "tore ettevotlusvorm";
//		return $this->parse();
	}




	function ettevotlusvorm_list()
	{
	
	$this->db_query("select * from kliendibaas_ettevotlusvorm");

			load_vcl("table");
			$t = new aw_table(array(
				"prefix" => "kliendibaas", 
			));
//echo $this->cfg["site_basedir"];
//			$t->parse_xml_def($this->cfg["site_basedir"]."/xml/linklist/show_stats.xml"); 
			$t->parse_xml_def("/www/automatweb_dev/xml/kliendibaas/ettevotlusvorm_list.xml"); 
			
			while ($row= $this->db_next()) 
			{ 
				$t->define_data($row); 
			} 
			$t->sort_by(); 
			return $t->draw();

	}


}
?>