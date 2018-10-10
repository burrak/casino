<?php

namespace App\Model;

use Nette;
use Nette\Utils\Strings;
use Nette\Utils\Json;

/**
 * Branch Manager
 */
class Messages {

   private $database;

   public function __construct(Nette\Database\Context $database) {
      $this->database = $database;
   }



}
