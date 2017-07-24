<?php 

namespace App\Http\Controllers;

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

      return new JsonResponse(['categories' => $categories]);
  }



  
}

?>