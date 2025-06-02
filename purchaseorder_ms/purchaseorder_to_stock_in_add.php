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
$auto_seq = $_GET['auto_seq'];

function processform($aFormValues,$auto_seq){

	$objResponse = new xajaxResponse();

	if (trim($aFormValues['stock_in_id']) == "") {
		$objResponse->script("jAlert('警示', '請輸入入庫單號', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	if (trim($aFormValues['stock_in_date']) == "") {
		$objResponse->script("jAlert('警示', '請輸入入庫日期', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	/*
	if (trim($aFormValues['stock_in_type']) == "") {
		$objResponse->script("jAlert('警示', '請選擇入庫類型', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	*/
	
	$fm					= trim($aFormValues['fm']);
	$site_db			= trim($aFormValues['site_db']);
	$templates			= trim($aFormValues['templates']);
	$employee_id		= trim($aFormValues['employee_id']);
	$stock_in_id		= trim($aFormValues['stock_in_id']);
	$stock_in_date 		= trim($aFormValues['stock_in_date']);
	$stock_in_type 		= "採購入庫";
	$source_code 		= "採購供應商";
	

	//存入實體資料庫中
	$mDB = "";
	$mDB = new MywebDB();

	$mDB2 = "";
	$mDB2 = new MywebDB();

	$mDB3 = "";
	$mDB3 = new MywebDB();

	$mDB4 = "";
	$mDB4 = new MywebDB();
	
	//檢查編號是否重複
	$Qry="select stock_in_id from stock_in where stock_in_id = '$stock_in_id'";
	$mDB->query($Qry);
	$total = $mDB->rowCount();
	if ($total > 0) {
		$mDB->remove();
		$objResponse->script("jAlert('警示', '您輸入的入庫單號已重複，請重新輸入新的', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	
	// 新增入庫單
	$Qry="insert into stock_in (stock_in_id,stock_in_date,stock_in_type,handler_id,source_code,create_date,last_modify) values ('$stock_in_id','$stock_in_date','$stock_in_type','$employee_id','$source_code',now(),now())";
	$mDB->query($Qry);

	// 取得採購單資料，並直接新增入庫至stock_in_detail
	$Qry2 = "SELECT a.auto_seq,a.supplier_id,b.* FROM purchaseorder a
			LEFT JOIN purchaseorder_detail b ON a.purchase_order_id = b.purchase_order_id
			where a.auto_seq = '$auto_seq'";

	$mDB2->query($Qry2);		

	if ($mDB2->rowCount() > 0) {
		while ($row=$mDB2->fetchRow(2)) {
			$material_no = $row['material_no'];
			$warehouse = $row['warehouse'];
			$location_id = $row['location_id'];
			$stock_in_qty = $row['purchase_qty'];
			$unit_price = $row['unit_price'];
			$remarks = $row['remarks'];
			$supplier_id = $row['supplier_id'];
			$Qry3 = "INSERT INTO `stock_in_detail` (
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
									);";
			$mDB3->query($Qry3);
			
			// 更新入庫單基本資料
			$Qry4 = "UPDATE `stock_in` SET `supplier_id` = '$supplier_id' WHERE `stock_in_id` = '$stock_in_id';";
			$mDB4->query($Qry4);
		}

		$mDB->remove();
		$mDB2->remove();
		$mDB3->remove();
		$mDB4->remove();
		$objResponse->script("jAlert('成功', '新增入庫成功','green', '', 2000);");
		$objResponse->script("parent.myDraw();");
		$objResponse->script("parent.location.href = '?ch=stock_in';");
		$objResponse->script("parent.$.fancybox.close();");

	}

	
	
	return $objResponse;
		
}


$xajax->registerFunction("getno");
function getno(){

	$objResponse = new xajaxResponse();

	//系統取號

	//自動產生 stock_in_id
	$today = date("Ymd");

	$mDB = "";
	$mDB = new MywebDB();
	
	//取得最後代號
	$Qry = "SELECT stock_in_id FROM stock_in WHERE SUBSTRING(stock_in_id,3,8) = '$today' ORDER BY stock_in_id DESC LIMIT 0,1";
	$mDB->query($Qry);
	if ($mDB->rowCount() > 0) {
		$row=$mDB->fetchRow(2);
		$temp_stock_in_id = $row['stock_in_id'];
		$str4 = substr($temp_stock_in_id,-4,4);
		$num = (int)$str4+1;
		$filled_int = sprintf("%04d", $num);
		$new_stock_in_id = "SI".$today.$filled_int;
	} else {
		$new_stock_in_id = "SI".$today."0001";
	}

	$mDB->remove();




	
	$objResponse->assign("stock_in_id","value",$new_stock_in_id);

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


/*
//載入廠商
$Qry="SELECT supplier_id,supplier_name FROM supplier ORDER BY supplier_id";
$mDB->query($Qry);

$select_supplier = "";
$select_supplier .= "<option></option>";

if ($mDB->rowCount() > 0) {
	while ($row=$mDB->fetchRow(2)) {
		$ch_supplier_id = $row['supplier_id'];
		$ch_supplier_name = $row['supplier_name'];
		$select_supplier .= "<option value='$ch_supplier_id'>{$ch_supplier_id} {$ch_supplier_name}</option>";
	}
}
*/

//載入入庫類型

$def_stock_in_type = "採購入庫";

/*
$Qry="SELECT caption FROM items where pro_id ='stock_in_type' ORDER BY pro_id,orderby";
$mDB->query($Qry);
$select_stock_in_type = "";
$select_stock_in_type .= "<option></option>";

if ($mDB->rowCount() > 0) {
	while ($row=$mDB->fetchRow(2)) {
		$ch_stock_in_type = $row['caption'];
		$select_stock_in_type .= "<option value=\"$ch_stock_in_type\" ".mySelect($ch_stock_in_type,$def_stock_in_type).">$ch_stock_in_type</option>";
	}
}
*/

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
								<div class="field_div1">入庫單號:</div> 
								<div class="field_div3">
									<input type="text" class="inputtext" id="stock_in_id" name="stock_in_id" size="20" maxlength="20" style="width:100%;max-width:250px;"/>
									<button type="button" class="btn btn-success" onclick="xajax_getno();"><i class="bi bi-recycle"></i>&nbsp;系統取號</button>
								</div> 
							</div> 
						</div>
						<div class="row">
							<div class="col-lg-7 col-sm-12 col-md-12">
								<div class="field_div1">入庫日期:</div> 
								<div class="field_div3">
									<div class="input-group" id="stock_in_date"  style="width:100%;max-width:250px;">
										<input type="text" class="form-control" name="stock_in_date" placeholder="請輸入入庫日期" aria-describedby="stock_in_date" value="$default_day">
										<button class="btn btn-outline-secondary input-group-append input-group-addon" type="button" data-target="#stock_in_date" data-toggle="datetimepicker"><i class="bi bi-calendar"></i></button>
									</div>
									<script type="text/javascript">
										$(function () {
											$('#stock_in_date').datetimepicker({
												locale: 'zh-tw'
												,format:"YYYY-MM-DD"
												,allowInputToggle: true
											});
										});
									</script>
								</div> 
							</div> 
							<div class="col-lg-5 col-sm-12 col-md-12">
								<div class="field_div1">入庫類型:</div> 
								<div class="field_div2">
									<div class="pt-2">採購入庫</div>
									<!--
									<select id="stock_in_type" name="stock_in_type" placeholder="請選擇入庫類型" style="width:100%;max-width:250px;">
										$select_stock_in_type
									</select>
									-->
								</div> 
							</div> 
						</div>
					</div>
				</div>
				<div class="form_btn_div mt-5">
					<input type="hidden" name="fm" value="$fm" />
					<input type="hidden" name="site_db" value="$site_db" />
					<input type="hidden" name="templates" value="$templates" />
					<input type="hidden" name="employee_id" value="$employee_id" />
					<button class="btn btn-primary" type="button" onclick="CheckValue(this.form,$auto_seq);" style="padding: 10px;margin-right: 10px;"><i class="bi bi-check-lg green"></i>&nbsp;確定新增</button>
					<button class="btn btn-danger" type="button" onclick="parent.myDraw();parent.$.fancybox.close();" style="padding: 10px;"><i class="bi bi-power"></i>&nbsp;關閉</button>
				</div>
			</form>
		</div>
	</div>
</div>
<script>

function CheckValue(thisform) {
	xajax_processform(xajax.getFormValues('addForm'),$auto_seq);

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