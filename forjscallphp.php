<?php
// // search should use search product for
// if (isset($_POST["get_product_by_category_for_shopping"])) {
	
// 	require_once ('inc/Product.php');
// 	require_once ('inc/ProductDescription.php');
// 	require_once ('inc/Category.php');
// 	require_once ('inc/Brand.php');
	
// 	$product;
// 	if ($_POST["get_product_by_category_for_shopping"] == ""){
// 		$product = Product::GetAllProduct();
// 	}
// // 	else{
		
// // 		$productdesc = ProductDescription::GetProductDescriptionsByCategoryId($_POST["get_product_by_category_for_shopping"]);
// // 		foreach($productdesc as $pdesc){
// // 			array_push($product,Product::GetEnabledProductByProductDescriptionId($pdesc->$pid));
// // 		}
// //	}
// 	foreach ($product as $p) {
// // 		echo("test");
// // 		print_r($p);
// //		$product = Product::GetEnabledProductByProductDescriptionId($p->id);
// 		createProductBoxForShopping($p);
// 	}
	
// 	if (count($product) == 0)
// 		echo "No result.";
// }

// if (isset($_POST["get_product_by_category_for_inventory"])) {

// 	require_once ('inc/Product.php');
// 	require_once ('inc/ProductDescription.php');
// 	require_once ('inc/Category.php');
// 	require_once ('inc/Brand.php');

// 	$product;
// 	if ($_POST["get_product_by_category_for_inventory"] == "")
// 		$product = Product::GetAllProduct();
// // 	else
// // 		$productdescs = ProductDescription::GetProductDescriptionsByCategoryId($_POST["get_product_by_category_for_inventory"]);
	

// 	foreach ($product as $p) {
// 		createProductBoxForInventory($p);
// 	}

// 	if (count($product) == 0)
// 		echo "No result.";
// }

if (isset($_POST["search_product_for_shopping"])) {
	require_once ('inc/Product.php');
	require_once ('inc/ProductDescription.php');
	require_once ('inc/Category.php');
	require_once ('inc/Brand.php');
	require_once ('inc/WishList.php');
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	
	$cat = $_POST["category"];
	$str = $_POST["search_product_for_shopping"];
	$pages = $_POST["page"];
	//wait for search by tag , page
	if (isset($_POST["customer_id"]))
		$customerId = $_POST["customer_id"];
	
	$productdescs = array();
	if ($str == "") {
		if ($cat == "Category" || $cat == "All") {
			$product = Product::GetAllProductWithLimit(30, $pages);
			foreach ($product as $p){
				array_push($productdescs, $p->productDescription);
			}
		}
		else {
			$productdescs = ProductDescription::SearchByTagsWithLimit(array($cat), 30, $pages);
		}
	}
	else {
		$str = array_map( 'trim', explode(",", $str) );
		
		if (!($cat == "Category" || $cat == "All")) {
			array_push($str, $cat);
			$productdescs = ProductDescription::SearchByTagsWithLimit($str, 30, $pages);
		}
		else {
			$productdescs = ProductDescription::SearchByTagsWithLimit($str, 30, $pages);
		}
	}
	
	echo "<div style=\"width: 100%;\">";
	if (count($productdescs) == 0)
		echo "No result.";
	else 
		echo count($productdescs) ." result(s).<br>";
	echo "</div>";
	
	if (isset($_POST["customer_id"])) {
		$wishList = WishList::GetWishListFromCustomer(Customer::GetCustomer($customerId));
		foreach ($productdescs as $p) {
			$product = Product::GetEnabledProductByProductDescriptionId($p->id);
			
			$isWish = $wishList->isWish($product);
			
			createProductBoxForShopping($product, $isWish);
		}
	}
	else {
		foreach ($productdescs as $p) {
			$product = Product::GetEnabledProductByProductDescriptionId($p->id);
								
			createProductBoxForShopping($product, 0);
		}
	
	}
}

