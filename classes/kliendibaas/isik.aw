<?php
class isik extends aw_template
{

	function isik()
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
		$this->read_template("isik_change.tpl");

		extract($arr);

		$ob = $this->get_object($id);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda isik");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda isik");
		}


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
			
			
			$q="select * from kliendibaas_isik where oid='$id'";
			$data=$this->db_fetch_row($q);

			get_instance("kliendibaas/contact");
//echo $data['personal_contact'];

			if (!$data['personal_contact'])
			{
				$data['personal_contact']=contact::new_contact(array(
					"parent"=>$ob['parent'],
					"comment"=>' kodune aadress',
					"name"=>$data['name'],
					"contact" => array(
						"name"=>$data['name'],
						),
				));
				$q='update kliendibaas_isik set personal_contact='.$data['personal_contact'].' where oid='.$id;
				$this->db_query($q);
			}
			$personal_contact_change=$this->mk_my_orb("change",array("id" => $data['personal_contact'],"parent"=>$ob["parent"],"return_url" => urlencode($return_url)),contact);
			$this->vars(array(
				"what"=>"personal_contact",
				"value"=>$data['personal_contact'],
				"desc"=>" kodu kontakt",
				"contact_change"=>$personal_contact_change,

			));
			$contact.=$this->parse("contact");

			if (!$data['work_contact'])
			{
				$data['work_contact']=contact::new_contact(array(
					"parent"=>$ob['parent'],
					"comment"=>' töö aadress',
					"name"=>$data['name'],
					"contact" => array(
						"name"=>$data['name'],
						),

				));
				$q='update kliendibaas_isik set work_contact='.$data['work_contact'].' where oid='.$id;
				$this->db_query($q);
			}
			$work_contact_change=$this->mk_my_orb("change",array("id" => $data['work_contact'],"parent"=>$ob["parent"],"return_url" => urlencode($return_url)),contact);
	
			$this->vars(array(
				"what"=>"work_contact",
				"contact_change"=>$work_contact_change,
				"desc"=>"töö kontakt",
				"value"=>$data['work_contact'],
				
			));
			$contact.=$this->parse("contact");
		}

$fields=array(
	"name"=>array("desc"=>"nimi","size"=>25,"maxlength"=>70),
	"firstname"=>array("desc"=>"eesinimi","size"=>15,"maxlength"=>50),
	"lastname"=>array("desc"=>"perekonnanimi","size"=>15,"maxlength"=>50),
	"personal_id"=>array("desc"=>"isikukood","size"=>10,"maxlength"=>11),
	"title"=>array("desc"=>"tiitel","size"=>5,"maxlength"=>10),
	"nickname"=>array("desc"=>"pseudonüüm","size"=>10,"maxlength"=>20),
	"birthday"=>array("desc"=>"sünnikuupäev","size"=>10,"maxlength"=>10),
	"social_status"=>array("desc"=>"sotsiaalne seis","size"=>10,"maxlength"=>20),
	"spouse"=>array("desc"=>"abikaasa","size"=>15,"maxlength"=>50),
	"children"=>array("desc"=>"lapsed","size"=>20,"maxlength"=>60),
//	"h_e_mail"=>array("desc"=>"e-post","size"=>15,"maxlength"=>50),
/*
mysql> create table kliendibaas_isik(
    -> oid int primary key unique,
    -> firstname varchar(50),
    -> lastname varchar(50),
    -> name varchar(100),
    -> gender char(10),
    -> personal_id bigint,
    -> title varchar(10),
    -> nickname varchar(20),
    -> messenger varchar(200),
    -> birthday varchar(20),
    -> social_status varchar(20),
    -> spouse varchar(50),
    -> children varchar(100),
    -> personal_contact int,
    -> work_contact int,
    -> digitalID text,
    -> notes text,
    -> pictureurl varchar(200),
    -> picture blob
    -> );*/
);


		///genereerime formi

		foreach ($fields as $key=>$val)
		{


		if (1==1)
		{
			$this->vars(array(
				"desc"=>$val["desc"],
				"name"=>"isik[$key]",
				"value"=>$data[$key],
				"size"=>$val["size"],
				"maxlength"=>$val["maxlength"],
			));
			$form.=$this->parse("textbox");
		}
		else
		{
/*		
			$this->vars(array(
				"name"=>$,
				"size"=>	$,
				"maxlength"=>$,
				""=>$,
				"name"=>$,
				$this->parse("inputtextarea");
			));
*/
		}
		
		
		
		}





		$this->vars(array(
			"name"=>$data['name'],
			"comment"=>$ob["comment"],
			"contact"=>$contact,
//			"work_contact_change"=>$work_contact_change,
			"personal_contact_change"=>$personal_contact_change,
			"form"=>$form,
			"abx"=>$abx,
			"toolbar"=>$toolbar->get_toolbar(),
			"reforb" => $this->mk_reforb("submit", array(
				"id" => $id, 
				"return_url" => urlencode($return_url),
				"parent" => $parent, 
			)),
		));

		return $this->parse();

	}



	////	
	// ! create new isik entry
	// isik			at least one element required
	//	name
	//	firstname
	//	lastname
	//	...
	// name
	// parent
	// comment
	// ...
	function new_isik($arr)
	{
		extract($arr);

			$isik["name"]=$isik["name"]?$isik["name"]:$isik["firstname"]." ".$isik["lastname"];
			foreach ($isik as $key=>$val)
			{
				{
					$this->quote($val);
					$f[]=$key;
					$v[]="'".$val."'";
				}
			}
	
			$id = $this->new_object(array(
				"name" => $isik["name"],
				"parent" => $parent,
				"class_id" => CL_ISIK,
				"comment" => $comment,
				"metadata" => array(
				)
			));
			$f[]='oid';
			$v[]="'".$id."'";

			$q='insert into kliendibaas_isik('.implode(",",$f).')values('.implode(",",$v).')';

		
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
			foreach ($isik as $key=>$val)
			{
				$f[]=" $key=\"$val\"";
			}
			$vv=implode(" , ",$f);
			$q='update kliendibaas_isik set '.$vv.' where oid='.$id;

			$this->upd_object(array(
				"oid" => $id,
				"name" => $isik["name"]?$isik["name"]:$isik["firstname"]." ".$isik["lastname"],
				"comment" => $comment,
				"metadata" => array(
//					"isik"=>$isik,
				)
			));
		$this->db_query($q);
		}
		else
		{	
			$id=$this->new_isik($arr);
		}
		
		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));
	}

}
?>