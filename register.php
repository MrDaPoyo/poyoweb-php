<?php
// Connect to your SQLite database
$db = new SQLite3('users.db');

    $username = $_POST["username"];
    $password = $_POST["password"];

    $check_stmt = $db->prepare("SELECT COUNT(*) AS count FROM users WHERE username = :username");
    $check_stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $check_result = $check_stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($check_result['count'] > 0) {
        // Username already exists, throw an error
        http_response_code(400); // Bad request
        echo "Error: Username already exists.";
        exit;
    }

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Get the current datetime
    $current_time = gmdate("Y-m-d H:i:s");
    $current_date = gmdate("Y-m-d");

    // Insert the data into the users table
    $stmt = $db->prepare("INSERT INTO users (username, password_hash, last_active_time, join_date) VALUES (:username, :password_hash, :last_active_time,:join_date)");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':password_hash', $password_hash, SQLITE3_TEXT);
    $stmt->bindValue(':last_active_time', $current_time, SQLITE3_TEXT);
    $stmt->bindValue(':join_date', $current_date, SQLITE3_TEXT);

    $stmt->execute();
    $userDir = "/users/" . $username;
    exec("sudo useradd $username", $output, $return_var);
    if($return_var !== 0){
        mkdir($userDir, 0777, true);
        exec("sudo chown $username:$username $userDir");
        exec("sudo chmod 700 $userDir");
    }
?>