<?php
// $Header: /home/cvs/automatweb_dev/classes/pank/pank.aw,v 1.1 2004/07/15 06:49:26 rtoomas Exp $
// crm_pank.aw - Pank 
/*
@classinfo syslog_type=ST_PANK relationmgr=yes
@tableinfo pank index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

@default table=pank

@groupinfo account_overview caption="Konto ülevaade"

@default group=account_overview

@property account_balance type=textbox field=account_balance
@caption Konto saldo

@groupinfo accounts caption="Kontod" submit=no
@caption Kontod
@default group=accounts

@property accounts_table type=table no_caption=1
@caption Kontod

@groupinfo pank_owner_group caption="..." submit=no
@groupinfo pank_owner_group_main_sub caption="..." submit=no parent=pank_owner_group

@default group=pank_owner_group_main_sub

@layout main_toolbar_hbox type=hbox group=pank_owner_group_main_sub

@property main_toolbar type=toolbar parent=main_toolbar_hbox no_caption=1
@caption Suurepärane toolbar

@layout main_hbox type=hbox group=pank_owner_group_main_sub width=20%:80%

@layout main_vbox_left type=vbox group=pank_owner_group_main_sub parent=main_hbox

@property main_treeview type=treeview parent=main_vbox_left no_caption=1
@caption Treeview

@layout main_vbox_right type=vbox group=pank_owner_group_main_sub parent=main_hbox

@property main_company_info type=text parent=main_vbox_right no_caption=1 store=no
@caption Firma info

@property main_table type=table parent=main_vbox_right no_caption=1
@caption Tabel

@groupinfo pank_make_trans caption="Tee ülekanne" 
@default group=pank_make_trans

@layout trans_hbox type=hbox group=pank_make_trans width=20%:20%:30%

@layout trans_vbox_left type=vbox group=pank_make_trans parent=trans_hbox

@property konto_caption type=text parent=trans_vbox_left no_caption=1 value=Konto store=no
@caption kapten

@property from_account type=select parent=trans_vbox_left no_caption=1 store=no
@caption Konto

@layout trans_vbox_middle type=vbox group=pank_make_trans parent=trans_hbox 

@property to_company_caption type=text parent=trans_vbox_middle no_caption=1 value=Saaja store=no
@caption kapten

@property to_company type=select parent=trans_vbox_middle no_caption=1 store=no
@caption Kellele

@layout trans_vbox_right type=vbox group=pank_make_trans parent=trans_hbox

@property summa_caption type=text parent=trans_vbox_right no_caption=1 value=Summa store=no
@caption kapten

@property summa type=textbox parent=trans_vbox_right no_caption=1 size=10 store=no
@caption Summa

@layout trans_vbox_last type=vbox group=pank_make_trans parent=trans_hbox

@property submit_caption type=text parent=trans_vbox_last no_caption=1 value=Soorita store=no

@property submit type=submit parent=trans_vbox_last no_caption=1 size=10 store=no 
@caption Soorita

@reltype OWNER value=1 clid=CL_CRM_COMPANY 
@caption Kellele kuulub

*/

