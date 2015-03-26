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
	 /*
	 public function supprimerfraishorsforfaitAction($id){
                $session= $this->get('request')->getSession();
                $idVisiteur =  $session->get('id');
                $mois = getMois(date("d/m/Y"));
                $numAnnee =substr( $mois,0,4);
                $numMois =substr( $mois,4,2);
                $pdo = $this->get('G3_gsb_frais.pdo');
                if( $pdo->estValideSuppressionFrais($idVisiteur,$mois,$id))
                            $pdo->supprimerFraisHorsForfait($id);
                else {
                     $response = new Response;
                     $response->setContent("<h2>Page introuvable erreur 404 ");
                     $response->setStatusCode(404);
                     return $response;
                }
                $lesFraisForfait= $pdo->getLesFraisForfait($idVisiteur,$mois);
                $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur,$mois);
                return $this->render('G3GsbFraisBundle:SaisirFrais:saisirtouslesfrais.html.twig',
                array('lesfraisforfait'=>$lesFraisForfait,'lesfraishorsforfait'=>$lesFraisHorsForfait,'nummois'=>$numMois,
                    'numannee'=>$numAnnee,'leserreursforfait'=>null,'leserreurshorsforfait'=>null));
     }
	 
	  public function validerfraishorsforfaitAction(){
                $session= $this->get('request')->getSession();
                $idVisiteur =  $session->get('id');
                $mois = getMois(date("d/m/Y"));
                $numAnnee =substr( $mois,0,4);
                $numMois =substr( $mois,4,2);
                $pdo = $this->get('G3_gsb_frais.pdo');
                $request = $this->get('request');
                $dateFrais = $request->request->get('dateFrais');
		$libelle = $request->request->get('libelle');
                $montant = $request->request->get('montant');
		$lesErreursHorsForfait = valideInfosFrais($dateFrais,$libelle,$montant);
              	if (count($lesErreursHorsForfait)==0){
			$pdo->creeNouveauFraisHorsForfait($idVisiteur,$mois,$libelle,$dateFrais,$montant);
		}
                $lesFraisForfait= $pdo->getLesFraisForfait($idVisiteur,$mois);
                $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur,$mois);
                return $this->render('G3GsbFraisBundle:SaisirFrais:saisirtouslesfrais.html.twig',
                array('lesfraisforfait'=>$lesFraisForfait,'lesfraishorsforfait'=>$lesFraisHorsForfait,'nummois'=>$numMois,
                    'numannee'=>$numAnnee,'leserreursforfait'=>null,'leserreurshorsforfait'=> $lesErreursHorsForfait));
     
     }
    */
    
}





?>
