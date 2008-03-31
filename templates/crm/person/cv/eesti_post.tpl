<style type="text/css">
<!--
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
a:link {
	color: #1D2D7C;
	text-decoration: none;
}

a:visited {
	color: #1D2D7C;
	text-decoration: none;
}

a:hover {
	color: #1D2D7C;
	text-decoration: underline;
}

.link10px {
	font-family: Tahoma, Verdana, Arial, Sans-serif;
	font-size: 10px;
	color:#FFFFFF;
}

.link10px a:link {
	color: #FFFFFF;
	text-decoration: none;
}

.link10px a:visited {
	color: #FFFFFF;
	text-decoration: none;
}

.link10px a:hover {
	color: #FFFFFF;
	text-decoration: underline;
}



.banner {
	background-image: url(../img/banner_bg_2.jpg);
	background-repeat: no-repeat;
}

.aw04tab2content {
height: 24px;
font-family: Arial, sans-serif;
font-size: 11px;
font-weight: bold;
color:#5D5D5D;
vertical-align: middle;
}

.aw04tab2content a {
color: #0017AF;
text-decoration:none;
}

.aw04tab2content a:hover {
color: #0017AF;
text-decoration:underline;
}




.aw04tab2selcontent {
height: 24px;
font-family: Arial, sans-serif;
font-size: 11px;
font-weight: bold;
color:#FFFFFF;
vertical-align: middle;
}

.aw04tab2selcontent a {
color: #FFFFFF;
text-decoration:none;
}

.aw04tab2selcontent a:link {
color: #FFFFFF;
text-decoration:none;
}

.aw04tab2selcontent a:hover {
color: #FFFFFF;
text-decoration:underline;
}

.topBg {
	background-image: url(../img/top_bg.gif);
	background-color: #62BFE5;
}

.aw04tab2smallcontent {
height: 18px;
font-family: Arial, sans-serif;
font-size: 11px;
color:#FFFFFF;
vertical-align: middle;
}

.aw04tab2smallcontent a {
color: #000000;
text-decoration:none;
}

.aw04tab2smallcontent a:hover {
color: #0017AF;
text-decoration:underline;
}


.text11px {
	font-family: Tahoma, Verdana, Arial, Sans-serif;
	font-size: 11px;
	color: #000000;
}
.date {
	font-family: Tahoma, Verdana, Arial, Sans-serif;
	font-size: 10px;
	color: #666666;
}

.dateGr {
	font-family: Tahoma, Verdana, Arial, Sans-serif;
	font-size: 10px;
	color: #009900;
}

.link11px {
	font-family: Tahoma, Verdana, Arial, Sans-serif;
	font-size: 11px;
	color: #000000;
}
.input {
	border: 1px solid #6473C0;
	font-family: Tahoma, Verdana, Arial, Sans-serif;
	font-size: 11px;
	color: #333333;
}
.text10px {
	font-family: Tahoma, Verdana, Arial, Sans-serif;
	font-size: 10px;
	color: #000000;
}
.text16px {
	font-family: Tahoma, Verdana, Arial, Sans-serif;
	font-size: 16px;
	color: #000000;
}
-->
</style>
<!--<link href="http://personal.struktuur.ee/css/personal.css" rel="stylesheet" type="text/css">-->
<center>

