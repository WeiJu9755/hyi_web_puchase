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
	
//   	$objResponse->alert("formData: " . print_r($aFormValues, true));
//   	$objResponse->alert("formData: " . print_r($_POST, true));
	
	
	$bError = false;
	

	if (trim($aFormValues['material_no']) == "")	{
		$objResponse->script("jAlert('警示', '請輸入物料編碼', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	if (trim($aFormValues['warehouse']) == "")	{
		$objResponse->script("jAlert('警示', '請選擇倉庫別', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	if ((int)trim($aFormValues['purchase_qty']) < 1)	{
		$objResponse->script("jAlert('警示', '入庫數量不可小於1', 'red', '', 2000);");
		return $objResponse;
		exit;
	}

	if ((float)trim($aFormValues['unit_price']) == "")	{
		$objResponse->script("jAlert('警示', '單價不可小於等於0', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	
	if (!$bError) {
		$fm					= trim($aFormValues['fm']);
		$site_db			= trim($aFormValues['site_db']);
		$templates			= trim($aFormValues['templates']);
		$web_id				= trim($aFormValues['web_id']);
		$purchase_order_id	= trim($aFormValues['purchase_order_id']);
		$material_no		= trim($aFormValues['material_no']);
		$warehouse			= trim($aFormValues['warehouse']);
		$purchase_qty		= trim($aFormValues['purchase_qty']);
		$unit_price			= trim($aFormValues['unit_price']);
		$remarks			= trim($aFormValues['remarks']);
		$memberID			= trim($aFormValues['memberID']);
		

		
		//存入實體資料庫中
		$mDB = "";
		$mDB = new MywebDB();

		//檢查物料編碼是否重覆
		$Qry="SELECT material_no FROM purchaseorder_detail WHERE purchase_order_id = '$purchase_order_id' AND material_no = '$material_no'";
		$mDB->query($Qry);
		$total = $mDB->rowCount();
		if ($total > 0) {
			$mDB->remove();
			$objResponse->script("jAlert('警示', '您輸入的物料編碼已重複，請重新輸入新的', 'red', '', 2000);");
			return $objResponse;
			exit;
		}
	  
		$Qry="insert into purchaseorder_detail (purchase_order_id,material_no,warehouse,purchase_qty,unit_price,remarks,last_modify) values ('$purchase_order_id','$material_no','$warehouse','$purchase_qty','$unit_price','$remarks',now())";
		$mDB->query($Qry);

        $mDB->remove();

		$objResponse->script("parent.purchaseorder_detail_myDraw();");
		$objResponse->script("parent.$.fancybox.close();");
		
	};
	
	return $objResponse;	
}

$xajax->processRequest();

$fm = $_GET['fm'];
$auto_seq = $_GET['auto_seq'];
$mDB = "";
$mDB = new MywebDB();
$Qry="select purchase_order_id,auto_seq from purchaseorder where auto_seq = '$auto_seq'";
$mDB->query($Qry);
if ($mDB->rowCount() > 0) {
	while ($row=$mDB->fetchRow(2)) {
		$purchase_order_id = $row['purchase_order_id'];
	}
}


$mess_title = $title;


$mDB = "";
$mDB = new MywebDB();

//載入所有料件編號
$Qry="select material_no,material_name from inventory order by material_no";
$mDB->query($Qry);
$material_no_list = "";

if ($mDB->rowCount() > 0) {
	while ($row=$mDB->fetchRow(2)) {
		$ch_material_no = $row['material_no'];
		$ch_material_name = $row['material_name'];
		$material_no_list .= "<option value=\"$ch_material_no\">$ch_material_no $ch_material_name</option>";
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
	width: 800px !Important;
	margin: 0 auto !Important;
}

.field_div1 {width:150px;display: none;font-size:18px;color:#000;text-align:right;font-weight:700;padding:15px 10px 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}
.field_div2 {width:100%;max-width:630px;display: none;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 0 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}

.maxwidth {
    width: 100%;
    max-width: 250px;
}
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

.maxwidth {
    width: 100%;
}
</style>
EOT;

}


$show_center=<<<EOT
<script src="/os/Autogrow-Textarea/jquery.autogrowtextarea.min.js"></script>

$style_css
<div class="card card_full">
	<div class="card-header text-bg-info">
		<div class="size14 weight float-start me-3 mt-2">
			$mess_title
		</div>
	</div>
	<div id="full" class="card-body data-overlayscrollbars-initialize">
		<div id="info_container">
			<form method="post" id="addForm" name="addForm" enctype="multipart/form-data" action="javascript:void(null);">
				<div class="field_container3">
					<div>
						<div class="field_div1">物料編碼:</div> 
						<div class="field_div2">
							<input list="material_no_list" type="text" class="inputtext w-100" id="material_no" name="material_no" autocomplete="off" style="width:100%;max-width:250px;"/>
							<datalist id="material_no_list">
								$material_no_list
							</datalist>
						</div> 
					</div>
					<div>
						<div class="field_div1"></div> 
						<div class="field_div2">
							<div id="material_info"></div>
						</div> 
					</div>
					<div>
						<div class="field_div1">倉庫別:</div> 
						<div class="field_div2">
							<select $disabled id="warehouse" name="warehouse" placeholder="請選擇倉庫別" style="width:100%;max-width:250px;" onchange="setEdit();">
								$select_warehouse
							</select>
						</div> 
					</div>
					<div>
						<div class="field_div1">入庫數量:</div> 
						<div class="field_div2">
							<input type="text" class="inputtext w-100" id="purchase_qty" name="purchase_qty" style="width:100%;max-width:180px;"/>
						</div> 
					</div>
					<div>
						<div class="field_div1">單價:</div> 
						<div class="field_div2">
							<input type="text" class="inputtext w-100" id="unit_price" name="unit_price" style="width:100%;max-width:180px;"/>
						</div> 
					</div>
					<div>
						<div class="field_div1">備註:</div> 
						<div class="field_div2">
							<textarea class="inputtext w-100 p-3" id="remarks" name="remarks" cols="80" rows="2" style="max-width: 500px;" onchange="setEdit();">$remarks</textarea>
						</div> 
					</div>
				</div>
				<div class="form_btn_div mt-5">
					<input type="hidden" name="fm" value="$fm" />
					<input type="hidden" name="site_db" value="$site_db" />
					<input type="hidden" name="templates" value="$templates" />
					<input type="hidden" name="web_id" value="$web_id" />
					<input type="hidden" name="purchase_order_id" value="$purchase_order_id" />
					<input type="hidden" name="memberID" value="$memberID" />
					<button class="btn btn-primary" type="button" onclick="CheckValue(this.form);" style="padding: 10px;margin-right: 10px;"><i class="bi bi-check-lg green"></i>&nbsp;確定新增</button>
					<button class="btn btn-danger" type="button" onclick="parent.$.fancybox.close();" style="padding: 10px;"><i class="bi bi-power"></i>&nbsp關閉</button>
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
	oTable = parent.$('#stock_in_detail_table').dataTable();
	oTable.fnDraw(false);
}
	
$(document).ready(function() {
	$("#remarks").autoGrow({
		extraLine: true // Adds an extra line at the end of the textarea. Try both and see what works best for you.
	});
});

$(document).ready(async function() {
	//等待其他資源載入完成，此方式適用大部份瀏覽器
	await new Promise(resolve => setTimeout(resolve, 100));
	$('#material_no').focus();
});

</script>
EOT;


?>