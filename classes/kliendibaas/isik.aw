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
			$data=$this->db_query($q);
			$data=$this->db_next();
		}

$fields=array(
	"firstname"=>array("desc"=>"eesinimi","size"=>15,"maxlength"=>50),
	"lastname"=>array("desc"=>"perekonnanimi","size"=>15,"maxlength"=>50),
	"personal_id"=>array("desc"=>"isikukood","size"=>10,"maxlength"=>11),
	"title"=>array("desc"=>"tiitel","size"=>5,"maxlength"=>10),
	"nickname"=>array("desc"=>"pseudonüüm","size"=>10,"maxlength"=>20),
	"birthday"=>array("desc"=>"sünnikuupäev","size"=>10,"maxlength"=>10),
	"social_status"=>array("desc"=>"sotsiaalne seis","size"=>10,"maxlength"=>20),
	"spouse"=>array("desc"=>"abikaasa","size"=>15,"maxlength"=>50),
	"children"=>array("desc"=>"lapsed","size"=>20,"maxlength"=>60),
	"h_e_mail"=>array("desc"=>"e-post","size"=>15,"maxlength"=>50),
/*
	""=>array("desc"=>"","size"=,"maxlength"=>),


"lastname"=>"perekonnanimi",
//""name varchar(100),
"personal_id"=>"isikukood",
"title"=>"tiitel",
"nickname"=>"pseudonüüm",
//"gender"=>"sugu",
"birthday"=>"sünnikuupäev",
"social_status"=>"sotsiaalne seis",
"spouse"=>"abikaasa",
"children"=>"lapsed",
"h_e_mail"=>"e-post"

/*"=>"",
"h_street"=>"tänav/maja",
"h_city varchar(50),
h_country varchar(50),
h_zipcode char(5),
h_phone varchar(20),
h_fax varchar(20),
h_mobile varchar(20),
h_webpage varchar(200),
h_messenger varchar(200),
*//*
w_company varchar(50),
w_office varchar(50),
w_jobtitle varchar(20),
w_e_mail varchar(100),
w_street varchar(100),
w_city varchar(50),
w_country varchar(50),
w_zipcode char(5),
w_phone varchar(20),
w_fax varchar(20),
w_webpage varchar(200),
*/
/*digitalID text,
notes text,
pictureurl varchar(200),
picture blob
*/

);



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
			"comment"=>$ob["comment"],
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
				"name" => $isik["firstname"]." ".$isik["lastname"],
				"comment" => $comment,
				"metadata" => array(
//					"isik"=>$isik,
				)
			));
		}
		else
		{
			foreach ($isik as $key=>$val)
			{
				$f[]=$key;
				$v[]="'".$val."'";
			}
	
			$ff=implode(",",$f);
			$vv=implode(",",$v);
			$id = $this->new_object(array(
				"name" => $isik["firstname"]." ".$isik["lastname"],
				"parent" => $parent,
				"class_id" => CL_ISIK,
				"comment" => $comment,
				"metadata" => array(
//					"isik"=>$isik,
				)
			));
			$q="insert into kliendibaas_isik($ff,oid)values($vv,'$id')";

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