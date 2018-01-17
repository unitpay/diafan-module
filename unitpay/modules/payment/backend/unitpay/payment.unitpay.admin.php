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

class Payment_unitpay_admin
{
	public $config;

	public function __construct()
	{
		$this->config = array(
			"name" => 'Unitpay',
			"params" => array(
				'unitpay_public_key' => array(
					'name'	=>	'PUBLIC KEY',
					'help'	=>	'Возьмите PUBLIC KEY в личном кабинете Unitpay.ru'
				),
				'unitpay_secret_key' =>  array(
					'name'	=>	'SECRET KEY',
					'help'	=>	'Возьмите SECRET KEY в личном кабинете Unitpay.ru'
				),
                'unitpay_send_cash_items' => array(
                    'name' => 'Передавать позиции заказа',
                    'type' => 'checkbox',
                    'help' => 'Если опция включена, то в чеке будут присутствовать позиции заказа (для ФЗ-54)'
                )
			)
		);
	}
}