<?php
// $Header: /home/cvs/automatweb_dev/classes/import/livelink_import.aw,v 1.2 2003/04/08 13:29:52 duke Exp $
// livelink_import.aw - Import livelingist

/*
	@groupinfo general caption=Üldine

	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

	@property rootnode type=textbox size=10 maxlength=10
	@caption Juurika ID

	@property outdir type=textbox 
	@caption Kataloog, kuhu failid kirjutada

	@property fileprefix type=textbox
	@caption Prefiks tabelisse kirjutatavatele failinimedele

	@property message type=text editonly=1
	@caption Objekti staatus

	@property invoke type=text editonly=1
	@caption Käivita

	@classinfo syslog_type=ST_LIVELINK_IMPORT

*/

class livelink_import extends class_base
{
	function livelink_import()
	{
		$this->init(array(
			'clid' => CL_LIVELINK_IMPORT
		));
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		switch($data["name"])
		{
			case "outdir":
				if (empty($args["obj"]["oid"]))
				{
					$data["value"] = aw_ini_get("site_basedir") . "/public";
				};
				break;
			case "invoke":
				list($err,) = $this->_chk_outdir($args["obj"]["meta"]["outdir"]);
				if (!$err)
				{
					$data["value"] = html::href(array(
						"url" => $this->mk_my_orb("invoke",array("id" => $args["obj"]["oid"])),
						"caption" => $data["caption"],
					));
				}
				
				break;

			case "message":
				list($error,$data["value"]) = $this->_chk_outdir($args["obj"]["meta"]["outdir"]);
				break;

		}
		return $retval;
	}

	function _chk_outdir($_dir)
	{
		$error = true;
		$msg = "";
		if (!file_exists($_dir))
		{
			$msg = "Sellist kataloogi pole";
		}
		else
		if (!is_dir($_dir))
		{
			$msg = "See pole kataloog";
		}
		else
		if (!is_writable($_dir))
		{
			$msg = "Väljundkataloog pole kirjutatav!";
		}
		else
		{
			$msg = "OK";
			$error = false;
		};
		return array($error,$msg);
	}

	function invoke($args = array())
	{
		$obj = $this->get_object(array(
			"oid" => $args["id"],
			"clid" => $this->clid,
		));
		$outdir = $obj["meta"]["outdir"];
		list($err,$msg) = $this->_chk_outdir($outdir);
		if ($err)
		{
			print $msg;
			die();
		};

		print "<pre>";

		$this->import_livelink_structure(array(
			"outdir" => $outdir,
			"rootnode" => (int)$obj["meta"]["rootnode"],
			"fileprefix" => $obj["meta"]["fileprefix"],
		));

		print "</pre>";
	}

	function import_livelink_structure($args = array())
	{
		$this->tmpdir = aw_ini_get("server.tmpdir");
		$this->outdir = $args["outdir"];
		$this->rootnode = $args["rootnode"];
		$this->fileprefix = $args["fileprefix"];

		$this->docs_to_retrieve = array();
                $this->need2update = array();

		ob_implicit_flush(1);

                print "going to fetch structure<br>";
                $outf = $this->fetch_structure();
                print "done!<br>";

                # parse the structure
                $xml_parser = xml_parser_create();
                xml_parser_set_option($xml_parser,XML_OPTION_CASE_FOLDING,0);
                xml_set_object($xml_parser,&$this);
                xml_set_element_handler($xml_parser,"_xml_start_element","_xml_end_element");
                $xml_data = join("",file($outf));
                $xml_data = str_replace("\r","",$xml_data);

                if (!xml_parse($xml_parser,$xml_data))
                {
			$xs = get_instance("xml/xml_parser");
			$xs->bitch_and_die(&$xml_parser,&$xml_data);
                };

                foreach($this->docs_to_retrieve as $node_id)
                {
                        $this->fetch_node($node_id);
                };

		# clean up too after ourselves
		unlink($outf);

        }

	function _xml_start_element($parser,$name,$attribs)
	{
		if ($name == "llnode" && isset($attribs["objtype"]) && ($attribs["objtype"] == 0))
		{
			$name = $attribs["name"];
			$description = isset($attribs["description"]) ? $attribs["description"] : "";
			$id = $attribs["id"];
			$parent = $attribs["parentid"];
			$modified = strtotime($attribs["modified"]);

			$old = $this->db_fetch_row("SELECT * FROM livelink_folders WHERE id = '$id'");
			if (empty($old))
			{
				# so it must be new
				$this->quote($name);
				$this->quote($description);
				print "creating $name\n";
				$q = "INSERT INTO livelink_folders  (id,name,description,parent,modified)
					VALUES ('$id','$name','$description','$parent','$modified')";
				print $q;
				print "\n";
				$this->need2update[] = $id;
				$this->db_query($q);
			}
			else
			if ($modified > $old["modified"])
			{
				# update existing one
				print "renewing $name\n";
				$this->quote($name);
				$this->quote($description);

				$q = "UPDATE livelink_folders SET
					name = '$name',description = '$description',parent = '$parent',
					modified = '$modified'
					WHERE id = '$id'";
				$this->need2update[] = $id;

				print $q;
				print "\n";
				$this->db_query($q);
			}
			else
			{
				print "not touching $name, since it has not been modified\n";
			};
                }

		if ($name == "llnode" && isset($attribs["objtype"]) && ($attribs["objtype"] > 0))
		{
			// only retrieve the docs, if the parent has been modified
			if (in_array($attribs["parentid"],$this->need2update))
			{
				$this->docs_to_retrieve[] = $attribs["id"];
			};
		}
	}

