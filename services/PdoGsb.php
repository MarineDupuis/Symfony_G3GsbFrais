<?php
namespace G3\GsbFraisBundle\services;
/** 
 * Classe d'accès aux données. 
 
 * Utilise les services de la classe PDO
 * pour l'application GSB
 * Les attributs sont tous statiques,
 * les 4 premiers pour la connexion
 * $monPdo de type PDO 
 * $monPdoGsb qui contiendra l'unique instance de la classe
 
 * @package default
 * @author Cheri Bibi
 * @version    1.0
 * @link       http://www.php.net/manual/fr/book.pdo.php
 */
 
 
 
 
 
 
use PDO;
class PdoGsb{
        private static $monPdo;
	private static $monPdoGsb=null;
/**
 * Constructeur privé, crée l'instance de PDO qui sera sollicitée
 * pour toutes les méthodes de la classe
 */				
	public function __construct($serveur, $bdd, $user, $mdp){
    	PdoGsb::$monPdo = new PDO($serveur.';'.$bdd, $user, $mdp); 
		PdoGsb::$monPdo->query("SET CHARACTER SET utf8");
                PdoGsb::$monPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //pour les tests
	}
	public function _destruct(){
		PdoGsb::$monPdo = null;
	}
/**
 * Fonction statique qui crée l'unique instance de la classe
 
 * Appel : $instancePdoGsb = PdoGsb::getPdoGsb();
 
 * @return l'unique objet de la classe PdoGsb
 */
	public  static function getPdoGsb(){
		if(PdoGsb::$monPdoGsb==null){
			PdoGsb::$monPdoGsb= new PdoGsb();
		}
		return PdoGsb::$monPdoGsb;  
	}
/**
 * Retourne les informations d'un visiteur
 
 * @param $login 
 * @param $mdp
 * @return l'id, le nom et le prénom sous la forme d'un tableau associatif 
*/
	public function getInfosVisiteur($login, $mdp){
                $req = "select visiteur.id as id, visiteur.nom as nom, visiteur.prenom as prenom from visiteur INNER JOIN user ON visiteur.login = user.login
        where visiteur.login=:log and user.mdp=(md5(:mdp))";
		$stmt = PdoGsb::$monPdo->prepare($req);
                $stmt->bindParam(':log', $login);
		$stmt->bindParam(':mdp', $mdp);
                $stmt->execute();
		$ligne = $stmt->fetch();
            //    var_dump($ligne);
		return $ligne;
	}

/**
 * @author DUPUIS Marine
 * Retourne les informations d'un comptable
 
 * @param $login 
 * @param $mdp
 * @return l'id, le nom et le prénom sous la forme d'un tableau associatif 
*/
 	public function getInfosComptable($login, $mdp){
                $req = "select comptable.id as id, comptable.nom as nom, comptable.prenom as prenom from comptable INNER JOIN user ON comptable.login = user.login
        where comptable.login=:log and user.mdp=(md5(:mdp))";
		$stmt = PdoGsb::$monPdo->prepare($req);
                $stmt->bindParam(':log', $login);
		$stmt->bindParam(':mdp', $mdp);
                $stmt->execute();
		$ligne = $stmt->fetch();
            //    var_dump($ligne);
		return $ligne;
	}
                
/**
 * Retourne sous forme d'un tableau associatif toutes les lignes de frais hors forfait
 * concernées par les deux arguments
 
 * La boucle foreach ne peut être utilisée ici car on procède
 * à une modification de la structure itérée - transformation du champ date-
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return tous les champs des lignes de frais hors forfait sous la forme d'un tableau associatif 
*/
	public function getLesFraisHorsForfait($idVisiteur,$mois){
	    $req = "select * from lignefraishorsforfait where lignefraishorsforfait.idvisiteur = :idVisiteur 
		and lignefraishorsforfait.mois = :mois ";
                $stmt = PdoGsb::$monPdo->prepare($req);
                $stmt->bindParam(':idVisiteur', $idVisiteur);
		$stmt->bindParam(':mois', $mois);
                $stmt->execute();
		$lesLignes = $stmt->fetchAll();
		$nbLignes = count($lesLignes);
		for ($i=0; $i<$nbLignes; $i++){
			$date = $lesLignes[$i]['date'];
			$lesLignes[$i]['date'] =  dateAnglaisVersFrancais($date);
		}
		return $lesLignes; 
	}
/**
 * Retourne le nombre de justificatif d'un visiteur pour un mois donné
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return le nombre entier de justificatifs 
*/
	public function getNbjustificatifs($idVisiteur, $mois){
		$req = "select fichefrais.nbjustificatifs as nb from  fichefrais where fichefrais.idvisiteur =:idVisiteur and fichefrais.mois = :mois";
		$stmt = PdoGsb::$monPdo->prepare($req);
                $stmt->bindParam(':idVisiteur', $idVisiteur);
		$stmt->bindParam(':mois', $mois);
                $stmt->execute();
		$laLigne = $stmt->fetch();
		return $laLigne['nb'];
	}
/**
 * Retourne sous forme d'un tableau associatif toutes les lignes de frais au forfait
 * concernées par les deux arguments
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return l'id, le libelle et la quantité sous la forme d'un tableau associatif 
*/
	public function getLesFraisForfait($idVisiteur, $mois){
		$req = "select fraisforfait.id as idfrais, fraisforfait.libelle as libelle, 
		lignefraisforfait.quantite as quantite from lignefraisforfait inner join fraisforfait 
		on fraisforfait.id = lignefraisforfait.idfraisforfait
		where lignefraisforfait.idvisiteur = :idVisiteur and lignefraisforfait.mois= :mois 
		order by lignefraisforfait.idfraisforfait";	
		$stmt = PdoGsb::$monPdo->prepare($req);
                $stmt->bindParam(':idVisiteur', $idVisiteur);
		$stmt->bindParam(':mois', $mois);
                $stmt->execute();
		$lesLignes = $stmt->fetchAll();
		return $lesLignes; 
	}
/**
 * Retourne tous les id de la table FraisForfait
 
 * @return un tableau associatif 
*/
	public function getLesIdFrais(){
		$req = "select fraisforfait.id as idfrais from fraisforfait order by fraisforfait.id";
		$stmt = PdoGsb::$monPdo->prepare($req);
                $stmt->execute();
		$lesLignes = $stmt->fetchAll();
		return $lesLignes;
	}
/**
 * Met à jour la table ligneFraisForfait
 
 * Met à jour la table ligneFraisForfait pour un visiteur et
 * un mois donné en enregistrant les nouveaux montants
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @param $lesFrais tableau associatif de clé idFrais et de valeur la quantité pour ce frais
 * @return un tableau associatif 
*/
	public function majFraisForfait($idVisiteur, $mois, $lesFrais){
		$lesCles = array_keys($lesFrais);
		foreach($lesCles as $unIdFrais){
			$qte = $lesFrais[$unIdFrais];
			$req = "update lignefraisforfait set lignefraisforfait.quantite = $qte
			where lignefraisforfait.idvisiteur = :idVisiteur and lignefraisforfait.mois = :mois
			and lignefraisforfait.idfraisforfait = :unIdFrais";
                         $stmt = PdoGsb::$monPdo->prepare($req);
                        $stmt->bindParam(':idVisiteur', $idVisiteur);
                        $stmt->bindParam(':mois', $mois);
                         $stmt->bindParam(':unIdFrais', $unIdFrais);
                        $stmt->execute();
		}
		
	}
/**
 * met à jour le nombre de justificatifs de la table ficheFrais
 * pour le mois et le visiteur concerné
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
*/
        public function majNbJustificatifs($idVisiteur, $mois, $nbJustificatifs){
                $req = "update fichefrais set nbjustificatifs = :nbJustificatifs 
                where fichefrais.idvisiteur = :idVisiteur and fichefrais.mois = :mois";
                $stmt = PdoGsb::$monPdo->prepare($req);
                $stmt->bindParam(':idVisiteur', $idVisiteur);
                $stmt->bindParam(':mois', $mois);
                $stmt->bindParam(':nbJustificatifs', $nbJustificatifs);
                $stmt->execute();
        }
/**
 * Teste si un visiteur possède une fiche de frais pour le mois passé en argument
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return vrai ou faux 
*/	
        public function estPremierFraisMois($idVisiteur,$mois)
        {
                $ok = false;
                $req = "select count(*) as nblignesfrais from fichefrais 
                where fichefrais.mois = :mois and fichefrais.idvisiteur = :idVisiteur";
                $stmt = PdoGsb::$monPdo->prepare($req);
                $stmt->bindParam(':idVisiteur', $idVisiteur);
                $stmt->bindParam(':mois', $mois);
                $stmt->execute();
                $laLigne = $stmt->fetch();
                if($laLigne['nblignesfrais'] == 0){
                        $ok = true;
                }
                return $ok;
        }
/**
 * Retourne le dernier mois en cours d'un visiteur
 
 * @param $idVisiteur 
 * @return le mois sous la forme aaaamm
*/	
        public function dernierMoisSaisi($idVisiteur){
                $req = "select max(mois) as dernierMois from fichefrais where fichefrais.idvisiteur = :idVisiteur";
                $stmt = PdoGsb::$monPdo->prepare($req);
                $stmt->bindParam(':idVisiteur', $idVisiteur);
                $stmt->execute();
                $laLigne = $stmt->fetch();
                $dernierMois = $laLigne['dernierMois'];
                return $dernierMois;
        }
	
/**
 * Crée une nouvelle fiche de frais et les lignes de frais au forfait pour un visiteur et un mois donnés
 
 * récupère le dernier mois en cours de traitement, met à 'CL' son champs idEtat, crée une nouvelle fiche de frais
 * avec un idEtat à 'CR' et crée les lignes de frais forfait de quantités nulles 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
*/
        public function creeNouvellesLignesFrais($idVisiteur,$mois){
                $dernierMois = $this->dernierMoisSaisi($idVisiteur);
                $laDerniereFiche = $this->getLesInfosFicheFrais($idVisiteur,$dernierMois);
                if($laDerniereFiche['idEtat']=='CR'){
                                $this->majEtatFicheFrais($idVisiteur, $dernierMois,'CL');

                }
                $req = "insert into fichefrais(idvisiteur,mois,nbJustificatifs,montantValide,dateModif,idEtat) 
                values(:idVisiteur,:mois,0,0,now(),'CR')";
                $stmt = PdoGsb::$monPdo->prepare($req);
                $stmt->bindParam(':idVisiteur', $idVisiteur);
                 $stmt->bindParam(':mois', $mois);
                $stmt->execute();
                $lesIdFrais = $this->getLesIdFrais();
                foreach($lesIdFrais as $uneLigneIdFrais){
                        $unIdFrais = $uneLigneIdFrais['idfrais'];
                        $req = "insert into lignefraisforfait(idvisiteur,mois,idFraisForfait,quantite) 
                        values(:idVisiteur,:mois,:unIdFrais,0)";
                        $stmt = PdoGsb::$monPdo->prepare($req);
                        $stmt->bindParam(':idVisiteur', $idVisiteur);
                        $stmt->bindParam(':mois', $mois);
                        $stmt->bindParam(':unIdFrais', $unIdFrais);
                        $stmt->execute();
                 }
        }
/**
 * Crée un nouveau frais hors forfait pour un visiteur un mois donné
 * à partir des informations fournies en paramètre
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @param $libelle : le libelle du frais
 * @param $date : la date du frais au format français jj//mm/aaaa
 * @param $montant : le montant
*/
        public function creeNouveauFraisHorsForfait($idVisiteur,$mois,$libelle,$date,$montant){
                $dateFr = dateFrancaisVersAnglais($date);
                $req = "insert into lignefraishorsforfait(idvisiteur,mois,libelle,date,montant)
                values(:idVisiteur,:mois,:libelle,:dateFr,:montant)";
//                echo "données : $idVisiteur, $mois, $libelle, $dateFr, $montant";
                $stmt = PdoGsb::$monPdo->prepare($req);
                $stmt->bindParam(':idVisiteur', $idVisiteur);
                $stmt->bindParam(':mois', $mois);
                $stmt->bindParam(':libelle', $libelle);
                $stmt->bindParam(':dateFr', $dateFr);
                $stmt->bindParam(':montant', $montant);
                $stmt->execute();
        }
/**
 * Supprime le frais hors forfait dont l'id est passé en argument
 
 * @param $idFrais 
*/
        public function supprimerFraisHorsForfait($idFrais){
                $req = "delete from lignefraishorsforfait where lignefraishorsforfait.id =:idFrais ";
                $stmt = PdoGsb::$monPdo->prepare($req);
                $stmt->bindParam(':idFrais', $idFrais);
                $stmt->execute();
        }
/**
 * Retourne les mois pour lesquel un visiteur a une fiche de frais
 
 * @param $idVisiteur 
 * @return un tableau associatif de clé un mois -aaaamm- et de valeurs l'année et le mois correspondant 
*/
        public function getLesMoisDisponibles($idVisiteur){
                $req = "select fichefrais.mois as mois from  fichefrais where fichefrais.idvisiteur =:idVisiteur 
                order by fichefrais.mois desc ";
                $stmt = PdoGsb::$monPdo->prepare($req);
                $stmt->bindParam(':idVisiteur', $idVisiteur);
                $stmt->execute();
                $laLigne = $stmt->fetch();
                $lesMois =array();
                while($laLigne != null)	{
                        $mois = $laLigne['mois'];
                        $numAnnee =substr( $mois,0,4);
                        $numMois =substr( $mois,4,2);
                        $lesMois["$mois"]=array(
                        "mois"=>"$mois",
                        "numAnnee"  => "$numAnnee",
                        "numMois"  => "$numMois"
                        );
                        $laLigne = $stmt->fetch(); 		
                }
                return $lesMois;
        }
/**
 * Retourne les informations d'une fiche de frais d'un visiteur pour un mois donné
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return un tableau avec des champs de jointure entre une fiche de frais et la ligne d'état 
*/	
        public function getLesInfosFicheFrais($idVisiteur,$mois){
                $req = "select ficheFrais.idEtat as idEtat, ficheFrais.dateModif as dateModif, ficheFrais.nbJustificatifs as nbJustificatifs, 
                        ficheFrais.montantValide as montantValide, etat.libelle as libEtat from  fichefrais inner join Etat on ficheFrais.idEtat = Etat.id 
                        where fichefrais.idvisiteur = :idVisiteur and fichefrais.mois = :mois";
                $stmt = PdoGsb::$monPdo->prepare($req);
                $stmt->bindParam(':idVisiteur', $idVisiteur);
                $stmt->bindParam(':mois', $mois);
                $stmt->execute();
                $laLigne = $stmt->fetch();
                return $laLigne;
        }
/**
 * Modifie l'état et la date de modification d'une fiche de frais
 
 * Modifie le champ idEtat et met la date de modif à aujourd'hui
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 */
 
