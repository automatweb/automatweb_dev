<?php
// $Header: /home/cvs/automatweb_dev/classes/chat/Attic/chat_channel_conf.aw,v 1.1 2002/12/17 17:24:59 duke Exp $
// chat_channel_conf.aw - IRC kanali konff
/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property uid type=textbox
	@caption Kasutajanimi

	@property kanal type=textbox
	@caption Kanal
*/
class chat_channel_conf extends aw_template{
	
	function chat_channel_conf(){
		$this->init(array(
			"tpldir" => "chat_channel_conf",
			"clid" => CL_CHAT_CHANNEL_CONF,
		));
	}

	function parse_alias($arr){
		extract($arr);

		$dat=$this->get_object($alias['target']);

		$this->read_template('show.tpl');

		$this->vars(array(
			'name'=>$dat['name'],
			'uid'=>$dat['meta']['uid'],
			'kanal'=>$dat['meta']['kanal']));
		
		return $this->parse();
	}
}
?>
