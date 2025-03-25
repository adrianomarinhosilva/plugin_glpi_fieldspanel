<?php
/*
 * -------------------------------------------------------------------------
 * Plugin FieldsPanel para GLPI
 * -------------------------------------------------------------------------
 * © 2025
 * -------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Acesso direto não permitido");
}

/**
 * Classe de perfil para o plugin FieldsPanel
 */
class PluginFieldspanelProfile extends Profile {
   static $rightname = "profile";
   
   /**
    * Retorna os direitos do plugin
    * @param boolean $all
    * @return array
    */
   static function getAllRights($all = false) {
      $rights = [
         [
            'itemtype'  => 'PluginFieldspanelConfig',
            'label'     => __('Configuração do painel de campos', 'fieldspanel'),
            'field'     => 'plugin_fieldspanel_config',
            'rights'    => [
               READ    => __('Leitura', 'fieldspanel'),
               UPDATE  => __('Atualização', 'fieldspanel')
            ],
            'default'   => 31 // ALLSTANDARDRIGHT (1+2+4+8+16)
         ]
      ];
      
      return $rights;
   }
   
   /**
    * Adiciona direitos específicos do plugin
    * @param $profiles_id
    */
   static function createFirstAccess($profiles_id) {
      global $DB;
      
      // Dê acesso total ao superadmin
      if ($profiles_id == 4) { // 4 é geralmente o ID do Super-Admin
         $rights = ['plugin_fieldspanel_config' => 31]; // ALLSTANDARDRIGHT
         self::addDefaultProfileInfos($profiles_id, $rights);
      }
      
      // Adicione isso para todos os perfis existentes
      $profiles = getAllDataFromTable('glpi_profiles');
      foreach ($profiles as $profile) {
         $rights = ['plugin_fieldspanel_config' => 31]; // ALLSTANDARDRIGHT
         self::addDefaultProfileInfos($profile['id'], $rights);
      }
   }
   
   /**
    * Adiciona direitos a um perfil específico
    * @param $profiles_id
    * @param $rights
    */
   static function addDefaultProfileInfos($profiles_id, $rights) {
      global $DB;
      
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if (!countElementsInTable(
            'glpi_profilerights',
            ['profiles_id' => $profiles_id, 'name' => $right]
         )) {
            $profileRight->add([
               'profiles_id' => $profiles_id,
               'name'        => $right,
               'rights'      => $value
            ]);
         } else {
            // Atualiza permissões existentes
            $query = "UPDATE `glpi_profilerights` 
                      SET `rights` = $value 
                      WHERE `profiles_id` = $profiles_id 
                      AND `name` = '$right'";
            $DB->query($query);
         }
      }
   }
   
   /**
    * Atualiza os direitos para todos os perfis 
    * @return void
    */
   static function installProfile() {
      global $DB;
      
      // Para todos os perfis existentes, adicione acesso
      $profiles = getAllDataFromTable('glpi_profiles');
      foreach ($profiles as $profile) {
         $rights = ['plugin_fieldspanel_config' => 31]; // ALLSTANDARDRIGHT
         self::addDefaultProfileInfos($profile['id'], $rights);
      }
   }
   
   /**
    * Mostra o formulário de permissões do perfil
    * @param $item
    * @return void
    */
   static function showForProfile($item) {
      if ($item->getField('interface') == 'central') {
         $profile = new self();
         $profile->showForm($item->getID());
      }
   }
   
   /**
    * Mostra o formulário de permissões
    * @param $ID
    * @param $options
    * @return void
    */
   function showForm($ID, $options = []) {
      echo "<div class='firstbloc'>";
      
      if ($ID) {
         $profile = new Profile();
         $profile->getFromDB($ID);
         
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'>";
         echo "<th colspan='4' class='center'>";
         echo __('Permissões do plugin FieldsPanel', 'fieldspanel');
         echo "</th></tr>";
         
         $rights = self::getAllRights();
         $profile->displayRightsChoiceMatrix(
            $rights,
            [
               'canedit'       => $profile->canEdit(),
               'default_class' => 'tab_bg_2',
               'title'         => __('Configuração do painel de campos', 'fieldspanel')
            ]
         );
         
         echo "</table>";
      }
      
      echo "</div>";
   }
   
   /**
    * Inicializar perfis durante a instalação/atualização
    * @param Migration $migration
    * @return void
    */
   static function initProfile() {
      global $DB;
      
      // Atualiza todos os perfis existentes
      self::installProfile();
      
      // Dá todos os direitos ao perfil de Super Admin
      $profile = new Profile();
      if ($profile->getFromDB(4)) { // Super-Admin
         $rights = ['plugin_fieldspanel_config' => 31]; // ALLSTANDARDRIGHT
         self::addDefaultProfileInfos(4, $rights);
      }
   }
}