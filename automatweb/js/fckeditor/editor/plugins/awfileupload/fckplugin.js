FCKCommands.RegisterCommand(
   'awfileupload',
    new FCKDialogCommand(
        'boo',
        'Faili &uuml;leslaadimine',
        'http://terryf.dev.struktuur.ee/automatweb/orb.aw?class=file&action=new&parent=1316', 
        600, 
        530
   )
);

var oawfileuploadItem = new FCKToolbarButton('awfileupload', 'Faili &uuml;leslaadimine');
oawfileuploadItem.IconPath = 'http://terryf.dev.struktuur.ee/automatweb/js/fckeditor/editor/plugins/awfileupload/find.gif' ;


FCKToolbarItems.RegisterItem( 'awfileupload', oawfileuploadItem ) ;

