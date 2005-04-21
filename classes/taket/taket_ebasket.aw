<?php
// $Header: /home/cvs/automatweb_dev/classes/taket/Attic/taket_ebasket.aw,v 1.6 2005/04/21 08:54:58 kristo Exp $
// taket_ebasket.aw - Ostukorv
/*

HANDLE_MESSAGE(MSG_USER_LOGIN, msg_delete_users_ebasket)
@classinfo syslog_type= relationmgr=yes

@default table=objects
@default group=general

*/

class taket_ebasket extends class_base
{
	//defined in aw ini
	var $ebasket_parent_id; //location of the baskets
	var $ebasket_item_parent_id; //location of the basket items
	var $order_item_parent_id;

	function taket_ebasket()
	{
		$this->ebasket_parent_id = aw_ini_get('taket_ebasket.ebasket_parent_id');
		$this->ebasket_item_parent_id = aw_ini_get('taket_ebasket.ebasket_item_parent_id');
		$this->order_item_parent_id = aw_ini_get('taket_order.order_item_parent_id');
		// change this to the folder under the templates folder, where this classes templates will be,
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "taket/taket_ebasket",
			"clid" => CL_TAKET_EBASKET
		));
		lc_site_load('taket_ebasket',&$this);
	}

	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{				
		include('IXR_Library.inc.php');
		//let it be
		$ob = new object($arr["id"]);
		//needed template & settings
		$this->read_template("show.tpl");
		$this->sub_merge = 0;

		//current user
		$user_id = users::get_oid_for_uid(aw_global_get('uid'));

		//another thing written by the script
		$this->vars(array(
			"name" => $ob->prop("name"),
		));

		//ebasket
		$ebasket=$this->get_users_active_ebasket($user_id);

		$priceWithoutTax=0;
		$tax=0;
		$priceGrandTotal=0;
		
		//sort by logic
		$sortBy = 'product_code';
		$dirs= 'asc';
		$options = array('product_code','product_name','price',
									'discount','finalprice','quantity');
		$css = array(
					'product_codecss' => 'listTitle',
					'product_namecss' => 'listTitle',
					'pricecss' => 'listTitle',
					'discountcss' => 'listTitle',
					'finalpricecss' => 'listTitle',
					'quantitycss' => 'listTitle'
				);
		$dir = array(
					'product_codedir' => 'asc',
					'product_namedir' => 'asc',
					'pricedir' => 'asc',
					'discountdir' => 'asc',
					'finalpricedir' => 'asc',
					'quantitydir' => 'asc'
				);

		if(in_array($arr['sort'],$options))
		{
			$sortBy=$arr['sort'];
			$dirs=($arr['dir']=='asc')?'desc':'asc';
			$css[$sortBy.'css']='listTitlesort';
			$dir[$sortBy.'dir']=($arr['dir']=='asc')?'desc':'asc';
			if($arr['sort']=='order_id')
			{
				$sortBy='id';
				$dirs = ($arr['dir']=='asc')?'desc':'asc';	
			}
		}
		else
		{
			$css['product_codecss']='listTitlesort';
		}

		if($arr['sort']!='finalprice')
		{
			$sortBy='taket_ebasket_item.'.$sortBy.' '.$dirs;
		}
		else
		{
			$sortBy='(taket_ebasket_item.price*(1-(discount/100))) '.$dirs;
		}
		
		$this->vars($css);
		$this->vars($dir);
		$this->vars(array(
					'sort'=>$arr['sort'],
					'dir'=>$arr['dir']));
		//end of sort_by logic
		
		//load all the items
		$ol = new object_list(array(
						'parent'=>$this->ebasket_item_parent_id,
						'class_id' => CL_TAKET_EBASKET_ITEM,
						'ebasket_id' => $ebasket->id(),
						'lang_id' => array(),
						'sort_by' => $sortBy
					));
		$i=0;
		$content='';

		//have to gather all the product_codes so i won't
		//have to do product_code number of xml-rpc queries
		//opening http connections cost, 1 is cool, 10 sux
		$productCodes=array();
		for($o=$ol->begin();!$ol->end();$o=$ol->next())
		{
			if(!$o->prop('ebasket_id'))
				continue;
			$productCodes[]=$o->prop('product_code');
		}
		$client = new IXR_Client(aw_ini_get('taket.xmlrpchost'),
		                         aw_ini_get('taket.xmlrpcpath'),
		                         aw_ini_get('taket.xmlrpcport'));
		$client->query('server.getProductInfoArr',$productCodes);
		$data = $client->getResponse();
		$tmpFlag=1;
		for($o=$ol->begin();!$ol->end();$o=$ol->next())
		{
			//ebaskets item
			if(!$o->prop('ebasket_id'))
				continue;
			if($data[$o->prop('product_code')]['inStock']>=$o->prop('quantity'))
			{
				$msg = 'Jah';
				$this->vars(array('instock_parsed'=>$this->parse('instockyes')));
			}
			else
			{
				$msg = '<font color="#ADADAD;">'.Ei.'</font>';
				$this->vars(array('instock_parsed'=>$this->parse('instockno')));
				$tmpFlag=0;
			}
			$this->vars(array(
					'product_code' => $o->prop('product_code'),
					'product_name' => $o->prop('product_name'),
					'price' => number_format($o->prop('price'),2,'.',''),
					'discount' => $o->prop('discount'),
					'finalprice' => number_format(((1-$o->prop('discount')/100)*$o->prop('price')),2,'.',''),
					'quantity' => $o->prop('quantity'),
					//'inStock' => $msg,
					'i'	=> $i++,
					'tmpFlag' => $tmpFlag
			));

			$priceWithoutTax+=$o->prop('quantity')*
											($o->prop('price')*(1-$o->prop('discount')/100));
			$content.=$this->parse('toode');
		}

		$this->vars(array('toodeParsed'=>$content));
		$content='';
		//assign the variables calculated in the iteration
		$this->vars(array(
				'priceWithoutTax' => number_format($priceWithoutTax/1.18,2,'.',''),
				'tax'	=> number_format(round($priceWithoutTax/1.18*0.18,2),2,'.',''),
				'priceGrandTotal'	=> number_format($priceWithoutTax,2,'.','')
				));

		$this->vars(array(
			'reforb' => $this->mk_reforb('save_ebasket',
													array('no_reforb'=>true))
			));
		//save button was just pressed
		//it's okay to show the order form
		//if($arr['saved'])
		//{
			//have to query the different transportation types from AFP
			//simple :)
			$client = new IXR_Client(aw_ini_get('taket.xmlrpchost'),
											aw_ini_get('taket.xmlrpcpath'),
											aw_ini_get('taket.xmlrpcport'));
			$client->query('server.getTransportTypes',array());
			$data = $client->getResponse();
			$tmp='';
			if(!is_array($data))
				$data = array();
			foreach($data as $value)
			{
				if($value['transport_id']==$_SESSION['TAKET']['transport_type'])
				{
					$value['tselected']='selected';
				}
				else
				{
					$value['tselected']='';
				}
				$this->vars($value);
				$tmp.=$this->parse('transport');
			}
			$this->vars(array(
				'reforb2' => $this->mk_reforb('send_order',
													array('no_reforb'=>true)),
				'eesperenimi' => $_SESSION['TAKET']['eesperenimi'],
				'kontakttelefon' => $_SESSION['TAKET']['gsm'],
				'info' => $_SESSION['TAKET']['info'],
				'transportParsed' => $tmp
				));
			$tmp='';
			if($arr['inputErr'] && $arr['inputErr']!=2)
			{
				$this->vars(array('inputErrParsed'=>$this->parse('inputErr')));
			}
			else if($arr['inputErr']==2)
			{
				$this->vars(array('inputErrParsed'=>$this->parse('inputErr2')));			
			}
			$this->vars(array('vormistaParsed'=>$this->parse('vormista')));
		//}
		return $this->parse();
	}

	/**  
		
		@attrib name=send_order params=name default="0"
		
		@param transport optional
		@param kontakttelefon optional
		@param eesperenimi optional
		@param transport_name optional
		
		@returns
		
		
		@comment

	**/
	function send_order($arr)
	{
		//if all the fields weren't filled		
		if(!($arr['kontakttelefon'] && $arr['eesperenimi'] && $arr['transport']))
		{
			return $this->mk_my_orb('show',array('inputErr'=>1,'saved'=>1),'taket_ebasket');
		}
		//else continue
		

		//send the order to the AFP
		include('IXR_Library.inc.php');
		$client = new IXR_Client(aw_ini_get('taket.xmlrpchost'),
										aw_ini_get('taket.xmlrpcpath'),
										aw_ini_get('taket.xmlrpcport'));
		//let's gather the info to be sent
		$userinfo = array(); //i'll know about it more tomorrow
		//basket info
		$user_id = users::get_oid_for_uid(aw_global_get('uid'));
		$ebasket = $this->get_users_active_ebasket($user_id);
		$ol = new object_list(array(
					'parent' => $this->ebasket_item_parent_id,
					'class_id' => CL_TAKET_EBASKET_ITEM,
					'lang_id' => array(),
					'ebasket_id' => $ebasket->id()
				));
		$rows = array();
		$orderPrice=0;
		$orderPriceD=0;
		for($o=$ol->begin();!$ol->end();$o=$ol->next())
		{
			if($o->prop('ebasket_id')==$ebasket->id())
			{
				$row = array('product_code'=>$o->prop('product_code'),
								'quantity' => $o->prop('quantity'),
								'price' => $o->prop('price'),
								'product_name' => $o->prop('product_name'),
								'discount'	=> $o->prop('discount'),
								'supplier_id' => $o->prop('supplier_id'));
				$rows[] = $row;
				$orderPriceD+=($o->prop('price')*(1-$o->prop('discount')/100))*$o->prop('quantity');
				$orderPrice+=$o->prop('price')*$o->prop('quantity');
			}
		}

		if(!sizeof($rows)){
			return $this->mk_my_orb('show',array('inputErr'=>2,'saved'=>1),'taket_ebasket');
		}

		//store the order locally
		//save the order locally for later viewing
		$obj = new object();
		$obj->set_class_id(CL_TAKET_ORDER);
		$obj->set_parent(aw_ini_get('taket_order.order_parent_id'));
		$obj->set_prop('price', $orderPriceD);
		$obj->set_prop('comments','');
		$obj->set_prop('transport',$arr['transport_name']);
		$obj->set_prop('timestmp',time());
		$obj->set_prop('status', 'Edastatud');
		$obj->set_prop('contact', $arr['eesperenimi']);
		$obj->set_prop('user_id', users::get_oid_for_uid(aw_global_get('uid')));
		$obj->save();
		$orderId=$obj->id();
	
		//info that will go to the AFP order system
		$toBeSent = array();
		$toBeSent['data']=$rows;
		$toBeSent['user']=aw_global_get('uid');
		$toBeSent['tukkuGrupp']=$_SESSION['TAKET']['tukkuGrupp'];
		$toBeSent['price']=$orderPrice;
		$toBeSent['order_id']=$orderId;
		$toBeSent['transport']=$arr['transport'];
		$toBeSent['transport_name']=$arr['transport_name'];
		$toBeSent['user_info']=$arr['info'];
		$client->query('server.sendOrder', $toBeSent);
		$data=$client->getResponse();

		//let's remember the setting for this SESSION
		$_SESSION['TAKET']['info'] = $arr['info'];
		$_SESSION['TAKET']['eesperenimi'] = $arr['eesperenimi'];
		$_SESSION['TAKET']['gsm'] = $arr['kontakttelefon'];
		$_SESSION['TAKET']['transport_type'] = $arr['transport'];


		//save every item of the order for later viewing, FUN
		for($o=$ol->begin();!$ol->end();$o=$ol->next())
		{
			if($o->prop('ebasket_id')==$ebasket->id()){
				$obj = new object();
				$obj->set_class_id(CL_TAKET_ORDER_ITEM);
				$obj->set_parent(aw_ini_get('taket_order.order_item_parent_id'));
				$obj->set_prop('order_id',$orderId);
				$obj->set_prop('product_code',$o->prop('product_code'));
				$obj->set_prop('quantity',$o->prop('quantity'));
				$obj->set_prop('price',$o->prop('price'));
				$obj->set_prop('discount',$o->prop('discount'));
				$obj->set_prop('product_name',$o->prop('product_name'));
				$obj->save();	
			}
		}
	
		//send email
		classload('taket/taket_tellimuste_list');
		$emailContent=taket_tellimuste_list::show_order(array('order_id'=>$orderId));
		$this->read_template('shell.tpl');
		$this->vars(array(
						'content'=> $emailContent
					));
		$arr['user_id']=aw_global_get('uid');
		$this->vars($arr);
		$emailContent = $this->parse();
		$headers = "MIME-Version: 1.0\r\n";
		$headers.= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers.= "From: Taketi Tellimiskeskus <tellimine@taket.ee>\r\n";
		mail(aw_ini_get('taket.email_address'),'Tellimus Taketi Tellimiskeskusest',$emailContent, $headers);
		$emailContent='';
		//delete the ebasket
		$this->delete_users_ebasket($user_id);
		
		return $this->mk_my_orb('show',array(),'taket_tellimuste_list');
	}

	function add_item($arr, $return=true)
	{	
		//getting product info
		include_once('IXR_Library.inc.php');
		//let's get current users's id
		$user_id=users::get_oid_for_uid(aw_global_get('uid'));
		$client = new IXR_Client(aw_ini_get('taket.xmlrpchost'),
										aw_ini_get('taket.xmlrpcpath'),
										aw_ini_get('taket.xmlrpcport'));
		//xml-rpc call was made earlier?
		if(!$return)
		{
			$data = $arr['data'];
		}
		//have to make the call
		else
		{
			$client->query('server.getProductInfo', $arr['product_code']);	
			$data = $client->getResponse();
		}		
		//let's get all the items
		$ebasket = $this->get_users_active_ebasket($user_id);
		$ol = new object_list(array(
				'parent' => $this->ebasket_item_parent_id,
				'class_id' => CL_TAKET_EBASKET_ITEM,
				'lang_id' => array(),
				'ebasket_id' => $ebasket->id()
				));
		$tmpFound=false;
		//users ebasket
		$arr['product_code']=urldecode($arr['product_code']);
		//just in case
		$arr['quantity']=(int)$arr['quantity'];
		if(!$arr['quantity'])
			$arr['quantity']=1;
		for($o=$ol->begin();!$ol->end();$o=$ol->next())
		{
			//if the object is the needed item and the product code matches
			//ebasket_id==$ebasket->()
			if($o->prop('ebasket_id')==$ebasket->id() &&
				$o->prop('product_code')==$arr['product_code'])
			{
				//let's increment the quantity by one, save
				//and break off
				//have to check about the quantity though :(
				if($data['inStock']>=($o->prop('quantity')+$arr['quantity'])){
					$o->set_prop('quantity',($o->prop('quantity')+$arr['quantity']));
					$o->save();
				}
				$tmpFound=true;
				break;
			}
		}

		//such a product doesn't exist yet, lets add it
		if(!$tmpFound)
		{
			$o = new object();
			$o->set_class_id(CL_TAKET_EBASKET_ITEM);
			$o->set_parent($this->ebasket_item_parent_id);
			//now have to query data from the AFP and save as properties
			//query for the product
			if((int)$_SESSION['TAKET']['tukkuGrupp']==100)
			{
				$o->set_prop('price',$data['tukkuprice']);
			}
			else
			{
				$o->set_prop('price',$data['price']);
			}
			$o->set_prop('product_name', $data['product_name']);
			$o->set_prop('discount', (int)$data['kat_ale'.$_SESSION['TAKET']['ale']]);
			$o->set_prop('product_code',$arr['product_code']);
			$o->set_prop('inStock', $data['inStock']);
			$o->set_prop('supplier_id',$data['supplier_id']);
			$o->set_prop('ebasket_id',$ebasket->id());
			if($data['inStock']>=$arr['quantity'])
			{
				$o->set_prop('quantity',(int)$arr['quantity']);
			}
			else
			{
				$o->set_prop('quantity',1);
			}
			$o->save();
		}
		if($return)
			return $this->mk_my_orb("show", array(), "taket_ebasket");
	}

	function add_items($arr){
		//adds many items to the basket at a time, uses the add_item function
		//not to just copy the logic
		//getting product info
		include_once('IXR_Library.inc.php');
		//let's get current users's id
		$user_id=users::get_oid_for_uid(aw_global_get('uid'));
		$client = new IXR_Client(aw_ini_get('taket.xmlrpchost'),
										aw_ini_get('taket.xmlrpcpath'),
										aw_ini_get('taket.xmlrpcport'));
		//make array of selected products
		$product_codes = array();
		if(!is_array($arr['valitud']))
			$arr['valitud']=array();
		foreach($arr['valitud'] as $key=>$value)
		{
			$product_codes[]=$arr['productId'][$key];
		}
		//prefetching the data with one xml-rpc call
		//without thiss add_item would do everytime a separate call, it COSTS
		$client->query('server.getProductInfoArr', $product_codes);
		$data = $client->getResponse();
		//add the item
		//print_r($data);
		//die();
		foreach($arr['valitud'] as $key=>$value)
		{			
			$this->add_item(array(
									'quantity'=>$arr['quantity'][$key],
									'product_code'=>$arr['productId'][$key],
									'data'=>$data[$arr['productId'][$key]]
								) ,false);
		}
		return $this->mk_my_orb("show", array(), "taket_ebasket");
	}

	//saves the changes after the user has pushed the
	//check-out button
	/**  
		
		@attrib name=save_ebasket params=name default="0"
		
		@param productId optional
		@param quantity optional
		@param seesperenimi optional
		@param skontakttelefon optional
		@param stransport optional
		@param sort optional
		@param dir optional
		
		@returns
		
		
		@comment

	**/
	function save_ebasket($arr){
		//let's get id of the current user
		$user_id=users::get_oid_for_uid(aw_global_get('uid'));
		//let's get all the ebasket_items
		$ol = new object_list(array(
					'parent' => $this->ebasket_item_parent_id,
					'lang_id' => array()
					));
		//let's get the ebasket
		$ebasket = $this->get_users_active_ebasket($user_id);
		$tmpFlag=true;
		//change the default session values
		$_SESSION['TAKET']['info'] = $arr['sinfo'];
		$_SESSION['TAKET']['eesperenimi'] = $arr['seesperenimi'];
		$_SESSION['TAKET']['gsm'] = $arr['skontakttelefon'];
		$_SESSION['TAKET']['transport_type'] = $arr['stransport'];

		//print_r($client);
		//print_r($data);
		//$client->query('server.getProductInfo',$o->prop('product_code'));
		for($o=$ol->begin();!$ol->end();$o=$ol->next())
		{
			//if the object belongs to the $ebasket
			if($o->prop('ebasket_id')==$ebasket->id())
			{
				//find the "new" quantity for this product_id
				foreach($arr['productId'] as $key => $value)
				{
					//found
					if($value==$o->prop('product_code'))
					{
						//let's change the quantity
						//if quantity less than 0 or ==0 then the line will be deleted
						if((int)$arr['quantity'][$key]<=0)
						{
							$o->delete();
						}
						//let's update the obj property
						else
						{
							//if AFP has more items then the change is allowed, only then
							//echo $data[$o->prop('product_code')]['inStock'].">=".$arr['quantity'][$key].'<br>';
							//if($data[$o->prop('product_code')]['inStock']>=$arr['quantity'][$key])
							//{
								$o->set_prop('quantity',(int)$arr['quantity'][$key]);
								$o->save();
							//}
						}
					}
				}
			}
		}
		if($tmpFlag)
		{
			$tmp=array('action'=>'show','saved'=>1);
		}
		else
		{
			$tmp=array('action'=>'show');
		}
		$tmp['sort'] = $arr['sort'];
		$tmp['dir'] = $arr['dir'];
		return $this->mk_my_orb("show",$tmp,"taket_ebasket");
	}

	//gonna need this in many places
	//fetches the current users ebasket, if it
	//doesn't exist, it creates one
	function get_users_active_ebasket($user_id, $create=true)
	{
		//get all the ebasket objects
		$ol = new object_list(array(
				'parent' => $this->ebasket_parent_id,
				'lang_id' => array()
				));
		$ebasket = null;
		$user_id = users::get_oid_for_uid(aw_global_get('uid'));
		for($o=$ol->begin();!$ol->end();$o=$ol->next())
		{
			//object found, let's return it
			if($o->prop('user_id')==$user_id)
			{
				return $o;
			}
		}
		if($ebasket==null && $create)
		{
			$ebasket = new object();
			$ebasket->set_class_id(CL_TAKET_EBASKET_INST);
			$ebasket->set_parent($this->ebasket_parent_id);
			$ebasket->set_prop('user_id',$user_id);
			$ebasket->save();
		}
		return $ebasket;
	}

	//läheb sisselogimisel käivitusele
	//terrifile pean saatma emaili
	function delete_users_ebasket($user_id)
	{
		$ebasket = $this->get_users_active_ebasket($user_id, false);
		if($ebasket==null)
		{
			return;
		}
		$ol = new object_list(array(
			'parent' => $this->ebasket_item_parent_id,
			'lang_id' => array()
			));
		for($o=$ol->begin();!$ol->end();$o=$ol->next())
		{
			if($o->prop('ebasket_id')==$ebasket->id())
			{
				$o->delete();	
			}
		}
		$ebasket->delete();
	}

	function msg_delete_users_ebasket($arr)
	{
		// this should not be called if the site is not taket. I'm not sure whether
		// this check is the correct way so feel free to fix it.
		if (empty($this->ebasket_parent_id))
		{
			return false;
		};
		taket_ebasket::delete_users_ebasket(users::get_oid_for_uid($arr['uid']));
	}
}
?>
