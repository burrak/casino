<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model\ShiftsManager;
use Nette\Utils\DateTime;
use Nette\Security\Identity;
use Nette\Security\User;

class BranchesPresenter extends \Nette\Application\UI\Presenter {

   /**
    * @var \App\Model\BranchManager
    * @inject
    */
   public $branchManager;
   
   public function createComponentAddBranchForm() {
      $form = new Form;
      $form->addText('name', 'Jméno:')
              ->setRequired('Je nutné zadat název pobočky');
      $form->addText('address', 'Adresa:')
              ->setRequired('Je nutné zadat adresu pobočky');
      $form->addText('phone', 'Telefon:')
              ->setType('number')
              ->setRequired('Je nutné zadat telefonní číslo');
      $form->addSubmit('addBranch', 'Přidat pobočku');
      $form->addProtection('Platnost formuláře vypršela');
      $form->onSuccess[] = [$this, 'addBranchFormSucceeded'];
      return $form;
   }
   
   public function addBranchFormSucceeded(Form $form) {
      $values = $form->getValues();
      $branch = $this->branchManager;
      $branch->addBranch($values->name, $values->address, $values->phone);
      $this->redirect('Branches:');
   }
   
   public function handleDelete($id) {
      $this->redirectUnauthorized('branch', 'add');
      $this->branchManager->deleteBranch($id);
      $this->redirect('Branches:');
   }

   public function renderDefault() {
      $branches = $this->branchManager->getBranches();
      $json = $this->branchManager->jsonBranch();
      $this->template->branches = $branches;    
      $this->template->coords = $json;
   }
   
   

}
