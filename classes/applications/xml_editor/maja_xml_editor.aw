<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/xml_editor/maja_xml_editor.aw,v 1.1 2004/09/21 17:21:23 dragut Exp $
// maja_xml_editor.aw - maja xml-i editor 
/*

@classinfo syslog_type=ST_MAJA_XML_EDITOR relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general

@property orig_xml_file type=textbox field=meta method=serialize size=50
@caption Originaal xml fail

@property new_xml_file type=textbox field=meta method=serialize size=50
@caption Uus xml fail

@groupinfo content_change caption="Muutmine"
@default group=content_change

@property xml_content type=table store=no no_caption=1
@caption xml-i sisu tabel

*/

class maja_xml_editor extends class_base
{
	function maja_xml_editor()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/xml_editor/maja_xml_editor",
			"clid" => CL_MAJA_XML_EDITOR
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
		
		$data_file_content = file_get_contents($arr['obj_inst']->prop("orig_xml_file"));
		
		$xml_file_content = parse_xml_def(array("xml" => $data_file_content));
		
		foreach($xml_file_content[0] as $key => $value)
		{
			if(strcmp($value['type'], "open") == 0)
			{

/*

// antud juhul ei ole seda siin vaja, kuna "open" tyypi tagidel selles
// xml failis ei ole atribuute (maja element on ainus open tüüpi tag)

				$attributes = "";
				if(isset($value['attributes']))
				{
					foreach($value['attributes'] as $attrib_key => $attrib_value)
					{
                        $attribs .= " ".$attrib_key."=\"".$attrib_value."\" ";
					}
				}
*/


				$t->define_data(array(
					"xml_element" => "&lt;".$value['tag'].$attribs."&gt;",
				));
			}
			
			if(strcmp($value['type'], "complete") == 0)
			{
				$attributes = "";
				if(isset($value['attributes']))
				{
					foreach($value['attributes'] as $attribute_key => $attribute_value)
					{
					    $textfield_size = 10;
         				if(strcmp($attribute_key, "korterinr") == 0 || strcmp($attribute_key, "number") == 0 || strcmp($attribute_key, "tubadearv") == 0 || strcmp($attribute_key, "pindala") == 0)
					    {
							$textfield_size = 5;
						}
						if(strcmp($attribute_key, "plaan") == 0)
						{
							$textfield_size = 25;
						}
					    $attribute_textfield = html::textbox(array(
							"name" => $key."@".$attribute_key,
							"value" => $attribute_value,
							"size" => $textfield_size,
						));
					
                        $attributes .= " ".$attribute_key."=\"".$attribute_textfield."\" ";
					}
				}

				$indent = str_repeat("&nbsp;", 5);

				$t->define_data(array(
					"xml_element" => $indent."&lt;".$value['tag'].$attributes."/&gt;",
				));
			}
			
			if(strcmp($value['type'], "close") == 0)
			{
				$t->define_data(array(
					"xml_element" => "&lt;/".$value['tag']."&gt;",
				));
			}
		}
		
	}

	function callback_pre_save($arr)
	{
	    if (strcmp($arr['request']['group'], "content_change") == 0)
	    {
	    	$data_file_content = file_get_contents($arr['obj_inst']->prop("orig_xml_file"));

			$xml_file_content = parse_xml_def(array("xml" => $data_file_content));



			foreach($arr['request'] as $key => $value)
			{
		    	$keys = explode("@", $key);
				$xml_file_content[0][$keys[0]]['attributes'][$keys[1]] = $value;
			}

			$result = "";

        	foreach($xml_file_content[0] as $key => $value)
			{
				if(strcmp($value['type'], "open") == 0)
				{
					$result .= "<".$value['tag'].$attribs.">\n";
				
				}

				if(strcmp($value['type'], "complete") == 0)
				{
					$attributes = "";
					if(isset($value['attributes']))
					{
						foreach($value['attributes'] as $attribute_key => $attribute_value)
						{
	                        $attributes .= " ".$attribute_key."=\"".$attribute_value."\" ";
						}
					}


					$result .= "\t<".$value['tag'].$attributes."/>\n";
				}

				if(strcmp($value['type'], "close") == 0)
				{
					$result .= "</".$value['tag'].">\n";
				}
			}

			if(!$file_handle = fopen($arr['obj_inst']->prop("new_xml_file"), "w"))
			{
				echo "Couldn't open the file (".$arr['obj_inst']->prop("new_xml_file").") to write";
			}


			if(fwrite($file_handle, $result) === FALSE)
			{
				echo "Cannot write to this file: ".$arr['obj_inst']->prop("new_xml_file");
			}

			fclose($file_handle);
		}

	}
}
?>
