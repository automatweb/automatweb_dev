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
  window.open('/automatweb/orb.aw?class=image_manager&doc='+escape(window.parent.location.href)+"&in_popup=1&image_id="+FCK.Selection.GetSelectedElement()._oid,
					'InsertAWImageCommand', 'width=800,height=500,scrollbars=no,scrolling=no,location=no,toolbar=no');
}
FCKCommands.RegisterCommand('awimagechange', InsertAWImageCommand ); 

FCK.ContextMenu.RegisterListener( {
	AddItems : function( menu, tag, tagName )
	{
		if ( tagName == 'SPAN')
		{
			if (tag._awimageplaceholder)
			{
				menu.AddSeparator();
				menu.AddItem( "awimagechange", "Pildi atribuudid", 37 ) ;
			}
			
		}
	}}
);




/**
 * placeholder code
 */

// The object used for all AWFilePlaceholder operations.
var FCKAWImagePlaceholders = new Object() ;

// Add a new placeholder at the actual selection.
FCKAWImagePlaceholders.Add = function( name )
{
	var oSpan = FCK.InsertElement( 'SPAN' ) ;
	this.SetupImageDIV( oSpan, name ) ;
}

FCKAWImagePlaceholders.GUP = function(param)
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

FCKAWImagePlaceholders.GetUrlContents = function( url )
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

FCKAWImagePlaceholders.SetupSpan = function( span, name )
{
	doc_id = FCKAWImagePlaceholders.GUP("id");
	tmp = FCKAWImagePlaceholders.GetUrlContents("/automatweb/orb.aw?class=image&action=get_connection_details_for_doc&doc_id="+doc_id+"&alias_name="+name);
	eval(tmp);
	span.innerHTML = connection_details_for_doc["#"+name+"#"]["name"];
	
	//span.style.backgroundColor = '#ffff00' ;
	//span.style.color = '#000000' ;

	if ( FCKBrowserInfo.IsGecko )
		span.style.cursor = 'default' ;

	span._awimageplaceholder = name ;
	span._oid = connection_details_for_doc["#"+name+"#"]["id"]
	span.contentEditable = false ;

	// To avoid it to be resized.
	span.onresizestart = function()
	{
		FCK.EditorWindow.event.returnValue = false ;
		return false ;
	}
}

FCKAWImagePlaceholders.SetupImageDIV = function( div, name )
{
	doc_id = FCKAWImagePlaceholders.GUP("id");
	tmp = FCKAWImagePlaceholders.GetUrlContents("/automatweb/orb.aw?class=image&action=get_connection_details_for_doc&doc_id="+doc_id+"&alias_name="+name);
	eval(tmp);
	div.innerHTML = connection_details_for_doc["#"+name+"#"]["name"];

	if ( FCKBrowserInfo.IsGecko )
		div.style.cursor = 'default' ;

	div._awimageplaceholder = name ;
	div._oid = connection_details_for_doc["#"+name+"#"]["id"]
	//table.style.border = "0";

	// To avoid it to be resized.
	div.onresizestart = function()
	{	
		FCK.EditorWindow.event.returnValue = false ;
		return false ;
	}
}

// On Gecko we must do this trick so the user select all the SPAN when clicking on it.
FCKAWImagePlaceholders._SetupClickListener = function()
{
	FCKAWImagePlaceholders._ClickListener = function( e )
	{
		if ( e.target.tagName == 'SPAN' && e.target._awimageplaceholder )
			FCKSelection.SelectNode( e.target ) ;
	}

	FCK.EditorDocument.addEventListener( 'click', FCKAWImagePlaceholders._ClickListener, true ) ;
}

// Open the AWFilePlaceholder dialog on double click.
FCKAWImagePlaceholders.OnDoubleClick = function( div )
{
	if ( div.tagName == 'SPAN' && div._awimageplaceholder )
		FCKCommands.GetCommand( 'awimagechange' ).Execute() ;
}

FCK.RegisterDoubleClickHandler( FCKAWImagePlaceholders.OnDoubleClick, 'SPAN' ) ;

// Check if a Placholder name is already in use.
FCKAWImagePlaceholders.Exist = function( name )
{
	var aDivs = FCK.EditorDocument.getElementsByTagName( 'SPAN' ) ;

	for ( var i = 0 ; i < aDivs.length ; i++ )
	{
		if ( aDivs[i]._awimageplaceholder == name )
			return true ;
	}

	return false ;
}

if ( FCKBrowserInfo.IsIE )
{
	FCKAWImagePlaceholders.Redraw = function()
	{
		if ( FCK.EditMode != FCK_EDITMODE_WYSIWYG )
			return ;

		var aPlaholders = FCK.EditorDocument.body.innerText.match( /(#pict[^#]+#)/g ) ;
		if ( !aPlaholders )
			return ;
			
		var oRange = FCK.EditorDocument.body.createTextRange() ;

		for ( var i = 0 ; i < aPlaholders.length ; i++ )
		{
			if ( oRange.findText( aPlaholders[i] ) )
			{
				var name = aPlaholders[i].match( /#([^#]*?)#/ )[1] ;
			
				doc_id = FCKAWImagePlaceholders.GUP("id");
				tmp = FCKAWImagePlaceholders.GetUrlContents("/automatweb/orb.aw?class=image&action=get_connection_details_for_doc&doc_id="+doc_id+"&alias_name="+name);
				eval(tmp);

				oRange.pasteHTML('<span style="display: block; width: 150px;" contenteditable="false" _awimageplaceholder="'+ name +'" _oid="'+ connection_details_for_doc["#"+name+"#"]["id"] +'">' + connection_details_for_doc["#"+name+"#"]["name"]) + '</span>';
			}
		}
	}
}
else
{
	FCKAWImagePlaceholders.Redraw = function()
	{
		if ( FCK.EditMode != FCK_EDITMODE_WYSIWYG )
			return ;

		var oInteractor = FCK.EditorDocument.createTreeWalker( FCK.EditorDocument.body, NodeFilter.SHOW_TEXT, FCKAWImagePlaceholders._AcceptNode, true ) ;

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

						var oDiv = FCK.EditorDocument.createElement( 'SPAN' ) ;
						FCKAWImagePlaceholders.SetupImageDIV( oDiv, sName ) ;

						aNodes[n].parentNode.insertBefore( oDiv, aNodes[n] ) ;
					}
					else
						aNodes[n].parentNode.insertBefore( FCK.EditorDocument.createTextNode( aPieces[i] ) , aNodes[n] ) ;
				}
			}

			aNodes[n].parentNode.removeChild( aNodes[n] ) ;
		}

		FCKAWImagePlaceholders._SetupClickListener() ;
	}

	// accept aw aliases
	FCKAWImagePlaceholders._AcceptNode = function( node )
	{
		if ( /#pict[^#]+#/.test( node.nodeValue ) )
			return NodeFilter.FILTER_ACCEPT ;
		else
			return NodeFilter.FILTER_SKIP ;
	}
}

FCK.Events.AttachEvent( 'OnAfterSetHTML', FCKAWImagePlaceholders.Redraw ) ;

// We must process the SPAN tags to replace then with the real resulting value of the placeholder.
FCKXHtml.TagProcessors['SPAN'] = function( node, htmlNode )
{
	if ( htmlNode._awimageplaceholder )
		node = FCKXHtml.XML.createTextNode( '#' + htmlNode._awimageplaceholder + '#' ) ;
	else
		FCKXHtml._AppendChildNodes( node, htmlNode, false ) ;

	return node ;
}
