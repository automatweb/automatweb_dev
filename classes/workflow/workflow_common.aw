<?php
define("RELTYPE_INSTRUCTION",1);
define("RELTYPE_ACTION",10);
define("RELTYPE_PROCESS",11);

// links between actions
define("RELTYPE_NEXT_ACTION",21);
define("RELTYPE_PREV_ACTION",22);

class workflow_common extends class_base
{
	function callback_get_rel_types()
	{
                return array(
                        RELTYPE_INSTRUCTION => "juhendmaterjal",
                );
        }

	////
	// !Returns a list of allowed classes for a relation type
	function callback_get_classes_for_relation($args = array())
	{
		$retval = false;
		switch($args["reltype"])
		{
			case RELTYPE_INSTRUCTION:
				$retval = array(CL_IMAGE,CL_FILE);
				break;

			// if the relation type was not handled by this class,
			// ask the subclass	
			default:
				if (method_exists($this,"_get_classes_for_relation"))
				{
					$retval = $this->_get_classes_for_relation($args["reltype"]);
				};
				
		};
		return $retval;
	}
}
?>
