/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2008 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Plugin to insert "Placeholders" in the editor.
 */

// Register the related command.
FCKCommands.RegisterCommand( 'Placeholder', new FCKDialogCommand( 'Placeholder', FCKLang.PlaceholderDlgTitle, FCKPlugins.Items['placeholder'].Path + 'fck_placeholder.html', 340, 160 ) ) ;

// Create the "Plaholder" toolbar button.
var oPlaceholderItem = new FCKToolbarButton( 'Placeholder', FCKLang.PlaceholderBtn ) ;
oPlaceholderItem.IconPath = FCKPlugins.Items['placeholder'].Path + 'placeholder.gif' ;

FCKToolbarItems.RegisterItem( 'Placeholder', oPlaceholderItem ) ;


// The object used for all Placeholder operations.
var FCKPlaceholders = new Object() ;

// Add a new placeholder at the actual selection.
FCKPlaceholders.Add = function( name )
{
	var oSpan = FCK.InsertElement( 'span' ) ;
	this.SetupSpan( oSpan, name ) ;
}


FCKPlaceholders.GetUrlContents = function( url )
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

FCKPlaceholders.SetupSpan = function( span, name )
{
	//span.innerHTML = '[[ ' + name + ' ]]' ;
	
	tmp = FCKPlaceholders.GetUrlContents("/automatweb/orb.aw?class=file&action=get_connection_details_for_doc&doc_id=5631&alias_name="+name);
	eval(tmp);
	span.innerHTML = '<img src="http://register.automatweb.com/automatweb/images/icons/class_5.gif" />' + connection_details_for_doc["#"+name+"#"]["name"];
	
	//span.style.backgroundColor = '#ffff00' ;
	//span.style.color = '#000000' ;

	if ( FCKBrowserInfo.IsGecko )
		span.style.cursor = 'default' ;

	span._fckplaceholder = name ;
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
FCKPlaceholders._SetupClickListener = function()
{
	FCKPlaceholders._ClickListener = function( e )
	{
		if ( e.target.tagName == 'SPAN' && e.target._fckplaceholder )
			FCKSelection.SelectNode( e.target ) ;
	}

	FCK.EditorDocument.addEventListener( 'click', FCKPlaceholders._ClickListener, true ) ;
}

// Open the Placeholder dialog on double click.
FCKPlaceholders.OnDoubleClick = function( span )
{
	if ( span.tagName == 'SPAN' && span._fckplaceholder )
		FCKCommands.GetCommand( 'Placeholder' ).Execute() ;
}

FCK.RegisterDoubleClickHandler( FCKPlaceholders.OnDoubleClick, 'SPAN' ) ;

// Check if a Placholder name is already in use.
FCKPlaceholders.Exist = function( name )
{
	var aSpans = FCK.EditorDocument.getElementsByTagName( 'SPAN' ) ;

	for ( var i = 0 ; i < aSpans.length ; i++ )
	{
		if ( aSpans[i]._fckplaceholder == name )
			return true ;
	}

	return false ;
}

if ( FCKBrowserInfo.IsIE )
{
/*
	FCKPlaceholders.Redraw = function()
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
				oRange.pasteHTML( '<span style="color: #000000; background-color: #ffff00" contenteditable="false" _fckplaceholder="' + sName + '">' + aPlaholders[i] + '</span>' ) ;
			}
		}
	}
	*/
}
else
{
	FCKPlaceholders.Redraw = function()
	{
		if ( FCK.EditMode != FCK_EDITMODE_WYSIWYG )
			return ;

		var oInteractor = FCK.EditorDocument.createTreeWalker( FCK.EditorDocument.body, NodeFilter.SHOW_TEXT, FCKPlaceholders._AcceptNode, true ) ;

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
						FCKPlaceholders.SetupSpan( oSpan, sName ) ;

						aNodes[n].parentNode.insertBefore( oSpan, aNodes[n] ) ;
					}
					else
						aNodes[n].parentNode.insertBefore( FCK.EditorDocument.createTextNode( aPieces[i] ) , aNodes[n] ) ;
				}
			}

			aNodes[n].parentNode.removeChild( aNodes[n] ) ;
		}

		FCKPlaceholders._SetupClickListener() ;
	}

	// accept aw aliases
	FCKPlaceholders._AcceptNode = function( node )
	{
		if ( /#file[^#]+#/.test( node.nodeValue ) )
			return NodeFilter.FILTER_ACCEPT ;
		else
			return NodeFilter.FILTER_SKIP ;
	}
}

FCK.Events.AttachEvent( 'OnAfterSetHTML', FCKPlaceholders.Redraw ) ;

// We must process the SPAN tags to replace then with the real resulting value of the placeholder.
FCKXHtml.TagProcessors['span'] = function( node, htmlNode )
{
	if ( htmlNode._fckplaceholder )
		node = FCKXHtml.XML.createTextNode( '#' + htmlNode._fckplaceholder + '#' ) ;
	else
		FCKXHtml._AppendChildNodes( node, htmlNode, false ) ;

	return node ;
}
