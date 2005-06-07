<?php

class mail_protector
{
	function protect($str)
	{
		$js = "
		<script language=\"javascript\">
		function aw_proteml(n,d,f = 0)
		{
			var e = (n + \"@\" + d);
			if (!f) { f = e; }
			document.write( \"<a \"+\"hr\"+\"ef=\\\"mailto:\" + e + \"\\\">\" + f + \"</a>\");
		}
		</script>
		";

		// also try to do already existing email links that have as text the mail address
		$repl = "<script language=\"javascript\">aw_proteml(\"\\3\",\"\\4\");</script><noscript>\\3<img src='".aw_ini_get("baseurl")."/automatweb/images/at.png' alt='@' style='vertical-align: middle;'/>\\4</noscript>";

		$str = preg_replace("/<a([^>]*) href=([\"|'])mailto:(.*)@([\w|\.]*)([\"|'])(\w*)>\\3@\\4<\/a>/imsU",$repl, $str);
		#$str = preg_replace("/<a([^>]*) href=([\"|'])mailto:(.*)@(.*)([\"|'])(.*)>\\3@\\4<\/a>/imsU",$repl, $str);

		// finally links that go to mail but have the text different from the address
		$repl = "<script language=\"javascript\">aw_proteml(\"\\3\",\"\\4\",\"\\7\");</script><noscript>\\3<img src='".aw_ini_get("baseurl")."/automatweb/images/at.png' alt='@' style='vertical-align: middle;'/>\\4</noscript>";
		$str = preg_replace("/<a([^>]*)href=([\"|'])mailto:(.*)@(.*)([\"|'])(.*)>(.*)<\/a>/imsU",$repl, $str);

		$repl = "\\1<script language=\"javascript\">aw_proteml(\"\\2\",\"\\3\");</script><noscript>\\2<img src='".aw_ini_get("baseurl")."/automatweb/images/at.png' alt='@' style='vertical-align: middle;'/>\\3</noscript>";
		$str = preg_replace("/([\s|^|>])([-.a-zA-Z0-9_]*)@([-.a-zA-Z0-9_]*)/",$repl, $str);
		return $str;
	}
}
?>
