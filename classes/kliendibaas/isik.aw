<?php
/*
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

	@property gender type=textbox size=5 maxlength=10
	@caption sugu

	@property personal_id type=textbox size=10 maxlength=15
	@caption isikukood

	@property title type=textbox size=5 maxlength=10
	@caption tiitel

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

	@property work_contact type=relpicker reltype=WORKADDRESS
	@caption töökoha kontakt andmed

	@property personal_contact type=relpicker reltype=HOMEADDRESS
	@caption kodused kontakt andmed
*/

define ('HOMEADDRESS',1);
define ('WORKADDRESS',2);
define ('PICTURE',3);

class isik extends class_base
{

	function callback_get_rel_types()
	{
		return array(
			HOMEADDRESS => 'kodune aadress',
			WORKADDRESS => 'töökoha aadress',
			PICTURE => 'pilt',
		);
	}

	function isik()
	{
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
		$retval = true;

		switch($data["name"])
		{
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
