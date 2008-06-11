/**
 * toolbar code
 */

var InsertAWFupCommand=function(){};
InsertAWFupCommand.prototype.Execute=function(){}
InsertAWFupCommand.GetState=function() { return FCK_TRISTATE_OFF; }
InsertAWFupCommand.Execute=function() {
  window.open('/automatweb/orb.aw?class=file_manager&doc='+escape(window.parent.location.href), 
					'InsertAWFupCommand', 'width=800,height=600,scrollbars=no,scrolling=no,location=no,toolbar=no');
}
FCKCommands.RegisterCommand('awfup', InsertAWFupCommand ); 
var oawfupItem = new FCKToolbarButton('awfup', FCKLang.AWFileUpload);
oawfupItem.IconPath = FCKPlugins.Items['awfup'].Path + 'image.gif' ;


FCKToolbarItems.RegisterItem( 'awfup', oawfupItem ) ;


var InsertAWFupCommand=function(){};
InsertAWFupCommand.Name='FileChange';
InsertAWFupCommand.prototype.Execute=function(){}
InsertAWFupCommand.GetState=function() { return FCK_TRISTATE_OFF; }
InsertAWFupCommand.Execute=function() {
	window.open('/automatweb/orb.aw?class=file_manager&doc='+escape(window.parent.location.href)+"&in_popup=1&file_id="+FCK.Selection.GetSelectedElement()._oid, 
		'InsertAWFupCommand', 'width=800,height=500,scrollbars=no,scrolling=no,location=no,toolbar=no');
}
FCKCommands.RegisterCommand('awfilechange', InsertAWFupCommand );


FCK.ContextMenu.RegisterListener( {
	AddItems : function( menu, tag, tagName )
	{
			if ( tagName == 'SPAN' )
			{
				if (tag._awfileplaceholder)
				{
					menu.AddSeparator();
					menu.AddItem( "awfilechange", FCKLang.AWFileAttributes, 37 ) ;
				}
			}
	}
});

/**
 * placeholder code
 */

// The object used for all AWFilePlaceholder operations.
var FCKAWFilePlaceholders = new Object() ;

// Add a new placeholder at the actual selection.
FCKAWFilePlaceholders.Add = function( name )
{
	var oSpan = FCK.InsertElement( 'span' ) ;
	this.SetupSpan( oSpan, name ) ;
}

FCKAWFilePlaceholders.GUP = function(param)
{
	param = param.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
	var regexS = "[\\?&]"+param+"=([^&#]*)";
	var regex = new RegExp( regexS );
	var results = regex.exec( window.parent.location.href );
	if( results == null )
	{
		return "";
	}
	else
	{
		return results[1];
	}
}

FCKAWFilePlaceholders.GetUrlContents = function( url )
{
	var req;
	if (window.XMLHttpRequest) 
	{
		req = new XMLHttpRequest();
		req.open('GET', url, false);
		req.send(null);
	} 
	else 
	if (window.ActiveXObject) 
	{
		req = new ActiveXObject('Microsoft.XMLHTTP');
		if (req) 
		{
			req.open('GET', url, false);
			req.send();
		}
	}
	return req.responseText; 
}

FCKAWFilePlaceholders.SetupSpan = function( span, name )
{
	doc_id = FCKAWFilePlaceholders.GUP("id");
	tmp = FCKAWFilePlaceholders.GetUrlContents("/automatweb/orb.aw?class=file&action=get_connection_details_for_doc&doc_id="+doc_id+"&alias_name="+name);
	eval(tmp);
	span.innerHTML = '<img src="http://register.automatweb.com/automatweb/images/icons/class_5.gif" />' + connection_details_for_doc["#"+name+"#"]["name"];
	
	//span.style.backgroundColor = '#ffff00' ;
	//span.style.color = '#000000' ;

	if ( FCKBrowserInfo.IsGecko )
		span.style.cursor = 'default' ;

	span._awfileplaceholder = name ;
	span._oid = connection_details_for_doc["#"+name+"#"]["id"]
	span.contentEditable = false ;

	// To avoid it to be resized.
	span.onresizestart = function()
	{
		FCK.EditorWindow.event.returnValue = false ;
		return false ;
	}
}

// On Gecko we must do this trick so the user select all the SPAN when clicking on it.
FCKAWFilePlaceholders._SetupClickListener = function()
{
	FCKAWFilePlaceholders._ClickListener = function( e )
	{
		if ( e.target.tagName == 'SPAN' && e.target._awfileplaceholder )
			FCKSelection.SelectNode( e.target ) ;
	}

	FCK.EditorDocument.addEventListener( 'click', FCKAWFilePlaceholders._ClickListener, true ) ;
}

