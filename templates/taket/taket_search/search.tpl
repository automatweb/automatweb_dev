<br>
<script>
	function postSearch(start, order, direction){
		document.hiddenSearchForm.start.value=start;
		document.hiddenSearchForm.orderBy.value=order;
		document.hiddenSearchForm.direction.value=direction;
		document.hiddenSearchForm.submit();
	}
	
	function fillSearchForm(){
		//hiddenSearchForm
		document.hiddenSearchForm.tootekood.value=document.getElementById('newtootekood').value;
		document.hiddenSearchForm.otsitunnus.value=document.getElementById('newotsitunnus').value;
		document.hiddenSearchForm.kogus.value=document.getElementById('newkogus').value;
		document.hiddenSearchForm.asendustooted.value=document.getElementById('newasendustooted').value;
		document.hiddenSearchForm.laos.value=document.getElementById('newlaos').value;
		document.hiddenSearchForm.orderBy.value=document.getElementById('neworderBy').value;
		document.hiddenSearchForm.direction.value=document.getElementById('direction').value;
		
		//search form
		document.searchForm.tootekood.value=document.getElementById('newtootekood').value;
		document.searchForm.otsitunnus.value=document.getElementById('newotsitunnus').value;
		document.searchForm.kogus.value=document.getElementById('newkogus').value;
		document.searchForm.asendustooted.checked=(document.getElementById('newasendustooted').value==1)?true:false;
		document.searchForm.laos.checked=(document.getElementById('newlaos').value==1)?true:false;
		document.searchForm.orderBy.value=document.getElementById('neworderBy').value;;
		document.searchForm.direction.value=document.getElementById('direction').value;
	}
