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
		$db = Db::getInstance()->Execute('CREATE TABLE `'._DB_PREFIX_.'invoice_bill` (	`id_cart` INT(10) NOT NULL,	`document_type` INT(1) NOT NULL, PRIMARY KEY (`id_cart`, `document_type`))');
		Configuration::updateValue('IVBILL_DEFAULT', 2);
		
		return  parent::install() 
				&& $this->registerHook('displayPaymentTop') 
				&& $this->registerHook('displayHeader')
				&& $this->registerHook('displayAdminOrderContentOrder');
	}
	/**
	* 
	* 
	* @return
	*/
	public function uninstall()
	{
		$db = Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'invoice_bill`;');
     	return parent::uninstall() && $db  ;
	}
	/**
	* 
	* @param undefined $params
	* 
	* @return
	*/
	public function hookdisplayAdminOrderContentOrder($params){
		$out='';
		$order = new Order($params['order']->id);
		$cart_id = Order::getCartIdStatic($params['order']->id);
		$choice =  Db::getInstance()->getRow('SELECT document_type FROM `'._DB_PREFIX_.'invoice_bill` WHERE id_cart = '.$cart_id);

		if($choice)
			{
			$out = "<h3 style='margin-bottom:10px; border-bottom:1px solid #ccc;'>".$this->l('Sale document').": <span class='label label-info'><strong>".($choice['document_type']==1?$this->l('Invoice'):$this->l('Bill'))."</strong></span></h3>";
			}	
		return $out;
		
	}
	
	/**
	* 
	* 
	* @return
	*/
	public function hookDisplayHeader(){ 
  		 $this->context->controller->registerJavascript(
			        'paragonfaktura',
			        'modules/'.$this->name.'/save.js',
			        array('position' => 'bottom', 'priority' => 150)
			    );
	}
	/**
	* 
	* @param undefined $params
	* 
	* @return
	*/
	public function hookdisplayPaymentTop($params)
	{		
		$context = Context::getContext();
		$invoice_bill = Configuration::get('IVBILL_DEFAULT'); 
		$choice = Db::getInstance()->ExecuteS('SELECT document_type FROM `'._DB_PREFIX_.'invoice_bill` WHERE id_cart = '.$this->context->cookie->id_cart);

		if($choice)
			{
				if(!empty($choice['document_type']))
						$invoice_bill = $choice['document_type'];
					else{
						Db::getInstance()->update('invoice_bill', array(
						    'document_type'      => $invoice_bill
						), 'id_cart = '.(int)$this->context->cookie->id_cart);
					}
			}else{
				Db::getInstance()->insert('invoice_bill', array(
				    'id_cart' => (int)$this->context->cookie->id_cart,
				    'document_type'      => $invoice_bill,
				));

			}
		$this->context->smarty->assign(
			array(
				'type' => $invoice_bill,
				'id_cart' => $this->context->cookie->id_cart
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
		if (Tools::isSubmit('submit'.$this->name))
		{
			Configuration::updateValue('IVBILL_DEFAULT', $_POST['IVBILL_DEFAULT']); 
			
			$output .= $this->displayConfirmation($this->l('Settings updated'));
		}
		return $output.$this->displayForm();
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
    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
 
    // Language
    $helper->default_form_language = $default_lang;
    $helper->allow_employee_form_lang = $default_lang;
 
    // Title and toolbar
    $helper->title = $this->displayName;
    $helper->show_toolbar = true;        // false -> remove toolbar
    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
    $helper->submit_action = 'submit'.$this->name;
    $helper->toolbar_btn = array(
        'save' =>
        array(
            'desc' => $this->l('Save'),
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
            '&token='.Tools::getAdminTokenLite('AdminModules'),
        ),
        'back' => array(
            'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Back to list')
        )
    );
 
    // Load current value
    $helper->fields_value['IVBILL_DEFAULT'] = $invoice_bill = Configuration::get('IVBILL_DEFAULT'); ;
 
    return $helper->generateForm($fields_form);
}

	
}
?>