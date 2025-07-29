<?php
require_once 'config/database.php';

class MatchManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Vérifier si un arbitre peut être désigné pour un match
     * Contraintes: vérification des équipes déjà arbitrées (avertissement seulement)
     */
    public function verifierDisponibiliteArbitre($arbitre_id, $match_id, $role, $date_match = null, $heure_match = null, $equipe_a_id = null, $equipe_b_id = null) {
        $resultat = [
            'disponible' => true,
            'raisons' => [],
            'avertissements' => []
        ];
        
        // Si c'est un nouveau match (match_id = 0), utiliser les paramètres fournis
        if ($match_id == 0) {
            if (!$date_match || !$heure_match || !$equipe_a_id || !$equipe_b_id) {
                return $resultat; // Pas assez d'informations pour vérifier
            }
        } else {
            // Récupérer les informations du match existant
            $match = $this->getMatchInfo($match_id);
            if (!$match) {
                $resultat['disponible'] = false;
                $resultat['raisons'][] = "Match non trouvé";
                return $resultat;
            }
            $date_match = $match['date_match'];
            $heure_match = $match['heure_match'];
            $equipe_a_id = $match['equipe_a_id'];
            $equipe_b_id = $match['equipe_b_id'];
        }
        
        // Vérifier si l'arbitre est déjà désigné pour un autre match à la même heure le même jour
        if ($this->arbitreDejaDesignerMemeHeure($arbitre_id, $date_match, $heure_match, $match_id)) {
            $resultat['disponible'] = false;
            $resultat['raisons'][] = "Cet arbitre est déjà désigné pour un autre match à la même heure le même jour";
        }
        
        // Vérifier si l'arbitre a déjà arbitré une des équipes du match (avertissement seulement)
        $equipes_match = [$equipe_a_id, $equipe_b_id];
        foreach ($equipes_match as $equipe_id) {
            // Vérifier le même jour à la même heure
            if ($this->arbitreDejaArbitreEquipe($arbitre_id, $equipe_id, $date_match, $heure_match)) {
                $nom_equipe = $this->getNomEquipe($equipe_id);
                $arbitre = $this->getArbitreById($arbitre_id);
                $nom_arbitre = $arbitre ? $arbitre['nom'] . ' ' . $arbitre['prenom'] : 'Cet arbitre';
                $resultat['avertissements'][] = "$nom_arbitre a déjà arbitré l'équipe $nom_equipe le même jour à la même heure";
            }
            
            // Vérifier l'historique complet
            $nb_matchs_historique = $this->arbitreDejaArbitreEquipeHistorique($arbitre_id, $equipe_id);
            if ($nb_matchs_historique > 0) {
                $nom_equipe = $this->getNomEquipe($equipe_id);
                $arbitre = $this->getArbitreById($arbitre_id);
                $nom_arbitre = $arbitre ? $arbitre['nom'] . ' ' . $arbitre['prenom'] : 'Cet arbitre';
                $resultat['avertissements'][] = "$nom_arbitre a déjà arbitré l'équipe $nom_equipe $nb_matchs_historique fois dans l'historique";
            }
        }
        
        return $resultat;
    }
    

    
    /**
     * Vérifier si un arbitre est déjà désigné pour un autre match à la même heure le même jour
     */
    private function arbitreDejaDesignerMemeHeure($arbitre_id, $date_match, $heure_match, $match_id) {
        $sql = "SELECT COUNT(*) as nb_matchs 
                FROM matchs 
                WHERE (arbitre_id = ? OR assistant_1_id = ? OR assistant_2_id = ? OR officiel_4_id = ? OR assesseur_id = ?)
                AND date_match = ?
                AND heure_match = ?
                AND id != ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$arbitre_id, $arbitre_id, $arbitre_id, $arbitre_id, $arbitre_id, $date_match, $heure_match, $match_id]);
        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultat['nb_matchs'] > 0;
    }
    
    /**
     * Vérifier si un arbitre a déjà arbitré une équipe le même jour à la même heure
     */
    private function arbitreDejaArbitreEquipe($arbitre_id, $equipe_id, $date_match, $heure_match = null) {
        $sql = "SELECT COUNT(*) as nb_matchs 
                FROM matchs 
                WHERE (arbitre_id = ? OR assistant_1_id = ? OR assistant_2_id = ? OR officiel_4_id = ? OR assesseur_id = ?)
                AND (equipe_a_id = ? OR equipe_b_id = ?)
                AND date_match = ?";
        
        $params = [$arbitre_id, $arbitre_id, $arbitre_id, $arbitre_id, $arbitre_id, $equipe_id, $equipe_id, $date_match];
        
        if ($heure_match) {
            $sql .= " AND heure_match = ?";
            $params[] = $heure_match;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultat['nb_matchs'] > 0;
    }
    
    /**
     * Vérifier si un arbitre a déjà arbitré une équipe dans l'historique complet
     */
    private function arbitreDejaArbitreEquipeHistorique($arbitre_id, $equipe_id) {
        $sql = "SELECT COUNT(*) as nb_matchs 
                FROM matchs 
                WHERE (arbitre_id = ? OR assistant_1_id = ? OR assistant_2_id = ? OR officiel_4_id = ? OR assesseur_id = ?)
                AND (equipe_a_id = ? OR equipe_b_id = ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$arbitre_id, $arbitre_id, $arbitre_id, $arbitre_id, $arbitre_id, $equipe_id, $equipe_id]);
        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultat['nb_matchs'];
    }
    
    /**
     * Vérifier les conflits de rôles dans un même match
     */
    public function verifierConflitsRoles($data) {
        $arbitres = [];
        $conflits = [];
        
        // Collecter tous les arbitres désignés
        if ($data['arbitre_id']) $arbitres['arbitre_id'] = $data['arbitre_id'];
        if ($data['assistant_1_id']) $arbitres['assistant_1_id'] = $data['assistant_1_id'];
        if ($data['assistant_2_id']) $arbitres['assistant_2_id'] = $data['assistant_2_id'];
        if ($data['officiel_4_id']) $arbitres['officiel_4_id'] = $data['officiel_4_id'];
        if ($data['assesseur_id']) $arbitres['assesseur_id'] = $data['assesseur_id'];
        
        // Vérifier les doublons
        $arbitres_values = array_values($arbitres);
        $doublons = array_diff_assoc($arbitres_values, array_unique($arbitres_values));
        
        foreach ($doublons as $arbitre_id) {
            $arbitre = $this->getArbitreById($arbitre_id);
            if ($arbitre) {
                $conflits[] = "L'arbitre {$arbitre['nom']} {$arbitre['prenom']} est sélectionné pour plusieurs rôles";
            }
        }
        
        return $conflits;
    }
    
    /**
     * Ajouter un nouveau match
     */
    public function ajouterMatch($data) {
        try {
            // Vérifier les conflits de rôles
            $conflits_roles = $this->verifierConflitsRoles($data);
            if (!empty($conflits_roles)) {
                return [
                    'success' => false,
                    'message' => 'Erreur de conflit: ' . implode(', ', $conflits_roles)
                ];
            }
            
            // Vérifier les contraintes avant l'ajout
            $arbitres_a_verifier = [
                'arbitre_id' => 'Arbitre Principal',
                'assistant_1_id' => 'Assistant 1',
                'assistant_2_id' => 'Assistant 2',
                'officiel_4_id' => '4ème Officiel',
                'assesseur_id' => 'Assesseur'
            ];
            
            foreach ($arbitres_a_verifier as $field => $role) {
                if ($data[$field]) {
                    $verification = $this->verifierDisponibiliteArbitre(
                        $data[$field], 
                        0, 
                        $role, 
                        $data['date_match'], 
                        $data['heure_match'], 
                        $data['equipe_a_id'], 
                        $data['equipe_b_id']
                    );
                    
                    if (!$verification['disponible']) {
                        return [
                            'success' => false,
                            'message' => 'Erreur de contrainte: ' . implode(', ', $verification['raisons'])
                        ];
                    }
                }
            }
            
            $this->pdo->beginTransaction();
            
            $sql = "INSERT INTO matchs (ligue_id, nom_competition, ville, stade, tour, date_match, heure_match, 
                    equipe_a_id, equipe_b_id, arbitre_id, assistant_1_id, assistant_2_id, officiel_4_id, assesseur_id, publier) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['ligue_id'],
                $data['nom_competition'],
                $data['ville'],
                $data['stade'],
                $data['tour'],
                $data['date_match'],
                $data['heure_match'],
                $data['equipe_a_id'],
                $data['equipe_b_id'],
                $data['arbitre_id'],
                $data['assistant_1_id'],
                $data['assistant_2_id'],
                $data['officiel_4_id'],
                $data['assesseur_id'],
                $data['publier']
            ]);
            
            $match_id = $this->pdo->lastInsertId();
            
            // Si publié, envoyer les emails
            if ($data['publier'] == 'Oui') {
                $this->envoyerEmailsDesignation($match_id);
            }
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Match ajouté avec succès',
                'match_id' => $match_id
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'ajout: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Modifier un match
     */
    public function modifierMatch($match_id, $data) {
        try {
            $this->pdo->beginTransaction();
            
            $sql = "UPDATE matchs SET 
                    ligue_id = ?, nom_competition = ?, ville = ?, stade = ?, tour = ?, 
                    date_match = ?, heure_match = ?, equipe_a_id = ?, equipe_b_id = ?,
                    arbitre_id = ?, assistant_1_id = ?, assistant_2_id = ?, officiel_4_id = ?, 
                    assesseur_id = ?, publier = ?
                    WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['ligue_id'],
                $data['nom_competition'],
                $data['ville'],
                $data['stade'],
                $data['tour'],
                $data['date_match'],
                $data['heure_match'],
                $data['equipe_a_id'],
                $data['equipe_b_id'],
                $data['arbitre_id'],
                $data['assistant_1_id'],
                $data['assistant_2_id'],
                $data['officiel_4_id'],
                $data['assesseur_id'],
                $data['publier'],
                $match_id
            ]);
            
            // Si publié, envoyer les emails
            if ($data['publier'] == 'Oui') {
                $this->envoyerEmailsDesignation($match_id);
            }
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Match modifié avec succès'
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            return [
                'success' => false,
                'message' => 'Erreur lors de la modification: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Supprimer un match
     */
    public function supprimerMatch($match_id) {
        try {
            $sql = "DELETE FROM matchs WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$match_id]);
            
            return [
                'success' => true,
                'message' => 'Match supprimé avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Récupérer tous les matchs
     */
    public function getMatchs() {
        $sql = "SELECT m.*, l.nom as ligue_nom, 
                       e1.nom as equipe_a_nom, e2.nom as equipe_b_nom,
                       a1.nom as arbitre_nom, a1.prenom as arbitre_prenom,
                       as1.nom as assistant1_nom, as1.prenom as assistant1_prenom,
                       as2.nom as assistant2_nom, as2.prenom as assistant2_prenom,
                       o4.nom as officiel4_nom, o4.prenom as officiel4_prenom,
                       ass.nom as assesseur_nom, ass.prenom as assesseur_prenom
                FROM matchs m
                LEFT JOIN ligues l ON m.ligue_id = l.id
                LEFT JOIN equipes e1 ON m.equipe_a_id = e1.id
                LEFT JOIN equipes e2 ON m.equipe_b_id = e2.id
                LEFT JOIN arbitres a1 ON m.arbitre_id = a1.id
                LEFT JOIN arbitres as1 ON m.assistant_1_id = as1.id
                LEFT JOIN arbitres as2 ON m.assistant_2_id = as2.id
                LEFT JOIN arbitres o4 ON m.officiel_4_id = o4.id
                LEFT JOIN arbitres ass ON m.assesseur_id = ass.id
                ORDER BY m.date_match DESC, m.heure_match DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer un match par ID
     */
    public function getMatchById($match_id) {
        $sql = "SELECT m.*, l.nom as ligue_nom, 
                       e1.nom as equipe_a_nom, e2.nom as equipe_b_nom,
                       a1.nom as arbitre_nom, a1.prenom as arbitre_prenom,
                       as1.nom as assistant1_nom, as1.prenom as assistant1_prenom,
                       as2.nom as assistant2_nom, as2.prenom as assistant2_prenom,
                       o4.nom as officiel4_nom, o4.prenom as officiel4_prenom,
                       ass.nom as assesseur_nom, ass.prenom as assesseur_prenom
                FROM matchs m
                LEFT JOIN ligues l ON m.ligue_id = l.id
                LEFT JOIN equipes e1 ON m.equipe_a_id = e1.id
                LEFT JOIN equipes e2 ON m.equipe_b_id = e2.id
                LEFT JOIN arbitres a1 ON m.arbitre_id = a1.id
                LEFT JOIN arbitres as1 ON m.assistant_1_id = as1.id
                LEFT JOIN arbitres as2 ON m.assistant_2_id = as2.id
                LEFT JOIN arbitres o4 ON m.officiel_4_id = o4.id
                LEFT JOIN arbitres ass ON m.assesseur_id = ass.id
                WHERE m.id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$match_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer les informations d'un match
     */
    private function getMatchInfo($match_id) {
        $sql = "SELECT * FROM matchs WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$match_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer le nom d'une équipe
     */
    private function getNomEquipe($equipe_id) {
        $sql = "SELECT nom FROM equipes WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$equipe_id]);
        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultat ? $resultat['nom'] : 'Inconnue';
    }
    
    /**
     * Envoyer les emails de désignation
     */
    private function envoyerEmailsDesignation($match_id) {
        $match = $this->getMatchById($match_id);
        
        // Liste des arbitres à notifier
        $arbitres = [];
        if ($match['arbitre_id']) $arbitres[] = $match['arbitre_id'];
        if ($match['assistant_1_id']) $arbitres[] = $match['assistant_1_id'];
        if ($match['assistant_2_id']) $arbitres[] = $match['assistant_2_id'];
        if ($match['officiel_4_id']) $arbitres[] = $match['officiel_4_id'];
        if ($match['assesseur_id']) $arbitres[] = $match['assesseur_id'];
        
        foreach ($arbitres as $arbitre_id) {
            $arbitre = $this->getArbitreById($arbitre_id);
            if ($arbitre && $arbitre['email']) {
                $this->envoyerEmailArbitre($arbitre, $match);
            }
        }
    }
    
    /**
     * Récupérer un arbitre par ID
     */
    private function getArbitreById($arbitre_id) {
        $sql = "SELECT * FROM arbitres WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$arbitre_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Envoyer email à un arbitre
     */
    private function envoyerEmailArbitre($arbitre, $match) {
        $to = $arbitre['email'];
        $subject = "Désignation d'arbitrage - " . $match['nom_competition'];
        
        $message = "Bonjour " . $arbitre['prenom'] . " " . $arbitre['nom'] . ",\n\n";
        $message .= "Vous avez été désigné pour arbitrer le match suivant :\n\n";
        $message .= "Compétition : " . $match['nom_competition'] . "\n";
        $message .= "Match : " . $match['equipe_a_nom'] . " vs " . $match['equipe_b_nom'] . "\n";
        $message .= "Date : " . date('d/m/Y', strtotime($match['date_match'])) . "\n";
        $message .= "Heure : " . $match['heure_match'] . "\n";
        $message .= "Lieu : " . $match['stade'] . " - " . $match['ville'] . "\n";
        $message .= "Tour : " . $match['tour'] . "\n\n";
        $message .= "Merci de confirmer votre disponibilité.\n\n";
        $message .= "Cordialement,\nL'équipe de désignation";
        
        $headers = "From: designation@arbitrage.com\r\n";
        $headers .= "Reply-To: designation@arbitrage.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // En production, utiliser une vraie fonction d'envoi d'email
        // mail($to, $subject, $message, $headers);
        
        // Pour le développement, on simule l'envoi
        error_log("Email envoyé à $to: $subject");
    }
    
    /**
     * Générer le PDF de désignation
     */
    public function genererPDFDesignation($match_id) {
        $match = $this->getMatchById($match_id);
        
        if (!$match) {
            return false;
        }
        
        // Ici vous pouvez utiliser une bibliothèque comme TCPDF ou FPDF
        // Pour l'instant, on retourne les données du match
        return $match;
    }
}
?> 