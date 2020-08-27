<?php

$target_html = file_get_contents('http://www.jma.go.jp/jp/week/');
$target_html = mb_convert_encoding($target_html, 'HTML-ENTITIES', 'auto');

$dom = new DOMDocument;
@$dom->loadHTML($target_html);


$info = $dom->getElementById('infotablefont');
$tr_nodes = $info->getElementsByTagName('tr');

//days list 1番目から取得
$tr_node = $tr_nodes->item(0);
$th_nodes = $tr_node->getElementsByTagName('th');
$days = [];
$n = count($th_nodes);
for ($i = 1; $i < $n; $i++) {
    $node = $th_nodes[$i];
    //$str = $dom->saveHTML($node);
    array_push($days, get_inner_html($node));
}


$areaDataList = [];
$n = count($tr_nodes);
for ($i = 1; $i < $n; $i++) {
    $tr_node = $tr_nodes->item($i);
    $str = $dom->saveHTML($tr_node);
    if ($tr_node) {
        $td_nodes = $tr_node->getElementsByTagName('td');
        $td_node = $td_nodes->item(0);
        if (count($td_nodes) > 0) {
            if ($td_node->attributes->getNamedItem('class')) {
                if ($td_node->attributes->getNamedItem('class')->nodeValue == "area") {
                    /*
                    echo $dom->saveHTML($td_node) . "\n";
                    $m = count($td_nodes);
                    for ($j = 1; $j < $m; $j++) {
                        //echo $j." // ".$td_nodes->item($j)->nodeValue."\n";
                        $main_td_node = $td_nodes->item($j);
                        echo $dom->saveHTML($main_td_node) . "\n";
                    }
                    //次の列
                    $next_tr_node = $tr_nodes->item($i + 1);
                    $next_td_nodes = $next_tr_node->getElementsByTagName('td');
                    echo $dom->saveHTML($next_tr_node) . "\n";
                    $m = count($next_td_nodes);
                    for ($j = 0; $j < $m; $j++) {

                        echo $j . " // " . $next_td_nodes->item($j)->nodeValue . "\n";

                    }
                    print (count($next_td_nodes) . "\n");
                    */
                    $next_tr_node = $tr_nodes->item($i + 1);
                    $areaData = new AreaData();
                    $areaData->setData($days,$tr_node,$next_tr_node);

                    array_push($areaDataList,$areaData);
                };
            }
        }

    }
    //echo $node->saveHTML($td_node);
    //echo "////////////// \n";
}


$jsonstr =  json_encode($areaDataList, JSON_UNESCAPED_UNICODE);


echo $jsonstr;
//end
function get_inner_html($node)
{
    $innerHTML = '';
    $children = $node->childNodes;
    foreach ($children as $child) {

        $innerHTML .= $child->ownerDocument->saveXML($child);
    }

    return $innerHTML;
}

function getElementsByClassName($dom, $ClassName, $tagName = null)
{
    if ($tagName) {
        $Elements = $dom->getElementsByTagName($tagName);
    } else {
        $Elements = $dom->getElementsByTagName("*");
    }
    $Matched = array();
    for ($i = 0; $i < $Elements->length; $i++) {
        if ($Elements->item($i)->attributes->getNamedItem('class')) {
            if ($Elements->item($i)->attributes->getNamedItem('class')->nodeValue == $ClassName) {
                $Matched[] = $Elements->item($i);
            }
        }
    }
    return $Matched;
}

class AreaData
{
    public $area;
    public $forecastDataList = [];

    public function setData($days ,$main_tr, $next_tr)
    {
        $main_td_nodes = $main_tr->getElementsByTagName('td');
        $next_td_nodes = $next_tr->getElementsByTagName('td');
        $area = $main_td_nodes->item(0)->nodeValue;
        $area = str_replace(array("\r\n", "\r", "\n"), '', $area);
        $this->area = str_replace(PHP_EOL, '' , $area);
        //
        $this->forecastDataList = [];
        $n = count($days);
        for ($i = 0; $i < $n; $i++) {
            $day = $days[$i];
            $main_td = $main_td_nodes->item($i+1);
            $next_td = $next_td_nodes->item($i);

            //echo "day //".$day;
            /*echo "main // " . $main_td->nodeValue . "\n";
            echo "main // " . get_inner_html($main_td) . "\n";
            echo "next // " . $next_td->nodeValue . "\n";
            */
            $forecastData = new ForecastData();
            $forecastData->setData($day,$main_td,$next_td);

            array_push($this->forecastDataList,$forecastData);
        }
    }
}

class ForecastData
{
    public $day;
    public $week;
    public $highestTemperature;
    public $lowestTemperature;
    public $rainyPercent;
    public $accuracy;
    public $value;

    public function setData($day_week, $main_td, $next_td)
    {
        //$list = explode("<br>",$day_week);
        $list = explode('<br/>', $day_week);
        //echo $day_week."\n";
        $this->day = $list[0];
        $this->week = $list[1];
        //

        $font_node = $main_td->getElementsByTagName('font')->item(0);
        $this->lowestTemperature = $font_node->nodeValue;
        $font_node = $main_td->getElementsByTagName('font')->item(1);
        $this->highestTemperature = $font_node->nodeValue;
        $font_node = $main_td->getElementsByTagName('font')->item(2);
        $this->rainyPercent = $font_node->nodeValue;
        $img_node = $main_td->getElementsByTagName('img')->item(0);
        $this->value = $img_node->getAttribute("title");

        $this->accuracy = $next_td->nodeValue;

        /*
        echo "day :".$day."\n";
        echo "week :".$week."\n";
        echo "lowestTemperature :".$lowestTemperature."\n";
        echo "highestTemperature :".$highestTemperature."\n";
        echo "rainyPercent :".$rainyPercent."\n";
        echo "value :".$value."\n";
        echo "accuracy :".$accuracy."\n";
        echo "/// \n";
        */

    }
}