/**
 * -------------------------------------------------------------------------
 * Plugin FieldsPanel para GLPI
 * -------------------------------------------------------------------------
 * © 2025
 * -------------------------------------------------------------------------
 */

var FieldsPanel = {
   /**
    * Reordena os painéis de acordo com a configuração
    * @param {Object} config - Configuração do plugin
    */
   reorderPanels: function(config) {
      if (!config || !config.order || !config.visible) {
         return;
      }
      
      var container = document.getElementById('itil-data');
      if (!container) {
         return;
      }
      
      // Mapeia os elementos do acordeão
      var panels = {
         'item-main': container.querySelector('.accordion-item:has(#item-main)'),
         'actors': container.querySelector('.accordion-item:has(#actors)'),
         'items': container.querySelector('.accordion-item:has(#items)'),
         'service-levels': container.querySelector('.accordion-item:has(#service-levels)'),
         'linked_tickets': container.querySelector('.accordion-item:has(#linked_tickets)'),
         'analysis': container.querySelector('.accordion-item:has(#analysis)'),
         'plans': container.querySelector('.accordion-item:has(#plans)')
      };
      
      // Remove todos os painéis do container
      Object.keys(panels).forEach(function(key) {
         var panel = panels[key];
         if (panel && panel.parentNode === container) {
            container.removeChild(panel);
         }
      });
      
      // Adiciona os painéis na ordem especificada
      config.order.forEach(function(key) {
         var panel = panels[key];
         if (panel) {
            // Verifica se o painel deve ser visível
            if (config.visible[key]) {
               container.appendChild(panel);
            } else {
               // Se não for visível, adiciona a classe 'd-none'
               panel.classList.add('d-none');
               container.appendChild(panel);
            }
         }
      });
      
      // Adiciona os painéis que não estão na configuração
      Object.keys(panels).forEach(function(key) {
         var panel = panels[key];
         if (panel && !config.order.includes(key) && panel.parentNode !== container) {
            container.appendChild(panel);
         }
      });
   },
   
   /**
    * Inicializa o plugin
    */
   init: function() {
      // Adiciona funcionalidades de arrastar e soltar para o formulário de configuração
      var sortableContainer = document.getElementById('sortable-items');
      if (sortableContainer) {
         this.initSortable(sortableContainer);
      }
   },
   
   /**
    * Inicializa a funcionalidade de arrastar e soltar
    * @param {HTMLElement} container - Elemento que vai receber a funcionalidade
    */
   initSortable: function(container) {
      // Verifica se o jQuery UI está disponível
      if (typeof jQuery !== 'undefined' && typeof jQuery.ui !== 'undefined') {
         jQuery(container).sortable({
            items: '.fieldspanel-item',
            cursor: 'move',
            axis: 'y',
            opacity: 0.7,
            handle: '.fa-arrows-alt',
            update: function(event, ui) {
               // Atualiza os campos hidden de ordem
               jQuery(container).find('.fieldspanel-item').each(function(index) {
                  var key = jQuery(this).data('key');
                  jQuery(this).find('input[name="order[]"]').val(key);
               });
            }
         });
      }
   }
};

// Inicializa o plugin quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
   FieldsPanel.init();
});

// Função auxiliar para compatibilidade com versões mais antigas do JavaScript
if (!document.querySelector(':has(*)')) {
   Element.prototype.querySelector = function(selector) {
      // Implementação simplificada para o seletor :has()
      if (selector.includes(':has(')) {
         // Extrai o seletor principal e o sub-seletor do :has()
         var match = selector.match(/(.+):has\((.+)\)/);
         if (match) {
            var mainSelector = match[1];
            var subSelector = match[2];
            
            // Encontra os elementos que correspondem ao seletor principal
            var elements = Array.from(this.querySelectorAll(mainSelector));
            
            // Filtra elementos que contêm o sub-seletor
            for (var i = 0; i < elements.length; i++) {
               if (elements[i].querySelector(subSelector)) {
                  return elements[i];
               }
            }
            
            return null;
         }
      }
      
      // Comportamento padrão para seletores normais
      return Element.prototype.querySelector.call(this, selector);
   };
}