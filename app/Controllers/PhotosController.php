<?php
namespace App\Controllers;

use App\Services\Database;
use App\Services\ImageManager;
use Delight\Auth\Auth;
use League\Plates\Engine;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;
use JasonGrimes\Paginator;

class PhotosController extends Controller
{
    private $imageManager;

    public function __construct(ImageManager $imageManager)
    {
        parent::__construct();
        $this->imageManager = $imageManager;
    }

    public function index()
    {
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $perPage = 8;

        $photos = $this->database->getPaginatedFrom('photos', 'user_id' ,$this->auth->getUserId(), $page, $perPage);

        $paginator = paginate(
            $this->database->getCount('photos', 'user_id', $this->auth->getUserId()),
            $page,
            $perPage,
            '/photos?page=(:num)'
        );

        echo $this->view->render('photos/index', ['photos'   =>  $photos, 'paginator'    =>  $paginator]);
    }

    public function create()
    {
        $categories = $this->database->all('categories');
        echo $this->view->render('photos/create', ['categories'  =>  $categories]);
    }

    public function store()
    {
        $validator = v::key('title', v::stringType()->notEmpty())
            ->key('description', v::stringType()->notEmpty())
            ->key('category_id', v::intVal())
            ->keyNested('image.tmp_name', v::image());

        $this->validate($validator);
        $image = $this->imageManager->uploadImage($_FILES['image']);
        $dimensions = $this->imageManager->getDimensions($image);
        $data = [
            "image" =>  $image,
            "title" =>  $_POST['title'],
            "description" =>  $_POST['description'],
            "category_id" =>  $_POST['category_id'],
            "user_id"   =>  $this->auth->getUserId(),
            "dimensions" =>  $dimensions,
            "date"  =>  time(),
        ];
        $this->database->create('photos', $data);

        flash()->success(['Спасибо! Картинка загружена']);

        return back();
    }

    public function edit($id)
    {
        $photo = $this->database->find('photos', $id);
        if($photo['user_id'] != $this->auth->getUserId()) {
            flash()->error(['Можно редактировать только свои фотографии.']);
            return redirect('/photos');
        }

        $categories = $this->database->all('categories');
        echo $this->view->render('photos/edit', ['photo' =>  $photo, 'categories'    =>  $categories]);
    }

    public function update($id)
    {
        $validator = v::key('title', v::stringType()->notEmpty())
            ->key('description', v::stringType()->notEmpty())
            ->key('category_id', v::intVal())
            ->keyNested('image.tmp_name', v::optional(v::image()));

        $this->validate($validator);
        $photo = $this->database->find('photos', $id);

        $image = $this->imageManager->uploadImage($_FILES['image'], $photo['image']);
        $dimensions = $this->imageManager->getDimensions($image);

        $data = [
            "image" =>  $image,
            "title" =>  $_POST['title'],
            "description" =>  $_POST['description'],
            "category_id" =>  $_POST['category_id'],
            "user_id"   =>  $this->auth->getUserId(),
            "dimensions"    =>  $dimensions
        ];

        $this->database->update('photos', $id, $data);

        flash()->success(['Запись успешно обновлена']);

        return back();
    }

    public function show($id)
    {
        $photo = $this->database->find('photos', $id);
        $user = $this->database->find('users', $photo['user_id']);
        $userImages = $this->database->whereAll('photos', 'user_id', $user['id'], 4);

        echo $this->view->render('photos/show', [
            'photo' =>  $photo,
            'user'  =>  $user,
            'userImages'    =>  $userImages
        ]);
    }

    public function delete($id)
    {
        $photo = $this->database->find('photos', $id);
        if($photo['user_id'] != $this->auth->getUserId()) {
            flash()->error(['Можно редактировать только свои фотографии.']);
            return redirect('/photos');
        }
        $this->imageManager->deleteImage($photo['image']);
        $this->database->delete('photos', $id);

        return back();
    }

    public function download($id)
    {
        $photo = $this->database->find('photos',$id);
        echo $this->view->render('photos/download', [
            'photo' =>  $photo
        ]);
    }



    private function validate($validator)
    {
        try {
            $validator->assert(array_merge($_POST, $_FILES));

        } catch (ValidationException $exception) {
            $exception->findMessages($this->getMessages());
            flash()->error($exception->getMessages());

            return back();
        }
    }

    private function getMessages()
    {
        return [
            'title' => 'Введите название',
            'description'   =>  'Введите описание',
            'category_id'   =>  'Выберите категорию',
            'image' =>  'Неверный формат картинки'
        ];
    }
}