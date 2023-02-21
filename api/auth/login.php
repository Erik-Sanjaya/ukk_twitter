<?php session_start();

require "../../db.php";
$body = json_decode(file_get_contents("php://input"));

if(empty($body->email) || empty($body->password)) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['status' => 400, 'message' => 'Missing field.']);
    return;
}

$email = $body->email;
$password = $body->password;

$query_user = mysqli_query($database_connection, "SELECT * FROM users WHERE email = '$email' LIMIT 1");
$result_user = mysqli_fetch_assoc($query_user);

if(empty($result_user)) {
    header("HTTP/1.1 404 Not Found");
    echo json_encode(['status' => 404, 'message' => 'Account not found.']);
    return;
}

if(!password_verify($password, $result_user['password'])) {
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode(['status' => 401, 'message' => 'Incorrect password.']);
    return;
}

$_SESSION['email'] = $email;

header("HTTP/1.1 200 OK");
echo json_encode(['status' => 200, 'message' => 'Logged in', 'user_id' => $result_user['id']]);
return;