<?php

class shop_product_category_type_obj extends _int_object
{
	/** return categories
		@attrib api=1
		@returns
			object list
	**/
	public function get_categories()
	{
		$ol= new object_list();
		$conn = $this->connections_from(array(
			"type" => "RELTYPE_CATEGORY",
		));
		foreach($conn as $c)
		{
			$ol->add($c->prop("to"));
		}
		return $ol;
	}

}

?>
