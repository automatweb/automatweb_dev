<?php

/*

@classinfo syslog_type=ST_GRAPH_NG

@groupinfo general caption=Üldine
@groupinfo data caption=Andmed
@groupinfo conf caption=&Uuml;dine_konfiguratsioon
@groupinfo specific caption=T&uuml;&uuml;bi_konfiguratsioon

@default table=objects
@default group=general
@default field=meta

@property status type=status field=status
@caption Staatus

@default method=serialize

@property graph_type type=select 
@caption Graafiku t&uuml;&uuml;p


@default group=data

@property data_y type=textbox
@caption Y telje v&auml;&auml;rtused

@property data_x type=generated generator=generator_x_values
@caption X telje v&auml;&auml;rtused

@property data_cvs type=fileupload
@caption Uploadi CSV fail

@property data_cvs_sep type=textbox size=2
@caption CSV failis eraldaja



@property typeconf type=generated generator=prop_gen group=specific
@caption Konf


@default group=conf

@property title type=textbox
@caption Pealkiri

@property title_color type=colorpicker
@caption Pealkirja v&auml;rv

@property bg_color type=colorpicker
@caption Tausta v&auml;rv

@property height type=textbox size=4
@caption K&otilde;rgus 

@propertry width type=textbox size=4
@caption Laius

@property int_height type=textbox size=4
@caption Sisemine k&otilde;rgus

@property int_width type=textbox size=4
@caption Sisemine laius

@property show_values type=checkbox ch_value=1
@caption N&auml;itan v&auml;&auml;rtusi

*/

class graph_ng extends class_base
{
	function graph_ng()
	{
		$this->init(array(
			'tpldir' => 'graph/Graafik (ng)',
			'clid' => CL_GRAPH_NG
		));

		$this->mod_dir = $this->cfg['classdir'].'/graph/modules';
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

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		$this->read_template('show.tpl');

		$this->vars(array(
			'name' => $ob['name']
		));

		return $this->parse();
	}

	////
	// !returns a list of graph modules - they are stored in the modules folder
	function get_module_list($arr = array())
	{
		extract($arr);
		$ret = array();

		if ($dir = @opendir($this->mod_dir)) 
		{
			while (($file = readdir($dir)) !== false)
			{
				if (!($file == "." || $file == ".."))
				{
					$file = basename($file, '.'.$this->cfg["ext"]);
					$finst = get_instance('graph/modules/'.$file);
					$ret[$file] = $finst->get_module_name();
				}
			}  
			closedir($dir);
		}
		return $ret;
	}

	////
	// !gets the drawing module instance by graph object id
	function get_module_inst($id)
	{
		$ob = $this->get_object($id);
		if ($ob['meta']['graph_type'] == '')
		{
			return get_instance('graph/graph_base');
		}
		return get_instance('graph/modules/'.$ob['meta']['graph_type']);
	}

	function get_property(&$arr)
	{
		$prop = &$arr['prop'];

		if ($prop['name'] == 'graph_type')
		{
			$prop['options'] = array("" => "") + $this->get_module_list();
		}
		return PROP_OK;
	}

	function prop_gen($arr)
	{
		extract($arr);
		if ($oid)
		{
			$inst = $this->get_module_inst($oid);
			return $inst->prop_gen($arr);
		}
		return array();
	}

	function generator_x_values($arr)
	{
		extract($arr);
		if ($oid)
		{
			$inst = $this->get_module_inst($oid);
			return $inst->generator_x_values($arr);
		}
		return array();
	}
}
?>
