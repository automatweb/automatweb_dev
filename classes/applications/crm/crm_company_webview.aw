<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_company_webview.aw,v 1.2 2005/11/10 21:37:31 kristo Exp $
// crm_company_webview.aw - Organisatsioonid veebis 
/*

@classinfo syslog_type=ST_CRM_COMPANY_WEBVIEW relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property crm_db type=relpicker reltype=RELTYPE_CRM_DB automatic=1 field=meta method=serialize
@caption Andmebaas, mille organisatsioone kuvatakse

@property limit_sector type=popup_search style=relpicker reltype=RELTYPE_LIMIT_SECTOR clid=CL_CRM_SECTOR field=meta method=serialize
@caption Tegevusala piirang
@comment Kui otsitud on mitu tegevusala, kuvatakse nende koigi firmasid, va. juhul, kui neist yks on valja valitud.

@property limit_city type=relpicker reltype=RELTYPE_LIMIT_CITY automatic=1 field=meta method=serialize
@caption Linna piirang

@property limit_county type=relpicker reltype=RELTYPE_LIMIT_COUNTY automatic=1 field=meta method=serialize
@caption Maakonna piirang

@property template type=select field=meta method=serialize
@caption Template

@property ord1 type=select field=meta method=serialize
@caption J&auml;rjestamisprintsiip 1

@property ord2 type=select field=meta method=serialize
@caption J&auml;rjestamisprintsiip 2

@property ord3 type=select field=meta method=serialize
@caption J&auml;rjestamisprintsiip 2

@property clickable type=checkbox field=flags ch_value=16 method=bitmask
@caption Organistatsioonide nimed on klikitavad



@reltype LIMIT_SECTOR value=1 clid=CL_CRM_SECTOR
@caption Tegevusala piirang

@reltype LIMIT_CITY value=2 clid=CL_CRM_CITY
@caption Linna piirang

@reltype LIMIT_COUNTY value=3 clid=CL_CRM_COUNTY
@caption Maakonna piirang

@reltype CRM_DB value=4 clid=CL_CRM_DB
@caption Organisatsioonide andmebaas

*/

