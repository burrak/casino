<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model\ShiftsManager;
use Nette\Utils\DateTime;
use Nette\Security\Identity;
use Nette\Security\User;

class ShiftsPresenter extends \Nette\Application\UI\Presenter {

   public $date;
   private $shifts;
   private $user;

   /**
    * @var \App\Model\ShiftsManager
    * @inject
    */
   public $shiftsManager;

   /*
    * Forms 
    */

   protected function createComponentAddShiftForm() {
      $shifts = $this->shiftsManager;
      $form = new Form;
      $form->addText('date', 'Datum:')
              ->setType('date')
              ->setRequired('Je nutné zadat datum');
      $form->addText('time', 'Čas:')
              ->setType('time')
              ->setRequired('Je nutné zadat čas');
      $form->addText('quantity', 'Počet dealerů:')
              ->setType('number')
              ->setRequired('Je nutné zadat počet požadovaných dealerů');
      $form->addSelect('branch', 'Pobočka:', $shifts->getBranchesSelect())
              ->setPrompt('Vyberte pobočku')
              ->addRule(Form::FILLED, 'Je nutné vybrat pobočku');
      $form->addSubmit('addShift', 'Přidat směny');
      $form->addProtection('Platnost formuláře vypršela');
      $form->onSuccess[] = [$this, 'addShiftFormSucceeded'];
      return $form;
   }

   public function addShiftFormSucceeded(Form $form) {
      $values = $form->getValues();
      $shifts = $this->shiftsManager;
      $shifts->addShifts($values->date, $values->time, $values->quantity, $values->branch);
   }

   public function createComponentEdit() {
      $form = new Form;
      $form->addProtection('Platnost formuláře vypršela');
      $form->onSubmit[] = [$this, 'editSucceeded'];
      return $form;
   }

   public function editSucceeded(Form $form, $values) {
      $shifts = $this->shiftsManager;
      $values = $form->getHttpData();
      $id = key($values['quant']);
      $value = (int) $values['quant'][$id];
      $shifts->editQuantity($id, $value);
   }

   public function createComponentFilterForm() {
      $data = $this->shiftsManager;
      $form = new Form;
      $form->getElementPrototype()->class('ajax');
      $form->getElementPrototype()->class('form-filter');
      $form->addSelect('branch', 'Pobočka:', $data->getBranchesSelect())
              ->setPrompt('Všechny');
      $form->addText('from', 'Od:')
              ->setType('date');
      $form->addText('to', 'Do:')
              ->setType('date');
      $form->onSubmit[] = [$this, 'filterShifts'];
      return $form;
   }
   
   /*
    * Signal handles
    */

   public function handleAssign($id) {
      $this->redirectUnauthorized('shifts', 'assign');
      $assign = $this->shiftsManager;
      $assign->assignShift($id, $this->getUser()->getId());
      $shift = $assign->getShift($id);
      $subject = 'Zapsání směny ' . $shift->datetime->format('d. m. Y') . ' od ' . $shift->datetime->format('H:i') . ' na pobočce ' . $shift->name;
      $text = 'Zapsání směny proběhlo úspěšně. Nyní vyčkejte na její potvrzení';
      $assign->addMessage(0, $this->getUser()->getId(), $subject, $text);
      $this->redirect('Shifts:');
   }

   public function handleCancel($id) {
      $cancel = $this->shiftsManager;
      $cancel->cancelShift($id, $this->getUser()->getId());
      $this->redirect('Shifts:');
   }

   public function handleCancelByManager($shift, $user) {
      $this->redirectUnauthorized('shifts', 'list');
      $cancel = $this->shiftsManager;
      $cancel->cancelShift($shift, $user);
      $shift_message = $cancel->getShift($shift);
      $subject = 'Zrušení směny směny ' . $shift_message->datetime->format('d. m. Y') . ' od ' . $shift_message->datetime->format('H:i') . ' na pobočce ' . $shift_message->name;
      $text = 'Manažer ' . $this->getUser()->getIdentity()->firstname . ' ' . $this->getUser()->getIdentity()->surname . ' Vám zrušil směnu.';
      $cancel->addMessage(0, $user, $subject, $text);
      $this->redirect('Shifts:dealersOnShift', $shift);
   }

   public function handleDelete($id) {
      
   }

   public function handleConfirm($shift, $user, $status) {
      $this->redirectUnauthorized('shifts', 'list');
      $confirm = $this->shiftsManager;
      $confirm->confirmShift($shift, $user, $status);
      $shift_message = $confirm->getShift($shift);
      $subject = 'Potvrzení směny ' . $shift_message->datetime->format('d. m. Y') . ' od ' . $shift_message->datetime->format('H:i') . ' na pobočce ' . $shift_message->name;
      $text = 'Manažer ' . $this->getUser()->getIdentity()->firstname . ' ' . $this->getUser()->getIdentity()->surname . ' Vám potvrdil směnu.';
      $confirm->addMessage(0, $user, $subject, $text);
      $this->redirect('Shifts:dealersOnShift', $shift);
   }

   public function handleFilterShifts($form) {
      $shift = $this->shiftsManager;
      $branch = $this->getHttpRequest()->getQuery('branch');
      $from = $this->getHttpRequest()->getQuery('from');
      $to = $this->getHttpRequest()->getQuery('to');
      $date = 'a';
      if ($branch !== '') {
         if ($from === '') {
            $from = '2000-01-01';
         }
         if ($to !== '') {
            $to = date('Y-m-d', strtotime($to . '+1 day'));
         } else {
            $to = '3000-01-01';
         }
         $this->template->shifts = $shift->listShiftsByDateAndBranch($this->getUser()->getId(), $from, $to, $branch);
         $this->template->shiftsAviable = $shift->listShiftsByDateAndBranchUpcoming($this->getUser()->getId(), $from, $to, $branch);
      } else {
         if ($from === '') {
            $from = '2000-01-01';
         }
         if ($to !== '') {
            $to = date('Y-m-d', strtotime($to . '+1 day'));
         } else {
            $to = '3000-01-01';
         }
         $this->template->shifts = $shift->listShiftsByDate($this->getUser()->getId(), $from, $to);
         $this->template->shiftsAviable = $shift->listShiftsUpcomingByDate($this->getUser()->getId(), $from, $to);
      }
      $this->redrawControl('shifts');
   }
   
   /*
    * Actions
    */

   public function actionDealersOnShift($id) {
      $this->redirectUnauthorized('shifts', 'list');
      $dealersOnShift = $this->shiftsManager;
      $this->template->now = Date("Y-m-d H:i:s");
      $this->template->shiftInfo = $dealersOnShift->getShift($id);
      $this->template->dealers = $dealersOnShift->getDealersOnShift($id);
   }

   
   /*
    * Default
    */
   public function renderDefault() {
      $shifts = $this->shiftsManager;
      $this->template->now = Date("Y-m-d H:i:s");
      if (!isset($this->template->shifts)) {
         $this->template->shifts = $shifts->listShiftsAll($this->getUser()->getId());
      }
      if (!isset($this->template->shiftsAviable)) {
         $this->template->shiftsAviable = $shifts->listShiftsUpcoming($this->getUser()->getId());
      }
      $this->template->shiftsMine = $shifts->listShiftsMine($this->getUser()->getId());
   }

}
