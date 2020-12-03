<?php 
use MapasCulturais\i;

$app = MapasCulturais\App::i();

$slug = $plugin_validador->getSlug();
$name = $plugin_validador->getName();

$route = MapasCulturais\App::i()->createUrl($slug, 'export');    
?>

<a class="btn btn-default download btn-export-cancel"  ng-click="editbox.open('<?= $slug ?>-editbox', $event)" rel="noopener noreferrer">CSV <?= $name ?></a>

<!-- Formulário -->
<edit-box id="<?= $slug ?>-editbox" position="top" title="<?php i::esc_attr_e('Exportar CSV - ' . $name) ?>" cancel-label="Cancelar" close-on-cancel="true">
    <form class="form-export-<?= $slug ?>" action="<?=$route?>" method="POST">
    
        <label for="financeiro-from">Data inícial</label>
        <input type="date" name="from" id="financeiro-from">
        
        <label for="financeiro-to">Data final</label>  
        <input type="date" name="to" id="financeiro-to">
        # Caso não queira filtrar entre datas, deixe os campos vazios.

        <input type="hidden" name="opportunity" value="<?=$opportunity?>">

        <label style="display: block; margin-bottom: 1em;">
            <input type="checkbox" name="only_unprocessed" value="1" checked> Exportar somente as inscrições sem validação financeira 
        </label>
        
        <button class="btn btn-primary download" type="submit">Exportar</button>
    </form>
</edit-box>
