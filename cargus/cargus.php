<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Entity\Repository\TabRepository;

class Cargus extends Module
{
    function __construct()
    {
        $this->name = 'cargus';
        $this->tab = 'shipping_logistics';
        $this->version = '4.0';
        $this->author = 'Cargus';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Cargus');
        $this->description = $this->l('Curier Rapid');

        $this->confirmUninstall = $this->l('Sunteti sigur ca doriti sa dezinstalati modulul Cargus?');

        if (!Configuration::get('CARGUS_API_URL')) {
            $this->warning = $this->l('API URL nu a fost configurat!');
        }

        if (!Configuration::get('CARGUS_API_KEY')) {
            $this->warning = $this->l('API KEY nu a fost configurata!');
        }

        if (!Configuration::get('CARGUS_USERNAME')) {
            $this->warning = $this->l('API USERNAME nu a fost configurata!');
        }

        if (!Configuration::get('CARGUS_PASSWORD')) {
            $this->warning = $this->l('API PASSWORD nu a fost configurata!');
        }
    }

    function install()
    {
        Db::getInstance()->execute("DROP TABLE IF EXISTS `awb_urgent_cargus`");
        Db::getInstance()->execute(
            '
            CREATE TABLE IF NOT EXISTS `awb_urgent_cargus` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL,
            `pickup_id` int(11) NOT NULL,
            `name` varchar(64) NOT NULL,
            `locality_id` int(11) NOT NULL,
            `locality_name` varchar(128) NOT NULL,
            `county_id` int(11) NOT NULL,
            `county_name` varchar(128) NOT NULL,
            `street_id` int(11) NOT NULL,
            `street_name` varchar(128) NOT NULL,
            `number` varchar(32) NOT NULL,
            `address` varchar(256) NOT NULL,
            `contact` varchar(64) NOT NULL,
            `phone` varchar(32) NOT NULL,
            `email` varchar(96) NOT NULL,
            `parcels` int(11) NOT NULL,
            `envelopes` int(11) NOT NULL,
            `weight` int(11) NOT NULL,
            `value` double NOT NULL,
            `cash_repayment` double NOT NULL,
            `bank_repayment` double NOT NULL,
            `other_repayment` varchar(256) NOT NULL,
            `payer` tinyint(1) NOT NULL,
            `morning_delivery` tinyint(1) NOT NULL,
            `saturday_delivery` tinyint(1) NOT NULL,
	        `openpackage` tinyint(1) NOT NULL,
            `observations` varchar(256) NOT NULL,
            `contents` varchar(256) NOT NULL,
            `barcode` varchar(50) NOT NULL,
            `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
            '
        );

        if (!parent::install() ||
            !$this->registerHook('header') ||
            !$this->registerHook('rightColumn') ||
            !$this->registerHook('leftColumn') ||
            !$this->registerHook('backOfficeHeader') ||
            !$this->installTabs()
        ) {
            return false;
        }

       $this->addCarrier();

        return true;
    }

    public function installTabs()
    {
        $mainTab = new Tab();

        foreach (Language::getLanguages() as $language) {
            $mainTab->name[$language['id_lang']] = $this->l('Cargus');
        }

        $mainTab->class_name = 'CARGUS';
        $mainTab->id_parent = 0;
        $mainTab->add();

        $tab = new Tab();
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = $this->l('Comanda curenta');
        }

        $tab->class_name = 'CargusOrders';
        $tab->module = $this->name;
        $tab->id_parent = $mainTab->id;
        $tab->icon = 'shopping_basket';
        $tab->add();

        $tab = new Tab();
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = $this->l('Istoric livrari');
        }

        $tab->class_name = 'CargusHistory';
        $tab->module = $this->name;
        $tab->id_parent = $mainTab->id;
        $tab->icon = 'list';
        $tab->add();

        $tab = new Tab();
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = $this->l('Preferinte');
        }

        $tab->class_name = 'CargusPreferences';
        $tab->module = $this->name;
        $tab->id_parent = $mainTab->id;
        $tab->icon = 'settings';
        $tab->add();

        $tab = new Tab();
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = $this->l('Istoric comanda');
        }

        $tab->class_name = 'CargusOrderHistory';
        $tab->module = $this->name;
        $tab->id_parent = $mainTab->id;
        $tab->active = false;
        $tab->add();

        $tab = new Tab();
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = $this->l('Istoric awb');
        }

        $tab->class_name = 'CargusAwbHistory';
        $tab->module = $this->name;
        $tab->id_parent = $mainTab->id;
        $tab->active = false;
        $tab->add();

        $tab = new Tab();
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = $this->l('Editeaza awb');
        }

        $tab->class_name = 'CargusEditAwb';
        $tab->module = $this->name;
        $tab->id_parent = $mainTab->id;
        $tab->active = false;
        $tab->add();

        $tab = new Tab();
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = $this->l('Admin');
        }

        $tab->class_name = 'CargusAdmin';
        $tab->module = $this->name;
        $tab->id_parent = $mainTab->id;
        $tab->active = false;
        $tab->add();

        return true;
    }

    public function addCarrier()
    {
        $carrier = new Carrier();
        $carrier->name = 'Cargus';
        $carrier->id_tax_rules_group = 0;
        $carrier->id_zone = 1;
        $carrier->active = true;
        $carrier->deleted = 0;
        $carrier->shipping_handling = false;
        $carrier->range_behavior = 0;
        $carrier->is_module = true;
        $carrier->shipping_external = true;
        $carrier->external_module_name = 'cargus';
        $carrier->need_range = true;

        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            if ($language['iso_code'] == 'fr') {
                $carrier->delay[(int)$language['id_lang']] = '24 heures';
            }
            if ($language['iso_code'] == 'ro') {
                $carrier->delay[(int)$language['id_lang']] = '24 ore';
            }
            if ($language['iso_code'] == 'en') {
                $carrier->delay[(int)$language['id_lang']] = '24 hours';
            }
            if ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'))) {
                $carrier->delay[(int)$language['id_lang']] = '24 hours';
            }
        }

        if ($carrier->add()) {

            // Save Carrier ID
            Configuration::updateValue('CARGUS_CARRIER_ID', (int)$carrier->id);

            $groups = Group::getGroups(true);
            foreach ($groups as $group) {
                Db::getInstance()->insert(
                    _DB_PREFIX_ . 'carrier_group',
                    array(
                        'id_carrier' => (int)($carrier->id),
                        'id_group' => (int)($group['id_group'])
                    )
                );
            }

            $rangePrice = new RangePrice();
            $rangePrice->id_carrier = $carrier->id;
            $rangePrice->delimiter1 = '0';
            $rangePrice->delimiter2 = '10000';
            $rangePrice->add();

            $rangeWeight = new RangeWeight();
            $rangeWeight->id_carrier = $carrier->id;
            $rangeWeight->delimiter1 = '0';
            $rangeWeight->delimiter2 = '10000';
            $rangeWeight->add();

            $zones = Zone::getZones(true);

            foreach ($zones as $zone) {
                Db::getInstance()->insert(
                    _DB_PREFIX_ . 'carrier_zone',
                    array(
                        'id_carrier' => (int)($carrier->id),
                        'id_zone' => (int)($zone['id_zone'])
                    )
                );
                Db::getInstance()->insert(
                    _DB_PREFIX_ . 'delivery',
                    array(
                        'id_carrier' => (int)($carrier->id),
                        'id_range_price' => (int)($rangePrice->id),
                        'id_range_weight' => null,
                        'id_zone' => (int)($zone['id_zone']),
                        'price' => '0'
                    )
                );
                Db::getInstance()->insert(
                    _DB_PREFIX_ . 'delivery',
                    array(
                        'id_carrier' => (int)($carrier->id),
                        'id_range_price' => null,
                        'id_range_weight' => (int)($rangeWeight->id),
                        'id_zone' => (int)($zone['id_zone']),
                        'price' => '0'
                    )
                );
            }

            // Copy Logo
            if (!copy(dirname(__FILE__) . '/carrier.png', _PS_SHIP_IMG_DIR_ . '/' . (int)$carrier->id . '.jpg')) {
                return false;
            }

            // Return ID Carrier
            return (int)($carrier->id);
        }

        return false;
    }

    public function uninstall()
    {
        Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "configuration WHERE `name` LIKE '%CARGUS%'");
        Db::getInstance()->execute("DROP TABLE IF EXISTS awb_urgent_cargus");

        if (!parent::uninstall() ||
            !$this->unregisterHook('header') ||
            !$this->unregisterHook('rightColumn') ||
            !$this->unregisterHook('leftColumn') ||
            !$this->unregisterHook('backOfficeHeader') ||
            !$this->uninstallTabs()

        ) {
            return false;
        }

        $this->removeCarrier();

        return true;
    }

    public function removeCarrier()
    {
        $carrier = new Carrier(Configuration::get('CARGUS_CARRIER_ID', $id_lang = null));
        $carrier->delete();
    }

    public function uninstallTabs()
    {

        $id_tab = (int) Tab::getIdFromClassName('CargusAwbHistory');
        $tab = new Tab($id_tab);
        $tab->delete();
        $id_tab = (int) Tab::getIdFromClassName('CargusOrderHistory');
        $tab = new Tab($id_tab);
        $tab->delete();
        $id_tab = (int) Tab::getIdFromClassName('CargusPreferences');
        $tab = new Tab($id_tab);
        $tab->delete();
        $id_tab = (int) Tab::getIdFromClassName('CargusHistory');
        $tab = new Tab($id_tab);
        $tab->delete();
        $id_tab = (int) Tab::getIdFromClassName('CargusOrders');
        $tab = new Tab($id_tab);
        $tab->delete();
        $id_tab = (int) Tab::getIdFromClassName('CargusEditAwb');
        $tab = new Tab($id_tab);
        $tab->delete();
        $id_tab = (int) Tab::getIdFromClassName('CargusAdmin');
        $tab = new Tab($id_tab);
        $tab->delete();
        $id_tab = (int) Tab::getIdFromClassName('CARGUS');
        $tab = new Tab($id_tab);
        $tab->delete();

        return true;
    }

    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit' . $this->name)) {
            $url = strval(Tools::getValue('CARGUS_API_URL'));
            $key = strval(Tools::getValue('CARGUS_API_KEY'));
            $user = strval(Tools::getValue('CARGUS_USERNAME'));
            $pass = strval(Tools::getValue('CARGUS_PASSWORD'));

            $valid = true;

            if (!$url || empty($url) || !Validate::isGenericName($url)) {
                $output .= $this->displayError($this->l('API Url invalid!'));
                $valid = false;
            }

            if (!$key || empty($key) || !Validate::isGenericName($key)) {
                $output .= $this->displayError($this->l('Subscription Key invalid!'));
                $valid = false;
            }
            if (!$user || empty($user) || !Validate::isGenericName($user)) {
                $output .= $this->displayError($this->l('Username invalid!'));
                $valid = false;
            }

            if (!$pass || empty($pass) || !Validate::isGenericName($pass)) {
                $output .= $this->displayError($this->l('Password invalid!'));
                $valid = false;
            }

            if ($valid) {
                Configuration::updateValue('CARGUS_API_URL', $url);
                Configuration::updateValue('CARGUS_API_KEY', $key);
                Configuration::updateValue('CARGUS_USERNAME', $user);
                Configuration::updateValue('CARGUS_PASSWORD', $pass);
                $output .= $this->displayConfirmation($this->l('Configurare salvata!'));
            }
        }

        return $output . $this->displayForm();
    }

    public function displayForm()
    {
        // Get default language
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Configurare'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('API Url'),
                    'name' => 'CARGUS_API_URL',
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Subscription Key'),
                    'name' => 'CARGUS_API_KEY',
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Username'),
                    'name' => 'CARGUS_USERNAME',
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'password',
                    'label' => $this->l('Password'),
                    'name' => 'CARGUS_PASSWORD',
                    'size' => 20,
                    'required' => true
                ]
            ],
            'submit' => [
                'title' => $this->l('Salveaza'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Language
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                    '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            ]
        ];

        // Load current value
        $helper->fields_value['CARGUS_API_URL'] = Tools::getValue(
            'CARGUS_API_URL',
            Configuration::get('CARGUS_API_URL')
        );
        $helper->fields_value['CARGUS_API_KEY'] = Tools::getValue(
            'CARGUS_API_KEY',
            Configuration::get('CARGUS_API_KEY')
        );
        $helper->fields_value['CARGUS_USERNAME'] = Tools::getValue(
            'CARGUS_USERNAME',
            Configuration::get('CARGUS_USERNAME')
        );
        $helper->fields_value['CARGUS_PASSWORD'] = Tools::getValue(
            'CARGUS_PASSWORD',
            Configuration::get('CARGUS_PASSWORD')
        );

        return $helper->generateForm($fieldsForm);
    }

    function hookHeader($params)
    {
        return $this->display(__FILE__, 'views/templates/hook/frontend_header.tpl');
    }

    function hookRightColumn($params)
    {
        return $this->display(__FILE__, 'views/templates/hook/awb_tracking.tpl');
    }

    function hookLeftColumn($params)
    {
        return $this->display(__FILE__, 'views/templates/hook/awb_tracking.tpl');
    }

    function hookBackOfficeHeader($params)
    {
        if (strtolower($_GET['controller']) == 'adminorders' && !isset($_GET['id_order'])) {
            $this->context->smarty->assign('CargusAdminToken', Tools::getAdminTokenLite('CargusAdmin'));
            return $this->display(__FILE__, 'views/templates/hook/admin_mods.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/hook/cargus_autocomplete.tpl');
        }
    }

    public function getOrderShippingCost($params, $shipping_cost)
    {
        return $this->calculeazaTransport($params);
    }

    public function getOrderShippingCostExternal($params)
    {
        return $this->calculeazaTransport($params);
    }

    public function calculeazaTransport($cart)
    {
        try {
            // daca este ales un cost fix pentru expeditie, nu mai calculeaza transportul si returneaza costul fix
            $cost_fix_expeditie = Configuration::get('CARGUS_COST_FIX', $id_lang = null);
            if ($cost_fix_expeditie != '') {
                return round($cost_fix_expeditie, 2);
            }

            // determin greutatea
            $total_weight = ceil($cart->getTotalWeight());
            if ($total_weight == 0) {
                $total_weight = 1;
            }

            // obtin id-ul monezii folosite in cos
            $id_currency = (int)$cart->id_currency;

            // obiectele pentru cursul de schimb
            $key_ron = 0;
            $currency = Currency::getCurrencies();
            foreach ($currency as $cc) {
                if (strtolower($cc['iso_code']) == 'ron') {
                    $key_ron = $cc['id_currency'];
                }
            }
            if ($key_ron == 0) {
                die('Trebuie adaugata moneda RON');
            }
            $currency_RON = new Currency($key_ron);
            $currency_DEFAULT = new Currency($id_currency);

            // obtin totalul cosului in lei
            $orderTotal = Tools::convertPriceFull(
                $cart->getOrderTotal(true, Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING),
                $currency_DEFAULT,
                $currency_RON
            );

            // daca totalul cosului depaseste plafonul, transportul este gratuit
            $plafon_plata_dest = Configuration::get('CARGUS_TRANSPORT_GRATUIT', $id_lang = null);
            if (round($orderTotal, 2) > $plafon_plata_dest && $plafon_plata_dest > 0) {
                return 0;
            }

            // obtin valoarea declarata a expeditiei
            if (Configuration::get('CARGUS_ASIGURARE', $id_lang = null) == '1') {
                $valoare_declarata = round($orderTotal, 2);
            } else {
                $valoare_declarata = 0;
            }

            // stabileste suma ramburs
            $suma_ramburs = round($orderTotal, 2);

            // daca se plateste cu altceva decat ramburs, suma rambursata devine 0
            $url_path = $_SERVER['REQUEST_URI'];
            if (
                !strstr($url_path, 'cashondelivery')
                && !strstr($url_path, 'cod')
                && !strstr($url_path, 'ramburs')
                && !strstr($url_path, 'cash')
                && !strstr($url_path, 'numerar')

                && strstr($url_path, 'module')
            ) {
                $suma_ramburs = 0;
            }

            // detarmin valorile pentru ramburs
            if (Configuration::get('CARGUS_TIP_RAMBURS', $id_lang = null) == 'cont') {
                $ramburs_cont_colector = $suma_ramburs;
                $ramburs_cash = 0;
            } else {
                $ramburs_cont_colector = 0;
                $ramburs_cash = $suma_ramburs;
            }

            $id_address_delivery = (int)$cart->id_address_delivery;

            // obtine adresa de livrare
            if ($id_address_delivery != 0) {
                $delivery_address = new Address($id_address_delivery);
                if (!isset($delivery_address->city) || strlen($delivery_address->city) < 3) {
                    return null;
                }
            } else {
                return null;
            }

            // obtin indicativul judetului destinatarului
            $states = State::getStatesByIdCountry($delivery_address->id_country);
            $state_ISO = null;
            foreach ($states as $s) {
                if ($s['id_state'] == $delivery_address->id_state) {
                    $state_ISO = $s['iso_code'];
                }
            }
            if (is_null($state_ISO)) {
                die('Nu am putut obtine indicativul judetului destinatarului');
            }

            // include si instantiaza clasa urgent
            require_once(_PS_MODULE_DIR_ . '/cargus/cargus.class.php');
            $cargus = new CargusClass(
                Configuration::get('CARGUS_API_URL', $id_lang = null),
                Configuration::get('CARGUS_API_KEY', $id_lang = null)
            );

            // UC login user
            $fields = array(
                'UserName' => Configuration::get('CARGUS_USERNAME', $id_lang = null),
                'Password' => Configuration::get('CARGUS_PASSWORD', $id_lang = null)
            );
            $token = $cargus->CallMethod('LoginUser', $fields, 'POST');

            // UC punctul de ridicare default
            $location = array();
            $pickups = $cargus->CallMethod('PickupLocations', array(), 'GET', $token);
            if (is_null($pickups)) {
                die('Nu exista niciun punct de ridicare asociat acestui cont!');
            }
            foreach ($pickups as $pick) {
                if (Configuration::get('CARGUS_PUNCT_RIDICARE', $id_lang = null) == $pick['LocationId']) {
                    $location = $pick;
                }
            }

            // UC shipping calculation
            $fields = array(
                'FromLocalityId' => $location['LocalityId'],
                'ToLocalityId' => 0,
                'FromCountyName' => '',
                'FromLocalityName' => '',
                'ToCountyName' => $state_ISO,
                'ToLocalityName' => $delivery_address->city,
                'Parcels' => Configuration::get('CARGUS_TIP_EXPEDITIE', $id_lang = null) != 'plic' ? 1 : 0,
                'Envelopes' => Configuration::get('CARGUS_TIP_EXPEDITIE', $id_lang = null) == 'plic' ? 1 : 0,
                'TotalWeight' => $total_weight,
                'DeclaredValue' => $valoare_declarata,
                'CashRepayment' => $ramburs_cash,
                'BankRepayment' => $ramburs_cont_colector,
                'OtherRepayment' => '',
                'PaymentInstrumentId' => 0,
                'PaymentInstrumentValue' => 0,
                'OpenPackage' => Configuration::get('CARGUS_DESCHIDERE_COLET', $id_lang = null) != 1 ? false : true,
                'SaturdayDelivery' => Configuration::get('CARGUS_SAMBATA', $id_lang = null) != 1 ? false : true,
                'MorningDelivery' => Configuration::get('CARGUS_DIMINEATA', $id_lang = null) != 1 ? false : true,
                'ShipmentPayer' => Configuration::get('CARGUS_PLATITOR', $id_lang = null) != 'expeditor' ? 2 : 1,
                'ServiceId' => Configuration::get('CARGUS_PLATITOR', $id_lang = null) != 'expeditor' ? 4 : 1,
                //'PriceTableId' => Configuration::get('_URGENT_PLAN_TARIFAR_', $id_lang = NULL)
                'PriceTableId' => null
            );
            $result = $cargus->CallMethod('ShippingCalculation', $fields, 'POST', $token);

            if (!isset($result['Subtotal'])) {
                return null;
            }

            if (!is_null($result)) {
                return Tools::convertPriceFull($result['Subtotal'], $currency_RON, $currency_DEFAULT);
            } else {
                echo '<pre>';
                print_r($fields);
                die();
            }
        } catch (Exception $ex) {
            print_r($ex);
            die();
        }
    }
}