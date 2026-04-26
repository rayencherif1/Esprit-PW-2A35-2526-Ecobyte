<?php
require_once __DIR__ . '/../Model/commande.php'; // lien vers le model
require_once __DIR__ . '/../config.php';

class CommandeC
{
    // ➕ Ajouter une commande
    function addCommande($commande)
    {
        $sql = "INSERT INTO commandes (nom, prenom, telephone, traitement, quantite)
                VALUES (:nom, :prenom, :telephone, :traitement, :quantite)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom'        => $commande->getNom(),
                'prenom'     => $commande->getPrenom(),
                'telephone'  => $commande->getTelephone(),
                'traitement' => $commande->getTraitement(),
                'quantite'   => $commande->getQuantite(),
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    // 📋 Afficher toutes les commandes
    function listCommandes()
    {
        $sql = "SELECT * FROM commandes";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // 🔍 Récupérer une commande par ID
    function getCommandeById($id)
    {
        $sql = "SELECT * FROM commandes WHERE id = :id LIMIT 1";
        $db  = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // ✏️ Modifier une commande
    function updateCommande($commande, $id)
    {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE commandes SET
                    nom = :nom,
                    prenom = :prenom,
                    telephone = :telephone,
                    traitement = :traitement,
                    quantite = :quantite
                WHERE id = :id'
            );

            $query->execute([
                'id'         => $id,
                'nom'        => $commande->getNom(),
                'prenom'     => $commande->getPrenom(),
                'telephone'  => $commande->getTelephone(),
                'traitement' => $commande->getTraitement(),
                'quantite'   => $commande->getQuantite(),
            ]);

            echo $query->rowCount() . " records UPDATED successfully <br>";
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    // ❌ Supprimer une commande
    function deleteCommande($id)
    {
        $sql = "DELETE FROM commandes WHERE id = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);

        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }
}
?>