let globalPegawaiList = [];
let globalKedisiplinanMap = {};
let unsavedInputData = {};
let unsavedInputDataPerFilter = {};
let currentFilterKey = null;

function cloneUnsavedData(data) {
  return JSON.parse(JSON.stringify(data || {}));
}

function buildFilterKey(bulan, tahun, jabatan = []) {
  const jabatanPart = (
    jabatan && jabatan.length ? [...jabatan].sort() : []
  ).join(",");
  return `${bulan}-${tahun}-${jabatanPart}`;
}

$(document).ready(function () {
  clearSavedCheckboxState();
});

function saveUnsavedInputs() {
  let isMobile = window.innerWidth <= 767;

  if (isMobile) {
    $(".mobile-input-card .checkPegawai").each(function () {
      let pegawaiId = $(this).data("pegawai");
      let $card = $(this).closest(".mobile-input-card");

      if (!unsavedInputData[pegawaiId]) {
        unsavedInputData[pegawaiId] = {};
      }
      unsavedInputData[pegawaiId].terlambat =
        $card
          .find('input[name^="terlambat"][data-pegawai="' + pegawaiId + '"]')
          .val() || "0";
      unsavedInputData[pegawaiId].tidak_absen_masuk =
        $card
          .find(
            'input[name^="tidak_absen_masuk"][data-pegawai="' +
              pegawaiId +
              '"]',
          )
          .val() || "0";
      unsavedInputData[pegawaiId].pulang_awal =
        $card
          .find('input[name^="pulang_awal"][data-pegawai="' + pegawaiId + '"]')
          .val() || "0";
      unsavedInputData[pegawaiId].tidak_absen_pulang =
        $card
          .find(
            'input[name^="tidak_absen_pulang"][data-pegawai="' +
              pegawaiId +
              '"]',
          )
          .val() || "0";
      unsavedInputData[pegawaiId].keluar_tidak_izin =
        $card
          .find(
            'input[name^="keluar_tidak_izin"][data-pegawai="' +
              pegawaiId +
              '"]',
          )
          .val() || "0";
      unsavedInputData[pegawaiId].tidak_masuk_tanpa_ket =
        $card
          .find(
            'input[name^="tidak_masuk_tanpa_ket"][data-pegawai="' +
              pegawaiId +
              '"]',
          )
          .val() || "0";
      unsavedInputData[pegawaiId].tidak_masuk_sakit =
        $card
          .find(
            'input[name^="tidak_masuk_sakit"][data-pegawai="' +
              pegawaiId +
              '"]',
          )
          .val() || "0";
      unsavedInputData[pegawaiId].tidak_masuk_kerja =
        $card
          .find(
            'input[name^="tidak_masuk_kerja"][data-pegawai="' +
              pegawaiId +
              '"]',
          )
          .val() || "0";
      unsavedInputData[pegawaiId].bentuk_pembinaan =
        $card
          .find(
            'input[name^="bentuk_pembinaan"][data-pegawai="' + pegawaiId + '"]',
          )
          .val() || "";
      unsavedInputData[pegawaiId].keterangan =
        $card
          .find('input[name^="keterangan"][data-pegawai="' + pegawaiId + '"]')
          .val() || "";
    });
  } else {
    let $trs = $("#tbodyKedisiplinanAjax tr");
    for (let i = 0; i < $trs.length; i += 2) {
      let $trAkumulasi = $trs.eq(i);
      let $trBP = $trs.eq(i + 1);
      let $hiddenInput = $trAkumulasi.find('input[name^="pegawai_id"]');

      if ($hiddenInput.length > 0) {
        let pegawaiId = $hiddenInput.val();
        if (pegawaiId) {
          if (!unsavedInputData[pegawaiId]) {
            unsavedInputData[pegawaiId] = {};
          }
          unsavedInputData[pegawaiId].terlambat =
            $trAkumulasi.find('input[name^="terlambat"]').val() || "0";
          unsavedInputData[pegawaiId].tidak_absen_masuk =
            $trAkumulasi.find('input[name^="tidak_absen_masuk"]').val() || "0";
          unsavedInputData[pegawaiId].pulang_awal =
            $trAkumulasi.find('input[name^="pulang_awal"]').val() || "0";
          unsavedInputData[pegawaiId].tidak_absen_pulang =
            $trAkumulasi.find('input[name^="tidak_absen_pulang"]').val() || "0";
          unsavedInputData[pegawaiId].keluar_tidak_izin =
            $trAkumulasi.find('input[name^="keluar_tidak_izin"]').val() || "0";
          unsavedInputData[pegawaiId].tidak_masuk_tanpa_ket =
            $trAkumulasi.find('input[name^="tidak_masuk_tanpa_ket"]').val() ||
            "0";
          unsavedInputData[pegawaiId].tidak_masuk_sakit =
            $trAkumulasi.find('input[name^="tidak_masuk_sakit"]').val() || "0";
          unsavedInputData[pegawaiId].tidak_masuk_kerja =
            $trAkumulasi.find('input[name^="tidak_masuk_kerja"]').val() || "0";
          unsavedInputData[pegawaiId].bentuk_pembinaan =
            $trBP.find('input[name^="bentuk_pembinaan"]').val() || "";
          unsavedInputData[pegawaiId].keterangan =
            $trBP.find('input[name^="keterangan"]').val() || "";
        }
      }
    }
  }
}

