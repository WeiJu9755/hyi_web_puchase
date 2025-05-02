<?php

//error_reporting(E_ALL); 
//ini_set('display_errors', '1');

require_once '/website/os/Mobile-Detect-2.8.34/Mobile_Detect.php';
$detect = new Mobile_Detect;

if (!($detect->isMobile() && !$detect->isTablet())) {
	$isMobile = "0";
} else {
	$isMobile = "1";
}


@include_once("/website/class/".$site_db."_info_class.php");

/* 使用xajax */
@include_once '/website/xajax/xajax_core/xajax.inc.php';
$xajax = new xajax();

$xajax->registerFunction("DeleteRow");
function DeleteRow($auto_seq,$site_db){

	$objResponse = new xajaxResponse();
	
	$mDB = "";
	$mDB = new MywebDB();
	
	//刪除主資料
	$Qry="delete from inventory where auto_seq = '$auto_seq'";
	$mDB->query($Qry);
	
	$mDB->remove();
	
    $objResponse->script("oTable = $('#db_table').dataTable();oTable.fnDraw(false)");
    $objResponse->script("art.dialog.tips('相關資料已全數刪除!',2)");

	return $objResponse;
	
}

$xajax->registerFunction("returnValue");
function returnValue($auto_seq,$team_id){
	$objResponse = new xajaxResponse();

	$mDB = "";
	$mDB = new MywebDB();
	$Qry="select auto_seq from team_member where team_id = '$team_id'";
	$mDB->query($Qry);
	$employee_total = $mDB->rowCount();
	$mDB->remove();
	
	if ($employee_total > 0)
		$show_employee_total = "<div class=\"inline size12\">人數：</div><div class=\"inline size12 red weight\">$employee_total</div>";
	else 
		$show_employee_total = "";
	
	
	$objResponse->assign("employee_total".$auto_seq,"innerHTML",$show_employee_total);
	
    return $objResponse;
}


$xajax->processRequest();

$fm = $_GET['fm'];
$t = $_GET['t'];
$mc = $_GET['mc'];
$sc = $_GET['sc'];

$tb = "inventory";
$project_id = "202502110002";
$auth_id = "ST001";

$m_t = urlencode($_GET['t']);

$mess_title = $t;


$today = date("Y-m-d");



$dataTable_de = getDataTable_de();
$Prompt = getlang("提示訊息");
$Confirm = getlang("確認");
$Cancel = getlang("取消");


$pubweburl = "//".$domainname;

/*
//設定權限
$cando = "N";
if ($powerkey=="A") {
	$cando = "Y";
} else if ($super_admin=="Y") {
	if ($admin_readonly <> "Y") {
		$cando = "Y";
	}
} else if ($super_advanced=="Y") {
	if ($advanced_readonly <> "Y") {
		$cando = "Y";
	}
}


if ($cando=="Y") {

$show_modify_btn=<<<EOT
<div class="text-center my-2">
	<div class="btn-group" role="group" style="margin:0;">
		<button type="button" class="btn btn-danger text-nowrap" onclick="openfancybox_edit('/index.php?ch=add&t=$t&fm=$fm',840,'65%','');"><i class="bi bi-plus-circle"></i>&nbsp;新增資料</button>
		<button type="button" class="btn btn-success text-nowrap" onclick="myDraw();"><i class="bi bi-arrow-repeat"></i>&nbsp;重整</button>
	</div>
</div>
EOT;

} else {
$show_modify_btn=<<<EOT
<div class="size14 red m-auto text-center my-2 px-2 py-1 border border-danger" style="width:100px;">唯讀</div>
EOT;
}
*/

$fellow_count = 0;
//取得指定管理人數
$pjmyfellow_row = getkeyvalue2($site_db."_info","pjmyfellow","web_id = '$web_id' and project_id = '$project_id' and auth_id = '$auth_id' and pro_id = 'inventory'","count(*) as fellow_count");
$fellow_count =$pjmyfellow_row['fellow_count'];
if ($fellow_count == 0)
	$fellow_count = "";

