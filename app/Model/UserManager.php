<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use Contributte\Facebook\Exceptions\FacebookLoginException;
use Contributte\Facebook\FacebookLogin;

/**
 * Users management
 */
class UserManager implements Nette\Security\IAuthenticator {

   use \Nette\SmartObject;

   const
           TABLE_NAME = 'user',
           COLUMN_ID = 'id',
           COLUMN_FIRSTNAME = 'firstname',
           COLUMN_SURNAME = 'surname',
           COLUMN_EMAIL = 'email',
           COLUMN_PASSWORD = 'password',
           COLUMN_PHONE = 'phone',
           COLUMN_ROLE = 'role';

   /** @var Nette\Database\Context */
   private $database;

   /** @var Passwords */
   private $passwords;

   public function __construct(Nette\Database\Context $database) {
      $this->database = $database;
   }

   /**
    * Performs an authentication.
    * @throws Nette\Security\AuthenticationException
    */
   public function authenticate(array $credentials): Nette\Security\IIdentity {
      [$email, $psid, $password, $facebook, $first_name, $last_name] = $credentials;

      $row = $this->database->table(self::TABLE_NAME)
              ->select('user.*, role.role')
              ->where(self::COLUMN_EMAIL, $email)
              ->fetch();

      /*
       * Přihlášení přes formulář
       */
      if (($password !== '') && ($facebook === 0)) {
         if (!$row) {
            throw new Nette\Security\AuthenticationException('Tento e-mail není zaregistrovaný');
         } elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD])) {
            throw new Nette\Security\AuthenticationException('Špatné heslo');
         } elseif (Passwords::needsRehash($row[self::COLUMN_PASSWORD])) {
            $row->update([
                self::COLUMN_PASSWORD => Passwords::hash($password),
            ]);
         }
      }
      /*
       * Přihlášení přes Facebook
       */
      if (($password === '') && ($facebook === 1)) {
         if (!$row) {
            $this->database->table(self::TABLE_NAME)->insert([
                'psid' => $psid,
                self::COLUMN_EMAIL => $email,
                self::COLUMN_FIRSTNAME => $first_name,
                self::COLUMN_SURNAME => $last_name,
            ]);

            $row = $this->database->table(self::TABLE_NAME)
                    ->select('user.*, role.role')
                    ->where(self::COLUMN_EMAIL, $email)
                    ->fetch();
         }
      }

      $arr = $row->toArray();
      unset($arr[self::COLUMN_PASSWORD]);
      return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
   }

   /**
    * Adds new user.
    * @throws DuplicateNameException
    */
   public function add(string $firstname, string $surname, string $email, string $password, int $phone = null): void {
      try {
         $this->database->table(self::TABLE_NAME)->insert([
             self::COLUMN_FIRSTNAME => $firstname,
             self::COLUMN_SURNAME => $surname,
             self::COLUMN_PASSWORD => Passwords::hash($password),
             self::COLUMN_EMAIL => $email,
             self::COLUMN_PHONE => $phone,
         ]);
      } catch (Nette\Database\UniqueConstraintViolationException $e) {
         throw new DuplicateNameException;
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

}

class DuplicateNameException extends \Exception {
   
}
