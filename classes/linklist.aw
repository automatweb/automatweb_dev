<?php
/*
	@default table=objects
	@default group=general
	@property comment type=textarea field=comment cols=40 rows=3
	@caption Kommentaar

	@default field=meta
	@default method=serialize

	@property lingiroot type=select
	@caption lingikogu root

	@property add_remove type=text
	@caption lisa tasand (tasandi lisamisel saab ta algul default ehk 0 tasandi väärtused)

	@property forms type=select
	@caption formid
	@property felement type=select
	@caption formi element
	@property vordle type=select
	@caption formi element vastavusse

	@property dir_is_form_result type=checkbox ch_value=1
	@caption kataloog on formi väljund

	@property is_formentry type=select
	@caption kataloogi link on:

	@property YAH type=checkbox ch_value=1
	@caption YAH riba näidatakse

	@property active_dirs type=checkbox ch_value=1
	@caption kuvada ainult aktiivsed kataloogid

	@property active_links type=checkbox ch_value=1
	@caption kuvada ainult aktiivsed lingid

	@property levels type=text
	@property dir type=textbox
	@property diri type=textbox
	@property link type=textbox
	@property yahi type=textbox
	@property add_level type=textbox
	@property delete_level type=textbox


////////////////////////////////////////////////

//	@default group=whesftgh
//	@groupinfo output_conf caption=väljund

///////////////////////////////////////////////

*/

class linklist extends class_base
{

