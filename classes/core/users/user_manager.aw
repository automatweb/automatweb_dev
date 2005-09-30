<?php
// $Header: /home/cvs/automatweb_dev/classes/core/users/user_manager.aw,v 1.1 2005/09/30 11:58:28 ekke Exp $
// user_manager.aw - Kasutajate haldus 
/*
HANDLE_MESSAGE_WITH_PARAM(MSG_POPUP_SEARCH_CHANGE,CL_USER_MANAGER, on_popup_search_change)

@classinfo syslog_type=ST_USER_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property root type=relpicker reltype=RELTYPE_ROOT field=meta method=serialize 
@caption Juurkaust/-grupp
@comment Hallata saab selle objekti all olevaid gruppe ja kasutajaid

@groupinfo users caption=Kasutajad 
@default group=users

@layout hbox_toolbar type=hbox

@property users_tb type=toolbar store=no no_caption=1 editonly=1 parent=hbox_toolbar 

@layout hbox_data type=hbox width=20%:80%

@layout vbox_users_tree type=vbox parent=hbox_data 

@property groups_tree type=treeview no_caption=1 store=no parent=vbox_users_tree
@caption Puu

@layout vbox_users_content type=vbox parent=hbox_data

@property table_groups type=table store=no no_caption=1 parent=vbox_users_content
@caption Grupid 

@property table_users type=table store=no no_caption=1 parent=vbox_users_content
@caption Kasutajad 




@reltype ROOT value=1 clid=CL_GROUP,CL_MENU
@caption Juurkaust/-grupp

*/

class user_manager extends class_base
{
	var $parent = null;
	var $permissions_form;

	function user_manager()
	{
		$this->permissions_form = "<p class='plain'>".t("Vali õigused").":<br>".html::checkbox(array(
			'name' => 'sel_can_view',
			'caption' => t("Vaatamine"),
		));
		$this->permissions_form .= "<br>".html::checkbox(array(
			'name' => 'sel_can_edit',
			'caption' => t("Muutmine"),
		));
		$this->permissions_form .= "<br>".html::checkbox(array(
			'name' => 'sel_can_delete',
			'caption' => t("Kustutamine"),
		));
		$this->permissions_form .= "<br>".html::checkbox(array(
			'name' => 'sel_can_add',
			'caption' => t("Lisamine"),
		));
		$this->permissions_form .= "<br>".html::checkbox(array(
			'name' => 'sel_can_admin',
			'caption' => t("ACL Muutmine"),
		));
		$this->permissions_form .= "</p>";
	
		$this->permissions_form .= "</p>";
	
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "core/users/user_manager",
			"clid" => CL_USER_MANAGER
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
			case 'users_tb':
				$this->do_users_toolbar(&$prop['toolbar'], $arr);
			break;
			case 'table_groups':
				$this->do_table_groups($prop['vcl_inst'], $arr);
			break;
			case 'table_users':
				$this->do_table_users($prop['vcl_inst'], $arr);
			break;
			case 'groups_tree':
				$parent = $arr['obj_inst']->prop('root');
				if (!$parent)
				{
					$prop['error'] = t("Juurkaust/-grupp valimata");
					return PROP_ERROR;
				}
				$prop['vcl_inst']->start_tree(array(
					'type' => TREE_DHTML,
					'root_name' => t("Grupid"),
					'root_url' => aw_url_change_var("parent", 0),
					'has_root' => true,
				));
				$this->do_groups_tree($prop['vcl_inst'], $parent, 0);
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
			case 'table_groups':
				// Save priority values
				if (isset($arr['request']['priority']) && is_array($arr['request']['priority']))
				{
					foreach ($arr['request']['priority'] as $oid => $value)
					{
						$o = obj($oid);
						if (is_numeric($value) && is_oid($oid) && $this->can('edit', $oid) && $o->class_id() == CL_GROUP
							&& $o->prop('priority') != $value)
						{
							$o->set_prop('priority', $value);
							$o->save();
						}
					}
				}
			break;

		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr['last_parent'] = $this->parent;
		$arr['ob_group'] = 'um';
		$arr["post_ru"] = post_ru();
	}

