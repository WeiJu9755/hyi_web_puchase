<?php

session_start();

$memberID = $_SESSION['memberID'];
$powerkey = $_SESSION['powerkey'];


require_once '/website/os/Mobile-Detect-2.8.34/Mobile_Detect.php';
$detect = new Mobile_Detect;


@include_once("/website/class/".$site_db."_info_class.php");

/* 使用xajax */
@include_once '/website/xajax/xajax_core/xajax.inc.php';
$xajax = new xajax();

$xajax->registerFunction("processform");

function processform($aFormValues){

	$objResponse = new xajaxResponse();

	if (trim($aFormValues['purchase_type']) == "") {
		$objResponse->script("jAlert('警示', '請選擇採購性質', 'red', '', 2000);");
		return $objResponse;

	}


	if (trim($aFormValues['contract_type']) == "") {
		$objResponse->script("jAlert('警示', '請選擇採購合約種類', 'red', '', 2000);");
		return $objResponse;

	}

	if (trim($aFormValues['makeby']) == "") {
		$objResponse->script("jAlert('警示', '請輸入經辦人', 'red', '', 2000);");
		return $objResponse;

	}
	
	if (trim($aFormValues['requirement_description']) == "") {
		$objResponse->script("jAlert('警示', '請輸入採購需求說明', 'red', '', 2000);");
		return $objResponse;

	}

	if (trim($aFormValues['order_date']) == "") {
		$objResponse->script("jAlert('警示', '請輸入採購日期', 'red', '', 2000);");
		return $objResponse;

	}

	if (trim($aFormValues['company_id']) == "") {
		$objResponse->script("jAlert('警示', '請選擇廠商', 'red', '', 2000);");
		return $objResponse;

	}

	if (trim($aFormValues['company_id']) == "") {
		$objResponse->script("jAlert('警示', '請選擇廠商', 'red', '', 2000);");
		return $objResponse;

	}

	
	
	$fm					= trim($aFormValues['fm']);
	$site_db			= trim($aFormValues['site_db']);
	$templates			= trim($aFormValues['templates']);
	$handler_id			= trim($aFormValues['handler_id']);
	$big_fixed 			= (trim($aFormValues['big_fixed']) === "Y") ? "Y" : "N";
	$purchase_type		= trim($aFormValues['purchase_type']);
	$contract_id 		= trim($aFormValues['contract_id']);
	$contract_seq 		= trim($aFormValues['contract_seq']);
	$contract_type 		= trim($aFormValues['contract_type']);
	$makeby 			= trim($aFormValues['makeby']);
	$requirement_description = trim($aFormValues['requirement_description']);
	$order_date 		= trim($aFormValues['order_date']);
	$delivery_date 		= trim($aFormValues['delivery_date']);
	$delivered 			= (trim($aFormValues['delivered']) === "Y") ? "Y" : "N";
	$company_id 		= trim($aFormValues['company_id']);
	$location 			= trim($aFormValues['location']);
	$purchase_order_id 	= "";

	// 採購編號生成
	$mDB = "";
	$mDB = new MywebDB();
	$Qry="select * from contract where contract_id='$contract_id' ";
	$mDB->query($Qry);
	if ($mDB->rowCount() > 0) {
		$row=$mDB->fetchRow(2);
		$contract_code = $row['contract_code'];
		$roc_year = date("Y") - 1911;
		$step_purchase_order_id = $contract_code . "_" . sprintf("%03d", $roc_year) . date("md") . "_";
	}
		
	$mDB2 = "";
	$mDB2 = new MywebDB();
	$Qry2 = "SELECT purchase_order_id 
         FROM purchaseorder 
         WHERE LEFT(purchase_order_id, LENGTH(purchase_order_id) - 2) = '$step_purchase_order_id'
         ORDER BY purchase_order_id DESC 
         LIMIT 1";
	$mDB2->query($Qry2);
	if ($mDB2->rowCount() > 0) {
        $row3 = $mDB2->fetchRow(2);
        $last_id = $row3['purchase_order_id'];
        $last_sn = substr($last_id, -2); // 取最後兩碼流水號
        $new_sn = sprintf("%02d", intval($last_sn) + 1); // 加1並補零
    } else {
        $new_sn = "01"; // 第一筆從 01 開始
    }

	// 組成新的採購單號
    $purchase_order_id = $step_purchase_order_id . $new_sn;


	//存入實體資料庫中
	$mDB = "";
	$mDB = new MywebDB();
	
	$now = date("Y-m-d H:i:s");
	$Qry = " INSERT INTO `purchaseorder` (
    `purchase_order_id`,
    `handler_id`,
    `purchase_type`,
    `contract_id`,
    `contract_seq`,
    `contract_type`,
    `big_fixed`,
    `makeby`,
    `requirement_description`,
    `order_date`,
    `delivery_date`,
    `delivered`,
    `company_id`,
    `location`,
    `created_at`,
    `updated_at`
) VALUES (
    '$purchase_order_id',
    '$handler_id',
    '$purchase_type',
    '$contract_id',
    $contract_seq,
    '$contract_type',
    '$big_fixed',
    '$makeby',
    '$requirement_description',
    '$order_date',
    '$delivery_date',
    '$delivered',
    '$company_id',
    '$location',
    '$now',
    '$now'
)";
	$mDB->query($Qry);

	$mDB->remove();
	if (!empty($purchase_order_id)) {
		$objResponse->script("myDraw();");
		$objResponse->script("art.dialog.tips('已新增，請繼續輸入其他資料...',2);");
		$objResponse->script("parent.$.fancybox.close();");
	} else {
		$objResponse->script("jAlert('警示', '發生不明原因的錯誤，資料未新增，請再試一次!', 'red', '', 2000);");
		$objResponse->script("parent.$.fancybox.close();");
	}
	
	return $objResponse;	
}


