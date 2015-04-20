<?php
// jQuery port to PHP
require('phpQuery.php');
//  additional functions
require('core.php');

// Configuration section

$offset = 50;
$currentOffset = 0;
$sourcecUrl = 'http://localhost';
$restartTrigger = false;
// In this file there should be links to categories where we need to grab product links
$fileCategoriesLinksCsv = 'data/CategoriesLinks.csv';
// Urls of products to grab info - name,price,image etc.
$fileProductLinksCsv = 'data/ProductLinks.csv';
// products info ready to import to th DB
$fileProductDataCsv = 'data/ProductData.csv';

// Mysql connection data
$hostname = 'localhost';
$databaseName = 'databaseName';
$username = 'username';
$password = 'password';
$dbSuffix = 'tmsti_';

// tables that are used by virtuemart 3 RU to save product and related stuff
// virtuemart_products
// virtuemart_products_ru_ru
// virtuemart_product_categories
// virtuemart_product_prices
// virtuemart_product_customfields
// virtuemart_medias
// virtuemart_product_medias


if (!empty($_REQUEST['offset']) AND is_numeric($_REQUEST['offset'])) {
	$currentOffset = (int)$_REQUEST['offset'];
	//outputVar($currentOffset);
}
if(file_exists($fileProductDataCsv) AND 0 !== filesize($fileProductDataCsv)){
	$lineNumber = 0;
	$fileProductData = fopen($fileProductDataCsv, 'a+');	//Open file
	while (($data = fgets($fileProductData)) !== FALSE) {
		$dataArray = explode("|", $data);

		if ($lineNumber < $currentOffset) {
			
			$lineNumber++;
			continue;
			
		} else {			
			// Set category
			
			$categoryId = 0;
			switch ($dataArray[0]) {
				// Project specific categories
				case 'Бюджет': $categoryId = 3; break;
				case 'Yogin: Classic Series': $categoryId = 12; break;
				case 'Универсал': $categoryId = 11; break;
				case 'Yogin: Master Series': $categoryId = 13; break;
				case 'F.A.Bodhi': $categoryId = 14; break;
				case 'Профи': $categoryId = 15; break;
				case 'Ojas Salamander': $categoryId = 16; break;
				case 'Эко': $categoryId = 17; break;
				case 'Сумки для ковриков': $categoryId = 18; break;
				case 'Чехлы для ковриков': $categoryId = 20; break;
				case 'Стяжки': $categoryId = 21; break;
				case 'Для Йоги Критического Выравнивания': $categoryId = 22; break;
				case 'Ремни': $categoryId = 23; break;
				case 'Оборудование': $categoryId = 19; break;
				case 'Подушки и болстеры': $categoryId = 24; break;
				case 'Ароматерапия': $categoryId = 8; break;
				case 'Благовония': $categoryId = 8; break;
				case 'Подставки под благовония': $categoryId = 8; break;
				case 'Чётки': $categoryId = 9; break;
				case 'Подарки': $categoryId = 9; break;
				case 'Аюрведа': $categoryId = 7; break;
				//case 'Косметика': $categoryId = 0; break;
				case 'Гигиена': $categoryId = 6; break;
			}
			//outputVar($categoryId);
			if ($categoryId == 0) {
				$lineNumber++;
				continue;
			}
			
 			$currentDate = date('Y-m-d H:i:s');
			
			$db = new mysqli($hostname, $username, $password, $databaseName);
			$db->set_charset("utf8");
			// Add product
			$db->query("INSERT INTO `".$dbSuffix."virtuemart_products` 
				(`virtuemart_product_id`, `virtuemart_vendor_id`, `product_parent_id`, `product_sku`, `product_gtin`, `product_mpn`, `product_weight`, `product_weight_uom`,
				`product_length`, `product_width`, `product_height`, `product_lwh_uom`, `product_url`, `product_in_stock`, `product_ordered`, `low_stock_notification`, `product_available_date`, 
				`product_availability`, `product_special`, `product_sales`, `product_unit`, `product_packaging`, `product_params`, `hits`, `intnotes`, `metarobot`, `metaauthor`, `layout`, `published`, 
				`pordering`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`)
				VALUES
				(0, 1, 0, '', '', '', NULL, 'KG', NULL, NULL, NULL, 'M', '', 0, 0, 0, '".$currentDate."', '', 0, 0, 'KG', NULL, 'min_order_level=\"\"|max_order_level=\"\"|step_order_level=\"\"|product_box=\"\"|', NULL, '', '', '', 'notify', 1, 0, '".$currentDate."', 655, '".$currentDate."', 655, '0000-00-00 00:00:00', 0);
			");
			// Now we have product ID
			$productId = $db->insert_id;
			outputVar($productId);
			outputVar($dataArray);
			// Add product title, slug description
 			$db->query("INSERT INTO `".$dbSuffix."virtuemart_products_ru_ru` 
			(`virtuemart_product_id`, `product_s_desc`, `product_desc`, `product_name`, `metadesc`, `metakey`, `customtitle`, `slug`)
			VALUES
			(".$productId.", '".mysqli_real_escape_string($db,substr($dataArray[5],0,250).'...')."', '".mysqli_real_escape_string($db,$dataArray[5])."', '".mysqli_real_escape_string($db,$dataArray[3])."', '', '', '', '".mysqli_real_escape_string($db,$dataArray[6])."');");

			// Add category
 			$db->query("INSERT INTO `".$dbSuffix."virtuemart_product_categories` 
			(`id`, `virtuemart_product_id`, `virtuemart_category_id`, `ordering`) 
			VALUES
			(0, ".$productId.", ".$categoryId.", 0);");
			
			// Add price
			$priceArray = explode(" ", $dataArray[4]);
 			$db->query("INSERT INTO `".$dbSuffix."virtuemart_product_prices`
			(`virtuemart_product_price_id`, `virtuemart_product_id`, `virtuemart_shoppergroup_id`, `product_price`, `override`, `product_override_price`, `product_tax_id`, `product_discount_id`, 
			`product_currency`, `product_price_publish_up`, `product_price_publish_down`, `price_quantity_start`, `price_quantity_end`, `created_on`, `created_by`, `modified_on`, `modified_by`,
			`locked_on`, `locked_by`) 
			VALUES
			(0, ".$productId.", 0, '".$priceArray[0]."', 0, '0.00000', 0, 0, 131, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, '".$currentDate."', 655, '".$currentDate."', 655, '0000-00-00 00:00:00', 0);
			");
			
			// Add custom fields
			
			// Color
			if (!empty($dataArray[7])) {
				$db->query("INSERT INTO `".$dbSuffix."virtuemart_product_customfields` 
				(`virtuemart_customfield_id`, `virtuemart_product_id`, `virtuemart_custom_id`, `customfield_value`, 
				`customfield_price`, `disabler`, `override`, `customfield_params`, `product_sku`, `product_gtin`, `product_mpn`, `published`, `created_on`, `created_by`, `modified_on`, 
				`modified_by`, `locked_on`, `locked_by`, `ordering`) 
				VALUES
				(0, ".$productId.", 6, '".mysqli_real_escape_string($db,$dataArray[7])."', NULL, 0, 0, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', 0, '".$currentDate."', 655, '0000-00-00 00:00:00', 0, 0);
				");
			} */
			// Length
			if (!empty($dataArray[11])) {
				$db->query("INSERT INTO `".$dbSuffix."virtuemart_product_customfields` 
				(`virtuemart_customfield_id`, `virtuemart_product_id`, `virtuemart_custom_id`, `customfield_value`, 
				`customfield_price`, `disabler`, `override`, `customfield_params`, `product_sku`, `product_gtin`, `product_mpn`, `published`, `created_on`, `created_by`, `modified_on`, 
				`modified_by`, `locked_on`, `locked_by`, `ordering`) 
				VALUES
				(0, ".$productId.", 7, '".mysqli_real_escape_string($db,$dataArray[11])."', NULL, 0, 0, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', 0, '".$currentDate."', 655, '0000-00-00 00:00:00', 0, 0);
				");
			}
			// width
			if (!empty($dataArray[10])) {
				$db->query("INSERT INTO `".$dbSuffix."virtuemart_product_customfields` 
				(`virtuemart_customfield_id`, `virtuemart_product_id`, `virtuemart_custom_id`, `customfield_value`, 
				`customfield_price`, `disabler`, `override`, `customfield_params`, `product_sku`, `product_gtin`, `product_mpn`, `published`, `created_on`, `created_by`, `modified_on`, 
				`modified_by`, `locked_on`, `locked_by`, `ordering`) 
				VALUES
				(0, ".$productId.", 8, '".mysqli_real_escape_string($db,$dataArray[10])."', NULL, 0, 0, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', 0, '".$currentDate."', 655, '0000-00-00 00:00:00', 0, 0);
				");
			}			
			// thickness
			if (!empty($dataArray[9])) {
				$db->query("INSERT INTO `".$dbSuffix."virtuemart_product_customfields` 
				(`virtuemart_customfield_id`, `virtuemart_product_id`, `virtuemart_custom_id`, `customfield_value`, 
				`customfield_price`, `disabler`, `override`, `customfield_params`, `product_sku`, `product_gtin`, `product_mpn`, `published`, `created_on`, `created_by`, `modified_on`, 
				`modified_by`, `locked_on`, `locked_by`, `ordering`) 
				VALUES
				(0, ".$productId.", 9, '".mysqli_real_escape_string($db,$dataArray[9])."', NULL, 0, 0, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', 0, '".$currentDate."', 655, '0000-00-00 00:00:00', 0, 0);
				");
			}
			
			// add virtuemart media
			$url = $sourcecUrl . $dataArray[1];
			$imageProperties = pathinfo($url);
			$numMatches = preg_match('/^[A-Za-z0-9!@#%$&.]+$/', $dataArray[6], $matches);
			$imageName = $dataArray[6] . '.' . $imageProperties['extension'];
			$img = 'images/stories/virtuemart/product/' . $imageName;
			if (!file_exists('../'.$img)) {
				
				file_put_contents('../'.$img, file_get_contents($url));
			}

 			$db->query("INSERT INTO `".$dbSuffix."virtuemart_medias` 
			(`virtuemart_media_id`, `virtuemart_vendor_id`, `file_title`, `file_description`, `file_meta`, `file_class`, `file_mimetype`, `file_type`, `file_url`, `file_url_thumb`, `file_is_product_image`,
			`file_is_downloadable`, `file_is_forSale`, `file_params`, `file_lang`, `shared`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`)
			VALUES
			(0, 1, '".$imageName."', '', '', '', 'image/".$imageProperties['extension']."', 'product', '".$img."', '', 0, 0, 0, '', '', 0, 1, '".$currentDate."', 655, '".$currentDate."', 655, 
			'0000-00-00 00:00:00', 0);");
			// Now we have Media ID
			$mediaId = $db->insert_id;
			
			// add media to product
			$db->query("INSERT INTO `".$dbSuffix."virtuemart_product_medias` 
			(`id`, `virtuemart_product_id`, `virtuemart_media_id`, `ordering`) 
			VALUES
			(0, ".$productId.", ".$mediaId.", 1);");
			
			$lineNumber++;
			
			if ($lineNumber > ($currentOffset + $offset)) {
				$restartTrigger = true;
				break;
			}
			
		}
			
    }

	fclose($fileProductData);

	
	if ($restartTrigger AND $lineNumber > ($currentOffset + $offset)) {
		header("Location: parse.php?offset=".$lineNumber);
		exit;
	}
	exit;
}
if(file_exists($fileProductLinksCsv) AND 0 !== filesize($fileProductLinksCsv)){
	$lineNumber = 0;
	$fileProductLinks = fopen($fileProductLinksCsv, 'a+');	//Open file
	
	// If file with product urls is not empty start parsing data
	while (($data = fgetcsv($fileProductLinks,",")) !== FALSE) {
		
		if ($lineNumber < $currentOffset) {
			
			$lineNumber++;
			continue;
			
		} else {
			
			// request html
			$html = request($sourcecUrl.$data[0]);
			// intialize new DOM from markup
			$document = phpQuery::newDocumentHTML($html, $charset = 'utf-8');
			
			if (!empty($document->find('div.cpt_category_tree .child_current > a')->text())){
				$productData[$data[0]]['category'] = $document->find('div.cpt_category_tree .child_current > a')->text();
			} else {
				$productData[$data[0]]['category'] = $document->find('div.cpt_category_tree .parent_current > a')->text();
			}
			$productData[$data[0]]['img_small'] = $document->find('#img-current_picture')->attr('src');
			$productData[$data[0]]['img_large'] = $document->find('#img-current_picture')->parents('')->attr('href');
			$productData[$data[0]]['name'] = trim($document->find('div.cpt_product_name')->text());
			$productData[$data[0]]['price'] = trim($document->find('span.totalPrice')->text());
			$productData[$data[0]]['description'] = str_replace(array("\n","\r\n","\r"), '', $document->find('div.cpt_product_description > div')->text());
			$productData[$data[0]]['slug'] = '';
			$slug = array_filter(explode('/',$data[0]));
			if (is_array($slug)) {
				foreach ($slug as $slug_value) {
					if ($slug_value != 'product' AND strlen($slug_value) > 1) {
						$productData[$data[0]]['slug'] = $slug_value;
					}
				}
			}

			$productData[$data[0]]['color'] = '';
			if (!empty( $document->find('select[name="option_5"]'))) {
				foreach($document->find('select[name="option_5"] option') as $a) {
					if ($a->nodeValue != 'Не определено') {
						$productData[$data[0]]['color'] .= trim($a->nodeValue).',';
					}
				}
				unset($a);
			}
			
			$productData[$data[0]]['weight'] = '';
			if (!empty( $document->find('select[name="option_4"]'))) {
				foreach($document->find('select[name="option_4"] option') as $a) {
					if ($a->nodeValue != 'Не определено') {
						$productData[$data[0]]['weight'] .= trim($a->nodeValue).',';
					}
				}
				unset($a);
			}
			
			$productData[$data[0]]['deep'] = '';		
			if (!empty( $document->find('select[name="option_3"]'))) {
				foreach($document->find('select[name="option_3"] option') as $a) {
					if ($a->nodeValue != 'Не определено') {
						$productData[$data[0]]['deep'] .= trim($a->nodeValue).',';
					}
				}
				unset($a);
			}
			
			$productData[$data[0]]['width'] = '';
			if (!empty( $document->find('select[name="option_2"]'))) {
				foreach($document->find('select[name="option_2"] option') as $a) {
					if ($a->nodeValue != 'Не определено') {
						$productData[$data[0]]['width'] .= trim($a->nodeValue).',';
					}
				}
				unset($a);
			}
			
			$productData[$data[0]]['length'] = '';
			if (!empty( $document->find('select[name="option_1"]'))) {
				foreach($document->find('select[name="option_1"] option') as $a) {
					if ($a->nodeValue != 'Не определено') {
						$productData[$data[0]]['length'] .= trim($a->nodeValue).',';
					}
				}
				unset($a);
			}
			//outputVar($lineNumber);
			$lineNumber++;
			if ($lineNumber > ($currentOffset + $offset)) {
				$restartTrigger = true;
				break;
			}
			
		}
			
    }

	fclose($fileProductLinks);
	
	$fileProductData = fopen($fileProductDataCsv, 'a+');	//Open file
	
	if($fileProductData AND !empty($productData)){
		//outputVar($productData);
		$inputspace = '';
		foreach($productData as $product) {
			$inputspace .= implode("|", $product) . "\n";
		}

		fwrite($fileProductData, $inputspace); 
	}
	fclose($fileProductData);
	
	if ($restartTrigger AND $lineNumber > ($currentOffset + $offset)) {
		header("Location: parse.php?offset=".$lineNumber);
		exit;
	}
	
} else {
	
	// If NO product urls - start grabbing them
	$fileCategoriesLinks = fopen($fileCategoriesLinksCsv, 'a+');
	
	if($fileCategoriesLinks AND 0 !== filesize($fileCategoriesLinksCsv)){
		
		while (($data = fgetcsv($fileCategoriesLinks,",")) !== FALSE) {
			// request html
			$html = request($data[0]);
			// intialize new DOM from markup
			$document = phpQuery::newDocumentHTML($html, $charset = 'utf-8');
			
			foreach($document->find('ul.products-list div.h3 > a') as $a) {
				//add each link to array
				$links[] = pq($a)->attr('href');
			}
			
		}
		
		fclose($fileCategoriesLinks);
		
		$fileProductLinks = fopen($fileProductLinksCsv, 'a+');	//Open file
		
		if($fileProductLinks){
			$inputspace = '';
			foreach($links as $link) {
				$inputspace .= $link . "\n";
			}

			fwrite($fileProductLinks, $inputspace); 
			fclose($fileProductLinks);
		}
		
	}
}
?>
