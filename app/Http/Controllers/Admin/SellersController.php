<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SellersController extends Controller
{
    public function index(Request $request) {
        $query=User::where('role','seller')->latest();
        if($request->filled('status')) $query->where('status',$request->status);
        if($request->filled('search')) { $s=$request->search; $query->where(fn($q)=>$q->where('name','like',"%$s%")->orWhere('email','like',"%$s%")); }
        $sellers=$query->paginate(25)->withQueryString();
        $counts=['all'=>User::where('role','seller')->count(),'pending'=>User::where('role','seller')->where('status','pending')->count(),'approved'=>User::where('role','seller')->where('status','approved')->count(),'rejected'=>User::where('role','seller')->where('status','rejected')->count()];
        return view('admin.sellers.index',compact('sellers','counts'));
    }
    public function approve(User $user) { $user->update(['status'=>'approved']); return back()->with('success','Vendedor aprobado.'); }
    public function reject(User $user) { $user->update(['status'=>'rejected']); return back()->with('success','Vendedor rechazado.'); }
}
