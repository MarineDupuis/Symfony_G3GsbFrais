<?php

namespace G3\GsbFraisBundle\Controller;
require_once("include/fct.inc.php");
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use G3\GsbFraisBundle\services;

class ListeFraisController extends Controller
{
    public function indexAction()
    {
//        $session= $this->container->get('request')->getSession();
        $session= $this->get('request')->getSession();
        $idVisiteur =  $session->get('id');
//        $pdo = PdoGsb::getPdoGsb();
        $pdo = $this->get('G3_gsb_frais.pdo');
        $lesMois=$pdo->getLesMoisDisponibles($idVisiteur);
        if($this->get('request')->getMethod() == 'GET'){
                // Afin de sélectionner par défaut le dernier mois dans la zone de liste
                // on demande toutes les clés, et on prend la première,
                // les mois étant triés décroissants
            $lesCles = array_keys( $lesMois );
            $moisASelectionner = $lesCles[0];
            return $this->render('G3GsbFraisBundle:ListeFrais:listemois.html.twig',
                array('lesmois'=>$lesMois,'lemois'=>$moisASelectionner, 'AffichFrais'=>'N'));
        }
        else{
            $request = $this->get('request');
            $leMois =  $request->request->get('lstMois');
            $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur,$leMois);
            $lesFraisForfait= $pdo->getLesFraisForfait($idVisiteur,$leMois);
            $lesInfosFicheFrais = $pdo->getLesInfosFicheFrais($idVisiteur,$leMois);
            $numAnnee =substr( $leMois,0,4);
            $numMois =substr( $leMois,4,2);
            $libEtat = $lesInfosFicheFrais['libEtat'];
            if ($libEtat== 'CL' || $libEtat== 'CR')
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
                return $this->render('G3GsbFraisBundle:ListeFrais:listemois.html.twig',
                array('lesmois'=>$lesMois,'lesfraisforfait'=>$lesFraisForfait,'lesfraishorsforfait'=>$lesFraisHorsForfait,
                    'lemois'=>$leMois,'numannee'=>$numAnnee,'nummois'=> $numMois,'libetat'=>$libEtat,
                        'montantvalide'=>$montantValide,'nbjustificatifs'=>$nbJustificatifs,'datemodif'=>$dateModif, 'AffichFrais'=>'O'));
            }
          
    }
    
}






?>
