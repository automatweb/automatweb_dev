/*
 * Plugin to clear html from word
 */

var ClearStylesCommand=function(){};
ClearStylesCommand.Name='Clean Styles';
ClearStylesCommand.prototype.Execute=function(){}
ClearStylesCommand.GetState=function() { return FCK_TRISTATE_OFF; }
ClearStylesCommand.Execute=function() {
	content = FCK.EditorDocument.body.innerHTML;
	FCK.EditorDocument.body.innerHTML = FCKClearStyles.CleanWordHTML(content);
}
FCKCommands.RegisterCommand('clear_styles', ClearStylesCommand ); 

// Create the "Plaholder" toolbar button.
var oClearStyleItem = new FCKToolbarButton( 'clear_styles', FCKLang.ClearStylesBtn ) ;
oClearStyleItem.IconPath = FCKPlugins.Items['clear_styles'].Path + 'button.gif' ;

FCKToolbarItems.RegisterItem( 'clear_styles', oClearStyleItem );

var FCKClearStyles = new Object() ;

FCKClearStyles.CleanWordHTML = function(str)
{
	str = str.replace(/<o:p>\s*<\/o:p>/g, "") ;
	str = str.replace(/<o:p>.*?<\/o:p>/g, "&nbsp;") ;
	str = str.replace( /\s*mso-[^:]+:[^;"]+;?/gi, "" ) ;
	str = str.replace( /\s*MARGIN: 0cm 0cm 0pt\s*;/gi, "" ) ;
	str = str.replace( /\s*MARGIN: 0cm 0cm 0pt\s*"/gi, "\"" ) ;
	str = str.replace( /\s*TEXT-INDENT: 0cm\s*;/gi, "" ) ;
	str = str.replace( /\s*TEXT-INDENT: 0cm\s*"/gi, "\"" ) ;
	str = str.replace( /\s*TEXT-ALIGN: [^\s;]+;?"/gi, "\"" ) ;
	str = str.replace( /\s*PAGE-BREAK-BEFORE: [^\s;]+;?"/gi, "\"" ) ;
	str = str.replace( /\s*FONT-VARIANT: [^\s;]+;?"/gi, "\"" ) ;
	str = str.replace( /\s*tab-stops:[^;"]*;?/gi, "" ) ;
	str = str.replace( /\s*tab-stops:[^"]*/gi, "" ) ;
	str = str.replace( /\s*face="[^"]*"/gi, "" ) ;
	str = str.replace( /\s*face=[^ >]*/gi, "" ) ;
	str = str.replace( /\s*FONT-FAMILY:[^;"]*;?/gi, "" ) ;
	str = str.replace(/<(\w[^>]*) class=([^ |>]*)([^>]*)/gi, "<$1$3") ;
	str = str.replace( /<(\w[^>]*) style="([^\"]*)"([^>]*)/gi, "<$1$3" ) ;
	str = str.replace( /\s*style="\s*"/gi, '' ) ;
	str = str.replace( /<SPAN\s*[^>]*>\s*&nbsp;\s*<\/SPAN>/gi, '&nbsp;' ) ;
	str = str.replace( /<SPAN\s*[^>]*><\/SPAN>/gi, '' ) ;
	str = str.replace(/<(\w[^>]*) lang=([^ |>]*)([^>]*)/gi, "<$1$3") ;
	str = str.replace( /<SPAN\s*>(.*?)<\/SPAN>/gi, '$1' ) ;
	str = str.replace( /<FONT\s*>(.*?)<\/FONT>/gi, '$1' ) ;
	str = str.replace(/<\\?\?xml[^>]*>/gi, "") ;
	str = str.replace(/<\/?\w+:[^>]*>/gi, "") ;
	str = str.replace( /<H\d>\s*<\/H\d>/gi, '' ) ;
	str = str.replace( /<H1([^>]*)>/gi, '' ) ;
	str = str.replace( /<H2([^>]*)>/gi, '' ) ;
	str = str.replace( /<H3([^>]*)>/gi, '' ) ;
	str = str.replace( /<H4([^>]*)>/gi, '' ) ;
	str = str.replace( /<H5([^>]*)>/gi, '' ) ;
	str = str.replace( /<H6([^>]*)>/gi, '' ) ;
	str = str.replace( /<\/H\d>/gi, '<br>' ) ; //remove this to take out breaks where Heading tags were
	str = str.replace( /<(U|I|STRIKE)>&nbsp;<\/\1>/g, '&nbsp;' ) ;
	if (!document.all) // If Internet Explorer. 
	{
		str = str.replace( /<(B|b)>&nbsp;<\/\b|B>/g, '' ) ;
	}
	str = str.replace( /<([^\s>]+)[^>]*>\s*<\/\1>/g, '' ) ;
	str = str.replace( /<([^\s>]+)[^>]*>\s*<\/\1>/g, '' ) ;
	str = str.replace( /<([^\s>]+)[^>]*>\s*<\/\1>/g, '' ) ;
	//some RegEx code for the picky browsers
	var re = new RegExp("(<P)([^>]*>.*?)(<\/P>)","gi") ;
	str = str.replace( re, "<div$2</div>" ) ;
	var re2 = new RegExp("(<font|<FONT)([^*>]*>.*?)(<\/FONT>|<\/font>)","gi") ;
	str = str.replace( re2, "<div$2</div>") ;
	str = str.replace( /size|SIZE = ([\d]{1})/g, '' ) ;
	
	return str ;
}