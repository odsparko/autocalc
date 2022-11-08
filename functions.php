<?php

function get_currencies()
{
    $file = date('Y-m-d', time());

    if(!file_exists('cache/'.$file) || (time() - filemtime('cache/'.$file) > 3600)) {
		$currency = [
			1 => ["name" => "Евро"],
			2 => ["name" => "Доллары, США"],
			3 => ["name" => "Рубли"],
			4 => ["name" => "Йена"]
		];
		//$rates = json_decode(file_get_contents('https://www.cbr-xml-daily.ru/daily_json.js'), true);
		
		$xml = new DOMDocument();
		$url = 'https://www.cbr.ru/scripts/XML_daily.asp';

		if ($xml->load($url))
		{
			$root = $xml->documentElement;
			$items = $root->getElementsByTagName('Valute');

			foreach ($items as $item)
			{
				$code = $item->getElementsByTagName('CharCode')->item(0)->nodeValue;
				$curs = $item->getElementsByTagName('Value')->item(0)->nodeValue;
				$nominal = $item->getElementsByTagName('Nominal')->item(0)->nodeValue;
				
				if($code == 'EUR'){
					$currency[1]['rate'] = floatval(str_replace(',', '.', $curs));
					$currency[1]['html'] = '&euro;';
					$currency[1]['nominal'] = $nominal;
				}
				if($code == 'USD'){
					$currency[2]['rate'] = floatval(str_replace(',', '.', $curs));
					$currency[2]['html'] = '$';
					$currency[2]['nominal'] = $nominal;
				}
				if($code == 'JPY'){
					$currency[4]['rate'] = floatval(str_replace(',', '.', $curs));
					$currency[4]['html'] = '&#165;';
					$currency[4]['nominal'] = $nominal;
				}
				
			}
			$currency[3]['rate'] = 1;
			$currency[3]['html'] = '&#8381;';
			$currency[3]['nominal'] = 1;

			$fp = fopen('cache/'.$file, 'w');
			fwrite($fp, json_encode($currency));
			fclose($fp);
		} 
    }
    else {
        $currency = json_decode(file_get_contents('cache/'.$file), true);    
    }
    return $currency;
}

function get_auctions()
{
    $filename='auction.csv';
    $delimiter=';';
    
    if(!file_exists($filename) || !is_readable($filename))
        return FALSE;

    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE)
    {
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
        {
            if(!$data[$row[0]]) $data[$row[0]] = [];
            $data[$row[0]][$row[1]] = $row[2];
        }
        fclose($handle);
    }
    
    return $data;
}