<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_product_table_layout.aw,v 1.3 2004/05/19 16:07:11 kristo Exp $
// shop_product_table_layout.aw - Lao toodete tabeli kujundus 
/*

@classinfo syslog_type=ST_SHOP_PRODUCT_TABLE_LAYOUT relationmgr=yes no_status=1

@default table=objects
@default group=general

@property columns type=textbox size=5 field=meta method=serialize
@caption Tulpi

@property rows type=textbox size=5 field=meta method=serialize
@caption Ridu

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
		$this->read_template("table.tpl");

		$soce = new aw_array(aw_global_get("soc_err"));
		foreach($soce->get() as $prid => $errmsg)
		{
			$this->vars(array(
				"msg" => $errmsg
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
	}

	/** adds a product to the product table
	**/
	function add_product($p_html)
	{
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
		$this->ft_str .= $this->parse($this->r_template);
		$this->vars(array(
			"ROW" => $this->ft_str,
			"ROW1" => $this->ft_str,
			"ROW2" => "",
			"reforb" => $this->mk_reforb("submit_add_cart", array("section" => aw_global_get("section"), "oc" => $this->oc->id(), "return_url" => aw_global_get("REQUEST_URI")), "shop_order_cart")
		));
		return $this->parse();
	}
}
?>
