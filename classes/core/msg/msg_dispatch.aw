<?php

// this is the aw message dispatcher
// it accepts messages and delivers them to all listeners

class msg_dispatch extends class_base
{
	function msg_dispatch()
	{
		$this->init();
	}

	////
	// !this delivers posted messages
	// parameters:
	//	msg - message 
	//	params - array of parameters
	function post_message($arr)
	{
		error::throw_if(!isset($arr["msg"]), array(
			"id" => ERR_NO_MSG, 
			"msg" => "msg_dispatch::post_message - no message posted!"
		));

		$handlers = $this->_get_handlers_for_message($arr["msg"]);
		foreach($handlers as $handler)
		{
			$class = $handler["class"];
			$func = $handler["func"];
			$inst = get_instance($handler["class"]);
			error::throw_if(!method_exists($inst, $func), array(
				"id" => ERR_NO_HANDLER_FUNC,
				"msg" => "msg_dispatch::post_message - no handler function ($func) in class ($class) for message $arr[msg]!"
			));

			$inst->$func($arr["params"]);
		}
	}

	function _get_handlers_for_message($msg)
	{
		$msg = str_replace(".", "", $msg);
		$file = $this->cfg["basedir"]."/xml/msgmaps/".$msg.".xml";
		
		$fc = $this->get_file(array("file" => $file));
		error::throw_if($fc === false, array(
			"id" => ERR_NO_SUCH_MESSAGE,
			"msg" => "msg_dispatch::post_message - no such message ($msg) defined!"
		));

		$handlers = new aw_array(aw_unserialize($fc));
		return $handlers->get();
	}
}
?>