<?php
// $Header: /home/cvs/automatweb_dev/classes/chat/Attic/chatserver.aw,v 1.1 2002/12/17 17:20:37 duke Exp $
// chatserver.aw - IRC server object
/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property servername type=textbox
	@caption IRC serveri nimi

	@property ircserver type=textbox
	@caption IRC serveri aadress

	@property port type=textbox
	@caption Port

*/
class chatserver extends aw_template
{
	
	function chatserver()
	{
		$this->init(array(
			"clid" => CL_CHATSERVER,
			"tpldir" => "chatserver",
		));
	}

	function parse_alias($arr)
	{
		extract($arr);

		$dat=$this->get_object($alias['target']);

		$this->read_template('show.tpl');

		return $this->parse();
	}
}
?>
