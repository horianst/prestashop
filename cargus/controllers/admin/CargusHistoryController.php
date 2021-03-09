<?php

include_once (_PS_MODULE_DIR_.'/cargus/cargus.class.php');

class CargusHistoryController extends ModuleAdminController
{
    /**
     * CargusHistoryController constructor.
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

        if (Tools::isSubmit('submitPickup')) {
            Configuration::updateValue('CARGUS_PUNCT_RIDICARE', Tools::getValue('CARGUS_PUNCT_RIDICARE'));

            $_SESSION['post_status'] = array(
                'confirmations' => array("Punctul de ridicare a fost schimbat!"),
            );

            ob_end_clean();
            header('Location: '.$_SERVER['REQUEST_URI']);
            die();
        }

        if (isset($_SESSION['post_status'])) {
            $this->confirmations = $_SESSION['post_status']['confirmations'];
            unset($_SESSION['post_status']);
        }

        $cargus = new CargusClass(Configuration::get('CARGUS_API_URL', $id_lang = NULL), Configuration::get('CARGUS_API_KEY', $id_lang = NULL));

        $fields = array(
            'UserName' => Configuration::get('CARGUS_USERNAME', $id_lang = NULL),
            'Password' => Configuration::get('CARGUS_PASSWORD', $id_lang = NULL)
        );

        $token = $cargus->CallMethod('LoginUser', $fields, 'POST');

        $pickupLocations = $cargus->CallMethod('PickupLocations', array(), 'GET', $token);

        if (is_null($pickupLocations)) {
            $this->errors[] = 'Nu exista niciun punct de ridicare asociat acestui cont!';
        } elseif(Configuration::get('CARGUS_USERNAME', $id_lang = NULL) == '' || Configuration::get('CARGUS_PASSWORD', $id_lang = NULL) == ''){
            $this->errors[] = 'Va rugam sa completati username-ul si parola in pagina de configurare a modulului!';
        } else {
            if (Configuration::get('CARGUS_PUNCT_RIDICARE', $id_lang = NULL) == '' && count($pickupLocations) > 0) {
                Configuration::updateValue('CARGUS_PUNCT_RIDICARE', $pickupLocations[0]['LocationId']);
            }

            $orders = $cargus->CallMethod('Orders?locationId='.Configuration::get('CARGUS_PUNCT_RIDICARE', $id_lang = NULL).'&status=1&pageNumber=1&itemsPerPage=100', [], 'GET', $token);

            if (is_null($orders)) {
                $this->errors[] = 'Nu exista nicio comanda pentru punctul curent de ridicare!';
            } else {
                foreach ($pickupLocations as $pickupLocation){
                    $pickups[$pickupLocation['LocationId']] = $pickupLocation['Name'];
                }

                $this->context->smarty->assign('pickups', $pickups);
                $this->context->smarty->assign('pickupsSelect', Configuration::get('CARGUS_PUNCT_RIDICARE', $id_lang = NULL));
                $this->context->smarty->assign('orders', $orders);
                $this->context->smarty->assign('token', Tools::getAdminTokenLite('CargusOrderHistory'));

                $dateFormat['date'] = '%I:%M %p';
                $dateFormat['time'] = '%H:%M:%S';
                $this->context->smarty->assign('dateFormat', $dateFormat);
            }

            $this->setTemplate('history.tpl');
        }
    }
}