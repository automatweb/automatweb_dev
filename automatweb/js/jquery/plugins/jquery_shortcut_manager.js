(function (jQuery){
	this.version = '(0.1)';
	
	this.get_action = function(oid) {
		$.getScript("/automatweb/orb.aw?class=shortcut&action=get_action&id="+oid);
	};
	jQuery.shortcut_manager = this;
	return jQuery;    
})(jQuery);