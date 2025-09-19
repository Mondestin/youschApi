<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdminAcademics\Venue;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $venues = [
            // Bâtiment Principal - Salles de classe
            [
                'name' => 'Salle 101',
                'description' => 'Salle de classe standard avec tableau blanc et projecteur, climatisée',
                'capacity' => 30,
                'location' => 'Bâtiment Principal - Premier Étage',
                'type' => 'classroom',
                'is_active' => true,
            ],
            [
                'name' => 'Salle 102',
                'description' => 'Salle de classe standard avec tableau interactif et système audio',
                'capacity' => 25,
                'location' => 'Bâtiment Principal - Premier Étage',
                'type' => 'classroom',
                'is_active' => true,
            ],
            [
                'name' => 'Salle 103',
                'description' => 'Petite salle de classe pour cours spécialisés',
                'capacity' => 20,
                'location' => 'Bâtiment Principal - Premier Étage',
                'type' => 'classroom',
                'is_active' => true,
            ],
            [
                'name' => 'Salle 201',
                'description' => 'Grande salle de classe pour cours avancés avec équipement multimédia',
                'capacity' => 40,
                'location' => 'Bâtiment Principal - Deuxième Étage',
                'type' => 'classroom',
                'is_active' => true,
            ],
            [
                'name' => 'Salle 202',
                'description' => 'Salle de classe standard avec tableau blanc et projecteur',
                'capacity' => 35,
                'location' => 'Bâtiment Principal - Deuxième Étage',
                'type' => 'classroom',
                'is_active' => true,
            ],
            [
                'name' => 'Salle 203',
                'description' => 'Salle de classe avec disposition en table ronde pour discussions',
                'capacity' => 15,
                'location' => 'Bâtiment Principal - Deuxième Étage',
                'type' => 'classroom',
                'is_active' => true,
            ],
            [
                'name' => 'Amphithéâtre 301',
                'description' => 'Grand amphithéâtre avec sièges en gradins',
                'capacity' => 60,
                'location' => 'Bâtiment Principal - Troisième Étage',
                'type' => 'classroom',
                'is_active' => true,
            ],
            [
                'name' => 'Salle 302',
                'description' => 'Salle de classe standard avec tableau interactif',
                'capacity' => 30,
                'location' => 'Bâtiment Principal - Troisième Étage',
                'type' => 'classroom',
                'is_active' => true,
            ],

            // Bâtiment des Sciences - Laboratoires
            [
                'name' => 'Laboratoire de Physique 1',
                'description' => 'Laboratoire de physique avec équipement expérimental et équipement de sécurité',
                'capacity' => 15,
                'location' => 'Bâtiment des Sciences - Premier Étage',
                'type' => 'laboratory',
                'is_active' => true,
            ],
            [
                'name' => 'Laboratoire de Physique 2',
                'description' => 'Laboratoire de physique avancé avec instruments spécialisés',
                'capacity' => 12,
                'location' => 'Bâtiment des Sciences - Premier Étage',
                'type' => 'laboratory',
                'is_active' => true,
            ],
            [
                'name' => 'Laboratoire de Chimie 1',
                'description' => 'Laboratoire de chimie avec hottes aspirantes et équipement de sécurité',
                'capacity' => 18,
                'location' => 'Bâtiment des Sciences - Premier Étage',
                'type' => 'laboratory',
                'is_active' => true,
            ],
            [
                'name' => 'Laboratoire de Chimie 2',
                'description' => 'Laboratoire de chimie avancé avec instruments d\'analyse',
                'capacity' => 16,
                'location' => 'Bâtiment des Sciences - Premier Étage',
                'type' => 'laboratory',
                'is_active' => true,
            ],
            [
                'name' => 'Laboratoire de Biologie 1',
                'description' => 'Laboratoire de biologie avec microscopes et stockage d\'échantillons',
                'capacity' => 20,
                'location' => 'Bâtiment des Sciences - Deuxième Étage',
                'type' => 'laboratory',
                'is_active' => true,
            ],
            [
                'name' => 'Laboratoire de Biologie 2',
                'description' => 'Laboratoire de biologie avancé avec équipement de biologie moléculaire',
                'capacity' => 14,
                'location' => 'Bâtiment des Sciences - Deuxième Étage',
                'type' => 'laboratory',
                'is_active' => true,
            ],

            // Bâtiment Technologique - Salles informatiques
            [
                'name' => 'Salle Informatique A',
                'description' => 'Laboratoire informatique avec 20 postes de travail et internet haut débit',
                'capacity' => 20,
                'location' => 'Bâtiment Technologique - Rez-de-chaussée',
                'type' => 'laboratory',
                'is_active' => true,
            ],
            [
                'name' => 'Salle Informatique B',
                'description' => 'Laboratoire informatique avec 25 postes de travail et logiciels spécialisés',
                'capacity' => 25,
                'location' => 'Bâtiment Technologique - Rez-de-chaussée',
                'type' => 'laboratory',
                'is_active' => true,
            ],
            [
                'name' => 'Salle Informatique C',
                'description' => 'Laboratoire informatique avancé avec postes de travail Mac',
                'capacity' => 15,
                'location' => 'Bâtiment Technologique - Premier Étage',
                'type' => 'laboratory',
                'is_active' => true,
            ],
            [
                'name' => 'Laboratoire Réseaux',
                'description' => 'Laboratoire de réseaux avec équipement Cisco et serveurs',
                'capacity' => 12,
                'location' => 'Bâtiment Technologique - Premier Étage',
                'type' => 'laboratory',
                'is_active' => true,
            ],

            // Auditoriums et grandes salles
            [
                'name' => 'Grand Auditorium',
                'description' => 'Grand auditorium avec scène, système audio et écran de projection',
                'capacity' => 200,
                'location' => 'Bâtiment Principal - Rez-de-chaussée',
                'type' => 'auditorium',
                'is_active' => true,
            ],
            [
                'name' => 'Petit Auditorium',
                'description' => 'Auditorium de taille moyenne pour présentations et séminaires',
                'capacity' => 100,
                'location' => 'Bâtiment Principal - Rez-de-chaussée',
                'type' => 'auditorium',
                'is_active' => true,
            ],
            [
                'name' => 'Salle de Conférence',
                'description' => 'Salle de conférence professionnelle avec équipement de visioconférence',
                'capacity' => 80,
                'location' => 'Bâtiment Administration - Rez-de-chaussée',
                'type' => 'auditorium',
                'is_active' => true,
            ],

            // Salles de réunion
            [
                'name' => 'Salle de Réunion 1',
                'description' => 'Petite salle de réunion pour discussions de groupe et présentations',
                'capacity' => 12,
                'location' => 'Bâtiment Administration - Premier Étage',
                'type' => 'meeting_room',
                'is_active' => true,
            ],
            [
                'name' => 'Salle de Réunion 2',
                'description' => 'Salle de réunion moyenne avec équipement de visioconférence',
                'capacity' => 20,
                'location' => 'Bâtiment Administration - Premier Étage',
                'type' => 'meeting_room',
                'is_active' => true,
            ],
            [
                'name' => 'Salle du Conseil',
                'description' => 'Salle du conseil exécutif avec mobilier haut de gamme et équipement AV',
                'capacity' => 16,
                'location' => 'Bâtiment Administration - Deuxième Étage',
                'type' => 'meeting_room',
                'is_active' => true,
            ],
            [
                'name' => 'Salle de Séminaire A',
                'description' => 'Salle de séminaire avec disposition en U',
                'capacity' => 25,
                'location' => 'Bâtiment Principal - Rez-de-chaussée',
                'type' => 'meeting_room',
                'is_active' => true,
            ],
            [
                'name' => 'Salle de Séminaire B',
                'description' => 'Salle de séminaire avec sièges en style théâtre',
                'capacity' => 30,
                'location' => 'Bâtiment Principal - Rez-de-chaussée',
                'type' => 'meeting_room',
                'is_active' => true,
            ],

            // Sports et éducation physique
            [
                'name' => 'Gymnase Principal',
                'description' => 'Grande salle de sport pour cours d\'éducation physique et événements',
                'capacity' => 50,
                'location' => 'Complexe Sportif',
                'type' => 'gymnasium',
                'is_active' => true,
            ],
            [
                'name' => 'Petit Gymnase',
                'description' => 'Petit gymnase pour activités d\'éducation physique spécialisées',
                'capacity' => 25,
                'location' => 'Complexe Sportif',
                'type' => 'gymnasium',
                'is_active' => true,
            ],
            [
                'name' => 'Studio de Danse',
                'description' => 'Studio de danse avec miroirs et système audio',
                'capacity' => 20,
                'location' => 'Complexe Sportif',
                'type' => 'gymnasium',
                'is_active' => true,
            ],
            [
                'name' => 'Centre de Fitness',
                'description' => 'Centre de fitness avec équipement d\'exercice',
                'capacity' => 15,
                'location' => 'Complexe Sportif',
                'type' => 'gymnasium',
                'is_active' => true,
            ],

            // Bibliothèque et espaces d'étude
            [
                'name' => 'Salle d\'Étude A',
                'description' => 'Salle d\'étude silencieuse dans la bibliothèque avec bureaux individuels',
                'capacity' => 8,
                'location' => 'Bâtiment Bibliothèque - Deuxième Étage',
                'type' => 'library',
                'is_active' => true,
            ],
            [
                'name' => 'Salle d\'Étude B',
                'description' => 'Salle d\'étude de groupe avec grande table et tableau blanc',
                'capacity' => 12,
                'location' => 'Bâtiment Bibliothèque - Deuxième Étage',
                'type' => 'library',
                'is_active' => true,
            ],
            [
                'name' => 'Salle d\'Étude C',
                'description' => 'Salle d\'étude silencieuse avec carrels individuels',
                'capacity' => 6,
                'location' => 'Bâtiment Bibliothèque - Troisième Étage',
                'type' => 'library',
                'is_active' => true,
            ],
            [
                'name' => 'Salle de Lecture',
                'description' => 'Grande salle de lecture avec sièges confortables',
                'capacity' => 40,
                'location' => 'Bâtiment Bibliothèque - Premier Étage',
                'type' => 'library',
                'is_active' => true,
            ],

            // Lieux spécialisés
            [
                'name' => 'Atelier d\'Art 1',
                'description' => 'Atelier d\'art avec chevalets, tables de dessin et rangement',
                'capacity' => 18,
                'location' => 'Bâtiment des Arts - Premier Étage',
                'type' => 'other',
                'is_active' => true,
            ],
            [
                'name' => 'Atelier d\'Art 2',
                'description' => 'Atelier de céramique et sculpture avec four',
                'capacity' => 12,
                'location' => 'Bâtiment des Arts - Premier Étage',
                'type' => 'other',
                'is_active' => true,
            ],
            [
                'name' => 'Salle de Musique 1',
                'description' => 'Salle de musique avec piano et équipement audio',
                'capacity' => 15,
                'location' => 'Bâtiment des Arts - Deuxième Étage',
                'type' => 'other',
                'is_active' => true,
            ],
            [
                'name' => 'Salle de Musique 2',
                'description' => 'Salle de répétition avec instruments et système audio',
                'capacity' => 20,
                'location' => 'Bâtiment des Arts - Deuxième Étage',
                'type' => 'other',
                'is_active' => true,
            ],
            [
                'name' => 'Laboratoire de Langues',
                'description' => 'Laboratoire de langues avec équipement audio-visuel',
                'capacity' => 24,
                'location' => 'Centre de Langues - Premier Étage',
                'type' => 'laboratory',
                'is_active' => true,
            ],
            [
                'name' => 'Centre Média',
                'description' => 'Centre média avec équipement de montage vidéo et fond vert',
                'capacity' => 16,
                'location' => 'Bâtiment Technologique - Deuxième Étage',
                'type' => 'laboratory',
                'is_active' => true,
            ],

            // Lieux inactifs (pour les tests)
            [
                'name' => 'Ancien Laboratoire 1',
                'description' => 'Laboratoire obsolète en cours de rénovation',
                'capacity' => 10,
                'location' => 'Ancien Bâtiment - Rez-de-chaussée',
                'type' => 'laboratory',
                'is_active' => false,
            ],
            [
                'name' => 'Salle de Stockage A',
                'description' => 'Salle de stockage temporaire, non disponible pour les cours',
                'capacity' => 5,
                'location' => 'Bâtiment Principal - Sous-sol',
                'type' => 'other',
                'is_active' => false,
            ],
        ];

        foreach ($venues as $venue) {
            Venue::create($venue);
        }
    }
}
