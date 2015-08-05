<?php
require_once DINAMIZE_PLUGIN_DIR . '/admin/class.list-form.php';

class DinamizeWidget extends WP_Widget {
    public function __construct() {
        parent::WP_Widget(false, $name = 'Formulários Dinamize');
    }

    /**
     * Exibição final do Widget (já no sidebar)
     *
     * @param array $argumentos Argumentos passados para o widget
     * @param array $instancia Instância do widget
     */
    public function widget($argumentos, $widgetInfo) {
        $optionId = (int) $widgetInfo['dinamize_form_id'];
    	$form = dinamize_form($optionId);
        $form_title = $form->title;
        $form_html = $form->form_html();

        if (empty($form_html)) return;
        
        echo $argumentos['before_widget'];
        echo $argumentos['before_title'] . $form_title . $argumentos['after_title'];
        echo $form_html;
        echo $argumentos['after_widget'];
    }

    /**
     * Salva os dados do widget no banco de dados
     *
     * @param array $nova_instancia Os novos dados do widget (a serem salvos)
     * @param array $instancia_antiga Os dados antigos do widget
     */
    public function update($widgetInfo, $widgetOldInfo) {
        $instancia = array_merge($widgetOldInfo, $widgetInfo);
        
        return $instancia;
    }

    /**
     * Formulário para os dados do widget (exibido no painel de controle)
     *
     * @param array $instancia Instância do widget
     */
    public function form($widgetInfo) {
        $divFormId = $this->get_field_id('dinForm');

        $optionId = 0;
        $form_title = '';
        if (!empty($widgetInfo['dinamize_form_id'])) {
	        $optionId = (int) $widgetInfo['dinamize_form_id'];
	        // pegar o form title a partir do form_id
	        $form = dinamize_form($optionId);
	        $form_title = $form->title;
        }
        
        $list_table = new Dinamize_List_Form();
        $list_table->prepare_items(1000);
        
    	echo '<div id="'.$divFormId.'" class="widget-content">';
        echo '<p>';
        // Inserindo campo hidden para manter o nome na barra
        echo '<input id="'.$divFormId.'-title" type="hidden" value="'.$form_title.'" name="'.$this->get_field_name('form_title').'" disabled />';
        echo '<label for="'.$divFormId.'-form">'.__('Form', 'dinamize').':</label>';
        echo '<select id="'.$divFormId.'-form" name="'.$this->get_field_name('dinamize_form_id').'" class="widefat">';
        $adc = ($optionId == 0) ? 'selected' : '';
        echo '<option value="" disabled style="display:none;" '.$adc.'>'.__('Select the form', 'dinamize').'</option>';
        foreach ($list_table->items as $k => $row) {
        	$adc = ($row->id == $optionId) ? 'selected' : '';
        	echo '<option value="'.$row->id.'" '.$adc.'>'.$row->title.'</option>';
        }
        echo '</select>';
        echo '</p>';
        echo '</div>';
    }
}
?>