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
	$order_date		= trim($aFormValues['order_date']);
	$purchase_order_id		= trim($aFormValues['purchase_order_id']);
	
	if (trim($aFormValues['order_date']) == "") {
		$objResponse->script("jAlert('警示', '請輸入採購日期', 'red', '', 2000);");
		return $objResponse;
		exit;
	}

	SaveValue($aFormValues);
	
	$objResponse->script("setSave();");
	$objResponse->script("parent.myDraw();");

	//$objResponse->script("parent.$.fancybox.close();");
	//$objResponse->script("jAlert('警示', '已存檔', 'green', '', 500);");
		
	return $objResponse;
}


$xajax->registerFunction("SaveValue");
function SaveValue($aFormValues){


	$objResponse = new xajaxResponse();
	
		//進行存檔動作
		$site_db						= trim($aFormValues['site_db']);
		$purchase_order_id				= trim($aFormValues['purchase_order_id']);
		$order_date						= trim($aFormValues['order_date']);
		$supplier_id					= trim($aFormValues['supplier_id']);
		$handler_id						= trim($aFormValues['handler_id']);
		$requirement_description		= trim($aFormValues['requirement_description']);
		$delivery_date 					= trim($aFormValues['delivery_date']);
		$memberID						= trim($aFormValues['memberID']);
		
		//存入實體資料庫中
		$mDB = "";
		$mDB = new MywebDB();

		$Qry="UPDATE purchaseorder set
				 order_date							= '$order_date'
				,supplier_id						= '$supplier_id'
				,handler_id							= '$handler_id'
				,requirement_description			= '$requirement_description'
				,delivery_date						= '$delivery_date'
				,last_modify						= now()
				where purchase_order_id 			= '$purchase_order_id'";
				
		$mDB->query($Qry);
        $mDB->remove();

		
	return $objResponse;
}

$xajax->registerFunction("purchaseorder_detailDeleteRow");
function purchaseorder_detailDeleteRow($auto_seq){

	$objResponse = new xajaxResponse();
	
	$mDB = "";
	$mDB = new MywebDB();

	//刪除子資料
	$Qry="delete from purchaseorder_detail where auto_seq = '$auto_seq'";
	$mDB->query($Qry);

	$mDB->remove();
	
    $objResponse->script("oTable = $('#purchaseorder_detail_table').dataTable();oTable.fnDraw(false)");
	$objResponse->script("autoclose('提示', '資料已刪除！', 500);");

	return $objResponse;
	
}

