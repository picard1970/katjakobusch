/* Newsticker für die Startseite
   Holt die neuesten Beiträge der WordPress-Kategorie »Ticker« aus der Spielwiese
   und zeigt sie nacheinander in einer Zeile an.

   Pflege in WordPress:
   - Beitrag in der Kategorie »Ticker« (Titelform: slug »ticker«) veröffentlichen.
   - Der Titel ist die Tickerzeile.
   - Der Link führt auf den Beitrag. Soll er woanders hinführen (Amazon, eine
     Unterseite …), die Zieladresse als Textauszug (»Excerpt«) eintragen,
     z. B. https://www.amazon.de/dp/B0HM23N8KQ oder elodie_book.html
   - Beitrag aus der Kategorie nehmen oder auf Entwurf stellen = Meldung weg.
   - Normale Blogartikel können zusätzlich die Kategorie »Ticker« bekommen –
     sie erscheinen dann im Ticker und bleiben ganz normal im Blog.

   Besucher können den Ticker mit × ausblenden. Er bleibt aus, bis eine neue
   Meldung dazukommt (wird im Browser des Besuchers gemerkt).
*/
(function () {
  'use strict';

  var box = document.querySelector('.ticker');
  if (!box) return;

  var API       = 'https://katjakobusch.de/spielwiese/index.php/wp-json/wp/v2';
  var KATEGORIE = 'ticker';   // Titelform (slug) der WordPress-Kategorie
  var ANZAHL    = 5;          // höchstens so viele Meldungen
  var WECHSEL   = 6000;       // ms pro Meldung
  var CACHE_KEY = 'kk-ticker-v1';
  var CACHE_MIN = 10;         // Minuten, die die Meldungen zwischengespeichert werden
  var ZU_KEY    = 'kk-ticker-zu';

  var slot = box.querySelector('.ticker__text');
  var ruhig = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function holen(url) {
    var ctrl = window.AbortController ? new AbortController() : null;
    var t = ctrl ? setTimeout(function () { ctrl.abort(); }, 5000) : null;
    return fetch(url, ctrl ? { signal: ctrl.signal } : {})
      .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
      .finally(function () { if (t) clearTimeout(t); });
  }

  // HTML aus WordPress (&amp;, <p> …) in reinen Text verwandeln – ohne es auszuführen
  function text(html) {
    return (new DOMParser().parseFromString(html || '', 'text/html').body.textContent || '').trim();
  }

  function ziel(post) {
    var alt = text(post.excerpt && post.excerpt.rendered);
    if (/^https?:\/\/\S+$/i.test(alt) || /^[\w\-\/]+\.(html|php)(#[\w\-]*)?$/i.test(alt)) return alt;
    return post.link;
  }

  function ausCache() {
    try {
      var c = JSON.parse(sessionStorage.getItem(CACHE_KEY) || 'null');
      if (c && Date.now() - c.zeit < CACHE_MIN * 60000) return c.daten;
    } catch (e) {}
    return null;
  }
  function inCache(daten) {
    try { sessionStorage.setItem(CACHE_KEY, JSON.stringify({ zeit: Date.now(), daten: daten })); } catch (e) {}
  }

  function laden() {
    var c = ausCache();
    if (c) return Promise.resolve(c);
    return holen(API + '/categories?slug=' + KATEGORIE + '&_fields=id')
      .then(function (kat) {
        if (!kat.length) return [];
        return holen(API + '/posts?categories=' + kat[0].id + '&per_page=' + ANZAHL +
                     '&_fields=title,link,excerpt');
      })
      .then(function (posts) {
        var daten = posts.map(function (p) {
          return { text: text(p.title && p.title.rendered), url: ziel(p) };
        }).filter(function (m) { return m.text; });
        inCache(daten);
        return daten;
      });
  }

  function link(m) {
    var a = document.createElement('a');
    a.textContent = m.text;
    a.href = m.url;
    var host = '';
    try { host = new URL(m.url, location.href).hostname; } catch (e) {}
    var eigen = host === location.hostname || /(^|\.)katjakobusch\.de$/i.test(host);
    if (!eigen) {                              // fremde Seite (z. B. Amazon) im neuen Tab
      a.target = '_blank';
      a.rel = 'noopener noreferrer';
    }
    a.addEventListener('click', function () {
      if (window.goatcounter && window.goatcounter.count) {
        window.goatcounter.count({ path: 'ticker-klick', title: m.text, event: true });
      }
    });
    return a;
  }

  function starten(meldungen) {
    if (!meldungen.length) return;           // nichts da -> Ticker bleibt unsichtbar

    // Weggeklickt? Dann aus, solange es keine neue Meldung gibt.
    var kennung = meldungen.map(function (m) { return m.text; }).join('|');
    try { if (localStorage.getItem(ZU_KEY) === kennung) return; } catch (e) {}

    var i = 0, pause = false, takt = null;
    var zu = box.querySelector('.ticker__zu');
    if (zu) zu.addEventListener('click', function () {
      box.hidden = true;
      document.body.classList.remove('hat-ticker');
      if (takt) clearInterval(takt);
      try { localStorage.setItem(ZU_KEY, kennung); } catch (e) {}
    });

    function zeigen() {
      slot.classList.remove('ist-sichtbar');
      setTimeout(function () {
        slot.replaceChildren(link(meldungen[i]));
        slot.classList.add('ist-sichtbar');
      }, ruhig ? 0 : 350);
    }

    box.hidden = false;
    document.body.classList.add('hat-ticker');
    zeigen();
    if (meldungen.length < 2) return;

    box.addEventListener('mouseenter', function () { pause = true; });
    box.addEventListener('mouseleave', function () { pause = false; });
    box.addEventListener('focusin',    function () { pause = true; });
    box.addEventListener('focusout',   function () { pause = false; });

    takt = setInterval(function () {
      if (pause || document.hidden) return;
      i = (i + 1) % meldungen.length;
      zeigen();
    }, WECHSEL);
  }

  laden().then(starten).catch(function () { /* Blog nicht erreichbar: Ticker bleibt aus */ });
})();
