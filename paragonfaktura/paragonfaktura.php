<?php
if (!defined('_PS_VERSION_'))
    exit;

class paragonfaktura extends Module
{
    public function __construct()
    {
        $this->name = 'paragonfaktura';
        $this->tab = 'billing_invoicing';
        $this->displayName = 'Paragon Faktura';
        $this->version = '1.0.1';
        $this->author = 'Cypis.net';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.6.0', 'max' => '1.7');

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->is_eu_compatible = 1;


        parent::__construct();

        $this->displayName = $this->l('Paragon Faktura 1.7');
        $this->description = $this->l('Select sale document on checkout');

        $this->confirm_uninstall = $this->l('Are you sure you want to uninstall? You will lose all your settings!');


    }

    /**
     *
     *
     * @return
     */
    public function install()
    {

        $db = Db::getInstance()->Execute('CREATE TABLE `' . _DB_PREFIX_ . 'invoice_bill` (	`id_cart` INT(10) NOT NULL,	`document_type` INT(1) NOT NULL, PRIMARY KEY (`id_cart`, `document_type`))');
        Configuration::updateValue('IVBILL_DEFAULT', 2);
        Configuration::updateValue('IVBILL_PLACE', 'hookdisplayPaymentTop');

        return parent::install()
            && $this->registerHook('displayPaymentTop')
            && $this->registerHook('displayHeader')
            && $this->registerHook('customParagon')
            && $this->registerHook('actionValidateOrder')
            && $this->registerHook('displayAdminOrderSideBottom')
            && $this->registerHook('displayAdminOrderMainBottom')
            && $this->registerHook('displayAdminOrderTabContent')

            && $this->registerHook('displayAdminOrderContentOrder');
    }

