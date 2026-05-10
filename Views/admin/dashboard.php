<?php
/**
 * Tableau de bord : graphique circulaire Chart.js + pourcentages par type d’exercice.
 * Variables : $counts, $percents, $total
 */
ob_start();
?>
<div class="flex flex-wrap mt-6">
    <div class="w-full max-w-full px-3 mb-6">
        <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
            <div class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent">
                <h6 class="dark:text-white mb-0">Répartition des exercices par type</h6>
                <p class="leading-normal text-sm text-slate-500">Basé sur la table <code>exercices</code> (MySQL).</p>
            </div>
            <div class="flex-auto p-6">
                <?php if ($total === 0) : ?>
                    <p class="text-amber-600">Aucun exercice en base — ajoutez-en depuis le menu « Exercices ».</p>
                <?php else : ?>
                    <div class="max-w-sm mx-auto">
                        <canvas id="chartTypes" width="280" height="280"></canvas>
                    </div>
                    <ul class="mt-4 text-sm space-y-1">
                        <?php foreach ($counts as $type => $nb) : ?>
                            <li>
                                <strong><?= e($type) ?></strong> :
                                <?= (int) $nb ?> exercice(s) — <?= e((string) $percents[$type]) ?> %
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <script>
                        (function () {
                            const ctx = document.getElementById('chartTypes');
                            if (!ctx) return;
                            new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: <?= json_encode(array_keys($counts)) ?>,
                                    datasets: [{
                                        data: <?= json_encode(array_values($counts)) ?>,
                                        backgroundColor: ['#5b8def', '#5ee0a0', '#f0b429']
                                    }]
                                },
                                options: { plugins: { legend: { position: 'bottom' } } }
                            });
                        })();
                    </script>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$slot = ob_get_clean();
$pageTitle = 'Tableau de bord — exercices';
require VIEW_PATH . '/admin/layout.php';
