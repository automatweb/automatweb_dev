<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_rpc.aw,v 2.3 2001/07/27 02:51:44 duke Exp $
// form_rpc.aw - RPC functions for formgen
classload("form");
class form_rpc extends form {
	function form_rpc($args = array())
	{
		$this->db_init();
	}

	////
	// !Handleb üle RPC sisse tulnud sisestust
	function rpc_handle_entry($args = array())
	{
		// fetchime XML sisendi andmed
		$meta = $this->get_xml_input(array(
			"alias" => $args["alias"],
		));
		

		// iga vormi submitmine eraldi
		foreach($meta["forms"] as $key => $form_id)
		{
			$object = $this->get_object($form_id);
			$el_values = array();
			
			foreach($meta["elements"] as $id => $props)
			{
				if ($props["form"] == $form_id)
				{
					// radiobuttons get special handling
					if ($props["type"] == "radiobutton")
					{
						$group = $props["group"];
						if ($args["elements"]["radio_$group"] == $id)
						{
							$el_values["radio_group_$group"] = $args["elements"]["radio_$group"];
						}
					}
					elseif ($props["type"] == "listbox")
					{
						$el_values[$id] = "element_" . $id . "_lbopt_" . $args["elements"][$props["name"]];
					}
					else
					{
						$el_values[$id] = $args["elements"][$props["name"]];
					};
				};
			}
		
			$datablock["id"] = $form_id;

			$datablock["values"] = $el_values;
 
			$this->process_entry($datablock);
		};
 
		// entry tuleb salvestada parenti RPC_ENTRIES alla
		$retval = array(
			"success" => "yes",
		);
		return $retval;
	}	

	////
	// !Fetches information about an XML input
	function get_xml_input($args = array())
	{
		$alias = $args["alias"];
		$q = sprintf("SELECT * FROM objects WHERE name = '%s' AND class_id = %d",$alias,CL_FORM_XML_INPUT);
		$this->db_query($q);
		$row = $this->db_next();
		$meta = $this->get_object_metadata(array(
			"metadata" => $row["metadata"],
		));
		return $meta;
	}

	////
	// !Lists all entries for a particular form
	function rpc_listentries($args = array())
	{
		preg_match("/^(\w*)/",$args[0],$matches);
		$alias = $matches[1];
		$q = "SELECT * FROM objects WHERE name = '$alias' AND class_id = " . CL_FORM_XML_OUTPUT;
		$this->db_query($q);
		$entry_list = array();
		classload("xml");
		$xml = new xml();
		$blacklist = array();
                $row = $this->db_next();
		if ($row)
		{
			$xdata = $this->get_object_metadata(array(
				"metadata" => $row["metadata"],
			));
			if (is_array($xdata["forms"]))
			{
				foreach($xdata["forms"] as $key => $val)
				{
					$q = "SELECT * FROM form_" . $val . "_entries";
					$this->db_query($q);
					// if the entry is a part of form_chain,
					while($row = $this->db_next())
					{
						if (!$blacklist[$row["id"]])
						{
							$entry_list[] = $row["id"];
                                                };
 
						if ($row["chain_id"])
						{
							$this->save_handle();
							$q = "SELECT * FROM form_chain_entries WHERE id = '$row[chain_id]'";
							$this->db_query($q);
							$crow = $this->db_next();
							$blacklist = $blacklist + array_flip($xml->xml_unserialize(array("source" => $crow["ids"])));
							$this->restore_handle();
						};
					};
				};
			};

			$retval = array(
				"data" => $entry_list,
				"type" => "array",
			);
		}
		else
		{
			$retval = array(
				"error" => "Name/alias not found",
				"errno" => "1",
			);
		};
		return $retval;
	}

	////
	// !Gets a form entry

	function rpc_getentry($args = array())
	{
		$eid = $args[0];
		$alias = $args[1];
 
		classload("form_entry");
		$form_entry = new form_entry();
		$block = $form_entry->get_entry(array("eid" => $eid));

		$q = "SELECT * FROM objects WHERE name = '$alias' AND class_id = " . CL_FORM_XML_OUTPUT;
		$this->db_query($q);
 
		$row = $this->db_next();
 
		$xdata = $this->get_object_metadata(array(
			"metadata" => $row["metadata"],
		));
 
		$jrk = $xdata["data"]["jrk"];
		$active = $xdata["data"]["active"];
		$tags = $xdata["data"]["tag"];

		$this->pstruct = array();
	
		asort($jrk);

		foreach($jrk as $key => $val)
		{
			if ($active[$key])
			{
				$idx = "el_" . $key;
				$keyblock = array(
					"name" => $tags[$key],
					"value" => $block[$idx],
				);
				$this->pstruct[] = $keyblock;
			};
		};

		$retval = array(
			"data" => $this->pstruct,
			"type" => "struct",
		);
		return $retval;
	}
};      // class ends
?>
