<?php

class aw_http_response extends aw_resource
{
	protected $headers = array(); // http headers, array( header_name => value, ...)

	/**
	@attrib api=1 params=pos
	@returns void
		Sends http headers and content to user agent.
	**/
	public function send()
	{
		if (!headers_sent())
		{
			foreach ($this->headers as $name => $value)
			{
				header($name . ": " . $value, true);
			}

			header("Last-Modified: " . date("r", $this->last_modified), true);
		}
		else
		{
			trigger_error("Headers were already sent", E_USER_WARNING);
		}

		if (aw_ini_get("content.compress"))
		{
			ob_start( 'ob_gzhandler' );
		}

		parent::send();
	}
}

?>
