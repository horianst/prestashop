<?php

include_once (_PS_MODULE_DIR_.'/cargus/cargus.class.php');

class CargusEditAwbController extends ModuleAdminController
{
    /**
     * AdminPreferencesController constructor.
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
            $sql = "UPDATE awb_urgent_cargus SET 
                                pickup_id = '".addslashes(Tools::getValue('pickup_id'))."',
                                name = '".addslashes(Tools::getValue('name'))."',
                                locality_name = '".addslashes(Tools::getValue('city'))."',
                                county_name = '".addslashes(Tools::getValue('id_state'))."',
                                address = '".addslashes(Tools::getValue('address'))."',
                                postal_code = '".addslashes(Tools::getValue('postal_code'))."',
                                contact = '".addslashes(Tools::getValue('contact'))."',
                                phone = '".addslashes(Tools::getValue('phone'))."',
                                email = '".addslashes(Tools::getValue('email'))."',
                                parcels = '".addslashes(Tools::getValue('parcels'))."',
                                envelopes = '".addslashes(Tools::getValue('envelopes'))."',
                                weight = '".addslashes(Tools::getValue('weight'))."',
                                length = '".addslashes(Tools::getValue('length'))."',
                                width = '".addslashes(Tools::getValue('width'))."',
                                height = '".addslashes(Tools::getValue('height'))."',
                                value = '".addslashes(Tools::getValue('value'))."',
                                cash_repayment = '".addslashes(Tools::getValue('cash_repayment'))."',
                                bank_repayment = '".addslashes(Tools::getValue('bank_repayment'))."',
                                other_repayment = '".addslashes(Tools::getValue('other_repayment'))."',
                                payer = '".addslashes(Tools::getValue('payer'))."',
                                morning_delivery = '".addslashes(Tools::getValue('morning_delivery'))."',
                                saturday_delivery = '".addslashes(Tools::getValue('saturday_delivery'))."',
                                openpackage = '".addslashes(Tools::getValue('openpackage'))."',
                                observations = '".addslashes(Tools::getValue('observations'))."',
                                contents = '".addslashes(Tools::getValue('contents'))."'
                            WHERE id = '".Tools::getValue('id')."'
                            ";

            $result = Db::getInstance()->execute($sql);

            if ($result == 1) {
                $_SESSION['post_status'] = array(
                    'confirmations' => array("Modificarile au fost salvate cu succes!"),
                );
            } else {
                $_SESSION['post_status'] = array(
                    'errors' => array("Eroare la inserarea datelor in baza de date!"),
                );
            }

            ob_end_clean();
            header('Location: '.$_SERVER['REQUEST_URI']);
            die();
        }

        if (isset($_SESSION['post_status'])) {
            if(isset($_SESSION['post_status']['confirmations'])){
                $this->confirmations = $_SESSION['post_status']['confirmations'];
            }

            if(isset($_SESSION['post_status']['errors'])){
                $this->errors = $_SESSION['post_status']['errors'];
            }

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
            $this->errors[] = 'Nu exista niciun punct de ridicare disponibil pentru acest utilizator!';
        } elseif (Configuration::get('CARGUS_USERNAME', $id_lang = NULL) == '' || Configuration::get('CARGUS_PASSWORD', $id_lang = NULL) == ''){
            $this->errors[] = 'Va rugam sa completati username-ul si parola in pagina de configurare a modulului!';
        } else {
            foreach ($pickupLocations as $pickupLocation){
                $pickups[$pickupLocation['LocationId']] = $pickupLocation['Name'];
            }

            $data = Db::getInstance()->ExecuteS("SELECT * FROM awb_urgent_cargus WHERE id = '".Tools::getValue('id')."'");
            $fullStates = State::getStatesByIdCountry(36);

            $states = [];

            foreach ($fullStates as $state){
                $states[$state['iso_code']] = $state['name'];
            }

            $this->context->smarty->assign('states', $states);
            $this->context->smarty->assign('statesSelect', $data[0]['county_name']);
            $this->context->smarty->assign('payers', [1 => 'Expeditor', 2 => 'Destinatar']);
            $this->context->smarty->assign('payerSelect', $data[0]['payer']);
            $this->context->smarty->assign('yesNo', [0 => 'Nu', 1 => 'Da']);
            $this->context->smarty->assign('yesNoSambata', $data[0]['saturday_delivery']);
            $this->context->smarty->assign('yesNoDimineata', $data[0]['morning_delivery']);
            $this->context->smarty->assign('yesNoDeschidereColet', $data[0]['openpackage']);
            $this->context->smarty->assign('data', $data[0]);
            $this->context->smarty->assign('pickups', $pickups);
            $this->context->smarty->assign('pickupsSelect', $data[0]['pickup_id']);

            $this->setTemplate('edit_awb.tpl');
        }
    }
}