<?php
/**
 * Modèle Programme + table de jointure programme_exercice.
 */

declare(strict_types=1);

final class ProgramModel
{
    /**
     * Liste les programmes avec filtres optionnels.
     *
     * @return list<array<string,mixed>>
     */
    public function findAll(?string $typeFilter = null, ?string $searchName = null): array
    {
        $pdo = Database::getPdo();

        $sql = 'SELECT * FROM programmes WHERE 1=1';
        $params = [];

        if ($typeFilter !== null && $typeFilter !== '') {
            $sql .= ' AND type_programme = :type';
            $params['type'] = $typeFilter;
        }

        if ($searchName !== null && $searchName !== '') {
            $sql .= ' AND nom LIKE :nom';
            $params['nom'] = '%' . $searchName . '%';
        }

        $sql .= ' ORDER BY nom ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Détail programme + liste ordonnée des exercices liés.
     *
     * @return array{program: array<string,mixed>|null, exercises: list<array<string,mixed>>}
     */
    public function findWithExercises(int $id): array
    {
        $pdo = Database::getPdo();

        $stmt = $pdo->prepare('SELECT * FROM programmes WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $program = $stmt->fetch();

        if ($program === false) {
            return ['program' => null, 'exercises' => []];
        }

        $sql = 'SELECT e.*, pe.ordre, pe.repetitions AS repetitions_programme
                FROM programme_exercice pe
                INNER JOIN exercices e ON e.id = pe.exercice_id
                WHERE pe.programme_id = :pid
                ORDER BY pe.ordre ASC';

        $stmt2 = $pdo->prepare($sql);
        $stmt2->execute(['pid' => $id]);

        return ['program' => $program, 'exercises' => $stmt2->fetchAll()];
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::getPdo();
        $stmt = $pdo->prepare('SELECT * FROM programmes WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Premier programme correspondant au type (pour « IA » rule-based).
     */
    public function findFirstByType(string $type): ?array
    {
        $pdo = Database::getPdo();
        $stmt = $pdo->prepare('SELECT * FROM programmes WHERE type_programme = :t ORDER BY id ASC LIMIT 1');
        $stmt->execute(['t' => $type]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Insère un programme et ses exercices (transaction).
     *
     * @param list<int> $exerciseIds ordre = ordre du tableau
     * @param list<int|null> $repsPerExercise même taille que $exerciseIds (null = défaut BDD exercice)
     */
    public function insert(string $nom, int $dureeSemaines, string $type, array $exerciseIds, array $repsPerExercise): int
    {
        $pdo = Database::getPdo();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO programmes (nom, duree_semaines, type_programme) VALUES (:nom, :duree, :type)'
            );
            $stmt->execute(['nom' => $nom, 'duree' => $dureeSemaines, 'type' => $type]);
            $pid = (int) $pdo->lastInsertId();

            $this->syncExercises($pdo, $pid, $exerciseIds, $repsPerExercise);

            $pdo->commit();

            return $pid;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function update(int $id, string $nom, int $dureeSemaines, string $type, array $exerciseIds, array $repsPerExercise): void
    {
        $pdo = Database::getPdo();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'UPDATE programmes SET nom = :nom, duree_semaines = :duree, type_programme = :type WHERE id = :id'
            );
            $stmt->execute(['nom' => $nom, 'duree' => $dureeSemaines, 'type' => $type, 'id' => $id]);

            $stmtDel = $pdo->prepare('DELETE FROM programme_exercice WHERE programme_id = :id');
            $stmtDel->execute(['id' => $id]);

            $this->syncExercises($pdo, $id, $exerciseIds, $repsPerExercise);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @param list<int> $exerciseIds
     * @param list<int|null> $repsPerExercise
     */
    private function syncExercises(PDO $pdo, int $programId, array $exerciseIds, array $repsPerExercise): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions) VALUES (:pid, :eid, :ordre, :rep)'
        );

        $ordre = 1;

        foreach ($exerciseIds as $i => $eid) {
            $eid = (int) $eid;
            if ($eid <= 0) {
                continue;
            }

            $rep = $repsPerExercise[$i] ?? null;
            $repBind = $rep === null || $rep === '' ? null : (int) $rep;

            $stmt->execute([
                'pid' => $programId,
                'eid' => $eid,
                'ordre' => $ordre,
                'rep' => $repBind,
            ]);
            $ordre++;
        }
    }

    public function delete(int $id): void
    {
        $pdo = Database::getPdo();
        $stmt = $pdo->prepare('DELETE FROM programmes WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
