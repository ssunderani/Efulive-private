<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BudgetExport;
use App\Exports\ItemsExport;
use App\Exports\InventoryExport;
use App\Exports\EditlogsExport;
use App\Exports\InventoryinExport;
use App\Exports\InventoryoutExport;
use App\Exports\BalanceExport;
use App\Exports\BincardExport;
use App\Exports\AssetrepairingExport;
use App\Exports\DisposalExport;
use App\Exports\DispatchinExport;
use App\Exports\DispatchoutExport;
use App\Exports\VendorbuyingExport;
use App\Exports\ReorderlevelExport;
class ExcelController extends Controller
{
    
    public function export_budget($data){
        $filters = json_decode($data);
        $year = Year::find($filters->yearid);
        $category = Category::find($filters->catid);
        return Excel::download(new ItemsExport($data), $category->category_name.'_report_'.$year->year.'.xlsx');
    }
    public function export_summary($data) 
    {
        $budget = Budget::where('year_id', $data)->first();
        $record = array();
        if(!empty($budget)){
$grand_u_d = 0;
$grand_u_p = 0; 
$grand_t_d = 0; 
$grand_t_p = 0;
$grand_qty = 0; 
$grand_c = 0; 
$grand_r = 0;
$grand_c_u_d = 0;
$grand_c_u_p = 0; 
$grand_r_t_d = 0; 
$grand_r_t_p = 0; 
            $types = Type::all();
            foreach($types as $type){
            $unit_b_d = 0;
            $unit_b_p = 0;
            $total_b_d = 0;
            $total_b_p = 0;
            $t_qty = 0;
            $c = 0;
            $r = 0;
            $c_b_d = 0;
            $c_b_p = 0;
            $r_b_d = 0;
            $r_b_p = 0;
            $record[] = (object)array('','','','','',$type->type,'','','','','','');    
            $category = Category::where('status',1)->get();
            foreach($category as $cat){
                $consumed_price_dollar = 0;
                $consumed_price_pkr = 0;
                $remaining_price_dollar = 0;
                $remaining_price_pkr = 0; 
                $fetch = Inventory::where('category_id', $cat->id)->where('year_id', $data)->where('type_id', $type->id)->get();               
                foreach($fetch as $get){
                    $consumed_price_dollar += round($get->item_price)/$get->dollar_rate;
                    $consumed_price_pkr += round($get->item_price); 
                }
                $cat['unit_price_dollar'] = Budget::where('category_id', $cat->id)->where('year_id', $data)->where('type_id', $type->id)->sum('unit_price_dollar');
                $cat['unit_price_pkr'] = Budget::where('category_id', $cat->id)->where('year_id', $data)->where('type_id', $type->id)->sum('unit_price_pkr');
                $cat['total_price_dollar'] = Budget::where('category_id', $cat->id)->where('year_id', $data)->where('type_id', $type->id)->sum('total_price_dollar');
                $cat['total_price_pkr'] = Budget::where('category_id', $cat->id)->where('year_id', $data)->where('type_id', $type->id)->sum('total_price_pkr');
                $cat['qty'] = Budget::where('category_id', $cat->id)->where('year_id', $data)->where('type_id', $type->id)->sum('qty');
                $cat['consumed'] = Inventory::where('category_id', $cat->id)->where('year_id', $data)->where('type_id', $type->id)->count();
                $cat['consumed_price_dollar'] = $consumed_price_dollar;
                $cat['consumed_price_pkr'] = $consumed_price_pkr;
                $cat['remaining_price_dollar'] = ($cat->total_price_dollar-$consumed_price_dollar);
                $cat['remaining_price_pkr'] = ($cat->total_price_pkr-$consumed_price_pkr);
                $cat['remaining'] = ($cat->qty-$cat->consumed);

                $unit_b_d += $cat->unit_price_dollar;
                $unit_b_p += $cat->unit_price_pkr;
                $total_b_d += $cat->total_price_dollar;
                $total_b_p += $cat->total_price_pkr;
                $t_qty += $cat->qty;
                $c += $cat->consumed;
                $r += $cat->remaining;
                $c_b_d += $cat->consumed_price_dollar;
                $c_b_p += $cat->consumed_price_pkr;
                $r_b_d += $cat->remaining_price_dollar;
                $r_b_p += $cat->remaining_price_pkr;
                
                unset($cat->id);
                unset($cat->threshold);
                unset($cat->status);
                unset($cat->created_at);
                unset($cat->updated_at);
                $record[] = $cat;    
            }
            $record[] = (object)array('Total',$unit_b_d,$unit_b_p,$total_b_d,$total_b_p,$t_qty,$c,$c_b_d,$c_b_p,$r_b_d,$r_b_p,$r);   
            $grand_u_d += $unit_b_d;
            $grand_u_p += $unit_b_p; 
            $grand_t_d += $total_b_d; 
            $grand_t_p += $total_b_p;
            $grand_qty += $t_qty; 
            $grand_c += $c; 
            $grand_r += $r;
            $grand_c_u_d += $c_b_d;
            $grand_c_u_p += $c_b_p; 
            $grand_r_t_d += $r_b_d; 
            $grand_r_t_p += $r_b_p;   
            }

            $record[] = (object)array('Grand Total',$grand_u_d,$grand_u_p,$grand_t_d,$grand_t_p,$grand_qty,$grand_c,$grand_c_u_d,$grand_c_u_p,$grand_r_t_d,$grand_r_t_p,$grand_r);   
            
        }
        $year = Year::find($data);
        return Excel::download(new BudgetExport(json_encode($record)), 'Summaryreport_'.$year->year.'.xlsx');
    }
    public function export_inventory($data){
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
            $record = array();
            foreach($inventories as $inv){
                $user = Employee::where('emp_code', $inv->issued_to)->first();
                if($user){
                    $inv['user'] = $user;
                }
                $record[] = (object)array(
                    'make' => $inv->make_id?$inv->make->make_name:'',
                    'model' => $inv->model_id?$inv->model->model_name:'',
                    'product_sn' => $inv->product_sn,
                    'purchase_date' => date('d-M-Y' ,strtotime($inv->purchase_date)),
                    'subcategory' => empty($inv->subcategory)?'':$inv->subcategory->sub_cat_name,
                    'item_price' => round($inv->item_price),
                    'user' => empty($inv->user)?'':$inv->user->name,
                    'location' => empty($inv->location)?'':$inv->location->location,
                    'inventorytype' => empty($inv->inventorytype)?'':$inv->inventorytype->inventorytype_name,
                    'devicetype' => empty($inv->devicetype)?'':$inv->devicetype->devicetype_name,
                    'remarks' => $inv->remarks
                );
            }
            
        return Excel::download(new InventoryExport(json_encode($record)), 'inventoryreport.xlsx');
    }
    public function export_editlogs($data){
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
            $record = array();
            foreach($inventories as $inv){
                $record[] = (object)array(
                    'subcategory' => empty($inv->subcategory)?'':$inv->subcategory->sub_cat_name,
                    'product_sn' => $inv->product_sn,
                    'make' => $inv->make_id?$inv->make->make_name:'',
                    'model' => $inv->model_id?$inv->model->model_name:'',
                    'purchase_date' => date('d-M-Y' ,strtotime($inv->purchase_date)),
                    'po_number' => $inv->po_number,
                    'vendor' => empty($inv->vendor)?'':$inv->vendor->vendor_name,
                    'warrenty_period' => $inv->warrenty_period,
                    'remarks' => $inv->remarks,
                    'item_price' => round($inv->item_price),
                    'itemnature_name' => empty($inv->itemnature)?'':$inv->itemnature->itemnature_name
                    
                );
            }
            
            return Excel::download(new EditlogsExport(json_encode($record)), 'inventoryeditlogsreport.xlsx');
    }

