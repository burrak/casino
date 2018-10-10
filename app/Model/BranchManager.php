<?php

namespace App\Model;

use Nette;
use Nette\Utils\Strings;
use Nette\Utils\Json;

/**
 * Branch Manager
 */
class BranchManager {
   
   private $database;
   
   public function __construct(Nette\Database\Context $database) {
      $this->database = $database;
   }
   
   public function getBranches() {
      try {
         $branches = $this->database->table('branch')->select('*');
      } catch (Exception $ex) {

      }
      return $branches;
   }
   
   private function geocode($address) {
      $address = urlencode($address);
      $url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$address."&key=AIzaSyAbkVb-r49-oyjjvDa_yb2VipF9i8Ztciw";
      $resp_json = file_get_contents($url);
      $resp = json_decode($resp_json, true);
      return array('lat' => $resp['results'][0]['geometry']['location']['lat'], 'lng' => $resp['results'][0]['geometry']['location']['lng'], 'place_id' => $resp['results'][0]['place_id']);
   }
   
   public function addBranch(string $name, string $address, int $phone) {
      $coord = $this->geocode($address);
      try {
         $this->database->table('branch')->insert([
             'name' => $name,
             'address' => $address,
             'phone' => $phone,
             'lat' => $coord['lat'],
             'lng' => $coord['lng'],
             'place_id' => $coord['place_id']
         ]);
      } catch (Exception $ex) {

      }
      
   }
   
   public function deleteBranch($id) {
      try {
         $delete = $this->database->table('branch')->where('id', $id)->delete();
      } catch (Exception $ex) {

      }
      return $delete;
   }
   
   public function jsonBranch() {
      $branches = $this->database->table('branch')->select('name, lat, lng')->fetchAssoc('name');
      $json = Json::encode($branches);
      return $json;
   }
}

