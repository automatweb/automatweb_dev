<?php
/*
create table kliendibaas_contact(
oid int primary key unique,
name int,
tyyp int,
riik int,
linn int,
maakond int,
postiindeks char(5),
telefon varchar(20),
mobiil varchar(20),
faks varchar(20),
piipar varchar(20),
aadress text,
e_mail varchar(255),
kodulehekylg varchar(255)
);*/
class firma extends aw_template
{

	function firma()
	{
		// change this to the folder under the templates folder, where this classes templates will be 
		$this->init("kliendibaas");
	}

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

		if ($delsub)
		{
			$q="select korvaltegevus from kliendibaas_firma where oid='$id'";	
			$resul=$this->db_fetch_field($q,"korvaltegevus");
			$korva=explode(";",$resul);
			$del=array_search($delsub,$korva);
			$korva[$del]=NULL;
			$q='update kliendibaas_firma set korvaltegevus="'.implode(";",$korva).'" where oid='.$id;
			$this->db_query($q);
		}


		if ($id)
		{

			$toolbar->add_button(array(
				"name" => "lisa",
				"tooltip" => "lisa isik",
				"url" => $this->mk_my_orb("change", array("return_url" => urlencode($return_url),"parent" => $ob["parent"],)),
				"imgover" => "new_over.gif",
				"img" => "new.gif",
			));

			$q="select  t.* ,
			t4.name as s_contact,		
			t1.tegevusala_et as s_pohitegevus,
			t2.tegevusala_et as s_tegevusala,
			concat(t6.firstname,' ', t6.lastname) as s_firmajuht,
			t7.name as s_ettevotlusvorm
			from kliendibaas_firma as t 
			left join kliendibaas_ettevotlusvorm as t7 on t7.oid=t.ettevotlusvorm
			left join kliendibaas_contact as t4 on t4.oid=t.contact
			left join kliendibaas_tegevusala as t1 on t1.oid=t.pohitegevus
			left join kliendibaas_tegevusala as t2 on t2.oid=t.tegevusala
			left join kliendibaas_isik as t6 on t6.oid=t.firmajuht
			where t.oid='$id'"; //where t*.status=2

			$res=$this->db_query($q);
			$res=$this->db_next();
			extract($res, EXTR_PREFIX_ALL, "f");

			$korval=explode(";",$f_korvaltegevus);
			$f_korvaltegevus=implode(";",$korval);

			if(is_array($korval))
			foreach($korval as $key => $val)
			{
				if (!$val) continue;
				
				$q="select tegevusala_et from kliendibaas_tegevusala where oid='$val'";	
				$resul=$this->db_fetch_field($q,"tegevusala_et");
				if ($resul)
				{
					$this->vars(array(
						"nimetus"=>$resul,
						"delete"=>$this->mk_my_orb("change",array("id" => $id,"delsub" => $val, "return_url" => urlencode($return_url))),
					));
					$s_korvaltegevused.=$this->parse("s_korvaltegevus");
				}
			}
		}

//get_instance("kliendibaas/contact");
//classload("kliendibaas/contact");

//$konn=contact::show(array("id"=>53422));

//$konn=implode("",file($this->mk_my_orb("show",array("id" => 53422),"contact")));