	function linklist()
	{
		define('SHOW_TPL_DIR',aw_ini_get('tpldir').'/linklist/show');
		$this->init(array(
			'clid' => CL_LINK_LIST,
		));
	}


	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = PROP_OK;
		$meta=$args['obj']['meta'];
		$id=$args['obj']['oid'];
		switch($data['name'])
		{
			case 'dir_is_form_result':
				$retval=PROP_IGNORE;
			break;
			case 'dir':
				$retval=PROP_IGNORE;
			break;
			case 'diri':
				$retval=PROP_IGNORE;
			break;
			case 'link':
				$retval=PROP_IGNORE;
			break;
			case 'yahi':
				$retval=PROP_IGNORE;
			break;
			case 'alias':
				$retval=PROP_IGNORE;
			break;
			case 'jrk':
				$retval=PROP_IGNORE;
			break;
			case 'status':
				//$retval=PROP_IGNORE;
			break;
			case 'comment':
				$retval=PROP_IGNORE;
			break;

			case 'levels':
				$data['value']=$this->levels($args['obj']);
			break;

			case 'is_formentry':
				$data['options'] = array('is_formentry' => 'on vormisisestus','is_not_formentry' => 'on tavaline kataloog vmt');
				$data['selected'] = $meta['is_formentry'];
									$retval=PROP_IGNORE;
			break;

			case 'vordle':

		// these are the directory properties which we can assign to a form element for searching
		$propertid=array(
			'oid'		=> 'oid',
			'name'		=> 'name',
			'parent'	=>'parent',
			'createdby'	=> 'createdby',
			'class_id'	=> 'class_id',
			'created'	=> 'created',
			'modified'	=> 'modified',
			'status'	=> 'status',
			'hits'		=> 'hits',
			'lang_id'	=> 'lang_id',
			'comment'	=> 'comment',
			'last'		=> 'last',
			'modifiedby'	=> 'modifiedby',
			'jrk'		=> 'jrk',
			'visible'	=> 'visible',
			'period'	=> 'period',
			'alias'		=> 'alias',
			'periodic'	=> 'periodic',
			'site_id'	=> 'site_id',
			'doc_template'	=> 'doc_template',
			'activate_at'	=> 'activate_at',
			'deactivate_at'	=> 'deactivate_at',
			'autoactivate'	=> 'autoactivate',
			'autodeactivate'	=> 'autodeactivate',
			'brother_of'	=> 'brother_of',
			'cachedirty'	=> 'cachedirty',
			'metadata'	=> 'metadata',
		);
				$data['options'] = $propertid;
				$data['selected'] = $meta['vordle'];
									$retval=PROP_IGNORE;
			break;

			case 'forms':
			$forms = $this->list_objects(array(
					'class' => CL_FORM,
					'orderby' => 'name',
//					'return' => ARR_ALL,
				));
				$data['options'] = $forms;
				$data['selected'] = $meta['forms'];
									$retval=PROP_IGNORE;
			break;
			case 'felement':
				if($meta['forms'])
				{
					$form = get_instance('formgen/form');
	/*				$form->load($ob['meta']['forms']);
	//siin tekim mingi error kui fomi elemente ei leita
					$felement = $form->get_form_elements(array(
						'id' => $ob['meta']['forms'],
						'key' => 'id',
						'use_loaded' => true,
						'all_data' => false,
					));
	*/
					$data['options'] = $felement;
					$data['selected'] = $meta['felement'];

				}
				else
				{
					$retval=PROP_IGNORE;
				}
					$retval=PROP_IGNORE;
			break;

			case 'lingiroot':

				$objects = get_instance('objects');
				$root_list= $objects->get_list();
				$data['options'] = $root_list;
				$data['selected'] = $meta['lingiroot'];

				$retval=PROP_IGNORE;
			break;


			case 'add_level':
				$retval=PROP_IGNORE;
			break;
			case 'delete_level':
				$retval=PROP_IGNORE;
			break;


			case 'add_remove':

				$data['value']=
				html::button(array(
					'value'=>'lisa tasand',
					'onclick'=>'document.changeform.add_level.value=1;document.changeform.submit();'
				)).
				html::textbox(array('name'=>'add_level','value'=>'')).
				html::textbox(array('name'=>'delete_level','value'=>''));
//				html::hidden(array('name'=>'lisa_tasand','value'=>''));

			break;
		}
		return  $retval;
	}


	function levels($ob)
	{
		$meta= $ob['meta'];
		// list of a link object properties by wich we can order links
		$sortim = array (
			'name'		=> 'lingi nime',
			'jrk'		=> 'lingi jrknr',
			'modified'	=> 'muutmise aja',
			'modified' => 'modified',
			'created' => 'created',
			'oid' => 'oid',

		);
		//list of object properties, that we also can turn into a hyperlink

		$linkis=array(
			'caption' => 'caption',//			'name' => 'name', //name is actually the same as the caption
			'url' => 'url',
			'comment' => 'comment',
			'hits' => 'hits',
			'modified' => 'modified',
			'modifiedby' => 'modifiedby',
			'created' => 'created',
			'createdby' => 'createdby',
			'jrk' => 'jrk',

//			'oma_tekst_1'=>'oma_tekst_1',
//			'oma_tekst_2'=>'oma_tekst_2',
/*
			'oid' =>
			'parent' => 51394
			'class_id' => 21
			'created' => 1033467989
			'status' => 2
			'lang_id' => 6
			'last' =>
			'jrk' => 222
			'visible' => 1
			'period' =>
			'alias' =>
			'periodic' => 0
			'site_id' => 9
			'doc_template' => 0
			'activate_at' => 0
			'deactivate_at' => 0
			'autoactivate' => 0
			'autodeactivate' => 0
			'brother_of' => 0
			'cachedirty' => 1
			*/
		);

//see on see tasandite konfinnimise süteem
		$dir = $ob['meta']['dir'];
		$diri = $ob['meta']['diri'];
		$link = $ob['meta']['link'];
		$yahi = $ob['meta']['yahi'];

		//delete level(s)

		if ($meta['delete_level'])
		{
			unset($dir[$meta['delete_level']]);
			unset($link[$meta['delete_level']]);
			unset($diri[$meta['delete_level']]);
			unset($yahi[$meta['delete_level']]);
		}

		// default levelit ei saa kustutada, vaja on ju kindlasti templatet jne
		$dir[0]=$dir[0]?$dir[0]:array();
//		$dir[1]=$dir[1]?$dir[1]:array();

		// add level
		if($meta['add_level'])
		{
			$yahi[]= $yahi[0];
			$diri[]= $diri[0];
			$dir[]= $dir[0];
			$link[]= $link[0];
		}

		$list_templates = $this->get_templates(SHOW_TPL_DIR);
		load_vcl('table');
		$t = new aw_table(array(
			'prefix' => 'html_ruul_conf',
		));
		$t->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');
		$t->define_field(array(
			'name' => 'key',
			'caption' => 'object',
		));
		$t->define_field(array(
			'name' => 'level_template',
			'caption' => 'leveli templiit',
		));
		$t->define_field(array(
			'name' => 'show_yah',
			'caption' => 'YAH',
		));
		$t->define_field(array(
			'name' => 'show_dir',
			'caption' => 'DIR',
		));
		$t->define_field(array(
			'name' => 'tulpi',
			'caption' => 'tulpi',
		));
		$t->define_field(array(
			'name' => 'sortby_dirs',
			'caption' => 'sort dirs',
		));
		$t->define_field(array(
			'name' => 'jrk_columns',
			'caption' => 'jrk',
		));
		$t->define_field(array(
			'name' => 'show_links',
			'caption' => 'näita linke',
		));
		$t->define_field(array(
			'name' => 'tulpi2',
			'caption' => 'x',
		));
		$t->define_field(array(
			'name' => 'newwindow',
			'caption' => 'uues aknas',
		));
		$t->define_field(array(
			'name' => 'sortby_links',
			'caption' => 'sort link',
		));
		$t->define_field(array(
			'name' => 'kustuta',
			'caption' => 'kustuta',
		));

		foreach($dir as $key =>  $val)
		{
			$levels[$key] = array(
				'key' => (int)$key.'',
				'level_template' => html::select(array('name'=>'dir['.$key.'][level_template]', 'selected' => $dir[$key]['level_template'],'options'=>$list_templates)),
				'show_yah' => html::checkbox(array('name'=>'dir['.$key.'][show_yah]','checked'=>$dir[$key]['show_yah'])),
				'show_dir' => html::checkbox(array('name'=>'dir['.$key.'][show_dir]','checked'=>$dir[$key]['show_dir'])),
				'tulpi' => html::textbox(array('name' => 'dir['.$key.'][tulpi]' , 'value'=>(int)$dir[$key]['tulpi'], 'size'=>4)),
				'sortby_dirs' => html::select(array('name'=>'dir['.$key.'][sortby_dirs]','options' => $sortim,'selected'=>$dir[$key]['sortby_dirs'])),
				'jrk_columns' => html::checkbox(array('name'=>'dir['.$key.'][jrk_columns]','checked'=>$dir[$key]['jrk_columns'])),
				'show_links' => html::checkbox(array('name'=>'dir['.$key.'][show_links]','checked'=>$dir[$key]['show_links'])),
				'tulpi2' => html::textbox(array('name' => 'dir['.$key.'][tulpi2]' , 'value'=>(int)$dir[$key]['tulpi'], 'size'=>4)),
				'newwindow' => html::checkbox(array('name'=>'dir['.$key.'][newwindow]','checked'=>$dir[$key]['newwindow'])),
				'sortby_links' => html::select(array('name'=>'dir['.$key.'][sortby_links]','options' => $sortim,'selected'=>$dir[$key]['sortby_links'])),
//	'kustuta' => html::checkbox(array('name'=>'dir['.$key.'][kustuta]','checked'=>$dir[$key]['kustuta'])),
	'kustuta' => $key?html::button(array('value'=>'kustuta',
			'onclick'=>'document.changeform.delete_level.value='.$key.';document.changeform.submit();'
	)):'',
			);
		}

		ksort($levels);


		$arr = new aw_array($levels);
		foreach($arr->get() as $row)
		{
			$t->define_data(
				$row
			);
		}
//		$t->sort_by();


//		$levels = implode('',$levels);

		foreach($dir as $key=>$val)
		{
			$asjad=array('yahi'=>1,'diri'=>1,'link'=>1);
			$level=$key;
	/// iga jubina jaoks oma konf
			foreach($asjad as $key => $val)
			{
				$poo=$$key;
				$level_styles.="$level tasandi $key stiil ja konf".$this->propertii($key,$poo[$level],$level,$linkis);
			}
		}

		return $t->draw().'<hr />'.$level_styles;
	}
