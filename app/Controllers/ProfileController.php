<?php
namespace App\Controllers;

use App\Services\Mail;
use App\Services\Profile;

class ProfileController extends Controller
{
    private $mailer;
    private $profile;

    public function __construct(Mail $mailer, Profile $profile)
    {
        parent::__construct();
        $this->mailer = $mailer;
        $this->profile = $profile;
    }

    public function showInfo()
    {
        $user = $this->database->find('users',$this->auth->getUserId());
        echo $this->view->render('profile/info', compact('user'));
    }

    public function showSecurity()
    {
        echo $this->view->render('profile/security');
    }

    public function postInfo()
    {
        try {
            $this->profile->changeInformation($_POST['email'], $_POST['username'],  $_FILES['image']);
            flash()->success(['Профиль обновлен']);
        }
        catch (\Delight\Auth\InvalidEmailException $e) {
            flash()->error(['неверный формат имейла']);
            // invalid email address
        }
        catch (\Delight\Auth\UserAlreadyExistsException $e) {
            // email address already exists
            flash()->error(['E-mail уже существует']);
        }
        catch (\Delight\Auth\EmailNotVerifiedException $e) {
            // account not verified
            flash()->error(['почта не подтверждена']);
        }
        catch (\Delight\Auth\NotLoggedInException $e) {
            // not logged in
            flash()->error(['ты не залогинен']);
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            // too many requests
            flash()->error(['куда ломишься!']);
        }
        return back();

    }

    public function postSecurity()
    {
        try {
            $this->auth->changePassword($_POST['password'], $_POST['new_password']);
            flash()->success(['Пароль успешно изменен.']);
            // password has been changed
        }
        catch (\Delight\Auth\NotLoggedInException $e) {
            // not logged in
            flash()->error(['Залогиньтесь!']);
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            // invalid password(s)
            flash()->error(['Неправильный пароль!']);
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            // too many requests
            flash()->error(['Куда ломишься!']);
        }

        return back();
    }
}