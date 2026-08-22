
<?php
session_start();
include '../../config.php';
require_once '../points_update.php';
ob_start();
$sess_rt_email = $_SESSION['rt_email'];
$sel_points = mysqli_query($conn,"SELECT points FROM rtr_retailer WHERE email = '$sess_rt_email'");
$row = mysqli_fetch_array($sel_points);
$rt_points = $row['points'];
ob_start();

$path = (getenv('MPDF_ROOT')) ? getenv('MPDF_ROOT') : __DIR__;
require_once $path . '/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf();

//==============================================================

$html = '

';

$mpdf->SetWatermarkText('DEMO');
$mpdf->showWatermarkText = true;


$mpdf->WriteHTML($html);

$mpdf->Output(); exit;