//			'vormisisestus' => $vormisisestus,				// formentry data (sub)




	function propertii($whaa,$obj,$level,$linkis)
	{
		$oma=array(); //plah :| ... oma teksti jaoks key-d, + 1 uue tekstivälja jaoks on see järgnev jama siin

		$i=0;$j=1;
		while(($obj['oma_tekst_'.++$i]))
		{
			if ($obj['oma_tekst_'.$i]['text']!='')
			{
				$obj['oma_tekst_'.$j]=$obj['oma_tekst_'.$i];
				$oma['oma_tekst_'.$j]='oma_tekst_'.$j;
				$j++;
			}
//			$i++;
		}
		$oma['oma_tekst_'.$j]='oma_tekst_'.$j; //üks tühi textbox lõppu et saaks uue lisada
		$obj['oma_tekst_'.$j]=array();

		$properti=$linkis + $oma; //textiväljade keyd siia otsa, igal levelil siis kujuneb eri arv tekstivälju, vastavalt vajadusele


		load_vcl('table');
		$t = new aw_table(array(
			'prefix' => 'html_ruul_conf',
		));
		$t->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');
		$t->define_field(array(
			'name' => 'ruul',
			'caption' => 'item',
		));
		$t->define_field(array(
			'name' => 'jrk',
			'caption' => 'jrk',
		));
		$t->define_field(array(
			'name' => 'show',
			'caption' => 'nähtav',
		));
		$t->define_field(array(
			'name' => 'hyper',
			'caption' => 'hüperlink',
		));
		$t->define_field(array(
			'name' => 'style',
			'caption' => 'stiil',
		));
		$t->define_field(array(
			'name' => 'br',
			'caption' => 'reavahetus',
		));
		$t->define_field(array(
			'name' => 'text',
			'caption' => 'lisa tekst',
		));

		$stiilid= $this->list_objects(array(
			'class' => CL_CSS,
			'orderby' => 'name',
		));

		foreach($properti as $key => $val)
		{

			$text='';
			/// see on lame aga praegu vaadatakse kas key nimi algab 'oma_' st siis on see enda sissestatav tekst
			if ((strpos($key,'oma_')==0) and (strpos($key,'oma_')!==false))
			{
				$text= html::textbox(array('name'=> $whaa.'['.$level.']['.$key.'][text]', 'value' => $obj[$key]['text'], 'size'=>10));
			}
			$data[]=array(
				'ruul' => $key,
				'jrk' => html::textbox(array('name'=>$whaa.'['.$level.']['.$key.'][jrk]', 'value'=>(int)$obj[$key]['jrk'],'size'=>4)),
				'show' =>html::checkbox(array('name'=>$whaa.'['.$level.']['.$key.'][show]','checked'=>$obj[$key]['show'])),
				'hyper' =>html::checkbox(array('name'=>$whaa.'['.$level.']['.$key.'][hyper]','checked'=>$obj[$key]['hyper'])),
				'style' =>html::select(array('name'=>$whaa.'['.$level.']['.$key.'][style]','options'=>$this->picker($obj[$key]['style'],array('vali') + $stiilid))),
				'br' =>html::checkbox(array('name'=>$whaa.'['.$level.']['.$key.'][br]','checked'=>$obj[$key]['br'])),
				'text' =>$text,
			);
		}

		$arr = new aw_array($data);
		foreach($arr->get() as $row)
		{
			$t->define_data(
				$row
			);
		}
//		$t->sort_by();

		return $t->draw();
	}


