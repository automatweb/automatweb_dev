<?php
/*

	@classinfo relationmgr=yes
	@groupinfo general caption=�ldine	
	@tableinfo kliendibaas_isik index=oid master_table=objects master_index=oid

	@default table=objects
	@default group=general

	@property name type=textbox
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
	@caption h��dnimi

	@property messenger type=textbox size=30 maxlength=200
	@caption msn/yahoo/aol/icq

	@property birthday type=textbox size=10 maxlength=20
	@caption s�nnip�ev

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
	@caption t��koha kontakt andmed

	@property personal_contact type=relpicker reltype=HOMEADDRESS
	@caption kodused kontakt andmed


	@default group=forms
	@default field=meta
	@default table=objects
	@default method=serialize
	@groupinfo forms caption=V�ljundid

	@property forms type=relpicker reltype=BACKFORMS
	@caption tagasiside vormid

	@property templates type=select
	@caption templiidid

	@default group=show
	@groupinfo show caption=Visiitkaart submit=no
	@property dokus type=text callback=show_isik


*/

/*

CREATE TABLE `kliendibaas_isik` (
  `oid` int(11) NOT NULL default '0',
  `firstname` varchar(50) default NULL,
  `lastname` varchar(50) default NULL,
  `name` varchar(100) default NULL,
  `gender` varchar(10) default NULL,
  `personal_id` bigint(20) default NULL,
  `title` varchar(10) default NULL,
  `nickname` varchar(20) default NULL,
  `messenger` varchar(200) default NULL,
  `birthday` varchar(20) default NULL,
  `social_status` varchar(20) default NULL,
  `spouse` varchar(50) default NULL,
  `children` varchar(100) default NULL,
  `personal_contact` int(11) default NULL,
  `work_contact` int(11) default NULL,
  `digitalID` text,
  `notes` text,
  `pictureurl` varchar(200) default NULL,
  `picture` blob,
  PRIMARY KEY  (`oid`),
  UNIQUE KEY `oid` (`oid`)
) TYPE=MyISAM;

*/


define ('HOMEADDRESS',1);
define ('WORKADDRESS',2);
define ('PICTURE',3);
define ('BACKFORMS',4);
//define ('TEMPLATES',5);


class isik extends class_base
{

	function isik()
	{
		$this->init(array(
			"tpldir" => "isik",
			'clid' => CL_ISIK,
		));
	}

	function callback_get_rel_types()
	{
		return array(
			HOMEADDRESS => 'Kodune aadress',
			WORKADDRESS => 'T��koha aadress',
			PICTURE => 'Pilt',
			BACKFORMS => 'Tagasiside vorm', //pilootobjekt praegu
//			TEMPLATES => 'Templiit',
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		$retval = false;
                switch($args["reltype"])
                {
			case HOMEADDRESS:
				$retval = array(CL_ADDRESS);
			break;
			case WORKADDRESS:
				$retval = array(CL_ADDRESS);
			break;
			case PICTURE:
				$retval = array(CL_IMAGE);
			break;
			case BACKFORMS:
				$retval = array(CL_PILOT);
			break;
		};
		return $retval;
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
				$tpls = $this->get_directory(array('dir' => $this->cfg['tpldir'].'/isik/visit/'));
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
			case 'forms':
				$data['multiple'] = 1;
			break;
		}
		return $retval;

	}

	function show_isik($args)
	{

		$arg2['id'] = $args['obj'][OID];
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

		if (strlen($obj['meta']['templates']) > 4)
		{
			$this->read_template('visit/'.$obj['meta']['templates']);
		}
		else
		{
			$this->read_template('visit/visiit1.tpl');
		}

		$row = $this->fetch_all_data($id);

		$forms = '';
		if (is_array($this->deafult_forms))
		{
			$obj['meta']['forms'] = array_merge($this->deafult_forms, $obj['meta']['forms']);
		}


		if (is_array($obj['meta']['forms']))
		{
			$obj['meta']['forms'] = array_unique($obj['meta']['forms']);
			foreach($obj['meta']['forms'] as $val)
//			$val = $obj['meta']['forms'];
			{
				if (!$val)
				continue;

				$form = $this->get_object($val);
				$forms.= html::href(array(
				'target' => $form['meta']['open_in_window']? '_blank' : NULL,
				'caption' => $form['name'], 'url' => $this->mk_my_orb('form', array(
					'id' => $form[OID],
					'feedback' => $id,
					'feedback_cl' => rawurlencode('kliendibaas/isik'),
					),'pilot_object'))).'<br />';
			}
		}

		if ($row['picture'])
		{
			$img = get_instance('image');
			$row['picture'] = $img->view(array('id' => $row['picture'], 'height' => '65'));
		}
		else
		{
			$row['picture'] = '';
		}

//		$row['picture']=$row['picture']?html::img(array('src' => $row['picture'])):'';
		//$row['picture'].=$row['pictureurl']?html::img(array('url' => $row['pictureurl'])):'';

		if (($row['lastname'] == '') &&($row['firstname'] == ''))
		{
			$row['firstname'] = $row['name'];
		}

		$row['k_e_mail']=(!empty($row['k_e_mail']))?html::href(array('url' => 'mailto:'.$row['k_e_mail'], 'caption' => $row['k_e_mail'])):'';
		$row['w_e_mail']=(!empty($row['w_e_mail']))?html::href(array('url' => 'mailto:'.$row['w_e_mail'],'caption' => $row['w_e_mail'])):'';
		$row['k_kodulehekylg']=$row['k_kodulehekylg']?html::href(array('url' => $row['k_kodulehekylg'],'caption' => $row['k_kodulehekylg'],'target' => '_blank')):'';
		$row['w_kodulehekylg']=$row['w_kodulehekylg']?html::href(array('url' => $row['w_kodulehekylg'],'caption' => $row['w_kodulehekylg'],'target' => '_blank')):'';
		$row['tagasisidevormid'] = $forms;

		$this->vars($row);

		return $this->parse();
	}


	function fetch_all_data($id)
	{
//vot siuke p�ring, �ra k�si
		return  $this->db_fetch_row("select
			t1.oid as oid,
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
			picture,
			t11.name as k_riik,
			t6.name as k_maakond,
			t7.name as k_linn,
			t8.name as w_maakond,
			t9.name as w_linn,
			t10.name as w_riik,
			t4.name as fnimi,

			t3.postiindeks as k_postiindex,
			t3.aadress as k_aadress,
			t3.telefon as k_telefon,
			t3.mobiil as k_mobiil,
			t3.faks as k_faks,
			t3.e_mail as k_e_mail,
			t3.kodulehekylg as k_kodulehekylg,

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

			left join kliendibaas_maakond as t6 on t6.oid=t3.maakond
			left join kliendibaas_linn as t7 on t7.oid=t3.linn
			left join kliendibaas_riik as t11 on t11.oid=t3.riik
			left join kliendibaas_maakond as t8 on t8.oid=t4.maakond
			left join kliendibaas_linn as t9 on t9.oid=t4.linn
			left join kliendibaas_riik as t10 on t10.oid=t4.riik

			where t1.oid=".$id);

	//left join images as t5 on t2.picture=t5.id
//			t5.link as picture,
	}

	////
	// !callback, used by selection
	// id - object to show
	function show_in_selection($args)
	{
		return $this->show(array('id' => $args['id']));
	}

	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}

}
?>
