<?php

class Jobs {
	$catid = 1;

	function __construct() {
		echo 'initialized';
	}
	
	function getJobs($catid=null) {
		if(is_null($catid)) $catid = 1;
	}
	
	function showJobs($catid=null,$limit=null) {
		$jobs = $this->getJobs($catid);
		var_dump($jobs);
	}
}

$jobbie = new Jobs();

$managers = $jobbie->showJobs(1,5);
$programmers = $jobbie->showJobs(2);

foreach($managers as $i) { echo $i; }

?>