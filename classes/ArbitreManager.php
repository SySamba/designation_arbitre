<?php
require_once __DIR__ . '/../config/database.php';

class ArbitreManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Ajouter un arbitre
     */
    public function ajouterArbitre($data) {
        try {
            // Validation du numéro de téléphone sénégalais
            if (!empty($data['telephone'])) {
                if (!$this->validerTelephoneSenegal($data['telephone'])) {
                    return [
                        'success' => false,
                        'message' => 'Le numéro de téléphone doit commencer par 7 et contenir 9 chiffres (ex: 701234567)'
                    ];
                }
            }
            
            $sql = "INSERT INTO arbitres (nom, prenom, adresse, email, telephone, fonction, photo) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['nom'], 
                $data['prenom'], 
                $data['adresse'], 
                $data['email'],
                $data['telephone'] ?? null,
                $data['fonction'] ?? 'Arbitre',
                $data['photo'] ?? null
            ]);
            
            return [
                'success' => true,
                'message' => 'Arbitre ajouté avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'ajout: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Modifier un arbitre
     */
    public function modifierArbitre($arbitre_id, $data) {
        try {
            // Validation du numéro de téléphone sénégalais
            if (!empty($data['telephone'])) {
                if (!$this->validerTelephoneSenegal($data['telephone'])) {
                    return [
                        'success' => false,
                        'message' => 'Le numéro de téléphone doit commencer par 7 et contenir 9 chiffres (ex: 701234567)'
                    ];
                }
            }
            
            $sql = "UPDATE arbitres SET nom = ?, prenom = ?, adresse = ?, email = ?, telephone = ?, fonction = ?, photo = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['nom'], 
                $data['prenom'], 
                $data['adresse'], 
                $data['email'],
                $data['telephone'] ?? null,
                $data['fonction'] ?? 'Arbitre',
                $data['photo'] ?? null,
                $arbitre_id
            ]);
            
            return [
                'success' => true,
                'message' => 'Arbitre modifié avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la modification: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Supprimer un arbitre
     */
    public function supprimerArbitre($arbitre_id) {
        try {
            // Vérifier si l'arbitre est utilisé dans des matchs
            $sql = "SELECT COUNT(*) as nb_matchs FROM matchs 
                    WHERE arbitre_id = ? OR assistant_1_id = ? OR assistant_2_id = ? 
                    OR officiel_4_id = ? OR assesseur_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$arbitre_id, $arbitre_id, $arbitre_id, $arbitre_id, $arbitre_id]);
            $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultat['nb_matchs'] > 0) {
                return [
                    'success' => false,
                    'message' => 'Impossible de supprimer cet arbitre car il est utilisé dans des matchs'
                ];
            }
            
            $sql = "DELETE FROM arbitres WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$arbitre_id]);
            
            return [
                'success' => true,
                'message' => 'Arbitre supprimé avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Récupérer tous les arbitres
     */
    public function getArbitres() {
        $sql = "SELECT * FROM arbitres ORDER BY nom, prenom";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer les arbitres par fonction
     */
    public function getArbitresByFunction($fonction) {
        $sql = "SELECT * FROM arbitres WHERE fonction = ? ORDER BY nom, prenom";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$fonction]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer un arbitre par ID
     */
    public function getArbitreById($arbitre_id) {
        $sql = "SELECT * FROM arbitres WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$arbitre_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer l'historique des arbitrages d'un arbitre
     */
    public function getHistoriqueArbitre($arbitre_id) {
        $sql = "SELECT m.*, e1.nom as equipe_a_nom, e2.nom as equipe_b_nom,
                       CASE 
                           WHEN m.arbitre_id = ? THEN 'Arbitre Principal'
                           WHEN m.assistant_1_id = ? THEN 'Assistant 1'
                           WHEN m.assistant_2_id = ? THEN 'Assistant 2'
                           WHEN m.officiel_4_id = ? THEN '4ème Officiel'
                           WHEN m.assesseur_id = ? THEN 'Assesseur'
                       END as role
                FROM matchs m
                JOIN equipes e1 ON m.equipe_a_id = e1.id
                JOIN equipes e2 ON m.equipe_b_id = e2.id
                WHERE m.arbitre_id = ? OR m.assistant_1_id = ? OR m.assistant_2_id = ? 
                      OR m.officiel_4_id = ? OR m.assesseur_id = ?
                ORDER BY m.date_match DESC, m.heure_match DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$arbitre_id, $arbitre_id, $arbitre_id, $arbitre_id, $arbitre_id, 
                       $arbitre_id, $arbitre_id, $arbitre_id, $arbitre_id, $arbitre_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Valider un numéro de téléphone sénégalais
     * Format: 7XXXXXXXX (9 chiffres commençant par 7)
     */
    private function validerTelephoneSenegal($telephone) {
        // Nettoyer le numéro (enlever espaces, tirets, etc.)
        $telephone = preg_replace('/[^0-9]/', '', $telephone);
        
        // Vérifier que c'est exactement 9 chiffres commençant par 7
        return preg_match('/^7[0-9]{8}$/', $telephone);
    }
}
?> 