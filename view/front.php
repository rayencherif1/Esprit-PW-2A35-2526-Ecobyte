<?php
require_once __DIR__ . '/../controller/RecetteController.php';
require_once __DIR__ . '/../model/Instruction.php';

$controller = new RecetteController();
$recettes = $controller->afficherRecettes();

$instructionRepository = new InstructionRepository();
$iaChatRecipes = [];
foreach ($recettes as $recette) {
    $rid = (int) ($recette['id'] ?? 0);
    if ($rid < 1) {
        continue;
    }
    $inst = $instructionRepository->findByRecetteId($rid);
    $ing = $inst !== null ? trim($inst->getIngredients()) : '';
    if ($ing === '' || str_starts_with($ing, 'À compléter')) {
        $ing = (string) ($recette['nom'] ?? '');
    }
    $iaChatRecipes[] = [
        'id' => $rid,
        'nom' => (string) ($recette['nom'] ?? ''),
        'type' => (string) ($recette['type'] ?? ''),
        'ingredients' => $ing,
        'url' => '/recette/view/recette-instructions.php?id=' . $rid,
    ];
}
$iaChatJson = json_encode($iaChatRecipes, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
$templatePath = __DIR__ . '/../assets/FoodMart-1.0.0/index.html';
$template = file_get_contents($templatePath);

if ($template === false) {
    http_response_code(500);
    echo 'Impossible de charger le template FoodMart.';
    exit;
}

function normalizeType(string $type): string
{
    if (function_exists('mb_strtolower')) {
        $value = trim(mb_strtolower($type, 'UTF-8'));
    } else {
        $value = trim(strtolower($type));
    }
    $value = strtr($value, ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a', 'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ù' => 'u', 'û' => 'u']);
    return preg_replace('/\s+/', ' ', $value) ?? $value;
}

/** Catégorie barre de recherche : food | drink */
function recetteCategoryBucket(array $recette): string
{
    $normType = normalizeType((string) ($recette['type'] ?? ''));
    $normName = normalizeType((string) ($recette['nom'] ?? ''));
    $haystack = $normType . ' ' . $normName;
    if (preg_match(
        '/\b(boisson|boissons|drink|drinks|jus|smoothie|smoothies|cocktail|cocktails|citronnade|limonade|shake|milkshake|café|cafe|the\b|thé|infusion|soda|tonic|cola|nectar)\b/u',
        $haystack
    )) {
        return 'drink';
    }

    return 'food';
}

function renderCards(array $items, string $label, string $badgeClass): string
{
    if (count($items) === 0) {
        return '
        <div class="col-md-6 col-lg-4 recette-card-col" data-recette-category="food" data-search-nom="">
        <div class="product-item h-100">
          <figure>
            <a href="#" title="Recette">
              <img src="/recette/public/image/salade.jpg" class="tab-image" alt="Aucune recette">
            </a>
          </figure>
          <h3>Aucune recette</h3>
          <span class="qty">Type: ' . htmlspecialchars($label) . '</span>
          <span class="price">Aucune recette disponible</span>
        </div>
        </div>';
    }

    $cards = '';
    foreach ($items as $recette) {
        $nom = htmlspecialchars($recette['nom'] ?? '');
        $nomPlain = (string) ($recette['nom'] ?? '');
        if (function_exists('mb_strtolower')) {
            $searchNom = mb_strtolower(trim($nomPlain), 'UTF-8');
        } else {
            $searchNom = strtolower(trim($nomPlain));
        }
        $bucket = recetteCategoryBucket($recette);
        $calories = htmlspecialchars((string) ($recette['calories'] ?? '0'));
        $temps = htmlspecialchars((string) ($recette['tempsPreparation'] ?? '0'));
        $difficulte = htmlspecialchars($recette['difficulte'] ?? '');
        $impact = htmlspecialchars($recette['impactCarbone'] ?? '');
        $image = htmlspecialchars($recette['image'] ?? '/recette/public/image/salade.jpg');
        $recetteId = (int) ($recette['id'] ?? 0);
        $instructionsUrl = '/recette/view/recette-instructions.php?id=' . $recetteId;

        $cards .= '
        <div class="col-md-6 col-lg-4 recette-card-col" data-recette-category="' . htmlspecialchars($bucket, ENT_QUOTES, 'UTF-8') . '" data-search-nom="' . htmlspecialchars($searchNom, ENT_QUOTES, 'UTF-8') . '">
        <div class="product-item h-100">
          <span class="badge ' . $badgeClass . ' position-absolute m-3">' . htmlspecialchars($label) . '</span>
          <button type="button" class="btn-wishlist btn-like-recette" data-recette-id="' . $recetteId . '" title="Favori" aria-pressed="false" aria-label="Ajouter aux favoris"><svg width="24" height="24" aria-hidden="true"><use xlink:href="#heart"></use></svg></button>
          <figure>
            <a href="' . htmlspecialchars($instructionsUrl) . '" title="' . $nom . ' — voir les instructions">
              <img src="' . $image . '" class="tab-image" alt="' . $nom . '">
            </a>
          </figure>
          <h3><a href="' . htmlspecialchars($instructionsUrl) . '" class="text-decoration-none text-dark">' . $nom . '</a></h3>
          <span class="qty">Temps: ' . $temps . ' min | Difficulte: ' . $difficulte . '</span>
          <span class="price">' . $calories . ' kcal</span>
          <div class="d-flex align-items-center justify-content-between">
            <small>Impact carbone: ' . $impact . '</small>
            <a href="' . htmlspecialchars($instructionsUrl) . '" class="nav-link text-decoration-none fw-semibold" title="Voir les instructions" aria-label="Voir les instructions">→</a>
          </div>
        </div>
        </div>';
    }

    return $cards;
}

$grouped = [
    'Petit dejeuner' => [],
    'Dejeuner' => [],
    'Diner' => [],
    'Autres' => [],
];

foreach ($recettes as $recette) {
    $normalized = normalizeType((string) ($recette['type'] ?? ''));
    if ($normalized === 'petit dejeuner') {
        $grouped['Petit dejeuner'][] = $recette;
    } elseif ($normalized === 'dejeuner') {
        $grouped['Dejeuner'][] = $recette;
    } elseif ($normalized === 'diner') {
        $grouped['Diner'][] = $recette;
    } else {
        $grouped['Autres'][] = $recette;
    }
}

$petitDejeunerCards = renderCards($grouped['Petit dejeuner'], 'Petit dejeuner', 'bg-warning');
$dejeunerCards = renderCards($grouped['Dejeuner'], 'Dejeuner', 'bg-success');
$dinerCards = renderCards($grouped['Diner'], 'Diner', 'bg-primary');
$autresCards = renderCards($grouped['Autres'], 'Autres', 'bg-secondary');
$autresSectionHtml = '';
if (count($grouped['Autres']) > 0) {
    $autresSectionHtml = '
      <section class="py-3 recette-type-section">
        <div class="section-header mb-4">
          <h2 class="section-title">Autres</h2>
        </div>
        <div class="row g-4">' . $autresCards . '</div>
      </section>';
}

$recipesMain = '
<main id="accessories">
  <section class="py-5" id="accessories">
    <div class="container-fluid">
      <div class="section-header d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h1 class="section-title">Mes Recettes</h1>
      </div>

      <section class="py-3 recette-type-section">
        <div class="section-header mb-4">
          <h2 class="section-title">Petit dejeuner</h2>
        </div>
        <div class="row g-4">' . $petitDejeunerCards . '</div>
      </section>

      <section class="py-3 recette-type-section">
        <div class="section-header mb-4">
          <h2 class="section-title">Dejeuner</h2>
        </div>
        <div class="row g-4">' . $dejeunerCards . '</div>
      </section>

      <section class="py-3 recette-type-section">
        <div class="section-header mb-4">
          <h2 class="section-title">Diner</h2>
        </div>
        <div class="row g-4">' . $dinerCards . '</div>
      </section>' . $autresSectionHtml . '
    </div>
  </section>
</main>';

$customFooter = '
<footer class="py-5" id="about-us" style="background-color: #f7c948;">
  <div class="container">
       <div class="text-center mb-4 pb-3 border-bottom border-dark border-opacity-25">
      <h2 class="section-title mb-0 text-dark fw-bold">About Us</h2>
    </div>
    <div class="row g-4 text-dark">
      <div class="col-md-3">
        <h4 class="fw-bold mb-3">Address</h4>
        <p class="mb-0 opacity-75">
          It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.
        </p>
      </div>
      <div class="col-md-3">
        <h4 class="fw-bold mb-3">Links</h4>
        <ul class="list-unstyled mb-0">
          <li class="mb-2">Home</li>
          <li class="mb-2">About</li>
          <li class="mb-2">Car</li>
          <li class="mb-2">Booking</li>
          <li>Contact Us</li>
        </ul>
      </div>
      <div class="col-md-3">
        <h4 class="fw-bold mb-3">Follow Us</h4>
        <ul class="list-unstyled mb-0">
          <li class="mb-2">Facebook</li>
          <li class="mb-2">Twitter</li>
          <li class="mb-2">Linkedin</li>
          <li class="mb-2">Youtube</li>
          <li>Instagram</li>
        </ul>
      </div>
      <div class="col-md-3">
        <h4 class="fw-bold mb-3">Newsletter</h4>
        <input type="email" class="form-control mb-3" placeholder="Enter Your Email">
        <button class="btn text-white px-4" style="background-color: #d23c78;">SUBSCRIBE</button>
      </div>
    </div>
  </div>
</footer>';

$replaceCount = 0;
$updatedTemplate = preg_replace('~<main[^>]*>.*?</main>~s', $recipesMain, $template, 1, $replaceCount);
if (is_string($updatedTemplate)) {
    $template = $updatedTemplate;
}
if ($replaceCount === 0) {
    $template = preg_replace('~</header>~i', '</header>' . $recipesMain, $template, 1) ?? $template;
}
$template = preg_replace('~</main>\s*.*?<footer~is', '</main><footer', $template, 1) ?? $template;
$template = preg_replace('~<footer[^>]*>.*?</footer>~is', $customFooter, $template, 1) ?? $template;
$template = str_replace('<html>', '<html lang="fr">', $template);
$template = str_replace('<title>Foodmart - Free eCommerce Grocery Store HTML Website Template</title>', '<title>FoodMart - Mes Recettes</title>', $template);
$template = str_replace('href="css/', 'href="/recette/assets/FoodMart-1.0.0/css/', $template);
$template = str_replace('href="style.css"', 'href="/recette/assets/FoodMart-1.0.0/style.css"', $template);
$template = str_replace('src="images/', 'src="/recette/assets/FoodMart-1.0.0/images/', $template);
$template = str_replace('url(\'images/', 'url(\'/recette/assets/FoodMart-1.0.0/images/', $template);
$template = str_replace('/recette/assets/FoodMart-1.0.0/images/logo.png', '/recette/public/image/logo.png', $template);
$template = str_replace('src="js/', 'src="/recette/assets/FoodMart-1.0.0/js/', $template);
$template = str_replace('href="index.html"', 'href="/recette/"', $template);

$template = str_replace(
    '<select class="form-select border-0 bg-transparent">
                  <option>All Categories</option>
                  <option>Groceries</option>
                  <option>Drinks</option>
                  <option>Chocolates</option>
                </select>',
    '<select id="recette-category-select" class="form-select border-0 bg-transparent" aria-label="Categorie recettes">
                  <option value="all">Toutes</option>
                  <option value="food">Foods</option>
                  <option value="drink">Drinks</option>
                </select>',
    $template
);
$template = str_replace(
    '<form id="search-form" class="text-center" action="index.html" method="post">
                  <input type="text" class="form-control border-0 bg-transparent" placeholder="Search for more than 20,000 products" />
                </form>',
    '<form id="search-form" class="text-center" action="#" method="get">
                  <input type="search" id="recette-search-input" class="form-control border-0 bg-transparent" placeholder="Nom d\'une recette…" autocomplete="off" aria-label="Rechercher une recette" />
                </form>',
    $template
);

$headerFavoris = '              <li>
                <a href="/recette/view/favoris.php" class="rounded-circle bg-light p-2 mx-1 position-relative text-decoration-none text-dark" title="Mes favoris" id="header-favoris-link">
                  <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#heart"></use></svg>
                  <span id="likes-header-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" hidden></span>
                </a>
              </li>';
$template = str_replace(
    '              <li>
                <a href="#" class="rounded-circle bg-light p-2 mx-1">
                  <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#heart"></use></svg>
                </a>
              </li>',
    $headerFavoris,
    $template
);

$iaHeaderBtn = '
            <button type="button" id="btn-assistance-ia" class="btn btn-success d-flex align-items-center gap-1 rounded-pill px-2 px-sm-3 py-2 flex-shrink-0 shadow-sm" style="white-space: nowrap;" aria-expanded="false" aria-controls="ia-chat-panel" title="Ouvrir l&apos;assistant recettes">
              <span aria-hidden="true" style="font-size:1.1rem;line-height:1;">&#129302;</span>
              <span class="d-none d-sm-inline fw-semibold">Assistance IA</span>
              <span class="d-sm-none fw-semibold">IA</span>
            </button>';
$template = str_replace(
    '<div class="col-sm-8 col-lg-2 d-flex justify-content-end gap-2 align-items-center justify-content-center justify-content-sm-end">',
    '<div class="col-sm-8 col-lg-2 d-flex justify-content-end gap-2 align-items-center justify-content-center justify-content-sm-end">' . $iaHeaderBtn,
    $template
);

$iaWidget = <<<'HTML'
<style id="ia-assistant-styles">
#ia-chat-panel {
  position: fixed;
  bottom: 1rem;
  right: 1rem;
  width: min(100vw - 2rem, 380px);
  max-height: min(88vh, 520px);
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 12px 40px rgba(0,0,0,.18);
  display: flex;
  flex-direction: column;
  z-index: 1080;
  font-family: "Nunito", "Open Sans", system-ui, sans-serif;
  overflow: hidden;
  border: 1px solid rgba(0,0,0,.08);
}
#ia-chat-panel[hidden] { display: none !important; }
.ia-chat-header {
  background: linear-gradient(135deg, #198754 0%, #146c43 100%);
  color: #fff;
  padding: 0.65rem 0.85rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}
.ia-chat-header h2 { font-size: 0.95rem; margin: 0; font-weight: 700; }
.ia-chat-close {
  background: rgba(255,255,255,.2);
  border: none;
  color: #fff;
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  line-height: 1;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  padding: 0;
}
.ia-chat-close:hover { background: rgba(255,255,255,.35); }
.ia-chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 0.85rem;
  background: #f8faf8;
  min-height: 200px;
  max-height: 340px;
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}
.ia-msg {
  max-width: 92%;
  padding: 0.55rem 0.75rem;
  border-radius: 12px;
  font-size: 0.9rem;
  line-height: 1.45;
}
.ia-msg-bot {
  align-self: flex-start;
  background: #fff;
  border: 1px solid #e2e8e4;
  color: #1a2e24;
}
.ia-msg-user {
  align-self: flex-end;
  background: #d1e7dd;
  color: #0f5132;
}
.ia-msg a { color: #146c43; font-weight: 600; }
.ia-chat-form {
  display: flex;
  gap: 0.5rem;
  padding: 0.65rem;
  background: #fff;
  border-top: 1px solid #e8ece9;
}
.ia-chat-form input {
  flex: 1;
  border: 1px solid #ced4da;
  border-radius: 999px;
  padding: 0.45rem 0.9rem;
  font-size: 0.9rem;
}
.ia-chat-form button[type="submit"] {
  border: none;
  background: #198754;
  color: #fff;
  border-radius: 999px;
  padding: 0.45rem 1rem;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
}
.ia-chat-form button[type="submit"]:hover { background: #157347; }
</style>
<div id="ia-chat-panel" hidden role="dialog" aria-label="Assistant recettes" aria-modal="true">
  <div class="ia-chat-header">
    <h2>&#129302; Assistant recettes</h2>
    <button type="button" class="ia-chat-close" id="ia-chat-close" aria-label="Fermer le chat">&times;</button>
  </div>
  <div class="ia-chat-messages" id="ia-chat-messages"></div>
  <form class="ia-chat-form" id="ia-chat-form" autocomplete="off">
    <label for="ia-chat-input" class="visually-hidden">Votre message</label>
    <input type="text" id="ia-chat-input" placeholder="Ex tomates pâtes ail" maxlength="2000">
    <button type="submit">Envoyer</button>
  </form>
</div>
HTML;

$iaWidget .= '<script>
(function () {
  const RECIPES = ' . $iaChatJson . ';
  const panel = document.getElementById("ia-chat-panel");
  const btnOpen = document.getElementById("btn-assistance-ia");
  const btnClose = document.getElementById("ia-chat-close");
  const messagesEl = document.getElementById("ia-chat-messages");
  const form = document.getElementById("ia-chat-form");
  const input = document.getElementById("ia-chat-input");
  const WELCOME = "Dis-moi quels ingrédients tu as et je te trouve une recette qui colle";
  const REPLY_BONJOUR = "Bonjour,Dis-moi quels ingrédients tu as et je te trouve une recette qui colle";
  const REPLY_MERCI = "Avec plaisir ! N’hésite pas si tu veux une autre suggestion de recette.";

  function stripAccents(s) {
    try {
      return s.normalize("NFD").replace(/\p{M}/gu, "");
    } catch (e) {
      return s;
    }
  }
  function tokensFromText(s) {
    return stripAccents(String(s).toLowerCase())
      .replace(/[,;|•·\n\r]+/g, " ")
      .replace(/\bet\b/g, " ")
      .split(/\s+/)
      .map(function (t) { return t.replace(/[^a-z0-9àâäéèêëïîôùûüç-]/gi, ""); })
      .filter(function (t) { return t.length > 1; });
  }
  function scoreRecipe(userTok, recipe) {
    const blob = stripAccents((recipe.ingredients + " " + recipe.nom).toLowerCase());
    let score = 0;
    userTok.forEach(function (t) {
      const tl = stripAccents(t.toLowerCase());
      if (tl.length < 2) return;
      if (blob.indexOf(tl) !== -1) score += 2;
      else {
        const parts = blob.split(/[^a-z0-9àâäéèêëïîôùûüç-]+/i);
        for (let i = 0; i < parts.length; i++) {
          if (parts[i].length > 1 && (parts[i].indexOf(tl) !== -1 || tl.indexOf(parts[i]) !== -1)) {
            score += 1;
            break;
          }
        }
      }
    });
    return score;
  }
  function pickRecipe(userText) {
    const userTok = tokensFromText(userText);
    if (RECIPES.length === 0) {
      return { recipe: null, score: 0 };
    }
    let best = RECIPES[0];
    let bestScore = userTok.length ? scoreRecipe(userTok, best) : 0;
    for (let i = 1; i < RECIPES.length; i++) {
      const sc = userTok.length ? scoreRecipe(userTok, RECIPES[i]) : 0;
      if (sc > bestScore) {
        bestScore = sc;
        best = RECIPES[i];
      }
    }
    return { recipe: best, score: bestScore };
  }
  function smallTalkReply(raw) {
    const simple = stripAccents(String(raw).toLowerCase())
      .replace(/[!?.…,:;]+/g, " ")
      .replace(/\s+/g, " ")
      .trim();
    if (!simple) return null;
    const greetingWords = { bonjour: 1, salut: 1, bonsoir: 1, coucou: 1, hello: 1, hey: 1, hi: 1 };
    const parts = simple.split(" ").filter(Boolean);
    const onlyGreeting = parts.length > 0 && parts.every(function (w) { return greetingWords[w]; });
    if (onlyGreeting) return REPLY_BONJOUR;
    if (/^merci(\s+bien|\s+beaucoup|\s+encore)?$/.test(simple)) return REPLY_MERCI;
    if (simple === "thanks" || simple === "thank you" || simple === "ty" || simple === "thx") return REPLY_MERCI;
    const thanksParts = simple.split(" ").filter(Boolean);
    if (thanksParts.indexOf("merci") !== -1 && thanksParts.every(function (w) {
      return w === "merci" || w === "beaucoup" || w === "bien" || w === "encore" || w === "à" || w === "toi" || w === "a";
    })) return REPLY_MERCI;
    return null;
  }
  function appendMsg(text, isUser) {
    const div = document.createElement("div");
    div.className = "ia-msg " + (isUser ? "ia-msg-user" : "ia-msg-bot");
    div.innerHTML = text;
    messagesEl.appendChild(div);
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }
  function openPanel() {
    panel.hidden = false;
    btnOpen.setAttribute("aria-expanded", "true");
    messagesEl.innerHTML = "";
    appendMsg(WELCOME, false);
    input.focus();
  }
  function closePanel() {
    panel.hidden = true;
    btnOpen.setAttribute("aria-expanded", "false");
  }
  btnOpen.addEventListener("click", function () {
    if (panel.hidden) openPanel();
    else closePanel();
  });
  btnClose.addEventListener("click", closePanel);
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    const text = (input.value || "").trim();
    if (!text) return;
    appendMsg(text.replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\n/g, "<br>"), true);
    input.value = "";
    const chit = smallTalkReply(text);
    if (chit) {
      appendMsg(chit, false);
      return;
    }
    if (!Array.isArray(RECIPES) || RECIPES.length === 0) {
      appendMsg("Aucune recette n’est disponible dans la base pour le moment.", false);
      return;
    }
    const userIngTok = tokensFromText(text);
    if (userIngTok.length === 0) {
      appendMsg("Je n’ai pas reconnu d’ingrédient. Essaie avec un ou plusieurs mots, par exemple : tomates, riz, poulet.", false);
      return;
    }
    const { recipe, score } = pickRecipe(text);
    if (!recipe) {
      appendMsg("Aucune recette n’est disponible dans la base pour le moment.", false);
      return;
    }
    if (score === 0) {
      appendMsg("Il n’y a pas de recette disponible pour cet ingrédient.", false);
      return;
    }
    const nomEsc = recipe.nom.replace(/</g, "&lt;").replace(/>/g, "&gt;");
    const typeEsc = (recipe.type || "").replace(/</g, "&lt;");
    let reply = "D’après ce que tu as indiqué, je te propose : <strong>" + nomEsc + "</strong> (" + typeEsc + "). ";
    reply += "<a href=\"" + recipe.url + "\">Voir la recette et les instructions</a>. Tu peux aussi envoyer d’autres ingrédients pour une nouvelle suggestion.";
    appendMsg(reply, false);
  });
})();
</script>';

