<?php
    require_once "databaseConnect.php";
    session_start();

    if(!isset($_POST['email']) || !isset($_POST['password'])){
        header("Location: ../view/index.php");
        exit();
    }
    $email = $_POST['email'];
    $password = $_POST['password'];

    $email = htmlentities($email, ENT_QUOTES, "UTF-8");

    $dbConnection = @new mysqli($host, $dbUser, $dbPassword, $dbName);

    if($dbConnection -> connect_errno != 0)
        echo "Blad polaczenia z baza danych";
    else{
        mysqli_set_charset($dbConnection, 'utf8');
        if($result = @$dbConnection -> query(sprintf("Select * from customers where email='%s'",
                                                mysqli_real_escape_string($dbConnection, $email)))){
            if($result -> num_rows == 1){
                $data = $result -> fetch_assoc();
                $result -> free_result();

                if (password_verify($password, $data['password'])){
                    $_SESSION['logged'] = true;
                    $_SESSION['id'] = $data['id'];
                    $_SESSION['firstName'] = $data['first_name'];
                    $_SESSION['lastName'] = $data['last_name'];
                    $_SESSION['wallet'] = $data['wallet'];
                    $_SESSION['rentedBikes'] = $data['rented_bikes'];

                    $result = @$dbConnection -> query("SELECT * FROM rents_history WHERE customer_id=".$_SESSION['id']."  AND return_station_id IS NULL");

                    $_SESSION['activeRents'] = array();

                    if($result -> num_rows > 0){
                        $_SESSION['activeRents'] = array();
                        while ($row = mysqli_fetch_assoc($result)) {
                            $array = array();
                            $array['rentDate'] = $row['rent_date'];
                            $array['rentId'] = $row['id'];
                            $r = @$dbConnection -> query("select address from stations where id=".$row['rent_station_id']);
                            $d = $r -> fetch_assoc();
                            $r -> free_result();
                            $array['rentStationAddress'] = $d['address'];

                            $_SESSION['activeRents'][] = $array;
                        }
                    }
                    $result -> free_result();

                    unset($_SESSION['error']);
                    header("Location: ../view/user.php");
                }
                else{
                    $_SESSION['error'] = '<span class="error">Nieprawidlowy login lub haslo</span>';
                    header("Location: ../view/index.php");
                }
            }else{
                $_SESSION['error'] = '<span class="error">Nieprawidlowy login lub haslo</span>';
                header("Location: ../view/index.php");
            }
        }
        $dbConnection -> close();
    }