        public function majEtatFicheFrais($idVisiteur,$mois,$etat){
                $req = "update ficheFrais set idEtat = :etat, dateModif = now() 
                where fichefrais.idvisiteur = :idVisiteur and fichefrais.mois = :mois";
                $stmt = PdoGsb::$monPdo->prepare($req);
                $stmt->bindParam(':idVisiteur', $idVisiteur);
                $stmt->bindParam(':mois', $mois);
                $stmt->bindParam(':etat', $etat);
                $stmt->execute();

        }
/**
 * Retourne toutes les fiches de frais dont l'état est à validé
 * @return type
 */
        public function getLesFichesValidees()
        {
            $req = "select * from fichefrais
                    inner join etat on fichefrais.idetat = etat.id
                    inner join visiteur on visiteur.id = fichefrais.idVisiteur
                    where idetat = 'VA'";	
            $stmt = PdoGsb::$monPdo->prepare($req);
            $stmt->execute();
            $lesLignes = $stmt->fetchAll();
            return $lesLignes; 
        }
/**
 * Vérifie si un frais existe pour un visiteur et un mois donné
 * @param type $idVisiteur
 * @param type $mois
 * @param type $idFrais
 * @return 1 ou 0
 */
        public function estValideSuppressionFrais($idVisiteur,$mois,$idFrais){
            $req = "select count(*) as nb from lignefraishorsforfait 
            where lignefraishorsforfait.id=:idfrais and lignefraishorsforfait.mois=:mois
            and lignefraishorsforfait.idvisiteur=:idvisiteur";
            $stmt = PdoGsb::$monPdo->prepare($req);
            $stmt->bindParam(':idfrais', $idFrais);
            $stmt->bindParam(':mois', $mois);
            $stmt->bindParam(':idvisiteur', $idVisiteur);
            $stmt->execute();
            $ligne = $stmt->fetch();
            return $ligne['nb'];
        }
		
