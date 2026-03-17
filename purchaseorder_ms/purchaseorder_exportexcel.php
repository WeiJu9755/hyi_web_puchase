<?php

//error_reporting(E_ALL); 
//ini_set('display_errors', '1');

session_start();

$memberID = $_SESSION['memberID'];
$powerkey = $_SESSION['powerkey'];

/**
 * PHPExcel
 *
 * Copyright (C) 2006 - 2012 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2012 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.7.8, 2012-10-12
 */

/** Error reporting */
/*
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set("Asia/Taipei");
*/
ini_set('display_errors', FALSE);
ini_set('display_startup_errors', FALSE);
date_default_timezone_set("Asia/Taipei");










//載入公用函數
@include_once '/website/include/pub_function.php';


$site_db = "eshop";
$web_id = "sales.eshop";
$purchase_order_id = $_GET['purchase_order_id'];



/*
//檢查是否為管理員及進階會員
$super_admin = "N";
$super_advanced = "N";
$mem_row = getkeyvalue2('memberinfo','member',"member_no = '$memberID'",'admin,advanced,checked,luck,admin_readonly,advanced_readonly');
$super_admin = $mem_row['admin'];
$super_advanced = $mem_row['advanced'];
*/

@include_once("/website/class/" . $site_db . "_info_class.php");


if (PHP_SAPI == 'cli')
    die('This programe should only be run from a Web Browser');

/** Include PHPExcel */
require_once '/website/os/PHPExcel-1.8.1/Classes/PHPExcel.php';
// require_once 'vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
// require_once 'vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php';
$mDB = "";
$mDB = new MywebDB();

$objPHPExcel = new PHPExcel();
$sheet = $objPHPExcel->getActiveSheet();
$objPHPExcel->getDefaultStyle()->getFont()->setName('DFKai-SB')->setSize(12);

// 設定列印邊界，減少表頭空白
// $sheet->getPageMargins()->setTop(0.3);
// $sheet->getPageMargins()->setBottom(0.5);
// $sheet->getPageMargins()->setLeft(0.3);
// $sheet->getPageMargins()->setRight(0.3);

// 設定列印為一頁列印
$sheet->getPageSetup()
    ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT)
    ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4)
    ->setFitToPage(true)     // 啟用自動縮放
    ->setFitToWidth(1)       // 寬度一頁
    ->setFitToHeight(1);     // 高度一頁

// 列印範圍
$sheet->getPageSetup()->setPrintArea('A1:O41');

// 列印邊界
$sheet->getPageMargins()->setTop(0.3);
$sheet->getPageMargins()->setBottom(0.3);
$sheet->getPageMargins()->setLeft(0.3);
$sheet->getPageMargins()->setRight(0.3);
    

// 欄寬
$defaultWidth = 14.55;
$sheet->getColumnDimension('A')->setWidth(3);
foreach (range('B', '0') as $col) {
    $sheet->getColumnDimension($col)->setWidth($defaultWidth);
}


// Set document properties
$objPHPExcel->getProperties()->setCreator("PowerSales")
    ->setLastModifiedBy("PowerSales")
    ->setTitle("Office 2007 XLSX Document")
    ->setSubject("Office 2007 XLSX Document")
    ->setDescription("The document for Office 2007 XLSX, generated using PHP classes.")
    ->setKeywords("office 2007 openxml php")
    ->setCategory($purchase_order_id."_採購單");


// Title

// B3:N3 合併
$sheet->mergeCells('B3:N3');
$sheet->setCellValue('B3', '請 購 / 採 購 / 驗收單');

$sheet->getRowDimension(3)->setRowHeight(32);

$sheet->getRowDimension(15)->setRowHeight(55);
$sheet->getRowDimension(31)->setRowHeight(30);
$sheet->getRowDimension(34)->setRowHeight(80);
$sheet->getRowDimension(37)->setRowHeight(40);
$sheet->getRowDimension(38)->setRowHeight(40);
$sheet->getRowDimension(40)->setRowHeight(30);
$sheet->getRowDimension(41)->setRowHeight(50);

