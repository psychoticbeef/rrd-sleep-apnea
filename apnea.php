<?php

class Entry {
	private $rdi;
	private $leakage;
	private $pressure;
	public $time_slept;
	public $date;

	function __construct ($d, $m, $y, $rdi, $leakage, $pressure, $time_slept) {
		$this->date = new DateTime();
		$this->date->setDate((int)$y, (int)$m, (int)$d);
		$this->date->setTime(0, 0, 0);
		$this->rdi = trim(str_replace(',', '.', $rdi));
		$this->leakage = trim(str_replace(',', '.', $leakage));
		$this->pressure = trim(str_replace(',', '.', $pressure));
		$this->time_slept = trim($time_slept);
	}

	public function is_valid() {
		return $this->time_slept > 240;
	}

	public function getTimestamp() {
		return $this->date->getTimestamp();
	}

	/*
'DS:time_slept:GAUGE:86400:0:1440' \
	'DS:rdi_hour:GAUGE:86400:0:100' \
		'DS:p90_hpa:GAUGE:86400:0:20' \
			'DS:leakage_percent:GAUGE:86400:0:100' \
	 */

	public function __toString() {
		return implode(':', array($this->date->getTimestamp(), $this->time_slept, $this->rdi, $this->pressure, $this->leakage));
	}
	
}

$csv = explode("\n", file_get_contents('WM_DIARY.CSV'));
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
	//echo $entry . PHP_EOL;
	$entry = explode(';', $entry);
	if (sizeof($entry) === 1) continue; 
	$e = new Entry($entry[1], $entry[2], $entry[3], $entry[6], $entry[10], $entry[9], $entry[4]);	
	if (!$e->is_valid()) continue;
	$entries []= $e;
	//echo $e . PHP_EOL;
}

usort($entries, function($a, $b) {
	return $a->getTimestamp() > $b->getTimestamp();
});

$m_total = 0;
$m_cnt = 0;
$m_cur = 0;
foreach ($entries as $e) {
	$m = $e->date->format('m');
	if ($m_cur != $m) {
		if ($m_cnt > 1) {
			echo $m_cur . ': ' . round($m_total/$m_cnt) . PHP_EOL;
		}
		$m_cur = $m;
		$m_total = 0;
		$m_cnt = 0;
	}
	if ($e->time_slept < 300 || $e->time_slept > 600) continue;
	$m_cnt++;
	$m_total += $e->time_slept;
}
			echo $m_cur . ': ' . round($m_total/$m_cnt) . PHP_EOL;

exit;

foreach ($entries as $e) {
	echo 'rrdtool update apnea.rrd ' . $e . PHP_EOL;
	shell_exec('rrdtool update apnea.rrd ' . $e);
}
echo sizeof($entries);
