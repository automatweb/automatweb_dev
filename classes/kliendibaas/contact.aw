<?php
class contact extends aw_template
{

	function contact()
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

		$ob = $this->get_object($id);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda kontakt");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda kontakt");
		}
		$this->read_template("contact_change.tpl");

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
			$toolbar->add_button(array(
				"name" => "lisa",
				"tooltip" => "lisa isik",
				"url" => $this->mk_my_orb("change", array("return_url" => urlencode($return_url),"parent" => $ob["parent"],)),
				"imgover" => "new_over.gif",
				"img" => "new.gif",
			));

//			$q="select * from kliendibaas_contact where oid='$id'";

			$q="select  t.* ,
			t4.name as s_riik,		
			t3.name as s_linn,
			t5.name as s_maakond
			from kliendibaas_contact as t 
			left join kliendibaas_riik as t4 on t4.oid=t.riik
			left join kliendibaas_linn as t3 on t3.oid=t.linn
			left join kliendibaas_maakond as t5 on t5.oid=t.maakond
			where t.oid='$id'"; //where t*.status=2

			
			$res=$this->db_query($q);
			$res=$this->db_next();
			extract($res, EXTR_PREFIX_ALL, "f");
		}



		$this->vars(array(
			"f_tyyp"=>$this->picker(0,$f_tyyp),
			"f_name"=>$f_name,
			"f_riik"=>$f_riik,
			"f_linn"=>$f_linn,
			"f_maakond"=>$f_maakond,
			"f_postiindeks"=>$f_postiindeks,
			"f_telefon"=>$f_telefon,
			"f_mobiil"=>$f_mobiil,
			"f_faks"=>$f_faks,
			"f_aadress"=>$f_aadress,
			"f_e_mail"=>$f_e_mail,
			"f_kodulehekylg"=>$f_kodulehekylg,
			"f_piipar"=>$f_piipar,
	
			"s_riik"=>$f_s_riik,
			"s_linn"=>$f_s_linn,
			"s_maakond"=>$f_s_maakond,
			"s_firmajuht"=>$f_s_firmajuht,


			"f_maakond_pop"=>$this->mk_my_orb("pop_select", array("id" => $id,"tyyp" => "maakond", "return_url" => urlencode($return_url))),
			"f_linn_pop"=>$this->mk_my_orb("pop_select", array("id" => $id,"tyyp" => "linn", "return_url" => urlencode($return_url))),
			"f_riik_pop"=>$this->mk_my_orb("pop_select", array("id" => $id,"tyyp" => "riik", "return_url" => urlencode($return_url))),
			"comment"=>$ob["comment"],
			"toolbar"=>$toolbar->get_toolbar(),
			"reforb" => $this->mk_reforb("submit", array(
				"id" => $id, 
				"alias_to" => $alias_to, 
				"return_url" => urlencode($return_url),
				"parent" => $parent, 
			)),
		));
		return $this->parse();
	}