if (isset($_POST["search_product_for_inventory"])) {

	require_once ('inc/Product.php');
	require_once ('inc/ProductDescription.php');
	require_once ('inc/Category.php');
	require_once ('inc/Brand.php');

	$cat = $_POST["category"];
	$str = $_POST["search_product_for_inventory"];
	$pages = $_POST["page"];
	$productdescs = array();
	if ($str == "") {
		if ($cat == "Category" || $cat == "All") {
				$product = Product::GetAllProductWithLimit(30, $pages);
				foreach ($product as $p) {
				array_push($productdescs, $p->productDescription);
			}		
		}
		else{
			$productdescs = ProductDescription::SearchByTagsWithLimit(array($cat), 30, $pages);
		}
	}
	else {
		$str = explode(",", $str);
		array_map( 'trim', $tags_str );
		if (!($cat == "Category" || $cat == "All")) {
			array_push($str, $cat);
			$productdescs = ProductDescription::SearchByTagsWithLimit($str, 30, $pages);
		}
		else {
			$productdescs = ProductDescription::SearchByTagsWithLimit($str, 30, $pages);
		}
	}

	if (count($productdescs) == 0)
		echo "No result.";
	else
		echo count($productdescs) ." result(s).<br>";
	
	foreach ($productdescs as $p) {
		$product = Product::GetEnabledProductByProductDescriptionId($p->id);
		createProductBoxForInventory($product);
	}
}

function createProductBoxForShopping($product, $isWish) {
	if (!($product -> id))
		return "";
	require_once ('inc/Inventory.php');
	include_once ('inc/InventoryDao.php');
	
	$productId = $product->id;
	$productName = $product->productDescription->productName;
	$price = $product->price;
	$imageLen = count($product->productDescription->images);
	$maxQuan = Inventory::getQuntity($productId);
	
	$buttonStatus = $maxQuan > 0 ? "" : "disabled";
	
	if ($isWish)
	echo "
			
	<div class=\"thumbnail productbox\">
	
	   	<a href=\"?page=detail&id=$productId\">
	   		<!-- May change link of pic to product desc page -->
	   		<img src=\"{$product->productDescription->images[$imageLen-1]}\">
	   		<span style=\"position: relative; top: -20px; left: 70px;\" class=\"glyphicon glyphicon-heart\">$isWish</span> 
			<div class=\"name\" id=\"name\">$productName</div>
		</a>
	
		<div id=\"price\">&#3647;$price</div>
	
		<button type=\"button\" class=\"btn btn-success\" onclick=\"addToCart($productId, '$productName', $price, 1/*, $maxQuan*/);\" $buttonStatus>ADD</button>
	</div>

	";

	else
	echo "
		
	<div class=\"thumbnail productbox\">
	
	<a href=\"?page=detail&id=$productId\">
		<!-- May change link of pic to product desc page -->
		<img src=\"{$product->productDescription->images[$imageLen-1]}\">
		<div class=\"name\" id=\"name\">$productName</div>
	</a>

		<div id=\"price\">&#3647;$price</div>

		<button type=\"button\" class=\"btn btn-success\" onclick=\"addToCart($productId, '$productName', $price, 1/*, $maxQuan*/);\" $buttonStatus>ADD</button>
	</div>
	
	";
	
}

function createProductBoxForInventory($product) {
	if (!($product -> id))
		return "";
	$productId = $product->id;
	$productName = $product->productDescription->productName;
	$price = $product->price;
	
	$images = $product->productDescription->images;
	$imageLen = count($product->productDescription->images);
	
	$coverImage;
	if ( $images == null || count ( $images ) < 1 ) {
		$coverImage = 'image/logo.png';
	} else {
		$coverImage = $product->productDescription->images[$imageLen-1];
	}
	echo "
	<div class=\"thumbnail productbox\">

		<!-- May change link of pic to product desc page -->
		<img src=\"{$coverImage}\">
	
		<div class=\"name\" id=\"name\">$productName</div>
	
		<div id=\"price\">&#3647;$price</div>
	
		<button type=\"button\" class=\"btn btn-info\" onclick=\"editProduct('$productId');\">EDIT</button>
		<button type=\"button\" class=\"btn btn-Danger\" onclick=\"removeProduct('$productId');\">REMOVE</button>
	</div>

	";
}

