document.addEventListener("DOMContentLoaded", function () {
  initMusicPlayer();
  initGallery();
  initCommentToggles();
  initCharCounters();
  initConfirmForms();
  initCountdown();
  initNavScrollHint();
  initReplies();
  initCalendarMenus();
});

/* ------------------------------------------------------------------
   Balas komentar: tombol "Balas" membuka form balasan di bawah thread,
   otomatis berisi siapa yang dibalas (@username).
------------------------------------------------------------------ */
function initReplies() {
  document.querySelectorAll("[data-reply-button]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      const form = document.querySelector('[data-reply-form="' + btn.dataset.thread + '"]');
      if (!form) return;

      // Tutup form balasan lain yang sedang terbuka
      document.querySelectorAll("[data-reply-form]").forEach(function (f) {
        if (f !== form) f.hidden = true;
      });

      const target = btn.dataset.replyTo;
      form.querySelector("[data-reply-to-input]").value = target;
      form.querySelector("[data-reply-to-label]").textContent = "@" + target;
      form.hidden = false;

      // Pastikan daftar komentar terlihat, lalu fokus ke kotak balasan
      const box = form.closest("[data-comment-box]");
      if (box && box.hidden) {
        const toggle = document.querySelector('[aria-controls="' + box.id + '"]');
        if (toggle) toggle.click();
      }
      const textarea = form.querySelector("textarea");
      textarea.focus({ preventScroll: true });
      form.scrollIntoView({ behavior: "smooth", block: "center" });
    });
  });

  document.querySelectorAll("[data-reply-cancel]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      const form = btn.closest("[data-reply-form]");
      form.hidden = true;
      form.querySelector("textarea").value = "";
      form.querySelector("textarea").dispatchEvent(new Event("input"));
    });
  });

  // Setelah kirim balasan, sorot komentar barunya sebentar
  if (location.hash.indexOf("#komentar-") === 0) {
    const target = document.getElementById(location.hash.slice(1));
    if (target) {
      // Gambar di atasnya bisa menggeser posisi; scroll ulang setelah semua selesai dimuat.
      // "instant": smooth scroll ke dalam kotak komentar (yang punya scroll sendiri) sering dibatalkan browser.
      const focusTarget = () => target.scrollIntoView({ block: "center", behavior: "instant" });
      if (document.readyState === "complete") focusTarget();
      else window.addEventListener("load", focusTarget, { once: true });
      target.classList.add("is-highlighted");
      setTimeout(() => target.classList.remove("is-highlighted"), 3000);
    }
  }
}

/* Menu "Tambah ke kalender": tutup menu lain saat satu dibuka, dan saat klik di luar */
function initCalendarMenus() {
  const menus = Array.from(document.querySelectorAll(".cal-menu"));
  menus.forEach(function (menu) {
    menu.addEventListener("toggle", function () {
      if (menu.open) menus.forEach((m) => { if (m !== menu) m.open = false; });
    });
  });
  document.addEventListener("click", function (e) {
    menus.forEach((m) => { if (m.open && !m.contains(e.target)) m.open = false; });
  });
}

/* Menu di HP bisa digeser; hilangkan efek pudar saat sudah sampai ujung kanan */
function initNavScrollHint() {
  const nav = document.querySelector(".site-nav");
  if (!nav) return;
  const update = () => {
    nav.classList.toggle("is-scrolled-end", nav.scrollLeft + nav.clientWidth >= nav.scrollWidth - 4);
  };
  nav.addEventListener("scroll", update, { passive: true });
  window.addEventListener("resize", update);
  update();
}

/* YouTube: iframe (youtube-nocookie tidak memasang cookie pelacak sebelum video diputar) */
function createYouTubeIframe(videoId, title) {
  const iframe = document.createElement("iframe");
  iframe.src = "https://www.youtube-nocookie.com/embed/" + encodeURIComponent(videoId) + "?rel=0&autoplay=1";
  iframe.title = "YouTube: " + title;
  iframe.allow = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture";
  iframe.allowFullscreen = true;
  return iframe;
}

/* Thumbnail + tombol play; diganti iframe saat diklik */
function createYouTubeFacade(song) {
  const btn = document.createElement("button");
  btn.type = "button";
  btn.className = "yt-facade";
  btn.style.backgroundImage = "url('https://i.ytimg.com/vi/" + encodeURIComponent(song.youtube) + "/hqdefault.jpg')";
  btn.setAttribute("aria-label", "Putar video " + song.title);
  btn.innerHTML = '<span class="yt-facade__play" aria-hidden="true"></span>';
  btn.addEventListener("click", function () {
    btn.replaceWith(createYouTubeIframe(song.youtube, song.title));
  });
  return btn;
}

