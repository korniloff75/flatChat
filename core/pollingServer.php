<?php
// set php runtime to unlimited
set_time_limit(0);

// where does the data come from ? In real world this would be a SQL query or something
$data_source_file = 'data.txt';

// main loop
while (true) {

	// if ajax request has send a timestamp, then $last_ajax_call = timestamp, else $last_ajax_call = null
	$last_ajax_call = isset($_GET['timestamp']) ? (int)$_GET['timestamp'] : null;

	// PHP caches file data, like requesting the size of a file, by default. clearstatcache() clears that cache
	clearstatcache();
	// get timestamp of when file has been changed the last time
	$last_change_in_data_file = filemtime($data_source_file);

	// if no timestamp delivered via ajax or data.txt has been changed SINCE last ajax timestamp
	if ($last_ajax_call == null || $last_change_in_data_file > $last_ajax_call) {

		// get content of data.txt
		$data = file_get_contents($data_source_file);

		// put data.txt's content and timestamp of last data.txt change into array
		$result = [
			'data' => $data,
			'timestamp' => $last_change_in_data_file
		];

		// encode to JSON, render the result (for AJAX)
		$json = json_encode($result, JSON_UNESCAPED_UNICODE);
		echo $json;

		// leave this loop step
		break;

	} else {
		// wait for 1 sec (not very sexy as this blocks the PHP/Apache process, but that's how it goes)
		sleep( 1 );
		continue;
	}
}