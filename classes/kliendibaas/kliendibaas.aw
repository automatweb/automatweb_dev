<?php

/*
	@default table=objects
	@default group=general

	@property comment type=textarea field=comment
	@caption Kommentaar

	@property asi type=textarea
	@caption asi

*/
classload('defs');
class kliendibaas extends class_base
{
	function kliendibaas()
	{
		$this->init("kliendibaas");
		$this->init(array(
			'clid' => CL_KLIENDIBAAS,
		));
	}


	////
	// !
	// table - required
	// field -

	function pop_select($arr)
	{
		extract($arr);

		if (!$tyyp) die('no type');

		if (($table == 'kliendibaas_contact') || ($table == 'kliendibaas_firma')) $field=$tyyp; else die('vale tabel');
		;
		if ($id)
		{
//			$selected=$this->db_fetch_field("select $field from $table where oid=$id",$field);
//			$ob = $this->get_object($id);
		}

		switch($field)
		{
			case "firmajuht":
				{
					$q="select t1.oid,concat(t1.firstname,' ',t1.lastname) as name from kliendibaas_isik as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$object='isik';
				}
			break;
			case "korvaltegevused":
				{
					$q="select t1.kood as oid,t1.tegevusala as name from kliendibaas_tegevusala as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$object='tegevusala';
				}
			break;
			case "tooted":
				{
					$q="select t1.kood as oid,t1.toode as name from kliendibaas_toode as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$object='toode';
				}
			break;
			case "pohitegevus":
				{
					$q="select t1.kood as oid,t1.tegevusala as name from kliendibaas_tegevusala as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$object='tegevusala';
				}
			break;
			case "ettevotlusvorm":
				{
					$q="select t1.oid,t1.name from kliendibaas_ettevotlusvorm as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$object='ettevotlusvorm';
				}
			break;
			case "linn":
				{
					$q="select t1.oid,t1.name from kliendibaas_linn as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$object='linn';
				}
			break;
			case "riik":
				{
					echo $q="select t1.oid,t1.name from kliendibaas_riik as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$object='riik';
				}
			break;
			case "maakond":
				{
					$q="select t1.oid,t1.name from kliendibaas_maakond as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$object='maakond';
				}
			break;
		}

		$data[0] = ' - ';

		if ($q)
		{
			$this->db_query($q);
			while($row = $this->db_next())
			{
				$data[$row["oid"]] = substr($row["name"],0,50);
			};
		}
		else
		{
			$data[]='tekkis viga';
		}
		if ($id)
		{
			$add=$this->mk_my_orb('new',array('parent'=>$ob['parent']),'kliendibaas/'.$object);
			$add_new=html::href(array('caption' => 'lisa andmebaasi uus '.$field, 'target' =>'_blank','url' =>$add));
		}

		if (is_array($data))
		{
			asort($data);
		}


$pop_tpl=<<<TPL
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
	<HEAD>
		<TITLE> Vali {VAR:mida}</TITLE>
		<SCRIPT language=JavaScript>
			function SendValue(value)
			{
				opener.put_value(document.selectform.tyyp.value,value);
				window.close();
			}
		</SCRIPT>
	</HEAD>
	<BODY onload="document.selectform.selector.focus()" bgcolor=#777777>
		<form name=selectform>
			 Vali {VAR:mida}<br/>
			{VAR:tyyp}
			{VAR:selector}
			<br>
			{VAR:cancel}
			<br>
			{VAR:ok}
			<br>
			{VAR:add}
		</form>
	</BODY>
</HTML>
TPL;

		$vars=array(

			'tyyp' => html::hidden(array('name'=>'tyyp','value' => $field)),
			'cancel' => html::button(array('value'=>'cancel','onclick'=>"javascript:window.close()")),
			'ok' => html::button(array('value'=>'ok','onclick'=>"javascript:SendValue(document.selectform.selector.value)")),
			"add" => $add_new,
			"mida" => $field,

			'selector' => html::select(array(
				'name' => 'selector',
				'size'=> 10,
				'selected' => $selected,
//				'multiple' => 1,
				'options' => $data,
			)),
		);

		echo localparse($pop_tpl,$vars);
		die();//et mingit jama ei väljastaks
	}



//do+
//tyyp+
//id
//name
//parent

	function contact_makah($arr)
	{

		extract($arr);


if ($do=='new')
{
	$value=$this->new_contact(array('contact'=>array('name'=>$name),'name'=>$name,'parent'=>$parent));
}
elseif ($do=='delete')
{
	$this->upd_object(array(
		"oid" => $id,
		'status'=>0,
		));
$value=0;
}

//	$tyyp=;


$pop_tpl=<<<TPL
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
		<SCRIPT language=JavaScript>
				opener.put_value('{VAR:tyyp}','{VAR:value}');
				window.close();
		</SCRIPT>
<title>palun oota</tiltle>
</HEAD>

	<BODY>
	{VAR:msg}

	</BODY>
</HTML>
TPL;


$vars=array('msg'=>'ok','tyyp'=>$tyyp,'value'=>$value);


		echo localparse($pop_tpl,$vars);
		die();//et mingit jama ei väljastaks


	}

	
	


	function isik_makah($arr)
	{

		extract($arr);


if ($do=='new')
{
	$value=$this->new_isik(array('isik'=>array('name'=>$name),'name'=>$name,'parent'=>$parent));
}
elseif ($do=='delete')
{
	$this->upd_object(array(
		"oid" => $id,
		'status'=>0,
		));
$value=0;
}

//	$tyyp=;


$pop_tpl=<<<TPL
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
		<SCRIPT language=JavaScript>
				opener.put_value('{VAR:tyyp}','{VAR:value}');
				window.close();
		</SCRIPT>
<title>palun oota</tiltle>
</HEAD>

	<BODY>
	{VAR:msg}

	</BODY>
</HTML>
TPL;


$vars=array('msg'=>'ok','tyyp'=>$tyyp,'value'=>$value);


		echo localparse($pop_tpl,$vars);
		die();//et mingit jama ei väljastaks


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


}
?>
