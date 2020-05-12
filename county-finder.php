<?php

include "./point-to-polygon.php";

class county_finder
{
	
	function __construct() {
	}

	//puts data for the northernmost, southernmost, easternmost, and westermost points of every county into an array from CSV
	public function extremes_array($csv = 'extremes.csv') {
		$file = file_get_contents($csv);
		$extremes = str_getcsv($file, $delimiter = "\n");
		foreach($extremes as &$row) {
			$row = str_getcsv($row, ",");
			foreach($row as $key => &$new) {
				if($key != 0) {
					$new = str_getcsv($new, " ");
				}
			}
		}
		return $extremes;
	}

	//outputs an array of a few counties that could be a match for the target coordinate
	public function narrow_down($loc) {
		//print_r($loc);
		$long = $loc[0];
		$lat = $loc[1];
		$ex = $this->extremes_array();

		//remove all counties north of target
		foreach ($ex as $key => $value) {
			if($lat <= $value[2][1]) {
				unset($ex[$key]);
			}
		}
		//remove all counties south of target
		foreach ($ex as $key => $value) {
			if($lat >= $value[1][1]) {
				unset($ex[$key]);
			}
		}
		//remove all counties east of target
		foreach ($ex as $key => $value) {
			if($long <= $value[4][0]) {
				unset($ex[$key]);
			}
		}
		//remove all counties west of target
		foreach ($ex as $key => $value) {
			if($long >= $value[3][0]) {
				unset($ex[$key]);
			}
		}

		$narrowed = array();
		
		foreach($ex as $value) {
			$narrowed[] = $value[0];
		}

		return $narrowed;
	}

	//outputs the name, state, and FIPS codes in an array when given a FIPS code as a string, e.g. "22.033", "[STATE FIPS].[COUNTY FIPS]"
	public function county_info($county, $csv = 'county-fips.csv') {
		$fips = explode(".", $county);
		//print_r($fips);
		$file = file_get_contents($csv);
		$counties = str_getcsv($file, $delimiter = "\n");
		foreach($counties as &$row) $row = str_getcsv($row, ",");
		//print_r($counties);
		foreach ($counties as $key => $value) {
			if($value[2] == $fips[1] AND $value[3] == $fips[0]) {
				$new = array('county' => $value[0], 'state' => $value[1], 'county_fips' => $value[2], 'state_fips' => $value[3], 'fips' => $value[3].$value[2]);
				return $new;
			}
		}
		return FALSE;
	}

	//when given a coordinate will outpute an array containing what county the coordinate is in; if the coordinate is not in any county, it will return false
	public function find_county($loc) {
		$pointLocation = new pointLocation();

		$narrowed = $this->narrow_down($loc);
		//print_r($narrowed);
		$target = $loc[0]." ".$loc[1];

		foreach($narrowed as $county) {
			$filename = './county-files/'.$county.".csv";
			$boundaries = file($filename, FILE_IGNORE_NEW_LINES);
			if($pointLocation->pointInPolygon($target, $boundaries) == 1) {
				return $this->county_info($county);
			} 
		}
		if(!empty($narrowed)) {
			return $narrowed;
		} 
		return FALSE;
	}

	

}

?>