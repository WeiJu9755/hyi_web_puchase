<?php


require_once '/website/os/Mobile-Detect-2.8.34/Mobile_Detect.php';
$detect = new Mobile_Detect;


@include_once("/website/class/".$site_db."_info_class.php");

/* 使用xajax */
@include_once '/website/xajax/xajax_core/xajax.inc.php';
$xajax = new xajax();

$xajax->registerFunction("processform");

function processform($aFormValues){

	$objResponse = new xajaxResponse();

	if (trim($aFormValues['material_no1']) == "") {
		$objResponse->script("jAlert('警示', '請選擇大分類碼', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	if (trim($aFormValues['material_no2']) == "") {
		$objResponse->script("jAlert('警示', '請選擇小分類碼', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	if (trim($aFormValues['material_no3']) == "") {
		$objResponse->script("jAlert('警示', '請輸入規格碼', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	if (trim($aFormValues['material_no4']) == "") {
		$objResponse->script("jAlert('警示', '請輸入流水號', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	if (trim($aFormValues['material_no5']) == "") {
		$objResponse->script("jAlert('警示', '請選擇廠商代號', 'red', '', 2000);");
		return $objResponse;
		exit;
	}

	/*
	if (trim($aFormValues['material_no']) == "") {
		$objResponse->script("jAlert('警示', '請輸入物料編碼', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	*/
	if (trim($aFormValues['material_name']) == "") {
		$objResponse->script("jAlert('警示', '請輸入物料名稱', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	
	$fm					= trim($aFormValues['fm']);
	$site_db			= trim($aFormValues['site_db']);
	$templates			= trim($aFormValues['templates']);
	$material_no			= trim($aFormValues['material_no']);
	$material_name 			= htmlspecialchars(trim($aFormValues['material_name']), ENT_QUOTES, 'utf8');
	

	//存入實體資料庫中
	$mDB = "";
	$mDB = new MywebDB();
	
	//檢查帳號是否重複
	$Qry="select material_no from inventory where material_no = '$material_no'";
	$mDB->query($Qry);
	$total = $mDB->rowCount();
	if ($total > 0) {
		$mDB->remove();
		$objResponse->script("jAlert('警示', '您輸入的物料編碼已重複，請重新輸入新的', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	
	
	$Qry="insert into inventory (material_no,material_name,create_date,last_modify) values ('$material_no','$material_name',now(),now())";
	$mDB->query($Qry);

	$mDB->remove();
	if (!empty($material_no)) {
		$objResponse->script("myDraw();");
		$objResponse->script("art.dialog.tips('已新增，請繼續輸入其他資料...',2);");
		$objResponse->script("window.location='/?ch=edit&material_no=$material_no&fm=$fm';");
	} else {
		$objResponse->script("jAlert('警示', '發生不明原因的錯誤，資料未新增，請再試一次!', 'red', '', 2000);");
		$objResponse->script("parent.$.fancybox.close();");
	}
	
	return $objResponse;	
}

$xajax->processRequest();

$fm = $_GET['fm'];
$t = $_GET['t'];

$mess_title = $title;

$mDB = "";
$mDB = new MywebDB();


$m_code_no = "";

$getsmallclass = "/smarty/templates/$site_db/$templates/sub_modal/base/pjclass_ms/getsmallclass.php";
$getmainclass = "/smarty/templates/$site_db/$templates/sub_modal/base/pjclass_ms/getmainclass.php";

$pro_id = "materialcategory";
//載入大分類選項
$Qry="select code_no,caption from pjclass where pro_id = '$pro_id' and small_class = '0' order by orderby";
$mDB->query($Qry);
$select_material_no1 = "";
$select_material_no1 .= "<option></option>";

if ($mDB->rowCount() > 0) {
    while ($row=$mDB->fetchRow(2)) {
		$mc_code_no = $row['code_no'];
		$mc_caption = $row['caption'];
		//$select_material_no1 .= "<option value=\"$mc_caption\" ".mySelect($mc_caption,$m_code_no).">$mc_caption</option>";
		$select_material_no1 .= "<option value=\"$mc_code_no\">{$mc_code_no} {$mc_caption}</option>";
	}
}
//檢查並設定細類
//先取出 caption () 的 main_class 值
$m_row = getkeyvalue2($site_db."_info","pjclass","pro_id = '$pro_id' and small_class = '0' and code_no = '$m_code_no'","main_class");
$main_class_seq = $m_row['main_class'];
//從資料庫中讀取大分類資料
$Qry="select code_no,caption from pjclass where pro_id = '$pro_id' and main_class = '$main_class_seq' and small_class <> '0' order by orderby";
$select_material_no2 = "";
$select_material_no2 .= "<option></option>";
$mDB->query($Qry);
if ($mDB->rowCount() > 0) {
	while ($row=$mDB->fetchRow(2)) {
		$sc_code_no = $row['code_no'];
		$sc_caption = $row['caption'];
		$select_material_no2 .= "<option value=\"$sc_code_no\">{$sc_code_no} {$sc_caption}</option>";
	}
}	


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
	max-width: 1150px; !Important;
	margin: 0 auto !Important;
}

.field_div1 {width:100%;max-width:200px;display: none;font-size:18px;color:#000;text-align:right;font-weight:700;padding:15px 10px 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}
.field_div2 {width:100%;max-width:900px;display: none;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 0 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}

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
					<div class="mytable w-100 mb-5">
						<div class="myrow">
							<div class="mycell size14 weight text-center p-2" style="width:20%;">
								<div>大分類碼</div> 
								<div class="size08 blue02 text-nowrap">(共英文字1碼)</div>
							</div>
							<div class="mycell size14 weight text-center p-2" style="width:20%;">
								<div>小分類碼</div> 
								<div class="size08 blue02 text-nowrap">(共3碼:英文字1碼+2碼數字)</div>
							</div> 
							<div class="mycell size14 weight text-center p-2" style="width:20%;">
								<div>規格碼</div> 
								<div class="size08 blue02 text-nowrap">(共3碼數字)</div>
							</div> 
							<div class="mycell size14 weight text-center p-2" style="width:20%;"> 
								<div>流水號</div> 
								<div class="size08 blue02 text-nowrap">(共3碼數字)</div>
							</div> 
							<div class="mycell size14 weight text-center p-2" style="width:20%;">
								<div>廠商代號</div> 
								<div class="size08 blue02 text-nowrap">(共4碼:英文字S+3碼數字)</div>
							</div> 
						</div>
						<div class="myrow">
							<div class="mycell size14 weight text-center px-2">
								<select class="form-select w-100" name="material_no1" id="material_no1">
									$select_material_no1
								</select>
							</div> 
							<div class="mycell size14 weight text-center px-2">
								<select class="form-select w-100" name="material_no2" id="material_no2">
									$select_material_no2
								</select>
							</div> 
							<div class="mycell size14 weight text-center px-2">
								<input type="text" class="inputtext" id="material_no3" name="material_no3" size="3" maxlength="3" style="width:100%;"/>
							</div> 
							<div class="mycell size14 weight text-center px-2">
								<input type="text" class="inputtext" id="material_no4" name="material_no4" size="3" maxlength="3" style="width:100%;"/>
							</div> 
							<div class="mycell size14 weight text-center px-2">
								<select class="form-select w-100" name="material_no5" id="material_no5">
									$select_supplier
								</select>
							</div> 
						</div>
					</div>
					<!--
					<div class="text-center mt-2 mb-3">
						<button type="button" class="btn btn-warning btn-lg px-5" onclick="">確認物料編碼</button>
					</div>
					-->
					<div>
						<div class="field_div1">物料編碼:</div> 
						<div class="field_div2">
							<input readonly type="text" class="inputtext inline me-2" id="material_no" name="material_no" size="30" maxlength="30" style="width:100%;max-width:300px;"/>
							<div id="material_no_warning" class="inline weight red"></div>
						</div> 
					</div>
					<div>
						<div class="field_div1">物料名稱:</div> 
						<div class="field_div2">
							<input type="text" class="inputtext" id="material_name" name="material_name" size="80" maxlength="120" style="width:100%;max-width:800px;"/>
						</div> 
					</div>
				</div>
				<div class="form_btn_div mt-5">
					<input type="hidden" name="fm" value="$fm" />
					<input type="hidden" name="site_db" value="$site_db" />
					<input type="hidden" name="templates" value="$templates" />
					<button class="btn btn-primary" type="button" onclick="CheckValue(this.form);" style="padding: 10px;margin-right: 10px;"><i class="bi bi-check-lg green"></i>&nbsp;確定新增</button>
					<button class="btn btn-danger" type="button" onclick="parent.myDraw();parent.$.fancybox.close();" style="padding: 10px;"><i class="bi bi-power"></i>&nbsp;關閉</button>
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
	
function getSelectVal(){ 
	$("option",material_no2).remove(); //清空原有的選項
	var main_class_val = $("#material_no1").val();
    $.getJSON('$getsmallclass',{main_class:main_class_val,site_db:'$site_db',pro_id:'$pro_id'},function(json){ 
        var small_class = $("#material_no2"); 
        var option = "<option></option>";
		small_class.append(option);
        $.each(json,function(index,array){ 
			option = "<option value='"+array['code_no']+"'>"+array['code_no']+' '+array['caption']+"</option>"; 
            small_class.append(option); 
        }); 
    });
}

$(function(){ 
    $("#material_no1").change(function(){ 
        getSelectVal(); 
		update_material_no();
    }); 
});

$(function(){ 
    $("#material_no2").change(function(){ 
		update_material_no();
    }); 
});


//更新大分類
function getMainSelectVal(){ 
    $.getJSON("$getmainclass",{site_db:'$site_db',pro_id:'$pro_id'},function(json){ 
        var main_class = $("#material_no1"); 
		var last_option = main_class.val();
        $("option",material_no1).remove(); //清空原有的選項
        var option = "<option></option>";
		main_class.append(option);
        $.each(json,function(index,array){
			if (array['caption'] == last_option)
				option = "<option value='"+array['caption']+"' selected>"+array['caption']+"</option>"; 
			else
				option = "<option value='"+array['caption']+"'>"+array['caption']+"</option>"; 
            main_class.append(option); 
        }); 
    }); 
}

</script>
<script>

$('#material_no3').on('keyup', function() {
  update_material_no();
});

$('#material_no4').on('keyup', function() {
  update_material_no();
});

$(function(){ 
    $("#material_no5").change(function(){ 
		update_material_no();
    }); 
});

//更新編碼
function update_material_no(){ 
	var material_no1 = $("#material_no1").val();
	if (material_no1 == null)
		material_no1 = '-';

	var material_no2 = $("#material_no2").val();
	if (material_no2 == null)
		material_no2 = '';

	// 如果長度小於3，就補空白
  	while (material_no2.length < 3) {
    	material_no2 += '-';
  	}

	var material_no3 = $("#material_no3").val();
	if (material_no3 == null)
		material_no3 = '';

	// 如果長度小於3，就補空白
  	while (material_no3.length < 3) {
    	material_no3 += '-';
  	}

	var material_no4 = $("#material_no4").val();
	if (material_no4 == null)
		material_no4 = '';

	// 如果長度小於3，就補空白
  	while (material_no4.length < 3) {
    	material_no4 += '-';
  	}

	var material_no5 = $("#material_no5").val();
	if (material_no5 == null)
		material_no5 = '';

	// 如果長度小於4，就補空白
  	while (material_no5.length < 4) {
    	material_no5 += '-';
  	}

	//var value_str = value.replace(/\s/g, '*');

	$("#material_no").val(material_no1+material_no2+material_no3+material_no4+material_no5);

}

</script>
EOT;

?>