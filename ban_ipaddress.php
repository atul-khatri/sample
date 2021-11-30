<?php
/**
* 2007-2019 PrestaShop.
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ban_ipaddress extends Module
{
    protected $config_form = false;
    protected $tableName;

    public function __construct()
    {
        $this->name = 'ban_ipaddress';
        $this->tab = 'administration';
        $this->version = '0.0.1';
        $this->author = 'Sphinx';
        $this->need_instance = 0;

        /*
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Ban Ip Address');
        $this->description = $this->l('ban IP address manually');
        $this->tableName = 'ban_ipaddress';
        $this->confirmUninstall = $this->l('Are you sure want to uninstall');
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update.
     */
    public function install()
    {
        Configuration::updateValue('BAN_IPADDRESS_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('home') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayHome');
    }

    public function uninstall()
    {
        Configuration::deleteByName('BAN_IPADDRESS_REDIRECTURL');
        Configuration::deleteByName('BAN_IPADDRESS_MESSAGE');
        return parent::uninstall();
    }

    /**
     * Load the configuration form.
     */
    public function getContent()
    {
        $output = '';
        /*
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitBan_ipaddressModule')) == true) {
            $this->postProcess();
        }
        if (Tools::isSubmit('submit_unblock')=='Unblock') {
            $idBanIpAddress = Tools::getValue('id_ban_ipaddress');
            $this->unblockIpAddress($idBanIpAddress);
        }
        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign('blockedIpAddress', $this->getBlockedIpAddress());
        $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        $output .= $this->renderForm();
        $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/displaybannedIp.tpl');
        return $output;
    }

    /**
     * unblock given ip address
     *
     * @param  mixed $idBanIpAddress
     *
     * @return void
     */

    protected function unblockIpAddress($idBanIpAddress)
    {
        $idBanIpAddress = (int) $idBanIpAddress;
        if (Db::getInstance()->Execute(
            'delete from `'._DB_PREFIX_.$this->tableName.'` where `id_ban_ipaddress` = '.$idBanIpAddress
        )) {
            $this->context->smarty->assign('success', $this->trans(
                'Ip address unblocked successfully from blocked list.',
                [],
                'Modules.ban_ipaddress'
            ));
        }
    }
    /**
     * Get blocked Ip address.
     *
     * @return array $ipResult
     */
    protected function getBlockedIpAddress()
    {
        $ipResult = array();
        $ipResult = Db::getInstance()->ExecuteS(
            'select id_ban_ipaddress, ip_address, blocking_type, ip_type, status, DATE(date_add) as 
            date_add from `'._DB_PREFIX_.$this->tableName.'` where blocking_type = "ipaddress"'
        );
        
        return $ipResult;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBan_ipaddressModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $optionsforselect = array(
            array('value' => 'ipaddress', 'name' => 'Ip address')
            );
        $ipTypeOptions = array(
                array('value' => 'ipv4', 'name' => 'IPV 4')
                );

        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 6,
                        'type' => 'text',
                        'required' => true,
                        'prefix' => '<i class="icon icon-ip"></i>',
                        'name' => 'ip_address',
                        'label' => $this->l('Ip address'),
                    ),
                    array(
                        'col' => 6,
                        'type' => 'select',
                        'label' => $this->l('Block Type'),
                        'name' => 'blocking_type',
                        'options' => array(
                            'query' => $optionsforselect,
                            'id' => 'value',
                            'name' => 'name',
                        ),
                        ),
                        array(
                            'col' => 6,
                            'type' => 'select',
                            'label' => $this->l('IP Type'),
                            'name' => 'ip_type',
                            'options' => array(
                                'query' => $ipTypeOptions,
                                'id' => 'value',
                                'name' => 'name',
                            ),
                            ),
                            array(
                                'col' => 6,
                                'type' => 'text',
                                'required' => false,
                                'prefix' => '<i class="icon icon-ip"></i>',
                                'name' => 'BAN_IPADDRESS_REDIRECTURL',
                                'label' => $this->l('Redirection URL'),
                            ),
                            array(
                                'col' => 6,
                                'type' => 'textarea',
                                'required' => false,
                                'prefix' => '<i class="icon icon-ip"></i>',
                                'name' => 'BAN_IPADDRESS_MESSAGE',
                                'label' => $this->l('Display Message'),
                            ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'BAN_IPADDRESS_MESSAGE' => Configuration::get('BAN_IPADDRESS_MESSAGE', null),
            'BAN_IPADDRESS_REDIRECTURL' => Configuration::get('BAN_IPADDRESS_REDIRECTURL', null),
            'ip_address' =>'',
            'blocking_type' =>'ipaddress',
            'ip_type' =>'ipv4',


        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $ipDetails = array();
        $ipDetails['ip_address'] = Tools::getValue('ip_address');
        $ipDetails['blocking_type'] = Tools::getValue('blocking_type');
        $ipDetails['ip_type'] = Tools::getValue('ip_type');
        
        if (Tools::getValue('BAN_IPADDRESS_REDIRECTURL') != '') {
            Configuration::updateValue('BAN_IPADDRESS_REDIRECTURL', Tools::getValue('BAN_IPADDRESS_REDIRECTURL'));
        }
        if (Tools::getValue('BAN_IPADDRESS_MESSAGE') != '') {
            Configuration::updateValue('BAN_IPADDRESS_MESSAGE', Tools::getValue('BAN_IPADDRESS_MESSAGE'));
        }
        $this->context->smarty->assign('success', $this->trans(
            'Message and redirection URL added successfully.',
            [],
            'Modules.ban_ipaddress'
        ));
        $validated = $this->validateActionBeforeSave($ipDetails);
        if ($validated) {
            Db::getInstance()->insert($this->tableName, $ipDetails);
        } else {
            $this->context->controller->errors[] = $this->trans(
                'Unable to add Ip address due to validation',
                [],
                'Modules.ban_ipaddress'
            );
        }
        if (!count($this->context->controller->errors)) {
            $this->context->smarty->assign('success', $this->trans(
                'Ip address added successfully in the blocked list.',
                [],
                'Modules.ban_ipaddress'
            ));
        }
    }

    protected function validateActionBeforeSave($ipDetails = array())
    {
        $sqlPart = '';
        $totalCounter = count($ipDetails);
        $counter = 1;
        if ($ipDetails['ip_address'] == '') {
            $this->context->controller->errors[] = $this->trans(
                'Ip address cannot be blank',
                [],
                'Modules.ban_ipaddress'
            );
            return false;
        }
        foreach ($ipDetails as $key => $value) {
            $sqlPart .= $key.' = "'.$value.'"';
            if ($counter != $totalCounter) {
                $sqlPart .= ' AND ';
            }
            ++$counter;
        }
        $validateResult = Db::getInstance()->ExecuteS(
            'select count(*) as qty from `'._DB_PREFIX_.$this->tableName.'` where '.$sqlPart
        );
        if ((int) $validateResult[0]['qty'] > 0) {
            return false;
        }

        return true;
    }

    /**
     * Validate Ip against blocked address.
     *
     * @param mixed $currentIP
     */
    protected function validateIpSpecifically($currentIP)
    {
        $validateResult = Db::getInstance()->ExecuteS(
            'select count(*) as qty from `'._DB_PREFIX_.$this->tableName.'` 
            where blocking_type = "ipaddress" and ip_address ="'.$currentIP.'"'
        );
        if ((int) $validateResult[0]['qty'] > 0) {
            return true;
        }

        return false;
    }

    /**
     * Validate Ip against blocked address range.
     *
     * @param mixed $currentIP
     */
    protected function validateIpAgainstIprange($currentIP)
    {
        $validateResult = Db::getInstance()->ExecuteS(
            'select * from `'._DB_PREFIX_.$this->tableName.'` where  `blocking_type` = "iprange"'
        );
        if (count($validateResult) > 0) {
            foreach ($validateResult as $indvResult) {
                $rangeArray = explode('-', $indvResult['ip_address']);
                if ($this->ipInRange($rangeArray[0], $rangeArray[1], $currentIP)) {
                    return true;
                    //break;
                }
            }
        }

        return false;
    }

    /**
     * validate if the ip is in a specific range.
     *
     * @param mixed $lower_range_ip_address
     * @param mixed $upper_range_ip_address
     * @param mixed $needle_ip_address
     */
    public function ipInRange($lower_range_ip_address, $upper_range_ip_address, $needle_ip_address)
    {
        // Get the numeric reprisentation of the IP Address with IP2long
        $min = ip2long($lower_range_ip_address);
        $max = ip2long($upper_range_ip_address);
        if ($min > $max) {
            $maxIp = $min;
            $min = $max;
            $max = $maxIp;
            unset($maxIp);
        }
        $needle = ip2long($needle_ip_address);

        // Then it's as simple as checking whether the needle falls between the lower and upper ranges
        return ($needle >= $min) and ($needle <= $max);
    }

    /**
     * check current ip if it is blocked either specific or in IP range.
     *
     * @param mixed $currentIp
     */
    public function validateSpecificIpAgainstBlockage($currentIp)
    {
        //Validate Ip against ipaddress
        if ($this->validateIpSpecifically($currentIp)) {
            return true;
        }

        //Validate Ip against Ip range
        if ($this->validateIpAgainstIprange($currentIp)) {
            return true;
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayHeader()
    {
        $currentIp = Tools::getRemoteAddr();
        if ($currentIp == '::1') {
            $currentIp = '127.0.0.1';
        }
        //Validate Against Specific id
        if ($this->validateSpecificIpAgainstBlockage($currentIp)) {
            $redirectURL = Configuration::get('BAN_IPADDRESS_REDIRECTURL');
            $message = Configuration::get('BAN_IPADDRESS_MESSAGE');
            header("refresh:2; url=".$redirectURL);
            echo $message;
            die;
        }
        //Validate Against Specific Range
    }

    public function hookDisplayHome()
    {
        $currentIp = Tools::getRemoteAddr();
        if ($currentIp == '::1') {
            $currentIp = '127.0.0.1';
        }
        //Validate Against Specific id
        if ($this->validateSpecificIpAgainstBlockage($currentIp)) {
            $redirectURL = Configuration::get('BAN_IPADDRESS_REDIRECTURL');
            $message = Configuration::get('BAN_IPADDRESS_MESSAGE');
            header("refresh:2; url=".$redirectURL);
            echo $message;
            die;
        }
    }

    public function hookHome()
    {
        $currentIp = Tools::getRemoteAddr();
        if ($currentIp == '::1') {
            $currentIp = '127.0.0.1';
        }
        //Validate Against Specific id
        if ($this->validateSpecificIpAgainstBlockage($currentIp)) {
            $redirectURL = Configuration::get('BAN_IPADDRESS_REDIRECTURL');
            $message = Configuration::get('BAN_IPADDRESS_MESSAGE');
            header("refresh:2; url=".$redirectURL);
            echo $message;
            die;
        }
    }
}
