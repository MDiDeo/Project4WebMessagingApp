<?php
    session_start();

    $firstName = $lastName = $uName = $sender = $recipient = $tName = $email = $pass1 = $pass2 = $semester = $role = "";
    $year = 0;
    $errors = array();
    // connect to the database
    $db = mysqli_connect('localhost', 'root', '', 'registration');
    $message_db = mysqli_connect('localhost', 'root', '', 'webmessageapp');

    // if register button is clicked
    if (isset($_POST['register'])) {
        $firstName = mysqli_real_escape_string($db, $_POST['firstName']);
        $lastName = mysqli_real_escape_string($db, $_POST['lastName']);
        $uName = mysqli_real_escape_string($db, $_POST['uName']);
        $email = mysqli_real_escape_string($db, $_POST['email']);
        $pass1 = mysqli_real_escape_string($db, $_POST['pass1']);
        $pass2 = mysqli_real_escape_string($db, $_POST['pass2']);
        $role = mysqli_real_escape_string($db, $_POST['role']);

        // ensure that form fields are filled properly
        if (empty($uName)) {
            array_push($errors, "Username is required");
        }

        if (preg_match('/[^a-z_\-0-9]/i', $uName)) {
            array_push($errors, "Username must contain only letters and numbers");
        }

        if (empty($email)) {
            array_push($errors, "Email is required");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            array_push($errors, "Invalid email address");
        }
        if (empty($pass1)) {
            array_push($errors, "Password is required");
        }

        if (empty($role)) {
            array_push($errors, "Must select a role");
        }

        if ($pass1 != $pass2) {
            array_push($errors, "The two passwords do not match");
        }
        
        // If there are no errors, save user to database
        if (count($errors) == 0) {
            $password = md5($pass1);
            $sql = "INSERT INTO users (uName, email, pass, role, firstName, lastName) VALUES ('$uName', '$email', '$password', '$role', '$firstName', '$lastName')";
            mysqli_query($db, $sql);

            $_SESSION['firstName'] = $firstName;
            $_SESSION['lastName'] = $lastName;
            $_SESSION['uName'] = $uName;
            $_SESSION['role'] = $role;
            $_SESSION['success'] = "You are now logged in";
            header('location: index.php');
            
        }
    }
    //log user in from login page
    if (isset($_POST['login'])) {
        $uName = mysqli_real_escape_string($db, $_POST['uName']);
        $pass1 = mysqli_real_escape_string($db, $_POST['pass1']);

        // ensure that form fields are filled properly
        if (empty($uName)) {
            array_push($errors, "Username is required");
        }
        if (empty($pass1)) {
            array_push($errors, "Password is required");
        }      

        if (count($errors) == 0) {
            $pass1 = md5($pass1);
            $query = "SELECT * FROM users WHERE uName= '$uName' AND pass= '$pass1'";
            $res = mysqli_query($db, $query) or die(mysqli_error($db));

            if (mysqli_num_rows($res) == 1) {
            //log user in
                $row = mysqli_fetch_array($res);
                $role = $row['role'];
                $firstName = $row['firstName'];
                $lastName = $row['lastName'];
                $_SESSION['uName'] = $uName;
                $_SESSION['firstName'] = $firstName;
                $_SESSION['lastName'] = $lastName;
                $_SESSION['role'] = $role;
                $_SESSION['success'] = "You are now logged in ";
                header('location: index.php');
    
            } else {
                array_push($errors, "The username or password is incorrect.");
            }
        }
    }

    if (isset($_POST['send_message'])) {
        $recipient = mysqli_real_escape_string($message_db, $_POST['recipient']);
        $sender = $_SESSION['uName'];
        $message = mysqli_real_escape_string($message_db, $_POST['message']);

        $find_recipient = mysqli_query($db, "SELECT uName FROM users WHERE uName = '$recipient'");
        $find_sender = mysqli_query($db, "SELECT uName FROM users WHERE uName = '$sender'");

        if (empty($recipient)) {
            array_push($errors, "Enter the recipient's username");
        }

        if ($find_recipient == 0) {
            array_push($errors, "Must send to a valid recipient. Please enter their username.");
        }

        if ($find_sender == 0) {
            array_push($errors, "Incorrect username");
        }

        if (empty($message)) {
            array_push($errors, "Username is required");
        }

        if (count($errors) == 0) {
            $sql = "INSERT INTO mailbox (message, sender, recipient) VALUES ('$message', '$sender', '$recipient')";
            mysqli_query($message_db, $sql);
            $_SESSION['sent'] = "Your message has been sent.";
            
            header('location: inbox.php');
        }

    }

    //logout
    if (isset($_GET['logout'])) {
        session_unset();
        session_destroy();
        header("location: login.php");
        exit();
    }

?>