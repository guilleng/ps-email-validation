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

        if (!$token) {
            $this->handleError($this->module->l('It seems you provided an invalid activation token.', 'activate'));
            return;
        }

        $data = $this->getValidationData($token);

        if (!$data) {
            $this->handleError($this->module->l('It seems you provided an invalid activation token.', 'activate'));
            return;
        } else {
            $this->nullifyEntry($token);
        }

        $dateAdded  = $data['date_added'];
        $customerId = (int)$data['customer_id'];
        $cartId     = (int)$data['visitor_cart_id'];

        if ($this->isTokenExpired($dateAdded)) {
            $this->handleError($this->module->l('Your token is expired.', 'activate'));
            return;
        }

        if (!$this->activateCustomer($customerId)) {
            $this->handleError($this->module->l('There was a problem activating your account.', 'activate'));
            return;
        }

        $this->loginCustomer($customerId);

        if ($cartId && $this->promoteCart($cartId)) {
            $this->redirectToCheckout();
        } else {
            $this->redirectToAccountPage();
        }
    }

    private function getValidationData($token)
    {
        $sql = 'SELECT customer_id, visitor_cart_id, date_added 
                FROM ' . _DB_PREFIX_ . 'psemailvalidation 
                WHERE token = "' . pSQL($token) . '"';

        return Db::getInstance()->getRow($sql);
    }

    private function nullifyEntry($token)
    {
        $epochZero  = "1970-01-01 00:00:00";
        $newToken   = bin2hex(random_bytes(16));

        $sql = 'UPDATE ' . _DB_PREFIX_ . 'psemailvalidation 
                SET token = "' . $newToken . '", date_added = "' . $epochZero . '"
                WHERE token = "' . $token . '"';

        return Db::getInstance()->getRow($sql);
    }

    private function isTokenExpired($dateAdded)
    {
        return strtotime($dateAdded) < (time() - 5 * 3600);
    }

    private function activateCustomer($customerId)
    {
        $customer = new Customer($customerId);
        if (!Validate::isLoadedObject($customer)) {
            return false;
        }

        $customer->active = true;
        return $customer->save();
    }

    private function loginCustomer($customerId)
    {
        $customer = new Customer($customerId);
        if (!Validate::isLoadedObject($customer)) {
            return;
        }

        Hook::exec('actionBeforeAuthentication');

        $this->context->customer = $customer;
        $cookie = $this->context->cookie;
        $cookie->id_customer = (int)$customer->id;
        $cookie->customer_lastname = $customer->lastname;
        $cookie->customer_firstname = $customer->firstname;
        $cookie->email = $customer->email;
        $cookie->passwd = $customer->passwd;
        $cookie->logged = 1;
        $cookie->write();

        $this->context->updateCustomer($customer);

        Hook::exec('actionAuthentication', ['customer' => $customer]);
    }

    /**
     * Attach the visitor cart to the customer's session.
     */
    private function promoteCart($cartId)
    {
        if (!$cartId) {
            return false;
        }

        $cart = new Cart($cartId);
        if (!Validate::isLoadedObject($cart)) {
            return false;
        }

        $this->context->cart = $cart;
        $cookie = $this->context->cookie;
        $cookie->id_cart = (int)$cart->id;
        $cookie->write();
        $cart->update();

        return count($cart->getProducts()) > 0;
    }

    private function redirectToCheckout()
    {
        $this->context->smarty->assign([
            'redirectUrl' => $this->context->link->getPageLink('order'),
            'delay'       => 3000,
        ]);
        $this->setTemplate('module:' . $this->module->name . '/views/templates/front/success-redirect.tpl');
    }

    private function redirectToAccountPage()
    {
        $this->info[] = $this->module->l('Successful verification! You may now use your credentials the next time you log in.', 'activate');
        $this->redirectWithNotifications('index.php?controller=authentication');
    }

    private function handleError($message)
    {
        $this->context->smarty->assign(['error_message' => $message]);
        $this->setTemplate('module:' . $this->module->name . '/views/templates/front/failure.tpl');
    }
}
