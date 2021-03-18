<?php

include_once (_PS_MODULE_DIR_.'/cargus/cargus.class.php');

class CargusOrdersController extends ModuleAdminController
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

        if (Tools::isSubmit('submitPickup')) {
            Configuration::updateValue('CARGUS_PUNCT_RIDICARE', Tools::getValue('CARGUS_PUNCT_RIDICARE'));

            $_SESSION['post_status'] = array(
                'confirmations' => array('Punctul de ridicare a fost schimbat!'),
            );

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

            if(isset($_SESSION['post_status']['warnings'])){
                $this->errors = $_SESSION['post_status']['warnings'];
            }

            unset($_SESSION['post_status']);
        }

        // VALIDEAZA AWB-urile AFLATE IN ASTEPTARE
        if (Tools::isSubmit('submit_valideaza')) {
            $errors = [];
            $success = [];
            foreach ($_POST['selected'] as $id) {
                $cargus = new CargusClass(Configuration::get('CARGUS_API_URL', $id_lang = NULL), Configuration::get('CARGUS_API_KEY', $id_lang = NULL));

                $fields = array(
                    'UserName' => Configuration::get('CARGUS_USERNAME', $id_lang = NULL),
                    'Password' => Configuration::get('CARGUS_PASSWORD', $id_lang = NULL)
                );

                $token = $cargus->CallMethod('LoginUser', $fields, 'POST');

                // UC create awb
                $row = Db::getInstance()->ExecuteS("SELECT * FROM awb_urgent_cargus WHERE barcode = '0' AND id = '".addslashes($id)."'");

                $go = true;

                if(!$row[0]['height'] || !$row[0]['length'] || !$row[0]['width']){
                    $errors[] = 'Va rugam sa introduceti dimensiunile coletelor!';
                    $go = false;
                }

                if(!$row[0]['postal_code']){
                    $errors[] = 'Va rugam sa introduceti codul postal al destinatarului!';
                    $go = false;
                }

                if($go) {
                    $fields = array(
                        'Sender' => array(
                            'LocationId' => $row[0]['pickup_id']
                        ),
                        'Recipient' => array(
                            'LocationId' => null,
                            'Name' => $row[0]['name'],
                            'CountyId' => null,
                            'CountyName' => $row[0]['county_name'],
                            'LocalityId' => null,
                            'LocalityName' => $row[0]['locality_name'],
                            'StreetId' => null,
                            'StreetName' => '-',
                            'AddressText' => $row[0]['address'],
                            'ContactPerson' => $row[0]['contact'],
                            'PhoneNumber' => $row[0]['phone'],
                            'Email' => $row[0]['email'],
                            'CodPostal' => $row[0]['postal_code'],
                        ),
                        'Parcels' => $row[0]['parcels'],
                        'Envelopes' => $row[0]['envelopes'],
                        'TotalWeight' => $row[0]['weight'],
                        'DeclaredValue' => $row[0]['value'],
                        'CashRepayment' => $row[0]['cash_repayment'],
                        'BankRepayment' => $row[0]['bank_repayment'],
                        'OtherRepayment' => $row[0]['other_repayment'],
                        'OpenPackage' => $row[0]['openpackage'] == 1 ? true : false,
                        'ShipmentPayer' => $row[0]['payer'],
                        'MorningDelivery' => $row[0]['morning_delivery'] == 1 ? true : false,
                        'SaturdayDelivery' => $row[0]['saturday_delivery'] == 1 ? true : false,
                        'Observations' => $row[0]['observations'],
                        'PackageContent' => $row[0]['contents'],
                        'CustomString' => $row[0]['order_id'],
                        'ParcelCodes' => [
                            [
                                'Code'=> 0,
                                'Type'=>   $row[0]['parcels'] > 0 ? 1 : 0,
                                'Weight' => $row[0]['weight'],
                                'Length' => $row[0]['length'],
                                'Width' => $row[0]['width'],
                                'Height' => $row[0]['height'],
                                'ParcelContent' => $row[0]['contents']
                            ]
                        ]
                    );

                    $barcode = $cargus->CallMethod('Awbs', $fields, 'POST', $token, true);

                    if (is_null($barcode)) {
                        $errors[] = 'AWB-ul ' . addslashes($id) . ' nu a putut fi validat';
                        $errorIds = addslashes($id);
                    } else {
                        $update = Db::getInstance()->execute("UPDATE awb_urgent_cargus SET barcode = '".$barcode."' WHERE id = '".addslashes($id)."'");
                        if ($update == 1) {
                            $success[] = addslashes($id);
                        } else {
                            $errors[] = 'AWB-ul ' . addslashes($id) . ' nu a putut fi actualizat in baza de date';
                            $errorIds = addslashes($id);
                        }
                    }
                }
            }

            if (count($errors) == 0) {
                $_SESSION['post_status'] = array(
                    'confirmations' => array('Toate AWB-urile selectate au fost validate cu succes!'),
                );
            } else if (count($success) == 0) {
                $_SESSION['post_status'] = array(
                    'errors' => $errors,
                );
            } else {
                $_SESSION['post_status'] = array(
                    'errors' => array('Nu a fost posibila validarea urmatoarelor comenzi: '.implode(', ', $errorIds)),
                    'warnings' => array('ATENTIE: Doar o parte din AWB-urile selectate au fost validate cu succes!'),
                );
            }

            ob_end_clean();
            header('Location: '.$_SERVER['REQUEST_URI']);
            die();
        }

        // STERGE AWB-urile AFLATE IN ASTEPTARE
        if (Tools::isSubmit('submit_sterge')) {
            $errors = [];
            $success = [];
            foreach ($_POST['selected'] as $id) {
                $delete = Db::getInstance()->execute("DELETE FROM awb_urgent_cargus WHERE id = '".addslashes($id)."'");
                if ($delete == 1) {
                    $success[] = addslashes($id);
                } else {
                    $errors[] = addslashes($id);
                }
            }

            if (count($errors) == 0) {
                $_SESSION['post_status'] = array(
                    'confirmations' => array('Toate AWB-urile selectate au fost sterse cu succes!'),
                );
            } else if (count($success) == 0) {
                $_SESSION['post_status'] = array(
                    'errors' => array('Niciun AWB selectat nu a putut fi sters!'),
                );
            } else {
                $_SESSION['post_status'] = array(
                    'errors' => array('Nu a fost posibila stergerea urmatoarelor comenzi: '.implode(', ', $errors)),
                    'warnings' => array('ATENTIE: Doar o parte din AWB-urile selectate au fost sterse cu succes!'),
                );
            }

            ob_end_clean();
            header('Location: '.$_SERVER['REQUEST_URI']);
            die();
        }

        // DEZACTIVEAZA AWB-urile DEJA VALIDATE
        if (Tools::isSubmit('submit_dezactiveaza')) {
            $errors = array();
            $success = array();
            foreach ($_POST['awbs'] as $barcode) {
                $cargus = new CargusClass(Configuration::get('CARGUS_API_URL', $id_lang = NULL), Configuration::get('CARGUS_API_KEY', $id_lang = NULL));

                $fields = array(
                    'UserName' => Configuration::get('CARGUS_USERNAME', $id_lang = NULL),
                    'Password' => Configuration::get('CARGUS_PASSWORD', $id_lang = NULL)
                );

                $token = $cargus->CallMethod('LoginUser', $fields, 'POST');

                // UC delete awb
                $result = $cargus->CallMethod('Awbs?barCode=' . addslashes($barcode), [], 'DELETE', $token);
                if ($result == 1) {
                    $update = Db::getInstance()->execute("UPDATE awb_urgent_cargus SET barcode = '0' WHERE barcode = '".addslashes($barcode)."'");
                    if ($update == 1) {
                        $success[] = addslashes($barcode);
                    } else {
                        $errors[] = addslashes($barcode);
                    }
                } else {
                    $errors[] = addslashes($barcode);
                }
            }

            if (count($errors) == 0) {
                $_SESSION['post_status'] = array(
                    'confirmations' => array('Toate AWB-urile selectate au fost dezactivate cu succes!'),
                );
            } else if (count($success) == 0) {
                $_SESSION['post_status'] = array(
                    'errors' => array('Niciun AWB selectat nu a putut fi dezactivat!'),
                );
            } else {
                $_SESSION['post_status'] = array(
                    'errors' => array('Nu a fost posibila dezactivarea urmatoarelor AWB-uri: '.implode(', ', $errors)),
                    'warnings' => array('ATENTIE: Doar o parte din AWB-urile selectate au fost dezactivate cu succes!'),
                );
            }

            ob_end_clean();
            header('Location: '.$_SERVER['REQUEST_URI']);
            die();
        }

        $pickups = null;
        $awbs = null;
        $lines = null;

        if (Configuration::get('CARGUS_USERNAME', $id_lang = NULL) == '' || Configuration::get('CARGUS_PASSWORD', $id_lang = NULL) == ''){
            $this->errors[] = 'Va rugam sa completati username-ul si parola in pagina de configurare a modulului!';
        } else {
            $cargus = new CargusClass(Configuration::get('CARGUS_API_URL', $id_lang = NULL), Configuration::get('CARGUS_API_KEY', $id_lang = NULL));

            $fields = array(
                'UserName' => Configuration::get('CARGUS_USERNAME', $id_lang = NULL),
                'Password' => Configuration::get('CARGUS_PASSWORD', $id_lang = NULL)
            );

            $token = $cargus->CallMethod('LoginUser', $fields, 'POST');

            $pickupLocations = $cargus->CallMethod('PickupLocations', array(), 'GET', $token);

            if (is_null($pickupLocations)) {
                $this->errors[] = 'Nu exista niciun punct de ridicare asociat acestui cont!';
            } else {
                foreach ($pickupLocations as $pickupLocation){
                    $pickups[$pickupLocation['LocationId']] = $pickupLocation['Name'];
                }

                if (Configuration::get('CARGUS_PUNCT_RIDICARE', $id_lang = NULL) == '' && count($pickupLocations) > 0) {
                    Configuration::updateValue('CARGUS_PUNCT_RIDICARE', $pickupLocations[0]['LocationId']);
                }

                $orders = $cargus->CallMethod('Orders?locationId='.Configuration::get('CARGUS_PUNCT_RIDICARE', $id_lang = NULL).'&status=0&pageNumber=1&itemsPerPage=1000', [], 'GET', $token);

                $awbs = [];
                if (!is_null($orders)) {
                    $allAwbs = $cargus->CallMethod('Awbs?&orderId='.(isset($orders['OrderId']) == 1 ? $orders['OrderId'] : $orders[0]['OrderId']), [], 'GET', $token);
                    if (!is_null($allAwbs)) {
                        foreach ($allAwbs as $rawAwb) {
                            if ($rawAwb['Status'] != 'Deleted') {
                                $awbs[] = $rawAwb;
                            }
                        }
                    }
                }

                if (count($awbs) == 0){
                    $this->warnings[] = 'Nu exista niciun AWB validat pentru punctul curent de ridicare!';
                }

                $lines = Db::getInstance()->ExecuteS("SELECT * FROM awb_urgent_cargus WHERE barcode = '0'");
                if (count($lines) == 0) {
                    $this->warnings[] = 'Nu exista niciun AWB in asteptare!';
                }
            }
        }

        $this->context->smarty->assign('pickups', $pickups);
        $this->context->smarty->assign('awbs', $awbs);
        $this->context->smarty->assign('lines', $lines);

        $this->context->smarty->assign('printTypes', ['A4', '10x14']);
        $this->context->smarty->assign('printType', Tools::getValue('PRINT_TYPE') ? Tools::getValue('PRINT_TYPE') : 0);

        $this->context->smarty->assign('token', Tools::getAdminTokenLite('CargusEditAwb'));
        $this->context->smarty->assign('tokenAdmin', Tools::getAdminTokenLite('CargusAdmin'));
        $this->context->smarty->assign('cookie', _COOKIE_KEY_);
        $this->context->smarty->assign('pickupsSelect', Configuration::get('CARGUS_PUNCT_RIDICARE', $id_lang = NULL));

        $this->setTemplate('orders.tpl');
    }
}