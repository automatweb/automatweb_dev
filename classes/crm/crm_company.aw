<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_company.aw,v 1.6 2003/12/09 18:34:39 duke Exp $
/*
@classinfo relationmgr=yes
@tableinfo kliendibaas_firma index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

@property navtoolbar type=toolbar store=no no_caption=1 group=general,overview editonly=1

@property name type=textbox size=30 maxlenght=255 table=objects
@caption Organisatsiooni nimi

@property comment type=textarea cols=65 rows=3 table=objects
@caption Kommentaar

@property reg_nr type=textbox size=10 maxlength=20 table=kliendibaas_firma
@caption Registri number

@property pohitegevus type=relpicker reltype=RELTYPE_TEGEVUSALAD table=kliendibaas_firma
@caption Põhitegevus

@property ettevotlusvorm type=relpicker reltype=RELTYPE_ETTEVOTLUSVORM table=kliendibaas_firma
@caption Õiguslik vorm

@property tooted type=relpicker reltype=RELTYPE_TOOTED method=serialize field=meta table=objects
@caption Tooted

@property kaubamargid type=textarea cols=65 rows=3 table=kliendibaas_firma
@caption Kaubamärgid

@property contact type=relpicker reltype=RELTYPE_ADDRESS table=kliendibaas_firma
@caption Aadress

@property tegevuse_kirjeldus type=textarea cols=65 rows=3 table=kliendibaas_firma
@caption Tegevuse kirjeldus

@property logo type=textbox size=40 method=serialize field=meta table=objects
@caption Organisatsiooni logo(url)

@property firmajuht type=chooser orient=vertical table=kliendibaas_firma 
@caption Organisatsiooni juht

@default group=overview
@groupinfo overview caption="Seotud tegevused"

@property progress type=text callback=callback_org_actions store=no no_caption=1
@caption org_actions

@reltype ETTEVOTLUSVORM value=1 clid=CL_CRM_CORPFORM
@caption Õiguslik vorm

@reltype ADDRESS value=3 clid=CL_CRM_ADDRESS
@caption Kontaktaadress

@reltype TEGEVUSALAD value=5 clid=CL_CRM_SECTOR
@caption Tegevusalad

@reltype TOOTED value=6 clid=CL_CRM_PRODUCT
@caption Tooted

@reltype CHILD_ORG value=7 clid=CL_CRM_COMPANY
@caption Tütar-organisatsioonid

@reltype WORKERS value=8 clid=CL_CRM_PERSON
@caption Töötajad

@reltype OFFER value=9 clid=CL_CRM_OFFER
@caption Pakkumine

@reltype DEAL value=10 clid=CL_CRM_DEAL
@caption Tehing

@reltype KOHTUMINE value=11 clid=CL_CRM_MEETING
@caption Kohtumine

@reltype CALL value=12 clid=CL_CRM_CALL
@caption Kõne

@classinfo no_status=1
			
*/
/*
CREATE TABLE `kliendibaas_firma` (
  `oid` int(11) NOT NULL default '0',
  `firma_nim` varchar(255) default NULL,
  `reg_nr` varchar(20) default NULL,
  `ettevotlusvorm` int(11) default NULL,
  `pohitegevus` int(11) default NULL,
  `tegevuse_kirjeldus` text,
  `contact` int(11) default NULL,
  `firmajuht` int(11) default NULL,
  `korvaltegevused` text,
  `kaubamargid` text,
  `tooted` text,
  PRIMARY KEY  (`oid`),
  UNIQUE KEY `oid` (`oid`),
  KEY `teg_i` (`pohitegevus`)
) TYPE=MyISAM;
*/

class crm_company extends class_base
{
	function crm_company()
	{
		$this->init(array(
			'clid' => CL_CRM_COMPANY,
			'tpldir' => 'firma',
		));
	}
	
