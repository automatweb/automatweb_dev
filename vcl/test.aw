<?php
session_name("test");
session_start();
?>
<html>
<head>
<title></title>
<link rel="stylesheet" href="tables.css">
</head>
<body>
<?
include("table.aw");
$baseurl = "http://anti.ccu.ut.ee/aw/vcl";
$aw_imgurl = $baseurl . "/img";
// väike tablegeni demon
$t = new aw_table(array("prefix" => "test",
		        "sortby" => $sortby,
			"lookfor" => $lookfor,
			"imgurl" => $aw_imgurl,
			"self"   => $PHP_SELF));
$t->parse_xml_def("test.xml");

// impordime andmed
$data = array("id" => 1,
              "name" => "tursk",
	      "pikkus" => 200);
$t->define_data($data);
$data = array("id" => 3,
	      "pikkus" => 400,
              "name" => "räim2");
$t->define_data($data);
$data = array("id" => 2,
	      "pikkus" => 666,
              "name" => "vobla");
$t->define_data($data);
$data = array("id" => 4,
              "pikkus" => 433,
              "name" => "kilu");
$t->define_data($data);

// anname kaasa parameetrid, kui neid oli
// ja sorteerime tabeli
$t->sort_by(array("field" => $sortby));

// väljastame sorteeritud tabeli
echo $t->draw();
?>
