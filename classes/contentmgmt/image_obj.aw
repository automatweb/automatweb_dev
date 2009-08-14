<?php

class image_obj extends _int_object
{
	function set_prop($k, $v)
	{
		if($k == "file" || $k == "file2")
		{
			parent::set_meta("old_file", parent::prop("file"));
		}
		return parent::set_prop($k, $v);
	}

	/** Creates HTML image tag
	@attrib name=view nologin="1" 
	@returns
		HTML image tag
	**/
	public function get_html()
	{
		$img_inst = get_instance(CL_IMAGE);
		$idata = $img_inst->get_image_by_id($this->id());
		$img_inst->mk_path($idata["parent"],"Vaata pilti");
		$retval = html::img(array(
			"url" => $idata["url"],
			'height' => (isset($args['height']) ? $args['height'] : NULL),
		));
		return $retval;
	}

	/** Get image url
		@attrib api=1 
		@errors 
			none
		@returns 
			empty value if the image object has no view access, url to the image othervise
	**/
	public function get_url()
	{
		$img_inst = get_instance(CL_IMAGE);
		$url = $img_inst->get_url($this->prop("file"));
		return $img_inst->check_url($url);
	}

	/** Creates big image HTML image tag
	@attrib name=view nologin="1" 
	@returns
		HTML image tag
	**/
	public function get_big_html()
	{
		$img_inst = get_instance(CL_IMAGE);
		$idata = $img_inst->get_image_by_id($this->id());
		$img_inst->mk_path($idata["parent"],"Vaata pilti");
		$retval = html::img(array(
			"url" => $idata["big_url"],
			'height' => (isset($args['height']) ? $args['height'] : NULL),
		));
		return $retval;
	}

	/** Get big image url
		@attrib api=1 
		@errors 
			none
		@returns 
			empty value if the image object has no view access, url to the image othervise
	**/
	public function get_big_url()
	{
		$img_inst = get_instance(CL_IMAGE);
		$url = $img_inst->get_url($this->prop("file2"));
		return $img_inst->check_url($url);
	}



	public function add_image_big($file)
	{
		$_fi = get_instance(CL_FILE);
		$mime = get_instance("core/aw_mime_types");

		$f2 = $_fi->_put_fs(array(
			"type" => $mime->type_for_file(basename($file)),
			"content" => file_get_contents($file)
		));

		$this->set_prop("file2", $f2);
	
	}
}

?>
