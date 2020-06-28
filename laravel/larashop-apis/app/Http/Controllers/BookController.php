<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Book;
use App\Http\Resources\Books as BookResourceCollection;
use App\Http\Resources\Book as BookResource;

class BookController extends Controller
{
    public function index()
    {
        $datas = Book::paginate(2);
        return new BookResourceCollection($datas);
    }

    public function top($count)
    {
        $datas = Book::select('*')
                ->orderBy('viewer', 'DESC')
                ->limit($count)
                ->get();
        return new BookResourceCollection($datas);
    }

    public function slug($slug)
    {
        $data = Book::where('slug', $slug)->first();
        $data->viewer = $data->viewer + 1;
        $data->save();
        return new BookResource($data);
    }

    public function search($keyword)
    {
        $data = Book::select('*')
            ->where('title', 'like', "%".$keyword."%")
            ->orderBy('viewer', 'DESC')
            ->get();
        return new BookResourceCollection($data);
    }
}
    