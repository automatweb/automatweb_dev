<?php

class codestat extends aw_template
{
	function codestat()
	{
		$this->tpl_init("");
	}

	/**  
		
		@attrib name=show params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function show($arr)
	{
		extract($arr);
		$this->read_template("show_codestat.tpl");

		$this->vars(array(
			"stats" => join("",file("/www/automatweb/public/scripts/stats"))
		));

		return $this->parse();
	}
}

?>