function renderMobileCards(pegawai_list, kedisiplinan_map) {
  let cardsHtml = "";
  if (!pegawai_list || pegawai_list.length === 0) {
    cardsHtml =
      '<div class="text-center text-muted p-4">Belum ada pegawai</div>';
  } else {
    pegawai_list.forEach(function (pegawai, idx) {
      let row = kedisiplinan_map[pegawai.id] || {};
      if (unsavedInputData[pegawai.id]) {
        row = Object.assign({}, row, unsavedInputData[pegawai.id]);
      }
      let isChecked = isPegawaiChecked(pegawai.id);

      let namaEscaped = $("<div>").text(pegawai.nama).html();
      let nipEscaped = $("<div>").text(pegawai.nip).html();
      let bentukPembinaanEscaped = row.bentuk_pembinaan
        ? $("<div>").text(row.bentuk_pembinaan).html()
        : "";
      let keteranganEscaped = row.keterangan
        ? $("<div>").text(row.keterangan).html()
        : "";

      cardsHtml += `
        <div class="border rounded mb-3 p-3 shadow-sm mobile-input-card" style="background:#fff;">
          <div class="d-flex align-items-start mb-2">
            <input type="checkbox" class="checkPegawai me-2 mt-1" data-pegawai="${
              pegawai.id
            }" ${isChecked ? "checked" : ""} style="flex-shrink:0;">
            <div class="flex-grow-1">
              <div class="fw-bold mb-1">No. ${idx + 1} - ${namaEscaped}</div>
              <div class="small text-muted mb-2">NIP: ${nipEscaped}</div>
            </div>
            ${
              row.id
                ? `
            <div class="ms-auto" style="flex-shrink:0;">
              <a href="/user/kelola_disiplin/delete/${row.id}" class="btn btn-danger btn-sm btn-hapus-kedisiplinan" style="min-width:32px; width:32px; height:32px; padding:0; display:inline-flex; align-items:center; justify-content:center;">
                <i class="fas fa-trash"></i>
              </a>
            </div>
            `
                : ""
            }
          </div>
          
          <div class="row g-2 mb-3">
            <div class="col-3">
              <label class="small fw-semibold text-center d-block mb-1" style="color:#5b21b6;">T</label>
              <input type="number" class="form-control form-control-sm input-akumulasi text-center" 
                     name="terlambat[]" value="${row.terlambat ?? 0}" min="0" 
                     data-pegawai="${pegawai.id}" placeholder="0">
            </div>
            <div class="col-3">
              <label class="small fw-semibold text-center d-block mb-1" style="color:#5b21b6;">TAM</label>
              <input type="number" class="form-control form-control-sm input-akumulasi text-center" 
                     name="tidak_absen_masuk[]" value="${
                       row.tidak_absen_masuk ?? 0
                     }" min="0" 
                     data-pegawai="${pegawai.id}" placeholder="0">
            </div>
            <div class="col-3">
              <label class="small fw-semibold text-center d-block mb-1" style="color:#5b21b6;">PA</label>
              <input type="number" class="form-control form-control-sm input-akumulasi text-center" 
                     name="pulang_awal[]" value="${
                       row.pulang_awal ?? 0
                     }" min="0" 
                     data-pegawai="${pegawai.id}" placeholder="0">
            </div>
            <div class="col-3">
              <label class="small fw-semibold text-center d-block mb-1" style="color:#5b21b6;">TAP</label>
              <input type="number" class="form-control form-control-sm input-akumulasi text-center" 
                     name="tidak_absen_pulang[]" value="${
                       row.tidak_absen_pulang ?? 0
                     }" min="0" 
                     data-pegawai="${pegawai.id}" placeholder="0">
            </div>
            <div class="col-3">
              <label class="small fw-semibold text-center d-block mb-1" style="color:#5b21b6;">KTI</label>
              <input type="number" class="form-control form-control-sm input-akumulasi text-center" 
                     name="keluar_tidak_izin[]" value="${
                       row.keluar_tidak_izin ?? 0
                     }" min="0" 
                     data-pegawai="${pegawai.id}" placeholder="0">
            </div>
            <div class="col-3">
              <label class="small fw-semibold text-center d-block mb-1" style="color:#5b21b6;">TK</label>
              <input type="number" class="form-control form-control-sm input-akumulasi text-center" 
                     name="tidak_masuk_tanpa_ket[]" value="${
                       row.tidak_masuk_tanpa_ket ?? 0
                     }" min="0" 
                     data-pegawai="${pegawai.id}" placeholder="0">
            </div>
            <div class="col-3">
              <label class="small fw-semibold text-center d-block mb-1" style="color:#5b21b6;">TMS</label>
              <input type="number" class="form-control form-control-sm input-akumulasi text-center" 
                     name="tidak_masuk_sakit[]" value="${
                       row.tidak_masuk_sakit ?? 0
                     }" min="0" 
                     data-pegawai="${pegawai.id}" placeholder="0">
            </div>
            <div class="col-3">
              <label class="small fw-semibold text-center d-block mb-1" style="color:#5b21b6;">TMK</label>
              <input type="number" class="form-control form-control-sm input-akumulasi text-center" 
                     name="tidak_masuk_kerja[]" value="${
                       row.tidak_masuk_kerja ?? 0
                     }" min="0" 
                     data-pegawai="${pegawai.id}" placeholder="0">
            </div>
          </div>
          
          <div class="mb-2">
            <label class="small fw-semibold mb-1" style="color:#5b21b6;">Bentuk Pembinaan</label>
            <input type="text" class="form-control form-control-sm input-bp" 
                   name="bentuk_pembinaan[]" value="${bentukPembinaanEscaped}" 
                   data-pegawai="${pegawai.id}" placeholder="Bentuk Pembinaan">
          </div>
          
          <div class="mb-2">
            <label class="small fw-semibold mb-1" style="color:#5b21b6;">Keterangan</label>
            <input type="text" class="form-control form-control-sm input-ket" 
                   name="keterangan[]" value="${keteranganEscaped}" 
                   data-pegawai="${pegawai.id}" placeholder="Keterangan">
          </div>
        </div>
      `;
    });
  }
  $("#mobileInputDisiplinCards").html(cardsHtml);
}

