<?php
/*

@classinfo relationmgr=yes

@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

@default field=meta
@default method=serialize

@property datadir type=relpicker reltype=DATADIR
@caption Peamenüü asukoht (soovitatavalt kodukataloogis)

@property launchbar type=relpicker reltype=LAUNCHBAR
@caption Kiirmenüü

@property newobjects type=relpicker reltype=NEWOBJECTS
@caption Uute objektide kaust

@property desktopobjects type=relpicker reltype=DESKTOPOBJECTS
@caption Tausta objektide kataloog

@property backgroundcolor type=colorpicker
@caption Taustavärv

@property backgroundimage type=relpicker reltype=BACKGROUNDIMAGE
@caption Taustapilt

@property backgroundtextcolor type=colorpicker
@caption Tausta tekstivärv

//@property showclock type=checkbox value=1 ch_value=0
//@caption Näita kella

@property clockstyle type=checkbox value=12 ch_value=12
@caption 12 tunnine kell

@property windowsW type=textbox size=4
@caption Avatava akna vaikimisi laius pixelites (vaikimisi 600)

@property windowsH type=textbox size=4
@caption kõrgus (vaikimisi 500)

@property calendar type=relpicker reltype=CALENDAR
@caption Kalender

@property backgroundtype type=select
@caption Tausta pilt on ...

//group=general,activedesktop

@property startdesktop type=text
@caption Käivita

@groupinfo activedesktop caption=Aktivedesktop
@default group=activedesktop

@property activedesktop_on type=checkbox value=1 ch_value=1
@caption aktiivne

@property activedesktop_url type=textbox
@caption Tausta lehekülg

@property AC_top type=textbox size=4
@caption top
@property AC_left type=textbox size=4
@caption left
@property AC_width type=textbox size=4
@caption laius
@property AC_height type=textbox size=4
@caption kõrgus

@property activedesktop_href type=checkbox value=1 ch_value=1
@caption Lingid on klikitavad



*/

define('BACKGROUNDIMAGE', 1);
define('DATADIR', 2);
define('LAUNCHBAR', 3);
define('NEWOBJECTS', 4);
define('CALENDAR', 5);
define('DESKTOPOBJECTS', 6);

class desktop extends class_base
{
	function desktop()
	{
		$this->init(array(
			'tpldir' => 'desktop',
			'clid' => CL_DESKTOP
		));
	}

	function callback_get_rel_types()
	{
		return array(
			BACKGROUNDIMAGE => 'Tausta pilt',
			DATADIR => 'Juurkaust',
			LAUNCHBAR => 'Kiirmenüü',
			NEWOBJECTS => 'Uute objektide kaust',
			CALENDAR => 'Kalender',
			DESKTOPOBJECTS => 'Taustal olevad objektid',
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		$retval = false;
                switch($args['reltype'])
                {
			case BACKGROUNDIMAGE:
				$retval = array(CL_IMAGE);
			break;
			case DATADIR:
				$retval = array(CL_PSEUDO);
			break;
			case LAUNCHBAR:
				$retval = array(CL_PSEUDO);
			break;
			case NEWOBJECTS:
				$retval = array(CL_PSEUDO);
			break;
			case DESKTOPOBJECTS:
				$retval = array(CL_PSEUDO);
			break;
			case CALENDAR:
				$retval = array(CL_PLANNER);
			break;
		};
		return $retval;
	}

	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = PROP_OK;

		switch($data['name'])
		{
			case 'startdesktop':

			$scr = "<script>
function pop(url,w,h)
{
	//prop='width=' + w + ',height=' + h + ',status=yes,scrollbars=no,toolbar=no,menubar=no,resizable=yes';
	prop = 'fullscreen';
	window.open(url,'pilt',prop);
	return false;
}
</script>";

			$link = $scr.html::button(array(
				'onclick' => "pop('".$this->mk_my_orb('show_desktop', array('id' => $args['obj']['oid']))."','700', '600');return false;",
				'value' => 'Käivita AW desktop',
			));

				$data['value'] = $link;
			break;

			case 'backgroundtype':
				$data['options'] = array(
					'repeat' => 'korduv',
					'center' => 'keskel',
//					'stretsh' => 'venitatud',
				);
			break;
			/*
			case '':
				$data[''] = ;
			break;
			/*case '':
				$data[''] = ;
			break;
			case '':
				$data[''] = ;
			break;
			case '':
				$data[''] = ;
			break;*/
		}
		return $retval;

	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$form = &$args["form_data"];
		$meta =  &$args['obj']['meta'];
		$retval = PROP_OK;

		switch($data['name'])
		{
			case 'windowsW':
				$data['value'] = between((int)$form[$data['name']],200, 900, (int)$form[$data['name']], 600);
			break;
			case 'windowsH':
				$data['value'] = between((int)$form[$data['name']],100, 800, (int)$form[$data['name']], 500);
			break;
			case 'AC_top':
				$data['value'] = between((int)$form[$data['name']],0, 1000, (int)$form[$data['name']], 50);
			break;
			case 'AC_left':
				$data['value'] = between((int)$form[$data['name']],0, 1200, (int)$form[$data['name']], 50);
			break;
			case 'AC_height':
				$data['value'] = between((int)$form[$data['name']],20, 1000, (int)$form[$data['name']], 400);
			break;
			case 'AC_width':
				$data['value'] = between((int)$form[$data['name']],50, 1200, (int)$form[$data['name']], 500);
			break;
		};

		return $retval;
	}
	


