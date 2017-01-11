<?php

/*
   ------------------------------------------------------------------------
   Plugin Escalation for GLPI
   Copyright (C) 2012-2017 by the Plugin Escalation for GLPI Development Team.

   https://github.com/ddurieux/glpi_escalation
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Escalation project.

   Plugin Escalation for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Escalation for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Escalation. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Escalation for GLPI
   @author    David Durieux
   @co-author
   @comment
   @copyright Copyright (c) 2011-2017 Plugin Escalation for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://github.com/ddurieux/glpi_escalation
   @since     2012

   ------------------------------------------------------------------------
 */

define ("PLUGIN_ESCALATION_VERSION", "0.90+1.2");

// Init the hooks of escalation
function plugin_init_escalation() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['change_profile']['escalation'] = array('PluginEscalationProfile','changeprofile');

   $PLUGIN_HOOKS['csrf_compliant']['escalation'] = true;

   // After escalation, if user can't see the ticket (dan't see all ticket right), it redirect to ticket list
   if (isset($_SERVER['HTTP_REFERER'])
           AND strstr($_SERVER['HTTP_REFERER'], "escalation/front/group_group.form.php")) {
      if (isset($_GET['id'])) {
         $ticket = new Ticket();
         $ticket->getFromDB($_GET['id']);
         if (!$ticket->canViewItem()) {
            // Can't see ticket, go in ticket list
            $ticket->redirectToList();
         }
      }
   }
      if (isset($_SESSION["glpiID"])) {

         $plugin = new Plugin();
         if ($plugin->isActivated('escalation')) {

            Plugin::registerClass('PluginEscalationProfile',
                                          array('addtabon'=> array('Profile')));
            Plugin::registerClass('PluginEscalationTicketCopy',
                                          array('addtabon'=> array('Ticket')));
            Plugin::registerClass('PluginEscalationConfig',
                                          array('addtabon'=> array('Entity')));
            Plugin::registerClass('PluginEscalationGroup_Group',
                                          array('addtabon'=> array(
                                              'Ticket',
                                              'Group')));

            $PLUGIN_HOOKS['menu_entry']['escalation'] = false;

            PluginEscalationGroup_Group::convertNewTicket();

            // limit group
            $peConfig = new PluginEscalationConfig();
            if ($peConfig->getValue('limitgroup', $_SESSION['glpidefault_entity']) == '1') {
               if ((strpos($_SERVER['PHP_SELF'], "ticket.form.php")
                       OR strpos($_SERVER['PHP_SELF'], "problem.form.php"))
                       && !isset($_GET['id'])) {

                  $group = new Group();
                  $a_groups = array();
                  foreach($_SESSION['glpigroups'] as $groups_id) {
                     $a_groups[$groups_id] = $groups_id;
                  }
                  $_SESSION['plugin_escalation_requestergroups'] = $a_groups;

               }
               if (strpos($_SERVER['PHP_SELF'], "getDropdownValue.php")) {
                  if (isset($_GET['itemtype'])
                          && $_GET['itemtype'] == 'Group') {
                     if (count($_SESSION['plugin_escalation_requestergroups']) > 0) {
                        if (isset($_GET['condition'])
                                && !empty($_GET['condition'])
                                && $_SESSION['glpicondition'][$_GET['condition']] == '`is_requester`') {
                           $_SESSION['glpicondition'][$_GET['condition']."-restrict"] = $_SESSION['glpicondition'][$_GET['condition']].
                                   " AND `id` IN ('".  implode("', '", $_SESSION['plugin_escalation_requestergroups'])."')";
                           $_GET['condition'] .= "-restrict";
                        }
//                        $_GET['condition'] = " `id` IN ('".  implode("', '", $_SESSION['plugin_escalation_requestergroups'])."')";
                     }
                  }
               }
            }
            // end limit group
         }

         $PLUGIN_HOOKS['pre_item_add']['escalation'] = array(
             'Ticket' => array(
                 'PluginEscalationGroup_Group',
                 'selectGroupOnAdd'));
         $PLUGIN_HOOKS['item_add']['escalation'] = array(
             'Ticket' => array(
                 'PluginEscalationTicketCopy',
                 'finishAdd'));

         $PLUGIN_HOOKS['pre_item_update']['escalation'] = array('Ticket' => array('PluginEscalationGroup_Group', 'notMultiple'));


      }

}

// Name and Version of the plugin
function plugin_version_escalation() {
   return array('name'           => 'Escalation ticket',
                'shortname'      => 'escalation',
                'version'        => PLUGIN_ESCALATION_VERSION,
                'author'         =>'<a href="mailto:david@durieux.family">David DURIEUX</a>'
                                   .'& <a href="mailto:dcs.glpi@dcsit-group.com">DCS company</a>',
                'homepage'       =>'',
                'minGlpiVersion' => '0.85'
   );
}


// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_escalation_check_prerequisites() {

   if (version_compare(GLPI_VERSION, '0.85', 'lt') || version_compare(GLPI_VERSION, '0.91', 'ge')) {
      echo __('Your GLPI version not compatible, require 0.85/0.90', 'escalation');
      return FALSE;
   }
   return true;
}

function plugin_escalation_check_config() {
   return true;
}

function plugin_escalation_haveTypeRight($type, $right) {
   return true;
}



function plugin_escalation_on_exit() {

   $out = ob_get_contents();
   ob_end_clean();

   $split = explode('pics/groupes.png', $out);
   if (!isset($split[1])) {
      echo $out;
      return;
   }
   $first_out = $split[0];
   unset($split[0]);
   $split2 = explode('</td>', implode('pics/groupes.png', $split));

   unset($split2[0]);
   echo $first_out."pics/groupes.png' alt='".__('Groups')."' title='".__('Groups')."' width='20'>";
   if (strstr($out, "<span class='red'>*</span>")) {
      echo '<span class="red">*</span> ';
   }

   // Extract default value if exist
   $d_split = explode('dropdown__groups_id_requester', $out);
   $d_split2 = explode('</select>', $d_split[1]);
//   echo $d_split2[0];
   preg_match("/selected value='([0-9]+)'/", $d_split2[0], $a_selected);

/*
<option class="tree" selected="" value="3">
<option class="tree" selected="" value="3">groupe 1 &gt; groupe 3</option>

 */

   $options = array();
   if (isset($a_selected[1])
           && is_numeric($a_selected[1])
           && isset($_SESSION['plugin_escalation_requestergroups'][$a_selected[1]])) {

      $options['value'] = $a_selected[1];
   } else if (count($_SESSION['plugin_escalation_requestergroups']) == 2) {
      $a_list_tmp = array_slice($_SESSION['plugin_escalation_requestergroups'], 1, 1, TRUE);
      $options['value'] = key($a_list_tmp);
   }

   if (!isset($options['value'])
           && isset($_SESSION['plugin_escalation_groups_id_requester'])) {
      $options['value'] = $_SESSION['plugin_escalation_groups_id_requester'];
   }

   Dropdown::showFromArray("dropdown__groups_id_requester",
                           $_SESSION['plugin_escalation_requestergroups'],
                           $options);

   echo implode('</td>', $split2);

}

?>