function renderTabelKedisiplinan(pegawai_list, kedisiplinan_map) {
  var isMobile = window.innerWidth <= 767;

  if (isMobile) {
    renderMobileCards(pegawai_list, kedisiplinan_map);
  } else {
    let html = "";
    if (!pegawai_list || pegawai_list.length === 0) {
      html =
        '<tr><td colspan="14" class="text-center">Belum ada pegawai</td></tr>';
    } else {
      pegawai_list.forEach(function (pegawai, idx) {
        let row = kedisiplinan_map[pegawai.id] || {};
        if (unsavedInputData[pegawai.id]) {
          row = Object.assign({}, row, unsavedInputData[pegawai.id]);
        }
        let isChecked = isPegawaiChecked(pegawai.id);

        html += `<tr>
                    <td><input type="checkbox" class="checkPegawai" data-pegawai="${
                      pegawai.id
                    }" ${isChecked ? "checked" : ""}></td>
                    <td data-label="No">${idx + 1}</td>
                    <td data-label="Nama Pegawai" style="width:140px;"><input type="hidden" name="pegawai_id[]" value="${
                      pegawai.id
                    }">
                    <input type="text" class="form-control-plaintext" value="${
                      pegawai.nama
                    }" readonly></td>
                    <td data-label="NIP" style="width:180px;"><input type="text" class="form-control-plaintext" value="${
                      pegawai.nip
                    }" readonly></td>
                    <td data-label="T" style="width:80px;"><input type="number" class="form-control" name="terlambat[]" value="${
                      row.terlambat ?? 0
                    }" min="0"></td>
                    <td data-label="TAM" style="width:80px;"><input type="number" class="form-control" name="tidak_absen_masuk[]" value="${
                      row.tidak_absen_masuk ?? 0
                    }" min="0"></td>
                    <td data-label="PA" style="width:80px;"><input type="number" class="form-control" name="pulang_awal[]" value="${
                      row.pulang_awal ?? 0
                    }" min="0"></td>
                    <td data-label="TAP" style="width:80px;"><input type="number" class="form-control" name="tidak_absen_pulang[]" value="${
                      row.tidak_absen_pulang ?? 0
                    }" min="0"></td>
                    <td data-label="KTI" style="width:80px;"><input type="number" class="form-control" name="keluar_tidak_izin[]" value="${
                      row.keluar_tidak_izin ?? 0
                    }" min="0"></td>
                    <td data-label="TK" style="width:80px;"><input type="number" class="form-control" name="tidak_masuk_tanpa_ket[]" value="${
                      row.tidak_masuk_tanpa_ket ?? 0
                    }" min="0"></td>
                    <td data-label="TMS" style="width:80px;"><input type="number" class="form-control" name="tidak_masuk_sakit[]" value="${
                      row.tidak_masuk_sakit ?? 0
                    }" min="0"></td>
                    <td data-label="TMK" style="width:80px;"><input type="number" class="form-control" name="tidak_masuk_kerja[]" value="${
                      row.tidak_masuk_kerja ?? 0
                    }" min="0"></td>
                    <td data-label="Aksi" rowspan="2" style="vertical-align:middle; text-align:center;">`;
        if (row.id) {
          html += `<a href="/user/kelola_disiplin/delete/${row.id}" class="btn btn-danger btn-sm btn-hapus-kedisiplinan"><i class="fas fa-trash"></i></a>`;
        }
        html += `</td></tr>`;
        html += `<tr>
                    <td colspan="6"><input type="text" class="form-control input-bp" name="bentuk_pembinaan[]" value="${
                      row.bentuk_pembinaan
                        ? row.bentuk_pembinaan.replace(/\"/g, "&quot;")
                        : ""
                    }" placeholder="Bentuk Pembinaan"></td>
                    <td colspan="5"><input type="text" class="form-control input-ket" name="keterangan[]" value="${
                      row.keterangan
                        ? row.keterangan.replace(/\"/g, "&quot;")
                        : ""
                    }" placeholder="Keterangan"></td>
                </tr>`;
      });
    }
    $("#tbodyKedisiplinanAjax").html(html);
  }

  let visibleCheckboxes = $(".checkPegawai:visible");
  let all = visibleCheckboxes.length;
  let checked = visibleCheckboxes.filter(":checked").length;
  $("#checkAllBox, #checkAllBoxHeader").prop(
    "checked",
    all > 0 && all === checked,
  );

  updateBadgeTerpilih();
}

