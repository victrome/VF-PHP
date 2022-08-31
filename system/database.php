<?php

namespace VF;

if (!defined('PATH_VF')) { // DO NOT REMOVE
   exit('VF IS NOT LOADED');
}
class Database
{

   protected function defaultConnection(): \PDO
   {
      $server = "";
      $database = "vf";
      $user = "v";
      $pass = "12345";
      $driver = "mysql";
      //$driverArray = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES ".DB_CHARSET);
      $driverArray = array();
      try {
         $conn = new \PDO("{$driver}:host={$server};dbname={$database}", "{$user}", "{$pass}", $driverArray);
         $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
         return $conn;
      } catch (PDOException $e) {
         echo '<center><h1>DATABASE ERROR</h1></center>';
         echo $e->getMessage();
         exit();
      }
      return false;
   }


   protected function wordpressConnection(): \PDO
   {
      $server = DB_HOST;
      $database = DB_NAME;
      $user = DB_USER;
      $pass = DB_PASSWORD;
      $driver = "mysql";
      $driverArray = array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET);
      try {
         $conn = new \PDO("{$driver}:host={$server};dbname={$database}", "{$user}", "{$pass}", $driverArray);
         $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
         return $conn;
      } catch (PDOException $e) {
         echo '<center><h1>DATABASE ERROR</h1></center>'; //$e->getMessage(); 
         exit();
      }
      return false;
   }
}
