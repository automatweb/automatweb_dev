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
-->
</style>
<table width="520" border=0 align="center" cellpadding=3 cellspacing=2>
	<tr bgcolor="B1DFF2">
          <td class="text11px" colspan="2" style="padding-bottom:5px; padding-top:5px; padding-left:10px; font-weight: bold;" valing="top">{VAR:company}</td>
        </tr>
        {VAR:org_description}
  <tr>
    <td bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;"><b>Pakutav ametikoht</b></td>
    <td class="text11px" style="padding-left:10px;"><b>{VAR:name}</b></td>
  </tr>
  <tr>
    <td valign="top" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;"><b>Tööülesanded</b></td>
    <td class="text11px" style="padding-left:10px;">
    {VAR:description}</td>

  </tr>
  <tr>
    <td valign="top" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;"><b>Nõudmised kandidaadile</b> </td>
    <td class="text11px" style="padding-left:10px;">
    {VAR:requirements}</td>
  </tr>
  <tr>
    <td valign="top" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;">Asukoht</td>
    <td class="text11px" style="padding-left:10px;">{VAR:location}</td>
  </tr>
  
  <tr>
    <td valign="top" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;">Tööle asumise aeg</td>
    <td class="text11px" style="padding-left:10px;">{VAR:start_date}</td>
  </tr>
  <tr>
    <td valign="top" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;">Koormus</td>
    <td class="text11px" style="padding-left:10px;">{VAR:tookoormused}</td>
  </tr>
  <tr>

    <td class="text11px" valign="top" colspan="2"  style="padding-left: 10px; padding-right:10px; padding-top: 10px; padding-bottom:10px;"><b>
      Kandideerimine eeldab CV olemasolu andmebaasis.<br/>
   </b></td>
  </tr>

  <tr>
    <td valign="top" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;">Kontaktisik</td>

    <td class="text11px" style="padding-left:10px;">{VAR:contact_person}</td>
  </tr>
  
  <tr>
    <td valign="top" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;">Kohtade arv</td>
    <td class="text11px" style="padding-left:10px;">{VAR:job_nr}</td>
  </tr>

   <tr class="Grey">
    <td class="text11px" valign="top" colspan="2" style="padding-left: 10px; padding-right:10px; padding-top: 10px; padding-bottom:10px;">Kandideerimise tähtaeg: {VAR:deadline}</td>
  </tr>
</table>

<!-- SUB: company_name_sub -->
<tr bgcolor="B1DFF2">
	<td class="text11px" colspan="2" style="padding-bottom:5px; padding-top:5px; padding-left:10px; font-weight: bold;" valing="top">{VAR:company}</td>
</tr>
<!-- END SUB: company_name_sub -->


<!-- SUB: email_sub -->
	<tr>
		<td valign="top" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;">E-mail</td>
		<td class="text11px" style="padding-left:10px;"><a href="mailto:{VAR:email}">{VAR:email}</a></td>
	</tr>
<!-- END SUB: email_sub -->

<!-- SUB: phone_nr_sub -->
	<tr>
		<td valign="top" bgcolor="#EBEBEB" class="text11px" style="padding-left:10px;">Lisainfo telefonil</td>
		<td class="text11px" style="padding-left:10px;">{VAR:phone_nr}</td>
	</tr>
<!-- END SUB: phone_nr_sub -->


<!-- SUB: org_description_sub -->
	<tr>
    	<td colspan="2" class="text11px" style="padding-left: 10px; padding-right:10px; padding-top: 10px; padding-bottom:10px;">{VAR:org_description}</p>
		</td>
	</tr>
<!-- END SUB: org_description_sub -->


<!-- SUB: sectors_list -->
	<ul>
		<li>{VAR:sector}</li>
	</ul>
<!-- END SUB: sectors_list -->

