<FORM METHOD=POST ACTION="reforb.aw">
<table border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td bgcolor="#CCCCCC">
<TABLE border=0>
<!-- SUB: LINEG -->
<TR>
	<TD class="fcaption">Vali joongraafiku "<b>{VAR:name}</b>" andmed:
</TR>
<TR>
	<TD class="fcaption"><SELECT NAME="datasrc">
			<OPTION VALUE=userdata>Sisestan ise andmed</OPTION>
			<OPTION VALUE=stats_rows>Koodamise stats: Ridade j�rgi</OPTION>
			<OPTION VALUE=stats_bytes>Koodamise stats: Baitide J�rgi</OPTION>
			<OPTION VALUE=stats_words>Koodamise stats: S�nade J�rgi</OPTION>
		</SELECT>

</TR>
<!-- END SUB: LINEG -->
<!-- SUB: BARG -->
<TR>
	<TD class="fcaption">Vali tulpgraafiku "<b>{VAR:name}</b>" andmed:
</TR>
<TR>
	<TD class="fcaption"><SELECT NAME="datasrc">
			<OPTION VALUE=userdata>Sisestan ise andmed</OPTION>
			<OPTION VALUE=stats_all>Koodamise stats: K�ik koos</OPTION>
		</SELECT>
</TR>
<!-- END SUB: BARG -->
<TR>
	<TD class="fcaption">Y teljele <INPUT TYPE="text" NAME="ycount" VALUE="3" SIZE="2"> andmed.
</TR>
<TR>
	<TD class="fcaption">M�rkus: Kasutatakse ainult sisestavate andmete puhul.
</TR>
	
</TABLE>
<input type="submit" name="Submit" value="Edasi">
</TABLE>
{VAR:reforb}
</FORM>
