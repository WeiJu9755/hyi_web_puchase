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
		$delivered 						= (trim($aFormValues['delivered']) === "Y") ? "Y" : "N";
		$delivery_date					= trim($aFormValues['delivery_date']);
		$memberID						= trim($aFormValues['memberID']);
		
		//存入實體資料庫中
		$mDB = "";
		$mDB = new MywebDB();

		$Qry="UPDATE purchaseorder set
				 order_date							= '$order_date'
				,supplier_id						= '$supplier_id'
				,handler_id							= '$handler_id'
				,requirement_description			= '$requirement_description'
				,delivered							= '$delivered'
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
			$unit_price = $row['unit_price'];

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

	//檢查無問題即可進行入庫作業
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

			$total_amt = $purchase_qty*$unit_price;

			//必須先取得原庫存總庫存量及單價
			$Qry2="SELECT * FROM inventory
			WHERE material_no = '$material_no'";
			$mDB2->query($Qry2);
			if ($mDB2->rowCount() > 0) {
				$row2=$mDB2->fetchRow(2);
				$org_unit_price = $row2['unit_price'];
				$stock_qty = $row2['stock_qty'];

				$sum_stock_qty = $stock_qty+$purchase_qty;

				//加權移動平均
				$avg_unit_price = round((($org_unit_price*$stock_qty)+($unit_price*$purchase_qty))/$sum_stock_qty,4);


				//更新倉庫子檔內容
				//檢查是否已有此倉庫
				$Qry2="SELECT * FROM inventory_sub
				WHERE material_no = '$material_no' AND warehouse = '$warehouse'";
				$mDB2->query($Qry2);
				if ($mDB2->rowCount() > 0) {
					$row2=$mDB2->fetchRow(2);
					$auto_seq = $row2['auto_seq'];
					//已存在則進行更新
					$Qry2="UPDATE inventory_sub SET
							stock_qty			= stock_qty + '$purchase_qty'
							,last_modify		= NOW()
							WHERE auto_seq = '$auto_seq'";
					$mDB2->query($Qry2);

				} else {
					//不存在則新增
					$Qry2="INSERT INTO inventory_sub (material_no,warehouse,stock_qty,last_modify) VALUES ('$material_no','$warehouse','$purchase_qty',NOW())";
					$mDB2->query($Qry2);
				}

				//更新庫存料件資料
				/*
				$Qry2="UPDATE inventory set
						unit_price			= ROUND(((stock_qty*unit_price) + '$total_amt')/(stock_qty + '$purchase_qty'),4)
						,stock_qty			= stock_qty + '$purchase_qty'
						,last_modify		= now()
						where material_no = '$material_no'";
				$mDB2->query($Qry2);
				*/
				$Qry2="UPDATE inventory set
						unit_price			= '$avg_unit_price'
						,stock_qty			= '$sum_stock_qty'
						,last_modify		= now()
						where material_no = '$material_no'";
				$mDB2->query($Qry2);


			}


		}
	}

	//更新主檔狀態
	$Qry="UPDATE purchaseorder set
			status	= '已入庫'
			,last_modify		= now()
			where purchase_order_id = '$purchase_order_id'";
	$mDB->query($Qry);


	$mDB2->remove();
	$mDB->remove();
	
    //$objResponse->script("oTable = $('#purchaseorder_detail_table').dataTable();oTable.fnDraw(false)");
	$objResponse->script("parent.myDraw();");
	$objResponse->script("autoclose('提示', '已完成入庫！', 500);");
	$objResponse->script("parent.$.fancybox.close();");

	return $objResponse;
	
}

$xajax->processRequest();


$fm = $_GET['fm'];
$purchase_order_id = $_GET['purchase_order_id'];

$mess_title = $title;

//$pro_id = "com";


$mDB = "";
$mDB = new MywebDB();

