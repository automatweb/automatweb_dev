<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/transport_management/crm_transport_management.aw,v 1.1 2006/06/15 12:35:26 dragut Exp $
// transport_management.aw - Veotellimuste haldus 
/*

@classinfo syslog_type=ST_TRANSPORT_MANAGEMENT relationmgr=yes no_status=1 prop_cb=1

@default table=objects
@default group=general

@property manager_org type=relpicker reltype=RELTYPE_MANAGER_ORG
@caption Haldaja organisatsioon

@property config_manager type=relpicker reltype=RELTYPE_CONFIG_MANAGER
@caption Seadete haldur

@property foo type=textbox
@caption Ekspediitorite ametinimetused

@property bar type=textbox
@caption Juhtide ametinimetused

@groupinfo orders caption="Tellimused"

	@property orders_toolbar type=toolbar no_caption=1 group=orders_status,orders_routes,orders_clients,orders_dispatchers,my_orders
	@caption Staatuste t&ouml;&ouml;riistariba

	@groupinfo orders_status caption="Staatused" parent=orders

		@layout orders_status_frame type=hbox width=20%:80% group=orders_status

			@layout orders_status_left type=vbox parent=orders_status_frame group=orders_status

				@property orders_status_tree type=treeview store=no parent=orders_status_left captionside=top group=orders_status
				@caption Staatused

			@layout orders_status_right type=vbox parent=orders_status_frame group=orders_status

				@layout orders_status_search type=hbox parent=orders_status_right group=orders_status

					@property orders_status_search_route type=textbox store=no parent=orders_status_search group=orders_status
					@caption Marsruut

					@property orders_status_search_car_trailer type=textbox store=no parent=orders_status_search group=orders_status
					@caption Auto/Treiler


				@property orders_status_table type=table parent=orders_status_right no_caption=1 group=orders_status
				@caption Staatuste tabel

	@groupinfo orders_routes caption="Marsruudid" parent=orders

		@layout orders_routes_frame type=hbox width=20%:80% group=orders_routes

			@layout orders_routes_left type=vbox parent=orders_routes_frame group=orders_routes

				@property orders_routes_tree type=treeview store=no parent=orders_routes_left captionside=top group=orders_routes
				@caption Marsruudid

			@layout orders_routes_right type=vbox parent=orders_routes_frame group=orders_routes

				@property orders_routes_table type=table parent=orders_routes_right no_caption=1 group=orders_routes
				@caption Marsruutide tabel

	@groupinfo orders_clients caption="Kliendid" parent=orders

		@layout orders_clients_frame type=hbox width=20%:80% group=orders_clients

			@layout orders_clients_left type=vbox parent=orders_clients_frame group=orders_clients

				@property orders_clients_tree type=treeview store=no parent=orders_clients_left captionside=top group=orders_clients
				@caption Kliendid

			@layout orders_clients_right type=vbox parent=orders_clients_frame group=orders_clients

				@property orders_clients_table type=table parent=orders_clients_right no_caption=1 group=orders_clients
				@caption Klientide tabel

	@groupinfo orders_dispatchers caption="Ekspediitorid" parent=orders

		@layout orders_dispatchers_frame type=hbox width=20%:80% group=orders_dispatchers

			@layout orders_dispatchers_left type=vbox parent=orders_dispatchers_frame group=orders_dispatchers

				@property orders_dispatchers_tree type=treeview store=no parent=orders_dispatchers_left captionside=top group=orders_dispatchers
				@caption Ekspediitorid

			@layout orders_dispatchers_right type=vbox parent=orders_dispatchers_frame group=orders_dispatchers

				@property orders_dispatchers_table type=table parent=orders_dispatchers_right no_caption=1 group=orders_dispatchers
				@caption Ekspediitorite tabel

@groupinfo routes caption="Marsruudid"

	@property routes_toolbar type=toolbar no_caption=1 group=routes
	@caption Marsruutide t&ouml;&ouml;riistariba

	@property routes_search_address type=textbox store=no group=routes
	@caption Otsi aadressi

	@property routes_table type=table no_caption=1 group=routes
	@caption Marsruutide tabel

@groupinfo carriages caption="Veod"

	@property carriages_toolbar type=toolbar no_caption=1 group=carriages,my_carriages
	@caption Vedude t&ouml;&ouml;riistariba

	@property carriages_search_client type=textbox store=no group=carriages
	@caption Otsi kliendi j&auml;rgi

	@property carriages_table type=table no_caption=1 group=carriages
	@caption Vedude tabel

@groupinfo my_desktop caption="Minu T&ouml;&ouml;laud"

	@groupinfo my_clients caption="Kliendid" parent=my_desktop

		@property foo type=text store=no group=my_clients
		@caption foo

	@groupinfo my_orders caption="Tellimused" parent=my_desktop

		@layout my_orders_frame type=hbox width=20%:80% group=my_orders

			@layout my_orders_left type=vbox parent=my_orders_frame group=my_orders

				@property my_orders_tree type=treeview store=no parent=my_orders_left captionside=top group=my_orders
				@caption Staatused

			@layout my_orders_right type=vbox parent=my_orders_frame group=my_orders

				@layout my_orders_search type=hbox parent=my_orders_right group=my_orders

					@property my_orders_search_route type=textbox store=no parent=my_orders_search group=my_orders
					@caption Marsruut

					@property my_orders_search_car_trailer type=textbox store=no parent=my_orders_search group=my_orders
					@caption Auto/Treiler


				@property my_orders_table type=table parent=my_orders_right no_caption=1 group=my_orders
				@caption Staatuste tabel


	@groupinfo my_carriages caption="Veod" parent=my_desktop

		property my_carriages_toolbar type=toolbar no_caption=1 group=my_carriages
		caption Vedude t&ouml;&ouml;riistariba

		@property my_carriages_search_client type=textbox store=no group=my_carriages
		@caption Otsi kliendi j&auml;rgi

		@property my_carriages_table type=table no_caption=1 group=my_carriages
		@caption Vedude tabel


@reltype MANAGER_ORG value=1 clid=CL_CRM_COMPANY
@caption Haldaja organisatsioon

@reltype CONFIG_MANAGER value=1 clid=CL_CFGMANAGER
@caption Seadete haldur

*/

