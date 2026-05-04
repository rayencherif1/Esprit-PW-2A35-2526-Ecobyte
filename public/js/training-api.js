/**
 * Scripts front — appels aux APIs distantes (BMI, wger, conseils).
 * Rien n’est « stocké en local » : tout part du navigateur vers Internet.
 * Sur un autre PC, vérifiez seulement la connexion réseau et pas de blocage CORS (ces APIs l’autorisent).
 */
(function () {
  'use strict';

  /** URL de l’API BMI (poids en kg, taille en mètres dans le chemin) */
  var BMI_BASE = 'https://bmicalculatorapi.vercel.app/api/bmi';
  /** Liste d’exercices filtrée par muscle (wger) */
  var WGER_EX = 'https://wger.de/api/v2/exercise';
  /** Traductions françaises / anglais des exercices */
  var WGER_TR = 'https://wger.de/api/v2/exercise-translation';
  /** Conseil court aléatoire */
  var ADVICE = 'https://api.adviceslip.com/advice';

  /**
   * Enlève les balises HTML d’une chaîne (aperçu texte).
   * @param {string} html
   * @returns {string}
   */
  function stripHtml(html) {
    var d = document.createElement('div');
    d.innerHTML = html || '';
    var t = d.textContent || '';
    return t.replace(/\s+/g, ' ').trim();
  }

  /**
   * Charge un conseil depuis l’API Advice Slip.
   */
  function loadQuote() {
    var el = document.getElementById('nf-quote');
    if (!el) return;
    el.textContent = 'Chargement…';
    fetch(ADVICE)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        el.textContent = (data && data.slip && data.slip.advice) ? data.slip.advice : 'Bougez un peu chaque jour.';
      })
      .catch(function () {
        el.textContent = 'Impossible de charger un conseil (réseau).';
      });
  }

  /**
   * Calcule l’IMC via l’API distante.
   */
  function calcBmi() {
    var hCm = document.getElementById('nf-bmi-h');
    var wKg = document.getElementById('nf-bmi-w');
    var out = document.getElementById('nf-bmi-out');
    var err = document.getElementById('nf-bmi-err');
    if (!hCm || !wKg || !out || !err) return;
    err.textContent = '';
    out.textContent = '';
    var h = parseFloat(String(hCm.value).replace(',', '.'));
    var w = parseFloat(String(wKg.value).replace(',', '.'));
    if (!isFinite(h) || h <= 0) {
      err.textContent = 'Indiquez une taille valide en cm.';
      return;
    }
    if (!isFinite(w) || w <= 0) {
      err.textContent = 'Indiquez un poids valide en kg.';
      return;
    }
    var m = h / 100;
    var url = BMI_BASE + '/' + encodeURIComponent(w) + '/' + encodeURIComponent(m);
    fetch(url)
      .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
      .then(function (x) {
        if (!x.ok) {
          err.textContent = (x.j && (x.j.error || x.j.Error)) ? String(x.j.error || x.j.Error) : 'Erreur API.';
          return;
        }
        if (typeof x.j.bmi !== 'number') {
          err.textContent = 'Réponse API inattendue.';
          return;
        }
        out.textContent = 'IMC : ' + x.j.bmi.toFixed(1) + (x.j.Category ? ' — ' + x.j.Category : '');
      })
      .catch(function () {
        err.textContent = 'Erreur réseau vers l’API BMI.';
      });
  }

  /**
   * Récupère le nom + description EN pour un id exercice wger.
   * @param {number} exerciseId
   * @returns {Promise<{name:string,desc:string}|null>}
   */
  function fetchTranslation(exerciseId) {
    var u = WGER_TR + '/?exercise=' + exerciseId + '&language=2';
    return fetch(u)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        var rows = data.results || [];
        var i;
        for (i = 0; i < rows.length; i++) {
          if (rows[i].language === 2) {
            return { name: rows[i].name, desc: stripHtml(rows[i].description) };
          }
        }
        return null;
      });
  }

  /**
   * Mélange un tableau (Fisher–Yates simplifié).
   */
  function shuffle(arr) {
    var a = arr.slice();
    var i, j, t;
    for (i = a.length - 1; i > 0; i--) {
      j = Math.floor(Math.random() * (i + 1));
      t = a[i];
      a[i] = a[j];
      a[j] = t;
    }
    return a;
  }

  /**
   * Affiche jusqu’à 5 alternatives pour un muscle wger.
   * @param {number} muscleId
   * @param {HTMLElement} wrap
   */
  function loadAlternatives(muscleId, wrap) {
    var loading = wrap.querySelector('.nf-alt-loading');
    var list = wrap.querySelector('.nf-alt-list');
    var errEl = wrap.querySelector('.nf-alt-error');
    if (loading) loading.classList.remove('d-none');
    if (list) list.innerHTML = '';
    if (errEl) {
      errEl.classList.add('d-none');
      errEl.textContent = '';
    }
    var url = WGER_EX + '/?language=2&muscles=' + muscleId + '&limit=40';
    fetch(url)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        var ids = (data.results || []).map(function (x) { return x.id; });
        ids = shuffle(ids).slice(0, 12);
        var chain = Promise.resolve();
        var found = [];
        ids.forEach(function (id) {
          chain = chain.then(function () {
            if (found.length >= 5) return null;
            return fetchTranslation(id).then(function (t) {
              if (t && t.name) found.push(t);
            });
          });
        });
        return chain.then(function () { return found; });
      })
      .then(function (alts) {
        if (loading) loading.classList.add('d-none');
        if (!list) return;
        if (!alts || !alts.length) {
          if (errEl) {
            errEl.textContent = 'Aucune alternative trouvée.';
            errEl.classList.remove('d-none');
          }
          return;
        }
        list.innerHTML = alts.map(function (a) {
          var d = a.desc ? a.desc.slice(0, 220) : '';
          if (a.desc && a.desc.length > 220) d += '…';
          return '<div class="mb-2 p-2 bg-light rounded"><strong class="text-success">' +
            escapeHtml(a.name) + '</strong><br><span class="text-muted">' +
            escapeHtml(d) + '</span></div>';
        }).join('');
      })
      .catch(function () {
        if (loading) loading.classList.add('d-none');
        if (errEl) {
          errEl.textContent = 'Erreur réseau (wger).';
          errEl.classList.remove('d-none');
        }
      });
  }

  function escapeHtml(s) {
    var d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
  }

  document.addEventListener('DOMContentLoaded', function () {
    var qbtn = document.getElementById('nf-quote-btn');
    if (document.getElementById('nf-quote')) {
      loadQuote();
      if (qbtn) qbtn.addEventListener('click', loadQuote);
    }
    var bbtn = document.getElementById('nf-bmi-btn');
    if (bbtn) bbtn.addEventListener('click', calcBmi);

    document.querySelectorAll('.nf-cant-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var mid = parseInt(btn.getAttribute('data-muscle'), 10);
        var idx = btn.getAttribute('data-block');
        var wrap = document.getElementById('nf-alt-' + idx);
        if (!wrap) return;
        wrap.classList.remove('d-none');
        if (!mid || mid < 1) {
          wrap.querySelector('.nf-alt-error').classList.remove('d-none');
          wrap.querySelector('.nf-alt-error').textContent =
            'Aucun muscle n’est défini pour cet exercice : complétez le champ dans l’admin (API wger).';
          return;
        }
        loadAlternatives(mid, wrap);
      });
    });
  });
})();
