var InsertAWFupCommand=function(){};
InsertAWFupCommand.prototype.Execute=function(){}
InsertAWFupCommand.GetState=function() { return FCK_TRISTATE_OFF; }
InsertAWFupCommand.Execute=function() {
  window.open('/automatweb/orb.aw?class=file_manager&doc='+escape(window.parent.location.href), 
					'InsertAWFupCommand', 'width=500,height=400,scrollbars=no,scrolling=no,location=no,toolbar=no');
}
FCKCommands.RegisterCommand('awfup', InsertAWFupCommand ); 
var oawfupItem = new FCKToolbarButton('awfup', 'Faili &uuml;leslaadimine');
oawfupItem.IconPath = '/automatweb/js/fckeditor/editor/plugins/awfup/image.gif' ;


FCKToolbarItems.RegisterItem( 'awfup', oawfupItem ) ;


