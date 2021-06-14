<!DOCTYPE html>
<html>
<head>
	<title>asset repairing report</title>
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
                    <h2><u>Inventory Asset Repairing Report</u><span class="font-14"><b> - <u>EFU Life Assurance Ltd.</u></b></span></h2>
                    
                    
                </td>
            </tr>
        </table> <br> 
                                    <table class="secondary-table">
                                    <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Item Category</th>
                                                <th>Product SN</th>
                                                <th>Make</th>
                                                <th>Model</th>
                                                <th>Issued to</th>
                                                <th>Location</th>
                                                <th>Repairing Date</th>
                                                <th>Actual Price</th>
                                                <th>Repairing Cost</th>
                                                <th>Cumulative Cost</th>
                                                <th>Initial Status</th>
                                                <th>Current Condition</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        
                                        <tbody>
                                        <?php $i = 1; ?>
                                        @foreach ($repairs as $repair)
                                        <?php
                                        $total = $repair->actual_price_value+$repair->price_value;
                                        ?>
                                            <tr>
                                                <td>{{ $i++ }}</td>
                                                <td>{{ empty($repair->subcategory)?'':$repair->subcategory->sub_cat_name }}</td>
                                                <td>{{ empty($repair->item)?'':$repair->item->product_sn }}</td>
                                                <td>{{ empty($repair->item->make)?'':$repair->item->make->make_name }}</td>
                                                <td>{{ empty($repair->item->model)?'':$repair->item->model->model_name }}</td>
                                                <td>{{ empty($repair->item->user)?'':$repair->item->user->name }}</td>
                                                <td>{{ empty($repair->item->location)?'':$repair->item->location->location }}</td>
                                                <td>{{ date('j-F-Y' ,strtotime($repair->date)) }}</td>
                                                <td class='text-align-right'>{{ number_format($repair->actual_price_value,2) }}</td>
                                                <td class='text-align-right'>{{ number_format($repair->price_value,2) }}</td>
                                                <td class='text-align-right'>{{ number_format($total,2) }}</td>
                                                <td>{{ empty($repair->item->inventorytype)?'':$repair->item->inventorytype->inventorytype_name }}</td>
                                                <td>{{ empty($repair->item->devicetype)?'':$repair->item->devicetype->devicetype_name }}</td>
                                                <td>{{ $repair->remarks }}</td>
                                                
                                            </tr>
                                        @endforeach  
                                        </tbody>
                                    </table>
</body>
</html>