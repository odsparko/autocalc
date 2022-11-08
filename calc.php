<?php
require_once("functions.php");

$user       = ($_REQUEST['user'] && in_array((int)$_REQUEST['user'], [1,2])) ? (int)$_REQUEST['user'] : 1;
$age        = ($_REQUEST['age'] && in_array((int)$_REQUEST['age'], [1,3,5,7])) ? (int)$_REQUEST['age'] : 3;
$engine     = ($_REQUEST['engine'] && in_array((int)$_REQUEST['engine'], [1,2,3,4])) ? (int)$_REQUEST['engine'] : 1;
$power      = ($_REQUEST['power']) ? (float)$_REQUEST['power'] : 150;
$power_type = ($_REQUEST['power_type'] && in_array((int)$_REQUEST['power_type'], [1,2])) ? (int)$_REQUEST['power_type'] : 1;
$power_ls   = floor(($power && $power_type) ? (($power_type == 2) ? ($power/0.735) : $power) : 110);
$vol        = ($_REQUEST['vol']) ? (float)$_REQUEST['vol'] : 1600;
$auction    = ($_REQUEST['auction']) ? $_REQUEST['auction'] : "Toyama Port-Honda Tokyo";
$price      = ($_REQUEST['price']) ? (float)$_REQUEST['price'] : 500000;
$currency   = ($_REQUEST['currency'] && in_array((int)$_REQUEST['currency'], [1,2,3,4])) ? (int)$_REQUEST['currency'] : 1;
$glonas     = ($_REQUEST['glonas']) ? 43000 : 0;
$service    = 30000;
$svh        = 58000;
$dosyavkaDoVlad = 170000;
$laboratory = 5000;



$currencies = get_currencies(); 
$auctions   = get_auctions();

$fob = 0;
$auc_select = "";
foreach($auctions as $port => $auc) {
    $auc_select.='<optgroup label="'.$port.'">';
    foreach($auc as $name => $_fob) {
        $selected = "";
        if($port."-".$name == $auction) {
            $fob = $_fob;
            $selected = " selected";
        }
        $auc_select.='<option value="'.$port."-".$name.'" '.$selected.'>'.$name.'</option>';
    }
    $auc_select.='</optgroup>';
};

