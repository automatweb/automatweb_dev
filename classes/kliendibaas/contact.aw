<?php
/*
	@default table=objects
	@default group=general

	@property comment type=textarea field=comment cols=40 rows=3
	@caption Kommentaar

	@default table=kliendibaas_contact

	@property postiindeks type=textbox size=5 maxlength=5
	@caption postinindex

	@property telefon type=textbox size=10 maxlength=15
	@caption telefon
	@property mobiil type=textbox size=10 maxlength=15
	@caption telefon
	@property faks type=textbox size=10 maxlength=20
	@caption faks
	@property piipar type=textbox size=10 maxlength=20
	@caption piipar
	@property aadress type=textbox size=30 maxlength=100
	@caption aadress
	@property e_mail type=textbox size=25 maxlength=100
	@caption e-mail
	@property kodulehekylg type=textbox size=40 maxlength=300
	@caption kodulehekülg

	@property linn type=hidden
	@property maakond type=hidden
	@property riik type=hidden

	@property linn_c type=button
	@caption vali_linn

	@property linn_s type=text
	@caption linn

	@property maakond_c type=button
	@caption vali_linn

	@property maakond_s type=text
	@caption maakond

	@property riik_c type=button
	@caption vali_linn

	@property riik_s type=text
	@caption riik

	@property popups type=text

	@property more type=text

	@classinfo objtable=kliendibaas_contact
	@classinfo objtable_index=oid
*/


class contact extends class_base
{

	function contact()
	{
//		$this->init("kliendibaas");
		$this->init(array(
			'clid' => CL_CONTACT,
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
		$data = &$args["prop"];
		$retval = true;
		switch($data["name"])
		{

			case "popups":

$data['value']=<<<SCR
<script language='javascript'>

function put_value(target,value)
{
	if (target == "linn")
		document.changeform.linn.value = value;
	else
	if (target == "maakond")
		document.changeform.maakond.value = value;
	else
	if (target == "riik")
		document.changeform.riik.value = value;
	else {}
		document.changeform.submit();
}

function pop_select(url)
{
	aken=window.open(url,"selector","HEIGHT=300,WIDTH=310,TOP=400,LsEFT=500")
 	aken.focus()
}
</script>
SCR;
			break;

			case "linn_c":
				$data['value']="vali linn";
				$data['onclick']="javascript:pop_select('".$this->mk_my_orb("pop_select", array("id" => $id,'table' => 'kliendibaas_firma',"tyyp" => "linn", "return_url" => urlencode($return_url)),'kliendibaas/kliendibaas')."')";
			break;
			case "riik_c":
				$data['value']="vali riik";
				$data['onclick']="javascript:pop_select('".$this->mk_my_orb("pop_select", array("id" => $id,'table' => 'kliendibaas_firma',"tyyp" => "riik", "return_url" => urlencode($return_url)),'kliendibaas/kliendibaas')."')";
			break;
			case "maakond_c":
				$data['value']="vali maakond";
				$data['onclick']="javascript:pop_select('".$this->mk_my_orb("pop_select", array("id" => $id,'table' => 'kliendibaas_firma',"tyyp" => "maakond", "return_url" => urlencode($return_url)),'kliendibaas/kliendibaas')."')";
			break;

			case 'more':
				$data['value']='';
			break;

			case 'riik_s':
			$q="select name	from kliendibaas_riik where oid='".$args['objdata']['riik']."'";
			$data['value']=$this->db_fetch_field($q,'name');
			break;

			case 'linn_s':
			$q="select name	from kliendibaas_linn where oid='".$args['objdata']['linn']."'";
			$data['value']=$this->db_fetch_field($q,'name');
			break;

			case 'maakond_s':
			$q="select name	from kliendibaas_maakond where oid='".$args['objdata']['maakond']."'";
			$data['value']=$this->db_fetch_field($q,'name');

			break;

		}
		return $retval;
	}


	function show($arr)
	{
		extract($arr);
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
		));
		return $this->parse();

	}


/*
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
*/


}
?>
