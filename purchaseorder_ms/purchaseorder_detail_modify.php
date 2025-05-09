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
	
	/*
	if (trim($aFormValues['material_no']) == "")	{
		$objResponse->script("jAlert('警示', '請輸入物料編碼', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	*/

	if ((int)trim($aFormValues['stock_in_qty']) < 1)	{
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
		$auto_seq			= trim($aFormValues['auto_seq']);
		$stock_in_id		= trim($aFormValues['stock_in_id']);
		$material_no		= trim($aFormValues['material_no']);
		$stock_in_qty		= trim($aFormValues['stock_in_qty']);
		$unit_price			= trim($aFormValues['unit_price']);
		$location_id		= trim($aFormValues['location_id']);
		$remarks			= trim($aFormValues['remarks']);
		$memberID			= trim($aFormValues['memberID']);
		

		
		//存入實體資料庫中
		$mDB = "";
		$mDB = new MywebDB();

		$Qry="UPDATE stock_in_detail set
				 stock_in_qty		= '$stock_in_qty'
				,unit_price			= '$unit_price'
				,location_id		= '$location_id'
				,remarks			= '$remarks'
				,last_modify		= now()
				where auto_seq = '$auto_seq'";

		$mDB->query($Qry);

        $mDB->remove();

		$objResponse->script("parent.stock_in_detail_myDraw();");
		$objResponse->script("parent.$.fancybox.close();");
		
	};
	
	return $objResponse;	
}

$xajax->processRequest();

$fm = $_GET['fm'];
$auto_seq = $_GET['auto_seq'];

$mess_title = $title;


//從 dispatch 取得 team_id
//$dispatch_row = getkeyvalue2($site_db."_info","dispatch","stock_in_id = '$stock_in_id'","team_id");
//$team_id =$dispatch_row['team_id'];


$mDB = "";
$mDB = new MywebDB();

$Qry="SELECT a.*,b.material_name,b.specification,b.unit FROM stock_in_detail a
LEFT JOIN inventory b ON b.material_no = a.material_no
WHERE a.auto_seq = '$auto_seq'";
$mDB->query($Qry);
$total = $mDB->rowCount();
if ($total > 0) {
    //已找到符合資料
	$row=$mDB->fetchRow(2);
	$stock_in_id = $row['stock_in_id'];
	$material_no = $row['material_no'];
	$material_name = $row['material_name'];
	$specification = $row['specification'];
	$unit = $row['unit'];
	$stock_in_qty = $row['stock_in_qty'];
	$unit_price = $row['unit_price'];
	$location_id = $row['location_id'];
	$remarks = $row['remarks'];
	$last_modify = $row['last_modify'];
  
}

$mDB->remove();

/*
$material_no_list = "";
$material_no_list .= "<option value=\"Apple\">Apple 蘋果</option>";
$material_no_list .= "<option value=\"Banana\">Banana 香蕉</option>";
$material_no_list .= "<option value=\"Cherry\">Cherry 櫻桃</option>";
$material_no_list .= "<option value=\"Grapes\">Grapes 葡萄</option>";
$material_no_list .= "<option value=\"Guava\">Grapes 番石榴</option>";
$material_no_list .= "<option value=\"Mango\">Mango 芒果</option>";
*/


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
						<div class="field_div2"><div class="blue weight mt-2">$material_no</div></div>
					</div>
					<div>
						<div class="field_div1"></div> 
						<div class="field_div2">
							<div id="material_info">$material_name</div>
						</div> 
					</div>
					<div>
						<div class="field_div1">入庫數量:</div> 
						<div class="field_div2">
							<input type="text" class="inputtext w-100" id="stock_in_qty" name="stock_in_qty" value="$stock_in_qty" style="width:100%;max-width:180px;"/>
						</div> 
					</div>
					<div>
						<div class="field_div1">單價:</div> 
						<div class="field_div2">
							<input type="text" class="inputtext w-100" id="unit_price" name="unit_price" value="$unit_price" style="width:100%;max-width:180px;"/>
						</div> 
					</div>
					<div>
						<div class="field_div1">儲存位置:</div> 
						<div class="field_div2">
							<input type="text" class="inputtext w-100" id="location_id" name="location_id" size="80" maxlength="180" value="$location_id" style="width:100%;max-width:180px;"/>
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
					<input type="hidden" name="auto_seq" value="$auto_seq" />
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