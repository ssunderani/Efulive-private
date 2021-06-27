<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Category;
use App\Subcategory;
use App\Inventory;
use App\Dispatchin;

class DispatchinController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $dispatch = Dispatchin::all();
        return view('dispatchin', ['dispatches' => $dispatch]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|not_in:0',
            'subcategory_id' => 'required|not_in:0',
            'inventory_id' => 'required',
            'dispatchin_date' => 'required',
            'memo' => 'required'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        $fields = array(
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'inventory_id' => $request->inventory_id,
            'dispatchin_date' => $request->dispatchin_date,
            'remarks' => $request->remarks,
            'memo' => $request->memo

        );
        $create = Dispatchin::create($fields);
        if($create){
            $update = Inventory::where('id', $request->inventory_id)->update(['devicetype_id'=>2]);
            return redirect()->back()->with('msg', 'Dispatch In Added Successfully!');
        }
        else{
            return redirect()->back()->with('msg', 'Could not add dispatch in, Try Again!');
        }
    }

    public function show($id)
    {
        $data = array();
        $data['dispatch'] = Dispatchin::find($id);
        $data['categories'] = Category::where('status',1)->orderBy('category_name', 'asc')->get();
        return view('edit_dispatchin', $data);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|not_in:0',
            'subcategory_id' => 'required|not_in:0',
            'inventory_id' => 'required',
            'dispatchin_date' => 'required',
            'memo' => 'required'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        $fields = array(
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'inventory_id' => $request->inventory_id,
            'dispatchin_date' => $request->dispatchin_date,
            'remarks' => $request->remarks,
            'memo' => $request->memo
        );
        $update = Dispatchin::where('id', $id)->update($fields);
        if($update){
            return redirect()->back()->with('msg', 'Dispatch In Updated Successfully!');
        }
        else{
            return redirect()->back()->with('msg', 'Could not update dispatch in, Try Again!');
        }
    }

    public function destroy($id)
    {
        $find = Dispatchin::find($id);
        return $find->delete() ? redirect()->back()->with('msg', 'Dispatch In Deleted Successfully!') : redirect()->back()->with('msg', 'Could not delete dispatch in, Try Again!');
    }
}