function loadTabelKedisiplinanAjax(bulan, tahun, jabatan = []) {
  const filterKey = buildFilterKey(bulan, tahun, jabatan);
  const isSameFilter = filterKey === currentFilterKey;

  let isBulanTahunChanged = false;
  if (currentFilterKey) {
    const parts = currentFilterKey.split("-");
    const oldBulan = parts[0];
    const oldTahun = parts[1];
    if (oldBulan !== bulan.toString() || oldTahun !== tahun.toString()) {
      isBulanTahunChanged = true;
    }
  } else {
    isBulanTahunChanged = false;
  }

  if (isSameFilter) {
    saveUnsavedInputs();
  } else {
    saveUnsavedInputs();

    if (currentFilterKey) {
      unsavedInputDataPerFilter[currentFilterKey] =
        cloneUnsavedData(unsavedInputData);
    }

    if (isBulanTahunChanged) {
      unsavedInputData = {};
      const newFilterData = cloneUnsavedData(
        unsavedInputDataPerFilter[filterKey] || {},
      );
      unsavedInputData = newFilterData;
    } else {
      const newFilterData = cloneUnsavedData(
        unsavedInputDataPerFilter[filterKey] || {},
      );
      Object.keys(newFilterData).forEach(function (pegawaiId) {
        if (
          newFilterData[pegawaiId] &&
          Object.keys(newFilterData[pegawaiId]).length > 0
        ) {
          unsavedInputData[pegawaiId] = newFilterData[pegawaiId];
        }
      });
    }
  }
  currentFilterKey = filterKey;

  $("#tbodyKedisiplinanAjax").html(
    '<tr><td colspan="14" class="text-center">Memuat data...</td></tr>',
  );

  let params = {
    bulan: bulan,
    tahun: tahun,
  };

  if (jabatan && jabatan.length > 0) {
    jabatan.forEach(function (jab, index) {
      params["jabatan[" + index + "]"] = jab;
    });
  }

  $.get(
    "/user/getPegawaiKedisiplinanAjax",
    params,
    function (res) {
      globalPegawaiList = res.pegawai_list;
      globalKedisiplinanMap = res.kedisiplinan_map;
      renderTabelKedisiplinan(globalPegawaiList, globalKedisiplinanMap);
    },
    "json",
  );
}

