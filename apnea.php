<?php

class Entry {
	private $rdi;
	private $leakage;
	private $pressure;
	private $time_slept;
	private $date;

	function __construct ($d, $m, $y, $rdi, $leakage, $pressure, $time_slept) {
		$this->date = new DateTime();
		$this->date->setDate((int)$y, (int)$m, (int)$d);
		$this->rdi = $rdi;
		$this->leakage = $leakage;
		$this->pressure = $pressure;
		$this->time_slept = $time_slept;
	}

	public function is_valid() {
		return $this->time_slept > 240;
	}

	public function __toString() {
		return $this->date->format('Y M D') . ' ' . $this->time_slept . ' ' . $this->leakage;
	}
	
}

$csv = explode("\n", file_get_contents('WM_DIARY.csv'));
$csv = array_slice($csv, 7); // skip entries without data

/*
   [1]=>
	   string(2) "31"
		   [2]=>
			   string(2) "12"
				   [3]=>
					   string(4) "2014"
						   [4]=>
							   string(3) "384"
								   [5]=>
									   string(1) "0"
										   [6]=>
											   string(3) "4,3"
												   [7]=>
													   string(3) "0,9"
														   [8]=>
															   string(3) "3,4"
																   [9]=>
																	   string(2) "12"
																		   [10]=>
																			   string(3) "0,6"
 */
$entries = array();
foreach ($csv as $entry) {
	$entry = explode(';', $entry);
	$e = new Entry($entry[1], $entry[2], $entry[3], $entry[6], $entry[10], $entry[9], $entry[4]);	
	if (!$e->is_valid()) continue;
	$entries []= $e;
	echo $e . PHP_EOL;
}

echo sizeof($entries);
