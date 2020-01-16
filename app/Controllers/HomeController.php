<?php
namespace App\Controllers;

use App\Services\Database;
use App\Services\ImageManager;
use Delight\Auth\Auth;
use League\Plates\Engine;

class HomeController extends Controller
{
    public function index()
    {
        $photos = $this->database->all("photos", 8);

        echo $this->view->render('home', ['photos'   =>  $photos]);
    }

    public function category($id)
    {
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $perPage = 8;
        $photos = $this->database->getPaginatedFrom('photos', 'category_id', $id, $page, $perPage);
        $paginator = paginate(
            $this->database->getCount('photos', 'category_id',$id),
            $page,
            $perPage,
            "/category/$id?page=(:num)"
        );
        $category = $this->database->find('categories', $id);

        echo $this->view->render('category',[
            'photos'   =>  $photos,
            'paginator'    =>  $paginator,
            'category'  =>  $category
        ]);
    }

    public function user($id)
    {
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $perPage = 8;

        $photos = $this->database->getPaginatedFrom('photos', 'user_id' ,$id, $page, $perPage);

        $paginator = paginate(
            $this->database->getCount('photos', 'user_id',$id),
            $page,
            $perPage,
            "/user/$id?page=(:num)"
        );

        $user = $this->database->find('users', $id);

        echo $this->view->render('user', [
            'photos' =>  $photos,
            'user'  =>  $user,
            'paginator'    =>  $paginator
        ]);
    }

}