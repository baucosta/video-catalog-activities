<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    private $rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean'
    ];

    public function index()
    {
        return Category::all();
    }

    public function store(Request $request)
    {
        $this->validate($request, $this->rules);

        // no Models/Category eu defino quais campos podem ser salvos pelo atributo $fillable
        return Category::create($request->all());
    }

    public function show(Category $category)
    {
        return $category;
    }

    public function update(Request $request, Category $category)
    {
        $this->validate($request, $this->rules);
        /*$category->fill($request->all());
        $category->save();*/
        $category->update($request->all());
        return $category;
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->noContent();
        // return response()->json([], 204);
    }
}
