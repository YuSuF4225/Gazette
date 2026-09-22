<?php

/**
 * Page d'édition et de suppression d'un article
 *
 * CEtte page est accessible uniquement :
 *  - aux utilisateurs authentifiés
 *  - disposant du droit de rédaction
 *  - auteurs de l'article concerné
 *
 * Fonctionnalités :
 *  - affichage du formulaire de modification d'un article
 *  - mise à jour du titre, résumé, texte et image
 *  - suppression de l'article (et de ses commentaires associés)
 *
 * Sécurité :
 *  - controle strict des paramètres GET et POST
 *  - vérification de l'auteurs
 *  - protection contre les injections SQL
 *  - protection contre les attaque XSS
 */

require_once './bibli_generale.php';
require_once ('./bibli_gazette.php');

// bufferisation des sorties
ob_start();

//démarrage ou reprise de la session
session_start();

if(!estAuthentifie() || !isset($_SESSION['redacteur']) || !$_SESSION['redacteur']){
    header ('Location: ../index.php');
    exit();
}

if(!isset($_GET['id']) || !ctype_digit($_GET['id'])){
    header ('Location: ../index.php');
    exit();
}

$id=(int)$_GET['id'];
$pseudo = $_SESSION['pseudo'];
$bd = bdConnect();
$sql = "SELECT arTitre, arResume, arTexte, arAuteur
        FROM article
        WHERE arID = $id";
$res = bdSendRequest($bd, $sql);
$article = mysqli_fetch_assoc($res);
mysqli_free_result($res);

if(!$article || $article['arAuteur']!==$pseudo){
    mysqli_close($bd);
    header ('Location: ../index.php');
    exit();
}

if(isset($_POST['btnModifier'])){
    $erreurs = traitementEditionArticle($bd, $id);
}
else if(isset($_POST['confirmSuppr'])){
    $sql = "DELETE FROM commentaire WHERE coArticle = $id";
    bdSendRequest($bd, $sql);
    $sql = "DELETE FROM article WHERE arID = $id";
    bdSendRequest($bd, $sql);
    $img = realpath('.')."/../upload/$id.jpg";
    if(file_exists($img)){
        @unlink($img);
    }
    header('Location: ../index.php');
    exit();
}
else{
    $erreurs = null;
}

affDebutMenuEntete('Édition article');

$values = isset($_POST['btnModifier']) ? htmlProtegerSorties($_POST) : htmlProtegerSorties([
    'txtTitre' => $article['arTitre'],
    'txtResume' => $article['arResume'],
    'txtArticle' => $article['arTexte']
]);
affFormulaireArticle($values, $erreurs, 'edition', $id);

affPiedFin();

mysqli_close($bd);

/**
 * Traite la modification d'un article existant
 *
 * Vérifie les param POST, valide les champs texte,
 * met à jour l'article en base de données et remplace
 * l'image associée si une nouvelle image est fournie
 *
 * En cas de succès, redirige vers la page d'affichafe de l'article modifié
 *
 * @param mysqli $bd Connexion active à la base de données
 * @param int    $id Identifiant de l'article à modifier
 *
 * @return array     Tableau des messages d'erreur (vide si aucune erreur)
 */
function traitementEditionArticle(mysqli $bd, int $id) : array {
    if( !parametresControle('post', clesObligatoires:['txtTitre', 'txtResume', 'txtArticle', 'btnModifier', 'MAX_FILE_SIZE'], clesFacultatives:[])) {
        sessionExit();
    }

    $erreurs=[];
    if(isset($_FILES['image']) && @is_uploaded_file($_FILES['image']['tmp_name'])){
        $image = traiterImageUpload($_FILES['image'], $erreurs);
    }
    else{
        $image = null;
    }
    [$titre, $resume, $texte] = verifierChampsArticle($_POST, $erreurs);

    if(count($erreurs) > 0){
        return $erreurs;
    }

    $titreE = mysqli_real_escape_string($bd, $titre);
    $resumeE = mysqli_real_escape_string($bd, $resume);
    $texteE = mysqli_real_escape_string($bd, $texte);
    $date = date('YmdHi', time());

    $sql = "UPDATE article SET
            arTitre = '$titreE',
            arResume = '$resumeE',
            arTexte = '$texteE',
            arDateModif = $date
            WHERE arID = $id";

    bdSendRequest($bd, $sql);

    if($image!==null){
        $dest = realpath('.')."/../upload/$id.jpg";
        $qualite = 80;
        imagejpeg($image, $dest, $qualite);
    }

    header("Location: article.php?id=$id");
    exit();
}