	function _xml_end_element($parser,$name)
        {
                //print "name $name ends<br>";
        }

	function fetch_structure()
        {
		$outfile = tempnam($this->tmpdir,"aw-");
		$rootnode = $this->rootnode;
		passthru("wget -O $outfile 'https://dok.ut.ee/livelink/livelink?func=LL.login&username=avatud&password=avatud'  'https://dok.ut.ee/livelink/livelink?func=ll&objId=$rootnode&objAction=XMLExport&scope=sub&versioninfo=current&schema' 2>&1",$retval);
		var_dump($retval);
		print "got file, parsing \n";
		// check whether opening succeeded?
		$fc = join("",file($outfile));
		// reap the bloody header
		$real_xml = substr($fc,strpos($fc,"<?xml"));
		$fh = fopen($outfile,"w");
		fwrite($fh,$real_xml);
		fclose($fh);
		return $outfile;
	}

	function fetch_node($node_id)
	{
		$outfile = tempnam($this->tmpdir,"aw-");
		passthru("wget -O $outfile 'https://dok.ut.ee/livelink/livelink?func=LL.login&username=avatud&password=avatud'  'https://dok.ut.ee/livelink/livelink?func=ll&objId=$node_id&objAction=XMLExport&scope=sub&versioninfo=current&schema&content=base64'");
		// check whether opening succeeded?
		$fc = join("",file($outfile));
		// reap the bloody header
		$real_xml = substr($fc,strpos($fc,"<?xml"));
		$fh = fopen($outfile,"w");
		fwrite($fh,$real_xml);
		fclose($fh);
		sleep(3);

		print "entering parser<br>";
		$this->parse_file($outfile);
		unlink($outfile);
        }

	function parse_file($fname)
	{
		$infile = $fname;
		$xml_data = join("",file($infile));
		#$xml_data = str_replace("\r","",$xml_data);
		$this->catch_content = false;
		$this->content = "";
		$this->filename = "";

		$xml_parser = xml_parser_create();
		xml_set_object($xml_parser,&$this);
		xml_parser_set_option($xml_parser,XML_OPTION_CASE_FOLDING,0);
		xml_set_element_handler($xml_parser,"_xml_file_start_element","_xml_file_end_element");
		xml_set_character_data_handler($xml_parser,"_xml_file_cdata_handler");
		if (!xml_parse($xml_parser,$xml_data))
		{
			$xs = get_instance("xml/xml_parser");
			$xs->bitch_and_die(&$xml_parser,&$xml_data);
		};

		# parse the structure
		if ($this->filename)
		{
			#$name = $this->name;
			$name = $this->desc;
			$id = $this->id;
			$parent = $this->parentid;
			$modified = $this->modified;
			$filename = $this->fileprefix . $this->filename;
			$old = $this->db_fetch_row("SELECT modified FROM livelink_files WHERE id = '$id'");
			if (empty($old))
			{
				print "creating file $filename\n";
				$this->quote($name);
				$this->quote($filename);
				// wah, wah
				$this->write_outfile();
				$q = "INSERT INTO livelink_files (id,parent,name,filename,modified)
				VALUES('$id','$parent','$name','$filename','$modified')";
				$this->db_query($q);
			}
			else
			if ($modified > $old["modified"])
			{
				print "updating file $filename";
				$this->quote($name);
				$this->quote($filename);
				// wah, wah
				$this->write_outfile();
				$q = "UPDATE livelink_files SET
				parent = '$parent',name = '$name',filename = '$filename',
				modified = '$modified'
				WHERE id = '$id'";
				$this->db_query($q);
			}
			else
			{
				print "not touching $filename, it has not been modified\n";
			};
		};
	}

	function write_outfile()
	{
		$outfile = $this->outdir . "/" . $this->filename;
		print "writing $outfile<br>>";
		$fh = fopen($outfile,"w");
		fwrite($fh,base64_decode(trim($this->content)));
		fclose($fh);
	}

        function _xml_file_start_element($parser,$name,$attribs)
        {
                if (($name == "content") && ($attribs["type"] == "base64"))
                {
                        $this->catch_content = true;
                };

                if (($name == "version") && isset($attribs["filename"]))
                {
                        $this->filename = $attribs["id"] . "-" . $attribs["filename"];
                        $this->name = ($attribs["name"]) ? $attribs["name"] : $attribs["filename"];
                        $this->modified = strtotime($attribs["modifydate"]);
                        $this->id = $attribs["id"];
                };

                if (($name == "llnode") && isset($attribs["parentid"]))
                {
                        $this->parentid = $attribs["parentid"];
                        $this->desc = $attribs["description"];
                };
        }

	function _xml_file_end_element($parser,$name)
	{
		if ($this->catch_content)
		{
			$this->catch_content = false;
		};
	}

	function _xml_file_cdata_handler($parser,$data)
	{
		if ($this->catch_content)
		{
			$this->content .= $data;
		};
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
}
?>
