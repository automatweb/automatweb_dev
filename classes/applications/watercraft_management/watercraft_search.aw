<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/watercraft_management/watercraft_search.aw,v 1.5 2006/10/13 13:24:20 dragut Exp $
// watercraft_search.aw - Veesõidukite otsing 
/*

@classinfo syslog_type=ST_WATERCRAFT_SEARCH relationmgr=yes no_comment=1 no_status=1 prop_cb=1
@tableinfo watercraft_search index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

	@property results_on_page type=textbox table=watercraft_search
	@caption Tulemuste arv lehel

	@property max_results type=textbox table=watercraft_search
	@caption Maksimaalne tulemuste arv

	@property no_search_form type=checkbox ch_value=1 table=watercraft_search
	@caption &Auml;ra kuva otsinguvormi

	@property saved_searches type=text store=no
	@caption Salvestatud otsing

@groupinfo parameters caption="Parameetrid"
@default group=parameters

	@property search_form_conf type=chooser orient=vertical multiple=1 field=meta method=serialize
	@caption Otsinguvormis kuvatavad v&auml;ljad

	property parameters_subtitle type=text store=no subtitle=1
	caption Otsinguvormis kuvatavad v&auml;ljad

	property watercraft_type type=checkbox ch_value=1 table=watercraft_search
	caption Aluse t&uuml;&uuml;p

	property condition type=checkbox ch_value=1 table=watercraft_search
	caption Seisukord

	property body_material type=checkbox ch_value=1 table=watercraft_search
	caption Kerematerjal

	property location type=checkbox ch_value=1 table=watercraft_search
	caption Asukoht

	property length type=checkbox ch_value=1 table=watercraft_search
	caption Pikkus

	property width type=checkbox ch_value=1 table=watercraft_search
	caption Laius

	property height type=checkbox ch_value=1 table=watercraft_search
	caption K&otilde;rgus

	property weight type=checkbox ch_value=1 table=watercraft_search
	caption Raskus

	property draught type=checkbox ch_value=1 table=watercraft_search
	caption S&uuml;vis

	property creation_year type=checkbox ch_value=1 table=watercraft_search
	caption Valmistamisaasta

	property passanger_count type=checkbox ch_value=1 table=watercraft_search
	caption Reisijaid

	property additional_equipment type=checkbox ch_value=1 table=watercraft_search
	caption Lisavarustus

	property seller type=checkbox ch_value=1 table=watercraft_search
	caption M&uuml;&uuml;ja
	
	property price type=checkbox ch_value=1 table=watercraft_search
	caption Hind

*/

class watercraft_search extends class_base
{

	var $search_form_elements;
	var $additional_equipment_elements;

