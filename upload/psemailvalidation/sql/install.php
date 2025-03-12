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

$sql = [];

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'psemailvalidation` ( 
    `psemailvalidation_id` int(10) unsigned NOT NULL AUTO_INCREMENT, 
    `customer_id` int(10) unsigned NOT NULL, 
    `visitor_cart_id` int(10) unsigned NOT NULL DEFAULT 0, 
    `token` char(32) NOT NULL,
    `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`psemailvalidation_id`), 
    UNIQUE KEY `token` (`token`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
