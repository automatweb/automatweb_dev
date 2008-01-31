<?php
/*
@classinfo  maintainer=kristo
*/
class html2pdf extends class_base
{
	function html2pdf()
	{
		$this->init();
	}

	/**
		@attrib api=1
		@comment
			check's if any html2pdf conversion is possible at the moment
		@returns
			true if all ok, false otherwise
	**/
	function can_convert()
	{
		if (is_file(aw_ini_get("html2pdf.htmldoc_path")))
		{
			return true;
		}
		return false;
	}
	
	////
	// !converts html to pdf, returns pdf content
	// parameters:
	//	source - html source to convert
	/**
		@attrib api=1 params=name
		@param source required type=string
			html soucre to be converted
		@param landscape optional type=bool
			if set to true.. landscape pdf is created
		@param no_numbers optional type=bool
			if set to true, no page numbers are set to pdf
		@comment
			converts html contents to pdf
		@returns
			converted pdf 
		@errors
			raises ERR_CONVERT error if there aren't any available converters found. 
	**/
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

	/**
		@attrib api=1 params=name
		@param source required type=string
			html soucre to be converted
		@param landscape optional type=bool
			if set to true.. landscape pdf is created
		@param no_numbers optional type=bool
			if set to true, no page numbers are set to pdf
		@comment
			generates pdf and outputs it to browser with correct headers.
		@errors
			raises ERR_CONVERT error if there aren't any available converters found. 

	**/
	function gen_pdf($arr)
	{
		$str = $this->convert($arr);
		header("Content-type: application/pdf");
		header("Content-Disposition: filename=".(strpos($arr["filename"], ".pdf") !== false ? $arr["filename"] : $arr["filename"].".pdf"));
		header("Content-Length: ".strlen($str));
		die($str);
	}

	function _convert_using_htmldoc($arr)
	{
		$tmpf = aw_ini_get("server.tmpdir")."/aw-html2pdf-".gen_uniq_id();
		$fp = fopen($tmpf, "w");
		fwrite($fp, $arr["source"]);
		fclose($fp);

		$lds = "";
		if ($arr["landscape"] == 1)
		{
			$lds = "--landscape";
		}

		$nns = "";
		if ($arr["no_numbers"] == 1)
		{
			$nns = "--no-numbered";
		}

		$hd = aw_ini_get("html2pdf.htmldoc_path");
		$cmdl = $hd." -t pdf --quiet --book --jpeg --webpage $lds $nns '$tmpf'";
		$pdf = shell_exec($cmdl);
		unlink($tmpf);
		return $pdf;
	}
}
?>
