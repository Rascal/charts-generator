<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("class/pData.class.php");
include("class/pDraw.class.php");
include("class/pImage.class.php");

//Parse file in array
$handle = @fopen("testinput/testparse.txt", "r");
$energy = 0;
$maxCurrent = 0;
$maxPower = 0;
$minVoltage = 200000;
$maxVoltage = 0;
if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
        $temp = explode(";", $buffer);

        //absolute current and capacity
        $temp[5] = abs($temp[5]);
        $temp[8] = abs($temp[8]);

        //calc energy and power
        $temp[3] = $temp[5] * $temp[7] / 100000;
        $energy = $energy + $temp[3] / 3600;
        $data[] = $temp;

        //find max/min
        if ($temp[3] > $maxPower) {
            $maxPower = $temp[3];
        }
        if ($temp[5] > $maxCurrent) {
            $maxCurrent = $temp[5];
        }
        if ($temp[7] > $maxVoltage) {
            $maxVoltage = $temp[7];
        }
        if ($temp[7] < $minVoltage) {
            $minVoltage = $temp[7];
        }
    }
    fclose($handle);
}

//Count X Axis, Capacity

$counter = count($data);
$temp = end($data);
$capacity = $temp[8] / 1000;

//Plot chart image
$myData = new pData();
$myData->loadPalette("palettes/blind.color", TRUE);

$myData->addPoints(array_column($data, 7), "Serie1");
$myData->setSerieDescription("Serie1", "Batt.Voltage");
$myData->setSerieOnAxis("Serie1", 0);
$myData->setAxisPosition(0, AXIS_POSITION_LEFT);
$myData->setAxisName(0, "Batt.Voltage  [V]");
$myData->setAxisUnit(0, "");
$myData->setAxisDisplay(0, AXIS_FORMAT_CUSTOM, "FirstAxisFormat");
function FirstAxisFormat($Value)
{
    return ($Value / 1000);
}

$myData->setAxisColor(0, array("R" => 109, "G" => 152, "B" => 171));

$myData->addPoints(array_column($data, 5), "Serie2");
$myData->setSerieDescription("Serie2", "Current");
$myData->setSerieOnAxis("Serie2", 1);
$myData->setAxisPosition(1, AXIS_POSITION_LEFT);
$myData->setAxisName(1, "Current  [A]");
$myData->setAxisUnit(1, "");
$myData->setAxisDisplay(1, AXIS_FORMAT_CUSTOM, "SecondAxisFormat");
function SecondAxisFormat($Value)
{
    return ($Value / 100);
}

$myData->setAxisColor(1, array("R" => 0, "G" => 39, "B" => 94));

$myData->addPoints(array_column($data, 3), "Serie3");
$myData->setSerieDescription("Serie3", "Power");
$myData->setSerieOnAxis("Serie3", 2);
$myData->setAxisPosition(2, AXIS_POSITION_RIGHT);
$myData->setAxisName(2, "Power  [W]");
$myData->setAxisUnit(2, "");
$myData->setAxisColor(2, array("R" => 255, "G" => 255, "B" => 255));

$myData->addPoints(array_keys($data), "Absissa");
$myData->setAbscissa("Absissa");
$myData->setXAxisDisplay(AXIS_FORMAT_CUSTOM, "TimeAxisFormat");

//X axis D-H-m-s format
function TimeAxisFormat($Value)
{
    switch ($Value) {
        case ($Value < 60):
            return ($Value . " s");
            break;
        case ($Value < 3600):
            $output = floor($Value / 60) . "m";
            if (fmod($Value, 60) > 0) {
                $output .= " " . fmod($Value, 60) . "s";
            }
            return ($output);
            break;
        case ($Value < 86400):
            $Value = $Value / 60;
            $output = floor($Value / 60) . "H";
            if (fmod($Value, 60) > 0) {
                $output .= " " . fmod($Value, 60) . "m";
            }
            return ($output);
            break;
        case ($Value < 900000):
            $Value = $Value / 3600;
            $output = floor($Value / 24) . "D";
            if (fmod($Value, 24) > 0) {
                $output .= " " . fmod($Value, 24) . "H";
            }
            return ($output);
            break;
        default:
            return ("0");
    }
}


