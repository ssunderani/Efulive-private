<!DOCTYPE html>
<html>
<head>
	<title>asset disposal report</title>
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
    .text-padding{
        padding: 50px;
        width: 25%;
    }
    .text-border-top{
        border-top:1px solid black;
    }
    </style>
</head>
<body>
<?php
$fields = (array)json_decode($filters);
$from = isset($fields['from_date'])?$fields['from_date']:null;
$to = isset($fields['to_date'])?$fields['to_date']:null;
?>
<table cellpadding="0" cellspacing="0" style="width:100%;">
            <tr class="text-center">
                <td class="text-center" style="width:85%; padding-left: 100px;">
                    <h2>EFULife Assurance Ltd.</h2>
                    <h2 style="font-weight:normal; line-height:1px;">IT Equipment Disposal</h2>
                    <p style="font-size: 12px;"><b>From Date:</b>{{ empty($from)?'-':date('j-F-Y', strtotime($from)) }} <b>To Date:</b>{{ empty($to)?'-':date('j-F-Y', strtotime($to)) }}</p>
                </td>
                <td style="width:15%;">
                <p><b>Username:</b>{{ Auth::user()->name }}</p>
                <p style="line-height: 0px;"><b>Printed</b></p>
                </td>
            </tr>
        </table> <br> 
                                    <table class="secondary-table">
                                    <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Item</th>
                                                <th>Product S#</th>
                                                <th>User/Location</th>
                                                <th>Disposal Status</th>
                                                <th>Purchase Date</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        
                                        <tbody>
                                        <?php $i = 1; ?>
                                        @foreach ($disposals as $disposal)
                                            <tr>
                                                <td class='text-align-right'>{{ $i++ }}</td>
                                                <td>{{ !empty($disposal->subcategory)?$disposal->subcategory->sub_cat_name:'' }}</td>
                                                <td>{{ !empty($disposal->inventory)?$disposal->inventory->product_sn:'' }}</td>
                                                <td>{{ !empty($disposal->inventory->location)?$disposal->inventory->location->location:'' }}</td>
                                                <td>{{ !empty($disposal->disposalstatus)?$disposal->disposalstatus->d_status:'' }}</td>
                                                <td>{{ !empty($disposal->inventory)?date('j-F-Y', strtotime($disposal->inventory->purchase_date)):'' }}</td>
                                                <td>{{ $disposal->remarks }}</td>
                                            </tr>
                                        @endforeach 
                                        </tbody>
                                    </table>
                                    <br><br>
<table cellpadding="0" cellspacing="0" style="width:100%; border:0px;">
    <tr class="text-center">
        <td class="text-center text-padding">
            <p class="text-border-top">Recommendation</p>
        </td>
        <td class="text-center text-padding">
            <p class="text-border-top">Checked by Helpdesk Manager</p>
        </td>
        <td class="text-center text-padding">
            <p class="text-border-top">Remove from Inventory System</p>
        </td>
        <td class="text-center text-padding">
            <p class="text-border-top">Validated by NSID Manager</p>
        </td>
    </tr>
</table>
<table cellpadding="0" cellspacing="0" style="width:100%; border:0px;">
    <tr class="text-center">
        <td class="text-center text-padding">
            <p class="text-border-top">Equipment Auction by GA Representative</p>
        </td>
        <td class="text-center text-padding">
            <p class="text-border-top">Approved by NSID HoD</p>
        </td>
        <td class="text-center text-padding">
            <p class="text-border-top">Approved by GA HoD</p>
        </td>
        <td class="text-center text-padding">
            <p class="text-border-top">Equipment remove from Finance record and approved by Finance/Accounts HoD</p>
        </td>
    </tr>
</table>                                
</body>
</html>