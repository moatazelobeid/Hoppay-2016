<?php
ob_start();
ob_end_flush();
error_reporting(0);
ini_set('max_execution_time','99999999999');
ini_set('memory_limit','9999999999M');

include('db.php');
//mysql_query("truncate table mc_products_temp")or die(mysql_error());
include('simple_html_dom.php');

$site_url = 'http://markavip.com';
$language = 'sa';
$temp_language = 'en';
$marchant_url = $site_url;
$marchant_logo = 'http://assets.ak.markavip-cdn.com/skin/frontend/inception/default/images/logo.png';

$all_category = array("women","men","children","electronics","home");
//$all_category = array("women");
foreach($all_category as $temp_cate){
	
	// Create DOM from URL or file
	$html = file_get_html($site_url.'/'.$language.'/'.$temp_cate.'/?___lang='.$temp_language);
		
	foreach($html->find('div.group-events') as $cate_list) {
		
		foreach($cate_list->find('a') as $each_cate_list) { 					
			$category_name = addslashes($temp_cate); 
			$department = addslashes($each_cate_list->find('strong.title', 0)->plaintext); 
			$offer_end_dt = addslashes(trim($each_cate_list->find('span.subtitle', 0)->plaintext)); 
			$off = explode(" ",$offer_end_dt); 
			$off_dt = implode("/",array($off[1],date(Y))); 
			$offer_start_dt = date('Y-m-d'); 
			$offer_end_dt = implode('-', array_reverse(explode('/', $off_dt))); 
			$product_list_url = trim($each_cate_list->href);
			//$brand =  $each_cate_list->find('strong.title', 0)->plaintext;
			
					if(isset($each_cate_list->find('strong.title', 0)->plaintext)){
						$brand_nm = $each_cate_list->find('strong.title', 0)->plaintext;  
						$chk_brand =  mysql_query("SELECT id FROM  `mc_product_brands` WHERE  `slug` = '".$brand_nm."'");
						$brand_id = mysql_fetch_array($chk_brand);		
						if(isset($brand_id) && !empty($brand_id)){ 
							$brand = $brand_id['id'];
						}else{ 
							$chk_brand1 = mysql_query("INSERT INTO mc_product_brands set slug ='$brand_nm',status=1");
							$lst_insrt_id_en = mysql_query("SELECT MAX(id) as MaximumID FROM mc_product_brands"); 
							$brand_id_en = mysql_fetch_array($lst_insrt_id_en); 
							$brand = $brand_id_en["MaximumID"];
							
							$insrt_langs = mysql_query("INSERT INTO mc_product_brand_langs set brand_id ='$brand', lang_id = 1, brand_title = '$brand_nm', description = '$brand_nm',status=1 ");							
						}					
					}else{
						$brand = 'None';
						$chk_brand =  mysql_query("SELECT id FROM  `mc_product_brands` WHERE  `slug` = '".$brand."'");
						$brand_id = mysql_fetch_array($chk_brand);		
						if(isset($brand_id) && !empty($brand_id)){ 
							$brand = $brand_id['id'];
						}else{ 
							$inst = "INSERT INTO mc_product_brands set slug ='$brand',status=1";
							$chk_brand = mysql_query($inst);
							$lst_insrt_id_en = mysql_query("SELECT MAX(id) as MaximumID FROM mc_product_brands"); 
							$brand_id_en = mysql_fetch_array($lst_insrt_id_en); 
							$brand = $brand_id_en["MaximumID"];
							$insrt_langs = mysql_query("INSERT INTO mc_product_brand_langs set brand_id ='$brand', lang_id = 1, brand_title = '$brand_nm', description = '$brand_nm',status=1 ");
						}	
					}
			
			// Get product list page
			$prod_list_html = file_get_html($product_list_url);
			//$prod_list_html = file_get_html("http://markavip.com/sa/catalog/women/eyewear");
			foreach($prod_list_html->find('ul.products-grid') as $all_product_list) {
				
				$prod_sl = 1;
				foreach($all_product_list->find('a') as $all_product_list_link) {
					
					$product_url = $all_product_list_link->href;
					
					// Get single product page  
					$single_prod_html = file_get_html($product_url);  
					
					$product_full_name = addslashes($single_prod_html->find('div.product-view-title h1',0)->plaintext); //echo $product_full_name;exit;
					
					# Get Product full name for SLUG
					if($product_full_name <> ""){
						$slug = create_url_slug($product_full_name);
					}
					# End Slug
					//$a11 = $single_prod_html->find('div.container',0)->find('span.days')->plaintext; echo "<pre>";print_r($a11);echo "---------".$a11;exit;
					$currency = trim($single_prod_html->find('span.sign',0)->plaintext);
					$price = trim($single_prod_html->find('span.digits',0)->plaintext); //echo $price."<br>";//exit;
					$price = str_replace( ',', '', $price );
					$old_price = ''; $diff = ''; $percent = ''; $percent_friendly = '';
					//echo "===".$single_prod_html->find('span.digits',1)->plaintext;exit;					
					
					if(isset($single_prod_html->find('span.digits',1)->plaintext)){
						$old_price = $single_prod_html->find('span.digits',1)->plaintext;
						$old_price = trim(str_replace($currency, "", $old_price));
						$old_price = str_replace( ',', '', $old_price );
						$diff = round($old_price) - round($price);
						$percent = $diff/$old_price;
						$percent_friendly = number_format( $percent * 100, 2 ) . '%';
						$ins_qry_en = "INSERT INTO mc_offers set merchant_id='2', offer_title='$percent_friendly. Discount', discount='$percent_friendly',status=1, start_date='$offer_start_dt', end_date='$offer_end_dt'"; //echo $ins_qry;
						mysql_query($ins_qry_en);
						$lst_insrt_id_en = mysql_query("SELECT MAX(id) as MaximumID FROM mc_offers"); //echo $lst_insrt_id;exit;
						$off_id_en = mysql_fetch_array($lst_insrt_id_en); 
						$id_en = $off_id_en["MaximumID"];
					}else{
						$id_en = ''; 
					}
					
					$product_description = mysql_real_escape_string(trim($single_prod_html->find('div.std',0)->plaintext));	
					$product_description = addslashes($product_description);
					
					//$image_url = '["'.$single_prod_html->find('div.product-img-box', 0)->find('a', 0)->href.'"]';
					$image_url = '"'.addslashes($single_prod_html->find('div.product-img-box', 0)->find('a', 0)->href).'"';
					if(isset($single_prod_html->find('div.more-views ul li',0)->find('img',0)->src) && !empty($single_prod_html->find('div.more-views ul li',0)->find('img',0)->src)){
						$thumb1 = '"'.addslashes(trim($single_prod_html->find('div.more-views ul li',0)->find('img',0)->src)).'"';
						$image_url = $image_url.",".$thumb1;
					}else if(isset($single_prod_html->find('div.more-views ul li',1)->find('img',0)->src) && !empty($single_prod_html->find('div.more-views ul li',1)->find('img',0)->src)){
						$thumb2 = '"'.addslashes(trim($single_prod_html->find('div.more-views ul li',1)->find('img',0)->src)).'"';
						$image_url = $image_url.",".$thumb2;
					}else if(isset($single_prod_html->find('div.more-views ul li',2)->find('img',0)->src) && !empty($single_prod_html->find('div.more-views ul li',2)->find('img',0)->src)){
						$thumb3 = '"'.addslashes(trim($single_prod_html->find('div.more-views ul li',2)->find('img',0)->src)).'"';
						$image_url = $image_url.",".$thumb3;
					}
					$image_url = '['.$image_url.']';
					
					# Check product exist in database for a particular marchant with product name
					/*$chk_qry = mysql_query("select id from mc_products_temp where marchant_url='$marchant_url' And product_name='$product_full_name' And price='$price' and category_name='$category_name' and department='$department' and language='$temp_language'");
					$chk_num = mysql_num_rows($chk_qry);
					if($chk_num > 0){
						mysql_query("delete from mc_products_temp where marchant_url='$marchant_url' And product_name='$product_full_name' And price='$price' and category_name='$category_name' and department='$department' and language='$temp_language'");
					}*/
					
					
					$rs1 = mysql_query("select * from mc_products_temp where product_url = '".$product_url."' and lang=1")or die(mysql_error());
					if(mysql_num_rows($rs1) <= 0){
						$ins_query = "INSERT INTO mc_products_temp set merchant_product_id='2',marchant_url='$marchant_url',marchant_logo='$marchant_logo',language='$temp_language',product_name='$product_full_name',slug='$slug',product_url='$product_url',product_description='$product_description',category_name='$category_name',brand='$brand',department='$department',currency='$currency',price='$price',image_url='$image_url',offer_id='$id_en',lang=1";
						mysql_query($ins_query);
					}
					/*if($prod_sl == 1){ // Number of products
						echo "Crawl Completed.";
						break;
					}*/
					$prod_sl++;
				}
			}
		}
	}
}


