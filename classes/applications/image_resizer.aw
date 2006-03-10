<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/Attic/image_resizer.aw,v 1.4 2006/03/10 11:40:15 ahti Exp $
// image_resizer.aw - Piltide muutja 

/*

@classinfo syslog_type=ST_IMAGE_RESIZER relationmgr=yes

@default table=objects
@default group=general

@property input_type type=chooser method=serialize field=meta
@caption Sisendi tüüp

@property from_folder type=textbox method=serialize field=meta
@caption Süsteemi kaust

@property from_folder_aw type=relpicker reltype=RELTYPE_IMAGE_FOLDER field=meta
@caption AW kaust

@groupinfo config caption="Võimalused"
@default group=config

@property width_bigger_than type=textbox size=10 method=serialize field=meta
@caption Laiuse tingimus

@property new_width type=textbox size=10 method=serialize field=meta
@caption Uus laius

@groupinfo status caption="Staatus"
@default group=status

@property message type=hidden store=no
@caption Staatus

@property from_folder_contents type=table store=no no_caption=1
@caption Info table

//@property status_info type=text store=no
//@caption Töötlemata

@property do_resize type=submit value=Töötle action=do_resize store=no
@caption Töötle

@reltype IMAGE_FOLDER value=1 clid=CL_MENU
@caption Piltide kaust

*/
define('INPUT_TYPE_AW_FOLDER', 1);
define('INPUT_TYPE_SYSTEM_FOLDER', 2);

class image_resizer extends class_base
{
	function image_resizer()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/image_resizer",
			"clid" => CL_IMAGE_RESIZER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case 'from_folder':
				if($arr['obj_inst']->meta('input_type')==INPUT_TYPE_AW_FOLDER)
				{
					return PROP_IGNORE;
				}
			break;
			case 'from_folder_aw':
				if($arr['obj_inst']->meta('input_type')==INPUT_TYPE_SYSTEM_FOLDER)
				{
					return PROP_IGNORE;
				}
			break;
			case 'input_type':
				$prop['options'] = array(
					1=>'AW kaust',
					2=>'Süsteemi kaust',
				);