/* ------------------------------------------------------------------
   Pemutar musik: MP4 lokal (kalau ada) / YouTube + Spotify
   Data tiap lagu dibaca dari atribut data-* di <option> (diisi dari data/songs.php)
------------------------------------------------------------------ */
function initMusicPlayer() {
  const select = document.getElementById("songSelection");
  const player = document.querySelector("[data-player]");
  if (!select || !player) return;

  const el = {
    title: player.querySelector("[data-player-title]"),
    note: player.querySelector("[data-player-note]"),
    isNew: player.querySelector("[data-player-new]"),
    video: player.querySelector("[data-player-video]"),
    yt: player.querySelector("[data-player-yt]"),
    empty: player.querySelector("[data-player-empty]"),
    spotify: player.querySelector("[data-player-spotify]"),
    switcher: player.querySelector("[data-source-switch]"),
    linkYt: player.querySelector("[data-link-youtube]"),
    linkSp: player.querySelector("[data-link-spotify]"),
  };

  let current = null;
  let firstLoad = true;

  function stopAll() {
    el.video.pause();
    el.video.removeAttribute("src");
    el.video.load();
    el.video.hidden = true;
    el.yt.innerHTML = "";
    el.yt.hidden = true;
    el.empty.hidden = true;
  }

  function showSource(source) {
    stopAll();
    el.switcher.querySelectorAll("button").forEach(function (btn) {
      const active = btn.dataset.source === source;
      btn.classList.toggle("is-active", active);
      btn.setAttribute("aria-pressed", active ? "true" : "false");
    });

    if (source === "video" && current.video) {
      el.video.src = current.video;
      el.video.hidden = false;
      if (!firstLoad) el.video.play().catch(function () {});
    } else if (source === "youtube" && current.youtube) {
      el.yt.hidden = false;
      if (firstLoad) {
        // Saat halaman dibuka: tampilkan thumbnail saja. Player YouTube (berat, ~1 MB)
        // baru dimuat saat tombol play diklik, jadi halaman lebih cepat terbuka di HP.
        el.yt.appendChild(createYouTubeFacade(current));
      } else {
        el.yt.appendChild(createYouTubeIframe(current.youtube, current.title));
      }
    } else {
      el.empty.hidden = false;
    }
  }

  function loadSong() {
    const opt = select.options[select.selectedIndex];
    current = {
      slug: opt.value,
      title: opt.dataset.title,
      titleJp: opt.dataset.titleJp,
      video: opt.dataset.video,
      youtube: opt.dataset.youtube,
      spotify: opt.dataset.spotify,
      note: opt.dataset.note,
      isNew: opt.dataset.new === "1",
    };

    el.title.textContent = current.title + (current.titleJp ? " — " + current.titleJp : "");
    el.note.textContent = current.note || "";
    el.note.hidden = !current.note;
    el.isNew.hidden = !current.isNew;

    el.switcher.hidden = !(current.video && current.youtube);
    showSource(current.video ? "video" : "youtube");

    el.spotify.innerHTML = "";
    if (current.spotify) {
      const sp = document.createElement("iframe");
      sp.src = "https://open.spotify.com/embed/track/" + encodeURIComponent(current.spotify) + "?utm_source=generator&theme=0";
      sp.title = "Spotify: " + current.title;
      sp.allow = "autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture";
      sp.loading = "lazy";
      el.spotify.appendChild(sp);
    }

    el.linkYt.hidden = !current.youtube;
    if (current.youtube) el.linkYt.href = "https://www.youtube.com/watch?v=" + encodeURIComponent(current.youtube);
    el.linkSp.hidden = !current.spotify;
    if (current.spotify) el.linkSp.href = "https://open.spotify.com/track/" + encodeURIComponent(current.spotify);

    // Simpan pilihan di URL supaya bisa dibagikan: index.php?lagu=slug#musik
    if (!firstLoad && window.history.replaceState) {
      const url = new URL(window.location.href);
      url.searchParams.set("lagu", current.slug);
      url.hash = "musik";
      window.history.replaceState(null, "", url);
    }
    firstLoad = false;
  }

  el.switcher.addEventListener("click", function (e) {
    const btn = e.target.closest("button[data-source]");
    if (btn) showSource(btn.dataset.source);
  });

  select.addEventListener("change", loadSong);
  loadSong();
}

