
$(document).ready(function(){
	$("select[name^='rule_question_picker']").change(function(){
		var select = $(this);
		$.getJSON("orb.aw", {
			"class": "k_test",
			"action": "get_option_picker_options",
			"k_test_question": $(this).val()
		}, function(json){
			var list = $("#"+select.attr("id").replace("question", "answer")).get(0);
			aw_clear_list(list);
			for(i in json){
				aw_add_list_el(list, i, json[i]);
			}
		});
	});
});