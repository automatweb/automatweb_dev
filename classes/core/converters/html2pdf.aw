<?php

class html2pdf extends class_base
{
	function html2pdf()
	{
		$this->init();
	}

	////
	// !converts html to pdf, returns pdf content
	// parameters:
	//	source - html source to convert
	function convert($arr)
	{
		// right, figure out which converter we got
		// first, try htmldoc
		$hd = aw_ini_get("html2pdf.htmldoc_path");
		if (file_exists($hd) && is_executable($hd))
		{
			return $this->_convert_using_htmldoc($arr);
		}
		else
		{
			$this->raise_error(ERR_CONVERT, "html2pdf::convert(): no available converters found!");
		}
	}

	function _convert_using_htmldoc($arr)
	{
		$tmpf = aw_ini_get("server.tmpdir")."/aw-html2pdf-".gen_uniq_id();
		$fp = fopen($tmpf, "w");
		fwrite($fp, $arr["source"]);
		fclose($fp);

		$hd = aw_ini_get("html2pdf.htmldoc_path");
		$pdf = shell_exec($hd." -t pdf --quiet --book --jpeg --webpage '$tmpf'");
		@unlink($tmpf);
		return $pdf;
	}
}
?>
