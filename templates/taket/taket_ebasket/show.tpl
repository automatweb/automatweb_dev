<br>
<script>
	function fillKorv()
	{
		document.getElementById('seesperenimi').value=document.transa.eesperenimi.value;
		document.getElementById('skontakttelefon').value=document.transa.kontakttelefon.value;
		document.getElementById('stransport').value=document.transa.transport.value;
	}

	function checkIfValid(){
		document.getElementById("transport_name").value = document.transa.transport.options[document.transa.transport.options.selectedIndex].text;

		if(document.getElementById('canWeProceed').value==1)
			return true;
		else{
			alert('Mõnda ostukorvis olevat toodet ei ole laos piisavalt.');
			return false;
		}
	}
</script>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td bgcolor="#B9BED2"><table width="100%"  border="0" cellspacing="1" cellpadding="0">
                  <tr>
                    <td height="70" valign="middle" bgcolor="#FFFFFF"><table border=0 cellspacing=0 cellpadding=2 width=100% align=center>
                      <form method=POST action='index.aw' onSubmit="fillKorv();">
                        <tr>
                          <td colspan="7"><table width="100%" height="30"  border="0" cellpadding="0" cellspacing="0">
                              <tr>
                                <td class="lrgTitle">Ostukorv </td>
                                <td align="right">
										  	<span class="listItem"><a href="javascript:void()" onClick="window.open('156','','directory=0,height=290,width=350,resizable=1, statusbar=0, hotkeys=0,menubar=0,scrollbars=0,status=0,toolbar=0')"><img src="img/qmark.gif" width="20" height="20" hspace="10" border="0"></a></span>
											<input type='hidden' name='seesperenimi' id='seesperenimi'>
											<input type='hidden' name='skontakttelefon' id='skontakttelefon'>
											<input type='hidden' name='stransport' id='stransport'>
											</td>
                              </tr>
                            </table></td>
                          </tr>
<tr>
	<td width="19%" class="{VAR:product_codecss}"><a href="?class=taket_ebasket&action=show&sort=product_code&dir={VAR:product_codedir}">Tootekood</a></td>
	<td width="18%" class="{VAR:product_namecss}"><a href="?class=taket_ebasket&action=show&sort=product_name&dir={VAR:product_namedir}">Nimetus</a></td>
	<td width="10%" class="{VAR:pricecss}"><a href="?class=taket_ebasket&action=show&sort=price&dir={VAR:pricedir}">Jaehind</a></td>
	<td width="10%" class="{VAR:discountcss}"><a href="?class=taket_ebasket&action=show&sort=discount&dir={VAR:discountdir}">Allah %</a></td>
	<td width="5%" class="{VAR:finalpricecss}"><a href="?class=taket_ebasket&action=show&sort=finalprice&dir={VAR:finalpricedir}">Lõpphind</a></td>
	<td width="10%" class="{VAR:quantitycss}"><a href="?class=taket_ebasket&action=show&sort=quantity&dir={VAR:quantitydir}">Vali kogus</a></td>
	<td width="10%" class="listTitle"><a href="#">Laos</a></td>
</tr>								
{VAR:toodeParsed}
<!-- SUB: toode -->
                        <tr onmouseover="setPointer(this, '#EEEFF4')" onmouseout="setPointer(this, '#FFFFFF')">
                          <td class="listItem" >{VAR:product_code}</td>
                          <td class="listItemSec" >{VAR:product_name}</td>
                          <td class="listItemSec" >{VAR:price}</td>
                          <td class="listItemSec" >{VAR:discount}</td>
                          <td class="listItemSec" >{VAR:finalprice}</td>
                          <td class="listItemSec" >
                          	<input id="koguseId{VAR:i}" name='quantity[]' type="text" class="formBox" size="2" value='{VAR:quantity}'>
									<a href="javascript:void()" onClick='addOne(document.getElementById("koguseId{VAR:i}"))'><img src="img/sym_inc.gif" width="7" height="7" border="0"></a>
									<a href="javascript:void()" onClick='subtractOne(document.getElementById("koguseId{VAR:i}"))'><img src="img/sym_deg.gif" width="7" height="7" border="0"></a>
                            <input type='hidden' name='productId[]' value='{VAR:product_code}'>
                          </td>
                          <td class="orange">{VAR:inStock}&nbsp;</td>
                        </tr>
