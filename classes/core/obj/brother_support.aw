<?php

/*

HANDLE_MESSAGE(MSG_STORAGE_ALIAS_DELETE, on_delete_alias)


*/

class brother_support
{
	function on_delete_alias($arr)
	{
		// now, get the alias. if it has reltype of 10000, then get the object it points TO
		// then find any brothers of the object the relation comes from and delete them
		if ($arr["connection"]->prop("reltype") == 10000)
		{
			$ol = new object_list(array(
				"parent" => $arr["connection"]->prop("to"),
				"brother_of" => $arr["connection"]->prop("from")
			));
			for($o =& $ol->begin(); !$ol->end(); $o = $ol->next())
			{
				$o->delete();
			}
		}
	}
}
?>