    /**
     *
     *
     * @return
     */
    public function uninstall()
    {
        $db = Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'invoice_bill`;');
        return parent::uninstall() && $db;
    }

    /**
     *
     * @param undefined $params
     *
     * @return
     */
    public function hookdisplayAdminOrderContentOrder($params)
    {
        $out = '';
        $order = new Order($params['order']->id);
        $cart_id = Order::getCartIdStatic($params['order']->id);
        $choice = Db::getInstance()->getRow('SELECT document_type FROM `' . _DB_PREFIX_ . 'invoice_bill` WHERE id_cart = ' . $cart_id);

        if ($choice) {
            $out = "<h3 style='margin-bottom:10px; border-bottom:1px solid #ccc;'>" . $this->l('Sale document') . ": <span class='label label-info'><strong>" . ($choice['document_type'] == 1 ? $this->l('Invoice') : $this->l('Bill')) . "</strong></span></h3>";
        }
        return $out;

    }

    public function hookdisplayAdminOrderSideBottom($params)
    {
        return "";
    }

    public function hookdisplayAdminOrderMainBottom($params)
    {
        return "";
    }

    public function hookdisplayAdminOrderTabContent($params)
    {
        $out = '';
        $order = new Order($params['id_order']);
        $cart_id = Order::getCartIdStatic($params['id_order']);
        $choice = Db::getInstance()->getRow('SELECT document_type FROM `' . _DB_PREFIX_ . 'invoice_bill` WHERE id_cart = ' . $cart_id);

        if ($choice) {
            $out = "<h3 style='margin-bottom:10px; border-bottom:1px solid #ccc;'>" . $this->l('Sale document') . ": <span class='badge rounded badge-info'><strong>" . ($choice['document_type'] == 1 ? $this->l('Invoice') : $this->l('Bill')) . "</strong></span></h3>";
        }

        return $out . $this->l('Order id') . ': ' . $params['id_order'] . ' / ' . $this->l('Cart id') . ': ' . $cart_id;;
    }

    /**
     *
     *
     * @return
     */
    public function hookDisplayHeader()
    {
        $this->context->controller->registerJavascript(
            'paragonfaktura',
            'modules/' . $this->name . '/save.js',
            array('position' => 'bottom', 'priority' => 150)
        );
    }

    public function hookCustomParagon($params)
    {
        $this->context->controller->addCSS($this->_path . 'views/css/paragonfaktura.css');
        $pl = Configuration::get('IVBILL_PLACE');
        if ($pl === 'hookCustomParagon') {

            $context = Context::getContext();
            $invoice_bill = Configuration::get('IVBILL_DEFAULT');
            $choice = Db::getInstance()->getRow('SELECT document_type FROM `' . _DB_PREFIX_ . 'invoice_bill` WHERE id_cart = ' . $this->context->cookie->id_cart);
            if ($choice) {
                if (!empty($choice['document_type']))
                    $invoice_bill = $choice['document_type'];
                else {
                    Db::getInstance()->update('invoice_bill', array(
                        'document_type' => $invoice_bill
                    ), 'id_cart = ' . (int)$this->context->cookie->id_cart);
                }
            } else {
                Db::getInstance()->insert('invoice_bill', array(
                    'id_cart' => (int)$this->context->cookie->id_cart,
                    'document_type' => $invoice_bill,
                ));

            }
            $this->context->smarty->assign(
                array(
                    'type' => $invoice_bill,
                    'id_cart' => $this->context->cookie->id_cart,

                )
            );

            return $this->display(__FILE__, 'cart.tpl');
        }
    }

    /**
     *
     * @param undefined $params
     *
     * @return
     */

    public function hookdisplayPaymentTop($params)
    {
        $this->context->controller->addCSS($this->_path . 'views/css/paragonfaktura.css');
        $pl = Configuration::get('IVBILL_PLACE');
        if ($pl != 'hookdisplayPaymentTop') {
            return null;
        }
        //set to default value
        $invoice_bill = Configuration::get('IVBILL_DEFAULT');

        // check if customer has picked a confirmation type already, if not create in DB
        $sql = 'SELECT document_type FROM `' . _DB_PREFIX_ . 'invoice_bill` WHERE id_cart = ' . $this->context->cookie->id_cart;
        $choice = Db::getInstance()->getRow($sql);
        if ($choice && !empty($choice["document_type"])) {
            if (!empty($choice['document_type'])){
                $invoice_bill = $choice['document_type'];
            }
            else {
                Db::getInstance()->update('invoice_bill', array(
                    'document_type' => $invoice_bill
                ), 'id_cart = ' . (int)$this->context->cookie->id_cart);
            }
        } else {
            Db::getInstance()->insert('invoice_bill', array(
                'id_cart' => (int)$this->context->cookie->id_cart,
                'document_type' => $invoice_bill,
            ));
        }

        $sql = 'SELECT id_address_invoice FROM `' . _DB_PREFIX_ . 'cart` WHERE id_cart = ' . $this->context->cookie->id_cart;
        $id_invoice = Db::getInstance()->getValue($sql);
        $getBusinessData = 'SELECT company,vat_number FROM `' . _DB_PREFIX_ . 'address` WHERE id_address = ' . $id_invoice;
        $businessData = Db::getInstance()->getRow($getBusinessData);
        $companyName = $businessData["company"];
        $nip = $businessData["vat_number"];

        $this->context->smarty->assign(
            array(
                'type' => $invoice_bill,
                'id_cart' => $this->context->cookie->id_cart,
                'company' => $companyName,
                'id_address' => $id_invoice,
                'nip' => $nip,
            )
        );
        return $this->display(__FILE__, 'cart.tpl');
    }

    /**
     *
     *
     * @return
     */
    public function getContent()
    {


        $output = null;
        if (Tools::isSubmit('submit' . $this->name)) {
            Configuration::updateValue('IVBILL_DEFAULT', $_POST['IVBILL_DEFAULT']);
            Configuration::updateValue('IVBILL_PLACE', $_POST['IVBILL_PLACE']);

            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }
        return $output . $this->displayForm() . "<div class='panel'>" . $this->l('For custom hook display use code') . ": <code>{hook h='CustomParagon'}</code> </div>";
    }

    /**
     *
     *
     * @return
     */
    public function displayForm()
    {
        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $options_place = array(
            array(
                'id_option' => 'hookdisplayPaymentTop',
                'name' => $this->l('Payment section')
            ),
            array(
                'id_option' => 'hookCustomParagon',
                'name' => $this->l('Custom section')
            ),
        );
        $options = array(
            array(
                'id_option' => 1,
                'name' => $this->l('Invoice')
            ),
            array(
                'id_option' => 2,
                'name' => $this->l('Bill')
            ),
        );
        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Default sale document'),
                    'desc' => $this->l('Select default selected sale document'),
                    'name' => 'IVBILL_DEFAULT',
                    'required' => true,
                    'options' => array(
                        'query' => $options,
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Default sale edisplay placet'),
                    'desc' => $this->l('Select default display place'),
                    'name' => 'IVBILL_PLACE',
                    'required' => true,
                    'options' => array(
                        'query' => $options_place,
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                )

            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = array(
            'save' =>
                array(
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                        '&token=' . Tools::getAdminTokenLite('AdminModules'),
                ),
            'back' => array(
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        // Load current value
        $helper->fields_value['IVBILL_DEFAULT'] = Configuration::get('IVBILL_DEFAULT');;
        $helper->fields_value['IVBILL_PLACE'] = Configuration::get('IVBILL_PLACE');;

        return $helper->generateForm($fields_form);
    }


    public function hookActionValidateOrder($params)
    {
        //send email to store for conifirmation
//	  	$cart = $params['cart']; // The cart object
//	    $order_status = $params['orderStatus']; // The order status
//	    $order = $params['order']; // And the order object
//
//	    $choice =  Db::getInstance()->getRow('SELECT document_type FROM `'._DB_PREFIX_.'invoice_bill` WHERE id_cart = '.$cart->id);
//
//		if($choice)
//			{
//			$out = "<h3 style='margin-bottom:10px; border-bottom:1px solid #ccc;'>".$this->l('Sale document').": <span class='label label-info'><strong>".($choice['document_type']==1?$this->l('Invoice'):$this->l('Bill'))."</strong></span></h3>";
//			}
//
//		$to_email = $customer->email;
//
//	  		$iso = Language::getIsoById((int) Configuration::get('PS_LANG_DEFAULT'));
//			$dir_mail = false;
//            if (file_exists(dirname(__FILE__).'/mails/'.$iso.'/document.txt') &&
//                file_exists(dirname(__FILE__).'/mails/'.$iso.'/document.html')) {
//                $dir_mail = dirname(__FILE__).'/mails/';
//            }
//
//            if (file_exists(_PS_MAIL_DIR_.$iso.'/document.txt') &&
//                file_exists(_PS_MAIL_DIR_.$iso.'/document.html')) {
//                $dir_mail = _PS_MAIL_DIR_;
//            }
//
//			$context = Context::getContext();
//			$id_lang = (int) $context->language->id;
//			$id_shop = (int) $context->shop->id;
//
//			$template_vars = array(
//						'{firstname}' => $customer->firstname,
//						'{lastname}' => $customer->lastname,
//						'{shop_name}' => Configuration::get('PS_SHOP_NAME'),
//						'{order_name}'	=> $order->reference,
//						'{info}'	=> $out
//						);
//			 //die( Configuration::get('PS_SHOP_EMAIL') );
//			 if ($dir_mail) {
//			 	if (Validate::isEmail(Configuration::get('PS_SHOP_EMAIL')))
//			         $out =  Mail::Send(
//		                    $id_lang,
//		                    'document',
//		                    $this->l('Document picked for order')."#".$order->id." ".$order->reference,
//		                    $template_vars,
//		                    Configuration::get('PS_SHOP_EMAIL'),
//		                    null,
//		                    Configuration::get('PS_SHOP_EMAIL'),
//		                    Configuration::get('PS_SHOP_NAME'),
//		                    null,
//		                    null,
//		                    $dir_mail,
//		                    null,
//		                    $id_shop
//		                );
//				}

        return;

    }

}

?>
