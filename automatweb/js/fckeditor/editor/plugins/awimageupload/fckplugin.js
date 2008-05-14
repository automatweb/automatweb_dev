var InsertAWImageCommand=function(){};
InsertAWImageCommand.prototype.Execute=function(){}
InsertAWImageCommand.GetState=function() { return FCK_TRISTATE_OFF; }
InsertAWImageCommand.Execute=function() {
  window.open('../../../orb.aw?class=image_manager&doc='+escape(window.parent.location.href), 
					'InsertAWImageCommand', 'width=800,height=600,scrollbars=no,scrolling=no,location=no,toolbar=no');
}
FCKCommands.RegisterCommand('awimageupload', InsertAWImageCommand ); 
var oawimageuploadItem = new FCKToolbarButton('awimageupload', FCKLang.AWUploadImage);
oawimageuploadItem.IconPath = '/automatweb/js/fckeditor/editor/plugins/awimageupload/image.gif' ;


FCKToolbarItems.RegisterItem( 'awimageupload', oawimageuploadItem ) ;