if (isset($_POST["submit"])) {
	if ($_POST["submit"] == "add") {
		include_once ('inc/Product.php');
		include_once ('inc/ProductDescription.php');
		include_once ('inc/Category.php');
		include_once ('inc/Brand.php');
		include_once ('inc/Inventory.php');
		include_once ('inc/InventoryDao.php');
		$brand = Brand::CreateBrand($_POST['brand']);
		$category = Category::CreateCategory($_POST['category']);
		$productDesc = ProductDescription::CreateProductDescription( $category, $brand, $_POST['desc'], $_POST['code'], array_map( 'trim', explode(',', $_POST['tag']) ), $_POST['name'] ,$_POST['weight']);
		$productDesc->addImages( array( $_POST['image'] ) );
		$product = Product::CreateProduct($productDesc, $_POST['price']);
		Inventory::addProduct($product, $_POST['quan'] );
	}
	if ($_POST["submit"] == "edit") {
		include_once ('inc/ProductDescription.php');
		include_once ('inc/Product.php');
		include_once ('inc/Category.php');
		include_once ('inc/Brand.php');
		include_once ('inc/Inventory.php');
		include_once ('inc/InventoryDao.php');
		
		$id = $_POST["id"];
		$product = Product::GetProduct($id);
		$productdescs = $product->productDescription;
		$product = Product::ReplaceActiveProduct($productdescs, $_POST["price"]);
		Inventory::addProduct($product, $_POST["quan"]);
		$productdescs -> productName = $_POST["name"];
		$productdescs -> modelCode = $_POST["code"];
		$productdescs -> description = $_POST["desc"];
		$productdescs -> addImages(array($_POST["image"]));
		$productdescs -> category = Category::CreateCategory($_POST["category"]);
		$productdescs -> additionTags = array_map( 'trim', explode(',', $_POST['tag']) );
		$productdescs -> brand = Brand::CreateBrand($_POST["brand"]);
		$productdescs -> weight = $_POST['weight'];
		$productdescs -> updateData();
		
		//print_r($productdescs);
		//echo ("Product Edited");
	}
	
}

if (isset($_POST["get_product_detail_by_id"])) {

	require_once ('inc/Product.php');
	require_once ('inc/ProductDescription.php');
	require_once ('inc/Category.php');
	require_once ('inc/Brand.php');
	require_once ('inc/Inventory.php');
	include_once ('inc/InventoryDao.php');
	
	$product = Product::GetProduct($_POST["get_product_detail_by_id"]);
	$productdescs = $product->productDescription;
	$quan = Inventory::getQuntity($product->id);
	$desc = str_replace('"', '\"', $productdescs->description);
	
// 	print_r($product);
// 	print_r($productdescs);
// 	print_r($quan);
// 	echo($desc);
	
	$tags_str = implode("\", \"", $productdescs->additionTags);
	$imageLen = count($product->productDescription->images);
	
	echo "
	{
		\"id\" : {$_POST["get_product_detail_by_id"]},
		\"name\" : \"{$productdescs->productName}\",
		\"code\" : \"{$productdescs->modelCode}\",
		\"price\" : {$product->price},
		\"description\" : \"{$desc}\",
		\"image\" : \"{$product->productDescription->images[$imageLen-1]}\",
		\"category\" : \"{$productdescs->category->value}\",
		\"tag\" : [\"$tags_str\"],
		\"weight\" : $productdescs->weight,
		\"quantity\" : $quan,
		\"brand\" : \"{$productdescs->brand->value}\"
	}";

}

if (isset($_POST["sign_up"])) {
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Customer.php');
	
	$result = Customer::CreateCustomer($_POST["firstname"], $_POST["lastname"], $_POST["email"], $_POST["password"], "");
	echo "
	{
		\"id\" : {$result->id},
		\"username\" : \"{$result->username}\",
		\"firstname\" : \"{$result->firstName}\",
		\"lastname\" : \"{$result->lastName}\"
	}";
}

if (isset($_POST["sign-in"])) {
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Customer.php');
	require_once ('inc/Admin.php');
	
// 	var_dump($_POST);
	
	$result = Customer::Authenticate($_POST["email"], $_POST["password"]);
	if ($result->id != 0) {
		echo "
		{
			\"id\" : {$result->id},
			\"username\" : \"{$result->username}\",
			\"firstname\" : \"{$result->firstName}\",
			\"lastname\" : \"{$result->lastName}\"
		}";
		return;
	}
	else {
		$result = Admin::Authenticate($_POST["email"], $_POST["password"]);
		echo "
		{
		\"id\" : {$result->id},
		\"username\" : \"{$result->username}\",
		\"firstname\" : \"{$result->firstName}\",
		\"lastname\" : \"{$result->lastName}\",
		\"adminlevel\" : \"{$result->level}\"
		}";
		return;
	}
}

if (isset($_POST["remove"])) {
	require_once('inc/Product.php');
	require_once('inc/ProductDao.php');
	
	Product::GetProduct($_POST["remove"])->disable();
}

