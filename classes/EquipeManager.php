<?php
require_once __DIR__ . '/../config/database.php';

class EquipeManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Ajouter une équipe
     */
    public function ajouterEquipe($data) {
        try {
            $sql = "INSERT INTO equipes (nom, ville) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data['nom'], $data['ville']]);
            
            return [
                'success' => true,
                'message' => 'Équipe ajoutée avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'ajout: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Modifier une équipe
     */
    public function modifierEquipe($equipe_id, $data) {
        try {
            $sql = "UPDATE equipes SET nom = ?, ville = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data['nom'], $data['ville'], $equipe_id]);
            
            return [
                'success' => true,
                'message' => 'Équipe modifiée avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la modification: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Supprimer une équipe
     */
    public function supprimerEquipe($equipe_id) {
        try {
            // Vérifier si l'équipe est utilisée dans des matchs
            $sql = "SELECT COUNT(*) as nb_matchs FROM matchs 
                    WHERE equipe_a_id = ? OR equipe_b_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$equipe_id, $equipe_id]);
            $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultat['nb_matchs'] > 0) {
                return [
                    'success' => false,
                    'message' => 'Impossible de supprimer cette équipe car elle est utilisée dans des matchs'
                ];
            }
            
            $sql = "DELETE FROM equipes WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$equipe_id]);
            
            return [
                'success' => true,
                'message' => 'Équipe supprimée avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Récupérer toutes les équipes
     */
    public function getEquipes() {
        $sql = "SELECT * FROM equipes ORDER BY nom";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer une équipe par ID
     */
    public function getEquipeById($equipe_id) {
        $sql = "SELECT * FROM equipes WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$equipe_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer l'historique des matchs d'une équipe
     */
    public function getHistoriqueEquipe($equipe_id) {
        $sql = "SELECT m.*, e1.nom as equipe_a_nom, e2.nom as equipe_b_nom,
                       a1.nom as arbitre_nom, a1.prenom as arbitre_prenom
                FROM matchs m
                JOIN equipes e1 ON m.equipe_a_id = e1.id
                JOIN equipes e2 ON m.equipe_b_id = e2.id
                LEFT JOIN arbitres a1 ON m.arbitre_id = a1.id
                WHERE m.equipe_a_id = ? OR m.equipe_b_id = ?
                ORDER BY m.date_match DESC, m.heure_match DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$equipe_id, $equipe_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 