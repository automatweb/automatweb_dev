var InsertAWImageCommand=function(){};
InsertAWImageCommand.Name='ImageUpload';
InsertAWImageCommand.prototype.Execute=function(){}
InsertAWImageCommand.GetState=function() { return FCK_TRISTATE_OFF; }
InsertAWImageCommand.Execute=function() {
  window.open('/automatweb/orb.aw?class=image_manager&doc='+escape(window.parent.location.href), 
					'InsertAWImageCommand', 'width=800,height=600,scrollbars=no,scrolling=no,location=no,toolbar=no');
}
FCKCommands.RegisterCommand('awimageupload', InsertAWImageCommand ); 
var oawimageuploadItem = new FCKToolbarButton('awimageupload', FCKLang.AWUploadImage);
oawimageuploadItem.IconPath = FCKPlugins.Items['awimageupload'].Path + 'image.gif' ;

FCKToolbarItems.RegisterItem( 'awimageupload', oawimageuploadItem ) ;



var InsertAWImageCommand=function(){};
InsertAWImageCommand.Name='ImageChange';
InsertAWImageCommand.prototype.Execute=function(){}
InsertAWImageCommand.GetState=function() { return FCK_TRISTATE_OFF; }
InsertAWImageCommand.Execute=function() {
  window.open('/automatweb/orb.aw?class=image_manager&doc='+escape(window.parent.location.href)+"&in_popup=1&imgsrc="+escape(FCK.Selection.GetSelectedElement().src), 
					'InsertAWImageCommand', 'width=800,height=500,scrollbars=no,scrolling=no,location=no,toolbar=no');
}
FCKCommands.RegisterCommand('awimagechange', InsertAWImageCommand ); 

FCK.ContextMenu.RegisterListener( {
	AddItems : function( menu, tag, tagName )
	{
		if ( tagName == 'IMG')
		{
			menu.AddSeparator();
			menu.AddItem( "awimagechange", "Pildi atribuudid", 37 ) ;
		}
	}}
);

