<?php

classload("file");
class aip_file extends file
{
	function aip_file()
	{
		$this->file();
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
			"rootmenu" => get_root(),
			"YAH_LINK" => mk_yah_link($fi["parent"], $this),
			"toolbar" => make_toolbar($fi["parent"], $this, "javascript:document.a.submit()"),
		));
		return $this->parse();
	}
}
?>