// Open the AWFilePlaceholder dialog on double click.
FCKAWFilePlaceholders.OnDoubleClick = function( span )
{
	if ( span.tagName == 'SPAN' && span._awfileplaceholder )
		FCKCommands.GetCommand( 'AWFilePlaceholder' ).Execute() ;
}

FCK.RegisterDoubleClickHandler( FCKAWFilePlaceholders.OnDoubleClick, 'SPAN' ) ;

// Check if a Placholder name is already in use.
FCKAWFilePlaceholders.Exist = function( name )
{
	var aSpans = FCK.EditorDocument.getElementsByTagName( 'SPAN' ) ;

	for ( var i = 0 ; i < aSpans.length ; i++ )
	{
		if ( aSpans[i]._awfileplaceholder == name )
			return true ;
	}

	return false ;
}

if ( FCKBrowserInfo.IsIE )
{
/*
	FCKAWFilePlaceholders.Redraw = function()
	{
		if ( FCK.EditMode != FCK_EDITMODE_WYSIWYG )
			return ;

		var aPlaholders = FCK.EditorDocument.body.innerText.match( /\[\[[^\[\]]+\]\]/g ) ;
		if ( !aPlaholders )
			return ;

		var oRange = FCK.EditorDocument.body.createTextRange() ;

		for ( var i = 0 ; i < aPlaholders.length ; i++ )
		{
			if ( oRange.findText( aPlaholders[i] ) )
			{
				var sName = aPlaholders[i].match( /\[\[\s*([^\]]*?)\s*\]\]/ )[1] ;
				oRange.pasteHTML( '<span style="color: #000000; background-color: #ffff00" contenteditable="false" _awfileplaceholder="' + sName + '">' + aPlaholders[i] + '</span>' ) ;
			}
		}
	}
	*/
}
else
{
	FCKAWFilePlaceholders.Redraw = function()
	{
		if ( FCK.EditMode != FCK_EDITMODE_WYSIWYG )
			return ;

		var oInteractor = FCK.EditorDocument.createTreeWalker( FCK.EditorDocument.body, NodeFilter.SHOW_TEXT, FCKAWFilePlaceholders._AcceptNode, true ) ;

		var	aNodes = new Array() ;

		while ( ( oNode = oInteractor.nextNode() ) )
		{
			aNodes[ aNodes.length ] = oNode ;
		}

		for ( var n = 0 ; n < aNodes.length ; n++ )
		{
			var aPieces = aNodes[n].nodeValue.split( /(#[^#]+#)/g ) ;

			for ( var i = 0 ; i < aPieces.length ; i++ )
			{
				if ( aPieces[i].length > 0 )
				{
					if ( aPieces[i].indexOf( '#' ) == 0 )
					{
						var sName = aPieces[i].match( /#\s*([^#]*?)\s*#/ )[1] ;

						var oSpan = FCK.EditorDocument.createElement( 'span' ) ;
						FCKAWFilePlaceholders.SetupSpan( oSpan, sName ) ;

						aNodes[n].parentNode.insertBefore( oSpan, aNodes[n] ) ;
					}
					else
						aNodes[n].parentNode.insertBefore( FCK.EditorDocument.createTextNode( aPieces[i] ) , aNodes[n] ) ;
				}
			}

			aNodes[n].parentNode.removeChild( aNodes[n] ) ;
		}

		FCKAWFilePlaceholders._SetupClickListener() ;
	}

	// accept aw aliases
	FCKAWFilePlaceholders._AcceptNode = function( node )
	{
		if ( /#file[^#]+#/.test( node.nodeValue ) )
			return NodeFilter.FILTER_ACCEPT ;
		else
			return NodeFilter.FILTER_SKIP ;
	}
}

FCK.Events.AttachEvent( 'OnAfterSetHTML', FCKAWFilePlaceholders.Redraw ) ;

// We must process the SPAN tags to replace then with the real resulting value of the placeholder.
FCKXHtml.TagProcessors['span'] = function( node, htmlNode )
{
	if ( htmlNode._awfileplaceholder )
		node = FCKXHtml.XML.createTextNode( '#' + htmlNode._awfileplaceholder + '#' ) ;
	else
		FCKXHtml._AppendChildNodes( node, htmlNode, false ) ;

	return node ;
}
