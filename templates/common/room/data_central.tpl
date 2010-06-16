
<!--*********-->
<form action='{VAR:submit}' method='POST' name='changeform' id='changeform'><input type="hidden" name="stay" id="stay" value=''>
{VAR:reforb}

<ul class="steps clear">
	<li class="first"><div class="active"><div style="cursor: pointer" onclick="location.href='{VAR:path}'">1. {VAR:LC_ALGANDMED}</div></div></li>
	<li><div><div>2. {VAR:LC_BRONEERING}</div></div></li>
	<li class="last"><div><div>3. {VAR:LC_KINNITUS}</div></div></li>
</ul>

<br />

<!-- <div class="box6">
	<div class="box6-a1">
		<div class="box6-a2">&nbsp; </div>
		<div class="box6-a3">&nbsp;</div>
	</div>
	<div class="box6-b1">
		<div class="box6-b2">
			<div class="box6-b3">
				<div class="process">
					<div class="process-active-first">
						<a href="{VAR:path}">1. {VAR:LC_ALGANDMED}</a>
					</div>
					<div class="spacer-ap">&nbsp;</div>
					<div class="process">
						<span>2. {VAR:LC_BRONEERING}</span>
					</div>
					<div class="spacer-pp">&nbsp;</div>
					<div class="process">
						<span>3. {VAR:LC_KINNITUS}</span>
					</div>
					<div class="spacer-p-last">&nbsp;</div>
				</div>

				<div class="clear1">&nbsp;</div>
			</div>						
		</div>
	</div>
	<div class="box6-c1">
		<div class="box6-c2">&nbsp;</div>
		<div class="box6-c3">&nbsp;</div>
	</div>
</div> -->


<div class="box4-outer">
	<div class="box4-inner">
			<table class="type2 fl">
				<tr>
					<th><label for="date1">{VAR:LC_VALI_SOBIV_AEG}:</label></th>
					<td><a href="#" onclick='{VAR:calendar_link}'><img src="{VAR:baseurl}/img/ico_calendar.gif" alt="" title=""  class="ico" /></a>
					</td>

					<th><label for="s1">{VAR:LC_KYL_ARV}:</label></th>
					<td>
						{VAR:people}
					</td>
					<th><label for="date1">{VAR:LC_VALI_SAUN_MENU}:</label></th>
					<th><a href="#" onclick='{VAR:products_link}'>{VAR:LC_SAUN_MENU}</a></th>
				</tr>
			</table>
	</div>
</div>

<table class="form">
	<tr class="subheading">
		<th class="sauna" colspan="2">{VAR:LC_HIND}</th>
	</tr>
	<tr>
		<th>{VAR:LC_SAUN}:</th>
		<td class="data">{VAR:sum_wb}</td>
	</tr>
	<tr>
		<th>{VAR:LC_SAUNA_MENU}:</th>
		<td class="data">
				<!-- SUB: PROD -->
				{VAR:prod_name} {VAR:prod_amount}X{VAR:prod_value} EEK <br />
				<!-- END SUB: PROD -->
		</td>
        </tr>
	<tr>
		<th>{VAR:LC_SOODUSTUS}:</th>
		<td class="data">{VAR:bargain}</td>
	</tr>
	<tr>
		<th>{VAR:LC_TASUDA}:</th>
		<td class="data bold red">{VAR:sum_pay}</td>
	</tr>

</table>

	<p class="actions actions2">
		<input type="submit" value="{VAR:LC_JATKA}" onclick="{VAR:continue_submit}changeform.submit();" />
	</p>

</form>