if (isset($_POST["get_all_product_in_cart"])) {
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Customer.php');
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');
	require_once ('inc/Cart.php');
	require_once ('inc/InventoryDao.php');
	require_once ('inc/ProductDescription.php');
	require_once ('inc/Category.php');
	require_once ('inc/Brand.php');
	
	$customer = Customer::GetCustomer($_POST["get_all_product_in_cart"]);
	$products = $customer->getCart()->GetProducts();
	
	echo json_encode($products);
}

if (isset($_POST["add_to_cart"])) {
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Customer.php');
	require_once ('inc/Cart.php');
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');
	require_once ('inc/ProductDescription.php');
	require_once ('inc/Category.php');
	require_once ('inc/Brand.php');

	$customer = Customer::GetCustomer($_POST["add_to_cart"]);
	$cart = $customer->getCart();
	
	$product = Product::GetProduct($_POST["product_id"]);
	$amount = $_POST["quantity"];
	
	if ($amount > 0) {
		$cart->AddProduct($product, $amount);
	}
	else if ($amount < 0) {
		$cart->RemoveProduct($product, -1 * $amount);
	}
}

if (isset($_POST["clear-cart"])) {
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Customer.php');
	require_once ('inc/Cart.php');
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');
	require_once ('inc/ProductDescription.php');
	require_once ('inc/Category.php');
	require_once ('inc/Brand.php');

	$customer = Customer::GetCustomer($_POST["clear-cart"]);
	$cart = $customer->getCart();
	$cart->ClearProducts();
}

if (isset($_POST["confirm-payment"])) {
	include_once 'inc/CreditCard.php';
	include_once 'inc/Customer.php';
	include_once 'inc/CustomerDao.php';
	include_once 'inc/Cart.php';
	include_once 'inc/Sale.php';
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');
	require_once ('inc/Promotion.php');
	require_once ('inc/PaymentDao.php');
	require_once ('inc/Payment.php');
	$card = CreditCard::CreateCreditCard($_POST["name"], $_POST["number"], $_POST["cvv"], $_POST["expYear"], $_POST["expMonth"]);
	$customer = Customer::GetCustomer($_POST['customerid']);	
	$customerDetail = $_POST["customerDetail"];
	$cart = $customer->getCart();
	$cart->setFee($_POST["fee"]);
	$sale = $cart->purchase($card, $customerDetail);
	
	if($sale !== null){
		echo("Pass");
	}else{
		echo("Fail");
	}
}

if (isset($_POST["get_wishlist_product"])) {
	require_once ('inc/WishList.php');
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');
	
	$customerId = $_POST["get_wishlist_product"];
	$pages = $_POST["page"];
	$wishlist = Customer::GetCustomer($customerId)->getWishList();
	$products = $wishlist -> GetProductsWithLimit(30, $pages);
	
	foreach ($products as $p) {
		createProductBoxForWishList($p["Product"], $p["Quantity"]);
	}
	
	if (count($products) == 0)
		echo "No result.";
}

if (isset($_POST["add_to_wishlist"])) {
	require_once ('inc/WishList.php');
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');
	
	$customerId = $_POST["add_to_wishlist"];
	$productId = $_POST["product_id"];
	$wishlist = Customer::GetCustomer($customerId) -> getWishList();
	$wishlist -> addProduct(Product::GetProduct($productId), 1);
}

function createProductBoxForWishList($product, $wishQuan) {
	if (!($product -> id))
		return "";
	require_once ('inc/Inventory.php');
	include_once ('inc/InventoryDao.php');

	$productId = $product->id;
	$productName = $product->productDescription->productName;
	$price = $product->price;
	$imageLen = count($product->productDescription->images);
	$maxQuan = Inventory::getQuntity($productId);

	$buttonStatus = $maxQuan > 0 ? "" : "disabled";

	
	echo "
		
	<div class=\"thumbnail productbox\">

	<a href=\"?page=detail&id=$productId\">
	<!-- May change link of pic to product desc page -->
	<img src=\"{$product->productDescription->images[$imageLen-1]}\">
	 
	<div class=\"name\" id=\"name\">$wishQuan $productName</div>
	</a>

	<div id=\"price\">&#3647;$price</div>

	<button type=\"button\" class=\"btn btn-success\" onclick=\"addToCart($productId, '$productName', $price, 1/*, $maxQuan*/);\" $buttonStatus>ADD</button>
	<button type=\"button\" class=\"btn btn-Danger\" onclick=\"removeWishProduct('$productId');\">REMOVE</button>
	</div>

	";

}

