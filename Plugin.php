<?php

namespace AldirBlancValidadorFinanceiro;

use MapasCulturais\App;
use MapasCulturais\Entities\Registration;

class Plugin extends \AldirBlanc\PluginValidador
{
    function __construct(array $config = [])
    {
        $config += [
            // se true, só exporta as inscrições pendentes que já tenham alguma avaliação
            'exportador_requer_homologacao' => true,

            // se true, só exporta as inscrições 
            'exportador_requer_validacao' => ['dataprev'],
        ];
        $this->_config = $config;
        parent::__construct($config);
    }

    function _init()
    {
        $app = App::i();

        $plugin_aldirblanc = $app->plugins['AldirBlanc'];
        $plugin_validador = $this;

        //botao de export csv
        $app->hook('template(opportunity.single.header-inscritos):end', function () use($plugin_aldirblanc, $plugin_validador, $app){
            $inciso1Ids = [$plugin_aldirblanc->config['inciso1_opportunity_id']];
            $inciso2Ids = array_values($plugin_aldirblanc->config['inciso2_opportunity_ids']);
            $inciso3Ids = is_array($plugin_aldirblanc->config['inciso3_opportunity_ids']) ? $plugin_aldirblanc->config['inciso3_opportunity_ids'] : [];
            
            $opportunities_ids = array_merge($inciso1Ids, $inciso2Ids, $inciso3Ids);
            $requestedOpportunity = $this->controller->requestedEntity; //Tive que chamar o controller para poder requisitar a entity
            $opportunity = $requestedOpportunity->id;
            if(($requestedOpportunity->canUser('@control')) && in_array($requestedOpportunity->id,$opportunities_ids) ) {
                $app->view->enqueueScript('app', 'aldirblanc', 'aldirblanc/app.js');
                $this->part('validador-financeiro/csv-button', ['opportunity' => $opportunity, 'plugin_aldirblanc' => $plugin_aldirblanc, 'plugin_validador' => $plugin_validador]);
            }
        });

        // uploads de CSVs 
        $app->hook('template(opportunity.<<single|edit>>.sidebar-right):end', function () use($plugin_aldirblanc, $plugin_validador) {
            $opportunity = $this->controller->requestedEntity; 
            if($opportunity->canUser('@control')){
                $this->part('validador-financeiro/validador-uploads', ['entity' => $opportunity, 'plugin_aldirblanc' => $plugin_aldirblanc, 'plugin_validador' => $plugin_validador]);
            }
        });

        parent::_init();
    }

    function register()
    {
        $app = App::i();
        $slug = $this->getSlug();

        $this->registerOpportunityMetadata($slug . '_processed_files', [
            'label' => 'Arquivos do Validador Financeiro Processados',
            'type' => 'json',
            'private' => true,
            'default_value' => '{}'
        ]);

        $this->registerRegistrationMetadata($slug . '_filename', [
            'label' => 'Nome do arquivo de retorno do validador financeiro',
            'type' => 'string',
            'private' => true,
        ]);

        $this->registerRegistrationMetadata($slug . '_raw', [
            'label' => 'Validador Financeiro raw data (csv row)',
            'type' => 'json',
            'private' => true,
            'default_value' => '{}'
        ]);

        $this->registerRegistrationMetadata($slug . '_processed', [
            'label' => 'Validador Financeiro processed data',
            'type' => 'json',
            'private' => true,
            'default_value' => '{}'
        ]);

        $file_group_definition = new \MapasCulturais\Definitions\FileGroup($slug, ['^text/csv$'], 'O arquivo enviado não é um csv.',false,null,true);
        $app->registerFileGroup('opportunity', $file_group_definition);

        parent::register();

        $app->controller($slug)->plugin = $this;
    }

    function getName(): string
    {
        return 'Validador Financeiro';
    }

    function getSlug(): string
    {
        return 'financeiro';
    }

    function getControllerClassname(): string
    {
        return Controller::class;
    }

    function isRegistrationEligible(Registration $registration): bool
    {
        return true;
    }
}
