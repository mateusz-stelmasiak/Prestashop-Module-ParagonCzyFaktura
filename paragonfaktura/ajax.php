<?php
require_once(dirname(__FILE__) . '../../../config/config.inc.php');
require_once(dirname(__FILE__) . '../../../init.php');

//Get all values
$id_cart = (int)(Tools::getValue('id_cart'));
$value = (int)(Tools::getValue('value'));
$id_address = (int)(Tools::getValue('id_address'));
$nip = (string)(Tools::getValue('nip'));
$companyName = (string)(Tools::getValue('companyName'));

// Change document type selected
if (!empty($id_cart) and !empty($value)) {
    $choice = Db::getInstance()->ExecuteS('SELECT document_type FROM `' . _DB_PREFIX_ . 'invoice_bill` WHERE id_cart = ' . $id_cart);
    if (count($choice) == 0) {
        $insert_sql = 'INSERT INTO ' . _DB_PREFIX_ . 'invoice_bill (id_cart,document_type) VALUES(' . $id_cart . ',' . $value . ')';
        Db::getInstance()->execute($insert_sql);

    } else {
        $update_sql = 'UPDATE ' . _DB_PREFIX_ . 'invoice_bill SET document_type = ' . $value . ' WHERE id_cart = ' . $id_cart;
        Db::getInstance()->execute($update_sql);
    }
}
// Change NIP
if (!empty($nip)) {
    $update_sql = 'UPDATE ' . _DB_PREFIX_ . 'address SET vat_number = ' . $nip . ' WHERE id_address = ' . $id_address;
    Db::getInstance()->execute($update_sql);
}
// Change company name
if (!empty($companyName)) {
    $update_sql = 'UPDATE ' . _DB_PREFIX_ . 'address SET company = "' . $companyName . '" WHERE id_address = ' . $id_address;
    Db::getInstance()->execute($update_sql);
}

?>