<?php
/*
@classinfo syslog_type=ST_PRODUCTS_SHOW relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=smeedia
@tableinfo aw_products_show master_index=brother_of master_table=objects index=aw_oid

@default table=aw_products_show
@default group=general


@property categories type=relpicker multiple=1 store=connect reltype=RELTYPE_CATEGORY 
@caption Tootekategooriad
@comment Tootekategooriad millesse toode peaks kuuluma, et teda kuvataks

@property template type=select
@caption Toodete n&auml;itamise template

@property type type=select
@caption N&auml;idatavad klassi t&uuml;&uuml;bid

@reltype CATEGORY value=1 clid=CL_SHOP_PRODUCT_CATEGORY
@caption Tootekategooria



*/

class products_show extends class_base
{
	function products_show()
	{
		$this->init(array(
			"tpldir" => "applications/shop/products_show",
			"clid" => CL_PRODUCTS_SHOW
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
					"folder" => "applications/shop/products_show/"
				));
				if(sizeof($prop["options"]) < 2)
				{
					$prop["caption"].= "<br>".t("templates/applications/shop/products_show/");
				}
				break;
			case "type":
				$prop["options"] = array(
					CL_SHOP_PRODUCT => t("Toode"),
					CL_SHOP_PRODUCT_PACKAGING => t("Pakend"),
					CL_SHOP_PACKET => t("Pakett"),
				);
				break;
		}

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

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_products_show(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "template":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "varchar(63)"
				));
				return true;
			case "type":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}

	function parse_alias($arr = array())
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		
		$this->read_template($ob->get_template());
		$this->vars(array(
			"name" => $ob->prop("name"),
		));

		$products = $ob->get_web_items();
		$oc = $ob->get_oc();
		$prod = "";//templeiti muutuja PRODUCT v22rtuseks

		foreach($products->arr() as $product)
		{
			$product_data = $product->get_data();

			preg_match  ( "/.*src='(.*)'.*$/imsU", $product_data["image"], $mt );
			$product_data["image_url"] = $mt[1];
			$product_data["checkbox"] = html::checkbox(array(
				"name" => "add_to_cart[".$product_data["product_id"]."]",
				"value" => 1,
			));
			$this->vars($product_data);
			$prod.=$this->parse("PRODUCT");
		}

		$data = array();
		$data["PRODUCT"] = $prod;
		$cart_inst = get_instance(CL_SHOP_ORDER_CART);
 		$data["submit_url"] = $this->mk_my_orb("submit_add_cart", array(
			"oc" => $oc->id(),
			"id" => $oc->prop("cart"),
		),CL_SHOP_ORDER_CART,false,false,"&amp;");

		if(!substr_count("orb.aw" ,$data["submit_url"] ))
		{
			$data["submit_url"] = str_replace(aw_ini_get("baseurl")."/" ,aw_ini_get("baseurl")."/orb.aw" , $data["submit_url"]);

		}
		$data["oc"] = $oc->id();
		$data["submit"] = html::submit(array(
			"value" => t("Lisa tooted korvi"),
		));

		$this->vars($data);
		return $this->parse();
	}
}

?>
