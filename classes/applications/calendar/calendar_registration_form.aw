<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/calendar_registration_form.aw,v 1.1 2004/10/28 09:51:23 kristo Exp $
// calendar_registration_form.aw - Kalendri s&uuml;ndmusele registreerimise vorm 
/*

@classinfo syslog_type=ST_CALENDAR_REGISTRATION_FORM relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
@default field=meta 
@default method=serialize

@property firstname type=textbox
@caption Eesnimi

@property lastname type=textbox
@caption Perekonnanimi

@property co_name type=textbox
@caption Ettev&otilde;tte nimi

@property address type=textbox
@caption Aadress

@property phone type=textbox
@caption Telefon

@property fax type=textbox
@caption Faks

@property email type=textbox
@caption E-post

@property comment type=texarea cols=50 rows=5
@caption Kommentaar

@property person_id type=hidden


@groupinfo data caption="Andmed"
@default group=data

@property user1 type=textbox
@caption User-defined textbox 1

@property user2 type=textbox
@caption User-defined textbox 2

@property user3 type=textbox
@caption User-defined textbox 3

@property user4 type=textbox
@caption User-defined textbox 4

@property user5 type=textbox
@caption User-defined textbox 5

@property userta1 type=textarea rows=5 cols=30
@caption User-defined textarea 1

@property userta2 type=textarea rows=5 cols=30
@caption User-defined textarea 2

@property userta3 type=textarea rows=5 cols=30
@caption User-defined textarea 3

@property userta4 type=textarea rows=5 cols=30
@caption User-defined textarea 4

@property userta5 type=textarea rows=5 cols=30
@caption User-defined textarea 5

@property uservar1 type=classificator 
@caption User-defined var 1

@property uservar2 type=classificator 
@caption User-defined var 2

@property uservar3 type=classificator
@caption User-defined var 3

@property uservar4 type=classificator 
@caption User-defined var 4

@property uservar5 type=classificator 
@caption User-defined var 5

@property uservar6 type=classificator 
@caption User-defined var 6

@property uservar7 type=classificator 
@caption User-defined var 7

@property uservar8 type=classificator 
@caption User-defined var 8

@property uservar9 type=classificator 
@caption User-defined var 9

@property uservar10 type=classificator 
@caption User-defined var 10


@reltype EVENT value=1 clid=CL_CRM_MEETING,CL_TASK,CL_CRM_CALL
@caption s&uuml;ndmus

@reltype OT value=2 clid=CL_OBJECT_TYPE
@caption registreerumisvormi t&uuml;&uuml;p

@reltype DATA value=3 clid=CL_CRM_MEETING,CL_TASK,CL_CRM_CALL
@caption andmed

*/

class calendar_registration_form extends class_base
{
	function calendar_registration_form()
	{
		$this->init(array(
			"tpldir" => "applications/calendar/calendar_registration_form",
			"clid" => CL_CALENDAR_REGISTRATION_FORM
		));
	}
}
?>
