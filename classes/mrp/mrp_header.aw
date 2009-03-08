<?php

/*
included in all mrp files except operator and import.
*/

### resource types
define ("MRP_RESOURCE_SCHEDULABLE", 1);
define ("MRP_RESOURCE_NOT_SCHEDULABLE", 2);
define ("MRP_RESOURCE_SUBCONTRACTOR", 3);

### states
define ("MRP_STATUS_NEW", 1);
define ("MRP_STATUS_PLANNED", 2);
define ("MRP_STATUS_INPROGRESS", 3);
define ("MRP_STATUS_ABORTED", 4);
define ("MRP_STATUS_DONE", 5);
define ("MRP_STATUS_LOCKED", 6);
define ("MRP_STATUS_PAUSED", 7);
define ("MRP_STATUS_DELETED", 8);
define ("MRP_STATUS_ONHOLD", 9);
define ("MRP_STATUS_ARCHIVED", 10);
define ("MRP_STATUS_VIRTUAL_PLANNED", 11);

define ("MRP_STATUS_RESOURCE_AVAILABLE", 10);
define ("MRP_STATUS_RESOURCE_INUSE", 11);
define ("MRP_STATUS_RESOURCE_OUTOFSERVICE", 12);
define ("MRP_STATUS_RESOURCE_INACTIVE", 13);

### misc
define ("MRP_DATE_FORMAT", "j/m/Y H.i");
define ("MRP_NEWLINE", "<br />\n");
define("RECUR_DAILY",1);
define("RECUR_WEEKLY",2);
define("RECUR_MONTHLY",3);
define("RECUR_YEARLY",4);
define("RECUR_HOURLY",5);
define("RECUR_MINUTELY",6);

### colours (CSS colour definition)
define ("MRP_COLOUR_NEW", "#05F123");
define ("MRP_COLOUR_PLANNED", "#5B9F44");
define ("MRP_COLOUR_INPROGRESS", "#FF9900");
define ("MRP_COLOUR_ABORTED", "#FF13F3");
define ("MRP_COLOUR_DONE", "#996600");
define ("MRP_COLOUR_PAUSED", "#999999");
define ("MRP_COLOUR_DELETED", "#FF0000");
define ("MRP_COLOUR_UNAVAILABLE", "#D0D0D0");
define ("MRP_COLOUR_ONHOLD", "#9900CC");
define ("MRP_COLOUR_ARCHIVED", "#0066CC");
define ("MRP_COLOUR_HILIGHTED", "#FFE706");
define ("MRP_COLOUR_PLANNED_OVERDUE", "#FBCEC1");
define ("MRP_COLOUR_OVERDUE", "#DF0D12");
define ("MRP_COLOUR_AVAILABLE", "#FCFCF4");
define ("MRP_COLOUR_PRJHILITE", "#FFE706");

class awex_mrp extends aw_exception {}

?>
