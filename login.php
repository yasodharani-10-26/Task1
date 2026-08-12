<?php

header("Content-Type: application/json");

session_start();

require_once "db.php";


/*
====================================================
ALLOW POST ONLY
====================================================
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([

        "success" => false,

        "message" =>
            "Only POST requests are allowed"

    ]);

    exit;
}


/*
====================================================
GET LOGIN DATA
====================================================
*/

$email =
    trim($_POST["email"] ?? "");

$password =
    $_POST["password"] ?? "";


/*
====================================================
VALIDATION
====================================================
*/

if (
    empty($email) ||
    empty($password)
) {

    echo json_encode([

        "success" => false,

        "message" =>
            "Email and password are required"

    ]);

    exit;
}


/*
====================================================
GET USER
====================================================
*/

$sql = "
    SELECT
        id,
        name,
        email,
        password,
        role,
        rollNumber,
        branch,
        section,
        codingLink

    FROM users

    WHERE email = ?
";


$stmt =
    $conn->prepare($sql);


$stmt->bind_param(
    "s",
    $email
);


$stmt->execute();


$result =
    $stmt->get_result();


/*
====================================================
USER NOT FOUND
====================================================
*/

if ($result->num_rows === 0) {

    $stmt->close();

    $conn->close();

    echo json_encode([

        "success" => false,

        "message" =>
            "Invalid email or password"

    ]);

    exit;
}


/*
====================================================
GET USER DATA
====================================================
*/

$user =
    $result->fetch_assoc();


/*
====================================================
VERIFY PASSWORD
====================================================
*/

if (
    !password_verify(
        $password,
        $user["password"]
    )
) {

    $stmt->close();

    $conn->close();

    echo json_encode([

        "success" => false,

        "message" =>
            "Invalid email or password"

    ]);

    exit;
}


/*
====================================================
CREATE SESSION
====================================================
*/

$_SESSION["user_id"] =
    $user["id"];

$_SESSION["role"] =
    $user["role"];

$_SESSION["name"] =
    $user["name"];

$_SESSION["email"] =
    $user["email"];

$_SESSION["branch"] =
    $user["branch"];

$_SESSION["section"] =
    $user["section"];


/*
====================================================
REMOVE PASSWORD
====================================================
*/

unset(
    $user["password"]
);


/*
====================================================
SUCCESS
====================================================
*/

echo json_encode([

    "success" => true,

    "message" =>
        "Login successful",

    "user" =>
        $user

]);


$stmt->close();

$conn->close();

?>