/*

		$p_tbp = get_instance('vcl/tabpanel');
		$p_tbp->hide_one_tab = false;
		$num_pages = ($per_page > 0 ? $num_rows / $per_page : 1);
		for ($i = 0; $i < $num_pages; $i++)
		{
			$p_tbp->add_tab(array(
				'active' => ($page == $i),
				'caption' => ($i * $per_page).' - '.min((($i+1) * $per_page), $num_rows),
				'link' => $this->mk_my_orb('content', array('id' => $ob['oid'], 'page' => $i))
			));
		}
		return $p_tbp->get_tabpanel(array('content' => ''));
*/

	////
	// !gets list of the files in given path (eg templates)
	// parameters:
	//  $path - directory where to search the files
	//  returns key and value as 'filename.ext', because numeric key may differ after file add/delete
	function get_templates($path,$ext='')
	{
		if ($dir = @opendir($path))
		{
			while (($file = readdir($dir)) !== false)
			{
				if ($file != '.' && $file != '..' && is_file("$path/$file"))
				{
					$list_templates[] = $file;
				}
			}
			closedir($dir);
		}
		return $this->make_keys($list_templates);
	}


	////
	// !linklist, currently shows a predefined catalogs at the top and below links of the active catalog or
	// form output maching the serach criteria
	//
	function show($arr)
	{
	return 'jee';
		extract($arr); // cd = current directory
		$uid= aw_global_get('uid');
		$this->write_stat(array(
			'oid'=>$cd,
			'lkid'=>$id,
			'uid'=>$uid,
			'action'=>1
		));

		$ob = $this->get_object($id);
		$cd = $cd?$cd:$ob['meta']['lingiroot'];
		$this->add_hit($cd);
		$ak = $cd;

		//start YAH
		if ($ob['meta']['is_formentry'] && $ob['meta']['dir_is_form_result'])
		{
			// kuidas ma saan yah menüü kui menüü lingid on kõik otsinguga leitud?
		}
		else
		{
			while((($ak == $ob['meta']['lingiroot']))==false)
			{
				$ph = $this->get_object(array('oid' => $ak,'return' => ARR_ALL),false,false);
				//$tase++;
				$YAH[++$tase] = array(
					'caption' => $ph['name'],
					'link' => $this->mk_my_orb('show',array('cd' => $ak,'id' => $id))
				);
				$ak = $ph['parent'];
			};
		}

		$YAH[++$tase]= array( //taseme number on igaljuhul vajalik siit kätte saada
			'caption' => $ob['name'],
			'link' => $this->mk_my_orb('show',array('cd' => $ob['meta']['lingiroot'],'id' => $id))
		);





		if (!is_array($ob['meta']['dir'][$tase]))
		{
			$tase=0; //käiku lähevad default taseme määrangud
		}
		$this_dir=$ob['meta']['dir'][$tase];
		$this_link=$ob['meta']['link'][$tase];
		$this_yahi=$ob['meta']['yahi'][$tase];

		$templiit = $this_dir['level_template'];
		$this->read_template("show/$templiit");
		$order_dirs = $this_dir['sortby_dirs'];
		$order_links = $this_dir['sortby_links'];

		// kui  kasutame vormisisestust
		if ($ob['meta']['is_formentry'])
		{
			//kataloogi lingi väärtus võetakse vormisissestusest
			$form = get_instance('formgen/form');
			$form->load($ob['meta']['forms']);
			$form->set_element_value($ob['meta']['felement'], urldecode($search));
			if ($ob['meta']['dir_is_form_result'])
			{
				// leitud id järgi, kõik objektid
				$arr = new aw_array($form->search());
				foreach($arr->get() as $val)
				{
					$menus[$val] = $this->get_object($val);
				}
				//linke ei pane
			}
			else
			{
				$menus = $this->list_objects(array('class' => CL_PSEUDO,
					'parent' => $cd,
					'active' => $ob['meta']['active_dirs'],
					'orderby' => $order_dirs,
					'return' => ARR_ALL
				));
				//linkide asemel on vormi väljastus
				$links = $form->new_do_search(array('output_id'=>2));//$ob['meta']['form_output_is']));
			}
		}

		//tavaline lingikogu
		if (!$ob['meta']['is_formentry'])
		{
		// menüüd on 'füüsilised' kataloogid
			$menus = $this->list_objects(array('class' => CL_PSEUDO,
				'parent' => $cd,
				'active' => $ob['meta']['active_dirs'],
				'orderby' => $order_dirs,
				'return' => ARR_ALL
			));
		//lingid on aktiivses kataloogis olevad lingiobjektid
			if($this_dir['show_links'])
			{

				$objects = $this->list_objects(array(
					'class' =>  CL_EXTLINK,
					'parent' => $cd,
					'active' => $ob['meta']['active_links'],
					'orderby' => $order_links,
					'return' => ARR_ALL,
				));
			}
		}

		//kui menüüsid on siis parsime tulpadesse
		if ($menus)
		{
			$tulbad=$this->menus($menus,$this_dir,$id,$ob['meta']['felement']?$ob['meta']['vordle']:'');
		}

		//kui tahame linke
		if ($objects)
		{
			$links=$this->oooo($objects,$this_link,$this_dir,$id,'objects');
		}

////////////
		if ($ob['meta']['YAH']) 		//if YAH then parse it
		{
			$YEP=$this->oooo(array_reverse($YAH),$this_yahi,$this_dir,$id,'yahi');
		}

		$this->vars(array(
			'css' => $this->css,
			'abix' => $tase,
			'YAHBAR' => $YEP,
			'total' => (int)$total,
			'total2' => (int)$total2,
			'name' => $ob['name'],
			'comment' => $ob['comment'],
			'cd' => $cd,
			'tulp' => $tulbad,
			'links' => $links
		));
		return $this->parse().'whee';
	}



	function oooo($objects,$this_link,$this_dir,$id,$T)
	{
		$total2=0;
		$ll = get_instance('extlinks');
		//makes css for link objects
		$css=$this->mk_link_css($this_link);
		$this->css.=$css;
		//makes template for link objects
		$link_tpl=$this->mk_link_obj_template($this_link);
		// localparse
		foreach($objects as $key => $val)
		{
			extract($val); //link properties
			$total2++;
			list($url,$target,$caption) = $ll->draw_link($key);
			$target=$this_dir['newwindow']?'target=_blank':'';
			$link=array(
				'caption' => $val['caption'],
				'comment' => $comment,
				'target' => $target,
				'modified' => $modified,
				'modifiedby' => $modifiedby,
				'createdby' => $createdby,
				'modified' => $modified,
				'created' => $created,
				'jrk' => $jrk,
				'plain_url' => $url,
				'url' => $url,
			);


			if ($T == 'yahi')
			{
				$link+=array(
					'link' => $val['link'],//?$val['link']:$this->mk_my_orb('goto',array('id'  => $oid, 'lkid'=>$id),''),
					'hits' => $this->get_hit($key),
				);
				$items[$tlp].= localparse($link_tpl,$link); //parse links
			}
			else
			{
				$link+=array(
					'link' => $this->mk_my_orb('goto',array('id'  => $oid, 'lkid'=>$id),''),
					'hits' => $this->get_hit($key),
					'caption' => $val['caption'],
				);
				$tlp=$jrk?$jrk:((3%$t++)+1);
				$items[$tlp].= localparse($link_tpl,$link); //parse links

			}

			if ($tulpa=1)
			{
				$links=$this->tulpadesse($items,array('tulpi'=>2, 'jrk_columns'=>1),$total2);
			}
			else
			{
				$links=implode('',$items);
			}

		}
		return $links;
	}

	////
	// !menüü andmete tulpadesse jagamine
	//  menus - menüü objectid
	//  ob - tulpade confi andmed
	//  optional:
	//	$search - millise parameetri järgi otsime //name, oid, ...
	function menus($menus,$conf,$id,$search='')
	{
		foreach($menus as $key => $value)
		{
			extract($value);
			//leiame alammenüüs olevate objektidearvu // praegu leitakse alamenüüde arv, aga vaest oleks mõttekas leida (ka) linkide arv
			$sub_count = $this->count_objects(array(
				'class' => CL_PSEUDO,
				'parent' => $oid,
			));
			if ($sub_count)
			{
				$this->vars(array('count'=>$sub_count));
				$subs=$this->parse('sub_count');
			}
			$this->vars(array(
				'hits' => $this->get_hit($oid),
				'sub_count' => $subs,
				'name' => $name,
				'link' => $this->mk_my_orb(
					'show',
					array(
						'cd' => $oid,
						'id'  => $id,
						'search' => urlencode($value[$search]),
					)
				)
			));

			$tlp=$value['jrk']?$value['jrk']:(($conf['tulpi']%$t++)+1);
			$items[$tlp].= $this->parse('dir');
			$total++;
		}//foreach

		return $this->tulpadesse($items,$conf,$total);
	}






	////
	// !make css for link object
	// conf - level conf
	//
	function mk_link_css($conf)
	{
		if(!is_array($conf))
		{
			return false;
		}
		$s = get_instance('css');
		foreach($conf as $key => $val)
		{
			if ($val['style'] && !$css[$val['style']])
			{//echo $val['style'];
				$style = $this->get_object($val['style']);
				$css[$val['style']] = $s->_gen_css_style('style'.$style['oid'],$style['meta']['css']);
			}
		}
		return $css?("<style>\n".implode('',$css).'</style>'):'';
	}




	////
	// !make local template for link object
	// conf - level conf
	/* array(   [item] => Array
		        (
		            [jrk] => 2
		            [show] => 1
		            [style] => 0
		            [br] => 1
			    ...
		        )
			...
		)
		*/
	function mk_link_obj_template($conf)
	{
//print_r($conf);
		if(is_array($conf))
		{
			foreach($conf as $key => $val)
			{
				if (is_array($val) && $val['show']){
					$val['br']=$val['br']?'<br />':'';
					$class=$val['style']?"class=\"style".$val["style"]."\"":'';

					if ($val['hyper']){
						$linktpl[(int)$val['jrk']].="<A $class HREF=\"{VAR:link}\" onMouseover=\"window.status='{VAR:url}'; return true\" {VAR:target}>{VAR:".$key."}".$val["text"]."</A>".$val["br"]."\n";
					}
					elseif ($val['style'])
					{
						$linktpl[(int)$val['jrk']].="<span $class>{VAR:".$key."}".$val["text"]."</span>".$val['br']."\n";
					}
					else
					{
						$linktpl[(int)$val['jrk']].='{VAR:'.$key.'}'.$val['text'].$val['br']."\n";
					}
				}
			};
		}
		@ksort($linktpl);
		return @implode('',$linktpl);
	}




	////
	// !noh, jaotab tulpadesse või nii
	// items - array of links or whatever
	// conf=array("tulpi'=>3, 'jrk_colomns'=>1|0)  //tulpade arv, kas jaotame itemid key järgi tulpa või nii nagu datat tuleb
	// total - linkide arv, võrdselt jagamise jaoks
	function tulpadesse($items,$conf,$total)
	{
		if (is_array($conf))
			{extract($conf);}

		$jrku=!$tulpi?$jrk_columns:($jrk_columns?1:''); //ühesõnaga, kui tulpi confis üldse kirjas pole siis paneme ühte tulpa

		foreach ($items as $key => $val)
		{
			if($jrku)  //see on see jagamine tulpadesse jrk järgi
			{
				$kk=(string)$key;
				$tulp=$kk[0];
				if($tulpi<$tulp) $tulp = 1; //kui jrk algab suurema numbriga kui tulpade arv, siis lheb esimesse
			}
			else
			{
				$tulp = ($total % $tulpi)+1; //siin peaks kuidagi võrdselt ära jagama
			}

			$tulp = $tulp?$tulp:1;
			$tasand[$tulp].= $val;
		}

		ksort($tasand);
		foreach ($tasand as $val)	//parsime tulbad
		{
			$this->vars(array(
				'dir' => $val,//implode('',$val)
			));
			$tulbad.= $this->parse('tulp');
		}
		return $tulbad;
	}

	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}

	////
	// !this adds a hit to the external link (and possibly some information about user) and redirects user to the url,
	// browser's back button does not return to this page :)
	//
	function link_redirect($arr)
	{
		extract($arr); //id = link id
		$uid= aw_global_get('uid');
		$this->write_stat(array(
			'oid'=>$id,
			'lkid'=>$lkid,
			'uid'=>$uid,
			'action'=>2
		));
		$ob = $this->get_object($id);
		$this->add_hit($id);
		$ll = get_instance('extlinks');
		list($url,$target,$caption) = $ll->draw_link($id);
//		echo $uid.' ';
		header("Location: $url");
		die();
	}


	function write_stat($arr)
	{
		extract($arr);
		$now=time();
		$in = "insert into lingikogu_stat (oid, lkid, uid, action,tm) values ('$oid','$lkid','$uid','$action',$now)";
		$this->db_query($in);
	}

}
?>
