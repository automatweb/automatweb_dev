<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/watercraft_management/watercraft.aw,v 1.5 2006/09/25 12:41:11 dragut Exp $
// watercraft.aw - Veesõiduk 
/*

@classinfo syslog_type=ST_WATERCRAFT relationmgr=yes no_comment=1 no_status=1 prop_cb=1
@tableinfo watercraft index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

	@property deal_type type=select table=watercraft
	@caption Tehingu t&uuml;&uuml;p

	@property watercraft_type type=select table=watercraft
	@caption Aluse t&uuml;&uuml;p

	@property watercraft_type_other type=textbox table=watercraft
	@caption Muu aluset&uuml;&uuml;p

	@property watercraft_accessories type=chooser orient=vertical multiple=1 field=meta method=serialize
	@caption Varustus/tarvikud (hetkel ei salvestu)

	@property manufacturer type=select table=watercraft
	@caption Tootja 

	@property brand type=textbox table=watercraft
	@caption Mark

	@property body_material type=select table=watercraft
	@caption Kerematerjal

	@property body_material_other type=textbox table=watercraft
	@caption Muu kerematerjal

	property location type=relpicker reltype=RELTYPE_LOCATION automatic=1 table=watercraft
	caption Asukoht (praegu v&otilde;etakse vaikimisi k&otilde;ik s&uuml;steemis olevad linnad)

	@property location type=select table=watercraft
	@caption Asukoht 

	@property location_other type=textbox table=watercraft
	@caption Muu asukoht

	@property condition type=select table=watercraft
	@caption Seisukord

	@property condition_info type=textbox table=watercraft
	@caption Lisainfo seisukorra kohta

	@property seller type=relpicker reltype=RELTYPE_SELLER table=watercraft
	@caption M&uuml;&uuml;ja

	@property price type=textbox table=watercraft
	@caption Hind

	@property visible type=checkbox ch_value=1 table=watercraft
	@caption N&auml;htav

	@property archived type=checkbox ch_value=1 table=watercraft
	@caption Arhiveeritud

@groupinfo images caption="Pildid"
@default group=images

	@property images_toolbar type=toolbar no_caption=1
	@caption Piltide t&ouml;&ouml;riistariba

	@property images_table type=table no_caption=1
	@caption Pildid

@groupinfo parameters caption="Parameetrid"
@default group=parameters

	@property centreboard type=chooser orient=vertical multiple=1 store=no
	@caption Kiil/Svert (hetkel ei salvestu)

	@property length type=textbox table=watercraft
	@caption Pikkus (m)

	@property width type=textbox table=watercraft
	@caption Laius (m)
	
	@property height type=textbox table=watercraft
	@caption K&otilde;rgus (m)

	@property weight type=textbox table=watercraft
	@caption Raskus (kg)

	@property draught type=textbox table=watercraft
	@caption S&uuml;vis (cm)

	@property creation_year type=select table=watercraft
	@caption Valmistamisaasta

	@property passanger_count type=select table=watercraft
	@caption Reisijaid

	@property sleeper_count type=select table=watercraft
	@caption Magamiskohti

@groupinfo engines caption="Mootor(id)"
@default group=engines

	@property engine_manufacturer type=textbox table=watercraft
	@caption Tootja

	@property engine_model type=textbox table=watercraft
	@caption Mudel
	
	@property engine_count type=select table=watercraft
	@caption Mootorite arv

	@property engine_type type=select table=watercraft
	@caption T&uuml;&uuml;p

	@property engine_capacity type=textbox table=watercraft
	@caption T&ouml;&ouml;maht (cm3)

	@property fuel_tank type=textbox table=watercraft
	@caption K&uuml;tusepaak (l)
	
	@property fuel type=select table=watercraft
	@caption K&uuml;tus

	@property engine_power type=textbox table=watercraft
	@caption V&otilde;imsus

	@property engine_cooling type=select table=watercraft
	@caption Jahutus

@groupinfo mast caption="Mast(id)"
@default group=mast

	@property mast_material type=select table=watercraft
	@caption Materjal

	@property mast_material_other type=textbox table=watercraft
	@caption Muu materjal

	@property mast_count type=select table=watercraft
	@caption Mastide arv

@groupinfo sail caption="Purjed"
@default group=sail

	@property sail_table type=table
	@caption Purjed

	@property sail_info type=textarea rows=10 cols=80 table=watercraft
	@caption Lisainfo

@groupinfo additional_equipment caption="Lisavarustus"
@default group=additional_equipment

	@property additional_equipment_table type=table no_caption=1
	@caption Lisavarustus

	@property additional_equipment_info type=textarea table=watercraft
	@caption T&auml;iendav info

@reltype LOCATION value=1 clid=CL_CRM_CITY
@caption Asukoht

@reltype SELLER value=2 clid=CL_CRM_PERSON,CL_CRM_COMPANY
@caption M&uuml;&uuml;ja

*/

