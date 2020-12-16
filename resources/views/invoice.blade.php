<!-- Latest compiled and minified CSS -->
<html>
<head>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<style>
	.invoice-title h2, .invoice-title h3 {
		display: inline-block;
	}

	.table > tbody > tr > .no-line {
		border-top: none;
	}


	.table > thead > tr > .no-line {
		border-bottom: none;
	}

	.table > tbody > tr > .thick-line {
		border-top: 2px solid;
	}
    </style>
    <style>
    thead:before, thead:after,
    tbody:before, tbody:after,
    tfoot:before, tfoot:after
    {
        display: none;
    }
    .img {
    display: block;
    margin-left: auto;
    margin-right: auto;
    /* margin-top:-60px;
    margin-bottom:-60px; */
    }
</style>
</head>
<body>
<img class="img" src="{{asset('020.jpg')}}" alt="Tapndeal" style="width:20%;height:50%">

<main>

<div class="container-fluid">
<div class="container">
<hr>
<h3><strong>Order Information</strong></h3><hr><br>
    <div class="row d-flex col-12">
        <div class="col-sm-6">
            <address>
                <strong> From:</strong>{{$order['seller']['name']}}<br>
                <strong> To:</strong>{{$order['cust']['name']}}<br>
                @if($order['agent']['name'])
                <strong> Agent:</strong>{{$order['agent']['name']}}
                @endif
            </address>
    	</div>
        <div class="col-sm-6 text-right">
            <address>
                <strong> Order Date:</strong>{{date('d-m-Y',strtotime($order['order']['created_at']))}}<br>
                <strong> Number:</strong>{{$order['order']['order_name']}}<br>
                <strong> Order status:</strong>{{$order['order']['status_name']}}<br>
            </address>
    	</div>
    </div>

    <div class="row">
    	<div class="col-md-12">
    		<div class="panel panel-default">
    			<div class="panel-heading">
    				<h3 class="panel-title"><strong>Order summary</strong></h3>
    			</div>
    			<div class="panel-body">
    				<div class="table-responsive">
    					<table class="table table-condensed table-bordered table-striped">
    						<thead>
                                <tr>
        							<td class="text-center"><strong>Item</strong></td>
        							<td class="text-center"><strong>Product</strong></td>
									<td class="text-center"><strong>Price</strong></td>
									<td class="text-center"><strong>Colors</strong></td>
        							<td class="text-center"><strong>Quantity</strong></td>
        							<td class="text-center"><strong>Total</strong></td>
                                </tr>
    						</thead>
    						<tbody>
                            <?php $prod=json_decode($order['order']['products']);$q=0?>

                            @for($j=0;$j < count($prod) ;$j++)
                                <tr>
    								<td class="text-center">{{$j+1}}</td>
    								<td class="text-center">
                                    <img src="{{asset('productPhotos')}}/{{$prod[0]->product_image}}" alt="Product Image" style="width:100px;height:90px;"><br>{{$prod[0]->product_name}}
                                    </td>
									<td class="text-center">{{$prod[0]->product_price}}</td>
									<td class="text-center">

                                    <?php $col=$prod[0]->col_wise_qty;
                                        $col=json_decode($col);
                                        $col=(array)$col;
                                        $arr=$col;
                                        if(is_array($col)){
                                        ?>
                                        <?php
                                            foreach($col as $k=>$value) {
                                                 echo  $k ." <b>:</b> ";
                                                 echo $arr[$k]."<br>";
                                            }
                                            ?>


                                    <?php
                                        }
                                    ?></td>
    								<td class="text-center">{{$prod[0]->qty}}</td>
                                    <?php $q+=$prod[0]->qty ?>
    								<td class="text-center">Rs.{{$prod[0]->total_price}}</td>
    							</tr>
                            @endfor
    							<tr>
    								<td class="thick-line"></td>
                                    <td class="thick-line"></td>
    								<td class="thick-line"></td>
                                    <td class="thick-line text-center"><strong>Total</strong></td>
									<td class="thick-line text-center">{{$q}}</td>
    								<td class="thick-line text-center">Rs.{{$order['order']['total_price']}}</td>
    							</tr>
    						</tbody>
    					</table>
    				</div>
    			</div>
    		</div>
    	</div>
    </div>
<b>Notes : </b>{{$order['order']['notes']}}
</div>
</div>
</main>
</body>
</html>
