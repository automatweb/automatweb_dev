<?php
class firma extends aw_template
{

	function firma()
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
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda firma");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda firma");
		}
		$this->read_template("firma_change.tpl");

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
			$q="select * from kliendibaas_firma where oid=$id";
			$res=$this->db_query($q);
			$res=$this->db_next();
			extract($res, EXTR_PREFIX_ALL, "f");
		}


		$this->vars(array(
			"f_oid"=>$f_oid,
			"f_reg_nr" =>$f_reg_nr,
			"f_tegevusala"=>$f_tegevusala,
			"f_pohitegevus"=>$f_pohitegevus,
			"f_korvaltegevus"=>$f_korvaltegevus,
			"f_ettevotlusvorm"=>$f_ettevotlusvorm,
			"f_firma_nimetus"=>$f_firma_nimetus,
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
			"f_firmajuht"=>$f_firmajuht,
			"f_sourcefile"=>$f_sourcefile,
			"f_olek"=>$f_olek,

			"f_linn_pop"=>$this->mk_my_orb("pop_select", array("id" => $id,"tyyp" => "linn", "return_url" => urlencode($return_url))),
			"f_linn_text"=>"test",


//			"name"=>$ob["name"],
			"comment"=>$ob["comment"],
			"toolbar"=>$toolbar->get_toolbar(),
			"id"=>$firma["id"],
//			"vorm"=>$form,

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

			foreach ($firma as $key=>$val)
			{
				$f[]=" $key=\"$val\"";
			}
			$vv=implode(" , ",$f);
			$q='update kliendibaas_firma set '.$vv.' where oid='.$id;

			$this->upd_object(array(
				"oid" => $id,
				"name" => $firma["firma_nimetus"],
				"comment" => $comment,
				"metadata" => array(
					"firma"=>$firma,
				)
			));

		}
		else
		{
			$exclude=array("1","oid","id");
			foreach ($firma as $key=>$val)
			{
				if(!array_search($key,$exclude))
				{
					$f[]=$key;
					$v[]="'".$val."'";
				}
			}
	
			$ff=implode(",",$f);
			$vv=implode(",",$v);
			$id = $this->new_object(array(
				"name" => $firma["firma_nimetus"],
				"parent" => $parent,
				"class_id" => CL_FIRMA,
				"comment" => $comment,
				"metadata" => array(
//					"firma"=>$firma,
				)
			));
			$q="insert into kliendibaas_firma($ff,oid)values($vv,'$id')";

		}
//echo $q;
//if($q)
$this->db_query($q);


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

		return $this->parse();
	}



	////
	// !called, when adding a new object 
	// parameters:
	// parent - the folder under which to add the object
	// return_url - optional, if set, the "back" link should point to it
	// alias_to - optional, if set, after adding the object an alias to the object with oid alias_to should be created


	function pop_select($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
//		$this->read_template("pop_select.tpl");
		echo $tyyp."__";
		switch($tyyp)
		{
			case "linn":
				{
					$q="select * from html_import_linn";
					$this->db_query($q);
					while($row = $this->db_next()) 
					{
						$data[$row["id"]] = $row["linn"];
					};
				}
			break;
			default: $data=array("nosource");

		}

		$vars=array(
			"tyyp"=>$tyyp,
			"mida"=>"midagi",
			"options"=>$this->picker(1,$data),
		);

		echo $this->localparse(implode("",file(aw_ini_get("tpldir")."/kliendibaas/pop_select.tpl")),$vars); //parse links

//		echo $valu=$ob["meta"][$tyyp];
//		echo "<html>jeberijee</html>";
		die();//et mingit jama ei väljastaks
	}







}
?>