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
	$material_no		= trim($aFormValues['material_no']);
	
	if (trim($aFormValues['material_name']) == "") {
		$objResponse->script("jAlert('警示', '請輸入物料名稱', 'red', '', 2000);");
		return $objResponse;
		exit;
	}

	SaveValue($aFormValues);
	
	$objResponse->script("setSave();");
	$objResponse->script("parent.myDraw();");

	$objResponse->script("art.dialog.tips('已存檔!',1);");
	$objResponse->script("parent.$.fancybox.close();");
		
	return $objResponse;
}


$xajax->registerFunction("SaveValue");
function SaveValue($aFormValues){

	$objResponse = new xajaxResponse();
	
		//進行存檔動作
		$site_db				= trim($aFormValues['site_db']);
		$material_no			= trim($aFormValues['material_no']);
		$material_name			= trim($aFormValues['material_name']);
		$specification			= trim($aFormValues['specification']);
		$unit					= trim($aFormValues['unit']);
		$stock_min_qty			= trim($aFormValues['stock_min_qty']);
		$stock_max_qty			= trim($aFormValues['stock_max_qty']);
		$stock_safety			= trim($aFormValues['stock_safety']);
		$unit_price				= trim($aFormValues['unit_price']);
		$stock_qty				= trim($aFormValues['stock_qty']);
		$status					= trim($aFormValues['status']);
		$memberID				= trim($aFormValues['memberID']);
		
		//存入實體資料庫中
		$mDB = "";
		$mDB = new MywebDB();

		$Qry="UPDATE inventory set
				 material_name		= '$material_name'
				,specification		= '$specification'
				,unit				= '$unit'
				,stock_min_qty		= '$stock_min_qty'
				,stock_max_qty		= '$stock_max_qty'
				,stock_safety		= '$stock_safety'
				,unit_price			= '$unit_price'
				,stock_qty			= '$stock_qty'
				,`status`			= '$status'
				,last_modify		= now()
				where material_no = '$material_no'";
				
		$mDB->query($Qry);
        $mDB->remove();

		
	return $objResponse;
}

$xajax->processRequest();


$fm = $_GET['fm'];
$material_no = $_GET['material_no'];

$mess_title = $title;

//$pro_id = "com";


$mDB = "";
$mDB = new MywebDB();

$Qry="SELECT * FROM inventory a
WHERE material_no = '$material_no'";
$mDB->query($Qry);
$total = $mDB->rowCount();
if ($total > 0) {
    //已找到符合資料
	$row=$mDB->fetchRow(2);
	$material_no = $row['material_no'];
	$material_name = $row['material_name'];
	$specification = $row['specification'];
	$unit = $row['unit'];
	$stock_min_qty = $row['stock_min_qty'];
	$stock_max_qty = $row['stock_max_qty'];
	$stock_safety = $row['stock_safety'];
	$unit_price = $row['unit_price'];
	$stock_qty = $row['stock_qty'];
	$status = $row['status'];
	//$makeby = $row['makeby'];
	$last_modify = $row['last_modify'];
  
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
	max-width: 1150px; !Important;
	margin: 0 auto !Important;
}

.field_div1 {width:200px;display: none;font-size:18px;color:#000;text-align:right;font-weight:700;padding:15px 10px 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}
.field_div2 {width:100%;max-width:900px;display: none;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 0 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}
.field_div3 {width:100%;max-width:360px;display: none;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 0 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}

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
								<div class="field_div1">物料編碼:</div>
								<div class="field_div2"><div class="blue weight mt-2">$material_no</div></div>
							</div> 
						</div>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-md-12">
								<div class="field_div1">物料名稱:</div> 
								<div class="field_div2">
									<input type="text" class="inputtext" id="material_name" name="material_name" size="80" maxlength="120" value="$material_name" style="width:100%;max-width:800px;" onchange="setEdit();"/>
								</div> 
							</div> 
						</div>
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-md-12">
								<div class="field_div1">規格:</div> 
								<div class="field_div2">
									<textarea class="inputtext w-100 p-3" id="specification" name="specification" cols="80" rows="10" style="max-width: 800px;" onchange="setEdit();">$specification</textarea>
								</div> 
							</div> 
						</div>
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-md-12">
								<div class="field_div1">單位:</div> 
								<div class="field_div2">
									<input type="text" class="inputtext" id="unit" name="unit" size="20" maxlength="20" value="$unit" style="width:100%;max-width:200px;" onchange="setEdit();"/>
								</div> 
							</div> 
						</div>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">最小庫存量:</div> 
								<div class="field_div3">
									<input type="text" class="inputtext" id="stock_min_qty" name="stock_min_qty" size="20" value="$stock_min_qty" style="width:100%;max-width:250px;" onchange="setEdit();"/>
								</div> 
							</div> 
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">最大庫存量:</div> 
								<div class="field_div3">
									<input type="text" class="inputtext" id="stock_max_qty" name="stock_max_qty" size="20" value="$stock_max_qty" style="width:100%;max-width:250px;" onchange="setEdit();"/>
								</div> 
							</div> 
						</div>
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">安全庫存量:</div> 
								<div class="field_div3">
									<input type="text" class="inputtext" id="stock_safety" name="stock_safety" size="20" value="$stock_safety" style="width:100%;max-width:250px;" onchange="setEdit();"/>
								</div> 
							</div> 
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">單價:</div> 
								<div class="field_div3">
									<input type="text" class="inputtext" id="unit_price" name="unit_price" size="20" value="$unit_price" style="width:100%;max-width:250px;" onchange="setEdit();"/>
								</div> 
							</div> 
						</div>
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">庫存量:</div> 
								<div class="field_div3">
									<input type="text" class="inputtext" id="stock_qty" name="stock_qty" size="20" value="$stock_qty" style="width:100%;max-width:250px;" onchange="setEdit();"/>
								</div> 
							</div> 
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">狀態:</div> 
								<div class="field_div3">
									<input type="text" class="inputtext" id="status" name="status" size="20" maxlength="50" value="$status" style="width:100%;max-width:250px;" onchange="setEdit();"/>
								</div> 
							</div> 
						</div>
					</div>
					<div>
						<input type="hidden" name="fm" value="$fm" />
						<input type="hidden" name="site_db" value="$site_db" />
						<input type="hidden" name="material_no" value="$material_no" />
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
	$("#specification").autoGrow({
		extraLine: true // Adds an extra line at the end of the textarea. Try both and see what works best for you.
	});
});



$(document).ready(async function() {
	//等待其他資源載入完成，此方式適用大部份瀏覽器
	await new Promise(resolve => setTimeout(resolve, 100));
	$('#material_name').focus();
});

</script>

EOT;

?>