<?php
// property.aw - Object property
/*
	@default table=objects
	@default field=meta
	@default method=serialize

	@property test type=textbox size=40
	@caption Test
	
	@classinfo corefields=name,comment,status
*/
class property extends aw_template
{
        function property($args = array())
        {
                $this->init(array(
                        "clid" => CL_PROPERTY,
                ));
        }
};
?>