	function watercraft_search()
	{
		$this->init(array(
			"tpldir" => "applications/watercraft_management/watercraft_search",
			"clid" => CL_WATERCRAFT_SEARCH
		));

		$this->search_form_elements = array(
			'watercraft_type' => t('Aluse t&uuml;&uuml;p'),
			'condition' => t('Seisukord'),
			'body_material' => t('Kerematerjal'),
			'location' => t('Asukoht'),
			'length' => t('Pikkus'),
			'width' => t('Laius'),
			'height' => t('K&otilde;rgus'),
			'weight' => t('Raskus'),
			'draught' => t('S&uuml;vis'),
			'creation_year' => t('Valmistamisaasta'),
			'passanger_count' => t('Reisijaid'),
			'additional_equipment' => t('Lisavarustus'),
			'seller' => t('M&uuml;&uuml;ja'),
			'price' => t('Hind')
		);
	

		// XXX siia peaks panem chooserid, info ja koguse propid ka
		// ja kuidagi peab siis raalima, et mis sinna tekstikasti pandi
		// ja otsima selle järgi nii selle j2rgi, et kas see prop on valitud
		// see s6na leidub info v2ljas, v6is matchib kogus.	
		$this->additional_equipment_elements = array(
			'electricity_110V_sel' => t('Elekter 110V'),
			'electricity_220V_sel' => t('Elekter 220V'),
			'radio_station_sel' => t('Raadiojaam'),
			'stereo_sel' => t('Stereo'),
			'cd_sel' => t('CD'),
			'waterproof_speakers_sel' => t('Veekindlad k&otilde;larid'),
			'burglar_alarm_sel' => t('Signalisatsioon'),
			'navigation_system_sel' => t('Navigatsioonis&uuml;steem'),
			'navigation_lights_sel' => t('Navigatsioonituled'),
			'trailer_sel' => t('Treiler'),
			'toilet_sel' => t('Tualett'),
			'shower_sel' => t('Dush'),
			'lifejacket_sel' => t('P&auml;&auml;stevest'),
			'swimming_ladder_sel' => t('Ujumisredel'),
			'awning_sel' => t('Varikatus'),
			'kitchen_cooker_sel' => t('K&ouml;&ouml;k/Pliit'),
			'vendrid_sel' => t('Vendrid'),
			'fridge_sel' => t('K&uuml;lmkapp'),
			'anchor_sel' => t('Ankur'),
			'oars_sel' => t('Aerud'),
			'tv_video_sel' => t('TV-video'),
			'fuel_sel' => t('K&uuml;te'),
			'water_tank_sel' => t('Veepaak'),
			'life_boat_sel' => t('P&auml;&auml;stepaat'),

			'electricity_110V_info' => t('Elekter 110V'),
			'electricity_220V_info' => t('Elekter 220V'),
			'radio_station_info' => t('Raadiojaam'),
			'stereo_info' => t('Stereo'),
			'cd_info' => t('CD'),
			'waterproof_speakers_info' => t('Veekindlad k&otilde;larid'),
			'burglar_alarm_info' => t('Signalisatsioon'),
			'navigation_system_info' => t('Navigatsioonis&uuml;steem'),
			'navigation_lights_info' => t('Navigatsioonituled'),
			'trailer_info' => t('Treiler'),
			'toilet_info' => t('Tualett'),
			'shower_info' => t('Dush'),
			'lifejacket_info' => t('P&auml;&auml;stevest'),
			'swimming_ladder_info' => t('Ujumisredel'),
			'awning_info' => t('Varikatus'),
			'kitchen_cooker_info' => t('K&ouml;&ouml;k/Pliit'),
			'vendrid_info' => t('Vendrid'),
			'fridge_info' => t('K&uuml;lmkapp'),
			'anchor_info' => t('Ankur'),
			'oars_info' => t('Aerud'),
			'tv_video_info' => t('TV-video'),
			'fuel_info' => t('K&uuml;te'),
			'water_tank_info' => t('Veepaak'),
			'life_boat_info' => t('P&auml;&auml;stepaat'),

			
		);

	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case 'results_on_page':
				if ( $arr['new'] == 1 )
				{
					$prop['value'] = 50;
				}
				break;
			case 'max_results':
				if ( $arr['new'] == 1 )
				{
					$prop['value'] = 500;
				}
				break;
			case 'search_form_conf':
				$prop['options'] = $this->search_form_elements;
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

	// searches the watercrafts
	function search($arr)
	{
		$filter = array(
			'class_id' => CL_WATERCRAFT,
			'parent' => $arr['obj_inst']->prop('data')
		);
		foreach ($this->search_form_elements as $name => $caption)
		{
			// if it is range:
			if ( is_array($arr['request'][$name]) )
			{
				$from = (int)$arr['request'][$name]['from'];
				$to = (int)$arr['request'][$name]['to'];

				// if both are empty, then don't need to search by that:
				if ( empty($from) && empty($to) )
				{
					continue;
				}
				else
				if ( empty($from) ) 
				{
					// we have only $to value:
					$filter[$name] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $to);
				}
				else
				if ( empty($to) )
				{
					// we have only $from value
					$filter[$name] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $from);
				}
				else
				{
					// and finally we have them both:
					$filter[$name] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $from, $to);
				}
			}
			else
			{
				if ( !empty($arr['request'][$name]) )
				{
					if ($name == 'seller')
					{
						$filter['CL_WATERCRAFT.RELTYPE_SELLER.class_id'] = $arr['request'][$name];
					}
					else
					if ($name == 'additional_equipment')
					{
						// we have this additional equipment search field, which content have to searched from
						// all additional equipment fields
					}
					else
					{
						$filter[$name] = $arr['request'][$name];
					}
				}
			}
		}

		$watercrafts = new object_list($filter);
		return $watercrafts;
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
			case 'results_on_page':
			case 'max_results':
			case 'no_search_form':
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