$(document).ready(function () {
  let bulan = $("#inputBulan").val();
  let tahun = $("#inputTahun").val();
  loadTabelKedisiplinanAjax(bulan, tahun, []);

  setTimeout(() => {
    updateBadgeTerpilih();
  }, 500);

  $("#bulan, #tahun").on("change", function () {
    let bulan = $("#bulan").val();
    let tahun = $("#tahun").val();

    $("#inputBulan").val(bulan);
    $("#inputTahun").val(tahun);

    loadTabelKedisiplinanAjax(bulan, tahun, []);
  });

  $("#filterForm").on("submit", function (e) {
    e.preventDefault();
    let bulan = $("#bulan").val();
    let tahun = $("#tahun").val();
    let jabatan = $("#filter_jabatan").val() || [];

    $("#inputBulan").val(bulan);
    $("#inputTahun").val(tahun);

    loadTabelKedisiplinanAjax(bulan, tahun, jabatan);
  });

  $("#btnSimpanSemua").on("click", function (e) {
    e.preventDefault();

    saveUnsavedInputs();

    const checkedIds = JSON.parse(
      localStorage.getItem("input_disiplin_checked_ids") || "[]",
    );

    if (checkedIds.length === 0) {
      Swal.fire(
        "Peringatan",
        "Silakan centang minimal satu pegawai yang ingin disimpan!",
        "warning",
      );
      return;
    }

    let rows = [];
    let isMobile = window.innerWidth <= 767;

    checkedIds.forEach(function (pegawaiId) {
      let row = {};
      row["pegawai_id"] = pegawaiId;

      if (unsavedInputData[pegawaiId]) {
        let data = unsavedInputData[pegawaiId];
        row["terlambat"] = data.terlambat || "0";
        row["tidak_absen_masuk"] = data.tidak_absen_masuk || "0";
        row["pulang_awal"] = data.pulang_awal || "0";
        row["tidak_absen_pulang"] = data.tidak_absen_pulang || "0";
        row["keluar_tidak_izin"] = data.keluar_tidak_izin || "0";
        row["tidak_masuk_tanpa_ket"] = data.tidak_masuk_tanpa_ket || "0";
        row["tidak_masuk_sakit"] = data.tidak_masuk_sakit || "0";
        row["tidak_masuk_kerja"] = data.tidak_masuk_kerja || "0";
        row["bentuk_pembinaan"] = data.bentuk_pembinaan || "";
        row["keterangan"] = data.keterangan || "";
      } else {
        let $checkbox = $(`.checkPegawai[data-pegawai="${pegawaiId}"]`);
        if ($checkbox.length > 0 && $checkbox.is(":visible")) {
          if (isMobile) {
            let $card = $checkbox.closest(".mobile-input-card");
            row["terlambat"] =
              $card
                .find(
                  'input[name^="terlambat"][data-pegawai="' + pegawaiId + '"]',
                )
                .val() || "0";
            row["tidak_absen_masuk"] =
              $card
                .find(
                  'input[name^="tidak_absen_masuk"][data-pegawai="' +
                    pegawaiId +
                    '"]',
                )
                .val() || "0";
            row["pulang_awal"] =
              $card
                .find(
                  'input[name^="pulang_awal"][data-pegawai="' +
                    pegawaiId +
                    '"]',
                )
                .val() || "0";
            row["tidak_absen_pulang"] =
              $card
                .find(
                  'input[name^="tidak_absen_pulang"][data-pegawai="' +
                    pegawaiId +
                    '"]',
                )
                .val() || "0";
            row["keluar_tidak_izin"] =
              $card
                .find(
                  'input[name^="keluar_tidak_izin"][data-pegawai="' +
                    pegawaiId +
                    '"]',
                )
                .val() || "0";
            row["tidak_masuk_tanpa_ket"] =
              $card
                .find(
                  'input[name^="tidak_masuk_tanpa_ket"][data-pegawai="' +
                    pegawaiId +
                    '"]',
                )
                .val() || "0";
            row["tidak_masuk_sakit"] =
              $card
                .find(
                  'input[name^="tidak_masuk_sakit"][data-pegawai="' +
                    pegawaiId +
                    '"]',
                )
                .val() || "0";
            row["tidak_masuk_kerja"] =
              $card
                .find(
                  'input[name^="tidak_masuk_kerja"][data-pegawai="' +
                    pegawaiId +
                    '"]',
                )
                .val() || "0";
            row["bentuk_pembinaan"] =
              $card
                .find(
                  'input[name^="bentuk_pembinaan"][data-pegawai="' +
                    pegawaiId +
                    '"]',
                )
                .val() || "";
            row["keterangan"] =
              $card
                .find(
                  'input[name^="keterangan"][data-pegawai="' + pegawaiId + '"]',
                )
                .val() || "";
          } else {
            let $trAkumulasi = $checkbox.closest("tr");
            let $trBP = $trAkumulasi.next("tr");
            if (
              $trBP.length === 0 ||
              $trBP.find('input[name^="bentuk_pembinaan"]').length === 0
            ) {
              let pegawaiIdFromInput = $trAkumulasi
                .find('input[name^="pegawai_id"]')
                .val();
              if (pegawaiIdFromInput === pegawaiId) {
                $trBP = $trAkumulasi.next("tr");
              }
            }
            row["terlambat"] =
              $trAkumulasi.find('input[name^="terlambat"]').val() || "0";
            row["tidak_absen_masuk"] =
              $trAkumulasi.find('input[name^="tidak_absen_masuk"]').val() ||
              "0";
            row["pulang_awal"] =
              $trAkumulasi.find('input[name^="pulang_awal"]').val() || "0";
            row["tidak_absen_pulang"] =
              $trAkumulasi.find('input[name^="tidak_absen_pulang"]').val() ||
              "0";
            row["keluar_tidak_izin"] =
              $trAkumulasi.find('input[name^="keluar_tidak_izin"]').val() ||
              "0";
            row["tidak_masuk_tanpa_ket"] =
              $trAkumulasi.find('input[name^="tidak_masuk_tanpa_ket"]').val() ||
              "0";
            row["tidak_masuk_sakit"] =
              $trAkumulasi.find('input[name^="tidak_masuk_sakit"]').val() ||
              "0";
            row["tidak_masuk_kerja"] =
              $trAkumulasi.find('input[name^="tidak_masuk_kerja"]').val() ||
              "0";
            row["bentuk_pembinaan"] =
              $trBP.find('input[name^="bentuk_pembinaan"]').val() || "";
            row["keterangan"] =
              $trBP.find('input[name^="keterangan"]').val() || "";
          }
        } else {
          let existingData = globalKedisiplinanMap[pegawaiId] || {};
          row["terlambat"] = existingData.terlambat || "0";
          row["tidak_absen_masuk"] = existingData.tidak_absen_masuk || "0";
          row["pulang_awal"] = existingData.pulang_awal || "0";
          row["tidak_absen_pulang"] = existingData.tidak_absen_pulang || "0";
          row["keluar_tidak_izin"] = existingData.keluar_tidak_izin || "0";
          row["tidak_masuk_tanpa_ket"] =
            existingData.tidak_masuk_tanpa_ket || "0";
          row["tidak_masuk_sakit"] = existingData.tidak_masuk_sakit || "0";
          row["tidak_masuk_kerja"] = existingData.tidak_masuk_kerja || "0";
          row["bentuk_pembinaan"] = existingData.bentuk_pembinaan || "";
          row["keterangan"] = existingData.keterangan || "";
        }
      }

      rows.push(row);
    });

    if (rows.length === 0) {
      Swal.fire("Error", "Tidak ada data yang akan dikirim!", "error");
      return;
    }

    let batchSize = 50;
    let totalBatch = Math.ceil(rows.length / batchSize);
    let bulan = $("#inputBulan").val();
    let tahun = $("#inputTahun").val();

    function simpanBatch(batchIndex) {
      if (batchIndex >= totalBatch) {
        unsavedInputData = {};
        Swal.fire("Berhasil", "Semua data berhasil disimpan!", "success").then(
          () => (window.location.href = "/user/kelola_disiplin"),
        );
        return;
      }
      let batchData = rows.slice(
        batchIndex * batchSize,
        (batchIndex + 1) * batchSize,
      );
      if (batchData.length === 0) {
        Swal.fire("Error", "Batch data kosong!", "error");
        return;
      }
      Swal.fire({
        title: "Menyimpan data...",
        html: "Phase " + (batchIndex + 1) + " dari " + totalBatch,
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });
      // Ambil CSRF token: utamakan window.CSRF_HASH dari navbar (paling reliable)
      // Fallback ke DOM selector jika tidak tersedia
      var csrfName, csrfVal;
      if (window.CSRF_HASH) {
        // CSRF_HASH dari navbar_user.php sudah pasti ada
        var $csrfInput = $('#formKedisiplinanTabel input[name^="csrf_"]');
        csrfName = $csrfInput.length > 0 ? $csrfInput.attr('name') : 'csrf_test_name';
        csrfVal  = window.CSRF_HASH;
      } else {
        var $csrfInput = $('#formKedisiplinanTabel input[name^="csrf_"]');
        if ($csrfInput.length === 0) {
          $csrfInput = $('input[name^="csrf_"]').first();
        }
        csrfName = $csrfInput.attr('name');
        csrfVal  = $csrfInput.val();
      }

      if (!csrfName || !csrfVal) {
        console.error('[BATCH] CSRF token tidak ditemukan!');
        Swal.fire("Gagal", "Token keamanan tidak ditemukan. Silakan refresh halaman dan coba lagi.", "error");
        return;
      }

      var postData = {
        data_json: JSON.stringify(batchData),
        bulan: bulan,
        tahun: tahun,
      };
      postData[csrfName] = csrfVal;

      $.ajax({
        url: (window.BASE_URL || "") + "user/inputdisiplin/save_batch",
        type: "POST",
        data: postData,
        dataType: "json",
        success: function (res) {
          if (res.success) {
            simpanBatch(batchIndex + 1);
          } else {
            Swal.fire("Gagal", res.message || "Terjadi kesalahan!", "error");
          }
        },
        error: function (xhr) {
          if (xhr.status === 403) {
            Swal.fire("Gagal", "Akses ditolak (403). Silakan refresh halaman dan coba lagi.", "error");
          } else {
            Swal.fire("Gagal", "Terjadi kesalahan saat menyimpan data (HTTP " + xhr.status + ")", "error");
          }
        },
      });
    }

    simpanBatch(0);
  });

  $("#btnSimpanSemuaMobile").on("click", function (e) {
    e.preventDefault();
    $("#btnSimpanSemua").trigger("click");
  });

  $("#btnSimpanSemuaDesktop").on("click", function (e) {
    e.preventDefault();
    $("#btnSimpanSemua").trigger("click");
  });

  $(document).on("input", "#searchPegawai", function () {
    saveUnsavedInputs();

    let q = $(this).val().toLowerCase();
    let filtered = globalPegawaiList.filter(function (pegawai) {
      return (
        pegawai.nama.toLowerCase().indexOf(q) !== -1 ||
        pegawai.nip.toLowerCase().indexOf(q) !== -1
      );
    });
    renderTabelKedisiplinan(filtered, globalKedisiplinanMap);

    $("#checkAllBox, #checkAllBoxHeader").prop("checked", false);
    let visibleCheckboxes = $(".checkPegawai:visible");
    let all = visibleCheckboxes.length;
    let checked = visibleCheckboxes.filter(":checked").length;
    $("#checkAllBox, #checkAllBoxHeader").prop(
      "checked",
      all > 0 && all === checked,
    );

    updateBadgeTerpilih();
  });

  $(document).on("click", "#btnPilihSemua", function (e) {
    e.preventDefault();
    e.stopPropagation();

    let currentState = $("#checkAllBox").is(":checked");
    let newState = !currentState;

    $("#checkAllBox, #checkAllBoxHeader").prop("checked", newState);
    $(".checkPegawai:visible").prop("checked", newState);

    saveCheckboxState();
    updateBadgeTerpilih();
  });

  $(document).on("change", "#checkAllBox, #checkAllBoxHeader", function () {
    let checked = $(this).is(":checked");
    $(".checkPegawai:visible").prop("checked", checked);
    $("#checkAllBox, #checkAllBoxHeader").prop("checked", checked);

    saveCheckboxState();
    updateBadgeTerpilih();
  });

  $(document).on("change", ".checkPegawai", function () {
    let visibleCheckboxes = $(".checkPegawai:visible");
    let all = visibleCheckboxes.length;
    let checked = visibleCheckboxes.filter(":checked").length;

    $("#checkAllBox, #checkAllBoxHeader").prop(
      "checked",
      all > 0 && all === checked,
    );

    saveCheckboxState();
    updateBadgeTerpilih();
  });

  $(document).on("click", ".mobile-input-card", function (e) {
    if (
      $(e.target).is("input[type='checkbox']") ||
      $(e.target).closest("input[type='checkbox']").length > 0 ||
      $(e.target).is(".btn-hapus-kedisiplinan") ||
      $(e.target).closest(".btn-hapus-kedisiplinan").length > 0 ||
      $(e.target).is("input[type='number']") ||
      $(e.target).is("input[type='text']") ||
      $(e.target).closest("input").length > 0 ||
      $(e.target).is("label") ||
      $(e.target).closest("label").length > 0
    ) {
      return;
    }

    let $checkbox = $(this).find(".checkPegawai");
    if ($checkbox.length > 0) {
      let newState = !$checkbox.is(":checked");
      $checkbox.prop("checked", newState).trigger("change");
    }
  });

  $(document).on(
    "input change",
    ".input-akumulasi, .input-bp, .input-ket",
    function () {
      clearTimeout(window.saveInputTimeout);
      window.saveInputTimeout = setTimeout(function () {
        saveUnsavedInputs();
      }, 300);
    },
  );

  $(document).on("click", ".btn-hapus-kedisiplinan", function (e) {
    var href = $(this).attr("href");
    if (href && href.includes("kelola_disiplin/delete/")) {
      e.preventDefault();
      Swal.fire({
        title: "Konfirmasi Hapus",
        text: "Anda yakin ingin menghapus data ini?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#39396a",
        confirmButtonText: "Ya, Hapus",
        cancelButtonText: "Batal",
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: href,
            type: "GET",
            success: function (res) {
              Swal.fire(
                "Berhasil",
                "Data kedisiplinan berhasil dihapus",
                "success",
              );
              let bulan = $("#inputBulan").val();
              let tahun = $("#inputTahun").val();
              loadTabelKedisiplinanAjax(bulan, tahun);
            },
            error: function () {
              Swal.fire(
                "Gagal",
                "Terjadi kesalahan saat menghapus data",
                "error",
              );
            },
          });
        }
      });
    }
  });
});

