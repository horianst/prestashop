<?php

include_once(_PS_MODULE_DIR_ . '/cargus/cargus.class.php');

class CargusCityModuleFrontController extends ModuleFrontController
{
    /**
     * CargusAdminController constructor.
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

        if (isset($_GET['judet'])) {
            $cargus = new CargusClass(
                Configuration::get('CARGUS_API_URL', $id_lang = null),
                Configuration::get('CARGUS_API_KEY', $id_lang = null)
            );

            $fields = array(
                'UserName' => Configuration::get('CARGUS_USERNAME', $id_lang = null),
                'Password' => Configuration::get('CARGUS_PASSWORD', $id_lang = null)
            );

            $token = $cargus->CallMethod('LoginUser', $fields, 'POST');

            if (is_numeric(addslashes($_GET['judet']))) {
                $states = State::getStatesByIdCountry(36);
                $state_ISO = null;
                foreach ($states as $s) {
                    if ($s['id_state'] == addslashes($_GET['judet'])) {
                        $state_ISO = trim(strtolower($s['iso_code']));
                    }
                }
            } else {
                $state_ISO = addslashes(trim(strtolower($_GET['judet'])));
            }

            if ($state_ISO == 'b') {
                echo '<option value="Bucuresti" km="0">Bucuresti</option>';
            } else {
                $countyId = null;
                $counties = $cargus->CallMethod('Counties?countryId=1', array(), 'GET', $token);
                foreach ($counties as $c) {
                    if (trim(strtolower($c['Abbreviation'])) == $state_ISO) {
                        $countyId = trim($c['CountyId']);
                    }
                }

                if (is_null($countyId)) {
                    echo '<option value="" km="0">ERROR: Nu am putut obtine indicativul judetului</option>';
                    die();
                } else {
                    $localities = $cargus->CallMethod(
                        'Localities?countryId=1&countyId=' . $countyId,
                        array(),
                        'GET',
                        $token
                    );

                    if (count($localities) > 1) {
                        echo '<option value="" km="0">-</option>' . "\n";
                    }

                    $val = '';
                    if (isset($_GET['val'])) {
                        $val = trim(strtolower(addslashes($_GET['val'])));
                    }

                    foreach ($localities as $l) {
                        echo '<option' . ($val == trim(
                                strtolower($l['Name'])
                            ) ? ' selected="selected"' : '') . ' km="' . ($l['InNetwork'] ? 0 : ($l['ExtraKm'] ? $l['ExtraKm'] : 0)) . '">' . $l['Name'] . '</option>' . "\n";
                    }
                }
            }
        }

        die();
    }
}