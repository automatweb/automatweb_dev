<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<form method="POST" name="sysconf" action="reforb.{VAR:ext}">
{VAR:placeholder}
<!-- SUB: menu_general -->
<tr>
<td class="fform" valign="top">Menüü, millest sait algab (rootmenu)</td>
<td class="fform">
<select size=10 name="rootmenu">
{VAR:rootmenu}
</select>
</td>
</tr>
<tr>
<td class="fform" valign="top">Menüü, mida näidatakse esilehel (frontpage)</td>
<td class="fform">
<select size="10" name="frontpage">
{VAR:frontpage}
</select>
</td>
</tr>
<tr>
<td class="fform" valign="top">Aadress, mis pannakse saidi saadetavate kirjade From reale</td>
<td class="fform"><input type="text" name="mailfrom" value="automatweb@struktuur.ee" size="40"></td>
</tr>
<tr>
<td class="fform" valign="top">Sitemapi menüü</td>
<td class="fform">
<select name="sitemap" size="10">
{VAR:sitemap}
</select>
</td>
</tr>
<tr>
<td class="fform">Kontrollida objektide ACLi</td>
<td class="fform"><input type="checkbox" name="check_acl" value="1" checked></td>
</tr>
<tr>
<td class="fform">Kontrollida programmide ACLi:</td>
<td class="fform"><input type="checkbox" name="check_prog_acl" value="1" checked></td>
</tr>
<!-- END SUB: menu_general -->

<!-- SUB: menu_menus -->
<tr>
<td class="fform" colspan="2"><b>Defineeritud menüüd<b>
</tr>
<!-- SUB: line -->
<tr>
<td class="fform" valign="top">{VAR:name}</td>
<td class="fform">
<select name="pick[{VAR:name}]" size="10">
{VAR:pickmenu}
</select>
</td>
</tr>
<!-- END SUB: line -->

<!-- END SUB: menu_menus -->

<!-- SUB: menu_templates -->

<!-- END SUB: menu_templates -->

<!-- SUB: menu_devel -->
<tr>
<td class="fform" valign="top">Bugtracki developerite grupp:</td>
<td class="fform">
<select name="bugtrack_developergid" size="10">
{VAR:groups}
</select>
</td>
</tr>
<tr>
<td class="fform">Bugtracki foorumi ID:</td>
<td class="fform">
<input type="text" name="bugtrack_forum">
</td>
</tr>
<tr>
<td class="fform">Debug info kuvamine:</td>
<td class="fform">
<input type="checkbox" name="show_debug" value="1">
</td>
</tr>
<!-- END SUB: menu_devel -->
<tr>
{VAR:reforb}
<td class="fform" align="center" colspan="2">
<input type="submit" value="Salvesta">
</td>
</tr>
</form>
</table>