$xajax->registerFunction("execute_purchaseorder");
function execute_purchaseorder($purchase_order_id){

	$objResponse = new xajaxResponse();
	
	$mDB = "";
	$mDB = new MywebDB();

	//檢查此入庫單資料是否可以進行入庫作業
	//先檢查主檔資料
	$Qry="SELECT * FROM purchaseorder
	where purchase_order_id = '$purchase_order_id'";
	$mDB->query($Qry);
	if ($mDB->rowCount() > 0) {
		$row=$mDB->fetchRow(2);
		$order_date = $row['order_date'];
		$delivery_date = $row['delivery_date'];
		$handler_id = $row['handler_id'];


		if (($order_date == "") || ($order_date == "0000-00-00")) {
			$mDB->remove();
			$objResponse->script("jAlert('警示', '請輸入入庫日期', 'red', '', 2000);");
			return $objResponse;
			exit;
		}


	} else {
		$mDB->remove();
		$objResponse->script("jAlert('警示', '查無主檔訊息，資料可能有誤', 'red', '', 2000);");
		return $objResponse;
		exit;
	}

	//先檢查明細檔資料
	$Qry="SELECT * FROM purchaseorder_detail
	where purchase_order_id = '$purchase_order_id'
	order by auto_seq";
	$mDB->query($Qry);
	if ($mDB->rowCount() > 0) {
		while ($row=$mDB->fetchRow(2)) {
			$material_no = $row['material_no'];
			$warehouse = $row['warehouse'];
			$purchase_qty = $row['purchase_qty'];
			

			if ($material_no == "") {
				$mDB->remove();
				$objResponse->script("jAlert('警示', '物料編碼不可空白', 'red', '', 2000);");
				return $objResponse;
				exit;
			}
			if ($warehouse == "") {
				$mDB->remove();
				$objResponse->script("jAlert('警示', '倉庫別不可空白', 'red', '', 2000);");
				return $objResponse;
				exit;
			}
			if ($purchase_qty <= 0) {
				$mDB->remove();
				$objResponse->script("jAlert('警示', '入庫數量不可為0或負數', 'red', '', 2000);");
				return $objResponse;
				exit;
			}
			if ($purchase_qty <= 0) {
				$mDB->remove();
				$objResponse->script("jAlert('警示', '單價不可為0或負數', 'red', '', 2000);");
				return $objResponse;
				exit;
			}
			
		}
	} else {
		$mDB->remove();
		$objResponse->script("jAlert('警示', '入庫清單沒有任何資料', 'red', '', 2000);");
		return $objResponse;
		exit;
	}

	$mDB2 = "";
	$mDB2 = new MywebDB();

	//檢查無問題即可進行採購作業
	$Qry="SELECT * FROM purchaseorder_detail
	where purchase_order_id = '$purchase_order_id'
	order by auto_seq";
	$mDB->query($Qry);
	if ($mDB->rowCount() > 0) {
		while ($row=$mDB->fetchRow(2)) {
			$material_no = $row['material_no'];
			$warehouse = $row['warehouse'];
			$purchase_qty = $row['purchase_qty'];
			$unit_price = $row['unit_price'];
		}
	}

	// 建立入庫單編號
	//自動產生 stock_in_id
	$mDB3 = "";
	$mDB3 = new MywebDB();
	$today = date("Ymd");
	$Qry3 = "SELECT stock_in_id FROM stock_in WHERE SUBSTRING(stock_in_id,3,8) = '$today' ORDER BY stock_in_id DESC LIMIT 0,1";
	$mDB3->query($Qry3);
	if ($mDB3->rowCount() > 0) {
	$row=$mDB3->fetchRow(2);
	$temp_stock_in_id = $row['stock_in_id'];
	$str4 = substr($temp_stock_in_id,-4,4);
	$num = (int)$str4+1;
	$filled_int = sprintf("%04d", $num);
	$stock_in_id = "SI".$today.$filled_int;
	} else {
		$stock_in_id = "SI".$today."0001";
		
	}
	$mDB3->remove();

	// 建立入庫單
	$mDB4 = "";
	$mDB4 = new MywebDB();
	$stock_in_type 		= "採購入庫";
	$source_code 		= "採購供應商";
	$Qry4="insert into stock_in (stock_in_id,stock_in_date,stock_in_type,handler_id,source_code,create_date,last_modify) values ('$stock_in_id','$delivery_date','$stock_in_type','$handler_id','$source_code',now(),now())";
	$mDB4->query($Qry4);


	$mDB5 = "";
	$mDB5 = new MywebDB();
	$mDB6 = "";
	$mDB6 = new MywebDB();
	$mDB7 = "";
	$mDB7 = new MywebDB();

	
	// 取得採購單資料，並直接新增入庫至stock_in_detail
	$Qry5 = "SELECT a.auto_seq,a.supplier_id,b.* FROM purchaseorder a
		LEFT JOIN purchaseorder_detail b ON a.purchase_order_id = b.purchase_order_id
		where a.purchase_order_id = '$purchase_order_id'";

	$mDB5->query($Qry5);
	if ($mDB5->rowCount() > 0) {
		while ($row = $mDB5->fetchRow(2)) {
			$material_no   = $row['material_no'];
			$warehouse     = $row['warehouse'];
			$location_id   = $row['location_id'];
			$stock_in_qty  = $row['purchase_qty'];
			$unit_price    = $row['unit_price'];
			$remarks       = $row['remarks'];
			$supplier_id   = $row['supplier_id'];

			// 新增入庫明細資料
			$Qry6 = "
				INSERT INTO `stock_in_detail` (
					`stock_in_id`,
					`material_no`,
					`warehouse`,
					`location_id`,
					`stock_in_qty`,
					`unit_price`,
					`remarks`,
					`create_date`,
					`last_modify`
				) VALUES (
					'$stock_in_id',    -- 入庫單號
					'$material_no',    -- 材料編號
					'$warehouse',      -- 倉庫名稱
					'$location_id',    -- 儲位編號
					$stock_in_qty,     -- 入庫數量
					$unit_price,       -- 單價
					'$remarks',        -- 備註
					NOW(),             -- 建立時間
					NOW()              -- 最後修改時間
				);
			";
			$mDB6->query($Qry6);

			// 更新入庫單基本資料的供應商編號
			$Qry7 = "
				UPDATE `stock_in`
				SET `supplier_id` = '$supplier_id'
				WHERE `stock_in_id` = '$stock_in_id';
			";
			$mDB7->query($Qry7);
		}
	}
		


		//更新主檔狀態
		$Qry="UPDATE purchaseorder set
				status				= '已結單'
				,stock_in_id		= '$stock_in_id'
				,last_modify		= now()
				where purchase_order_id = '$purchase_order_id'";
		$mDB->query($Qry);


		$mDB2->remove();
		$mDB->remove();
		
		//$objResponse->script("oTable = $('#purchaseorder_detail_table').dataTable();oTable.fnDraw(false)");
		$objResponse->script("parent.myDraw();");
		$objResponse->script("autoclose('提示', '採購入庫作業已完成！',3000);");
		// $objResponse->script("parent.$.fancybox.close();");
		// 等待 1 秒後自動重新整理頁面（讓使用者看到提示）
		$objResponse->script("setTimeout(function(){ location.reload(); }, 1500);");


		return $objResponse;
		
	}