/*
$warning_count = 0;
//取得指定管理人數(警訊通知對象)
$pjmyfellow_row = getkeyvalue2($site_db."_info","pjmyfellow","web_id = '$web_id' and project_id = '$project_id' and auth_id = '$auth_id' and pro_id = 'alertlist'","count(*) as warning_count");
$warning_count =$pjmyfellow_row['warning_count'];
if ($warning_count == 0)
	$warning_count = "";
*/

$pjItemManager = false;
//檢查是否為指定管理人
$pjmyfellow_row = getkeyvalue2($site_db."_info","pjmyfellow","web_id = '$web_id' and project_id = '$project_id' and auth_id = '$auth_id' and pro_id = 'inventory' and member_no = '$memberID'","count(*) as enable_count");
$enable_count =$pjmyfellow_row['enable_count'];
if ($enable_count > 0)
	$pjItemManager = true;


//設定權限
$cando = "N";
if (($powerkey=="A") || ($super_admin=="Y") || ($pjItemManager == true)) {
	$cando = "Y";
}


//取得使用者員工身份
$member_picture = getmemberpict160($memberID);

$member_row = getkeyvalue2("memberinfo","member","member_no = '$memberID'","member_name");
$member_name = $member_row['member_name'];

$employee_row = getkeyvalue2($site_db."_info","employee","member_no = '$memberID'","count(*) as manager_count,employee_name,employee_type,team_id");
$manager_count =$employee_row['manager_count'];
$team_id = $employee_row['team_id'];
if ($manager_count > 0) {
	$employee_name = $employee_row['employee_name'];
	$employee_type = $employee_row['employee_type'];

	$team_row = getkeyvalue2($site_db."_info","team","team_id = '$team_id'","team_name");
	$team_name = $team_row['team_name'];
} else {
	$employee_name = $member_name;
	$team_name = "未在員工名單";
}


$member_logo=<<<EOT
<div class="mytable bg-white m-auto rounded">
	<div class="myrow">
		<div class="mycell" style="text-align:center;width:73px;padding: 5px 0;">
			<img src="$member_picture" height="75" class="rounded">
		</div>
		<div class="mycell text-start p-2 vmiddle" style="width:107px;">
			<div class="size14 blue02 weight mb-1 text-nowrap">$employee_name</div>
			<div class="size12 weight text-nowrap">$team_name</div>
			<div class="size12 weight text-nowrap">$employee_type</div>
		</div>
	</div>
</div>
EOT;


$show_disabled = "";
$show_disabled_warning = "";
/*
//if ((empty($team_id)) || ((($super_admin=="Y") && ($admin_readonly == "Y")) || (($super_advanced=="Y") && ($advanced_readonly == "Y")))) {
if (((($super_admin=="Y") && ($admin_readonly == "Y")) || (($super_advanced=="Y") && ($advanced_readonly == "Y")))) {
	if ($pjItemManager <> "Y") {
		$show_disabled = "disabled";
		$show_disabled_warning = "<div class=\"size12 red weight text-center p-2\">此區為管理人專區，非經授權請勿進行任何處理</div>";
	}
}
*/

//if ($cando == "Y") {
	if (($super_admin == "Y") && ($admin_readonly == "Y")) {
		$show_disabled = "disabled";
		$show_disabled_warning = "<div class=\"size12 red weight text-center p-2\">此區為管理人專區，非經授權請勿進行任何處理</div>";
	}
//}


$show_admin_list = "";


