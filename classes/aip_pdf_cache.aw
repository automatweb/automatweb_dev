<?php

class aip_pdf_cache extends class_base
{
	function aip_pdf_cache()
	{
		$this->init();
	}

	function get_pdf_count_for_menu($fn)
	{
		return $this->db_fetch_field("
			SELECT count(aip_files.id) as cnt 
			FROM aip_files 
				LEFT JOIN objects ON objects.oid = aip_files.id 
			WHERE 
				aip_files.filename LIKE '%".$fn."%' 
				AND objects.class_id = ".CL_FILE." 
				AND objects.status = 2
		","cnt");
	}
}
?>
