<!-- SUB: online -->
<img src="{VAR:baseurl}/img/{VAR:online_light}_light.gif" /> {VAR:online_caption}
<!-- END SUB: online -->

<!-- SUB: karma -->
Karma <img src="{VAR:baseurl}/img/smiley_{VAR:karma_smiley}.gif" alt="{VAR:alt_karma}" />
<!-- END SUB: karma -->

<!-- SUB: muuda -->
<table align="right" width="*" border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td align="right" style="color:#FFFFFF; font-family:Verdana, Arial, Helvetica, sans-serif; font-weight:bold;" bgcolor="#FFCC33" nowrap><img src="{VAR:baseurl}/img/prof_change_tab_left.gif" /><a href="{VAR:my_profile_switch}">{VAR:LC_RATE_CHANGE}</a><img src="{VAR:baseurl}/img/prof_change_tab_right.gif" /></td>
	</tr>
</table>
<!-- END SUB: muuda -->

<!-- SUB: send_message -->
<a href="{VAR:blog_url}"><img src="{VAR:baseurl}/img/icon_print.gif" alt="Loe blogi" border="0" /> {VAR:LC_RATE_READ_BLOG}</a> 
<a href="{VAR:send_url}"><img src="{VAR:baseurl}/img/icon_saada.gif" alt="Saada snum" border="0" /> {VAR:LC_RATE_SEND_MAIL}</a>
<!-- END SUB: send_message -->

<!-- SUB: blog -->

<!-- END SUB: blog -->

<!-- SUB: header -->
<table width="100%" border="0" cellspacing="0" cellpadding="2">
	<tr height="20">
		<td>
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td height="20" nowrap>{VAR:online}&nbsp;&nbsp;&nbsp;&nbsp;{VAR:karma}</td>
					<td height="100%" align="right">{VAR:the_thing}</td>
				</tr>
				<tr>
					<td colspan="2">
					<table class="text" border="0" cellpaddding="2" cellspacing="2" width="100%">
					<tr>
						<td class="rate_rowbgcolor_odd" align="center">{VAR:LC_RATE_LAST_VISIT}:<br /> {VAR:last_visit_time}</td>
						<td class="rate_rowbgcolor_even" align="center">{VAR:LC_RATE_LAST_MOD}:<br /> {VAR:last_mod_time}</td>
						<td class="rate_rowbgcolor_odd" align="center">{VAR:LC_RATE_REGISTERED}:<br /> {VAR:registered_time}</td>
						<td class="rate_rowbgcolor_even" align="center">{VAR:LC_RATE_VISITS}:<br /> {VAR:visits} {VAR:LC_RATE_VISITS_TIMES}</td>
					</tr>
					</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<!-- END SUB: header -->

<!-- SUB: property_list -->
<table border="0" align="center" cellpadding="2" cellspacing="2" width="100%">
{VAR:property_item}
<!-- SUB: property_item_even -->
  <tr class="text" height="14">
    <td class="rate_rowbgcolor_even" width="100" height="16" padding="3" nowrap>
      {VAR:prop_caption}
    </td>
    <td class="rate_rowbgcolor_odd">
      {VAR:prop_value}
    </td>
  </tr>
<!-- END SUB: property_item_even -->
<!-- SUB: property_item_odd -->
  <tr class="text"  height="14">
    <td class="rate_rowbgcolor_odd" width="100"  height="16" nowrap>
      {VAR:prop_caption}
    </td>
    <td class="rate_rowbgcolor_even">
      {VAR:prop_value}
    </td>
  </tr>
<!-- END SUB: property_item_odd -->
</table>
<!-- END SUB: property_list -->

<!-- SUB: img_list -->
<table border="0" class="text" cellpadding="0" cellspacing="0" width="100%">
<!-- SUB: imgs -->
<tr>
	<td>{VAR:LC_RATE_HOWMANY} {VAR:num} {VAR:LC_RATE_PICTURE}</td>
</tr>
<!-- END SUB: imgs -->
	<tr>
		<td>
<!-- SUB: img_item -->
<a href="{VAR:url}"><img src="{VAR:img}" border="0" /></a> 
<!-- END SUB: img_item -->
		</td>
	</tr>
</table>
<!-- END SUB: img_list -->
