<?php
// $Header: /home/cvs/automatweb_dev/classes/taket/Attic/taket_users_import.aw,v 1.4 2005/04/21 08:54:58 kristo Exp $
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

	/** this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias 
		
		@attrib name=import_users params=name default="0"
		
		@param username optional
		@param password optional
		@param changed optional
		
		@returns
		
		
		@comment
		

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
		
		$client = new IXR_Client(aw_ini_get('taket.xmlrpchost'),
											aw_ini_get('taket.xmlrpcpath'),
											aw_ini_get('taket.xmlrpcport'));
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
				//damn i didn't add the users to the 
				//right group, this is a quick work-around
				//$inst->add_users_to_group_rec(9, array($value['kasutajanimi']));
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
		// this message should only be handled for taket sites, feel free to implement
		// it better -- duke
		if (aw_ini_get("taket.xmlrpchost") == "")
		{
			return false;
		};
		include('IXR_Library.inc.php');
		$user_id=users::get_oid_for_uid($arr['uid']);
		//tirib infi windooza servust kasutaja kohta
		//kui juhuslikult on mingi väli muutunud
		$client = new IXR_Client(aw_ini_get('taket.xmlrpchost'),
											aw_ini_get('taket.xmlrpcpath'),
											aw_ini_get('taket.xmlrpcport'));
		//username is the user_id in taket database@windoooza
		$client->query('server.getUsers',array('user_id'=>$arr['uid']));
		$data=$client->getResponse();	
		$_SESSION['TAKET']=$data[0];
		$_SESSION['TAKET']['eesperenimi'] = $_SESSION['TAKET']['eesnimi'].' '.
														$_SESSION['TAKET']['perenimi'];
	}
}
?>
