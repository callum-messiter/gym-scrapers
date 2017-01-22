<?php
	
	require  '/../resources/simple_html_dom.php';
	require '/../core/functions.php';

	// Connect to the database
	$db = connection();

	// Make HTTP request 
	$url  = 'http://loughboroughsport.com/holywell-fitness-centre/';
	$html = file_get_html($url);

	// Check that the HTTP request was successful
	if (!$html) {
    	echo date('Y-m-d H:i:s') . ": rawValues.php - HTTP request failed.". PHP_EOL;
    	die();
	} else {
		// Search the HTML document for the target element	
		$element  = 'p.users';
		$str = $html -> find($element, 0) -> innertext; // Returns "x users", where x is a +ve integer
		// Check that the element was found
		if(!$str) {
	    	echo date('Y-m-d H:i:s') . ": rawValues.php - Error finding element.". PHP_EOL;
    		die();
		}else {
			$numUsers = preg_replace('/[^0-9]/', '', $str); // Returns the +ve integer "x" 
		}
	}

	// Insert attendance value into the database
	try {
	    $stmt = $db->prepare("INSERT INTO attendance (numUsers, date, time) 
		         				VALUES (:numUsers, :currDate, :currTime)");
	    $stmt->bindParam(':numUsers', $numUsers);
	    $stmt->bindParam(':currDate', $currDate);
	    $stmt->bindParam(':currTime', $currTime);
	    $stmt->execute();

    } catch(PDOException $e) {
    	echo date('Y-m-d H:i:s') . ": rawValues.php Error: " . $e->getMessage() . PHP_EOL;
    	die();
    }

    // Log successful insertion 
	echo date('Y-m-d H:i:s') . ": New raw value inserted into 'attendance' " . PHP_EOL;

?>