<ul class="steps clear">
	<li class="first"><div><div style="cursor: pointer" onclick="location.href='{VAR:back_url}'">1. {VAR:LC_ALGANDMED}</div></div></li>
	<li><div class="active"><div style="cursor: pointer" onclick="location.href='{VAR:this_url}'">2. {VAR:LC_BRONEERING}</div></div></li>
	<li class="last"><div><div>3. {VAR:LC_KINNITUS}</div></div></li>
</ul>

<br />

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
						<a href="{VAR:back_url}">1. {VAR:LC_ALGANDMED}</a>
					</div>
					<div class="spacer-pa">&nbsp;</div>
					<div class="process-active">
						<a href="{VAR:this_url}">2. {VAR:LC_BRONEERING}</a>

					</div>
					<div class="spacer-ap">&nbsp;</div>
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
</div>
-->

<form action='{VAR:submit}' method='POST' name='changeform'>

	<table class="form">
		<tr><font color="red">{VAR:errors}</font>
			<th>{VAR:LC_BRONEERITUD}:</th>
			<td class="data">{VAR:time_str}</td>
		</tr>
		<tr>
			<th>{VAR:LC_KYLASTUSAEG}:</th>

			<td class="data">{VAR:hours} h</td>
		</tr>
		<tr>
			<th>{VAR:LC_KYLASTAJAID}:</th>
			<td class="data">{VAR:people_value}</td>
		</tr>
		<tr>
			<th>{VAR:LC_SAUNA_MENU}:</th>
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
			<th>{VAR:LC_SAUN}:</th>
			<td class="data">{VAR:sum_wb}</td>
		</tr>
                <tr>
                        <th>{VAR:LC_MENU}:</th>
                        <td class="data">{VAR:menu_sum}</td>
                </tr>
		<tr>
			<th>{VAR:LC_SOODUSTUS}:</th>
			<td class="data">{VAR:bargain}</td>
		</tr>
		<tr>
			<th>{VAR:LC_TASUDA}:</th>
			<td class="data bold red">{VAR:sum_pay}</td>
		</tr>

		<tr class="subheading">
			<th class="sauna" colspan="2">{VAR:LC_ERISOOVID}:</th>
		</tr>
		<tr>
			<th><label for="iField9"></label></th>
			<td>{VAR:comment}</textarea></td>
		</tr>

		<tr class="subheading">
			<th class="sauna" colspan="2">{VAR:LC_TELLIJA_ANDMED}:</th>
		</tr>
		<tr>
			<th><label for="iField1">{VAR:LC_NIMI}:</label></th>
			<td>{VAR:name}</td>
		</tr>
		<tr>
			<th><label for="iField2">{VAR:LC_TELEFON}:</label></th>
			<td>{VAR:phone}</td>
		</tr>
		<tr>
			<th><label for="iField3">{VAR:LC_EMAIL}:</label></th>
			<td>{VAR:email}</td>
		</tr>
		<tr class="subheading">
			<th class="sauna" colspan="2">{VAR:LC_VALIGE_PANK}:</th>

		</tr>
		<tr>
			<th class="inpt">{VAR:bank_hansapank}</th>
			<td>
				<img src="{VAR:baseurl}/automatweb/images/pank/hansapank_pay.gif">
				<select name="bank_country"  id="bank_country">
                                <option>EE-Hansapank</option>
                                <option>LV-Hansabanka</option>
                                <option>LT-Hansabankas</option>
                                </select>
			</td>
		</tr>
		<tr>
			<th class="inpt">{VAR:bank_seb}</th>

			<td>
				<img src="{VAR:baseurl}/img/gfx/pank_yhis.gif">
			</td>
		</tr>
		<tr>
			<th class="inpt">{VAR:bank_sampopank}</th>
			<td>
				<img src="{VAR:baseurl}/img/gfx/pank_sampo.gif">
			</td>
		</tr>
<!--
		<tr>
                        <th class="inpt">{VAR:bank_credit_card}</th>
                        <td>
                                <img src="http://www.estcard.ee/publicweb/graphics/misc/mastercard.gif">
                                <img src="http://www.estcard.ee/publicweb/graphics/misc/visaelectron.gif">
                        </td>

                </tr>-->
			<input type="hidden" name="lang" value="{VAR:LC_BANK_LANGUAGE}"} />
	</table>

			<p class="actions actions2">
				<input type="button" class="btn4" value="{VAR:LC_JATKA}" name="btn1" onclick="changeform.submit();" />
				<input type="button" class="cancel" value="{VAR:LC_TYHISTA}" onclick="location.href='{VAR:revoke_url}'"/>
				<input type="button" class="cancel" value="{VAR:LC_TAGASI}" onclick="location.href='{VAR:back_url}'"/>
			</p>

</form>
