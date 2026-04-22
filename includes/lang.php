<?php
// =====================================================================
// Système de traduction FR / EN
// La traduction se fait côté client (instantanée, sans rechargement)
// Le serveur sert les textes FR par défaut (initial render),
// et le JS bascule via les attributs data-i18n
// =====================================================================
if (session_status() === PHP_SESSION_NONE) session_start();

$LANG = $_COOKIE['lang'] ?? 'fr';
if (!in_array($LANG, ['fr','en'])) $LANG = 'fr';

$TRANSLATIONS = [
    'fr' => [
        'nav.home'          => 'Accueil',
        'nav.realisations'  => 'Nos réalisations',
        'nav.donate'        => 'Faire un don',
        'nav.contact'       => 'Nous contacter',

        'hero.badge'        => 'Urgence humanitaire en cours',
        'hero.title'        => 'Ensemble, sauvons des enfants de la famine',
        'hero.subtitle'     => 'Au Cameroun et en Afrique subsaharienne, des millions d\'enfants meurent de faim chaque année. Votre don, même modeste, peut sauver une vie aujourd\'hui.',
        'hero.cta'          => 'Faire un don maintenant',
        'hero.cta_secondary'=> 'Voir nos réalisations',

        'transparency.title'=> 'Transparence financière',
        'transparency.desc' => 'Suivez en temps réel l\'évolution de notre collecte.',
        'stats.collected'   => 'Collectés',
        'stats.goal'        => 'Objectif',
        'stats.donors'      => 'Donateurs',
        'stats.percent'     => 'Atteint',

        'project.current'   => 'Projet en cours',

        'reach.title'       => 'Nos réalisations à travers l\'Afrique',
        'reach.subtitle'    => 'Grâce à des donateurs comme vous, nous avons déjà aidé des centaines de milliers de personnes dans plus de 10 pays.',
        'reach.people'      => 'personnes aidées',

        'share.title'       => 'Partagez pour aider, même sans donner',
        'share.subtitle'    => 'Un partage peut sauver une vie. Faites passer le message.',

        'contact.title'     => 'Nous contacter',
        'contact.telegram'  => 'Telegram',
        'contact.email'     => 'Email',

        'footer.rights'     => 'Tous droits réservés.',
        'footer.tagline'    => 'Chaque don compte. Chaque vie compte.',

        'theme.toggle'      => 'Thème',
        'lang.toggle'       => 'EN',
    ],
    'en' => [
        'nav.home'          => 'Home',
        'nav.realisations'  => 'Our impact',
        'nav.donate'        => 'Donate',
        'nav.contact'       => 'Contact us',

        'hero.badge'        => 'Ongoing humanitarian emergency',
        'hero.title'        => 'Together, let\'s save children from famine',
        'hero.subtitle'     => 'In Cameroon and sub-Saharan Africa, millions of children die of hunger every year. Your donation, no matter how small, can save a life today.',
        'hero.cta'          => 'Donate now',
        'hero.cta_secondary'=> 'See our impact',

        'transparency.title'=> 'Financial transparency',
        'transparency.desc' => 'Track our fundraising progress in real time.',
        'stats.collected'   => 'Collected',
        'stats.goal'        => 'Goal',
        'stats.donors'      => 'Donors',
        'stats.percent'     => 'Reached',

        'project.current'   => 'Current project',

        'reach.title'       => 'Our impact across Africa',
        'reach.subtitle'    => 'Thanks to donors like you, we have already helped hundreds of thousands of people in over 10 countries.',
        'reach.people'      => 'people helped',

        'share.title'       => 'Share to help, even without donating',
        'share.subtitle'    => 'One share can save a life. Spread the word.',

        'contact.title'     => 'Contact us',
        'contact.telegram'  => 'Telegram',
        'contact.email'     => 'Email',

        'footer.rights'     => 'All rights reserved.',
        'footer.tagline'    => 'Every donation matters. Every life matters.',

        'theme.toggle'      => 'Theme',
        'lang.toggle'       => 'FR',
    ],
];

function t(string $key): string {
    global $TRANSLATIONS, $LANG;
    return $TRANSLATIONS[$LANG][$key] ?? ($TRANSLATIONS['fr'][$key] ?? $key);
}

function currentLang(): string { global $LANG; return $LANG; }
function otherLang():   string { global $LANG; return $LANG === 'fr' ? 'en' : 'fr'; }

/** Expose toutes les traductions à JavaScript pour la bascule instantanée */
function dumpTranslationsJS(): string {
    global $TRANSLATIONS, $LANG;
    return 'window.__I18N = ' . json_encode($TRANSLATIONS, JSON_UNESCAPED_UNICODE)
         . '; window.__LANG = ' . json_encode($LANG) . ';';
}
