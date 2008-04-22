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
						<a href="mailto:{VAR:crm_person.email}">{VAR:crm_person.email}</a><br>
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

			<!-- SUB: PREVIOUS_CANDIDACIES -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" colspan="5" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>Varasemad kandideerimised: {VAR:personnel_management.owner_org}</strong></span></td>
				</tr>
				<!-- SUB: PREVIOUS_CANDIDACIES.HEADER -->
				<tr>
					<!-- SUB: PREVIOUS_CANDIDACIES.HEADER.PROFESSION -->
					<td width="165" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Ametikoht</td>
					<!-- END SUB: PREVIOUS_CANDIDACIES.HEADER.PROFESSION -->
					<!-- SUB: PREVIOUS_CANDIDACIES.HEADER.FIELD -->
					<td width="165" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Valdkond</td>
					<!-- END SUB: PREVIOUS_CANDIDACIES.HEADER.FIELD -->
					<!-- SUB: PREVIOUS_CANDIDACIES.HEADER.END -->
					<td width="66" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">T&auml;htaeg</td>
					<!-- END SUB: PREVIOUS_CANDIDACIES.HEADER.END -->
					<!-- SUB: PREVIOUS_CANDIDACIES.HEADER.RATING -->
					<td width="75" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Kandidatuuri keskmine hinne</td>
					<!-- END SUB: PREVIOUS_CANDIDACIES.HEADER.RATING -->
					<!-- SUB: PREVIOUS_CANDIDACIES.HEADER.ADDINFO -->
					<td width="75" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Lisainfo</td>
					<!-- END SUB: PREVIOUS_CANDIDACIES.HEADER.ADDINFO -->
				</tr>
				<!-- END SUB: PREVIOUS_CANDIDACIES.HEADER -->
				<!-- SUB: PREVIOUS_CANDIDACY -->
				<tr>
					<!-- SUB: PREVIOUS_CANDIDACY.PROFESSION -->
					<td width="165" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_offer.profession}</td>
					<!-- END SUB: PREVIOUS_CANDIDACY.PROFESSION -->
					<!-- SUB: PREVIOUS_CANDIDACY.FIELD -->
					<td width="165" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_offer.field}</td>
					<!-- END SUB: PREVIOUS_CANDIDACY.FIELD -->
					<!-- SUB: PREVIOUS_CANDIDACY.END -->
					<td width="66" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_offer.end}</td>
					<!-- END SUB: PREVIOUS_CANDIDACY.END -->
					<!-- SUB: PREVIOUS_CANDIDACY.RATING -->
					<td width="75" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_offer.rating}</td>
					<!-- END SUB: PREVIOUS_CANDIDACY.RATING -->
					<!-- SUB: PREVIOUS_CANDIDACY.ADDINFO -->
					<td width="75" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_offer.addinfo}</td>
					<!-- END SUB: PREVIOUS_CANDIDACY.ADDINFO -->
				</tr>
				<!-- END SUB: PREVIOUS_CANDIDACY -->
				<tr>
					<td class="cvVormSpacer" colspan="5">&nbsp;</td>
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
				<!-- SUB: CRM_PERSON_EDUCATIONS.HEADER -->
				<tr>
					<!-- SUB: CRM_PERSON_EDUCATIONS.HEADER.SCHOOL -->
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Kool</td>
					<!-- END SUB: CRM_PERSON_EDUCATIONS.HEADER.SCHOOL -->
					<!-- SUB: CRM_PERSON_EDUCATIONS.HEADER.DEGREE -->
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Akadeemiline kraad</td>
					<!-- END SUB: CRM_PERSON_EDUCATIONS.HEADER.DEGREE -->
					<!-- SUB: CRM_PERSON_EDUCATIONS.HEADER.FIELD -->
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Valdkond</td>
					<!-- END SUB: CRM_PERSON_EDUCATIONS.HEADER.FIELD -->
					<!-- SUB: CRM_PERSON_EDUCATIONS.HEADER.SPECIALITY  -->
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Eriala</td>
					<!-- END SUB: CRM_PERSON_EDUCATIONS.HEADER.SPECIALITY -->
					<!-- SUB: CRM_PERSON_EDUCATIONS.HEADER.MAIN_SPECIALITY -->
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">P&otilde;hieriala</td>
					<!-- END SUB: CRM_PERSON_EDUCATIONS.HEADER.MAIN_SPECIALITY -->
					<!-- SUB: CRM_PERSON_EDUCATIONS.HEADER.IN_PROGRESS -->
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Omandamisel</td>
					<!-- END SUB: CRM_PERSON_EDUCATIONS.HEADER.IN_PROGRESS -->
					<!-- SUB: CRM_PERSON_EDUCATIONS.HEADER.OBTAIN_LANGUAGE -->
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Omandamise keel</td>
					<!-- END SUB: CRM_PERSON_EDUCATIONS.HEADER.OBTAIN_LANGUAGE -->
					<!-- SUB: CRM_PERSON_EDUCATIONS.HEADER.START -->
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Algus</td>
					<!-- END SUB: CRM_PERSON_EDUCATIONS.HEADER.START -->
					<!-- SUB: CRM_PERSON_EDUCATIONS.HEADER.END -->
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">L&otilde;pp</td>
					<!-- END SUB: CRM_PERSON_EDUCATIONS.HEADER.END -->
					<!-- SUB: CRM_PERSON_EDUCATIONS.HEADER.END_DATE -->
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">L&otilde;petamise kuup&auml;ev</td>
					<!-- END SUB: CRM_PERSON_EDUCATIONS.HEADER.END_DATE -->
					<!-- SUB: CRM_PERSON_EDUCATIONS.HEADER.DIPLOMA_NR -->
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Diplomi nr</td>
					<!-- END SUB: CRM_PERSON_EDUCATIONS.HEADER.DIPLOMA_NR -->
				</tr>
				<!-- END SUB: CRM_PERSON_EDUCATIONS.HEADER -->
				<!-- SUB: CRM_PERSON_EDUCATION -->
				<tr>
					<!-- SUB: CRM_PERSON_EDUCATION.SCHOOL -->
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.school}</td>
					<!-- END SUB: CRM_PERSON_EDUCATION.SCHOOL -->
					<!-- SUB: CRM_PERSON_EDUCATION.DEGREE -->
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.degree}</td>
					<!-- END SUB: CRM_PERSON_EDUCATION.DEGREE -->
					<!-- SUB: CRM_PERSON_EDUCATION.FIELD -->
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.field}</td>
					<!-- END SUB: CRM_PERSON_EDUCATION.FIELD -->
					<!-- SUB: CRM_PERSON_EDUCATION.SPECIALITY  -->
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.speciality}</td>
					<!-- END SUB: CRM_PERSON_EDUCATION.SPECIALITY -->
					<!-- SUB: CRM_PERSON_EDUCATION.MAIN_SPECIALITY -->
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.main_speciality}</td>
					<!-- END SUB: CRM_PERSON_EDUCATION.MAIN_SPECIALITY -->
					<!-- SUB: CRM_PERSON_EDUCATION.IN_PROGRESS -->
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.in_progress}</td>
					<!-- END SUB: CRM_PERSON_EDUCATION.IN_PROGRESS -->
					<!-- SUB: CRM_PERSON_EDUCATION.OBTAIN_LANGUAGE -->
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.obtain_language}</td>
					<!-- END SUB: CRM_PERSON_EDUCATION.OBTAIN_LANGUAGE -->
					<!-- SUB: CRM_PERSON_EDUCATION.START -->
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.start}</td>
					<!-- END SUB: CRM_PERSON_EDUCATION.START -->
					<!-- SUB: CRM_PERSON_EDUCATION.END -->
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.end}</td>
					<!-- END SUB: CRM_PERSON_EDUCATION.END -->
					<!-- SUB: CRM_PERSON_EDUCATION.END_DATE -->
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.end_date}</td>
					<!-- END SUB: CRM_PERSON_EDUCATION.END_DATE -->
					<!-- SUB: CRM_PERSON_EDUCATION.DIPLOMA_NR -->
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_education.diploma_nr}</td>
					<!-- END SUB: CRM_PERSON_EDUCATION.DIPLOMA_NR -->
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
					<td height="20" colspan="6" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>T&auml;iendkoolitus</strong></span></td>
				</tr>
				<!-- SUB: CRM_PERSON_ADD_EDUCATIONS.HEADER -->
				<tr>
					<!-- SUB: CRM_PERSON_ADD_EDUCATIONS.HEADER.ORG -->
					<td width="173" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Ettev&otilde;te</td>
					<!-- END SUB: CRM_PERSON_ADD_EDUCATIONS.HEADER.ORG -->
					<!-- SUB: CRM_PERSON_ADD_EDUCATIONS.HEADER.FIELD -->
					<td width="173" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Teema</td>
					<!-- END SUB: CRM_PERSON_ADD_EDUCATIONS.HEADER.FIELD -->
					<!-- SUB: CRM_PERSON_ADD_EDUCATIONS.HEADER.TIME -->
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Algus</td>
					<!-- END SUB: CRM_PERSON_ADD_EDUCATIONS.HEADER.TIME -->
					<!-- SUB: CRM_PERSON_ADD_EDUCATIONS.HEADER.TIME_TEXT -->
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Aeg</td>
					<!-- END SUB: CRM_PERSON_ADD_EDUCATIONS.HEADER.TIME_TEXT -->
					<!-- SUB: CRM_PERSON_ADD_EDUCATIONS.HEADER.LENGTH -->
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Maht p&auml;evades</td>
					<!-- END SUB: CRM_PERSON_ADD_EDUCATIONS.HEADER.LENGTH -->
					<!-- SUB: CRM_PERSON_ADD_EDUCATIONS.HEADER.LENGTH_HRS -->
					<td width="50" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Maht tundides</td>
					<!-- END SUB: CRM_PERSON_ADD_EDUCATIONS.HEADER.LENGTH_HRS -->
				</tr>
				<!-- END SUB: CRM_PERSON_ADD_EDUCATIONS.HEADER -->
				<!-- SUB: CRM_PERSON_ADD_EDUCATION -->
				<tr>
					<!-- SUB: CRM_PERSON_ADD_EDUCATION.ORG -->
					<td width="173" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_add_education.org}</td>
					<!-- END SUB: CRM_PERSON_ADD_EDUCATION.ORG -->
					<!-- SUB: CRM_PERSON_ADD_EDUCATION.FIELD -->
					<td width="173" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_add_education.field}</td>
					<!-- END SUB: CRM_PERSON_ADD_EDUCATION.FIELD -->
					<!-- SUB: CRM_PERSON_ADD_EDUCATION.TIME -->
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_add_education.time}</td>
					<!-- END SUB: CRM_PERSON_ADD_EDUCATION.TIME -->
					<!-- SUB: CRM_PERSON_ADD_EDUCATION.TIME_TEXT -->
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_add_education.time_text}</td>
					<!-- END SUB: CRM_PERSON_ADD_EDUCATION.TIME_TEXT -->
					<!-- SUB: CRM_PERSON_ADD_EDUCATION.LENGTH -->
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_add_education.length}</td>
					<!-- END SUB: CRM_PERSON_ADD_EDUCATION.LENGTH -->
					<!-- SUB: CRM_PERSON_ADD_EDUCATION.LENGTH_HRS -->
					<td width="50" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_add_education.length_hrs}</td>
					<!-- END SUB: CRM_PERSON_ADD_EDUCATION.LENGTH_HRS -->
				</tr>
				<!-- END SUB: CRM_PERSON_ADD_EDUCATION -->
				<tr>
					<td class="cvVormSpacer" colspan="6">&nbsp;</td>
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
					<!-- SUB: CRM_PERSON_LANGUAGE.TALK -->
					<td width="150" height="20" align="left" class="text11px" style="padding-left:10px;">R&auml;&auml;gin: {VAR:crm_person_language.talk}</td>
					<!-- END SUB: CRM_PERSON_LANGUAGE.TALK -->
					<!-- SUB: CRM_PERSON_LANGUAGE.UNDERSTAND -->
					<td width="150" height="20" align="left" class="text11px" style="padding-left:10px;">Saan aru: {VAR:crm_person_language.understand}</td>
					<!-- END SUB: CRM_PERSON_LANGUAGE.UNDERSTAND -->
					<!-- SUB: CRM_PERSON_LANGUAGE.WRITE -->
					<td width="150" height="20" align="left" class="text11px" style="padding-left:10px;">Kirjutan: {VAR:crm_person_language.write}</td>
					<!-- END SUB: CRM_PERSON_LANGUAGE.WRITE -->
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
				<!-- SUB: CRM_SKILL_LEVEL_GROUP -->
				<!-- SUB: CRM_SKILL_LEVEL_SUBHEADING -->
				<tr>
					<td height="20" colspan="2" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>{VAR:crm_skill.parent}</strong></span></td>
				</tr>
				<!-- END SUB: CRM_SKILL_LEVEL_SUBHEADING -->
				<!-- SUB: CRM_SKILL_LEVEL -->
				<tr>
					<td width="372" height="20" align="left" class="link11px" style="padding-left:10px;" bgcolor="#EBEBEB">{VAR:crm_skill_level.skill}</td>
					<td width="174" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_skill_level.level}</td>
				</tr>
				<!-- END SUB: CRM_SKILL_LEVEL -->
				<!-- END SUB: CRM_SKILL_LEVEL_GROUP -->
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
				<!-- SUB: CRM_PERSON.DL_CAN_USE -->
				<tr>
					<td width="174" height="20" align="left" class="link11px" style="padding-left:10px;" bgcolor="#EBEBEB">Isikliku auto kasutamisv&otilde;imalus</td>
					<td width="372" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person.dl_can_use}</td>
				</tr>
				<!-- END SUB: CRM_PERSON.DL_CAN_USE -->
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
				<!-- SUB: CRM_COMPANY_RELATIONS.HEADER -->
				<tr>
					<!-- SUB: CRM_COMPANY_RELATIONS.HEADER.ORG -->
					<td width="173" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Organisatsioon</td>
					<!-- END SUB: CRM_COMPANY_RELATIONS.HEADER.ORG -->
					<!-- SUB: CRM_COMPANY_RELATIONS.HEADER.START -->
					<td width="100" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Algus</td>
					<!-- END SUB: CRM_COMPANY_RELATIONS.HEADER.START -->
					<!-- SUB: CRM_COMPANY_RELATIONS.HEADER.END -->
					<td width="100" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">L&otilde;pp</td>
					<!-- END SUB: CRM_COMPANY_RELATIONS.HEADER.END -->
					<!-- SUB: CRM_COMPANY_RELATIONS.HEADER.ADD_INFO -->
					<td width="173" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Lisainfo</td>
					<!-- END SUB: CRM_COMPANY_RELATIONS.HEADER.ADD_INFO -->
				</tr>
				<!-- END SUB: CRM_COMPANY_RELATIONS.HEADER -->
				<!-- SUB: CRM_COMPANY_RELATION -->
				<tr>
					<!-- SUB: CRM_COMPANY_RELATION.ORG -->
					<td width="173" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_company_relation.org}</td>
					<!-- END SUB: CRM_COMPANY_RELATION.ORG -->
					<!-- SUB: CRM_COMPANY_RELATION.START -->
					<td width="100" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_company_relation.start}</td>
					<!-- END SUB: CRM_COMPANY_RELATION.START -->
					<!-- SUB: CRM_COMPANY_RELATION.END -->
					<td width="100" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_company_relation.end}</td>
					<!-- END SUB: CRM_COMPANY_RELATION.END -->
					<!-- SUB: CRM_COMPANY_RELATION.ADD_INFO -->
					<td width="173" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_company_relation.add_info}</td>
					<!-- END SUB: CRM_COMPANY_RELATION.ADD_INFO -->
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
					<td height="20" colspan="10" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>T&ouml;&ouml;kogemus</strong></span></td>
				</tr>
				<!-- SUB: CRM_PERSON_WORK_RELATIONS.HEADER -->
				<tr>
					<!-- SUB: CRM_PERSON_WORK_RELATIONS.HEADER.ORG -->
					<td width="66" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Organisatsioon</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATIONS.HEADER.ORG -->
					<!-- SUB: CRM_PERSON_WORK_RELATIONS.HEADER.SECTION -->
					<td width="60" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">&Uuml;ksus</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATIONS.HEADER.SECTION -->
					<!-- SUB: CRM_PERSON_WORK_RELATIONS.HEADER.PROFESSION -->
					<td width="60" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Amet</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATIONS.HEADER.PROFESSION -->
					<!-- SUB: CRM_PERSON_WORK_RELATIONS.HEADER.START -->
					<td width="60" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Algus</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATIONS.HEADER.START -->
					<!-- SUB: CRM_PERSON_WORK_RELATIONS.HEADER.END -->
					<td width="60" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">L&otilde;pp</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATIONS.HEADER.END -->
					<!-- SUB: CRM_PERSON_WORK_RELATIONS.HEADER.TASKS -->
					<td width="60" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">&Uuml;lesanded</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATIONS.HEADER.TASKS -->
					<!-- SUB: CRM_PERSON_WORK_RELATIONS.HEADER.LOAD -->
					<td width="60" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Koormus</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATIONS.HEADER.LOAD -->
					<!-- SUB: CRM_PERSON_WORK_RELATIONS.HEADER.SALARY -->
					<td width="60" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Kuutasu (bruto)</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATIONS.HEADER.SALARY -->
					<!-- SUB: CRM_PERSON_WORK_RELATIONS.HEADER.BENEFITS -->
					<td width="60" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Soodustused ja eritingimused</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATIONS.HEADER.BENEFITS -->
					<!-- SUB: CRM_PERSON_WORK_RELATIONS.HEADER.FIELD -->
					<td width="60" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Valdkond</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATIONS.HEADER.FIELD -->
				</tr>
				<!-- END SUB: CRM_PERSON_WORK_RELATIONS.HEADER -->
				<!-- SUB: CRM_PERSON_WORK_RELATION -->
				<tr>
					<!-- SUB: CRM_PERSON_WORK_RELATION.ORG -->
					<td width="66" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_work_relation.org}</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATION.ORG -->
					<!-- SUB: CRM_PERSON_WORK_RELATION.SECTION -->
					<td width="60" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_work_relation.section}</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATION.SECTION -->
					<!-- SUB: CRM_PERSON_WORK_RELATION.PROFESSION -->
					<td width="60" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_work_relation.profession}</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATION.PROFESSION -->
					<!-- SUB: CRM_PERSON_WORK_RELATION.START -->
					<td width="60" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_work_relation.start}</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATION.START -->
					<!-- SUB: CRM_PERSON_WORK_RELATION.END -->
					<td width="60" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_work_relation.end}</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATION.END -->
					<!-- SUB: CRM_PERSON_WORK_RELATION.TASKS -->
					<td width="60" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_work_relation.tasks}</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATION.TASKS -->
					<!-- SUB: CRM_PERSON_WORK_RELATION.LOAD -->
					<td width="60" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_work_relation.load}</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATION.LOAD -->
					<!-- SUB: CRM_PERSON_WORK_RELATION.SALARY -->
					<td width="60" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_work_relation.salary}</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATION.SALARY -->
					<!-- SUB: CRM_PERSON_WORK_RELATION.BENEFITS -->
					<td width="60" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_work_relation.benefits}</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATION.BENEFITS -->
					<!-- SUB: CRM_PERSON_WORK_RELATION.FIELD -->
					<td width="60" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_person_work_relation.field}</td>
					<!-- END SUB: CRM_PERSON_WORK_RELATION.FIELD -->
				</tr>
				<!-- END SUB: CRM_PERSON_WORK_RELATION -->
				<tr>
					<td class="cvVormSpacer" colspan="10">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: CRM_PERSON_WORK_RELATIONS -->

			<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" colspan="17" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>Soovitud t&ouml;&ouml;</strong></span></td>
				</tr>
				<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER -->
				<tr>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.FIELD -->
					<td width="32" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Tegevusala</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.FIELD -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.JOB_TYPE -->
					<td width="32" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">T&ouml;&ouml; liik</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.JOB_TYPE -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.PROFESSIONS_RELS -->
					<td width="32" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Ametid</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.PROFESSIONS_RELS -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.PROFESSIONS -->
					<td width="32" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Ametid (vabatekst)</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.PROFESSIONS -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.LOAD -->
					<td width="32" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Koormus</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.LOAD -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.PAY -->
					<td width="32" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Palk</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.PAY -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.LOCATION -->
					<td width="32" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Asukoht (esimene eelistus)</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.LOCATION -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.LOCATION_2 -->
					<td width="32" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Asukoht (teine eelistus)</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.LOCATION_2 -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.LOCATION_TEXT -->
					<td width="32" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Asukoht (t&auml;psemalt)</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.LOCATION_TEXT -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.ADDINFO -->
					<td width="32" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Lisainfo</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.ADDINFO -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.WORK_AT_NIGHT -->
					<td width="32" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">N&otilde;us t&ouml;&ouml;tama &ouml;&ouml;sel</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.WORK_AT_NIGHT -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.WORK_BY_SCHEDULE -->
					<td width="32" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">N&otilde;us t&ouml;&ouml;tama graafiku alusel</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.WORK_BY_SCHEDULE -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.START_WORKING -->
					<td width="32" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">T&ouml;&ouml;leasumise aeg</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.START_WORKING -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.READY_FOR_ERRAND -->
					<td width="32" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Valmis t&ouml;&ouml;l&auml;hetusteks</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.READY_FOR_ERRAND -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.ADDITIONAL_SKILLS -->
					<td width="32" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">T&auml;iendavad oskused</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.ADDITIONAL_SKILLS -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.HANDICAPS -->
					<td width="32" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Takistavad tegurid</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.HANDICAPS -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.HOBBIES_VS_WORK -->
					<td width="32" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Hobid, mille t&otilde;ttu on vajalik t&ouml;&ouml;lt eemal viibida</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER.HOBBIES_VS_WORK -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED.HEADER -->
				<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED -->
				<tr>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.FIELD -->
					<td width="32" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.field}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.FIELD -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.JOB_TYPE -->
					<td width="32" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.job_type}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.JOB_TYPE -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.PROFESSIONS_RELS -->
					<td width="32" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.professions_rels}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.PROFESSIONS_RELS -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.PROFESSIONS -->
					<td width="32" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.professions}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.PROFESSIONS -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.LOAD -->
					<td width="32" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.load}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.LOAD -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.PAY -->
					<td width="32" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.pay}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.PAY -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.LOCATION -->
					<td width="32" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.location}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.LOCATION -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.LOCATION_2 -->
					<td width="32" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.location_2}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.LOCATION_2 -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.LOCATION_TEXT -->
					<td width="32" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.location_text}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.LOCATION_TEXT -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.ADDINFO -->
					<td width="32" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.addinfo}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.ADDINFO -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.WORK_AT_NIGHT -->
					<td width="32" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.work_at_night}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.WORK_AT_NIGHT -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.WORK_BY_SCHEDULE -->
					<td width="32" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.work_by_schedule}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.WORK_BY_SCHEDULE -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.START_WORKING -->
					<td width="32" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.start_working}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.START_WORKING -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.READY_FOR_ERRAND -->
					<td width="32" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.ready_for_errand}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.READY_FOR_ERRAND -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.ADDITIONAL_SKILLS -->
					<td width="32" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.additional_skills}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.ADDITIONAL_SKILLS -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.HANDICAPS -->
					<td width="32" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.handicaps}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.HANDICAPS -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.HOBBIES_VS_WORK -->
					<td width="32" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.hobbies_vs_work}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.HOBBIES_VS_WORK -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED -->
				<tr>
					<td class="cvVormSpacer" colspan="17">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED -->

			<!-- SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED_VERTICAL -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>Soovitud t&ouml;&ouml;</strong></span></td>
				</tr>
			</table>
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.FIELD.VERTICAL -->
				<tr>
					<td width="150" height="20" width="100" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Tegevusala</td>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.FIELD -->
					<td height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.field}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.FIELD -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.FIELD.VERTICAL -->
				<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.JOB_TYPE.VERTICAL -->
				<tr>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">T&ouml;&ouml; liik</td>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.JOB_TYPE -->
					<td height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.job_type}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.JOB_TYPE -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.JOB_TYPE.VERTICAL -->
				<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.PROFESSIONS_RELS.VERTICAL -->
				<tr>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Ametid</td>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.PROFESSIONS_RELS -->
					<td height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.professions_rels}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.PROFESSIONS_RELS -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.PROFESSIONS_RELS.VERTICAL -->
				<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.PROFESSIONS.VERTICAL -->
				<tr>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Ametid (vabatekst)</td>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.PROFESSIONS -->
					<td height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.professions}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.PROFESSIONS -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.PROFESSIONS.VERTICAL -->
				<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.LOAD.VERTICAL -->
				<tr>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Koormus</td>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.LOAD -->
					<td height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.load}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.LOAD -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.LOAD.VERTICAL -->
				<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.PAY.VERTICAL -->
				<tr>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Palk</td>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.PAY -->
					<td height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.pay}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.PAY -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.PAY.VERTICAL -->
				<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.LOCATION.VERTICAL -->
				<tr>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Asukoht (esimene eelistus)</td>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.LOCATION -->
					<td height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.location}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.LOCATION -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.LOCATION.VERTICAL -->
				<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.LOCATION_2.VERTICAL -->
				<tr>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Asukoht (teine eelistus)</td>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.LOCATION_2 -->
					<td height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.location_2}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.LOCATION_2 -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.LOCATION_2.VERTICAL -->
				<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.LOCATION_TEXT.VERTICAL -->
				<tr>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Asukoht (t&auml;psemalt)</td>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.LOCATION_TEXT -->
					<td height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.location_text}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.LOCATION_TEXT -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.LOCATION_TEXT.VERTICAL -->
				<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.ADDINFO.VERTICAL -->
				<tr>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Lisainfo</td>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.ADDINFO -->
					<td height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.addinfo}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.ADDINFO -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.ADDINFO.VERTICAL -->
				<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.WORK_AT_NIGHT.VERTICAL -->
				<tr>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">N&otilde;us t&ouml;&ouml;tama &ouml;&ouml;sel</td>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.WORK_AT_NIGHT -->
					<td height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.work_at_night}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.WORK_AT_NIGHT -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.WORK_AT_NIGHT.VERTICAL -->
				<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.WORK_BY_SCHEDULE.VERTICAL -->
				<tr>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">N&otilde;us t&ouml;&ouml;tama graafiku alusel</td>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.WORK_BY_SCHEDULE -->
					<td height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.work_by_schedule}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.WORK_BY_SCHEDULE -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.WORK_BY_SCHEDULE.VERTICAL -->
				<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.START_WORKING.VERTICAL -->
				<tr>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">T&ouml;&ouml;leasumise aeg</td>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.START_WORKING -->
					<td height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.start_working}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.START_WORKING -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.START_WORKING.VERTICAL -->
				<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.READY_FOR_ERRAND.VERTICAL -->
				<tr>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Valmis t&ouml;&ouml;l&auml;hetusteks</td>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.READY_FOR_ERRAND -->
					<td height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.ready_for_errand}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.READY_FOR_ERRAND -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.READY_FOR_ERRAND.VERTICAL -->
				<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.ADDITIONAL_SKILLS.VERTICAL -->
				<tr>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">T&auml;iendavad oskused</td>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.ADDITIONAL_SKILLS -->
					<td height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.additional_skills}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.ADDITIONAL_SKILLS -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.ADDITIONAL_SKILLS.VERTICAL -->
				<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.HANDICAPS.VERTICAL -->
				<tr>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Takistavad tegurid</td>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.HANDICAPS -->
					<td height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.handicaps}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.HANDICAPS -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.HANDICAPS.VERTICAL -->
				<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.HOBBIES_VS_WORK.VERTICAL -->
				<tr>
					<td width="150" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Hobid, mille t&otilde;ttu on vajalik t&ouml;&ouml;lt eemal viibida</td>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.HOBBIES_VS_WORK -->
					<td height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_wanted.hobbies_vs_work}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED_VERTICAL.HOBBIES_VS_WORK -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_WANTED.HOBBIES_VS_WORK.VERTICAL -->
				<tr>
			</table>
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr>
					<td class="cvVormSpacer">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: PERSONNEL_MANAGEMENT_JOBS_WANTED_VERTICAL -->

			<!-- SUB: PERSONNEL_MANAGEMENT_CANDIDATES -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" colspan="6" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>Aktiivsed kandideerimised</strong></span></td>
				</tr>
				<!-- SUB: PERSONNEL_MANAGEMENT_CANDIDATES.HEADER -->
				<tr>
					<!-- SUB: PERSONNEL_MANAGEMENT_CANDIDATES.HEADER.COMPANY -->
					<td width="100" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Organisatsioon</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_CANDIDATES.HEADER.COMPANY -->
					<!-- SUB: PERSONNEL_MANAGEMENT_CANDIDATES.HEADER.PROFESSION -->
					<td width="100" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Ametikoht</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_CANDIDATES.HEADER.PROFESSION -->
					<!-- SUB: PERSONNEL_MANAGEMENT_CANDIDATES.HEADER.FIELD -->
					<td width="100" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Valdkond</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_CANDIDATES.HEADER.FIELD -->
					<!-- SUB: PERSONNEL_MANAGEMENT_CANDIDATES.HEADER.END -->
					<td width="96" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">T&auml;htaeg</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_CANDIDATES.HEADER.END -->
					<!-- SUB: PERSONNEL_MANAGEMENT_CANDIDATES.HEADER.RATING -->
					<td width="75" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Kandidatuuri keskmine hinne</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_CANDIDATES.HEADER.RATING -->
					<!-- SUB: PERSONNEL_MANAGEMENT_CANDIDATES.HEADER.ADDINFO -->
					<td width="75" height="20" align="left" bgcolor="#EBEBEB" class="link11px" style="padding-left:10px;">Lisainfo</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_CANDIDATES.HEADER.ADDINFO -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_CANDIDATES.HEADER -->
				<!-- SUB: PERSONNEL_MANAGEMENT_CANDIDATE -->
				<tr>
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_OFFER.COMPANY -->
					<td width="100" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_offer.company}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_OFFER.COMPANY -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_OFFER.PROFESSION -->
					<td width="100" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_offer.profession}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_OFFER.PROFESSION -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_OFFER.FIELD -->
					<td width="150" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_offer.field}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_OFFER.FIELD -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_OFFER.END -->
					<td width="96" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_offer.end}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_OFFER.END -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_OFFER.RATING -->
					<td width="75" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_offer.rating}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_OFFER.RATING -->
					<!-- SUB: PERSONNEL_MANAGEMENT_JOB_OFFER.ADDINFO -->
					<td width="75" height="20" valign="top" align="left" class="text11px" style="padding-left:10px;">{VAR:personnel_management_job_offer.addinfo}</td>
					<!-- END SUB: PERSONNEL_MANAGEMENT_JOB_OFFER.ADDINFO -->
				</tr>
				<!-- END SUB: PERSONNEL_MANAGEMENT_CANDIDATE -->
				<tr>
					<td class="cvVormSpacer" colspan="6">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: PERSONNEL_MANAGEMENT_CANDIDATES -->

			<!-- SUB: CRM_FAMILY_RELATIONS -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" colspan="3" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>Sugulased</strong></span></td>
				</tr>
				<!-- SUB: CRM_FAMILY_RELATION_0 -->
				<!-- Abikaasa -->
				<tr>
					<!-- SUB: CRM_FAMILY_RELATION.PERSON -->
					<td width="346" height="20" align="left" class="link11px" style="padding-left:10px;" bgcolor="#EBEBEB">{VAR:crm_family_relation.person}</td>
					<!-- END SUB: CRM_FAMILY_RELATION.PERSON -->
					<!-- SUB: CRM_FAMILY_RELATION.START -->
					<td width="100" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_family_relation.start}</td>
					<!-- END SUB: CRM_FAMILY_RELATION.START -->
					<!-- SUB: CRM_FAMILY_RELATION.END -->
					<td width="100" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_family_relation.end}</td>
					<!-- END SUB: CRM_FAMILY_RELATION.END -->
				</tr>
				<!-- END SUB: CRM_FAMILY_RELATION_0 -->
				<!-- SUB: CRM_FAMILY_RELATION_1 -->
				<!-- Laps -->
				<tr>
					<!-- SUB: CRM_FAMILY_RELATION.PERSON -->
					<td width="346" height="20" align="left" class="link11px" style="padding-left:10px;" bgcolor="#EBEBEB">{VAR:crm_family_relation.person}</td>
					<!-- END SUB: CRM_FAMILY_RELATION.PERSON -->
					<!-- SUB: CRM_FAMILY_RELATION.START -->
					<td width="100" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_family_relation.start}</td>
					<!-- END SUB: CRM_FAMILY_RELATION.START -->
					<!-- SUB: CRM_FAMILY_RELATION.END -->
					<td width="100" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_family_relation.end}</td>
					<!-- END SUB: CRM_FAMILY_RELATION.END -->
				</tr>
				<!-- END SUB: CRM_FAMILY_RELATION_1 -->
				<!-- SUB: CRM_FAMILY_RELATION_2 -->
				<!-- Vanem -->
				<tr>
					<!-- SUB: CRM_FAMILY_RELATION.PERSON -->
					<td width="346" height="20" align="left" class="link11px" style="padding-left:10px;" bgcolor="#EBEBEB">{VAR:crm_family_relation.person}</td>
					<!-- END SUB: CRM_FAMILY_RELATION.PERSON -->
					<!-- SUB: CRM_FAMILY_RELATION.START -->
					<td width="100" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_family_relation.start}</td>
					<!-- END SUB: CRM_FAMILY_RELATION.START -->
					<!-- SUB: CRM_FAMILY_RELATION.END -->
					<td width="100" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:crm_family_relation.end}</td>
					<!-- END SUB: CRM_FAMILY_RELATION.END -->
				</tr>
				<!-- END SUB: CRM_FAMILY_RELATION_2 -->
				<tr>
					<td class="cvVormSpacer" colspan="3">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: CRM_FAMILY_RELATIONS -->

			<!-- SUB: CRM_RECOMMENDATIONS -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" colspan="4" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>Soovitajad</strong></span></td>
				</tr>
				<!-- SUB: CRM_RECOMMENDATION -->
				<tr>
					<!-- SUB: RECOMMENDATION.PERSON -->
					<td width="196" height="20" align="left" class="link11px" style="padding-left:10px;" bgcolor="#EBEBEB">{VAR:recommendation.person}</td>
					<!-- END SUB: RECOMMENDATION.PERSON -->
					<!-- SUB: RECOMMENDATION.RELATION -->
					<td width="150" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:recommendation.relation}</td>
					<!-- END SUB: RECOMMENDATION.RELATION -->
					<!-- SUB: RECOMMENDATION.PERSON.PROFESSION -->
					<td width="150" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:recommendation.person.profession}</td>
					<!-- END SUB: RECOMMENDATION.PERSON.PROFESSION -->
					<!-- SUB: RECOMMENDATION.PERSON.COMPANY -->
					<td width="150" height="20" align="left" class="text11px" style="padding-left:10px;">{VAR:recommendation.person.company}</td>
					<!-- END SUB: RECOMMENDATION.PERSON.COMPANY -->
				</tr>
				<!-- END SUB: CRM_RECOMMENDATION -->
				<tr>
					<td class="cvVormSpacer" colspan="4">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: CRM_RECOMMENDATIONS -->

			<!-- SUB: CRM_PERSON.ADDINFO -->
			<table width="546" cellpadding="1" cellspacing="1" border="0">
				<tr bgcolor="#B1DFF2">
					<td height="20" align="left" valign="top" class="text11px" style="padding-bottom:5px; padding-top:5px; padding-left:10px;"><strong>Muud oskused</strong></span></td>
				</tr>
				<tr>
					<td width="546" height="20" align="left" class="link11px" style="padding-left:10px;">{VAR:crm_person.addinfo}</td>
				</tr>
				<tr>
					<td class="cvVormSpacer">&nbsp;</td>
				</tr>
			</table>
			<!-- END SUB: CRM_PERSON.ADDINFO -->

			<!-- SUB: CRM_PERSON.UDEF_CH1 -->
			{VAR:crm_person.udef_ch1}<br>
			<!-- END SUB: CRM_PERSON.UDEF_CH1 -->

			<!-- SUB: CRM_PERSON.USER1 -->
			{VAR:crm_person.user1}<br>
			<!-- END SUB: CRM_PERSON.USER1 -->

			<!-- SUB: CRM_PERSON.UDEF_TA1 -->
			{VAR:crm_person.udef_ta1}<br>
			<!-- END SUB: CRM_PERSON.UDEF_TA1 -->

			<!-- SUB: CRM_PERSON.USERVAR1 -->
			{VAR:crm_person.uservar1}<br>
			<!-- END SUB: CRM_PERSON.USERVAR1 -->
		</td>
		<td valign="top">
			<!-- SUB: CRM_PERSON.PICTURE -->
			{VAR:crm_person.picture}
			<!-- END SUB: CRM_PERSON.PICTURE -->
		</td>
	<tr>
</table>