define('DEAL_TYPE_SALE', 1);
define('DEAL_TYPE_LEASE', 2);
define('DEAL_TYPE_BUY', 3);

define('WATERCRAFT_TYPE_MOTOR_BOAT', 1);
define('WATERCRAFT_TYPE_SAILING_SHIP', 2);
define('WATERCRAFT_TYPE_DINGHY', 3);
define('WATERCRAFT_TYPE_ROWING_BOAT', 4);
define('WATERCRAFT_TYPE_SCOOTER', 5);
define('WATERCRAFT_TYPE_SAILBOARD', 6);
define('WATERCRAFT_TYPE_CANOE', 7);
define('WATERCRAFT_TYPE_FISHING_BOAT', 8);
define('WATERCRAFT_TYPE_OTHER', 9);
define('WATERCRAFT_TYPE_ACCESSORIES', 10);

define('ACCESSORIES_ACCESSORY', 1);
define('ACCESSORIES_ENGINE', 2);
define('ACCESSORIES_SAIL', 3);
define('ACCESSORIES_MAST', 4);

define('BODY_MATERIAL_WOOD', 1);
define('BODY_MATERIAL_STEEL', 2);
define('BODY_MATERIAL_ALUMINUM', 3);
define('BODY_MATERIAL_PLASTIC', 4);
define('BODY_MATERIAL_FIBERGLASS', 5);
define('BODY_MATERIAL_OTHER', 6);

define('CONDITION_NEW', 1);
define('CONDITION_GOOD', 2);
define('CONDITION_LITTLE_USED', 3);
define('CONDITION_USED', 4);
define('CONDITION_NEEDS_REPAIR', 5);

define('CENTREBOARD_1');
define('CENTREBOARD_2');

define('ENGINE_TYPE_2_TACT', 1);
define('ENGINE_TYPE_4_TACT', 2);

define('FUEL_PETROL', 1);
define('FUEL_DIESEL', 2);

define('ENGINE_COOLING_SEA_WATER', 1);
define('ENGINE_COOLING_FRESH_WATER', 2);

define('MAST_MATERIAL_WOOD', 1);
define('MAST_MATERIAL_ALUMINIUM', 2);
define('MAST_MATERIAL_PLASTIC', 3);
define('MAST_MATERIAL_OTHER', 4);

class watercraft extends class_base
{
	var $deal_type;
	var $watercraft_type;
	var $accessories;
	var $body_material;
	var $condition;
	var $centreboard;
	var $engine_type;
	var $fuel;
	var $engine_cooling;
	var $mast_material;

