var InsertAWLupCommand=function(){};
InsertAWLupCommand.prototype.Execute=function(){}
InsertAWLupCommand.GetState=function() { return FCK_TRISTATE_OFF; }
InsertAWLupCommand.Execute=function() {
  window.open('../../../orb.aw?class=link_manager&doc='+escape(window.parent.location.href), 
					'InsertAWFupCommand', 'width=800,height=600,scrollbars=no,scrolling=no,location=no,toolbar=no');
}
FCKCommands.RegisterCommand('awlup', InsertAWLupCommand ); 
var oawlupItem = new FCKToolbarButton('awlup', FCKLang.AWLinkUpload);
oawlupItem.IconPath = '/automatweb/js/fckeditor/editor/plugins/awlup/image.gif' ;


FCKToolbarItems.RegisterItem( 'awlup', oawlupItem ) ;