function saveCheckboxState() {
  const checkboxes = document.querySelectorAll(".checkPegawai");
  let checkedIds = JSON.parse(
    localStorage.getItem("input_disiplin_checked_ids") || "[]",
  );

  checkboxes.forEach((cb) => {
    const pegawaiId = cb.getAttribute("data-pegawai");
    if (cb.checked && !checkedIds.includes(pegawaiId)) {
      checkedIds.push(pegawaiId);
    } else if (!cb.checked && checkedIds.includes(pegawaiId)) {
      checkedIds = checkedIds.filter((id) => id !== pegawaiId);
    }
  });

  localStorage.setItem(
    "input_disiplin_checked_ids",
    JSON.stringify(checkedIds),
  );
}

function isPegawaiChecked(pegawaiId) {
  const checkedIds = JSON.parse(
    localStorage.getItem("input_disiplin_checked_ids") || "[]",
  );
  return checkedIds.includes(pegawaiId.toString());
}

function clearSavedCheckboxState() {
  localStorage.removeItem("input_disiplin_checked_ids");
  const checkboxes = document.querySelectorAll(".checkPegawai");
  checkboxes.forEach((cb) => (cb.checked = false));
  updateBadgeTerpilih();
}

let notificationTimer = null;

function updateBadgeTerpilih() {
  const checkedIds = JSON.parse(
    localStorage.getItem("input_disiplin_checked_ids") || "[]",
  );
  const totalChecked = checkedIds.length;

  const notifPopup = document.getElementById("notifDataTerpilih");
  const notifText = document.getElementById("notifTextTerpilih");

  if (notifPopup && notifText) {
    if (totalChecked > 0) {
      notifText.textContent = totalChecked + " data terpilih";
      showNotification();
    } else {
      hideNotification();
    }
  }
}

function showNotification() {
  const notifPopup = document.getElementById("notifDataTerpilih");
  if (notifPopup) {
    if (notificationTimer) {
      clearTimeout(notificationTimer);
      notificationTimer = null;
    }

    notifPopup.classList.remove("show");
    notifPopup.style.display = "block";
    setTimeout(() => {
      notifPopup.classList.add("show");

      notificationTimer = setTimeout(() => {
        hideNotification();
        notificationTimer = null;
      }, 3000);
    }, 10);
  }
}

function hideNotification() {
  const notifPopup = document.getElementById("notifDataTerpilih");
  if (notifPopup) {
    if (notificationTimer) {
      clearTimeout(notificationTimer);
      notificationTimer = null;
    }

    notifPopup.classList.remove("show");
    setTimeout(() => {
      if (!notifPopup.classList.contains("show")) {
        notifPopup.style.display = "none";
      }
    }, 300);
  }
}
