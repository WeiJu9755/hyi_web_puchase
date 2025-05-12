<?php

session_start();

$memberID = $_SESSION['memberID'];
$powerkey = $_SESSION['powerkey'];


require_once '/website/os/Mobile-Detect-2.8.34/Mobile_Detect.php';
$detect = new Mobile_Detect;


//載入公用函數
@include_once '/website/include/pub_function.php';

//連結資料
@include_once("/website/class/".$site_db."_info_class.php");

/* 使用xajax */
@include_once '/website/xajax/xajax_core/xajax.inc.php';
$xajax = new xajax();

$xajax->registerFunction("processform");
function processform($aFormValues){

	$objResponse = new xajaxResponse();
	
	$web_id				= trim($aFormValues['web_id']);
	$purchase_order_id		= trim($aFormValues['purchase_order_id']);
	
	if (trim($aFormValues['order_date']) == "") {
		$objResponse->script("jAlert('警示', '請輸入入庫日期', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	if (trim($aFormValues['stock_in_type']) == "") {
		$objResponse->script("jAlert('警示', '請選擇入庫類型', 'red', '', 2000);");
		return $objResponse;
		exit;
	}


	SaveValue($aFormValues);
	
	$objResponse->script("setSave();");
	$objResponse->script("parent.myDraw();");

	$objResponse->script("parent.$.fancybox.close();");
		
	return $objResponse;
}


$xajax->registerFunction("SaveValue");
function SaveValue($aFormValues){

	$objResponse = new xajaxResponse();
	
		//進行存檔動作
		$site_db				= trim($aFormValues['site_db']);
		$purchase_order_id			= trim($aFormValues['purchase_order_id']);
		$order_date			= trim($aFormValues['order_date']);
		$stock_in_type			= trim($aFormValues['stock_in_type']);
		$source_code			= trim($aFormValues['source_code']);
		$supplier_id			= trim($aFormValues['supplier_id']);
		$warehouse				= trim($aFormValues['warehouse']);
		$handler_id				= trim($aFormValues['handler_id']);
		$remarks				= trim($aFormValues['remarks']);
		$status					= trim($aFormValues['status']);
		$memberID				= trim($aFormValues['memberID']);
		
		//存入實體資料庫中
		$mDB = "";
		$mDB = new MywebDB();

		$Qry="UPDATE stock_in set
				 order_date			= '$order_date'
				,stock_in_type		= '$stock_in_type'
				,source_code		= '$source_code'
				,supplier_id		= '$supplier_id'
				,warehouse			= '$warehouse'
				,handler_id			= '$handler_id'
				,remarks			= '$remarks'
				,last_modify		= now()
				where purchase_order_id = '$purchase_order_id'";
				
		$mDB->query($Qry);
        $mDB->remove();

		
	return $objResponse;
}

$xajax->registerFunction("stock_in_detailDeleteRow");
function stock_in_detailDeleteRow($auto_seq){

	$objResponse = new xajaxResponse();
	
	$mDB = "";
	$mDB = new MywebDB();

	//刪除子資料
	$Qry="delete from stock_in_detail where auto_seq = '$auto_seq'";
	$mDB->query($Qry);

	$mDB->remove();
	
    $objResponse->script("oTable = $('#stock_in_detail_table').dataTable();oTable.fnDraw(false)");
	$objResponse->script("autoclose('提示', '資料已刪除！', 500);");

	return $objResponse;
	
}


$xajax->processRequest();


$fm = $_GET['fm'];
$purchase_order_id = $_GET['purchase_order_id'];

$mess_title = $title;

//$pro_id = "com";


$mDB = "";
$mDB = new MywebDB();

$Qry="SELECT a.*,b.employee_name,c.supplier_name,d.contract_caption FROM purchaseorder a
LEFT JOIN employee b ON b.employee_id = a.handler_id
LEFT JOIN supplier c ON c.supplier_id = a.company_id
LEFT JOIN contract d ON d.contract_id = a.contract_id";
$mDB->query($Qry);
$total = $mDB->rowCount();
if ($total > 0) {
    //已找到符合資料
	$row=$mDB->fetchRow(2);
	$purchase_order_id = $row['purchase_order_id'];
	$contract_id = $row['contract_id'];
	$contract_caption = $row['contract_caption'];
	$handler_id = $row['handler_id'];
	$purchase_type = $row['purchase_type'];
	$order_date = $row['order_date'];
	$delivery_date = $row['delivery_date'];
	$company_id = $row['company_id'];
	$company_name = $row['supplier_name'];
	$location = $row['location'];
	$makeby = $row['makeby'];
	$employee_name = $row['employee_name'];
  
}

//載入入庫類型
$Qry="SELECT caption FROM items where pro_id ='stock_in_type' ORDER BY pro_id,orderby";
$mDB->query($Qry);
$select_stock_in_type = "";
$select_stock_in_type .= "<option></option>";

if ($mDB->rowCount() > 0) {
	while ($row=$mDB->fetchRow(2)) {
		$ch_stock_in_type = $row['caption'];
		$select_stock_in_type .= "<option value=\"$ch_stock_in_type\" ".mySelect($ch_stock_in_type,$stock_in_type).">$ch_stock_in_type</option>";
	}
}

//載入物料來源
$Qry="SELECT caption FROM items where pro_id ='source_code' ORDER BY pro_id,orderby";
$mDB->query($Qry);
$select_source_code = "";
$select_source_code .= "<option></option>";

if ($mDB->rowCount() > 0) {
	while ($row=$mDB->fetchRow(2)) {
		$ch_source_code = $row['caption'];
		$select_source_code .= "<option value=\"$ch_source_code\" ".mySelect($ch_source_code,$source_code).">$ch_source_code</option>";
	}
}

//載入廠商
$Qry="select supplier_id,supplier_name from supplier order by auto_seq";
$mDB->query($Qry);
$select_supplier = "";
$select_supplier .= "<option value=\"$company_id\" ".mySelect($company_id,$company_id).">$company_id $company_name</option>";
$select_supplier .= "<option></option>";
if ($mDB->rowCount() > 0) {
	while ($row=$mDB->fetchRow(2)) {
		$ch_supplier_id = $row['supplier_id'];
		$ch_supplier_name = $row['supplier_name'];
		$select_supplier .= "<option value=\"$ch_supplier_id\" ".mySelect($ch_supplier_id,$supplier_id).">$ch_supplier_id $ch_supplier_name</option>";
	}
}

//載入倉庫別
$Qry="SELECT caption FROM items where pro_id ='warehouse' ORDER BY pro_id,orderby";
$mDB->query($Qry);
$select_warehouse = "";
$select_warehouse .= "<option></option>";

if ($mDB->rowCount() > 0) {
	while ($row=$mDB->fetchRow(2)) {
		$ch_warehouse = $row['caption'];
		$select_warehouse .= "<option value=\"$ch_warehouse\" ".mySelect($ch_warehouse,$warehouse).">$ch_warehouse</option>";
	}
}


$mDB->remove();


$show_savebtn=<<<EOT
<div class="btn-group vbottom" role="group" style="margin-top:5px;">
	<button id="save" class="btn btn-primary" type="button" onclick="CheckValue(this.form);" style="padding: 5px 15px;"><i class="bi bi-check-circle"></i>&nbsp;存檔</button>
	<button id="cancel" class="btn btn-secondary display_none" type="button" onclick="setCancel();" style="padding: 5px 15px;"><i class="bi bi-x-circle"></i>&nbsp;取消</button>
	<button id="close" class="btn btn-danger" type="button" onclick="parent.myDraw();parent.$.fancybox.close();" style="padding: 5px 15px;"><i class="bi bi-power"></i>&nbsp;關閉</button>
</div>
EOT;


if (!($detect->isMobile() && !$detect->isTablet())) {
	$isMobile = 0;
	
$style_css=<<<EOT
<style>

.card_full {
    width: 100%;
	height: 100vh;
}

#full {
    width: 100%;
	height: 100vh;
}

#info_container {
	width: 100% !Important;
	max-width: 1400px; !Important;
	margin: 0 auto !Important;
}

