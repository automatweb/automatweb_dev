var intCurrentBron=false;
var intCurrentBronLength=false;
var intRoomReservationLength;
var dontExecutedoBron = 0;
var bronErrors = Array();
bronErrors["CANT_BRON"] = "Ei saa broneerida";

function doBron (that, intCalendarIntervall)
{
	var strNextId;
	if (dontExecutedoBron == 0)
	{
		sel = document.getElementById("room_reservation_length");
	
		if (!intRoomReservationLength && sel)
		{
			intRoomReservationLength = sel.options[sel.selectedIndex].value*intCalendarIntervall;
		}
		
		if (intCurrentBron)
		{
			if ( checkBrons (intCurrentBron, intRoomReservationLength , intCalendarIntervall) )
			{
				if (intCurrentBron)
					clearCurrentBron (intCurrentBron, intCurrentBronLength, intCalendarIntervall);
			}
			intCurrentBron=false;
		}
		
		if (sel)
		{
			intCurrentBronLength = intRoomReservationLength = sel.options[sel.selectedIndex].value*intCalendarIntervall;
		}
		
		if (intCurrentBron==false)
		{
			strNextId = intCurrentBron = that.id*1.0;
			

			
			// kas saab broneerida?
			if ( checkBrons (intCurrentBron, intRoomReservationLength , intCalendarIntervall) )
			{
				i=1;
				while (intRoomReservationLength>0)
				{
							
					document.getElementById(strNextId).style.background = "red";
					document.getElementById(strNextId).parentNode.firstChild.style.background = "red";
					if (navigator.userAgent.indexOf("MSIE") > 0)
					{
						document.getElementById(strNextId).childNodes[0].innerHTML = "Broneeri";
						//document.getElementById(strNextId).childNodes[1].value = 1;
					}
					else
					{
						document.getElementById(strNextId).childNodes[1].innerHTML = "Broneeri";
						document.getElementById(strNextId).childNodes[2].value = 1;
					}
				
					strNextId = intCurrentBron+(i*intCalendarIntervall);
					intRoomReservationLength -= intCalendarIntervall;
					i++;
				}
			}
			else
			{
				alert (bronErrors["CANT_BRON"]);
			}
		}
	}
	dontExecutedoBron = 0;
}


function clearCurrentBron (intCurrentBron, intCurrentBronLength, intCalendarIntervall )
{
		intTimestampNext = intCurrentBron;
		while (intCurrentBronLength>0)
		{
			document.getElementById(intTimestampNext).style.background = "#e1e1e1";
			document.getElementById(intTimestampNext).parentNode.firstChild.style.background = "#e1e1e1";
			if (navigator.userAgent.indexOf("MSIE") > 0)
			{
				document.getElementById(intTimestampNext).childNodes[0].innerHTML = "Vaba";
				document.getElementById(intTimestampNext).childNodes[1].value = 0;
			}
			else
			{
				document.getElementById(intTimestampNext).childNodes[1].innerHTML = "Vaba";
				document.getElementById(intTimestampNext).childNodes[2].value = 0;
			}

			intTimestampNext += intCalendarIntervall;
			intCurrentBronLength -= intCalendarIntervall;
		}
		

		
	intCurrentBron=false;
}

/**
 * Basically as doBron but ment for activating from any link. doBron on the otherhand needs to get this as a parameter
 */
function doBronWithProduct (that, intRoomReservationLength, intTimestamp, intCalendarIntervall, intProduct )
{
	dontExecutedoBron = 1;
	if (checkBrons(intTimestamp, intRoomReservationLength, intCalendarIntervall ))
	{
		document.getElementById("product").value = intProduct;
	
		document.getElementById(intTimestamp).style.background = "red";
		
		if (intCurrentBron)
			clearCurrentBron (intCurrentBron, intCurrentBronLength, intCalendarIntervall);
		
		intCurrentBron = intTimestampNext= intTimestamp;
		intCurrentBronLength = intRoomReservationLength;
		
		while (intRoomReservationLength>0)
		{
			document.getElementById(intTimestampNext).style.background = "red";
			document.getElementById(intTimestampNext).parentNode.firstChild.style.background = "red";
			if (navigator.userAgent.indexOf("MSIE") > 0)
			{
				document.getElementById(intTimestampNext).childNodes[0].innerHTML = "Broneeri";
				document.getElementById(intTimestampNext).childNodes[1].value = 1;
			}
			else
			{
				document.getElementById(intTimestampNext).childNodes[1].innerHTML = "Broneeri";
				document.getElementById(intTimestampNext).childNodes[2].value = 1;
			}

			intTimestampNext = intTimestampNext + intCalendarIntervall;
			intRoomReservationLength -= intCalendarIntervall;
		}
		return true;
	}
	else
	{
		alert (bronErrors["CANT_BRON"]);
		return false;
	}
	return true;
}

/** 
 * Checks if bron can be made
 *
 *
 */
function checkBrons (intCurrBronn, intRoomReservationL, intCalendarIntervall)
{
	var strNextId = intCurrBronn*1.0;
	var i=1;
	while (intRoomReservationL>0)
	{
		if (!document.getElementById(strNextId).childNodes[2])
		{
			return false;
		}
		strNextId = (intCurrBronn*1.0)+(i*intCalendarIntervall);
		intRoomReservationL -= intCalendarIntervall;
		i++;
	}
	return true;
}