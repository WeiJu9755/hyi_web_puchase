<?php


//error_reporting(E_ALL); 
//ini_set('display_errors', '1');


require_once '/website/os/Mobile-Detect-2.8.34/Mobile_Detect.php';
$detect = new Mobile_Detect;

if( $detect->isMobile() && !$detect->isTablet() ){
	$isMobile = 1;
} else {
	$isMobile = 0;
}


$fm = $_GET['fm'];


$sure_to_delete = getlang("您確定要刪除此筆資料嗎?");

$dataTable_de = getDataTable_de();
$Prompt = getlang("提示訊息");
$Confirm = getlang("確認");
$Cancel = getlang("取消");

$purchaseorder_row = getkeyvalue2($site_db."_info","purchaseorder","auto_seq = '$auto_seq'","status");
// $status =$stock_in_row['status'];
$status =$purchaseorder_row['status'];



$list_view=<<<EOT
<div class="w-100 p-3">
	<table class="table table-bordered border-dark w-100" id="purchaseorder_detail_table" style="min-width:1320px;">
		<thead class="table-light border-dark">
			<tr style="border-bottom: 1px solid #000;">
				<th scope="col" class="text-center text-nowrap" style="width:15%;">物料編碼</th>
				<th scope="col" class="text-center text-nowrap" style="width:25%;">物料名稱/規格</th>
				<th scope="col" class="text-center text-nowrap" style="width:5%;">單位</th>
				<th scope="col" class="text-center text-nowrap" style="width:8%;">倉庫別</th>
				<th scope="col" class="text-center text-nowrap" style="width:8%;">入庫數量</th>
				<th scope="col" class="text-center text-nowrap" style="width:10%;">單價</th>
				<th scope="col" class="text-center text-nowrap" style="width:21%;">備註</th>
				<th scope="col" class="text-center text-nowrap" style="width:8%;">處理</th>
			</tr>
		</thead>
		<tbody class="table-group-divider">
			<tr>
				<td colspan="8" class="dataTables_empty">資料載入中...</td>
			</tr>
		</tbody>
	</table>
</div>
EOT;



$scroll = true;
if (!($detect->isMobile() && !$detect->isTablet())) {
	$scroll = false;
}
$encoded_purchase_order_id = rawurlencode($purchase_order_id);

$show_purchaseorder_detail=<<<EOT
<style>
#purchaseorder_detail_table {
	width: 100% !Important;
	margin: 5px 0 0 0 !Important;
}
</style>

$list_view

