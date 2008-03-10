<?php
/*
@classinfo  maintainer=kristo
*/

class mail_protector
{
	function protect($str)
	{
		$js = "
		<script type=\"text/javascript\">
		function aw_proteml(n,d,f = 0)
		{
			var e = (n + \"@\" + d);
			if (!f) { f = e; }
			document.write( \"<a \"+\"hr\"+\"ef=\\\"mailto:\" + e + \"\\\">\" + f + \"</a>\");
		}
		</script>
		";
		$xhtml_slash = '';
		if (aw_ini_get("content.doctype") == "xhtml")
		{
			$xhtml_slash = " /";
		}
		// also try to do already existing email links that have as text the mail address
		$repl = "<script type=\"text/javascript\">aw_proteml(\"\\3\",\"\\4\");</script><noscript>\\3<img src='".aw_ini_get("baseurl")."/automatweb/images/at.png' alt='@' style='vertical-align: middle;'$xhtml_slash>\\4</noscript>";

		$str = preg_replace("/<a([^>]*) href=([\"|'])mailto:([^\"']*)@([\w|\.]*)([\"|'])([^>]*)>\\3@\\4<\/a>/imsU",$repl, $str);

		// finally links that go to mail but have the text different from the address
		$repl = "<script type=\"text/javascript\">aw_proteml(\"\\3\",\"\\4\",\"\\7\");</script><noscript>\\3<img src='".aw_ini_get("baseurl")."/automatweb/images/at.png' alt='@' style='vertical-align: middle;'$xhtml_slash>\\4</noscript>";
		$str = preg_replace("/<a([^>]*)href=([\"|'])mailto:([^\"']*)@(.*)([\"|'])(.*)>(.*)<\/a>/imsU",$repl, $str);

		$repl = "\\1<script type=\"text/javascript\">aw_proteml(\"\\2\",\"\\3\");</script><noscript>\\2<img src='".aw_ini_get("baseurl")."/automatweb/images/at.png' alt='@' style='vertical-align: middle;'$xhtml_slash>\\3</noscript>";
		$str = preg_replace("/([^=\"][\s|^|>|^[:punct:]])(\w[-.a-zA-Z0-9_]+)@([-.a-zA-Z0-9_]+\.[a-zA-Z]{2,6})/",$repl, $str);
		return $str;
	}
}
?>
