{*
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
 *
 *}

{extends file='page.tpl'}

{block name='page_content'}
  <div id="custom-text">
    <h1>{l s='Successful verification' mod='psemailvalidation'}</h1>
    <p>
      <br>
      {l s='You will be redirected to ' mod='psemailvalidation'}
      <a href="{$redirectUrl|escape:"htmlall":"UTF-8"}">
        {l s='checkout' mod='psemailvalidation'}
      </a>
      {l s='in ' mod='psemailvalidation'}
      <strong id="countdown">{$delay/1000|intval}</strong>
      .
    <p>
  </div>
  <script>
    var countdown = ({$delay/1000|intval});
    var countdownElement = document.getElementById("countdown");

    var timer = setInterval(function() {
      countdown--;
      countdownElement.textContent = countdown;

      if (countdown <= 0) {
        clearInterval(timer);
        window.location.href = '{$redirectUrl|escape:"htmlall":"UTF-8"}';
      }
    }, 1000);
  </script>
{/block}
