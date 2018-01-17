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

	echo $result["text"];
	?>
	<form name="unitpay" action="https://unitpay.ru/pay/<?php echo $result["public_key"]; ?>">
		<input type="hidden" name="account" value="<?php echo $result["account"]; ?>">
		<input type="hidden" name="sum" value=" <?php echo $result["sum"]; ?>">
		<input type="hidden" name="desc" value="<?php echo $result["desc"]; ?>">
        <input type="hidden" name="customerEmail" value="<?php echo $result['customerEmail']; ?>">
        <?php if ($result['showCashItems']): ?>
        <input type="hidden" name="cashItems" value="<?php echo $result['cashItems']; ?>">
        <?php endif ?>
		<p><input type="submit" value="<?php echo $this->diafan->_('Оплатить', false);?>"></p>
	</form>
