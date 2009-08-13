<?php

class shop_packet_obj extends _int_object
{
	public function get_products()
	{
		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT,
			"lang_id" => array(),
			"site_id" => array(),
			"CL_SHOP_PRODUCT.RELTYPE_PRODUCT(CL_SHOP_PACKET)" => $this->id()
		));
		return $ol;
	}
	
	public function get_categories()
	{
		$ol = new object_list();
		foreach($this->connections_from(array(
			"type" => "RELTYPE_CATEGORY",

		)) as $c)
		{
			$ol->add($c->prop("to"));;
		}
		return $ol;
	}

	private function random_product_id()
	{
		$product = $this->get_first_obj_by_reltype("RELTYPE_PRODUCT");
		if(is_object($product))
		{
			return $product->id();
		}
		return null;
	}

	public function get_data()
	{
		$data = $this->properties();
		$data["id"] = $this->id();
		$data["product_id"] = $this->random_product_id();
		$data["image"] = $this->get_image();
		$data["image_url"] = $this->get_image_url();
		$data["colors"] = $this->get_colors();
		$data["packages"] = $this->get_packagings();
		$data["prices"] = $this->get_prices();
		$data["sizes"] = $this->get_sizes();
		$data["descriptions"] = $this->get_descriptions();
		$data["min_price"] = $this->get_min_price();
		return $data;
	}

	private function _set_image_object()
	{
		if(empty($this->image_object))
		{
			foreach($this->connections_from(array(
				"type" => "RELTYPE_IMAGE",

			)) as $c)
			{
				$this->image_object = $c->to();
				return;
			}
			foreach($this->connections_from(array(
				"type" => "RELTYPE_PRODUCT",
			)) as $c)
			{
				$product = $c->to();
				foreach($product->connections_from(array(
					"type" => "RELTYPE_IMAGE",

				)) as $c)
				{
					$this->image_object = $c->to();
					return;
				}
			}		
			return "";
		}
	}

	//makes var product_objects usable for everyone
	private function _set_products()
	{
		if(empty($this->product_objects))
		{
			$this->product_objects = new object_list();
			foreach($this ->connections_from(array(
				"type" => "RELTYPE_PRODUCT",
			)) as $c)
			{
				$this->product_objects->add($c->prop("to"));
			}
		}
	}

	//makes var packaging_objects usable for everyone
	private function _set_packagings()
	{
		if(empty($this->packaging_objects))
		{
			$this->_set_products();
			$ret = array();
			$this->packaging_objects = new object_list();
			foreach($this->product_objects->arr() as $product)
			{
				$this->packaging_objects->add($product->get_packagings());
			}
		}
	}

	private function get_image_url()
	{
		$this->_set_image_object();
		if(!empty($this->image_object) && is_object($this->image_object))
		{
			return $this->image_object->get_url();
		}		
		return "";
	}

	private function get_image()
	{
		$this->_set_image_object();
		if(!empty($this->image_object) && is_object($this->image_object))
		{
			return $this->image_object->get_html();
		}		
		return "";
	}
//returns array(product id => color name)
	private function get_colors()
	{
		$colors = array();
		foreach($this ->connections_from(array(
			"type" => "RELTYPE_PRODUCT",
		)) as $c)
		{
			$product = $c->to();
			$color = $product->get_color_name();
			if($color)
			{
				$colors[$product->id()] = $color;
			}
		}
		return $colors;
	}

	private function get_packagings()
	{
		$this->_set_products();
		$ret = array();
		foreach($this->product_objects->arr() as $product)
		{
			$ret[$product->id()] = $product->get_packagings()->ids();
		}
		return $ret;
	}

	private function get_prices()
	{
		$ret = array();
		$this->_set_packagings();
		foreach($this->packaging_objects->arr() as $packaging)
		{
			$ret[$packaging->id()] = $packaging->get_shop_price();
		}
		return $ret;
	}

	private function get_sizes()
	{
		$ret = array();
		$this->_set_packagings();
		foreach($this->packaging_objects->arr() as $packaging)
		{
			$ret[$packaging->id()] = $packaging->prop("size");
		}
		return $ret;
	}

	private function get_min_price()
	{
		$this->_set_packagings();
		$min = "";
		foreach($this->packaging_objects->arr() as $packaging)
		{
			if(!(is_numeric($min)) || $packaging->get_shop_price() < $min)
			{
				$min = $packaging->get_shop_price();
			}
		}
		return $min;
	}

	private function get_descriptions()
	{
		$ret = array();
		$this->_set_products();
		foreach($this->product_objects->arr() as $product)
		{
			$ret[$product->id()] = $product->prop("description");
		}
		return $ret;
	}
}

?>
