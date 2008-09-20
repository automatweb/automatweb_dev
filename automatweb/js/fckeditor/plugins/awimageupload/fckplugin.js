var _aw_fck_selected_image_oid = false;
var _aw_fck_selected_image_alias = false;



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
	oid =  _aw_fck_selected_image_oid;
	//alert (_aw_fck_selected_image_alias);
	window.open('/automatweb/orb.aw?class=image_manager&doc='+escape(window.parent.location.href)+"&in_popup=1&image_id="+oid,
		'InsertAWImageCommand', 'width=800,height=500,scrollbars=no,scrolling=no,location=no,toolbar=no');
}
FCKCommands.RegisterCommand('awimagechange', InsertAWImageCommand ); 


var FloatImage2LeftCommand=function(){};
FloatImage2LeftCommand.Name='Float2Left';
FloatImage2LeftCommand.prototype.Execute=function(){}
FloatImage2LeftCommand.GetState=function() { return FCK_TRISTATE_OFF; }
FloatImage2LeftCommand.Execute=function() {
	FCKAWImagePlaceholders.Add(FCK, _aw_fck_selected_image_alias, "v" )
}
FCKCommands.RegisterCommand('awimagechange_float_left', FloatImage2LeftCommand ); 


// float to right command
var FloatImage2RightCommand=function(){};
FloatImage2RightCommand.Name='Float2Right';
FloatImage2RightCommand.prototype.Execute=function(){}
FloatImage2RightCommand.GetState=function() { return FCK_TRISTATE_OFF; }
FloatImage2RightCommand.Execute=function() {
	FCKAWImagePlaceholders.Add(FCK, _aw_fck_selected_image_alias, "p" )
}
FCKCommands.RegisterCommand('awimagechange_float_right', FloatImage2RightCommand ); 

/** backward compability 
 *
 */
var InsertAWImageCommandOld=function(){};
InsertAWImageCommandOld.Name='ImageChangeOld';
InsertAWImageCommandOld.prototype.Execute=function(){}
InsertAWImageCommandOld.GetState=function() { return FCK_TRISTATE_OFF; }
InsertAWImageCommandOld.Execute=function() {
  window.open('/automatweb/orb.aw?class=image_manager&doc='+escape(window.parent.location.href)+"&in_popup=1&imgsrc="+escape(FCK.Selection.GetSelectedElement().src), 
					'InsertAWImageCommand', 'width=800,height=500,scrollbars=no,scrolling=no,location=no,toolbar=no');
}
FCKCommands.RegisterCommand('awimagechange_old', InsertAWImageCommandOld ); 