	function show_icons()
	{

		$path = '/www/dev/axel/automatweb_dev/automatweb/images/';
		$uri = $this->cfg['baseurl'].'/automatweb/images/';
		$paths = array(
			'',
			'/icons/',
			'/blue/',
			'/blue/awicons/',
		);

		foreach($paths as $folder)
		{
			$arr = $this->get_directory(array('dir' => $path.$folder));
			echo '<b>'.$path.$folder.'<br>';
			echo $uri.$folder.'</b><br>';

			foreach($arr as $key => $val)
			echo '<img src='.$uri.$folder.$val.' /> '.$val.' <br />';

		}
		die;

	}


	function show_desktop($args = array())
	{


		$ob = $this->get_object($args['id']);
		$current_layout = $this->get_object_metadata(array(OID => $args['id'],'key' => 'desktop_layout'));

		$this->read_template('desktop.tpl');

		if ($ob['meta']['backgroundimage'])
		{
			$img = get_instance('image');

			$bg = $img->get_image_by_id($ob['meta']['backgroundimage']);

			$this->vars(array(
				'bgimage' => $bg['url'],
			));
			$backgroundimage = $this->parse('backgroundimage');
		}

		$this->menu = '';
		$this->newobjects = $ob['meta']['newobjects'];
		$classes = $this->cfg['classes'];
		$this->xy = $ob['meta']['windowsW'].','.$ob['meta']['windowsH'];

		$this->vars(array(
			'xy' => $this->xy,
			'icons_path' => $this->cfg['baseurl'].'/automatweb/images/icons',
			'images_path' => $this->cfg['baseurl'].'/automatweb/images',
			'transgif' => $this->cfg['baseurl'].'/automatweb/images/trans.gif',
		));

		
			// ----------- keelevalik
			$l = get_instance('languages');
			$langs = $l->get_list(array('all_data' => 1));



			$lc = aw_global_get('admin_lang_lc');


			foreach($langs as $val)
			{
				if($val['acceptlang'] == $lc)
				{
					$this->vars(array(
						'active_acceptlang' => $val['acceptlang'],
						'active_lang' => $val['name'],
					));
				}
				$req_uri = preg_replace('/&set_lang_id=[0-9]+/','',aw_global_get('REQUEST_URI'));

				$this->vars(array(
					//'icon' => '<IMG SRC="'.$this->cfg['baseurl'].'/automatweb/images/trans.gif" WIDTH="1" HEIGHT="7" BORDER=0 ALT="" />',
					'caption' => $val['name'],
					'title' => $val['acceptlang'].' '.$val['charset'] ,
					'url' => $req_uri.'&set_lang_id='.$val['id'],
				));
				$menucontent .= $this->parse('MENU_ITEM_lang');
			}
			$this->vars(array('name' => 'filemenu_lang', 'content' => $menucontent));
			$this->menu .= $this->parse('MENU');


		$cnt += 1;
		$this->levelcontent[1]['items'].= $this->parse('RUN_MENU_ITEM');

		//main menu
		if ($ob['meta']['datadir'])
		{


			//$ico = icons::get_icon_url(CL_PSEUDO,'');
			//$icon = '<img src="'.$ico.'" border="0" height="16">';


			// ----------- lisalingid startmenüüs
/*
			$this->vars(array(
				'icon' => $icon,
				'caption' => 'programmid',
				'sub_menu_id' => 'filemenu'.'programs',
			));
			$this->levelcontent[1]['items'] = $this->parse('MENU_ITEM_SUB');
*/
/*			$cnt += 1;
			$this->vars(array(
				'caption' => 'Google',
				'url' => 'http://www.google.com/',
				'title' => '',
				'clid' => '1',
			));
			$this->levelcontent[1]['items'].= $this->parse('MENU_ITEM');
*/
/*
			// ----------- programmid
			$this->menu .= '<div id="filemenu'.'programs'.'" class="menu"  style="z-index:1" onmouseover="menuMouseover(event)">'."\n";
			$cnt += 1;

			foreach($this->cfg['programs'] as $val)
			{
				if (isset($val['name']) && isset($val['url']))
				{
					$this->vars(array(
						'icon' => $icon,
						'caption' => $val['name'],
						'url' => $val['url'],
					));
					$this->menu .= $this->parse('MENU_ITEM');
				}
			}
			$this->menu .= '</div>'."\n\n";
*/
			$this->genmenu(array($ob['meta']['datadir']),1);

		}
		else
		{
			$this->menu = 'peamenüü kaust määramata';
		}




		// ----------- kiirvaliku nupud
		if ($ob['meta']['launchbar'])
		{
//			$arr = $this->db_fetch_array('select name, parent, '.OID.', class_id from objects where class_id='.CL_OBJECT_TYPE.' parent = '.$ob['meta']['launchbar'].' order by jrk');
			$arr = $this->get_objects_below(array('parent' => $ob['meta']['launchbar'], 'class' => CL_OBJECT_TYPE, 'orderby' => 'jrk', 'ret' => ARR_ALL));
			
			//!!! KEELT PEAKS KA ÄKKI AREVSTAMA?!

			$this->vars(array(
				'add_object_type' => $this->mk_my_orb('new', array('parent' => $ob['meta']['launchbar']),'object_type'),
			));
			
			$launchercontexts = '';
			$launchbar = '';

			if (count($arr)>0)
			{

				foreach($arr as $val)
				{
					$val['meta'] = aw_unserialize($val['metadata']);
					$type = $val['meta']['type'];

					$cldat = $classes[$val['meta']['type']];

					if ($cldat['alias_class'])
					{
						$cldat['file'] = $cldat['alias_class'];
						$classes[$clid]['file'] = $cldat['alias_class'];
					}

					//if (!isset($this->icons[$type]))
					//{
					//	$this->icons[$type] = icons::get_icon_url($type,$val["name"]);
					//}
					$val['change_object_type'] = $this->mk_my_orb('change', array('id' => $val['oid']),'object_type');
					$val['delete_object_type'] = $this->mk_my_orb('dodelete', array('id' => $val['oid'], 'class_id' => 'object_type'));
					//$val['icon'] = $this->icons[$type];
					$val['url'] = $this->mk_my_orb('new', array('parent' => $ob['meta']['newobjects']),$cldat['file']);
					$val['title'] = 'Lisa uus '.$cldat['name'];
					$val['clid'] = $type;
					$this->vars($val);

					$launchercontexts .= $this->parse('LAUNCHERCONTEXTS');
					$launchbar .= $this->parse('LAUNCHER');
				}
			}
			else
			{
					$showlaunche = '$';
			}



		}



		if ($ob['meta']['clockstyle'] == '12')
		{
			$usdate = true;
		}


		//if ($ob['meta']['calendar'])
		//{

			//$this->vars(array('calendar_url' => $this->mk_my_orb("change", array('id' => $ob['meta']['calendar']),'planner')));
			//$calendar = $this->parse('CALENDAR_BUTTON');
		//}


		switch($ob['meta']['backgroundtype'])
		{
			case 'repeat':
				$bgstyle = 'background-repeat: repeat';
			break;
			case 'center':
				$bgstyle = 'background-repeat: no-repeat;
				background-position: center;';
			break;
			default:
				$bgstyle = 'background-repeat: no-repeat;
				background-position: center;';
		}

		// ----------- tausta objektid, ikoonid
		$desktop_items = '';
		if ($ob['meta']['desktopobjects'])
		{
			$arr = $this->get_objects_below(array('parent' => $ob['meta']['desktopobjects'], 'orderby' => 'jrk', 'ret' => ARR_ALL));
			if (count($arr)>0)
			{
				foreach($arr as $val)
				{
					$val['meta'] = aw_unserialize($val['metadata']);

					$cldat = $classes[$val['class_id']];

					if ($cldat['alias_class'])
					{
						$cldat['file'] = $cldat['alias_class'];
						$classes[$clid]['file'] = $cldat['alias_class'];
					}

					//if (!isset($this->icons[$val["class_id"]]))
					//{
					//	$this->icons[$val["class_id"]] = icons::get_icon_url($val["class_id"],$val["name"]);
					//}
					//$val['icon'] = $this->icons[$val["class_id"]];


					$context_items = array();

					$this->vars($val);

					if ($val['class_id'] == CL_PSEUDO)
					{
						$context_items['open'] = array(
							'title' => '<b>Ava puuta kaust</b>',
							'caption' => 'Ava',
							'url' => $this->mk_my_orb('right_frame', array('parent' => $val[OID]),'admin_menus'),
							'wxy' => $this->xy,
							'default' => true,
							'iconfile' => 'class_1.gif',
						);
						$context_items['explore'] = array(
							'title' => 'Ava puuga kaust',
							'caption' => 'Ava puuga',
							'url' => $this->cfg['baseurl'].'/automatweb/index.aw?parent='.$val['oid'],
							'wxy' => $this->xy,
							'iconfile' => 'class_1.gif',
						);

						$val['change_url'] = $this->mk_my_orb('change', array('id' => $val['oid']),'menu');
					}
					else
					{
						$val['change_url'] = $this->mk_my_orb('change', array('id' => $val['oid']),$cldat['file']);

						$context_items['view'] = array(
							'title' => '<b>Vaata</b>',
							'caption' => 'Vaata',
							'url' => $this->mk_my_orb('view', array('id' => $val['oid']),$cldat['file']),
							'wxy' => $this->xy,
							'default' => true,
							'iconfile' => 'class_6.gif',
						);
					}

					$context_items['change'] = array(
						'title' => 'Muuda',
						'caption' => 'Muuda',
						'url' => $val['change_url'],
						'wxy' => $this->xy,
						'iconfile' => 'small_settings.gif',
					);

					$context_items['delete'] = array(
						'title' => 'Kustuta',
						'caption' => 'Kustuta',
						'url' => $this->mk_my_orb('dodelete', array('id' => $val['oid'], 'class_id' => $val['class_id'])),
						'iconfile' => 'small_delete.gif',
						'tpl' => 'ICON_CONTEXT_ITEM2',
					);

					$icon_context_items =  '';

					foreach($context_items as $cval)
					{
						if (isset($cval['default']))
						{
							$cval['caption'] = '<b>'.$cval['caption'].'</b>';
							$val['default_url'] = $cval['url'];
						}
						$this->vars($cval);
						$icon_context_items .= $this->parse(isset($cval['tpl']) ? $cval['tpl'] : 'ICON_CONTEXT_ITEM');
					}


					//vaatame kas ikoonil on salvestatud asukoht
					if (isset($current_layout['I']['dra'.$val[OID]]))
					{
						if ($current_layout['I']['dra'.$val[OID]]['left'])
						{
							$val['POS'] = 'position:absolute;left:'.$current_layout['I']['dra'.$val[OID]]['left'].';top:'.$current_layout['I']['dra'.$val[OID]]['top'].';z-index:'.$current_layout['I']['dra'.$val[OID]]['z'];
						}
						else
						{
							$val['POS'] = 'float:left;z-index:3;';
						}
					}
					else
					{
						$val['POS'] = 'float:left;z-index:1;';
					}

					$val['ICON_CONTEXT_ITEM'] = $icon_context_items;

					$val['title'] = $val['comment'] ? ($val['name'].' - '.$val['comment']) : $val['name'];
					$val['icon_caption'] = wordwrap( $val['name'], 15, '<br />');
					$val['clid'] = $val['class_id'];

					$this->vars($val);

					$desktop_items .= $this->parse('DESKTOP_ITEM');
				}
			}
		}

		$OPENSAVEDWINDOWS = '';$OPENSAVEDWINDOWS2 = '';
		if (is_array($current_layout['W']))
		{
			$wcnt = 0;
			foreach($current_layout['W']  as $key => $val)
			{
				$this->vars(array(
					'url' => $current_layout['WS']['i'.$key]['src'],
					'caption' => $current_layout['WC']['wc'.$key]['capt'],
					'icon' => $current_layout['WI']['ic'.$key]['icon'],
					'z' => $val['z'],
					'left' => $val['left'],
					'top' => $val['top'],
					'cnt' => ++$wcnt,
				));
				$OPENSAVEDWINDOWS .= $this->parse('OPENSAVEDWINDOWS');
				$OPENSAVEDWINDOWS2 .= $this->parse('OPENSAVEDWINDOWS2');
			//break;
			}

		}

		
		// RUN programs
		$RUNPROGRAMS = '';
		
		foreach($classes as $clid => $cldat)
		{
			if (isset($cldat['alias']))
			{
				if ($cldat['alias_class'])
				{
					$cldat['file'] = $cldat['alias_class'];
					$classes[$clid]['file'] = $cldat['alias_class'];
				}
				$this->vars(array(
					'prg_file' => basename($cldat['file']),
					'class_id' => $clid,
				));
				$RUNPROGRAMS .= $this->parse('RUNPROGRAMS');
			}
		}


		//activedesktop
		$activedesktop = '';
		if (isset($ob['meta']['activedesktop_on']) && ($ob['meta']['activedesktop_on'] == '1'))
		{
			$this->vars(array(
				'url' => $ob['meta']['activedesktop_url'] ? $ob['meta']['activedesktop_url'] : 'http://www.neti.ee',
				'width' => $ob['meta']['AC_width'],
				'top' => $ob['meta']['AC_top'],
				'height' => $ob['meta']['AC_height'],
				'left' => $ob['meta']['AC_left'],
			));
			$activedesktop = $this->parse('activedesktop');
		}


		$this->vars(array(
			'date' => date('d.').(defined($month = 'LC_M'.date('n')) ? constant($month) : date(' n')).date(' Y'),
			'desktop_change' => $this->mk_my_orb('change', array('id' => $args['id']), 'desktop'),
		));
		$this->vars(array(
			//'redirect' =>
			'activedesktop' => $activedesktop,
			'RUNPROGRAMS' => $RUNPROGRAMS,
			'OPENSAVEDWINDOWS2' => $OPENSAVEDWINDOWS2,
			'OPENSAVEDWINDOWS' => $OPENSAVEDWINDOWS,
			'new_link' => $this->mk_my_orb('new', array('parent' => $ob['meta']['desktopobjects']), 'xxx'),
			'showlaunche' => $showlaunche,
			'LAUNCHERCONTEXTS' => $launchercontexts,
			'add_folder' => $this->mk_my_orb('new', array('parent' => $ob['meta']['desktopobjects']), 'menu'),
			'add_object_type' => $this->mk_my_orb('new', array('parent' => $ob['meta']['launchbar']), 'object_type'),
			'pipe_url' => $this->mk_my_orb('pipe', array('id' => $ob['oid'])),
			//'showclock' => $ob['meta']['showclock'] ? 'true' : 'false',
			'showclock' => 'true',
			//'CLOCK' => $ob['meta']['showclock'] ? $this->parse('CLOCK') : '',
			'CLOCK' => $this->parse('CLOCK'),
			'refresh_url' => $this->mk_my_orb('redirect', array('url' => urlencode(aw_global_get('REQUEST_URI')))),
//'refresh_url' => aw_global_get("REQUEST_URI").'&plah=0',
			'REQUEST_URI' => aw_global_get('REQUEST_URI'),
			'minikal' => $ob['meta']['calendar'] ? $this->calender_navigator(array('id' => $ob['meta']['calendar'])) : $this->parse('NOCALENDER'),
			'bgstyle' => $bgstyle,
			'calendar' => $calendar,
			//'aw_icon' => $this->cfg['baseurl'].'/automatweb/images/aw_ikoon.gif',
			'DESKTOP_ITEM' => $desktop_items,
			'clockwidth' => 4 + ($usdate ? 4 : 0),
			'usdate' => (int)$usdate,
			//'active_acceptlang' => aw_global_get('admin_lang_lc'),
			'launchbar' => $launchbar,
			'datadir' => $ob['meta']['datadir'],
			'filemenufix' => ($this->cnt + $cnt) * 25,
			'langmenufix' => count($langs) * 20,
			'main_menu' => $this->menu,
			'backgroundimage' => $backgroundimage,
			'backgroundcolor' => $ob['meta']['backgroundcolor'] ? $ob['meta']['backgroundcolor']: '88ccee',
			'backgroundtextcolor' => $ob['meta']['backgroundtextcolor'],
			'name' => $ob['name'],

		));
		echo $this->parse();
		die;
	}


