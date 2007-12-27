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
