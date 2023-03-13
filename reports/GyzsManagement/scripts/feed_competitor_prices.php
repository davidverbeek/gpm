<?php
//to read xml file ...have you done before..yes in ecp project
$path = "abc.xml";
   $xmlfile = file_get_contents($path);
   $new = simplexml_load_string($xmlfile);
   $jsonfile = json_encode($new);
   $myarray = json_decode($jsonfile, true);
   print_r($myarray);