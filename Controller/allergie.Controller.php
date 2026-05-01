<?php
require_once __DIR__ . '/../Model/allergie.php';
require_once __DIR__ . '/../config.php';

class AllergieC
{
    // ✅ AJOUT
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

            return $result ? $db->lastInsertId() : false;

        } catch (PDOException $e) {
            error_log('Erreur addAllergie: ' . $e->getMessage());
            return false;
        }
    }

    // ✅ LISTE + FILTRE + RECHERCHE (début du mot)
    function listAllergie($gravite = null, $search = null)
    {
        $db = config::getConnexion();

        try {
            $sql = "SELECT * FROM allergie WHERE 1=1";
            $params = [];

            // 🔍 Recherche (commence par)
            if (!empty($search)) {
                $sql .= " AND nom LIKE :search";
                $params[':search'] = $search . "%";
            }

            // 🎯 Filtre gravité
            if (!empty($gravite)) {
                $sql .= " AND gravite = :gravite";
                $params[':gravite'] = $gravite;
            }

            $sql .= " ORDER BY id_allergie DESC";

            $query = $db->prepare($sql);
            $query->execute($params);

            return $query->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('Erreur listAllergie: ' . $e->getMessage());
            return [];
        }
    }

    // ✅ GET BY ID
    function getAllergieById($id)
    {
        $db = config::getConnexion();

        try {
            $query = $db->prepare("SELECT * FROM allergie WHERE id_allergie = :id LIMIT 1");
            $query->execute([':id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('Erreur getAllergieById: ' . $e->getMessage());
            return false;
        }
    }

    // ✅ UPDATE
    function updateAllergie($allergie, $id)
    {
        $db = config::getConnexion();

        try {
            $query = $db->prepare(
                "UPDATE allergie SET
                    nom = :nom,
                    description = :description,
                    gravite = :gravite,
                    symptomes = :symptomes
                WHERE id_allergie = :id"
            );

            return $query->execute([
                ':id' => $id,
                ':nom' => $allergie->getNom(),
                ':description' => $allergie->getDescription(),
                ':gravite' => $allergie->getGravite(),
                ':symptomes' => $allergie->getSymptomes()
            ]);

        } catch (PDOException $e) {
            error_log('Erreur updateAllergie: ' . $e->getMessage());
            return false;
        }
    }

    // ✅ DELETE
    function deleteAllergie($id)
    {
        $db = config::getConnexion();

        try {
            $query = $db->prepare("DELETE FROM allergie WHERE id_allergie = :id");
            return $query->execute([':id' => $id]);

        } catch (PDOException $e) {
            error_log('Erreur deleteAllergie: ' . $e->getMessage());
            return false;
        }
    }

    // ✅ NOUVELLE MÉTHODE : Récupérer tous les noms d'allergies (pour le chatbot)
    function getAllAllergieNames()
    {
        $db = config::getConnexion();
        try {
            $query = $db->query("SELECT id_allergie, nom FROM allergie ORDER BY nom");
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur getAllAllergieNames: ' . $e->getMessage());
            return [];
        }
    }
}
?>