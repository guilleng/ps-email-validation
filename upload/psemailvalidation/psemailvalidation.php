<?php
/**
 * @author    Guille Garay <guillegaray@guillegaray.com>
 * @copyright Since 2025 Guille Garay
 * @license   GNU/GPL 2 or later
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
        $this->author = 'Guille Garay';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Activate account by email');
        $this->description = $this->l('Users must verify their email to access their account. Upon activation, they are fully logged in, their cart is restored, and they are redirected to checkout.');

        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        include dirname(__FILE__) . '/sql/install.php';

        return parent::install()
            && $this->registerHook('createAccount');
    }

    public function uninstall()
    {
        include dirname(__FILE__) . '/sql/uninstall.php';

        return parent::uninstall() && 
          $this->unregisterHook('createAccount');
    }

    public function hookcreateAccount($params)
    {
        $customer = $this->context->customer;
        $customer->active = false;
        $customer->logout();
        $customer->save();

        $activationToken = bin2hex(random_bytes(16));
        $cartId = isset($params['cart']->id) ? (int) $params['cart']->id : 0;

        // Insert the data into the database
        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'psemailvalidation (
                    customer_id, visitor_cart_id, token
                ) VALUES (
                    ' . $customer->id . ',
                    ' . $cartId . ',
                    "' . $activationToken . '"
                )';
        if (!Db::getInstance()->execute($sql)) {
            PrestaShopLogger::addLog('Failed to insert customer into ' . $this->name . 'table. id_customer: ' . $customer->id, 3);
            Tools::redirect($this->context->link->getModuleLink($this->name, 'failure'));
        }

        // Send the link
        $activationLink = $this->context->link->getModuleLink($this->name, 'activate', ['token' => $activationToken]);
        $emailSent = Mail::Send(
            (int) Context::getContext()->language->id,
            'validate_email',
            $this->l('Email Validation'),
            [
                '{firstname}' => $customer->firstname,
                '{lastname}' => $customer->lastname,
                '{email}' => $customer->email,
                '{link}' => $activationLink,
            ],
            $customer->email,
            null,
            null,
            null,
            null,
            null,
            _PS_MODULE_DIR_ . $this->name . '/mails/'
        );
        if (!$emailSent) {
            PrestaShopLogger::addLog('Failed to send email from ' . $this->name . 'to ' . $customer->email, 3);
            Tools::redirect($this->context->link->getModuleLink($this->name, 'failure'));
        }

        $this->context->cookie->email_to_validate = $customer->email;
        Tools::redirect($this->context->link->getModuleLink($this->name, 'emailsentmessage'));
    }
}