class crm_company_webview extends class_base
{
	function crm_company_webview()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/crm/crm_company_webview",
			"clid" => CL_CRM_COMPANY_WEBVIEW
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			case 'limit_city':
			case 'limit_county':
				if (!empty($prop['value']))
				{
					$excl_name = $prop['name'].'_excl';
					$val = $arr['obj_inst']->meta($excl_name);
					$prop['post_append_text'] = html::checkbox(array(
						'name' => $prop['name'].'_excl',
						'value' => 1,
						'caption' => t("V&auml;listav") .' / ',
						'checked' => $val,
					)) . $prop['post_append_text'];
				}
			break;
			case 'template':
				$inst = get_instance(CL_CRM_COMPANY_WEBVIEW);
				$sys_tpldir = $inst->adm_template_dir;
				$site_tpldir = $inst->site_template_dir;
				$prop['options'] = array('default.tpl' => t("Vaikimisi"));
				foreach (glob($site_tpldir.'/*.tpl') as $file)
				{
					$base = basename($file);
					$prop['options'][$base] = $base;
				}
			break;
			case 'ord1':
			case 'ord2':
			case 'ord3':
				$options = array(
					'jrk' => t("J&auml;rjekorranr"),
					'name' => t("Nimi"),
					'county' => t("Maakond"),
					'city' => t("Linn"),
				);
				$prop['options'] = $options;
				
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
			case 'limit_city':
			case 'limit_county':
				$excl_name = $prop['name'].'_excl';
				$val = empty($arr['request'][$excl_name]) ? 0 : 1;
				$arr['obj_inst']->set_meta($excl_name, $val);
			break;
		}
		return $retval;
	}	

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		if (!is_oid($arr['id']) || !$this->can('view', $arr['id']))
		{
			return;
		}
		$o = obj($arr['id']);
		if ($o->class_id() != CL_CRM_COMPANY_WEBVIEW)
		{
			return;
		}
		$tmpl = $o->prop('template');
		// XXX EKKE: pick template
		if (!preg_match('/^[^\\\/]+\.tpl$/', $tmpl))
		{
			$tmpl = "default.tpl";
		}
		
		$this->read_template($tmpl);
		
		$org = ifset($_REQUEST, 'org');
		if (!$this->can('view', $org) || !($c = obj($org)) || $c->class_id() != CL_CRM_COMPANY)
		{
			$org = null;
		}

	enter_function('crm_company_webview::show');
		if (is_null($org))
		{
			// LIST COMPANIES
			$ret = $this->_get_companies_list_html(array('id' => $arr['id']));
			
		}
		else
		{
			// SHOW COMPANY
			$ret = $this->_get_company_show_html(array('list_id' => $arr['id'], 'company_id' => $org));;
		}
	exit_function('crm_company_webview::show');
		return $ret;
		
	}

	// Return html for company display
	function _get_company_show_html ($arr)
	{
		$this->sub_merge = 0;
		$org = ifset($arr, 'company_id');
		if (!$this->can('view', $org) || !($c = obj($org)) || $c->class_id() != CL_CRM_COMPANY)
		{
			return "";
		}
		$this->add_hit($org);
		
		// All possible line_* values are defined here
		$datafields = array(
			'sectors' => '',
			'address' => 'contact',
			'phone' => 'phone_id',
			'openhours' => '',
			'fax' => 'telefax_id',
			'email' => 'email_id',
			'url' => 'url_id',
			'comment' => 'comment',
			'name' => 'name',
			'founded' => 'year_founded',
			'specialoffers' => 'special_offers',
			'extrafeatures' => '',
			'num_rooms' => '',
			'num_beds' => '',
			'prices' => 'price_txt',
			'description' => 'tegevuse_kirjeldus',
			'type' => '',
		);
		
		// Name is not obligatory - will go to template as {VAR:key}
		// [not really: If name is not set here, it will be looked up from property definitions]
		$fieldnames = array(
			'address' => t("Aadress"),
			'phone' => t("Tel"),
			'fax' => t("Faks"),
			'openhours' => t("Avatud"),
			'email' => t("E-post"),
			'sectors' => t("Tegevusalad"),
			'founded' => t("Asutatud"),
			'specialoffers' => t("Eripakkumised"),
			'extrafeatures' => t("Lisav&otilde;imalused"),
			'num_beds' => t("Kohti"),
			'num_rooms' => t("Toad"),
			'prices' => t("Hinnad"),
			'type' => t("T&uuml;&uuml;p"),
		);


		$extrainfo = array(); // crm_field_{type} objects in type => array('o'=>obj,'p'=>properties)  array (type is one of 'ACCOMMODATION', ...) (see reltype FIELD on crm_company)
		$used_fields = $this->v2_name_map;
		classload("crm/crm_company");

		foreach ($datafields as $item => $mapped)
		{
			// Skip parsing for values which are not used anyway
			if (!isset($used_fields['line_'.$item]))
			{
				continue;
			}
			$key = $value = "";
			switch ($item)
			{
				case 'openhours':
					$inst = get_instance(CL_OPENHOURS);
					$o_item = $c->get_first_obj_by_reltype('RELTYPE_OPENHOURS');
					if (!is_object($o_item))
					{
						continue;
					}
					$value = $inst->show(array(
						'id' => $o_item->id(),
					));
				break;
				case 'sectors':
					$conns = $c->connections_from(array(
						'type' => 'RELTYPE_TEGEVUSALAD',
					));
					foreach ($conns as $conn)
					{
						$sector = $conn->to();
						$value[] = $sector->name();
					}
				break;
				case 'name':
				case 'comment':
				case 'description':
					$value = nl2br($c->prop($mapped));
				break;
				case 'founded':
					if ($c->prop($mapped))
					{
						$value = date('d-m-Y', $c->prop($mapped));
					}
				break;
				case 'specialoffers':
					$conns = $c->connections_from(array(
						'type' => 'RELTYPE_SPECIAL_OFFERS',
					));
					$url = '/specialoffers/?offer=';
					$instance = get_instance(CL_CRM_SPECIAL_OFFER);
					foreach ($conns as $con)
					{
						$offer = $con->to();
						$value[] = html::href(array(
							'url' => $url.$offer->id(),
							'caption' => $offer->name(),
						));
					}
					$value = implode(', ', $value);
				break;
				case 'extrafeatures':
					$type = 'ACCOMMODATION';
					// checkboxes in crm_field_accommodation
					if (!array_key_exists($type, $extrainfo))
					{
						$extrainfo[$type]['o'] = crm_company::find_crm_field_obj(array(
							'oid' => $c->id(),
							'type' => $type,
						));
						if (is_object($extrainfo[$type]['o']))
						{
							$extrainfo[$type]['p'] = $extrainfo[$type]['o']->properties();
						}
					}
					
					if (is_object($extrainfo[$type]['o']))
					{
						$sm = $this->sub_merge;
						$this->sub_merge = 0;
						// Get all checkbox properties and values
						$pval = $extrainfo[$type]['p'];
						$pl = $extrainfo[$type]['o']->get_property_list();
						foreach ($pl as $name => $pinf)
						{
							if ($pinf['type'] == 'checkbox' && $pval[$name])
							{
								// Output where value is set
								$this->vars(array(
									'extraf_name' => t($pinf['caption']),
								));
								$value .= $this->parse('extraf_value');
							}
						}
						$this->sub_merge = $sm;
					}
				break;
					// Find the following from appropriate crm_field_ object
				case 'num_rooms':
				case 'num_beds':
				case 'prices':
				case 'type':
					$type = 'ACCOMMODATION';

				// case 'whatever':
					if (!array_key_exists($type, $extrainfo))
					{
						$extrainfo[$type]['o'] = crm_company::find_crm_field_obj(array(
							'oid' => $c->id(),
							'type' => $type,
						));
						if (is_object($extrainfo[$type]['o']))
						{
							$extrainfo[$type]['p'] = $extrainfo[$type]['o']->properties();
						}
					}
					
					if (is_array($extrainfo[$type]['p']))
					{
						$use = $item;
						if (!empty($mapped))
						{
							$use = $mapped;
						}
						if ($item == 'prices' && !empty($extrainfo[$type]['p']['price_level']))
						{
							$l = $extrainfo[$type]['p']['price_level'];
							$value[] = t($l);
						}
						$value[] = $extrainfo[$type]['p'][$use];
						if ($item == 'type')
						{
							$rename = array( // copied from class/applications/crm/crm_field_accommodation get_property->type
								'tp_hotel' => t("Hotell"),
								'tp_motel' => t("Motell"),
								'tp_guesthouse' => t("K&uuml;lalistemaja"),
								'tp_hostel' => t("Hostel"),
								'tp_camp' => t("Puhkek&uuml;la ja -laager"),
								'tp_wayhouse' => t("Puhkemaja"),
								'tp_apartment' => t("K&uuml;laliskorter"),
								'tp_homestay' => t("Kodumajutus"),
							);
							$value[count($value)-1] = $rename[$value[count($value)-1]];
						}
					}
				break;
				default:
					$oid = $c->prop($mapped);
					if (is_oid($oid) && ($o_item = obj($oid)) && is_object($o_item) && is_numeric($o_item->id()) )
					{
						if ($item == 'email')
						{
							$value = html::href(array(
								'url' => 'mailto:'.$o_item->prop('mail'),
								'caption' => $o_item->prop('mail'),
							));
						} 
						elseif ($item == 'url')
						{
							$value = $o_item->name();
							$value = html::href(array(
								'url' => $value,
								'caption' => $value,
							));
						}
						else
						{
							$value = $o_item->name();
						}
					
					}
				break;
			}
			
			$key = ifset($fieldnames, $item);
			if (is_array($value))
			{
				$value = join(', ', $value);
			}
			
			if (!empty($value))
			{
				$this->vars(array(
					'key' => $key,
					'value' => $value,
				));
				$this->vars(array('line_'.$item => $this->parse('line_'.$item)));
			}
		}

		// Images
		$conns = $c->connections_from(array(
			'type' => 'RELTYPE_IMAGE',
		));
		$inst_img = get_instance(CL_IMAGE);
		foreach ($conns as $conn)
		{
			$image = $conn->to();
			if ($image->prop('status') != STAT_ACTIVE)
			{
				continue;
			}
			$tmp = $inst_img->parse_alias(array(
				'alias' => array(
					'target' => $image->id(),
				),
			));
			$images[] = $tmp['replacement']; // No, replacement is not a logical name in this context. However, it works!
		}
		$images_html = join('<br><br>', $images);
	
		$have_rating = false;
		$ro = aw_global_get('rated_objs');
		if (!is_array($ro) || !isset($ro[$c->id()]))
		{
	 		$scale_inst = get_instance(CL_RATE_SCALE);
			$scales = $scale_inst->get_scale_objs_for_obj($c->id());
			foreach ($scales as $scale)
			{
				$scale_values = $scale_inst->_get_scale($scale);
				$scale_obj = obj($scale);
				$this->vars(array(
					'rating_caption' => $scale_obj->name(),
					'rating_value' => '',
				));
					foreach ($scale_values as $num => $txt)
				{
					$this->vars(array(
						'rating_value_name' => 'rate['.$scale_obj->id().']',
						'rating_value_value' => $num,
						'rating_value_caption' => $txt,
					));
					$this->vars_merge(array('rating_value' => $this->parse('rating_value')));
					$have_rating = true;
				}
				$this->vars_merge(array('rating' => $this->parse('rating')));
			}
		}
		$rating_form = "";
		if ($have_rating)
		{
			$rating_form .= html::submit(array(
				'value' => t("H&auml;&auml;leta"),
			));
			$hiddens = array(
				'return_url' => htmlspecialchars(aw_global_get('REQUEST_URI')),
				'class' => 'rate',
				'action' => 'rate',
				'oid' => $c->id(),
			);
			foreach ($hiddens as $name => $value)
			{
				$rating_form .= html::hidden(array(
					'name' => $name,
					'value' => $value,
				));
			}
		}
		
		$this->vars(array(
			'rating_form_vars' => $rating_form,
			'images' => $images_html,
		));

		// Alrighty then, parse your arse away
		return $this->parse('company_show');
	}

	// Return sorted list of companies to display
	function _list_companies ($arr)
	{
	enter_function('crm_company_webview::list');
		$orgs = array(); // return value
		$ob = new object($arr["id"]);
		$db = $ob->prop('crm_db');
		$crm_db = obj($db);
		$dir = is_oid($crm_db->prop('dir_firma')) ? $crm_db->prop('dir_firma') : $crm_db->prop('dir_default');
		$objs = array();

		// Get configuration
		$limited = false;
		
		// Limit by sector
		$sector = $ob->prop('limit_sector');
		if (is_oid($sector) && ($osector = obj($sector)) && $osector->class_id() == CL_CRM_SECTOR)
		{
			$limit_sector = array($sector);
		}

		// If none is selected, limit by any connected sector
		if (!isset($limit_sector))
		{
			$limit_sector = array();
			foreach ($ob->connections_from(array('type' => 'RELTYPE_LIMIT_SECTOR')) as $con)
			{
				$limit_sector[] = $con->prop('to');
			}
		}

		

		// Setup limit by location - county
		$limit_city = $limit_county = null;
		$county = $ob->prop('limit_county');
		if (is_oid($county) && ($ocounty = obj($county)) && $ocounty->class_id() == CL_CRM_COUNTY)
		{
			$limit_county = $county;
			$limit_county_excl = $ob->meta('limit_county_excl'); // Exclusive
		}
		
		// Setup limit by location - city
		$city = $ob->prop('limit_city');
		if (is_oid($city) && ($ocity = obj($city)) && $ocity->class_id() == CL_CRM_CITY)
		{
			$limit_city = $city;
			$limit_city_excl = $ob->meta('limit_city_excl'); // Exclusive
		}

		$order_by = array($ob->prop('ord1'), $ob->prop('ord2'), $ob->prop('ord3'));

		$this->vars(array(
			'txt_address' => t("Aadress"),
			'txt_phone' => t("Tel"),
			'txt_fax' => t("Faks"),
			'txt_openhours' => t("Avatud"),
			'txt_email' => t("E-post"),
			'txt_web' => t("Koduleht"),
		));
		
		/// okay, I'm sorry, this is just SO badly done, I'm rewriting this completely.
		$filt = array(
			'class_id' => CL_CRM_COMPANY,
			'status' => STAT_ACTIVE,
			'parent' => $dir,
		);
		if (isset($limit_sector) && is_array($limit_sector) && count($limit_sector))
		{
			$filt["pohitegevus"] = $limit_sector;
		}
		if (empty($limit_city_excl) && !empty($limit_city))
		{
			$filt["CL_CRM_COMPANY.contact.linn"] = $limit_city;
		}
		if (!empty($limit_city_excl) && !empty($limit_city))
		{
			$filt["CL_CRM_COMPANY.contact.linn"] = new obj_predicate_not($limit_city);
		}
		if (empty($limit_county_excl) && !empty($limit_county))
		{
			$filt["CL_CRM_COMPANY.contact.maakond"] = $limit_county;
		}
		if (!empty($limit_county_excl) && !empty($limit_county))
		{
			$filt["CL_CRM_COMPANY.contact.maakond"] = new obj_predicate_not($limit_county);
		}

		$o_lut = array(
			'jrk' => "objects.jrk",
			'name' => "objects.name",
			'county' => "kliendibaas_address_129_contact.maakond",
			'city' => "kliendibaas_address_129_contact.linn",
		);
		$order = array();
		if ($ob->prop("ord1"))
		{
			$order[] = $o_lut[$ob->prop("ord1")];
		}
		if ($ob->prop("ord2"))
		{
			$order[] = $o_lut[$ob->prop("ord2")];
		}
		if ($ob->prop("ord3"))
		{
			$order[] = $o_lut[$ob->prop("ord3")];
		}
		if (count($order))
		{
			$filt["sort_by"] = join(", ", $order);
		}
		$ol = new object_list($filt);
		exit_function('crm_company_webview::list');
		return $ol->arr();
	}

	// Returns html for companies list
	function _get_companies_list_html($arr)
	{
		$ob = new object($arr["id"]);
		$this->sub_merge = 1;
	
		$orgs = $this->_list_companies(array('id' => $arr['id']));

		// Prepare for output
		$do_link = $ob->prop('clickable');
		$this->vars(array(
			'name' => $ob->name(),
			'company_list_item' => '',
			'company_list' => '',
		));
		$datalist = array(
			'address' => 'contact',
			'phone' => 'phone_id',
			'openhours' => 'openhours',
			'fax' => 'telefax_id',
			'email' => 'email_id',
			'web' => 'url_id'
		);

		$url = '/'.aw_global_get('section').'?org=';
		$used_fields = $this->v2_name_map;

		// Output company list
		$oh_inst = get_instance(CL_OPENHOURS);
		foreach ($orgs as $o)
		{
			$address = $phone = $fax = $openhours = $email = $web = "";
			$name = $o->name();
			$this->vars(array(
				'company_name' => $do_link ? html::href(array(
						'url' => $url . $o->id(),
						'caption' => $name))
						: $name,
				'company_changeurl' => $this->can('edit', $o->id()) ? html::href(array(
						'caption' => '('.t("Muuda").')',
						'url' => $this->mk_my_orb('change',array(
								'id' => $o->id(),
							),CL_CRM_COMPANY, true),
						))
						: '',		
			));

			foreach ($datalist as $item => $mapped)
			{
				// Skip parsing for values which are not used anyway
				if (!isset($used_fields['company_item_'.$item]))
				{
					unset($datalist[$item]); // and don't come here again!
					continue;
				}
				$this->vars(array('company_item_'.$item => ''));
				$oid = $o->prop($mapped);
				if (is_oid($oid) && ($o_item = obj($oid)) && (is_object($o_item) && is_numeric($o_item->id())) || $item=='openhours')
				{
					if ($item == 'email')
					{
						$value = html::href(array(
							'url' => 'mailto:'.$o_item->prop('mail'),
							'caption' => $o_item->prop('mail'),
						));
					}
					elseif ($item == 'web')
					{
						$value = $o_item->name();
						$value = html::href(array(
							'url' => $value,
							'caption' => $value,
						));
					}
					elseif ($item == 'openhours')
					{
						$o_item = $o->get_first_obj_by_reltype('RELTYPE_OPENHOURS');
						if (!is_object($o_item))
						{
							continue;
						}
						$value = $oh_inst->show(array(
							'id' => $o_item->id(),
							'style' => 'short',
						));
					}
					else
					{
						$value = $o_item->name();
					}
					if (empty($value))
					{
						continue;
					}
					$this->vars(array('company_'.$item => $value));
					$this->parse('company_item_'.$item);
				}
			}
			$this->parse('company_list_item');
		}
		$this->parse('company_list');
		return $this->parse();
	}

}
?>