$Qry="SELECT a.*,b.employee_name,c.contract_caption FROM purchaseorder a
LEFT JOIN employee b ON b.employee_id = a.handler_id
LEFT JOIN contract c ON c.contract_id = a.contract_id
WHERE a.purchase_order_id = '$purchase_order_id'";
$mDB->query($Qry);
$total = $mDB->rowCount();
$delivered="";
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
	$employee_name = $row['employee_name'];
	if($row['delivered'] == "Y"){
		$checked = 'checked';
	}else{
		$checked = '';
	}
	$delivery_date = $row['delivery_date'];
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
if ($status == "待入庫") {
$show_fellow_btn2=<<<EOT
<div class="btn-group" role="group">
	<button type="button" class="btn btn-success btn-sm text-nowrap px-3" onclick="CheckValue(this.form);execute_purchaseorder('$purchase_order_id');"><i class="bi bi-box-arrow-in-down"></i>&nbsp;執行入庫作業</button>
</div>
EOT; 
} else if ($status == "已入庫") {
$show_fellow_btn2=<<<EOT
<div class="size14 weight red">此單已於 $last_modify 完成入庫</div>
EOT; 
$disabled = "disabled";
}

$show_fellow_btn=<<<EOT
<div class="btn-group" role="group">
	<button $disabled type="button" class="btn btn-danger btn-sm text-nowrap px-3" onclick="CheckValue(this.form);openfancybox_edit('/index.php?ch=purchaseorder_detail_add&purchase_order_id=$purchase_order_id&fm=$fm',800,'96%','');"><i class="bi bi-plus-circle"></i>&nbsp;新增料件</button>
	<button type="button" class="btn btn-success btn-sm text-nowrap px-3" onclick="purchaseorder_detail_myDraw();"><i class="bi bi-arrow-repeat"></i>&nbsp;重整</button>
</div>
EOT; 

$show_savebtn=<<<EOT
<div class="btn-group vbottom" role="group" style="margin-top:5px;">
	<button $disabled id="save" class="btn btn-primary" type="button" onclick="CheckValue(this.form);" style="padding: 5px 15px;"><i class="bi bi-check-circle"></i>&nbsp;存檔</button>
	<button $disabled id="cancel" class="btn btn-secondary display_none" type="button" onclick="setCancel();" style="padding: 5px 15px;"><i class="bi bi-x-circle"></i>&nbsp;取消</button>
	<button id="close" class="btn btn-danger" type="button" onclick="parent.myDraw();parent.$.fancybox.close();" style="padding: 5px 15px;"><i class="bi bi-power"></i>&nbsp;關閉</button>
</div>
EOT;


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
						</div>

							<div class="row">
								<div class="col-lg-6 col-sm-12 col-md-12">
									<div class="field_div1">到貨狀況:</div> 
									<div class="field_div3 mt-2">
										<input type="checkbox" class="inputtext" name="delivered" id="delivered" value="Y" $checked>
										<label for="delivered">已到貨</label>
									</div> 
								</div> 

								<div class="col-lg-6 col-sm-12 col-md-12">
									<!-- 到貨日期區塊，初始隱藏 -->
									<div class="row" id="delivery_date_section" style="display: none;">
										<div class="field_div1">到貨日期:</div> 
										<div class="field_div3">
											<div class="input-group" id="delivery_date" style="width:100%;max-width:250px;">
												<input type="text" class="form-control" name="delivery_date" placeholder="請輸入入庫日期" aria-describedby="delivery_date" value="$delivery_date">
												<button class="btn btn-outline-secondary input-group-append input-group-addon" type="button" data-target="#delivery_date" data-toggle="datetimepicker">
													<i class="bi bi-calendar"></i>
												</button>
											</div>
											<script type="text/javascript">
												$(function () {
													$('#delivery_date').datetimepicker({
														locale: 'zh-tw',
														format: "YYYY-MM-DD",
														allowInputToggle: true
													});
												});
											</script>
										</div> 
									</div> 
								</div>
							</div>

								<!-- 監聽checkbox:到貨 是否打勾-->
								
								<script>
									$(document).ready(function() {
										function toggleDeliveryDateSection() {
											if ($('#delivered').is(':checked')) {
												$('#delivery_date_section').slideDown();
											} else {
												$('#delivery_date_section').slideUp();
											}
										}

										// 初始檢查
										toggleDeliveryDateSection();

										// checkbox 改變時再檢查一次
										$('#delivered').change(function() {
											toggleDeliveryDateSection();
										});
									});
								</script>
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
	title: "您確定要執行入庫作業嗎?",
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