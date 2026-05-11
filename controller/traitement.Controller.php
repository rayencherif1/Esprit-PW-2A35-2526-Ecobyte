<?php
require_once __DIR__ . '/../Model/traitement.php';
require_once __DIR__ . '/../config.php';

class TraitementC
{
    function addTraitement($traitement)
    {
        $sql = "INSERT INTO traitement (nom_traitement, conseils, interdiction, id_allergie) 
                VALUES (:nom_traitement, :conseils, :interdiction, :id_allergie)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom_traitement' => $traitement->getNomTraitement(),
                'conseils'       => $traitement->getConseils(),
                'interdiction'   => $traitement->getInterdiction(),
                'id_allergie'    => $traitement->getIdAllergie(),
            ]);
            return $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Erreur addTraitement: ' . $e->getMessage());
            return false;
        }
    }

    function listTraitement()
    {
        $sql = "SELECT * FROM traitement ORDER BY id_traitement DESC";
        $db = config::getConnexion();
        try {
            $result = $db->query($sql);
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur listTraitement: ' . $e->getMessage());
            return [];
        }
    }

    function listTraitementByAllergie($id_allergie)
    {
        $sql = "SELECT * FROM traitement WHERE id_allergie = :id_allergie ORDER BY id_traitement DESC";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_allergie' => $id_allergie]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur listTraitementByAllergie: ' . $e->getMessage());
            return [];
        }
    }

    function getTraitementById($id)
    {
        $sql = "SELECT * FROM traitement WHERE id_traitement = :id LIMIT 1";
        $db  = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur getTraitementById: ' . $e->getMessage());
            return false;
        }
    }

    function updateTraitement($traitement, $id)
    {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                "UPDATE traitement SET
                    nom_traitement = :nom_traitement,
                    conseils = :conseils,
                    interdiction = :interdiction,
                    id_allergie = :id_allergie
                 WHERE id_traitement = :id"
            );

            $result = $query->execute([
                'id'             => $id,
                'nom_traitement' => $traitement->getNomTraitement(),
                'conseils'       => $traitement->getConseils(),
                'interdiction'   => $traitement->getInterdiction(),
                'id_allergie'    => $traitement->getIdAllergie(),
            ]);

            return $result;
        } catch (PDOException $e) {
            error_log('Erreur updateTraitement: ' . $e->getMessage());
            return false;
        }
    }

    function deleteTraitement($id)
    {
        $sql = "DELETE FROM traitement WHERE id_traitement = :id";
        $db = config::getConnexion();
        
        try {
            $req = $db->prepare($sql);
            $result = $req->execute(['id' => $id]);
            return $result;
        } catch (PDOException $e) {
            error_log('Erreur deleteTraitement: ' . $e->getMessage());
            return false;
        }
    }
}
?>