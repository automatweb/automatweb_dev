<?php
/*
	
	@groupinfo general caption=&Uuml;ldine
	
	@classinfo relationmgr=yes
	@tableinfo kliendibaas_isik index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property firstname type=text
	@caption nimi

	@property comment type=textarea field=comment cols=40 rows=3
	@caption Kommentaar

	@default table=kliendibaas_isik

	@property firstname type=textbox size=15 maxlength=50
	@caption eesnimi

	@property lastname type=textbox size=15 maxlength=50
	@caption perekonnanimi

	@property title type=textbox size=5 maxlength=10
	@caption tiitel

	@property gender type=textbox size=5 maxlength=10
	@caption sugu

	@property personal_id type=textbox size=13 maxlength=11
	@caption isikukood

	@property nickname type=textbox size=10 maxlength=20
	@caption hüüdnimi

	@property messenger type=textbox size=30 maxlength=200
	@caption msn/yahoo/aol/icq

	@property birthday type=textbox size=10 maxlength=20
	@caption sünnipäev

	@property social_status type=textbox size=20 maxlength=20
	@caption perekonnaseis

	@property spouse type=textbox size=25 maxlength=50
	@caption abikaasa

	@property children type=textarea cols=20 rows=2
	@caption lapsed

	@property digitalID type=textbox size=20 maxlength=300
	@caption digitaalallkiri(fail vms)?

	@property pictureurl type=textbox size=40 maxlength=200
	@caption pildi/foto url

	@property picture type=relpicker reltype=PICTURE
	@caption pilt/foto

//	@property work_contact type=popup_objmgr clid=CL_TEGEVUSALA multiple=1 method=serialize field=meta table=objects
	@property work_contact type=relpicker reltype=WORKADDRESS
	@caption töökoha kontakt andmed

	@property personal_contact type=relpicker reltype=HOMEADDRESS
	@caption kodused kontakt andmed


	@default group=forms
	@default field=meta
	@default table=objects
	@default method=serialize
	@groupinfo forms caption=väljundid

	@property forms type=relpicker reltype=BACKFORMS
	@caption tagasiside vormid

	@property templates type=select
	@caption templiidid

	@default group=show
	@groupinfo show caption=Visiitkaart submit=no
	@property dokus type=text callback=show_isik


*/


define ('HOMEADDRESS',1);
define ('WORKADDRESS',2);
define ('PICTURE',3);
define ('BACKFORMS',4);
define ('TEMPLATES',5);


class isik extends class_base
{

	function show_isik($args)
	{

		$arg2['id'] = $args['obj']['oid'];
		$nodes = array();
		$nodes['visitka'] = array(
			"value" => $this->show($arg2),
		);
		return $nodes;
	}


	function show($args)
	{
		extract($args);

		$obj = $this->get_object($id);
		$this->read_template('visit/'.$obj['meta']['templates']);

		if (isset($obj['meta']['form']))
		{
			$forms = '';
			foreach($obj['meta']['form'] as $val)
			{
				$form = $this->get_object($val);
				$forms.= html::href(array('caption' => $form['name'], 'url' => $this->mk_my_orb('show', array(
					'id' => $form['oid'],
					'tagasiside' => $id,
					'tagasiside_class' => 'isik',

					)))).'<br />';
			}

		}


//vot siuke päring, ära küsi
		$row = $this->db_fetch_row("select
			t2.name as name,
			firstname,
			lastname,
			gender,
			personal_id,
			title,
			nickname,
			messenger,
			birthday,
			social_status,
			spouse,
			children,
			personal_contact,
			work_contact,
			digitalID,
			notes,
			pictureurl,
			t5.link as picture,
			t3.riik as k_riik,
			t3.maakond as k_maakond,
			t3.postiindeks as k_postiindex,
			t3.aadress as k_aadress,
			t3.telefon as k_telefon,
			t3.mobiil as k_mobiil,
			t3.faks as k_faks,
			t3.e_mail as k_e_mail,
			t3.kodulehekylg as k_kodulehekylg,
			t4.riik as w_riik,
			t4.maakond as w_maakond,
			t4.postiindeks as w_postiindex,
			t4.aadress as w_aadress,
			t4.telefon as w_telefon,
			t4.mobiil as w_mobiil,
			t4.faks as w_faks,
			t4.e_mail as w_e_mail,
			t4.kodulehekylg as w_kodulehekylg

			from objects as t1

			left join kliendibaas_isik as t2 on t1.oid=t2.oid
			left join kliendibaas_address as t3 on t2.personal_contact=t3.oid
			left join kliendibaas_address as t4 on t2.work_contact=t4.oid
			left join images as t5 on t2.picture=t5.id

			where t1.oid=".$id);



		 if ($row['picture'])
		 {
			$imd = $this->get_image_by_id($row['picture']);
			if ($imd['file'] != '')
			{
				$row['picture'] = html::img(array('url' => $imd['url']));
			};

		}
//		$row['picture']=$row['picture']?html::img(array('src' => $row['picture'])):'';
		
		$row['picture'].=$row['pictureurl']?html::img(array('url' => $row['pictureurl'])):'';
		$row['k_e_mail']=$row['k_e_mail']?html::href(array('url' => 'mailto:'.$row['k_e_mail'], 'caption' => $row['k_e_mail'])):'';
		$row['w_e_mail']=$row['w_e_mail']?html::href(array('url' => 'mailto:'.$row['w_e_mail'],'caption' => $row['w_e_mail'])):'';
		$row['k_kodulehekylg']=$row['k_kodulehekylg']?html::href(array('url' => $row['k_kodulehekylg'],'caption' => $row['k_kodulehekylg'],'target' => '_blank')):'';
		$row['w_kodulehekylg']=$row['w_kodulehekylg']?html::href(array('url' => $row['w_kodulehekylg'],'caption' => $row['w_kodulehekylg'],'target' => '_blank')):'';
		$row['tagasisidevormid'] =

		$this->vars($row);

		return $this->parse();;
	}

	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}


	function callback_get_rel_types()
	{
		return array(
			HOMEADDRESS => 'kodune aadress',
			WORKADDRESS => 'töökoha aadress',
			PICTURE => 'pilt',
			BACKFORMS => 'tagasiside vorm', //pilootobjekt siis ühesõnaga
			TEMPLATES => 'templiit',
		);
	}

	function isik()
	{
		$this->init(array(
			"tpldir" => "isik",
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
				if ($args['objdata']['firstname'] || $args['objdata']['lastname'])
				{
					$data['value'] =  $args['objdata']['firstname']." ".$args['objdata']['lastname'];
				}
			break;
		};
		return $retval;
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;

		switch($data["name"])
		{
			case 'templates':
				$tpls = $this->get_directory(array('dir' => aw_ini_get('tpldir').'/isik/visit/'));
				$data['options'] = $tpls;
			break;
			case 'name':
				$data['value'] = $args['obj']['name'];
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
}
?>