.field_div1 {width:200px;display: none;font-size:18px;color:#000;text-align:right;font-weight:700;padding:15px 10px 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}
.field_div2 {width:100%;max-width:900px;display: none;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 0 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}
.field_div3 {width:100%;max-width:450px;display: none;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 0 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}

</style>

EOT;

} else {
	$isMobile = 1;

$style_css=<<<EOT
<style>

.card_full {
    width: 100vw;
	height: 100vh;
}

#full {
    width: 100vw;
	height: 100vh;
}

#info_container {
	width: 100% !Important;
	margin: 0 auto !Important;
}

.field_div1 {width:100%;display: block;font-size:18px;color:#000;text-align:left;font-weight:700;padding:15px 10px 0 0;vertical-align: top;}
.field_div2 {width:100%;display: block;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 10px 0 0;vertical-align: top;}
.field_div3 {width:100%;display: block;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 10px 0 0;vertical-align: top;}

</style>
EOT;

}



include $m_location."/sub_modal/project/func04/stock_in_ms/stock_in_detail.php";


$show_fellow_btn=<<<EOT
<div class="btn-group" role="group">
	<button type="button" class="btn btn-danger btn-sm text-nowrap px-3" onclick="openfancybox_edit('/index.php?ch=stock_in_detail_add&purchase_order_id=$purchase_order_id&fm=$fm',800,'96%','');"><i class="bi bi-plus-circle"></i>&nbsp;新增料件</button>
	<button type="button" class="btn btn-success btn-sm text-nowrap px-3" onclick="stock_in_detail_myDraw();"><i class="bi bi-arrow-repeat"></i>&nbsp;重整</button>
