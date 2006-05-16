var InsertAWLupCommand=function(){};
InsertAWLupCommand.prototype.Execute=function(){}
InsertAWLupCommand.GetState=function() { return FCK_TRISTATE_OFF; }
InsertAWLupCommand.Execute=function() {
  window.open('/automatweb/orb.aw?class=link_manager&doc='+escape(window.parent.location.href), 
					'InsertAWFupCommand', 'width=500,height=400,scrollbars=no,scrolling=no,location=no,toolbar=no');
}
FCKCommands.RegisterCommand('awlup', InsertAWLupCommand ); 
var oawlupItem = new FCKToolbarButton('awlup', 'Sisesta Link');
oawlupItem.IconPath = '/automatweb/js/fckeditor/editor/plugins/awlup/image.gif' ;


FCKToolbarItems.RegisterItem( 'awlup', oawlupItem ) ;


