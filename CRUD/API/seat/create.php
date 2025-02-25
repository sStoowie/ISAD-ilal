<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-type: application/json; charset=utf-8");
    include '../../config/db.php';
    $data = json_decode(file_get_contents("php://input"));

    if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
        echo json_encode(array("status" => "error", "message" => "Invalid request method."));
        die();
    }

    try {
        $checkSeatStmt = $conn->prepare("SELECT COUNT(*) FROM seats WHERE seat_number = ?");
        $checkSeatStmt->execute([$data->seat_number]);
        $seatExists = $checkSeatStmt->fetchColumn();

        if($seatExists) {
            echo json_encode(array("status" => "error", "message" => "Seat ID already exists."));
            die();
        }

        $checkFlightStmt = $conn->prepare("SELECT COUNT(*) FROM flight WHERE flight_id = ?");
        $checkFlightStmt->execute([$data->flight_id]);
        $flightExists = $checkFlightStmt->fetchColumn();

        if(!$flightExists) {
            echo json_encode(array("status" => "error", "message" => "Invalid Flight ID."));
            die();
        }

        $stmt = $conn->prepare("INSERT INTO seats (seat_number, flight_id, seat_status, class) VALUES (?, ?, ?, ?)");
        $stmt->bindParam(1, $data->seat_number);
        $stmt->bindParam(2, $data->flight_id);
        $stmt->bindParam(3, $data->seat_status);
        $stmt->bindParam(4, $data->class);

        if($stmt->execute()){
            echo json_encode(array("status" => "complete"));
        } else {
            echo json_encode(array("status" => "error", "message" => "Failed to add seat."));
        }

        $conn = null;

    } catch (PDOException $e) {
        echo json_encode(array("status" => "error", "message" => "Error: " . $e->getMessage()));
    }
?>
