<?php
// $Header: /home/cvs/automatweb_dev/classes/pank/pank.aw,v 1.2 2004/07/20 14:46:44 rtoomas Exp $
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

@property main_company_info type=table parent=main_vbox_right no_caption=1 store=no
@caption Firma info

@property main_company_projects_info type=table parent=main_vbox_right no_caption=1 store=no
@caption Firma projektide info

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
	var $company_account = null;

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
			case 'main_company_projects_info':
				if(is_oid($arr['request']['company']))
				{
					$this->do_company_accounts_table($arr);
				}
				break;
			case 'main_company_info':
				if(is_oid($arr['request']['company']))
				{
					$arr['company_id'] = $arr['request']['company'];
					$this->do_company_info_table($arr);
				}
				break;
			case 'main_table':
				if(is_oid($arr['request']['company']) || is_oid($arr['request']['project_id']))
				{
					$arr['parent'] = $arr['request']['project_id']?$arr['request']['project_id']:$arr['request']['company'];
					$this->do_transactions_table($arr);	
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
		if($arr['id']=='pank_owner_group' || 
			$arr['id']=='pank_owner_group_main_sub' 
			&& is_oid($arr['obj_inst']->id())
		)
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

	/*
		Every compay has ONE account. This function returns that
		account. If it doesn't find any, then i'll create one manually.
		
	*/
	function get_company_account($comp)
	{
		//listing all the accounts from the company
		$ol = new object_list(array(
						'class_id' => CL_ACCOUNT,
						'parent' => $comp->id(),
				));

		//checking if there is one
		if(sizeof($ol->ids()))
		{
			$tmp = $ol->arr();
			return current($tmp);
		}
		//none found, will create one
		else
		{
		
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

	/*
		every company can have just one account, 
		its more like it should have one account.

		This function returns the account of the
		give company, if none exist, will make 
		a new one, and return that one.
	*/
	function get_account_for_obj($parent)
	{
		//paistab, et tuli id hoopis sisse
		if(!is_object($parent))
		{
			$parent = new object($parent);
		}
		
		//company accounts aren't in the company
		//but in the pank associated with the company
		/*if($parent->class_id() == CL_CRM_COMPANY)
		{
			$conns = $parent->connections_to(array(
								'type' => RELTYPE_OWNER
			));
			if(sizeof($conns))
			{
				$bank = current($conns);
				$bank = $bank->from();
				return $this->get_account_for_obj(&$bank);
			}
			else
			{
				//ei suutnud saada
				echo "Ei suutnud saada kontot järgmisele objektile: ";
				arr($parent->properties());
				die();
			}
		}
		else*/
		{
			$ol = new object_list(array(
							'parent' => $parent->id(),
							'class_id' => CL_ACCOUNT
			));
			if(sizeof($ol->ids()))
			{
				return new object(current($ol->ids()));
			}
			//make account
			else
			{
				$obj = new object();
				$obj->set_class_id(CL_ACCOUNT);
				$obj->set_parent($parent->id());
				$obj->save();
				return $obj;
			}
		}
	}

	function do_transactions_table($arr)
	{
		$table = &$arr['prop']['vcl_inst'];
		
		$table->define_field(array(
			'name' => 'from',
			'caption' => 'Kandja',			
		));

		$table->define_field(array(
			'name' => 'to',
			'caption' => 'Saaja'
		));

		$table->define_field(array(
			'name' => 'sum',
			'caption' => 'Summa',
		));

		$table->define_field(array(
			'name' => 'date',
			'caption' => 'Aeg'
		));


		$parent = $this->get_account_for_obj($arr['parent']);

		//let's list the accounts
		$ol = new object_list(array(
					'parent' => $parent->id(),
					'class_id' => CL_TRANSACTION,
		));
	
		$trans = get_instance('pank/transaction');

		$accounts = $ol->arr();
		
		foreach($accounts as $obj)
		{
			$trans_info = $trans->get_info_on_transaction(&$obj);
			if(!$trans_info)
			{
				continue;
			}

			$table->define_data(array(
					'from' => $trans_info['from_obj']->name(),
					'to' => $trans_info['to_obj']->name(),
					'date' => $this->time2date($obj->prop('date')),
					'sum' => $obj->prop('sum'),
			));
		}
	}
	
	function do_company_info_table($arr)
	{
		$table = &$arr['prop']['vcl_inst'];

		$table->set_layout('cool');

		$table->define_field(array(
			'name' => 'account_name',
			'caption' => 'Konto nimi',
		));

		$table->define_field(array(
			'name' => 'saldo',
			'caption' => 'Saldo',
			'type' => 'int',
		));

		$company = new object($arr['company_id']);

		$account = $this->get_account_for_obj($company);

		$table->define_data(array(
					'saldo' => $account->prop('account_balance'),
					'account_name' => $account->name(),
		));
	}

	function do_company_accounts_table($arr)
	{
		$table = &$arr['prop']['vcl_inst'];

		$table->define_field(array(
			'name' => 'project_name',
			'caption' => 'Projekt',
		));


		$table->define_field(array(
			'name' => 'project_account',
			'caption' => 'Konto',
		));

		$table->define_field(array(
					'name' => 'saldo',
					'caption' => 'Saldo',
					'type' => int,
		));

		$table->define_chooser(array(
					'name' => 'check',
					'field' => 'id',
					'caption' => 'X',
		));
	
		$ol = new object_list(array(
					'parent' => $arr['obj_inst']->id(),
					'class_id' => CL_ACCOUNT
		));


		$company = new object($arr['request']['company']);
		$crm_company = get_instance(CL_CRM_COMPANY);
		//getting all the projects for this company
		$projects = $crm_company->get_all_projects_for_company(array('id'=>$arr['request']['company']));
		
		$from_account = $this->get_account_for_obj($company);
		
		foreach($projects as $project)
		{
			$project_account = $this->get_account_for_obj($project); 

			$project_name = $this->mk_my_orb('change',
									array(
										'group' => $arr['request']['group'],
										'id' => $arr['request']['id'],
									),
									CL_PANK);

			$table->define_data(array(
				'project_name' => html::href(array(
											'url'=>aw_url_change_var(array(
												'project_id' => $project->id(),
												'account_id' => '',
												'return_url' => '',
											)),
											'caption'=>$project->name()
										)),
				'saldo' => $project_account->prop('account_balance'),
				'project_account' => $project_account->name()
								."<input type='hidden' name='from_account[".$project_account->id()
								."]' value='".$from_account->id()."'>",
				'id' => $project_account->id(),
			));
			
		}
		
		//now we have to get all the banking information for this company
		$conns = $company->connections_to(array(
						'type' => RELTYPE_OWNER
		));
	
	}

	function do_main_toolbar($arr)
	{
		$toolbar = &$arr['prop']['toolbar'];

		$toolbar->add_button(array(
			'name' => 'Tee ülekanne',
			'img' => 'objects/document.gif',
			'tooltip' => 'Soorita ülekanne',
			'action' => 'submit_make_transaction'
		));
	}
	
	/**
		@attrib name=submit_make_transaction
		@param id required type=int acl=view
	**/
	function submit_make_transaction($arr)
	{
		//arr['check'] sees on kontode id
		if(is_array($arr['check']) && sizeof($arr['check'])==1)
		{
			$account_id = current($arr['check']);
			$url =  $this->mk_my_orb('new',array(
								'parent' => $account_id,
								'from_account' => $arr['from_account'][$account_id],
							),
							CL_TRANSACTION
			);
			return $url;
		}
		else
		{
			echo "Mida ma pean tegema, kui kasutaja ei vali ühtegi checkboxi välja või valib rohkem kui ühe?";
		}
	}

}
?>
