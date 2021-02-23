<?php

include_once (_PS_MODULE_DIR_.'/cargus/cargus.class.php');

class CargusAwbHistoryController extends ModuleAdminController
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

            $awb = $cargus->CallMethod('Awbs?&barCode=' . Tools::getValue('BarCode'), [], 'GET', $token);

            if (is_null($awb)) {
                $this->errors[] = 'Nu s-au putut prelua detaliile acestui AWB!';
            } else {
                $this->context->smarty->assign('BarCode', Tools::getValue('BarCode'));
                $this->context->smarty->assign('awb', $awb[0]);
                $this->setTemplate('awb_history.tpl');
            }

        }
    }
}