$myPicture = new pImage(1500, 800, $myData);
$Settings = array("R" => 170, "G" => 183, "B" => 87, "Dash" => 1, "DashR" => 190, "DashG" => 203, "DashB" => 107);
$myPicture->drawFilledRectangle(0, 0, 1500, 800, $Settings);
$myPicture->Antialias = FALSE;

$Settings = array("StartR" => 219, "StartG" => 231, "StartB" => 139, "EndR" => 219, "EndG" => 231, "EndB" => 139, "Alpha" => 50);
$myPicture->drawGradientArea(0, 0, 1500, 800, DIRECTION_VERTICAL, $Settings);

$myPicture->drawRectangle(0, 0, 1499, 799, array("R" => 0, "G" => 0, "B" => 0));


$myPicture->setFontProperties(array("FontName" => "fonts/universbold.ttf", "FontSize" => 9));
$TextSettings = array("Align" => TEXT_ALIGN_MIDDLEMIDDLE
, "R" => 0, "G" => 39, "B" => 94);
$myPicture->drawText(750, 25, "Energy:  " . round($energy, 3) . " Wh   Capacity:  " . $capacity . " Ah", $TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(100, 50, 1453, 760);
$myPicture->setFontProperties(array("R" => 0, "G" => 0, "B" => 0, "FontName" => "fonts/minecraft.ttf", "FontSize" => 6));

//Epic crutch to count the number of skips
switch ($counter) {
    case ($counter <= 20):
        $skips = 0;
        break;
    case ($counter <= 40):
        $skips = 1;
        break;
    case ($counter <= 60):
        $skips = 2;
        break;
    case ($counter <= 100):
        $skips = 4;
        break;
    case ($counter <= 200):
        $skips = 9;
        break;
    case ($counter <= 400):
        $skips = 19;
        break;
    case ($counter <= 600):
        $skips = 29;
        break;
    case ($counter <= 1200):
        $skips = 59;
        break;
    case ($counter <= 2400):
        $skips = 119;
        break;
    case ($counter <= 3600):
        $skips = 179;
        break;
    case ($counter <= 6000):
        $skips = 299;
        break;
    case ($counter <= 12000):
        $skips = 599;
        break;
    case ($counter <= 24000):
        $skips = 1199;
        break;
    case ($counter <= 36000):
        $skips = 1799;
        break;
    case ($counter <= 72000):
        $skips = 3599;
        break;
    case ($counter <= 144000):
        $skips = 7199;
        break;
    case ($counter <= 216000):
        $skips = 10799;
        break;
    case ($counter <= 288000):
        $skips = 14399;
        break;
    case ($counter <= 432000):
        $skips = 21599;
        break;
    case ($counter > 432000):
        $skips = 43199;
        break;
}

//maxVoltageAxis minVoltageAxis
$minmaxVoltage = $maxVoltage - $minVoltage;
switch ($minmaxVoltage) {
    case ($minmaxVoltage <= 500):
        $maxVoltageAxis = ceil($maxVoltage / 10) * 10;
        $minVoltageAxis = floor($minVoltage / 10) * 10;
        break;
    case ($minmaxVoltage <= 1000):
        $maxVoltageAxis = ceil($maxVoltage / 20) * 20;
        $minVoltageAxis = floor($minVoltage / 20) * 20;
        break;
    case ($minmaxVoltage <= 2000):
        $maxVoltageAxis = ceil($maxVoltage / 50) * 50;
        $minVoltageAxis = floor($minVoltage / 50) * 50;
        break;
    case ($minmaxVoltage <= 5000):
        $maxVoltageAxis = ceil($maxVoltage / 100) * 100;
        $minVoltageAxis = floor($minVoltage / 100) * 100;
        break;
    case ($minmaxVoltage <= 50000):
        $maxVoltageAxis = ceil($maxVoltage / 500) * 500;
        $minVoltageAxis = floor($minVoltage / 500) * 500;
        break;
    case ($minmaxVoltage <= 500000):
        $maxVoltageAxis = ceil($maxVoltage / 1000) * 1000;
        $minVoltageAxis = floor($minVoltage / 1000) * 1000;
        break;
}

//maxCurrentAxis
switch ($maxCurrent) {
    case ($maxCurrent <= 100):
        $maxCurrentAxis = ceil($maxCurrent / 5) * 5;
        break;
    case ($maxCurrent <= 200):
        $maxCurrentAxis = ceil($maxCurrent / 10) * 10;
        break;
    case ($maxCurrent <= 500):
        $maxCurrentAxis = ceil($maxCurrent / 20) * 20;
        break;
    case ($maxCurrent <= 1000):
        $maxCurrentAxis = ceil($maxCurrent / 50) * 50;
        break;
    case ($maxCurrent <= 5000):
        $maxCurrentAxis = ceil($maxCurrent / 100) * 100;
        break;
    case ($maxCurrent <= 10000):
        $maxCurrentAxis = ceil($maxCurrent / 200) * 200;
        break;
}

//maxPowerAxis
switch ($maxPower) {
    case ($maxPower <= 0.5):
        $maxPowerAxis = ceil($maxPower / 0.05) * 0.05;
        break;
    case ($maxPower <= 1):
        $maxPowerAxis = ceil($maxPower / 0.1) * 0.1;
        break;
    case ($maxPower <= 2):
        $maxPowerAxis = ceil($maxPower / 0.2) * 0.2;
        break;
    case ($maxPower <= 5):
        $maxPowerAxis = ceil($maxPower / 0.5) * 0.5;
        break;
    case ($maxPower <= 50):
        $maxPowerAxis = ceil($maxPower);
        break;
    case ($maxPower <= 500):
        $maxPowerAxis = ceil($maxPower / 5) * 5;
        break;
    case ($maxPower <= 1000):
        $maxPowerAxis = ceil($maxPower / 10) * 10;
        break;
    case ($maxPower <= 5000):
        $maxPowerAxis = ceil($maxPower / 50) * 50;
        break;
}


$AxisBoundaries = array(0 => array("Min" => $minVoltageAxis, "Max" => $maxVoltageAxis), 1 => array("Min" => 0, "Max" => $maxCurrentAxis), 2 => array("Min" => 0, "Max" => $maxPowerAxis));
$Settings = array("Pos" => SCALE_POS_LEFTRIGHT
//, "Mode"=>SCALE_MODE_FLOATING
, "Mode" => SCALE_MODE_MANUAL
, "ManualScale" => $AxisBoundaries
, "LabelingMethod" => LABELING_ALL
, "GridR" => 255, "GridG" => 255, "GridB" => 255, "GridAlpha" => 50, "TickR" => 0, "TickG" => 0, "TickB" => 0, "TickAlpha" => 50, "LabelRotation" => 0, "LabelSkip" => $skips, "SkippedAxisAlpha" => 0, "CycleBackground" => TRUE, "DrawXLines" => 1, "DrawSubTicks" => 0, "SubTickR" => 0, "SubTickG" => 0, "SubTickB" => 0, "SubTickAlpha" => 50, "DrawYLines" => array(0));
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 50, "G" => 50, "B" => 50, "Alpha" => 10));

$Config = "";
$myPicture->drawLineChart($Config);

$Config = array("FontR" => 0, "FontG" => 0, "FontB" => 0, "FontName" => "fonts/minecraft.ttf", "FontSize" => 6, "Margin" => 6, "Alpha" => 30, "BoxSize" => 5, "Style" => LEGEND_NOBORDER
, "Mode" => LEGEND_HORIZONTAL
);
$myPicture->drawLegend(1300, 16, $Config);

$myPicture->stroke();

?>