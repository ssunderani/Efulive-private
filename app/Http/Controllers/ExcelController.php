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
                $fetch = Inventory::where('category_id', $cat->id)->where('year_id', $data)->get();               
                foreach($fetch as $get){
                    $consumed_price_dollar += $get->item_price/$get->dollar_rate;
                    $consumed_price_pkr += $get->item_price; 
                }
                $cat['unit_price_dollar'] = Budget::where('category_id', $cat->id)->where('year_id', $data)->where('type_id', $type->id)->sum('unit_price_dollar');
                $cat['unit_price_pkr'] = Budget::where('category_id', $cat->id)->where('year_id', $data)->where('type_id', $type->id)->sum('unit_price_pkr');
                $cat['total_price_dollar'] = Budget::where('category_id', $cat->id)->where('year_id', $data)->where('type_id', $type->id)->sum('total_price_dollar');
                $cat['total_price_pkr'] = Budget::where('category_id', $cat->id)->where('year_id', $data)->where('type_id', $type->id)->sum('total_price_pkr');
                $cat['qty'] = Budget::where('category_id', $cat->id)->where('year_id', $data)->where('type_id', $type->id)->sum('qty');
                $cat['consumed'] = Inventory::where('category_id', $cat->id)->where('year_id', $data)->count();
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
                    'item_price' => $inv->item_price,
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
                    'item_price' => $inv->item_price,
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
        foreach($inventories as $inv){
            $inv->added_by = User::find($inv->added_by);
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
                'item_price' => $inv->item_price,
                'itemnature_name' => empty($inv->itemnature)?'':$inv->itemnature->itemnature_name
                
            );
        }
        return Excel::download(new EditlogsExport(json_encode($record)), 'inventoryinreport.xlsx');
    }
}
