<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model\UserManager;

class RegisterPresenter extends \Nette\Application\UI\Presenter
{
    private $user;
    /**
    * @var \App\Model\UserManager
    * @inject
    */
    public $userManager;
    
    protected function createComponentRegisterForm() {
        $form = new Form;
        $form->addText('firstname', 'Jméno:')
                ->setType('text')
                ->setRequired('Prosím vyplňte jméno');
        $form->addText('surname', 'Příjmení:')
                ->setType('text')
                ->setRequired('Prosím vyplňte příjmení');
        $form->addText('email', 'E-mail:')
                ->setType('email')
                ->setRequired('Prosím vyplňte e-mail')
                ->addRule(Form::EMAIL, 'E-mail je ve špatném formátu');
        $form->addInteger('phone', 'Telefon:')
                ->setType('number')
                ->setRequired(false)
                ->addRule(Form::INTEGER, 'Telefon musí být číslo')
                ->addRule(Form::LENGTH, 'Číslo není ve správném formátu', 9);       
        $form->addPassword('password', 'Heslo:')
                ->setType('password')
                ->setRequired('Prosím vyplňte heslo')
                ->addRule(Form::MIN_LENGTH, 'Heslo musí být alespoň %d znaků dlouhé', 6);
        $form->addPassword('password_verify', 'Ověření hesla:')
                ->setRequired('Zadejte heslo pro kontrolu')
                ->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password']);
        $form->addSubmit('register', 'Zaregistrovat');
        $form->onSuccess[] = [$this, 'registerFormSucceeded'];
        return $form;
                
    }
    
    public function registerFormSucceeded(Form $form) {
        $values = $form->getValues();
        $user = $this->userManager;
        $user->add($values->firstname, $values->surname, $values->email, $values->password, $values->phone);
        $this->flashMessage('Registrace proběhla úspěšně');
        $this->redirect('Homepage:');
    }
}
