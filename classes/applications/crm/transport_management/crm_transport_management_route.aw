<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/transport_management/crm_transport_management_route.aw,v 1.2 2006/06/15 15:17:23 dragut Exp $
// route.aw - Marsruut 
/*

@classinfo syslog_type=ST_ROUTE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@tableinfo crm_transport_management_route index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

	@property start_location type=relpicker reltype=RELTYPE_START_LOCATION table=crm_transport_management_route
	@caption Alguspunkt

	@property end_location type=relpicker reltype=RELTYPE_END_LOCATION table=crm_transport_management_route
	@caption Sihtpunkt

	@property route_status type=select table=crm_transport_management_route
	@caption Staatus

@groupinfo route_content caption="Marsruudi sisu"
@default group=route_content

	@layout route_content_frame type=hbox width=20%:80% 
	
		@layout route_content_left type=vbox parent=route_content_frame
		
			@property route_tree type=treeview parent=route_content_left captionside=top
			@caption Puu

		@layout route_content_right type=vbox parent=route_content_frame

			@property route_table type=table parent=route_content_right captionside=top
			@caption Tabel

@reltype START_LOCATION value=1 clid=CL_CRM_CITY
@caption Alguspunkt

@reltype END_LOCATION value=1 clid=CL_CRM_CITY
@caption Sihtpunkt

*/

define('ROUTE_STATUS_ACTIVE', 1);
define('ROUTE_STATUS_ARCHIVED', 2);

class crm_transport_management_route extends class_base
{

	var $route_status = array();

	function crm_transport_management_route()
	{
		$this->init(array(
			"tpldir" => "applications/crm/transport_management/crm_transport_management_route",
			"clid" => CL_CRM_TRANSPORT_MANAGEMENT_ROUTE
		));

		$this->route_status = array(
			ROUTE_STATUS_ACTIVE => t('Aktiivne'),
			ROUTE_STATUS_ARCHIVED => t('Arhiveeritud')
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case 'route_status':
				$prop['options'] = $this->route_status;
				break;
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

	function _get_route_tree($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
                $t->start_tree(array(
                        'type' => TREE_DHTML,
                        'root_name' => 'routes_tree',
                ));
                $t->add_item(0, array(
                        'id' => 'foobar',
                        'name' => 'Asukohad'
                ));

		return PROP_OK;
	}

	function _get_route_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->set_sortable(false);
		$t->define_field(array(
			'name' => 'name',
			'caption' => t('Nimi')
		));
		return PROP_OK;
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
			case 'start_location':
			case 'end_location':
			case 'route_status':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'int'
				));
                                return true;
                }

		return false;
	}
}
?>
