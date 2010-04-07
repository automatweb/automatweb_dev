<form action="{VAR:submit_url}" name="cataloglist">
<input type="hidden" name="class" value="shop_order_cart" />
<input type="hidden" name="action" value="submit_add_cart" />
<input type="hidden" name="oc" value="{VAR:oc}" />

<div id="cataloglist">
	<div class="separator clear"><!-- --></div>
<?php
$cataloglist_i = 0;

<!-- SUB: PRODUCT -->
$out = <<<EOF
	<div class="catalog">
		<table>
			<tr>
				<td class="image">
					{VAR:image}
				</td>
				<td class="content">
					<div>
						<p>
							<strong>{VAR:name}</strong><br />
							{VAR:description}
						</p>
						<p class="order">
							{VAR:checkbox} <label for="add_to_cart_{VAR:product_id}_">telli tasuta kataloog</label>
						</p>
					</div>
				</td>
			</tr>
		</table>
	</div><!-- .catalog end -->
EOF;
echo $out;
if ($cataloglist_i==1) 
{
	echo '<div class="separator clear"><!-- --></div>';
	$cataloglist_i=0;
}
$cataloglist_i++;

<!-- END SUB: PRODUCT -->
?>

</div><!-- #cataloglist end -->


<div id="cataloglistSubmit">
	<a href="JavaScript:void(0);" onclick="document.cataloglist.submit();">Telli valitud kataloogid</a>
</div>

</form>