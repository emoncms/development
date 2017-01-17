<?php

class User
{
    private $mysqli;
    
    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
    }
    
    //---------------------------------------------------------------------------------------
    // Status
    //---------------------------------------------------------------------------------------
    public function status()
    {
        if (!isset($_SESSION['userid'])) return false;
        if ($_SESSION['userid']<1) return false;
        return $_SESSION;
    }

    //---------------------------------------------------------------------------------------
    // User login
    //---------------------------------------------------------------------------------------
    public function register($username,$password)
    {
        if ($username==null) return array("success"=>false, "username address missing");
        if ($password==null) return array("success"=>false, "Password missing");
        
        // Validate username
        if (!ctype_alnum($username)) return array("success"=>false, "Username must only contain a-z and 0-9 characters");
        if (strlen($username) < 3 || strlen($username) > 30) return array("success"=>false, "Username length error");
        if (strlen($password) < 4 || strlen($password) > 250) return array("success"=>false, "Password length error");

        $stmt = $this->mysqli->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows>0) return array("success"=>false, "User already exists");

        $hash = hash('sha256', $password);
        $salt = md5(uniqid(mt_rand(), true));
        $hash = hash('sha256', $salt . $hash);
        
        // MQTT hash
        include "genhash.php";
        $mqtt_hash = create_hash($password);

        $stmt = $this->mysqli->prepare("INSERT INTO users (username, hash, salt, pw, super) VALUES (?,?,?,?,0)");
        $stmt->bind_param("ssss", $username, $hash, $salt, $mqtt_hash);
        if (!$stmt->execute()) {
            return array("success"=>false, "Error creating user");
        }

        // Make the first user an admin
        $userid = $this->mysqli->insert_id;
        // return $userid;
        
        // Set MQTT ACL's
        $topic = "user/$userid/#";
        $stmt = $this->mysqli->prepare("INSERT INTO acls (username,topic,rw) VALUES (?,?,2)");
        $stmt->bind_param("ss", $username, $topic);
        if (!$stmt->execute()) {
            return array("success"=>false, "Error setting MQTT ACL entry");
        }
        
        session_regenerate_id();
        $_SESSION['userid'] = $userid;
        $_SESSION['username'] = $username; 
        return $_SESSION;
    }
    
    //---------------------------------------------------------------------------------------
    // User login
    //---------------------------------------------------------------------------------------
    public function login($username,$password)
    {        
        if ($username==null) return array("success"=>false, "message"=>"username address missing");
        if ($password==null) return array("success"=>false, "message"=>"Password missing");
        
        if (!ctype_alnum($username)) return "Username must only contain a-z and 0-9 characters";
        if (strlen($username) < 3 || strlen($username) > 30) return "Username length error";
        if (strlen($password) < 4 || strlen($password) > 250) return "Password length error";
        
        $stmt = $this->mysqli->prepare("SELECT id,hash,salt FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows!=1) return array("success"=>false, "message"=>"User not found");
        
        $stmt->bind_result($id, $dbhash, $salt);
        $u = $stmt->fetch();
        
        $hash = hash('sha256', $salt . hash('sha256', $password));
        if ($hash!=$dbhash) return array("success"=>false, "message"=>"Invalid password");
        
        session_regenerate_id();
        $_SESSION['userid'] = $id;
        $_SESSION['username'] = $username; 
        return $_SESSION;
    }

    //---------------------------------------------------------------------------------------
    // Logout
    //---------------------------------------------------------------------------------------
    public function logout() 
    {
        session_unset();
        session_destroy();
    }
}
