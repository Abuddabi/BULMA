<?php
namespace App\Controllers\Admin;

use App\Services\ImageManager;
use App\Services\Roles;
use Delight\Auth\Status;

class UsersController extends Controller
{
    private $imageManager;

    public function __construct(ImageManager $imageManager)
    {
        parent::__construct();
        $this->imageManager = $imageManager;
    }

    public function index()
    {
      $users = $this->database->all('users');
      echo $this->view->render('admin/users/index', ['users' =>  $users]);
    }

    public function create()
    {
        $roles = Roles::getRoles();
        echo $this->view->render('admin/users/create', ['roles'  =>  $roles]);
    }

    public function store()
    {
        try {
            $id = $this->auth->admin()->createUser($_POST['email'], $_POST['password'], $_POST['username']);
            $user = $this->database->find('users',$id);
            $data = [
                'status'    =>  isset($_POST['status']) ? Status::BANNED : Status::NORMAL,
                'roles_mask'    =>  $_POST['roles_mask']
            ];

            $data['image'] = $this->imageManager->uploadImage($_FILES['image'], $user['image'], 'avatar');

            $this->database->update('users', $id, $data);
            return redirect('/admin/users');
        }
        catch (\Delight\Auth\InvalidEmailException $e) {
            // invalid email address
            flash()->error(['Неправильный формат email']);
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            // invalid password
            flash()->error(['Неправильный пароль']);
        }
        catch (\Delight\Auth\UserAlreadyExistsException $e) {
            // user already exists
            flash()->error(['Пользователь уже существует']);
        }

        return back();
    }

    public function edit($id)
    {
        $user = $this->database->find('users', $id);
        $roles = Roles::getRoles();
        $this->auth->hasRole(1); 
        echo $this->view->render('admin/users/edit', ['user'    =>  $user, 'roles'   =>  $roles]);
    }

    public function update($id)
    {
        $data = [
            'username'   =>  $_POST['username'],
            'email' =>  $_POST['email'],
            'status'    =>  isset($_POST['status']) ? Status::BANNED : Status::NORMAL,
            "roles_mask"    =>  $_POST['roles_mask']
        ];
        if(!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'],PASSWORD_DEFAULT);
        }
        $user = $this->database->find('users', $id);
        $data['image'] = $this->imageManager->uploadImage($_FILES['image'], $user['image'], 'avatar');

        $this->database->update('users', $id, $data);

        return redirect('/admin/users');
    }

    public function delete($id)
    {
        try {
            $user = $this->database->find('users', $id);
            $this->imageManager->deleteImage($user['image'], 'avatar');
            $this->auth->admin()->deleteUserById($id);
            return redirect('/admin/users');
        }
        catch (\Delight\Auth\UnknownIdException $e) {
            flash()->error(['Пользователь не найден']);
        }

        return back();
    }

    private function validate($validator, $data, $message)
    {
        try {
            $validator->assert($data);

        } catch (ValidationException $exception) {
            $exception->findMessages($message);
            flash()->error($exception->getMessages());

            return back();
        }
    }

}