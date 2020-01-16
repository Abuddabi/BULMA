<?php
namespace App\Controllers;

use App\Services\RegistrationService;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

class RegisterController extends Controller
{
    private $registration;

    public function __construct(RegistrationService $registration)
    {
        parent::__construct();
        $this->registration = $registration;
    }

    public function showForm()
    {
        echo $this->view->render('auth/register');
    }

    public function register()
    {
        $this->validate();
        try {
            $this->registration->make(
                $_POST['email'],
                $_POST['password'],
                $_POST['username']
            );
            flash()->success(['На вашу почту ' . $_POST['email'] . ' был отправлен код с подтверждением.']);
            return redirect('/login');
        }
        catch (\Delight\Auth\InvalidEmailException $e) {
            flash()->error(['Неправильный email']);
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            flash()->error(['Неправильный пароль']);
        }
        catch (\Delight\Auth\UserAlreadyExistsException $e) {
            flash()->error(['Пользователь уже существует']);
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            flash()->error(['Слишком много раз пытаетесь зарегаться']);
        }

        return redirect('/register');
    }

    private function validate()
    {
        $validator = v::key('username', v::stringType()->notEmpty())
            ->key('email', v::email())
            ->key('password', v::stringType()->notEmpty())
            ->key('terms', v::trueVal())
            ->keyValue('password_confirmation', 'equals', 'password');

        try {
            $validator->assert($_POST);

        } catch (ValidationException $exception) {
            $exception->findMessages($this->getMessages());
            flash()->error($exception->getMessages());

            return redirect('register');
        }
    }

    private function getMessages()
    {
        return [
            'terms'   =>  'Вы должны согласится с правилами.',
            'username' => 'Введите имя',
            'email' => 'Неверный формат e-mail',
            'password'  =>  'Введите пароль',
            'password_confirmation' =>  'Пароли не совпадают'
        ];
    }
}