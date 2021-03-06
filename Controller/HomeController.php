<?php

namespace G3\GsbFraisBundle\Controller;
require_once("include/fct.inc.php");
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use G3\GsbFraisBundle\services;

class HomeController extends Controller
{
    public function indexAction(){
       $session= $this->get('request')->getSession();
       if(estConnecte($session)){
            return $this->render('G3GsbFraisBundle::accueil.html.twig');
        }
        else 
            return $this->render('G3GsbFraisBundle:Home:connexion.html.twig');
    }
    
    public function validerconnexionAction(){
        $session= $this->get('request')->getSession();
        $request = $this->get('request');
        $login =  $request->request->get('login');
        $mdp = $request->request->get('mdp');
        $pdo = $this->get('G3_gsb_frais.pdo');
        
        $visiteur = $pdo->getInfosVisiteur($login,$mdp);
        $comptable = $pdo->getInfosComptable($login,$mdp);
               
        if(is_array($visiteur)){
            $session->set('id',$visiteur['id']);
            $session->set('nom',$visiteur['nom']);
            $session->set('prenom',$visiteur['prenom']);
            $session->set('fonction',$visiteur['fonction']);
            return $this->render('G3GsbFraisBundle::accueil.html.twig');            
        }
        elseif(is_array($comptable)){
            $session->set('id',$comptable['id']);
            $session->set('nom',$comptable['nom']);
            $session->set('prenom',$comptable['prenom']);
            $session->set('fonction',$comptable['fonction']);
            return $this->render('G3GsbFraisBundle::accueil.html.twig'); 
        }
        else{
            return $this->render('G3GsbFraisBundle:Home:connexion.html.twig',array(
               'message'=>'Erreur de login ou de mot de passe '));             
        }
        
    }
    
    public function deconnexionAction(){
        $session= $this->get('request')->getSession();
        $session->clear();
        return $this->render('G3GsbFraisBundle:Home:connexion.html.twig');
    }
}
