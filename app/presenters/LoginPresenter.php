<?php

namespace App\Presenters;

use Nette;
use Contributte\Facebook\Exceptions\FacebookLoginException;
use Contributte\Facebook\FacebookLogin;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\UI\Presenter;
use Nette\Security\AuthenticationException;

final class LoginPresenter extends Nette\Application\UI\Presenter
{

    /** @var FacebookLogin @inject */
    public $facebookLogin;

    public function actionFacebook()
    {
        // Redirect to FB and ask customer to grant access to his account
        $url = $this->facebookLogin->getLoginUrl($this->link('//facebookAuthorize'), ['email', 'public_profile']);
        $this->sendResponse(new RedirectResponse($url));
    }

    /**
     * Log in user with accessToken obtained after redirected from FB
     *
     * @return void
     */
    public function actionFacebookAuthorize()
    {
        // Fetch User data from FB and try to login
        try {
            $token = $this->facebookLogin->getAccessToken();
            $email = $this->facebookLogin->getMe($token, ['email', 'id' ,'first_name', 'last_name']);

            $this->user->login($email['email'], $email['id'],'', 1, $email['first_name'], $email['last_name']);
            $this->flashMessage('Login successful :-).', 'success');
        } catch (FacebookLoginException | AuthenticationException $e) {
            $this->flashMessage('Login failed. :-( Try again.', 'danger');
        }
    }

}