<?php

class products_show_obj extends _int_object
{
	public function get_template()
	{
		if($this->prop("template"))
		{
			return $this->prop("template");
		}
		return "show.tpl";
	}

	public function get_products()
	{
		$ol = new object_list();
		foreach($this->prop("categories") as $category)
		{
			$c = obj($category);
			$ol->add($c->get_products());
		}
		return $ol;
	}

	public function get_web_items()
	{
		$ol = new object_list();
		foreach($this->prop("categories") as $category)
		{
			$c = obj($category);
			

			if(false)
			{
				$products = $c->get_packagings();
			}
			elseif(false)
			{
				$products = $c->get_products();
			}
			else
			{
				$products = $c->get_packets();
			}

			$ol->add($products);

		}
		return $ol;


	}

	public function get_oc()
	{
		$ol = new object_list(array("class_id" => CL_SHOP_ORDER_CENTER));

		return reset($ol->arr());
	}

	public function get_categories()
	{
		$ol = new object_list();	
		foreach($this->prop("categories") as $category)
		{
			$ol->add($category);
		}
		return $ol;
	}

	public function add_category($cat)
	{
		if(is_oid($cat))
		{
			$cat = array($cat);
		}
		foreach($cat as $category)
		{
			$this->connect(array(
				"to" => $category,
				"reltype" => "RELTYPE_CATEGORY",
			));
		}
		return true;
	}

	public function remove_category($cat)
	{
		$this->disconnect(array(
			"from" => $cat,
		));
	}

}

?>
