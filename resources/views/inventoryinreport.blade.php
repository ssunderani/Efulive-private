<!DOCTYPE html>
<html>
<head>
	<title>inventory report</title>
    <style>
    .secondary-table{
        width:100%;
        border-spacing: 0px;
    }
    .secondary-table tr th, .secondary-table tr td{
        border: 1px solid;
        font-size: 14px;
    }
    .text-center{
                text-align: center;
            }
    .font-14{
        font-size: 14px;
    } 
    </style>
</head>
<body>
<table cellpadding="0" cellspacing="0" style="width:100%;">
            <tr class="text-center">
                <td class="text-center">
                    <h2><u>Inventory IN Report</u><span class="font-14"><b> - <u>EFU Life Assurance Ltd.</u></b></span></h2>
                </td>
            </tr>
        </table>  <br> 
                                    <table class="secondary-table">
                                    <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Item Category</th>
                                                <th>Product S#</th>
                                                <th>Make</th>
                                                <th>Model</th>
                                                <th>Price</th>
                                                <th>PO Number</th>
                                                <th>DC No</th>
                                                <th>Vendor</th>
                                                <th>Initial Status</th>
                                                <th>Current Condition</th>
                                                <th>Remarks</th>
                                                <th>Enter By</th>
                                            </tr>
                                        </thead>
                                        
                                        <tbody>
                                        <?php $i = 1; ?>
                                        @foreach ($inventories as $inventory)
                                            <tr>
                                            <td class='text-align-right'>{{ $i++ }}</td>
                                                <td>{{ $inventory->subcategory_id?$inventory->subcategory->sub_cat_name:'' }}</td>
                                                <td>{{ $inventory->product_sn }}</td>
                                                <td>{{ $inventory->make_id?$inventory->make->make_name:'' }}</td>
                                                <td>{{ $inventory->model_id?$inventory->model->model_name:'' }}</td>
                                                <td class='text-align-right'>{{ number_format($inventory->item_price,2) }}</td>
                                                <td>{{ $inventory->po_number }}</td>
                                                <td></td>
                                                <td>{{ empty($inventory->vendor)?'':$inventory->vendor->vendor_name }}</td>
                                                <td>{{ empty($inventory->inventorytype)?'':$inventory->inventorytype->inventorytype_name }}</td>
                                                <td>{{ empty($inventory->devicetype)?'':$inventory->devicetype->devicetype_name }}</td>
                                                <td>{{ $inventory->remarks }}</td>
                                                <td>{{ empty($inventory->added_by)?'':$inventory->added_by->name }}</td>
                                            </tr>
                                        @endforeach 
                                        </tbody>
                                    </table>
</body>
</html>