$recetteSearchScript = <<<'HTML'
<script>
(function () {
  function norm(s) {
    try {
      return String(s || '').toLowerCase().normalize('NFD').replace(/\p{M}/gu, '');
    } catch (e) {
      return String(s || '').toLowerCase();
    }
  }
  function applyRecetteFilters() {
    var input = document.getElementById('recette-search-input');
    var cat = document.getElementById('recette-category-select');
    if (!input || !cat) return;
    var q = norm(input.value.trim());
    var c = cat.value || 'all';
    document.querySelectorAll('.recette-card-col').forEach(function (col) {
      var nom = norm(col.getAttribute('data-search-nom') || '');
      var bucket = col.getAttribute('data-recette-category') || 'food';
      var matchCat = (c === 'all' || c === bucket);
      var matchSearch = !q || nom.indexOf(q) !== -1;
      col.classList.toggle('d-none', !(matchCat && matchSearch));
    });
    document.querySelectorAll('.recette-type-section').forEach(function (sec) {
      var row = sec.querySelector('.row.g-4');
      if (!row) return;
      var any = false;
      row.querySelectorAll('.recette-card-col').forEach(function (col) {
        if (!col.classList.contains('d-none')) any = true;
      });
      sec.classList.toggle('d-none', !any);
    });
  }
  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('search-form');
    var input = document.getElementById('recette-search-input');
    var cat = document.getElementById('recette-category-select');
    if (form) form.addEventListener('submit', function (e) { e.preventDefault(); });
    if (input) input.addEventListener('input', applyRecetteFilters);
    if (cat) cat.addEventListener('change', applyRecetteFilters);
  });
})();
</script>
HTML;

$likesScripts = '<script src="/recette/public/js/recette-likes.js" defer></script>'
    . '<script defer>document.addEventListener("DOMContentLoaded",function(){if(window.initRecetteLikesOnListPage)initRecetteLikesOnListPage();});</script>';

$template = preg_replace('/<\/body>/i', $recetteSearchScript . "\n" . $likesScripts . "\n" . $iaWidget . "\n</body>", $template, 1);

echo $template;
