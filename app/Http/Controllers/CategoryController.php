<?php 

namespace App\Http\Controllers;
use App\Category as Category;

use Illuminate\Http\JsonResponse;

class CategoryController extends Controller 
{

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
      public function getCategories()
  {
      $categories = Category::get();

      return $categories;
  }



  
}

?>