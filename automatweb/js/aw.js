// AW Javascript functions

// see on nö "core" funktsioon popuppide kuvamiseks. Interface juures on soovitav kasutada
// jargnenaid funktsioone
function _aw_popup(file,name,toolbar,location,status,menubar,scrollbars,resizable,width,height)
{
	 var wprops = 	"toolbar=" + toolbar + "," + 
	 		"location= " + location + "," +
			"directories=0," + 
			"status=" + status + "," +
	        	"menubar=" + menubar + "," +
			"scrollbars=" + scrollbars + "," +
			"resizable=" + resizable + "," +
			"width=" + width + "," +
			"height=" + height;

	openwindow = window.open(file,name,wprops);
};

function aw_popup(file,name,width,height)
{
	_aw_popup(file,name,0,1,0,0,0,1,width,height);
}

function aw_popup_s(file,name,width,height)
{
	_aw_popup(file,name,0,0,0,0,0,0,width,height);
};

function aw_popup_scroll(file,name,width,height)
{
	_aw_popup(file,name,0,0,0,0,1,1,width,height);
};

function list_preset(el,oid)
{
	var i = 1;
	elem = el + '_' + i;
	while(it = document.getElementById(elem))
	{
		it.style.color='blue';
	
		i+=1;
		elem = el + '_' + i;
	}
	document.getElementById(el).value=oid;
}

// set/changes cookie 
function set_cookie(name,value)
{
        document.cookie = name+"="+value;
}


// gets the value of a cookie
function get_cookie(name)
{
        if (document.cookie.length > 0)
        {
                // we can have multiple cookies on a domain
                begin = document.cookie.indexOf(name+"=");
                if (begin != -1)
                {
                        begin += name.length+1;
                        end = document.cookie.indexOf(";", begin);
                        if (end == -1) end = document.cookie.length;
                        return document.cookie.substring(begin, end);
                }
        }
        else
        {
                return -1;
        }
}

// checks whether value exists in arr
function aw_in_array(value,arr)
{
	for (i = 0; i < arr.length; i++)
		if (arr[i] == value)
			return 1;
	return 0;
}

// removes value from array, returns the new array
function aw_remove_arr_el(value,arr)
{
	rv = new Array();
	for (i = 0; i < arr.length; i++)
		if (arr[i] != value)
			rv.push(arr[i]);
	return rv;
};

function awlib_addevent(o,e,f)
{
	if(o.addEventListener)
		o.addEventListener(e,f,true);

	else if(o.attachEvent)
		o.attachEvent("on"+e,f);
	
	else
		eval("o.on"+e+"="+f)
};

var chk_status;

function aw_sel_chb(form,elname)
{
	chs = !chk_status;

	len = form.elements.length;
	for(i = 0; i < len; i++)
	{
		if (form.elements[i].name.indexOf(elname) != -1)
		{
			form.elements[i].checked = chs;
		}
	}

	chk_status  = chk_status ? 0 : 1;
}
