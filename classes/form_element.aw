<?php

global $orb_defs;
$orb_defs["form_element"] = 
array("change"	=> array("function"	=> "change",	"params"	=> array("id")));

class form_element extends aw_template
{
	function form_element()
	{
		$this->db_init();
	}

	////
	// !Loads the element from the array from inside the form
	function load(&$arr,$fid,$col,$row)
	{
		$this->arr = $arr;
		$this->id = $arr[id];
		$this->fid = $fid;
		$this->col = $col;
		$this->row = $row;
	}

	function gen_check_html()
	{
		if ($this->arr["type"] == "textbox" && $this->arr["must_fill"] == 1)
		{
			$str = "for (i=0; i < document.fm_".$this->fid.".elements.length; i++) ";
			$str .= "{ if (document.fm_".$this->fid.".elements[i].name == \"";
			$str .=$this->id;
			$str .= "\" && document.fm_".$this->fid.".elements[i].value == \"\")";
			return  $str."{ alert(\"".$this->arr["must_error"]."\");return false; }}\n";
		}
		else
		if ($this->arr["type"] == "listbox" && $this->arr["must_fill"] == 1)
		{
			$str = "for (i=0; i < document.fm_".$this->fid.".elements.length; i++) ";
			$str .= "{ if (document.fm_".$this->fid.".elements[i].name == \"";
			$str .=$this->id;
			$str .= "\" && document.fm_".$this->fid.".elements[i].selectedIndex == 0)";
			return  $str."{ alert(\"".$this->arr["must_error"]."\");return false; }}\n";
		}
		return "";
	}
	function get_text()		{	return $this->arr["text"]; }
	function get_el_name()		{	return $this->arr["name"]; }
	function get_style()	{	return $this->arr["style"]; }
	function get_type()		{	return $this->arr["type"]; }
	function get_subtype()		{	return $this->arr["subtype"]; }
	function get_srow_grp()		{	return $this->arr["srow_grp"]; }
	function get_id()			{ return $this->id;	}
	function get_order()	{ return $this->arr["ord"]; }
	function get_acl()		{ return $this->acl; }
	function get_props()  { return $this->arr; }

	function save_short()
	{
		$var = "element_".$this->id."_text";
		global $$var;
		$this->arr["text"] = $$var;
		$this->dequote(&$this->arr["text"]);

		$var = "element_".$this->id."_order";
		global $$var;
		$$var+=0;
		if ($this->arr["ord"] != $$var)
		{
			$this->arr["ord"] = $$var;
			$this->upd_object(array("oid" => $this->id, "jrk" => $$var));
		}
	}

	// this function deletes the element. called from $this->save();
	function del()
	{
	}

	function gen_action_html()
	{
		$this->read_template("admin_element_actions.tpl");
		$this->vars(array("element_id" => "element_".$this->id, "email" => $this->arr[email], "element_text" => $this->arr[text]));
		return $this->parse();
	}

	function set_style($id)
	{
		$this->arr[style] = $id;
	}

	function set_entry(&$arr, $e_id)
	{
		$this->entry = $arr[$this->id];
		$this->entry_id = $e_id;
	}

	function set_mark($mk)
	{
		$this->arr[mark] = $mk;
	}

	function gen_controller_html()
	{
		$this->read_template("admin_element_controllers.tpl");
		$tt = "";
		if ($this->arr[type] == "textbox")
		{
			$this->vars(array("el_maxlen"			=> $this->arr[c_maxlen],
												"el_minlen"			=> $this->arr[c_minlen],
												"t_email_sel"		=> ($this->arr[c_type] == "email" ? "SELECTED" : ""),
												"t_url_sel"			=> ($this->arr[c_type] == "url" ? "SELECTED" : ""),
												"t_number_sel"	=> ($this->arr[c_type] == "number" ? "SELECTED" : ""),
												"t_letter_sel"	=> ($this->arr[c_type] == "letter" ? "SELECTED" : ""),
												"el_req_sel"		=> ($this->arr[c_req] == 1 ? "CHECKED" : "" )));
			$tt = $this->parse("T_TEXTBOX");
		}
		$this->vars(array("element_text"	=> $this->arr[text], 
											"element_type"	=> $this->arr[type],
											"T_TEXTBOX"			=> $tt));
		return $this->parse();
	}

	function change($arr)
	{
		extract($arr);
		global $ext;

		$this->db_query("SELECT row,col,form_id FROM form_elements WHERE id = $id");
		$row = $this->db_next();
		header("Location: forms.$ext?type=change_form_cell&f_id=".$row[form_id]."&col=".$row[col]."&row=".$row[row]."#el_".$id);
		die();
	}

	function change_pos($arr,&$f)
	{
		$this->read_template("change_pos.tpl");
		$o = new db_objects;
		$obj = $this->get_object($this->id);
		$this->vars(array("reforb" => $this->mk_reforb("submit_chpos", array("id" => $this->fid, "col" => $this->col, "row" => $this->row, "el_id" => $this->id), "form"),
											"folders"	=> $this->picker($obj[parent], $o->get_list()),
											"name"		=> $this->arr[name]));

		for ($row = 0; $row < $f->arr[rows]; $row++)
		{
			$c = "";
			for ($col = 0; $col < $f->arr[cols]; $col++)
			{
				$this->vars(array("row" => $row, "col" => $col, "checked" => checked($this->col == $col && $this->row == $row)));
				$c.=$this->parse("COL");
			}
			$this->vars(array("COL" => $c));
			$l.=$this->parse("ROW");
		}
		$this->vars(array("ROW" => $l));
		return $this->parse();
	}
}
?>