	function callback_pre_edit($arr)
	{
		$this->parent = $this->find_parent($arr['obj_inst']);
	}

	// Adds content to users toolbar
	function do_users_toolbar(&$tb, $arr)
	{	
		if(!isset($this->parent))
		{
			return "";
		}
		
		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));
		
		$clss = aw_ini_get("classes");
		foreach(array(CL_USER, CL_GROUP) as $clid)
		{
			$tb->add_menu_item(array(
				'parent' => 'add_item',
				'text' => $clss[$clid]['name'],
				'disabled' => !$this->can('edit', $this->parent),
				'link' =>$this->mk_my_orb("new", array(
					'parent' => $this->parent,
					'return_url' => urlencode(aw_global_get('REQUEST_URI')),
				), $clid)
			));
		}
		
		$tb->add_button(array(
			'name' => 'import',
			'tooltip' => t("Impordi"),
			'img' => 'import.gif',
			'action' => $this->mk_my_orb('import', array("parent" => $this->parent)),
		));	

		$tb->add_separator();

		// Copypaste buttons
		$this->do_objectbuffer_toolbar(array(
			'toolbar' => &$tb,
			'ob_group' => 'um',
		)); 

		$tb->add_separator();

		$tb->add_button(array(
			'name' => 'delete',
			'tooltip' => t("Kustuta valitud"),
			'img' => 'delete.gif',
			"url" => "javascript:if(confirm('".t("Kustutada valitud objektid?")."')){submit('delete')};",
		));	

	}
	
	// Create cut, copy and paste buttons, if needed
	function do_objectbuffer_toolbar($arr)
	{
		if (!isset($arr['toolbar']))
		{
			return;
		}
		$cut_action = isset($arr['cut_action']) ? $arr['cut_action'] : 'ob_cut';
		$copy_action = isset($arr['copy_action']) ? $arr['copy_action'] : 'ob_copy';
		$paste_action = isset($arr['paste_action']) ? $arr['paste_action'] : 'ob_paste';
		$prefix = isset($arr['ob_group']) ? $arr['ob_group'].'_' : '';
		
		
		$tb =& $arr['toolbar'];
		$tb->add_button(array(
			'name' => 'cut',
			'tooltip' => t("L&otilde;ika"),
			'img' => 'cut.gif',
			'action' => $cut_action, 
		));	
		$tb->add_button(array(
			'name' => 'copy',
			'tooltip' => t("Kopeeri"),
			'img' => 'copy.gif',
			'action' => $copy_action,
		));	
		
		$tooltip = "Ei saa asetada";
		$disabled = true;
		$cut_objects = safe_array(aw_global_get('cut_objects'));
		$copy_objects = safe_array(aw_global_get('copied_objects'));
		if ((count($cut_objects) || count($copy_objects)) && $this->can('view', $this->parent))
		{
			$tooltip = "";
			$names = array();
			foreach (array_keys($cut_objects) as $oid)
			{
				$o = obj($oid);
				$names[] = $o->name();
			}
			if (count($names))
			{
				$tooltip = t('L&otilde;igatud').": ".join(",",$names);
				$tooltip .= " \n";
			}
			$names = array();
			foreach (array_keys($copy_objects) as $oid)
			{
				$o = obj($oid);
				$names[] = $o->name();
			}
			if (count($names))
			{
				$tooltip .= t('Kopeeritud').": ".join(",",$names);
			}
			$disabled = false;
		}
		
		$tb->add_button(array(
			'name' => 'paste',
			'tooltip' => t("Aseta")."\n".' ('.$tooltip.')',
			'img' => 'paste.gif',
			'action' => $paste_action,
			'disabled' => $disabled,
		));	
		
	}
	
	/** deletes selected objects

		@attrib name=delete
		
		@param sel_u optional
		@param sel_g optional
		@param post_ru optional
	**/
	function delete($arr)
	{
		$selected = safe_array(ifset($arr,'sel_g')) + safe_array(ifset($arr,'sel_u'));
		if (count($selected))
		{
			$o = get_instance("admin/admin_menus");
			$o->new_delete(array('sel' => $selected));
		}
		return $arr['post_ru'];
	}		

	/** cuts objects

		@attrib name=ob_cut
		@param sel_u optional
		@param sel_g optional
		@param post_ru optional

	**/
	function ob_cut($arr)
	{
		$selected = safe_array(ifset($arr,'sel_g')) + safe_array(ifset($arr,'sel_u'));
		if (count($selected))
		{
			aw_session_del('copied_objects');
		
			$o = get_instance("admin/admin_menus");
			return $o->cut(array('sel' => $selected, 'return_url' => $arr['post_ru']));
		}
	}		

	/** copies objects

		@attrib name=ob_copy

		@param sel_u optional
		@param sel_g optional
		@param post_ru optional

	**/
	function ob_copy($arr)
	{
		$selected = safe_array(ifset($arr,'sel_g')) + safe_array(ifset($arr,'sel_u'));
		if (count($selected))
		{
			aw_session_del('cut_objects');
		
			$o = get_instance("admin/admin_menus");
			return $o->copy(array('sel' => $selected, 'return_url' => $arr['post_ru']));
		}
		
		//$prefix = isset($arr['ob_group']) ? $arr['ob_group'].'_' : '';
/*		$copy_objects = array();
		if (count($selected) && is_oid(ifset($arr,'last_parent')))
		{
			foreach ($selected as $oid => $value)
			{
				if ($value && $this->can('edit', $oid) && !isset($copy_objects[$oid]))
				{
					$copy_objects[$oid] = array(
						'oid' => $oid,
						'from' => $arr['last_parent'],
					);
				}
			}
		}
		aw_session_set($prefix.'copy_objects', $copy_objects);
		return $arr['post_ru'];
		*/
	}
	
	/** pastes the cut/copied objects

		@attrib name=ob_paste

	**/
	function ob_paste($arr)
	{
		if(!is_oid(ifset($arr,'last_parent')))
		{
			return;
		}
		$o = get_instance("admin/admin_menus");
		return $o->paste(array('parent' => $arr['last_parent'], 'return_url' => $arr['post_ru']));
		
/*
		$prefix = isset($arr['ob_group']) ? $arr['ob_group'].'_' : '';
		
		$cut_objects = safe_array(aw_global_get($prefix.'cut_objects'));
		$copy_objects = safe_array(aw_global_get($prefix.'copy_objects'));
		
		// first copied persons
		foreach(safe_array($copy_objects) as $oid => $data)
		{
			if (!(is_oid($oid) && $this->can("edit", $oid)))
			{
				continue;
			}

			$o = obj($oid);
			switch($o->class_id())
			{
				case CL_USER: // on copy we need just to add a connection, on cut only move 
				break;
				case CL_GROUP: // change parent
				break;
			}
		}

		
		
		return $arr['post_ru'];
		*/
	}
	
	/** blocks/unblocks a user 

		@attrib name=block_u

		@param oid required type=int acl=edit class=CL_USER
		@param post_ru required

	**/
	function block_u($arr)
	{
		$o = obj($arr['oid']);
		$o->set_prop('blocked', !$o->prop('blocked'));
		$o->save();
		return $arr['post_ru'];
	}

	// Recursively populates groups tree
	function do_groups_tree(&$tree, $parent, $treeroot = 1)
	{
		$ol = new object_list(array(
			'parent' => $parent,
			'class_id' => CL_GROUP,
		));
		
		for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$tree->add_item($treeroot ? $parent : 0,array(
				'id' => $o->id(),
				'name' => strlen($o->name()) ? $o->name() : '('.t("nimetu").' '.$o->id().')' ,
				'url' => aw_url_change_var('parent', $o->id()),
			));
			// make kids
			$this->do_groups_tree(&$tree, $o->id());
		}
	}

	// Defines and populates users table
	function do_table_users (&$table, $arr)
	{
		if (!isset($this->parent))
		{
			return;
		}
		$fields = array(
			array(
				'name' => 'username',
				'caption' => t("Kasutajanimi"),
			),
			array(
				'name' => 'name',
				'caption' => t("P&auml;risnimi"),
			),
			array(
				'name' => 'company',
				'caption' => t("Organisatsioon"),
			),
			array(
				'name' => 'mail',
				'caption' => t("E-post"),
			),
			array(
				'name' => 'last_active',
				'caption' => t("Viimane tegevus"),
			),
			array(
				'name' => 'block',
				'caption' => t("Blokeeritud"),
				'tooltip' => t("Kasutaja blokeerimine s&uuml;steemist"),
			),
			array(
				'name' => 'groups',
				'caption' => t("Grupid"),
			),
			array(
				'name' => 'action',
				'caption' => t("Tegevus"),
				'sortable' => false,
				'align' => 'center',
			),
		);	
		foreach ($fields as $f)
		{
			 // By default fields are sortable and aligned to right
			$f['sortable'] = isset($f['sortable']) ? $f['sortable'] : true;
			$f['align'] = isset($f['align']) ? $f['align'] : 'right';
			$f['chgbgcolor'] = 'cutcopied';
			$table->define_field($f);
		}
		$table->define_chooser(array(
			'field' => 'oid',
			'name' => 'sel_u',
			'chgbgcolor' => 'cutcopied',
		));

		$table->set_header("ja kasutajad".$link);	

		// Now, find data for the table
		$g = obj($this->parent);
		$users = $g->connections_from(array(
			'type' => 'RELTYPE_MEMBER',
			'class' => CL_USER,
		));

		$df = aw_ini_get('config.dateformats');
		foreach ($users as $c)
		{
			$o = $c->to();
			if (!$this->can('view', $o->id()))
			{
				continue;
			}
			
			$ccp = (isset($_SESSION["copied_objects"][$o->id()]) || isset($_SESSION["cut_objects"][$o->id()]) ? "#E2E2DB" : "");
		
			// Find user's groups
			$conns = $o->connections_to(array(
				'type' => 'RELTYPE_MEMBER',
				'from.class_id' => CL_GROUP,
			));
			$groups = array();
			foreach ($conns as $c)
			{
				$from = $c->from();
				$groups[ html::href(array(
					'caption' => $c->prop('from.name'),
					'url' => aw_url_change_var('parent', $c->prop('from')),
				)) ] = $from->prop('priority');;
			}
			arsort($groups); // Sort groups by priority
			$groups = array_keys($groups);

			// Find user's company, if CL_USER has CL_CRM_PERSON
			if ($person = $o->get_first_obj_by_reltype('RELTYPE_PERSON'))
			{
				$conns = $person->connections_from(array(
					'type' => 'RELTYPE_WORK',
					'class' => CL_CRM_COMPANY,
				));
				$companies = array();
				foreach ($conns as $c)
				{
					$companies[] = html::href(array(
						'caption' => $c->prop('to.name'),
						'url' => $this->mk_my_orb("change", array(
							'id' => $c->prop('to'),
							'return_url' => urlencode(aw_global_get("REQUEST_URI"))
						), CL_CRM_COMPANY),
					));		
				}
			}
		
			$items = array( // Edit-menu items
				$this->mk_my_orb("change", array(
						'id' => $o->id(),
						'return_url' => urlencode(aw_global_get("REQUEST_URI"))
					), CL_USER) => t("Muuda"),
				$this->mk_my_orb("block_u", array("oid" => $o->id(), "post_ru" => get_ru())) => $o->prop('blocked') ? t("Blokeering maha") : t("Blokeeri"),
				$this->mk_my_orb("ob_cut", array("sel_u[".$o->id()."]" => 1, "post_ru" => get_ru())) => t("L&otilde;ika"),
				$this->mk_my_orb("ob_copy", array("sel_u[".$o->id()."]" => 1, "post_ru" => get_ru())) => t("Kopeeri"),
				$this->mk_my_orb("change", array(
						'id' => $o->id(),
						'return_url' => urlencode(aw_global_get("REQUEST_URI")),
						'group' => 'chpwd'
					), CL_USER) => t("Muuda parooli"),
				$this->mk_my_orb("change", array(
						'id' => $o->id(),
						'return_url' => urlencode(aw_global_get("REQUEST_URI")),
						'group' => 'stat'
					), CL_USER) => t("Vaata statistikat"),
			);

			$row = array(
				'username' => $o->prop('uid'),
				'name' => html::href(array(
					'caption' => strlen($o->prop('real_name')) ? $o->prop('real_name') : '('.t("nimetu").')',
					'url' => $this->mk_my_orb("change", array(
						'id' => $o->id(),
						'return_url' => urlencode(aw_global_get("REQUEST_URI"))
					), CL_USER),
				)),
				'company' => join(', ', $companies),
				'mail' => $o->prop('email'),
				'last_active' => date($df[2], $o->prop('lastaction')),
				'block' => $o->prop('blocked') ? t("Jah") : t("Ei"),
				'groups' => join(', ', $groups),
				'action' => $this->_get_menu(array(
					'id' => $o->id(),
					'items' => $items,
				)),
				'oid' => $o->id(),
				'cutcopied' => $ccp,
			);
			$table->define_data($row);
		}
	}

	// Defines and populates groups table
	function do_table_groups (&$table, $arr)
	{
		if (!isset($this->parent))
		{
			return;
		}
		$fields = array(
			array(
				'name' => 'gid',
				'caption' => t("Grupi ID"),
				'numeric' => 1,
			),
			array(
				'name' => 'name',
				'caption' => t("Nimi"),
			),
			array(
				'name' => 'priority',
				'caption' => t("Prioriteet"),
			),
			array(
				'name' => 'modified',
				'caption' => t("Muutmise aeg"),
			),
			array(
				'name' => 'modified_by',
				'caption' => t("Muutja"),
			),
			array(
				'name' => 'aw',
				'caption' => t("AW"),
				'tooltip' => t("Lubada administreerimiskeskkonda"),
			),
			array(
				'name' => 'status',
				'caption' => t("Aktiivne"),
			),
			array(
				'name' => 'members',
				'caption' => t("Liikmeid"),
				'numeric' => 1,
			),
			array(
				'name' => 'rootfolders',
				'caption' => t("Juurkaustad"),
			),
			array(
				'name' => 'action',
				'caption' => t("Tegevus"),
				'sortable' => false,
				'align' => 'center',
			),
		);	
		foreach ($fields as $f)
		{
			 // By default fields are sortable and aligned to right
			$f['sortable'] = isset($f['sortable']) ? $f['sortable'] : true;
			$f['align'] = isset($f['align']) ? $f['align'] : 'right';
			$f['chgbgcolor'] = 'cutcopied';
			$table->define_field($f);
		}
		$table->define_chooser(array(
			'field' => 'oid',
			'name' => 'sel_g',
			'chgbgcolor' => 'cutcopied',
		));

		$g = obj($this->parent);
		$table->set_header(sprintf(t("'%s' alamgrupid"), $g->name() ? $g->name() : '('.t("nimetu").' '.$g->id().')'));


		// Now, find data for the table

		$ol = new object_list(array(
			'parent' => $this->parent,
			'class_id' => CL_GROUP,
		));

		$df = aw_ini_get('config.dateformats');
		for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			if (!$this->can('view', $o->id()))
			{
				continue;
			}
			
			$ccp = (isset($_SESSION["copied_objects"][$o->id()]) || isset($_SESSION["cut_objects"][$o->id()]) ? "#E2E2DB" : "");
		
			$conns = $o->connections_from(array(
				'type' => 'RELTYPE_MEMBER',
				'class' => CL_USER,
			));
			$members = count($conns);
			
			$rootfolders = array();
			$rootmenus = $o->prop('admin_rootmenu2');
			if (isset($rootmenus[aw_global_get('lang_id')]))
			{
				foreach ($rootmenus[aw_global_get('lang_id')] as $jrk => $menu)
				{
					$o_menu = obj($menu);
					$rootfolders[] = $o->name();
				}
			}
			
			$delurl = $this->mk_my_orb("delete", array(
				"sel_g[".$o->id()."]" => "1", 
				'post_ru' => urlencode(get_ru()),
			));
			$delurl = "javascript:if(confirm('".t("Kustutada valitud objekt?")."')){window.location='$delurl';};";
			
			// Create links for Rootfolder and Objects popup items
			$html = $this->permissions_form . html::hidden(array(
					'name' => 'oid_rootf',
					'value' => $o->id(),
				));
				
			$url_rootfolder = "javascript:aw_popup_scroll('".$this->mk_my_orb("do_search", array(
						"id" => $arr["obj_inst"]->id(),
						"pn" => "table_groups",
						"clid" => CL_MENU,
						"append_html" => urlencode((str_replace(array("'","\n"),"",$html))),
					), 'popup_search')."','Vali',550,500)";
					
			$html = $this->permissions_form . html::hidden(array(
					'name' => 'oid_objects',
					'value' => $o->id(),
				));
			$url_objects = "javascript:aw_popup_scroll('".$this->mk_my_orb("do_search", array(
						"id" => $arr["obj_inst"]->id(),
						"pn" => "table_groups",
						"clid" => 0, // Any class
						"append_html" => ((urlencode(str_replace("&","%26",str_replace(array("'","\n"),"",($html)))))),
					), 'popup_search')."','".t("M&auml;&auml;ra &otilde;igused")."',550,500)";
			
			$items = array( // Edit-menu items
				$this->mk_my_orb("change", array(
						'id' => $o->id(),
						'return_url' => urlencode(aw_global_get("REQUEST_URI"))
					), CL_GROUP) => t("Muuda"),
				$delurl => t("Kustuta"),
				$this->mk_my_orb("ob_cut", array("sel_g[".$o->id()."]" => 1, "post_ru" => get_ru())) => t("L&otilde;ika"),
//				$this->mk_my_orb("ob_copy", array("sel_g[".$o->id()."]" => 1, "post_ru" => get_ru())) => t("Kopeeri"),
				//$this->mk_my_orb("imp", array("sel_fld" => array($id), "post_ru" => get_ru())) => t("Impordi"),
				$this->mk_my_orb("change", array(
						'id' => $o->id(),
						'group' => 'import',
						'return_url' => urlencode(aw_global_get("REQUEST_URI"))
					), CL_GROUP) => t("Impordi"),
				$url_rootfolder => t("Juurkaust"),
				$url_objects => t("Objektid"),
			);
		
			$row = array(
				'gid' => $o->prop('gid'),
				'name' => html::href(array(
					'caption' => strlen($o->name()) ? $o->name() : '('.t("nimetu").' '.$o->id().')',
					'url' => aw_url_change_var('parent', $o->id()),
				)),
				'priority' => html::textbox(array(
					'name' => 'priority['.$o->id().']', 
					'size' => 6, 
					'value' => $o->prop('priority'), 
					'disabled' => $this->can('edit', $o->id()) ? false : true 
				)),
				'modified' => date($df[2], $o->prop('modified')),
				'modified_by' => $o->modifiedby(),
				'aw' => $o->prop('can_admin_interface') ? t("Jah") : t("Ei"),
				'status' => $o->prop('status') == STAT_ACTIVE ? t("Jah") : t("Ei"),
				'members' => $members, 
				'rootfolders' => join(', ', $rootfolders),
				'action' => $this->_get_menu(array(
					'id' => $o->id(),
					'items' => $items,
				)),
				'oid' => $o->id(),
				'cutcopied' => $ccp,
			);
			$table->define_data($row);
		}
	}

	// Returns current parent group/menu OID.
	// ?parent=<parent oid>
	function find_parent($manager)
	{
		$parent = $manager->prop('root');
		
		if (!$parent)
		{
			return null;
		}
		
		if (isset($_GET['parent']) && is_oid($_GET['parent']) && $this->can('view', $_GET['parent']) 
			&& ($p = obj($_GET['parent'])) && in_array($p->class_id(), array(CL_MENU,CL_GROUP)) )
		{
			foreach ($p->path() as $i => $ancestor)
			{
				if ($ancestor->id() == $parent)
				{
					$parent = $_GET['parent'];
					break;
				}
			}
		}
	
		return $parent;
	}

	// Creates popup menu html
	/*
	 	Yanked from class_designer_manager, there should be something easier

		id - menu id (may be any oid)
		icon - url for icon file
	*/
	function _get_menu($arr)
	{
		if (!isset($arr['id']) || !is_oid($arr['id']) || !$this->can('view', $arr['id']) || !is_array(ifset($arr, "items")))
		{
			return "";
		}
		$items = $arr['items'];
	
		$this->tpl_init("automatweb/menuedit");
		$this->read_template("js_popup_menu.tpl");

		$this->vars(array(
			"menu_id" => "menu-".$arr['id'],
			"menu_icon" => $this->cfg["baseurl"]."/automatweb/images/blue/obj_settings.gif",
		));
	
			
		$mi = "";
		foreach($items as $url => $txt)
		{
			$this->vars(array(
				"link" => $url,
				"text" => $txt
			));
			$mi .= $this->parse("MENU_ITEM");
		}

		$this->vars(array(
			"MENU_ITEM" => $mi
		));
		return $this->parse();
	}
	
	/** message handler for the MSG_POPUP_SEARCH_CHANGE message so we can 
	
	**/
	function on_popup_search_change($arr)
	{
		$arr = $arr['arr'];
		if (isset($arr['oid_rootf']) && is_oid($arr['oid_rootf']) && $this->can('edit', $arr['oid_rootf']) )
		{
			$o = obj($arr['oid_rootf']);
			
			// First find rootmenu active values
			$m = $o->prop('admin_rootmenu2');
			$lang = aw_global_get('lang_id');
			if (!is_array(ifset($m,$lang)))
			{
				$m[$lang] = array();
			}
			
			
			// Create connections from group to objects
			foreach (safe_array(ifset($arr,'sel')) as $x => $id)
			{
				$o->connect(array(
					'to' => $id,
					'reltype' => 'RELTYPE_ADMIN_ROOT',
				));
				$m[$lang][] = $id;
			}
			$m[$lang] = array_unique($m[$lang]);
			
			// Update group rootmenu property
			$o->set_prop('admin_rootmenu2', $m);
			$o->save();

			$arr['oid_objects'] = $arr['oid_rootf']; // This enables next section to set permissions to rootmenus
		}

		if (isset($arr['oid_objects']) && is_oid($arr['oid_objects']) && $this->can('edit', $arr['oid_objects']) )
		{
			$o = obj($arr['oid_objects']);
			if ($o->class_id() != CL_GROUP)
			{
				return;
			}
			$o_i = $o->instance();
			$gid = $o_i->users->get_gid_for_oid($o->id());
			$a = $o_i->acl_list_acls();
			
			$acl = array();
			// Create ACL settings array
			foreach ($a as $a_bp => $a_name)
			{
				$acl[$a_name] = ifset($arr,'sel_'.$a_name);
			}
			// Create connections from selected objects to group 
			foreach (safe_array(ifset($arr,'sel')) as $x => $id)
			{
				$s = obj($id);
				$s->connect(array(
					'to' => $o->id(),
					'reltype' => RELTYPE_ACL, 
				));
				$s->save();
				$o_i->add_acl_group_to_obj($gid, $id, $acl);
			}
		}
	}
}
?>
