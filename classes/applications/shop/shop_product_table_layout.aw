<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_product_table_layout.aw,v 1.1 2004/04/13 12:36:34 kristo Exp $
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
	}

	/** adds a product to the product table
	**/
	function add_product($p_html)
	{
		if (($this->cnt % $this->t->prop("columns")) == 0)
		{
			$this->vars(array(
				"COL" => $this->t_str
			));
			$this->ft_str .= $this->parse("ROW");
			$this->t_str = "";
		}
	
		$this->vars(array(
			"product" => $p_html
		));
		$this->t_str .= $this->parse("COL");
		$this->cnt++;
	}

	/** returns the html for the product table
	**/
	function finish_table()
	{
		$this->vars(array(
			"COL" => $this->t_str
		));
		$this->ft_str .= $this->parse("ROW");
		$this->vars(array(
			"ROW" => $this->ft_str,
			"reforb" => $this->mk_reforb("submit_add_cart", array("oc" => $this->oc->id()), "shop_order_cart")
		));
		return $this->parse();
	}
}
?>
