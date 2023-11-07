<?php 
    require_once("config.php");

    class Database extends Config {

        // Transform all ID into (ID = IDValue) in form of string.
        // Used by update and delete function.
        public function extractID($idArray) {
            $idString = "";
            foreach ($idArray as $key => $value){
                $idString .= $key . " = " . $value . " AND ";
            }
            return $idString = substr($idString, 0, -4);  
        }

        public function login($username, $password){
            $sql = "SELECT password FROM `User` WHERE Uname = '{$username}'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            if (empty($rows)){
                return false;
            } else {
                if (password_verify($password, $rows[0]['password'])) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        public function fetch($tableName, $idArray) {
            $idString = $this->extractID($idArray);                                                      
            $sql = "SELECT * FROM $tableName";
            if (!empty($idArray)) {
                $sql .= " WHERE {$idString}";
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return $rows;
        }

        public function insert($tableName, $postData) {
            // Transform all columns into [(column)) values value] in form of string
            $columnsString = "";
            $valuesString = "";
            foreach ($postData as $key => $value) {
                $columnsString .= $key . ", ";
                if (!is_int($value)) {
                    $valuesString .= "\"" . $value . "\"" . ", ";
                } else {
                    $valuesString .= $value . ", ";
                }
            }
            $columnsString = substr($columnsString, 0, -2);
            $valuesString = substr($valuesString, 0, -2);

            $sql = "INSERT INTO `$tableName` ({$columnsString}) values ({$valuesString})";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return true;
        }

        public function update($tableName, $idArray, $postData) {

            $idString = $this->extractID($idArray);
            // Transform all columns (except IDs) into (column = values) in form of string 
            $updateString = "";
            foreach ($postData as $key => $value) {
                $updateString .= $key . " = ";
                if (!is_int($value)) {
                    $updateString .= "\"" . $value . "\"" . ", ";
                } else {
                    $updateString .= $value . ", ";
                }
            }
            $updateString = substr($updateString, 0, -2);

            $sql = "UPDATE `{$tableName}` SET {$updateString} WHERE {$idString}";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return true;
        }

        public function delete($tableName, $idArray) {

            $idString = $this->extractID($idArray);

            $sql = "DELETE FROM `{$tableName}` WHERE {$idString}";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return true;
        }
    }
?>