</script>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td bgcolor="#B9BED2"><table width="100%"  border="0" cellspacing="1" cellpadding="0">
                  <tr>
                    <td height="70" valign="middle" bgcolor="#FFFFFF"><table border=0 cellspacing=0 cellpadding=2 width=100% align=center>
                      <tr>
                          <td height=30 class="listItem" colspan="2">Otsisin toodet &quot;<b>{VAR:otsisin}</b>&quot;, leidsin {VAR:results} vastet.</td>
                          <td colspan="8" align="right" class="listItem" >Hinnad sisaldavad käibemaksu! <a href="javascript:void(0)" onClick="window.open('155','','directory=0,height=340,width=350,resizable=1, statusbar=0, hotkeys=0,menubar=0,scrollbars=0,status=0,toolbar=0')"><img src="img/qmark.gif" width="20" height="20" hspace="10" border="0" align="absmiddle"></a></td>
                      </tr>
                      <tr>
                        <td width=50 height=30 class="{VAR:cssstaatus}"><a href="javascript: postSearch({VAR:prev},'staatus','{VAR:direction}')">Staatus</a></td>
                        <td width=180 height=30 class="{VAR:csstootekood}"><a href="javascript: postSearch({VAR:prev},'tootekood','{VAR:direction}')">Tootekood</a></td>
                        <td width=170 class="{VAR:cssnimetus}"><a href="javascript: postSearch({VAR:prev},'nimetus','{VAR:direction}')">Nimetus</a></td>
                        <td width=180 class="{VAR:cssotsitunnus}"><a href="javascript: postSearch({VAR:prev},'otsitunnus','{VAR:direction}')">Otsitunnus</a></td>
                        <td width=30 class="{VAR:csshind}"><a href="javascript: postSearch({VAR:prev},'hind','{VAR:direction}')">Jae-hind</a></td>
                        <td width=20 class="{VAR:cssallahindlus}"><a href="javascript: postSearch({VAR:prev},'allahindlus','{VAR:direction}')">Allah %</a></td>
                        <td width=30 class="{VAR:csslopphind}"><a href="javascript: postSearch({VAR:prev},'lopphind','{VAR:direction}')">Lõpp-hind</a></td>
                        <td width=30 class="listTitle"><a href="javascript:void(0)">Kogus</a></td>
                        <td width=20 class="{VAR:csslaos}"><a href="javascript: postSearch({VAR:prev},'laos','{VAR:direction}')">Laos</a></td>
                        <td class="listTitle"><a href="javascript:void(0)">Telli</a></td>
                      </tr>
							 
							 <!-- SUB: asendustoodeblock -->
                        <td class="{VAR:staatuscss}"><span title='{VAR:peatoode}'>{VAR:replacement}</span>&nbsp;</td>
							 <!-- END SUB: asendustoodeblocblockk -->
							 <!-- SUB: mainproduct -->
                        <td class="{VAR:staatuscss}"><span>{VAR:replacement}</span>&nbsp;</td>
							 <!-- END SUB: mainproduct -->

							 <!-- SUB: product -->
                      <tr onmouseover="setPointer(this, '#EEEFF4')" onmouseout="setPointer(this, '#FFFFFF')">
							 	{VAR:esimeneVeerg}
                        <td class="listItemSec" >{VAR:product_code}&nbsp;</td>
                        <td class="listItemSec" >{VAR:product_name}&nbsp;</td>
                        <td class="listItemSec" >{VAR:search_code}&nbsp;</td>
                        <td class="listItemSec" >{VAR:price}&nbsp;</td>
                        <td class="listItemSec" >{VAR:discount}&nbsp;</td>
                        <td class="listItemSec" >{VAR:finalPrice}&nbsp;</td>
                        <td class="listItemSec" >{VAR:quantity}&nbsp;</td>
                        <td align=center class="listItemSec" >{VAR:inStock2}</td>
								{VAR:karuParsed}
                      </tr>
							 <!-- END SUB: product -->

							 {VAR:productParsed}
									<!-- SUB: karu -->
                        <td align="center" class="listItemSec" style="cursor:hand" onClick='document.location.href="index.aw?class=taket_ebasket&action=add_item&product_code={VAR:product_code2}&quantity={VAR:quantity}"'>
									<img src="img/karu.gif" width="13" height="10" border="0"></td>
									<!-- END SUB: karu -->
									<!-- SUB: karupole -->
                        <td align="center" class="listItemSec">
									<img src="img/karu_pole.gif" width="13" height="10" border="0"></td>
									<!-- END SUB: karupole -->
                      <tr>
							 	<td height=30 class="listItem" colspan="10" align='middle'>
									<input type='hidden' id='newasendustooted' value='{VAR:asendustooted}'>
									<input type='hidden' id='newotsitunnus' value='{VAR:otsitunnus}'>
									<input type='hidden' id='newkogus' value='{VAR:kogus}'>
									<input type='hidden' id='newlaos' value='{VAR:laos}'>
									<input type='hidden' id='newtootekood' value='{VAR:tootekood}'>
									<input type='hidden' id='neworderBy' value='{VAR:orderBy}'>
									<input type='hidden' id='newdirection' value='{VAR:direction}'>
									<script>
										fillSearchForm();
									</script>
								<!-- SUB: numbersPart -->
									<a href='javascript: postSearch({VAR:prev},"{VAR:orderBy}","{VAR:direction}")'>&laquo;</a>
									{VAR:pageNumbersParsed}
									<a href='javascript:postSearch({VAR:next},"{VAR:orderBy}","{VAR:direction}");'>&raquo;</a>
								<!-- END SUB: numbersPart -->
									<!-- SUB: pageNumbers -->
										<a href='javascript:postSearch({VAR:start},"{VAR:orderBy}","{VAR:direction}");'>{VAR:pageNumber}</a>
									<!-- END SUB: pageNumbers -->
								</td>
                      </tr>
                    </table>					
						</td>
                  </tr>
              </table>
				  </td>
              <td width="2" valign="top" bgcolor="#B9BED2"><img src="img/one_w.gif" width="2" height="2"></td>
            </tr>
            <tr>
              <td align="left" bgcolor="#B9BED2"><img src="img/one_w.gif" width="2" height="2"></td>
              <td bgcolor="#B9BED2"><img src="img/one.gif" width="2" height="2"></td>
            </tr>
          </table>
          <p>&nbsp;</p>