if ($cando == "Y") {

	$show_modify_btn = "";

		if (($powerkey == "A") || (($super_admin=="Y") && ($admin_readonly <> "Y"))) {
$show_admin_list=<<<EOT
<div class="text-center">
	<div class="btn-group me-2 mb-2" role="group">
		<a role="button" class="btn btn-light" href="javascript:void(0);" onclick="openfancybox_edit('/index.php?ch=fellowlist&project_id=$project_id&auth_id=$auth_id&pro_id=inventory&t=指定管理人&fm=base',850,'96%',true);" title="指定管理人"><i class="bi bi-shield-fill-check size14 red inline me-2 vmiddle"></i><div class="inline size12 me-2">指定管理人</div><div class="inline red weight vmiddle">$fellow_count</div></a>
	</div>
</div>
EOT;
		}

$show_modify_btn=<<<EOT
<div class="text-center my-2">
	<div class="btn-group me-2 mb-2" role="group">
		<button type="button" class="btn btn-danger text-nowrap" onclick="openfancybox_edit('/index.php?ch=add&t=$t&fm=$fm',1200,'96%','');"><i class="bi bi-plus-circle"></i>&nbsp;新增資料</button>
		<button type="button" class="btn btn-success text-nowrap" onclick="myDraw();"><i class="bi bi-arrow-repeat"></i>&nbsp;重整</button>
	</div>
</div>
$show_admin_list
EOT;



$list_view=<<<EOT
<div class="w-100 m-auto p-1 mb-5 bg-white">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-2 col-sm-12 col-md-12 p-1 d-flex flex-column justify-content-center align-items-center">
				$member_logo
			</div> 
			<div class="col-lg-8 col-sm-12 col-md-12 p-1">
				<div class="size20 pt-1 text-center">採購單管理</div>
				$show_modify_btn
				$show_disabled_warning
			</div> 
			<div class="col-lg-2 col-sm-12 col-md-12">
			</div> 
		</div>
	</div>
	<table class="table table-bordered border-dark w-100" id="db_table" style="min-width:1000px;">
		<thead class="table-light border-dark">
			<tr style="border-bottom: 1px solid #000;">
				<th scope="col" class="text-center text-nowrap" style="width:10%;">NO.</th>
				<th scope="col" class="text-center" style="width:14%;">請購單號</th>
				<th scope="col" class="text-center" style="width:20%;">請購性質</th>
				<th scope="col" class="text-center" style="width:7%;">報價種類</th>
				<th scope="col" class="text-center" style="width:7%;">需求說明</th>
				<th scope="col" class="text-center" style="width:7%;">訂購日期</th>
				<th scope="col" class="text-center" style="width:7%;">訂單回傳</th>
				<th scope="col" class="text-center" style="width:7%;">到貨狀態</th>
				<th scope="col" class="text-center text-nowrap" style="width:7%;">處理</th>
			</tr>
		</thead>
		<tbody class="table-group-divider">
			<tr>
				<td colspan="11" class="dataTables_empty">資料載入中...</td>
			</tr>
		</tbody>
	</table>
</div>
EOT;

	
$scroll = true;
if (!($detect->isMobile() && !$detect->isTablet())) {
	$scroll = false;
}

	
$show_view=<<<EOT
<style type="text/css">
#db_table {
	width: 100% !Important;
	margin: 5px 0 0 0 !Important;
}

</style>

$list_view

