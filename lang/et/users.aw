<?php
// localization constans for users.aw

// messages
define("USR_LOGGED_IN","Logis sisse");
define("USR_LOGGED_OUT","Logis välja");


// errors
define("E_USR_UID_TOO_SHORT","Liiga lühike kasutajanimi, uid=%s, pass=%s");
define("E_USR_PASS_TOO_SHORT","Liiga lühike parool, uid=%s, pass=%s");
define("E_USR_USER_UNKNOWN","Tundmatu kasutaja, uid=%s, pass=%s");
define("E_USR_WRONG_PASS","Vale parool, uid=%s, pass=%s");

define("E_USR_DYN_GROUP_UPDATE","can't update dynamic group, the search form for users does not specify the entry form for users as a search target!");

global $lc_users;

$lc_users["LC_JF_USERNAME"] = "User name:";
$lc_users["LC_JF_EMAIL"] = "E-mail:";
$lc_users["LC_JF_PASSWORD"] = "Password:";
$lc_users["LC_JF_PASSWORD2"] = "Password 2x:";
$lc_users["LC_JF_PASSWORD2"] = "Password 2x:";
$lc_users["LC_JF_NEXT"] = "Next";

?>
