<!DOCTYPE html>
<html>
<head>
	<title>inventory reorder level</title>
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
                <td class="text-center" style="width:85%; padding-left: 100px;">
                    <h2>EFULife Assurance Ltd.</h2>
                    <h2 style="font-weight:normal; line-height:1px;">Inventory Reorder Level</h2>
                </td>
                <td style="width:15%;">
                <p style="font-size: 12px;"><b>Username:</b>{{ Auth::user()->name }}</p>
                <p style="font-size: 12px;"><b>Printed:</b></p>
                <p style="line-height: 0px; font-size: 12px;">{{ date('d-M-Y') }}</p><r>
                <p style="line-height: 0px; font-size: 12px;">{{ date('h:i:sa') }}</p>
                </td>
            </tr>
        </table> <br> 
        <table class="secondary-table">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Item</th>
                                                <th>Reorder Level</th>
                                                <th>Qty. in Stock</th>                           
                                            </tr>
                                        </thead>
                                        
                                        <tbody>
                                        <?php $i = 1; ?>
                                        @foreach ($reorders as $reorder)
                                            <tr>
                                                <td>{{ $i++ }}</td>
                                                <td>{{ empty($reorder->subcategory)?'':$reorder->subcategory->sub_cat_name }}</td>
                                                <td>{{ empty($reorder->subcategory)?'':$reorder->subcategory->threshold }}</td>
                                                <td>{{ $reorder->qty }}</td>
                                            </tr>
                                        @endforeach    
                                        </tbody>
                                    </table>
</body>
</html>