if (isset($_POST["remove_wish"])) {
	$customerId = isset($_POST["remove_wish"]);
	$productId = isset($_POST["product_id"]);
	$wishlist = WishLists::GetWishListFromCustomer(Customer::GetCustomer($customerId));
	$wishlist -> RemoveProduct(Product::GetProduct($productId),1);
	////
}

if (isset($_POST["sign_up_admin"])) {
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Admin.php');
	$result = Admin::CreateAdmin($_POST["firstname"], $_POST["lastname"], $_POST["email"], $_POST["password"],1);
	echo "
	{
	\"id\" : {$result->id},
	\"username\" : \"{$result->username}\",
	\"firstname\" : \"{$result->firstName}\",
	\"lastname\" : \"{$result->lastName}\",
	\"adminlevel\" : {$result->level}
	}";
}

if (isset($_POST["get_all_transaction"])) {
	require_once ('inc/Sale.php');
	require_once ('inc/PaymentDao.php');
	require_once ('inc/Cart.php');
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Payment.php');
	require_once ('inc/Creditcard.php');
	$pages = $_POST["page"];
	$allSale = Sale::GetAllWithLimit(30, $page);
	echo json_encode($allSale);
}

if (isset($_POST["get_product_in_transaction"])) {
	require_once ('inc/Product.php');
	require_once ('inc/ProductDao.php');
	require_once ('inc/Sale.php');
	require_once ('inc/PaymentDao.php');
	require_once ('inc/Cart.php');
	require_once ('inc/InventoryDao.php');
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Payment.php');
	require_once ('inc/Creditcard.php');
	
	$cartId = $_POST["get_product_in_transaction"];
	$pages = $_POST["page"];
	$products = Cart::GetCart($cartId)->GetProductsWithLimit(30, $pages);
	echo json_encode($products);
}




if (isset($_POST["get_customer_list"])) {
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	
	$customers = Customer:: getAllCustomers();
	
	echo json_encode($customers);
}


if (isset($_POST["get_admin_list"])) {
	require_once ('inc/Admin.php');
	require_once ('inc/CustomerDao.php');

	$admins = Admin::getAllAdmins();
	// bat เสจแล้วทักมาใน facebook นะ กุออกก่อนละกัน
	
	echo json_encode($admins);
}

if(isset($_POST["get_cartid_by_customerid"])){
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	require_once ('inc/Cart.php');
	require_once ('inc/InventoryDao.php');
	
	$customer = Customer::GetCustomer($_POST["get_cartid_by_customerid"]);
	echo( $customer->getCart()->cartId );
}

if (isset($_POST["get_customer_detail"])) {
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	
	$customerId = $_POST["get_customer_detail"];
	
	$customer = Customer::GetCustomer($customerId);
	$address = explode("**", $customer->Address);
	
	echo "
	{
	\"username\" : \"{$customer->username}\",
	\"firstname\" : \"{$customer->firstName}\",
	\"lastname\" : \"{$customer->lastName}\",
	\"address\" : \"{$address[0]}\",
	\"address2\" : \"{$address[1]}\",
	\"district\" : \"{$address[2]}\",
	\"province\" : \"{$address[3]}\",
	\"country\" : \"{$address[4]}\",
	\"zip\" : \"{$address[5]}\",
	\"phone\" : \"{$address[6]}\"
	}";
}

if (isset($_POST["save_customer_detail"])) {
	require_once ('inc/Customer.php');
	require_once ('inc/CustomerDao.php');
	
	$customerId = $_POST["save_customer_detail"];
	$customer = Customer::GetCustomer($customerId);
	$customer ->updatePassword( $_POST["password"] );
	$customer -> firstName = $_POST["firstname"];
	$customer -> lastName = $_POST["lastname"];
	$customer ->  Address = $_POST["address"] . "**" . $_POST["address2"] . "**" . $_POST["district"] . "**" . $_POST["province"] . "**" . $_POST["country"] . "**" . $_POST["zip"] . "**" . $_POST["phone"];
	$customer->updateCustomer();
}

if (isset($_GET["get_all_promotion"])) {
	echo "[{\"date\":\"27\/2\/\",\"title\":\"Getting Contacts Barcelona - test1\",\"link\":\"http:\/\/gettingcontacts.com\/events\/view\/barcelona\",\"color\":\"red\"},{\"date\":\"25\/5\/\",\"title\":\"test2\",\"link\":\"http:\/\/gettingcontacts.com\/events\/view\/barcelona\",\"color\":\"pink\"},{\"date\":\"20\/6\/\",\"title\":\"test2\",\"link\":\"http:\/\/gettingcontacts.com\/events\/view\/barcelona\",\"color\":\"green\"},{\"date\":\"7\/10\/\",\"title\":\"test3\",\"link\":\"http:\/\/gettingcontacts.com\/events\/view\/barcelona\",\"color\":\"blue\",\"class\":\"miclasse \",\"content\":\"contingut popover";
}