		public function majMontantFrais($lesInfosFicheFrais,$lesFraisForfait,$lesFraisHorsForfait) {// fonction créée par Julien Pulido: Modifie la valeur du montant des frais lorsque la fiche est CR ou CL
	 /*Calcul somme des frais forfaitise */
		$montantValide = '0.00';
		$nb = array(0=>''); // creation tableau stockage nombre de frais forfait
		$n=1;
		foreach ($lesFraisForfait as $unFraisForfait) //parcours des valeurs pour les frais forfaits
		{
			$quantite = $unFraisForfait['quantite'];
			$nb[$n] = $quantite; //stockage de la valeur dans le tableau
			$n++;
		}
		// $nb[1] = nombre de forfait etape
		// $nb[2] = nombre de Frais Kilométrique
		// $nb[3] = nombre de Nuitée Hôtel
		// $nb[4] = nombre de Repas Restaurant
		
		$req= "SELECT montant FROM fraisforfait ORDER BY id"; //recupere les montant de chaque frais
		$stmt = PdoGsb::$monPdo->prepare($req);
		$stmt->execute();
		$montant = $stmt->fetchAll();
		$tab = array(0=>''); // creation tableau stockage nombre de frais forfait
		$n=1;
		foreach ($montant as $unMontant) //parcours des valeurs pour les frais
		{
			$prix = $unMontant['montant'];
			$tab[$n] = $prix; //stockage de la valeur dans le tableau
			$n++;
		}
		// $tab[1] = nombre de forfait etape
		// $tab[2] = nombre de Frais Kilométrique
		// $tab[3] = nombre de Nuitée Hôtel
		// $tab[4] = nombre de Repas Restaurant
		
		$montantValide = $nb[1]* $tab[1] + $nb[2]* $tab[2] + $nb[3]* $tab[3] + $nb[4]* $tab[4]; //Calcul Somme montant forfaitise
		
	/* Calcul somme frais hors forfait */
		$montantHF=0; // stockage des montants hors forfaits
		foreach ($lesFraisHorsForfait as $unFraisHorsForfait) //parcours des valeurs pour les hors forfaits
		{
			$montantHF+= $unFraisHorsForfait['montant']; // somme de tout les frais Hors Forfaits
			
		}
		$montantValide+= $montantHF; // Somme du montant de Frais Forfait + montant de Frais Hors Forfait
		return $montantValide;
	 }

