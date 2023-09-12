<?php
require_once(dirname(__FILE__).'../../../config/config.inc.php');
require_once(dirname(__FILE__).'../../../init.php');

	$id_cart = (int)(Tools::getValue('id_cart'));
	$value = (int)(Tools::getValue('value'));
	if(!empty($id_cart) and !empty($value)){
			$choice = Db::getInstance()->ExecuteS('SELECT document_type FROM `'._DB_PREFIX_.'invoice_bill` WHERE id_cart = '.$id_cart);
	
			if(count($choice) == 0)
			{
				Db::getInstance()->insert('invoice_bill', array(
					'id_cart' => $id_cart,
					'document_type' => $value
					));
			} else {
				Db::getInstance()->update('invoice_bill', 
											array('document_type'=>$value), 
											'id_cart = '.(int) $id_cart 
										  );
			}
	}

?>