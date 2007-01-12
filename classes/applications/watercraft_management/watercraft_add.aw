<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/watercraft_management/watercraft_add.aw,v 1.5 2007/01/12 00:41:20 dragut Exp $
// watercraft_add.aw - Vees&otilde;iduki lisamine 
/*

@classinfo syslog_type=ST_WATERCRAFT_ADD relationmgr=yes no_comment=1 no_status=1 prop_cb=1
@tableinfo watercraft_add index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

	@property watercraft_type type=select table=watercraft_add
	@caption Aluse t&uuml;&uuml;p 

	@property watercraft_management type=relpicker reltype=RELTYPE_WATERCRAFT_MANAGEMENT table=watercraft_add
	@caption Vees&otilde;idukite haldus

@groupinfo required_fields caption="Kohustuslikud v&auml;ljad"
@default group=required_fields

	@property required_fields_table type=table no_caption=1
	@caption Kohustuslikud v&auml;ljad

@groupinfo pages caption="Lehek&uuml;ljed"
@default group=pages

	@property pages_table type=table no_caption=1
	@caption Lehek&uuml;ljed

@reltype WATERCRAFT_MANAGEMENT value=1 clid=CL_WATERCRAFT_MANAGEMENT
@caption Vees&otilde;idukite haldus
*/

class watercraft_add extends class_base
{

	var $watercraft_inst;

