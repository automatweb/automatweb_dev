<?php

global $orb_defs;
$orb_defs["shop_item"] = "xml";

class shop_item extends aw_template
{
	function shop_item()
	{
		$this->tpl_init("shop");
		$this->db_init();
		$this->sub_merge = 1;
	}

	////
	// !asks the user which form to use for adding an item and then shows the form
	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent, "Lisa kaup");
		if ($step < 2)
		{
			$this->read_template("add_item_form.tpl");
			classload("form_base");
			$fb = new form_base;

			$op_list = $fb->get_op_list();

			$fl = $fb->get_list(FTYPE_ENTRY);
			reset($fl);
			while (list($id,) = each($fl))
			{
				$this->vars(array("form_id" => $id));
				if (is_array($op_list[$id]))
				{
					reset($op_list[$id]);
					$cnt = 0;
					$fop = "";
					while (list($op_id,$op_name) = each($op_list[$id]))
					{
						$this->vars(array("cnt" => $cnt, "op_id" => $op_id, "op_name" => $op_name));
						$fop.=$this->parse("FORM_OP");
						$cnt++;
					}
					$this->vars(array("FORM_OP" => $fop));
					$this->parse("FORM");
				}
			}

			$this->vars(array(
				"reforb" => $this->mk_reforb("new", array("parent" => $parent,"reforb" => 0,"step" => 2)),
				"flist" => $this->picker(0,$fl)
			));
			return $this->parse();
		}
		else
		{
			classload("form");
			$f = new form;
			return $f->gen_preview(array(
				"id" => $form_id,
				"reforb" => $this->mk_reforb("submit", array("parent" => $parent, "fid" => $form_id,"op_id" => $op_id))
			));
		}
	}

	////
	// !saves the data
	function submit($arr)
	{
		extract($arr);
		classload("form");
		$f = new form;

		if ($id)
		{
			$o = $this->get($id);
			$f->process_entry(array("id" => $o["form_id"],"entry_id" => $o["entry_id"]));
			$this->upd_object(array("oid" => $id, "name" => $f->get_element_value_by_name("nimi")));
			$price = $f->get_element_value_by_type("price");
			$this->db_query("UPDATE shop_items SET price='$price' WHERE id = $id");
		}
		else
		{
			$f->process_entry(array("id" => $fid));
			$eid = $f->entry_id;

			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_SHOP_ITEM, "status" => 2, "name" => $f->get_element_value_by_name("nimi")));
			$price = $f->get_element_value_by_type("price");
			$this->db_query("INSERT INTO shop_items(id,form_id,entry_id,op_id,price) values($id,'$fid','$eid','$op_id','$price')");
		}
		return $this->mk_orb("change", array("id" => $id));
	}

	////
	// !shows the form for changing the data
	function change($arr)
	{
		extract($arr);
		$o = $this->get($id);
		$this->mk_path($o["parent"],"Muuda kaupa");

		classload("form");
		$f = new form;
		return $f->gen_preview(array(
			"id" => $o["form_id"],
			"entry_id" => $o["entry_id"],
			"reforb" => $this->mk_reforb("submit", array("id" => $id))
		));
	}

	function get($id)
	{
		$this->db_query("SELECT objects.*,shop_items.* FROM objects LEFT JOIN shop_items ON shop_items.id = objects.oid WHERE objects.oid = $id");
		return $this->db_next();
	}
}
?>