$xajax->processRequest();


$fm = $_GET['fm'];
// $purchase_order_id = $_GET['purchase_order_id'];
$auto_seq = $_GET['auto_seq'];

$mess_title = $title;

//$pro_id = "com";


$mDB = "";
$mDB = new MywebDB();

$Qry="SELECT a.*,b.employee_name,c.contract_caption FROM purchaseorder a
LEFT JOIN employee b ON b.employee_id = a.handler_id
LEFT JOIN contract c ON c.contract_id = a.contract_id
WHERE a.auto_seq = '$auto_seq'";
$mDB->query($Qry);
$total = $mDB->rowCount();
if ($total > 0) {
    //已找到符合資料
	$row=$mDB->fetchRow(2);
	$purchase_order_id = $row['purchase_order_id'];
	$order_date = $row['order_date'];
	$purchase_type = $row['purchase_type'];
	$contract_id = $row['contract_id'];
	$contract_name = $row['contract_caption'];
	$contract_type = $row['contract_type'];
	$supplier_id = $row['supplier_id'];
	$handler_id = $row['handler_id'];
	$status = $row['status'];
	$employee_name = $row['employee_name'];
	$delivery_date = $row['delivery_date'];
	$stock_in_id = $row['stock_in_id'];
	$requirement_description = $row['requirement_description'];
	$last_modify = $row['last_modify'];
  
}

//載入廠商
$Qry="select supplier_id,supplier_name from supplier order by auto_seq";
$mDB->query($Qry);
$select_supplier = "";
$select_supplier .= "<option></option>";
if ($mDB->rowCount() > 0) {
	while ($row=$mDB->fetchRow(2)) {
		$ch_supplier_id = $row['supplier_id'];
		$ch_supplier_name = $row['supplier_name'];
		$select_supplier .= "<option value=\"$ch_supplier_id\" ".mySelect($ch_supplier_id,$supplier_id).">$ch_supplier_id $ch_supplier_name</option>";
	}
}

$mDB->remove();

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



include $m_location."/sub_modal/project/func09/purchaseorder_ms/purchaseorder_detail.php";


