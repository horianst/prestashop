<?php

include_once (_PS_MODULE_DIR_.'/cargus/cargus.class.php');

class CargusOrderHistoryController extends ModuleAdminController
{
    /**
     * CargusOrderHistory constructor.
     * @throws PrestaShopException
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        parent::init();
    }

    public function initContent()
    {
        parent::initContent();

        if(Configuration::get('CARGUS_USERNAME', $id_lang = NULL) == '' || Configuration::get('CARGUS_PASSWORD', $id_lang = NULL) == ''){
            $this->errors[] = 'Va rugam sa completati username-ul si parola in pagina de configurare a modulului!';
        } else {
            $cargus = new CargusClass(Configuration::get('CARGUS_API_URL', $id_lang = NULL), Configuration::get('CARGUS_API_KEY', $id_lang = NULL));

            $fields = array(
                'UserName' => Configuration::get('CARGUS_USERNAME', $id_lang = NULL),
                'Password' => Configuration::get('CARGUS_PASSWORD', $id_lang = NULL)
            );

            $token = $cargus->CallMethod('LoginUser', $fields, 'POST');

            $awbs = $cargus->CallMethod('Awbs?&orderId=' . Tools::getValue('OrderId'), [], 'GET', $token);

            $this->context->smarty->assign('orderId', Tools::getValue('OrderId'));

            if (is_null($awbs)) {
                $this->errors[] = 'Nu exista niciun AWB asociat acestei comenzi!';
            } else {
                $this->context->smarty->assign('orderId', Tools::getValue('OrderId'));
                $this->context->smarty->assign('awbs', $awbs);
                $this->context->smarty->assign('token', Tools::getAdminTokenLite('CargusAwbHistory'));

                $this->setTemplate('order_history.tpl');
            }
        }
    }
}