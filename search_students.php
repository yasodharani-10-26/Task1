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


/*
====================================================
GET SEARCH TEXT
====================================================
*/

$search =
    trim($_GET["name"] ?? "");


if (empty($search)) {

    echo json_encode([

        "success" => false,

        "message" =>
            "Enter a student name"

    ]);

    exit;
}


$userId =
    $_SESSION["user_id"];

$role =
    $_SESSION["role"];


/*
====================================================
SEARCH TEXT
====================================================
*/

$searchPattern =
    "%" . $search . "%";


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

        AND LOWER(name)
            LIKE LOWER(?)

        ORDER BY name

    ";


    $stmt =
        $conn->prepare($sql);


    $stmt->bind_param(

        "is",

        $userId,

        $searchPattern

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
    GET COORDINATOR BRANCH + SECTION
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
    SEARCH THEIR CLASS ONLY
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

        AND LOWER(name)
            LIKE LOWER(?)

        ORDER BY name

    ";


    $stmt =
        $conn->prepare($sql);


    $stmt->bind_param(

        "sss",

        $coordinator["branch"],

        $coordinator["section"],

        $searchPattern

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
    SEARCH OWN BRANCH
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

        AND LOWER(name)
            LIKE LOWER(?)

        ORDER BY name

    ";


    $stmt =
        $conn->prepare($sql);


    $stmt->bind_param(

        "ss",

        $hod["branch"],

        $searchPattern

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

        AND LOWER(name)
            LIKE LOWER(?)

        ORDER BY name

    ";


    $stmt =
        $conn->prepare($sql);


    $stmt->bind_param(

        "s",

        $searchPattern

    );

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
EXECUTE
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
RETURN RESULTS
====================================================
*/

echo json_encode([

    "success" => true,

    "search" =>
        $search,

    "count" =>
        count($students),

    "students" =>
        $students

]);


$stmt->close();

$conn->close();

?>