	function get_property($arr)
	{
		$data = &$arr['prop'];
		$retval = PROP_OK;
		switch($data['name'])
		{
			case "firmajuht":
				$conns = $arr["obj_inst"]->connections_from(array(
					"type" => RELTYPE_WORKERS,
				));

				foreach($conns as $conn)
				{
					$data["options"][$conn->prop("to")] = $conn->prop("to.name");

				};
				break;
			
			case "navtoolbar":
				$this->firma_toolbar($arr);
				break;
			
		};
		return $retval;
	}
	
	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
		};
		return $retval;
	}	

	function callback_org_actions($args)
	{
		/// XXX: I need to rewrite this and put in some more logical place
		$ob = $args["obj_inst"];
		$conns = $ob->connections_from();

		$t = new aw_table(array(
			'prefix' => 'org_actions',
		));

		$t->set_default_sortby('changed');//peaks otsima docust sündmuse kellaaja

		$t->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');

		$t->define_field(array(
			'name' => 'name',
			'caption' => 'Nimi',
			'sortable' => '1',
		));
		
		$t->define_field(array(
			'name' => 'type',
			'caption' => 'Tüüp',
			'sortable' => '1',
		));
		
		$t->define_field(array(
			'name' => 'moreinfo',
			'caption' => 'lisainfo',
			//'sortable' => '1',
		));
		
		$t->define_field(array(
			'name' => 'event_start',
			'caption' => 'Sündmus algab',
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'event_end',
			'caption' => 'Sündmus lõppeb',
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'createdby',
			'caption' => 'Looja',
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'modifiedby',
			'caption' => 'Muutja',
			'sortable' => '1',
		));

		$classes = $this->cfg["classes"];
	
		foreach($conns as $conn)
		{
			$item = new object($conn->prop("to"));
			
			$cldat = $classes[$item->class_id()];
			if (isset($cldat["alias"]))
			{
				if ($cldat["alias_class"])
				{
					$cldat["file"] = $cldat["alias_class"];
				}
			}
		
			// I need to filder the connections based on whether they write to calendar
			// or not.
			
			$t->define_data(array(
				'name' => html::href(array('caption' => $item->name(),'url' => $this->mk_my_orb('change', array('id' => $item->id()), $cldat["file"]))),
				// oh geez. so that is how it's done
				'type' => $cldat["name"],
				'event_start' => date('Y-m-d H:i',$item->prop("start1")),
				'event_end' => date('Y-m-d H:i',$item->prop("duration")),
				'moreinfo' => $doc['moreinfo'],
				'modifiedby' => $val['modifiedby'],
				'createdby' => $val['createdby'],
			));
		}
		$t->sort_by();
		
		$nodes = array();
		$nodes['actions'] = array(
			'value' => $t->draw(),
		);
		return $nodes;	
	}
	
	
	function firma_toolbar(&$args)
	{
		$toolbar = &$args["prop"]["toolbar"];
		$users = get_instance("users");

                $crm_db_id = $users->get_user_config(array(
                        "uid" => aw_global_get("uid"),
                        "key" => "kliendibaas",
                ));

		$cal_id = $users->get_user_config(array(
			"uid" => aw_global_get("uid"),
			"key" => "user_calendar",
		));

		// hm, I dunno but there seems to be a conflict here. Because you set the folders
		// through the crm_db class, which means that they can be different for each user
		if (empty($crm_db_id))
		{
			$parents[RELTYPE_ETTEVOTLUSVORM] = $parents[RELTYPE_WORKERS] = $parents[RELTYPE_ADDRESS] = $parents[RELTYPE_TEGEVUSALAD] = $args['obj_inst']->parent();
		}
		else
		{
			$crm_db = new object($crm_db_id);
			$default_dir = $crm_db->prop("dir_default");
			$parents[RELTYPE_ADDRESS] = $crm_db->prop("dir_address") == "" ? $default_dir : $crm_db->prop('dir_address');
			$parents[RELTYPE_TEGEVUSALAD] = $crm_db->prop("dir_tegevusala") == "" ? $default_dir : $crm_db->prop('dir_address');
			$parents[RELTYPE_WORKERS] = $crm_db->prop("dir_isik") == "" ? $default_dir : $crm_db->prop('dir_isik');
			$parents[RELTYPE_ETTEVOTLUSVORM] = $crm_db->prop("dir_ettevotlusvorm") == "" ? $default_dir : $crm_db->prop('dir_ettevotlusvorm');
		};

		if (!empty($cal_id))
		{
			$user_calendar = new object($cal_id);
			$parents[RELTYPE_CALL] = $parents[RELTYPE_OFFER] = $parents[RELTYPE_KOHTUMINE] = $parents[RELTYPE_DEAL] = $user_calendar->prop('event_folder');
		}

		$toolbar->add_menu_button(array(
			"name" => "main_menu",
			"tooltip" => "Uus",
		));

		$toolbar->add_sub_menu(array(
			"parent" => "main_menu",
			"name" => "calendar_sub",
			"text" => $this->cfg["classes"][CL_PLANNER]["name"],
		));
		
		$toolbar->add_sub_menu(array(
			"parent" => "main_menu",
			"name" => "firma_sub",
			"text" => $this->cfg["classes"][$this->clid]["name"],
		));

		$alist = array(RELTYPE_WORKERS,RELTYPE_TEGEVUSALAD,RELTYPE_ADDRESS,RELTYPE_ETTEVOTLUSVORM);
		foreach($alist as $key => $val)
		{
			$clids = $this->relinfo[$val]["clid"];
			foreach($clids as $clid)
			{
				$classinf = $this->cfg["classes"][$clid];

				$url = $this->mk_my_orb('new',array(
					'alias_to' => $args['obj_inst']->id(),
					'reltype' => $val,
					'title' => $classinf["name"].' : '.$args['obj_inst']->name(),
					'parent' => $parents[$val],
					'return_url' => urlencode(aw_global_get('REQUEST_URI')),
				),$clid);

				$has_parent = isset($parents[$val]) && $parents[$val];
				$disabled = $has_parent ? false : true;
				$toolbar->add_menu_item(array(
					"parent" => "firma_sub",
					"text" => 'Lisa '.$classinf["name"],
					"link" => $has_parent ? $url : "",
					"title" => $has_parent ? "" : "Kataloog määramata",
					"disabled" => $has_parent ? false : true,
				));
			};
		};

		// aha, I need to figure out which objects can be added to that relation type

		// basically, I need to create a list of relation types that are of any
		// interest to me and then get a list of all classes for those

		$action = array(RELTYPE_OFFER,RELTYPE_DEAL,RELTYPE_KOHTUMINE,RELTYPE_CALL);

		foreach($action as $key => $val)
		{
			$clids = $this->relinfo[$val]["clid"];
			$reltype = $this->relinfo[$val]["value"];
			foreach($clids as $clid)
			{
				$classinf = $this->cfg["classes"][$clid];
				$url = $this->mk_my_orb('new',array(
					// alright then. so what do those things to? 
					// they add a relation between the object created through
					// the planner and this object


					// can I do that with messages instead? and if I can, how
					// on earth am I going to do that?

					// I'm adding an event object to a calendar, how do I know
					// that I will have to attach it to an organization as well?
					
					// Maybe I should attach it directly to the organization and
					// then send a message somehow that it should be put in my
					// calendar as well .. hm that actually does sound
					// like a solution.
					'alias_to_org' => $args['obj_inst']->id(),
					'reltype_org' => $reltype,
					'class' => 'planner',
					'id' => $cal_id,
					'group' => 'add_event',
					'clid' => $clid,
					'action' => 'change',
					'title' => urlencode($classinf["name"].': '.$args['obj_inst']->name()),
					'parent' => $parents[$reltype],
					'return_url' => urlencode(aw_global_get('REQUEST_URI')),
				));
				$has_parent = isset($parents[$val]) && $parents[$val];
				$disabled = $has_parent ? false : true;
				$toolbar->add_menu_item(array(
					"parent" => "calendar_sub",
					"title" => $has_parent ? "" : "Kalender või kalendri sündmuste kataloog määramata",
					"text" => "Lisa ".$classinf["name"],
					"disabled" => $has_parent ? false : true,
					"link" => $has_parent ? $url : "",
				));
			};
		};
			
		if (!empty($cal_id))	
		{
			$toolbar->add_button(array(
				"name" => "user_calendar",
				"tooltip" => "Kasutaja kalender",
				"url" => $this->mk_my_orb('change', array('id' => $cal_id,'return_url' => urlencode(aw_global_get('REQUEST_URI')),),'planner'),
				"onClick" => "",
				"img" => "icon_cal_today.gif",
				"class" => "menuButton",
			));
		}
		
	}


}
?>
