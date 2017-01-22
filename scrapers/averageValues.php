<?php
	
	require '/../core/functions.php';

	// Connect to the database
	$db = connection();
	
	// Pull from the database the average of the four values recorded this hour
	try {
		$stmt = $db->prepare("SELECT ROUND(AVG(numUsers)) as numUsers 
							 FROM attendance WHERE date = :currDate 
							 AND HOUR(time) = :currHour");
		$stmt->bindParam(':currDate', $currDate);
		$stmt->bindParam(':currHour', $currHour);
		$stmt->execute();
		$res = $stmt->fetch(PDO::FETCH_OBJ);
		$numUsers = $res->numUsers;

		// Check if attendance figures for the current hour exist
		if (empty($numUsers) || $numUsers == null) {
			echo date('Y-m-d H:i:s') . ": averageValues.php error: No attendance data for the current hour." . PHP_EOL;
			die();
		}

	} catch (PDOException $e) {
		echo date('Y-m-d H:i:s') . ": averageValues.php (hourly average) error: " . $e->getMessage() . PHP_EOL;
		die();
	}

	// Check if this week's row for the current timeslot has already been inserted
	try {
		$stmt = $db->prepare("SELECT id FROM timeslots 
				WHERE timeslot = :currTimeslot 
				AND Week = :currWeek 
				AND Year = :currYear");
		$stmt->bindParam(':currTimeslot', $currTimeslot);
		$stmt->bindParam(':currWeek', $currWeek);
		$stmt->bindParam(':currYear', $currYear);
		$stmt->execute();
		$res = $stmt->fetch(PDO::FETCH_OBJ);

		// Check if the row representing this week's current timeslot exists 
		if ($stmt->rowCount() > 0) {
			// Update the column for today's weekday
			updateRow($res, $db, $currDay, $numUsers, $currTimeslot, $currWeek, $currYear);
		} else {
			// Create the entire row and insert today's value
			createRow($currDay, $currTimeslot, $numUsers, $currWeek, $currMonth, $currYear, $db);
		}

	} catch (PDOException $e) {
		echo date('Y-m-d H:i:s') . ": averageValues.php (row-exists check) error: " . $e->getMessage() . PHP_EOL;
		die();
	}	

?>