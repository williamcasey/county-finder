<?php

ini_set('memory_limit', '1024M');

$csv = array_map('str_getcsv', file('county-boundaries-raw.csv')); 

//print_r($csv);

$new_file = "";
$county = "01.001";
$pre_county = "01.001";

$total = 0;

foreach ($csv as $line) {
	if($line[2] == $county) {
		$new_file .= $line[0]." ".$line[1]."\n";
	} else {
		$file = "./county-files/".$county.".csv";
		file_put_contents($file, $new_file);
		echo $county."\n";
		$county = $line[2];
		$new_file = $line[0]." ".$line[1]."\n";
		$total = $total + 1;
	}

}

$file = "./county-files/".$county.".csv";
file_put_contents($file, $new_file);

echo "\n ".($total + 1)." files were created.\n";

//echo $new_file;


?>