<?php
?>
<div class="petunjuk-outline" style="margin-bottom: 1rem;">
    <div class="petunjuk-header d-flex justify-content-between align-items-center" style="cursor:pointer; user-select:none; padding-bottom:0;">
        <span class="fw-semibold"><i class="bi bi-info-circle me-1 fw-semibold"></i> Petunjuk</span>
        <i class="bi bi-chevron-down chevron-petunjuk" style="transition: transform 0.2s;"></i>
    </div>
    <div class="isi-petunjuk" style="display:none; margin-top:10px;">
        <?= $isiPetunjuk ?? '' ?>
    </div>
</div>
<script>
(function() {
    if (window._petunjukToggleInit) return;
    window._petunjukToggleInit = true;
    $(document).on('click', '.petunjuk-header', function() {
        var card = $(this).closest('.petunjuk-outline');
        var isi = card.find('.isi-petunjuk');
        var chevron = card.find('.chevron-petunjuk');
        if (isi.is(':visible')) {
            isi.stop(true, true).slideUp(180);
            chevron.css('transform', 'rotate(0deg)');
        } else {
            isi.stop(true, true).slideDown(180);
            chevron.css('transform', 'rotate(180deg)');
        }
    });
})();
</script> 