<div class="text16px"><b>CURRICULUM VITAE</b></div><br>
<table width="800" cellpadding="1" cellspacing="1" border="0">
	<tr>
		<td>
			<!-- SUB: CRM_PERSON.PERSONAL_INFO -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" colspan="2" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>&nbsp;Isikuandmed</strong></td>
				</tr>
				<!-- SUB: CRM_PERSON.NAME -->
				<tr>
					<td width="174" height="20" align="left" valign="middle" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;"><strong>Nimi:&nbsp;</strong></td>
					<td class="text11px" width="372" height="20" align="left" style="padding-left:10px;"><b>{VAR:crm_person.name}</b></td>
				</tr>
				<!-- END SUB: CRM_PERSON.NAME -->
				<!-- SUB: CRM_PERSON.PERSONAL_ID -->
				<tr>
					<td width="174" height="20" align="left" valign="middle" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;"><strong>Isikukood:&nbsp;</strong></td>
					<td class="text11px" width="372" height="20" align="left" style="padding-left:10px;"><b>{VAR:crm_person.personal_id}</b></td>
				</tr>
				<!-- END SUB: CRM_PERSON.PERSONAL_ID -->
				<!-- SUB: CRM_PERSON.BIRTHDAY -->
				<tr>
					<td width="174" height="20" align="left" valign="middle" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;"><strong>S&uuml;nniaeg:&nbsp;</strong></td>
					<td class="text11px" width="372" height="20" align="left" style="padding-left:10px;"><b>{VAR:crm_person.birthday} ({VAR:crm_person.age})</b></td>
				</tr>
				<!-- END SUB: CRM_PERSON.BIRTHDAY -->
				<!-- SUB: CRM_PERSON.GENDER -->
				<tr>
					<td width="174" height="20" align="left" valign="middle" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;"><strong>Sugu:&nbsp;</strong></td>
					<td class="text11px" width="372" height="20" align="left" style="padding-left:10px;"><b>{VAR:crm_person.gender}</b></td>
				</tr>
				<!-- END SUB: CRM_PERSON.GENDER -->
				<!-- SUB: CRM_PERSON.NATIONALITY -->
				<tr>
					<td width="174" height="20" align="left" valign="middle" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;"><strong>Rahvus:&nbsp;</strong></td>
					<td class="text11px" width="372" height="20" align="left" style="padding-left:10px;"><b>{VAR:crm_person.nationality}</b></td>
				</tr>
				<!-- END SUB: CRM_PERSON.NATIONALITY -->
				<!-- SUB: CRM_PERSON.MLANG -->
				<tr>
					<td width="174" height="20" align="left" valign="middle" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;"><strong>Emakeel:&nbsp;</strong></td>
					<td class="text11px" width="372" height="20" align="left" style="padding-left:10px;"><b>{VAR:crm_person.mlang}</b></td>
				</tr>
				<!-- END SUB: CRM_PERSON.MLANG -->
				<!-- SUB: CRM_PERSON.EDULEVEL -->
				<tr>
					<td width="174" height="20" align="left" valign="middle" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;"><strong>Haridutase:&nbsp;</strong></td>
					<td class="text11px" width="372" height="20" align="left" style="padding-left:10px;"><b>{VAR:crm_person.edulevel}</b></td>
				</tr>
				<!-- END SUB: CRM_PERSON.EDULEVEL -->
				<!-- SUB: CRM_PERSON.ACADEMIC_DEGREE -->
				<tr>
					<td width="174" height="20" align="left" valign="middle" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;"><strong>Akadeemiline kraad:&nbsp;</strong></td>
					<td class="text11px" width="372" height="20" align="left" style="padding-left:10px;"><b>{VAR:crm_person.academic_degree}</b></td>
				</tr>
				<!-- END SUB: CRM_PERSON.ACADEMIC_DEGREE -->
				<!-- SUB: CRM_PERSON.SOCIAL_STATUS -->
				<tr>
					<td width="174" height="20" align="left" valign="middle" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;"><strong>Perekonnaseis:&nbsp;</strong></td>
					<td class="text11px" width="372" height="20" align="left" style="padding-left:10px;"><b>{VAR:crm_person.social_status}</b></td>
				</tr>
				<!-- END SUB: CRM_PERSON.SOCIAL_STATUS -->
				<!-- SUB: CRM_PERSON.CHILDREN1 -->
				<tr>
					<td width="174" height="20" align="left" valign="middle" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;"><strong>Laste arv:&nbsp;</strong></td>
					<td class="text11px" width="372" height="20" align="left" style="padding-left:10px;"><b>{VAR:crm_person.children1}</b></td>
				</tr>
				<!-- END SUB: CRM_PERSON.CHILDREN1 -->
				<!-- SUB: CRM_PERSON.MODIFIED -->
				<tr>
					<td width="174" height="20" align="left" valign="middle" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;"><strong>CV uuendamise kuupäev:&nbsp;</strong></td>
					<td class="text11px" width="372" height="20" align="left" valign="top" style="padding-left:10px;">{VAR:crm_person.modified}</td>
				</tr>
				<!-- END SUB: CRM_PERSON.MODIFIED -->
				<tr>
					<td class="cvVormSpacer" colspan="2">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: CRM_PERSON.PERSONAL_INFO -->

			<!-- SUB: PREVIOUS_CANDIDACIES -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" colspan="4" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>Varasemad kandideerimised: {VAR:personnel_management.owner_org}</strong></span></td>
				</tr>
				<tr>
					<td width="240" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Ametikoht</td>
					<td width="240" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Valdkond</td>
					<td width="66" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">T&auml;htaeg</td>
				</tr>
					<!-- SUB: PREVIOUS_CANDIDACY -->
				<tr>
					<td width="240" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_offer.profession}</td>
					<td width="240" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_offer.field}</td>
					<td width="66" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_offer.end}</td>
				</tr>
					<!-- END SUB: PREVIOUS_CANDIDACY -->
				<tr>
					<td class="cvVormSpacer" colspan="4">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: PREVIOUS_CANDIDACIES -->

			<!-- SUB: CITIZENSHIPS -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" colspan="2" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>Kodakondsus</strong></span></td>
				</tr>
				<!-- SUB: CITIZENSHIP -->
				<tr>
					<td width="174" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">{VAR:citizenship.country}</td>
					<td class="text11px" height="20" width="372" align="left" style="padding-left:10px;">Alates {VAR:citizenship.start} kuni {VAR:citizenship.end}</td>
				</tr>
				<!-- END SUB: CITIZENSHIP -->
				<tr>
					<td class="cvVormSpacer" colspan="2">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: CITIZENSHIPS -->

			<!-- SUB: CRM_PERSON_EDUCATIONS -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" colspan="11" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>Haridus</strong></span></td>
				</tr>
				<tr>
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Kool</td>
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Akadeemiline kraad</td>
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Valdkond</td>
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Eriala</td>
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">P&otilde;hieriala</td>
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Omandamisel</td>
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Omandamise keel</td>
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Algus</td>
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">L&otilde;pp</td>
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">L&otilde;petamise kuup&auml;ev</td>
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Diplomi nr</td>
				</tr>
				<!-- SUB: CRM_PERSON_EDUCATION -->
				<tr>
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.school}</td>
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.degree}</td>
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.field}</td>
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.speciality}</td>
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.main_speciality}</td>
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.in_progress}</td>
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.obtain_language}</td>
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.start}</td>
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.end}</td>
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.end_date}</td>
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.diploma_nr}</td>
				</tr>
				<!-- END SUB: CRM_PERSON_EDUCATION -->
				<tr>
					<td class="cvVormSpacer" colspan="11">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: CRM_PERSON_EDUCATIONS -->

			<!-- SUB: CRM_PERSON_ADD_EDUCATIONS -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" colspan="4" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>T&auml;iendkoolitus</strong></span></td>
				</tr>
				<tr>
					<td width="173" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Ettev&otilde;te</td>
					<td width="173" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Teema</td>
					<td width="100" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Algus</td>
					<td width="100" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Kestvus p&auml;evades</td>
				</tr>
				<!-- SUB: CRM_PERSON_ADD_EDUCATION -->
				<tr>
					<td width="173" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_add_education.org}</td>
					<td width="173" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_add_education.field}</td>
					<td width="100" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_add_education.time}</td>
					<td width="100" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_add_education.length}</td>
				</tr>
				<!-- END SUB: CRM_PERSON_ADD_EDUCATION -->
				<tr>
					<td class="cvVormSpacer" colspan="4">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: CRM_PERSON_ADD_EDUCATIONS -->

			<!-- SUB: CRM_PERSON_LANGUAGES -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" colspan="4" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>Keeleoskus</strong></span></td>
				</tr>
				<!-- SUB: CRM_PERSON_LANGUAGE -->
				<tr>
					<td width="96" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">{VAR:crm_person_language.language}</td>
					<td width="150" height="20" align="left" class="text11px" style="padding-left:10px;">R&auml;&auml;gin: {VAR:crm_person_language.talk}</td>
					<td width="150" height="20" align="left" class="text11px" style="padding-left:10px;">Saan aru: {VAR:crm_person_language.understand}</td>
					<td width="150" height="20" align="left" class="text11px" style="padding-left:10px;">Kirjutan: {VAR:crm_person_language.write}</td>
				</tr>
				<!-- END SUB: CRM_PERSON_LANGUAGE -->
				<tr>
					<td class="cvVormSpacer" colspan="4">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: CRM_PERSON_LANGUAGES -->

			<!-- SUB: CRM_SKILL -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" colspan="2" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>{VAR:crm_skill}</strong></span></td>
				</tr>
				<!-- SUB: CRM_SKILL_LEVEL -->
				<tr>
					<td width="372" height="20" align="left" class="link11px" style="padding-left:10px;" bgcolor="#EBEBEB">{VAR:crm_skill_level.skill}</td>
					<td width="174" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_skill_level.level}</td>
				</tr>
				<!-- END SUB: CRM_SKILL_LEVEL -->
				<tr>
					<td class="cvVormSpacer" colspan="2">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: CRM_SKILL -->

			<!-- SUB: CRM_PERSON.DRIVERS_LICENSE -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" colspan="2" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>Autojuhiload</strong></span></td>
				</tr>
				<tr>
					<td width="174" height="20" align="left" class="link11px" style="padding-left:10px;" bgcolor="#EBEBEB">Kategooriad</td>
					<td width="372" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person.drivers_license}</td>
				</tr>
				<tr>
					<td width="174" height="20" align="left" class="link11px" style="padding-left:10px;" bgcolor="#EBEBEB">Isikliku auto kasutamisv&otilde;imalus</td>
					<td width="372" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person.dl_can_use}</td>
				</tr>
				<tr>
					<td class="cvVormSpacer" colspan="2">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: CRM_PERSON.DRIVERS_LICENSE -->

			<!-- SUB: CRM_COMPANY_RELATIONS -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" colspan="4" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>Organisatoorne kuuluvus</strong></span></td>
				</tr>
				<tr>
					<td width="173" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Organisatsioon</td>
					<td width="100" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Algus</td>
					<td width="100" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">L&otilde;pp</td>
					<td width="173" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Lisainfo</td>
				</tr>
					<!-- SUB: CRM_COMPANY_RELATION -->
				<tr>
					<td width="173" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_company_relation.org}</td>
					<td width="100" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_company_relation.start}</td>
					<td width="100" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_company_relation.end}</td>
					<td width="173" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_company_relation.add_info}</td>
				</tr>
					<!-- END SUB: CRM_COMPANY_RELATION -->
				<tr>
					<td class="cvVormSpacer" colspan="4">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: CRM_COMPANY_RELATIONS -->

			<!-- SUB: CRM_PERSON_WORK_RELATIONS -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" colspan="8" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>T&ouml;&ouml;kogemus</strong></span></td>
				</tr>
				<tr>
					<td width="70" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Organisatsioon</td>
					<td width="68" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">&Uuml;ksus</td>
					<td width="68" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Amet</td>
					<td width="68" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Algus</td>
					<td width="68" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">L&otilde;pp</td>
					<td width="68" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">&Uuml;lesanded</td>
					<td width="68" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Koormus</td>
					<td width="68" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Kuutasu (bruto)</td>
				</tr>
					<!-- SUB: CRM_PERSON_WORK_RELATION -->
				<tr>
					<td width="70" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_work_relation.org}</td>
					<td width="68" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_work_relation.section}</td>
					<td width="68" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_work_relation.profession}</td>
					<td width="68" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_work_relation.start}</td>
					<td width="68" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_work_relation.end}</td>
					<td width="68" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_work_relation.tasks}</td>
					<td width="68" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_work_relation.load}</td>
					<td width="68" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_work_relation.salary}</td>
				</tr>
					<!-- END SUB: CRM_PERSON_WORK_RELATION -->
				<tr>
					<td class="cvVormSpacer" colspan="8">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: CRM_PERSON_WORK_RELATIONS -->

			<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" colspan="8" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>Soovitud t&ouml;&ouml;</strong></span></td>
				</tr>
				<tr>
					<td width="68" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Tegevusala</td>
					<td width="68" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">T&ouml;&ouml; liik</td>
					<td width="68" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Ametid</td>
					<td width="68" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Koormus</td>
					<td width="68" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Palk</td>
					<td width="68" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Asukoht</td>
					<td width="68" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Lisainfo</td>
					<td width="70" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Soovitajad</td>
				</tr>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED -->
				<tr>
					<td width="68" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.field}</td>
					<td width="68" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.job_type}</td>
					<td width="68" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.professions}</td>
					<td width="68" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.load}</td>
					<td width="68" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.pay} - {VAR:personnel_management_job_wanted.pay2}</td>
					<td width="68" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.location}</td>
					<td width="68" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.addinfo}</td>
					<td width="70" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:recommendation.person}</td>
				</tr>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED -->
				<tr>
					<td class="cvVormSpacer" colspan="8">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED -->

			<!-- SUB: PERSONNEL_MANAGEMENT_CANDIDATES -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" colspan="4" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>Aktiivsed kandideerimised</strong></span></td>
				</tr>
				<tr>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Organisatsioon</td>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Ametikoht</td>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Valdkond</td>
					<td width="96" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">T&auml;htaeg</td>
				</tr>
					<!-- SUB: PERSONNEL_MANAGEMENT_CANDIDATE -->
				<tr>
					<td width="150" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_offer.company}</td>
					<td width="150" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_offer.profession}</td>
					<td width="150" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_offer.field}</td>
					<td width="96" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_offer.end}</td>
				</tr>
					<!-- END SUB: PERSONNEL_MANAGEMENT_CANDIDATE -->
				<tr>
					<td class="cvVormSpacer" colspan="4">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: PERSONNEL_MANAGEMENT_CANDIDATES -->

			<!-- SUB: CRM_PERSON.CONTACT -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" colspan="2" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>Kontakt</strong></span></td>
				</tr>
				<!-- SUB: CRM_PERSON.PHONES -->
				<tr>
					<td width="174" height="20" align="left" valign="top" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;"><strong>Telefon:&nbsp;</strong></td>
					<td class="text11px" height="20" width="372" align="left" style="padding-left:10px;">
						<!-- SUB: CRM_PERSON.PHONE -->
						{VAR:crm_person.phone.name} ({VAR:crm_person.phone.type})<br>
						<!-- END SUB: CRM_PERSON.PHONE -->
					</td>
				</tr>
				<!-- END SUB: CRM_PERSON.PHONES -->
				<!-- SUB: CRM_PERSON.FAXES -->
				<tr>
					<td width="174" height="20" align="left" valign="top" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;"><strong>Faks:&nbsp;</strong></td>
					<td class="text11px" height="20" width="372" align="left" style="padding-left:10px;">
						<!-- SUB: CRM_PERSON.FAX -->
						{VAR:crm_person.fax}<br>
						<!-- END SUB: CRM_PERSON.FAX -->
					</td>
				</tr>
				<!-- END SUB: CRM_PERSON.FAXES -->
				<!-- SUB: CRM_PERSON.EMAILS -->
				<tr>
					<td width="174" height="20" align="left" valign="top" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;"><strong>E-post:&nbsp;</strong></td>
					<td class="text11px" height="20" width="372" align="left" style="padding-left:10px;">
						<!-- SUB: CRM_PERSON.EMAIL -->
						{VAR:crm_person.email}<br>
						<!-- END SUB: CRM_PERSON.EMAIL -->
					</td>
				</tr>
				<!-- END SUB: CRM_PERSON.EMAILS -->
				<!-- SUB: CRM_PERSON.ADDRESSES -->
				<tr>
					<td width="174" height="20" align="left" valign="top" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;"><strong>Aadress:&nbsp;</strong></td>
					<td class="text11px" height="20" width="372" align="left" style="padding-left:10px;">
						<!-- SUB: CRM_PERSON.ADDRESS -->
						{VAR:crm_person.address.aadress}, {VAR:crm_person.address.linn} {VAR:crm_person.address.postiindeks}, {VAR:crm_person.address.maakond}, {VAR:crm_person.address.piirkond}, {VAR:crm_person.address.riik}<br>
						<!-- END SUB: CRM_PERSON.ADDRESS -->
					</td>
				</tr>
				<!-- END SUB: CRM_PERSON.ADDRESSES -->
				<tr>
					<td class="cvVormSpacer" colspan="2">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: CRM_PERSON.CONTACT -->
		</td>
		<td valign="top">
			<!-- SUB: CRM_PERSON.PICTURE -->
			{VAR:crm_person.picture}
			<!-- END SUB: CRM_PERSON.PICTURE -->
		</td>
	<tr>
</table>