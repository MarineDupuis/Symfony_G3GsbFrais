<?php

namespace G3\GsbFraisBundle\Controller;
require_once("include/fct.inc.php");
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use G3\GsbFraisBundle\services;

class SuivrePaiementController extends Controller
{
    public function indexAction()
    {
        $session= $this->get('request')->getSession();
        $pdo = $this->get('G3_gsb_frais.pdo');
        if($this->get('request')->getMethod() == 'GET'){			
            $lesVisiteurs=$pdo->getLesUtilisateursSuivi();
            $lesCles = array_keys($lesVisiteurs);		
            $prenomSelectionner = $lesCles[0];
            return $this->render('G3GsbFraisBundle:SuivrePaiement:listeutilisateurssuivi.html.twig',
            array('lesVisiteurs'=>$lesVisiteurs,'prenomSelectionner'=>$prenomSelectionner));
        }
        else{
            $infosUser = $_REQUEST['infosUser'];
            $etat = $_REQUEST['etat'];
            list($nom,$prenom,$idCommercial) = explode("+", $infosUser);
            $lesFiches=$pdo->getInformationsFichesSuivi($idCommercial,$etat);
            return $this->render('G3GsbFraisBundle:SuivrePaiement:listefichesuivi.html.twig',
            array('lesFiches'=>$lesFiches,'nom'=>$nom,'prenom'=>$prenom,'idCommercial'=>$idCommercial));
        }
    }
    public function AfficheDetailAction($id,$leMois){
        $request = $this->get('request');
        $pdo = $this->get('G3_gsb_frais.pdo');
        $lesMois=$pdo->getLesMoisDisponibles($id);
        $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($id,$leMois);
        $lesFraisForfait= $pdo->getLesFraisForfait($id,$leMois);
        $lesInfosFicheFrais = $pdo->getLesInfosFicheFrais($id,$leMois);
        $numAnnee =substr( $leMois,0,4);
        $numMois =substr( $leMois,4,2);
        $libEtat = $lesInfosFicheFrais['libEtat'];
        if ($lesInfosFicheFrais['idEtat']== 'CL' || $lesInfosFicheFrais['idEtat']== 'CR')
        {
            $montantValide = $pdo ->majMontantFrais($lesInfosFicheFrais,$lesFraisForfait,$lesFraisHorsForfait); //montant modifié si fiche en CL ou CR
        }
        else
        {
            $montantValide = $lesInfosFicheFrais['montantValide'];
        }
        $nbJustificatifs = $lesInfosFicheFrais['nbJustificatifs'];
        $dateModif =  $lesInfosFicheFrais['dateModif'];
        $dateModif =  dateAnglaisVersFrancais($dateModif);
        return $this->render('G3GsbFraisBundle:SuivrePaiement:detailfichesuivi.html.twig',
            array('lesmois'=>$lesMois,'lesfraisforfait'=>$lesFraisForfait,'lesfraishorsforfait'=>$lesFraisHorsForfait,
                'lemois'=>$leMois,'numannee'=>$numAnnee,'nummois'=> $numMois,'libetat'=>$libEtat,
                    'montantvalide'=>$montantValide,'nbjustificatifs'=>$nbJustificatifs,'datemodif'=>$dateModif, 'AffichFrais'=>'O'));            
    }
    
}

?>