// StatusType :: 'ORDER_RETRIEVE','ORDER_PICK','ORDER_READY','ORDER_SCAN','ORDER_TRANSIT'
if (isset($_POST["bind_cartid"])) {
	
	$host="localhost";
	$user = "tsp";
	$password="tsp";
	$database="ecomerce";
	 
	$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
	$STH = $db->prepare("INSERT INTO `OrderTrackings` (`CartId`, `StatusType`, `Date`, `Description`, `UpdatedBy`) VALUES (:cid, 'ORDER_RETRIEVE', :date, 'Undefined', :by)");
	$STH->bindParam(':cid', $_POST["cartId"]);
	$STH->bindParam(':date', date("Y-m-d h:i:sa"));
	$STH->bindParam(':by', "System");
	$STH->execute();
}

if (isset($_POST["get_lastest_order_status_by_cartid"])) {
	$cartid = $_POST["get_lastest_order_status_by_cartid"];
	
	$host="localhost";
	$user = "tsp";
	$password="tsp";
	$database="ecomerce";
	
	$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
	$STH = $db->prepare("SELECT * FROM `OrderTrackings` WHERE `CartId`=:cid ORDER BY `Key` DESC LIMIT 1");
	$STH->bindParam(':cid', $cartid);
	$STH->execute();
	echo json_encode($STH->fetchAll());
}

if (isset($_POST["get_order_status_by_cartid"])) {
	$cartid = $_POST["get_order_status_by_cartid"];
	
	$host="localhost";
	$user = "tsp";
	$password="tsp";
	$database="ecomerce";
	
	$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
	$STH = $db->prepare("SELECT * FROM `OrderTrackings` WHERE `CartId`=:cid ORDER BY `Key`");
	$STH->bindParam(':cid', $cartid);
	$STH->execute();
	echo json_encode($STH->fetchAll());
}

if (isset($_POST["is_cartid_exists"])) {
	$cartid = $_POST["is_cartid_exists"];
	
	$host="localhost";
	$user = "tsp";
	$password="tsp";
	$database="ecomerce";
	
	$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
	$STH = $db->prepare("SELECT COUNT(*) AS num FROM `OrderTrackings` WHERE `CartId`=:cid");
	$STH->bindParam(':cid', $cartid);
	$STH->execute();
	
	echo $STH->fetch()["num"];
}

if (isset($_POST["update_order_status_by_cartid"])) {
	$cartid = $_POST["update_order_status_by_cartid"];
	$status = $_POST["status"];
	$description = $_POST["description"];
	$by = $_POST["by"];

	$host="localhost";
	$user = "tsp";
	$password="tsp";
	$database="ecomerce";
	 
	$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
	$STH = $db->prepare("INSERT INTO `OrderTrackings` (`CartId`, `StatusType`, `Date`, `Description`, `UpdatedBy`) VALUES (:cid, :type, :date, :desc, :by)");
	$STH->bindParam(':cid', $cartid);
	$STH->bindParam(':type', $status);
	$STH->bindParam(':date', date("Y/m/d h:i:s A"));
	$STH->bindParam(':desc', $description);
	$STH->bindParam(':by', $by);
	$STH->execute();
}

if (isset($_POST["get_enum_values"])) {
	$host="localhost";
	$user = "tsp";
	$password="tsp";
	$database="ecomerce";
	
	$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
	$STH = $db->prepare("SHOW COLUMNS FROM `OrderTrackings` WHERE `Field`='StatusType'");
	$STH->execute();
	$row = $STH->fetch();
	$type = $row['Type'];
	preg_match('/enum\((.*)\)$/', $type, $matches);
	$vals = explode(',', $matches[1]);
	echo json_encode($vals);
}

if (isset($_POST["get_customer_detail_by_cartid"])) {
	$cartId = $_POST["get_customer_detail_by_cartid"];
	
	$host="localhost";
	$user = "tsp";
	$password="tsp";
	$database="ecomerce";
	
	$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
	$STH = $db->prepare("SELECT `CustomerDetail` FROM `Sales` WHERE `CartId`=:cid");
	$STH->bindParam(':cid', $cartId);
	$STH->execute();
	echo $STH->fetch()[0];
}





