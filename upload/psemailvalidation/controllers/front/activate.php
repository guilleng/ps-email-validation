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

class psemailvalidationactivateModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $token = pSQL(Tools::getValue('token'));

        if ($id = $this->verify($token)) {
            $this->activateCustomer($id);
            $this->loginCustomer($id);
        } else {
            $this->setTemplate('module:psemailvalidation/views/templates/front/failure.tpl');
        }
    }

    private function verify($token)
    {
        $sql = 'SELECT psemailvalidation_activation_expire, id_customer
                FROM ' . _DB_PREFIX_ . 'customer 
                WHERE psemailvalidation_activation_token = "' . pSQL($token) . '"';

        $db = Db::getInstance();
        $data = $db->getRow($sql);

        if (!$data) {
            return false;
        }

        if (strtotime($data['psemailvalidation_activation_expire']) < time()) {
            return false;
        }

        return (int) $data['id_customer'];
    }

    private function activateCustomer($id)
    {
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'customer 
                SET active = 1, psemailvalidation_activation_token = NULL
                WHERE id_customer = ' . (int)$id;

        Db::getInstance()->execute($sql);
    }

    private function loginCustomer($id)
    {
        $customer = new Customer($id);
        if (!Validate::isLoadedObject($customer)) {
            $this->setTemplate('module:psemailvalidation/views/templates/front/failure.tpl');
            return;
        }

        Hook::exec('actionBeforeAuthentication');

        $this->context->customer = $customer;
        $this->context->cookie->id_customer = (int)$customer->id;
        $this->context->cookie->customer_lastname = $customer->lastname;
        $this->context->cookie->customer_firstname = $customer->firstname;
        $this->context->cookie->email = $customer->email;
        $this->context->cookie->passwd = $customer->passwd;
        $this->context->cookie->logged = 1;
        $this->context->cookie->write();
        $this->context->updateCustomer($customer);

        Hook::exec('actionAuthentication', ['customer' => $customer]);

        $products = $this->context->cart->getProducts();
        if (count($products) > 0) {
            $this->redirectToCheckout();
        } else {              
            $this->redirectToAccountPage();
        } 
    }

    private function redirectToCheckout() 
    {
        $this->context->smarty->assign([
            'redirectUrl'  => $this->context->link->getPageLink('order'),
            'delay'        => 5000,
        ]);
        $this->setTemplate('module:psemailvalidation/views/templates/front/success-redirect.tpl');
    }

    private function redirectToAccountPage() 
    {
        $this->info[] = $this->module->l(
            'Successful verification! You may now use your credentials the next time you log in.',
            'activate'
        );
        $this->redirectWithNotifications('index.php?controller=authentication');             
    }
}