$disabled = "";

$show_fellow_btn2 = "";
if ($status == "未結單") {
$show_fellow_btn2=<<<EOT
<div class="btn-group" role="group">
	<button type="button" id="execute_purchase_btn" class="btn btn-success btn-sm text-nowrap px-3" onclick="CheckValue(this.form);execute_purchaseorder('$purchase_order_id');"><i class="bi bi-box-arrow-in-down"></i>&nbsp;結單作業，並新增入庫單</button>
</div>
EOT; 
} else if ($status == "已結單") {
$show_fellow_btn2=<<<EOT
<div class="size14 weight">
  <span style="color:blue;">入庫編號 : $stock_in_id</span>
</div>

EOT; 
$disabled = "disabled";
}


$show_fellow_btn = "";
if ($status == "未結單") {
$show_fellow_btn=<<<EOT
<div class="btn-group" role="group">
	<button $disabled type="button" class="btn btn-danger btn-sm text-nowrap px-3" onclick="CheckValue(this.form);openfancybox_edit('/index.php?ch=purchaseorder_detail_add&auto_seq=$auto_seq&fm=$fm',800,'96%','');"><i class="bi bi-plus-circle"></i>&nbsp;新增料件</button>
	<button type="button" class="btn btn-success btn-sm text-nowrap px-3" onclick="purchaseorder_detail_myDraw();"><i class="bi bi-arrow-repeat"></i>&nbsp;重整</button>
</div>
EOT; 
}else if ($status == "已結單") {
	$show_fellow_btn=<<<EOT
<div class="btn-group" role="group">
	<button $disabled type="button" class="btn btn-danger btn-sm text-nowrap px-3" onclick="CheckValue(this.form);openfancybox_edit('/index.php?ch=purchaseorder_detail_add&auto_seq=$auto_seq&fm=$fm',800,'96%','');"><i class="bi bi-plus-circle"></i>&nbsp;新增料件</button>
	<button type="button" class="btn btn-success btn-sm text-nowrap px-3" onclick="purchaseorder_detail_myDraw();"><i class="bi bi-arrow-repeat"></i>&nbsp;重整</button>
</div>
EOT; 
}
$show_savebtn=<<<EOT
<div class="btn-group vbottom" role="group" style="margin-top:5px;">
	<button id="save" class="btn btn-primary" type="button" onclick="CheckValue(this.form);" style="padding: 5px 15px;"><i class="bi bi-check-circle"></i>&nbsp;存檔</button>
	<button $disabled id="cancel" class="btn btn-secondary display_none" type="button" onclick="setCancel();" style="padding: 5px 15px;"><i class="bi bi-x-circle"></i>&nbsp;取消</button>
	<button id="close" class="btn btn-danger" type="button" onclick="parent.myDraw();parent.$.fancybox.close();" style="padding: 5px 15px;"><i class="bi bi-power"></i>&nbsp;關閉</button>
</div>
EOT;