    public function export_inventoryin($data){
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
        $record = array();
        foreach($inventories as $inv){
            $inv->added_by = User::find($inv->added_by);
            $record[] = (object)array(
                'subcategory' => empty($inv->subcategory)?'':$inv->subcategory->sub_cat_name,
                'product_sn' => $inv->product_sn,
                'make' => $inv->make_id?$inv->make->make_name:'',
                'model' => $inv->model_id?$inv->model->model_name:'',
                'item_price' => round($inv->item_price),
                'po_number' => $inv->po_number,
                'dc_number' => '',
                'vendor' => empty($inv->vendor)?'':$inv->vendor->vendor_name,
                'initial_status' => empty($inv->inventorytype)?'':$inv->inventorytype->inventorytype_name,
                'current_condition' => empty($inv->devicetype)?'':$inv->devicetype->devicetype_name,
                'remarks' => $inv->remarks,
                'enter_by' => empty($inv->added_by)?'':$inv->added_by->name
                
            );
        }
        return Excel::download(new InventoryinExport(json_encode($record)), 'inventoryinreport.xlsx');
    }
    public function export_inventoryout($data){
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
            $record = array();
        foreach($inventories as $inv){
            $record[] = (object)array(
                'subcategory' => empty($inv->subcategory)?'':$inv->subcategory->sub_cat_name,
                'product_sn' => $inv->product_sn,
                'make' => $inv->make_id?$inv->make->make_name:'',
                'model' => $inv->model_id?$inv->model->model_name:'',
                'issued_to' => empty($inv->user)?'':$inv->user->name,
                'location' => empty($inv->location)?'':$inv->location->location,
                'issued_by' => empty($inv->issued_by)?'':$inv->issued_by->name,
                'issued_date' => empty($inv->issue_date)?'':date('d-M-Y' ,strtotime($inv->issue_date->created_at)),
                'initial_status' => empty($inv->inventorytype)?'':$inv->inventorytype->inventorytype_name,
                'current_condition' => empty($inv->devicetype)?'':$inv->devicetype->devicetype_name,
                'remarks' => $inv->remarks,
            );
        }
        return Excel::download(new InventoryoutExport(json_encode($record)), 'inventoryoutreport.xlsx');
    }
    public function export_balance($data){
        $fields = (array)json_decode($data);
        $record = array();
        $subcategories = Subcategory::where('status',1)->get();
            foreach($subcategories as $subcat){
                $subcat->rem = Inventory::where([[$fields]])->where('subcategory_id', $subcat->id)->where('issued_to', NULL)->count();
                $subcat->out = Inventory::where([[$fields]])->where('subcategory_id', $subcat->id)->whereNotNull('issued_to')->count();
                $record[] = (object)array(
                    'category' => $subcat->category->category_name??'',
                    'subcategory' => $subcat->sub_cat_name,
                    'in' => ($subcat->rem+$subcat->out),
                    'out' => $subcat->out,
                    'balance' => $subcat->rem
                );
            }
        return Excel::download(new BalanceExport(json_encode($record)), 'balancereport.xlsx');
    }
    public function export_bincard($data){
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
            $record = array();
            if(!empty($inventories)){
                foreach($inventories as $inv){
                    $inv->repairing = Repairing::where('item_id',$inv->id)->first();
                    $inv->added_by = User::where('id',$inv->added_by)->first();
                    $record[] = (object)array(
                        'subcategory' => empty($inv->subcategory)?'':$inv->subcategory->sub_cat_name,
                        'product_sn' => $inv->product_sn,
                        'make' => $inv->make_id?$inv->make->make_name:'',
                        'model' => $inv->model_id?$inv->model->model_name:'',
                        'location' => empty($inv->location)?'':$inv->location->location,
                        'initial_status' => empty($inv->inventorytype)?'':$inv->inventorytype->inventorytype_name,
                        'remarks' => $inv->remarks,
                        'action_date' => date('Y-m-d', strtotime($inv->updated_at)),
                        'actual_price' => number_format(round($inv->item_price),2),
                        'cost_price' => empty($inv->repairing)?'':$inv->repairing->price_value,
                        'repaiting_remarks' => empty($inv->repairing)?'':$inv->repairing->remarks
                    );
                }
            }
            return Excel::download(new BincardExport(json_encode($record)), 'bincardreport.xlsx');
    }
    public function export_assetrepairing($data){
        date_default_timezone_set('Asia/karachi');
        $fields = (array)json_decode($data);
        $repairs = Repairing::where([[$fields]])->orderBy('item_id', 'desc')->get();
        $record = array();
        foreach($repairs as $repair){
            $total = $repair->actual_price_value+$repair->price_value;
            $repair->item->user = Employee::where('emp_code', $repair->item->issued_to)->first();
            $record[] = (object)array(
                'subcategory' => empty($repair->subcategory)?'':$repair->subcategory->sub_cat_name,
                'product_sn' => empty($repair->item)?'':$repair->item->product_sn,
                'make' => empty($repair->item->make)?'':$repair->item->make->make_name,
                'model' => empty($repair->item->model)?'':$repair->item->model->model_name,
                'issued_to' => empty($repair->item->user)?'':$repair->item->user->name,
                'location' => empty($repair->item->location)?'':$repair->item->location->location,
                'repairing_date' => date('d-M-Y' ,strtotime($repair->date)),
                'actual_price' => number_format($repair->actual_price_value,2),
                'repairing_cost' => number_format($repair->price_value,2),
                'cumulative_cost' => number_format($total,2),
                'initial_status' => empty($repair->item->inventorytype)?'':$repair->item->inventorytype->inventorytype_name,
                'current_condition' => empty($repair->item->devicetype)?'':$repair->item->devicetype->devicetype_name,
                'remarks' => $repair->remarks
            );
        }
        return Excel::download(new AssetrepairingExport(json_encode($record)), 'assetrepairingreport.xlsx');    
    }
    public function export_disposal($data){
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
        $record = array();
            if(!empty($inventories)){
                foreach($inventories as $inv){
                    $issue = Issue::where('inventory_id', $inv->inventory_id)->orderBy('id', 'DESC')->first();
                    if($issue){
                        $user = Employee::where('emp_code', $issue->employee_id)->first();
                        if($user){
                            $inv->user = $user;
                        }
                    }
                    $record[] = (object)array(
                        'subcategory' => !empty($inv->subcategory)?$inv->subcategory->sub_cat_name:'',
                        'location' => !empty($inv->inventory->location)?$inv->inventory->location->location:'',
                        'product_sn' => !empty($inv->inventory)?$inv->inventory->product_sn:'',
                        'disposal_status' => !empty($inv->disposalstatus)?$inv->disposalstatus->d_status:'',
                        'purchase_date' => !empty($inv->inventory)?date('d-M-Y', strtotime($inv->inventory->purchase_date)):'',
                        'disposal_date' => date('d-M-Y' ,strtotime($inv->dispose_date)),
                        'handover_date' => $inv->handover_date == null?'Null':date('d-M-Y' ,strtotime($inv->handover_date)),
                        'remarks' => $inv->remarks
                    );
                }
            }
            return Excel::download(new DisposalExport(json_encode($record)), 'assetdisposalreport.xlsx'); 
    }
    public function export_dispatchin($data){
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
            $record = array();
        if(!empty($inventories)){
            foreach($inventories as $inv){
                if(!empty($inv->inventory)){
                    $user = Employee::where('emp_code', $inv->inventory->issued_to)->first();
                    if($user){
                        $inv->user = $user;
                    }
                }
                $record[] = (object)array(
                    'date_in' => date('d-M-Y', strtotime($inv->dispatchin_date)),
                    'subcategory' => !empty($inv->subcategory)?$inv->subcategory->sub_cat_name:'',
                    'product_sn' => !empty($inv->inventory)?$inv->inventory->product_sn:'',
                    'assigned_to' => !empty($inv->user)?$inv->user->name:'',
                    'branch' => !empty($inv->user)?$inv->user->branch:'',
                    'br_code' => !empty($inv->user)?$inv->user->branch_id:'',
                    'make' => !empty($inv->inventory->make)?$inv->inventory->make->make_name:'',
                    'model' => !empty($inv->inventory->model)?$inv->inventory->model->model_name:'',
                    'accessories' => !empty($inv->inventory)?$inv->inventory->other_accessories:''
                );
            }
        }
        return Excel::download(new DispatchinExport(json_encode($record)), 'dispatchinreport.xlsx'); 
    }
    public function export_dispatchout($data){
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
            $record = array();
        if(!empty($inventories)){
            foreach($inventories as $inv){
                if(!empty($inv->inventory)){
                    $user = Employee::where('emp_code', $inv->inventory->issued_to)->first();
                    if($user){
                        $inv->user = $user;
                    }
                }
                $record[] = (object)array(
                    'date_out' => date('d-M-Y', strtotime($inv->dispatchout_date)),
                    'subcategory' => !empty($inv->subcategory)?$inv->subcategory->sub_cat_name:'',
                    'product_sn' => !empty($inv->inventory)?$inv->inventory->product_sn:'',
                    'branch' => !empty($inv->user)?$inv->user->branch:'',
                    'br_code' => !empty($inv->user)?$inv->user->branch_id:'',
                    'insured' => $inv->insured,
                    'cost' => !empty($inv->inventory)?number_format($inv->inventory->item_price,2):''
                );
            }
        }
        
        return Excel::download(new DispatchoutExport(json_encode($record)), 'dispatchoutreport.xlsx'); 
    }
    public function export_vendorbuying($data){
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
        $record = array();
        if(!empty($inventories)){
            foreach($inventories as $inventory){
                $record[] = (object)array(
                    'subcategory' => $inventory['subcategory'],
                    'vendor' => $inventory['vendor']->vendor_name,
                    'total_items' => number_format($inventory['total_items'],2),
                    'amount' => number_format(round($inventory['amount']),2),
                );
            }
        }
        return Excel::download(new VendorbuyingExport(json_encode($record)), 'vendorbuyingreport.xlsx'); 
    }
    public function export_reorderlevel($data){
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
        $record = array();
        foreach($records as $reorder){
            $record[] = (object)array(
                'subcategory' => $reorder->sub_cat_name,
                'threshold' => $reorder->threshold,
                'in_stock' => $reorder->in_stock,
                'issued_count' => $reorder->issued_count
            );
        }
        return Excel::download(new ReorderlevelExport(json_encode($record)), 'reorderlevelreport.xlsx');
    }
}
