<?php

classload("file");
class aip_file extends file
{
	function aip_file()
	{
		$this->file();
	}

	////
	// !Kuvab faili lisamise vormi
	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent,LC_FILE_ADD_FILE);
		// kui messenger argument on seatud, siis submit_add peaks tagasi messengeri minema
		if ($msg_id)
		{
			$tpl = "attach.tpl";
		}
		else
		{
			$tpl = ($arr["tpl"]) ? $arr["tpl"] : "upload.tpl";
		};

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

	function submit_change($arr)
	{
		parent::submit_change($arr);
		return $this->mk_my_orb("change",array("id" => $arr['id']),'',false,true);
	}

	////
	// !Lisab faili lisamisvormist tulnud info pohjal
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

			if ($msg_id)
			{
				classload("messenger");
				$messenger = new messenger();
				$row = array();
				$row["type"] = $file_type;
				$row["content"] = $fc;
				$row["class_id"] = CL_FILE;
				$row["name"] = $file_name;
				$ser = serialize($row);
				$this->quote($ser);
				$messenger->attach_serialized_object(array(
					"msg_id" => $msg_id,
					"data" => $ser,
				));
			}
			else
			{
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

				// id on dokumendi ID, kui fail lisatakse doku juurde
				// add_alias teeb voimalikus #fn# tagi kasutamise doku kuvamise juures
				if ($id)
				{
					$this->add_alias($id,$pid);
				}
			};

			// defineerime voimalikud orb-i väärtused siin ära

			// parent on menüü
			$orb_urls = array(
				// saidi seest lisati doku juurde fail
				"user" => $this->mk_site_orb(array("class" => "document","action" => "change","id" => $id)),

				// aw-st lisati doku juurde fail
				"awdoc" => $this->mk_my_orb("list_aliases", array("id" => $id), "aliasmgr"),

				// menueditist lisati fail
				"awfile" => $this->mk_my_orb("obj_list", array("parent" => $parent), "menuedit"),

				// messengeri külge attachitud fail
				"messenger" => $this->mk_site_orb(array("class" => "messenger","action" => "edit","id" => $msg_id)),

				// saidi poole pealt uploaditi kodukataloogi fail
				"site" => $this->mk_site_orb(array("class" => "homedir","action" => "gen_home_dir","id" => $parent)),
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
			if (strpos(aw_global_get("REQUEST_URI"),"automatweb") === false)
			{
				$retval = $orb_urls["site"];
			}
			elseif ($msg_id)
			{
				$retval = $orb_urls["messenger"];
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
