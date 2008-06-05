var InsertAWFupCommand=function(){};
InsertAWFupCommand.prototype.Execute=function(){}
InsertAWFupCommand.GetState=function() { return FCK_TRISTATE_OFF; }
InsertAWFupCommand.Execute=function() {
  window.open('/automatweb/orb.aw?class=file_manager&doc='+escape(window.parent.location.href), 
					'InsertAWFupCommand', 'width=800,height=600,scrollbars=no,scrolling=no,location=no,toolbar=no');
}
FCKCommands.RegisterCommand('awfup', InsertAWFupCommand ); 
var oawfupItem = new FCKToolbarButton('awfup', FCKLang.AWFileUpload);
oawfupItem.IconPath = '/automatweb/js/fckeditor/editor/plugins/awfup/image.gif' ;


FCKToolbarItems.RegisterItem( 'awfup', oawfupItem ) ;


var InsertAWFupCommand=function(){};
InsertAWFupCommand.Name='FileChange';
InsertAWFupCommand.prototype.Execute=function(){}
InsertAWFupCommand.GetState=function() { return FCK_TRISTATE_OFF; }
InsertAWFupCommand.Execute=function() {
	window.open('/automatweb/orb.aw?class=file_manager&doc='+escape(window.parent.location.href)+"&in_popup=1&file_id="+FCK.Selection.GetSelectedElement()._oid, 
		'InsertAWImageCommand', 'width=800,height=500,scrollbars=no,scrolling=no,location=no,toolbar=no');
}
FCKCommands.RegisterCommand('awfilechange', InsertAWFupCommand );

FCK.ContextMenu.RegisterListener( {
	AddItems : function( menu, tag, tagName )
	{
		if ( tag.tagName == 'SPAN' && tag._fckplaceholder )
		{
			menu.AddSeparator();
			menu.AddItem( "awfilechange", "Faili atribuudid", 37 ) ;
		}
	}}
);

