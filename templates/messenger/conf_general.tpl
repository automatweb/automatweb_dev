<div class="text">
<b>Üldine</b>
</div>
<HR size="1" width="100%" color="#C8C8C8">
<span class="text">
<ol>
<li>Messenger avatakse eraldi aknas
<input type="checkbox" name="msg_window" {VAR:msg_window} value="1">
<li>Vaikimisi avatav leht
<select name="msg_default_page">
	{VAR:msg_default_page}
</select>
<li>Peita menüüriba?
<input type="checkbox" name="msg_hide_menubar" {VAR:msg_hide_menubar} value="1">
<li>Uue kirja formaat kirjutamisel on:
<select name="msg_default_format">
{VAR:msg_default_format}
</select><br>
<li>Mitu kirja lehel?
<select name="msg_on_page">
{VAR:msg_on_page}
</select>
<li>Filtreerida meiliaadress "Kellelt" valjal? <input type="checkbox" name="msg_filter_address" {VAR:msg_filter_address} value="1">
<li>Font kirja vaatamisel <select name="msg_font">
{VAR:msg_font}
</select>
<select name="msg_font_size">
{VAR:msg_font_size}
</select>
<li>Tekstivaljade laius: <input type="text" name="msg_field_width" size="2" maxlength="2" value="{VAR:msg_field_width}"><br>
<li>Tekstikast mootmed: <input type="text" name="msg_box_width" size="2" maxlength="2" value="{VAR:msg_box_width}"> x <input type="text" name="msg_box_height" size="3" maxlength="3" value="{VAR:msg_box_height}">
<li>Loetud kirjad liigutada <input type="checkbox" name="msg_move_read" {VAR:msg_move_read} value="1">
<select name="msg_move_read_folder">
{VAR:msg_move_read_folder}
</select> 
<li>Salvestada saadetud kirjad Outboxi? <input type="checkbox" name="msg_store_sent" {VAR:msg_store_sent} value="1">
<li>Kustutatud kirjad:
<select name="msg_ondelete">
{VAR:msg_ondelete}
</select>
<li>Küsida kinnitust kirja saatmisel? <input type="checkbox" name="msg_confirm_send" {VAR:msg_confirm_send} value="1">
<li>Vaikimisi prioriteet uue kirja kirjutamisel <select name="msg_default_pri">
{VAR:msg_default_pri}
</select>
<li>Kvootimismärk <select name="msg_quotechar">
{VAR:msg_quote_list}
</select>
<li>Kirjale lisatavate attachide arv <select name="msg_cnt_att">
{VAR:msg_cnt_att}
</select>
<input type="hidden" name="checkbool" value="1">
</ol>
</span>
