<?php

/*
   ------------------------------------------------------------------------
   Plugin Escalation for GLPI
   Copyright (C) 2012-2012 by the Plugin Escalation for GLPI Development Team.

   https://forge.indepnet.net/projects/escalation/
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
   along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Escalation for GLPI
   @author    David Durieux
   @co-author 
   @comment   
   @copyright Copyright (c) 2011-2012 Plugin Escalation for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/escalation/
   @since     2012
 
   ------------------------------------------------------------------------
 */

define ("PLUGIN_ESCALATION_VERSION","0.83+1.1");

// Init the hooks of escalation
function plugin_init_escalation() {
   global $PLUGIN_HOOKS,$CFG_GLPI,$LANG;
   
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
            
            $PLUGIN_HOOKS['menu_entry']['escalation'] = false;
            
         }
         
         $PLUGIN_HOOKS['headings']['escalation'] = 'plugin_get_headings_escalation';
         $PLUGIN_HOOKS['headings_action']['escalation'] = 'plugin_headings_actions_escalation';

         $PLUGIN_HOOKS['pre_item_add']['escalation'] = array('Ticket' => array('PluginEscalationGroup_Group', 'selectGroupOnAdd'));
         
         $PLUGIN_HOOKS['pre_item_update']['escalation'] = array('Ticket' => array('PluginEscalationGroup_Group', 'notMultiple'));
         
//         $PLUGIN_HOOKS['pre_item_update']['escalation'] = array('Ticket' => array('PluginEscalationGroup_Group', 'allowAssignRight'));
//         $PLUGIN_HOOKS['item_update']['escalation'] = array('Ticket' => array('PluginEscalationGroup_Group', 'restoreAssignRight'));
         
      }

}

// Name and Version of the plugin
function plugin_version_escalation() {
   return array('name'           => 'Escalation ticket',
                'shortname'      => 'escalation',
                'version'        => PLUGIN_ESCALATION_VERSION,
                'author'         =>'<a href="mailto:d.durieux@siprossii.com">David DURIEUX</a>',
                'homepage'       =>'',
                'minGlpiVersion' => '0.83'
   );
}


// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_escalation_check_prerequisites() {
   global $LANG;
   
   if (GLPI_VERSION >= '0.83') {
      return true;
   } else {
      echo "error";
   }
}

function plugin_escalation_check_config() {
   return true;
}

function plugin_escalation_haveTypeRight($type,$right) {
   return true;
}

?>