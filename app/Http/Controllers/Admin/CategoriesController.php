<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\WatchCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    public function index() {
        $categories = WatchCategory::orderBy('sort_order')->orderBy('name_es')->get();
        return view('admin.catalog.categories', compact('categories'));
    }
    public function store(Request $request) {
        $v = $request->validate(['name_es'=>'required|string|max:200','name_en'=>'nullable|string|max:200','sort_order'=>'nullable|integer']);
        $slug=Str::slug($v['name_es']); $base=$slug; $i=1;
        while(WatchCategory::where('slug',$slug)->exists()) $slug=$base.'-'.$i++;
        $cat=WatchCategory::create([...$v,'slug'=>$slug,'active'=>true]);
        if(empty($cat->name_en)) { $cat->name_en=BrandsController::aiTranslate($cat->name_es,'en'); $cat->save(); }
        return back()->with('success','Categoría creada.');
    }
    public function update(Request $request, WatchCategory $category) {
        $v=$request->validate(['name_es'=>'required|string|max:200','name_en'=>'nullable|string|max:200','active'=>'nullable','sort_order'=>'nullable|integer']);
        $v['active']=$request->boolean('active');
        $category->update($v);
        return back()->with('success','Categoría actualizada.');
    }
    public function destroy(WatchCategory $category) {
        $category->delete();
        return back()->with('success','Categoría eliminada.');
    }
}