$auction_text = str_replace("-", ", ", $auction);


    /*
     *   Сумма сбора оформления, зависит только от стоимости автомобиля.
     */ 
    if($currency !=3) { // если не рубли, то переводим в рубли по текущему курсу
       $price_rub = round($price * $currencies[$currency]['rate']/$currencies[$currency]['nominal'], 2); 
    }
    else {
        $price_rub = $price;
    }
    
    switch (true) {
        case($price_rub <= 200000):  $sbor =   775; break;
        case($price_rub <= 450000):  $sbor =  1550; break;
        case($price_rub <= 1200000): $sbor =  3100; break;
        case($price_rub <= 2700000): $sbor =  8530; break;
        case($price_rub <= 4200000): $sbor = 12000; break;
        case($price_rub <= 5500000): $sbor = 15500; break;
        case($price_rub <= 7000000): $sbor = 20000; break;
        case($price_rub <= 8000000): $sbor = 23000; break;
        case($price_rub <= 9000000): $sbor = 25000; break;
        case($price_rub <= 10000000):$sbor = 27000; break;
        default:                     $sbor = 30000;
    }
    
    
    /* 
     *   Рассчитываем утилизационный сбор
     *   УС = БС * К
     */
    $us = 0;
    $bs = 20000;
     
    switch(true) {
        case($age == 1): 
            if($user == 1) {
                $k = 0.17;
            }
            else {
                switch(true) {
                    case($vol <= 1000): $k = 2.41; break;
                    case($vol <= 2000): $k = 8.92;  break;
                    case($vol <= 3000): $k = 14.08;  break;
                    case($vol <= 3500): $k = 12.98; break;
                    default:           $k = 22.25; break;
                }
                if($engine == 4) $k = 1.63;
            }
        break;
        default: 
            if($user == 1) {
                $k = 0.26;
            }
            else {
                switch(true) {
                    case($vol <= 1000): $k = 6.15;  break;
                    case($vol <= 2000): $k = 15.69; break;
                    case($vol <= 3000): $k = 24.01; break;
                    case($vol <= 3500): $k = 28.5;  break;
                    default:           $k = 35.01;  break;
                }
                if($engine == 4) $k = 6.1;
            }
    }
    $us = round($bs * $k);
    $us_text = "20 000 руб. х ".$k;
    
    
    /*
     *  Рассчитываем Акциз
     */
    $asciz = 0;
    if($user == 2 || $engine == 4) {
        switch(true) {
            case($power_ls < 91):   $stavka = 0; break;
            case($power_ls < 151):  $stavka = 53; break;
            case($power_ls < 201):  $stavka = 511; break;
            case($power_ls < 301):  $stavka = 836; break;
            case($power_ls < 401):  $stavka = 1425; break;
            case($power_ls < 501):  $stavka = 1475; break;
            default:                $stavka = 1523; break;
        }
        $akciz = $power_ls * $stavka;
        $akciz_text = $stavka.' руб./1 л.с.';
    }
    

    /*
     *  Таможенная пошлина
     *  Если стоимость указана не евро, то для расчета нужно сначала конвертировать цену в евро.
     */
    if($currency !=1) { // если не евро, то переводим в евро из рублей по текущему курсу
       $price_eur = round($price_rub / $currencies[1]['rate'], 4); 
    }
    else {
       $price_eur = $price;
    }
    //print $price_eur; exit;
    if($user == 1) { // Для физического лица
        if($age == 1) { // Для автомобилей младше 3-х лет
            switch(true) {
                case($price_eur < 8500):   
                    $percent = 48;
                    $k = 2.5; 
                break;
                case($price_eur < 16700):     
                    $percent = 48;
                    $k = 3.5;
                break;
                case($price_eur < 42300):     
                    $percent = 48;
                    $k = 5.5; 
                break;
                case($price_eur < 84500):     
                    $percent = 48;
                    $k = 7.5;
                break;
                case($price_eur < 169000):     
                    $percent = 48;
                    $k = 15;
                break;
                default:                
                    $percent = 48;
                    $k = 20;
            }
            $stavka_percent = $price_eur * $percent / 100;
            $stavka_vol     = $vol * $k; 
            $tstavka = ($stavka_percent > $stavka_vol) ? $stavka_percent : $stavka_vol;
            $tstavka_text = $percent.'%, но не менее '.$k.' евро/см<sup>3</sup>';
        }
        elseif($age == 3) { // Для автомобилей от 3 до 5 лет
            switch(true) {
                case($vol < 1001):  $k = 1.5; break;
                case($vol < 1501):  $k = 1.7; break;
                case($vol < 1801):  $k = 2.5; break;
                case($vol < 2301):  $k = 2.7; break;
                case($vol < 3001):  $k = 3;   break;
                default          :  $k = 3.6; break;
            }
            $tstavka = $vol * $k;
            $tstavka_text = $k.' евро/см<sup>3</sup>';
        }
        else { // Для автомобилей старше 5 лет
            switch(true) {
                case($vol < 1001):  $k = 3;   break;
                case($vol < 1501):  $k = 3.2; break;
                case($vol < 1801):  $k = 3.5; break;
                case($vol < 2301):  $k = 4.8; break;
                case($vol < 3001):  $k = 5;   break;
                default          :  $k = 5.7; break;
            }
            $tstavka = $vol * $k;
            $tstavka_text = $k.' евро/см<sup>3</sup>';
        }
    }
    else { // Для юридического лица
        if(in_array($engine, [1,3])) { // Бензиновый и гибридный двигатель
            if($age == 1) { // Для автомобилей младше 3-х лет
                switch(true) {
                    case($vol < 1001):   
                    case($vol < 1501):     
                    case($vol < 1801):     
                    case($vol < 2301):     
                    case($vol < 3001):     
                        $percent = 15;
                        break;
                    default:                
                        $percent = 12.5;
                }
                $stavka_percent = $price_eur * $percent / 100;
                $tstavka = $stavka_percent;
                $tstavka_text = $percent.'%';
            }
            elseif($age == 3) { // Для автомобилей от 3 до 5 лет
                switch(true) {
                    case($vol < 1001):   
                        $percent = 20;
                        $k = 0.36;                        
                    break;
                    case($vol < 1501):     
                        $percent = 20;
                        $k = 0.4;
                    break;
                    case($vol < 1801):     
                        $percent = 20;
                        $k = 0.36;                        
                    break;
                    case($vol < 2301):     
                        $percent = 20;
                        $k = 0.44;                        
                    break;
                    case($vol < 3001):     
                        $percent = 20;
                        $k = 0.44;
                    break;
                    default:                
                        $percent = 20;
                        $k = 8;
                }
                $stavka_percent = $price_eur * $percent / 100;
                $stavka_vol     = $vol * $k; 
                $tstavka = ($stavka_percent > $stavka_vol) ? $stavka_percent : $stavka_vol;
                $tstavka_text = $percent.'%, но не менее '.$k.' евро/см<sup>3</sup>';
            }
            elseif($age == 5) { // Для автомобилей от 5 до 7 лет
                switch(true) {
                    case($vol < 1001):   
                        $percent = 20;
                        $k = 0.36;                        
                    break;
                    case($vol < 1501):     
                        $percent = 20;
                        $k = 0.4;
                    break;
                    case($vol < 1801):     
                        $percent = 20;
                        $k = 0.36;                        
                    break;
                    case($vol < 2301):     
                        $percent = 20;
                        $k = 0.44;                        
                    break;
                    case($vol < 3001):     
                        $percent = 20;
                        $k = 0.44;
                    break;
                    default:                
                        $percent = 20;
                        $k = 8;
                }
                $stavka_percent = $price_eur * $percent / 100;
                $stavka_vol     = $vol * $k; 
                $tstavka = ($stavka_percent > $stavka_vol) ? $stavka_percent : $stavka_vol;
                $tstavka_text = $percent.'%, но не менее '.$k.' евро/см<sup>3</sup>';
            }
            else { // старше 7 лет
                switch(true) {
                    case($vol < 1001):  $k = 1.4; break;
                    case($vol < 1501):  $k = 1.5; break;
                    case($vol < 1801):  $k = 1.6; break;
                    case($vol < 2301):  $k = 2.2; break;
                    case($vol < 3001):  $k = 2.2; break;
                    default          :  $k = 3.2; break;
                }
                $tstavka = $vol * $k;
                $tstavka_text = $k.' евро/см<sup>3</sup>';
            }
        }
        elseif ($engine == 2) { // Для дизельных двигателей
            if($age == 1) { // Для автомобилей младше 3-х лет
                switch(true) {
                    case($vol < 1501):   
                    case($vol < 2501):     
                    default:                
                        $percent = 15;
                }
                $stavka_percent = $price_eur * $percent / 100;
                $tstavka = $stavka_percent;
                $tstavka_text = $percent.'%';
            }
            elseif($age == 3) { // Для автомобилей от 3 до 5 лет
                switch(true) {
                    case($vol < 1501):   
                        $percent = 20;
                        $k = 0.32;
                    break;
                    case($vol < 2501):     
                        $percent = 20;
                        $k = 0.4;
                    break;
                    default:                
                        $percent = 20;
                        $k = 8;
                }
                $stavka_percent = $price_eur * $percent / 100;
                $stavka_vol     = $vol * $k; 
                $tstavka = ($stavka_percent > $stavka_vol) ? $stavka_percent : $stavka_vol;
                $tstavka_text = $percent.'%, но не менее '.$k.' евро/см<sup>3</sup>';
            }
            elseif($age == 5) { // Для автомобилей от 5 до 7 лет
                switch(true) {
                    case($vol < 1501):   
                        $percent = 20;
                        $k = 0.32;
                    break;
                    case($vol < 2501): 
                        $percent = 20;
                        $k = 0.4;                    
                    break;
                    default: 
                        $percent = 20;
                        $k = 8; 
                }
                $stavka_percent = $price_eur * $percent / 100;
                $stavka_vol     = $vol * $k; 
                $tstavka = ($stavka_percent > $stavka_vol) ? $stavka_percent : $stavka_vol;
                $tstavka_text = $percent.'%, но не менее '.$k.' евро/см<sup>3</sup>';
            }
            else { // старше 7 лет
                switch(true) {
                    case($vol < 1501):  $k = 1.5; break;
                    case($vol < 2501):  $k = 2.2; break;
                    default          :  $k = 3.2; break;
                }
                $tstavka = $vol * $k;
                $tstavka_text = $k.' евро/см<sup>3</sup>';
            }
        }
        else {
            $tstavka = 0;
            $tstavka_text = '';
        }
    }
    $tstavka = round(ceil($tstavka * $currencies[1]['rate']*100)/100, 2);
    $fob_text = $fob;
    $fob = round($fob * ($currencies[4]['rate']/$currencies[4]['nominal']), 2);
    
    $dostavka = round($dosyavkaDoVlad * ($currencies[4]['rate']/$currencies[4]['nominal']), 2);
    
    
    
    $nds = 0;
    if($engine == 4) {
        $tstavka = round(ceil($price_eur * 0.15 * $currencies[1]['rate']*100)/100, 2);
        $tstavka_text = '15%';
    }
    if($user == 2 || $engine == 4) $nds = ($price_rub + $akciz + $tstavka) * 0.2;
    
    //print $nds;
    