<script>
	var oTable;
	$(document).ready(function() {
		$('#purchaseorder_detail_table').dataTable( {
			"processing": false,
			"serverSide": true,
			"responsive":  {
				details: true
			},//RWD響應式
			"scrollX": '$scroll',
			"paging": false,
			"searching": false,  //禁用原生搜索
			"ordering": false,
			"ajaxSource": "/smarty/templates/$site_db/$templates/sub_modal/project/func09/purchaseorder_ms/server_purchaseorder_detail.php?site_db=$site_db&purchase_order_id=$encoded_purchase_order_id",
			"info": false,
			"language": {
						"sUrl": "$dataTable_de"
					},
			"fnRowCallback": function( nRow, aData, iDisplayIndex ) { 

				/*
				var seq_no = "";
				seq_no = iDisplayIndex + 1;
				$('td:eq(0)', nRow).html( '<div class="d-flex justify-content-center align-items-center size14 weight text-center" style="height:auto;min-height:32px;">('+seq_no+')</div>' );
				*/

				//物料編碼
				var material_no = "";
				if (aData[0] != null && aData[0] != "")
					material_no = aData[0];

				$('td:eq(0)', nRow).html( '<div class="d-flex justify-content-center align-items-center size14 blue02 weight text-center" style="height:auto;min-height:32px;">'+material_no+'</div>' );

				//物料名稱/規格
				var material_name = "";
				if (aData[1] != null && aData[1] != "") {
					if (aData[11] != null && aData[11] != "") {
						material_name = '<div class="size14 weight">'+aData[1]+'</div><div class="size12">'+aData[11]+'</div>';
					} else {
						material_name = '<div class="size14 weight">'+aData[1]+'</div>';
					}
				}

				$('td:eq(1)', nRow).html( '<div class="text-start" style="height:auto;min-height:32px;">'+material_name+'</div>' );
				
				//單位
				var unit = "";
				if (aData[2] != null && aData[2] != "")
					unit = aData[2];

				$('td:eq(2)', nRow).html( '<div class="d-flex justify-content-center align-items-center size14 text-center" style="height:auto;min-height:32px;">'+unit+'</div>' );

				//倉庫別
				var warehouse = "";
				if (aData[3] != null && aData[3] != "")
					warehouse = aData[3];

				$('td:eq(3)', nRow).html( '<div class="d-flex justify-content-center align-items-center size14 text-center" style="height:auto;min-height:32px;">'+warehouse+'</div>' );

				//入庫數量
				var purchase_qty = "";
				if (aData[4] != null && aData[4] != "" && aData[4] != 0)
					purchase_qty = number_format(aData[4]);

				$('td:eq(4)', nRow).html( '<div class="d-flex justify-content-center align-items-center size14 text-center" style="height:auto;min-height:32px;">'+purchase_qty+'</div>' );

				//單價
				var unit_price = "";
				if (aData[5] != null && aData[5] != "" && aData[5] != 0)
					unit_price = number_format(aData[5]);

				$('td:eq(5)', nRow).html( '<div class="d-flex justify-content-center align-items-center size14 text-center text-nowrap" style="height:auto;min-height:32px;">'+unit_price+'</div>' );

				//備註
				var remarks = "";
				if (aData[7] != null && aData[7] != "")
					remarks = aData[7];

				$('td:eq(6)', nRow).html( '<div class="d-flex justify-content-start align-items-center size14 text-start" style="height:auto;min-height:32px;">'+remarks+'</div>' );
				
				var show_btn = '';
				
				var url1 = "openfancybox_edit('/index.php?ch=purchaseorder_detail_modify&auto_seq="+aData[8]+"&fm=$fm',800,'96%','');";

				var mdel = "purchaseorder_detail_myDel('"+aData[8]+"');";
			
				if ('$status' == '已結單') {
					show_btn = '<div class="size14">已結單</div>';
				} else {
					show_btn = '<div class="btn-group text-nowrap">'
							+'<button type="button" class="btn btn-light py-0 my-0" onclick="'+url1+'" title="修改"><i class="bi bi-pencil-square"></i></button>'
							+'<button type="button" class="btn btn-light py-0 my-0" onclick="'+mdel+'" title="刪除"><i class="bi bi-trash"></i></button>'
							+'</div>';
				}
				
				$('td:eq(7)', nRow).html( '<div class="text-center">'+show_btn+'</div>' );

				return nRow;
			}
		});
	
		/* Init the table */
		oTable = $('#purchaseorder_detail_table').dataTable();
		
	} );
	
var purchaseorder_detail_myDel = function(auto_seq){
	/*
	xajax_purchaseorder_detailDeleteRow(auto_seq);
	return true;
	*/

	Swal.fire({
	title: "您確定要刪除此筆資料嗎?",
	text: "此項作業會刪除所有與此筆記錄有關的資料",
	icon: "question",
	showCancelButton: true,
	confirmButtonColor: "#3085d6",
	cancelButtonColor: "#d33",
	cancelButtonText: "取消",
	confirmButtonText: "刪除"
	}).then((result) => {
		if (result.isConfirmed) {
			xajax_purchaseorder_detailDeleteRow(auto_seq);
		}
	});


};

var purchaseorder_detail_myDraw = function(){
	var oTable;
	oTable = $('#purchaseorder_detail_table').dataTable();
	oTable.fnDraw(false);
}

</script>

EOT;

?>