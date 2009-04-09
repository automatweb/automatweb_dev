<?php

/*
@classinfo  maintainer=voldemar
*/

require_once "mrp_header.aw";

class mrp_workspace_obj extends _int_object
{
/**
	@attrib params=pos api=1
	@returns void
	@errors
		awex_mrp_ws_schedule if rescheduling request fails for some reason
**/
	public function request_rescheduling()
	{
		try
		{
			$this->set_prop("rescheduling_needed", 1);
			aw_disable_acl();
			$this->save();
			aw_restore_acl();
		}
		catch (Exception $E)
		{
			$e = new awex_mrp_ws_schedule("Rescheduling request failed");
			$e->set_forwarded_exception($E);
			throw $e;
		}
	}
}

/** Generic workspace error **/
class awex_mrp_ws extends awex_mrp {}

/** Generic workspace scheduling operations error **/
class awex_mrp_ws_schedule extends awex_mrp_ws {}


?>
