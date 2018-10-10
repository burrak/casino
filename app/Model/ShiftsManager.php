<?php

namespace App\Model;

use Nette;

/**
 * Shifts manager
 */
class ShiftsManager {

   private $database;
   private $shiftsList;

   public function __construct(Nette\Database\Context $database) {
      $this->database = $database;
   }

   public function getBranchesSelect() {
      $rows = $this->database->table('branch')
              ->select('id, name')
              ->fetchAll();
      $branches = [];
      foreach ($rows as $row) {
         $branches[$row['id']] = $row['name'];
      }
      return $branches;
   }

   public function addShifts(string $date, string $time, int $quantity, int $branch) {
      try {
         $this->database->table('shift')->insert([
             'datetime' => $date.' '.$time,
             'quantity' => $quantity,
             'branch' => $branch]);
      } catch (Exception $ex) {
         
      }
   }
   
   public function addMessage($sender, $reciever, $subject, $text) {
      try {
         $message = $this->database->table('message')->insert([
             'sender' => $sender,
             'reciever' => $reciever,
             'subject' => $subject,
             'text' => $text]);
      } catch (Exception $ex) {

      }
      return $message;
   }

   public function editQuantity($id, $quantity) {
      try {
         $edit = $this->database->table('shift')->where('id', $id)->update(['quantity' => $quantity]);
      } catch (Exception $ex) {
         
      }
      return $edit;
   }

   public function listShiftsAll($user) {
      try {
         $shiftsList = $this->database->query('SELECT s.*, a.*, branch.name, (SELECT count(*) FROM shift_assign WHERE shift_assign.id_shift = s.id) AS assigned FROM shift s LEFT JOIN (SELECT * FROM shift_assign WHERE id_user = ?) a ON s.id = a.id_shift LEFT JOIN branch ON s.branch = branch.id ORDER BY s.id DESC', $user);
      } catch (Exception $ex) {
         
      }
      return $shiftsList;
   }

   public function listShiftsUpcoming($user) {
      try {
         $shiftsList = $this->database->query('SELECT s.*, a.*, branch.name, (SELECT count(*) FROM shift_assign WHERE shift_assign.id_shift = s.id) AS assigned FROM shift s LEFT JOIN (SELECT * FROM shift_assign WHERE id_user = ?) a ON s.id = a.id_shift LEFT JOIN branch ON s.branch = branch.id WHERE s.datetime > NOW() ORDER BY s.id DESC', $user);
      } catch (Exception $ex) {
         
      }
      return $shiftsList;
   }

   public function listShiftsUnassigned($user) {
      try {
         $shiftsList = $this->database->query('select s.*, (SELECT count(*) FROM shift_assign WHERE shift_assign.id_shift = s.id) as assigned from shift s left JOIN (SELECT id_user, id_shift AS u FROM shift_assign GROUP BY id_user) z ON s.id = z.id_user where z.id_user != ?', $user);
      } catch (Exception $ex) {
         
      }
      return $shiftsList;
   }

   public function listShiftsMine($user) {
      try {
         $shiftsList = $this->database->query('SELECT shift.*, shift_assign.*, branch.name FROM shift LEFT JOIN shift_assign ON shift.id = shift_assign.id_shift LEFT JOIN branch ON branch.id = shift.branch WHERE shift_assign.id_user = ? ORDER BY id DESC', $user);
      } catch (Exception $ex) {
         
      }
      return $shiftsList;
   }
   
   public function listShiftsByBranch($user, $branch) {
      try {
         $shiftsList = $this->database->query('SELECT s.*, a.*, branch.name, (SELECT count(*) FROM shift_assign WHERE shift_assign.id_shift = s.id) AS assigned FROM shift s LEFT JOIN (SELECT * FROM shift_assign WHERE id_user = ?) a ON s.id = a.id_shift LEFT JOIN branch ON s.branch = branch.id WHERE branch.id = ? ORDER BY s.id DESC', $user, $branch);
      } catch (Exception $ex) {
         
      }
      return $shiftsList;
   }
   
   public function listShiftsByBranchUpcoming($user, $branch) {
      try {
         $shiftsList = $this->database->query('SELECT s.*, a.*, branch.name, (SELECT count(*) FROM shift_assign WHERE shift_assign.id_shift = s.id) AS assigned FROM shift s LEFT JOIN (SELECT * FROM shift_assign WHERE id_user = ?) a ON s.id = a.id_shift LEFT JOIN branch ON s.branch = branch.id WHERE s.datetime > NOW() AND branch.id = ? ORDER BY s.id DESC', $user, $branch);
      } catch (Exception $ex) {
         
      }
      return $shiftsList;     
   }
   
