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
 * Classe de configuração do plugin FieldsPanel
 */
class PluginFieldspanelConfig extends CommonDBTM {
   static $rightname = 'config';
   
   /**
    * Retorna o nome do tipo
    * @param  $nb número de itens
    * @return string
    */
   static function getTypeName($nb = 0) {
      return __('Configuração do Painel de Campos', 'fieldspanel');
   }
   
   /**
    * Retorna a configuração atual
    * @return array
    */
   static function getConfig() {
      global $DB;
      
      // Verificar se a tabela existe
      if (!$DB->tableExists('glpi_plugin_fieldspanel_configs')) {
         return [
            'order' => ['item-main', 'actors', 'items', 'service-levels', 'linked_tickets'],
            'visible' => [
               'item-main' => 1, 
               'actors' => 1, 
               'items' => 1, 
               'service-levels' => 1, 
               'linked_tickets' => 1
            ]
         ];
      }
      
      // Verificar se existe registro
      $query = "SELECT * FROM glpi_plugin_fieldspanel_configs WHERE id=1";
      $result = $DB->query($query);
      
      if ($DB->numrows($result) == 0) {
         return [
            'order' => ['item-main', 'actors', 'items', 'service-levels', 'linked_tickets'],
            'visible' => [
               'item-main' => 1, 
               'actors' => 1, 
               'items' => 1, 
               'service-levels' => 1, 
               'linked_tickets' => 1
            ]
         ];
      }
      
      $data = $DB->fetchAssoc($result);
      
      $order = json_decode($data['order'], true);
      $visible = json_decode($data['visible'], true);
      
      if (!$order) {
         $order = ['item-main', 'actors', 'items', 'service-levels', 'linked_tickets'];
      }
      
      if (!$visible) {
         $visible = [
            'item-main' => 1, 
            'actors' => 1, 
            'items' => 1, 
            'service-levels' => 1, 
            'linked_tickets' => 1
         ];
      }
      
      return [
         'order' => $order,
         'visible' => $visible
      ];
   }
   
   /**
    * Salva a configuração
    * @param array $input
    * @return boolean
    */
   function saveConfig($input) {
      global $DB;
      
      // Certifica-se de que a tabela existe
      if (!$DB->tableExists('glpi_plugin_fieldspanel_configs')) {
         // Cria a tabela
         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_fieldspanel_configs` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                     `entities_id` int(11) NOT NULL DEFAULT '0',
                     `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
                     `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                     `order` text COLLATE utf8_unicode_ci,
                     `visible` text COLLATE utf8_unicode_ci,
                     `date_mod` timestamp NULL DEFAULT NULL,
                     `date_creation` timestamp NULL DEFAULT NULL,
                     PRIMARY KEY (`id`),
                     KEY `entities_id` (`entities_id`),
                     KEY `date_mod` (`date_mod`),
                     KEY `date_creation` (`date_creation`)
                   ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
      
      $input['id'] = 1; // Sempre usamos a configuração ID 1
      $input['date_mod'] = $_SESSION['glpi_currenttime'];
      
      // Converte para JSON
      if (isset($input['order']) && is_array($input['order'])) {
         $input['order'] = json_encode($input['order']);
      }
      
      if (isset($input['visible']) && is_array($input['visible'])) {
         $input['visible'] = json_encode($input['visible']);
      }
      
      // Verifica se já existe o registro
      $query = "SELECT COUNT(*) as count FROM glpi_plugin_fieldspanel_configs WHERE id=1";
      $result = $DB->query($query);
      $count = $DB->result($result, 0, 'count');
      
      if ($count > 0) {
         // Atualiza o registro existente
         return $this->update($input);
      } else {
         // Adiciona um novo registro
         $input['date_creation'] = $_SESSION['glpi_currenttime'];
         return $this->add($input);
      }
   }
   
   /**
    * Hook para modificar o arquivo Twig
    * @param array $params
    * @return void
    */
   static function modifyFieldsPanel($params) {
      if (isset($params['item']) && $params['item'] instanceof CommonITILObject) {
         $config = self::getConfig();
         
         // Adiciona o script que vai modificar o painel de campos
         echo "<script>
            $(document).ready(function() {
               if (typeof FieldsPanel !== 'undefined') {
                  var config = " . json_encode($config) . ";
                  FieldsPanel.reorderPanels(config);
               } else {
                  console.error('FieldsPanel JS não está carregado');
               }
            });
         </script>";
      }
   }
}