	//type str - return string part; int - return integer part, default - array('int' => integer, 'str' => string);
	function str_int($str, $type = 'both')
	{
		preg_match("/([[:alpha:]]*)([0-9]+)/", $str, $m);
		switch($type)
		{
			case 'int':
				return $m[2];
			break;
			case 'str':
				return $m[1];
			break;
			case 'both':
				return array('str' => $m[1], 'int' => $m[2]);
			break;
		}
		return false;
	}


	function pipe($args)
	{
//		if (!isset($args['element'])) return die('*');

		$str='';

		if (isset($args['reorder']))
		{
			$current_layout = $this->get_object_metadata(array(OID => $args['id'],'key' => 'desktop_layout'));
			$i=0;
			$j=0;
			foreach($current_layout['I'] as $key => $val)
			{
				$current_layout['I'][$key]['top'] = $newt = (($i * 76) . 'px');
				$current_layout['I'][$key]['left'] = $newl =(($j * 76) . 'px');

				$str.="parent.document.getElementById('".$key."').style.left='".$newl."';\n";
				$str.="parent.document.getElementById('".$key."').style.top='".$newt."';\n";

				$i++;
				if ($i >= 3)
				{
					$i = 0;
					$j++;
				}
			}


			//$current_layout['I'] = array();


			$this->set_object_metadata(array(OID => $args['id'], 'key' => 'desktop_layout', 'value' => $current_layout));
			//die('reordered');
		}

		elseif (isset($args['I']))
		{
//		arr($args,1);

			$current_layout = $this->get_object_metadata(array(OID => $args['id'],'key' => 'desktop_layout'));

			$strint = $this->str_int($args['element'],'both');

			$el = $strint['str'];
			$oid = $strint['int'];
			$top = (int)$args['top'];
			$left = (int)$args['left'];



			$current_layout = array(
				'I' => $args['I'],
				'W' => $args['W'],
				'WC' => $args['WC'],
				'WS' => $args['WS'],
				'WI' => $args['WI'],
			);//array('top' => (int)$args['top'], 'left' => (int)$args['left']);

			//arr($current_layout);

			$this->set_object_metadata(array(OID => $args['id'], 'key' => 'desktop_layout', 'value' => $current_layout));
		}

		//set metadata ('key' => $args['element'], 'value' => array('top' => $args['top'],'left' => $args['left'],));

	//		echo 'left: '.$args['left'].' top: '.$args['top'].' element: '.$args['element'],'integer';


		echo '<html>
		<head>
		<title>wehee</title>
		</head>
		<body>
		<script>
		'.$str.'
		</script>
		<span id="activity" style="color:red;postition:absolute;" ></span>
		</body></html>';


		die;
	}

