<?php
// klass, mis genereerib XML faili pohjal vormi.
// kui seda ideed veel edasi arendada, siis voiks ka paris formgeni selle
// peale ehitada
class aw_formgen {
	function aw_formgen() {

	}
	// loeb faili. Hiljem liigutame selle kuhugi baasklassi
        function get_file_contents($name,$bytes = 8192) {
                $fh = fopen($name,"r");
                $data = fread($fh,$bytes);
                fclose($fh);
                return $data;
        }

	
	function _xml_start_element($parser,$name,$attrs) {
		$construct = 
	}

	function _xml_end_element($parser,$name) {
		echo "end = $name<br>";
	}

	function parse_xml_def($file) {
                $xml_data = $this->get_file_contents($file);
		$construct = array();
                $xml_parser = xml_parser_create();
                xml_parser_set_option($xml_parser,XML_OPTION_CASE_FOLDING,0);
                xml_set_object($xml_parser,&$this);
                xml_set_element_handler($xml_parser,"_xml_start_element","_xml_end_element");
                if (!xml_parse($xml_parser,$xml_data)) {
                        echo(sprintf("XML error: %s at line %d",
                                      xml_error_string(xml_get_error_code($xml_parser)),
                                      xml_get_current_line_number($xml_parser)));
                };
                return $this->data;
        }


};
