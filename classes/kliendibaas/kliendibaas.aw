<?php

/*
	@default table=objects
	@default group=general

	@property comment type=textarea field=comment cols=40 rows=3
	@caption Kommentaar


	@default field=meta
	@default method=serialize

	@property kataloogid type=generated generator=changi group=whee

	@property blaa

	@property toolbar type=generated generator=my_toolbar group=whee

*/


//property kataloogid type=generated generator=change
class kliendibaas extends aw_template
{
	function kliendibaas()
	{
		$this->init(array(
//			'tpldir' => 'kliendibaas',
			'clid' => CL_KLIENDIBAAS,
		));
	}


	function changi($arr)
	{

		extract($arr);

		$ob = $this->get_object($obj['oid']);
//print_r($obj);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda kliendibaas");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda kliendibaas");
		}

		$par = get_instance("objects");

		return array(
			array(
				"caption" => "vali kataloogid",
				"type" => "select",
				"name" => "kataloogid",
				"options" =>$par->get_list(false,true,50477),
				"selected" => $obj['meta']['kataloogid'],
	                ),
	                array(
				"caption" => "blaa",
				"type" => "textbox",
				"name" => "blaa",
				"value" => $obj['meta']['blaa'],
			),

		);
	
	}



	function my_toolbar($arr)
	{
		$toolbar = get_instance("toolbar",array("imgbase" => "/automatweb/images/blue/awicons"));
		$toolbar->add_button(array(
			"name" => "save",
			"tooltip" => "salvesta",
			"url" => "javascript:document.add.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif",
		));
		$toolbar->add_button(array(
			"name" => "lisa_blaa",
			"tooltip" => "lisa blaa",
			"url" => $this->mk_my_orb("new", array("return_url" => urlencode($return_url),"parent" => $ob['blaa']["parent"],),"blaa"), //parent tuleb panna selleks mida confist määran!!
			"imgover" => "blaa_over.gif",
			"img" => "blaa.gif",
		));
		$toolbar->add_button(array(
			"name" => "lisa_blaa1",
			"tooltip" => "lisa blaa",
			"url" => $this->mk_my_orb("new", array("return_url" => urlencode($return_url),"parent" => $ob['blaa']["parent"],),"blaa"), //parent tuleb panna selleks mida confist määran!!
			"imgover" => "blaa_over.gif",
			"img" => "blaa.gif",
		));
		$toolbar->add_button(array(
			"name" => "lisa_blaa2",
			"tooltip" => "lisa blaa",
			"url" => $this->mk_my_orb("new", array("return_url" => urlencode($return_url),"parent" => $ob['blaa']["parent"],),"blaa"), //parent tuleb panna selleks mida confist määran!!
			"imgover" => "blaa_over.gif",
			"img" => "blaa.gif",
		));
		
	return array(
                array(
                        "caption" => "toolbar",
                        "type" => "text",
                        "name" => "toolbar",
                        "value" => $toolbar->get_toolbar(),
                ),
        );
	
	}




/*
	////
	// !this gets called when the user submits the object's form
	// parameters:
	// id - if set, object will be changed, if not set, new object will be created
	function submit($arr)
	{
		extract($arr);
		
		if ($id)
		{

			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
				"metadata" => array(
				)
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_KLIENDIBAAS,
				"comment" => $comment,
				"metadata" => array(
				)
			));
		}

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));
	}
*/
}
?>