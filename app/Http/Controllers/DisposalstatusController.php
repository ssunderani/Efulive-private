<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Disposalstatus;
class DisposalstatusController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $d_status = Disposalstatus::all();
        return view('disposalstatus', ['d_statuses' => $d_status]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'd_status' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        $create = Disposalstatus::create($request->all());
        if($create){
            return redirect()->back()->with('msg', 'Disposalstatus Added Successfully!');
        }
        else{
            return redirect()->back()->with('msg', 'Could not add disposalstatus, Try Again!');
        }
    }

    public function show($id)
    {
        $d_status = Disposalstatus::find($id);
        return view('edit_disposalstatus', ['d_status'=> $d_status]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'd_status' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $update = Disposalstatus::where('id', $id)->update(['d_status'=>$request->d_status]);
        if($update){
            return redirect()->back()->with('msg', 'Disposalstatus Updated Successfully!');
        }
        else{
            return redirect()->back()->with('msg', 'Could not update disposalstatus, Try Again!');
        }
    }

    public function destroy($id)
    {
        $find = Disposalstatus::find($id);
        return $find->delete() ? redirect()->back()->with('msg', 'Disposalstatus Deleted Successfully!') : redirect()->back()->with('msg', 'Could not delete disposalstatus, Try Again!');
    }
}
