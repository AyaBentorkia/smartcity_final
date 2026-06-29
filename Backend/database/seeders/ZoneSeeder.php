<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Zone;
use App\Models\Municipality;

/**
 * ZoneSeeder
 * Format : {delegation}-nord, {delegation}-sud, {delegation}-centre, {delegation}-est, {delegation}-ouest
 * Coordonnées GPS réelles, rayon_km adapté à la taille de la délégation.
 * Monastir : 13 délégations × 3-5 zones = ~50 zones
 * Sousse   : 15 délégations × 3-5 zones = ~60 zones
 */
class ZoneSeeder extends Seeder
{
    public function run(): void
    {
        // Helper : résoudre une municipality par son nom complet
        $mun = fn(string $name) => Municipality::where('name', "Municipalité de {$name}")->first();

        // ── Monastir ───────────────────────────────────────────────────
        $munMonastir       = $mun('Monastir');
        $munJemmal         = $mun('Jemmal');
        $munMoknine        = $mun('Moknine');
        $munKsarHellal     = $mun('Ksar Hellal');
        $munKsibet         = $mun('Ksibet el-Médiouni');
        $munBembla         = $mun('Bembla');
        $munTeboulba       = $mun('Téboulba');
        $munZeramdine      = $mun('Zéramdine');
        $munSayada         = $mun('Sayada-Lamta-Bou Hajar');
        $munBekalta        = $mun('Bekalta');
        $munSahline        = $mun('Sahline');
        $munOuerdanine     = $mun('Ouerdanine');
        $munBeniHassen     = $mun('Beni Hassen');

        // ── Sousse ─────────────────────────────────────────────────────
        $munSousseMedian   = $mun('Sousse Médina');
        $munSousseRiadh    = $mun('Sousse Riadh');
        $munSousseJawhara  = $mun('Sousse Jawhara');
        $munSousseSidi     = $mun('Sousse Sidi Abdelhamid');
        $munHammamSousse   = $mun('Hammam Sousse');
        $munAkouda         = $mun('Akouda');
        $munKalaKebira     = $mun('Kalâa Kebira');
        $munMsaken         = Municipality::where('name', "Municipalité de M'saken")->first();
        $munKalaSeghira    = $mun('Kalâa Seghira');
        $munEnfida         = $mun('Enfida');
        $munBouficha       = $mun('Bouficha');
        $munZaouiet        = $mun('Zaouiet Ksibet Thrayet');
        $munSidiBouAli     = $mun('Sidi Bou Ali');
        $munKondar         = $mun('Kondar');
        $munSidiElHani     = $mun('Sidi El Hani');
        $munHergla         = $mun('Hergla');

        $zones = [
            // ═══════════════════════════════════════════════════════════
            // MONASTIR — centre-ville côtier, aéroport, médina
            // ═══════════════════════════════════════════════════════════
            ['municipality_id'=>$munMonastir?->id, 'name'=>'Monastir-Centre', 'latitude_center'=>35.7643, 'longitude_center'=>10.8113, 'rayon_km'=>1.5, 'description'=>'Centre-ville de Monastir — avenue Bourguiba, médina, ribat.'],
            ['municipality_id'=>$munMonastir?->id, 'name'=>'Monastir-Nord',   'latitude_center'=>35.7780, 'longitude_center'=>10.8000, 'rayon_km'=>2.0, 'description'=>'Zone nord de Monastir — aéroport, zones résidentielles.'],
            ['municipality_id'=>$munMonastir?->id, 'name'=>'Monastir-Sud',    'latitude_center'=>35.7500, 'longitude_center'=>10.8200, 'rayon_km'=>2.0, 'description'=>'Zone sud de Monastir — port de pêche, plages sud.'],
            ['municipality_id'=>$munMonastir?->id, 'name'=>'Monastir-Est',    'latitude_center'=>35.7643, 'longitude_center'=>10.8350, 'rayon_km'=>1.8, 'description'=>'Zone est de Monastir — corniche, hôtels, plage.'],
            ['municipality_id'=>$munMonastir?->id, 'name'=>'Monastir-Ouest',  'latitude_center'=>35.7643, 'longitude_center'=>10.7900, 'rayon_km'=>1.8, 'description'=>'Zone ouest de Monastir — quartiers populaires, marché.'],
            ['municipality_id'=>$munMonastir?->id, 'name'=>'QUEENS',  'latitude_center'=>40.7282, 'longitude_center'=>-73.7949, 'rayon_km'=>1.8, 'description'=>'Zone ouest de Monastir — quartiers populaires, marché.'],

            // JEMMAL — délégation agricole et industrielle
            ['municipality_id'=>$munJemmal?->id,   'name'=>'Jemmal-Centre',   'latitude_center'=>35.6221, 'longitude_center'=>10.7610, 'rayon_km'=>1.5, 'description'=>'Centre de Jemmal — marché, équipements publics.'],
            ['municipality_id'=>$munJemmal?->id,   'name'=>'Jemmal-Nord',     'latitude_center'=>35.6380, 'longitude_center'=>10.7600, 'rayon_km'=>2.0, 'description'=>'Zone nord de Jemmal — zone industrielle, entrepôts.'],
            ['municipality_id'=>$munJemmal?->id,   'name'=>'Jemmal-Sud',      'latitude_center'=>35.6060, 'longitude_center'=>10.7620, 'rayon_km'=>2.0, 'description'=>'Zone sud de Jemmal — zones agricoles, oliveraies.'],
            ['municipality_id'=>$munJemmal?->id,   'name'=>'Jemmal-Ouest',    'latitude_center'=>35.6221, 'longitude_center'=>10.7400, 'rayon_km'=>2.0, 'description'=>'Zone ouest de Jemmal — cités résidentielles.'],

            // MOKNINE — ville industrielle textile
            ['municipality_id'=>$munMoknine?->id,  'name'=>'Moknine-Centre',  'latitude_center'=>35.6422, 'longitude_center'=>10.9015, 'rayon_km'=>1.5, 'description'=>'Centre de Moknine — médina, souks textile.'],
            ['municipality_id'=>$munMoknine?->id,  'name'=>'Moknine-Nord',    'latitude_center'=>35.6580, 'longitude_center'=>10.9000, 'rayon_km'=>2.0, 'description'=>'Zone nord de Moknine — quartiers résidentiels nord.'],
            ['municipality_id'=>$munMoknine?->id,  'name'=>'Moknine-Sud',     'latitude_center'=>35.6260, 'longitude_center'=>10.9030, 'rayon_km'=>2.0, 'description'=>'Zone sud de Moknine — zone industrielle textile.'],
            ['municipality_id'=>$munMoknine?->id,  'name'=>'Moknine-Est',     'latitude_center'=>35.6422, 'longitude_center'=>10.9200, 'rayon_km'=>2.0, 'description'=>'Zone est de Moknine — accès côtier, plages.'],
            ['municipality_id'=>$munMoknine?->id,  'name'=>'Moknine-Ouest',   'latitude_center'=>35.6422, 'longitude_center'=>10.8820, 'rayon_km'=>2.0, 'description'=>'Zone ouest de Moknine — zones agricoles.'],

            // KSAR HELLAL — capitale du textile tunisien
            ['municipality_id'=>$munKsarHellal?->id,'name'=>'Ksar Hellal-Centre','latitude_center'=>35.6474,'longitude_center'=>10.8906,'rayon_km'=>1.5,'description'=>'Centre de Ksar Hellal — marché, commerces textile.'],
            ['municipality_id'=>$munKsarHellal?->id,'name'=>'Ksar Hellal-Nord',  'latitude_center'=>35.6630,'longitude_center'=>10.8880,'rayon_km'=>1.8,'description'=>'Zone nord de Ksar Hellal — cités ouvrières, usines.'],
            ['municipality_id'=>$munKsarHellal?->id,'name'=>'Ksar Hellal-Sud',   'latitude_center'=>35.6310,'longitude_center'=>10.8930,'rayon_km'=>1.8,'description'=>'Zone sud de Ksar Hellal — zone industrielle.'],
            ['municipality_id'=>$munKsarHellal?->id,'name'=>'Ksar Hellal-Ouest', 'latitude_center'=>35.6474,'longitude_center'=>10.8700,'rayon_km'=>1.8,'description'=>'Zone ouest de Ksar Hellal — quartiers résidentiels.'],

            // KSIBET EL-MÉDIOUNI — zone côtière
            ['municipality_id'=>$munKsibet?->id,   'name'=>'Ksibet el-Médiouni-Centre','latitude_center'=>35.6900,'longitude_center'=>10.7800,'rayon_km'=>1.5,'description'=>'Centre de Ksibet el-Médiouni.'],
            ['municipality_id'=>$munKsibet?->id,   'name'=>'Ksibet el-Médiouni-Nord',  'latitude_center'=>35.7050,'longitude_center'=>10.7780,'rayon_km'=>1.8,'description'=>'Zone nord — accès vers Monastir.'],
            ['municipality_id'=>$munKsibet?->id,   'name'=>'Ksibet el-Médiouni-Sud',   'latitude_center'=>35.6750,'longitude_center'=>10.7820,'rayon_km'=>1.8,'description'=>'Zone sud — zones agricoles côtières.'],
            ['municipality_id'=>$munKsibet?->id,   'name'=>'Ksibet el-Médiouni-Est',   'latitude_center'=>35.6900,'longitude_center'=>10.7950,'rayon_km'=>1.5,'description'=>'Zone est — plage, front de mer.'],

            // BEMBLA
            ['municipality_id'=>$munBembla?->id,   'name'=>'Bembla-Centre',   'latitude_center'=>35.7200, 'longitude_center'=>10.7600, 'rayon_km'=>1.5, 'description'=>'Centre de Bembla.'],
            ['municipality_id'=>$munBembla?->id,   'name'=>'Bembla-Nord',     'latitude_center'=>35.7350, 'longitude_center'=>10.7580, 'rayon_km'=>1.8, 'description'=>'Zone nord de Bembla — vers Monastir.'],
            ['municipality_id'=>$munBembla?->id,   'name'=>'Bembla-Sud',      'latitude_center'=>35.7050, 'longitude_center'=>10.7620, 'rayon_km'=>1.8, 'description'=>'Zone sud de Bembla — zones rurales.'],

            // TÉBOULBA — port de pêche
            ['municipality_id'=>$munTeboulba?->id, 'name'=>'Téboulba-Centre', 'latitude_center'=>35.6431, 'longitude_center'=>10.9764, 'rayon_km'=>1.5, 'description'=>'Centre de Téboulba — marché, port de pêche.'],
            ['municipality_id'=>$munTeboulba?->id, 'name'=>'Téboulba-Nord',   'latitude_center'=>35.6580, 'longitude_center'=>10.9750, 'rayon_km'=>1.8, 'description'=>'Zone nord de Téboulba.'],
            ['municipality_id'=>$munTeboulba?->id, 'name'=>'Téboulba-Est',    'latitude_center'=>35.6431, 'longitude_center'=>10.9950, 'rayon_km'=>1.5, 'description'=>'Zone est — côte, port, plages.'],
            ['municipality_id'=>$munTeboulba?->id, 'name'=>'Téboulba-Ouest',  'latitude_center'=>35.6431, 'longitude_center'=>10.9580, 'rayon_km'=>1.8, 'description'=>'Zone ouest — zones agricoles.'],

            // ZÉRAMDINE
            ['municipality_id'=>$munZeramdine?->id,'name'=>'Zéramdine-Centre','latitude_center'=>35.5967,'longitude_center'=>10.7319,'rayon_km'=>1.5,'description'=>'Centre de Zéramdine.'],
            ['municipality_id'=>$munZeramdine?->id,'name'=>'Zéramdine-Nord',  'latitude_center'=>35.6120,'longitude_center'=>10.7310,'rayon_km'=>2.0,'description'=>'Zone nord de Zéramdine — vers Jemmal.'],
            ['municipality_id'=>$munZeramdine?->id,'name'=>'Zéramdine-Sud',   'latitude_center'=>35.5810,'longitude_center'=>10.7330,'rayon_km'=>2.0,'description'=>'Zone sud de Zéramdine — zones rurales.'],

            // SAYADA-LAMTA-BOU HAJAR — zone côtière touristique
            ['municipality_id'=>$munSayada?->id,   'name'=>'Sayada-Lamta-Bou Hajar-Centre','latitude_center'=>35.6600,'longitude_center'=>10.7450,'rayon_km'=>1.5,'description'=>'Centre de Sayada — marché local.'],
            ['municipality_id'=>$munSayada?->id,   'name'=>'Sayada-Lamta-Bou Hajar-Nord',  'latitude_center'=>35.6750,'longitude_center'=>10.7420,'rayon_km'=>1.8,'description'=>'Zone nord — Lamta, vestiges romains.'],
            ['municipality_id'=>$munSayada?->id,   'name'=>'Sayada-Lamta-Bou Hajar-Sud',   'latitude_center'=>35.6450,'longitude_center'=>10.7480,'rayon_km'=>1.8,'description'=>'Zone sud — Bou Hajar, zones rurales.'],
            ['municipality_id'=>$munSayada?->id,   'name'=>'Sayada-Lamta-Bou Hajar-Est',   'latitude_center'=>35.6600,'longitude_center'=>10.7600,'rayon_km'=>1.5,'description'=>'Zone est — littoral, plages.'],

            // BEKALTA
            ['municipality_id'=>$munBekalta?->id,  'name'=>'Bekalta-Centre',  'latitude_center'=>35.6108,'longitude_center'=>11.0019,'rayon_km'=>2.0,'description'=>'Centre de Bekalta.'],
            ['municipality_id'=>$munBekalta?->id,  'name'=>'Bekalta-Nord',    'latitude_center'=>35.6260,'longitude_center'=>11.0010,'rayon_km'=>2.0,'description'=>'Zone nord de Bekalta — vers Moknine.'],
            ['municipality_id'=>$munBekalta?->id,  'name'=>'Bekalta-Est',     'latitude_center'=>35.6108,'longitude_center'=>11.0200,'rayon_km'=>1.8,'description'=>'Zone est — côte, plages isolées.'],

            // SAHLINE
            ['municipality_id'=>$munSahline?->id,  'name'=>'Sahline-Centre',  'latitude_center'=>35.7483,'longitude_center'=>10.7767,'rayon_km'=>1.5,'description'=>'Centre de Sahline.'],
            ['municipality_id'=>$munSahline?->id,  'name'=>'Sahline-Nord',    'latitude_center'=>35.7630,'longitude_center'=>10.7750,'rayon_km'=>1.8,'description'=>'Zone nord — vers Monastir.'],
            ['municipality_id'=>$munSahline?->id,  'name'=>'Sahline-Sud',     'latitude_center'=>35.7330,'longitude_center'=>10.7790,'rayon_km'=>1.8,'description'=>'Zone sud — zones résidentielles.'],

            // OUERDANINE
            ['municipality_id'=>$munOuerdanine?->id,'name'=>'Ouerdanine-Centre','latitude_center'=>35.6367,'longitude_center'=>10.7550,'rayon_km'=>2.0,'description'=>'Centre de Ouerdanine.'],
            ['municipality_id'=>$munOuerdanine?->id,'name'=>'Ouerdanine-Nord',  'latitude_center'=>35.6520,'longitude_center'=>10.7540,'rayon_km'=>2.0,'description'=>'Zone nord — vers Jemmal.'],
            ['municipality_id'=>$munOuerdanine?->id,'name'=>'Ouerdanine-Sud',   'latitude_center'=>35.6210,'longitude_center'=>10.7560,'rayon_km'=>2.0,'description'=>'Zone sud — zones rurales.'],

            // BENI HASSEN
            ['municipality_id'=>$munBeniHassen?->id,'name'=>'Beni Hassen-Centre','latitude_center'=>35.7050,'longitude_center'=>10.7200,'rayon_km'=>2.0,'description'=>'Centre de Beni Hassen.'],
            ['municipality_id'=>$munBeniHassen?->id,'name'=>'Beni Hassen-Nord',  'latitude_center'=>35.7200,'longitude_center'=>10.7190,'rayon_km'=>2.0,'description'=>'Zone nord — accès vers Monastir.'],
            ['municipality_id'=>$munBeniHassen?->id,'name'=>'Beni Hassen-Sud',   'latitude_center'=>35.6900,'longitude_center'=>10.7210,'rayon_km'=>2.0,'description'=>'Zone sud — zones agricoles.'],

            // ═══════════════════════════════════════════════════════════
            // SOUSSE — 4 délégations urbaines intra-muros
            // ═══════════════════════════════════════════════════════════

            // SOUSSE MÉDINA
            ['municipality_id'=>$munSousseMedian?->id, 'name'=>'Sousse Médina-Centre', 'latitude_center'=>35.8258,'longitude_center'=>10.6367,'rayon_km'=>0.8,'description'=>'Médina historique UNESCO — ribat, grande mosquée, remparts.'],
            ['municipality_id'=>$munSousseMedian?->id, 'name'=>'Sousse Médina-Nord',   'latitude_center'=>35.8310,'longitude_center'=>10.6360,'rayon_km'=>0.8,'description'=>'Secteur nord médina — Bab el-Gharbi, souks.'],
            ['municipality_id'=>$munSousseMedian?->id, 'name'=>'Sousse Médina-Est',    'latitude_center'=>35.8258,'longitude_center'=>10.6450,'rayon_km'=>0.8,'description'=>'Secteur est médina — front de mer, plage.'],

            // SOUSSE RIADH
            ['municipality_id'=>$munSousseRiadh?->id,  'name'=>'Sousse Riadh-Centre',  'latitude_center'=>35.8400,'longitude_center'=>10.6200,'rayon_km'=>1.5,'description'=>'Quartier Riadh — zone résidentielle moderne.'],
            ['municipality_id'=>$munSousseRiadh?->id,  'name'=>'Sousse Riadh-Nord',    'latitude_center'=>35.8550,'longitude_center'=>10.6180,'rayon_km'=>1.8,'description'=>'Zone nord Riadh — cités résidentielles.'],
            ['municipality_id'=>$munSousseRiadh?->id,  'name'=>'Sousse Riadh-Ouest',   'latitude_center'=>35.8400,'longitude_center'=>10.6050,'rayon_km'=>1.5,'description'=>'Zone ouest Riadh — quartiers populaires.'],
            ['municipality_id'=>$munSousseRiadh?->id,  'name'=>'Sousse Riadh-Est',     'latitude_center'=>35.8400,'longitude_center'=>10.6350,'rayon_km'=>1.5,'description'=>'Zone est Riadh — corniche, hôtels.'],

            // SOUSSE JAWHARA
            ['municipality_id'=>$munSousseJawhara?->id,'name'=>'Sousse Jawhara-Centre','latitude_center'=>35.8200,'longitude_center'=>10.6150,'rayon_km'=>1.5,'description'=>'Quartier Jawhara — commerces, université.'],
            ['municipality_id'=>$munSousseJawhara?->id,'name'=>'Sousse Jawhara-Nord',  'latitude_center'=>35.8350,'longitude_center'=>10.6130,'rayon_km'=>1.8,'description'=>'Zone nord Jawhara — vers médina.'],
            ['municipality_id'=>$munSousseJawhara?->id,'name'=>'Sousse Jawhara-Sud',   'latitude_center'=>35.8050,'longitude_center'=>10.6170,'rayon_km'=>1.8,'description'=>'Zone sud Jawhara — cités nouvelles.'],
            ['municipality_id'=>$munSousseJawhara?->id,'name'=>'Sousse Jawhara-Ouest', 'latitude_center'=>35.8200,'longitude_center'=>10.6000,'rayon_km'=>1.5,'description'=>'Zone ouest Jawhara — zone industrielle légère.'],

            // SOUSSE SIDI ABDELHAMID
            ['municipality_id'=>$munSousseSidi?->id,   'name'=>'Sousse Sidi Abdelhamid-Centre','latitude_center'=>35.8150,'longitude_center'=>10.6500,'rayon_km'=>1.5,'description'=>'Quartier Sidi Abdelhamid — zone mixte résidentielle-commerciale.'],
            ['municipality_id'=>$munSousseSidi?->id,   'name'=>'Sousse Sidi Abdelhamid-Nord',  'latitude_center'=>35.8280,'longitude_center'=>10.6490,'rayon_km'=>1.5,'description'=>'Zone nord — vers médina.'],
            ['municipality_id'=>$munSousseSidi?->id,   'name'=>'Sousse Sidi Abdelhamid-Sud',   'latitude_center'=>35.8020,'longitude_center'=>10.6510,'rayon_km'=>1.8,'description'=>'Zone sud — quartiers populaires.'],
            ['municipality_id'=>$munSousseSidi?->id,   'name'=>'Sousse Sidi Abdelhamid-Est',   'latitude_center'=>35.8150,'longitude_center'=>10.6650,'rayon_km'=>1.5,'description'=>'Zone est — plage, zone touristique.'],

            // HAMMAM SOUSSE — zone touristique côtière
            ['municipality_id'=>$munHammamSousse?->id, 'name'=>'Hammam Sousse-Centre', 'latitude_center'=>35.8614,'longitude_center'=>10.5986,'rayon_km'=>1.5,'description'=>'Centre de Hammam Sousse — commerces, gare.'],
            ['municipality_id'=>$munHammamSousse?->id, 'name'=>'Hammam Sousse-Nord',   'latitude_center'=>35.8760,'longitude_center'=>10.5970,'rayon_km'=>1.8,'description'=>'Zone nord — vers Akouda, grands hôtels.'],
            ['municipality_id'=>$munHammamSousse?->id, 'name'=>'Hammam Sousse-Sud',    'latitude_center'=>35.8460,'longitude_center'=>10.6000,'rayon_km'=>1.8,'description'=>'Zone sud — vers Sousse, cités résidentielles.'],
            ['municipality_id'=>$munHammamSousse?->id, 'name'=>'Hammam Sousse-Est',    'latitude_center'=>35.8614,'longitude_center'=>10.6100,'rayon_km'=>1.2,'description'=>'Zone est — front de mer, plages, hôtels 5 étoiles.'],

            // AKOUDA — zone balnéaire
            ['municipality_id'=>$munAkouda?->id,       'name'=>'Akouda-Centre',        'latitude_center'=>35.8783,'longitude_center'=>10.5733,'rayon_km'=>1.5,'description'=>'Centre de Akouda — commerces, marché.'],
            ['municipality_id'=>$munAkouda?->id,       'name'=>'Akouda-Nord',          'latitude_center'=>35.8930,'longitude_center'=>10.5720,'rayon_km'=>1.8,'description'=>'Zone nord de Akouda — résidentiel.'],
            ['municipality_id'=>$munAkouda?->id,       'name'=>'Akouda-Est',           'latitude_center'=>35.8783,'longitude_center'=>10.5880,'rayon_km'=>1.5,'description'=>'Zone est — plage, hôtels balnéaires.'],
            ['municipality_id'=>$munAkouda?->id,       'name'=>'Akouda-Ouest',         'latitude_center'=>35.8783,'longitude_center'=>10.5580,'rayon_km'=>1.8,'description'=>'Zone ouest — zones agricoles, oliveraies.'],

            // KALÂA KEBIRA
            ['municipality_id'=>$munKalaKebira?->id,   'name'=>'Kalâa Kebira-Centre',  'latitude_center'=>35.8650,'longitude_center'=>10.5400,'rayon_km'=>1.5,'description'=>'Centre de Kalâa Kebira — marché, mairie.'],
            ['municipality_id'=>$munKalaKebira?->id,   'name'=>'Kalâa Kebira-Nord',    'latitude_center'=>35.8800,'longitude_center'=>10.5380,'rayon_km'=>2.0,'description'=>'Zone nord — vers Akouda.'],
            ['municipality_id'=>$munKalaKebira?->id,   'name'=>'Kalâa Kebira-Sud',     'latitude_center'=>35.8500,'longitude_center'=>10.5420,'rayon_km'=>2.0,'description'=>'Zone sud — zone industrielle.'],
            ['municipality_id'=>$munKalaKebira?->id,   'name'=>'Kalâa Kebira-Ouest',   'latitude_center'=>35.8650,'longitude_center'=>10.5200,'rayon_km'=>2.0,'description'=>'Zone ouest — zones agricoles.'],

            // M'SAKEN — grande ville artisanale
            ['municipality_id'=>$munMsaken?->id,       'name'=>"M'saken-Centre",       'latitude_center'=>35.7311,'longitude_center'=>10.5706,'rayon_km'=>1.5,'description'=>"Centre de M'saken — médina, souks artisanaux."],
            ['municipality_id'=>$munMsaken?->id,       'name'=>"M'saken-Nord",         'latitude_center'=>35.7470,'longitude_center'=>10.5690,'rayon_km'=>2.0,'description'=>"Zone nord de M'saken — cités résidentielles."],
            ['municipality_id'=>$munMsaken?->id,       'name'=>"M'saken-Sud",          'latitude_center'=>35.7150,'longitude_center'=>10.5720,'rayon_km'=>2.0,'description'=>"Zone sud de M'saken — zones industrielles."],
            ['municipality_id'=>$munMsaken?->id,       'name'=>"M'saken-Est",          'latitude_center'=>35.7311,'longitude_center'=>10.5900,'rayon_km'=>2.0,'description'=>'Zone est — zones rurales, agriculture.'],
            ['municipality_id'=>$munMsaken?->id,       'name'=>"M'saken-Ouest",        'latitude_center'=>35.7311,'longitude_center'=>10.5500,'rayon_km'=>2.0,'description'=>'Zone ouest — oliveraies, zone rurale.'],

            // KALÂA SEGHIRA
            ['municipality_id'=>$munKalaSeghira?->id,  'name'=>'Kalâa Seghira-Centre', 'latitude_center'=>35.8386,'longitude_center'=>10.5494,'rayon_km'=>1.5,'description'=>'Centre de Kalâa Seghira.'],
            ['municipality_id'=>$munKalaSeghira?->id,  'name'=>'Kalâa Seghira-Nord',   'latitude_center'=>35.8530,'longitude_center'=>10.5480,'rayon_km'=>1.8,'description'=>'Zone nord — vers Kalâa Kebira.'],
            ['municipality_id'=>$munKalaSeghira?->id,  'name'=>'Kalâa Seghira-Sud',    'latitude_center'=>35.8240,'longitude_center'=>10.5510,'rayon_km'=>1.8,'description'=>"Zone sud — vers M'saken."],
            ['municipality_id'=>$munKalaSeghira?->id,  'name'=>'Kalâa Seghira-Ouest',  'latitude_center'=>35.8386,'longitude_center'=>10.5300,'rayon_km'=>1.8,'description'=>'Zone ouest — zones agricoles.'],

            // ENFIDA — zone d'activités, aéroport
            ['municipality_id'=>$munEnfida?->id,       'name'=>'Enfida-Centre',        'latitude_center'=>36.1400,'longitude_center'=>10.3700,'rayon_km'=>2.0,'description'=>'Centre de Enfida — marché, équipements.'],
            ['municipality_id'=>$munEnfida?->id,       'name'=>'Enfida-Nord',          'latitude_center'=>36.1560,'longitude_center'=>10.3680,'rayon_km'=>2.5,'description'=>'Zone nord — aéroport international Hammamet-Enfida.'],
            ['municipality_id'=>$munEnfida?->id,       'name'=>'Enfida-Sud',           'latitude_center'=>36.1240,'longitude_center'=>10.3720,'rayon_km'=>2.5,'description'=>'Zone sud — zones agricoles, plaine.'],
            ['municipality_id'=>$munEnfida?->id,       'name'=>'Enfida-Est',           'latitude_center'=>36.1400,'longitude_center'=>10.3900,'rayon_km'=>2.5,'description'=>"Zone est — zone industrielle, parcs d'activités."],

            // BOUFICHA
            ['municipality_id'=>$munBouficha?->id,     'name'=>'Bouficha-Centre',      'latitude_center'=>36.2100,'longitude_center'=>10.5200,'rayon_km'=>2.0,'description'=>'Centre de Bouficha — marché, mairie.'],
            ['municipality_id'=>$munBouficha?->id,     'name'=>'Bouficha-Nord',        'latitude_center'=>36.2260,'longitude_center'=>10.5180,'rayon_km'=>2.5,'description'=>'Zone nord — vers Hammamet.'],
            ['municipality_id'=>$munBouficha?->id,     'name'=>'Bouficha-Sud',         'latitude_center'=>36.1940,'longitude_center'=>10.5220,'rayon_km'=>2.5,'description'=>'Zone sud — zones agricoles.'],
            ['municipality_id'=>$munBouficha?->id,     'name'=>'Bouficha-Est',         'latitude_center'=>36.2100,'longitude_center'=>10.5400,'rayon_km'=>2.0,'description'=>'Zone est — littoral, plage.'],

            // ZAOUIET KSIBET THRAYET
            ['municipality_id'=>$munZaouiet?->id,      'name'=>'Zaouiet Ksibet Thrayet-Centre','latitude_center'=>35.7100,'longitude_center'=>10.5950,'rayon_km'=>1.5,'description'=>'Centre de Zaouiet Ksibet Thrayet.'],
            ['municipality_id'=>$munZaouiet?->id,      'name'=>'Zaouiet Ksibet Thrayet-Nord',  'latitude_center'=>35.7250,'longitude_center'=>10.5930,'rayon_km'=>2.0,'description'=>"Zone nord — vers M'saken."],
            ['municipality_id'=>$munZaouiet?->id,      'name'=>'Zaouiet Ksibet Thrayet-Sud',   'latitude_center'=>35.6950,'longitude_center'=>10.5970,'rayon_km'=>2.0,'description'=>'Zone sud — zones rurales.'],
            ['municipality_id'=>$munZaouiet?->id,      'name'=>'Zaouiet Ksibet Thrayet-Est',   'latitude_center'=>35.7100,'longitude_center'=>10.6150,'rayon_km'=>2.0,'description'=>'Zone est — zones agricoles.'],

            // SIDI BOU ALI
            ['municipality_id'=>$munSidiBouAli?->id,   'name'=>'Sidi Bou Ali-Centre',  'latitude_center'=>36.0600,'longitude_center'=>10.4800,'rayon_km'=>2.0,'description'=>'Centre de Sidi Bou Ali.'],
            ['municipality_id'=>$munSidiBouAli?->id,   'name'=>'Sidi Bou Ali-Nord',    'latitude_center'=>36.0760,'longitude_center'=>10.4780,'rayon_km'=>2.5,'description'=>'Zone nord — vers Akouda.'],
            ['municipality_id'=>$munSidiBouAli?->id,   'name'=>'Sidi Bou Ali-Sud',     'latitude_center'=>36.0440,'longitude_center'=>10.4820,'rayon_km'=>2.5,'description'=>'Zone sud — zones agricoles.'],

            // KONDAR
            ['municipality_id'=>$munKondar?->id,       'name'=>'Kondar-Centre',        'latitude_center'=>35.6950,'longitude_center'=>10.5600,'rayon_km'=>2.0,'description'=>'Centre de Kondar.'],
            ['municipality_id'=>$munKondar?->id,       'name'=>'Kondar-Nord',          'latitude_center'=>35.7100,'longitude_center'=>10.5580,'rayon_km'=>2.0,'description'=>"Zone nord — vers M'saken."],
            ['municipality_id'=>$munKondar?->id,       'name'=>'Kondar-Ouest',         'latitude_center'=>35.6950,'longitude_center'=>10.5400,'rayon_km'=>2.0,'description'=>'Zone ouest — zones rurales.'],

            // SIDI EL HANI
            ['municipality_id'=>$munSidiElHani?->id,   'name'=>'Sidi El Hani-Centre',  'latitude_center'=>35.7900,'longitude_center'=>10.3700,'rayon_km'=>2.5,'description'=>'Centre de Sidi El Hani — zone intérieure.'],
            ['municipality_id'=>$munSidiElHani?->id,   'name'=>'Sidi El Hani-Nord',    'latitude_center'=>35.8060,'longitude_center'=>10.3680,'rayon_km'=>3.0,'description'=>'Zone nord — zones agricoles.'],
            ['municipality_id'=>$munSidiElHani?->id,   'name'=>'Sidi El Hani-Sud',     'latitude_center'=>35.7740,'longitude_center'=>10.3720,'rayon_km'=>3.0,'description'=>'Zone sud — chott, zones arides.'],

            // HERGLA — village côtier historique
            ['municipality_id'=>$munHergla?->id,       'name'=>'Hergla-Centre',        'latitude_center'=>36.0900,'longitude_center'=>10.4450,'rayon_km'=>1.5,'description'=>'Centre de Hergla — village historique, médina.'],
            ['municipality_id'=>$munHergla?->id,       'name'=>'Hergla-Nord',          'latitude_center'=>36.1050,'longitude_center'=>10.4430,'rayon_km'=>2.0,'description'=>'Zone nord — vers Akouda, côte.'],
            ['municipality_id'=>$munHergla?->id,       'name'=>'Hergla-Est',           'latitude_center'=>36.0900,'longitude_center'=>10.4600,'rayon_km'=>1.5,'description'=>'Zone est — plage, site archéologique.'],
        ];

        foreach ($zones as $z) {
            if ($z['municipality_id']) {
                Zone::firstOrCreate(['name' => $z['name']], $z);
            }
        }
    }
}