function show($arr)
{

		extract($arr);
//		$ob = $this->get_object($id);
		$this->read_template("contact_show.tpl");

			$q="select  t.* ,
			t4.name as s_riik,		
			t3.name as s_linn,
			t5.name as s_maakond
			from kliendibaas_contact as t 
			left join kliendibaas_riik as t4 on t4.oid=t.riik
			left join kliendibaas_linn as t3 on t3.oid=t.linn
			left join kliendibaas_maakond as t5 on t5.oid=t.maakond
			where t.oid='$id'"; //where t*.status=2
			
			$res=$this->db_query($q);
			$res=$this->db_next();
			extract($res, EXTR_PREFIX_ALL, "f");

		$this->vars(array(
			"f_name"=>$f_name,
			"f_riik"=>$f_riik,
			"f_linn"=>$f_linn,
			"f_maakond"=>$f_maakond,
			"f_postiindeks"=>$f_postiindeks,
			"f_telefon"=>$f_telefon,
			"f_mobiil"=>$f_mobiil,
			"f_faks"=>$f_faks,
			"f_aadress"=>$f_aadress,
			"f_e_mail"=>$f_e_mail,
			"f_kodulehekylg"=>$f_kodulehekylg,
			"f_piipar"=>$f_piipar,

			"s_riik"=>$f_s_riik,
			"s_linn"=>$f_s_linn,
			"s_maakond"=>$f_s_maakond,
			"s_firmajuht"=>$f_s_firmajuht,

//			"comment"=>$ob["comment"],
		));
		return $this->parse();

}






	////	
	// ! create new contact entry
	// contact		at least one element required here		
	//	name
	//	riik
	//	linn
	//	...		
	// name
	// parent
	// comment
	// ...
	function new_contact($arr)
	{
		extract($arr);

			foreach ($contact as $key=>$val)
			{
				{
					$this->quote($val);
					$f[]=$key;
					$v[]="'".$val."'";
				}
			}

			$id = $this->new_object(array(
				"name" => $name,
				"parent" => $parent,
				"class_id" => CL_CONTACT,
				"comment" => $comment,
				"metadata" => array(
//					"contact"=>$contact,
				)
			));
			$f[]='oid';
			$v[]="'".$id."'";

			$q='insert into kliendibaas_contact('.implode(",",$f).')values('.implode(",",$v).')';
		
		$this->db_query($q);
		return $id;
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

			foreach ($contact as $key=>$val)
			{
				{
					$f[]=" $key=\"$val\"";
				}
				
			}
			$vv=implode(" , ",$f);
			$q='update kliendibaas_contact set '.$vv.' where oid='.$id;

			$this->upd_object(array(
				"oid" => $id,
				"name" => $contact["name"],
				"comment" => $comment,
				"metadata" => array(
					"contact"=>$contact,
				)
			));
			$this->db_query($q);
		}
		else
		{
			$id=$this->new_contact($arr);

		}
		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));
	}

	
	
	
	function parse_alias($args)
	{
		extract($args);
			
		return $this->show(array("id" => $alias["target"]));
	}


	function pop_select($arr)
	{
		extract($arr);
		$this->read_template("pop_select.tpl");
		if ($id)
		{
			$selected=$this->db_fetch_field("select $tyyp from kliendibaas_contact where oid=$id",$tyyp);
			$ob = $this->get_object($id);		
		}

		switch($tyyp)
		{
			case "linn":
				{
					$q="select t1.oid,t1.name from kliendibaas_linn as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$this->db_query($q);
					while($row = $this->db_next())
					{
						$data[$row["oid"]] = $row["name"];
					};
					$add=$this->mk_my_orb("new",array("parent"=>$ob["parent"]),"kliendibaas/linn");
				}
			break;
			case "riik":
				{
					$q="select t1.oid,t1.name from kliendibaas_riik as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$this->db_query($q);
					while($row = $this->db_next()) 
					{
						$data[$row["oid"]] = $row["name"];
					};
					$add=$this->mk_my_orb("new",array("parent"=>$ob["parent"]),"kliendibaas/riik");
				}
			break;
			case "maakond":
				{
					$q="select t1.oid,t1.name from kliendibaas_maakond as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$this->db_query($q);
					while($row = $this->db_next()) 
					{
						$data[$row["oid"]] = $row["name"];
					};
					$add=$this->mk_my_orb("new",array("parent"=>$ob["parent"]),"kliendibaas/maakond");
				}
			break;

			default: $data=array(1=>"nosource");

		}


		asort($data);
		$options=$this->picker($selected,array(0=>" - ")+(array)$data);

		$this->vars=array(
			"add"=>$add,
			"tyyp"=>$tyyp,
			"mida"=>$tyyp,
			"options"=>$options,
			"multiple"=>"multiple",
		);

		echo $this->parse();

		die();//et mingit jama ei väljastaks
	}
}
?>