	function dodelete($args)
	{

		echo "<html><head><title>objekti Kustutamine</title></head>
		<body>
		kustutamine...";
		flush();

		$str = '';
		//arr($args,1);
		if ($this->can('delete', $args['id']))
		{
			if (($args['class_id'] != CL_PSEUDO) &&   $this->cfg['classes'][$args['class_id']]['file'] != '')
			{
				$inst = get_instance($this->cfg['classes'][$args['class_id']]['alias_class'] != '' ? $this->cfg['classes'][$row['class_id']]['alias_class'] : $this->cfg['classes'][$args['class_id']]['file']);
				if (method_exists($inst, 'delete_hook'))
				{
					$inst->delete_hook(array('oid' => $args['id']));
				}
			}
			$this->delete_object($args['id']);

			$str="parent.document.getElementById('dra".$args['id']."').style.visibility='hidden';\n";
		}
		else
		{
			$str = "alert('Ei saanud kustutada, puuduvad õigused!');\n";
		}

		echo <<<DEL
		<script>
		$str
		</script>
		<body>
		<span id="activity" style="color:red;postition:absolute;" ></span>
		$txt
		</body>
		</html>
DEL;

die;
	}



	function calender_navigator($args = array())
	{
		$_cal = get_instance('calendar',array('tpldir' => 'planner'));

//		if ($args['id'])
		{
			$this->day_orb_link = $this->mk_my_orb('change',array(
				'id' => $args['id'],
				'group' => 'show_day',
				'type' => 'day',
				'ctrl' => $ctrl,
				'section' => aw_global_get('section')
				),
			'planner');
		}

			//$this->day_orb_link = NULL;
/*

			$fc = get_instance("formgen/form_calendar");
			$events = $fc->get_events(array(
				"eid" => $args['id'],
				"start" => $di["start"],
				"end" => $di["end"],
				//"eform" => $vac_cont,
				//"ctrl" => $ctrl,
			));
*/

		classload('date_calc');
		$di = get_date_range(array(
			'date' => date('d-m-Y'),
			'type' => 'day',
			'direction' => 1,
		));

		$di['start'] = time();


		list($_thismon,$_thisyear) = explode('-',date('m-Y',$di['start']));

		for ($i = 0;$i<3;$i++)
		{
			$_nextmon = mktime(0,0,0,$_thismon + $i,1,$_thisyear);
			$_nm = date('m',$_nextmon);
			$y = $_thisyear;

			$navi1 .= $_cal->draw_calendar(array(
				'tm' => $_nextmon,
				'caption' => get_lc_month((int)$_nm) . " $y",
				'width' => 7,
				'type' => 'month',
				'day_orb_link' => $this->day_orb_link,
				'marked' => $events,
				'more' => //'target="_blank"',
				"onclick='pop(\"{VAR:url}\",".$this->xy.",\"Kalender\", \"59\"); return false;'",
			));
		}
		return $navi1;
	}


