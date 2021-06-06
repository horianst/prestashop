<?php

include_once (_PS_MODULE_DIR_.'/cargus/cargus.class.php');

class CargusPreferencesController extends ModuleAdminController
{
    /**
     * CargusPreferencesController constructor.
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

        if (Tools::isSubmit('submit')) {
            Configuration::updateValue('CARGUS_PUNCT_RIDICARE', Tools::getValue('CARGUS_PUNCT_RIDICARE'));
            Configuration::updateValue('CARGUS_ASIGURARE', Tools::getValue('CARGUS_ASIGURARE'));
            Configuration::updateValue('CARGUS_SAMBATA', Tools::getValue('CARGUS_SAMBATA'));
            Configuration::updateValue('CARGUS_DIMINEATA', Tools::getValue('CARGUS_DIMINEATA'));
            Configuration::updateValue('CARGUS_DESCHIDERE_COLET', Tools::getValue('CARGUS_DESCHIDERE_COLET'));
            Configuration::updateValue('CARGUS_TIP_RAMBURS', Tools::getValue('CARGUS_TIP_RAMBURS'));
            Configuration::updateValue('CARGUS_PLATITOR', Tools::getValue('CARGUS_PLATITOR'));
            Configuration::updateValue('CARGUS_TIP_EXPEDITIE', Tools::getValue('CARGUS_TIP_EXPEDITIE'));
            Configuration::updateValue('CARGUS_TRANSPORT_GRATUIT', Tools::getValue('CARGUS_TRANSPORT_GRATUIT'));
            Configuration::updateValue('CARGUS_COST_FIX', Tools::getValue('CARGUS_COST_FIX'));
            Configuration::updateValue('CARGUS_SERVICIU', Tools::getValue('CARGUS_SERVICIU'));

            $_SESSION['post_status'] = array(
                'confirmations' => array('Preferintele au fost salvate cu succes!'),
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

        // UC login user
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

            foreach ($pickupLocations as $pickupLocation){
                $pickups[$pickupLocation['LocationId']] = $pickupLocation['Name'];
            }

            $this->context->smarty->assign('pickups', $pickups);
            $this->context->smarty->assign('pickupsSelect', Configuration::get('CARGUS_PUNCT_RIDICARE', $id_lang = NULL));
            $this->context->smarty->assign('yesNo', [0 => 'Nu', 1 => 'Da']);
            $this->context->smarty->assign('yesNoAsiguare', Configuration::get('CARGUS_ASIGURARE', $id_lang = NULL));
            $this->context->smarty->assign('yesNoSambata', Configuration::get('CARGUS_SAMBATA', $id_lang = NULL));
            $this->context->smarty->assign('yesNoDimineata', Configuration::get('CARGUS_DIMINEATA', $id_lang = NULL));
            $this->context->smarty->assign('yesNoDeschidereColet', Configuration::get('CARGUS_DESCHIDERE_COLET', $id_lang = NULL));
            $this->context->smarty->assign('ramburs', ['cash' => 'Numerar', 'cont' => 'Cont colector']);
            $this->context->smarty->assign('rambursSelect', Configuration::get('CARGUS_TIP_RAMBURS', $id_lang = NULL));
            $this->context->smarty->assign('platitor', ['destinatar' => 'Destinatar', 'expeditor' => 'Expeditor']);
            $this->context->smarty->assign('platitorSelect', Configuration::get('CARGUS_PLATITOR', $id_lang = NULL));
            $this->context->smarty->assign('expeditie', ['colet' => 'Colet', 'plic' => 'Plic']);
            $this->context->smarty->assign('expeditieSelect', Configuration::get('CARGUS_TIP_EXPEDITIE', $id_lang = NULL));
            $this->context->smarty->assign('transport', Configuration::get('CARGUS_TRANSPORT_GRATUIT', $id_lang = NULL));
            $this->context->smarty->assign('cost', Configuration::get('CARGUS_COST_FIX', $id_lang = NULL));
            $this->context->smarty->assign('yesNoServiciu', Configuration::get('CARGUS_SERVICIU', $id_lang = NULL));

            $this->setTemplate('preferences.tpl');
        }
    }
}