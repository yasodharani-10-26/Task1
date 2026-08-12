<?php

header("Content-Type: application/json");

session_start();

require_once "db.php";


/*
====================================================
CHECK LOGIN
====================================================
*/

if (!isset($_SESSION["user_id"])) {

    echo json_encode([

        "success" => false,

        "message" =>
            "Please login first"

    ]);

    exit;
}


$userId =
    $_SESSION["user_id"];

$role =
    $_SESSION["role"];


/*
====================================================
PREPARE QUERY
====================================================
*/

$sql = "";

$stmt = null;


/*
====================================================
STUDENT
====================================================
*/

if ($role === "student") {

    $sql = "

        SELECT
            id,
            name,
            email,
            role,
            rollNumber,
            branch,
            section,
            codingLink

        FROM users

        WHERE id = ?

        AND role = 'student'

        ORDER BY name

    ";


    $stmt =
        $conn->prepare($sql);


    $stmt->bind_param(
        "i",
        $userId
    );

}


/*
====================================================
COORDINATOR
====================================================
*/

elseif ($role === "coordinator") {


    /*
    -----------------------------------------------
    GET COORDINATOR DETAILS
    -----------------------------------------------
    */

    $sql = "

        SELECT
            branch,
            section

        FROM users

        WHERE id = ?

        AND role = 'coordinator'

    ";


    $stmt =
        $conn->prepare($sql);


    $stmt->bind_param(
        "i",
        $userId
    );


    $stmt->execute();


    $coordinator =
        $stmt
            ->get_result()
            ->fetch_assoc();


    $stmt->close();


    if (!$coordinator) {

        echo json_encode([

            "success" => false,

            "message" =>
                "Coordinator not found"

        ]);

        exit;
    }


    /*
    -----------------------------------------------
    GET STUDENTS
    -----------------------------------------------
    */

    $sql = "

        SELECT
            id,
            name,
            email,
            role,
            rollNumber,
            branch,
            section,
            codingLink

        FROM users

        WHERE role = 'student'

        AND branch = ?

        AND LOWER(section)
            = LOWER(?)

        ORDER BY rollNumber

    ";


    $stmt =
        $conn->prepare($sql);


    $stmt->bind_param(

        "ss",

        $coordinator["branch"],

        $coordinator["section"]

    );

}


/*
====================================================
HOD
====================================================
*/

elseif ($role === "hod") {


    /*
    -----------------------------------------------
    GET HOD BRANCH
    -----------------------------------------------
    */

    $sql = "

        SELECT
            branch

        FROM users

        WHERE id = ?

        AND role = 'hod'

    ";


    $stmt =
        $conn->prepare($sql);


    $stmt->bind_param(
        "i",
        $userId
    );


    $stmt->execute();


    $hod =
        $stmt
            ->get_result()
            ->fetch_assoc();


    $stmt->close();


    if (!$hod) {

        echo json_encode([

            "success" => false,

            "message" =>
                "HOD not found"

        ]);

        exit;
    }


    /*
    -----------------------------------------------
    GET BRANCH STUDENTS
    -----------------------------------------------
    */

    $sql = "

        SELECT
            id,
            name,
            email,
            role,
            rollNumber,
            branch,
            section,
            codingLink

        FROM users

        WHERE role = 'student'

        AND branch = ?

        ORDER BY rollNumber

    ";


    $stmt =
        $conn->prepare($sql);


    $stmt->bind_param(

        "s",

        $hod["branch"]

    );

}


/*
====================================================
PRINCIPAL
====================================================
*/

elseif ($role === "principal") {

    $sql = "

        SELECT
            id,
            name,
            email,
            role,
            rollNumber,
            branch,
            section,
            codingLink

        FROM users

        WHERE role = 'student'

        ORDER BY rollNumber

    ";


    $stmt =
        $conn->prepare($sql);

}


/*
====================================================
INVALID ROLE
====================================================
*/

else {

    echo json_encode([

        "success" => false,

        "message" =>
            "Invalid role"

    ]);

    exit;
}


/*
====================================================
EXECUTE QUERY
====================================================
*/

$stmt->execute();


$result =
    $stmt->get_result();


$students = [];


while (
    $row =
        $result->fetch_assoc()
) {

    $students[] =
        $row;
}


/*
====================================================
RETURN DATA
====================================================
*/

echo json_encode([

    "success" => true,

    "count" =>
        count($students),

    "students" =>
        $students

]);


$stmt->close();

$conn->close();

?>
