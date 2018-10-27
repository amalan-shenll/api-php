<?php
  header('Content-Type: application/json');
  $json_str = file_get_contents('php://input');
  $json_obj = json_decode($json_str);
  $email = $json_obj->email;
  $password = $json_obj->password;
  $response->status = "SUCCESS";
  if (empty($email)) {
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
      $sql = "SELECT password FROM users where email = '" . $email ."'";
      $q = $pdo->prepare($sql);
      if($q->execute()) {
        $num = $q->rowCount();
        if($num > 0) {
          $row = $q->fetch(PDO::FETCH_ASSOC);
          $db_password = $row['password'];
          if(strcmp($db_password, $password) == 0) {
            $response->status = "SUCCESS";
            $response->message = "AUTHENTICATION_SUCESS";
            $authtoken = bin2hex(random_bytes(64));
            $ssn = "INSERT INTO sessions (email, authtoken, timestamp) values(?, ?, ?) ON DUPLICATE KEY UPDATE authtoken='" .$authtoken ."', timestamp='".time()."'";
            $q = $pdo->prepare($ssn);
            $qry = $q->execute(array($email, $authtoken, time()));
            if($qry) {
              $response->authtoken = $authtoken;
              echo json_encode($response);
            } else {
              $response->status = "ERROR";
              $response->message = "ERROR_CREATING_SESSION";
              echo json_encode($response);
            }
          } else {
            $response->status = "ERROR";
            $response->message = "INVALID_PASSWORD";
            echo json_encode($response);
          }
        } else {
          $response->status = "ERROR";
          $response->message = "EMAIL_NOT_FOUND";
          echo json_encode($response);
        }
      } else {
        $response->status = "ERROR";
        $response->message = "SERVER_ERROR";
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