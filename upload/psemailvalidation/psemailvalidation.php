<?php
/*
 * @copyright     Copyright 2025 sincerity
 * @license       GNU/GPL 2 or later
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Psemailvalidation extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'psemailvalidation';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'sincerity';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Activate account by email');
        $this->description = $this->l('Sends a validation link to the user\'s email address at registration.');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('PSEMAILVALIDATION_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('createAccount') &&
            Db::getInstance()->execute(
                'ALTER TABLE ' . _DB_PREFIX_ . 'customer 
                ADD psemailvalidation_activation_token char(32)') &&
            Db::getInstance()->execute(
                'ALTER TABLE ' . _DB_PREFIX_ . 
                'customer ADD psemailvalidation_activation_expire DATETIME');
    }

    public function uninstall()
    {
        Configuration::deleteByName('PSEMAILVALIDATION_LIVE_MODE');

        $dropToken = Db::getInstance()->execute(
            "ALTER TABLE " . _DB_PREFIX_ . "customer 
            DROP COLUMN psemailvalidation_activation_token"
        );
        
        $dropExpire = Db::getInstance()->execute(
            "ALTER TABLE " . _DB_PREFIX_ . "customer 
            DROP COLUMN psemailvalidation_activation_expire"
        );
    
        return parent::uninstall() && $this->unregisterHook('createAccount') && $dropToken && $dropExpire;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitPsemailvalidationModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        return $this->renderForm();
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
        $helper->submit_action = 'submitPsemailvalidationModule';
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
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Status'),
                        'name' => 'PSEMAILVALIDATION_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Enable this module'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
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
            'PSEMAILVALIDATION_LIVE_MODE' => Configuration::get('PSEMAILVALIDATION_LIVE_MODE', true),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
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

    public function hookcreateAccount($params)
    {
        if (!Configuration::get('PSEMAILVALIDATION_LIVE_MODE')) {
            return;
        }

        $this->context->customer->logout();

        $activationToken  = bin2hex(random_bytes(16));
        $activationExpire = date('Y-m-d H:i:s', strtotime('+5 hours'));
        $customerId       = $params['newCustomer']->id;
        $activationLink   = $this->context->link->getModuleLink($this->name, 'activate') . '?token=' . $activationToken;

        $sql = 'UPDATE ' . _DB_PREFIX_ . 'customer 
                SET active = 0, 
                    psemailvalidation_activation_token = "' . $activationToken . '", 
                    psemailvalidation_activation_expire = "' . $activationExpire . '" 
                WHERE id_customer = ' . $customerId;

        Db::getInstance()->execute($sql);

        $customer = new Customer($customerId);
        $customer->getFields();

        Mail::Send($this->context->customer->id_lang,
                   'validate_email',
                   $this->l('Email Confirmation', 'psemailvalidation'),
                   array('{firstname}' => $customer->firstname,
                         '{lastname}' => $customer->lastname,
                         '{email}' => $customer->email,
                         '{link}' => $activationLink),
                   $customer->email,
                   NULL,
                   NULL,
                   NULL,
                   NULL,
                   NULL,
            _PS_MODULE_DIR_ . 'psemailvalidation/mails');

        $this->context->cookie->email_to_validate = $customer->email;
        Tools::redirect($this->context->link->getModuleLink($this->name, 'emailsentmessage'));
    }
}