$xajax->registerFunction("getno");
function getno(){

	$objResponse = new xajaxResponse();

	//系統取號

	//自動產生 purchase_order_id
	$today = date("Ymd");

	$mDB = "";
	$mDB = new MywebDB();
	
	//取得最後代號
	$Qry = "SELECT purchase_order_id FROM stock_in WHERE SUBSTRING(purchase_order_id,3,8) = '$today' ORDER BY purchase_order_id DESC LIMIT 0,1";
	$mDB->query($Qry);
	if ($mDB->rowCount() > 0) {
		$row=$mDB->fetchRow(2);
		$temp_purchase_order_id = $row['purchase_order_id'];
		$str4 = substr($temp_purchase_order_id,-4,4);
		$num = (int)$str4+1;
		$filled_int = sprintf("%04d", $num);
		$new_purchase_order_id = "SI".$today.$filled_int;
	} else {
		$new_purchase_order_id = "SI".$today."0001";
	}

	$mDB->remove();




	
	$objResponse->assign("purchase_order_id","value",$new_purchase_order_id);

	return $objResponse;

}


$xajax->processRequest();

$fm = $_GET['fm'];
$t = $_GET['t'];

$mess_title = $title;

//從會員帳號取得員工代號
$employee_row = getkeyvalue2($site_db.'_info','employee',"member_no = '$memberID'",'employee_id');
$employee_id = $employee_row['employee_id'];




$default_day = date("Y-m-d");

$mDB = "";
$mDB = new MywebDB();


//載入合約

$Qry="SELECT auto_seq,contract_id,contract_caption FROM contract ORDER BY auto_seq";
$mDB->query($Qry);
$select_contract = "";
$select_contract .= "<option></option>";

if ($mDB->rowCount() > 0) {
	while ($row=$mDB->fetchRow(2)) {
		$contract_id = $row['contract_id'];
		$contract_name = $row['contract_caption'];
		$select_contract .= "<option value=\"$contract_id\" ".mySelect($contract_id,"").">$contract_name</option>";
	}
}

//載入廠商

$Qry="SELECT supplier_id,supplier_name,short_name FROM supplier ORDER BY supplier_id";
$mDB->query($Qry);
$select_supplier = "";
$select_supplier .= "<option></option>";

if ($mDB->rowCount() > 0) {
	while ($row=$mDB->fetchRow(2)) {
		$supplier_id = $row['supplier_id'];
		$supplier_name = $row['supplier_name'];
		$select_supplier .= "<option value=\"$supplier_id\" ".mySelect($supplier_id,"").">$supplier_id $supplier_name</option>";
	}
}

  
$mDB->remove();



if (!($detect->isMobile() && !$detect->isTablet())) {
	$isMobile = 0;

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
	max-width: 1400px; !Important;
	margin: 0 auto !Important;
}

.field_div1 {width:100%;max-width:150px;display: none;font-size:18px;color:#000;text-align:right;font-weight:700;padding:15px 10px 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}
.field_div2 {width:100%;max-width:400px;display: none;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 0 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}
.field_div3 {width:100%;max-width:400px;display: none;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 0 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}

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

</style>
EOT;

}
	


