// AW Javascript functions

// see on n� "core" funktsioon popuppide kuvamiseks. Interface juures on soovitav kasutada
// jargnenaid funktsioone

/*
window.onerror = js_error_dbg

function js_error_dbg(msg, url, line)
{
	return aw_get_url_contents('http://tarvo.dev.struktuur.ee/automatweb/orb.aw?class=file&action=handle_remote_dbg&type=JS-FATAL-ERROR&url=' + Url.encode(url) +'&line=' + line + '&msg=' + msg);
}

function f(msg)
{
	
	return aw_get_url_contents('http://tarvo.dev.struktuur.ee/automatweb/orb.aw?class=file&action=handle_remote_dbg&type=JS-DEBUG&dbg=' + msg);
}
*/

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

function aw_get_el(name,form)
{
    if (!form)
	{
        form = document.changeform;
	}
    for(i = 0; i < form.elements.length; i++)
	{
        el = form.elements[i];
        if (el.name.indexOf(name) == 0)
		{
			return el;
		}
	}
	// here's a fix for IE because in search (class) names are removed from select boxes
	return $("select", form).each(function(){
		if (this.name_tmp == name)
		{
			this.name = this.name_tmp;
			return this;
		}
	});
}

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

var chk_status_nms = new Array();
var chk_status_sets = new Array();
var chk_status = 0;

function aw_sel_chb(form,elname)
{
	found = false;
	for(i = 0; i < chk_status_nms.length;i++)
	{
		if (chk_status_nms[i] == elname)
		{
			found = true;
			break;
		}
	}
	if (!found)
	{
		chk_status_nms.length++;
		chk_status_nms[chk_status_nms.length-1] = elname;
		i = chk_status_nms.length-1;
	}
	chs = !chk_status_sets[i];
	chk_status_sets[i] = chs ? true : false;

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


function aw_date_edit_clear(name)
{
    for(i = 0; i < document.changeform.elements.length; i++)
	{
        el = document.changeform.elements[i];
        if (el.name.indexOf(name) != -1)
		{
			if (el.options)
			{
				el.selectedIndex = 0;
			}
			else
			{
				el.value = '';
			}
		}
	}
} 

var cur_cal_el = null;

function aw_date_edit_show_cal(elname)
{
	cur_cal_el = elname;

	var cal16 = new CalendarPopup();
	cal16.setMonthNames("Jaanuar","Veebruar","M&auml;rts","Aprill","Mai","Juuni","Juuli","August","September","Oktoober","November","Detsember");
	cal16.setMonthAbbreviations("Jan","Veb","Mar","Apr","Mai","Jun","Jul","Aug","Sept","Okt","Nov","Dets");
	cal16.setDayHeaders("P","E","T","K","N","R","L");
	cal16.setWeekStartDay(1); // week is Monday - Sunday
	cal16.setTodayText("T&auml;na");
	cal16.showYearNavigation();

	var y_obj = aw_get_el(elname+"[year]");
	var m_obj = aw_get_el(elname+"[month]");
	var d_obj = aw_get_el(elname+"[day]");

	if (y_obj.value > 0)
	{
		var y = y_obj.value;
	}
	else
    if (y_obj.options && y_obj.selectedIndex > -1)
	{
		var y = y_obj.options[y_obj.selectedIndex].value;
	}


	if (m_obj.value > 0)
	{
		var m = m_obj.value;
	}
	else
    if (m_obj.options && m_obj.selectedIndex > -1)
	{
		var m = m_obj.options[m_obj.selectedIndex].value;
	}

	if (d_obj.value > 0)
	{
		var d = d_obj.value;
	}
	else
    if (d_obj.options && d_obj.selectedIndex > -1)
	{
		var d = d_obj.options[d_obj.selectedIndex].value;
	}

	if (d=="") 
	{ 
		d=1; 
	}
	if (y=="---" || m=="---" || y == undefined || m == undefined || y == "" || m == "") 
	{ 
		dt = null; 
	}
	else
	{
		dt = y+'-'+m+'-'+d;
	}
	cal16.setReturnFunction("aw_date_edit_set_val");
	cal16.showCalendar(elname,dt); 
}

function aw_set_lb_val(el, val)
{
    if (!el.options)
	{
	    return;
	}
	for(i = 0;  i < el.options.length; i++)
	{
		if (el.options[i].value == val)
		{
			el.selectedIndex = i;
			return;
		}
	}
}

function aw_date_edit_set_val(y,m,d)
{
	var y_el = aw_get_el(cur_cal_el+"[year]");
	aw_set_lb_val(y_el, y);
	y_el.value = y;

	var m_el = aw_get_el(cur_cal_el+"[month]");
	aw_set_lb_val(m_el, m);
	m_el.value = m;

	var d_el = aw_get_el(cur_cal_el+"[day]");
	aw_set_lb_val(d_el, d);
	d_el.value = d;
}

function aw_get_url_contents(url)
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

function aw_post_url_contents(url, params)
{
	var req;
	if(window.XMLHttpRequest)
	{
		req = new XMLHttpRequest();
		req.overrideMimeType('text/html');
		req.open('POST', url, false);
		req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		req.setRequestHeader("Content-length", params.length);
		req.setRequestHeader("Connection", "close");
		req.send(params);
	}
	else
	if(window.ActiveXObject)
	{
		req = new ActiveXObject('Microsoft.XMLHTTP');
		if(req)
		{
			req.overrideMimeType('text/html');
			req.open('POST', url, false);
			req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			req.setRequestHeader("Content-length", params.length);
			req.setRequestHeader("Connection", "close");
			req.send(params);
		}
	}
	return req.responseText;
}

var aw_xmlhttpr_cb;

function aw_handle_xml_data()
{
	if (req.readyState == 4)
	{
		// only if "OK"
		if (req.status == 200 && aw_xmlhttpr_cb) 
		{
			aw_xmlhttpr_cb();
		}
	}
}

function aw_do_xmlhttprequest(url, finish_callb)
{
	aw_xmlhttpr_cb = finish_callb;
	if (window.XMLHttpRequest) 
	{
        req = new XMLHttpRequest();
        req.onreadystatechange = aw_handle_xml_data;
        req.open("GET", url, true);
        req.send(null);
	} 
	else 
	if (window.ActiveXObject) 
	{
		req = new ActiveXObject("Microsoft.XMLHTTP");
		if (req) 
		{
            req.onreadystatechange = aw_handle_xml_data;
			req.open("GET", url, true);
			req.send();
		}
	}
}


function aw_clear_list(list)
{
	var listlen = list.length;

	for(i=0; i < listlen; i++)
		list.options[0] = null;
}

function aw_add_list_el(list, value, text)
{
	list.options[list.options.length] = new Option(text,""+value,false,false);
}

var aw_timers = new Array();
function aw_timer(timer)
{
	if(aw_timers[timer])
	{
		tmp = aw_timers[timer];
		aw_timers[timer] = false;
		return (new Date().getTime()) - tmp;
	}
	else
	{
		aw_timers[timer] = new Date().getTime();
		return true;
	}
}

