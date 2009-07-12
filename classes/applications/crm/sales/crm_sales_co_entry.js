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
var as = new AutoSuggest('contact_entry_co_name_', options);
var contactDetails = new Array();

$("#contact_entry_co_name_").focus();

function loadContactDetails(contactDetails)
{
	if ($("input[name='contact_entry_co[id]']").length > 0)
	{
		el = $("input[name='contact_entry_co[id]']");
	}
	else
	{
		// create id hidden input
		el = document.createElement("input");
		el.type = "hidden";
		el.name = "contact_entry_co[id]";
		$("form[name=changeform]").append(el);
	}

	el.value = contactDetails.id;

	// load data to changeform
	if (typeof(contactDetails["contact_entry_co[fake_phone]"]["value"]) != "undefined")
	{
		document.getElementById('contact_entry_co_fake_phone_').value = contactDetails["contact_entry_co[fake_phone]"]["value"];
	}
	else
	{
		document.getElementById('contact_entry_co_fake_phone_').value = "";
	}

	if (typeof(contactDetails["contact_entry_co[fake_mobile]"]["value"]) != "undefined")
	{
		document.getElementById('contact_entry_co_fake_mobile_').value = contactDetails["contact_entry_co[fake_mobile]"]["value"];
	}
	else
	{
		document.getElementById('contact_entry_co_fake_mobile_').value = "";
	}

	if (typeof(contactDetails["contact_entry_co[fake_email]"]["value"]) != "undefined")
	{
		document.getElementById('contact_entry_co_fake_email_').value = contactDetails["contact_entry_co[fake_email]"]["value"];
	}
	else
	{
		document.getElementById('contact_entry_co_fake_email_').value = "";
	}

	if (typeof(contactDetails["contact_entry_co[fake_address_address]"]["value"]) != "undefined")
	{
		document.getElementById('contact_entry_co_fake_address_address_').value = contactDetails["contact_entry_co[fake_address_address]"]["value"];
	}
	else
	{
		document.getElementById('contact_entry_co_fake_address_address_').value = "";
	}

	if (typeof(contactDetails["contact_entry_co[fake_address_postal_code]"]["value"]) != "undefined")
	{
		document.getElementById('contact_entry_co_fake_address_postal_code_').value = contactDetails["contact_entry_co[fake_address_postal_code]"]["value"];
	}
	else
	{
		document.getElementById('contact_entry_co_fake_address_postal_code_').value = "";
	}

	if (typeof(contactDetails["contact_entry_co[fake_address_city]"]["value"]) != "undefined")
	{
		document.getElementById('contact_entry_co_fake_address_city_').value = contactDetails["contact_entry_co[fake_address_city]"]["value"];
	}
	else
	{
		document.getElementById('contact_entry_co_fake_address_city_').value = "";
	}

	if (typeof(contactDetails["contact_entry_co[fake_address_county]"]["value"]) != "undefined")
	{
		document.getElementById('contact_entry_co_fake_address_county_').value = contactDetails["contact_entry_co[fake_address_county]"]["value"];
	}
	else
	{
		document.getElementById('contact_entry_co_fake_address_county_').value = "";
	}

	if (typeof(contactDetails["contact_entry_co[fake_address_country_relp]"]["value"]) != "undefined")
	{
		document.getElementById('contact_entry_co_fake_address_country_relp_').value = contactDetails["contact_entry_co[fake_address_country_relp]"]["value"];
	}
	else
	{
		document.getElementById('contact_entry_co_fake_address_country_relp_').value = "";
	}
}

function getContactDetails(obj)
{
	contactDetailsUrl = contactDetailsUrl + "&contact_id=" + obj.id;
	$.getJSON(contactDetailsUrl, {}, loadContactDetails);
}
