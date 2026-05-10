(function () {
  'use strict';

  var STORAGE_KEY = 'ecobyte_recette_likes';
  var HEART_SVG_DEFAULT =
    '<svg width="24" height="24" aria-hidden="true"><use xlink:href="#heart"></use></svg>';
  var HEART_PATH_SOLID =
    'M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z';
  var HEART_SVG_LIKED =
    '<svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" class="heart-liked-svg" xmlns="http://www.w3.org/2000/svg">' +
    '<path fill="#dc3545" fill-rule="evenodd" stroke="none" d="' +
    HEART_PATH_SOLID +
    '" clip-rule="evenodd"/></svg>';

  function setHeartIcon(btn, liked) {
    btn.innerHTML = liked ? HEART_SVG_LIKED : HEART_SVG_DEFAULT;
  }

  function injectStyles() {
    if (document.getElementById('recette-likes-styles')) {
      return;
    }
    var s = document.createElement('style');
    s.id = 'recette-likes-styles';
    s.textContent =
      '.btn-like-recette{cursor:pointer;line-height:0}' +
      '.btn-like-recette:focus-visible{outline:2px solid #198754;outline-offset:2px;border-radius:4px}' +
      '.product-item .btn-wishlist.btn-like-recette.is-liked,' +
      '.product-item .btn-wishlist.btn-like-recette.is-liked:hover,' +
      '.product-item .btn-wishlist.btn-like-recette.is-liked:focus,' +
      '.product-item .btn-wishlist.btn-like-recette.is-liked:active{' +
      'background:#fff!important;border-color:#dc3545!important;color:#dc3545!important}' +
      '.product-item .btn-wishlist.btn-like-recette.is-liked svg,' +
      '.product-item .btn-wishlist.btn-like-recette.is-liked:hover svg,' +
      '.product-item .btn-wishlist.btn-like-recette.is-liked:focus svg,' +
      '.product-item .btn-wishlist.btn-like-recette.is-liked:active svg,' +
      '.product-item .btn-wishlist.btn-like-recette.is-liked svg use,' +
      '.product-item .btn-wishlist.btn-like-recette.is-liked:hover svg use,' +
      '.product-item .btn-wishlist.btn-like-recette.is-liked:focus svg use,' +
      '.product-item .btn-wishlist.btn-like-recette.is-liked:active svg use{' +
      'color:#dc3545!important;fill:#dc3545!important;stroke:#dc3545!important}' +
      '.heart-liked-svg{display:block}' +
      '.heart-liked-svg path{fill:#dc3545!important;stroke:none!important}' +
      '#likes-header-count{min-width:1.1rem;font-size:0.65rem;line-height:1;padding:0.15em 0.35em}';
    document.head.appendChild(s);
  }

  function getLikes() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      var arr = raw ? JSON.parse(raw) : [];
      if (!Array.isArray(arr)) {
        return [];
      }
      return arr
        .map(function (n) {
          return parseInt(String(n), 10);
        })
        .filter(function (n) {
          return n > 0;
        });
    } catch (e) {
      return [];
    }
  }

  function setLikes(ids) {
    var unique = [];
    var seen = {};
    ids.forEach(function (n) {
      var x = parseInt(String(n), 10);
      if (x > 0 && !seen[x]) {
        seen[x] = true;
        unique.push(x);
      }
    });
    localStorage.setItem(STORAGE_KEY, JSON.stringify(unique));
    updateHeaderBadge();
    syncLikeButtons();
  }

  function toggleLike(id) {
    id = parseInt(String(id), 10);
    if (id < 1) {
      return;
    }
    var likes = getLikes();
    var i = likes.indexOf(id);
    if (i >= 0) {
      likes.splice(i, 1);
    } else {
      likes.push(id);
    }
    setLikes(likes);
  }

  function isLiked(id) {
    return getLikes().indexOf(parseInt(String(id), 10)) >= 0;
  }

  function syncLikeButtons() {
    document.querySelectorAll('.btn-like-recette[data-recette-id]').forEach(function (btn) {
      var id = parseInt(btn.getAttribute('data-recette-id'), 10);
      var on = isLiked(id);
      btn.classList.toggle('is-liked', on);
      btn.setAttribute('aria-pressed', on ? 'true' : 'false');
      btn.setAttribute('aria-label', on ? 'Retirer des favoris' : 'Ajouter aux favoris');
      setHeartIcon(btn, on);
    });
  }

  function updateHeaderBadge() {
    var el = document.getElementById('likes-header-count');
    if (!el) {
      return;
    }
    var n = getLikes().length;
    el.textContent = n > 0 ? String(n) : '';
    el.hidden = n === 0;
  }

  function initRecetteLikesOnListPage() {
    injectStyles();
    document.querySelectorAll('.btn-like-recette').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        toggleLike(btn.getAttribute('data-recette-id'));
      });
    });
    syncLikeButtons();
    updateHeaderBadge();
  }

  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function badgeClassForType(type) {
    var t = String(type || '')
      .toLowerCase()
      .replace(/é/g, 'e')
      .replace(/è/g, 'e')
      .replace(/ê/g, 'e')
      .replace(/î/g, 'i')
      .replace(/ô/g, 'o')
      .replace(/ù/g, 'u');
    if (t.indexOf('petit') !== -1) {
      return 'bg-warning';
    }
    if (t.indexOf('dejeuner') !== -1) {
      return 'bg-success';
    }
    if (t.indexOf('diner') !== -1) {
      return 'bg-primary';
    }
    return 'bg-secondary';
  }

  function recipeCardHtml(r) {
    var id = parseInt(String(r.id), 10);
    var nom = escapeHtml(r.nom || '');
    var calories = escapeHtml(String(r.calories != null ? r.calories : '0'));
    var temps = escapeHtml(String(r.tempsPreparation != null ? r.tempsPreparation : '0'));
    var diff = escapeHtml(r.difficulte || '');
    var impact = escapeHtml(r.impactCarbone || '');
    var image = escapeHtml(r.image || '/recette/public/image/salade.jpg');
    var typeLabel = escapeHtml(r.type || '');
    var badge = badgeClassForType(r.type);
    var instructionsUrl = '/recette/view/recette-instructions.php?id=' + id;
    var liked = isLiked(id);
    return (
      '<div class="col-md-6 col-lg-4">' +
      '<div class="product-item h-100">' +
      '<span class="badge ' +
      badge +
      ' position-absolute m-3">' +
      typeLabel +
      '</span>' +
      '<button type="button" class="btn-wishlist btn-like-recette' +
      (liked ? ' is-liked' : '') +
      '" data-recette-id="' +
      id +
      '" title="Favori" aria-pressed="' +
      (liked ? 'true' : 'false') +
      '" aria-label="' +
      (liked ? 'Retirer des favoris' : 'Ajouter aux favoris') +
      '">' +
      (liked ? HEART_SVG_LIKED : HEART_SVG_DEFAULT) +
      '</button>' +
      '<figure>' +
      '<a href="' +
      instructionsUrl +
      '" title="' +
      nom +
      ' — voir les instructions">' +
      '<img src="' +
      image +
      '" class="tab-image" alt="' +
      nom +
      '">' +
      '</a></figure>' +
      '<h3><a href="' +
      instructionsUrl +
      '" class="text-decoration-none text-dark">' +
      nom +
      '</a></h3>' +
      '<span class="qty">Temps: ' +
      temps +
      ' min | Difficulte: ' +
      diff +
      '</span>' +
      '<span class="price">' +
      calories +
      ' kcal</span>' +
      '<div class="d-flex align-items-center justify-content-between">' +
      '<small>Impact carbone: ' +
      impact +
      '</small>' +
      '<a href="' +
      instructionsUrl +
      '" class="nav-link text-decoration-none fw-semibold" aria-label="Voir les instructions">→</a>' +
      '</div></div></div>'
    );
  }

  function initRecetteFavorisPage() {
    injectStyles();
    var row = document.getElementById('favoris-row');
    var emptyEl = document.getElementById('favoris-empty');
    if (!row || !window.__ALL_RECETTES__) {
      return;
    }
    var likes = getLikes();
    var byId = {};
    window.__ALL_RECETTES__.forEach(function (r) {
      byId[parseInt(String(r.id), 10)] = r;
    });
    var html = '';
    likes.forEach(function (lid) {
      var r = byId[lid];
      if (r) {
        html += recipeCardHtml(r);
      }
    });
    row.innerHTML = html;
    if (emptyEl) {
      var hasCards = likes.some(function (lid) {
        return !!byId[lid];
      });
      emptyEl.hidden = hasCards;
    }
    document.querySelectorAll('#favoris-row .btn-like-recette').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        toggleLike(btn.getAttribute('data-recette-id'));
        initRecetteFavorisPage();
      });
    });
    updateHeaderBadge();
  }

  window.getRecetteLikes = getLikes;
  window.initRecetteLikesOnListPage = initRecetteLikesOnListPage;
  window.initRecetteFavorisPage = initRecetteFavorisPage;
})();
