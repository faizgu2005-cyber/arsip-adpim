(function () {
  var $ = function (s) { return document.querySelector(s); };

  // Menu burger (layar kecil)
  var burger = $('#burger'), side = $('#sidebar'), scrim = $('#scrim');
  function menu(buka) {
    if (!side) return;
    side.classList.toggle('open', buka);
    scrim.hidden = !buka;
    burger.setAttribute('aria-expanded', buka ? 'true' : 'false');
  }
  if (burger) {
    burger.addEventListener('click', function () { menu(!side.classList.contains('open')); });
    scrim.addEventListener('click', function () { menu(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') menu(false); });
  }

  // Tampilkan/sembunyikan password
  var lihat = $('#lihat-password'), pw = $('#p');
  if (lihat && pw) {
    lihat.addEventListener('click', function () {
      var tampil = pw.type === 'password';
      pw.type = tampil ? 'text' : 'password';
      lihat.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Tampilkan password');
    });
  }

  // Filter keterangan langsung dikirim
  var sel = $('[data-kirim-otomatis]');
  if (sel) sel.addEventListener('change', function () { sel.form.submit(); });

  // Dialog hapus
  var dlg = $('#dlg-hapus');
  if (dlg && dlg.showModal) {
    var form = $('#dlg-form'), nama = $('#dlg-nama');
    document.addEventListener('click', function (e) {
      var tombol = e.target.closest('[data-hapus]');
      if (!tombol) return;
      e.preventDefault();
      form.action = tombol.form.action;
      nama.textContent = tombol.getAttribute('data-nama');
      dlg.showModal();
    });
    dlg.addEventListener('click', function (e) {
      if (e.target === dlg || e.target.closest('[data-tutup]')) dlg.close();
    });
  }

  // Nama file terpilih pada formulir
  var file = $('#f-file'), label = $('#nama-file'), hapus = $('#f-hapus');
  if (file && label) {
    file.addEventListener('change', function () {
      label.textContent = file.files[0] ? file.files[0].name : label.getAttribute('data-awal');
      if (file.files[0] && hapus) hapus.checked = false;
    });
  }

  // Pemberitahuan hilang sendiri
  var toast = $('.toast');
  if (toast) setTimeout(function () {
    toast.classList.add('hide');
    setTimeout(function () { toast.remove(); }, 400);
  }, 4000);
})();
