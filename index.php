<?php

// chargement des bibliothèques de fonctions
require_once('./php/bibli_gazette.php');
require_once('./php/bibli_generale.php');

// démarrage de la bufférisation des sorties
ob_start();

//démarrage ou reprise de la session
session_start();

affDebutMenuEntete('Le site de désinformation n°1 des étudiants en Licence Info', '.');

// génération du contenu de la page
affContenuL();

affPiedFin();

// envoi du buffer et fin de la bufferisation
// facultatif car fait automatiquement par PHP
ob_end_flush();


/*********************************************************
 *
 * Définitions des fonctions locales de la page
 *
 *********************************************************/
//_______________________________________________________________
/**
 * Affichage du contenu principal de la page
 *
 * @return  void
 */
function affContenuL() : void {

    // connexion au serveur de BdD
    $bd = bdConnect();

    // 3 derniers articles
    $sql = 'SELECT arID, arTitre
            FROM article
            ORDER BY arDatePubli DESC
            LIMIT 3';

    $tab0 = bdSelectArticlesL($bd, $sql);
    affBlocTroisArticlesL('&Agrave; la Une', $tab0);

    // 3 articles les plus commentés
    $sql = 'SELECT arID, arTitre
            FROM article
            LEFT OUTER JOIN commentaire ON coArticle = arID
            GROUP BY arID
            ORDER BY COUNT(coArticle) DESC, rand()
            LIMIT 3';
    $tab1 = bdSelectArticlesL($bd, $sql);

    affBlocTroisArticlesL('L\'info brûlante', $tab1);

    // 3 articles parmi les articles restants
    $sql = 'SELECT arID, arTitre FROM article
            WHERE arID NOT IN (' . implode(',', array_merge(array_keys($tab0), array_keys($tab1))) . ')
            ORDER BY rand()
            LIMIT 3';
    $tab2 = bdSelectArticlesL($bd, $sql);

    // Fermeture de la connexion au serveur de BdD, réalisée le plus tôt possible
    mysqli_close($bd);

    affBlocTroisArticlesL('Les incontournables', $tab2);

    affHoroscopeL();
}

//_______________________________________________________________
/**
 * Renvoie dans un tableau l'id et le titre des articles sélectionnés par une requête SQL
 *
 * @param  mysqli  $bd      référence pointant sur l'objet connecteur à la base de données
 * @param  string  $sql     la requête SQL à envoyer
 *
 * @return array            tableau (clé : id de l'article, valeur associée à la clé : titre de l'article)
 */
function bdSelectArticlesL(mysqli $bd, string $sql) : array {
    $res = bdSendRequest($bd, $sql);
    $a = [];
    while($t = mysqli_fetch_assoc($res)){
        $a[$t['arID']] = $t['arTitre'];
    }
    // Libération de la mémoire associée au résultat de la requête
    mysqli_free_result($res);
    return $a;
}


//_______________________________________________________________
/**
 * Affiche une vignette
 *
 * @param   int         id de l'article
 * @param   string      titre de l'article
 *
 * @return  void
 */
function affUnArticleL(int $id, string $titre) : void {
    $titre = htmlProtegerSorties($titre); // ATTENTION : protection contre les attaques XSS, à ne pas oublier !!!
    // la protection des sorties est réalisée au dernier moment, juste avant l'encapsulation du titre dans le code HTML

    // Afin d'économiser des ressources :
    // - toutes les chaines littérales transmises à echo sont délimitées entre simples quotes
    // - le code HTML est généré sans utiliser de concaténations (consommatrices de mémoire), étant donné que echo
    //   accepte un nombre quelconque de paramètres
    $image = cheminImageArticle($id);
    echo
            '<a href="./php/article.php?id=', $id, '">',
                '<img src="', $image, '" alt="Photo d\'illustration | ', $titre, '"><br>',
                $titre,
            '</a> ';
}
//_______________________________________________________________
/**
 * Affiche un bloc de 3 vignettes.
 *
 * @param   string      titre du bloc de 3 vignettes
 * @param   array       ids et titres des articles (clé : id de l'article, valeur associée à la clé : titre de l'article)
 *
 * @return  void
 */
function affBlocTroisArticlesL(string $titreBloc, array $articles) : void {
    echo    '<section class="centre">',
                '<h2>', $titreBloc, '</h2>';
    foreach($articles as $id => $titre){
        affUnArticleL($id, $titre);
    }
    echo    '</section>';
}

//_______________________________________________________________
/**
 * Affichage de l'horoscope
 *
 * @return  void
 */
function affHoroscopeL() : void {
    echo
        '<section>',
            '<h2>Horoscope de la semaine</h2>',

            '<p>Vous l\'attendiez tous, voici l\'horoscope du semestre impair de l\'année 2025-2026. Sans surprise, il n\'est pas terrible...</p>',

            '<table id="horoscope">',
                '<tr>',
                    '<td>Signe</td>',
                    '<td>Date</td>',
                    '<td>Votre horoscope</td>',
                '</tr>',
                '<tr>',
                    '<td>&#9800; Bélier</td>',
                    '<td>du 21 mars<br>au 19 avril</td>',
                    '<td rowspan="4">',
                        '<p>Après des vacances bien méritées, l\'année reprend sur les chapeaux de roues. Tous les signes sont concernés. </p>',
                        '<p>Jupiter s\'aligne avec Saturne, péremptoirement à Venus, et nous promet un semestre qui ne sera pas de tout repos. ',
                        'Octobre sera le mois le plus tranquille puisqu\'il se termine par une semaine de vacances.</p>',
                        '<p>Les fins de mois seront douloureuses pour les natifs du 2e décan au moment où tomberont les résultats ',
                        'de certains modules.</p>',
                    '</td>',
                '</tr>',
                '<tr>',
                    '<td>&#9801; Taureau</td>',
                    '<td>du 20 avril<br>au 20 mai</td>',
                '</tr>',
                '<tr>',
                    '<td>...</td>',
                    '<td>...</td>',
                '</tr>',
                '<tr>',
                    '<td>&#9811; Poisson</td>',
                    '<td>du 20 février<br>au 20 mars</td>',
                '</tr>',
            '</table>',

            '<p>Malgré cela, notre équipe d\'astrologues de choc vous souhaite à tous un bon semestre.</p>',
        '</section>';
}