$show_center=<<<EOT
$style_css
<div class="card card_full">
	<div class="card-header text-bg-info">
		<div class="size14 weight float-start">
			$mess_title
		</div>
	</div>
	<div id="full" class="card-body data-overlayscrollbars-initialize">
		<div id="info_container">
			<form method="post" id="addForm" name="addForm" enctype="multipart/form-data" action="javascript:void(null);">
				<div class="field_container3">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-md-12">
								<div class="field_div1">合約案別:</div> 
								<div class="field_div2">
									<select id="contract_id" name="contract_id" placeholder="請選擇合約" style="width:100%;max-width:250px;">
										$select_contract
									</select>
								</div> 
							</div> 
						</div>
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">工項:</div> 
								<div class="field_div3">
									<div class="input-group" style="width:100%;max-width:155px;">
										<input type="number" class="form-control" name="contract_seq" id="contract_seq" placeholder="請輸入工項代號" >
									</div>
								</div>
							</div> 
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">合約類別:</div> 
								<div class="field_div2">
									<select id="contract_type" name="contract_type" placeholder="請選擇合約類別" style="width:100%;max-width:250px;">
										<option></option>
										<option value="合約內">合約內</option>
										<option value="合約外">合約外</option>
										<option value="其他">其他</option>
									</select>
								</div> 
							</div> 
						</div>
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">採購日期:</div> 
								<div class="field_div3">
									<div class="input-group" id="order_date"  style="width:100%;max-width:250px;">
										<input type="text" class="form-control" name="order_date" placeholder="請輸入入庫日期" aria-describedby="order_date" value="$default_day">
										<button class="btn btn-outline-secondary input-group-append input-group-addon" type="button" data-target="#order_date" data-toggle="datetimepicker"><i class="bi bi-calendar"></i></button>
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
								<div class="field_div2">
									<select id="purchase_type" name="purchase_type" placeholder="請選擇採購性質" style="width:100%;max-width:250px;">
										<option></option>
										<option value="採購">採購</option>
										<option value="勞務">勞務</option>
										<option value="勞務+採購">勞務+採購</option>
									</select>
								</div> 
							</div> 
						</div>
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
									<div class="field_div1">經辦人:</div> 
									<div class="field_div3">
										
										<div class="input-group text-nowrap" style="width:100%;max-width:450px;">
											<input readonly type="text" class="form-control w-25" id="handler_id" name="handler_id" aria-describedby="handler_id_addon" value="$employee_id"/>
											<input readonly type="text" class="form-control w-50" id="makeby" name="makeby"  value="$makeby"/>
											<button class="btn btn-outline-secondary w-25" type="button" id="handler_id_addon" onclick="openfancybox_edit('/index.php?ch=ch_employee&fm=$fm',800,'96%','');">選擇員工</button>
										</div> 
									</div> 
								</div> 
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">需求描述:</div> 
								<div class="field_div3">
									<textarea  name="requirement_description" id="requirement_description" rows="4" placeholder="請輸入需求描述" style="width:100%"></textarea>
								</div> 
							</div> 
						</div>
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-md-12">
								<div class="field_div1">供應商:</div> 
								<div class="field_div2">
									<select id="company_id" name="company_id" placeholder="請選擇廠商" style="width:100%;max-width:250px;">
										$select_supplier
									</select>
								</div> 
							</div> 
						</div>
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-md-12">
								<div class="field_div1">施作地點:</div> 
								<div class="field_div2">
									<input type="text" class="inputtext" name="location" id="location" size="50" placeholder="請輸入施作地點" maxlength="50" style="width:100%;max-width:250px;"/>
								</div> 
							</div> 
						</div>
						<div class="row d-flex flex-wrap gap-2 mt-3">
							<div class="field_div1 gap-2">採購事項確認:</div> 
							<div class="col-lg-2 col-sm-2 col-md-2 mt-2 mb-2 ">
									<div class="field_div3 ">
										<input type="checkbox" class="inputtext" name="big_fixed" id="big_fixed" value="Y" >
										<label for="big_fixed">大修</label>
									</div> 
							</div>
							<div class="col-lg-2 col-sm-2 col-md-2 mt-2 mb-2">
									<div class="field_div3 ">
										<input type="checkbox" class="inputtext" name="delivered" id="delivered" value="Y" >
										<label for="delivered" >已到貨</label>
									</div> 
							</div>
						</div>
						<!-- 到貨日期區塊，初始隱藏 -->
								<div class="row" id="delivery_date_section" style="display: none;">
								<div class="col-lg-6 col-sm-12 col-md-12">
									<div class="field_div1">到貨日期:</div> 
									<div class="field_div3">
									<div class="input-group" id="delivery_date" style="width:100%;max-width:250px;">
										<input type="text" class="form-control" name="delivery_date" placeholder="請輸入入庫日期" aria-describedby="delivery_date" value="$default_day">
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

								<!-- 監聽checkbox:到貨 是否打勾-->
								<script>
								$(document).ready(function() {
									$('#delivered').change(function() {
									if ($(this).is(':checked')) {
										$('#delivery_date_section').slideDown();
									} else {
										$('#delivery_date_section').slideUp();
									}
									});
								});
								</script>
					</div>
				</div>


					<div class="form_btn_div mt-5 d-flex justify-content-center">
						<input type="hidden" name="fm" value="$fm" />
						<input type="hidden" name="site_db" value="$site_db" />
						<input type="hidden" name="templates" value="$templates" />
						<input type="hidden" name="employee_id" value="$employee_id" />
						<button class="btn btn-primary" type="button" onclick="CheckValue(this.form);" style="padding: 10px; margin: 0 10px;">
							<i class="bi bi-check-lg green"></i>&nbsp;確定新增
						</button>
						<button class="btn btn-danger" type="button" onclick="parent.myDraw();parent.$.fancybox.close();" style="padding: 10px; margin: 0 10px;">
							<i class="bi bi-power"></i>&nbsp;關閉
						</button>
					</div>

			</form>
		</div>
	</div>
</div>
<script>

function CheckValue(thisform) {
	xajax_processform(xajax.getFormValues('addForm'));
	thisform.submit();
}

var myDraw = function(){
	var oTable;
	oTable = parent.$('#db_table').dataTable();
	oTable.fnDraw(false);
}

xajax_getno();

</script>
EOT;

?>