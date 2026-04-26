<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/allergie.php';
require_once __DIR__ . '/../../Controller/allergie.Controller.php';


$errors = [];
$old = [
    'nom' => '',
    'description' => '',
    'gravite' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $gravite = trim($_POST['gravite'] ?? '');

    // Sauvegarder les anciennes valeurs
    $old = compact('nom', 'description', 'gravite');

    // Validation Nom
    if (empty($nom)) {
        $errors['nom'] = "Le nom est obligatoire.";
    } elseif (mb_strlen($nom) < 3) {
        $errors['nom'] = "Le nom doit contenir au moins 3 caractères.";
    }

    // Validation Description
    if (empty($description)) {
        $errors['description'] = "La description est obligatoire.";
    }

    // Validation Gravité
    $gravitesValides = ['faible', 'moyenne', 'grave'];
    if (empty($gravite)) {
        $errors['gravite'] = "La gravité est obligatoire.";
    } elseif (!in_array($gravite, $gravitesValides, true)) {
        $errors['gravite'] = "Valeur de gravité invalide.";
    }

    // Si aucune erreur => insertion
    if (empty($errors)) {
        $allergie = new Allergie(null, $nom, $description, $gravite);
        $controller = new AllergieC();
        $controller->addAllergie($allergie);

        header("Location: index.html?success=added");
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ecobyte — Signaler une allergie</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

  <style>
    /* ── Reset & base ── */
    *, *::before, *::after { box-sizing: border-box; }
    body {
      font-family: 'DM Sans', sans-serif;
      background: #f4f6f8;
      color: #1a1a2e;
      margin: 0;
    }

    /* ── Header ── */
    .top-header {
      background: #fff;
      border-bottom: 1px solid #e8e8e8;
      padding: 12px 0;
    }
    .brand-logo { height: 48px; width: auto; }
    .nav-strip { background: #fff; border-bottom: 1px solid #f0f0f0; }
    .nav-strip .nav-link { color: #333; font-weight: 500; }
    .nav-strip .nav-link.active-link { color: #dc3545; font-weight: 700; }

    /* ── Card formulaire ── */
    .form-card {
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 4px 24px rgba(0,0,0,.08);
      padding: 2.5rem;
      max-width: 680px;
      margin: 2.5rem auto;
    }
    .form-card h2 {
      font-size: 1.75rem;
      font-weight: 700;
      color: #dc3545;
    }

    /* ── Champs ── */
    .form-control, .form-select {
      border-radius: 10px;
      border: 1.5px solid #e0e0e0;
      padding: .65rem 1rem;
      transition: border-color .2s, box-shadow .2s;
      font-size: .95rem;
    }
    .form-control:focus, .form-select:focus {
      border-color: #dc3545;
      box-shadow: 0 0 0 3px rgba(220,53,69,.12);
      outline: none;
    }
    .form-control.is-invalid, .form-select.is-invalid {
      border-color: #dc3545;
    }
    .invalid-feedback { font-size: .82rem; }

    /* ── Checkboxes symptômes ── */
    .symptom-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: .45rem .75rem;
    }
    .symptom-item {
      display: flex;
      align-items: center;
      gap: .45rem;
      background: #fafafa;
      border: 1.5px solid #e8e8e8;
      border-radius: 8px;
      padding: .45rem .75rem;
      cursor: pointer;
      transition: border-color .15s, background .15s;
    }
    .symptom-item:hover { border-color: #dc3545; background: #fff5f5; }
    .symptom-item input[type="checkbox"] { accent-color: #dc3545; width: 16px; height: 16px; }
    .symptom-item.checked { border-color: #dc3545; background: #fff0f1; }

    /* ── Bouton ── */
    .btn-submit {
      background: #dc3545;
      color: #fff;
      border: none;
      border-radius: 12px;
      padding: .8rem;
      font-size: 1rem;
      font-weight: 600;
      letter-spacing: .3px;
      transition: background .2s, transform .15s;
      width: 100%;
    }
    .btn-submit:hover { background: #b02a37; transform: translateY(-1px); }
    .btn-submit:active { transform: translateY(0); }

    /* ── Alerte succès ── */
    .alert-success-custom {
      background: #d1fae5;
      color: #065f46;
      border: 1.5px solid #6ee7b7;
      border-radius: 12px;
      padding: 1rem 1.25rem;
      font-weight: 500;
      text-align: center;
      margin-bottom: 1.5rem;
    }

    /* ── Label ── */
    .form-label { font-weight: 600; margin-bottom: .4rem; font-size: .9rem; color: #444; }

    @media (max-width: 500px) {
      .symptom-grid { grid-template-columns: 1fr; }
      .form-card { padding: 1.5rem; margin: 1rem; }
    }
  </style>
</head>

<body>

<!-- ══ HEADER ══════════════════════════════════════════════════════════════ -->
<header>
  <div class="container-fluid top-header">
    <div class="row py-2 align-items-center">
      <div class="col-sm-4 col-lg-3 text-center text-sm-start">
        <a href="index.html" class="d-inline-block">
          <img src="images/ecobyte-logo.png" alt="Ecobyte" class="brand-logo img-fluid">
        </a>
      </div>

      <div class="col-sm-6 offset-sm-2 offset-md-0 col-lg-5 d-none d-lg-block">
        <div class="search-bar row bg-light p-2 my-2 rounded-4">
          <div class="col-md-4 d-none d-md-block">
            <select class="form-select border-0 bg-transparent">
              <option>Toutes catégories</option>
            </select>
          </div>
          <div class="col-12 col-md-8">
            <input type="text" class="form-control border-0 bg-transparent" placeholder="Rechercher...">
          </div>
        </div>
      </div>

      <div class="col-sm-8 col-lg-4 d-flex justify-content-end gap-4 align-items-center mt-4 mt-sm-0 justify-content-center justify-content-sm-end">
        <div class="support-box text-end d-none d-xl-block">
          <span class="fs-6 text-muted">Support</span>
          <h5 class="mb-0">+980-34984089</h5>
        </div>
      </div>
    </div>
  </div>

  <div class="container-fluid nav-strip">
    <div class="row py-2">
      <div class="d-flex justify-content-center justify-content-sm-between align-items-center">
        <nav class="main-menu d-flex navbar navbar-expand-lg w-100">
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav"
                  aria-controls="topNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="topNav">
            <ul class="navbar-nav justify-content-end menu-list list-unstyled d-flex gap-md-3 mb-0 ms-auto">
              <li class="nav-item">
                <a href="allergy_report.php" class="nav-link active-link">🚨 Signaler Allergie</a>
              </li>
            </ul>
          </div>
        </nav>
      </div>
    </div>
  </div>
</header>

<!-- ══ MAIN ════════════════════════════════════════════════════════════════ -->
<main class="py-4" style="background:#f4f6f8; min-height: calc(100vh - 130px);">
  <div class="container">
    <div class="form-card">

      <h2 class="text-center mb-1">🚨 Signaler une allergie</h2>
      <p class="text-center text-muted mb-4" style="font-size:.9rem;">
        Remplissez ce formulaire pour signaler une réaction allergique.
      </p>

      <!-- ✅ Message succès -->


      <!-- ✅ Erreur globale si nécessaire -->
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger rounded-3 py-2 px-3 mb-3" style="font-size:.88rem;">
          ⚠️ Veuillez corriger les erreurs ci-dessous avant de soumettre.
        </div>
      <?php endif; ?>

<form method="POST" novalidate>

<!-- NOM -->
<div class="mb-3">
  <label class="form-label">Nom *</label>
  <input type="text" name="nom"
    class="form-control <?= isset($errors['nom']) ? 'is-invalid' : '' ?>"
    value="<?= htmlspecialchars($old['nom'] ?? '') ?>">
  <div class="invalid-feedback"><?= $errors['nom'] ?? '' ?></div>
</div>

<!-- DESCRIPTION -->
<div class="mb-3">
  <label class="form-label">Description *</label>
  <textarea name="description" rows="4"
    class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
  <div class="invalid-feedback"><?= $errors['description'] ?? '' ?></div>
</div>

<!-- GRAVITÉ -->
<div class="mb-3">
  <label class="form-label">Gravité *</label>
  <select name="gravite"
    class="form-select <?= isset($errors['gravite']) ? 'is-invalid' : '' ?>">

    <option value="">-- Choisir --</option>
    <option value="faible"  <?= ($old['gravite'] ?? '') === 'faible' ? 'selected' : '' ?>>Faible</option>
    <option value="moyenne" <?= ($old['gravite'] ?? '') === 'moyenne' ? 'selected' : '' ?>>Moyenne</option>
    <option value="grave"   <?= ($old['gravite'] ?? '') === 'grave' ? 'selected' : '' ?>>Grave</option>
  </select>

  <div class="invalid-feedback"><?= $errors['gravite'] ?? '' ?></div>
</div>

<!-- SUBMIT -->
<div class="d-grid">
  <button type="submit" class="btn btn-danger btn-lg">
    Envoyer
  </button>
</div>

</form>
    </div><!-- /.form-card -->
  </div><!-- /.container -->
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Highlight checkbox labels on check/uncheck -->
<script>
  document.querySelectorAll('.symptom-item input[type="checkbox"]').forEach(cb => {
    cb.addEventListener('change', () => {
      cb.closest('.symptom-item').classList.toggle('checked', cb.checked);
    });
  });
</script>

</body>
</html>