	function watercraft_add()
	{
		$this->init(array(
			"tpldir" => "applications/watercraft_management/watercraft_add",
			"clid" => CL_WATERCRAFT_ADD
		));

		$this->watercraft_inst = get_instance('applications/watercraft_management/watercraft');
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case 'watercraft_type':
				$prop['options'] = $this->watercraft_inst->watercraft_type;
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

	function _get_required_fields_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->set_caption($arr['prop']['caption']);
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'required',
			'caption' => t('Kohustuslik'),
			'width' => '5%',
			'align' => 'center'
		));
		$t->define_field(array(
			'name' => 'name',
			'caption' => t('Nimi')
		));
		$t->define_field(array(
			'name' => 'tpl_var',
			'caption' => t('Templeidi muutuja')
		));

		$o = new object();
		$o->set_class_id(CL_WATERCRAFT);
		$all_properties = $this->get_watercraft_properties(array(
			'type' => $arr['obj_inst']->prop('watercraft_type') // lets get the watercraft type from watercraft_add obj.
		));

		$all_groups = $o->get_group_list();

		$saved = $arr['obj_inst']->meta('required_fields');
		foreach ($all_properties as $prop_name => $prop_data)
		{

			if (empty($prop_data['caption']) || ($prop_data['caption'] == 'Info' && $prop_data['group'] == 'additional_equipment'))
			{
				$name_str = '<em>'.$prop_data['name'] .'</em> '. $prop_data['caption'];
			}
			else
			{
				$name_str = $prop_data['caption'];
			}

			$t->define_data(array(
				'required' => html::checkbox(array(
					'name' => 'sel['.$prop_name.']',
					'value' => $prop_name,
					'checked' => (empty($saved[$prop_name])) ? '' : $prop_name
				)),
				'name' => $name_str,
				'tpl_var' => '{VAR:'.$prop_name.'}',
				'group' => '<strong>'.$all_groups[$prop_data['group']]['caption'].'</strong>',
			));
		}

		$t->set_rgroupby(array(
			'group' => 'group'
		));
		return PROP_OK;
	}

	function _set_required_fields_table($arr)
	{
		$arr['obj_inst']->set_meta('required_fields', $arr['request']['sel']);
		return PROP_OK;
	}

	function _get_pages_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->set_caption($arr['prop']['caption']);
		$t->set_sortable(false);
		$t->define_field(array(
			'name' => 'page',
			'caption' => t('Lehek&uuml;lg'),
			'width' => '10%',
			'align' => 'center'
		));
		$t->define_field(array(
			'name' => 'title',
			'caption' => t('Pealkiri'),
			'align' => 'center'
		));
		$t->define_field(array(
			'name' => 'template',
			'caption' => t('Templeit'),
			'align' => 'center'
		));

		$saved = safe_array($arr['obj_inst']->meta('pages'));
		$count = 0;
		foreach ($saved as $page)
		{
			$t->define_data(array(
				'page' => $count,
				'title' => html::textbox(array(
					'name' => 'pages['.$count.'][title]',
					'value' => $saved[$count]['title']
				)),
				'template' => html::textbox(array(
					'name' => 'pages['.$count.'][template]',
					'value' => $saved[$count]['template']
				)),
			));
			$count++;
		}

		$t->define_data(array(
			'page' => $count,
			'title' => html::textbox(array(
				'name' => 'pages['.$count.'][title]',
			)),
			'template' => html::textbox(array(
				'name' => 'pages['.$count.'][template]',
			)),
		));

		return PROP_OK;

	}

	function _set_pages_table($arr)
	{
		$valid_rows = array();
		foreach ($arr['request']['pages'] as $page)
		{
			if (!empty($page['title']))
			{
				$valid_rows[] = $page;
			}
		}
		$arr['obj_inst']->set_meta('pages', $valid_rows);
		return PROP_OK;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////
	/** Change the realestate object info.
			
		@attrib name=parse_alias is_public="1" caption="Change"

	**/
	function parse_alias($arr)
	{
		return $this->show(array(
			'id' => $arr['alias']['to'],
			'doc_id' => $arr['alias']['from']
		));
	}

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		enter_function('watercraft_add::show');

		/*
			so this is how it works ...
			i'll create  a new object when user passes the first page
			i'll save the properties everytime form is submitted and passed (no errors)
			while showing already submitted forms, i'll ask the object the properties
			images are saved under watercraft object, no connections at the moment 
		*/

		// watercraft_add object
		$o = new object($arr["id"]);

		// ask the type from watercraft_add object
		$type = $o->prop('watercraft_type'); 

		// lets get the property elements:
		$elements = $this->get_watercraft_properties(array(
			'type' => $type,
		));

		$pages = $o->meta('pages');
		$saved_pages = $_SESSION['watercraft_input_data']['saved_pages'];
		$page = (int)$_GET['page'];
		$vars = array();


		// if the watercraft id is set in the url, then we should load that watercraft obj. 
		if ($this->can('view', $_SESSION['watercraft_input_data']['watercraft_id']))
		{
			$watercraft_obj = new object($_SESSION['watercraft_input_data']['watercraft_id']);
		}
		else
		{

			if ($this->can('view', $_GET['watercraft_id']))
			{
				$watercraft_obj = new object($_GET['watercraft_id']);
				$_SESSION['watercraft_input_data']['watercraft_id'] = $_GET['watercraft_id'];
				$saved_pages = array_keys($pages);
				$_SESSION['watercraft_input_data']['saved_pages'] = $saved_pages;
			}
		}

		// add 10 imageupload fields also
		for ($i = 1; $i <= 10; $i++)
		{
			$elements['image_upload_'.$i] = array(
				'name' => 'image_upload_'.$i,
				'id' => 'image_upload_'.$i,
				'type' => 'fileupload'
			);
		}


		// draw pages: 
		$vars['pages'] = $this->draw_pages(array(
			'saved_pages' => $saved_pages,
			'pages' => $pages,
			'page' => $page
		));

		// load template
		if (empty($pages))
		{
			$this->read_template("show.tpl");
		}
		else
		{
			if (empty($page))
			{
				$page_data = reset($pages); 
			}
			else
			{
				$page_data = $pages[$page];
			}
			$this->read_template($page_data['template']);
		}

		// if there are any errors, then show them
		if (!empty($_SESSION['watercraft_input_data']['errors']))
		{
			foreach ($_SESSION['watercraft_input_data']['errors'] as $key => $value)
			{
				$vars[$key.'_error'] = $this->parse('ERROR');
			}
		}

		// draw elements:
		classload('cfg/htmlclient');
		foreach ($elements as $name => $prop)
		{
			if ($this->template_has_var($name))
			{
				if (!empty($watercraft_obj))
				{
					$prop['value'] = $watercraft_obj->prop($name);
				}
				$vars[$name] = htmlclient::draw_element($prop);
			}
		}

		// generate uploaded images list
		if ($this->template_has_var('UPLOADED_IMAGES'))
		{
			if (!empty($watercraft_obj))
			{
				$images = new object_list(array(
					'parent' => $watercraft_obj->id(),
					'class_id' => CL_IMAGE
				));

				$image_inst = get_instance('image');
				$images_str = '';
				foreach ($images->arr() as $image_oid => $image_obj)
				{
					$this->vars(array(
						'image_url' => $image_inst->get_url_by_id($image_oid),
						'image_name' => $image_obj->name()
					));
					$images_str .= $this->parse('UPLOADED_IMAGE');
				}
				$this->vars(array(
					'UPLOADED_IMAGE' => $images_str
				));

				$vars['UPLOADED_IMAGES']= $this->parse('UPLOADED_IMAGES');
			}
		}


		$this->vars($vars);

		$this->vars(array(
			'reforb' => $this->mk_reforb('submit_data', array(
				'section' => aw_global_get('section'),
				'return_url' => post_ru(),
				'id' => $arr['id'],
				'page' => $page
			))
		));
		exit_function('watercraft_add::show');
		return $this->parse();
	}

        /** submit_data
                @attrib name=submit_data 
                @param id required type=int acl=view
                @param rel_id required type=int 
        **/
	function submit_data($arr)
	{

		if (isset($arr['cancel']))
		{
			if ($this->can('view', $_SESSION['watercraft_input_data']['watercraft_id']))
			{
				$watercraft_obj = new object($_SESSION['watercraft_input_data']['watercraft_id']);
				$watercraft_obj->delete(true);
			}
			unset($_SESSION['watercraft_input_data']);
			return aw_ini_get('baseurl').'/'.aw_global_get('section');
		}

		// watercraft_add object
		$o = new object($arr['id']);

		// get the required fields
		$required_fields = $o->meta('required_fields');

		$return_url = $arr['return_url'];
		unset($arr['return_url']);

		// check for errors
		$_SESSION['watercraft_input_data']['errors'] = array();
		foreach ($arr as $key => $value)
		{
			if (empty($value) && isset($required_fields[$key]))
			{
				$_SESSION['watercraft_input_data']['errors'][$key] = $value;
			}
		}

		// if we got any errors (required field is not filled) then redirect the user 
		// back the page where errors occurred
		if (!empty($_SESSION['watercraft_input_data']['errors']))
		{
			return $return_url;
		}

		$page = $arr['page'];

		// remember which pages are visited and saved:
		$_SESSION['watercraft_input_data']['saved_pages'][$page] = $page;

		// if the next button is pressed
		if (isset($arr['next']))
		{
			$page = $arr['page'] + 1;
		}
		// if the prev button is pressed
		if (isset($arr['prev']))
		{
			$page = $arr['page'] - 1;
		}
		

		$return_url = aw_url_change_var('page', $page, $return_url);
		$return_url = aw_url_change_var('watercraft_id', NULL, $return_url);

		// save object when everything is ok
		if (empty($_SESSION['watercraft_input_data']['watercraft_id']))
		{
			$watercraft_management_oid = $o->prop('watercraft_management');
			if (!$this->can('view', $watercraft_management_oid))
			{
				return $return_url;
			}
			$watercraft_management_obj = new object($watercraft_management_oid);
			$watercraft_obj = new object();
			$watercraft_obj->set_class_id(CL_WATERCRAFT);
			$watercraft_obj->set_parent($watercraft_management_obj->prop('data'));
			$watercraft_obj->set_meta('added_from_section', aw_global_get('section'));
			$watercraft_obj->set_prop('watercraft_type', $o->prop('watercraft_type'));
		}
		else
		{
			$watercraft_obj = new object($_SESSION['watercraft_input_data']['watercraft_id']);
		}

		// here i can upload/save images
		if (!empty($_FILES))
		{
			$image_inst = get_instance('image');
			foreach ($_FILES as $field_name => $image_data)
			{
				if ($image_data['error'] == 0)
				{
					$image = $image_inst->add_upload_image($field_name, $watercraft_obj->id());
				}
			}
		}

		// so, here i should have an watercraft_obj, so set the properties:
		foreach ($arr as $prop_name => $prop_value)
		{
			if ($watercraft_obj->is_property($prop_name))
			{
				$watercraft_obj->set_prop($prop_name, $prop_value);
			}
		}

		$page_keys = array_keys($o->meta('pages'));
		$saved_pages = $_SESSION['watercraft_input_data']['saved_pages'];
		$pages_diff = array_diff($page_keys, $saved_pages);
		// so, if the diff is empty, then all the pages have been saved
		if (empty($pages_diff) && isset($arr['send']))
		{
			$watercraft_obj->set_status(STAT_ACTIVE);
			$watercraft_obj->save();
			unset($_SESSION['watercraft_input_data']);
			$return_url = aw_ini_get('baseurl').'/'.aw_global_get('section');
		}
		else
		{
			$_SESSION['watercraft_input_data']['watercraft_id'] = $watercraft_obj->save();
		}

		return $return_url;
	}

	// draw's pages and returns it as string
	function draw_pages($arr)
	{

		$saved_pages = $arr['saved_pages'];
		$pages = (array)$arr['pages'];
		$page = $arr['page'];

		$this->read_template('pages.tpl');

		$pages_str = '';
		foreach ($pages as $key => $value)
		{
			$this->vars(array(
				'title' => $value['title'],
				'url' => aw_url_change_var('page', $key)
			));
			if ($page == $key)
			{
				$pages_str .= $this->parse('PAGE_ACT');
			}
			else
			if ($page > $key || isset($saved_pages[$key]))
			{
				$pages_str .= $this->parse('PAGE');
			}
			else
			{
				$pages_str .= $this->parse('PAGE_DISABLED');
			}
		}

		$this->vars(array(
			'PAGE' => $pages_str
		));
		return $this->parse();
	}

	function get_watercraft_properties($arr)
	{
		$type = $arr['type'];

		$o = new object();
		$o->set_class_id(CL_WATERCRAFT);
		$o->set_prop('watercraft_type', $type);

		$props = $o->get_property_list();
		$elements = array();
		foreach ($props as $name => $prop)
		{
			// let the watercraft class handle which properties should be shown:
			$prop_retval = $this->watercraft_inst->get_property(array(
				'prop' => &$prop,
				'obj_inst' => $o,
			));
			$tab_retval = $this->watercraft_inst->callback_mod_tab(array(
				'id' => $prop['group'],
				'obj_inst' => $o
			));

			if ($prop_retval == PROP_OK && $tab_retval === true)
			{
				$elements[$name] = $prop;
			}
		}
		return $elements;
	}
	/** Generate a list of realestate objects added by user 
		
		@attrib name=my_watercraft_list is_public="1" caption="Minu vees&otilde;idukid"

	**/
	function my_watercraft_list($arr)
	{
		$this->read_template('list.tpl');

		$uid = aw_global_get('uid');

		$ol = new object_list(array(
			'class_id' => CL_WATERCRAFT,
			'createdby' => $uid,
		));
		foreach ($ol->arr() as $o)
		{
			$watercraft_name = $o->name();
			$this->vars(array(
				'name' => ( empty($watercraft_name) ) ? t('(Nimetu)') : $watercraft_name,
				'change_url' => aw_ini_get('baseurl').'/'.$o->meta('added_from_section').'?watercraft_id='.$o->id()
			));

			$result .= $this->parse('ITEM'); 
		}

		$this->vars(array(
			'ITEM' => $result
		));

		return $this->parse();
	}

	function do_db_upgrade($table, $field, $query, $error)
	{

		if (empty($field))
		{
			// db table doesn't exist, so lets create it:
			$this->db_query('CREATE TABLE '.$table.' (
				oid INT PRIMARY KEY NOT NULL,
				
				watercraft_type int,
				watercraft_management int
			)');
			return true;
		}

		switch ($field)
		{
			case 'watercraft_type':
			case 'watercraft_management':
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
