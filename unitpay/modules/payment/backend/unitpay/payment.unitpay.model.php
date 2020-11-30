<?php

if (! defined('DIAFAN'))
{
	$path = __FILE__; $i = 0;
	while(! file_exists($path.'/includes/404.php'))
	{
		if($i == 10) exit; $i++;
		$path = dirname($path);
	}
	include $path.'/includes/404.php';
}

class Payment_unitpay_model extends Diafan
{

	public function get($params, $pay)
	{
		$sum = number_format($pay['summ'], 2, '.','');
		
		$desc = strip_tags($pay['text']);
		
		$signature = hash('sha256', join('{up}', array(
			$pay['id'],
			"RUB",
			$desc,
			$sum,
			trim($params["unitpay_secret_key"])
		)));
		
		$result["text"]			= $pay['text'];
		$result["sum"]			= $sum;
		$result["desc"]			= $desc;
		$result["account"]		= $pay['id'];
		$result["signature"]	= $signature;
		$result["currency"]		= "RUB";
        $result["domain"]	    = $params["unitpay_domain"];
		$result["public_key"]	= $params["unitpay_public_key"];
		$result['customerPhone'] =  preg_replace('/\D/', '', $pay['details']['phone']);
		$result['customerEmail'] = $pay['details']['email'];
		$result['cashItems'] = $this->getCashItems($params, $pay);
		$result['showCashItems'] = $params['unitpay_send_cash_items'];
		
		return $result;
	}

	private function getCashItems($params, $pay)
	{
		$items = array_map(function ($item) use($params, $pay) {
			return array(
				'name' => $item['name'],
				'count' => $item['count'],
				'currency' => "RUB",
				'price' => number_format($item['price'], 2, '.',''),
				'nds' => isset($params["unitpay_vat_code"]) ? $params["unitpay_vat_code"] : "none",
				"type" => "commodity",
			);
		}, $pay['details']['goods']);
		
		if(isset($pay['details']['additional'])) {
			foreach($pay['details']['additional'] as $additional) {
			    if($additional['summ'] > 0) {
                    $items[] = array(
                        'name' => $additional['name'],
                        'count' => 1,
                        'currency' => "RUB",
                        'price' => number_format($additional['summ'], 2, '.',''),
                        'nds' => isset($params["unitpay_vat_code"]) ? $params["unitpay_vat_code"] : "none",
                        "type" => "commodity",
                    );
                }
			}
		}
		
		if(isset($pay['details']['delivery']['summ']) && $pay['details']['delivery']['summ'] > 0) {
			$items[] = array(
				'name' => $pay['details']['delivery']['name'],
				'count' => 1,
				'currency' => "RUB",
				'price' => number_format($pay['details']['delivery']['summ'], 2, '.',''),
				'nds' => isset($params["unitpay_vat_code"]) ? $params["unitpay_vat_code"] : "none",
				"type" => "service",
			);
		}
			
		return base64_encode(json_encode($items));
	}
}

