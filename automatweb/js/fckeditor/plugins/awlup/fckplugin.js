var InsertAWLupCommand=function(){};
InsertAWLupCommand.Name = "LinkUpload";
InsertAWLupCommand.prototype.Execute=function(){}
InsertAWLupCommand.GetState=function() { return FCK_TRISTATE_OFF; }
InsertAWLupCommand.Execute=function() {
  window.open('/automatweb/orb.aw?class=link_manager&doc='+escape(window.parent.location.href), 
					'InsertAWFupCommand', 'width=800,height=600,scrollbars=no,scrolling=no,location=no,toolbar=no');
}
FCKCommands.RegisterCommand('awlup', InsertAWLupCommand ); 
var oawlupItem = new FCKToolbarButton('awlup', FCKLang.AWLinkUpload);
oawlupItem.IconPath = FCKPlugins.Items['awlup'].Path + 'image.gif' ;


FCKToolbarItems.RegisterItem( 'awlup', oawlupItem ) ;


var InsertAWLupCommand=function(){};
InsertAWLupCommand.Name = "LinkChange";
InsertAWLupCommand.prototype.Execute=function(){}
InsertAWLupCommand.GetState=function() { return FCK_TRISTATE_OFF; }
InsertAWLupCommand.Execute=function()
{
	window.open('/automatweb/orb.aw?class=link_manager&doc='+escape(window.parent.location.href)+'&no_popup=1&link_url='+escape(FCK.Selection.MoveToAncestorNode( 'A' ).href), 
				'InsertAWFupCommand', 'width=800,height=500,scrollbars=no,scrolling=no,location=no,toolbar=no');
}
FCKCommands.RegisterCommand('Link', InsertAWLupCommand ); 
