<?php
/**
 * Modèle Exercice : accès base de données (SELECT / INSERT / UPDATE / DELETE).
 * Aucune validation métier ici — uniquement des requêtes préparées PDO.
 */

declare(strict_types=1);

final class ExerciseModel
{
    /**
     * Liste tous les exercices (option filtre type + recherche nom).
     *
     * @return list<array<string,mixed>>
     */
    public function findAll(?string $typeFilter = null, ?string $searchName = null): array
    {
        $pdo = Database::getPdo(); // Connexion partagée

        $sql = 'SELECT * FROM exercice WHERE 1=1'; // Base de la requête
        $params = []; // Paramètres liés (sécurité injection SQL)

        if ($typeFilter !== null && $typeFilter !== '') {
            $sql .= ' AND type_exercice = :type'; // Filtre par colonne enum
            $params['type'] = $typeFilter; // Valeur liée
        }

        if ($searchName !== null && $searchName !== '') {
            $sql .= ' AND nom LIKE :nom'; // Recherche partielle insensible à la casse
            $params['nom'] = '%' . $searchName . '%'; // Motif LIKE
        }

        $sql .= ' ORDER BY nom ASC'; // Ordre alphabétique

        $stmt = $pdo->prepare($sql); // Requête préparée
        $stmt->execute($params); // Injection des paramètres échappés par PDO

        return $stmt->fetchAll(); // Tableau de lignes
    }

    /**
     * Compte les exercices par type (pour le graphique admin).
     *
     * @return array<string,int> type_exercice => nombre
     */
    public function countByType(): array
    {
        $pdo = Database::getPdo();

        $sql = 'SELECT type_exercice, COUNT(*) AS total FROM exercice GROUP BY type_exercice';
        $stmt = $pdo->query($sql); // Pas de variable utilisateur — query acceptable

        $out = ['musculation' => 0, 'cardio' => 0, 'perte_de_poids' => 0]; // Valeurs par défaut

        while ($row = $stmt->fetch()) {
            $out[$row['type_exercice']] = (int) $row['total']; // Cast entier
        }

        return $out; // Pourcentages calculés dans la vue / contrôleur
    }

    /**
     * Trouve un exercice par identifiant ou retourne null.
     *
     * @return array<string,mixed>|null
     */
    public function findById(int $id): ?array
    {
        $pdo = Database::getPdo();

        $sql = 'SELECT * FROM exercice WHERE id = :id LIMIT 1'; // Une seule ligne attendue
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]); // Liaison nommée

        $row = $stmt->fetch();

        return $row === false ? null : $row; // null si inexistant
    }

    /**
     * Insère un exercice et retourne l’ID auto-incrémenté.
     */
    public function insert(array $fields): int
    {
        $pdo = Database::getPdo();

        $sql = 'INSERT INTO exercice (nom, type_exercice, etapes, benefices, url_image, url_video, nb_repetitions_suggerees, muscle_wger_id)
                VALUES (:nom, :type_exercice, :etapes, :benefices, :url_image, :url_video, :nb_rep, :muscle_wger_id)';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nom' => $fields['nom'],
            'type_exercice' => $fields['type_exercice'],
            'etapes' => $fields['etapes'],
            'benefices' => $fields['benefices'],
            'url_image' => $fields['url_image'],
            'url_video' => $fields['url_video'],
            'nb_rep' => $fields['nb_repetitions_suggerees'],
            'muscle_wger_id' => $fields['muscle_wger_id'],
        ]);

        return (int) $pdo->lastInsertId(); // PK générée par MySQL
    }

    /**
     * Met à jour un exercice existant.
     */
    public function update(int $id, array $fields): void
    {
        $pdo = Database::getPdo();

        $sql = 'UPDATE exercice SET nom = :nom, type_exercice = :type_exercice, etapes = :etapes, benefices = :benefices,
                url_image = :url_image, url_video = :url_video, nb_repetitions_suggerees = :nb_rep, muscle_wger_id = :muscle_wger_id
                WHERE id = :id';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'nom' => $fields['nom'],
            'type_exercice' => $fields['type_exercice'],
            'etapes' => $fields['etapes'],
            'benefices' => $fields['benefices'],
            'url_image' => $fields['url_image'],
            'url_video' => $fields['url_video'],
            'nb_rep' => $fields['nb_repetitions_suggerees'],
            'muscle_wger_id' => $fields['muscle_wger_id'],
        ]);
    }

    /**
     * Supprime un exercice (les liaisons programme_exercice tombent en CASCADE).
     */
    public function delete(int $id): void
    {
        $pdo = Database::getPdo();

        $sql = 'DELETE FROM exercice WHERE id = :id'; // Suppression dure
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
    }
}
