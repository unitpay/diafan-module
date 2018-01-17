<?php

function callbackHandler( $data, $self )
{
	$method = '';
	$params = array();
	if ((isset($data['params'])) && (isset($data['method'])) && (isset($data['params']['signature']))){
		$params = $data['params'];
		$method = $data['method'];
		$signature = $params['signature'];
		if (empty($signature)){
			$status_sign = false;
		}else{
			$status_sign = verifySignature($params, $method);
		}
	}else{
		$status_sign = false;
	}
//    $status_sign = true;
	if ($status_sign){
		switch ($method) {
			case 'check':
				$result = check( $params );
				break;
			case 'pay':
				$result = pay( $params, $self );
				break;
			case 'error':
				$result = error( $params );
				break;
			default:
				$result = array('error' =>
					array('message' => 'неверный метод')
				);
				break;
		}
	}else{
		$result = array('error' =>
			array('message' => 'неверная сигнатура')
		);
	}
	hardReturnJson($result);
}

function check( $params )
{
	$id = $params['account'];
	$pay = DB::query_fetch_array("SELECT * FROM {payment_history} WHERE id=%d LIMIT 1", $id);
	if (! $pay)
	{
		$result = array('error' =>
			array('message' => 'платежа не существует')
		);
	}else{
		$pay["payment"] = DB::query_fetch_array("SELECT * FROM {payment} WHERE id=%d AND payment='%s' LIMIT 1", $pay["payment_id"], 'unitpay');
		if(! $pay["payment"])
		{
			$result = array('error' =>
				array('message' => 'платежной системы не существует')
			);
		}else{
			$pay["params"] = unserialize($pay["payment"]["params"]);

			$sum = $pay["summ"];
			$currency = 'RUB';

			if (!isset($params['orderSum']) || ((float)$sum != (float)$params['orderSum'])) {
				$result = array('error' =>
					array('message' => 'не совпадает сумма заказа')
				);
			}elseif (!isset($params['orderCurrency']) || ($currency != $params['orderCurrency'])) {
				$result = array('error' =>
					array('message' => 'не совпадает валюта заказа')
				);
			}
			else{
				$result = array('result' =>
					array('message' => 'Запрос успешно обработан')
				);
			}
		}
	}

	return $result;
}

function pay( $params, $self )
{
	$id = $params['account'];
	$pay = DB::query_fetch_array("SELECT * FROM {payment_history} WHERE id=%d LIMIT 1", $id);
	if (! $pay)
	{
		$result = array('error' =>
			array('message' => 'платежа не существует')
		);
	}else{
		$pay["payment"] = DB::query_fetch_array("SELECT * FROM {payment} WHERE id=%d AND payment='%s' LIMIT 1", $pay["payment_id"], 'unitpay');
		if(! $pay["payment"])
		{
			$result = array('error' =>
				array('message' => 'платежной системы не существует')
			);
		}else{
			$pay["params"] = unserialize($pay["payment"]["params"]);

			$sum = $pay["summ"];
			$currency = 'RUB';

			if (!isset($params['orderSum']) || ((float)$sum != (float)$params['orderSum'])) {
				$result = array('error' =>
					array('message' => 'не совпадает сумма заказа')
				);
			}elseif (!isset($params['orderCurrency']) || ($currency != $params['orderCurrency'])) {
				$result = array('error' =>
					array('message' => 'не совпадает валюта заказа')
				);
			}
			else{
				$self->diafan->_payment->success($pay, 'pay');
				$result = array('result' =>
					array('message' => 'Запрос успешно обработан')
				);
			}
		}
	}

	return $result;
}

function error( $params )
{
	$result = array('result' =>
		array('message' => 'Запрос успешно обработан')
	);
	return $result;
}

function getSignature($method, array $params, $secretKey)
{
	ksort($params);
	unset($params['sign']);
	unset($params['signature']);
	array_push($params, $secretKey);
	array_unshift($params, $method);
	return hash('sha256', join('{up}', $params));
}

function verifySignature($params, $method)
{
	$id = $params['account'];
	$pay = DB::query_fetch_array("SELECT * FROM {payment_history} WHERE id=%d LIMIT 1", $id);
	if (! $pay)
	{
		return false;
	}

	$pay["payment"] = DB::query_fetch_array("SELECT * FROM {payment} WHERE id=%d AND payment='%s' LIMIT 1", $pay["payment_id"], 'unitpay');
	if(! $pay["payment"])
	{
		return false;
	}
	$pay["params"] = unserialize($pay["payment"]["params"]);
	$secret = $pay["params"]["unitpay_secret_key"];
	return $params['signature'] == getSignature($method, $params, $secret);
}

function hardReturnJson( $arr )
{
	header('Content-Type: application/json');
	$result = json_encode($arr);
	die($result);
}

callbackHandler($_GET, $this);