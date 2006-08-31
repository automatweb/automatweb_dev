<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/watercraft_management/watercraft_search.aw,v 1.4 2006/08/31 14:36:32 dragut Exp $
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
		//	case 'watercraft_type':
		//	case 'condition':
		//	case 'body_material':
		//	case 'location':
		//	case 'length':
		//	case 'width':
		//	case 'height':
		//	case 'weight':
		//	case 'draught':
		//	case 'creation_year':
		//	case 'passanger_count':
		//	case 'additional_equipment':
		//	case 'seller':
		//	case 'price':
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
