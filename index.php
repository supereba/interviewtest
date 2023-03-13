<?php
    // send default headers
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    
    // DB access
    $servername = 'localhost';
    $username = 'eba';
    $db = 'eonix';
    $password = '';

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$db", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } 
    catch (PDOException $e) {
  
        http_response_code(500);
        $message[] = [$sql, $e->getMessage()];
        echo json_encode($message);
        exit;
    } 
    
    // ***
    
    /**
     * 
     * @global type $conn
     * @param int $userID
     * @return array
     */
    function deleteUser(int $userID) : array {
      
        global $conn;

        try {
            $sql = "DELETE FROM eonix.personnes where id=$userID";
            $conn->exec($sql);

            $response['status_code_header'] = 200;
            $response['body'] = json_encode($result);
        }
        catch (PDOException $e) {
            $response['status_code_header'] = 500;
            $message[] = [$sql, $e->getMessage()];
            $response['body'] = json_encode($message);
        } 
        
        return $response;
    }
    
    /**
     * 
     * @global type $conn
     * @param int $userId
     * @return array
     */
    function fetchUser(int $userId) : array {
        
        global $conn;

        $sql = "SELECT * FROM eonix.personnes where id='$userId'";
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetchAll();

            $response['status_code_header'] = 200;
            $response['body'] = json_encode($result);
        }
        catch (PDOException $e) {
            $response['status_code_header'] = 500;
            $message[] = [$sql, $e->getMessage()];
            $response['body'] = json_encode($message);
        } 
        
        return $response;
    }
    
    /**
     * 
     * @param string $nom
     * @param string $prenom
     * @return array
     */
    function findUsers(string $nom, string $prenom) : array {
        global $conn;

        $nomIsset = isset($nom) && (strlen($nom));
        $prenomIsset = isset($prenom) && (strlen($prenom));

        if ($nomIsset && $prenomIsset) {
            $sql = "SELECT * FROM eonix.personnes where nom like '%$nom%' and prenom like '%$prenom%'";
        }
        else if ($nomIsset) {
            $sql = "SELECT * FROM eonix.personnes where nom like '%$nom%'";
        }
        else if ($prenomIsset) {
            $sql = "SELECT * FROM eonix.personnes where prenom like '%$prenom%'";
        }
        else {
            return fetchAllUsers();
        }
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetchAll();
            
            $response['status_code_header'] = 200;
            $response['body'] = json_encode($result);
        } 
        catch (PDOException $e) {
            $response['status_code_header'] = 500;
            $message[] = [$sql, $e->getMessage()];
            $response['body'] = json_encode($message);
        } 
        return $response;        
    }
    
    /**
     * 
     * @global type $conn
     * @return array
     */
    function fetchAllUsers() : array {
        
        global $conn;

        $sql = 'SELECT * FROM eonix.personnes';
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetchAll();

            $response['status_code_header'] = 200;
            $response['body'] = json_encode($result);
        }
        catch (PDOException $e) {
            $response['status_code_header'] = 500;
            $message[] = [$sql, $e->getMessage()];
            $response['body'] = json_encode($message);
        } 
        
        return $response;
    }
    
    /**
     * 
     * @global type $conn
     * @param object $userData
     * @return array
     */
    function updateUser(object $userData) : array {
        
        global $conn;

        $id = $userData->id;
        $nom = $userData->nom;
        $prenom = $userData->prenom;
        $sql = "UPDATE eonix.personnes SET nom='$nom', prenom='$prenom' where id='$id'";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute();   

            $response['status_code_header'] = 201;
            $message[] = [$stmt->rowCount() . " records UPDATED successfully"];
            $response['body'] = json_encode($message);
        }
        catch (PDOException $e) {
            $response['status_code_header'] = 500;
            $message[] = [$sql, $e->getMessage()];
            $response['body'] = json_encode($message);
        } 

        return $response; 
    }
    
    /**
     * 
     * @global type $conn
     * @param object $userData
     * @return array
     */
    function createUser(object $userData) : array {

        global $conn;
        
        $nom = $userData->nom;
        $prenom = $userData->prenom;
        $sql = "INSERT INTO eonix.personnes (nom, prenom) values ('$nom', '$prenom')";
        try {
            $conn->exec($sql);
            $response['status_code_header'] = 201;
        }
        catch (PDOException $e) {
            $response['status_code_header'] = 500;
            $message[] = [$sql, $e->getMessage()];
            $response['body'] = json_encode($message);
        } 
        
        return $response;        
    }

    // get data
    $params = explode('/', filter_input(INPUT_SERVER, 'PATH_INFO', FILTER_SANITIZE_STRING));
    $command = isset($params[1]) ? $params[1] : null;
    $request = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING);
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    $nom = filter_input(INPUT_GET, 'nom', FILTER_SANITIZE_STRING); 
    $prenom = filter_input(INPUT_GET, 'prenom', FILTER_SANITIZE_STRING);
    $content = json_decode(file_get_contents('php://input'));

    // action
    if ($command != 'users') {
      
        http_response_code(405);
        exit;
    }
    
    switch ($request) {
        
        case 'GET' :
            
            if ($id > 0) {
                $result = fetchUser($id);
            } 
            else if (isset($nom) || isset ($prenom)) {
                
                $result = findUsers($nom, $prenom);
            }
            else {
                $result = fetchAllUsers();
            }   
            break;
        
        case 'POST' : 

            $result = createUser($content);  
            break;
    
        case 'PUT' :
            
            $result =  updateUser($content);
            break;
        
        case 'DELETE' : 
            
            $result =  deleteUser($userID);
            break;
        
        default :
            
            $result['status_code_header'] = 405;
            exit;
    }

    http_response_code($result['status_code_header']);
    
    if (isset($result['body'])) {
        echo $result['body'];
    }
    