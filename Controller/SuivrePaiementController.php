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
    
}

?>
