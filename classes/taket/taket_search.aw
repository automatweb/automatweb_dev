<?php
// $Header: /home/cvs/automatweb_dev/classes/taket/Attic/taket_search.aw,v 1.5 2005/04/21 08:54:58 kristo Exp $
// taket_search.aw - Taketi Otsing 
/*

@classinfo syslog_type= relationmgr=yes
//groupinfo blocked caption=Piirangud

@default table=objects
@default group=general

//property taket_block_conf type=relpicker group=blocked reltype=RELTYPE_TAKET_BLOCK_CONF multiple=1
//caption Piirangud

*/

class taket_search extends class_base
{
	function taket_search()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "taket/taket_search",
			"clid" => CL_TAKET_SEARCH
		));
		lc_site_load('taket_search',&$this);
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them
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

	/**  
		
		@attrib name=parse_submit_info params=name default="0"
		
		@param tootekood optional
		@param asendustooted optional
		@param otsitunnus optional
		@param laos optional
		@param kogus optional
		@param reforb optional
		@param start optional
		@param orderBy optional
		@param direction optional
		
		@returns
		
		
		@comment

	**/
	function parse_submit_info($arr)
	{
		//determine the xml-rpc call
		include('IXR_Library.inc.php');
		$client = new IXR_Client('80.235.30.13','/xmlrpc/index.php',8888);
		if(isset($arr['kogus']) && !strstr($arr['kogus'],','))
		{
			$arr['kogus']=((int)$arr['kogus']<=0)?1:(int)$arr['kogus'];
		}
		else if(isset($arr['kogus']))
		{
			$tmpArr=split(',',$arr['kogus']);
			$tmpArr2=array();
			foreach($tmpArr as $value){
				$tmpArr2[]=(int)$value<=0?1:(int)$value;
			}
			$arr['kogus']=implode(',',$tmpArr2);
			unset($tmpArr2);
			unset($tmpArr);
		}
		if(!$client->query('server.search',$arr['tootekood'], $arr['otsitunnus'],
														$arr['kogus'],$arr['asendustooted'],
														$arr['laos'],(int)($arr['start']),
														$arr['orderBy'],$arr['direction'],
														$arr['osaline']))
		{
			//echo('Something went wrong - '.$client->getErrorCode().' : '.
			//		$client->getErrorMessage());
			//echo $client->getResponse();
		};
		//print_r($client->getResponse());
		$data=$client->getResponse();
		//print_r($data);
		//print_r($client);
		//echo "<!-- ";
		//print_r($data['query']);
		//echo " -->";
		//die();
		$this->read_template('search.tpl');
		//ei ole eriti hea feature kui on mitmeleveliga subid
		$this->sub_merge=0;
		
		//initialize the search form
		//with the values just posted
		$arr['asendustooted']=(int)$arr['asendustooted'];
		$arr['laos']=(int)$arr['laos'];
		$this->vars($arr);

		
		//assign the results
		$count=0;
		if(!is_array($data))
		{
			$data=array();
		}

		//if the search was done as follows:
		//product_code & it's quantity, product_code & it's quantity etc
		//i have to display for a found product_code _it's_ quantity
		//so i have to some pattern matching here because i can't
		//extract the info from the query/results
		//build patterns:
		$match=false;
		if(strstr($arr['tootekood'], ','))
		{
			$match=true;
			$products = split(',',$arr['tootekood']);
			$quantities = split(',',$arr['kogus']);
			foreach($products as $key=>$value)
			{
				$products[$key]=trim($value);
				$quantities[trim($value)] = ((int)$quantities[$key])>0?(int)$quantities[$key]:1;
			}			
		}
		
		$numOfRows=0;
		$noSkipped=0;
		$content='';
		$hidden=array();
		$i=0;
		$lastQuantity=1;
		foreach($data as $key=>$value)
		{			
			if(isset($value['numOfRows']))
			{
				$numOfRows=$value['numOfRows']+$numOfRows;		
				continue;
			}
			if(isset($value['start']))
			{
				$noSkipped=$value['start'];
				continue;
			}
			if(isset($value['orderBy']))
			{
				$hidden['orderBy']=$value['orderBy'];
				continue;
			}
			if(isset($value['direction']))
			{
				if($value['direction']=='desc')
				{
					$hidden['direction']='asc';
				}
				else
				{
					$hidden['direction']='desc';
				}
				continue;
			}
			if(isset($value['query']))
			{
				//echo $value['query'];
				continue;
			}
		
			if($value['hide']){
				//echo $value['h!ide'].'  '.$value['inStock'].''.$value['hidden'].'<br>';
				if(!$data[$key+1]['hidden']){
					$numOfRows--;
					continue;
				}
			}
			
			if($value['replacement'])
			{
				$value['replacement'] = 'Küsitud';	
				$value['staatuscss'] = 'listItem';
			}
			else
			{
				$value['replacement'] = 'Asendus';//.$value['peatoode'];
				$value['staatuscss'] = 'listItemRep';
			}
			//have to determine the discount for this user
			$value['discount'] = (int)$value['kat_ale'.$_SESSION['TAKET']['ale']];
			if(!((int)$value['discount']))
			{
				$value['discount']=0;
			}
			$value['product_code2']=urlencode($value['product_code']);
			if((int)$_SESSION['TAKET']['tukkuGrupp']==100)
			{
				$value['price'] = number_format($value['tukkuprice'],2,'.','');				
			}
			else
			{
				$value['price'] = number_format($value['price'],2,'.','');
			}

			//if multiple quantities
			if($match)
			{
				$matched=false;
				//if matches its the mainproduct
				foreach($products as $key2=>$value2)
				{
					//if "partial" search
					if($arr['osaline'])
					{
						if(
							strstr(strtoupper($value['product_code']),strtoupper($value2))
							||
							strstr(strtoupper($value['search_code']),strtoupper($value2))
						)
						{
							$value['quantity']=(int)$quantities[$value2];
							$lastQuantity=(int)$value['quantity'];
							$matched=true;
						}					
					}
					else
					{
						if(
							strpos(strtoupper($value['product_code']),strtoupper($value2))===0
							||
							strpos(strtoupper($value['search_code']),strtoupper($value2))===0
						)
						{
							$value['quantity']=(int)$quantities[$value2];
							$lastQuantity=(int)$value['quantity'];
							$matched=true;
						}
					}
				}
				//its a replacement for the last matched one
				if(!$matched)
				{
					$value['quantity']=$lastQuantity;
				}
			}
			//single product&quantity search
			else
			{
				$value['quantity'] = ((int)$arr['kogus'])?(int)$arr['kogus']:'1';
			}
			//echo $value['quantity'].'<br>';
			//more or the same amount is in stock that was searched
			if($value['quantity']<=$value['inStock'])
			{
					$value['inStock2'] = 'Olemas';
					$this->vars(array('inStock3'=>$this->parse('instockyes')));
			}
			//this product is out of stock
			else
			{
					if($value['inStock']>0)
					{
						$value['inStock2'] = 'Osaliselt';
						$this->vars(array('inStock3'=>$this->parse('instockpartially')));
					}
					else
					{
						$value['inStock2'] = 'Ei ole';
						$this->vars(array('inStock3'=>$this->parse('instockno')));
					}
			}


			$value['finalPrice'] = number_format($value['price']*((100-$value['discount'])/100),2,'.','');			
			$value['search_code'] = str_replace(' ','&nbsp;', $value['search_code']);
			$value['product_code'] = str_replace(' ','&nbsp;', $value['product_code']);
			//$value['replacement'] = ($value['replacement'])?'Peatoode':'Asendus';
			$value['i']=$i++;
			$this->vars($value);

			//
			if($value['quantity']<=$value['inStock'])
			{
					$this->vars(array(
									'quantityParsed'=>$this->parse('canSetQuantity'),
									'karuParsed'=>$this->parse('karu')
									));				
			}
			else
			{
					$this->vars(array(
									'quantityParsed'=>$this->parse('cannotSetQuantity'),
									'karuParsed'=>$this->parse('karupole')									
									));			
			}

			//kas on asendustoode või mitte
			if($value['replacement']=='Küsitud')
			{
				$this->vars(array(
									'esimeneVeerg'=>$this->parse('mainproduct')
							));
			}
			else
			{
				$this->vars(array(
									'esimeneVeerg'=>$this->parse('asendustoodeblock')
							));
			}
						

			$content.=$this->parse('product');
			
			$count++;
		}
		$this->vars(array('productParsed'=>$content));
		$data='';
			
		//make column label bold if it was used to sort
		$tmpArr = array('cssstaatus'=>'listTitle',
							'csstootekood'=>'listTitle',
							'cssnimetus'=>'listTitle',
							'cssotsitunnus'=>'listTitle',
							'csshind'=>'listTitle',
							'cssallahindlus'=>'listTitle',
							'csslopphind'=>'listTitle',
							'csslaos'=>'listTitle');
		$tmpArr['css'.$hidden['orderBy']]='listTitleSort';
		$this->vars($tmpArr);

		//assign hidden values
		$this->vars($hidden);

		//generating page numbers
		$count2=$count;
		$count=ceil($numOfRows/40);
		$content='';
		for($i=0;$i<$count;$i++)
		{
			$prev=$noSkipped?($noSkipped-40):0;
			$next=($noSkipped==40*4)?(40*4):($noSkipped+40);
			$pageNumber=($i*40)==$noSkipped?'<b>'.($i+1).'</b>':($i+1);
			if($count==0)
				$next=0;
			$this->vars(array(
							'next' => $next,
							'prev' => $prev,
							'pageNumber'=>$pageNumber,
							'start'=>$i*40));
			$content.=$this->parse('pageNumbers');
		}
		$this->vars(array('pageNumbersParsed'=>$content));
		if($count>1)
		{
			$this->vars(array('numbersPart'=>$this->parse('numbersPart')));
		}
		
		//simple var assignments
		$this->vars(array(
				'otsisin' => $arr['tootekood'].' '.$arr['otsitunnus'],
				'tootekood' => $arr['tootekood'],
				'results' => $numOfRows
		));
		
		return $this->parse();
	}

	function on_get_subtemplate_content($arr)
	{
		$inst= &$arr['inst'];
	
		//hõmm main.tpl'i subi TAKET_SEARCH peax vist ikkagi
		//näitama antud klassi show.tpl'i	
		$this->read_template('show.tpl');
		//reforb		
		$this->vars(array(
					'reforb'=>$this->mk_reforb('parse_submit_info',
														array('no_reforb'=>true))
		));
		$inst->vars(array(
					'taket_search_content'=>$this->parse()
		));

		$inst->vars(array(
					'TAKET_SEARCH' => $inst->parse("TAKET_SEARCH")
		));	
	}
}
?>