/* ------------------------------------------------------------------
   Galeri (dari v1): geser dengan mouse, klik untuk memperbesar
------------------------------------------------------------------ */
function initGallery() {
  const slider = document.querySelector(".gallery-slider");
  const items = Array.from(document.querySelectorAll("[data-gallery-item]"));
  const modal = document.getElementById("galleryModal");
  if (!slider || !items.length || !modal) return;

  const modalImg = modal.querySelector(".gallery-modal__image");
  const modalCaption = modal.querySelector(".gallery-modal__caption");
  const modalDialog = modal.querySelector(".gallery-modal__dialog");
  const btnClose = modal.querySelector("[data-close-gallery]");

  let currentIndex = 0;
  let isDown = false;
  let dragged = false;
  let startX = 0;
  let scrollLeft = 0;
  let touchStartX = 0;
  let lastFocus = null;

  function openModal(index) {
    currentIndex = index;
    const item = items[currentIndex];
    modalImg.src = item.getAttribute("href");
    modalImg.alt = item.dataset.caption || "Gambar galeri";
    modalCaption.textContent = item.dataset.caption || "";
    if (!modal.classList.contains("is-open")) {
      lastFocus = document.activeElement;
      modal.classList.add("is-open");
      document.body.classList.add("modal-open");
      btnClose.focus();
    }
  }

  function closeModal() {
    modal.classList.remove("is-open");
    document.body.classList.remove("modal-open");
    if (lastFocus) lastFocus.focus();
  }

  const showPrev = () => openModal((currentIndex - 1 + items.length) % items.length);
  const showNext = () => openModal((currentIndex + 1) % items.length);

  items.forEach(function (item, index) {
    item.addEventListener("click", function (e) {
      e.preventDefault();
      if (dragged) return; // jangan buka modal kalau user sedang menggeser
      openModal(index);
    });
  });

  btnClose.addEventListener("click", closeModal);
  modal.querySelector("[data-prev-gallery]").addEventListener("click", showPrev);
  modal.querySelector("[data-next-gallery]").addEventListener("click", showNext);

  modal.addEventListener("click", function (e) {
    if (!modalDialog.contains(e.target)) closeModal();
  });

  document.addEventListener("keydown", function (e) {
    if (!modal.classList.contains("is-open")) return;
    if (e.key === "Escape") closeModal();
    if (e.key === "ArrowLeft") showPrev();
    if (e.key === "ArrowRight") showNext();
  });

  slider.addEventListener("mousedown", function (e) {
    isDown = true;
    dragged = false;
    startX = e.pageX;
    scrollLeft = slider.scrollLeft;
  });

  ["mouseleave", "mouseup"].forEach(function (type) {
    slider.addEventListener(type, function () {
      isDown = false;
      slider.classList.remove("dragging");
    });
  });

  slider.addEventListener("mousemove", function (e) {
    if (!isDown) return;
    const walk = e.pageX - startX;
    if (Math.abs(walk) > 5) {
      dragged = true;
      slider.classList.add("dragging");
    }
    e.preventDefault();
    slider.scrollLeft = scrollLeft - walk * 1.2;
  });

  modalDialog.addEventListener("touchstart", function (e) {
    touchStartX = e.changedTouches[0].screenX;
  }, { passive: true });

  modalDialog.addEventListener("touchend", function (e) {
    const delta = e.changedTouches[0].screenX - touchStartX;
    if (Math.abs(delta) < 40) return;
    if (delta > 0) showPrev();
    else showNext();
  }, { passive: true });
}

/* ------------------------------------------------------------------
   Komentar: tampil/sembunyi, penghitung karakter, konfirmasi hapus
------------------------------------------------------------------ */
function initCommentToggles() {
  document.querySelectorAll("[data-comment-toggle]").forEach(function (btn) {
    const box = document.getElementById(btn.getAttribute("aria-controls"));
    if (!box) return;
    btn.addEventListener("click", function () {
      const hidden = box.hidden = !box.hidden;
      btn.textContent = hidden ? "Tampilkan komentar" : "Sembunyikan komentar";
      btn.setAttribute("aria-expanded", hidden ? "false" : "true");
    });
  });
}

