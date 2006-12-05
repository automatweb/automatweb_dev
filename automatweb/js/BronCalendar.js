var arrBronsTry = Array(); // make the array for checkBrons(), if all's ok, then copy to arrBronsActive and draw
var arrBronsActive = Array(); // currently red on the screen
var bronTexts = Array();
bronTexts["BRON"] = "Broneeri";
bronTexts["FREE"] = "Vaba";
var bronErrors = Array();
bronErrors["CANT_BRON"] = "Ei saa broneerida";

/**
 * Does the bronning work
 *
 * @param strId 
 * @param intCalendarIntervall
 * @param intRoomReservationLength optional
 * @param intProduct optional
 *
 * For bronning with product all 4 params have to be set. 
 */
function doBron (strId, intCalendarIntervall, intRoomReservationLength, intProduct)
{
	if (!intRoomReservationLength)
	{
		sel = document.getElementById("room_reservation_length");
		intRoomReservationLength = sel.options[sel.selectedIndex].value*intCalendarIntervall;
	}
	
	if (intProduct)
		document.getElementById("product").value = intProduct;
	
	setBrons (strId, intCalendarIntervall, intRoomReservationLength);
}

/**
 * @param obj that element td
 * @param integer intCalendarIntervall length of one unit in millisecond
 * @return bool
 */
function doBronWithProduct (that, intRoomReservationLength, strId, intCalendarIntervall, intProduct, rid, ts, nice )
{
	//setBrons (strId, intCalendarIntervall, intRoomReservationLength);
}


function setBrons (strId, intCalendarIntervall, intRoomReservationLength)
{
	setArrBronsTry (strId, intCalendarIntervall, intRoomReservationLength);
	
	if (canBron())
	{
		drawBrons();
	}
		
}

/**
 * Draws nice colored boxed on calendar... and also sets value for the hidden formelement
 */
function drawBrons()
{
	var i;
	var strId;
	clearBrons ();
	arrBronsActive = arrBronsTry;
	
	for (i=0;i<arrBronsActive.length;i++)
	{
		strId = arrBronsActive[i];
		document.getElementById(strId).style.background = "red";
		document.getElementById(strId).parentNode.firstChild.style.background = "red";
		if (navigator.userAgent.indexOf("MSIE") > 0)
		{
			document.getElementById(strId).childNodes[0].innerHTML = bronTexts["BRON"];
			document.getElementById(strId).childNodes[1].value = 1;
		}
		else
		{
			document.getElementById(strId).childNodes[1].innerHTML = bronTexts["BRON"];
			document.getElementById(strId).childNodes[2].value = 1;
		}
	}
}

/**
 * @return bool
 * Uses global variable arrCurrentBrons to turn bronned times to not bronned
 */
function clearBrons ()
{
	if (arrBronsActive.length==0)
		return true;

	var strNextId;
	
	for (i=0;i<arrBronsActive.length;i++)
	{
		strNextId = arrBronsActive[i];
		document.getElementById(strNextId).style.background = "#e1e1e1";
		document.getElementById(strNextId).parentNode.firstChild.style.background = "#e1e1e1";
		if (navigator.userAgent.indexOf("MSIE") > 0)
		{
			document.getElementById(strNextId).childNodes[0].innerHTML = bronTexts["FREE"];
			document.getElementById(strNextId).childNodes[1].value = 0;
		}
		else
		{
			document.getElementById(strNextId).childNodes[1].innerHTML = bronTexts["FREE"];
			document.getElementById(strNextId).childNodes[2].value = 0;
		}
	}
	arrBronsActive = Array(); // reset
	return true;
}

/**
 * Checks if clicked elements exist and, if so, then if it is not bronned
 * Matches id's in  arrBronsTry to ones on page
 */
function canBron ()
{
	var i;

	for (i=0;i<arrBronsTry.length;i++)
	{
		if (isTimeBronned(arrBronsTry[i]) )
		{
			alert (bronErrors["CANT_BRON"]);
			return false;
		}
	}
	return true;
}

/**
 * @param strId fields id - td's id
 * Checks if field exists, has hidden form element (which has id)
 */
function isTimeBronned (strId)
{
	try {
		if (document.getElementById(strId).childNodes[2].id)
			return false
	}
	catch (e) {return true;}
}

/**
 * Before anything, arrBronsTry will be set with id of html elements.
 * arrBronsTry is used by canBron() to see if all indexes exist on page thus meaning if times ar 
 * availabe for bronning.
 */
function setArrBronsTry (strId, intCalendarIntervall, intRoomReservationLength)
{
	arrBronsTry = Array();
	var strNextId = strId;
	var intTS = getTSFromPrefixAndTimestamp(strId) ;
	var intRID = getRIDFromPrefixAndTimestamp (strId);
	var tmp;
	var i=1;
	while (intRoomReservationLength>0)
	{
		arrBronsTry[arrBronsTry.length] = strNextId;
		
		tmp = intCalendarIntervall+getTSFromPrefixAndTimestamp(strNextId);
		strNextId = intRID+"_"+tmp;
		intRoomReservationLength -= intCalendarIntervall;
		i++;
	}
}
 
 


function splitPrefixAndTimestamp (str)
{
	arrOutput = new Array ();
	str = str+"";
	
	intSplitI = str.indexOf("_");
	arrOutput["prefix"] = str.substring(0,intSplitI);
	arrOutput["timestamp"] = str.substring(intSplitI+1);
	return arrOutput;
}

function getTSFromPrefixAndTimestamp (str)
{
	t = new Array ();
	t = splitPrefixAndTimestamp (str);
	return t["timestamp"]*1.0;
}

function getRIDFromPrefixAndTimestamp (str)
{
	t = new Array ();
	t = splitPrefixAndTimestamp (str);
	return t["prefix"];
}