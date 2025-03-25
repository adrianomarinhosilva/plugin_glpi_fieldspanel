<?php
/*
 * -------------------------------------------------------------------------
 * Plugin FieldsPanel para GLPI
 * -------------------------------------------------------------------------
 * © 2025
 * -------------------------------------------------------------------------
 */

// Inclui os arquivos necessários do GLPI
include('../../../inc/includes.php');

// Verifica permissões
Session::checkRight("config", UPDATE);

// Instancia a classe de configuração
$config = new PluginFieldspanelConfig();

// Se o formulário foi enviado (POST)
if (isset($_POST["update"])) {
    // Transforma os dados do formulário em um formato correto
    $order = isset($_POST['order']) ? $_POST['order'] : [];
    $visible = isset($_POST['visible']) ? $_POST['visible'] : [];
    
    $input = [
        'order' => $order,
        'visible' => $visible
    ];
    
    // Salva a configuração
    $config->saveConfig($input);
    
    // Exibe mensagem de sucesso
    Session::addMessageAfterRedirect(__('Configuração salva com sucesso', 'fieldspanel'), true, INFO);
    
    // Redireciona para a página anterior
    Html::back();
} else {
    // Carrega o cabeçalho HTML
    Html::header(
        __('Configuração do Painel de Campos', 'fieldspanel'),
        $_SERVER['PHP_SELF'],
        "admin",
        "config"
    );
    
    // Cria ou obtém a configuração
    createOrGetConfig();
    
    // Obtém a configuração
    $configData = PluginFieldspanelConfig::getConfig();
    
    // Define os itens disponíveis para configuração
    $items = [
        'item-main' => 'Chamado',
        'actors' => 'Autores do Ticket',
        'items' => 'Dispositivos',
        'service-levels' => 'Níveis de Serviço',
        'linked_tickets' => 'Chamados Relacionados'
    ];
    
    // Exibe formulário com design melhorado
    echo "<div class='center' style='max-width: 950px; margin: 0 auto;'>";
    echo "<form method='post' action='".$_SERVER['PHP_SELF']."'>";
    echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
    
    echo "<div class='card'>";
    echo "<div class='card-header'>";
    echo "<h3>" . __('Ordem os Elementos Acordeon do Ticket', 'fieldspanel') . "</h3>";
    echo "</div>";
    echo "<div class='card-body'>";
    
    
    
    
    echo "<div id='sortable-items' class='fieldspanel-sortable'>";
    
    // Gera os elementos do formulário
    foreach ($configData['order'] as $key) {
        if (isset($items[$key])) {
            $checked = isset($configData['visible'][$key]) && $configData['visible'][$key] ? 'checked' : '';
            echo "<div class='fieldspanel-item' data-key='" . $key . "'>";
            echo "<div class='handle'><i class='fas fa-grip-vertical'></i></div>";
            echo "<div class='item-content'>";
            echo "<span class='item-title'>" . $items[$key] . "</span>";
            echo "</div>";
            echo "<div class='item-switch'>";
            echo "<label class='switch' title='Mostrar/Ocultar'>";
            echo "<input type='checkbox' name='visible[" . $key . "]' value='1' " . $checked . ">";
            echo "<span class='slider round'></span>";
            echo "</label>";
            echo "</div>";
            echo "<input type='hidden' name='order[]' value='" . $key . "'>";
            echo "</div>";
        }
    }
    
    echo "</div>";
    
    echo "</div>"; // card-body
    echo "<div class='card-footer text-center'>";
    echo "<button type='submit' name='update' class='btn btn-primary px-4'>";
    echo "<i class='fas fa-save me-2'></i>";
    echo "Salvar";
    echo "</button>";
    echo "</div>"; // card-footer
    echo "</div>"; // card
    
    echo "</form>";
    echo "</div>";
    
    // Script JavaScript para inicializar o sortable
    echo "<script>
        $(document).ready(function() {
            // Verifica se jQuery UI está disponível
            if (typeof $.fn.sortable !== 'undefined') {
                $('#sortable-items').sortable({
                    handle: '.handle',
                    axis: 'y',
                    placeholder: 'fieldspanel-item-placeholder',
                    forcePlaceholderSize: true,
                    update: function(event, ui) {
                        // Atualiza os campos hidden de ordem
                        $('#sortable-items .fieldspanel-item').each(function(index) {
                            var key = $(this).data('key');
                            $(this).find('input[name=\"order[]\"]').val(key);
                        });
                    }
                }).disableSelection();
            } else {
                console.error('jQuery UI Sortable não está disponível. Adicionando polyfill...');
                
                // Carrega jQuery UI dinamicamente se não estiver disponível
                var script = document.createElement('script');
                script.src = 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js';
                script.integrity = 'sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=';
                script.crossOrigin = 'anonymous';
                script.onload = function() {
                    // Inicializa sortable após carregar jQuery UI
                    $('#sortable-items').sortable({
                        handle: '.handle',
                        axis: 'y',
                        placeholder: 'fieldspanel-item-placeholder',
                        forcePlaceholderSize: true,
                        update: function(event, ui) {
                            $('#sortable-items .fieldspanel-item').each(function(index) {
                                var key = $(this).data('key');
                                $(this).find('input[name=\"order[]\"]').val(key);
                            });
                        }
                    }).disableSelection();
                };
                document.head.appendChild(script);
                
                // Adiciona o CSS do jQuery UI se necessário
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.min.css';
                document.head.appendChild(link);
            }
        });
    </script>";
    
    // Adiciona CSS personalizado para melhorar a aparência
    echo "<style>
        .fieldspanel-sortable {
            list-style-type: none;
            padding: 0;
            margin-bottom: 20px;
        }
        .fieldspanel-item {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            margin-bottom: 10px;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }
        .fieldspanel-item:hover {
            background-color: #e9ecef;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .fieldspanel-item-placeholder {
            height: 56px;
            background-color: #fffcb7 !important;
            border: 1px dashed #e3cc4d !important;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .handle {
            color: #6c757d;
            cursor: move;
            margin-right: 15px;
            font-size: 16px;
        }
        .handle:hover {
            color: #495057;
        }
        .item-content {
            flex-grow: 1;
            padding-right: 20px;
        }
        .item-title {
            font-weight: 500;
            font-size: 15px;
            color: #343a40;
        }
        .item-switch {
            margin-left: auto;
        }
        
        /* Switch personalizado estilo iOS */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
        }
        .slider:before {
            position: absolute;
            content: '';
            height: 22px;
            width: 22px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: .4s;
        }
        input:checked + .slider {
            background-color: #ffc107;
        }
        input:focus + .slider {
            box-shadow: 0 0 1px #ffc107;
        }
        input:checked + .slider:before {
            transform: translateX(24px);
        }
        .slider.round {
            border-radius: 26px;
        }
        .slider.round:before {
            border-radius: 50%;
        }
        
        /* Botão personalizado */
        .btn-primary {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
            font-weight: 500;
        }
        .btn-primary:hover {
            background-color: #e0a800;
            border-color: #e0a800;
            color: #212529;
        }
    </style>";
    
    // Carrega o rodapé HTML
    Html::footer();
}

/**
 * Cria ou obtém a configuração do plugin
 * @return PluginFieldspanelConfig
 */
function createOrGetConfig() {
    global $DB;
    
    $config = new PluginFieldspanelConfig();
    
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
    
    // Verifica se já existe configuração
    $query = "SELECT COUNT(*) as count FROM glpi_plugin_fieldspanel_configs WHERE id=1";
    $result = $DB->query($query);
    $count = $DB->result($result, 0, 'count');
    
    if ($count == 0) {
        // Se não existir, cria a configuração
        $input = [
            'id' => 1,
            'name' => 'Configuração Padrão',
            'order' => json_encode(['item-main', 'actors', 'items', 'service-levels', 'linked_tickets']),
            'visible' => json_encode(['item-main' => 1, 'actors' => 1, 'items' => 1, 'service-levels' => 1, 'linked_tickets' => 1]),
            'date_creation' => $_SESSION['glpi_currenttime'],
            'date_mod' => $_SESSION['glpi_currenttime'],
        ];
        
        $config->add($input);
    }
    
    return $config;
}