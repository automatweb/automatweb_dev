var awEestiEhitusturg = {
	"sendSpam": function(templateId){
		$.ajax({
			url: "?class=eesti_ehitusturg&action=send_spam&id="+$.gup("id")+"&mail_tpl="+templateId,
			success: function(reply){
				alert(reply);
			}
		});
	}
};

$(document).ready(function(){
	$("select[name='valdkond']").change(function(){
		document.location = document.location.href.replace(/&valdkond=[0-9]/g, '')+"&valdkond="+this.value;
	});
	$("input[type='button'][value='   Otsi   ']").click(function(){
		if($("select[name='valdkond2']").val() != ""){
			document.location = document.location.href.replace(/valdkond2=[0-9]*&/g, '').replace(/maakond=[0-9]*&/g, '')+
				"&valdkond2="+$("select[name='valdkond2']").val()+
				"&maakond="+$("select[name='maakond']").val()+
				"&EEaction=searchTegevus";
		}
	});

	// Add our custom drop-down menu
	$("input:button[value='Liitumine ja tasumine']").parent().html($("#awSpecialDiv").html());
});