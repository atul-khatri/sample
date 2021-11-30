{*
* 2007-2019 PrestaShop
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
*}

<div class="panel">
{if $smarty.server.SERVER_PORT eq '80'} 
    {assign var="http" value='http://'} 
{else} 
    {assign var="http" value='https://'} 
{/if} 
<table class="table">
<thead>
<tr>
    <th scope="col">Ip Address</th>
    <th scope="col">Blocking Type</th>
    <th scope="col">Ip Type</th>
    <th scope="col">Created Date</th>
    <th scope="col">Status</th>
    <th scope="col">Action</th>
</tr>
</thead>
<tbody>
{foreach from=$blockedIpAddress item=blockedIp}
<tr>
    <td scope="row">{l s='%message%' sprintf=[ '%message%' => $blockedIp.ip_address] 
    d='Modules.ban_ipaddress'}</td>
    <td>{l s='%message%' sprintf=[ '%message%' => $blockedIp.blocking_type] 
    d='Modules.ban_ipaddress'}</td>
    <td>{l s='%message%' sprintf=[ '%message%' => $blockedIp.ip_type] 
    d='Modules.ban_ipaddress'}</td>
    <td>{l s='%message%' sprintf=[ '%message%' => $blockedIp.date_add] 
    d='Modules.ban_ipaddress'}</td>
    <td>{l s='%message%' sprintf=[ '%message%' => $blockedIp.status] 
    d='Modules.ban_ipaddress'}</td>
    <td><form action ="" method="post">
            <input type ="hidden" name = "id_ban_ipaddress" value='{l s='%message%' sprintf=[ '%message%' => $blockedIp.id_ban_ipaddress] 
    d='Modules.ban_ipaddress'}'>
            <input type="submit" value="Unblock" class='form-control button button-submit' name="submit_unblock" />
        </form>
    </td>
</tr>
{/foreach}
</tbody>
</table>
</div>