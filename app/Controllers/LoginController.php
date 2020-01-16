<?php
namespace App\Controllers;

use App\Services\Roles;
use Delight\Auth\Auth;

class LoginController extends Controller
{

    public function showForm()
    {
        $this->checkForAccess();

        echo $this->view->render('auth/login');
    }

    public function login()
    {
        $this->checkForAccess();

        try {
            $rememberDuration = null;

            if (isset($_POST['remember'])) {
                // keep logged in for one year
                $rememberDuration = (int) (60 * 60 * 24 * 365.25);
            }

            $this->auth->login($_POST['email'], $_POST['password'], $rememberDuration);

            $this->checkIsBanned();

            return redirect('/');
            // user is logged in
        }
        catch (\Delight\Auth\InvalidEmailException $e) {
            flash()->error(['Email не верный']);
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            flash()->error(['Неверный пароль']);
        }
        catch (\Delight\Auth\EmailNotVerifiedException $e) {
            flash()->error(['Email не подтвержден']);
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            flash()->error(['КУда ломишься?!']);
        }

        return back();
    }

    public function logout()
    {
        $this->auth->logOut();
        return redirect('/');
    }

    public function checkIsBanned()
    {
        if($this->auth->isBanned()) {
            flash()->error(['Вы забанены.']);
            $this->auth->logout();
            return redirect('/login');
        }
    }
}