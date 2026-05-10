/**
 * Script du site — appels depuis le navigateur vers des services publics.
 *
 * Les 3 familles d’outils distants :
 *   1) BMI — calcul d’IMC.
 *   2) wger — exercices de remplacement (« je ne peux pas… ») : liste par muscle,
 *      textes via exercise-translation, vidéos via /api/v2/video/.
 *   3) Advice Slip — petit conseil sur l’accueil.
 *
 * YouTube : lien de recherche seulement (pas de clé API), comme sur les fiches principales.
 */
(function () {
  'use strict';

  var BMI_BASE = 'https://bmicalculatorapi.vercel.app/api/bmi';
  var WGER_EX = 'https://wger.de/api/v2/exercise';
  var WGER_TR = 'https://wger.de/api/v2/exercise-translation';
  /** Vidéos hébergées sur wger, liées à l’id d’exercice (même base que les alternatives). */
  var WGER_VIDEO = 'https://wger.de/api/v2/video';
  var ADVICE = 'https://api.adviceslip.com/advice';

  /** id langue dans l’API wger — voir https://wger.de/api/v2/language/ */
  var WGER_LANG_FR = 12;
  var WGER_LANG_EN = 2;

  var MAX_ALTERNATIVES = 7;
  var DESC_PREVIEW_LEN = 1200;

  function stripHtml(html) {
    var d = document.createElement('div');
    d.innerHTML = html || '';
    var t = d.textContent || '';
    return t.replace(/\s+/g, ' ').trim();
  }

  /** Pour mettre une URL dans un attribut HTML sans casser les guillemets. */
  function escapeHtmlAttr(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

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
   * Récupère nom + description : on prend le français (langue 12) si wger l’a,
   * sinon l’anglais (2), sinon la première langue disponible. Ce n’est pas une
   * traduction automatique : ce sont des fiches déjà saisies sur wger.
   */
  function fetchExerciseCopy(exerciseId) {
    return fetch(WGER_TR + '/?exercise=' + exerciseId)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        var rows = data.results || [];
        var fr = null;
        var en = null;
        var any = null;
        var i;
        for (i = 0; i < rows.length; i++) {
          var row = rows[i];
          if (!any) {
            any = row;
          }
          if (row.language === WGER_LANG_FR) {
            fr = row;
          }
          if (row.language === WGER_LANG_EN) {
            en = row;
          }
        }
        var pick = fr || en || any;
        if (!pick || !pick.name) {
          return null;
        }
        var langLabel = fr ? 'fr' : (en ? 'en' : 'autre');
        return {
          name: pick.name,
          desc: stripHtml(pick.description),
          langLabel: langLabel
        };
      });
  }

  /**
   * Première vidéo liée à l’exercice (on préfère celle marquée is_main si présente).
   * Les fichiers peuvent être en .MOV / HEVC : le lecteur du navigateur ne lit pas toujours ;
   * d’où le lien « ouvrir dans un nouvel onglet » en secours.
   */
  function fetchVideoForExercise(exerciseId) {
    return fetch(WGER_VIDEO + '/?exercise=' + exerciseId)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        var rows = data.results || [];
        if (!rows.length) {
          return null;
        }
        var i;
        var main = null;
        for (i = 0; i < rows.length; i++) {
          if (rows[i].is_main) {
            main = rows[i];
            break;
          }
        }
        var v = main || rows[0];
        return v && v.video ? String(v.video) : null;
      })
      .catch(function () {
        return null;
      });
  }

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
   * Comment les remplacements sont choisis :
   * 1) On demande à wger la liste des exercices qui ciblent ce muscle (même id que dans votre admin).
   * 2) On mélange une partie des ids pour varier, puis pour chaque id on charge texte + vidéo.
   * 3) On garde les premiers qui ont au moins un nom.
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
    // Pas de filtre langue ici : on prend tous les exercices du muscle, puis le texte FR / EN au cas par cas.
    var url = WGER_EX + '/?muscles=' + muscleId + '&limit=40';
    fetch(url)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        var ids = (data.results || []).map(function (x) { return x.id; });
        ids = shuffle(ids).slice(0, 18);
        var chain = Promise.resolve();
        var found = [];
        ids.forEach(function (id) {
          chain = chain.then(function () {
            if (found.length >= MAX_ALTERNATIVES) return null;
            return fetchExerciseCopy(id).then(function (copy) {
              if (!copy || !copy.name) return null;
              return fetchVideoForExercise(id).then(function (videoUrl) {
                if (found.length >= MAX_ALTERNATIVES) return;
                found.push({
                  name: copy.name,
                  desc: copy.desc,
                  langLabel: copy.langLabel,
                  videoUrl: videoUrl
                });
              });
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
          var d = a.desc ? a.desc.slice(0, DESC_PREVIEW_LEN) : '';
          if (a.desc && a.desc.length > DESC_PREVIEW_LEN) d += '…';
          var langHint = a.langLabel === 'fr'
            ? '<span class="badge text-bg-light border mb-1">Texte : français (wger)</span>'
            : (a.langLabel === 'en'
              ? '<span class="badge text-bg-warning border mb-1">Texte : anglais — pas de fiche FR sur wger pour cet exercice</span>'
              : '<span class="badge text-bg-secondary border mb-1">Texte : autre langue sur wger</span>');
          var videoHtml = '';
          if (a.videoUrl) {
            videoHtml =
              '<div class="mb-2">' +
              '<video controls class="w-100 rounded border nf-alt-vid" style="max-height:14rem;background:#000" preload="metadata">' +
              '<source src="' + escapeHtmlAttr(a.videoUrl) + '" type="video/mp4">' +
              '<source src="' + escapeHtmlAttr(a.videoUrl) + '">' +
              '</video>' +
              '<a class="small" href="' + escapeHtmlAttr(a.videoUrl) + '" target="_blank" rel="noopener">Ouvrir la vidéo (nouvel onglet)</a>' +
              '</div>';
          } else {
            videoHtml = '<p class="small text-muted mb-1">Pas de vidéo sur wger pour cet exercice — utilisez YouTube ci-dessous.</p>';
          }
          var yt = 'https://www.youtube.com/results?search_query=' + encodeURIComponent(a.name + ' exercice démonstration');
          var ytBtn = '<a class="btn btn-outline-danger btn-sm" href="' + yt + '" target="_blank" rel="noopener">YouTube</a>';
          return '<div class="mb-3 p-2 bg-light rounded border border-light-subtle">' +
            langHint +
            '<strong class="text-success d-block mb-1">' + escapeHtml(a.name) + '</strong>' +
            videoHtml +
            '<div class="mb-1">' + ytBtn + '</div>' +
            '<span class="text-muted d-block" style="white-space:pre-wrap;word-break:break-word;">' +
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
            'Aucun muscle n’est défini pour cet exercice : complétez le champ dans l’admin (wger).';
          return;
        }
        loadAlternatives(mid, wrap);
      });
    });
  });
})();
