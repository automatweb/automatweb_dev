<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_product_table_layout.aw,v 1.8 2004/12/27 12:31:54 kristo Exp $
// shop_product_table_layout.aw - Lao toodete tabeli kujundus 
/*

@classinfo syslog_type=ST_SHOP_PRODUCT_TABLE_LAYOUT relationmgr=yes no_status=1

@default table=objects
@default group=general

@property columns type=textbox size=5 field=meta method=serialize
@caption Tulpi

@property rows type=textbox size=5 field=meta method=serialize
@caption Ridu

@property per_page type=textbox size=5 field=meta method=serialize
@caption Tooteid lehel

@property template type=select field=meta method=serialize
@caption Template


*/

class shop_product_table_layout extends class_base
{
	function shop_product_table_layout()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_product_table_layout",
			"clid" => CL_SHOP_PRODUCT_TABLE_LAYOUT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "template":
				$tm = get_instance("templatemgr");
				$prop["options"] = $tm->template_picker(array(
					"folder" => "applications/shop/shop_product_table_layout"
				));
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

		}
		return $retval;
	}	

	/** starts drawing a table

		@comment
	
			$t - product_table_layout srtorage object
			$oc - shop_order_center srtorage object
	**/
	function start_table($t, $oc)
	{
		$this->t = $t;
		$this->oc = $oc;
		$this->cnt = 0;
		$tpl = "table.tpl";
		if ($t->prop("template") != "")
		{
			$tpl = $t->prop("template");
		}
		$this->read_template($tpl);

		$soce = new aw_array(aw_global_get("soc_err"));
		foreach($soce->get() as $prid => $errmsg)
		{
			if (!$errmsg["is_err"])
			{
				continue;
			}

			$this->vars(array(
				"msg" => $errmsg["msg"],
				"prod_name" => $errmsg["prod_name"],
				"prod_id" => $errmsg["prod_id"],
				"must_order_num" => $errmsg["must_order_num"],
				"ordered_num" => $errmsg["ordered_num"]
			));
			$err .= $this->parse("ERROR");
		}

		aw_session_del("soc_err");

		$this->vars(array(
			"ERROR" => $err
		));

		$this->r_template = "ROW";
		$this->r_cnt = 1;
		if ($this->is_template("ROW1"))
		{
			$this->r_template = "ROW1";
		}

		$this->per_page = $this->t->prop("columns") * $this->t->prop("rows");
	}

	function is_on_cur_page()
	{
		if ($this->is_template("PAGE"))
		{
			$from = $this->per_page * (int)$_GET["sptlp"];
			$to = $this->per_page * ((int)$_GET["sptlp"]+1);

			if (!($this->cnt >= $from && $this->cnt < $to))
			{
				return false;;
			}
		}
		return true;
	}

	/** adds a product to the product table
	**/
	function add_product($p_html)
	{
		if ($this->is_template("PAGE"))
		{
			$from = $this->per_page * (int)$_GET["sptlp"];
			$to = $this->per_page * ((int)$_GET["sptlp"]+1);

			if (!($this->cnt >= $from && $this->cnt < $to))
			{
				$this->cnt++;
				return;
			}
		}

		if (($this->cnt % $this->t->prop("columns")) == 0)
		{
			$this->r_template = "ROW";
			if ($this->is_template("ROW1"))
			{	
				if (($this->r_cnt % 2) == 0)
				{
					$this->r_template = "ROW2";
				}
				else
				{
					$this->r_template = "ROW1";
				}
			}
			
			$this->vars(array(
				"COL" => $this->t_str
			));
			$this->ft_str .= $this->parse($this->r_template);
			$this->t_str = "";
			$this->r_cnt++;
		}
	
		$this->vars(array(
			"product" => $p_html
		));
		$this->t_str .= $this->parse($this->r_template.".COL");
		$this->cnt++;
	}

	/** returns the html for the product table
	**/
	function finish_table()
	{
		$this->vars(array(
			"COL" => $this->t_str
		));

		$hi = "";
		if ($this->cnt > 0)
		{
			$hi = $this->parse("HAS_ITEMS");
		}

		$so = obj(aw_global_get("section"));
		if ($so->class_id() != CL_MENU)
		{
			$so = obj($so->parent());
		}
		$this->ft_str .= $this->parse($this->r_template);
		$this->vars(array(
			"ROW" => $this->ft_str,
			"ROW1" => $this->ft_str,
			"ROW2" => "",
			"reforb" => $this->mk_reforb("submit_add_cart", array("section" => aw_global_get("section"), "oc" => $this->oc->id(), "return_url" => aw_global_get("REQUEST_URI")), "shop_order_cart"),
			"HAS_ITEMS" => $hi,
			"sel_menu_text" => $so->name()
		));
		$this->draw_pageselector();
		return $this->parse();
	}

	function draw_pageselector()
	{
		if (!$this->t->prop("columns") || !$this->t->prop("rows"))
		{
			return;
		}

		$cur_page = $_GET["sptlp"];
		$num_pages = $this->cnt / $this->per_page;

		$pgs = array();
		for($i = 0; $i < $num_pages;  $i++)
		{
			$this->vars(array(
				"page_link" => aw_url_change_var("sptlp", $i),
				"page_number" => $i+1
			));
			if ($cur_page == $i)
			{
				$pgs[] = $this->parse("PAGE_SEL");
			}
			else
			{
				$pgs[] = $this->parse("PAGE");
			}

			if ($cur_page > 0 && ($cur_page-1) == $i)
			{
				$this->vars(array(
					"PREV_PAGE" => $this->parse("PREV_PAGE")
				));
			}

			if ($cur_page < ($num_pages-1) && ($cur_page+1) == $i)
			{
				$this->vars(array(
					"NEXT_PAGE" => $this->parse("NEXT_PAGE")
				));
			}
		}

		$this->vars(array(
			"PAGE" => join(" ".trim($this->parse("PAGE_SEP"))." ", $pgs),
			"PAGE_SEL" => "",
		));

		if ($num_pages > 1)
		{
			$this->vars(array(
				"HAS_PAGES" => $this->parse("HAS_PAGES"),
				"HAS_PAGES2" => $this->parse("HAS_PAGES2")
			));
		}
	}
}
?>
