<?php
/*

	@tableinfo kliendibaas_firma index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property comment type=textarea field=comment cols=40 rows=3
	@caption Kommentaar

	@default table=kliendibaas_firma

	@property reg_nr type=textbox size=10 maxlenght=20
	@caption registri nr

	@property pohitegevus type=textbox size=10 maxlenght=20
	@caption põhitegevus
	@property pohitegevus_b type=button
	@caption pohitegevus

	@property korvaltegevused type=textbox size=10 maxlenght=20
	@caption kõrvaltegevused
	@property korvaltegevused_b type=button
	@caption korvaltegevused

	@property ettevotlusvorm type=textbox size=10 maxlenght=20
	@caption ettevõtlusvorm
	@property ettevotlusvorm_b type=button
	@caption ettevõtlusvorm

	@property firma_nimetus type=textbox size=10 maxlenght=20
	@caption fima nimetus

	@property tooted type=textbox size=10 maxlenght=20
	@caption tooted
	@property tooted_b type=button
	@caption tooted

	@property kaubamargid type=textbox size=10 maxlenght=20
	@caption kaubamärgid

	@property contact type=textbox size=10 maxlenght=20
	@caption kontakt

	@property tegevuse_kirjeldus type=textbox size=10 maxlenght=20
	@caption tegevuse kirjeldus

	@property firmajuht type=textbox size=10 maxlenght=20
	@caption firmajuht

	@property popups type=text
	@caption pop

	@classinfo objtable=kliendibaas_firma
	@classinfo objtable_index=oid
*/

class firma extends class_base
{

	function firma()
	{
		$this->init("kliendibaas");
		$this->init(array(
			'clid' => CL_FIRMA,
		));
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case 'blaa':

			break;

		};
		return $retval;
	}

	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = true;
		switch($data['name'])
		{
			case 'popups':

$data['value']=<<<SCR
<script language='javascript'>

function put_value(target,value)
{
	if (target == "ettevotlusvorm")
		document.changeform.ettevotlusvorm.value = value;
	else
	if (target == "korvaltegevused")
		document.changeform.korvaltegevused.value = value;
	else
	if (target == "tooted")
		document.changeform.tooted.value = value;
	else
	if (target == "pohitegevus")
		document.changeform.pohitegevus.value = value;
	else
	{}
	document.changeform.submit();
}

function pop_select(url)
{
	aken=window.open(url,"selector","HEIGHT=300,WIDTH=310,TOP=400,LEFT=500")
 	aken.focus()
}
</script>
SCR;
			break;

			case 'ettevotlusvorm_b':
				$data['value']='vali ettevotlusvorm';
				$data['onclick']="javascript:pop_select('".$this->mk_my_orb('pop_select', array('id' => $id, 'table' => 'kliendibaas_firma','tyyp' => 'ettevotlusvorm', 'return_url' => urlencode($return_url)),'kliendibaas/kliendibaas')."')";
			break;
			case 'korvaltegevused_b':
				$data['value']='vali kõrvaltegevused';
				$data['onclick']="javascript:pop_select('".$this->mk_my_orb('pop_select', array('id' => $id, 'table' => 'kliendibaas_firma', 'tyyp' => 'korvaltegevused', 'return_url' => urlencode($return_url)),'kliendibaas/kliendibaas')."')";
			break;
			case 'tooted_b':
				$data['value']='vali tooted';
				$data['onclick']="javascript:pop_select('".$this->mk_my_orb('pop_select', array('id' => $id, 'table' => 'kliendibaas_firma','tyyp' => 'tooted', 'return_url' => urlencode($return_url)),'kliendibaas/kliendibaas')."')";
			break;
			case 'pohitegevus_b':
				$data['value']='vali põhitegevus';
				$data['onclick']="javascript:pop_select('".$this->mk_my_orb('pop_select', array('id' => $id, 'table' => 'kliendibaas_firma','tyyp' => 'pohitegevus', 'return_url' => urlencode($return_url)),'kliendibaas/kliendibaas')."')";
			break;

			case 'plaaah':
			break;
		};
		return $retval;
	}


/*
	function change($arr)
	{
		if ($id)
		{
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
			;

			$res=$this->db_query($q);

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

*/


		function delsub()
		{
			//$field,$table='kliendibaas_firma',$id
			extract($arr);
			$resul=$this->db_fetch_field("select $field from $table where oid=$id",$field);
			$arr=explode(";",$resul);
			$del=array_search($delsub,$arr);
			$arr[$del]=NULL;
			$q='update $table set korvaltegevused="'.implode(";",$arr).'" where oid='.$id;
//			$this->db_query($q);
		}
}
?>