<!-- END SUB: toode -->                        
                        <tr bgcolor="#B9BED2">
                          <td colspan="7"><table width="100%"  border="0" cellspacing="1" cellpadding="0">
                            <tr bgcolor="#FFFFFF">
                              <td width="70%" align="right" class="listItem">Summa k&auml;ibemaksuta: </td>
                              <td align="right" class="listItem">{VAR:priceWithoutTax}&nbsp;</td>
                            </tr>
                            <tr bgcolor="#FFFFFF">
                              <td align="right" class="listItem">K&auml;ibemaks:</td>
                              <td align="right" class="listItem">{VAR:tax}&nbsp;</td>
                            </tr>
                            <tr bgcolor="#FFFFFF">
                              <td align="right" class="listItem">Hind kokku: </td>
                              <td align="right" class="listItem"><b>{VAR:priceGrandTotal}&nbsp;</b>
<input type='hidden' value='{VAR:tmpFlag}' id='canWeProceed'>
										</td>
                            </tr>
                          </table>                            </td>
                          </tr>
                        <tr>
                          <td colspan="7">&nbsp;</td>
                        </tr>
                        <tr align="right">
                          <td colspan="7">
								  	<input type='hidden' name='sort' value='{VAR:sort}'>
									<input type='hidden' name='dir' value='{VAR:dir}'> 
								  	<input name="Submit" type="submit" class="formButton" value="Salvesta korv ja kontrolli saadavust">
								  </td>
                        </tr>
                        {VAR:reforb}
								</form>
{VAR:vormistaParsed}
<!-- SUB: vormista -->
                        <tr>
                          <td colspan="7">&nbsp;</td>
                        </tr>
{VAR:inputErrParsed}
<!-- SUB: inputErr -->                       
                        <tr>
                          <td colspan="7"><b>Kõik väljad peavad olema täidetud!</b></td>
                        </tr>
<!-- END SUB: inputErr -->
                        <form method=POST action='index.aw' name='transa' onSubmit='return checkIfValid()'>
                        <tr>
                          <td width="25%" class="listItem">Ees- ja perekonnanimi:</td>
                          <td class="listItem"><span class="formText">
                            <input class=formBox size=25 name='eesperenimi' value='{VAR:eesperenimi}'>
                          </span></td>
                          </tr>
                        <tr>
                          <td class="listItem">Kontakttelefon:</td>
                          <td class="listItem"><span class="formText">
                            <input class=formBox size=25 name='kontakttelefon' value='{VAR:kontakttelefon}'>
                          </span></td>
                          </tr>
                        <tr>
                          <td class="listItem">Transpordieelistus:</td>
                          <td class="listItem">
								  <input type='hidden' name='transport_name' id='transport_name'>
                          	<select name="transport" class="formBox" id='transport'>
										{VAR:transportParsed}
<!-- SUB: transport -->
                            <option value="{VAR:transport_id}" {VAR:tselected}>{VAR:transport_name}</option>
<!-- END SUB: transport -->
                          </select></td>
                          </tr>
                        <tr>
                          <td class="listItem">&nbsp;</td>
                          <td class="listItem">&nbsp;</td>
                          </tr>
                        <tr>
                          <td class="listItem">&nbsp;</td>
                          <td height="30" valign="top" class="listItem"><input name="Submit3" type="submit" class="formButton" value="Saada tellimus"></td>
                        </tr>
                        {VAR:reforb2}
                        </form>
<!-- END SUB: vormista -->
                    </table></td>
                  </tr>
              </table></td>
              <td width="2" valign="top" bgcolor="#B9BED2"><img src="img/one_w.gif" width="2" height="2"></td>
            </tr>
            <tr>
              <td align="left" bgcolor="#B9BED2"><img src="img/one_w.gif" width="2" height="2"></td>
              <td bgcolor="#B9BED2"><img src="img/one.gif" width="2" height="2"></td>
            </tr>
          </table>
