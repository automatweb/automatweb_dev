var options = {
	script: optionsUrl,
	varname: "typed_text",
	minchars: 2,
	timeout: 10000,
	delay: 200,
	json: true,
	shownoresults: false,
	callback: getContactDetails
};
var as = new AutoSuggest('contact_entry_person_lastname_', options);
var contactDetails = new Array();

$("#contact_entry_person_lastname_").focus();

function loadContactDetails(contactDetails)
{
	if ($("input[name='contact_entry_person[id]']").length > 0)
	{
		el = $("input[name='contact_entry_person[id]']");
	}
	else
	{
		// create id hidden input
		el = document.createElement("input");
		el.type = "hidden";
		el.name = "contact_entry_person[id]";
		$("form[name=changeform]").append(el);
	}

	el.value = contactDetails.id;

	// load data to changeform
	if (typeof(contactDetails["contact_entry_person[firstname]"]["value"]) != "undefined")
	{
		document.getElementById('contact_entry_person_firstname_').value = contactDetails["contact_entry_person[firstname]"]["value"];
	}
	else
	{
		document.getElementById('contact_entry_person_firstname_').value = "";
	}

	if (typeof(contactDetails["contact_entry_person[lastname]"]["value"]) != "undefined")
	{
		document.getElementById('contact_entry_person_lastname_').value = contactDetails["contact_entry_person[lastname]"]["value"];
	}
	else
	{
		document.getElementById('contact_entry_person_lastname_').value = "";
	}

	if (typeof(contactDetails["contact_entry_person[gender]"]["value"]) != "undefined")
	{
		if (contactDetails["contact_entry_person[gender]"]["value"] == "1")
		{
			document.forms["changeform"]["contact_entry_person[gender]"][0].checked = 1;
		}
		else if (contactDetails["contact_entry_person[gender]"]["value"] == "2")
		{
			document.forms["changeform"]["contact_entry_person[gender]"][1].checked = 1;
		}
	}
	else
	{
		document.forms["changeform"]["contact_entry_person[gender]"][0].checked = 0;
		document.forms["changeform"]["contact_entry_person[gender]"][1].checked = 0;
	}

	if (typeof(contactDetails["contact_entry_person[fake_phone]"]["value"]) != "undefined")
	{
		document.getElementById('contact_entry_person_fake_phone_').value = contactDetails["contact_entry_person[fake_phone]"]["value"];
	}
	else
	{
		document.getElementById('contact_entry_person_fake_phone_').value = "";
	}

	if (typeof(contactDetails["contact_entry_person[fake_email]"]["value"]) != "undefined")
	{
		document.getElementById('contact_entry_person_fake_email_').value = contactDetails["contact_entry_person[fake_email]"]["value"];
	}
	else
	{
		document.getElementById('contact_entry_person_fake_email_').value = "";
	}

	if (typeof(contactDetails["contact_entry_person[fake_address_address]"]["value"]) != "undefined")
	{
		document.getElementById('contact_entry_person_fake_address_address_').value = contactDetails["contact_entry_person[fake_address_address]"]["value"];
	}
	else
	{
		document.getElementById('contact_entry_person_fake_address_address_').value = "";
	}

	if (typeof(contactDetails["contact_entry_person[fake_address_postal_code]"]["value"]) != "undefined")
	{
		document.getElementById('contact_entry_person_fake_address_postal_code_').value = contactDetails["contact_entry_person[fake_address_postal_code]"]["value"];
	}
	else
	{
		document.getElementById('contact_entry_person_fake_address_postal_code_').value = "";
	}

	if (typeof(contactDetails["contact_entry_person[fake_address_city_relp]"]["value"]) != "undefined")
	{
		document.getElementById('contact_entry_person_fake_address_city_relp_').value = contactDetails["contact_entry_person[fake_address_city_relp]"]["value"];
	}
	else
	{
		document.getElementById('contact_entry_person_fake_address_city_relp_').value = "";
	}

	if (typeof(contactDetails["contact_entry_person[fake_address_county_relp]"]["value"]) != "undefined")
	{
		document.getElementById('contact_entry_person_fake_address_county_relp_').value = contactDetails["contact_entry_person[fake_address_county_relp]"]["value"];
	}
	else
	{
		document.getElementById('contact_entry_person_fake_address_county_relp_').value = "";
	}

	if (typeof(contactDetails["contact_entry_person[fake_address_country_relp]"]["value"]) != "undefined")
	{
		document.getElementById('contact_entry_person_fake_address_country_relp_').value = contactDetails["contact_entry_person[fake_address_country_relp]"]["value"];
	}
	else
	{
		document.getElementById('contact_entry_person_fake_address_country_relp_').value = "";
	}
}

function getContactDetails(obj)
{
	contactDetailsUrl = contactDetailsUrl + "&contact_id=" + obj.id;
	$.getJSON(contactDetailsUrl, {}, loadContactDetails);
}
