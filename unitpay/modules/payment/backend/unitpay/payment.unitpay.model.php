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
		$result["text"]			= $pay['text'];
		$result["sum"]			= $pay['summ'];
		$result["desc"]			= $pay['desc'];
		$result["account"]		= $pay['id'];
		$result["public_key"]	= $params["unitpay_public_key"];
		$result['customerEmail'] = $pay['details']['email'];
		$result['cashItems'] = $this->getCashItems($pay);
		$result['showCashItems'] = $params['unitpay_send_cash_items'];
		return $result;
	}

	private function getCashItems($pay)
	{
		return base64_encode(json_encode(
			array_map(function ($item) {
				return array(
					'name' => $item['name'],
					'count' => $item['count'],
					'price' => $item['price']
				);
			}, $pay['details']['goods'])
		));
	}
}