define('STATUS_NEW', 1);
define('STATUS_PLANNED', 2);
define('STATUS_ON_THE_ROAD', 3);
define('STATUS_OVER_DEADLINE', 4);
define('STATUS_CANCELED', 5);
define('STATUS_COMPLETED', 6);
define('STATUS_ARCHIVED', 7);

class crm_transport_management extends class_base
{

	var $status_array = array();

	function crm_transport_management()
	{
		$this->init(array(
			"tpldir" => "applications/crm/transport_management/crm_transport_management",
			"clid" => CL_CRM_TRANSPORT_MANAGEMENT
		));

		$this->status_array = array(
			STATUS_NEW => t('Uued'),
			STATUS_PLANNED => t('Planeeritud'),
			STATUS_ON_THE_ROAD => t('Hetkel vedamisel'),
			STATUS_OVER_DEADLINE => t('&Uuml;le t&auml;htaja'),
			STATUS_CANCELED => t('Katkestatud'),
			STATUS_COMPLETED => t('Valmis'),
			STATUS_ARCHIVED => t('Arhiveeritud')
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
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

	function _get_orders_toolbar($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Uus tellimus"),
			"url" => $this->mk_my_orb("do_something",array()),
		));
		$t->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta tellimus"),
			"url" => $this->mk_my_orb("do_something",array()),
		));
		$t->add_button(array(
			"name" => "archive",
			"img" => "delete.gif",
			"tooltip" => t("Lisa tellimus arhiivi"),
			"url" => $this->mk_my_orb("do_something",array()),
		));
		return PROP_OK;
	}

	function _get_orders_status_tree($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->start_tree(array(
			'type' => TREE_DHTML,
			'root_name' => 'orders_status_tree',
			'root_url' => 'http://www.neti.ee',
		));
		foreach ( $this->status_array as $status_key => $status_value )
		{
			$t->add_item(0,array(
				"id" => $status_key,
				"name" => $status_value,
			//	"iconurl" => "",
				"url" => $this->mk_my_orb("do_something",array()),
			));

		}
		return PROP_OK;
	}

	function _get_orders_status_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->define_field(array(
			'name' => 'client',
			'caption' => t('Klient'),
		));
		$t->define_field(array(
			'name' => 'project_name',
			'caption' => t('Projekti nimetus'),
		));
		$t->define_field(array(
			'name' => 'start_location',
			'caption' => t('L&auml;htekoht'),
		));
		$t->define_field(array(
			'name' => 'end_location',
			'caption' => t('Sihtkoht'),
		));
		$t->define_field(array(
			'name' => 'deadline',
			'caption' => t('T&auml;htaeg'),
		));
		$t->define_field(array(
			'name' => 'amount',
			'caption' => t('Kogus'),
		));

		return PROP_OK;
	}

	function _get_orders_routes_tree($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->start_tree(array(
			'type' => TREE_DHTML,
			'root_name' => 'orders_routes_tree',
		//	'root_url' => 'http://www.neti.ee',
		));
		$t->add_item(0, array(
			'id' => 'foobar',
			'name' => 'Marsruutide nimekiri'
		));
/*
		foreach ( $this->status_array as $status_key => $status_value )
		{
			$t->add_item(0,array(
				"id" => $status_key,
				"name" => $status_value,
				"url" => $this->mk_my_orb("do_something",array()),
			));

		}
*/
		return PROP_OK;
	}

	function _get_orders_routes_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->define_field(array(
			'name' => 'client',
			'caption' => t('Klient'),
		));
		$t->define_field(array(
			'name' => 'project_name',
			'caption' => t('Projekti nimetus'),
		));
		$t->define_field(array(
			'name' => 'start_location',
			'caption' => t('L&auml;htekoht'),
		));
		$t->define_field(array(
			'name' => 'end_location',
			'caption' => t('Sihtkoht'),
		));
		$t->define_field(array(
			'name' => 'deadline',
			'caption' => t('T&auml;htaeg'),
		));
		$t->define_field(array(
			'name' => 'amount',
			'caption' => t('Kogus'),
		));
		$t->define_field(array(
			'name' => 'status',
			'caption' => t('Staatus'),
		));
		$t->define_field(array(
			'name' => 'carriage',
			'caption' => t('Vedu'),
		));


		return PROP_OK;
	}

	function _get_orders_clients_tree($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->start_tree(array(
			'type' => TREE_DHTML,
			'root_name' => 'orders_clients_tree',
		//	'root_url' => 'http://www.neti.ee',
		));
		$t->add_item(0, array(
			'id' => 'foobar',
			'name' => 'Klientide nimekiri'
		));
/*
		foreach ( $this->status_array as $status_key => $status_value )
		{
			$t->add_item(0,array(
				"id" => $status_key,
				"name" => $status_value,
				"url" => $this->mk_my_orb("do_something",array()),
			));

		}
*/
		return PROP_OK;
	}

	function _get_orders_clients_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->define_field(array(
			'name' => 'project_name',
			'caption' => t('Projekti nimetus'),
		));
		$t->define_field(array(
			'name' => 'start_location',
			'caption' => t('L&auml;htekoht'),
		));
		$t->define_field(array(
			'name' => 'end_location',
			'caption' => t('Sihtkoht'),
		));
		$t->define_field(array(
			'name' => 'deadline',
			'caption' => t('T&auml;htaeg'),
		));
		$t->define_field(array(
			'name' => 'amount',
			'caption' => t('Kogus'),
		));
		$t->define_field(array(
			'name' => 'status',
			'caption' => t('Staatus'),
		));
		$t->define_field(array(
			'name' => 'income',
			'caption' => t('Tulu'),
		));


		return PROP_OK;
	}

	function _get_orders_dispatchers_tree($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->start_tree(array(
			'type' => TREE_DHTML,
			'root_name' => 'orders_dispatchers_tree',
		//	'root_url' => 'http://www.neti.ee',
		));
		$t->add_item(0, array(
			'id' => 'foobar',
			'name' => 'Ekspediitorite nimekiri'
		));
/*
		foreach ( $this->status_array as $status_key => $status_value )
		{
			$t->add_item(0,array(
				"id" => $status_key,
				"name" => $status_value,
				"url" => $this->mk_my_orb("do_something",array()),
			));

		}
*/
		return PROP_OK;
	}

	function _get_orders_dispatchers_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->define_field(array(
			'name' => 'client',
			'caption' => t('Klient'),
		));
		$t->define_field(array(
			'name' => 'project_name',
			'caption' => t('Projekti nimetus'),
		));
		$t->define_field(array(
			'name' => 'start_location',
			'caption' => t('L&auml;htekoht'),
		));
		$t->define_field(array(
			'name' => 'end_location',
			'caption' => t('Sihtkoht'),
		));
		$t->define_field(array(
			'name' => 'deadline',
			'caption' => t('T&auml;htaeg'),
		));
		$t->define_field(array(
			'name' => 'amount',
			'caption' => t('Kogus'),
		));
		$t->define_field(array(
			'name' => 'status',
			'caption' => t('Staatus'),
		));
		$t->define_field(array(
			'name' => 'income',
			'caption' => t('Tulu'),
		));


		return PROP_OK;
	}

	function _get_routes_toolbar($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Uus marsruut"),
			"url" => $this->mk_my_orb("do_something",array()),
		));
		$t->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta marsruut"),
			"url" => $this->mk_my_orb("do_something",array()),
		));
		return PROP_OK;
	}

	function _get_routes_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->define_field(array(
			'name' => 'name',
			'caption' => t('Nimetus')
		));
		$t->define_field(array(
			'name' => 'start_location',
			'caption' => t('L&auml;htekoht'),
		));
		$t->define_field(array(
			'name' => 'end_location',
			'caption' => t('Sihtkoht'),
		));
		$t->define_field(array(
			'name' => 'cars_trailers',
			'caption' => t('Autod/Haagised')
		));
	}

	function _get_carriages_toolbar($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Uus vedu"),
			"url" => $this->mk_my_orb("do_something",array()),
		));
		$t->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta vedu"),
			"url" => $this->mk_my_orb("do_something",array()),
		));
		return PROP_OK;
	}

	function _get_carriages_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->define_field(array(
			'name' => 'route',
			'caption' => t('Marsruut')
		));
		$t->define_field(array(
			'name' => 'dispatcher',
			'caption' => t('Ekspediitor')
		));
		$t->define_field(array(
			'name' => 'start_date',
			'caption' => t('Alguskuup&auml;ev'),
		));
		$t->define_field(array(
			'name' => 'end_date',
			'caption' => t('L&otilde;ppkuup&auml;ev'),
		));
		$t->define_field(array(
			'name' => 'start_location',
			'caption' => t('L&auml;htekoht'),
		));
		$t->define_field(array(
			'name' => 'end_location',
			'caption' => t('Sihtkoht'),
		));
		$t->define_field(array(
			'name' => 'cars_trailers',
			'caption' => t('Autod/Haagised')
		));
		$t->define_field(array(
			'name' => 'status',
			'caption' => t('Staatus')
		));
		$t->define_field(array(
			'name' => 'income',
			'caption' => t('Tulu')
		));
	}


	// xxx ???
	// äkki saab näidata lihtsalt seda orders toolbari siin ka ...
	function _get_my_orders_toolbar($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Uus tellimus"),
			"url" => $this->mk_my_orb("do_something",array()),
		));
		$t->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta tellimus"),
			"url" => $this->mk_my_orb("do_something",array()),
		));
		$t->add_button(array(
			"name" => "archive",
			"img" => "delete.gif",
			"tooltip" => t("Lisa tellimus arhiivi"),
			"url" => $this->mk_my_orb("do_something",array()),
		));
		return PROP_OK;
	}

	function _get_my_orders_tree($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->start_tree(array(
			'type' => TREE_DHTML,
			'root_name' => 'my_orders_tree',
		));
		foreach ( $this->status_array as $status_key => $status_value )
		{
			$t->add_item(0,array(
				"id" => $status_key,
				"name" => $status_value,
			//	"iconurl" => "",
				"url" => $this->mk_my_orb("do_something",array()),
			));

		}
		return PROP_OK;
	}

	function _get_my_orders_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->define_field(array(
			'name' => 'client',
			'caption' => t('Klient'),
		));
		$t->define_field(array(
			'name' => 'project_name',
			'caption' => t('Projekti nimetus'),
		));
		$t->define_field(array(
			'name' => 'start_location',
			'caption' => t('L&auml;htekoht'),
		));
		$t->define_field(array(
			'name' => 'end_location',
			'caption' => t('Sihtkoht'),
		));
		$t->define_field(array(
			'name' => 'deadline',
			'caption' => t('T&auml;htaeg'),
		));
		$t->define_field(array(
			'name' => 'amount',
			'caption' => t('Kogus'),
		));

		return PROP_OK;
	}
/*
	// xxx --showing the carriages toolbar here
	function _get_my_carriages_toolbar($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Uus vedu"),
			"url" => $this->mk_my_orb("do_something",array()),
		));
		$t->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta vedu"),
			"url" => $this->mk_my_orb("do_something",array()),
		));
		return PROP_OK;
	}
*/
	function _get_my_carriages_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->define_field(array(
			'name' => 'route',
			'caption' => t('Marsruut')
		));
		$t->define_field(array(
			'name' => 'start_date',
			'caption' => t('Alguskuup&auml;ev'),
		));
		$t->define_field(array(
			'name' => 'end_date',
			'caption' => t('L&otilde;ppkuup&auml;ev'),
		));
		$t->define_field(array(
			'name' => 'start_location',
			'caption' => t('L&auml;htekoht'),
		));
		$t->define_field(array(
			'name' => 'end_location',
			'caption' => t('Sihtkoht'),
		));
		$t->define_field(array(
			'name' => 'cars_trailers',
			'caption' => t('Autod/Haagised')
		));
		$t->define_field(array(
			'name' => 'status',
			'caption' => t('Staatus')
		));
		$t->define_field(array(
			'name' => 'income',
			'caption' => t('Tulu')
		));
	}



}
?>