// Arabic version
$site_url = 'http://markavip.com';
$language = 'sa';
$temp_language = 'ar';
$marchant_url = $site_url;
$marchant_logo = 'http://assets.ak.markavip-cdn.com/skin/frontend/inception/default/images/logo.png';

$all_category = array("women","men","children","electronics","home");
//$all_category = array("women");
foreach($all_category as $temp_cate){
	
	// Create DOM from URL or file
	$html = file_get_html($site_url.'/'.$language.'/'.$temp_cate.'/?___lang='.$temp_language);
	
	foreach($html->find('div.group-events') as $cate_list) {
		
		foreach($cate_list->find('a') as $each_cate_list) {
									
			$category_name = addslashes($temp_cate);
			$department = addslashes($each_cate_list->find('strong.title', 0)->plaintext);
			
			$offer_end_dt = addslashes(trim($each_cate_list->find('span.subtitle', 0)->plaintext)); 
			$off = explode(" ",$offer_end_dt); 
			$off_dt = implode("/",array($off[1],date(Y))); 
			$offer_start_dt = date('Y-m-d'); 
			$offer_end_dt = implode('-', array_reverse(explode('/', $off_dt))); 
			$product_list_url = trim($each_cate_list->href);
			//$brand =  $each_cate_list->find('strong.title', 0)->plaintext;
			
					if(isset($each_cate_list->find('strong.title', 0)->plaintext)){
						$brand_nm = $each_cate_list->find('strong.title', 0)->plaintext;  
						$chk_brand =  mysql_query("SELECT id FROM  `mc_product_brands` WHERE  `slug` = '".$brand_nm."'");
						$brand_id = mysql_fetch_array($chk_brand);		
						if(isset($brand_id) && !empty($brand_id)){ 
							$brand = $brand_id['id'];
						}else{ 
							$chk_brand1 = mysql_query("INSERT INTO mc_product_brands set slug ='$brand_nm',status=1");
							$lst_insrt_id_en = mysql_query("SELECT MAX(id) as MaximumID FROM mc_product_brands"); 
							$brand_id_en = mysql_fetch_array($lst_insrt_id_en); 
							$brand = $brand_id_en["MaximumID"];
							
							$insrt_langs = mysql_query("INSERT INTO mc_product_brand_langs set brand_id ='$brand', lang_id = 2, brand_title = '$brand_nm', description = '$brand_nm',status=1 ");							
						}					
					}else{
						$brand = 'None';
						$chk_brand =  mysql_query("SELECT id FROM  `mc_product_brands` WHERE  `slug` = '".$brand."'");
						$brand_id = mysql_fetch_array($chk_brand);		
						if(isset($brand_id) && !empty($brand_id)){ 
							$brand = $brand_id['id'];
						}else{ 
							$inst = "INSERT INTO mc_product_brands set slug ='$brand',status=1";
							$chk_brand = mysql_query($inst);
							$lst_insrt_id_en = mysql_query("SELECT MAX(id) as MaximumID FROM mc_product_brands"); 
							$brand_id_en = mysql_fetch_array($lst_insrt_id_en); 
							$brand = $brand_id_en["MaximumID"];
							$insrt_langs = mysql_query("INSERT INTO mc_product_brand_langs set brand_id ='$brand', lang_id = 2, brand_title = '$brand_nm', description = '$brand_nm',status=1 ");
						}	
					}
			
			$product_list_url = trim($each_cate_list->href).'?___lang='.$temp_language;
			
			$brand =  $each_cate_list->find('strong.title', 0)->plaintext;
			// Get product list page
			$prod_list_html = file_get_html($product_list_url);
			
			foreach($prod_list_html->find('ul.products-grid') as $all_product_list) {
				
				$prod_sl = 1;
				foreach($all_product_list->find('a') as $all_product_list_link) {
					
					$product_url = $all_product_list_link->href.'?___lang='.$temp_language;
					
					// Get single product page
					$single_prod_html = file_get_html($product_url);
					
					$product_full_name = addslashes($single_prod_html->find('h1',0)->plaintext);
					
					# Get Product full name for SLUG
					if($product_full_name <> ""){
						$slug = create_url_slug($product_full_name);
					}
					# End Slug
					//echo $single_prod_html->find('div.container span',0)->plaintext; exit;
					$currency = trim($single_prod_html->find('span.sign',0)->plaintext);
					$price = trim($single_prod_html->find('span.digits',0)->plaintext);
					
					if(isset($single_prod_html->find('p.old-price', 0)->find('span.digits',0)->plaintext)){
						$old_price = $single_prod_html->find('p.old-price', 0)->find('span.digits',0)->plaintext;
						$old_price = trim(str_replace($currency, "", $old_price));
						$diff = round($old_price) - round($price);
						$percent = $diff/$old_price;
						$percent_friendly = number_format( $percent * 100, 2 ) . '%';
						$ins_qry_en = "INSERT INTO mc_offers set merchant_id='2', offer_title='$percent_friendly. Discount', discount='$percent_friendly',status=1, start_date='$offer_start_dt', end_date='$offer_end_dt'"; //echo $ins_qry;
						mysql_query($ins_qry_en);
						$lst_insrt_id_en = mysql_query("SELECT MAX(id) as MaximumID FROM mc_offers"); //echo $lst_insrt_id;exit;
						$off_id_en = mysql_fetch_array($lst_insrt_id_en); 
						$id_ar = $off_id_en["MaximumID"];
					}else{
						$id_ar = '';
					}
					
					$product_description = mysql_real_escape_string(trim($single_prod_html->find('div.std',0)->plaintext));
					$product_description = addslashes($product_description);
					
					//$image_url = '["'.$single_prod_html->find('div.product-img-box', 0)->find('a', 0)->href.'"]';
					$image_url = '"'.addslashes($single_prod_html->find('div.product-img-box', 0)->find('a', 0)->href).'"';
					if(isset($single_prod_html->find('div.more-views ul li',0)->find('img',0)->src) && !empty($single_prod_html->find('div.more-views ul li',0)->find('img',0)->src)){
						$thumb1 = '"'.addslashes(trim($single_prod_html->find('div.more-views ul li',0)->find('img',0)->src)).'"';
						$image_url = $image_url.",".$thumb1;
					}else if(isset($single_prod_html->find('div.more-views ul li',1)->find('img',0)->src) && !empty($single_prod_html->find('div.more-views ul li',1)->find('img',0)->src)){
						$thumb2 = '"'.addslashes(trim($single_prod_html->find('div.more-views ul li',1)->find('img',0)->src)).'"';
						$image_url = $image_url.",".$thumb2;
					}else if(isset($single_prod_html->find('div.more-views ul li',2)->find('img',0)->src) && !empty($single_prod_html->find('div.more-views ul li',2)->find('img',0)->src)){
						$thumb3 = '"'.addslashes(trim($single_prod_html->find('div.more-views ul li',2)->find('img',0)->src)).'"';
						$image_url = $image_url.",".$thumb3;
					}
					$image_url = '['.$image_url.']';
					
					# Check product exist in database for a particular marchant with product name
					/*$chk_qry = mysql_query("select id from mc_products_temp where marchant_url='$marchant_url' And product_name='$product_full_name' And price='$price' and language='$temp_language'");
					$chk_num = mysql_num_rows($chk_qry);
					if($chk_num > 0){
						mysql_query("delete from mc_products_temp where marchant_url='$marchant_url' And product_name='$product_full_name' And price='$price' and language='$temp_language'");
					}*/
					#
					
					$rs1 = mysql_query("select * from mc_products_temp where product_url = '".$product_url."' and lang=2")or die(mysql_error());
					if(mysql_num_rows($rs1) <= 0){	
					# query string
						$ins_query = "INSERT INTO mc_products_temp set merchant_product_id='2',marchant_url='$marchant_url',marchant_logo='$marchant_logo',language='$temp_language',product_name='$product_full_name',slug='$slug',product_url='$product_url',product_description='$product_description',category_name='$category_name',brand='$brand',department='$department',currency='$currency',price='$price',image_url='$image_url',offer_id='$id_ar',lang=2";
						mysql_query($ins_query);
					}
					/*if($prod_sl == 2){ 
					break;
					}*/
					$prod_sl++;
				}
			}
		}
	}
}

# update crawl_end_date
mysql_query("update mc_merchants_new set crawl_end_date=now() where site_id > '0'");
mysql_query("update mc_merchants_new set site_id='0' where site_id > '0'");

// Slug generate function
function create_url_slug($string){
   $slug=preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
   return strtolower($slug);
}
mysql_query("update mc_merchants_new set crawl_end_date=now() where id=2");
mysql_query("update mc_merchants_new set site_id=0");
?>