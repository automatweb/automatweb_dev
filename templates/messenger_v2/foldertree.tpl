<script type="text/javascript">
function new_folder()
{
	parent = document.forms['changeform'].elements['currentfolder'].value;
	url = '{VAR:new_folder_url}&parentfolder=' + parent;
	alert(parent);
	//document.getElementById('msgrcont').src = url;
}

function rename_folder()
{
	id = document.forms['changeform'].elements['currentfolder'].value;
	document.getElementById('msgrcont').src = '{VAR:edit_folder_url}&folder=' + id;


}
</script>
