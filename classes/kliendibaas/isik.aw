<?php
/*
	@tableinfo kliendibaas_isik index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property comment type=textarea field=comment cols=40 rows=3
	@caption Kommentaar

	@default table=kliendibaas_isik

	@property firstname type=textbox size=15 maxlength=50
	@caption eesnimi
	@property lastname type=textbox size=15 maxlength=50
	@caption perekonnanimi
	@property gender type=textbox size=5 maxlength=10
	@caption sugu
	@property personal_id type=textbox size=10 maxlength=15
	@caption isikukood
	@property title type=textbox size=5 maxlength=10
	@caption tiitel
	@property nickname type=textbox size=10 maxlength=20
	@caption hüüdnimi
	@property messenger type=textbox size=10 maxlength=200
	@caption msn
	@property birthday type=textbox size=10 maxlength=20
	@caption sünnipäev
	@property social_status type=textbox size=10 maxlength=20
	@caption perekonnaseis
	@property spouse type=textbox size=15 maxlength=50
	@caption abikaasa
	@property children type=textarea cols=20 rows=3
	@caption lapsed
	@property digitalID type=textarea cols=20 rows=3
	@caption digitaalallkiri
	@property pictureurl type=textbox size=20 maxlength=200
	@caption foto/pildi url
	@property picture type=button value=lisa
	@caption pildiobjekt

	@property work_contact_change type=text
	@caption töökoha kontakt

	@property work_contact type=textbox
	@caption töökoha kontakt hidden



	@property personal_contact_change type=text
	@caption kodukoha kontakt

	@property personal_contact type=textbox
	@caption kodukoha kontakt hidden


	@property popups type=text
	@caption popup

//	@property more type=text
//	@caption more

	@classinfo objtable=kliendibaas_isik
	@classinfo objtable_index=oid
*/

class isik extends class_base
{

	function isik()
	{
		$this->init("kliendibaas");
		$this->init(array(
			'clid' => CL_ISIK,
		));
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case 'name':
				$data['value'] =  $args['objdata']['firstname']." ".$args['objdata']['lastname'];
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
//			case 'more':
//				$data['value'] = html::iframe(array('name' => 'contacts','src' => 'juhuu','width' => '500', 'height' => '300'));
//			break;
			case 'personal_contact_change':
				$what='personal_contact';
				$data['value']=$this->contact_manager($what,$args['objdata'][$what]);
			break;
			case 'work_contact_change':
				$what='work_contact';
				$data['value']=$this->contact_manager($what,$args['objdata'][$what]);
			break;


			case "popups":

$data['value']=<<<SCR
<script language='javascript'>

function put_value(target,value)
{
	if (value=='0')
		value='';
	if (target == "work_contact")
		document.changeform.work_contact.value = value;
	else
	if (target == "personal_contact")
		document.changeform.personal_contact.value = value;
	else {
		alert("form element not found")
	}

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
			case 'status':
				$retval=PROP_IGNORE;
			break;
			case 'jrk':
				$retval=PROP_IGNORE;
			break;
			case 'alias':
				$retval=PROP_IGNORE;
			break;
		}
		return $retval;
	}


	function contact_manager($tyyp,$id)
	{

		if (!$id)
		{
		//create
			$onclick="javascript:pop_select('".$this->mk_my_orb("contact_makah", array(
				"tyyp" => $tyyp,
				"do" => 'new',
				'name'=> 'nimi',
				),'kliendibaas/kliendibaas')."')";
			$data['value'].=' '.html::button(array('onclick'=>$onclick,'value'=>'loo'));
		}
		else
		{
		//change
			$data['value'] .=html::href(array(
				'caption' => 'muuda',
				'target' => '_blank',
				'url' => $this->mk_my_orb('change',array(
					'id'=>$id,
				),'contact'
				),
			));

			//delete
			$onclick="javascript:pop_select('".$this->mk_my_orb("contact_makah", array(
				"tyyp" => $tyyp,
				"do" => 'delete',
				'id' => $id,
					),'kliendibaas/kliendibaas')."')";
				$data['value'].=' '.html::button(array('onclick'=>$onclick,'value'=>'kustuta'));

		}
		return $data['value'];
	}


}
?>
