<?php
function has_space($text) {
	if( ereg("[     ]",$text) ) {
              return true;
         }
         return false;
};

function strip_space ($text)
{
	$Return = ereg_replace("([      ]+)","",$text);
	return ($Return);
};


//function is_number ($text) {
//	return true;
//	if( (gettype($text)) == "integer") {
//		 return true;
//	 }
//
//  // 	$Bad = strip_numbers($text);
//
//   	if(empty($Bad)) {
 //                       return true;
//        }
//        return false;
//};

function strip_numbers ($text) {
	$Stripped = eregi_replace("([0-9]+)","",$text);
        return ($Stripped);
};

function is_alpha ($text) {
       $Bad = strip_letters($text);
       if(empty($Bad)) {
              return true;
       }
return false;
};

function strip_letters ($text) {
    $Stripped = eregi_replace("([A-Z]+)","",$text);
    return $Stripped;
};

function is_alnum($text) {
	$stripped = strip_letters(strip_numbers($text));
	return empty($stripped);
};


function is_email ($address = "") {
	if(empty($Address)) {
		// tühi aadress
       		return false;
	}

	if(!ereg("@",$Address)) {
		// @-i pole
		return false;
        }

	list($User,$Host) = split("@",$Address);

	if ( (empty($User)) or (empty($Address)) ) {
		// kuju pole user@host
		return false;
	}
       
	if( ($this->has_space($User)) or ($this->has_space($Host)) ) {
		// whitespace sees
		return false;
	}

	return true;
};
?>