function initCharCounters() {
  document.querySelectorAll("textarea[data-char-counter]").forEach(function (textarea) {
    const counter = textarea.form && textarea.form.querySelector(".char-counter");
    if (!counter) return;
    const max = textarea.maxLength;
    const update = () => { counter.textContent = textarea.value.length + " / " + max; };
    textarea.addEventListener("input", update);
    update();
  });
}

function initConfirmForms() {
  document.querySelectorAll("form[data-confirm]").forEach(function (form) {
    form.addEventListener("submit", function (e) {
      if (!window.confirm(form.dataset.confirm)) e.preventDefault();
    });
  });
}

/* ------------------------------------------------------------------
   Countdown live: otomatis memilih live terdekat yang belum dimulai
------------------------------------------------------------------ */
function initCountdown() {
  const box = document.querySelector("[data-countdown]");
  const events = Array.from(document.querySelectorAll("[data-event]"));
  if (!box || !events.length) return;

  const titleEl = box.querySelector("[data-countdown-title]");
  const whenEl = box.querySelector("[data-countdown-when]");
  const units = {
    days: box.querySelector('[data-unit="days"]'),
    hours: box.querySelector('[data-unit="hours"]'),
    minutes: box.querySelector('[data-unit="minutes"]'),
    seconds: box.querySelector('[data-unit="seconds"]'),
  };

  const dateFmt = new Intl.DateTimeFormat("id-ID", { weekday: "long", day: "numeric", month: "long", year: "numeric" });
  const timeFmt = new Intl.DateTimeFormat("id-ID", { hour: "2-digit", minute: "2-digit", timeZoneName: "short" });
  const jstFmt = new Intl.DateTimeFormat("id-ID", { hour: "2-digit", minute: "2-digit", timeZone: "Asia/Tokyo" });
  // Untuk live yang jamnya belum diumumkan, tanggalnya harus tanggal Jepang
  // (00:00 JST = 22:00 WIB hari sebelumnya, jadi jangan dikonversi).
  const jstDateFmt = new Intl.DateTimeFormat("id-ID", { weekday: "long", day: "numeric", month: "long", year: "numeric", timeZone: "Asia/Tokyo" });

  // Tulis ulang jam tiap live ke zona waktu pengunjung, misal "16.00 WIB (18.00 JST)".
  events.forEach(function (li) {
    const start = new Date(li.dataset.start);
    const out = li.querySelector("[data-local-time]");
    if (out && !li.dataset.tba) {
      out.textContent = dateFmt.format(start) + ", " + timeFmt.format(start) + " (" + jstFmt.format(start) + " JST)";
    }
  });

  let filter = "all";
  let target = null;

  function pickTarget() {
    const now = Date.now();
    target = events.find(function (li) {
      return new Date(li.dataset.start).getTime() > now && (filter === "all" || li.dataset.solo === "1");
    }) || null;

    events.forEach((li) => li.classList.toggle("is-next", li === target));

    if (!target) {
      titleEl.textContent = filter === "solo" ? "live solo berikutnya (belum diumumkan)" : "live berikutnya (belum diumumkan)";
      whenEl.textContent = "";
      Object.values(units).forEach((u) => { u.textContent = "0"; });
      return;
    }

    const start = new Date(target.dataset.start);
    titleEl.textContent = target.dataset.title;
    whenEl.textContent = target.dataset.tba
      ? jstDateFmt.format(start) + " (tanggal Jepang). Jam mulai belum diumumkan, jadi dihitung sampai awal hari itu (00.00 JST)."
      : dateFmt.format(start) + " pukul " + timeFmt.format(start);
  }

  function tick() {
    if (!target) return;
    let diff = new Date(target.dataset.start).getTime() - Date.now();
    if (diff <= 0) {
      pickTarget(); // live sudah mulai -> pindah ke live berikutnya
      return;
    }
    const s = Math.floor(diff / 1000);
    units.days.textContent = Math.floor(s / 86400);
    units.hours.textContent = String(Math.floor((s % 86400) / 3600)).padStart(2, "0");
    units.minutes.textContent = String(Math.floor((s % 3600) / 60)).padStart(2, "0");
    units.seconds.textContent = String(s % 60).padStart(2, "0");
  }

  box.querySelectorAll("[data-countdown-filter]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      filter = btn.dataset.countdownFilter;
      box.querySelectorAll("[data-countdown-filter]").forEach((b) => b.classList.toggle("is-active", b === btn));
      pickTarget();
      tick();
    });
  });

  pickTarget();
  tick();
  setInterval(tick, 1000);
}
