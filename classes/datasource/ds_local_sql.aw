<?php
// $Id: ds_local_sql.aw,v 1.10 2003/10/29 15:01:34 duke Exp $
// ds_local_sql - interface for the local SQL database
class ds_local_sql extends aw_template
{
	function ds_local_sql()
	{
		$this->init(array());
		$this->_errortext = "";
	}

	////
	// !Retrieves an object
	function ds_get_object($args = array())
	{
		extract($args);
		$retval = false;
		if (isset($table) && isset($idfield) && isset($id))
		{
			$retval = new aw_array($this->get_record($table,$idfield,$id,array_keys($fields)));
			$tmp = array();
			foreach($retval->get() as $key => $val)
			{
				if ($fields[$key] == "serialize")
				{
					if ($key == "metadata")
					{
						$key = "meta";
					};
					$tmp[$key] = aw_unserialize($val);
				}
				else
				{
					$tmp[$key] = $val;
				};
			};
			$retval = $tmp;
		}
		return $retval;
	}

	function get_error_text()
	{
		return $this->_errortext;
	}

};
?>
