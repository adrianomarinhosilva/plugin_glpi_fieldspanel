<?php
/*
 * -------------------------------------------------------------------------
 * Plugin FieldsPanel para GLPI
 * -------------------------------------------------------------------------
 * © 2025
 * -------------------------------------------------------------------------
 */

define('FIELDSPANEL_VERSION', '1.0.0');
define('FIELDSPANEL_MIN_GLPI_VERSION', '10.0.0');
define('FIELDSPANEL_MAX_GLPI_VERSION', '10.1.99');

/**
 * Inicialização do plugin
 * @return boolean
 */
function plugin_init_fieldspanel() {
   global $PLUGIN_HOOKS;
   
   $PLUGIN_HOOKS['csrf_compliant']['fieldspanel'] = true;
   
   // Sempre adiciona o JavaScript e CSS independente de permissões
   $PLUGIN_HOOKS['add_javascript']['fieldspanel'] = 'js/fieldspanel.js';
   $PLUGIN_HOOKS['add_css']['fieldspanel'] = 'css/fieldspanel.css';
   
   // Registra a página de configuração - isso faz o botão de configuração aparecer
   if (Session::haveRight('config', UPDATE)) {
      // Adiciona o link no menu de configuração
      $PLUGIN_HOOKS['config_page']['fieldspanel'] = 'front/config.form.php';
      
      // Importante: isso registra o botão de configuração no painel do plugin
      Plugin::registerClass('PluginFieldspanelConfig', [
         'classname' => 'PluginFieldspanelConfig'
      ]);
   }
   
   // Hook para modificar o arquivo Twig
   $PLUGIN_HOOKS['pre_item_form']['fieldspanel'] = ['PluginFieldspanelConfig', 'modifyFieldsPanel'];
   
   return true;
}

/**
 * Verifica os requisitos do plugin
 * @return array
 */
function plugin_version_fieldspanel() {
   return [
      'name'           => "Fields Panel Manager",
      'version'        => FIELDSPANEL_VERSION,
      'author'         => 'Plugin Developer',
      'license'        => 'GPLv3+',
      'homepage'       => '',
      'minGlpiVersion' => FIELDSPANEL_MIN_GLPI_VERSION, // Importante: este parâmetro também é usado
      'requirements'   => [
         'glpi'   => [
            'min' => FIELDSPANEL_MIN_GLPI_VERSION,
            'max' => FIELDSPANEL_MAX_GLPI_VERSION,
         ],
         'php'    => [
            'min' => '7.4'
         ]
      ]
   ];
}

/**
 * Verifica se o plugin pode ser instalado
 * @return boolean
 */
function plugin_fieldspanel_check_prerequisites() {
   if (version_compare(GLPI_VERSION, FIELDSPANEL_MIN_GLPI_VERSION, 'lt') || 
       version_compare(GLPI_VERSION, FIELDSPANEL_MAX_GLPI_VERSION, 'gt')) {
      echo "Este plugin requer GLPI versão " . FIELDSPANEL_MIN_GLPI_VERSION . " a " . FIELDSPANEL_MAX_GLPI_VERSION;
      return false;
   }
   return true;
}

/**
 * Verifica se o plugin pode ser ativado
 * @return boolean
 */
function plugin_fieldspanel_check_config() {
   return true;
}