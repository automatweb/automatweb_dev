<?php
/*
*/
/*
create table kliendibaas_firma
(
oid int primary key unique,
firma_nimetus varchar(255),
reg_nr varchar(20),
ettevotlusvorm int,
pohitegevus varchar(20),
tegevuse_kirjeldus text,
contact int,
firmajuht int,
korvaltegevused text,
kaubamargid text,
tooted text
)
*/
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
			$q="select korvaltegevused from kliendibaas_firma where oid='$id'";
			$resul=$this->db_fetch_field($q,"korvaltegevused");
			$korva=explode(";",$resul);
			$del=array_search($delsub,$korva);
			$korva[$del]=NULL;
			$q='update kliendibaas_firma set korvaltegevused="'.implode(";",$korva).'" where oid='.$id;
			$this->db_query($q);
		}

		if ($deltoode)
		{
			$q="select tooted from kliendibaas_firma where oid='$id'";
			$resul=$this->db_fetch_field($q,"tooted");
			$tood=explode(";",$resul);
			$del=array_search($deltoode,$tood);
			$tood[$del]=NULL;
			$q='update kliendibaas_firma set tooted="'.implode(";",$tood).'" where oid='.$id;
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
			t9.aadress as s_contact,
			t10.name as s_contact2,
			t11.name as s_contact3,
			t1.tegevusala as s_pohitegevus,
			concat(t6.firstname,' ', t6.lastname) as s_firmajuht,
			t7.name as s_ettevotlusvorm
			from kliendibaas_firma as t
			left join kliendibaas_ettevotlusvorm as t7 on t7.oid=t.ettevotlusvorm
			left join kliendibaas_tegevusala as t1 on t1.kood=t.pohitegevus
			left join kliendibaas_isik as t6 on t6.oid=t.firmajuht
			left join kliendibaas_contact as t9 on t9.oid=t.contact
			left join kliendibaas_linn as t10 on t10.oid=t9.linn
			left join kliendibaas_riik as t11 on t11.oid=t9.riik
			where t.oid='$id'"
			; //where t*.status=2
//			concat(t9.aadress,', ',t10.name) as s_contact,
//			concat(t9.aadress,', ',t10.name,', ',t11.name) as s_contact,
//			concat(t9.aadress,', ',t9.linn,', ',t9.riik) as s_contact,

			$res=$this->db_query($q);
			$res=$this->db_next();
			extract($res, EXTR_PREFIX_ALL, "f");



			$korval=explode(";",$f_korvaltegevused);
			$f_korvaltegevused=implode(";",$korval);

			if(is_array($korval))
			foreach($korval as $key => $val)
			{
				if (!$val) continue;

				$q="select tegevusala from kliendibaas_tegevusala where kood='$val'";
				$resul=$this->db_fetch_field($q,"tegevusala");
				if ($resul)
				{
					$this->vars(array(
						"nimetus"=>$resul,
						"delete"=>$this->mk_my_orb("change",array("id" => $id,"delsub" => $val, "return_url" => urlencode($return_url))),
					));
					$s_korvaltegevusedd.=$this->parse("s_korvaltegevused");
				}
				else
				{
					$s_korvaltegevusedd.='<b>tundmatu tegevusala:'.$val.' (lisa)</b><br>';
				}

			}


			$tood=explode(";",$f_tooted);
			$f_tooted=implode(";",$tood);

			if(is_array($tood))
			foreach($tood as $key => $val)
			{
				if (!$val) continue;

				$q="select toode from kliendibaas_toode where kood='$val'";
				$resul=$this->db_fetch_field($q,"toode");
				if ($resul)
				{
					$this->vars(array(
						"nimetus"=>$resul,
						"delete"=>$this->mk_my_orb("change",array("id" => $id,"deltoode" => $val, "return_url" => urlencode($return_url))),
					));
					$s_tootedd.=$this->parse("s_tooted");
				}
				else
				{
					$s_tootedd.='<b>tundmatu toode:'.$val.' (lisa)</b><br>';
				}
			}




			if (!$f_contact)
			{
				get_instance("kliendibaas/contact");
				$f_contact=contact::new_contact(array(
					"parent"=>$ob['parent'],
					"comment"=>"",
					"name"=>$f_firma_nimetus,
					"contact" => array(
						"name"=>$f_firma_nimetus,
						),

				));
				$q='update kliendibaas_firma set contact='.$f_contact.' where oid='.$id;
				$this->db_query($q);
			}

			$contact_change=$this->mk_my_orb("change",array("id" => $f_contact,"parent"=>$ob["parent"],"return_url" => urlencode($return_url)),contact);

			if (!$f_firmajuht)
			{
				get_instance("kliendibaas/isik");

				$f_firmajuht = isik::new_isik(array(
					"name" => $f_firma_nimetus.' - firmajuht',
					"parent" => $ob['parent'],
					"comment" => $f_firma_nimetus.' - firmajuht',
					"isik" => array(
						"name" => "nimi??",
					),

				));
				$q='update kliendibaas_firma set firmajuht='.$f_firmajuht.' where oid='.$id;
				$this->db_query($q);
			}
			$firmajuht_change=$this->mk_my_orb("change",array("id" => $f_firmajuht,"parent"=>$ob["parent"],"return_url" => urlencode($return_url)),isik);

		}

		$this->vars(array(
			"f_oid"=>$f_oid,
			"f_reg_nr" =>$f_reg_nr,
			"f_pohitegevus"=>$f_pohitegevus,
			"f_korvaltegevused"=>$f_korvaltegevused,
			"f_ettevotlusvorm"=>$f_ettevotlusvorm,
			"f_firma_nimetus"=>$f_firma_nimetus,
			"f_tooted"=>$f_tooted,
			"f_kaubamargid"=>$f_kaubamargid,
			"f_contact"=>$f_contact,
			"f_tegevuse_kirjeldus"=>$f_tegevuse_kirjeldus,
			"f_tooted"=>$f_tooted,
			"f_kaubamargid"=>$f_kaubamargid,

			"contact_change"=>$contact_change,
			"firmajuht_change"=>$firmajuht_change,

			"f_firmajuht"=>$f_firmajuht,
//			"f_sourcefile"=>$f_sourcefile,
//			"f_olek"=>$f_olek,
			"s_tooted"=>$s_tootedd,
			"s_korvaltegevused"=>$s_korvaltegevusedd,
			"s_pohitegevus"=>$f_s_pohitegevus,
			"s_ettevotlusvorm"=>$f_s_ettevotlusvorm,
			"s_contact"=>$f_s_contact.', '.$f_s_contact2.', '.$f_s_contact3,
			"f_ettevotlusvorm_pop"=>$this->mk_my_orb("pop_select", array("id" => $id,"tyyp" => "ettevotlusvorm", "return_url" => urlencode($return_url))),
//change		"f_firmajuht_pop"=>$this->mk_my_orb("pop_select", array("id" => $id,"tyyp" => "firmajuht", "return_url" => urlencode($return_url))),
			"f_korvaltegevused_pop"=>$this->mk_my_orb("pop_select", array("id" => $id,"tyyp" => "korvaltegevused", "return_url" => urlencode($return_url))),
			"f_tooted_pop"=>$this->mk_my_orb("pop_select", array("id" => $id,"tyyp" => "tooted", "return_url" => urlencode($return_url))),
			"f_pohitegevus_pop"=>$this->mk_my_orb("pop_select", array("id" => $id,"tyyp" => "pohitegevus", "return_url" => urlencode($return_url))),

			"abx"=>$abx,
			"comment"=>$ob["comment"],
			"toolbar"=>$toolbar->get_toolbar(),
			"id"=>$firma["id"],
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
				if ($key=="korvaltegevused")
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
						$data[$row["oid"]] = substr($row["name"],0,30).".";
					};
					$add=$this->mk_my_orb("new",array("parent"=>$ob["parent"]),"kliendibaas/isik");
				}
			break;
			case "korvaltegevused":
				{
					$q="select t1.kood,t1.tegevusala from kliendibaas_tegevusala as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$this->db_query($q);
					while($row = $this->db_next())
					{
						$data[$row["kood"]] = substr($row["tegevusala"],0,50);
					};
					$add=$this->mk_my_orb("new",array("parent"=>$ob["parent"]),"kliendibaas/tegevusala");
				}
			break;
			case "tooted":
				{
					$q="select t1.kood,t1.toode from kliendibaas_toode as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$this->db_query($q);
					while($row = $this->db_next())
					{
						$data[$row["kood"]] = substr($row["toode"],0,50);
					};
					$add=$this->mk_my_orb("new",array("parent"=>$ob["parent"]),"kliendibaas/toode");
				}
			break;
			case "pohitegevus":
				{
					$q="select t1.kood,t1.tegevusala from kliendibaas_tegevusala as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$this->db_query($q);
					while($row = $this->db_next())
					{
						$data[$row["kood"]] = substr($row["tegevusala"],0,50);
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

			default: $data=array(1=>"nosource");

		}
		if (is_array($data))
		{
			asort($data);
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