$show_status = "";
if ($status == "未結單") {
	$show_status =<<<EOT
	<div class="field_div3 pt-3">$status</div>
	EOT;
}else if ($status == "已結單"){
	$show_status =<<<EOT
	<div class="field_div3 pt-3" style="color:red;">此單已於 $last_modify 結單</div>
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
							<div class="col-lg-6 col-sm-6 col-md-12">
								<div class="field_div1">採購合約:</div>
								<div class="field_div3"><div class="blue weight mt-2">$contract_name</div></div>
							</div> 
							<div class="col-lg-6 col-sm-6 col-md-12">
								<div class="field_div1">合約種類:</div>
								<div class="field_div3"><div class="blue weight mt-2">$contract_type</div></div>
							</div> 
						</div>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">採購日期:</div> 
								<div class="field_div3">
									<div class="input-group" id="order_date"  style="width:100%;max-width:250px;">
										<input $disabled type="text" class="form-control" name="order_date" placeholder="請輸入入庫日期" aria-describedby="order_date" value="$order_date" onchange="setEdit();">
										<button $disabled class="btn btn-outline-secondary input-group-append input-group-addon" type="button" data-target="#order_date" data-toggle="datetimepicker"><i class="bi bi-calendar"></i></button>
									</div>
									<script type="text/javascript">
										$(function () {          
											$('#order_date').datetimepicker({
												locale: 'zh-tw'
												,format:"YYYY-MM-DD"
												,allowInputToggle: true
											});
										});
									</script>
								</div> 
							</div> 
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">採購性質:</div> 
								<div class="field_div3">
									<div class="field_div2"><div class="weight">$purchase_type</div></div>
									
								</div> 
							</div> 
						</div>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">供應商:</div> 
								<div class="field_div3">
									<select $disabled id="supplier_id" name="supplier_id" value="$supplier_id" style="width:100%;max-width:350px;" onchange="setEdit();">
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
										<input $disabled readonly type="text" class="form-control w-25" id="handler_id" name="handler_id" aria-describedby="handler_id_addon" value="$handler_id" onchange="setEdit();"/>
										<input $disabled readonly type="text" class="form-control w-50" id="employee_name" name="employee_name"  value="$employee_name" onchange="setEdit();"/>
										<button $disabled class="btn btn-outline-secondary w-25" type="button" id="handler_id_addon" onclick="openfancybox_edit('/index.php?ch=ch_employee&fm=$fm',800,'96%','');">選擇員工</button>
									</div> 
								</div> 
							</div> 
							<div class="col-lg-6 col-sm-12 col-md-12">
							</div> 
						</div>
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">需求說明:</div> 
								<div class="field_div3">
									<textarea $disabled class="inputtext w-100 p-3" id="requirement_description" name="requirement_description" cols="80" rows="1" style="max-width: 500px;" onchange="setEdit();">$requirement_description</textarea>
								</div> 
							</div> 
								<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">狀態:</div>
								$show_status
							</div> 
						</div>

							<div class="row">

								<div class="col-lg-6 col-sm-12 col-md-12">
									<div class="row" >
										<div class="field_div1">到貨日期:</div> 
										<div class="field_div3">
											<div class="input-group" id="delivery_date"  style="width:100%;max-width:250px;">
												<input type="text" class="form-control" name="delivery_date" placeholder="請輸入入庫日期" aria-describedby="delivery_date" value="$delivery_date" onchange="setEdit();">
												<button class="btn btn-outline-secondary input-group-append input-group-addon" type="button" data-target="#delivery_date" data-toggle="datetimepicker"><i class="bi bi-calendar"></i></button>
											</div>
											<script type="text/javascript">
												$(function () {          
													$('#delivery_date').datetimepicker({
														locale: 'zh-tw'
														,format:"YYYY-MM-DD"
														,allowInputToggle: true
													});
												});
											</script>
										</div> 
									</div> 
								</div>

							</div>
							

					</div>
					<div class="w-100" style="margin: 10px 0 -15px 0;">
						<div class="inline size14 weight mx-5">採購清單</div>
						<div class="inline">$show_fellow_btn</div>
						<div class="inline float-end me-5">$show_fellow_btn2</div>
					</div>
					$show_purchaseorder_detail
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

var execute_purchaseorder = function(purchase_order_id){				

	Swal.fire({
	title: "您確定要執行結單作業嗎?",
	text: "完成此項作業後即無法再進行此單的修改，請留意",
	icon: "question",
	showCancelButton: true,
	confirmButtonColor: "#3085d6",
	cancelButtonColor: "#d33",
	cancelButtonText: "取消",
	confirmButtonText: "確認執行"
	}).then((result) => {
		if (result.isConfirmed) {
			xajax_execute_purchaseorder(purchase_order_id);
		}
	});

};


$(document).ready(async function() {
	//等待其他資源載入完成，此方式適用大部份瀏覽器
	await new Promise(resolve => setTimeout(resolve, 100));
	$('#order_date').focus();
});

</script>

EOT;

?>