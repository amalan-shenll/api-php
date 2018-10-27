<?php
  header('Content-Type: application/json');
  $json_str = file_get_contents('php://input');
  $json_obj = json_decode($json_str);
  $firstname = $json_obj->firstname;
  $lastname = $json_obj->lastname;
  $email = $json_obj->email;
  $password = $json_obj->password;
  $response->status = "SUCCESS";
  if (empty($firstname)) {
    $response->message = "EMPTY_FIRSTNAME";
    echo json_encode($response);
  } elseif (empty($lastname)) {
    $response->message = "EMPTY_LASTNAME";
    echo json_encode($response);
  } elseif (empty($email)) {
    $response->message = "EMPTY_EMAIL";
    echo json_encode($response);
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response->message = "INVALID_EMAIL";
    echo json_encode($response);
  } elseif (empty($password)) {
    $response->message = "EMPTY_PASSWORD";
    echo json_encode($response);
  } else {
    try {
      include("db_config.php");
      $pdo = Database::connect();
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = "INSERT INTO users (firstname, lastname, email, password) values(?, ?, ?, ?)";
      $q = $pdo->prepare($sql);
      $querystat = $q->execute(array($firstname, $lastname, $email,$password));
      if($querystat) {
        $response->status = "SUCCESS";
        $response->message = "USER_CREATED";
        $user_data->firstname = $firstname;
        $user_data->lastname = $lastname;
        $user_data->email = $email;
        $response->user_data = $user_data;   
        echo json_encode($response);
      } else {
        $response->status = "ERROR";
        $response->message = "USER_CREATION_FAILED";
        echo json_encode($response);
      }
      Database::disconnect();
    } catch(PDOException $e) {
      $response->status = "ERROR";
      $response->message = $e->getMessage();
      echo json_encode($response);
    }
  }
?>