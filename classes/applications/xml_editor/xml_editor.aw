<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/xml_editor/xml_editor.aw,v 1.1 2008/02/03 22:38:08 dragut Exp $
// xml_editor.aw - xml editor 
/*

@classinfo syslog_type=ST_XML_EDITOR relationmgr=yes

@default table=objects
@default group=general

@property xml_file type=textbox field=meta method=serialize
@caption XML fail

@property excisting_xml_files type=select store=no method=serialize
@caption files/xml kaustas olevad xml failid

@property add_xml_file_to_server type=fileupload store=no method=serialize
@caption lisa uus xml fail serverisse

@groupinfo content caption="Sisu"
@default group=content

@property xml_content type=table store=no
@caption xml-i sisu tabel



*/

class xml_editor extends class_base
{

	var $xml_parser;
	var $xml_elements;
	
	function xml_editor()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/xml_editor/xml_editor",
			"clid" => CL_XML_EDITOR
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them


	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		    case "xml_content":
					$this->create_content_table($arr);
					break;
				case "excisting_xml_files":
				  $prop['options'] = $this->get_directory(array("dir" => aw_ini_get("site_basedir")."/files/xml/"));
				  break;
				case "add_xml_file_to_server":
				  
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
	
	function create_content_table($arr)
	{
		$t = &$arr['prop']['vcl_inst'];

		$t->define_field(array(
			"name" => "xml_element",
			"caption" => "xml elements",
		));

		$t->set_sortable(false);

// siit algab xml-i parsimine callbackide abil
/*

		$this->xml_parser = xml_parser_create();
		
		xml_set_object($this->xml_parser, $this);
		
		xml_set_element_handler($this->xml_parser, "start_element", "end_element");
		xml_set_character_data_handler($this->xml_parser, "character_data");
		xml_parser_set_option ($this->xml_parser, XML_OPTION_CASE_FOLDING, FALSE);
		
		if(!($fp = fopen($arr['obj_inst']->prop('xml_file'), "r")))
		{
			die("Cannot open ".$arr['obj_inst']->prop('xml_file'));
		}
		
		while ($data = fread($fp, 4096))
		{
			if(!xml_parse($this->xml_parser, $data, feof($fp)))
			{
				die("Miskine xml error");
			}
		}
		
		xml_parser_free($this->xml_parser);
*/
// xml-i parsimine callbackide abil LOPP

// ok - üritab siis andmed mingil määral tablisse surada
		
		$values = $this->parse_xml_file(array(
			"file" => $arr['obj_inst']->prop("xml_file")
		));
		
		$xml_file_as_array = file($arr['obj_inst']->prop("xml_file"));
		$xml_file_beginning = "";
		foreach ($xml_file_as_array as $line)
		{
			if(strpos($line, "<".$values[0]['tag']) === false)
			{
				$xml_file_beginning .= $line;
			}
			else
			{
				break;
			}
		}
		
		$t->define_data(array(
			"xml_element" => html::textarea(array(
				"name" => "xml_file_beginning",
				"value" => $xml_file_beginning,
				"cols" => 100,
			))."\n".html::hidden(array(
				"name" => "basedir",
				"value" => $this->cfg['basedir'],
			)),
		));
//		arr($values);
		foreach($values as $key => $value)
		{
	//		echo $value['tag']."<br>";
		
	//	    $t->define_data(array(
	//				"xml_element" => str_repeat("&nbsp;", (intval($value[level]))*5).$value['tag']." - ".$value['type'],
	//		));
	
	    	if(strcmp($value['type'], "open") == 0)
	    	{
	    		$attribs = "";
				if(isset($value['attributes']))
				{
					foreach($value['attributes'] as $attrib_key => $attrib_value)
					{
						$attribs .= " ".$attrib_key."=\"".$attrib_value."\" ";
					}
				}
	        
	        	$indent = str_repeat("&nbsp;", (intval($value['level']) * 5));
	        
				$t->define_data(array(
					"xml_element" => $indent."&lt;".$value['tag'].$attribs."&gt;",
				));
			}
			
			if(strcmp($value['type'], "complete") == 0)
			{
			
			  $txtfield = html::textbox(array(
					"value" => $value['value'],
				    "name" => $key,
				));
				$attribs = "";
				$indent = str_repeat("&nbsp;", (intval($value['level']) * 5));
				if(isset($value['attributes']))
			  	{
					foreach($value['attributes'] as $attrib_key => $attrib_value)
					{
					  	$attr_tf = html::textbox(array(
					    	"value" => $attrib_value,
					    	"name" => $attrib_key,
					  	));
					
						$attribs .= " <br>".str_repeat($indent, 2).$attrib_key."=\"".$attr_tf."\" ";
					}
				}
				
				
				
				$t->define_data(array(
					"xml_element" => $indent."&lt;".$value['tag'].$attribs."&gt;".$txtfield."&lt;/".$value['tag']."&gt;",
				));
			}
		  	if(strcmp($value['type'], "close") == 0)
		  	{
		    	$indent = str_repeat("&nbsp;", (intval($value['level']) * 5));

				$t->define_data(array(
					"xml_element" => $indent."&lt;/".$value['tag']."&gt;",
				));
			}
		}
		
		

	}

