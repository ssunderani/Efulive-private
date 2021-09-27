<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use App\Subcategory;
use App\Grn;
use App\Gin;
use App\Inventory;
use App\Employee;
use App\Category;
use App\Type;
use App\Year;
use App\User;
use App\Budgetitem as Budget;
use App\Issue;
use App\Vendor;
use App\Repairing;
use App\Disposal;
use App\Dispatchin;
use App\Dispatchout;
class PDFController extends Controller
{
    public function generatePDF()
    {
        $data = ['title' => 'Welcome to ItSolutionStuff.com'];
        $pdf = PDF::loadView('myPDF', $data);
  
        return $pdf->download('itsolutionstuff.pdf');
    }
    public function generateGRN($id, $from, $to)
    {
        $range = array('from'=>$from, 'to'=>$to);
        $grn = GRN::where('id',$id)->first();
        $inv = json_decode($grn->inv_id);
        $inventories = array();
        $user = '';
        
        foreach($inv as $inv_id){
            $inventory = Inventory::find($inv_id);
            if($inventory){
            $user = isset($inventory->added_by)?User::find($inventory->added_by):'';
            $inventories[] = $inventory;
            }
        }
        $data = array('inventories'=>$inventories, 'user'=>$user, 'grn_date'=>$grn->created_at, 'range'=>$range);
        $pdf = PDF::loadView('grnreport', $data)->setPaper('a4', 'landscape');
  
        return $pdf->download($grn->grn_no.'.pdf');
    }
    public function generateGIN($id, $from, $to)
    {
        $range = array('from'=>$from, 'to'=>$to);
        $gin = GIN::where('id',$id)->first();
        $inv = json_decode($gin->inv_id);
        $inventories = array();
        $employee = '';
        
        foreach($inv as $inv_id){
            $inventory = Inventory::find($inv_id);
            $employee = Employee::where('emp_code', $inventory->issued_to)->first();
            $inventory->employee = $employee;
            $inventories[] = $inventory;
        }
        $data = array('inventories'=>$inventories, 'employee'=>$employee, 'gin'=>$gin, 'range'=>$range);
        //return view('grnreport', $data);
        $pdf = PDF::loadView('ginreport', $data);
  
        return $pdf->download($gin->gin_no.'.pdf');
    }
    public function budgetexport($data) 
    {
        $budget = Budget::where('year_id', $data)->first();
        
        if(!empty($budget)){
            
            $types = Type::all();
            foreach($types as $type){
            $category = Category::where('status',1)->get();
            foreach($category as $cat){
                $consumed_price_dollar = 0;
                $consumed_price_pkr = 0;
                $remaining_price_dollar = 0;
                $remaining_price_pkr = 0; 
                $fetch = Inventory::where('category_id', $cat->id)->where('year_id', $data)->where('type_id', $type->id)->get();               
                foreach($fetch as $get){
                    $consumed_price_dollar += round($get->item_price)/round($get->dollar_rate);
                    $consumed_price_pkr += round($get->item_price);
                }
                
                $cat['total_price_dollar'] = Budget::where('category_id', $cat->id)->where('year_id', $data)->where('type_id', $type->id)->sum('total_price_dollar');
                $cat['total_price_pkr'] = Budget::where('category_id', $cat->id)->where('year_id', $data)->where('type_id', $type->id)->sum('total_price_pkr');
                $cat['qty'] = Budget::where('category_id', $cat->id)->where('year_id', $data)->where('type_id', $type->id)->sum('qty');
                $cat['consumed'] = Inventory::where('category_id', $cat->id)->where('year_id', $data)->where('type_id', $type->id)->count();
                $cat['consumed_price_dollar'] = $consumed_price_dollar;
                $cat['consumed_price_pkr'] = $consumed_price_pkr;
                $cat['remaining_price_dollar'] = ($cat->total_price_dollar-$consumed_price_dollar);
                $cat['remaining_price_pkr'] = ($cat->total_price_pkr-$consumed_price_pkr);
                $cat['remaining'] = ($cat->qty-$cat->consumed);
                }
            $type->categories = $category;    
            }
        }
        //return $types;
        $year = Year::find($data);
        $pdf = PDF::loadView('summaryreport2', ['types'=>$types, 'year'=>$year->year])->setPaper('a4', 'landscape');
        return $pdf->download('Summaryreport_'.$year->year.'.pdf');
    }
    public function itemexport($data) 
    {
        $filters = json_decode($data);
        $types = Type::all();
        foreach($types as $type){
        $type->budgets = Budget::where('year_id', $filters->yearid)->where('category_id',$filters->catid)->where('type_id',$type->id)->get();
        }
        $year = Year::find($filters->yearid);
        $category = Category::find($filters->catid);
        $pdf = PDF::loadView('itemsreport', ['types'=>$types, 'year'=>$year->year, 'category'=>$category->category_name])->setPaper('a4', 'landscape');
        return $pdf->download($category->category_name.'_report_'.$year->year.'.pdf');
    }
    public function inventoryexport($data) 
    {
            $fields = (array)json_decode($data);
            $key = $fields['inout'][0]; 
            $op = $fields['inout'][1]; 
            $val = $fields['inout'][2];
            unset($fields['inout']); 
            if(isset($fields['from_date']) && isset($fields['to_date'])){
                $from = $fields['from_date'];
                $to = strtotime($fields['to_date'].'+1 day');
                unset($fields['from_date']);
                unset($fields['to_date']);
                $inventories = Inventory::where([[$fields]])->where($key, $op, $val)
                                        ->whereBetween('updated_at', [$from, date('Y-m-d', $to)])
                                        ->whereNotIn('status', [0])
                                        ->orderBy('id', 'desc')->get();
            }
            else if(isset($fields['from_date']) && !isset($fields['to_date'])){
                $from = $fields['from_date'];
                unset($fields['from_date']);
                $inventories = Inventory::where([[$fields]])->where($key, $op, $val)
                                        ->whereBetween('updated_at', [$from, date('Y-m-d', strtotime('+1 day'))])
                                        ->whereNotIn('status', [0])
                                        ->orderBy('id', 'desc')->get();
            }
            else if(!isset($fields['from_date']) && isset($fields['to_date'])){
                $to = strtotime($fields['to_date'].'+1 day');
                unset($fields['to_date']);
                $inventories = Inventory::where([[$fields]])->where($key, $op, $val)
                                        ->whereBetween('updated_at', ['', date('Y-m-d', $to)])
                                        ->whereNotIn('status', [0])
                                        ->orderBy('id', 'desc')->get();
            }
            else{
                $inventories = Inventory::where([[$fields]])->where($key, $op, $val)->whereNotIn('status', [0])->orderBy('id', 'desc')->get();
            }
            foreach($inventories as $inv){
                $user = Employee::where('emp_code', $inv->issued_to)->first();
                if($user){
                    $inv['user'] = $user;
                }
            }
            $pdf = PDF::loadView('inventoryreport', ['inventories'=>$inventories])->setPaper('a4', 'landscape');
            return $pdf->download('inventoryreport.pdf');
    }
    public function balanceexport($data) 
    {
        $fields = (array)json_decode($data);
        $subcategories = Subcategory::where('status',1)->get();
            foreach($subcategories as $subcat){
                $subcat->rem = Inventory::where([[$fields]])->where('subcategory_id', $subcat->id)->where('issued_to', NULL)->count();
                $subcat->out = Inventory::where([[$fields]])->where('subcategory_id', $subcat->id)->whereNotNull('issued_to')->count();
            }
        //return $subcategories;    
        $pdf = PDF::loadView('balanceexport', ['subcategories'=>$subcategories]);
        return $pdf->download('balancereport.pdf');    
    }
    public function editlogsexport($data) 
    {
        date_default_timezone_set('Asia/karachi');
        $fields = (array)json_decode($data);
            if(isset($fields['from_date']) && isset($fields['to_date'])){
                $from = $fields['from_date'];
                $to = strtotime($fields['to_date'].'+1 day');
                unset($fields['from_date']);
                unset($fields['to_date']);
                $inventories = Inventory::where([[$fields]])->whereBetween('updated_at', [$from, date('Y-m-d', $to)])
                                        ->whereNotIn('status', [0])
                                        ->orderBy('id', 'desc')->get();
            }
            else if(isset($fields['from_date']) && !isset($fields['to_date'])){
                $from = $fields['from_date'];
                unset($fields['from_date']);
                $inventories = Inventory::where([[$fields]])->whereBetween('updated_at', [$from, date('Y-m-d', strtotime('+1 day'))])
                                        ->whereNotIn('status', [0])
                                        ->orderBy('id', 'desc')->get();
            }
            else if(!isset($fields['from_date']) && isset($fields['to_date'])){
                $to = strtotime($fields['to_date'].'+1 day');
                unset($fields['to_date']);
                $inventories = Inventory::where([[$fields]])->whereBetween('updated_at', ['', date('Y-m-d', $to)])
                                        ->whereNotIn('status', [0])
                                        ->orderBy('id', 'desc')->get();
            }
            else{
                $inventories = Inventory::where([[$fields]])->whereNotIn('status', [0])->orderBy('id', 'desc')->get();
            }
            $pdf = PDF::loadView('inventorylogsreport', ['inventories'=>$inventories, 'filters'=>$data])->setPaper('a4', 'landscape');
            return $pdf->download('inventory_editlogs_report.pdf');
    }
    public function inventoryinexport($data) 
    {
        date_default_timezone_set('Asia/karachi');
        
            $fields = (array)json_decode($data);
            if(isset($fields['from_date']) && isset($fields['to_date'])){
                $from = $fields['from_date'];
                $to = strtotime($fields['to_date'].'+1 day');
                unset($fields['from_date']);
                unset($fields['to_date']);
                $inventories = Inventory::where([[$fields]])->whereBetween('updated_at', [$from, date('Y-m-d', $to)])
                                        ->whereNotIn('status', [0])
                                        ->orderBy('id', 'desc')->get();
            }
            else if(isset($fields['from_date']) && !isset($fields['to_date'])){
                $from = $fields['from_date'];
                unset($fields['from_date']);
                $inventories = Inventory::where([[$fields]])->whereBetween('updated_at', [$from, date('Y-m-d', strtotime('+1 day'))])
                                        ->whereNotIn('status', [0])
                                        ->orderBy('id', 'desc')->get();
            }
            else if(!isset($fields['from_date']) && isset($fields['to_date'])){
                $to = strtotime($fields['to_date'].'+1 day');
                unset($fields['to_date']);
                $inventories = Inventory::where([[$fields]])->whereBetween('updated_at', ['', date('Y-m-d', $to)])
                                        ->whereNotIn('status', [0])
                                        ->orderBy('id', 'desc')->get();
            }
            else{
                $inventories = Inventory::where([[$fields]])->whereNotIn('status', [0])->orderBy('id', 'desc')->get();
            }
            foreach($inventories as $inv){
                $inv->added_by = User::find($inv->added_by);
            }
            
            $pdf = PDF::loadView('inventoryinreport', ['inventories'=>$inventories, 'filters'=>$data])->setPaper('a4', 'landscape');
            return $pdf->download('inventory_in_report.pdf');
    }
    public function inventoryoutexport($data) 
    {
        date_default_timezone_set('Asia/karachi');
            $fields = (array)json_decode($data);
            $dept_id = $fields['dept_id']??null; 
            unset($fields['dept_id']);
            $key = $fields['inout'][0]; 
            $op = $fields['inout'][1]; 
            $val = $fields['inout'][2];
            unset($fields['inout']); 
            if(isset($fields['from_issuance']) || isset($fields['to_issuance'])){

                if(isset($fields['from_issuance']) && isset($fields['to_issuance'])){
                    $from = $fields['from_issuance'];
                    $to = strtotime($fields['to_issuance'].'+1 day');
                    unset($fields['from_issuance']);
                    unset($fields['to_issuance']);
                    $issue = Issue::whereBetween('updated_at', [$from, date('Y-m-d', $to)])
                                            ->select('inventory_id')
                                            ->orderBy('id', 'desc')->get();
                }
                else if(isset($fields['from_issuance']) && !isset($fields['to_issuance'])){
                    $from = $fields['from_issuance'];
                    unset($fields['from_issuance']);
                    $issue = Issue::whereBetween('updated_at', [$from, date('Y-m-d', strtotime('+1 day'))])
                                            ->select('inventory_id')
                                            ->orderBy('id', 'desc')->get();
                }
                else if(!isset($fields['from_issuance']) && isset($fields['to_issuance'])){
                    $to = strtotime($fields['to_issuance'].'+1 day');
                    unset($fields['to_issuance']);
                    $issue = Issue::whereBetween('updated_at', ['', date('Y-m-d', $to)])
                                            ->select('inventory_id')
                                            ->orderBy('id', 'desc')->get();
                }

                $ids = array();
                foreach($issue as $iss){
                    $ids[] = $iss->inventory_id;
                }
                $inventories = Inventory::where([[$fields]])->where($key, $op, $val)->whereIn('id', $ids)->orderBy('id', 'desc')->get();
            }
            else{
                $inventories = Inventory::where([[$fields]])->where($key, $op, $val)->whereNotIn('status', [0])->orderBy('id', 'desc')->get();
            }
            $items = array();
            foreach($inventories as $inv){
                $inv->user = Employee::where('emp_code', $inv->issued_to)->first();
                $inv->issued_by = User::find($inv->issued_by);
                $inv->issue_date = Issue::where('inventory_id', $inv->id)->select('created_at')->orderBy('id', 'desc')->first();
            if($dept_id == $inv->user->dept_id){
                    $items[] = $inv;
                }
            }
            if($dept_id){
                $inventories = $items;
            }
            $pdf = PDF::loadView('inventoryoutreport', ['inventories'=>$inventories, 'filters'=>$data])->setPaper('a4', 'landscape');
            return $pdf->download('inventory_out_report.pdf');
    }

