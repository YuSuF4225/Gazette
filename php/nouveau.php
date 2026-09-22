<?php

/**
 * Page de création d'un nouvel article
 *
 * Cette page est réserbée aux utilisateurs authentifiés disposant des droits de rédaction
 *
 * Elle permet :
 *  - la saise du titre, du résumé et du texte de l'article
 *  - l'envoi facultatif d'une image d'illustration
 *  - l'enregistrement de l'article en base de données
 *
 * En cas d'erreurs de validation, celles-ci sont affichées dans le formulaire
 * En cas de succès, l'utilisateur est rédirigé vers la page de l'article crée.
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

if(isset($_POST['btnNouveau'])){
    $erreurs = traitementNouveauL();
}
else{
    $erreurs = null;
}

affDebutMenuEntete('Nouveau article');

$values = isset($_POST['btnNouveau']) ? htmlProtegerSorties($_POST) : ['txtTitre' => '', 'txtResume' => '', 'txtArticle' => ''];
affFormulaireArticle($values, $erreurs, 'nouveau');

affPiedFin();

/**
 * traite la soumission du formulaire de création d'un nouvel article
 *
 * Vérifie :
 *  - la présence et la validité des paramètres POST
 *  - la validité des champs texte (titre, résumé, contenu)
 *  - la validité éventuelle de l'image uploadée
 *
 * En cas d'erreurs :
 *  - retourne un tableau contenant les messages d'erreur
 *
 * En cas de succès :
 *  - insère l'article dans la base de données
 *  - récupère l'identifiant généré par la base
 *  - enregistre l'image sous la forme upload/{id}.jpg si fournie
 *  - redirige vers la page article.php
 *
 * Toute incohérence dans les paramètres POST  est considérée comme
 * une tentative de piratage et entraine l'appel de sessionExit()
 *
 * @return array Tableau des messages d'erreur (vide si succès)
 */
function traitementNouveauL() : array {
    if( !parametresControle('post', clesObligatoires:['txtTitre', 'txtResume', 'txtArticle', 'btnNouveau', 'MAX_FILE_SIZE'], clesFacultatives:[])) {
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

    $bd = bdConnect();

    $titreE = mysqli_real_escape_string($bd, $titre);
    $resumeE = mysqli_real_escape_string($bd, $resume);
    $texteE = mysqli_real_escape_string($bd, $texte);

    $date = date('YmdHi', time());
    $pseudo = mysqli_real_escape_string($bd, $_SESSION['pseudo']);

    $sql = "INSERT INTO article SET
            arTitre = '$titreE',
            arResume = '$resumeE',
            arTexte = '$texteE',
            arDatePubli = $date,
            arDateModif = $date,
            arAuteur = '$pseudo'";

    bdSendRequest($bd, $sql);

    $id = mysqli_insert_id($bd);

    mysqli_close($bd);

    if($image !== null){
        $dest = realpath('.')."/../upload/$id.jpg";
        $qualite = 80;
        imagejpeg($image, $dest, $qualite);

        if(filesize($dest)>100000){
            $qualite = 70;
            imagejpeg($image, $dest, $qualite);
        }
        if(filesize($dest)>100000){
            @unlink($dest);
            return ["Impossible de réduire l'image en dessous de 100Ko."];
        }
    }

    header("Location: article.php?id=$id");
    exit();
}



