<?php
include("const.aw");
// can't use classload here, cause it will be included from within a function and then all kinds of nasty
// scoping rules come into action. blech.
include(aw_ini_get("classdir")."/orb_impl.".aw_ini_get("ext"));
include("site_footer.".aw_ini_get("ext"));
?>
