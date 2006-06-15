<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/transport_management/crm_transport_management_carriage_order.aw,v 1.2 2006/06/15 18:19:16 dragut Exp $
// carriage_order.aw - Veotellimus 
/*

@classinfo syslog_type=ST_CARRIAGE_ORDER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@tableinfo crm_transport_management_carriage_order index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

	@property date type=date_select table=crm_transport_management_carriage_order
	@caption Koostamise kuup&auml;ev

	@property location type=textbox table=crm_transport_management_carriage_order
	@caption Koostamise koht

	@property orderer type=relpicker reltype=RELTYPE_ORDERER table=crm_transport_management_carriage_order
	@caption Tellija

	@property dispatcher type=relpicker reltype=RELTYPE_DISPATCHER table=crm_transport_management_carriage_order
	@caption Ekspediitor

	@property deadline type=date_select table=crm_transport_management_carriage_order
	@caption T&auml:htaeg

	@property carriage_order_status type=select store=no 
	@caption Staatus

	@property sender type=relpicker reltype=RELTYPE_SENDER table=crm_transport_management_carriage_order
	@caption Saatja

	@property receiver type=relpicker reltype=RELTYPE_RECEIVER table=crm_transport_management_carriage_order
	@caption Saaja

	@property unloading_location type=relpicker reltype=RELTYPE_UNLOADING_LOCATION table=crm_transport_management_carriage_order
	@caption Mahalaadimiskoht

	@property unloading_note type=textarea table=crm_transport_management_carriage_order
	@caption M&auml;rkus

	@property loading_location type=relpicker reltype=RELTYPE_LOADING_LOCATION table=crm_transport_management_carriage_order
	@caption Pealelaadimiskoht

	@property loading_note type=textarea table=crm_transport_management_carriage_order
	@caption M&auml;rkus

	@property added_documents type=textarea table=crm_transport_management_carriage_order
	@caption Lisatud dokumendid

	@property transporter type=relpicker reltype=RELTYPE_TRANSPORTER table=crm_transport_management_carriage_order
	@caption Vedaja

	@property next_transporter type=relpicker reltype=RELTYPE_NEXT_TRANSPORTER table=crm_transport_management_carriage_order
	@caption Vedaja

	@property transporter_note type=textarea table=crm_transport_management_carriage_order
	@caption Vedaja m&auml;rkused

	@property carriage type=relpicker reltype=RELTYPE_CARRIAGE table=crm_transport_management_carriage_order
	@caption Veo nr.

	@property truck type=text store=no
	@captio Auto nr.

	@property trailer type=text store=no
	@captio Haagise nr.

	@property driver type=text store=no
	@captio Juht

@reltype ORDERER value=1 clid=CL_CRM_COMPANY
@caption Tellija

@reltype SENDER value=2 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Saatja

@reltype RECEIVER value=3 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Saaja

@reltype RECEIVER value=4 clid=CL_CRM_ADDRESS
@caption Mahalaadimiskoht

@reltype UNLOADING_LOCATION value=5 clid=CL_CRM_ADDRESS
@caption Mahalaadimiskoht

@reltype LOADING_LOCATION value=6 clid=CL_CRM_ADDRESS
@caption Mahalaadimiskoht

@reltype TRANSPORTER value=7 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Vedaja

@reltype NEXT_TRANSPORTER value=8 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption J&auml;rgmine vedaja

@reltype CARRIAGE value=9 clid=CL_CRM_TRANSPORT_MANAGEMENT_CARRIAGE
@caption Vedu
*/

define('CARRIAGE_ORDER_STATUS_NEW', 1);

class crm_transport_management_carriage_order extends class_base
{

	var $carriage_order_status = array();

	function crm_transport_management_carriage_order()
	{
		$this->init(array(
			"tpldir" => "applications/crm/transport_management/crm_transport_management_carriage_order",
			"clid" => CL_CRM_TRANSPORT_MANAGEMENT_CARRIAGE_ORDER
		));

		$this->carriage_order_status = array(
			CARRIAGE_ORDER_STATUS_NEW => t('Uus')
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case 'carriage_order_status':
				$prop['options'] = $this->carriage_order_status;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function do_db_upgrade($table, $field, $query, $error)
	{
		if (empty($field))
		{
			$this->db_query('CREATE TABLE '.$table.' (oid INT PRIMARY KEY NOT NULL)');
			return true;
		}

		switch ($field)
		{
			case 'date':
			case 'orderer':
			case 'dispatcher':
			case 'deadline':
			case 'sender':
			case 'receiver':
			case 'unloading_location':
			case 'loading_location':
			case 'transporter':
			case 'next_transporter':
			case 'carriage':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'int'
				));
                                return true;

			case 'location':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'varchar(255)'
				));
                                return true;

			case 'unloading_note':
			case 'loading_note':
			case 'added_documents':
			case 'transporter_note':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'text'
				));
                                return true;
                }

		return false;
	}

}
?>
