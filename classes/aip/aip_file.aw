<?php

classload("file");
class aip_file extends file
{
	function aip_file()
	{
		$this->file();
	}

	/** Kuvab faili lisamise vormi 
		
		@attrib name=new params=name default="0"
		
		@param parent optional
		@param id optional
		@param msg_id optional
		@param return_url optional
		@param alias_to optional
		
		@returns
		
		
		@comment

	**/
	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent,LC_FILE_ADD_FILE);
		$tpl = ($arr["tpl"]) ? $arr["tpl"] : "upload.tpl";

		$this->read_template($tpl);

		load_vcl("date_edit");
		$de = new date_edit("act_time");
		$de->configure(array(
			"year" => 1,
			"month" => 1,
			"day" => 1,
			"hour" => 1,
			"minute" => 1,
			"classid" => "formselect"
		));

		$this->vars(array(
			"act_date" => $de->gen_edit_form("act_time", time()),
			"j_date" => $de->gen_edit_form("j_time", time()),
			"reforb" => $this->mk_reforb("submit_add", array(
				"id" => $id,
				"msg_id" => $msg_id,
				"parent" => $parent,
				"return_url" => $return_url,
				"user" => $user
			))
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=change params=name default="0"
		
		@param id required
		@param parent optional
		@param return_url optional
		@param doc optional
		
		@returns
		
		
		@comment

	**/
	function change($arr)
	{
		extract($arr);
		$this->read_template("edit.tpl");
		$fi = $this->get_file_by_id($id);
		$this->mk_path($parent, LC_FILE_CHANGE_FILE);

		load_vcl("date_edit");
		$de = new date_edit("act_time");
		$de->configure(array(
			"year" => 1,
			"month" => 1,
			"day" => 1,
			"hour" => 1,
			"minute" => 1,
			"classid" => "formselect"
		));

		$this->vars(array(
			"reforb"	=> $this->mk_reforb("submit_change",array("id" => $id, "parent" => $parent,"doc" => $doc,"user" => $user,"return_url" => $return_url)),
			"act_date" => $de->gen_edit_form("act_time", $fi["meta"]["act_time"]),
			"j_date" => $de->gen_edit_form("j_time", $fi["meta"]["j_time"]),
			"comment" => $fi["comment"],
			"checked" => checked($fi["showal"]), 
			"show_framed" => checked($fi["meta"]["show_framed"]),
			"newwindow" => checked($fi["newwindow"]),
			"rootmenu" => aip::get_root(),
			"YAH_LINK" => aip::mk_yah_link($fi["parent"], $this),
			"toolbar" => make_toolbar($fi["parent"], $this, "javascript:document.a.submit()"),
		));
		return $this->parse();
	}
        ////
        // !Salvestab muudatused
        function _submit_change($arr)
        {
                extract($arr);
                global $file, $file_type,$file_name;

                load_vcl("date_edit");
                $de = new date_edit("act_time");

                if (!is_uploaded_file($file)) 
                {
                        // uut failinime ei määratud, muudame infot
                        $this->save_file(array(
                                "file_id" => $id,
                                "comment" => $comment,
                                "showal" => $show,
                                "newwindow" => $newwindow,
                                "show_framed" => $show_framed,
                                "act_time" => $de->get_timestamp($act_time),
                                "j_time" => $de->get_timestamp($j_time)
                        ));
                }
                else
                {
                        $pid = $this->save_file(array(
                                "file_id" => $id,
                                "name" => $file_name,
                                "comment" => $comment,
                                "content" => $this->get_file(array("file" => $file)),
                                "showal" => $show,
                                "type" => $file_type,
                                "newwindow" => $newwindow,
                                "act_time" => $de->get_timestamp($act_time),
                                "j_time" => $de->get_timestamp($j_time),
                                "show_framed" => $show_framed,
                        ));
                }

                // Probleemikoht. Mis siis, kui ma tahan monda teise kohta minna peale submitti?
                $obj = $this->get_object($id);
                $parent = $obj["parent"];
                if ($return_url != "")
                {
                        $retval = $return_url;
                }
                else
                if ($doc)
                {
                        $retval = $this->mk_my_orb("change", array("id" => $doc),"document");
                }
                else
                {
                        if ($GLOBALS["user"])
                        {
                                //$retval = $this->mk_my_orb("gen_home_dir", array("id" => $parent),"users");
                                $retval = $this->mk_my_orb("browse", array("id" => $parent),"manager",false,1);
                        }
                        else
                        {
                                //$retval = $this->mk_my_orb("obj_list", array("parent" => $parent),"menuedit");
                                $retval = $this->mk_my_orb("change",array("id" => $id));
                        }
                };
                return $retval;
        }


	/**  
		
		@attrib name=submit_change params=name default="0"
		
		@param parent required
		@param id required
		
		@returns
		
		
		@comment

	**/
	function submit_change($arr)
	{
		$this->_submit_change($arr);
		return $this->mk_my_orb("change",array("id" => $arr['id']),'',false,true);
	}

	/** Lisab faili lisamisvormist tulnud info pohjal 
		
		@attrib name=submit_add params=name default="0"
		
		@param parent required
		@param id optional
		
		@returns
		
		
		@comment

	**/
	function submit_add($arr)
	{
		extract($arr);
		// $file, $file_type ja $file_name on special muutujad,
		// mis tekitatakse php poolt faili uploadimisel
		global $file, $file_type,$file_name;

		if (is_uploaded_file($file))
		{
			// fail sisse
			$fc = $this->get_file(array(
				"file" => $file,
			));

			load_vcl("date_edit");
			$de = new date_edit("act_time");

			$pid = $this->save_file(array(
				"parent" => $parent,
				"name" => $file_name,
				"comment" => $comment,
				"content" => $fc,
				"showal" => $show,
				"type" => $file_type,
				"newwindow" => $newwindow,
				"j_time" => $de->get_timestamp($j_time)
			));

			$this->db_query("INSERT INTO aip_files(id,filename,tm,menu_id) VALUES($pid,'$file_name','".time()."','$parent')");
			die("inserted !! $file_name <br>");
			// id on dokumendi ID, kui fail lisatakse doku juurde
			// add_alias teeb voimalikus #fn# tagi kasutamise doku kuvamise juures
			if ($id)
			{
				$this->add_alias($id,$pid);
			}

			// defineerime voimalikud orb-i väärtused siin ära

			// parent on menüü
			$orb_urls = array(
				// aw-st lisati doku juurde fail
				"awdoc" => $this->mk_my_orb("list_aliases", array("id" => $id), "aliasmgr"),

				// menueditist lisati fail
				"awfile" => $this->mk_my_orb("obj_list", array("parent" => $parent), "menuedit"),

			);

			if ($return_url != "")
			{
				$retval = $return_url;
			}
			else
			if ($id)
			{
				// $user argument tähendab, et request tuli saidi seest
				// ja vastavalt sellele suuname kliendi ringi
				$retval = ($user) ? $orb_urls["user"] : $orb_urls["awdoc"];
			}
			else
			{
				$retval = $orb_urls["awfile"];
			}
		} 
		else 
		{
			// Sellist faili polnud. Voi tekkis mingi teine viga
			print LC_FILE_SOME_IS_WRONG;
			$retval = array();
		};
		return $this->mk_my_orb("change",array("id" => $pid),'',false,true);
	}
}
?>
