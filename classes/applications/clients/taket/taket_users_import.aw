<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/clients/taket/taket_users_import.aw,v 1.2 2008/10/01 14:47:07 markop Exp $
// taket_users_import.aw - Taketi kasutajate import 
/*
HANDLE_MESSAGE(MSG_USER_LOGIN, update_user_info)
@classinfo syslog_type=ST_TAKET_USERS_IMPORT relationmgr=yes

@default table=objects
@default group=general

@groupinfo caption=Impordi
@property store=no method=Import
*/

class taket_users_import extends class_base
{
	function taket_users_import()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "taket/taket_users_import",
			"clid" => CL_TAKET_USERS_IMPORT
		));
	}


	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->import_users(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	/**
		@attrib name=import_users api=1
	**/
	function import_users($arr)
	{
		if(!$this->can('add',aw_ini_get('taket.users_parent')))
		{
			$this->acl_error();
			die();
		}
		include('IXR_Library.inc.php');
		$this->read_template('import.tpl');	
		$this->sub_merge=1;
		$inst = get_instance("users");

		//change passwords
		if($arr['changed'])
		{
			foreach($arr['password'] as $key=>$value)
			{
				if(strlen($value)>=3)
				{
					//let's update the users password
					$inst->save(array('uid'=>$arr['username'][$key], 'password'=>$value));
				}
			}
		}
		
		$hosts = aw_ini_get('taket.xmlrpchost');
		$path = aw_ini_get("taket.xmlrpcpath");
		$port = aw_ini_get("taket.xmlrpcport");
		$client = new IXR_Client($hosts[0], $path[0], $port[0]);
		$client->query('server.getUsers',array());
		$data=$client->getResponse();

		foreach($data as $value)
		{
			$value['kasutajanimi']=$value['number'];
			$value['password']=$value['number'];
			
			//delete weirdass old juusers
			if(strlen($value['kasutajanimi'])<3 || strlen($value['password'])<3)
			{
				$inst->do_delete_user($value['kasutajanimi']);
			}
			
			if(strlen($value['kasutajanimi'])==1)
			{
				$value['kasutajanimi']='00'.$value['kasutajanimi'];
			}
			else if(strlen($value['kasutajanimi'])==2)
			{
				$value['kasutajanimi']='0'.$value['kasutajanimi'];
			}

			if(strlen($value['password'])==1)
			{
				$value['password']='00'.$value['password'];
			}
			else if(strlen($value['password'])==2)
			{
				$value['password']='0'.$value['password'];
			}
			
				
			$row = $this->db_fetch_row('SELECT uid, password FROM users WHERE uid= \''
													.$value['kasutajanimi'].'\'');


			//	$inst->add_users_to_group_rec(9, array($value['kasutajanimi']));
			if(!is_array($row))
			{
				$inst->add(array(
						'uid' => $value['kasutajanimi'],
						'password' => $value['password'],
						'email' => $value['email'],
				));
				$inst->add_users_to_group_rec(9, array($value['kasutajanimi']));
				$value['password']=md5($value['password']);
			}
			else
			{
				//just in case
				//if(!$inst->is_member($value['kasutajanimi'],9))
				//{
				//	$inst->add_users_to_group_rec(9, array($value['kasutajanimi']));
				//}
				//$value['action']='Juba olemas';
				$value['password'] = $row['password'];
			}
			$this->vars($value);	
			$this->parse('klient');	
		}
		$this->vars(array(
				'reforb'=>$this->mk_reforb('import_users',array('changed'=>1,'no_reforb'=>true),
													'taket_users_import')
						));
		echo $this->parse();
		die();
	}
	
	//sounds nice, aint so nice
	function generateUserName($arr)
	{
		$rtrn=str_replace(' ','',$arr['firmanimi']);
		$rtrn=str_replace('	','',$rtrn);
		$rtrn=substr($rtrn,0,8);
		return $rtrn;
	}
	
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		$this->import_users($arr);
		return $this->parse();
	}

	function update_user_info($arr)
	{
		include('IXR_Library.inc.php');
		$user_id=users::get_oid_for_uid($arr['uid']);
		//tirib infi windooza servust kasutaja kohta
		//kui juhuslikult on mingi v2li muutunud
		$host = aw_ini_get('taket.xmlrpchost');
		$path = aw_ini_get('taket.xmlrpcpath');
		$port = aw_ini_get('taket.xmlrpcport');
		$client = new IXR_Client($host[0], $path[0], $port[0]);
		//username is the user_id in taket database@windoooza
		$client->query('server.getUsers',array('user_id'=>$arr['uid']));
		$data=$client->getResponse();	
		$_SESSION['TAKET']=$data[0];
		$_SESSION['TAKET']['eesperenimi'] = $_SESSION['TAKET']['eesnimi'].' '.$_SESSION['TAKET']['perenimi'];
		//initsialiseerima juba ka selle ebasketi listi
		$_SESSION['TAKET']['ebasket_list'] = array();
		//
		classload('taket/taket_ebasket');
		$ebasket = new taket_ebasket();
		$ebasket = $ebasket->get_users_active_ebasket(aw_global_get('uid'));
		if($ebasket!=null)
		{
	      $ol = new object_list(array(
                  'parent'=>$ebasket->ebasket_item_parent_id,
                  'class_id' => CL_TAKET_EBASKET_ITEM,
                  'ebasket_id' => $ebasket->id(),
                  'lang_id' => array(),
               ));

	      $grouped_basket_items = array();

			for($o=$ol->begin();!$ol->end();$o=$ol->next())
			{
				if(!$o->prop('ebasket_id'))
					continue;
				$ebasket_name = $o->prop('ebasket_name');
				if(!strlen($ebasket_name))
				{
					$ebasket_name = $this->current_ebasket_identificator;
				}
				if(!array_key_exists($ebasket_name,$grouped_basket_items))
				{
					$grouped_basket_items[$ebasket_name] = array();
				}
			}
			$_SESSION['TAKET']['ebasket_list'] = array_keys($grouped_basket_items);
		}
	}
}
?>