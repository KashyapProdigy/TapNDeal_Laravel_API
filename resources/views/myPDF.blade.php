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
</style>
</head>
<body>
<hr>
<main>
<div class="container-fluid">
<div class="container">
    <div class="row d-flex col-12">
        <div class="col-5">
    				<address>
    				<h3><strong>Order Information</strong></h3><hr><br>
    					<strong> From:</strong>ABC<br>
                        <strong> To:</strong>PArth<br>
                        <strong> Agent:</strong>PArth
    				</address>

    	</div>
        <div class="col-5 text-right">
    				<address>
                        <strong> Order Date:</strong>12/08/1999<br>
                        <strong> Number:</strong>9033163874<br>
                        <strong> Order status:</strong>a@gmail.com<br>
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
    					<table class="table table-condensed">
    						<thead>
                                <tr>
        							<td><strong>Item</strong></td>
        							<td class="text-center"><strong>Product name</strong></td>
                                    <td class="text-center"><strong>Product Image</strong></td>
									<td class="text-center"><strong>Price</strong></td>
									<td class="text-center"><strong>Colors</strong></td>
        							<td class="text-center"><strong>Quantity</strong></td>
        							<td class="text-right"><strong>Totals</strong></td>
                                </tr>
    						</thead>
    						<tbody>
                                <tr>
    								<td>1</td>
    								<td class="text-center">pname</td>
                                    <td class="text-center"></td>
									<td class="text-center">5000</td>
									<td class="text-center">ahjag</td>
    								<td class="text-center">5</td>
    								<td class="text-right">&#x20b9;1000</td>
    							</tr>
    							<tr>
    								<td class="thick-line"></td>
                                    <td class="thick-line"></td>
    								<td class="thick-line"></td>
									<td class="thick-line"></td>
                                    <td class="thick-line text-center"><strong>Total</strong></td>
									<td class="thick-line text-center">100</td>
    								<td class="thick-line text-right">&#x20b9;7895</td>
    							</tr>
    						</tbody>
    					</table>
    				</div>
    			</div>
    		</div>
    	</div>
    </div>
</div>
</div>
</main>
</body>
</html>
