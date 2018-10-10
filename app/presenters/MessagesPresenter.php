<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\DateTime;
use Nette\Security\Identity;
use Nette\Security\User;

class MessagesPresenter extends \Nette\Application\UI\Presenter {

   /**
    * @var \App\Model\Messages
    * @inject
    */
   public $messages;
   
   public function renderDefault() {
      
   }

}
