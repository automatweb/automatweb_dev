<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>

<window title="AutomatWeb"
    style="margins: 5 5 5 5;"
    xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">

<script type="application/x-javascript"><![CDATA[
// Create a structure member
function rpc_add_member(request,str,name,value)
{
       el_name = request.createElement("name");
       el_val = document.createTextNode(name);
       el_name.appendChild(el_val);

       el_value = request.createElement("value");
       el_val = document.createTextNode(value);
       el_value.appendChild(el_val);

       el_member = request.createElement("member");
       el_member.appendChild(el_name);
       el_member.appendChild(el_value);

       str.appendChild(el_member);
}

function rpc_create_struct(request)
{
       el_struct = request.createElement("struct");
       return el_struct;

}

function callAsync() {

	var request = document.implementation.createDocument("", "methodCall", null);
	el_name = request.createElement('methodName');
	el_node = document.createTextNode("doc::submit");
	el_name.appendChild(el_node);
	request.firstChild.appendChild(el_name);

	var objlist = [{VAR:rlist}];
	var arglist = [];
       
	el_str = rpc_create_struct(request);

	// I have to create different string objects for each argument I'm going to give the beast,
	// because the values will resolved when the call is processed
	for (i = 0; i < objlist.length; i++)
	{
                var el = objlist[i];
		docel = document.getElementById(el);
		if (docel)
		{
			if (docel.tagName == "radiogroup")
			{
				val = docel.selectedItem.value;
			}
			else
			if (docel.tagName == "checkbox")
			{
				val = docel.checked ? docel.getAttribute('xval') : 0;
			}
			else
			{
				val = docel.value;
			};
       			rpc_add_member(request, el_str, el, val);
		};
	};

	el_params = request.createElement('params');
	el_params.appendChild(el_str);

	request.firstChild.appendChild(el_params);

	var httpRequest = new XMLHttpRequest();
	httpRequest.open("POST", "{VAR:rpchandler}", false, null, null);
	httpRequest.send(request);
	var response = httpRequest.responseText;

	var parser = new DOMParser();
	var doc = parser.parseFromString(response,"text/xml");

	// half-assed xmlrpc decoder
	var res = doc.getElementsByTagName("value");
	for (i = 0; i < res.length; i++)
	{
		el = res.item(i).childNodes;
		for (j = 0; j < el.length; j++)
		{
                     if ( (el[j].nodeName == "i4") || (el[j].nodeName == "string") )
                     {
                            rv = el[j].firstChild.nodeValue;
                     };
              };
	
	};
	window.location = rv;
	//alert(response);
}
]]></script>
<vbox style="overflow: auto;" flex="1">

<grid flex="1">
  <columns>
    <column style="width: 100px;"/>
    <column flex="1"/>
  </columns>

  <rows>

{VAR:content}
<!-- SUB: LINE -->
<row>
<label control="{VAR:id}" value="{VAR:caption}"/>
{VAR:element}
</row>
<!-- END SUB: LINE -->
<!-- SUB: SUBMIT -->
<row>
	<button label="Salvesta" onclick="callAsync()" />
</row>
<!-- END SUB: SUBMIT -->
</rows>
</grid>
{VAR:reforb}
</vbox>
</window>

