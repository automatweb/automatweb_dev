<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/clients/taket/taket_search.aw,v 1.1 2008/10/01 14:17:40 markop Exp $
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
		@param toote_nimetus optional
		@param laos optional
		@param kogus optional
		@param reforb optional
		@param start optional
		@param orderBy optional
		@param direction optional
		@param asukoht optional
		@param wvat optional
		
		@returns
		
		
		@comment

	**/
	function parse_submit_info($arr)
	{
		$site_log_line = '[taket_search::parse_submit_info] ';


		//determine the xml-rpc call
		include('IXR_Library.inc.php');
		$hosts = aw_ini_get('taket.xmlrpchost');
		$path = aw_ini_get("taket.xmlrpcpath");
		$port = aw_ini_get("taket.xmlrpcport");
		
		if (aw_ini_get('taket_search_log'))
		{
			$location_names = aw_ini_get('taket.location_name');
			if (isset($location_names[$arr['asukoht']]))
			{
				$location_name = $location_names[$arr['asukoht']];
			}
			else
			{
				$location_name = 'K&otilde;ik';
			}
			
			$taket_search_log = $arr['tootekood']."\t".$arr['toote_nimetus']."\t".$location_name."\t".aw_global_get('uid');
			$this->site_log($taket_search_log, '/taket_search_logs/'.date('Ymd').'.xls');
		}

		if(isset($arr["kogus"]) && !strstr($arr["kogus"], ","))
		{
			$arr["kogus"] = ((int)$arr['kogus'] <= 0) ? 1 : (int)$arr['kogus'];
		}
		elseif(isset($arr['kogus']))
		{
			$tmpArr = split(',',$arr['kogus']);
			$tmpArr2 = array();
			foreach($tmpArr as $value)
			{
				$tmpArr2[] = (int)$value <= 0 ? 1 : (int)$value;
			}
			$arr['kogus'] = implode(',', $tmpArr2);
			unset($tmpArr2);
			unset($tmpArr);
		}

		if ($arr['asukoht'] != -1)
		{
			$hosts = array($arr['asukoht'] => $hosts[$arr['asukoht']]);
		}

		$this->read_template('search.tpl');
		//ei ole eriti hea feature kui on mitmeleveliga subid aga siin k2ib kyll
		$this->sub_merge = 0;
		$data = array();
		$product_info_arr = array();
		$supplier_ids = array();
		
		$site_log_line .= '[XML-RPC]';
		foreach($hosts as $key => $host)
		{
			$site_log_line .= '[host: ('.$key.') '.$host.']';
			$client = new IXR_Client($host, $path[$key], $port[$key]);
			if (aw_global_get('uid') == 110)
			{
			//	$client->debug = true;
			}

			enter_function("taket_search::parse_submit_info::xml_rpc_query");

			$start_time = $this->microtime_float();
			if(!$client->query('server.search',
				$arr['tootekood'], 
		//		$arr['otsitunnus'],
				$arr['kogus'],
				$arr['asendustooted'],
				$arr['laos'],
				(int)($arr['start']),
				$arr['orderBy'],
				$arr['direction'],
				$arr['osaline'],
				$arr['toote_nimetus']
			))
			{
				if(aw_global_get("uid") == 110)
				{
					echo('Something went wrong - '.$client->getErrorCode().' : '.
					$client->getErrorMessage());
					arr($client->getResponse());
				}
				continue;
			};
			$end_time = $this->microtime_float();
	
			$site_log_line .= ' - product codes query/getResponse = '.(float)($end_time - $start_time).'/';

			exit_function("taket_search::parse_submit_info::xml_rpc_query");

			enter_function("taket_search::parse_submit_info::xml_rpc_getresponse");
			$start_time = $this->microtime_float();
			$tdata = $client->getResponse();
			$end_time = $this->microtime_float();
			$site_log_line .= (float)($end_time - $start_time).' | ';
			exit_function("taket_search::parse_submit_info::xml_rpc_getresponse");

			if(!is_array($tdata))
			{
				$tdata = array();
			}

			$product_codes = array();
			foreach($tdata as $tdat)
			{

				$no_add = false;
				foreach($data as $datkey => $val)
				{
					if($val["product_code"] == $tdat["product_code"])
					{
						$no_add = true;
						$data[$datkey]['inStock'.$key] = (int)$tdat['inStock'];
						break;
					}
				}
				if(!$no_add)
				{
					//$tdat["inStock2"] = $tdat["inStock"];
					$tdat['inStock'.$key] = (int)$tdat['inStock'];
					$data[] = $tdat;
					if (!empty($tdat['product_code']))
					{
						$product_codes[] = $tdat['product_code'];
					}
				}

			}

			// niih, product_codes on olemas, nyyd peaks ilmselt kysima yle xml_rpc
			// k6igi nende productide info
			// ja sealt ma saan siis selle pagana hankija koodi
			$start_time = $this->microtime_float();
			$client->query("server.getProductInfoArr", $product_codes);
			$end_time = $this->microtime_float();
			$site_log_line .= 'products info query/getRespnonse = '.(float)($end_time - $start_time).'/';
			$tmp_product_info_arr = $client->getResponse();

			$site_log_line .= (float)($end_time - $start_time).' | ';

			foreach ($tmp_product_info_arr as $product_code => $product_info)
			{
				$product_info_arr[$product_code] = $product_info;
				$supplier_ids[$product_info['supplier_id']] = $product_info['supplier_id'];
			}
			$site_log_line .= ' # ';

		}
		$site_log_line .= '##';
		//initialize the search form
		//with the values just posted
		$arr['asendustooted'] = (int)$arr['asendustooted'];
		$arr['laos'] = (int)$arr['laos'];
		$this->vars($arr);
		
		//assign the results
		$count = 0;


		//if the search was done as follows:
		//product_code & it's quantity, product_code & it's quantity etc
		//i have to display for a found product_code _it's_ quantity
		//so i have to some pattern matching here because i can't
		//extract the info from the query/results
		//build patterns:
		$match = false;
		if(strstr($arr['tootekood'], ','))
		{
			$match = true;
			$products = split(',', $arr['tootekood']);
			$quantities = split(',', $arr['kogus']);
			foreach($products as $key => $value)
			{
				$products[$key] = trim($value);
				$quantities[trim($value)] = ((int)$quantities[$key]) > 0 ? (int)$quantities[$key] : 1;
			}
		}
		$wx = 1;
		if(!$arr["wvat"])
		{
			$wvat = $_COOKIE["wvat"];
		}
		else
		{
			$wvat = $arr["wvat"];
		}
		if($wvat == 1)
		{
			$wx = 1.18;
		}
		
		$numOfRows = 0;
		$noSkipped = 0;
		$content = '';
		$hidden = array();
		$i = 0;
		$lastQuantity = 1;

		// lets remember the old $this->vars['trans_instock_no'] value, just to be able to replace it when
		// delivery date is not available
		$old_trans_instock_no = $this->vars['trans_instock_no'];
		$supplier_times = array();
		if (!empty($supplier_ids))
		{
			$supplier_times_data = $this->db_fetch_array("SELECT * FROM taket_times WHERE supplier_id IN (".join(",", map("'%s'", $supplier_ids)).")");
		}
		else
		{
			$supplier_times_data = array();
		}

		if ( !empty($supplier_times_data) )
		{
			// loop it through just to make the supplier id be the key of the array
			foreach ($supplier_times_data as $supplier_time)
			{
				$supplier_times[$supplier_time['supplier_id']] = $supplier_time;
			}
		}

		foreach($data as $key => $value)
		{
			if(isset($value['numOfRows']))
			{
				$numOfRows = $value['numOfRows'] + $numOfRows;
				continue;
			}
			if(isset($value['start']))
			{
				$noSkipped = $value['start'];
				continue;
			}
			if(isset($value['orderBy']))
			{
				$hidden['orderBy'] = $value['orderBy'];
				continue;
			}
			if(isset($value['direction']))
			{
				if($value['direction'] == 'desc')
				{
					$hidden['direction'] = 'asc';
				}
				else
				{
					$hidden['direction'] = 'desc';
				}
				continue;
			}
			if(isset($value['query']))
			{
				//echo $value['query'];
				continue;
			}
			if($value["tarjoushinta"] <= 0)
			{
				$value["tarjoushinta"] = "-";
			}
			else
			{
				$value["tarjoushinta"] = number_format(($value["tarjoushinta"]/$wx), 2, '.', '');
			}
		
			if($value['hide'])
			{
				//echo $value['h!ide'].'  '.$value['inStock'].''.$value['hidden'].'<br>';
				if(!$data[$key+1]['hidden'])
				{
					$numOfRows--;
					continue;
				}
			}
			
			if($value['replacement'])
			{
				$value['replacement'] = 'K&uuml;situd';
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
				$value['discount'] = 0;
			}
			$value['product_code2'] = urlencode($value['product_code']);
			if((int)$_SESSION['TAKET']['tukkuGrupp'] == 100)
			{
				$value['price'] = number_format(($value['tukkuprice']/$wx), 2, '.', '');
			}
			else
			{
				$value['price'] = number_format(($value['price']/$wx), 2, '.', '');
			}

			//if multiple quantities
			if($match)
			{
				$matched = false;
				//if matches its the mainproduct
				foreach($products as $key2 => $value2)
				{
					//if "partial" search
					if($arr['osaline'])
					{
						if(strstr(strtoupper($value['product_code']), strtoupper($value2)) || strstr(strtoupper($value['search_code']), strtoupper($value2)))
						{
							$value['quantity'] = (int)$quantities[$value2];
							$lastQuantity = (int)$value['quantity'];
							$matched = true;
						}
					}
					else
					{
						if(strpos(strtoupper($value['product_code']), strtoupper($value2)) === 0 || strpos(strtoupper($value['search_code']), strtoupper($value2)) === 0)
						{
							$value['quantity'] = (int)$quantities[$value2];
							$lastQuantity = (int)$value['quantity'];
							$matched = true;
						}
					}
				}
				//its a replacement for the last matched one
				if(!$matched)
				{
					$value['quantity'] = $lastQuantity;
				}
			}
			//single product&quantity search
			else
			{
				$value['quantity'] = ((int)$arr['kogus']) ? (int)$arr['kogus'] : '1';
			}
			//echo $value['quantity'].'<br>';
			//more or the same amount is in stock that was searched
			// stock #1
			if($value['quantity'] <= $value['inStock0'])
			{
				$in_stock3 = $this->parse('instockyes');
			}
			//this product is out of stock
			else
			{
				if($value['inStock0'] > 0)
				{
					$in_stock3 = $this->parse('instockpartially');
				}
				else
				{
					// lets check if we know when the goods are possibly available
					$date = $this->_get_date_by_supplier_id(array(
						"supplier_id" => $product_info_arr[$value['product_code']]['supplier_id'],
						'supplier_times' => $supplier_times
					));
					// if we know that, then lets show it to users too:
					if ($date !== false)
					{
						$this->vars(array(
							"trans_instock_no" => $date,
						));
					}
					else
					{
						$this->vars(array(
							"trans_instock_no" => $old_trans_instock_no,
						));
					}
					$in_stock3 = $this->parse('instockno');
				}
			}
			// stock #2
			// hmm, seems i have to implement the $date showing here too, but this later
			if($value['quantity'] <= $value['inStock1'])
			{
				$in_stock4 = $this->parse('instockyes');
			}
			//this product is out of stock
			else
			{
				if($value['inStock1'] > 0)
				{
					$in_stock4 = $this->parse('instockpartially');
				}
				else
				{
					// lets check if we know when the goods are possibly available
					$date = $this->_get_date_by_supplier_id(array(
						"supplier_id" => $product_info_arr[$value['product_code']]['supplier_id'],
						'supplier_times' => $supplier_times
					));
					// if we know that, then lets show it to users too:
					if ($date !== false)
					{
						$this->vars(array(
							"trans_instock_no" => $date,
						));
					}
					else
					{
						$this->vars(array(
							"trans_instock_no" => $old_trans_instock_no,
						));
					}

					$in_stock4 = $this->parse('instockno');
				}
			}

			// stock #3
			if($value['quantity'] <= $value['inStock2'])
			{
				$in_stock5 = $this->parse('instockyes');
			}
			//this product is out of stock
			else
			{
				if($value['inStock2'] > 0)
				{
					$in_stock5 = $this->parse('instockpartially');
				}
				else
				{
					// lets check if we know when the goods are possibly available
					$date = $this->_get_date_by_supplier_id(array(
						"supplier_id" => $product_info_arr[$value['product_code']]['supplier_id'],
						'supplier_times' => $supplier_times
					));
					// if we know that, then lets show it to users too:
					if ($date !== false)
					{
						$this->vars(array(
							"trans_instock_no" => $date,
						));
					}
					else
					{
						$this->vars(array(
							"trans_instock_no" => $old_trans_instock_no,
						));
					}

					$in_stock5 = $this->parse('instockno');
				}
			}
			// stock #4
			if($value['quantity'] <= $value['inStock3'])
			{
				$in_stock6 = $this->parse('instockyes');
			}
			//this product is out of stock
			else
			{
				if($value['inStock3'] > 0)
				{
					$in_stock6 = $this->parse('instockpartially');
				}
				else
				{
					// lets check if we know when the goods are possibly available
					$date = $this->_get_date_by_supplier_id(array(
						"supplier_id" => $product_info_arr[$value['product_code']]['supplier_id'],
						'supplier_times' => $supplier_times
					));
					// if we know that, then lets show it to users too:
					if ($date !== false)
					{
						$this->vars(array(
							"trans_instock_no" => $date,
						));
					}
					else
					{
						$this->vars(array(
							"trans_instock_no" => $old_trans_instock_no,
						));
					}

					$in_stock6 = $this->parse('instockno');
				}
			}
			// stock #5
			if($value['quantity'] <= $value['inStock4'])
			{
				$in_stock7 = $this->parse('instockyes');
			}
			else
			{
				//this product is out of stock:
				if($value['inStock4'] > 0)
				{
					$in_stock7 = $this->parse('instockpartially');
				}
				else
				{
					// lets check if we know when the goods are possibly available
					$date = $this->_get_date_by_supplier_id(array(
						"supplier_id" => $product_info_arr[$value['product_code']]['supplier_id'],
						'supplier_times' => $supplier_times
					));
					// if we know that, then lets show it to users too:
					if ($date !== false)
					{
						$this->vars(array(
							"trans_instock_no" => $date,
						));
					}
					else
					{
						$this->vars(array(
							"trans_instock_no" => $old_trans_instock_no,
						));
					}

					$in_stock7 = $this->parse('instockno');
				}
			}
			// stock #6
			if($value['quantity'] <= $value['inStock5'])
			{
				$in_stock8 = $this->parse('instockyes');
			}
			else
			{
				//this product is out of stock:
				if($value['inStock5'] > 0)
				{
					$in_stock8 = $this->parse('instockpartially');
				}
				else
				{
					// lets check if we know when the goods are possibly available
					$date = $this->_get_date_by_supplier_id(array(
						"supplier_id" => $product_info_arr[$value['product_code']]['supplier_id'],
						'supplier_times' => $supplier_times
					));
					// if we know that, then lets show it to users too:
					if ($date !== false)
					{
						$this->vars(array(
							"trans_instock_no" => $date,
						));
					}
					else
					{
						$this->vars(array(
							"trans_instock_no" => $old_trans_instock_no,
						));
					}

					$in_stock8 = $this->parse('instockno');
				}
			}
			if((string)$arr["asukoht"] == 0)
			{
				$in_stock4 = "n/a";
				$in_stock5 = "n/a";
				$in_stock6 = "n/a";
				$in_stock7 = "n/a";
				$in_stock8 = "n/a";
			}
			elseif((string)$arr["asukoht"] == 1)
			{
				$in_stock3 = "n/a";
				$in_stock5 = "n/a";
				$in_stock6 = "n/a";
				$in_stock7 = "n/a";
				$in_stock8 = "n/a";
			}
			elseif((string)$arr['asukoht'] == 2)
			{
				$in_stock3 = "n/a";
				$in_stock4 = "n/a";
				$in_stock6 = "n/a";
				$in_stock7 = "n/a";
				$in_stock8 = "n/a";
			}
			elseif((string)$arr['asukoht'] == 3)
			{
				$in_stock3 = "n/a";
				$in_stock4 = "n/a";
				$in_stock5 = "n/a";
				$in_stock7 = "n/a";
				$in_stock8 = "n/a";
			}
			elseif((string)$arr['asukoht'] == 4)
			{
				$in_stock3 = "n/a";
				$in_stock4 = "n/a";
				$in_stock5 = "n/a";
				$in_stock6 = "n/a";
				$in_stock8 = "n/a";
			}
			elseif((string)$arr['asukoht'] == 5)
			{
				$in_stock3 = "n/a";
				$in_stock4 = "n/a";
				$in_stock5 = "n/a";
				$in_stock6 = "n/a";
				$in_stock7 = "n/a";
			}
			
			$this->vars(array(
				"in_Stock3" => $in_stock3,
				"in_Stock4" => $in_stock4,
				"in_Stock5" => $in_stock5,
				"in_Stock6" => $in_stock6,
				"in_Stock7" => $in_stock7,
				"in_Stock8" => $in_stock8,
			));
			$value['finalPrice'] = number_format($value['price'] * ((100 - $value['discount']) / 100), 2, '.', '');
			$value['search_code'] = str_replace(' ','&nbsp;', $value['search_code']);
			$value['product_code'] = str_replace(' ','&nbsp;', $value['product_code']);
			//$value['replacement'] = ($value['replacement'])?'Peatoode':'Asendus';
			$value['i'] = $i++;
			$this->vars($value);
			//
			if($value['quantity'] <= $value['inStock'])
			{
				$this->vars(array(
					'quantityParsed' => $this->parse('canSetQuantity'),
					'karuParsed' => $this->parse('karu')
				));
			}
			else
			{
				$this->vars(array(
					'quantityParsed' => $this->parse('cannotSetQuantity'),
					'karuParsed' => $this->parse('karupole'),
				));
			}

			//kas on asendustoode v6i mitte
			if($value['replacement'] == 'K&uuml;situd')
			{
				$this->vars(array(
					'esimeneVeerg' => $this->parse('mainproduct')
				));
			}
			else
			{
				$this->vars(array(
					'esimeneVeerg' => $this->parse('asendustoodeblock')
				));
			}
			$content .= $this->parse('product');
			$count++;
		}
		$this->vars(array('productParsed' => $content));
		$data = '';
			
		//make column label bold if it was used to sort
		$tmpArr = array(
			'cssstaatus' => 'listTitle',
			'csstootekood' => 'listTitle',
			'cssnimetus' => 'listTitle',
			'cssotsitunnus' => 'listTitle',
			'csshind' => 'listTitle',
			'cssallahindlus' => 'listTitle',
			'csslopphind' => 'listTitle',
			'csslaos' => 'listTitle',
		);
		$tmpArr['css'.$hidden['orderBy']] = 'listTitleSort';
		$this->vars($tmpArr);
		classload('taket/taket_ebasket');
		$ebasket = new taket_ebasket();
		if(sizeof($_SESSION['TAKET']['ebasket_list']))
		{
			$tmp = '';
			foreach($_SESSION['TAKET']['ebasket_list'] as $key => $value)
			{
				if($value != $ebasket->current_ebasket_identificator)
				{
					$this->vars(array('ebasket_list_item_name' => $value));
					$tmp .= $this->parse('ebasket_list_item');
				}
			}
			$this->vars(array('ebasket_list_items' => $tmp));
			$this->vars(array('ebasket_list_value' => $this->parse('ebasket_list')));
		}
		

		//assign hidden values
		$this->vars($hidden);

		//generating page numbers
		$count2 = $count;
		$count = ceil($numOfRows/40);
		$content = '';
		for($i = 0; $i < $count; $i++)
		{
			$prev = $noSkipped ? ($noSkipped-40) : 0;
			$next = ($noSkipped == 40*4) ? (40*4) : ($noSkipped+40);
			$pageNumber = ($i*40) == $noSkipped ? '<b>'.($i+1).'</b>' : ($i+1);
			if($count == 0)
			{
				$next = 0;
			}
			$this->vars(array(
				'next' => $next,
				'prev' => $prev,
				'pageNumber' => $pageNumber,
				'start' => $i*40,
			));
			$content .= $this->parse('pageNumbers');
		}
		$this->vars(array('pageNumbersParsed' => $content));
		if($count>1)
		{
			$this->vars(array('numbersPart' => $this->parse('numbersPart')));
		}
		
		//simple var assignments
		$this->vars(array(
			'otsisin' => $arr['tootekood'].' '.$arr['otsitunnus'],
			'tootekood' => $arr['tootekood'],
			'toote_nimetus' => $arr['toote_nimetus'],
			'results' => $numOfRows
		));

		if (aw_ini_get('taket_extended_log'))
		{
			$this->site_log($site_log_line);
		}

		return $this->parse();
	}

	function on_get_subtemplate_content($arr)
	{
		$inst= &$arr['inst'];
	
		//h6mm main.tpl'i subi TAKET_SEARCH peax vist ikkagi
		//n2itama antud klassi show.tpl'i	
		$this->read_template('show.tpl');
		//reforb
		$asukoht = !$_REQUEST["asukoht"] ? 0 : $_REQUEST["asukoht"];
		switch($asukoht)
		{
			case -1:
				$name = "lis_sel";
				break;
			
			case 1:
				$name = "lis_sel1";
				break;
			case 2:
				$name = "lis_sel2";
				break;
			case 3: 
				$name = "lis_sel3";
				break;
			case 4: 
				$name = "lis_sel4";
				break;
			case 5: 
				$name = "lis_sel5";
				break;
			default:
				$name = "lis_sel0";
				break;
		}
		$value = array();
		if(!$_REQUEST["wvat"])
		{
			$wvat = $_COOKIE["wvat"];
		}
		else
		{
			$wvat = $_REQUEST["wvat"];
		}
		if($wvat == 1)
		{
			$value["wvat_check"] = "checked";
		}
		else
		{
			$wvat = 0;
		}
		setcookie("wvat", $wvat, (3600*24*365*5));
		
		$this->vars(array(
			'reforb'=>$this->mk_reforb('parse_submit_info', array('no_reforb'=>true)),
			$name => "selected",
		) + $value);
		$inst->vars(array(
			'taket_search_content'=>$this->parse()
		));

		$inst->vars(array(
			'TAKET_SEARCH' => $inst->parse("TAKET_SEARCH")
		));	
	}
	////
	// supplier_id - Supplier id
	function _get_date_by_supplier_id($arr)
	{
		// JC (supplier_id == 179) 
		// teisip2eva 6htust on ylej2rgmine esmasp2ev v6imalik
//		$supplier_times = $this->db_fetch_array("select * from taket_times where supplier_id='".$arr['supplier_id']."'");
		$supplier_times = $arr['supplier_times'];
		if (empty($arr['supplier_id']) || empty($supplier_times))
		{
			return false;
		}

		// i think, that supplier ids are unique, i don't assume that in times management
		// but here, if there are several, i'll take the first one
		$supplier_time = $supplier_times[$arr['supplier_id']];
		if ($supplier_time['day1'] == "---" || $supplier_time['day2'] == "---")
		{
			if ($supplier_time['date1'] >= (time() + $supplier_time['days'] * 86400))
			{
				$date = $supplier_time['date1'];
			} 
			else
			{
				$date = $supplier_time['date2'];
			}
		}
		else
		{
			// this is for strtotime, just to get the eng. day according to the number
			// i cant save the days like this in database, cause i need to do some 
			// comparison with day numbers
			$days = array(
				"0" => "Sun",
				"1" => "Mon",
				"2" => "Tue",
				"3" => "Wed",
				"4" => "Thu",
				"5" => "Fri",
				"6" => "Sat",
				"7" => "Sun"
			);

			// just for clearance:
			$delivery_day = $supplier_time['day1'];
//			$order_day = $supplier_time['day2'];

			$delivery_time = strtotime("this ".date("l")) + ($supplier_time['days'] * 24 * 3600);
			// in php4, if next "day" is same as today, then it returns today, not +1 week, changed in php5 --dragut
			if (date("w") == $delivery_day)
			{
				$next_delivery_day = strtotime("next ".$days[$delivery_day]) + (7 * 24 * 3600);
			}
			else
			{
				$next_delivery_day = strtotime("next ".$days[$delivery_day]);
			}

			if ($delivery_time <= $next_delivery_day)
			{

				$date = $next_delivery_day;
			}
			else
			{
				$date = strtotime("+1 week", $next_delivery_day);
			}

		}

		return date("d/m/y", $date);
	}
	
	/**

		@attrib name=give_me_times 

	**/
	function give_me_times($arr)
	{
		$this->read_template("give_me_times.tpl");

		$days = array(
			"---" => "---",
			"1" => "Esmasp&auml;ev",
			"2" => "Teisip&auml;ev",
			"3" => "Kolmap&auml;ev",
			"4" => "Neljap&auml;ev",
			"5" => "Reede",
			"6" => "Laup&auml;ev",
			"7" => "P&uuml;hap&auml;ev",
		);
		$suppliers = "";
		$suppliers_info = $this->db_fetch_array("SELECT * from taket_times");
		if (empty($suppliers_info))
		{
			$suppliers_info = array();
		}
		foreach($suppliers_info as $supplier)
		{
			$this->vars(array(
				"supplier_id" => $supplier['supplier_id'],
				"day1" => html::select(array(
					"name" => "suppliers[".$supplier['supplier_id']."][day1]",
					"options" => $days,
					"selected" => $supplier['day1'],
				)),
				"days" => html::textbox(array(
					"name" => "suppliers[".$supplier['supplier_id']."][days]",
					"size" => 7,
					"value" => $supplier['days'],
				)),
				"day2" => html::select(array(
					"name" => "suppliers[".$supplier['supplier_id']."][day2]",
					"options" => $days,
					"selected" => $supplier['day2'],
				)),
				"date1" => html::date_select(array(
					"name" => "suppliers[".$supplier['supplier_id']."][date1]",
					"value" => $supplier['date1'],
				)),
				"date2" => html::date_select(array(
					"name" => "suppliers[".$supplier['supplier_id']."][date2]",
					"value" => $supplier['date2'],
				)),
				"delete" => html::checkbox(array(
					"name" => "suppliers[".$supplier['supplier_id']."][delete]",
					"value" => $supplier['id'],
				)),
				"style" => "default_row",
			));
			$suppliers .= $this->parse("SUPPLIER");
		}
		// the row to add a new supplier
		$this->vars(array(
			"supplier_id" => html::textbox(array(
				"name" => "suppliers[new][supplier_id]",
				"size" => 10,
			)),
			"day1" => html::select(array(
				"name" => "suppliers[new][day1]",
				"options" => $days,
			)),
			"days" => html::textbox(array(
				"name" => "suppliers[new][days]",
				"size" => 7,
			)),
			"day2" => html::select(array(
				"name" => "suppliers[new][day2]",
				"options" => $days,
			)),
			"date1" => html::date_select(array(
				"name" => "suppliers[new][date1]",
			)),
			"date2" => html::date_select(array(
				"name" => "suppliers[new][date2]",
			)),
			"delete" => "",
			"style" => "new_row",
		));
		$suppliers .= $this->parse("SUPPLIER");

		$this->vars(array(
			"suppliers" => $suppliers,
			"reforb" => $this->mk_reforb("save_give_me_times", array("no_reforb" => true)),
		));
		

		return $this->parse();
	}

	/**
		@attrib name=save_give_me_times
		@param suppliers optional
	**/
	function save_give_me_times($arr)
	{
		$old_suppliers = $this->db_fetch_array("select * from taket_times");
		if (empty($old_suppliers))
		{
			$old_suppliers = array();
		}
		foreach ($old_suppliers as $old_supplier)
		{
                        $date1 = mktime(0,0,0,$arr['suppliers'][$old_supplier['supplier_id']]['date1']['month'], $arr['suppliers'][$old_supplier['supplier_id']]['date1']['day'], $arr['suppliers'][$old_supplier['supplier_id']]['date1']['year']);
                        $date2 = mktime(0,0,0,$arr['suppliers'][$old_supplier['supplier_id']]['date2']['month'], $arr['suppliers'][$old_supplier['supplier_id']]['date2']['day'], $arr['suppliers'][$old_supplier['supplier_id']]['date2']['year']);
			if (isset($arr['suppliers'][$old_supplier['supplier_id']]['delete']))
			{
				$this->db_query("delete from taket_times where id=".$old_supplier['id']);
			}
			else
			{
				$this->db_query("update taket_times set 
					day1='".$arr['suppliers'][$old_supplier['supplier_id']]['day1']."',
					days='".$arr['suppliers'][$old_supplier['supplier_id']]['days']."',
					day2='".$arr['suppliers'][$old_supplier['supplier_id']]['day2']."',
					date1='".$date1."',
					date2='".$date2."' 
					where id=".$old_supplier['id']
				);
			}
			
		}

		if (!empty($arr['suppliers']['new']['supplier_id']))
		{
			$date1 = mktime(0,0,0,$arr['suppliers']['new']['date1']['month'], $arr['suppliers']['new']['date1']['day'], $arr['suppliers']['new']['date1']['year']);
			$date2 = mktime(0,0,0,$arr['suppliers']['new']['date2']['month'], $arr['suppliers']['new']['date2']['day'], $arr['suppliers']['new']['date2']['year']);
			$days = (empty($arr['suppliers']['new']['days'])) ? 0 : $arr['suppliers']['new']['days'];
			$this->db_query("insert into taket_times set 
				supplier_id='".$arr['suppliers']['new']['supplier_id']."',
				day1='".$arr['suppliers']['new']['day1']."',
				days=".$days.",
				day2='".$arr['suppliers']['new']['day2']."',
				date1=".$date1.",
				date2=".$date2
			);
		}
		return $this->mk_my_orb("give_me_times");
	}

}
?>