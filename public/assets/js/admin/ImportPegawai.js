// ===== Import CSV Progress Tracking Script =====

// Import CSV Progress Tracking Variables
let progressInterval;
let isImporting = false;

$(document).ready(function () {
  // Aggressive fix for aria-hidden warning
  function fixAriaHidden() {
    $(".modal").each(function () {
      var modal = $(this);
      modal.removeAttr("aria-hidden");
      modal.find('[tabindex="-1"]').removeAttr("tabindex");
      // Jangan blur input yang sedang aktif
      modal
        .find("input:not(:focus), select:not(:focus), textarea:not(:focus)")
        .blur();
    });
    // Jangan blur jika yang aktif adalah input pencarian
    if (!$(document.activeElement).is("input, select, textarea")) {
      $(document.activeElement).blur();
    }
  }

  // Run fix immediately
  fixAriaHidden();

  // Run fix periodically
  setInterval(fixAriaHidden, 100);

  // Fix aria-hidden warning for import modal
  $("#importPegawaiModal").on("hidden.bs.modal", function (e) {
    var modal = $(e.target);
    modal.removeAttr("aria-hidden");
    modal.find('[tabindex="-1"]').removeAttr("tabindex");
    // Jangan blur input yang sedang aktif
    modal
      .find("input:not(:focus), select:not(:focus), textarea:not(:focus)")
      .blur();
    // Jangan blur jika yang aktif adalah input pencarian
    if (!$(document.activeElement).is("input, select, textarea")) {
      $(document.activeElement).blur();
    }

    // Force remove aria-hidden
    setTimeout(function () {
      modal.removeAttr("aria-hidden");
      fixAriaHidden();
    }, 100);
  });

  $("#importPegawaiModal").on("show.bs.modal", function (e) {
    var modal = $(e.target);
    modal.removeAttr("aria-hidden");
  });

  // Additional fix for import modal
  $("#importPegawaiModal").on(
    "click",
    '[data-bs-dismiss="modal"]',
    function () {
      var modal = $(this).closest(".modal");
      setTimeout(function () {
        modal.removeAttr("aria-hidden");
        modal.find('[tabindex="-1"]').removeAttr("tabindex");
        // Jangan blur input yang sedang aktif
        modal
          .find("input:not(:focus), select:not(:focus), textarea:not(:focus)")
          .blur();
        // Jangan blur jika yang aktif adalah input pencarian
        if (!$(document.activeElement).is("input, select, textarea")) {
          $(document.activeElement).blur();
        }
        fixAriaHidden();
      }, 100);
    }
  );

  // ===== IMPORT CSV PROGRESS TRACKING =====

  // Handle form submission
  $("#importForm").on("submit", function (e) {
    e.preventDefault();

    // Prevent multiple submissions
    if (isImporting) {
      return;
    }

    const fileInput = $("#file_csv")[0];
    if (!fileInput.files[0]) {
      Swal.fire("Error", "Pilih file CSV terlebih dahulu!", "error");
      return;
    }

    // Show progress step
    $("#step1").hide();
    $("#step2").show();
    $("#importBtn")
      .prop("disabled", true)
      .html('<i class="fas fa-spinner fa-spin me-1"></i> Importing...');
    $("#cancelBtn").prop("disabled", true);

    isImporting = true;

    // Start progress tracking
    startProgressTracking();

    // Submit form via AJAX
    const formData = new FormData(this);

    $.ajax({
      url: $(this).attr("action"),
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        isImporting = false;
        clearInterval(progressInterval);

        // Show final results
        showFinalResults();

        // Change button behavior - prevent re-import
        $("#importBtn")
          .prop("disabled", false)
          .html('<i class="fas fa-check me-1"></i> Selesai')
          .off("click")
          .on("click", function () {
            $("#importPegawaiModal").modal("hide");
            location.reload();
          });
        $("#cancelBtn").prop("disabled", false).html("Tutup");

        // Stop all loading animations
        $("#loadingSection").hide();
        $(".progress-bar-animated").removeClass("progress-bar-animated");
        $(".progress-bar-striped").removeClass("progress-bar-striped");
      },
      error: function (xhr, status, error) {
        isImporting = false;
        clearInterval(progressInterval);

        $("#currentProcessing").html(
          '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Error: ' +
            error +
            "</div>"
        );
        $("#importBtn")
          .prop("disabled", false)
          .html('<i class="fas fa-upload me-1"></i> Import');
        $("#cancelBtn").prop("disabled", false);
      },
    });
  });

  // Handle modal close
  $("#importPegawaiModal").on("hidden.bs.modal", function () {
    if (isImporting) {
      clearInterval(progressInterval);
      isImporting = false;
    }

    // Return to normal (short) modal on next open
    $("#importPegawaiModal").removeClass("import-modal-expanded");

    // Reset form
    $("#step1").show();
    $("#step2").hide();
    $("#importBtn")
      .prop("disabled", false)
      .html('<i class="fas fa-upload me-1"></i> Import')
      .off("click")
      .on("click", function () {
        $("#importForm").submit();
      });
    $("#cancelBtn").prop("disabled", false).html("Batal");
    $("#importForm")[0].reset();

    // Reset progress
    $("#progressBar").css("width", "0%").text("0%");
    $("#totalProcessed").text("0");
    $("#totalUpdated").text("0");

    // Fix aria-hidden
    var modal = $(this);
    modal.removeAttr("aria-hidden");
    modal.find('[tabindex="-1"]').removeAttr("tabindex");
    modal.find("input, select, textarea").blur();
    $(document.activeElement).blur();

    // Force remove aria-hidden
    setTimeout(function () {
      modal.removeAttr("aria-hidden");
      fixAriaHidden();
    }, 100);
    $("#totalNew").text("0");
    $("#totalMutasi").text("0");
    $("#totalSkip").text("0");
    $("#currentProcessing").html("Menunggu data...");
    $("#recentProcessed").html('<div class="text-muted">Belum ada data</div>');

    // Reload page to show updated data
    location.reload();
  });

  // Progress tracking function
  function startProgressTracking() {
    let progress = 0;
    let lastProcessedData = [];

    progressInterval = setInterval(function () {
      if (!isImporting) {
        clearInterval(progressInterval);
        return;
      }

      // Simulate progress (since we can't get real-time progress from server)
      progress += Math.random() * 10;
      if (progress > 85) progress = 85; // Don't reach 100% until complete

      $("#progressBar")
        .css("width", progress + "%")
        .text(Math.round(progress) + "%");

      // Fetch progress data from server
      $.ajax({
        url: "/admin/get_import_progress",
        type: "GET",
        dataType: "json",
        success: function (data) {
          if (data.status !== "no_data") {
            $("#totalProcessed").text(data.processed || 0);
            $("#totalUpdated").text(data.updated || 0);
            $("#totalNew").text(data.new || 0);
            $("#totalMutasi").text(data.mutasi || 0);
            $("#totalSkip").text(data.skip || 0);

            // Update current processing
            if (data.processed_data && data.processed_data.length > 0) {
              const current =
                data.processed_data[data.processed_data.length - 1];
              let statusText = "";

              if (current.action === "new") {
                statusText = `<i class="fas fa-user-plus text-primary"></i> Menambahkan pegawai baru: <strong>${current.nama}</strong> (${current.nip})`;
              } else if (current.action === "updated") {
                statusText = `<i class="fas fa-edit text-success"></i> Update data: <strong>${
                  current.nama
                }</strong> (${current.nip}) - ${current.changes.join(
                  ", "
                )} berubah`;
              } else if (current.action === "mutasi") {
                statusText = `<i class="fas fa-exchange-alt text-warning"></i> Mutasi: <strong>${current.nama}</strong> dari ${current.satker_lama} ke ${current.satker_baru}`;
              } else if (current.action === "updated_mutasi") {
                statusText = `<i class="fas fa-edit text-success"></i> Update & Mutasi: <strong>${
                  current.nama
                }</strong> (${current.nip}) - ${current.changes.join(
                  ", "
                )} berubah & pindah ke ${current.satker_baru}`;
              } else if (current.action === "skip") {
                statusText = `<i class="fas fa-minus-circle text-secondary"></i> Skip: <strong>${current.nama}</strong> (${current.nip}) - Data tidak berubah`;
              }

              $("#currentProcessing").html(
                `<div class="alert alert-info">${statusText}</div>`
              );

              // Update recent processed with detailed info
              const recentHtml = data.processed_data
                .slice(-5)
                .map((item) => {
                  let icon, text, color;

                  switch (item.action) {
                    case "new":
                      icon = "fas fa-user-plus";
                      text = "Baru";
                      color = "text-primary";
                      break;
                    case "updated":
                      icon = "fas fa-edit";
                      text = `Update (${item.changes.join(", ")})`;
                      color = "text-success";
                      break;
                    case "mutasi":
                      icon = "fas fa-exchange-alt";
                      text = "Mutasi";
                      color = "text-warning";
                      break;
                    case "updated_mutasi":
                      icon = "fas fa-edit";
                      text = `Update + Mutasi (${item.changes.join(", ")})`;
                      color = "text-success";
                      break;
                    case "skip":
                      icon = "fas fa-minus-circle";
                      text = "Skip";
                      color = "text-secondary";
                      break;
                  }

                  return `<div><i class="${icon} ${color}"></i> ${item.nama} - ${text}</div>`;
                })
                .join("");

              $("#recentProcessed").html(recentHtml);
            }

            // Show errors if any
            if (data.error_messages && data.error_messages.length > 0) {
              const errorHtml = data.error_messages
                .slice(-3)
                .map(
                  (error) =>
                    `<div class="text-danger"><i class="fas fa-exclamation-triangle"></i> ${error}</div>`
                )
                .join("");
              $("#recentProcessed").append(errorHtml);
            }
          }
        },
        error: function () {
          // Continue even if progress fetch fails
        },
      });
    }, 1000); // Update every second
  }

  // Function to show final results
  function showFinalResults() {
    // Expand modal only when final results are shown
    $("#importPegawaiModal").addClass("import-modal-expanded");

    // Set progress to 100% and stop any ongoing progress
    clearInterval(progressInterval);
    $("#progressBar").css("width", "100%").text("100%");

    // Stop all animations
    $("#loadingSection").hide();
    $(".progress-bar-animated").removeClass("progress-bar-animated");
    $(".progress-bar-striped").removeClass("progress-bar-striped");

    // Get final data from server
    $.ajax({
      url: "/admin/get_import_progress",
      type: "GET",
      dataType: "json",
      success: function (data) {
        if (data.status !== "no_data") {
          // Update final counters
          $("#totalProcessed").text(data.processed || 0);
          $("#totalUpdated").text(data.updated || 0);
          $("#totalNew").text(data.new || 0);
          $("#totalMutasi").text(data.mutasi || 0);
          $("#totalSkip").text(data.skip || 0);

          // Show completion message
          $("#currentProcessing").html(`
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle"></i> Import CSV Selesai!</h5>
                            <p class="mb-0">Semua data telah berhasil diproses.</p>
                        </div>
                    `);

          // Show processed data summary with detailed comparison
          let resultsHtml = "";
          if (data.processed_data && data.processed_data.length > 0) {
            resultsHtml += "<h6>Detail Data yang Diproses:</h6>";

            // Desktop: table view
            resultsHtml +=
              '<div class="import-results-table table-responsive">';
            resultsHtml += '<table class="table table-sm table-bordered">';
            resultsHtml +=
              '<thead class="table-light"><tr><th>Nama</th><th>NIP</th><th>Golongan</th><th>Jabatan</th><th>Satker</th><th>Status</th></tr></thead><tbody>';

            // Mobile: card view (rendered too, toggled via CSS)
            let cardsHtml = '<div class="import-results-cards">';

            // Sort data: updated/mutasi first, then others
            const sortedData = data.processed_data.sort((a, b) => {
              // Priority order: updated_mutasi > updated > mutasi > new > skip
              const priority = {
                updated_mutasi: 1,
                updated: 2,
                mutasi: 3,
                new: 4,
                skip: 5,
              };
              return (priority[a.action] || 6) - (priority[b.action] || 6);
            });

            let hasShownSeparator = false;
            sortedData.forEach((item) => {
              let statusBadge, rowClass;

              // Add separator only when transitioning from changed data to unchanged data
              if (!hasShownSeparator && item.action === "skip") {
                resultsHtml +=
                  '<tr class="table-light"><td colspan="6" class="text-center text-muted py-2"><small>--- Data yang tidak berubah ---</small></td></tr>';
                cardsHtml +=
                  '<div class="import-result-separator">--- Data yang tidak berubah ---</div>';
                hasShownSeparator = true;
              }

              switch (item.action) {
                case "new":
                  statusBadge = '<span class="badge bg-primary">Baru</span>';
                  rowClass = "";
                  break;
                case "updated":
                  statusBadge = '<span class="badge bg-success">Updated</span>';
                  rowClass = "table-warning";
                  break;
                case "mutasi":
                  statusBadge = '<span class="badge bg-warning">Mutasi</span>';
                  rowClass = "table-danger";
                  break;
                case "updated_mutasi":
                  statusBadge =
                    '<span class="badge bg-success">Updated + Mutasi</span>';
                  rowClass = "table-danger";
                  break;
                case "skip":
                  statusBadge = '<span class="badge bg-secondary">Skip</span>';
                  rowClass = "table-light";
                  break;
              }

              // Format cell content based on action
              let golonganCell, jabatanCell, satkerCell;

              if (item.action === "skip" || item.action === "new" || !item.old_data) {
                // Skip & New: tampilkan data saat ini saja (tidak ada perbandingan)
                golonganCell = item.new_data ? item.new_data.golongan : "-";
                jabatanCell  = item.new_data ? item.new_data.jabatan  : "-";
                satkerCell   = item.new_data ? item.new_data.satker   : "-";
              } else {
                // Updated / mutasi: tampilkan perbandingan lama vs baru
                const golChanged =
                  item.old_data.golongan !== item.new_data.golongan;
                const jabChanged =
                  item.old_data.jabatan !== item.new_data.jabatan;
                const satkerChanged =
                  item.old_data.satker_id !== null &&
                  item.old_data.satker_id !== item.new_data.satker_id &&
                  item.old_data.satker !== item.new_data.satker;

                // Format golongan
                if (golChanged) {
                  golonganCell = `${item.old_data.golongan} -> <span class="bg-warning">${item.new_data.golongan}</span>`;
                } else {
                  golonganCell = item.new_data.golongan;
                }

                // Format jabatan
                if (jabChanged) {
                  jabatanCell = `${item.old_data.jabatan} -> <span class="bg-warning">${item.new_data.jabatan}</span>`;
                } else {
                  jabatanCell = item.new_data.jabatan;
                }

                // Format satker
                if (satkerChanged) {
                  satkerCell = `${item.old_data.satker} -> <span class="bg-danger text-white">${item.new_data.satker}</span>`;
                } else {
                  satkerCell = item.new_data.satker;
                }
              }

              resultsHtml += `
                                <tr class="${rowClass}">
                                    <td>${item.nama}</td>
                                    <td>${item.nip}</td>
                                    <td>${golonganCell}</td>
                                    <td>${jabatanCell}</td>
                                    <td>${satkerCell}</td>
                                    <td>${statusBadge}</td>
                                </tr>
                            `;

              // Card item (mobile)
              cardsHtml += `
                                <div class="import-result-card-item">
                                    <div class="import-result-card-header">
                                        <div class="import-result-card-main">
                                            <div class="import-result-name">${item.nama}</div>
                                            <div class="import-result-nip">${item.nip}</div>
                                        </div>
                                        <div class="import-result-status">${statusBadge}</div>
                                    </div>
                                    <div class="import-result-card-body">
                                        <div class="import-field">
                                            <span class="label">Golongan</span>
                                            <span class="value">${golonganCell}</span>
                                        </div>
                                        <div class="import-field">
                                            <span class="label">Jabatan</span>
                                            <span class="value">${jabatanCell}</span>
                                        </div>
                                        <div class="import-field">
                                            <span class="label">Satker</span>
                                            <span class="value">${satkerCell}</span>
                                        </div>
                                    </div>
                                </div>
                            `;
            });

            resultsHtml += "</tbody></table></div>";
            cardsHtml += "</div>";
            resultsHtml += cardsHtml;
          }

          // Show errors if any
          if (data.error_messages && data.error_messages.length > 0) {
            resultsHtml += `
                            <div class="alert alert-warning mt-3">
                                <h6><i class="fas fa-exclamation-triangle"></i> Data yang Gagal Diproses (${
                                  data.error_messages.length
                                }):</h6>
                                <ul class="mb-0">
                                    ${data.error_messages
                                      .map((error) => `<li>${error}</li>`)
                                      .join("")}
                                </ul>
                            </div>
                        `;
          }

          // If no processed data, show a message
          if (!resultsHtml) {
            resultsHtml =
              '<div class="alert alert-info">Tidak ada data detail yang tersedia.</div>';
          }

          $("#recentProcessed").html(resultsHtml);
        }
      },
    });
  }
});
