<?php

class mail_protector
{
	function protect($str)
	{
		$js = "
		<script language=\"javascript\">
		function aw_proteml(n,d)
		{
			var e = (n + \"@\" + d);
			document.write( \"<a \"+\"hr\"+\"ef=\\\"mailto:\" + e + \"\\\">\" + e + \"</a>\");
		}
		</script>
		";
		$repl = "<script language=\"javascript\">aw_proteml(\"\\1\",\"\\2\");</script><noscript>\\1<img src='".aw_ini_get("baseurl")."/automatweb/images/at.png' alt='@' style='vertical-align: middle;'/>\\2</noscript>";
		$str = preg_replace("/[\s|^|>]([-.a-zA-Z0-9_]*)@([-.a-zA-Z0-9_]*)/",$repl, $str);
		return $str;
	}
}
?>