		public function getLesUtilisateursSuivi(){ // fonction créée par Julien Pulido: recupere la liste des utilisateurs ayant des fiches frais VA ou RB des 12 derniers mois et renvois sous forme de tableau leurs informations
		$current_date = date('Ym');
		$duree= $current_date - 100;   // $duree est la date maxi pour les 12 derniers mois             
		$req="select distinct id, nom, prenom from fichefrais inner join visiteur on id = idvisiteur where idEtat = 'VA' or idEtat = 'RB' and mois > '$duree' ORDER BY nom ASC ";
		$res = PdoGsb::$monPdo->query($req);                
		$lesVisiteur =array();
		$laLigne = $res->fetch();
		while($laLigne != null)	{
			$id = $laLigne['id'];
			$nom = $laLigne['nom'];
			$prenom = $laLigne['prenom'];
			$lesVisiteur["$id"]=array(
		     "id" => "$id",
		     "prenom" => "$prenom",
		     "nom" => "$nom"
             );
			$laLigne = $res->fetch(); 		
		}
		return $lesVisiteur; // retourne l'id, le prenom et le nom des utilisateurs concernes sous forme de tableau.
	}
	
	public function getInformationsFichesSuivi($idCommercial,$etat){ // fonction créée par Julien Pulido: recupere les informations des fiches de frais pour un utisateur et un etat voulu
		$current_date = date('Ym');
		$duree= $current_date - 100; // $duree est la date maxi pour les 12 derniers mois
		$req="select mois, dateModif, montantValide, idEtat, nom, prenom, etat.libelle as libEtat from  fichefrais inner join visiteur on visiteur.id = fichefrais.idVisiteur INNER JOIN Etat ON ficheFrais.idEtat = Etat.id where fichefrais.idvisiteur ='$idCommercial' and idEtat='$etat' and mois > '$duree' order by fichefrais.mois desc";
		$res = PdoGsb::$monPdo->query($req);
		$lesFiches =array();
		$laLigne = $res->fetch();
		while($laLigne != null)	{
			$mois = $laLigne['mois'];
			$dateModif = $laLigne['dateModif'];
			$montantValide = $laLigne['montantValide'];
			$idEtat = $laLigne['idEtat'];
			$libEtat = $laLigne['libEtat'];
			$lesFiches["$mois"]=array(
			 "mois" => "$mois",
			 "dateModif" => "$dateModif",
			 "montantValide" => "$montantValide",
			 "libEtat" => "$libEtat");
			$laLigne = $res->fetch(); 		
		}
		return $lesFiches;// retourne le mois, la dateModif, le montantValide et le libEtat des fiches de frais concernes sous forme de tableau.
	}
	 
}
?>
