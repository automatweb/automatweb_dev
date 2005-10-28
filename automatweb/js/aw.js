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

function aw_get_el(name)
{
    for(i = 0; i < document.changeform.elements.length; i++)
	{
        el = document.changeform.elements[i];
        if (el.name.indexOf(name) != -1)
		{
			return el;
		}
	}
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


function aw_date_edit_clear(name)
{
    for(i = 0; i < document.changeform.elements.length; i++)
	{
        el = document.changeform.elements[i];
        if (el.name.indexOf(name) != -1)
		{
		  el.selectedIndex = 0;
		  el.value = '';
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

	var y_obj = aw_get_el(elname+"[year]");
	var m_obj = aw_get_el(elname+"[month]");
	var d_obj = aw_get_el(elname+"[day]");

	if (y_obj.value > 0)
	{
		var y = y_obj.value;
	}
	else
	{
		var y = y_obj.options[y_obj.selectedIndex].value;
	}

	if (m_obj.value > 0)
	{
		var m = m_obj.value;
	}
	else
	{
		var m = m_obj.options[m_obj.selectedIndex].value;
	}

	if (d_obj.value > 0)
	{
		var d = d_obj.value;
	}
	else
	{
		var d = d_obj.options[d_obj.selectedIndex].value;
	}

	if (d=="") 
	{ 
		d=1; 
	}
	if (y=="---" || m=="---") 
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