    public function bincardexport($data) 
    {
        date_default_timezone_set('Asia/karachi');
        $fields = (array)json_decode($data);
            if(isset($fields['from_date']) && isset($fields['to_date'])){
                $from = $fields['from_date'];
                $to = strtotime($fields['to_date'].'+1 day');
                unset($fields['from_date']);
                unset($fields['to_date']);
                $inventories = Inventory::where([[$fields]])->whereBetween('updated_at', [$from, date('Y-m-d', $to)])
                                        ->whereNotIn('status', [0])
                                        ->orderBy('id', 'desc')->get();
            }
            else if(isset($fields['from_date']) && !isset($fields['to_date'])){
                $from = $fields['from_date'];
                unset($fields['from_date']);
                $inventories = Inventory::where([[$fields]])->whereBetween('updated_at', [$from, date('Y-m-d', strtotime('+1 day'))])
                                        ->whereNotIn('status', [0])
                                        ->orderBy('id', 'desc')->get();
            }
            else if(!isset($fields['from_date']) && isset($fields['to_date'])){
                $to = strtotime($fields['to_date'].'+1 day');
                unset($fields['to_date']);
                $inventories = Inventory::where([[$fields]])->whereBetween('updated_at', ['', date('Y-m-d', $to)])
                                        ->whereNotIn('status', [0])
                                        ->orderBy('id', 'desc')->get();
            }
            else{
                $inventories = Inventory::where([[$fields]])->whereNotIn('status', [0])->orderBy('id', 'desc')->get();
            }
            if(!empty($inventories)){
                foreach($inventories as $inventory){
                    $inventory->repairing = Repairing::where('item_id',$inventory->id)->first();
                    $inventory->added_by = User::where('id',$inventory->added_by)->first();
                }
            }
            $pdf = PDF::loadView('bincardreport', ['inventories'=>$inventories, 'filters'=>$data])->setPaper('a4', 'landscape');
            return $pdf->download('bin_card_report.pdf');
    }
    public function repairingexport($data) 
    {
        date_default_timezone_set('Asia/karachi');
        $fields = (array)json_decode($data);
        $repairs = Repairing::where([[$fields]])->orderBy('item_id', 'desc')->get();
        foreach($repairs as $repair){
            $repair->item->user = Employee::where('emp_code', $repair->item->issued_to)->first();
        }
            $pdf = PDF::loadView('repairingreport', ['repairs'=>$repairs])->setPaper('a4', 'landscape');
            return $pdf->download('asset_repairing_report.pdf');
    }
    public function disposalexport($data) 
    {
        date_default_timezone_set('Asia/karachi');
        $fields = (array)json_decode($data);
        if(isset($fields['from_date']) && isset($fields['to_date'])){
            $from = $fields['from_date'];
            $to = strtotime($fields['to_date'].'+1 day');
            unset($fields['from_date']);
            unset($fields['to_date']);
            if(isset($fields['handover'])){
                if($fields['handover'] == 1){
                    $inventories = Disposal::whereBetween('dispose_date', [$from, date('Y-m-d', $to)])
                    ->whereNotNull('handover_date')
                    ->orderBy('id', 'desc')->get();
                }
                else{
                    $inventories = Disposal::whereBetween('dispose_date', [$from, date('Y-m-d', $to)])
                    ->whereNull('handover_date')
                    ->orderBy('id', 'desc')->get();
                }
            }
            else{
            $inventories = Disposal::whereBetween('dispose_date', [$from, date('Y-m-d', $to)])
                                    ->orderBy('id', 'desc')->get();
            }
        }
        else if(isset($fields['from_date']) && !isset($fields['to_date'])){
            $from = $fields['from_date'];
            unset($fields['from_date']);
            $inventories = Disposal::whereBetween('dispose_date', [$from, date('Y-m-d', strtotime('+1 day'))])
                                    ->orderBy('id', 'desc')->get();
        }
        else if(!isset($fields['from_d ate']) && isset($fields['to_date'])){
            $to = strtotime($fields['to_date'].'+1 day');
            unset($fields['to_date']);
            $inventories = Disposal::whereBetween('dispose_date', ['', date('Y-m-d', $to)])
                                    ->orderBy('id', 'desc')->get();
        }
        else{
            if(isset($fields['handover'])){
                if($fields['handover'] == 1){
                    $inventories = Disposal::whereNotNull('handover_date')->orderBy('id', 'desc')->get();
                }
                else{
                    $inventories = Disposal::whereNull('handover_date')->orderBy('id', 'desc')->get();
                }
            }
            else{
                $inventories = Disposal::orderBy('id', 'desc')->get();
            }
        }
            if(!empty($inventories)){
                foreach($inventories as $inventory){
                    $issue = Issue::where('inventory_id', $inventory->inventory_id)->orderBy('id', 'DESC')->first();
                    if($issue){
                        $user = Employee::where('emp_code', $issue->employee_id)->first();
                        if($user){
                            $inventory->user = $user;
                        }
                    }
                
                }
            }
            $pdf = PDF::loadView('disposalreport', ['disposals'=>$inventories, 'filters'=>$data])->setPaper('a4', 'landscape');
            return $pdf->download('disposal_report.pdf');
            // $data = ["disposals"=>$inventories , 'filters'=>$data];
            // return view('disposalreport', $data);
    }
    public function vendor_buyingexport($data) 
    {
        date_default_timezone_set('Asia/karachi');
        $fields = (array)json_decode($data);
        
            if(empty($fields['subcategory_id'])){
                $subcat = Subcategory::where('status',1)->get();
            }
            else{
                $subcat = Subcategory::where('id',$fields['subcategory_id'])->get();
            }
            
            $array = array();
            $i = 0;
            foreach($subcat as $sub){
            
            if(isset($fields['from_date']) && isset($fields['to_date'])){
                $from = $fields['from_date'];
                $to = strtotime($fields['to_date'].'+1 day');
                unset($fields['from_date']);
                unset($fields['to_date']);
                $array[$i]['subcategory'] = $sub->sub_cat_name;
                $array[$i]['vendor'] = Vendor::where('id', $fields['vendor_id'])->select('vendor_name')->first();
                $array[$i]['total_items'] = Inventory::where('subcategory_id',$sub->id)->where('vendor_id',$fields['vendor_id'])->whereBetween('updated_at', [$from, date('Y-m-d', $to)])->whereNotIn('status', [0])->count();
                $array[$i]['amount'] = Inventory::where('subcategory_id',$sub->id)->where('vendor_id',$fields['vendor_id'])->whereBetween('updated_at', [$from, date('Y-m-d', $to)])->whereNotIn('status', [0])->sum('item_price');
                
            }
            else if(isset($fields['from_date']) && !isset($fields['to_date'])){
                $from = $fields['from_date'];
                unset($fields['from_date']);
                $array[$i]['subcategory'] = $sub->sub_cat_name;
                $array[$i]['vendor'] = Vendor::where('id', $fields['vendor_id'])->select('vendor_name')->first();
                $array[$i]['total_items'] = Inventory::where('subcategory_id',$sub->id)->where('vendor_id',$fields['vendor_id'])->whereBetween('updated_at', [$from, date('Y-m-d', strtotime('+1 day'))])->whereNotIn('status', [0])->count();
                $array[$i]['amount'] = Inventory::where('subcategory_id',$sub->id)->where('vendor_id',$fields['vendor_id'])->whereBetween('updated_at', [$from, date('Y-m-d', strtotime('+1 day'))])->whereNotIn('status', [0])->sum('item_price');
                
            }
            else if(!isset($fields['from_d ate']) && isset($fields['to_date'])){
                $to = strtotime($fields['to_date'].'+1 day');
                unset($fields['to_date']);
                $array[$i]['subcategory'] = $sub->sub_cat_name;
                $array[$i]['vendor'] = Vendor::where('id', $fields['vendor_id'])->select('vendor_name')->first();
                $array[$i]['total_items'] = Inventory::where('subcategory_id',$sub->id)->where('vendor_id',$fields['vendor_id'])->whereBetween('updated_at', ['', date('Y-m-d', $to)])->whereNotIn('status', [0])->count();
                $array[$i]['amount'] = Inventory::where('subcategory_id',$sub->id)->where('vendor_id',$fields['vendor_id'])->whereBetween('updated_at', ['', date('Y-m-d', $to)])->whereNotIn('status', [0])->sum('item_price');
                
            }
            else{
                $array[$i]['subcategory'] = $sub->sub_cat_name;
                $array[$i]['vendor'] = Vendor::where('id', $fields['vendor_id'])->select('vendor_name')->first();
                $array[$i]['total_items'] = Inventory::where('subcategory_id',$sub->id)->where('vendor_id',$fields['vendor_id'])->whereNotIn('status', [0])->count();
                $array[$i]['amount'] = Inventory::where('subcategory_id',$sub->id)->where('vendor_id',$fields['vendor_id'])->whereNotIn('status', [0])->sum('item_price');
            }
            if($array[$i]['total_items'] == 0){
                unset($array[$i]);
            }
            $i++;
        }
        $inventories = $array;
        $pdf = PDF::loadView('vendorbuyingreport', ['inventories'=>$inventories, 'filters'=>$data])->setPaper('a4', 'landscape');
        return $pdf->download('vendor_buying_report.pdf');
    }
    public function dispatchinexport($data)
    {
        date_default_timezone_set('Asia/karachi');
        $fields = (array)json_decode($data);
            if(isset($fields['from_date']) && isset($fields['to_date'])){
                $from = $fields['from_date'];
                $to = strtotime($fields['to_date'].'+1 day');
                unset($fields['from_date']);
                unset($fields['to_date']);
                $inventories = Dispatchin::whereBetween('dispatchin_date', [$from, date('Y-m-d', $to)])
                                        ->orderBy('id', 'desc')->get();
            }
            else if(isset($fields['from_date']) && !isset($fields['to_date'])){
                $from = $fields['from_date'];
                unset($fields['from_date']);
                $inventories = Dispatchin::whereBetween('dispatchin_date', [$from, date('Y-m-d', strtotime('+1 day'))])
                                        ->orderBy('id', 'desc')->get();
            }
            else if(!isset($fields['from_d ate']) && isset($fields['to_date'])){
                $to = strtotime($fields['to_date'].'+1 day');
                unset($fields['to_date']);
                $inventories = Dispatchin::whereBetween('dispatchin_date', ['', date('Y-m-d', $to)])
                                        ->orderBy('id', 'desc')->get();
            }
            else{
                $inventories = Dispatchin::orderBy('id', 'desc')->get();
            }
        
        if(!empty($inventories)){
            foreach($inventories as $inventory){
                if(!empty($inventory->inventory)){
                    $user = Employee::where('emp_code', $inventory->inventory->issued_to)->first();
                    if($user){
                        $inventory->user = $user;
                    }
                }
            }
        }
        $pdf = PDF::loadView('dispatchinreport', ['dispatches'=>$inventories, 'filters'=>$data])->setPaper('a4', 'landscape');
        return $pdf->download('dispatchin_report.pdf');
    }
    public function dispatchoutexport($data)
    {
        date_default_timezone_set('Asia/karachi');
        $fields = (array)json_decode($data);
            if(isset($fields['from_date']) && isset($fields['to_date'])){
                $from = $fields['from_date'];
                $to = strtotime($fields['to_date'].'+1 day');
                unset($fields['from_date']);
                unset($fields['to_date']);
                $inventories = Dispatchout::whereBetween('dispatchout_date', [$from, date('Y-m-d', $to)])
                                        ->orderBy('id', 'desc')->get();
            }
            else if(isset($fields['from_date']) && !isset($fields['to_date'])){
                $from = $fields['from_date'];
                unset($fields['from_date']);
                $inventories = Dispatchout::whereBetween('dispatchout_date', [$from, date('Y-m-d', strtotime('+1 day'))])
                                        ->orderBy('id', 'desc')->get();
            }
            else if(!isset($fields['from_d ate']) && isset($fields['to_date'])){
                $to = strtotime($fields['to_date'].'+1 day');
                unset($fields['to_date']);
                $inventories = Dispatchout::whereBetween('dispatchout_date', ['', date('Y-m-d', $to)])
                                        ->orderBy('id', 'desc')->get();
            }
            else{
                $inventories = Dispatchout::orderBy('id', 'desc')->get();
            }
        
        if(!empty($inventories)){
            foreach($inventories as $inventory){
                
                    $user = Employee::where('emp_code', $inventory->inventory->issued_to)->first();
                    if($user){
                        $inventory->user = $user;
                    }
               
            }
        }
        $pdf = PDF::loadView('dispatchoutreport', ['dispatches'=>$inventories, 'filters'=>$data])->setPaper('a4', 'landscape');
        return $pdf->download('dispatchout_report.pdf');
    }
    
    public function reorderexport($data){
        date_default_timezone_set('Asia/karachi');
        $fields = (array)json_decode($data);
        $from = date('Y-m-d', strtotime('-3 months'));
        $to = date('Y-m-d', strtotime('+1 day'));
        $records = array();
        $subcategories = Subcategory::where([[$fields]])->where('status',1)->get();
        foreach($subcategories as $subcategory){
            $items_in_stock = Inventory::where('subcategory_id', $subcategory->id)->where('issued_to', null)->whereNotIn('devicetype_id', [1])->count();
            $subcategory->in_stock = $items_in_stock;
            $subcategory->issued_count = 0;
            $inventories = Inventory::where('subcategory_id', $subcategory->id)->whereNotNull('issued_to')->whereNotIn('devicetype_id', [1])->get();
            foreach($inventories as $inv){
                $subcategory->issued_count += Issue::where('inventory_id', $inv->id)->whereBetween('updated_at', [$from, $to])->count();
            }
        if($items_in_stock <= $subcategory->threshold){
            $records[] = $subcategory;
        }
        }
        $record['reorders'] = $records;
        //return $data;
        $pdf = PDF::loadView('reorderlevel_report', $record);
        return $pdf->download('reorderlevel_report.pdf');
    }
}