		$this->vars(array(
			"f_oid"=>$f_oid,
			"f_reg_nr" =>$f_reg_nr,
			"f_tegevusala"=>$f_tegevusala,
			"f_pohitegevus"=>$f_pohitegevus,
			"f_korvaltegevus"=>$f_korvaltegevus,
			"f_ettevotlusvorm"=>$f_ettevotlusvorm,
			"f_firma_nimetus"=>$f_firma_nimetus,

			"f_contact"=>$this->mk_my_orb($f_contact?"change":"new",array("id" => $f_contact,"parent"=>$ob["parent"],"return_url" => urlencode($return_url)),contact),


			"contact"=>$konn."jee",
			"f_firmajuht"=>$f_firmajuht,
			"f_sourcefile"=>$f_sourcefile,
			"f_olek"=>$f_olek,

			"s_korvaltegevus"=>$s_korvaltegevused,
			"s_tegevusala"=>$f_s_tegevusala,
			"s_pohitegevus"=>$f_s_pohitegevus,
			"s_ettevotlusvorm"=>$f_s_ettevotlusvorm,
			"s_riik"=>$f_s_riik,
			"s_linn"=>$f_s_linn,
			"s_maakond"=>$f_s_maakond,
			"s_firmajuht"=>$f_s_firmajuht,


			"f_ettevotlusvorm_pop"=>$this->mk_my_orb("pop_select", array("id" => $id,"tyyp" => "ettevotlusvorm", "return_url" => urlencode($return_url))),
//change			"f_firmajuht_pop"=>$this->mk_my_orb("pop_select", array("id" => $id,"tyyp" => "firmajuht", "return_url" => urlencode($return_url))),
			"f_korvaltegevus_pop"=>$this->mk_my_orb("pop_select", array("id" => $id,"tyyp" => "korvaltegevus", "return_url" => urlencode($return_url))),
			"f_pohitegevus_pop"=>$this->mk_my_orb("pop_select", array("id" => $id,"tyyp" => "pohitegevus", "return_url" => urlencode($return_url))),
			"f_tegevusala_pop"=>$this->mk_my_orb("pop_select", array("id" => $id,"tyyp" => "tegevusala", "return_url" => urlencode($return_url))),
//change			"f_riik_pop"=>$this->mk_my_orb("pop_select", array("id" => $id,"tyyp" => "riik", "return_url" => urlencode($return_url))),
			"abx"=>$abx,
			"comment"=>$ob["comment"],
			"toolbar"=>$toolbar->get_toolbar(),
			"id"=>$firma["id"],
//			"aliasmgr_link_pohitegevus" => $this->mk_my_orb("list_aliases",array("id" => $id),"aliasmgr"),
			"reforb" => $this->mk_reforb("submit", array(
				"id" => $id, 
				"return_url" => urlencode($return_url),
				"parent" => $parent, 
			)),
		));
		return $this->parse();
	}


	function parse_alias($args)
	{
		extract($args);
			
		return $this->show(array("id" => $alias["target"]));
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
				if ($key=="korvaltegevus")
				{
					$f[]=" $key=\"".implode(";",array_unique(explode(";",$val)))."\"";
				}
				else
				{
					$f[]=" $key=\"$val\"";
				}
				
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
//			$exclude=array("1","oid","id");
			foreach ($firma as $key=>$val)
			{
//				if(!array_search($key,$exclude))
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


	function pop_select($arr)
	{
		extract($arr);
		$this->read_template("pop_select.tpl");
		if ($id)
		{
			$selected=$this->db_fetch_field("select $tyyp from kliendibaas_firma where oid=$id",$tyyp);
			$ob = $this->get_object($id);		
		}

		switch($tyyp)
		{
			case "firmajuht":
				{
					$q="select t1.oid,concat(t1.firstname,' ',t1.lastname) as name from kliendibaas_isik as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$this->db_query($q);
					while($row = $this->db_next()) 
					{
						$data[$row["oid"]] = $row["name"];
					};
					$add=$this->mk_my_orb("new",array("parent"=>$ob["parent"]),"kliendibaas/isik");
				}
			break;
			case "tegevusala":
				{
					$q="select t1.oid,t1.tegevusala_et from kliendibaas_tegevusala as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$this->db_query($q);
					while($row = $this->db_next()) 
					{
						$data[$row["oid"]] = $row["tegevusala_et"];
					};
					$add=$this->mk_my_orb("new",array("parent"=>$ob["parent"]),"kliendibaas/tegevusala");
				}
			break;
			case "korvaltegevus":
				{
					$q="select t1.oid,t1.tegevusala_et from kliendibaas_tegevusala as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$this->db_query($q);
					while($row = $this->db_next()) 
					{
						$data[$row["oid"]] = $row["tegevusala_et"];
					};
					$add=$this->mk_my_orb("new",array("parent"=>$ob["parent"]),"kliendibaas/tegevusala");
				}
			break;
			case "ettevotlusvorm":
				{
					$q="select t1.oid,t1.name from kliendibaas_ettevotlusvorm as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$this->db_query($q);
					while($row = $this->db_next()) 
					{
						$data[$row["oid"]] = $row["name"];
					};
					$add=$this->mk_my_orb("new",array("parent"=>$ob["parent"]),"kliendibaas/ettevotlusvorm");
				}
			break;
			case "pohitegevus":
				{
					$q="select t1.oid,t1.tegevusala_et from kliendibaas_tegevusala as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$this->db_query($q);
					while($row = $this->db_next()) 
					{
						$data[$row["oid"]] = $row["tegevusala_et"];
					};
					$add=$this->mk_my_orb("new",array("parent"=>$ob["parent"]),"kliendibaas/tegevusala");
				}
			break;


			default: $data=array(1=>"nosource");

		}



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