				if(!$arr['obj_inst']->meta('input_type'))
				{
					$prop['value'] = INPUT_TYPE_SYSTEM_FOLDER;
				}
			break;
			case 'from_folder_contents':
				$this->do_from_folder_contents($arr);
			break;
			case 'status_info':
				/*$result = $this->status_info(&$arr);
				if(!$result)
				{
					echo ":(";
				}
				else
				{
					$prop['value'] = "Töötlemata: ".$result['nok']."<br>Töödeldud: ".$result['ok'];
				}*/
			break;
		};
		return $retval;
	}

	/*
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
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

	function _get_info_on_image($o)
	{
		$rtrn = array();
		if(!is_object($this->converter))
		{
			$this->converter = get_instance('core/converters/image_convert');
		}
		if(file_exists($o->prop('file2')))
		{
			$file = $o->prop('file2');
			$ext = substr($file,strpos($file,'.')+1);
			$this->converter->load_from_file($file);
			$size = $this->converter->size();			
			$rtrn = array(
						'file_name' => $file,
						'caption' => $o->name(),
						'file_type' => $this->get_file_type_for_extension($ext),
						'writeable' => is_writeable($file),
						'width' => $size[0],
						'height' => $size[1],
			);
		}
		return $rtrn;
	}

	function _parse_folder_for_info($obj, $data)
	{
		$ol = new object_list(array(
					'class_id' => CL_IMAGE,
					'parent' => $obj->id(),
		));

		$arr = $ol->arr();
		foreach($arr as $o)
		{
			$data['files'][$o->id()] = $this->_get_info_on_image($o);
		}

		$ol = new object_list(array(
					'class_id' => CL_MENU,
					'parent' => $obj->id(),
		));

		$arr = $ol->arr();
		foreach($arr as $o)
		{
			$this->_parse_folder_for_info(&$o, &$data);
		}
	}

	function status_info_aw_folder($arr)
	{
		//sorry, aga ei ole valitud kausta
		if(!is_object($arr['obj_inst']) || !is_oid($arr['obj_inst']->meta('from_folder_aw')))
		{
			return array('files'=>array());
		}

		//vihkan rekursiivsust :(
		$data = array('files'=>array());
		$this->_parse_folder_for_info(new object($arr['obj_inst']->meta('from_folder_aw')), &$data);
		return $data;
	}

	function status_info_system_folder($arr)	
	{
		$rtrn = array('ok'=>0,'nok'=>0,'files'=>array(),'err'=>array());
		$extensions = array('jpg','png');
		$path = $arr['obj_inst']->meta('from_folder');
		if(!file_exists($path) || !is_dir($path))
		{
			return;
		}
		$dir = @opendir($path);
		$converter = get_instance("core/converters/image_convert");
		if($dir)
		{
			while(($file = readdir($dir))!==false)
			{
				if($file=='.' || $file=='..')
				{
					continue;
				}
				if(strlen($file)>3 && strpos($file,'.')!==false)
				{
					$ext = substr($file,strpos($file,'.')+1);
					if(in_array($ext, $extensions))
					{
						$converter->load_from_file($path."/".$file);
						$size = $converter->size();
						if($size[0]>$arr['obj_inst']->meta('width_bigger'))
						{
							$rtrn['nok']++;
							switch(strtolower($ext))
							{
								case 'png':
									$type = IMAGE_PNG;
								break;
								case 'jpg':
									$type = IMAGE_JPEG;
								break;
								default:
									$type = IMAGE_PNG;
								break;
							}
							$rtrn['files'][] = array(
									'file_name'=>$path."/".$file,
									'file_type' => $type,
									'writeable' => is_writable($path."/".$file),
									'width' => $size[0],
									'height' => $size[1],
							);
						}
						else
						{
							if($arr['all_image_files'])
							{
								$rtrn['files'][] = array(
									'file_name'=>$path."/".$file,
									'file_type' => $type,
									'writeable' => is_writable($path."/".$file),
									'width' => $size[0],
									'height' => $size[1],
								);
							
							}
							$rtrn['ok']++;
						}
					}
				}
			}
			return $rtrn;
		}
		@closedir($dir);
		return array();
	}

	/**
		@attrib name=do_resize 
	**/
	function do_resize($arr)
	{
		$obj = new object($arr['id']);
		if($obj->meta('input_type')==INPUT_TYPE_AW_FOLDER)
		{
			$arr['obj_inst'] = &$obj;
			$result = $this->status_info_aw_folder(&$arr);
		}
		else
		{
			$result = $this->status_info_system_folder(array('obj_inst'=>&$obj));
		}

		$converter = get_instance('core/converters/image_convert');
		foreach($result['files'] as $file)
		{
			$file_name = $file['file_name'];
			$file_type = $file['file_type'];
			$converter->load_from_file($file_name);
			$size=$converter->size();
			$height = 1;
			//have to decrease the height the same
			//amount of %
			if((int)$obj->meta('new_width'))
			{
				$percent = ((int)$obj->meta('new_width'))/$size[0];
				$height = (int)($percent*$size[1]);
				if($file['writeable'])
				{
					$converter->resize_simple((int)$obj->meta('new_width'),$height);
					$converter->save($file_name, $file_type);
					//echo "converdin $file_type $file_name uus laius ".(int)$obj->meta('new_width')." ja uus kõrgus $height<br>";
				}
			}
			//$converter->res
		}
		return $this->mk_my_orb('change',array(
						'id' => $arr['id'],
						'group' => $arr['group'],

					),'image_resizer');
	}
	
	function _init_do_from_folder_contents(&$arr)
	{
		$table = &$arr['prop']['vcl_inst'];

		$table->define_field(array(
			'name' => 'file_name',
			'caption' => 'Faili nimi',
		));
		
		$table->define_field(array(
			'name' => 'width',
			'caption' => 'Laius',
		));
		
		$table->define_field(array(
			'name' => 'height',
			'caption' => 'Kõrgus',
		));

		$table->define_field(array(
			'name' => 'writeable',
			'caption' => 'Saan ülekirjutada?',
		));
	}

	function do_from_folder_contents($arr)
	{
		$this->_init_do_from_folder_contents($arr);
		$arr['all_image_files'] = 1;

		if($arr['obj_inst']->meta('input_type')==INPUT_TYPE_AW_FOLDER)
		{
			$data = $this->status_info_aw_folder(&$arr);
		}
		else
		{
			$data = $this->status_info_system_folder(&$arr);
		}
		
		$table =& $arr['prop']['vcl_inst'];
		/*if(!is_array($data['files']))
		{
			return;
		}*/
		foreach($data['files'] as $data_item)
		{
			$writeable = $data_item['writeable']?"Jah":"Ei";
			$table->define_data(array(
				'file_name' => isset($data_item['caption'])?$data_item['caption']:$data_item['file_name'],
				'writeable' => $writeable,
				'width' => $data_item['width'],
				'height' => $data_item['height'],
			));
		}
	}

	function get_file_type_for_extension($ext)
	{
		$type = '';
		switch(strtolower($ext))
		{
			case 'png':
				$type = IMAGE_PNG;
			break;
			case 'jpg':
				$type = IMAGE_JPEG;
			break;
			default:
				$type = IMAGE_PNG;
			break;
		}
		return $type;
	}

}
?>
