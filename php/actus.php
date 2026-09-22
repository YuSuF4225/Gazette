<?php

/**
 * Affichage paginé de la liste des articles
 * Regroupement des articles par mois
 *
 * Paramètre GET :
 *  - t : le nombre total d'articles
 *  - p : la position dans les résultats
 */

require_once './bibli_generale.php';
require_once ('./bibli_gazette.php');

//Nombre d'articles affichés par page
define('PAGINATION', 4);

// bufferisation des sorties
ob_start();

//démarrage ou reprise de la session
session_start();

if (! parametresControle('get', [], ['p', 't'])){
    affDebutMenuEntete("L'actus");
    affPiedFin();
    exit;
}

$totalArticles = -1;
$position = -1;

if (isset($_GET['t']) && estEntier($_GET['t'])) {
    $totalArticles = (int) $_GET['t'];
}

if (isset($_GET['p']) && estEntier($_GET['p'])) {
    $position = (int) $_GET['p'];
}

if ($totalArticles < 0 || $position < 0) {
    $totalArticles = $position = 0;
}

if ($position >= $totalArticles) {
    $totalArticles = $position = 0;
}

$bd = bdConnect();

if ($totalArticles == 0){
    $totalArticles = getNbArticlesL($bd);
}
if ($totalArticles == 0){
    mysqli_close($bd);
    affDebutMenuEntete("L'actus");
    affPiedFin();
    exit;
}

$r = getArticlesL($bd, $position);

if(mysqli_num_rows($r) == 0) {
    mysqli_free_result($r);
    $totalArticles = getNbArticlesL($bd);
    if ($totalArticles == 0){
        mysqli_close($bd);
        affDebutMenuEntete("L'actus");
        affPiedFin();
        exit;
    }
    $position = 0;
    $r = getArticlesL($bd, $position);
}

affDebutMenuEntete("L'actus");

echo '<div class="blanche"><p class="pagination">Pages : ';

for ($i = 0, $page = 1; $i < $totalArticles; $i += PAGINATION, ++$page) {
    if ($i == $position) {
        echo '<span class="active">', $page, '</span> ';
    } else {
        echo '<a class="page" style="color: #333;" href="', basename($_SERVER['PHP_SELF']), '?t=', $totalArticles, '&p=', $i, '">', $page, '</a> ';
    }
}
echo '</p></div>';

$articles = [];
while($article = mysqli_fetch_assoc($r)){
    $articles[] = $article;
}
mysqli_free_result($r);
affArticlesGroupesParMois($articles);

mysqli_close($bd);
affPiedFin();

/**
 * Récupération du nombre total d'articles
 *
 * @param mysqli $bd Connexion à la base de données
 *
 * @return int       Nombre total d'articles présents en base
 */
function getNbArticlesL(mysqli $bd) : int {
    $sql = 'SELECT COUNT(*) AS N FROM article';
    $r = bdSendRequest($bd, $sql);
    $enr = mysqli_fetch_assoc($r);
    mysqli_free_result($r);
    return $enr['N'];
}

/**
 * Récupération des articles pour une page données
 *
 * @param mysqli $bd     Connexion à la base de données
 * @param int    $pos    Position de départ
 *
 * @return mysqli_result Résultat de la requête SQL
 */
function getArticlesL(mysqli $bd, int $pos) : mysqli_result {
    $sql = "SELECT  arTitre, arResume, arDatePubli, arID
    FROM article
    ORDER BY arDatePubli DESC
    LIMIT $pos, " . PAGINATION;
    return bdSendRequest($bd, $sql);
}
