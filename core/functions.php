<?php 
	
	// Set timezone
	ini_set('date.timezone', 'Europe/London');

	// Set current date and current hour values 
	$currTime	  = date('H:i:s');
	$currTimeslot = currentTimeslot();
	$currDay      = date('l');
	$currDate     = date('Y-m-d');
	$currHour     = date('H');
	$currWeek     = intval(date('W')); 
	$currMonth    = date('F');
	$currYear     = date('Y');

	// Connect to the database
	function connection() {
		$hostname = "localhost";
		$username = "root";
		$password = "";
		$dbname   = "quietgym";
		try {
		    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
		    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    } catch (PDOException $e) {
	    	echo date('Y-m-d H:i:s') . $e->getMessage() . PHP_EOL;
	    	die();
    	}
	    return $db;
	}

	// Convert the current hour value into a timeslot of the form '06:00-07:00'
	function currentTimeslot() {
		$currHour = date('H'); // 24-hour format of an hour with leading zeros (00 through 23)
		$nextHour = $currHour + 1; // Returns the next number in integer form, with no leading zero
		$nextHour = sprintf('%02d', $nextHour); // Reattaches the leading zero for numbers below 10
		$timeslot = $currHour.":00-".$nextHour.":00"; // Returns a timeslot to which the current moment belongs (E.G. 06:00-07:00)
		return $timeslot;
	}

	// Update the column corresponding to today if the row already exists
 	function updateRow($res, $db, $currDay, $numUsers, $currTimeslot, $currWeek, $currYear) {
		$id  = $res->id;
		try {
			$stmt = $db->prepare("UPDATE timeslots SET $currDay = :numUsers WHERE id = :id");
			$stmt->bindParam(':numUsers', $numUsers);
			$stmt->bindParam(':id', $id);
			$stmt->execute();
		} catch (PDOException $e) {
	    	echo date('Y-m-d H:i:s') . ": averageValues.php (row update) Error: " . $e->getMessage() . PHP_EOL;
    		die();
		}	

		// Log successful row update
		echo "Row containing $currTimeslot, Week $currWeek, $currYear: updated numUsers for $currDay." . PHP_EOL; 
 	}

 	// Insert the row for the current timeslot if it does not yet exist
 	function createRow($currDay, $currTimeslot, $numUsers, $currWeek, $currMonth, $currYear, $db) {
	 	try {
		    $stmt = $db->prepare("INSERT INTO timeslots (timeslot, $currDay, Week, Month, Year)
									VALUES (:currTimeslot, :numUsers, :currWeek,
				 					:currMonth, :currYear)");
		    $stmt->bindParam(':currTimeslot', $currTimeslot);
		    $stmt->bindParam(':numUsers', $numUsers);
		    $stmt->bindParam(':currWeek', $currWeek);
		    $stmt->bindParam(':currMonth', $currMonth);
		    $stmt->bindParam(':currYear', $currYear);
		    $stmt->execute();

	    } catch (PDOException $e) {
	    	echo date('Y-m-d H:i:s') . ": averageValues.php (new row creation) Error: " . $e->getMessage() . PHP_EOL;
	    	die();
	    }

	    // Log successful row creation
		echo "$currDay, Week $currWeek, $currYear: Row created with timeslot $currTimeslot." . PHP_EOL; 
 	}
?>