class pank extends class_base
{
	function pank()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "pank/pank",
			"clid" => CL_PANK
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
			case 'main_toolbar':
				$this->do_main_toolbar(&$arr);
				break;
			case 'main_company_info':
				if(is_oid($arr['request']['company']))
				{
					$this->do_company_info($arr);
				}
				break;
			case 'main_table':
				if(is_oid($arr['request']['company']))
				{
					$this->do_company_info_table($arr);	
				}
				break;
			case 'main_treeview':
				$this->do_main_treeview($arr);
				break;
			case 'accounts_table':
				$this->do_accounts_table($arr);
				break;
			case 'from_account':
				$ol = new object_list(array(
						'parent' => $arr['obj_inst']->id(),
						'class_id' => CL_ACCOUNT
				));
				$prop['options'] = $ol->list_names;
				break;
			case 'to_company':
				$company = get_instance(CL_CRM_COMPANY);
				$clients = array();
				$comp = $this->get_owner($arr['obj_inst']);
				if($comp)
				{
					$company->get_customers_for_company($comp, &$clients);
					$ol = new object_list(array(
							'class_id' => CL_CRM_COMPANY,
							'oid' => $clients
					));
					$prop['options'] = $ol->list_names;
				}
				break;
		};
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
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function do_accounts_table($arr)
	{
		$table = &$arr['prop']['vcl_inst'];

		$table->define_field(array(
			'name' => 'name',
			'caption' => 'Nimi',
		));

		$table->define_field(array(
			'name' => 'saldo',
			'caption'=> 'Saldo',
			'type' => 'int',
		));

		$ol = new object_list(array(
					'class_id' => CL_ACCOUNT,
					'parent' => $arr['obj_inst']->id()
		));

		for($o=$ol->begin();!$ol->end();$o=$ol->next())
		{
			$table->define_data(array(
				'name' => $o->prop('name'),				
				'saldo' => $o->prop('account_balance'),
			));
		}
	}

	function callback_mod_tab($arr)
	{
		if($arr['id']=='pank_owner_group' && is_oid($arr['obj_inst']->id()))
		{
			$company = $this->get_owner($arr['obj_inst']);
			if($company)
			{
				$arr['caption'] = $company->prop('name');
			}			
		}
		else if($arr['id']=='pank_owner_group_main_sub' && is_oid($arr['obj_inst']->id()))
		{
			$company = $this->get_owner($arr['obj_inst']);
			if($company)
			{
				$arr['caption'] = $company->prop('name');
			}			
		}
	}

	function do_main_treeview($arr)
	{
		$tree = &$arr['prop']['vcl_inst'];
		
		$counter = 5;
		$tree_node_info = array(
			'id' => 1,
			'name' => 'Projektid',
		);
		$tree->add_item(0, $tree_node_info);
		$obj = $this->get_owner($arr['obj_inst']);
		if($obj)
		{
			$this->do_main_tree_projects(&$tree, $obj, 1, &$counter);
		}
		
		$tree_node_info = array(
			'id' => 2,
			'name' => 'Tegijad',
		);
		$tree->add_item(0, $tree_node_info);
		
		$tree_node_info = array(
			'id' => 3,
			'name' => 'Tegevused',
		);
		$tree->add_item(0, $tree_node_info);
		$tree_node_info = array(
			'id' => 4,
			'name' => 'Organisatsioonid',
		);
		$this->do_main_tree_orgs(&$tree, &$arr, 4, &$counter);
		
		$tree->add_item(0, $tree_node_info);
	}

	function do_main_tree_projects($tree, $obj, $parent, $ids)
	{
		if($obj)
		{
			$conns = $obj->connections_from(array(
							'type' => RELTYPE_PROJECT
			));
			classload('icons');
			foreach($conns as $conn)
			{
				$tree_node_info = array(
					'id' => $ids++,
					'name' => $conn->prop('to.name'),
					'iconurl' => icons::get_icon_url(CL_PROJECT),
					'url' => aw_url_change_var(array(
									'company' => $conn->prop('to'),
									'return_url' => ''
								))
				);
				$tree->add_item($parent, $tree_node_info);
			}
		}
		else
		{
			return null;
		}
	}

	function do_main_tree_orgs($tree, $arr,$parent, $ids)
	{
		$obj = $this->get_owner($arr['obj_inst']);
		if(sizeof($obj))
		{
			$company = get_instance(CL_CRM_COMPANY);
			$companies = array();
			$company->get_customers_for_company($obj, &$companies);
			classload('icons');
			foreach($companies as $key=>$value)
			{
				$obj = new object($value);
				$tree_node_info = array(
					'id' => $ids++,
					'name' => strlen($obj->prop('name'))>15?substr($obj->prop('name'),0,15)."...":$obj->prop('name'),
					'iconurl' => icons::get_icon_url(CL_CRM_COMPANY),
					'url' => aw_url_change_var(array(
									'company'=>$obj->id(),
									'return_url' => ''
								)),
				);
				$tree->add_item($parent, $tree_node_info);
				//kuvan ka kõik projektid selle kompanii alt
				$this->do_main_tree_projects(&$tree, &$obj, $ids-1, &$ids);
			}
		}
	}

	function get_owner($obj)
	{
		//let's get the company of this pank
		//kui vaadata järgmist 3 rida koodi kaugemalt, siis
		//tundub see olevat amb kõrvalt vaates
		$conns = $obj->connections_from(array(
						'type' => 'RELTYPE_OWNER'
		));

		if(sizeof($conns))
		{
			$obj = current($conns);
			return $obj->to();
		}
		else
		{
			return null;
		}
	}

	function do_company_info_table($arr)
	{
		$table = &$arr['prop']['vcl_inst'];
		
		$table->define_field(array(
			'name ' => 'from',
			'caption' => 'Kandja',			
		));

		$table->define_field(array(
			'name' => 'to',
			'caption' => 'Saaja'
		));

		$table->define_field(array(
			'name' => 'date',
			'caption' => 'Aeg'
		));

		$company = new object($arr['request']['company']);

		//now we have to get all the banking information for this company
		$conns = $company->connections_to(array(
						'type' => RELTYPE_OWNER
		));
	
		//very good, the company has a bank!
		if(sizeof($conns))
		{
			$bank = current($conns);
			$bank = $bank->from();

			//we have the bank!

			//let's list the accounts
			$ol = new object_list(array(
						'parent' => $bank->id(),
						'class_id' => CL_ACCOUNT
			));
			
			foreach($ol->arr() as $obj)
			{
				//krt ma ei teagi, präägast listin mõlema kontoga toimunud
				//aktsioonid
				$tmp_ol = new object_list(array(
								'parent' => $obj->id(),
								'class_id' => CL_TRANSACTION
				));

				foreach($tmp_ol->arr() as $tmp_obj)
				{
					$table->define_data(array(
								'from' => $tmp_obj->prop('trans_from'),
								'to' => $tmp_obj->prop('trans_to'),
								'sum' => $tmp_obj->prop('sum')
					));
				}
			}
		}
	}

	function do_company_info($arr)
	{
		$company = new object($arr['request']['company']);

		//now we have to get all the banking information for this company
		$conns = $company->connections_to(array(
						'type' => RELTYPE_OWNER
		));
	
		//very good, the company has a bank!
		if(sizeof($conns))
		{
			$bank = current($conns);
			$bank = $bank->from();
			
			//ill just get all the accounts
			$ol = new object_list(array(
						'parent' => $bank->id(),
						'class_id' => CL_ACCOUNT
			));


			$arr['prop']['value'] = 'Kontod:<br><br>';
			foreach($ol->arr() as $o)
			{
				$arr['prop']['value'].=$o->prop('name')."<br>";
			}
		}

	}

	function do_main_toolbar($arr)
	{
		$toolbar = &$arr['prop']['toolbar'];

		$toolbar->add_button(array(
			'name' => 'Tee ülekanne',
			'img' => 'objects/document.gif',
			'tooltip' => 'Soorita ülekanne',			
		));
	}

}
?>
