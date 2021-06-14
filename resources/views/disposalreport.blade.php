<!DOCTYPE html>
<html>
<head>
	<title>bin card report</title>
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
                    <h2><u>Asset Disposal Report Report</u><span class="font-14"><b> - <u>EFU Life Assurance Ltd.</u></b></span></h2>
                    
                    
                </td>
            </tr>
        </table> <br> 
                                    <table class="secondary-table">
                                    <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Category</th>
                                                <th>Sub Category</th>
                                                <th>Product S#</th>
                                                <th>Dispose date</th>
                                                <th>Handover date</th>
                                                <th>Reason</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        
                                        <tbody>
                                        <?php $i = 1; ?>
                                        @foreach ($disposals as $disposal)
                                            <tr>
                                                <td class='text-align-right'>{{ $i++ }}</td>
                                                <td>{{ !empty($disposal->category)?$disposal->category->category_name:'' }}</td>
                                                <td>{{ !empty($disposal->subcategory)?$disposal->subcategory->sub_cat_name:'' }}</td>
                                                <td>{{ !empty($disposal->inventory)?$disposal->inventory->product_sn:'' }}</td>
                                                <td>{{ date('j-F-Y', strtotime($disposal->dispose_date)) }}</td>
                                                <td>{{ date('j-F-Y', strtotime($disposal->handover_date)) }}</td>
                                                <td>{{ !empty($disposal->disposalstatus)?$disposal->disposalstatus->d_status:'' }}</td>
                                                <td>{{ $disposal->remarks }}</td>
                                            </tr>
                                        @endforeach 
                                        </tbody>
                                    </table>
</body>
</html>