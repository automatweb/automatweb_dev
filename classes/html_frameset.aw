<?php
// kasutame built-in templatesid siin
$tmp = <<<EOT
<html>
<head>
<title>{VAR:title}</title>
</head>
{VAR:frameset}
</html>
EOT;

define(FRAME_HTML,$tmp);

$tmp = <<<EOT
<frameset rows="{VAR:rows}" frameborder="yes" framespacing="0">
	<frame name="{VAR:name1}" src="{VAR:src1}" marginheight="0" marginwidth="0" scrolling="auto">
	<frame name="{VAR:name2}" src="{VAR:src2}" marginehight="0" marignwidth="0" scrolling="auto">
</frameset>
EOT;

define(FRAME_HORIZ,$tmp);

class html_frameset {
	function html_framset($args = array())
	{

	}


	// Kuvab lihtsa 2 raamiga layoudi,
	// frame1 - ylemine
	// frame2 - alumine
	// rows - frameseti rows atribuudi sisu
	function horiz($args = array())
	{
		$vars = $args;
		$frameset = $this->_parse(FRAME_HORIZ,$args);
		$vars["frameset"] = $frameset;
		return $this->_parse(FRAME_HTML,$vars);
	}

	function _parse($src = "",$vars = array())
        {
                return preg_replace("/{VAR:(.+?)}/e","\$vars[\"\\1\"]",$src);
        }
};
?>
