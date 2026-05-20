<?php

class User
{

    private $db;
    private $dbHost;
    private $dbUsername;
    private $dbPassword;
    private $dbName;
    private $userTbl = 'usuarios';

    function __construct()
    {
        $this->dbHost = getenv('DB_HOST');
        $this->dbUsername = getenv('DB_USER');
        $this->dbPassword = getenv('DB_PASS');
        $this->dbName = getenv('DB_NAME');

        if (!$this->dbHost || !$this->dbUsername || !$this->dbName) {
            die("Faltan variables de entorno de base de datos.");
        }

        if (!isset($this->db)) {
            $conn = new mysqli(
                $this->dbHost,
                $this->dbUsername,
                $this->dbPassword,
                $this->dbName
            );

            if ($conn->connect_error) {
                die("Failed to connect with MySQL: " . $conn->connect_error);
            }

            $conn->set_charset("utf8");
            $this->db = $conn;
        }
    }

    function checkUser($userData = array())
    {
        if (!empty($userData)) {
            //Check whether user data already exists in database
            $prevQuery = "SELECT * FROM " . $this->userTbl . " WHERE oauth_provider = '" . $userData['oauth_provider'] . "' AND oauth_uid = '" . $userData['oauth_uid'] . "'";
            $prevResult = $this->db->query($prevQuery);
            if ($prevResult->num_rows > 0) {
                //Update user data if already exists
                $query = "UPDATE " . $this->userTbl . " SET name = '" . $userData['name'] . "', lastname = '" . $userData['lastname'] . "', mail = '" . $userData['mail'] . "',  locale = '" . $userData['locale'] . "', picture = '" . $userData['picture'] . "', modified = '" . date("Y-m-d H:i:s") . "' WHERE oauth_provider = '" . $userData['oauth_provider'] . "' AND oauth_uid = '" . $userData['oauth_uid'] . "'";
                $update = $this->db->query($query);
            } else {
                //Insert user data
                $query = "INSERT INTO " . $this->userTbl . " SET oauth_provider = '" . $userData['oauth_provider'] . "', oauth_uid = '" . $userData['oauth_uid'] . "', name = '" . $userData['name'] . "', lastname = '" . $userData['lastname'] . "', mail = '" . $userData['mail'] . "', locale = '" . $userData['locale'] . "', picture = '" . $userData['picture'] . "',  created = '" . date("Y-m-d H:i:s") . "', modified = '" . date("Y-m-d H:i:s") . "'";
                $insert = $this->db->query($query);
            }

            //Get user data from the database
            $result = $this->db->query($prevQuery);
            $userData = $result->fetch_assoc();
        }
        //Return user data
        return $userData;
    }
}
?>