	function watercraft()
	{
		$this->init(array(
			"tpldir" => "applications/watercraft_management/watercraft",
			"clid" => CL_WATERCRAFT
		));

		$this->deal_type = array(
			DEAL_TYPE_SALE => t('M&uuml;&uuml;k'),
			DEAL_TYPE_LEASE => t('Rent'),
			DEAL_TYPE_BUY => t('Ost')
		);

		$this->watercraft_type = array(
			WATERCRAFT_TYPE_MOTOR_BOAT => t('Mootorpaat'),
			WATERCRAFT_TYPE_SAILING_SHIP => t('Purjekas'),
			WATERCRAFT_TYPE_DINGHY => t('Kummipaat'),
			WATERCRAFT_TYPE_ROWING_BOAT => t('S&otilde;udepaat'),
			WATERCRAFT_TYPE_SCOOTER => t('Skuuter'),
			WATERCRAFT_TYPE_SAILBOARD => t('Purilaud'),
			WATERCRAFT_TYPE_CANOE => t('Kanuu'),
			WATERCRAFT_TYPE_FISHING_BOAT => t('Kalapaat'),
			WATERCRAFT_TYPE_OTHER => t('Muu'),
			WATERCRAFT_TYPE_ACCESSORIES => t('Varustus/tarvikud')
		);

		$this->accessories = array(
			ACCESSORIES_ACCESSORY => t('Lisavarustus'),
			ACCESSORIES_ENGINE => t('Mootor'),
			ACCESSORIES_SAIL => t('Purjed'),
			ACCESSORIES_MAST => t('Mast')
		);

		$this->body_material = array(
			BODY_MATERIAL_WOOD => t('Puit'),
			BODY_MATERIAL_STEEL => t('Teras'),
			BODY_MATERIAL_ALUMINUM => t('Alumiinium'),
			BODY_MATERIAL_PLASTIC => t('Plastik'),
			BODY_MATERIAL_FIBERGLASS => t('Klaaskiud'),
			BODY_MATERIAL_OTHER => t('Muu')
		);

		$this->condition = array(
			CONDITION_NEW => t('Uus'),
			CONDITION_GOOD => t('Heas korras'),
			CONDITION_LITTLE_USED => t('V&auml;he kasutatud'),
			CONDITION_USED => t('Kasutatud'),
			CONDITION_NEEDS_REPAIR => t('Vajab remonti')
		);
		
		$this->centreboard = array(
			CENTREBOARD_1 => t('Kiil'),
			CENTREBOARD_2 => t('Svert'),
		);

		$this->engine_type = array(
			ENGINE_TYPE_2_TACT => t('2-taktiline'),
			ENGINE_TYPE_4_TACT => t('4-taktiline')
		);

		$this->fuel = array(
			FUEL_PETROL => t('Bensiin'),
			FUEL_DIESEL => t('Diisel')
		);

		$this->engine_cooling = arraY(
			ENGINE_COOLING_SEA_WATER => t('Merevesi'),
			ENGINE_COOLING_FRESH_WATER => t('Magevesi')
		);

		$this->mast_material = array(
			MAST_MATERIAL_WOOD => t('Puit'),
			MAST_MATERIAL_ALUMINIUM => t('Alumiinium'),
			MAST_MATERIAL_PLASTIC => t('Plastik'),
			MAST_MATERIAL_OTHER => t('Muu materjal'),
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case 'deal_type':
				$prop['options'] = $this->deal_type;
				break;
			case 'watercraft_type':
				$prop['options'] = $this->watercraft_type;
				break;
			case 'watercraft_type_other':
				if ($arr['obj_inst']->prop('watercraft_type') != WATERCRAFT_TYPE_OTHER)
				{
					$retval = PROP_IGNORE;
				}
				break;
			case 'watercraft_accessories':
				if ($arr['obj_inst']->prop('watercraft_type') != WATERCRAFT_TYPE_ACCESSORIES)
				{
					$retval = PROP_IGNORE;
				}
				else
				{
					$prop['options'] = $this->accessories;
				}
				break;
			case 'manufacturer':
				$management = $this->get_management_object($arr);
				$prop['options'][] = t('--Vali--');
				if ( $management !== false )
				{
					$manufacturers = new object_list(array(
						'class_id' => CL_CRM_COMPANY,
						'parent' => $management->prop('manufacturers')
					));
					foreach ($manufacturers->arr() as $id => $manufacturer)
					{
						$prop['options'][$id] = $manufacturer->name();
					}
				}
				break;
			case 'location':
				$management = $this->get_management_object($arr);
				$prop['options'][] = t('--Vali--');
				if ( $management !== false )
				{
					$locations = new object_list(array(
						'class_id' => CL_CRM_ADDRESS,
						'parent' => $management->prop('locations')
					));
					foreach ($locations->arr() as $id => $location)
					{
						$prop['options'][$id] = $location->name();
					}
				}
				// xxx i don't know if this is necessary here, so i will comment out it now --dragut
				//$prop['options'][-1] = t('Muu asukoht');
				break;
			case 'location_other':
				if ($arr['obj_inst']->prop('location') > 0)
				{
					$retval = PROP_IGNORE;
				}
				break;
			case 'body_material':
				$prop['options'] = $this->body_material;
				break;
			case 'body_material_other':
				if ($arr['obj_inst']->prop('body_material') != BODY_MATERIAL_OTHER)
				{
					$retval = PROP_IGNORE;
				}
				break;
			case 'condition':
				$prop['options'] = $this->condition;
				break;
			case 'centreboard':
				if ($arr['obj_inst']->prop('watercraft_type') != WATERCRAFT_TYPE_SAILING_SHIP)
				{
					$retval = PROP_IGNORE;
				}
				else
				{
					$prop['options'] = $this->centreboard;
				}
				break;
			case 'creation_year':
				$prop['options'] = $this->custom_range(1900, date('Y'));
				if ( empty($prop['value']) )
				{
					$prop['selected'] = '2000';
				}
				break;
			case 'passanger_count':
				$prop['options'] = $this->custom_range(1, 50);
				break;
			case 'sleeper_count':
				$prop['options'] = $this->custom_range(0, 20);
				break;
			case 'engine_count':
				$prop['options'] = $this->custom_range(1, 4);
				break;
			case 'engine_type':
				$prop['options'] = $this->engine_type;
				break;
			case 'fuel':
				$prop['options'] = $this->fuel;
				break;
			case 'engine_cooling':
				$prop['options'] = $this->engine_cooling;
				break;
			case 'mast_material':
				$prop['options'] = $this->mast_material;
				break;
			case 'mast_material_other':
				if ($arr['obj_inst']->prop('mast_material') != MAST_MATERIAL_OTHER)
				{
					$retval = PROP_IGNORE;
				}
				break;
			case 'mast_count':
				$prop['options'] = $this->custom_range(1, 4);
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

	
	function _get_images_toolbar($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		
		$t->add_button(array(
			'name' => 'new',
			'img' => 'new.gif',
			'tooltip' => t('Uus Pilt'),
			'url' => $this->mk_my_orb('new', array(
				'parent' => $arr['obj_inst']->id(),
				'return_url' => get_ru()
			), CL_IMAGE),
		));

		$t->add_button(array(
			'name' => 'delete',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta'),
			'action' => '_delete_objects',
			'confirm' => t('Oled kindel et soovid valitud objektid kustutada?')
		));

		return PROP_OK;
	}

	function _get_images_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'name',
			'caption' => t('Nimi')
		));
		$t->define_field(array(
			'name' => 'select',
			'caption' => t('Vali'),
			'width' => '5%',
			'align' => 'center'
		));

