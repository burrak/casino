<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model\UserManager;
use Nette\Security\Identity;
use Contributte\Facebook\Exceptions\FacebookLoginException;
use Contributte\Facebook\FacebookLogin;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\UI\Presenter;
use Nette\Security\AuthenticationException;

class HomepagePresenter extends Nette\Application\UI\Presenter {

   private $user;

   /**
    * @var \App\Model\UserManager
    * @inject
    */
   public $userManager;

   /** @var FacebookLogin @inject */
   public $facebookLogin;

   public function actionFacebook() {
// Redirect to FB and ask customer to grant access to his account
      $url = $this->facebookLogin->getLoginUrl($this->link('//facebookAuthorize'), ['email', 'public_profile']);
      $this->sendResponse(new RedirectResponse($url));
   }

   /**
    * Log in user with accessToken obtained after redirected from FB
    *
    * @return void
    */
   public function actionFacebookAuthorize() {
// Fetch User data from FB and try to login
      try {
         $token = $this->facebookLogin->getAccessToken();
         $this->user->login('facebook', $this->facebookLogin->getMe($token, ['first_name', 'last_name', 'email']));
         $this->flashMessage('Login successful :-).', 'success');
      } catch (Exception $e) {
         $this->flashMessage('Login failed. :-( Try again.', 'danger');
      }
   }

   protected function createComponentLoginForm() {
      $form = new Form;
      $form->addText('email', 'E-mail:')
              ->setType('email')
              ->addRule(Form::EMAIL, 'E-mail je ve špatném formátu')
              ->setRequired('Zadejte e-mail');
      $form->addPassword('password', 'Heslo:')
              ->setType('password')
              ->setRequired('Zadejte heslo');
      $form->addSubmit('login', 'Přihlásit');
      $form->onSuccess[] = [$this, 'loginFormSucceeded'];
      return $form;
   }

   public function loginFormSucceeded(Form $form, $values) {
      try {
         if($values->password === '') {
            $this->flashMessage('Musíte zadat heslo');
            $this->redirect('Homepage:');
         }
         $this->getUser()->login($values->email, '', $values->password, 0, '', '');
         $this->flashMessage('Přihlášení bylo úspěšné.');
         $this->redirect('Homepage:');
      } catch (\Nette\Security\AuthenticationException $e) {
         $this->flashMessage($e);
//$this->redirect('Homepage:');
      }
   }

   public function actionLogout() {
      $this->getUser()->logout();
      $this->flashMessage('Odhlášení bylo úspěšné.');
      $this->redirect('Homepage:');
   }

   public function renderDefault() {
      
   }

}
