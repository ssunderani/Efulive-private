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
class ExcelController extends Controller
{
    // public function export_budget_summary($year){
    //     return Excel::download(new BudgetExport($year), 'budget.xlsx');
    // }
    public function export_budget($data){
        $filters = json_decode($data);
        $year = Year::find($filters->yearid);
        $category = Category::find($filters->catid);
        return Excel::download(new ItemsExport($data), $category->category_name.'_report_'.$year->year.'.xlsx');
    }
}
