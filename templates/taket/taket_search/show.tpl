<form name='hiddenSearchForm' action='index.aw' method='POST'>
<input type='hidden' name='otsitunnus'>
<input type='hidden' name='tootekood'>
<input type='hidden' name='laos'>
<input type='hidden' name='kogus'>
<input type='hidden' name='asendustooted'>
<input type='hidden' name='start'>
<input type='hidden' name='orderBy'>
<input type='hidden' name='direction'>
{VAR:reforb}
</form>
<form name='searchForm' action='index.aw' method='POST'>
							<table cellspacing=0 cellpadding=0 border=0>
                        <tr>
                          <td width=20 rowspan=7>&nbsp;</td>
                          <td class=formText nowrap>Toote kood</td>
                          <td class=formText>&nbsp;</td>
                          <td width="65" rowspan="6" align="right" nowrap>
								  <a href='javascript:document.searchForm.submit();'><input type='image' src="img/mago.gif" width="48" height="57" border="0"></a> </td>
                        </tr>
                        <tr>
                          <td nowrap>
								  	<input class=formBox size=25 name=tootekood id='tootekood'>
								</td>
                          <td class=formCheck><input type=checkbox value='1' name="asendustooted" id='asendustooted'>
ära otsi asendustooteid&nbsp;&nbsp;</td>
                          </tr>
                        <tr>
                          <td class=formText nowrap>Otsitunnus / oe-kood</td>
                          <td>&nbsp;</td>
                          </tr>
                        <tr>
                          <td nowrap><input class=formBox size=25 name="otsitunnus" id='otsitunnus'></td>
                          <td nowrap class=formCheck>
								  	<input type='checkbox' name='laos' value='1' id='laos'>
otsi ainult laos olevaid tooteid</td>
                        </tr>
                        <tr>
                          <td class=formText nowrap>Vali kogus</td>
                          <td>&nbsp;</td>
                        </tr>
                        <tr>
                          <td nowrap><input name="kogus" class=formBox id="kogus" size=25>&nbsp;&nbsp;</td>
									<td><input type='reset' class='formButton' value='Puhasta väljad'></td>
                        </tr>
								{VAR:reforb}
								<input type='hidden' id='start' value='0' name='start'>
								<input type='hidden' id='orderBy' value='' name='orderBy'>
								<input type='hidden' id='direction' value='' name='direction'>
								</form>
                      </table>
							 </td>
                    <td width="1" align="right" valign="middle">                      