		$images = new object_list(array(
			'class_id' => CL_IMAGE,
			'parent' => $arr['obj_inst']->id()
		));

		foreach ($images->arr() as $id => $image)
		{
			$t->define_data(array(
				'name' => html::href(array(
					'url' => $this->mk_my_orb('change', array(
						'id' => $image->id()
					), CL_IMAGE),
					'caption' => $image->name()
				)),
				'select' => html::checkbox(array(
					'name' => 'selected_ids['.$id.']',
					'value' => $id
				))
			));
		}
		return PROP_OK;
	}

	function _get_sail_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'type',
			'caption' => t('P&uuml;rje t&uuml;&uuml;p')
		));
		$t->define_field(array(
			'name' => 'area',
			'caption' => t('Pindala')
		));
		$t->define_field(array(
			'name' => 'material',
			'caption' => t('Purje materjal')
		));
		$t->define_field(array(
			'name' => 'age_and_condition',
			'caption' => t('Purje vanus ja seisukord')
		));

		$rows = array(
			'groot' => t('Groot/suurpuri'),
			'foka_1' => t('Foka 1'),
			'foka_2' => t('Foka 2'),
			'foka_3' => t('Foka 3'),
			'genoa_1' => t('Genoa 1'),
			'genoa_2' => t('Genoa 2'),
			'genoa_3' => t('Genoa 3'),
			'spinnaker' => t('Spinnaker'),
			'stormfoka' => t('Tormifoka'),
		);
	
		$saved_sail_table = $arr['obj_inst']->meta('sail_table');
		foreach ( $rows as $key => $value )
		{
			$t->define_data(array(
				'type' => $value,
				'area' => html::textbox(array(
					'name' => 'sail_table['.$key.'][area]',
					'value' => $saved_sail_table[$key]['area'],
					'size' => 20
				)),
				'material' => html::textbox(array(
					'name' => 'sail_table['.$key.'][material]',
					'value' => $saved_sail_table[$key]['material'],
					'size' => 20
				)),
				'age_and_condition' => html::textbox(array(
					'name' => 'sail_table['.$key.'][age_and_condition]',
					'value' => $saved_sail_table[$key]['age_and_condition'],
					'size' => 20
				)),

			));
		}

		// custom sail types:
		$t->define_data(array(
			'type' => t('Muu puri'),
			'area' => '',
			'material' => '',
			'age_and_condition' => ''
		));

		$t->define_data(array(
			'type' => html::textbox(array(
				'name' => 'sail_table[other_sail][type]',
				'size' => 10,
				'value' => $saved_sail_table['other_sail']['type']
			)),
			'area' => html::textbox(array(
				'name' => 'sail_table[other_sail][area]',
				'size' => 20,
				'value' => $saved_sail_table['other_sail']['area']
			)),
			'material' => html::textbox(array(
				'name' => 'sail_table[other_sail][material]',
				'size' => 20,
				'value' => $saved_sail_table['other_sail']['material']
			)),
			'age_and_condition' => html::textbox(array(
				'name' => 'sail_table[other_sail][age_and_condition]',
				'size' => 20,
				'value' => $saved_sail_table['other_sail']['age_and_condition']
			)),
		));
		return PROP_OK;
	}

	function _set_sail_table($arr)
	{
		$arr['obj_inst']->set_meta('sail_table', $arr['request']['sail_table']);
		return PROP_OK;
	}

	function _get_additional_equipment_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'check',
			'caption' => t('Olemas'),
			'align' => 'center',
			'width' => '5%'
		));
		$t->define_field(array(
			'name' => 'name',
			'caption' => t('Nimetus')
		));
		$t->define_field(array(
			'name' => 'info',
			'caption' => t('Lisainfo')
		));
		$t->define_field(array(
			'name' => 'amount',
			'caption' => t('Kogus')
		));

		$rows = array(
			'electricity_110V' => array( 'caption' => t('Elekter 110V'), 'amount' => null ),
			'electricity_220V' => array( 'caption' => t('Elekter 220V'), 'amount' => null ),
			'radio_station' => array( 'caption' => t('Raadiojaam'), 'amount' => null),
			'stereo' => array( 'caption' => t('Stereo'), 'amount' => null ),
			'cd' => array( 'caption' => t('CD'), 'amount' => null ),
			'waterproof_speakers' => array( 'caption' => t('Veekindlad k&otilde;larid'), 'amount' => null ),
			'burglar_alarm' => array( 'caption' => t('Signalisatsioon'), 'amount' => null ),
			'navigation_system' => array( 'caption' => t('Navigatsioonis&uuml;steem'), 'amount' => null ),
			'navigation_lights' => array( 'caption' => t('Navigatsioonituled'), 'amount' => null ),
			'trailer' => array( 'caption' => t('Treiler'), 'amount' => null ),
			'toilet' => array( 'caption' => t('Tualett'), 'amount' => null ),
			'shower' => array( 'caption' => t('Dush'), 'amount' => null ),
			'lifejacket' => array( 'caption' => t('P&auml;&auml;stevest'), 'amount' => t('tk') ),
			'swimming_ladder' => array( 'caption' => t('Ujumisredel'), 'amount' => null ),
			'awning' => array( 'caption' => t('Varikatus'), 'amount' => null ),
			'kitchen_cooker' => array( 'caption' => t('K&ouml;&ouml;k/Pliit'), 'amount' => null ),
			'vendrid' => array( 'caption' => t('Vendrid'), 'amount' => t('tk') ),
			'fridge' => array( 'caption' => t('K&uuml;lmkapp'), 'amount' => null ),
			'anchor' => array( 'caption' => t('Ankur'), 'amount' => null ),
			'oars' => array( 'caption' => t('Aerud'), 'amount' => t('tk') ),
			'tv_video' => array( 'caption' => t('TV-video'), 'amount' => null ),
			'fuel' => array( 'caption' => t('K&uuml;te'), 'amount' => null ),
			'water_tank' => array( 'caption' => t('Veepaak'), 'amount' => t('liitrit') ),
			'life_boat' => array( 'caption' => t('P&auml;&auml;stepaat'), 'amount' => null),
		);

		$saved_additional_equipment = $arr['obj_inst']->meta('additional_equipment_table');
		foreach ($rows as $key => $value)
		{
			$amount_str = "";
			if ($value['amount'] !== null)
			{
				$amount_str = html::textbox(array(
					'name' => 'additional_equipment['.$key.'][amount]',
					'value' => $saved_additional_equipment[$key]['amount']
				));
				$amount_str .= $value['amount'];
			}

			$t->define_data(array(
				'check' => html::checkbox(array(
					'name' => 'additional_equipment['.$key.'][check]',
					'value' => 1,
					'checked' => ($saved_additional_equipment[$key]['check'] == 1) ? true : false
				)),
				'name' => $value['caption'],
				'info' => html::textbox(array(
					'name' => 'additional_equipment['.$key.'][info]',
					'value' => $saved_additional_equipment[$key]['info']

				)),
				'amount' => $amount_str
			));
		}
		return PROP_OK;
	
	}

	function _set_additional_equipment_table($arr)
	{
		$arr['obj_inst']->set_meta('additional_equipment_table', $arr['request']['additional_equipment']);
		return PROP_OK;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function callback_mod_tab($arr)
	{
		$watercraft_type = $arr['obj_inst']->prop('watercraft_type');

		$no_engine = array(
			WATERCRAFT_TYPE_CANOE,
			WATERCRAFT_TYPE_SAILBOARD
		);
		if ( $arr['id'] == 'engines' && in_array($watercraft_type, $no_engine) )
		{
			return false;
		}

		$no_mast = array(
			WATERCRAFT_TYPE_MOTOR_BOAT,
			WATERCRAFT_TYPE_DINGHY,
			WATERCRAFT_TYPE_ROWING_BOAT,
			WATERCRAFT_TYPE_SCOOTER,
			WATERCRAFT_TYPE_CANOE,
		);
		if ( ( $arr['id'] == 'mast' || $arr['id'] == 'sail' ) && in_array($watercraft_type, $no_mast) )
		{
			return false;
		}
		return true;
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

	function custom_range($start, $end)
	{
		$result = array();
		foreach (range($start, $end) as $value)
		{
			$result[$value] = $value;
		}
		return $result;
	}

	function get_management_object($arr)
	{
		$managements_ol = new object_list(array(
			'class_id' => CL_WATERCRAFT_MANAGEMENT,
			'data' => $arr['obj_inst']->parent()
		));
		if ( $managements_ol->count() > 0 )
		{
			return $managements_ol->begin();
		}
		else
		{
			return false;
		}
	}

	/**
		@attrib name=_delete_objects
	**/
	function _delete_objects($arr)
	{

		foreach ($arr['selected_ids'] as $id)
		{
			if (is_oid($id) && $this->can("delete", $id))
			{
				$object = new object($id);
				$object->delete();
			}
		}
		return $arr['post_ru'];
	}

	function do_db_upgrade($table, $field, $query, $error)
	{

		if (empty($field))
		{
			// db table doesn't exist, so lets create it:
			$this->db_query('CREATE TABLE '.$table.' (
				oid INT PRIMARY KEY NOT NULL,

				deal_type int,
				watercraft_type int,
				body_material int,
				location int,
				condition int,
				seller int,
				visible int,
				archived int,
				centreboard int,
				creation_year int,
				passanger_count int,
				sleeper_count int,
				engine_count int,
				engine_type int,
				fuel int,
				engine_cooling int,
				mast_material int,
				mast_count int,
				manufacturer int,

				length float,
				width float,
				height float,
				weight float,
				draught float,
				fuel_tank float,
				engine_power float,

				watercraft_type_other varchar(255),
				body_material_other varchar(255),
				location_other varchar(255),
				condition_info varchar(255),
				brand varchar(255),
				price varchar(255),
				engine_manufacturer varchar(255),
				engine_model varchar(255),
				engine_capacity varchar(255),
				mast_material_other varchar(255),

				sail_info text,
				additional_equipment_info text
			)');
			return true;
		}

		switch ($field)
		{
			case 'deal_type':
			case 'watercraft_type':
			case 'body_material':
			case 'location':
			case 'condition':
			case 'seller':
			case 'visible':
			case 'archived':
			case 'centreboard':
			case 'creation_year':
			case 'passanger_count':
			case 'sleeper_count':
			case 'engine_count':
			case 'engine_type':
			case 'fuel':
			case 'engine_cooling':
			case 'mast_material':
			case 'mast_count':
			case 'manufacturer':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'int'
				));
                                return true;
			case 'length':
			case 'width':
			case 'height':
			case 'weight':
			case 'draught':
			case 'fuel_tank':
			case 'engine_power':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'float'
				));
                                return true;
			case 'watercraft_type_other':
			case 'body_material_other':
			case 'location_other':
			case 'condition_info':
			case 'brand':
			case 'price':
			case 'engine_manufacturer':
			case 'engine_model':
			case 'engine_capacity':
			case 'mast_material_other':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'varchar(255)'
				));
                                return true;
			case 'sail_info':
			case 'additional_equipment_info':
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
