<!-- SUB: online -->
<img src="{VAR:baseurl}/img/{VAR:online_light}_light.gif" /> {VAR:online_caption}
<!-- END SUB: online -->

<!-- SUB: karma -->
Karma <img src="{VAR:baseurl}/img/smiley_{VAR:karma_smiley}.gif" alt="{VAR:alt_karma}" />
<!-- END SUB: karma -->

<!-- SUB: muuda -->
<table align="right" width="*" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="right" style="color:#FFFFFF; font-family:Verdana, Arial, Helvetica, sans-serif; font-weight:bold;" bgcolor="#FFCC33" nowrap><img src="{VAR:baseurl}/img/prof_change_tab_left.gif" />{VAR:my_profile_switch}<img src="{VAR:baseurl}/img/prof_change_tab_right.gif" /></td>
	</tr>
</table>
<!-- END SUB: muuda -->
<!-- SUB: send_message -->
<a href="{VAR:send_url}"><img src="{VAR:baseurl}/img/icon_saada.gif" alt="Saada sõnum" border="0" /> Saada sõnum</a>
<!-- END SUB: send_message -->

<!-- SUB: header -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr height="20">
		<td>
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td height="20" nowrap>{VAR:online}&nbsp;&nbsp;&nbsp;&nbsp;{VAR:karma}</td>
					<td height="100%" align="right">{VAR:the_thing}</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<!-- END SUB: header -->

<!-- SUB: property_list -->
<table border="0" class="text" align="center" cellpadding="0" cellspacing="0" width="100%">
<!-- SUB: property_item -->
  <tr class="rate_rowbgcolor_{VAR:evenodd}">
    <td class="rate_aw04contentcellleft" width='80' nowrap>
      {VAR:prop_caption}
    </td>
    <td class="rate_aw04contentcellright">
      {VAR:prop_value}
    </td>
  </tr>
<!-- END SUB: property_item -->
</table>
<!-- END SUB: property_list -->

<!-- SUB: img_list -->
<table border="0" class="text" cellpadding="0" cellspacing="0" width="100%">
<!-- SUB: imgs -->
<tr>
	<td>Kokku {VAR:num} pilti:</td>
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

<table border="0" class="text" align="center" cellpadding="0" cellspacing="0">
{VAR:person.firstname}
{VAR:person.lastname}
{VAR:person.gender}
{VAR:person.nickname}
{VAR:person.social_status}
{VAR:profile.user_field1}
{VAR:profile.age}
{VAR:profile.sexual_orientation}
{VAR:profile.height}
{VAR:profile.weight}
{VAR:profile.body_type}
{VAR:profile.hair_color}
{VAR:profile.hair_type}
{VAR:profile.eyes_color}
{VAR:profile.tobacco}
{VAR:profile.alcohol}
{VAR:profile.user_text1}
{VAR:profile.user_text3}
{VAR:profile.user_text2}
{VAR:profile.user_text5}
{VAR:profile.user_field2}
{VAR:profile.user_text4}
{VAR:profile.user_blob1}
{VAR:profile.occupation}
{VAR:profile.user_blob2}
</table>
