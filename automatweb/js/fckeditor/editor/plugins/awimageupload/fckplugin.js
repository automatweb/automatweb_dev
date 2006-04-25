var InsertAWImageCommand=function(){};
InsertAWImageCommand.prototype.Execute=function(){}
InsertAWImageCommand.GetState=function() { return FCK_TRISTATE_OFF; }
InsertAWImageCommand.Execute=function() {
  window.open('/automatweb/orb.aw?class=image_manager&doc='+escape(window.parent.location.href), 
					'InsertAWImageCommand', 'width=500,height=400,scrollbars=no,scrolling=no,location=no,toolbar=no');
}
FCKCommands.RegisterCommand('awimageupload', InsertAWImageCommand ); 
var oawimageuploadItem = new FCKToolbarButton('awimageupload', 'Pildi &uuml;leslaadimine');
oawimageuploadItem.IconPath = '/automatweb/js/fckeditor/editor/plugins/awimageupload/image.gif' ;


FCKToolbarItems.RegisterItem( 'awimageupload', oawimageuploadItem ) ;


