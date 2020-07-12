<?php

const PROVINCES = [
	'AB' => 'Alberta',
	'BC' => 'British Columbia',
	'MB' => 'Manitoba',
	'NB' => 'New Brunswick',
	'NL' => 'Newfoundland & Labrador',
	'NS' => 'Nova Scotia',
	'NT' => 'Northwest Territories',
	'NU' => 'Nunavut',
	'ON' => 'Ontario',
	'PE' => 'Prince Edward Island',
	'QC' => 'Québec',
	'SK' => 'Saskatchewan',
	'YT' => 'Yukon',
];

const CAPITAL_CITY = [
	'AB' => 'Edmonton',
	'BC' => 'Victoria',
	'MB' => 'Winnipeg',
	'NB' => 'Fredericton',
	'NL' => 'St. John\'s',
	'NS' => 'Halifax',
	'NT' => 'Yellowknife',
	'NU' => 'Iqaluit',
	'ON' => 'Toronto',
	'PE' => 'Charlottetown',
	'QC' => 'Québec City',
	'SK' => 'Regina',
	'YT' => 'Whitehorse',
];

$db = new SQLite3('castest.db');

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>CAS Test</title>
	<style>
		body {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 13px;
		}
		
		table {
			border: 1px solid #ccc;
		}
		
		th {
			text-align: left;
		}
		
		td {
			border: 1px solid #ccc;
		}
		
		.list {
			display: inline-block;
			vertical-align: top;
			margin: 20px;
		}

		.form-field{
			margin-bottom: 5px;
		}
		.form-field > label {
			padding: 5px;
			font-weight: bold;
			text-align: center;
			width: 100px;
			display: inline-block;
		}
		.form-field > select{
			padding: 5px;
		}
		.multiple > label, .multiple > select{
			display: inline-block;
 			vertical-align: middle;
		}
	</style>
</head>

<body>

<?php
	// If clicked on submit 
	if(isset($_POST['submit']) ){
		// if product and stores value not empty
		if(!empty($_POST['product']) && !empty($_POST['stores'])){
			
			//for each store , insert relationship value to product_stores table
			foreach ($_POST['stores'] as $key => $value) {
				$sql_query = "INSERT INTO product_stores (product_id,store_id) VALUES (".$_POST['product'].",".$value.")";
				$db->exec($sql_query);
			}

			// Show insert message 
			echo "<p style='color:green;font-weight:bold;'>Insert succuessfully.</p>";
			
			echo "<p>Page will refresh automatically in 2 seconds. If not <a href='index.php'>click here</a>.</p>";
			header( "refresh:2;url=index.php" );
		}else{
			// show error message
			echo "<p style='color:red'>Plese select product and stores</p>";
		}		
	}
?>	

<!-- Form Area-->
<div class="form-div">
	<h3>Assign product to stores</h3>
	<form action="/index.php" method="post">
		<div class="form-field">
			<label>Product</label>
			<select name="product">
				<option value="">- select product -</option>
				<?php
				// Get products from database and display
				$result = $db->query('SELECT * FROM products');
				while($row = $result->fetchArray(SQLITE3_ASSOC)) {
					?>
					<option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
					<?php
				}
				
				?>
			</select>
		</div>	
		<div class="form-field multiple">
			<label>Store</label>
			<select name="stores[]" style="height: 200px;" multiple>
				<option value="">- select store -</option>
				<?php
				// Get stores name from database and display in multiple selection
				$result = $db->query('SELECT * FROM stores ORDER BY province, name');
				while($row = $result->fetchArray(SQLITE3_ASSOC)) {
					?>
					<option value="<?= $row['id'] ?>"><?= $row['name'] ?> - <?= key_exists($row['province'], PROVINCES) ? PROVINCES[$row['province']] : '' ?></option>
					<?php
				}
				
				?>
			</select>
		</div>	
		<div class="form-field">
			<input type="submit" name="submit">
		</div>
	</form>
	
</div>

<div class="list">
	<h3>Stores</h3>
	<table>
		<tr>
			<th>Name/City</th>
			<th>Province</th>
		</tr>
		<?php
		
		$result = $db->query('SELECT * FROM stores ORDER BY province, name');
		while($row = $result->fetchArray(SQLITE3_ASSOC)) {
			?>
			<tr>
				<td><?= $row['name'] ?></td>
				<td><?= key_exists($row['province'], PROVINCES) ? PROVINCES[$row['province']] : '' ?></td>
			</tr>
			<?php
		}
		
		?>
	</table>
</div>

<!-- Products table updated with new column stores name -->
<div class="list">
	<h3>Products</h3>
	<table>
		<tr>
			<th>Name</th>
			<th>Stores</th>
		</tr>
		<?php
		// get products and iterate for each products to get store and display 
		$result = $db->query('SELECT * FROM products');
		while($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$productID = $row['id'];
			?>
			<tr>
				<td><?= $row['name'] ?></td>
				<td>
			<?php 
			// fetch relationship between product and stores
			$result2 = $db->query('SELECT s.name as store_name, s.province  FROM product_stores ps JOIN stores s where ps.store_id = s.id and product_id = "'.$productID.'"');
			
			// all stores where product listed in stores array and if product in capital city store it in capitalArray
			$stores = [];
			$capitalArray = [];
			// iterate over product store relationship results
			while($row2 = $result2->fetchArray(SQLITE3_ASSOC)) {
				// save each store name with province in array and push it to stores array
				$s = [];
				$s[$row2['store_name']] = $row2['province'];
				array_push($stores, $s);

				// if there is any store in capital city and store it in capital array
				if(CAPITAL_CITY[$row2['province']] == $row2['store_name']){
					$capitalArray[$row2['province']] = $row2['store_name'];					
				}

			}
			//print_r($capitalArray);
			// currently stores array have nested array with store city as key and province as value
			// Make single array from nested array
			$singleArray = [];
			
			foreach ($stores as $key => $value) {
				foreach ($value as $key2 => $value2) {
					$singleArray[$value2][] = $key2;
				}
			}

			//print_r($singleArray);

			$res = array();
			
			// iterate trough singleArray
			foreach ($singleArray as $prov => $cities){
			 	// if capitalArray has value and if city is in capital array
			 	if(isset($capitalArray[$prov]) && in_array($capitalArray[$prov],$cities))
			    {
			        $res[]=$capitalArray[$prov];   
			    }
			    else{
			    	// merge city with result array
			      	$res=array_merge($res,$cities);
			    }
			}

			?>
				<?=implode(',',$res)?></td>
			</tr>
			<?php
		}
		
		?>
	</table>

</div>
</body>
</html>