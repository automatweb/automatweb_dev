<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://earth.google.com/kml/2.2">
<Document>
	<name>{VAR:filename}</name>
<!-- SUB: styles -->
	<Style id="{VAR:id}">
		<IconStyle>
			<color>{VAR:icon_transp}{VAR:icon_color}</color>
			<scale>{VAR:icon_size}</scale>
			<Icon>
				<href>{VAR:icon_url}</href>
			</Icon>
			<hotSpot x="20" y="2" xunits="pixels" yunits="pixels"/>
		</IconStyle>
		<LabelStyle>
			<color>{VAR:label_transp}{VAR:label_color}</color>
			<scale>{VAR:label_size}</scale>
		</LabelStyle>
	</Style>
<!-- END SUB: styles -->
<!-- SUB: placemarks -->
	<Placemark>
		<name>{VAR:name}</name>
		<description>
		<![CDATA[
<p>Aadress: {VAR:address}</p>
<table width="650" border="0" cellpadding="5" cellspacing="0">
  <tr>
    <td align="center"><img src="http://klient.struktuur.ee/ttu/kaart/flash/Pildid/{VAR:userta2}_1.JPG" width="200" height="150" border="1" bordercolor="#0099CC" /></td>
    <td align="center"><img src="http://klient.struktuur.ee/ttu/kaart/flash/Pildid/{VAR:userta2}_2.JPG" width="200" height="150" border="1" bordercolor="#0099CC" /></td>
    <td align="center"><img src="http://klient.struktuur.ee/ttu/kaart/flash/Pildid/{VAR:userta2}_3.JPG" width="200" height="150" border="1" bordercolor="#0099CC" /></td>
  </tr>
</table>
<table width="650" border="1" style="border-collapse:collapse" bordercolor="#0099CC" cellspacing="0" cellpadding="5">
  <tr>
    <td>Hoone kood: </td>
    <td>{VAR:userta2}</td>
  </tr>
  <tr>
    <td width="55%">Hoone maht (m3) </td>
    <td width="45%">{VAR:desc1}</td>
  </tr>
  <tr>
    <td>Suletud netopind (m2)</td>
    <td>{VAR:desc2}</td>
  </tr>
  <tr>
    <td>&Otilde;pilaste arv: </td>
    <td>{VAR:userta3}</td>
  </tr>
  <tr>
    <td>M&auml;rkused:</td>
    <td>{VAR:userta4}</td>
  </tr>
  <tr>
    <td>Hoone soojuse eritarbimine  kWh/(m2 a), 2003</td>
    <td>{VAR:usertf1}</td>
  </tr>
  <tr>
    <td>Hoone soojuse eritarbimine  kWh/(m2 a), 2004</td>
    <td>{VAR:usertf2}</td>
  </tr>
  <tr>
    <td>Hoone soojuse eritarbimine  kWh/(m2 a), 2005</td>
    <td>{VAR:usertf3}</td>
  </tr>
  <tr>
    <td>Hoone soojuse eritarbimine  kWh/(m2 a), 2006</td>
    <td>{VAR:usertf4}</td>
  </tr>
  <tr>
    <td>Hoone elektri eritarbimine kWh/(m2 a ) 2003</td>
    <td>{VAR:usertf5}</td>
  </tr>
  <tr>
    <td>Hoone elektri eritarbimine kWh/(m2 a ) 2004</td>
    <td>{VAR:usertf6}</td>
  </tr>
  <tr>
    <td>Hoone elektri eritarbimine kWh/(m2 a ) 2005</td>
    <td>{VAR:usertf7}</td>
  </tr>
  <tr>
    <td>Hoone elektri eritarbimine kWh/(m2 a ) 2006</td>
    <td>{VAR:usertf8}</td>
  </tr>
  <tr>
    <td>Hoone energia  eritarbimine kWh/(m2 a ) 2003</td>
    <td>{VAR:usertf9}</td>
  </tr>
  <tr>
    <td>Hoone energia  eritarbimine kWh/(m2 a ) 2004</td>
    <td>{VAR:usertf10}</td>
  </tr>
  <tr>
    <td>Hoone energia  eritarbimine kWh/(m2 a ) 2005</td>
    <td>{VAR:userta5}</td>
  </tr>
  <tr>
    <td>Hoone energia  eritarbimine kWh/(m2 a ) 2006</td>
    <td>{VAR:userta1}</td>
  </tr>
</table>
		{VAR:address} {VAR:desc1} {VAR:desc2} {VAR:usertf1} {VAR:userta1} {VAR:usertf2} {VAR:userta2} {VAR:usertf3} {VAR:userta3} {VAR:usertf4} {VAR:userta4} {VAR:usertf5} {VAR:userta5} {VAR:usertf6} {VAR:userta6} {VAR:usertf7} {VAR:userta7} {VAR:usertf8} {VAR:userta8} {VAR:usertf9} {VAR:userta9} {VAR:usertf10} {VAR:userta10}
		]]>
		</description>
		<LookAt>
			<longitude>{VAR:coord_y}</longitude>
			<latitude>{VAR:coord_x}</latitude>
			<altitude>0</altitude>
			<range>{VAR:view_range}</range>
			<tilt>{VAR:view_tilt}</tilt>
			<heading>{VAR:view_heading}</heading>
			<altitudeMode>relativeToGround</altitudeMode>
		</LookAt>
		<styleUrl>{VAR:style}</styleUrl>
		<Point>
			<extrude>1</extrude>
			<altitudeMode>relativeToGround</altitudeMode>
			<coordinates>{VAR:coord_y},{VAR:coord_x},{VAR:icon_height}</coordinates>
		</Point>
	</Placemark>
<!-- END SUB: placemarks -->
</Document>
</kml>