$sheet->getStyle('B3')->applyFromArray([
    'font'=>[
        'bold'=>true,
        'size'=>20
    ],
    'alignment'=>[
        'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER
    ]
]);



$sheet->getStyle('B3')->getFont()->setBold(true)->setSize(24);
$sheet->getStyle('B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('B3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

foreach (range(4,14) as $row) {
    $sheet->getRowDimension($row)->setRowHeight(24);
}
$sheet->getRowDimension(13)->setRowHeight(40);

$sheet->getStyle('A4:O8')->applyFromArray([
    'fill'=>[
        'type'=>PHPExcel_Style_Fill::FILL_SOLID,
        'color'=>['rgb'=>'F2F2F2']
    ],
    'font'=>[
        'size'=>14
    ],
    'alignment'=>[
        'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER
    ]
]);

$sheet->getStyle('A9:O14')->applyFromArray([
    'font'=>[
        'bold'=>true,
        'size'=>14
    ],
    'alignment'=>[
        'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER
    ],
    'borders'=>[
        'allborders'=>[
            'style'=>PHPExcel_Style_Border::BORDER_THIN
        ]
    ]
]);

$sheet->getStyle('A31:O41')->applyFromArray([
    'font'=>[
        'bold'=>true,
        'size'=>14
    ],
    'alignment'=>[
        'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER
    ],
    'borders'=>[
        'allborders'=>[
            'style'=>PHPExcel_Style_Border::BORDER_THIN
        ]
    ]
]);

$sheet->getStyle('A15:O15')->applyFromArray([

     'fill'=>[
        'type'=>PHPExcel_Style_Fill::FILL_SOLID,
        'color'=>['rgb'=>'F2F2F2']
    ],
     'font'=>[
        'bold'=>true,
        'size'=>17
    ],
    'alignment'=>[
        'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER
    ],
    'borders'=>[
        'allborders'=>[
            'style'=>PHPExcel_Style_Border::BORDER_THIN
        ]
    ]
]);

$sheet->getStyle('A32:O32')->applyFromArray([

     'fill'=>[
        'type'=>PHPExcel_Style_Fill::FILL_SOLID,
        'color'=>['rgb'=>'F2F2F2']
    ],
     'font'=>[
        'bold'=>true,
        'size'=>17
    ],
    'alignment'=>[
        'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER
    ],
    'borders'=>[
        'allborders'=>[
            'style'=>PHPExcel_Style_Border::BORDER_THIN
        ]
    ]
]);

$sheet->getStyle('A35:O35')->applyFromArray([

     'fill'=>[
        'type'=>PHPExcel_Style_Fill::FILL_SOLID,
        'color'=>['rgb'=>'F2F2F2']
    ],
     'font'=>[
        'bold'=>true,
        'size'=>17
    ],
    'alignment'=>[
        'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER
    ],
    'borders'=>[
        'allborders'=>[
            'style'=>PHPExcel_Style_Border::BORDER_THIN
        ]
    ]
]);

$sheet->getStyle('A39:O39')->applyFromArray([

     'fill'=>[
        'type'=>PHPExcel_Style_Fill::FILL_SOLID,
        'color'=>['rgb'=>'F2F2F2']
    ],
     'font'=>[
        'bold'=>true,
        'size'=>17
    ],
    'alignment'=>[
        'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER
    ],
    'borders'=>[
        'allborders'=>[
            'style'=>PHPExcel_Style_Border::BORDER_THIN
        ]
    ]
]);

$sheet->getStyle('C9')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('E9')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('G9')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$sheet->getStyle('C10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('E10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('G10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$sheet->getStyle('A4:O15')->getBorders()->getOutline()
->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);

$sheet->getColumnDimension('A')->setWidth(10);
$sheet->getColumnDimension('B')->setWidth(10);
$sheet->getColumnDimension('C')->setWidth(7);
$sheet->getColumnDimension('D')->setWidth(12);
$sheet->getColumnDimension('E')->setWidth(7);
$sheet->getColumnDimension('F')->setWidth(12);
$sheet->getColumnDimension('G')->setWidth(5);
$sheet->getColumnDimension('H')->setWidth(10);
$sheet->getColumnDimension('I')->setWidth(10);
$sheet->getColumnDimension('J')->setWidth(25);
$sheet->getColumnDimension('K')->setWidth(12);
$sheet->getColumnDimension('L')->setWidth(12);
$sheet->getColumnDimension('M')->setWidth(12);
$sheet->getColumnDimension('N')->setWidth(12);
$sheet->getColumnDimension('O')->setWidth(12);


// 注意事項
$sheet->mergeCells('A4:O4');
$sheet->setCellValue('A4','<注意事項>');
$sheet->mergeCells('A5:O5');
$sheet->setCellValue('A5','* 申請人需告知另一方(領班/工程師)請購品項及數量，並取得共識後皆須蓋章(僅有一方則不收單)');
$sheet->mergeCells('A6:O6');
$sheet->setCellValue('A6','* 不接受緊急請購單或未告知自行採購者，如小物件需緊急購買者，請事先告知獲准後採買並補單');
$sheet->mergeCells('A7:O7');
$sheet->setCellValue('A7','* 請購處理順序依請購單號排序為準，緊急程度依個人訂定，不能插單調整處裡順序');
$sheet->mergeCells('A8:O8');
$sheet->setCellValue('A8','* 請於填寫採購物料時寫上類碼 (A為重型機具類 / B為手工具類 / C為耗材類)');



$Qry="SELECT a.*,b.contract_abbreviation,c.short_name,d.supplier_name FROM purchaseorder a
LEFT JOIN contract b ON b.contract_id = a.contract_id
LEFT JOIN company c ON c.company_id = b.company_id
LEFT JOIN supplier d ON d.supplier_id = a.supplier_id
WHERE a.purchase_order_id = '$purchase_order_id'";
$mDB->query($Qry);

if ($mDB->rowCount() > 0) {
	while ($row=$mDB->fetchRow(2)) {
		$purchase_order_id                  = $row['purchase_order_id']; //採購單號
        $contract_id                        = $row['contract_id']; //合約案別
		$requirement_description            = $row['requirement_description']; //需求說明
        $order_date                         = $row['order_date']; //採購日期
        $delivery_date                      = $row['delivery_date'];//交貨日期
        $status                             = $row['status'];//採購單狀態
        $created_at                         = $row['created_at'];//採購單建立日期
        $contract_abbreviation              = $row['contract_abbreviation'];//合約名稱
        $short_name                         = $row['short_name'];//廠名
        $supplier_name                      = $row['supplier_name'];//廠名
	}
}

// 採購單資訊
$sheet->setCellValue('A9','請購性質');
$sheet->mergeCells('A9:B9');
$sheet->setCellValue('C9','☐');
$sheet->setCellValue('D9','勞務');
$sheet->setCellValue('E9','☐');
$sheet->setCellValue('F9','採購');
$sheet->setCellValue('G9','☐');
$sheet->mergeCells('H9:J9');
$sheet->setCellValue('H9','勞務+採購');
$sheet->setCellValue('K9','請購單號');
$sheet->mergeCells('L9:O9');
$sheet->setCellValue('L9',$purchase_order_id );

$sheet->setCellValue('A10','申請廠區');
$sheet->mergeCells('A10:B10');
$sheet->setCellValue('C10','☐');
$sheet->setCellValue('D10','林口');
$sheet->setCellValue('E10','☐');
$sheet->setCellValue('F10','大潭');
$sheet->setCellValue('G10','☐');
$sheet->mergeCells('H10:J10');
$sheet->setCellValue('H10','其他:');
$sheet->setCellValue('K10','契約編號');
$sheet->mergeCells('L10:O10');

// 純數
$sheet->setCellValueExplicit(
    'L10',
    $contract_id,
    PHPExcel_Cell_DataType::TYPE_STRING
);

$sheet->setCellValue('A11','申請狀態');
$sheet->mergeCells('A11:B11');
$sheet->setCellValue('C11','☐');
$sheet->setCellValue('D11','報價中');
$sheet->setCellValue('E11','☐');
$sheet->setCellValue('F11','採購');
$sheet->setCellValue('G11','☐');
$sheet->mergeCells('H11:J11');
$sheet->setCellValue('H11','補單');
$sheet->setCellValue('K11','填表日期');
$sheet->mergeCells('L11:O11');
$sheet->setCellValue('L11',$created_at);

$sheet->setCellValue('A12','是否報價');
$sheet->mergeCells('A12:B12');
$sheet->setCellValue('C12','☐');
$sheet->setCellValue('D12','報價');
$sheet->setCellValue('E12','☐');
$sheet->setCellValue('F12','不需報價');
$sheet->setCellValue('G12','☐');
$sheet->mergeCells('H12:J12');
$sheet->setCellValue('H12','詢價');
$sheet->setCellValue('K12','廠名');
$sheet->mergeCells('L12:M12');
$sheet->setCellValue('L12',$short_name);
$sheet->setCellValue('N12','預計工期');
$sheet->setCellValue('O12','');


$sheet->setCellValue('A13','合約狀態');
$sheet->mergeCells('A13:B13');
$sheet->setCellValue('C13','☐');
$sheet->setCellValue('D13','合約內');
$sheet->setCellValue('E13','☐');
$sheet->setCellValue('F13','合約外');
$sheet->setCellValue('G13','☐');
$sheet->mergeCells('H13:J13');
$sheet->setCellValue('H13','其他');
$sheet->setCellValue('K13','施作內容');
$sheet->mergeCells('L13:O13');

$sheet->setCellValue('L13',$requirement_description);


$sheet->setCellValue('A14','合約案別');
$sheet->mergeCells('A14:B14');
$sheet->mergeCells('C14:J14');
$sheet->setCellValue('C14',$contract_abbreviation);
$sheet->setCellValue('K14','施作人員');
$sheet->mergeCells('L14:O14');
$sheet->setCellValue('L14','');


$sheet->getStyle('J15:O15')->getAlignment()->setWrapText(true);
$sheet->setCellValue('A15','類碼');
$sheet->setCellValue('B15','序列');
$sheet->setCellValue('C15','工項');
$sheet->mergeCells('D15:G15');
$sheet->setCellValue('D15','品名材質規格');
$sheet->setCellValue('H15','數量');
$sheet->setCellValue('I15','單位');
$sheet->setCellValue('J15',"請購\n廠商");
$sheet->setCellValue('K15',"廠商\n單價");
$sheet->setCellValue('L15',"廠商\n複價");
$sheet->setCellValue('M15',"工項\n價格");
$sheet->setCellValue('N15',"預估\n收益");
$sheet->setCellValue('O15',"業主\n報價");

$count = 0;

$Qry="SELECT a.*,b.material_name,b.specification,b.unit,c.contract_id,d.unit_price AS contract_seq_unit_price,e.short_name
FROM purchaseorder_detail a
LEFT JOIN inventory b ON b.material_no = a.material_no
LEFT JOIN purchaseorder c ON c.purchase_order_id = a.purchase_order_id
LEFT JOIN contract_details d ON c.contract_id = d.contract_id AND d.seq = a.seq
LEFT JOIN supplier e ON e.supplier_id = c.supplier_id
WHERE a.purchase_order_id = '$purchase_order_id'";

$mDB->query($Qry);

$rowIndex = 16; // Excel從第16列開始
$seq = 1;
$purchase_order_price = 0;

$data = [];

if ($mDB->rowCount() > 0) {
    while ($row = $mDB->fetchRow(2)) {
        $data[] = $row;
    }
}


for ($i = 0; $i < 15; $i++) {

    if (isset($data[$i])) {
        $row = $data[$i];

        $material_no              = $row['material_no'];
        $material_code            = substr($material_no, 0, 1);
        $purchase_order_id        = $row['purchase_order_id'];
        $contract_seq             = $row['seq'];
        $material_name            = $row['material_name'];
        $specification            = $row['specification'];
        $purchase_qty             = $row['purchase_qty'];
        $unit                     = $row['unit'];
        $unit_price               = $row['unit_price'];
        $company_short_name       = $row['short_name'];
        $total_price              = $purchase_qty * $unit_price;
        $contract_seq_unit_price = $row['contract_seq_unit_price'];

        $purchase_order_price = $purchase_order_price+$total_price;

    } else {

        $material_code = "";
        $contract_seq  = "";
        $material_name = "";
        $specification = "";
        $purchase_qty  = "";
        $unit          = "";
        $company_short_name    = "";
        $unit_price    = "";
        $total_price   = "";
        $contract_seq_unit_price = "";
    }

    // 合併 D~G
    $sheet->mergeCells("D{$rowIndex}:G{$rowIndex}");

    // 寫入資料
    $sheet->setCellValue("A{$rowIndex}", $material_code);
    $sheet->setCellValue("B{$rowIndex}", $seq);
    $sheet->setCellValue("C{$rowIndex}", $contract_seq);

    $sheet->getStyle("D{$rowIndex}")->getAlignment()->setWrapText(true);
    $sheet->setCellValue("D{$rowIndex}", $material_name."\n".$specification);

    $sheet->setCellValue("H{$rowIndex}", $purchase_qty);
    $sheet->setCellValue("I{$rowIndex}", $unit);
    $sheet->setCellValue("J{$rowIndex}", $company_short_name );

    $sheet->setCellValue("K{$rowIndex}", $unit_price);
    $sheet->getStyle("K{$rowIndex}")->getNumberFormat()->setFormatCode('#,##0');

    $sheet->setCellValue("L{$rowIndex}", $total_price);
    $sheet->getStyle("L{$rowIndex}")->getNumberFormat()->setFormatCode('#,##0');

    $sheet->setCellValue("M{$rowIndex}", $contract_seq_unit_price);
    $sheet->getStyle("M{$rowIndex}")->getNumberFormat()->setFormatCode('#,##0');

   

    // 列高
    $sheet->getRowDimension($rowIndex)->setRowHeight(55);

    // 字型
$sheet->getStyle("A{$rowIndex}:O{$rowIndex}")->applyFromArray([
    'font'=>[
        'size'=>16
    ],
    'alignment'=>[
        'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER
    ],
    'borders'=>[
        'allborders'=>[
            'style'=>PHPExcel_Style_Border::BORDER_THIN
        ]
    ]
]);

    $rowIndex++;
    $seq++;
}

$sheet->mergeCells('A31:K31');
$sheet->setCellValue('A31','未稅總額)');
$sheet->getStyle('A31:K31')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$sheet->setCellValue('L31',$purchase_order_price);
$sheet->getStyle("L31")->getNumberFormat()->setFormatCode('#,##0');

$sheet->getStyle('M31')->getBorders()->setDiagonalDirection(
    PHPExcel_Style_Borders::DIAGONAL_DOWN
);

$sheet->getStyle('M31')->getBorders()->getDiagonal()->setBorderStyle(
    PHPExcel_Style_Border::BORDER_THIN
);

$sheet->getStyle('M31')->getBorders()->getDiagonal()->getColor()->setARGB('FF000000');

$sheet->mergeCells('A32:O32');
$sheet->setCellValue('A32','請購流程核章(壓日期)');

$sheet->mergeCells('A33:B33');
$sheet->setCellValue('A33','申請人');
$sheet->mergeCells('C33:E33');
$sheet->setCellValue('C33','工程師');
$sheet->mergeCells('F33:H33');
$sheet->setCellValue('F33','部門主管');
$sheet->mergeCells('I33:J33');
$sheet->setCellValue('I33','總務');
$sheet->mergeCells('K33:L33');
$sheet->setCellValue('K33','總監');
$sheet->mergeCells('M33:O33');
$sheet->setCellValue('M33','董事長');

$sheet->mergeCells('A34:B34');
$sheet->setCellValue('A34','');
$sheet->mergeCells('C34:E34');
$sheet->setCellValue('C34','');
$sheet->mergeCells('F34:H34');
$sheet->setCellValue('F34','');
$sheet->mergeCells('I34:J34');
$sheet->setCellValue('I34','');
$sheet->mergeCells('K34:L34');
$sheet->setCellValue('K34','');
$sheet->mergeCells('M34:O34');
$sheet->setCellValue('M34','');

$sheet->mergeCells('A35:O35');
$sheet->setCellValue('A35','採購流程核章(壓日期)');

$sheet->mergeCells('A36:B36');
$sheet->setCellValue('A36','預計交期');
$sheet->mergeCells('C36:E36');
$sheet->setCellValue('C36','採購');
$sheet->mergeCells('F36:H36');
$sheet->setCellValue('F36','收貨');
$sheet->mergeCells('I36:K36');
$sheet->setCellValue('I36','驗貨');
$sheet->mergeCells('L36:M36');
$sheet->setCellValue('K36','交料 / 入庫');
$sheet->mergeCells('N36:O36');
$sheet->setCellValue('K36','總務');

$sheet->mergeCells('A37:B38');
$sheet->setCellValue('A37','');
$sheet->mergeCells('C37:E38');
$sheet->setCellValue('C37','');
$sheet->mergeCells('F37:H38');
$sheet->setCellValue('F37','');
$sheet->mergeCells('I37:J38');
$sheet->setCellValue('I37','不合格/退換貨');
$sheet->setCellValue('K37','合格');
$sheet->mergeCells('L37:M38');
$sheet->setCellValue('L37','');
$sheet->mergeCells('N37:O38');
$sheet->setCellValue('N37','');

$sheet->mergeCells('A39:H39');
$sheet->setCellValue('A39','請款');

$sheet->mergeCells('I39:O39');
$sheet->setCellValue('I39','備註');


$sheet->mergeCells('A40:B40');
$sheet->setCellValue('A40','月份');
$sheet->mergeCells('C40:E40');
$sheet->setCellValue('C40','狀態');
$sheet->mergeCells('F40:H40');
$sheet->setCellValue('F40','資料上傳');
$sheet->mergeCells('I40:O41');
$sheet->setCellValue('F40','');

$sheet->mergeCells('A41:B41');
$sheet->setCellValue('A41','');
$sheet->mergeCells('C41:E41');
$sheet->setCellValue('C41','');
$sheet->mergeCells('F41:H41');
$sheet->setCellValue('F41','');












$sheet->getStyle('A9:O15')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$sheet->getStyle('A9:O15')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A9:O15')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// H欄靠左
$sheet->getStyle('H9:H13')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

// L欄靠左
$sheet->getStyle('L13')->getAlignment()->setWrapText(true);
$sheet->getStyle('L13')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


// 框線
$sheet->getStyle('A9:O14')->getBorders()->getAllBorders()
    ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

$objPHPExcel->getActiveSheet()->setTitle($purchase_order_id.'採購單');


$xlsx_filename = $purchase_order_id.'_採購單.xls';


// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename=' . $xlsx_filename);
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;



// 水平合併與外框線
function set_mergeCells_style_border($sheet, $cell, $cell2 = "")
{
    if ($cell2 != "") {
        $range = "$cell:$cell2";
        $sheet->mergeCells($range); // 合併儲存格
    } else {
        $range = $cell;
    }

    $sheet->getStyle($range)->applyFromArray([
        'borders' => [
            'outline' => [ // 僅外框邊框
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
        'alignment' => [
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ],
    ]);
}

function set_style_border_only_outline($sheet, $cell, $cell2 = "")
{
    $range = ($cell2 != "") ? "$cell:$cell2" : $cell;

    $sheet->getStyle($range)->applyFromArray([
        'borders' => [
            'outline' => [ // 只設定外框
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
        'alignment' => [
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ],
        'font' => [
            'size' => 10,
        ],
    ]);
}