      public function listShiftsByDate($user, $from, $to) {
      try {
         $shiftsList = $this->database->query('SELECT s.*, a.*, branch.name, (SELECT count(*) FROM shift_assign WHERE shift_assign.id_shift = s.id) AS assigned FROM shift s LEFT JOIN (SELECT * FROM shift_assign WHERE id_user = ?) a ON s.id = a.id_shift LEFT JOIN branch ON s.branch = branch.id WHERE s.datetime >= ? AND s.datetime <= ? ORDER BY s.id DESC', $user, $from, $to);
      } catch (Exception $ex) {
         
      }
      return $shiftsList;
   }
   
   public function listShiftsUpcomingByDate($user, $from, $to) {
      try {
         $shiftsList = $this->database->query('SELECT s.*, a.*, branch.name, (SELECT count(*) FROM shift_assign WHERE shift_assign.id_shift = s.id) AS assigned FROM shift s LEFT JOIN (SELECT * FROM shift_assign WHERE id_user = ?) a ON s.id = a.id_shift LEFT JOIN branch ON s.branch = branch.id WHERE s.datetime > NOW() AND s.datetime >= ? AND s.datetime <= ? ORDER BY s.id DESC', $user, $from, $to);
      } catch (Exception $ex) {
         
      }
      return $shiftsList;     
   }
   
         public function listShiftsByDateAndBranch($user, $from, $to, $branch) {
      try {
         $shiftsList = $this->database->query('SELECT s.*, a.*, branch.name, (SELECT count(*) FROM shift_assign WHERE shift_assign.id_shift = s.id) AS assigned FROM shift s LEFT JOIN (SELECT * FROM shift_assign WHERE id_user = ?) a ON s.id = a.id_shift LEFT JOIN branch ON s.branch = branch.id WHERE s.datetime >= ? AND s.datetime <= ? AND branch.id = ? ORDER BY s.id DESC', $user, $from, $to, $branch);
      } catch (Exception $ex) {
         
      }
      return $shiftsList;
   }
   
   public function listShiftsByDateAndBranchUpcoming($user, $from, $to, $branch) {
      try {
         $shiftsList = $this->database->query('SELECT s.*, a.*, branch.name, (SELECT count(*) FROM shift_assign WHERE shift_assign.id_shift = s.id) AS assigned FROM shift s LEFT JOIN (SELECT * FROM shift_assign WHERE id_user = ?) a ON s.id = a.id_shift LEFT JOIN branch ON s.branch = branch.id WHERE s.datetime > NOW() AND s.datetime >= ? AND s.datetime <= ? AND branch.id = ? ORDER BY s.id DESC', $user, $from, $to, $branch);
      } catch (Exception $ex) {
         
      }
      return $shiftsList;     
   }

   public function getShift($id) {
      try {
         $shift = $this->database->query('SELECT shift.*, branch.name FROM shift, branch WHERE shift.id=? AND branch.id = shift.branch', $id)
                      ->fetch();        
      } catch (Exception $ex) {

      }
      return $shift; 
   }

   public function getDealersOnShift($id) {
      $shift = $this->getShift($id);
      try {
         $dealersOnShift = $this->database->query('SELECT user.firstname, user.surname, user.email, user.phone, shift_assign.*, shift.* FROM shift_assign LEFT JOIN user ON user.id = shift_assign.id_user LEFT JOIN shift ON shift_assign.id_shift = shift.id WHERE shift_assign.id_shift = ?', $shift['id']);
      } catch (Exception $ex) {
         
      }
      return $dealersOnShift;
   }

   public function assignShift($id, $user) {
      try {
         $insert = $this->database->table('shift_assign')
                 ->insert([
             'id_shift' => $id,
             'id_user' => $user
         ]);
      } catch (Exception $ex) {
         
      }
      return $insert;
   }

   public function cancelShift($id, $user) {
      try {
         $delete = $this->database->table('shift_assign')->where('id_user', $user)->where('id_shift', $id)->delete();
      } catch (Exception $ex) {
         
      }
      return $delete;
   }
   
   public function confirmShift($shift, $user, $status) {
      try {
         $confirm = $this->database->table('shift_assign')->where('id_user', $user)->where('id_shift', $shift)->update(['status' => $status]);
      } catch (Exception $ex) {

      }
      return $confirm;
   }

}
