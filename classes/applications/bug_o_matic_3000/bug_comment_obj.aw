<?php
/*
@classinfo  maintainer=robert
*/

class bug_comment_obj extends _int_object
{
	function save()
	{
		//miskit sellise nimelist propi on vaja, et otsinguid teha jne
		if($this->prop("parent.class_id") == CL_BUG)
		{
			$this->set_prop("bug" , $this->parent());
		}
		return parent::save();
	}

	function set_prop($name,$value)
	{
		parent::set_prop($name,$value);
	}
}
