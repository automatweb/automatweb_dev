<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/server_folder.aw,v 1.4 2005/03/23 11:45:07 kristo Exp $
// server_folder.aw - Serveri Kataloog 
/*

@classinfo syslog_type=ST_SERVER_FOLDER no_comment=1 no_status=1

@default table=objects
@default group=general

@property folder field=meta method=serialize type=textbox
@caption Kataloog serveris kust failid v&otilde;tta

@forminfo ch_file onload=init_chfile onsubmit=submit_chfile

@default form=ch_file

@property chf_view_file type=text form=ch_file
@caption Vaata faili

@property chf_file type=fileupload form=ch_file
@caption Uploadi fail


@forminfo add_file onload=init_addfile onsubmit=submit_addfile

@default form=add_file

@property addf_file type=fileupload form=add_file
@caption Uploadi fail

*/

class server_folder extends class_base
{
	function server_folder()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/server_folder",
			"clid" => CL_SERVER_FOLDER
		));
	}

	function get_contents($o)
	{
		$fs = $this->get_directory(array(
			"dir" => $o->prop("folder")
		));
		$ret = array();
		foreach($fs as $file)
		{
			//$file = $o->id().":".$file;
			$ret[$file] = $file;
		}
		return $ret;
	}

	/** shows the file given in the argument from the server. 
		
		@attrib params=name name=show_file

		@param fid required

	**/
	function show_file($arr)
	{
		extract($arr);
		list($oid, $fname) = explode(":", $fid);
		error::raise_if(!is_oid($oid), array(
			"id" => ERR_PARAM,
			"msg" => sprintf(t("server_folder::show_file(%s): the fid parameter does not contain a valid object id!"), $fid)
		));
		$o = obj($oid);
		$fname = urldecode($fname);
		$fqfn = $o->prop("folder")."/".basename($fname);
		if (!file_exists($fqfn))
		{
			$fqfn = $o->prop("folder")."/".urlencode(basename($fname));
		}
		
		$mt = get_instance("core/aw_mime_types");
		
		header("Content-type: ".$mt->type_for_file($fname));
		header("Content-Disposition: filename=".urlencode($fname));
		header("Pragma: no-cache");
		readfile($fqfn);
		die();
	}

	////
	// !deletes the given file from the server
	function del_file($fid)
	{
		list($oid, $fname) = explode(":", $fid);
		error::raise_if(!is_oid($oid), array(
			"id" => ERR_PARAM,
			"msg" => sprintf(t("server_folder::del_file(%s): the fid parameter does not contain a valid object id!"), $fid)
		));
		$o = obj($oid);
		$fqfn = $o->prop("folder")."/".basename($fname);

		@unlink($fqfn);
	}

	/** lets the user modify a file

		@attrib name=change_file params=name

		@param fid required
		@param section optional
	**/
	function change_file($arr)
	{
		$arr["form"] = "ch_file";
		return $this->change($arr);
	}

	function init_chfile($arr)
	{
		extract($arr);
		list($oid, $fname) = explode(":", $fid);

		error::raise_if(!is_oid($oid), array(
			"id" => ERR_PARAM,
			"msg" => sprintf(t("server_folder::change_file(%s): the fid parameter does not contain a valid object id!"), $fid)
		));
		$o = obj($oid);
		$fqfn = $o->prop("folder")."/".basename($fname);

		$this->obj_inst = $o;
		$this->fqfn = $fqfn;
		$this->fname = $fname;
		$this->fid = $fid;
	}

	/** saves the file to the server. called from classbase submit action
		
		@attrib params=name name=submit_chfile

	**/
	function submit_chfile($arr)
	{
		extract($arr);

		list($oid, $fname) = explode(":", $fid);
		error::raise_if(!is_oid($oid), array(
			"id" => ERR_PARAM,
			"msg" => sprintf(t("server_folder::change_file(%s): the fid parameter does not contain a valid object id!"), $fid)
		));
		$o = obj($oid);
		$old_fqfn = $o->prop("folder")."/".urldecode(basename($fname));
		$new_fqfn = $o->prop("folder")."/".urldecode(basename($_FILES["chf_file"]["name"]));

		if (is_uploaded_file($_FILES["chf_file"]["tmp_name"]))
		{
			move_uploaded_file($_FILES["chf_file"]["tmp_name"], $new_fqfn);
			@unlink($old_fqfn);
			$fid = $oid.":".basename($_FILES["chf_file"]["name"]);
		}

		return $this->mk_my_orb("change_file", array("fid" => $fid, "section" => $section));
	}

	function get_property($arr)
	{
		$prop =& $arr["prop"];

		switch($prop["name"])
		{
			case "chf_view_file":
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("show_file", array("fid" => $arr["request"]["fid"])),
					"caption" => $this->fname
				));
				break;
		}
		return PROP_OK;
	}

	function callback_mod_reforb($arr)
	{
		if ($this->fid)
		{
			$arr["fid"] = $this->fid;
		}

		if ($this->id)
		{
			$arr["id"] = $this->id;
		}
	}

	/** lets the user add a file to the server folder

		@attrib name=add_file

		@param id required type=int
		@param section optional type=int

	**/
	function add_file($arr)
	{
		$arr["form"] = "add_file";
		return $this->change($arr);
	}

	function init_addfile($arr)
	{
		extract($arr);
		$this->id = $id;
	}


	/** saves the new file to the server
		
		@attrib params=name name=submit_addfile

	**/
	function submit_addfile($arr)
	{
		extract($arr);

		error::raise_if(!is_oid($id), array(
			"id" => ERR_PARAM,
			"msg" => sprintf(t("server_folder::submit_addfile(%s): the id parameter does not contain a valid object id!"), $id)
		));

		$o = obj($id);
		$fqfn = $o->prop("folder")."/".urldecode(basename($_FILES["addf_file"]["name"]));

		if (is_uploaded_file($_FILES["addf_file"]["tmp_name"]))
		{
			move_uploaded_file($_FILES["addf_file"]["tmp_name"], $fqfn);
		}

		$fid = $id.":".urlencode($_FILES["addf_file"]["name"]);
		return $this->mk_my_orb("change_file", array("fid" => $fid, "section" => $section));
	}
}
?>