		function genmenu($parents, $level=false)
		{

			foreach($parents as $parent)
			{
//				$arr = $this->db_fetch_array('select name, parent, '.OID.', class_id, metadata from objects where status=1 and parent = '.$parent.' order by jrk,name');

				if ($parent)
				{
					$arr = $this->get_objects_below(array(
						'parent' => $parent,
						'orderby' => 'jrk',
						'ret' => ARR_ALL,
					));
				}
				else
				{
					$arr = array();
				}
				$items = '';
				if (count($arr) > 0)
				{
					$subs = array();
					//$this->menu .= '<div id="filemenu'.$parent.'" class="menu" onmouseover="menuMouseover(event)">'."\n";

					foreach($arr as $key => $val)
					{
						$val['meta'] = aw_unserialize($val['metadata']);

						if ($val['class_id'] == CL_OBJECT_TYPE)
						{
							$type = $val['meta']['type'];
						}
						else
						{
							$type = $val['class_id'];
						}

						$cldat = $this->cfg['classes'][$type];

						if ($cldat['alias_class'])
						{
							$cldat['file'] = $cldat['alias_class'];
						}

						//if (!isset($this->icons[$type]))
						//{
						//	$this->icons[$type] = icons::get_icon_url($type,$val["name"]);
						//}


						//$icon = '<IMG SRC="'.$this->icons[$type].'" WIDTH="16" HEIGHT="16" BORDER=0 ALT="" />';


						switch($val['class_id'])
						{
							case CL_PSEUDO:
								$this->vars(array(
									//'icon' => $icon,
									'caption' => $val['name'],
									'sub_menu_id' => 'filemenu'.$val[OID],
									'title' => '',
									'clid' => $type,
								));
								$subs[] = $val[OID];
								$items .= $this->parse('MENU_ITEM_SUB');
							break;
							case CL_OBJECT_TYPE:
								$this->vars(array(
									//'icon' => $icon,
									'caption' => $val['name'],
									'url' => $this->mk_my_orb('new', array('parent' => $this->newobjects),$this->cfg['classes'][$type]['file']),
									'title' => '',
									'clid' => $type,
								));
								$items .= $this->parse('MENU_ITEM');
							break;

							default:
								$this->vars(array(
									//'icon' => $icon,
									'caption' => $val['name'],
									'url' => $this->mk_my_orb('change', array('id' => $val[OID]),$this->cfg['classes'][$type]['file']),
									'title' => '',
									'clid' => $type,
								));
								$items .= $this->parse('MENU_ITEM');
						}

					}

					if (isset($this->levelcontent[$level]))
					{
						$items .= $this->levelcontent[$level]['items'];
					}

					$this->vars(array(
						'name' => 'filemenu'.$parent,
						'content' => $items,
					));

					$this->menu .= $this->parse('MENU');

					if (count($subs) > 0)
					{
						$this->genmenu($subs);
					}
				}
				else
				{
					/*if (isset($this->levelcontent[$level]))
					{
						$items = $this->levelcontent[$level]['items'];
					}*/

					$this->vars(array(
						'name' => 'filemenu'.$parent,
						'content' => $items.'<a class="menuItem">tühi</a>',
					));
					$this->menu .= $this->parse('MENU');
				}


				//if (!isset($this->cnt))
				{
					$this->cnt = count($arr);
					//echo $this->cnt;
				}


			}
		}






