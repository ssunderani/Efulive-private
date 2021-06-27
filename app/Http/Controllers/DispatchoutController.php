<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Category;
use App\Subcategory;
use App\Inventory;
use App\Dispatchout;

class DispatchoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $dispatch = Dispatchout::all();
        return view('dispatchout', ['dispatches' => $dispatch]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|not_in:0',
            'subcategory_id' => 'required|not_in:0',
            'inventory_id' => 'required',
            'dispatchout_date' => 'required',
            'insured' => 'required'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        $fields = array(
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'inventory_id' => $request->inventory_id,
            'dispatchout_date' => $request->dispatchout_date,
            'remarks' => $request->remarks,
            'insured' => $request->insured

        );
        $create = Dispatchout::create($fields);
        if($create){
            $update = Inventory::where('id', $request->inventory_id)->update(['devicetype_id'=>3]);
            return redirect()->back()->with('msg', 'Dispatch Out Added Successfully!');
        }
        else{
            return redirect()->back()->with('msg', 'Could not add dispatch out, Try Again!');
        }
    }

    public function show($id)
    {
        $data = array();
        $data['dispatch'] = Dispatchout::find($id);
        $data['categories'] = Category::where('status',1)->orderBy('category_name', 'asc')->get();
        return view('edit_dispatchout', $data);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|not_in:0',
            'subcategory_id' => 'required|not_in:0',
            'inventory_id' => 'required',
            'dispatchout_date' => 'required',
            'insured' => 'required'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        $fields = array(
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'inventory_id' => $request->inventory_id,
            'dispatchout_date' => $request->dispatchout_date,
            'remarks' => $request->remarks,
            'insured' => $request->insured
        );
        $update = Dispatchout::where('id', $id)->update($fields);
        if($update){
            return redirect()->back()->with('msg', 'Dispatch Out Updated Successfully!');
        }
        else{
            return redirect()->back()->with('msg', 'Could not update dispatch out, Try Again!');
        }
    }

    public function destroy($id)
    {
        $find = Dispatchout::find($id);
        return $find->delete() ? redirect()->back()->with('msg', 'Dispatch Out Deleted Successfully!') : redirect()->back()->with('msg', 'Could not delete dispatch out, Try Again!');
    }
}
