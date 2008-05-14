<?php
/*

@classinfo maintainer=markop

*/
class budgeting_account_obj extends _int_object
{
	function get_account_balance()
	{
		return $this->prop("balance");
	}
}
?>