FCK.ContextMenu.RegisterListener( {
	AddItems : function( menu, tag, tagName )
	{
		if ( tagName == 'IMG')
		{
			if (tag._awimageplaceholder)
			{
				//menu.AddSeparator();
				//menu.AddItem( "awimagechange_float_left", "Joonda vasakule", 37 ) ;
				//menu.AddItem( "awimagechange_float_right", "Joonda paremale", 37 ) ;
				menu.AddSeparator();
				menu.AddItem( "awimagechange", "Pildi atribuudid", 37 ) ;
			} // probably IE
			else if (tag.parentNode._awimageplaceholder)
			{
				menu.AddSeparator();
				menu.AddItem( "awimagechange", "Pildi atribuudid", 37 ) ;
			}
			else
			{
				menu.AddSeparator();
				menu.AddItem( "awimagechange_old", "Pildi atribuudid vana", 37 ) ;
			}
		}
		if ( tagName == 'TABLE')
		{
			if (tag._awimageplaceholder)
			{
				menu.AddSeparator();
				menu.AddItem( "awimagechange_float_left", "Joonda vasakule", 37 ) ;
				menu.AddItem( "awimagechange_float_right", "Joonda paremale", 37 ) ;
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
FCKAWImagePlaceholders.Add = function( oEditor, name, float2 )
{
	if (!float2)
	{
		float2 = "";
	}
	name = name.match(/pict[0-9]/ig);
	// otherwise we insert the new alias into placeholder span
	if (FCKBrowserInfo.IsIE)
	{
		//FCKSelection.SelectNode( FCK.Selection.GetSelectedElement().parentNode ) ;
	}
	oEditor.InsertHtml( '#' + name + float2 +'#'  )
	FCKAWImagePlaceholders.Redraw();
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


FCKAWImagePlaceholders.GetImageFloat = function( image_name )
{
	float_sufix = image_name.match( /[a-z]*[0-9]{1,}(.*)$/ )[1];
	var out;
	if (float_sufix.length > 0)
	{
		if (float_sufix=="p")
		{
			out = "right";
		}
		else if (float_sufix=="v")
		{
			out = "left";
		}
		else if (float_sufix=="k")
		{
			out = "center";
		}
		return out;
	}
	else
	{
		return false;
	}
}

FCKAWImagePlaceholders.SetupTable = function( table, name )
{
	doc_id = FCKAWImagePlaceholders.GUP("id");
	tmp = FCKAWImagePlaceholders.GetUrlContents("/automatweb/orb.aw?class=image&action=get_connection_details_for_doc&doc_id="+doc_id+"&alias_name="+name);
	eval(tmp);
	table.className = "Fck_image";
	table.style.display = "block";
	table.width = connection_details_for_doc["#"+name+"#"]["width"]+"px ! important";
	img_float = FCKAWImagePlaceholders.GetImageFloat(name);
	table.alias = "#"+name+"#";
	if (img_float == "left" || img_float == "right")
	{
		table.setAttribute("style","float:"+img_float);
		table.style.styleFloat = img_float;
	}
	else if (img_float == "center")
	{
		table.style.textAlign = "center";
		table.style.width= "100%";
	}
	table.innerHTML = "<img _awimageplaceholder=\""+name+"\" alias=\"#"+name+"#\" width="+connection_details_for_doc["#"+name+"#"]["width"]+" src='"+connection_details_for_doc["#"+name+"#"]["url"]+"' />"+
			"<br />"+
			"<b>"+connection_details_for_doc["#"+name+"#"]["comment"]+"</b>";

	if ( FCKBrowserInfo.IsGecko )
		table.style.cursor = 'default' ;

	table._awimageplaceholder = name ;
	table._oid = connection_details_for_doc["#"+name+"#"]["id"]
	table.contentEditable = false ;

	// To avoid it to be resized.
	table.onresizestart = function()
	{
		FCK.EditorWindow.event.returnValue = false ;
		return false ;
	}
}

FCKAWImagePlaceholders.SetupSpan = function( span, name )
{
	doc_id = FCKAWImagePlaceholders.GUP("id");
	tmp = FCKAWImagePlaceholders.GetUrlContents("/automatweb/orb.aw?class=image&action=get_connection_details_for_doc&doc_id="+doc_id+"&alias_name="+name);
	eval(tmp);
	span.className = "Fck_image";
	span.style.display = "block";
	span.style.width = connection_details_for_doc["#"+name+"#"]["width"]+"px ! important";
	img_float = FCKAWImagePlaceholders.GetImageFloat(name);
	span.alias = "#"+name+"#";
	if (img_float == "left" || img_float == "right")
	{
		span.setAttribute("style","float:"+img_float);
		span.style.styleFloat = img_float;
	}
	else if (img_float == "center")
	{
		span.style.textAlign = "center";
		span.style.width= "100%";
	}
	span.innerHTML = "<img _awimageplaceholder=\""+name+"\" alias=\"#"+name+"#\" width="+connection_details_for_doc["#"+name+"#"]["width"]+" src='"+connection_details_for_doc["#"+name+"#"]["url"]+"' />"+
			"<br />"+
			"<b>"+connection_details_for_doc["#"+name+"#"]["comment"]+"</b>";

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

FCKAWImagePlaceholders._SetupClickListener = function()
{
	// because we call out FCKAWImagePlaceholders.Redraw() many times we 
	// can't attach multiple events doing the same things
	if (!FCKAWImagePlaceholders._SetupClickListenerInitialized)
	{
		FCKAWImagePlaceholders._SetupClickListenerInitialized = true
	}
	else
	{
		return false;
	}
	
	FCKAWImagePlaceholders.onPaste = function( e )
	{
		if (!e) var e = FCK.EditorWindow.event ;

		setTimeout(function() {
			sHTML = FCK.EditorDocument.body.innerHTML;
			var re=/alias/;
			if (re.test(sHTML))
			{
				FCK.EditorDocument.body.innerHTML = sHTML.replace(/<span.*img.*?alias="(.*?)".*?span>/g, "$1")
				FCKAWImagePlaceholders.Redraw();
				e.returnValue = false;
			}
		}, 1); // 1ms should be enough
	}
	
	FCKAWImagePlaceholders.onPasteIE = function(  )
	{
		var e = FCK.EditorWindow.event ;
		e.target = e.srcElement
		e.preventDefault();
		e.stopPropagation(); // ie
	}

	FCKAWImagePlaceholders._ClickListener = function( e )
	{
		if (!e) {var e = FCK.EditorWindow.event ; e.target = e.srcElement}
		if ( e.target.tagName == 'SPAN' )
		{
			if (e.target._awimageplaceholder )
			{
				_aw_fck_selected_image_oid = e.target._oid;
				_aw_fck_selected_image_alias = e.target._awimageplaceholder;
				FCKSelection.SelectNode( e.target ) ;
			}
		}
		if ( e.target.tagName == 'IMG')
		{
			if (e.target.parentNode._awimageplaceholder )
			{
				_aw_fck_selected_image_oid = e.target.parentNode._oid;
				_aw_fck_selected_image_alias = e.target.parentNode._awimageplaceholder;
				FCKSelection.SelectNode( e.target.parentNode ) ;
			}
		}
	}
	
	FCKAWImagePlaceholders._ClickListenerIE = function(  )
	{
		var e = FCK.EditorWindow.event ;
		e.target = e.srcElement

		if ( e.target.parentNode.tagName == 'SPAN' && e.target.parentNode._awimageplaceholder )
		{
			setTimeout(function() {
				_aw_fck_selected_image_oid = e.target._oid;
				_aw_fck_selected_image_alias = e.target._awimageplaceholder;
				//FCKSelection.SelectNode( e.target ) ;
			}, 1); // 1ms should be enough
		}
		if ( e.target.tagName == 'IMG' && e.target.parentNode._awimageplaceholder )
		{
			setTimeout(function() {
				_aw_fck_selected_image_oid = e.target._oid;
				_aw_fck_selected_image_alias = e.target._awimageplaceholder;
				//FCKSelection.SelectNode( e.target.parentNode ) ;
			}, 1); // 1ms should be enough
		}
	}
	
	if (document.all) {        // If Internet Explorer.
		// this was intended for ie's right click, so image caption could also be right clicked
		//FCK.EditorDocument.attachEvent("onclick", FCKAWImagePlaceholders._ClickListenerIE ) ;
		FCK.EditorDocument.attachEvent("onmousedown", FCKAWImagePlaceholders._ClickListenerIE ) ;
		//FCK.EditorDocument.attachEvent( 'OnPaste', FCKAWImagePlaceholders.onPasteIE ) ;
	} else {                // If Gecko.
		//FCK.EditorDocument.addEventListener( 'click', DenGecko_OnKeyDown, true ) ;
		FCK.EditorDocument.addEventListener( 'click', FCKAWImagePlaceholders._ClickListener, true ) ;
		FCK.EditorDocument.addEventListener( 'paste', FCKAWImagePlaceholders.onPaste, true ) ;	
	}
}

// Open the AWFilePlaceholder dialog on double click.
FCKAWImagePlaceholders.OnDoubleClick = function( span )
{
	if ( span.tagName == 'SPAN' && span._awimageplaceholder )
		FCKCommands.GetCommand( 'AWFilePlaceholder' ).Execute() ;
}

FCK.RegisterDoubleClickHandler( FCKAWImagePlaceholders.OnDoubleClick, 'SPAN' ) ;

// Check if a Placholder name is already in use.
FCKAWImagePlaceholders.Exist = function( name )
{
	var aSpans = FCK.EditorDocument.getElementsByTagName( 'SPAN' ) ;

	for ( var i = 0 ; i < aSpans.length ; i++ )
	{
		if ( aSpans[i]._awimageplaceholder == name )
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
				img_float = FCKAWImagePlaceholders.GetImageFloat(name);
				img_align = "";
				
				if (img_float)
				{
					if (img_float == "left" || img_float == "right")
					{
						img_align = "style=\"float:"+img_float+"\"";
					}
					else if (img_float == "center" ) 
					{
						img_align = "style=\"text-align: center\"";
					}
				}
				oRange.pasteHTML('<span class="Fck_image" '+img_align+' width='+connection_details_for_doc["#"+name+"#"]["width"]+' _awimageplaceholder="'+ name +'" _oid="'+ connection_details_for_doc["#"+name+"#"]["id"] +'">' + 
					"<img width="+connection_details_for_doc["#"+name+"#"]["width"]+" src='"+connection_details_for_doc["#"+name+"#"]["url"]+"' _awimageplaceholder="+name+" _oid="+connection_details_for_doc["#"+name+"#"]["id"]+" />"+
				  	'</span>');
			}
		}
		FCKAWImagePlaceholders._SetupClickListener() ;
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

						var oTable = FCK.EditorDocument.createElement( 'table' ) ;
						FCKAWImagePlaceholders.SetupTable( oTable, sName ) ;

						aNodes[n].parentNode.insertBefore( oTable, aNodes[n] ) ;
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
FCKXHtml.TagProcessors['table'] = function( node, htmlNode )
{
	if ( htmlNode._awimageplaceholder )
		node = FCKXHtml.XML.createTextNode( '#' + htmlNode._awimageplaceholder + '#' ) ;
	else
		FCKXHtml._AppendChildNodes( node, htmlNode, false ) ;
		
	return node ;
}

// We must process the SPAN tags to replace then with the real resulting value of the placeholder.
FCKXHtml.TagProcessors['span'] = function( node, htmlNode )
{
	if ( htmlNode._awfileplaceholder )
		node = FCKXHtml.XML.createTextNode( '#' + htmlNode._awfileplaceholder + '#' ) ;
	else if (FCKBrowserInfo.IsIE && htmlNode._awimageplaceholder)
		node = FCKXHtml.XML.createTextNode( '#' + htmlNode._awimageplaceholder + '#' ) ;
	else
		FCKXHtml._AppendChildNodes( node, htmlNode, false ) ;
		
	return node ;
}
