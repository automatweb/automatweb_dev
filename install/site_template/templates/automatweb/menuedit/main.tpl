<table border="1">
<tr>
	<td colspan="2">Ylemine menyy: &nbsp;&nbsp;&nbsp;
		<!-- SUB: MENU_YLEMINE_L1_ITEM_BEGIN -->
		<a href="{VAR:link}" {VAR:target}>{VAR:text}</a> 
		<!-- END SUB: MENU_YLEMINE_L1_ITEM_BEGIN -->

		<!-- SUB: MENU_YLEMINE_L1_ITEM -->
		| <a href="{VAR:link}" {VAR:target}>{VAR:text}</a> 
		<!-- END SUB: MENU_YLEMINE_L1_ITEM -->
	</td>
</tr>
<tr>
	<td width="20%">
		Vasak menyy: <br><br>

		<!-- SUB: MENU_VASAK_L1_ITEM -->
		<a href="{VAR:link}" {VAR:target}>{VAR:text}</a> <br>
		<!-- END SUB: MENU_VASAK_L1_ITEM -->

		<!-- SUB: login -->
		<form action="reforb.{VAR:ext}" method="POST">
			<table>
				<tr>
					<td>UID:</td>
					<td><input type="text" name="uid"></td>
				</tr>
				<tr>
					<td>Password:</td>
					<td><input type="password" name="password"></td>
				</tr>
				<tr>
					<td colspan="2"><input type="submit" value="Logi sisse"></td>
				</tr>
			</table>
			<input type="hidden" name="class" value="users">
			<input type="hidden" name="action" value="login">
		</form>
		<!-- END SUB: login -->

		<!-- SUB: logged -->
		<br><br>
		{VAR:uid} @ {VAR:date} <br><br>
		<!-- SUB: MENU_LOGIN_L1_ITEM -->		
		<a href="{VAR:link}" {VAR:target}>{VAR:text}</a> <br>
		<!-- END SUB: MENU_LOGIN_L1_ITEM -->		

		<!-- END SUB: logged -->
	</td>
	<td>Sisu <Br>{VAR:doc_content}</td>
</tr>
</table>
