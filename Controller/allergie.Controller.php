<?php
require_once __DIR__ . '/../Model/allergie.php';
require_once __DIR__ . '/../config.php';

class AllergieC
{
    function addAllergie($allergie)
    {
        $sql = "INSERT INTO allergie (nom, description, gravite, symptomes) 
                VALUES (:nom, :description, :gravite, :symptomes)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $result = $query->execute([
                ':nom' => $allergie->getNom(),
                ':description' => $allergie->getDescription(),
                ':gravite' => $allergie->getGravite(),
                ':symptomes' => $allergie->getSymptomes()
            ]);
            
            if ($result) {
                return $db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log('Erreur addAllergie: ' . $e->getMessage());
            return false;
        }
    }

    function listAllergie()
    {
        $sql = "SELECT * FROM allergie ORDER BY id_allergie DESC";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur listAllergie: ' . $e->getMessage());
            return [];
        }
    }

    function getAllergieById($id)
    {
        $sql = "SELECT * FROM allergie WHERE id_allergie = :id LIMIT 1";
        $db  = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur getAllergieById: ' . $e->getMessage());
            return false;
        }
    }

    function updateAllergie($allergie, $id)
    {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE allergie SET
                    nom = :nom,
                    description = :description,
                    gravite = :gravite,
                    symptomes = :symptomes
                WHERE id_allergie = :id'
            );

            $result = $query->execute([
                ':id' => $id,
                ':nom' => $allergie->getNom(),
                ':description' => $allergie->getDescription(),
                ':gravite' => $allergie->getGravite(),
                ':symptomes' => $allergie->getSymptomes()
            ]);

            return $result;
        } catch (PDOException $e) {
            error_log('Erreur updateAllergie: ' . $e->getMessage());
            return false;
        }
    }

    function deleteAllergie($ide)
    {
        $sql = "DELETE FROM allergie WHERE id_allergie = :id";
        $db = config::getConnexion();
        
        try {
            $req = $db->prepare($sql);
            $result = $req->execute([':id' => $ide]);
            return $result;
        } catch (PDOException $e) {
            error_log('Erreur deleteAllergie: ' . $e->getMessage());
            return false;
        }
    }
}
?>