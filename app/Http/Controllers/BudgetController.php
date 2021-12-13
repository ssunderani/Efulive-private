<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BudgetExport;
use App\Exports\ItemsExport;
use App\Category;
use App\Subcategory;
use App\Inventory;
use App\User;
use App\Year;
use App\Dollar;
use App\Type;
use App\Budgetitem as Budget;
use App\Employee;
class BudgetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|not_in:0',
            'sub_cat_id' => 'required|not_in:0',
            'dept_id' => 'required|not_in:0',
            'dept_branch_type' => 'required|not_in:0',
            'type_id' => 'required|not_in:0',
            'year_id' => 'required|not_in:0',
            'unit_dollar' => 'required',
            'unit_pkr' => 'required',
            'qty' => 'required',
            'total_dollar' => 'required',
            'total_pkr' => 'required',
            'budget_nature' => 'required'  
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        $year = Year::where('id',$request->year_id)->first();
        if($year->locked == 1){
            return redirect()->back()->with('msg', 'Sorry, You can not add item in Locked Budget');
        }
        $fields = array(
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'subcategory_id' => $request->sub_cat_id,
            'dept_id' => $request->dept_id,
            'dept_branch_type' => $request->dept_branch_type,
            'department' => $request->department,
            'type_id' => $request->type_id,
            'year_id' => $request->year_id,
            'description' => $request->description,
            'unit_price_dollar' => str_replace(",", "", $request->unit_dollar),
            'unit_price_pkr' => str_replace(",", "", $request->unit_dollar)*str_replace(",", "", $request->unit_pkr),
            'qty' => $request->qty,
            'consumed' => 0,
            'remaining' => $request->qty,
            'total_price_dollar' => str_replace(",", "", $request->total_dollar),
            'total_price_pkr' => str_replace(",", "", $request->total_pkr),
            'remarks' => $request->remarks,
            'budget_nature' => $request->budget_nature
        );
        $create = Budget::create($fields);
        if($create){
            return redirect()->back()->with('msg', 'Budget Item Added Successfully!');
        }
        else{
            return redirect()->back()->with('msg', 'Could not add budget item, Try Again!');
        }
    }

    public function show($id)
    {
        $data = array();
        $budget = Budget::find($id);
        $budget->unit_price_dollar = number_format($budget->unit_price_dollar);
        $budget->unit_price_pkr = number_format($budget->unit_price_pkr);
        $budget->total_price_dollar = number_format($budget->total_price_dollar);
        $budget->total_price_pkr = number_format($budget->total_price_pkr);
        $data['budget'] = $budget;
        $data['categories'] = Category::where('status',1)->orderBy('category_name', 'asc')->get();
        $data['subcategories'] = Subcategory::where('status',1)->orderBy('sub_cat_name', 'asc')->get();
        $data['types'] = Type::orderBy('type', 'asc')->get();
        $data['years'] = Year::where('locked', null)->orderBy('year', 'asc')->get();
        $data['pkr'] = Dollar::where('year_id', $budget->year_id)->first();
        // echo "<pre>";
        // print_r($data);
        return view('edit_budget', $data);
    }

    public function update(Request $request, $id)
    {
        $bd = Budget::find($id);
        if($bd->consumed == 0){
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|not_in:0',
            'sub_cat_id' => 'required|not_in:0',
            'dept_id' => 'required|not_in:0',
            'dept_branch_type' => 'required|not_in:0',
            'type_id' => 'required|not_in:0',
            'year_id' => 'required|not_in:0',
            'unit_dollar' => 'required',
            'unit_pkr' => 'required',
            'qty' => 'required',
            'total_dollar' => 'required',
            'total_pkr' => 'required', 
            'budget_nature' => 'required' 
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $fields = array(
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'subcategory_id' => $request->sub_cat_id,
            'dept_id' => $request->dept_id,
            'dept_branch_type' => $request->dept_branch_type,
            'department' => $request->department,
            'type_id' => $request->type_id,
            'year_id' => $request->year_id,
            'description' => $request->description,
            'unit_price_dollar' => str_replace(",", "", $request->unit_dollar),
            'unit_price_pkr' => str_replace(",", "", $request->unit_dollar)*str_replace(",", "", $request->unit_pkr),
            'qty' => $request->qty,
            'total_price_dollar' => str_replace(",", "", $request->total_dollar),
            'total_price_pkr' => str_replace(",", "", $request->total_pkr),
            'remarks' => $request->remarks,
            'budget_nature' => $request->budget_nature
        );
        if($bd->qty != $request->qty){
            $quantity = 0;
            $rem = 0;
            if($bd->qty < $request->qty){
                $quantity = ($request->qty-$bd->qty);
                $rem = $bd->remaining+$quantity;
            }
            else if($bd->qty > $request->qty){
                $quantity = ($bd->qty-$request->qty);
                $rem = $bd->remaining-$quantity;
            }
            $fields['remaining'] = $rem;
        }
        }
        else{
            $fields = array(
                'user_id' => Auth::id(),
                'description' => $request->description,
                'remarks' => $request->remarks
            );
        }
        $create = Budget::where('id',$id)->update($fields);
        if($create){
            return redirect()->back()->with('msg', 'Budget Item Updated Successfully!');
        }
        else{
            return redirect()->back()->with('msg', 'Could not update budget item, Try Again!');
        }
    }

    public function destroy($id)
    {
        $find = Budget::find($id);
        return $find->delete() ? redirect()->back()->with('msg', 'Budget Deleted Successfully!') : redirect()->back()->with('msg', 'Could not delete budget, Try Again!');
    }
    public function budget_by_year(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|not_in:0',
            'year_id' => 'required|not_in:0'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        $data = array();
        $data['years'] = Year::orderBy('year', 'asc')->get();
        $data['categories'] = Category::where('status',1)->orderBy('category_name', 'asc')->get();
        $data['budgets'] = Budget::where('year_id', $request->year_id)->where('category_id',$request->category_id)->get();
        $data['filter'] = Year::find($request->year_id);
        $data['filters'] = (object)array('catid'=>$request->category_id, 'yearid'=>$request->year_id);
        return view('show_budget', $data);
    }
    public function summary_by_year(Request $request)
    {
        $budget = Budget::where('year_id', $request->year_id)->first();
        
        // if(!empty($budget)){
        //     $types = Type::orderBy('type', 'asc')->get();
        //     foreach($types as $type){
        //     $category = Category::where('status',1)->get();
        //     foreach($category as $cat){  
                

        //         $cat['unit_price_dollar'] = Budget::where('category_id', $cat->id)->where('year_id', $request->year_id)->where('type_id', $type->id)->sum('unit_price_dollar');
        //         $cat['unit_price_pkr'] = Budget::where('category_id', $cat->id)->where('year_id', $request->year_id)->where('type_id', $type->id)->sum('unit_price_pkr');
        //         $cat['total_price_dollar'] = Budget::where('category_id', $cat->id)->where('year_id', $request->year_id)->where('type_id', $type->id)->sum('total_price_dollar');
        //         $cat['total_price_pkr'] = Budget::where('category_id', $cat->id)->where('year_id', $request->year_id)->where('type_id', $type->id)->sum('total_price_pkr');
        //         $cat['consumed'] = Budget::where('category_id', $cat->id)->where('year_id', $request->year_id)->where('type_id', $type->id)->sum('consumed');
        //         $cat['remaining'] = Budget::where('category_id', $cat->id)->where('year_id', $request->year_id)->where('type_id', $type->id)->sum('remaining');
        //         }
        //     $type->categories = $category;    
        //     }
        // }
        // $budget = Budget::where('year_id', $data)->first();
        
        if(!empty($budget)){
            
            $types = Type::all();
            foreach($types as $type){
            $category = Category::where('status',1)->get();
            foreach($category as $cat){
                $consumed_price_dollar = 0;
                $consumed_price_pkr = 0;
                $remaining_price_dollar = 0;
                $remaining_price_pkr = 0; 
                $fetch = Inventory::where('category_id', $cat->id)->where('year_id', $request->year_id)->where('type_id', $type->id)->get();               
                foreach($fetch as $get){
                    $consumed_price_dollar += round($get->item_price)/round($get->dollar_rate);
                    $consumed_price_pkr += round($get->item_price);
                }
                
                $cat['total_price_dollar'] = Budget::where('category_id', $cat->id)->where('year_id', $request->year_id)->where('type_id', $type->id)->sum('total_price_dollar');
                $cat['total_price_pkr'] = Budget::where('category_id', $cat->id)->where('year_id', $request->year_id)->where('type_id', $type->id)->sum('total_price_pkr');
                $cat['qty'] = Budget::where('category_id', $cat->id)->where('year_id', $request->year_id)->where('type_id', $type->id)->sum('qty');
                $cat['consumed'] = Inventory::where('category_id', $cat->id)->where('year_id', $request->year_id)->where('type_id', $type->id)->count();
                $cat['consumed_price_dollar'] = $consumed_price_dollar;
                $cat['consumed_price_pkr'] = $consumed_price_pkr;
                $cat['remaining_price_dollar'] = ($cat->total_price_dollar-$consumed_price_dollar);
                $cat['remaining_price_pkr'] = ($cat->total_price_pkr-$consumed_price_pkr);
                $cat['remaining'] = ($cat->qty-$cat->consumed);
                }
            $type->categories = $category;    
            }
        }
        else{
            $types = array();
        }
        return view('summary', ['filter'=>$request->year_id,'types'=>$types, 'years'=>Year::orderBy('year', 'asc')->get()]);
    }

    public function summary_by_year2(Request $request)
    {
        $budget = Budget::where('year_id', $request->year_id)->first();        
        if(!empty($budget)){
            
            $types = Type::all();
            foreach($types as $type){
            $category = Category::where('status',1)->get();
            foreach($category as $cat){
                $consumed_price_dollar = 0;
                $consumed_price_pkr = 0;
                $remaining_price_dollar = 0;
                $remaining_price_pkr = 0; 
                $fetch = Inventory::where('category_id', $cat->id)->where('year_id', $request->year_id)->where('type_id', $type->id)->get();               
                foreach($fetch as $get){
                    $consumed_price_dollar += round($get->item_price)/round($get->dollar_rate);
                    $consumed_price_pkr += round($get->item_price);
                }
                
                $cat['total_price_dollar'] = Budget::where('category_id', $cat->id)->where('year_id', $request->year_id)->where('type_id', $type->id)->sum('total_price_dollar');
                $cat['total_price_pkr'] = Budget::where('category_id', $cat->id)->where('year_id', $request->year_id)->where('type_id', $type->id)->sum('total_price_pkr');
                $cat['qty'] = Budget::where('category_id', $cat->id)->where('year_id', $request->year_id)->where('type_id', $type->id)->sum('qty');
                $cat['consumed'] = Inventory::where('category_id', $cat->id)->where('year_id', $request->year_id)->where('type_id', $type->id)->count();
                $cat['consumed_price_dollar'] = $consumed_price_dollar;
                $cat['consumed_price_pkr'] = $consumed_price_pkr;
                $cat['remaining_price_dollar'] = ($cat->total_price_dollar-$consumed_price_dollar);
                $cat['remaining_price_pkr'] = ($cat->total_price_pkr-$consumed_price_pkr);
                $cat['remaining'] = ($cat->qty-$cat->consumed);
                }
            $type->categories = $category;    
            }
        }
        else{
            $types = array();
        }
        return view('summary2', ['filter'=>$request->year_id,'types'=>$types, 'years'=>Year::orderBy('year', 'asc')->get()]);
    }
    public function lock_budget($id)
    {
        $budget = Budget::where('year_id', $id)->first();
        
        if(!empty($budget)){
            $year = Year::where('id',$id)->update(['locked'=>1]);
            if($year){
                return redirect()->back()->with('msg', 'Budget Locked Successfully!');
            }
            else{
                return redirect()->back()->with('msg', 'Could not lock budget, Try Again!');
            }
        }
        else{
            return redirect()->back()->with('msg', 'No any budget found in selected year, Kindly add budget and try again!');
        }    
    }

    public function get_budget_items($inv_id,$dept_id){
        $inv = Inventory::find($inv_id);
        $budgets = Budget::where('year_id', $inv->year_id)
                    ->where('category_id',$inv->category_id)
                    ->where('subcategory_id',$inv->subcategory_id)
                    ->where('dept_id',$dept_id)
                    ->where('remaining', '>', 0)
                    ->get();

        //return [$inv,$budgets];            
        return count($budgets)>0?$budgets:'0';
        //return view('get_budget_items', ['budgets'=>$budgets]);
    }
    public function budget_transfer(){
        $record = Year::all();
        $from = array();
        $to = array();
        foreach($record as $val){
            $budget = Budget::where('year_id', $val->id)->count();
            if($budget>0){
                $from[] = $val;
            }
            else{
                $to[] = $val;
            }
        }
        return view('budget_transfer', ['swap_from'=>$from, 'swap_to'=>$to]);
    }
    public function transfered(Request $request){
        $validator = Validator::make($request->all(), [
            'from_year_id' => 'required|not_in:0',
            'to_year_id' => 'required|not_in:0'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        $id = $request->from_year_id;
        $to = $request->to_year_id;
        $budgets = Budget::where('year_id', $id)->get();
        foreach($budgets as $budget){
            $fields = array(
                'user_id' => $budget->user_id,
                'category_id' => $budget->category_id,
                'subcategory_id' => $budget->subcategory_id,
                'type_id' => $budget->type_id,
                'dept_id' => $budget->dept_id,
                'dept_branch_type' => $budget->dept_branch_type,
                'department' => $budget->department,
                'year_id' => $to,
                'description' => $budget->description,
                'remarks' => $budget->remarks,
                'unit_price_dollar' => $budget->unit_price_dollar,
                'unit_price_pkr' => $budget->unit_price_pkr,
                'qty' => $budget->qty,
                'consumed' => $budget->consumed,
                'remaining' => $budget->remaining,
                'total_price_dollar' => $budget->total_price_dollar,
                'total_price_pkr' => $budget->total_price_pkr,            
                'budget_nature' => $budget->budget_nature
            );
            $create = Budget::create($fields);
        }
        if($create){
            return redirect()->back()->with('msg', 'Budget Transferred Successfully!');
        }
        else{
            return redirect()->back()->with('msg', 'Could not Transfer budget , Try Again!');
        }
    }
    public function swapping(){
       
        $data = array();
        $data['categories'] = Category::where('status',1)->orderBy('category_name', 'asc')->get();
        $data['subcategories'] = Subcategory::where('status',1)->orderBy('sub_cat_name', 'asc')->get();
        $data['types'] = Type::orderBy('type', 'asc')->get();
        $data['years'] = Year::where('locked', null)->orderBy('year', 'asc')->get();
        
        return view('swapping',$data);

    }

    public function swapping2(Request $request){

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|not_in:0',
            'sub_cat_id' => 'required|not_in:0',
            'from_dept' => 'required|not_in:0',
            'to_dept' => 'required|not_in:0',            
            'year_id' => 'required|not_in:0',            
            'qty' => 'required'
            
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        $qty = $request->qty;
        $from = Budget::where('year_id', $request->year_id)->where('category_id',$request->category_id)->where('subcategory_id',$request->sub_cat_id)->where('dept_id',$request->from_dept)->first();
        $to = Budget::where('year_id', $request->year_id)->where('category_id',$request->category_id)->where('subcategory_id',$request->sub_cat_id)->where('dept_id',$request->to_dept)->first();
        if($from && $to){
            if($qty > $from->remaining){
                return redirect()->back()->with('msg', 'Requested quantity must be less then or equal to available quantity!');
            }
            $from_qty = $from->qty-$qty;
            $from_remaining = $from->remaining-$qty;
            $to_qty = $to->qty+$qty;
            $to_remaining = $to->remaining+$qty;

            $from_fields = array('qty' => $from_qty, 'remaining' => $from_remaining);
            $from_update = Budget::where('id',$from->id)->update($from_fields);

            $to_fields = array('qty' => $to_qty, 'remaining' => $to_remaining);
            $to_update = Budget::where('id',$to->id)->update($to_fields);

            if($from_update && $to_update){
                return redirect()->back()->with('msg', 'Budget Swapped Successfully!');
            }
            else{
                return redirect()->back()->with('msg', 'Could not swap budget, Try Again!');
            }
        }
        else{
            return redirect()->back()->with('msg', 'Budget not available!');
        }

    }
    public function get_budget(Request $request){
        $from = Budget::where('year_id', $request->year_id)->where('category_id',$request->category_id)->where('subcategory_id',$request->sub_cat_id)->where('dept_id',$request->from_dept)->first();
        return $from;
    }
    public function budgetdetails($cat_id, $type_id, $year_id){
        // $budget = Budget::find($id);
        //return $budget->category_id.' : '.$budget->year_id.' : '.$budget->type_id;
        $inventories = Inventory::where('category_id', $cat_id)->where('year_id', $year_id)->where('type_id', $type_id)->get();               
        foreach($inventories as $inv){
            $user = Employee::where('emp_code', $inv->issued_to)->first();
            if($user){
                $inv['user'] = $user;
            }
        }
        return view('budgetdetails', ['inventories'=>$inventories]);        
    }
}