	/*			$this->vars(array(
					'url' => '#',
					'caption' => $obj['name'],
				));

				 $menu_items = $this->parse('MENU_ITEM');*/
	// parent(int) - millisest nodest alustame?
	// class(int) - milline klass meid huvitab?
	// type(int) - kui tegemist on menüüga, siis loetakse sisse ainult seda tüüpi menüüd.
	// status - only objects of this status are returned if set
	// orderby(string) - millise välja järgi tulemus järjestada?
	// full(bool) - if true, also recurses to subfolders
	// ret(int) - ARR_NAME or ARR_ALL, default is ARR_ALL

//	'parent' => $ob['meta']['datadir'],
//	'full' => true,

	function get_objects_below($args = array())
	{
		extract($args);

		$groups = array();


		if (isset($full))
		{
			$this->get_objects_by_class($args + array('class' => CL_PSEUDO));
			while ($row = $this->db_next())
			{
				$ta = $args;
				$ta['parent'] = $row['oid'];
				$this->vars(array(
					'sub_menu_id' => 'fmenu'.$ta['parent'],
					'caption' => $row['name'],
				));

				$this->pars.=$this->parse('MENU_ITEM_SUB');

				$this->pars.= '<div id="fmenu'.$ta["parent"].'" class="menu">';
				$tg = $this->get_objects_below($ta);
				$this->pars.= '</div>';

				foreach($tg as $k => $v)
				{
					$groups[$k] = $v;
				}
			}
		}

		// just pass everything, hopefully wont break anything, but it does kill
		// a bunch of warnings
		$this->get_objects_by_class($args);

		while($row = $this->db_next())
		{
			$groups[$row['oid']] = $row;
			/*
			if (isset($ret) && $ret == ARR_NAME)
			{
				$groups[$row["oid"]] = $row["name"];
			}
			else
			{
				$row["meta"] = $this->get_object_metadata(array(
					"metadata" => $row["metadata"]
				));
				$groups[$row["oid"]] = $row;
			}
			*/

			$this->vars(array(
				'url' => '',
				'caption' => $row['name'],
			));

			$this->pars .= $this->parse('MENU_ITEM');

		};
		$this->vars(array('menu_id' => 'fmenu'.$ta['parent']));
		$this->pars .= $this->parse('MENU');
		return $groups;
	}


	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row['parent'] = $parent;
		unset($row['brother_of']);
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}

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
		return $this->show(array('id' => $alias['target']));
	}

	function redirect($args)
	{

echo '
<html>
<HEAD>
<title>refreshing</title>
<META HTTP-EQUIV="Refresh"
CONTENT="0;'.$args['url'].'">
</HEAD>
<body>refreshing....</body>
</html>';


		//header('Location:'. $args['url']);
		die;
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
/*	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		$this->read_template('show.tpl');

		$this->vars(array(
			'name' => $ob['name']
		));

		return $this->parse();
	}*/
}
?>
