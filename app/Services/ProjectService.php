<?php

namespace App\Services;

class ProjectService
{
    public function __construct() {}

    public static function listPriorities(): array
    {
        return [
            'Menuiserie' => [
                'fenetre-pvc' => 'Fenêtre PVC',
                'fenetre-alu' => 'Fenêtre Alu',
                'fenetre-de-toit' => 'Fenêtre de toit',
                'volets-roulants' => 'Volets roulants',
                'volets-batants' => 'Volets batants',
                'volets-persienne' => 'Volets persienne',
                'porte-de-garage-sectionnel-plafond' => 'Porte de garage sectionnel plafond',
                'porte-de-garage-sectionnel-coulissante' => 'Porte de garage sectionnel coulissante',
                'porte-de-garage-sectionnel-roulante' => 'Porte de garage sectionnel roulante',
                'porte-de-garage-sectionnel-battante' => 'Porte de garage sectionnel battante',
                'porte-entree-pvc' => 'Porte entrée PVC',
                'porte-entree-alu' => 'Porte entrée Alu',
                'porte-de-service' => ' porte de service',
                'porte-interieur' => 'Porte intérieur',
                'portail-pvc' => 'Portail PVC',
                'portail-alu' => 'Portail Alu',
                'cloture' => 'Clôture',
                'store-exterieur' => 'Store extérieur',
                'store-interieur' => 'Store intérieur',
                'pergola' => 'Pergola',
                'carport' => 'Carport',
            ],
            'Isolation' => [
                'isolation-des-combles' => 'Isolation des combles',
                'isolation-des-sous-sols' => 'Isolation des sous-sols',
                'bardage-avec-isolation' => 'Bardage avec isolation',
                'bardage-sans-isolation' => 'Bardage sans isolation',
                'ravalement-avec-isolation' => 'Ravalement avec isolation',
                // "ravalement-sans-isolation" => "Ravalement sans isolation",
                'RPE' => 'RPE',
            ],
            'Plomberie Chauffage Sanitaires' => [
                'pompe-a-chaleur-air-eau' => 'Pompe à chaleur air-eau',
                'pompe-a-chaleur-air-air' => 'Pompe à chaleur air-air',
                'chauffage-a-inertie' => 'Chauffage à inertie',
                'chaudiere' => 'Chaudière',
                'poele' => 'Poêle',
                'poele-a-bois' => 'Poêle à bois',
                'poele-a-granule' => 'Poêle à granulé',
                'incert-a-granule' => 'Incert à granulé',
                'incert-a-bois' => ' Incert à bois',
                'ballon-eau-chaude' => 'Ballon eau chaude',
                'adoucisseur' => 'Adoucisseur',
                'vmc' => 'VMC',
                'salle-de-bain' => 'Salle de bain',
                'cabine-de-douche' => 'cabine de douche',
            ],
            'Toiture' => [
                'cache-moineau' => 'Cache moineau',
                'gouttiere' => 'Gouttière',
                'demoussage' => 'Démoussage',
            ],
            'Autres' => [
                'panneaux-photovoltaiques' => 'Panneaux photovoltaïques',
                'alarme' => 'Alarme',
                'borne-de-recharge' => 'Borne de recharge',
                'diagnostique' => 'Diagnostique',
            ],
        ];
    }

    public static function listProductsFlat(): array
    {
        $products = self::listPriorities();
        $flatList = [];
        foreach ($products as $category => $items) {
            foreach ($items as $key => $label) {
                $flatList[$key] = $label;
            }
        }

        return $flatList;
    }
}
