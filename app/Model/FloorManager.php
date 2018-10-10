<?php

namespace App\Model;

use Nette;

/**
 * Floor Manager
 */
class FloorManager {
   
   use \Nette\SmartObject;
   
   /** @var Nette\Database\Context */
    private $database;
    
    public function __construct(Nette\Database\Context $database) {
      $this->database = $database;
   }
     
   public function getAllFloor() {
      try {
         $floor = $this->database->table('user')->select('id, email, firstname, surname')->where('role', 1);
      } catch (Exception $ex) {

      }
      return $floor;
   }
   
   public function getDealersSelect() {
      try {
         $rows = $this->database->table('user')->select('id, email, firstname, surname')->where('role', 0)->fetchAll();
         $dealers = [];
      } catch (Exception $ex) {

      }
      foreach ($rows as $row) {
         $dealers[$row['id']] = $row['firstname'].' '.$row['surname'];
      }
      return $dealers;      
   }
   
   public function addFloor($user) {
      try {
         $add = $this->database->query('UPDATE user SET role = 1 WHERE id = ?', $user);
      } catch (Exception $ex) {

      }
      return $add;
   }
   
   public function revokeFloor($user) {
      try {
         $revoke = $this->database->query('UPDATE user SET role = 0 WHERE id = ?', $user);
      } catch (Exception $ex) {

      }
      return $revoke;
   }
} 

