<?php
/**
 * Tableau de bord admin : statistiques types d’exercices (pour graphique circulaire).
 */

declare(strict_types=1);

final class AdminDashboardController
{
    public function dispatch(): void
    {
        $model = new ExerciseModel();
        $counts = $model->countByType(); // musculation, cardio, perte_de_poids

        $total = array_sum($counts); // Somme pour calcul pourcentage
        $percents = [];
        foreach ($counts as $k => $v) {
            $percents[$k] = $total > 0 ? round(100 * $v / $total, 1) : 0.0; // Évite division par zéro
        }

        View::render('admin/dashboard', [
            'counts' => $counts,
            'percents' => $percents,
            'total' => $total,
        ]);
    }
}
