<?php

global $orb_defs;
$orb_defs["item_type"] = "xml";

classload("shop_base");
class item_type extends shop_base
{
	function item_type()
	{
		$this->shop_base();
		lc_load("shop");
		global $lc_shop;
		if (is_array($lc_shop))
		{
			$this->vars($lc_shop);
		}
		lc_load("definition");
	}

	////
	// !shows form for adding new item type under $parent
	function add($arr)
	{
		extract($arr);
		$this->read_template("add_item_type.tpl");
		$this->mk_path($parent, LC_ITEM_TYPE_ADD);

		classload("form_base");
		$fb = new form_base;

		$op_list = $fb->get_op_list();

		$fl = $fb->get_list(FTYPE_ENTRY);
		reset($fl);
		while (list($id,) = each($fl))
		{
			if (!$form_id)
			{
				$form_id = $id;
			}
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
			"form_id" => $form_id,
			"cnt_form_id" => $form_id,
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent)),
			"flist" => $this->picker(0,$fl),
			"eqs" => $this->picker(0,$this->listall_eqs())
		));
		return $this->parse();
	}

	////
	// !creates and/or saves the item type
	function submit($arr)
	{
		extract($arr);

		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name));
			$this->db_query("UPDATE shop_item_types set form_id = '$form_id' , short_op = '$op_id', long_op = '$op_id_l', cart_op = '$op_id_cart', cnt_form = '$cnt_form' , cnt_op = '$cnt_form_op',eq_id = '$eq', has_voucher = '$has_voucher' WHERE id = $id");
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent, "name" => $name, "class_id" => CL_SHOP_ITEM_TYPE));
			$this->db_query("INSERT INTO shop_item_types(id,form_id, short_op, long_op, cart_op, cnt_form, cnt_op,eq_id,has_voucher) values($id,'$form_id','$op_id','$op_id_l','$op_id_cart','$cnt_form','$cnt_form_op','$eq','$has_voucher')");
		}

		return $this->mk_orb("change", array("id" => $id));
	}

	////
	// !shows change form for item type $id
	function change($arr)
	{
		extract($arr);
		$itt = $this->get_item_type($id); 
		$tid = $id;
		$this->mk_path($itt["parent"], LC_ITEM_TYPE_CHANGE);
		$this->read_template("add_item_type.tpl");

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

		$es = $this->listall_eqs();
		$es[0] = "";
		$this->vars(array(
			"form_id" => $itt["form_id"],
			"cnt_form_id" => $itt["cnt_form"],
			"op_id" => $itt["short_op"],
			"op_id_l" => $itt["long_op"],
			"op_id_cart" => $itt["cart_op"],
			"cnt_op_id" => $itt["cnt_op"],
			"reforb" => $this->mk_reforb("submit", array("id" => $tid)),
			"flist" => $this->picker(0,$fl),
			"name" => $itt["name"],
			"eqs" => $this->picker($itt["eq_id"],$es),
			"has_voucher" => checked($itt["has_voucher"])
		));
		$this->parse("CHANGE");
		return $this->parse();
	}

	function mk_cache()
	{
		$this->type_cache = $this->listall_item_types(ALL_PROPS);
	}
}

?>