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
		$ol->sort_by(array(
			"prop" => "name",
			"order" => "desc"
		));
		$ol->sort_by(array(
			"prop" => "ord",
			"order" => "asc"
		));
		return $ol;
	}

	/** adds category
		@attrib api=1
	**/
	public function add_category($id)
	{
		$this->connect(array(
			"to" => $id,
			"reltype" => "RELTYPE_CATEGORY",
		));
	}

}

?>
