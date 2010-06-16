<ul class="steps clear">
	<li class="first"><div><div style="cursor: pointer" onclick="location.href='{VAR:path}'">1. {VAR:LC_ALGANDMED}</div></div></li>
	<li><div><div style="cursor: pointer" onclick="location.href='{VAR:back_url}'">2. {VAR:LC_BRONEERING}</div></div></li>
	<li class="last"><div class="active"><div style="cursor: pointer" onclick="location.href='{VAR:this_url}'">3. {VAR:LC_KINNITUS}</div></div></li>
</ul>

<!--
<div class="box6">
	<div class="box6-a1">
		<div class="box6-a2">&nbsp;</div>
		<div class="box6-a3">&nbsp;</div>
	</div>
	<div class="box6-b1">
		<div class="box6-b2">

			<div class="box6-b3">
				<div class="process">
					<div class="process-first">
						<a href="{VAR:path}">1. {VAR:LC_ALGANDMED}</a>
					</div>
					<div class="spacer-pp">&nbsp;</div>
					<div class="process">
						<a href="{VAR:back_url}">2. {VAR:LC_BRONEERING}</a>

					</div>
					<div class="spacer-pa">&nbsp;</div>
					<div class="process-active">
						<a href="{VAR:this_url}">3. {VAR:LC_KINNITUS}</a>
					</div>
					<div class="spacer-a-last">&nbsp;</div>
				</div>
				<div class="clear1">&nbsp;</div>

			</div>						
		</div>
	</div>
	<div class="box6-c1">
		<div class="box6-c2">&nbsp;</div>
		<div class="box6-c3">&nbsp;</div>
	</div>
</div>
-->

<form action="#">

	<table class="form">
		<tr>
			<th>{VAR:LC_BRONEERITUD}:</th>
			<td class="data">{VAR:time_str}</td>
		</tr>
		<tr>
			<th>{VAR:LC_KYLASTAJAID}:</th>
			<td class="data">{VAR:people_value}</td>
		</tr>
		<tr>
			<th>{VAR:LC_MENU}:</th>
			<td class="data">
				<!-- SUB: PROD -->
				{VAR:prod_name} {VAR:prod_amount}X{VAR:prod_value} EEK <br />
				<!-- END SUB: PROD -->
			</td>
		</tr>
		<tr class="subheading">
			<th class="sauna" colspan="2">{VAR:LC_HIND}:</th>
		</tr>

		<tr>
			<th>{VAR:LC_REST_MENU} {VAR:LC_REST_MENU_SUM}:</th>
			<td class="data">{VAR:menu_sum}</td>
		</tr>
		<tr>
			<th>{VAR:LC_REST_TABLE}:</th>
			<td class="data">{VAR:min_sum_left}</td>
		</tr>
		<!--
		<tr>
			<th>{VAR:LC_SOODUSTUS}:</th>
			<td class="data">{VAR:bargain}</td>
		</tr>
		-->
		<tr>
			<th>{VAR:LC_TASUDA}:</th>
			<td class="data bold red">{VAR:sum_pay}</td>
		</tr>
		<tr class="subheading">
			<th class="sauna" colspan="2">{VAR:LC_ERISOOVID}:</th>
		</tr>
		<tr>
			<th></th>
			<td class="data">{VAR:comment_value}</td>
		</tr>
		<tr class="subheading">
			<th class="sauna" colspan="2">{VAR:LC_TELLIJA_ANDMED}:</th>
		</tr>
		<tr>
			<th>{VAR:LC_NIMI}:</th>
			<td class="data">{VAR:name_value}</td>
		</tr>
		<tr>

			<th>{VAR:LC_TELEFON}:</th>
			<td class="data">{VAR:phone_value}</td>
		</tr>
		<tr>
			<th>{VAR:LC_EMAIL}:</th>
			<td class="data">{VAR:email_value}</td>
		</tr>

		<tr>
			<th>{VAR:LC_VALIGE_PANK}:</th>
			<td class="data">{VAR:bank_value}</td>
		</tr>
	</table>
{VAR:LC_MAIL_BOTTOM}
</form>
