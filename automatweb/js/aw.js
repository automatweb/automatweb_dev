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

