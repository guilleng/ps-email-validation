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

class psemailvalidationemailsentmessageModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $email = isset($this->context->cookie->email_to_validate) ? $this->context->cookie->email_to_validate : '';
        $this->context->smarty->assign('customer_email', $email);
        $this->setTemplate('module:psemailvalidation/views/templates/front/email-sent-to.tpl');
    }
}