<script>
	var oTable;
	$(document).ready(function() {
		$('#db_table').dataTable( {
			"processing": true,
			"serverSide": true,
			"responsive":  {
				details: true
			},//RWD響應式
			"scrollX": '$scroll',
			"paging": true,
			"pageLength": 50,
			"lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
			"pagingType": "full_numbers",  //分頁樣式： simple,simple_numbers,full,full_numbers
			"searching": true,  //禁用原生搜索
			"ordering": false,
			"ajaxSource": "/smarty/templates/$site_db/$templates/sub_modal/project/func09/purchaseorder_ms/server_purchaseorder.php?site_db=$site_db",
			"language": {
						"sUrl": "$dataTable_de"
					},
			"fixedHeader": true,
			"fixedColumns": {
        		left: 1,
    		},
			"fnRowCallback": function( nRow, aData, iDisplayIndex ) { 

				var material_no = "";
				if (aData[0] != null && aData[0] != "") {
					material_no = aData[0];
				}
				$('td:eq(0)', nRow).html( '<div class="size14 weight blue02 text-center">'+material_no+'</div>' );

				var material_name = "";
				if (aData[1] != null && aData[1] != "") {
					material_name = aData[1];
				}
				$('td:eq(1)', nRow).html( '<div class="size14 weight text-center">'+material_name+'</div>' );

				var specification = "";
				if (aData[2] != null && aData[2] != "") {
					specification = aData[2];
				}
				$('td:eq(2)', nRow).html( '<div class="size14 text-start">'+specification+'</div>' );

				var unit = "";
				if (aData[3] != null && aData[3] != "") {
					unit = aData[3];
				}
				$('td:eq(3)', nRow).html( '<div class="size14 text-center">'+unit+'</div>' );

				var stock_min_qty = '';
				if (aData[4] != null && aData[4] != "" && aData[4] != "0") {
					var stock_min_qty = number_format(aData[4]);
				}
				$('td:eq(4)', nRow).html( '<div class="size14 text-center">'+stock_min_qty+'</div>' );

				var stock_max_qty = '';
				if (aData[5] != null && aData[5] != "" && aData[5] != "0") {
					var stock_max_qty = number_format(aData[5]);
				}
				$('td:eq(5)', nRow).html( '<div class="size14 text-center">'+stock_max_qty+'</div>' );

				var stock_safety = '';
				if (aData[6] != null && aData[6] != "" && aData[6] != "0") {
					var stock_safety = number_format(aData[6]);
				}
				$('td:eq(6)', nRow).html( '<div class="size14 text-center">'+stock_safety+'</div>' );

				var unit_price = '';
				if (aData[7] != null && aData[7] != "" && aData[7] != "0") {
					var unit_price = number_format(aData[7]);
				}
				$('td:eq(7)', nRow).html( '<div class="size14 text-center">'+unit_price+'</div>' );

				// var stock_qty = '';
				// if (aData[8] != null && aData[8] != "" && aData[8] != "0") {
				// 	var stock_qty = number_format(aData[8]);
				// }
				// $('td:eq(8)', nRow).html( '<div class="size14 text-center">'+stock_qty+'</div>' );

				var status = "";
				if (aData[9] != null && aData[9] != "") {
					status = aData[9];
				}
				$('td:eq(9)', nRow).html( '<div class="size14 text-center">'+status+'</div>' );


				var show_btn = '';
				
				var url1 = "openfancybox_edit('/index.php?ch=edit&material_no="+aData[0]+"&fm=$fm',1200,'96%','');";

				var mdel = "myDel("+aData[0]+",'$site_db');";
				show_btn = '<div class="btn-group text-nowrap">'
						+'<button type="button" class="btn btn-light py-0 my-0" onclick="'+url1+'" title="修改"><i class="bi bi-pencil-square"></i></button>'
						+'<button type="button" class="btn btn-light py-0 my-0" onclick="'+mdel+'" title="刪除"><i class="bi bi-trash"></i></button>'
						+'</div>';
				
				$('td:eq(10)', nRow).html( '<div class="text-center">'+show_btn+'</div>' );
				
				return nRow;
			
			}
			
		});
	
		/* Init the table */
		oTable = $('#db_table').dataTable();
		
	} );
	
var myDel = function(auto_seq,site_db){				
	art.dialog({
		lock: true,
		icon: 'confirm',
		skin: 'default',
		content: '您確定要刪除此筆資料嗎?',
		yesFn: function(){
			xajax_DeleteRow(auto_seq,site_db);
			return true;
		},
		noFn: function(){
			return true;
		}
	});
};


var myDraw = function(){
	var oTable;
	oTable = $('#db_table').dataTable();
	oTable.fnDraw(false);
}



</script>

EOT;


} else {

	$sid = "mbwarning";
	$show_view = mywarning("很抱歉! 目前此功能只開放給本站特定會員，或是您目前的權限無法存取此頁面。");

}

?>