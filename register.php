<?php

header("Content-Type: application/json");

require_once "db.php";


/*
====================================================
ALLOW POST ONLY
====================================================
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Only POST requests are allowed"
    ]);

    exit;
}


/*
====================================================
GET FORM DATA
====================================================
*/

$name = trim($_POST["name"] ?? "");

$email = trim($_POST["email"] ?? "");

$password = $_POST["password"] ?? "";

$role = strtolower(
    trim($_POST["role"] ?? "")
);

$rollNumber =
    trim($_POST["rollNumber"] ?? "");

$branch =
    trim($_POST["branch"] ?? "");

$section =
    trim($_POST["section"] ?? "");

$codingLink =
    trim($_POST["codingLink"] ?? "");


/*
====================================================
VALIDATION
====================================================
*/

if (
    empty($name) ||
    empty($email) ||
    empty($password) ||
    empty($role)
) {

    echo json_encode([

        "success" => false,

        "message" =>
            "Name, email, password and role are required"

    ]);

    exit;
}


/*
====================================================
VALIDATE EMAIL
====================================================
*/

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    echo json_encode([

        "success" => false,

        "message" =>
            "Invalid email address"

    ]);

    exit;
}


/*
====================================================
VALIDATE ROLE
====================================================
*/

$allowedRoles = [

    "student",
    "coordinator",
    "hod",
    "principal"

];


if (!in_array($role, $allowedRoles)) {

    echo json_encode([

        "success" => false,

        "message" =>
            "Invalid role"

    ]);

    exit;
}


/*
====================================================
CHECK EXISTING EMAIL
====================================================
*/

$sql = "
    SELECT id
    FROM users
    WHERE email = ?
";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "s",
    $email
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows > 0) {

    $stmt->close();

    echo json_encode([

        "success" => false,

        "message" =>
            "Email already registered"

    ]);

    exit;
}

$stmt->close();


/*
====================================================
HASH PASSWORD
====================================================
*/

$hashedPassword =
    password_hash(
        $password,
        PASSWORD_DEFAULT
    );


/*
====================================================
INSERT USER
====================================================
*/

$sql = "
    INSERT INTO users
    (
        name,
        email,
        password,
        role,
        rollNumber,
        branch,
        section,
        codingLink
    )

    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?
    )
";


$stmt = $conn->prepare($sql);


$stmt->bind_param(

    "ssssssss",

    $name,
    $email,
    $hashedPassword,
    $role,
    $rollNumber,
    $branch,
    $section,
    $codingLink

);


/*
====================================================
EXECUTE
====================================================
*/

if ($stmt->execute()) {

    $userId =
        $stmt->insert_id;


    echo json_encode([

        "success" => true,

        "message" =>
            "Registration successful",

        "userId" =>
            $userId

    ]);

} else {

    echo json_encode([

        "success" => false,

        "message" =>
            "Registration failed"

    ]);

}


$stmt->close();

$conn->close();

?>