	function parse_xml_file($arr)
	{
	//	$data_file_path = $arr['obj_inst']->prop("xml_file");

		$data_file_content = file_get_contents($arr['file']);

		$xml_file_content = parse_xml_def(array("xml" => $data_file_content));

		$values = $xml_file_content[0];
	
		return $values;
	}


	function callback_pre_save($arr)
	{
		$orig_xml_values = $this->parse_xml_file(array(
			"file" => $arr['obj_inst']->prop("xml_file"),
		));
		
		$post_values = $arr['request'];
		
		foreach($post_values as $key => $value)
		{

			$orig_xml_values[$key]['value'] = $value;
			
			
		}
//		arr($orig_xml_values);
		
//		$output_file_string = "";
//		arr($arr['obj_inst']->arr());
//		$output_file = fopen($arr[request]['basedir']."/files/blah.xml", "w");

		$result = "";
		
		$result .= $post_values['xml_file_beginning'];

		foreach($orig_xml_values as $key => $value)
		{
		//	echo $value['tag']."<br>";

	//	    $t->define_data(array(
	//			"xml_element" => str_repeat("&nbsp;", (intval($value[level]))*5).$value['tag']." - ".$value['type'],
	//		));

	        if(strcmp($value['type'], "open") == 0)
	        {
	            $attribs = "";
			    if(isset($value['attributes']))
			    {
					foreach($value['attributes'] as $attrib_key => $attrib_value)
					{
						$attribs .= " ".$attrib_key."=\"".$attrib_value."\" ";
					}
				}

//	            $indent = str_repeat("&nbsp;", (intval($value['level']) * 5));

//				$t->define_data(array(
//					"xml_element" => $indent."&lt;".$value['tag'].$attribs."&gt;",
//				));
				$result .= "<".$value['tag'].$attribs.">\n";
//				fwrite($output_file, $s);
			}
			if(strcmp($value['type'], "complete") == 0)
			{

//			    $txtfield = html::textbox(array(
//					"value" => $value['value'],
//				    "name" => $key,
//				));
				$attribs = "";
			    if(isset($value['attributes']))
			    {
					foreach($value['attributes'] as $attrib_key => $attrib_value)
					{
						$attribs .= " ".$attrib_key."=\"".$attrib_value."\" ";
					}
				}

//				$indent = str_repeat("&nbsp;", (intval($value['level']) * 5));

//				$t->define_data(array(
//					"xml_element" => $indent."&lt;".$value['tag'].$attribs."&gt;".$txtfield."&lt;/".$value['tag']."&gt;",
//				));

//				fwrite($output_file, "<".$value['tag'].$attribs.">".$value['value']."</".$value['tag'].">\n");
					$result .= "<".$value['tag'].$attribs.">".$value['value']."</".$value['tag'].">\n";
			}
		    if(strcmp($value['type'], "close") == 0)
		    {
//		        $indent = str_repeat("&nbsp;", (intval($value['level']) * 5));

//				$t->define_data(array(
//					"xml_element" => $indent."&lt;/".$value['tag']."&gt;",
//				));

//				fwrite($output_file, "</".$value['tag'].">\n");
					$result .= "</".$value['tag'].">\n";
			}
			
			
			
			
		}
		
		
		
//		file_put_contents("../../automatweb_dev/files/xml/piirangud.xml", $output_file_string);
//		fclose($output_file);

		$this->put_file(array("file" => aw_ini_get("site_basedir")."/files/xml/blah.xml", "content" => $result));

		
	}
	
/****************************************************************
	xml parser callback functions - not used at the moment
*****************************************************************/
	function start_element($parser, $name, $attr)
	{
//		echo "start element: ".$name." [".xml_get_current_line_number($this->xml_parser)."] <br>";
		
	}
	
	function end_element($parser, $name)
	{
//		echo "end element: ".$name." [".xml_get_current_line_number($this->xml_parser)."] <br>";
	}
	
	function character_data($parser, $data)
	{
//	    if(strlen($data) > 1)
//	    {
			echo "<b>".$data."  [".xml_get_current_line_number($this->xml_parser)."] </b><br>";
//		}
	}
	
}
?>
