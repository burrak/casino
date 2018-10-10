<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model\ShiftsManager;
use Nette\Utils\DateTime;
use Nette\Security\Identity;
use Nette\Security\User;

class FloorPresenter extends \Nette\Application\UI\Presenter {

   /**
    * @var \App\Model\FloorManager
    * @inject
    */
   public $floorManager;

   public function renderDefault() {
      $this->redirectUnauthorized('floor', 'assign');
      $floors = $this->floorManager;
      $this->template->floors = $floors->getAllFloor();
   }

   public function createComponentAddFloor() {
      $users = $this->floorManager;
      $form = new Form;
      $form->addSelect('user', 'Uživatel', $users->getDealersSelect());
      $form->addSubmit('addFloor', 'Přidělit Floor status');
      $form->addProtection('Platnost formuláře vypršela');
      $form->onSuccess[] = [$this, 'AddFloorSucceeded'];
      return $form;
   }

   public function AddFloorSucceeded(Form $form) {
      $floor = $this->floorManager;
      $values = $form->getValues();
      $floor->addFloor($values->user);
      $this->redirect('Floor:');
   }

   public function handleRevoke($user) {
      $this->redirectUnauthorized('floor', 'assign');
      $this->floorManager->revokeFloor($user);     
      $this->redirect('Floor:');
   }

}
