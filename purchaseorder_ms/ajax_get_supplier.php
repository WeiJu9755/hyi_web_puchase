<?php

header('Content-Type: application/json; charset=utf-8');

$site_db = $_POST['site_db'];
$supplier_id = $_POST['supplier_id'];

//載入公用函數
//@include_once '/website/include/pub_function.php';

@include_once("/website/class/".$site_db."_info_class.php");

$mDB = "";
$mDB = new MywebDB();

//先檢查是否已在在
$Qry="select supplier_name from supplier where supplier_id = '$supplier_id'";
$mDB->query($Qry);
$caption = "";
if ($mDB->rowCount() > 0) {
    //已找到符合資料
	$row=$mDB->fetchRow(2);
	$supplier_name = $row['supplier_name'];
}
$mDB->remove();


$return_val=array(
	"success"=>true
	,"supplier_name"=>$supplier_name
);

echo json_encode($return_val, JSON_UNESCAPED_UNICODE);

?>