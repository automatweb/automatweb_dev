<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/xml_editor/maja_xml_editor.aw,v 1.3 2004/10/22 00:25:33 dragut Exp $
// maja_xml_editor.aw - maja xml-i editor 
/*

@classinfo syslog_type=ST_MAJA_XML_EDITOR relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general

@property orig_xml_file type=textbox field=meta method=serialize size=50
@caption Originaal xml fail

@property new_xml_file type=textbox field=meta method=serialize size=50
@caption Uus xml fail

@property db_table_contents type=relpicker reltype=RELTYPE_DB_TABLE_CONTENTS field=meta method=serialize
@caption Andmebaasitabeli sisu objekt

@groupinfo db_saving_settings caption="Baasi salvestamise seaded"
@default group=db_saving_settings

@property house_name type=textbox field=meta method=serialize
@caption Maja nimi/aadress

@property xml_to_db__connections type=table field=meta method=serialize no_caption=1
@caption XML ja DB v&auml;ljade vahelised seosed

@groupinfo content_change caption="XML-i muutmine"
@default group=content_change

@property xml_content type=table store=no no_caption=1
@caption xml-i sisu tabel

@reltype DB_TABLE_CONTENTS value=1 clid=CL_DB_TABLE_CONTENTS
@caption Andmebaasitabeli sisu objekt
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
				
			case "xml_to_db__connections":
				$this->create_xml_to_db_connections_table($arr);
				break;
		};
		return $retval;
	}
	


	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "xml_to_db__connections":
			//	arr($arr['request']);
				$arr['obj_inst']->set_meta("xml_to_db_conns", $arr['request']['xml_to_db_values']);
				break;
			case "house_name":
			//Here I'll check if house_name has changed, casue when it is
			//I have to change it in database too

				$old_house_name = $arr['obj_inst']->meta("house_name");
				$new_house_name = $prop['value'];

				if($old_house_name != $new_house_name)
				{
					$db_table_contents_obj = obj($arr['obj_inst']->prop("db_table_contents"));
					$db_table_name = $db_table_contents_obj->prop("db_table");

					$this->db_query("UPDATE ".$db_table_name." SET maja_nimi='".$new_house_name."' WHERE maja_nimi='".$old_house_name."'");
					
				}
				break;
			case "xml_content":
				$arr['obj_inst']->set_meta("floors", $arr['request']['floors_flats']);
				break;
		}
		return $retval;
	}	


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

		$t->define_field(array(
			"name" => "floor",
			"caption" => "korrus",
		));
		
		$t->set_sortable(false);
		
//		$data_file_content = file_get_contents($arr['obj_inst']->prop("orig_xml_file"));
		
		$data_file_content = $this->get_file(array(
				"file" => $arr['obj_inst']->prop("orig_xml_file"),
			));

		if($data_file_content == false)
		{
			die("faili ei &otilde;nnestunud leida!");
		}

		$xml_file_content = parse_xml_def(array("xml" => $data_file_content));
		$floors_arr = $xml_file_content[1]['korrus'];
		$floors = array();

		foreach($floors_arr as $floor)
		{
			array_push($floors, $xml_file_content[0][$floor]['attributes']['nimi']);
		}

		$floors_flats_saved = $arr['obj_inst']->meta("floors");
		
		foreach($xml_file_content[0] as $key => $value)
		{
			if($value['type'] == "open")
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
			
			if($value['type'] == "complete")
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

				if($value['tag'] == "korter")
				{
					$floor_select = html::select(array(
							"name" => "floors_flats[".$key."]",
							"options" => $floors,
							"selected" => $floors_flats_saved[$key],
						));
				}
				else
				{
					$floor_select = "";
				}

				$t->define_data(array(
					"xml_element" => $indent."&lt;".$value['tag'].$attributes."/&gt;",
					"floor" => $floor_select,
				));
			}
			
			if($value['type'] == "close")
			{
				$t->define_data(array(
					"xml_element" => "&lt;/".$value['tag']."&gt;",
				));
			}
		}
		
	}
////////////////////////////////////////////////////////////////////////////////
//
// RISTTABELI TEKITAMINE
//
////////////////////////////////////////////////////////////////////////////////



	function create_xml_to_db_connections_table($arr)
	{
	
		$t = &$arr['prop']['vcl_inst'];


//		$o = obj($arr['id']);

		$db_table_contents_obj = obj($arr['obj_inst']->prop("db_table_contents"));
		$db_table_contents_inst = get_instance(CL_DB_TABLE_CONTENTS);
		$table_fields = $db_table_contents_inst->get_fields($db_table_contents_obj);
//		$table_data = $db_table_contents_inst->get_objects($db_table_contents_obj);
//		arr($table_fields);
//		arr($arr);

		
// i'll unset some unneeded array members, whisch are 2 table field, which content
// has to come somewhere else
		unset($table_fields['id'], $table_fields['maja_nimi']);

		$t->define_field(array(
			"name" => "first_column",
			"caption" => "---",
		));
		$t->define_field(array(
			"name" => "empty",
			"caption" => "Ei Salvestata",
		));
		foreach($table_fields as $key => $value)
		{

			$t->define_field(array(
				"name" => $key,
				"caption" => $value,
				"align" => "center",
			));
		}

		$data_file_content = file_get_contents($arr['obj_inst']->prop("orig_xml_file"));

		$xml_file_content = parse_xml_def(array("xml" => $data_file_content));
		
// here I'll take the first <korter> elements attributes and assume that this is all
		$korter_el_attribs = array_keys($xml_file_content[0][1]['attributes']);
		
		$xml_to_db_values = $arr['obj_inst']->meta("xml_to_db_conns");
		

		

		foreach($korter_el_attribs as $value)
		{
			$row = array("first_column" => $value);
			$row['empty'] = html::radiobutton(array(
				"name" => "xml_to_db_values[$value]",
				"value" => "empty",
				"checked" => checked($xml_to_db_values[$value] == "empty"),
			));
			foreach($table_fields as $tf_key => $tf_value)
			{
				$row[$tf_key] = html::radiobutton(array(
					"name" => "xml_to_db_values[$value]",
					"value" => $tf_key,
					"checked" => checked($xml_to_db_values[$value] == $tf_key),
				));
			}
			$t->define_data($row);
		}
//		arr($xml_file_content[0][1]);
		
		$t->set_sortable(false);
	}

	function callback_pre_save($arr)
	{
		if (strcmp($arr['request']['group'], "db_saving_settings") == 0)
		{


		}
	
		if (strcmp($arr['request']['group'], "content_change") == 0)
		{
			$data_file_content = file_get_contents($arr['obj_inst']->prop("orig_xml_file"));

			$xml_file_content = parse_xml_def(array("xml" => $data_file_content));

// updateing the xml_file_content array with the content from $arr['request']

			foreach($arr['request'] as $key => $value)
			{
				$keys = explode("@", $key);
				$xml_file_content[0][$keys[0]]['attributes'][$keys[1]] = $value;
			}

// Here I'll loop through all the xml_file_content array and but together a string
// which I can nicely write into file.

			

			$result = "";


// some preparations for updating the db table
			$sql_commands = array();
			$db_table_contents_obj = obj($arr['obj_inst']->prop("db_table_contents"));
			$db_table_name = $db_table_contents_obj->prop("db_table");
			$saved_floors = $arr['obj_inst']->meta("floors");
			$xml_to_db_conns = $arr['obj_inst']->meta("xml_to_db_conns");
			
			foreach($xml_file_content[0] as $key => $value)
			{
				if(strcmp($value['type'], "open") == 0)
				{
					$result .= "<".$value['tag'].$attribs.">\n";
				
				}

				if(strcmp($value['type'], "complete") == 0)
				{
					$attributes = "";

					$sql_command = array();

					if(isset($value['attributes']))
					{
						foreach($value['attributes'] as $attribute_key => $attribute_value)
						{
							$attributes .= " ".$attribute_key."=\"".$attribute_value."\" ";
							if($value['tag'] == "korter" && $xml_to_db_conns[$attribute_key] != "empty")
							{
								$sql_command[$xml_to_db_conns[$attribute_key]] = $attribute_value;
								$sql_command['korrus'] = $saved_floors[$key];
							}
						}
					}


					$result .= "\t<".$value['tag'].$attributes."/>\n";
					if(!empty($sql_command))
					{
						array_push($sql_commands, $sql_command);
					}
				}

				if(strcmp($value['type'], "close") == 0)
				{
					$result .= "</".$value['tag'].">\n";
				}
			}

// some DEBUGGING STUFF


//			arr($sql_commands);

// let's but the data in db table


// I think i don't need the following check
//			$db_table_rows = $this->db_fetch_array("SELECT * FROM ".$db_table_name." WHERE maja_nimi='".$house_name."'");
//			if(!empty($db_table_rows))
//			{
//				$this->db_query("DELETE FROM ".$db_table_name." WHERE maja_nimi='".$house_name."'");
//			}

			$house_name = $arr['obj_inst']->prop("house_name");

			$this->db_query("DELETE FROM ".$db_table_name." WHERE maja_nimi='".$house_name."'");

			foreach($sql_commands as $sql_command)
			{
				$sql_insert = "INSERT INTO ".$db_table_name." SET maja_nimi='".$house_name."', ";
				foreach($sql_command as $sql_c_key => $sql_c_value)
				{
					$sql_insert .= $sql_c_key."='".$sql_c_value."', ";
				}

				$sql_insert = substr($sql_insert, 0, (strlen($sql_insert)-2));
				$this->db_query($sql_insert);
			}
			
//			$this->db_query();


// here I write the modified xml content to file

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