</div>
EOT; 

$show_fellow_btn2 = "";
if ($status == "待入庫") {
$show_fellow_btn2=<<<EOT
<div class="btn-group float-end me-5" role="group">
	<button type="button" class="btn btn-success btn-sm text-nowrap px-3" onclick=""><i class="bi bi-box-arrow-in-down"></i>&nbsp;執行入庫作業</button>
</div>
EOT; 
}

$show_center=<<<EOT
<script src="/os/Autogrow-Textarea/jquery.autogrowtextarea.min.js"></script>

$style_css

<div class="card card_full">
	<div class="card-header text-bg-info">
		<div class="size14 weight float-start" style="margin-top: 5px;">
			$mess_title
		</div>
		<div class="float-end" style="margin-top: -5px;">
			$show_savebtn
		</div>
	</div>
	<div id="full" class="card-body data-overlayscrollbars-initialize">
		<div id="info_container">
			<form method="post" id="modifyForm" name="modifyForm" enctype="multipart/form-data" action="javascript:void(null);">
			<div class="w-100">
				<div class="field_container3">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-md-12">
								<div class="field_div1">採購編號:</div>
								<div class="field_div2"><div class="blue weight mt-2">$purchase_order_id</div></div>
							</div> 
						</div>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">採購日期:</div> 
								<div class="field_div3 mt-2">								
										$order_date
								</div> 
							</div> 
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">合約名稱:</div> 
								<div class="field_div3">
									<div class="blue weight mt-2">$contract_caption</div>
								</div> 
							</div> 
						</div>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">供應商:</div> 
								<div class="field_div3">
									<select id="supplier_id" name="supplier_id" placeholder="請選擇供應商" style="width:100%;max-width:350px;" value="$company_id">
										$select_supplier
									</select>
								</div> 
							</div> 
						</div>
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">經辦人:</div> 
								<div class="field_div3">
									<div class="input-group text-nowrap" style="width:100%;max-width:450px;">
										<input readonly type="text" class="form-control w-25" id="handler_id" name="handler_id" aria-describedby="handler_id_addon" value="$handler_id"/>
										<input readonly type="text" class="form-control w-50" id="employee_name" name="employee_name"  value="$employee_name"/>
										<button class="btn btn-outline-secondary w-25" type="button" id="handler_id_addon" onclick="openfancybox_edit('/index.php?ch=ch_employee&fm=$fm',800,'96%','');">選擇員工</button>
									</div> 
								</div> 
							</div> 
							<div class="col-lg-6 col-sm-12 col-md-12">
								
							</div> 
						</div>
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">需求描述:</div> 
								<div class="field_div3">
									<textarea class="inputtext w-100 p-3" id="remarks" name="remarks" cols="80" rows="1" style="max-width: 500px;" onchange="setEdit();">$remarks</textarea>
								</div> 
							</div> 
							<div class="col-lg-12 col-sm-12 col-md-12">
								<div class="field_div1">施作地點:</div> 
								<div class="field_div2">
									<input type="text" class="inputtext" name="location" id="location" size="50" placeholder="請輸入施作地點" maxlength="50" style="width:100%;max-width:250px;"/>
								</div> 
							</div> 
						</div>
					</div>
					<div style="margin: 10px 0 -15px 120px;">$show_fellow_btn $show_fellow_btn2</div>
					$show_stock_in_detail
					<div>
						<input type="hidden" name="fm" value="$fm" />
						<input type="hidden" name="site_db" value="$site_db" />
						<input type="hidden" name="purchase_order_id" value="$purchase_order_id" />
						<input type="hidden" name="memberID" value="$memberID" />
					</div>
				</div>
			</div>
			</form>
		</div>
	</div>
</div>
<script>

function CheckValue(thisform) {
	xajax_processform(xajax.getFormValues('modifyForm'));
	thisform.submit();
}

function SaveValue(thisform) {
	xajax_SaveValue(xajax.getFormValues('modifyForm'));
	thisform.submit();
}

function setEdit() {
	$('#close', window.document).addClass("display_none");
	$('#cancel', window.document).removeClass("display_none");
}

function setCancel() {
	$('#close', window.document).removeClass("display_none");
	$('#cancel', window.document).addClass("display_none");
	document.forms[0].reset();
}

function setSave() {
	$('#close', window.document).removeClass("display_none");
	$('#cancel', window.document).addClass("display_none");
}

$(document).ready(function() {
	$("#remarks").autoGrow({
		extraLine: true // Adds an extra line at the end of the textarea. Try both and see what works best for you.
	});
});



$(document).ready(async function() {
	//等待其他資源載入完成，此方式適用大部份瀏覽器
	await new Promise(resolve => setTimeout(resolve, 100));
	$('#order_date').focus();
});

</script>

EOT;

?>