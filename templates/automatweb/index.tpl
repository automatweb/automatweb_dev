<html>
<head>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset={VAR:charset}">
<title>{VAR:title_action}{VAR:uid}@AutomatWeb</title>
<link REL="icon" HREF="{VAR:baseurl}/automatweb/images/icons/favicon.ico" TYPE="image/x-icon">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/aw.css" />


<!-- SUB: aw_styles -->
<!-- END SUB: aw_styles -->

<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/aw.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/browserdetect.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/cbobjects.js"></script>


<script type="text/javascript">
<!--
function remote(toolbar,width,height,file)
{
	self.name = "root";
	var wprops = "toolbar=" + toolbar + ",location=0,directories=0,status=0, "+
	"menubar=0,scrollbars=1,resizable=1,width=" + width + ",height=" + height;
	openwindow = window.open(file,"remote",wprops);
}

function box2(caption,url)
{
	var answer=confirm(caption)
	if (answer)
	{
		window.location=url
	}
}

function generic_loader()
{
	// don't do anything. screw you.
}

function check_generic_loader()
{
	if (generic_loader)
	{
		generic_loader();
	}
};

// -->
</script>
</head>
<body link="#0000ff" vlink="#0000ff" onLoad="create_objects(); check_generic_loader();">

<!-- SUB: YAH -->
<div class="aw04yah">{VAR:site_title}</div>
<!-- END SUB: YAH -->

{VAR:content}

</body>
</html>