$response = [
    'age'   => $age,
    'vol'   => $vol,
    'price'   => $price,
    'currency'   => $currency,
    'currencies' => $currencies,
    'engine'   => $engine,
    'power'   => $power,
    'power_type'   => $power_type,
    'itog_vl_rub' => number_format($price_rub + $fob + $dostavka, 2, ",", " "),
    'price_rub' => number_format($price_rub, 2, ",", " "),
    'sbor'  => number_format($sbor, 2, ",", " "),
    'us'    => number_format($us, 2, ",", " "),
    'us_text' => $us_text,
    'akciz' => number_format($akciz, 2, ",", " "),
    'akciz_text' => $akciz_text,
    'tstavka' => number_format($tstavka, 2, ",", " "),
    'tstavka_text' => $tstavka_text,
    'nds' => number_format($nds, 2, ",", " "),
    'fob' => number_format($fob, 2, ",", " "),
    'fob_text' => $fob_text,
    'glonas' => number_format($glonas, 2, ",", " "),
    'dostavka' => number_format($dostavka, 2, ",", " "),
    'service' => number_format($service, 2, ",", " "),
    'svh' => number_format($svh, 2, ",", " "),
    'itog' => number_format($sbor+$us+$akciz+$tstavka+$nds+$fob+$dostavka+$service+$laboratory+$svh+$glonas+$price_rub, 2, ",", " "),
    'titog' => number_format($sbor+$us+$akciz+$tstavka+$nds+$svh+$glonas, 2, ",", " "),
    'auction_text' => $auction_text,
    'auc_select' => $auc_select,
    'dosyavkaDoVlad' => $dosyavkaDoVlad,
    'laboratory'     => number_format($laboratory, 2, ",", " ")
];

header('Content-Type: application/json');
print json_encode($response);

















