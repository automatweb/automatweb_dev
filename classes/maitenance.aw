<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/maitenance.aw,v 1.2 2003/08/21 10:23:49 axel Exp $
// maitenance.aw - Saidi hooldus 
/*

@classinfo syslog_type=ST_MAITENANCE relatiomgr=yes

@default table=objects
@default group=general

*/

class maitenance extends class_base
{
	function maitenance()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. the default folder does not actually exist, 
		// it just points to where it should be, if it existed
		$this->init(array(
			"tpldir" => "maitenance",
			"clid" => CL_MAITENANCE
		));
	}

	function keys_vals($arr)
	{
		$keys = array();
		$vals = array();
		foreach($arr as  $key => $val)
		{
			$keys[] = $key;
			$vals[] = $val;
		}
		return array('keys' => implode(',',$keys), 'vals' => '"'.implode('","',$vals).'"');
	}
	
	function do_insert($arr,$table = 'objects')
	{
		$data = $this->keys_vals($arr);
		//echo 
		$q = 'insert into '.$table.' ('.$data['keys'].') values ('.$data['vals'].')';
		$this->db_query($q);
		return $this->db_last_insert_id();
	}
	
	function do_update($arr,$oid,$table)
	{
		if (!count($arr)>0) return false;
		
		$set = array();
		foreach($arr as $key => $val)
		{
			$set[] = $key.'="'.$val.'"';
		
		}
		$q = 'update '.$table.' set '.implode(', ',$set).' where oid='.$oid; 
		$this->db_query($q);
	}
	
	function my_trim($str)
	{
		$str = str_replace('&nbsp;',' ',$str);
		$str = str_replace('&amp;','&',$str);
		$str = trim(strip_tags($str));
		$str = str_replace('"','\"',$str);
		return $str;
	}
	
	function ta_convert($args)
	{
		$tegevusala_parent = 98130;
		$objdata = array(
			'parent' => $parent,
			'status' => 1,
			'createdby' => 'axel',
			'modifiedby' => 'axel',
			'created' => time(),
			'modified' => time(),
			'lang_id' => 1,
			'site_id' => 9,
		);
//id|kood|eng|est
		$next = isset($args['next']) ? $args['next'] : 0;
		$samm = isset($args['samm']) ? $args['samm'] : 1;
		
		$from = $next;
		$next = $next + $samm;

		echo $from.' - '.$next."<br />";
		
		$q = 'select * from html_import_tegevusalad limit '.$from.','.$samm;
		$arr = $this->db_fetch_array($q);
		//arr($arr);

		if(!is_array($arr))
		{
			die('pointer jõudis tabeli lõppu või tabel on tühi!');
		}
		foreach($arr as $key => $val)
		{
		//continue;		
		$tegevusala_obj_oid = 0;	
		$kood = $this->my_trim($val['kood']);
		$est = $this->my_trim($val['est']);
		$eng = $this->my_trim($val['eng']);
//		echo $kood;
		if ($kood > 0)
		{
			$tegevusala_obj_oid = $this->db_fetch_field('select t1.oid from kliendibaas_tegevusala as t1
			left join objects on objects.oid = t1.oid where objects.status=1
			and  t1.kood="'.$kood.'" and parent='.$tegevusala_parent.' limit 1', 'oid');

			if (is_numeric($tegevusala_obj_oid) && ($tegevusala_obj_oid > 0))
			{
			}
			else
			{
				$objects_data = array(
					'parent' => $tegevusala_parent,
					'name' => $kood.' '.$est,
					'class_id' => CL_TEGEVUSALA,
				);

				$tegevusala_obj_oid = $this->do_insert(array_merge($objdata,$objects_data),'objects');
				$tegevusala = array(
					'oid' => $tegevusala_obj_oid,
					'kood' => $kood,
					'tegevusala' => $est,
					'tegevusala_en' => $eng,
				);
				$this->do_insert($tegevusala,'kliendibaas_tegevusala');
			
			}
		}
		
		//echo $val['id'].' - '.$est."<br />";
		echo $val['id'].' - '."<br />";
		flush();
		//--end-------------------		
		}
		

	echo 	''.$str.' 
	<a href="'.$this->mk_my_orb('ta_convert', array('next' => $next,'samm' => $samm)).'#end">järgmised '.$samm.'</a><br />
	<a href="'.$this->mk_my_orb('ta_convert', array('next' => $next,'samm' => $samm = 5)).'#end">järgmised '.$samm.'</a><br />
	<a href="'.$this->mk_my_orb('ta_convert', array('next' => $next,'samm' => $samm = 10)).'#end">järgmised '.$samm.'</a><br />
	<a href="'.$this->mk_my_orb('ta_convert', array('next' => $next,'samm' => $samm = 20)).'#end">järgmised '.$samm.'</a><br />
	<a href="'.$this->mk_my_orb('ta_convert', array('next' => $next,'samm' => $samm = 50)).'#end">järgmised '.$samm.'</a><br />
	<a href="'.$this->mk_my_orb('ta_convert', array('next' => $next,'samm' => $samm = 100)).'#end">järgmised '.$samm.'</a><br />
	<a href="'.$this->mk_my_orb('ta_convert', array('next' => $next,'samm' => $samm = 250)).'#end">järgmised '.$samm.'</a><br />
	<a href="'.$this->mk_my_orb('ta_convert', array('next' => $next,'samm' => $samm = 400)).'#end">järgmised '.$samm.'</a><br />
	<a href="'.$this->mk_my_orb('ta_convert', array('next' => $next,'samm' => $samm = 800)).'#end">järgmised '.$samm.'</a><br />
			
		<a name=end></a><script>alert("valma");</script>';
		
		die('end');
	
	}	
	
	function get_kood($str)
	{
		preg_match('/([0-9]+)([^0-9])(.*)/Us',$str,$m);
		return $m[1];
	}
	
	function kb_convert($args)
	{
		//teha siia igat tüüpi objektidele eraldi kataloogid		
		$parent = 97175;//default
		$linn_parent = 98126;
		$maakond_parent = 98127;
		$firma_parent = 98125;
		$tegevusala_parent = 98130;
		$toode_parent = 98129;
		$ettevotlusvorm_parent = 98131;
		$aadress_parent = 98128;
		$isik_parent = 98132;
		$telefon_parent = 98243;
		$email_parent = 98241;
		$www_parent = 98242;
		/*
		deaktiivne = 1;
		aktiivne = 2;
		kustutatud = 0;
		*/
		$objdata = array(
			'parent' => $parent,
			'status' => 1,
			'createdby' => 'axel',
			'modifiedby' => 'axel',
			'created' => time(),
			'modified' => time(),
			'lang_id' => 1,
			'site_id' => 9,
		);
//id|tegevus_kir|pohitegevus|juht|www|korval|tooted|kaubamaerk|mail|faks|aadress|reg_nr|vorm|linn|maakond|mobl|tel|zip|nimi	
		//kui firmad jagada tähtede järgi kataloogidesse siis äkki on kliendibaasi lihtsam teha

		$next = isset($args['next']) ? $args['next'] : 0;
		$samm = isset($args['samm']) ? $args['samm'] : 1;
		
		$from = $next;
		$next = $next + $samm;

		echo $from.' - '.$next."<br />";
		
		$q = 'select * from html_import_firmad limit '.$from.','.$samm;
		$arr = $this->db_fetch_array($q);
		//arr($arr);

		if(!is_array($arr))
		{
			die('pointer jõudis tabeli lõppu või tabel on tühi!');
		}
		foreach($arr as $key => $val)
		{
		//continue;
		//--begin-------------------
		
		$pohitegevusala_obj_oid = 0;
		$isik_obj_oid = 0;
		$ettevotlusvorm_obj_oid = 0;
		$linn_obj_oid = 0;
		$maakond_obj_oid = 0;
		$aadress_obj_oid = 0;
		$telefon_obj_oid = 0;
		$mobiil_obj_oid = 0;
		$faks_obj_oid = 0;
				
		$firma_metadata = array();
		$isik_metadata = array();
		$aadress_metadata = array();
		
		$mkalias_tegevusalad = array();
		$mkalias_tooted = array();
		
		$adr_name = array();
		
		// -- pohitegevus
		$kood = $this->get_kood($val['pohitegevus']);
		
		if ($kood > 0)
		{
			$pohitegevusala_obj_oid = $this->db_fetch_field('select t1.oid from kliendibaas_tegevusala as t1
			left join objects on objects.oid = t1.oid where objects.status=1
			and  t1.kood="'.$kood.'" and parent='.$tegevusala_parent.' limit 1', 'oid');

			if (is_numeric($pohitegevusala_obj_oid) && ($pohitegevusala_obj_oid > 0))
			{
			}
			else
			{
				$objects_data = array(
					'parent' => $tegevusala_parent,
					'name' => $this->my_trim($val['pohitegevus']),
					'class_id' => CL_TEGEVUSALA,
				);

				$pohitegevusala_obj_oid = $this->do_insert(array_merge($objdata,$objects_data),'objects');
				$pohitegevusala = array(
					'oid' => $pohitegevusala_obj_oid,
					'kood' => $kood,
					'tegevusala' => strip_tags(trim($val['pohitegevus'],' 0123456789	')),
				);
				$this->do_insert($pohitegevusala,'kliendibaas_tegevusala');
			
			}
			$mkalias_tegevusalad[] = $pohitegevusala_obj_oid;
		}
		// -- end pohitegevus
		
		
		//tooted
		preg_match_all('/<b>([0-9]+)<\/b>&nbsp;(.*)<\/font><br>/Us',$val['tooted'],$tooted,2);
		foreach($tooted as $toode)
		{
			$tkood = $this->my_trim($toode[1]);
			$tname = $this->my_trim($toode[2]);
						
			$toode_obj_oid = 0;
					
			$toode_obj_oid = $this->db_fetch_field('select t1.oid from kliendibaas_toode as t1
			left join objects on objects.oid = t1.oid where objects.status=1
			and  t1.kood="'.$tkood.'" and parent='.$toode_parent.'
			
			', 'oid');
			
			if (is_numeric($toode_obj_oid) && ($toode_obj_oid > 0))
			{
			}
			else
			{
				$objects_data = array(
					'parent' => $toode_parent,
					'name' => $tkood.' '.$tname,
					'class_id' => CL_TOODE,
				);

				$toode_obj_oid = $this->do_insert(array_merge($objdata,$objects_data),'objects');
				$toode_data = array(
					'oid' => $toode_obj_oid,
					'kood' => $tkood,
					'toode' => $tname,
				);
				$this->do_insert($toode_data,'kliendibaas_toode');
			}
			$mkalias_tooted[] = $toode_obj_oid;
		}
		// end tooted
		
		//korvaltegevused

		preg_match_all('/<b>([0-9]+)<\/b>&nbsp;(.*)<\/font><br>/Us',$val['korval'],$korvaltegevused,2);
		foreach($korvaltegevused as $korvaltegevus)
		{
			$tkood = $this->my_trim($korvaltegevus[1]);
			$tname = $this->my_trim($korvaltegevus[2]);
						
			$korvaltegevus_obj_oid = 0;
					
			$korvaltegevus_obj_oid = $this->db_fetch_field('select t1.oid from kliendibaas_tegevusala as t1
			left join objects on objects.oid = t1.oid where objects.status=1
			and  t1.kood="'.$tkood.'" and parent='.$tegevusala_parent.'
			
			', 'oid');
			
			if (is_numeric($korvaltegevus_obj_oid) && ($korvaltegevus_obj_oid > 0))
			{
				//$pohitegevusala_obj_oid = $olemas;
			}
			else
			{
				$objects_data = array(
					'parent' => $tegevusala_parent,
					'name' => $tkood.' '.$tname,
					'class_id' => CL_TEGEVUSALA,
				);

				$korvaltegevus_obj_oid = $this->do_insert(array_merge($objdata,$objects_data),'objects');
				$korvaltegevus_data = array(
					'oid' => $korvaltegevus_obj_oid,
					'kood' => $tkood,
					'tegevusala' => $tname,
				);
				$this->do_insert($korvaltegevus_data,'kliendibaas_tegevusala');
			}
			$mkalias_tegevusalad[] = $korvaltegevus_obj_oid;
		}
		//end korvaltegevused		
		
		// juht
		if (strlen($this->my_trim($val['juht'])) > 3)
		{
		//$name = "peeter-meeter takso juht";
			$name = $this->my_trim($val['juht']);
			$objects_data = array(
				'parent' => $isik_parent,
				'name' => $name,
				'class_id' => CL_ISIK,
			);
			$isik_obj_oid = $this->do_insert(array_merge($objdata,$objects_data),'objects');
			
			$nameparts = explode(' ',$name);
			$lastname = array_pop($nameparts);
			$firstname = implode(' ',$nameparts);
			
			$isik = array(
				'oid' => $isik_obj_oid,
				'firstname' => $firstname,
				'lastname' => $lastname,
			);

			$this->do_insert($isik,'kliendibaas_isik');
			//paneme isiku firmajuhiks
			////$firma_metadata['firmajuht'] = $isik_obj_oid;
		}
		//end juht
		
		// vorm
		
		$vorm = $this->my_trim($val['vorm']);
		if (strlen($vorm)>1)
		{
		
			$ettevotlusvorm_obj_oid = $this->db_fetch_field('select objects.oid from objects 
			 where objects.class_id='.CL_ETTEVOTLUSVORM.' and objects.status=1
			and objects.name="'.$vorm.'" and parent='.$ettevotlusvorm_parent.'
			', 'oid');
			
			if (is_numeric($ettevotlusvorm_obj_oid) && ($ettevotlusvorm_obj_oid > 0))
			{

			}
			else
			{
				$objects_data = array(
					'parent' => $ettevotlusvorm_parent,
					'name' => $vorm,
					'class_id' => CL_ETTEVOTLUSVORM,
				);
			
				$ettevotlusvorm_obj_oid = $this->do_insert(array_merge($objdata,$objects_data),'objects');
			}
			////$firma_metadata['ettevotlusvorm'] = $ettevotlusvorm_obj_oid;

		}
		// end vorm
		
		//aadress
		if (true==true)
		{
			// linn
			$linn = $this->my_trim($val['linn']);
			if (strlen($linn) > 0)
			{
				$linn_obj_oid = $this->db_fetch_field('select oid from objects
			where objects.class_id='.CL_LINN.' and objects.status=1
			and  objects.name="'.$linn.'" and objects.parent='.$linn_parent.'', 'oid');
			
				if (is_numeric($linn_obj_oid) && ($linn_obj_oid > 0))
				{

				}
				else
				{
					$objects_data = array(
						'parent' => $linn_parent,
						'name' => $linn,
						'class_id' => CL_LINN,
					);
					$linn_obj_oid = $this->do_insert(array_merge($objdata,$objects_data),'objects');
					$linn_data = array(
						'oid' => $linn_obj_oid,
					);

					$this->do_insert($linn_data,'kliendibaas_linn');
				}
				////$aadress_metadata['linn'] = $linn_obj_oid;
			}
			//end linn
			
			//maakond
			$maakond = $this->my_trim($val['maakond']);
			if (strlen($maakond) > 0)
			{
				$maakond_obj_oid = $this->db_fetch_field('select oid from objects
			where objects.class_id='.CL_MAAKOND.' and objects.status=1
			and  objects.name="'.$maakond.'" and objects.parent='.$maakond_parent.'', 'oid');

						
				if (is_numeric($maakond_obj_oid) && ($maakond_obj_oid > 0))
				{

				}
				else
				{
					$objects_data = array(
						'parent' => $maakond_parent,
						'name' => $maakond,
						'class_id' => CL_MAAKOND,
					);
					$maakond_obj_oid = $this->do_insert(array_merge($objdata,$objects_data),'objects');
					$maakond_data = array(
						'oid' => $maakond_obj_oid,
					);

					$this->do_insert($maakond_data,'kliendibaas_maakond');
				}
				////$aadress_metadata['maakond'] = $maakond_obj_oid;
			}
			//end maakond
						
			$aadress = $this->my_trim($val['aadress']);
			
			
			//mobiil
			$mobl = $this->my_trim($val['mobl']);
			if (strlen($mobl) > 1)
			{
				$objects_data = array(
					'parent' => $telefon_parent,
					'name' => $mobl,
					'class_id' => CL_PHONE,
				);
				$telefon_obj_oid = $this->do_insert(array_merge($objdata,$objects_data),'objects');
				/*$this->set_object_metadata(array(
					"oid" => $mobiil_obj_oid,
					"data" => array('tyyp' => 'mobile'),
				));*/
			}
			//end mobiil
			
			//faks			
			$faks = $this->my_trim($val['faks']);
			if (strlen($faks) > 1)
			{
				$objects_data = array(
					'parent' => $telefon_parent,
					'name' => $faks,
					'class_id' => CL_PHONE,
				);
				$faks_obj_oid = $this->do_insert(array_merge($objdata,$objects_data),'objects');
				/*$this->set_object_metadata(array(
					"oid" => $faks_obj_oid,
					"data" => array('tyyp' => 'fax'),
				));*/
			}
			//end faks
			
			//telefon
			$tel = $this->my_trim($val['tel']);
			if (strlen($tel) > 1)
			{
				$objects_data = array(
					'parent' => $telefon_parent,
					'name' => $tel,
					'class_id' => CL_PHONE,
				);
				$telefon_obj_oid = $this->do_insert(array_merge($objdata,$objects_data),'objects');
				/*$this->set_object_metadata(array(
					"oid" => $telefon_obj_oid,
					"data" => array('tyyp' => 'general'),
				));*/
			}
			//end telefon
			
			$www = $this->my_trim($val['www']);
			if (strlen($www) > 1)
			{
				$objects_data = array(
					'parent' => $www_parent,
					'name' => $this->my_trim($val['nimi']).' '.$www,
					'class_id' => CL_EXTLINK,
				);
				$www_obj_oid = $this->do_insert(array_merge($objdata,$objects_data),'objects');
				$www_data = array(
					'id' => $www_obj_oid,
					'url' => $www,
				);
				$this->do_insert($www_data,'extlinks');
			}
						
			$mail = $this->my_trim($val['mail']);
			if (strlen($mail) > 1)
			{
				$objects_data = array(
					'parent' => $email_parent,
					'name' => $mail,
					'class_id' => CL_EXTLINK,
				);
				$email_obj_oid = $this->do_insert(array_merge($objdata,$objects_data),'objects');
				$email_data = array(
					'id' => $email_obj_oid,
					'url' => $mail,
				);
				$this->do_insert($email_data,'extlinks');
			}
			
			
			
			$zip = $this->my_trim($val['zip']);	
			
			
			if (strlen($aadress)>0)
				$adr_name[] = $aadress;
			if (strlen($linn)>0)
				$adr_name[] = $linn;
			if (strlen($maakond)>0)
				$adr_name[] = $maakond;
			if (count($adr_name) < 1)
			{
				if (strlen($mail)>0)
					$adr_name[] = $mail;
			}
			if (count($adr_name) < 1)
			{
				if (strlen($tel)>0)
					$adr_name[] = $tel;
			}
	
			
			$objects_data = array(
				'parent' => $aadress_parent,
				'name' => implode(', ',$adr_name),
				'class_id' => CL_ADDRESS,
			);
			$aadress_obj_oid = $this->do_insert(array_merge($objdata,$objects_data),'objects');
					
			$aadress = array(
				'oid' => $aadress_obj_oid,
				'postiindeks' => $zip,
				'telefon' => $telefon_obj_oid,
				'faks' => $faks_obj_oid,
				'mobiil' => $mobl_obj_oid,
				'aadress' => $aadress,
				'e_mail' => $email_obj_oid,
				'kodulehekylg' => $www_obj_oid,
				'linn' => $linn_obj_oid,
				'maakond' => $maakond_obj_oid,
//				'' => ,
			);

			$this->do_insert($aadress,'kliendibaas_address');
			
			////$firma_metadata['contact'] = $aadress_obj_oid;
			
			/*$this->set_object_metadata(array(
				"oid" => $aadress_obj_oid,
				"data" => $aadress_metadata,
			));*/
		}
		
		// end adress
		
	
		// -- firma
				
		$objects_data = array(
			'parent' => $firma_parent,
			'name' => $this->my_trim($val['nimi']),
			'class_id' => CL_FIRMA,
		);
						
		$firma_obj_oid = $this->do_insert(array_merge($objdata,$objects_data),'objects');
		
		$this->set_object_metadata(array(
			"oid" => $firma_obj_oid,
			"data" => $firma_metadata,
		));
		// -- end firma
		
		// -- firma data				
		$kliendibaas_firma_data = array(
			'oid' => $firma_obj_oid,
			'reg_nr' => $this->my_trim($val['reg_nr']),
			'tegevuse_kirjeldus' => $this->my_trim($val['tegevus_kir']),
			//'korvaltegevused' => '',
			'ettevotlusvorm' => $ettevotlusvorm_obj_oid,
			'contact' => $aadress_obj_oid,
			'firmajuht' => $isik_obj_oid,
			'pohitegevus' => $pohitegevusala_obj_oid,
			'kaubamargid' => $this->my_trim($val['kaubamaerk']),
			//'tooted' => '',
		);
		
		$this->do_insert($kliendibaas_firma_data,'kliendibaas_firma');
		// -- end firma data				
		
		foreach($mkalias_tegevusalad as $tegevusala_obj_oid)
		{
			$this->addalias(array(
				'id' => $firma_obj_oid,
				'alias' => $tegevusala_obj_oid,
				'no_cache' => true,
				'reltype' => 5,//TEGEVUSALAD
			));
		}
		
		foreach($mkalias_tooted as $toode_obj_oid)
		{
			$this->addalias(array(
				'id' => $firma_obj_oid,
				'alias' => $toode_obj_oid,
				'no_cache' => true,
				'reltype' => 6,//TOOTED
			));
		}
		
		
		
		if ($isik_obj_oid > 0)
		{	//paneme isiku firma töötajaks
			$this->addalias(array(
				'id' => $firma_obj_oid,
				'alias' => $isik_obj_oid,
				'no_cache' => true,
				'reltype' => 8,//WORKERS
			));
			
						
			//märgime isiku juurde mis organisatsioonis ta töötab
			$this->addalias(array(
				'id' => $isik_obj_oid,
				'alias' => $firma_obj_oid,
				'no_cache' => true,
				'reltype' => 6,//WORK
			));
			//$isik_metadata['work'] = $firma_obj_oid;
			/*			
			$this->set_object_metadata(array(
				"oid" => $isik_obj_oid,
				"data" => $isik_metadata,
			));*/
			$this->do_update(array('work_contact' => $firma_obj_oid),$isik_obj_oid,'kliendibaas_isik');
		}
		
		if ($ettevotlusvorm_obj_oid > 0)
		{
			$this->addalias(array(
				'id' => $firma_obj_oid,
				'alias' => $ettevotlusvorm_obj_oid,
				'no_cache' => true,
				'reltype' => 1,//ETTEVOTLUSVORM
			));
		}

		if (true==true)
		{
			$this->addalias(array(
				'id' => $firma_obj_oid,
				'alias' => $aadress_obj_oid,
				'no_cache' => true,
				'reltype' => 3,//ADDRESS
			));
		}
		
		if ($linn_obj_oid > 0)//aadressile linna seose
		{
			$this->addalias(array(
				'id' => $aadress_obj_oid,
				'alias' => $linn_obj_oid,
				'no_cache' => true,
				'reltype' => 1,//LINN
			));
		}
		if ($maakond_obj_oid > 0)//aadressile maakonna seose
		{
			$this->addalias(array(
				'id' => $aadress_obj_oid,
				'alias' => $maakond_obj_oid,
				'no_cache' => true,
				'reltype' => 3,//MAAKOND
			));
		}
		
		if ($telefon_obj_oid > 0)//aadressile telfoni seose
		{
			$this->addalias(array(
				'id' => $aadress_obj_oid,
				'alias' => $telefon_obj_oid,
				'no_cache' => true,
				'reltype' => 7,//TELEFON
			));	
		}
		if ($faks_obj_oid > 0)//aadressile faksi seose
		{
			$this->addalias(array(
				'id' => $aadress_obj_oid,
				'alias' => $faks_obj_oid,
				'no_cache' => true,
				'reltype' => 9,//FAKS
			));	
		}
		if ($mobiil_obj_oid > 0)//aadressile mobiili seose
		{
			$this->addalias(array(
				'id' => $aadress_obj_oid,
				'alias' => $mobiil_obj_oid,
				'no_cache' => true,
				'reltype' => 8,//MOBIIL
			));	
		}

		if ($www_obj_oid > 0)//aadressile  seose
		{
			$this->addalias(array(
				'id' => $aadress_obj_oid,
				'alias' => $www_obj_oid,
				'no_cache' => true,
				'reltype' => 6,//WWW
			));	
		}
		
		if ($email_obj_oid > 0)//aadressile  seose
		{
			$this->addalias(array(
				'id' => $aadress_obj_oid,
				'alias' => $email_obj_oid,
				'no_cache' => true,
				'reltype' => 5,//EMAIL
			));	
		}
				
		
		//if (true)//aadressile seos firma/ema objekt
		{
			$this->addalias(array(
				'id' => $aadress_obj_oid,
				'alias' => $firma_obj_oid,
				'no_cache' => true,
				'reltype' => 4,//BELONGTO
			));
		}
		
		
		echo $val['id'].' - '.$val['nimi']."<br />";
		flush();
		//--end-------------------		
		}
		
	////
	//   id - the id of the object where the alias will be attached
	//   alias - the id of the object to attach as an alias
	//   relobj_id - reference to the relation object
	//   reltype - type of the relation
	//   no_cache - if true, cache is not updated
	//   
	
	/*
	addalias(array(
		'' => '',
		'reltype' => 5,
	));
	*/


	echo 	''.$str.' 
	<a href="'.$this->mk_my_orb('kb_convert', array('next' => $next,'samm' => $samm)).'">järgmised '.$samm.'</a><br />
	<a href="'.$this->mk_my_orb('kb_convert', array('next' => $next,'samm' => $samm = 5)).'">järgmised '.$samm.'</a><br />
	<a href="'.$this->mk_my_orb('kb_convert', array('next' => $next,'samm' => $samm = 10)).'">järgmised '.$samm.'</a><br />
	<a href="'.$this->mk_my_orb('kb_convert', array('next' => $next,'samm' => $samm = 20)).'">järgmised '.$samm.'</a><br />
	<a href="'.$this->mk_my_orb('kb_convert', array('next' => $next,'samm' => $samm = 30)).'">järgmised '.$samm.'</a><br />
	<a href="'.$this->mk_my_orb('kb_convert', array('next' => $next,'samm' => $samm = 50)).'">järgmised '.$samm.'</a><br />
	<a href="'.$this->mk_my_orb('kb_convert', array('next' => $next,'samm' => $samm = 70)).'">järgmised '.$samm.'</a><br />
	<a href="'.$this->mk_my_orb('kb_convert', array('next' => $next,'samm' => $samm = 100)).'">järgmised '.$samm.'</a><br />
	<a href="'.$this->mk_my_orb('kb_convert', array('next' => $next,'samm' => $samm = 150)).'">järgmised '.$samm.'</a><br />
			
		<script>alert("valma");</script>';
		
		die('end');
	}
	
	function cache_clear($args)
	{
		echo "<br />
		<input type='button' value='clear cache' 
		onclick=\"document.location='".$this->mk_my_orb('cache_clear', array('clear' => '1'))."'\"><br />";
		
		$dir = aw_ini_get("cache.page_cache").'/';	
		$files = array();
		$cnt = 0;
		if ($handle = opendir($dir))
		{
			while (false !== ($file = readdir($handle)))
			{ 
				if ($file != "." && $file != "..")
				{ 
					$files[] = $file; 
				} 
			}
			closedir($handle); 
		}		
		
		if (isset($args['clear']))
		{
			echo 'about to delete '.count($files).' files<br />';
			$deleted = 0;
			foreach($files as $val)
			{
				if (unlink($dir.$val))
				{
					$deleted++;
				}
				
				$cnt++;
				if ($cnt > 1000)
				{
					echo " #";
					flush(); 
					$cnt = 0;
				}
			}
			echo '<br />'.$deleted.' files deleted!!<br />';
		}
		else
		{
			echo 'total:'. count($files).' files';
			if (isset($args['list']))
			{
				arr($files);
			}

		}
		die();
	}
	
	//////
	// class_base classes usually need those, uncomment them if you want to use them

	/*
	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

		};
		return $retval;
	}
	*/

	/*
	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
                {

		}
		return $retval;
	}	
	*/

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array("id" => $alias["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = new object($id);

		$this->read_template("show.tpl");

		$this->vars(array(
			"name" => $ob->prop("name"),
		));

		return $this->parse();
	}
}
?>
