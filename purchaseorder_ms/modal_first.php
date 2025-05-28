<?php

//error_reporting(E_ALL); 
//ini_set('display_errors', '1');


session_start();

$memberID = $_SESSION['memberID'];
$powerkey = $_SESSION['powerkey'];


@include_once("/website/class/".$site_db."_info_class.php");

//載入公用函數
@include_once '/website/include/pub_function.php';


$m_location		= "/website/smarty/templates/".$site_db."/".$templates;
$m_pub_modal	= "/website/smarty/templates/".$site_db."/pub_modal";


$sid = "";
if (isset($_GET['sid']))
	$sid = $_GET['sid'];

	//程式分類
	$ch = empty($_GET['ch']) ? 'default' : $_GET['ch'];
	switch($ch) {
		case 'add':
			$title = "新增採購單";
			$sid = "view01";
			$modal = $m_location."/sub_modal/project/func09/purchaseorder_ms/purchaseorder_add.php";
			include $modal;
			$smarty->assign('show_center',$show_center);
			$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
			break;
		case 'edit':
			$title = "編輯採購單";
			$sid = "view01";
			$modal = $m_location."/sub_modal/project/func09/purchaseorder_ms/purchaseorder_modify.php";
			include $modal;
			$smarty->assign('show_center',$show_center);
			$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
			break;
		case 'ch_employee':
			$title = "員工名單";
			if (empty($sid))
				$sid = "view01";
			$modal = $m_location."/sub_modal/project/func09/purchaseorder_ms/ch_employee.php";
			include $modal;
			$smarty->assign('show_center',$show_center);
			$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
			break;
		case 'purchaseorder_detail_add':
			$title = "新增料件";
			$sid = "view01";
			$modal = $m_location."/sub_modal/project/func09/purchaseorder_ms/purchaseorder_detail_add.php";
			include $modal;
			$smarty->assign('show_center',$show_center);
			$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
			break;
		case 'purchaseorder_detail_modify':
			$title = "編輯料件";
			$sid = "view01";
			$modal = $m_location."/sub_modal/project/func09/purchaseorder_ms/purchaseorder_detail_modify.php";
			include $modal;
			$smarty->assign('show_center',$show_center);
			$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
			break;
		case 'purchaseorder_to_stock_in_add':
			$title = "採購單轉入庫單";
			$sid = "view01";
			$modal = $m_location."/sub_modal/project/func09/purchaseorder_ms/purchaseorder_to_stock_in_add.php";
			include $modal;
			$smarty->assign('show_center',$show_center);
			$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
			break;
		case 'stock_in':
			$title = "入庫作業";
			$sid = "view01";
			$modal = $m_location."/sub_modal/project/func04/stock_in_ms/stock_in.php";
			include $modal;
			$smarty->assign('show_center',$show_center);
			$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
			break;
		default:
			if (empty($sid))
				$sid = "mbpjitem";
			$modal = $m_location."/sub_modal/project/func09/purchaseorder_ms/purchaseorder.php";
			include $modal;
			$smarty->assign('show_center',$show_center);
			$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
			break;
	};

?>