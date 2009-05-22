/*
 * Plugin to clear html from word
 */

var ClearStylesCommand=function(){};
var ScrollTop;
ClearStylesCommand.Name='Clean Styles';
ClearStylesCommand.prototype.Execute=function(){}
ClearStylesCommand.GetState=function() { return FCK_TRISTATE_OFF; }
ClearStylesCommand.Execute=function() {
	var http = new XMLHttpRequest();
	
	var url = "http://hannes.dev.struktuur.ee/automatweb/orb.aw?class=doc&action=clean_up_html";
	var params = "html="+escape(FCK.GetData());
	http.open("POST", url, true);

	//ScrollTop = (document.all)?FCK.EditorDocument.body.scrollTop:window.pageYOffset;
	//ScrollTop = (document.all)?FCK.EditorDocument.body.scrollTop:FCK.EditorDocument.pageYOffset;
	ScrollTop = FCK.EditorDocument.body.scrollTop;

	//Send the proper header information along with the request
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	//http.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8");
	http.setRequestHeader("Content-length", params.length);
	http.setRequestHeader("Connection", "close");
	
	http.onreadystatechange = function() {//Call a function when the state changes.
		if(http.readyState == 4 && http.status == 200)
		{
			FCK.SetData( unescape (http.responseText) );
			window.setTimeout("FCK.EditorDocument.body.scrollTop = ScrollTop",100);
		}
	}
	//alert(params);
	http.send(params);
}
FCKCommands.RegisterCommand('clear_styles', ClearStylesCommand ); 

// Create the "Plaholder" toolbar button.
var oClearStyleItem = new FCKToolbarButton( 'clear_styles', FCKLang.ClearStylesBtn ) ;
oClearStyleItem.IconPath = FCKPlugins.Items['clear_styles'].Path + 'button.gif' ;

FCKToolbarItems.RegisterItem( 'clear_styles', oClearStyleItem );

var FCKClearStyles = new Object() ;