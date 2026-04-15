                <?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Guest\LandingPageController::index');
$routes->get('home', 'Guest\\Home::index');


$routes->get("login", "Guest\\HalamanLoginController::index");
$routes->post("login/auth", "Guest\\HalamanLoginController::auth");
$routes->get("logout", "Guest\\HalamanLoginController::logout");
$routes->get("forgot-password", "Guest\\ForgotPasswordController::index");
$routes->post("forgot-password/send", "Guest\\ForgotPasswordController::sendCode");
$routes->post("forgot-password/verify", "Guest\\ForgotPasswordController::verifyCode");
$routes->get("reset-password", "Guest\\ForgotPasswordController::resetForm");
$routes->post("reset-password/submit", "Guest\\ForgotPasswordController::updatePassword");

$routes->group('admin', ['namespace' => 'App\Controllers', 'filter' => 'authFilter'], function ($routes) {
    $routes->get('dashboard', 'Admin\BerandaAdminController::dashboard');
    $routes->get('kelola_user', 'Admin\KelolaUserController::kelolaUser');
    $routes->post('addUser', 'Admin\KelolaUserController::addUser');
    $routes->post('updateUser', 'Admin\KelolaUserController::updateUser');
    $routes->post('deleteUser', 'Admin\KelolaUserController::deleteUser');
    $routes->get('kelola_laporan', 'Admin\KelolaLaporanController::kelolaLaporan');
    $routes->post('kelola_laporan/getLaporanAjax', 'Admin\KelolaLaporanController::getLaporanAjax');
    $routes->get('kelola_laporan/view/(:num)', 'Admin\KelolaLaporanController::viewLaporan/$1');
    $routes->get('kelola_laporan/link/(:num)', 'Admin\KelolaLaporanController::viewLink/$1');
    $routes->post('kelola_laporan/approve', 'Admin\KelolaLaporanController::approveLaporan');
    $routes->post('kelola_laporan/reject', 'Admin\KelolaLaporanController::rejectLaporan');
    $routes->post('kelola_laporan/delete', 'Admin\KelolaLaporanController::deleteLaporan');
    $routes->get('rekap_user_satker', 'Admin\RekapPegawaiSatkerController::rekapUserSatker');
    $routes->match(['get', 'post'], 'exportUserSatkerPdf', 'Admin\RekapPegawaiSatkerController::exportUserSatkerPdf');
    $routes->match(['get', 'post'], 'exportUserSatkerWord', 'Admin\RekapPegawaiSatkerController::exportUserSatkerWord');
    $routes->get('input_tanda_tangan', 'Admin\InputTandaTanganAdminController::inputTandaTangan');
    $routes->post('addTandaTangan', 'Admin\InputTandaTanganAdminController::addTandaTangan');
    $routes->get('editTandaTangan/(:num)', 'Admin\InputTandaTanganAdminController::editTandaTangan/$1');
    $routes->post('updateTandaTangan', 'Admin\InputTandaTanganAdminController::updateTandaTangan');
    $routes->get('deleteTandaTangan/(:num)', 'Admin\InputTandaTanganAdminController::deleteTandaTangan/$1');
    $routes->get('rekap_kedisiplinan', 'Admin\StatusDisiplinSatkerController::rekapKedisiplinan');
    $routes->match(['get', 'post'], 'exportRekapKedisiplinanPdf', 'AdminController::exportRekapKedisiplinanPdf');
    $routes->get('notifikasi', 'Admin\NotifikasiAdminController::notifikasi');
    $routes->post('notifikasi/getNotifikasiAjax', 'Admin\NotifikasiAdminController::getNotifikasiAjax');
    $routes->post('notifikasi/delete-all', 'Admin\NotifikasiAdminController::deleteAllNotifications');
    $routes->get('profil', 'Admin\\ProfilAdminController::profil');
    $routes->post('updateProfil', 'Admin\\ProfilAdminController::updateProfil');
    $routes->post('updateFotoProfil', 'Admin\\ProfilAdminController::updateFotoProfil');
    $routes->post('profil/update', 'Admin\\ProfilAdminController::updateProfil');
    $routes->post('profil/update_password', 'Admin\\ProfilAdminController::updatePassword');
    $routes->post('profil/update_foto', 'Admin\\ProfilAdminController::updateFotoProfil');
    $routes->get('input_pegawai', 'Admin\\KelolaPegawaiController::inputPegawai');
    $routes->post('getPegawaiAjax', 'Admin\\KelolaPegawaiController::getPegawaiAjax');
    $routes->get('kelola_satker', 'Admin\\KelolaSatkerController::kelolaSatker');
    $routes->post('simpanSatker', 'Admin\\KelolaSatkerController::simpanSatker');
    $routes->get('hapusSatker/(:num)', 'Admin\\KelolaSatkerController::hapusSatker/$1');
    $routes->get('mutasi_pegawai/(:num)', 'Admin\\MutasiPegawaiController::mutasiPegawai/$1');
    $routes->get('mutasiPegawai/(:num)', 'Admin\\MutasiPegawaiController::mutasiPegawai/$1'); // Tambahan untuk kompatibilitas
    $routes->post('prosesMutasiPegawai', 'Admin\\MutasiPegawaiController::prosesMutasiPegawai');
    $routes->post('input_pegawai/add', 'Admin\\KelolaPegawaiController::addPegawai');
    $routes->post('input_pegawai/update', 'Admin\\KelolaPegawaiController::updatePegawai');
    $routes->post('input_pegawai/update/(:num)', 'Admin\\KelolaPegawaiController::updatePegawai/$1');
    $routes->post('import_pegawai', 'Admin\\KelolaPegawaiController::importPegawai');
    $routes->get('get_import_progress', 'Admin\\KelolaPegawaiController::getImportProgress');
    $routes->match(['get', 'post'], 'getFile/(:any)', 'Admin\KelolaLaporanController::getFile/$1');
    $routes->get('toggleStatusPegawai/(:num)', 'Admin\\KelolaPegawaiController::toggleStatusPegawai/$1');
    $routes->get('input_pegawai/delete/(:num)', 'Admin\\KelolaPegawaiController::deletePegawai/$1');
    $routes->post('updateMutasiPegawai', 'Admin\\MutasiPegawaiController::updateMutasiPegawai');
    $routes->get('deleteMutasiPegawai/(:num)', 'Admin\\MutasiPegawaiController::deleteMutasiPegawai/$1');
    $routes->get('arsip_laporan', 'Admin\ArsipLaporanController::arsipLaporan');
    $routes->get('arsip_laporan/getFile/(:any)', 'Admin\ArsipLaporanController::getFile/$1');
    $routes->match(['get', 'post'], 'arsip_laporan/download_zip', 'Admin\ArsipLaporanController::downloadArsipZip');
    $routes->get('arsip_laporan/serve_zip/(:any)', 'Admin\ArsipLaporanController::serveZip/$1');
    $routes->post('arsip_laporan/delete', 'Admin\ArsipLaporanController::deleteArsipLaporan');
    $routes->post('getArsipLaporanAjax', 'Admin\ArsipLaporanController::getArsipLaporanAjax');
    $routes->post('searchPegawaiAjax', 'Admin\ArsipLaporanController::searchPegawaiAjax');
    $routes->post('getHukumanDisiplinAjax', 'Admin\KelolaHukumanDisiplinController::getHukumanDisiplinAjax');
    $routes->get('pengaturan', 'Admin\PengaturanAdminController::pengaturan');
    $routes->post('pengaturan/add_origin', 'Admin\PengaturanAdminController::addOrigin');
    $routes->get('pengaturan/delete_origin/(:num)', 'Admin\PengaturanAdminController::deleteOrigin/$1');
    $routes->get('pengaturan/toggle_origin/(:num)', 'Admin\PengaturanAdminController::toggleOrigin/$1');
    $routes->get('api_keys', 'Admin\PengaturanAdminController::apiKeyList'); // Redirect ke pengaturan
    $routes->post('api_keys/add', 'Admin\PengaturanAdminController::apiKeyAdd');
    $routes->get('api_keys/delete/(:num)', 'Admin\PengaturanAdminController::apiKeyDelete/$1');
    $routes->get('api_keys/toggle/(:num)', 'Admin\PengaturanAdminController::apiKeyToggleActive/$1');
    $routes->post('addTandaTanganGambar', 'Admin\InputTandaTanganAdminController::addTandaTanganGambar');
    $routes->post('updateTandaTanganGambar', 'Admin\InputTandaTanganAdminController::updateTandaTanganGambar');
    $routes->get('deleteTandaTanganGambar/(:num)', 'Admin\InputTandaTanganAdminController::deleteTandaTanganGambar/$1');
    $routes->get('setAktifTandaTangan/(:any)/(:num)', 'Admin\InputTandaTanganAdminController::setAktifTandaTangan/$1/$2');
    $routes->get('input_tanda_tangan/searchPegawaiAjax', 'Admin\InputTandaTanganAdminController::searchPegawaiAjax');
    $routes->get('input_tanda_tangan/getFile/(:any)', 'Admin\InputTandaTanganAdminController::getFile/$1');
    // ===== ROUTES KELOLA HUKUMAN DISIPLIN =====
    $routes->get('kelola_hukuman_disiplin', 'Admin\KelolaHukumanDisiplinController::kelolaHukumanDisiplin');
    $routes->post('addHukumanDisiplin', 'Admin\KelolaHukumanDisiplinController::addHukumanDisiplin');
    $routes->get('editHukumanDisiplin/(:num)', 'Admin\KelolaHukumanDisiplinController::editHukumanDisiplin/$1');
    $routes->post('updateHukumanDisiplin', 'Admin\KelolaHukumanDisiplinController::updateHukumanDisiplin');
    $routes->get('deleteHukumanDisiplin/(:num)', 'Admin\KelolaHukumanDisiplinController::deleteHukumanDisiplin/$1');
    $routes->match(['get', 'post'], 'exportHukumanDisiplinPdf', 'Admin\KelolaHukumanDisiplinController::exportHukumanDisiplinPdf');
    $routes->match(['get', 'post'], 'exportHukumanDisiplinWord', 'Admin\KelolaHukumanDisiplinController::exportHukumanDisiplinWord');
    $routes->post('getHukumanDisiplinDetailAjax/(:num)', 'Admin\KelolaHukumanDisiplinController::getHukumanDisiplinDetailAjax/$1');
    $routes->get('approveHukumanDisiplin/(:num)', 'Admin\KelolaHukumanDisiplinController::approveHukumanDisiplin/$1');
    $routes->get('rejectHukumanDisiplin/(:num)', 'Admin\KelolaHukumanDisiplinController::rejectHukumanDisiplin/$1');
    $routes->post('getHukumanDisiplinAjaxDataTables', 'Admin\KelolaHukumanDisiplinController::getHukumanDisiplinAjaxDataTables');
    $routes->post('getPengajuanHukumanDisiplinAjax', 'Admin\KelolaHukumanDisiplinController::getPengajuanHukumanDisiplinAjax');
    $routes->match(['get', 'post'], 'kelola_hukuman_disiplin/getFile/(:any)', 'Admin\KelolaHukumanDisiplinController::getFile/$1');
    // ===== ROUTES KELOLA DISIPLIN =====
    $routes->get('kelola_disiplin', 'Admin\KelolaDisiplinController::index');
    $routes->post('kelola_disiplin/ajax', 'Admin\KelolaDisiplinController::getDataAjax');
    // ===== ROUTES TRACKING KEDISIPLINAN =====
    $routes->get('tracking', 'Admin\TrackingKedisiplinanController::index');
    $routes->post('tracking/searchPegawaiAjax', 'Admin\TrackingKedisiplinanController::searchPegawaiAjax');
    $routes->post('tracking/getTrackRecordAjax', 'Admin\TrackingKedisiplinanController::getTrackRecordAjax');
    $routes->post('tracking/getTahunTersediaAjax', 'Admin\TrackingKedisiplinanController::getTahunTersediaAjax');
    $routes->match(['get', 'post'], 'tracking/exportPdfAjax', 'Admin\TrackingKedisiplinanController::exportPdfAjax');
});

$routes->group("user", ["filter" => "authFilter"], function ($routes) {
    $routes->get("beranda_user", "User\\BerandaUserController::dashboard");
    $routes->get("daftar_pegawai", "User\\DaftarPegawaiController::inputPegawai");
    $routes->post("import_pegawai", "User\\DaftarPegawaiController::importPegawai");
    $routes->get('mutasi_pegawai/(:num)', 'User\\DaftarPegawaiController::mutasiPegawai/$1');
    $routes->post('prosesMutasiPegawai', 'User\\DaftarPegawaiController::prosesMutasiPegawai');
    $routes->get("kelola_disiplin", "User\\KelolaDisiplinController::inputKedisiplinan");
    $routes->post("kelola_disiplin/add", "User\\KelolaDisiplinController::addKedisiplinan");
    $routes->post("kelola_disiplin/update", "User\\KelolaDisiplinController::updateKedisiplinan");
    $routes->get("kelola_disiplin/delete/(:num)", "User\\KelolaDisiplinController::deleteKedisiplinan/$1");
    $routes->post("hapus_kedisiplinan_periode", "User\\KelolaDisiplinController::hapusKedisiplinanPeriode");
    $routes->get("inputtandatanganuser", "User\\InputTandaTanganUserController::inputTandaTangan");
    $routes->post("inputtandatanganuser/add", "User\\InputTandaTanganUserController::addTandaTangan");
    $routes->post("inputtandatanganuser/update", "User\\InputTandaTanganUserController::updateTandaTangan");
    $routes->get("inputtandatanganuser/delete/(:num)", "User\\InputTandaTanganUserController::deleteTandaTangan/$1");
    $routes->get("inputtandatanganuser/set_aktif/(:any)/(:num)", "User\\InputTandaTanganUserController::setAktifTandaTangan/$1/$2");
    $routes->post("inputtandatanganuser/add_gambar", "User\\InputTandaTanganUserController::addTandaTanganGambar");
    $routes->post("inputtandatanganuser/update_gambar", "User\\InputTandaTanganUserController::updateTandaTanganGambar");
    $routes->get("inputtandatanganuser/delete_gambar/(:num)", "User\\InputTandaTanganUserController::deleteTandaTanganGambar/$1");
    $routes->get("inputtandatanganuser/edit_gambar/(:num)", "User\\InputTandaTanganUserController::editTandaTanganGambar/$1");
    $routes->get("inputtandatanganuser/getFile/(:any)", "User\\InputTandaTanganUserController::getFile/$1");
    $routes->get("rekaplaporandisiplin", "User\\RekapLaporanDisiplinController::rekapLaporan");
    $routes->match(['get', 'post'], "rekaplaporandisiplin/export_pdf", "User\\RekapLaporanDisiplinController::exportPdf");
    $routes->match(['get', 'post'], "rekaplaporandisiplin/export_word", "User\\RekapLaporanDisiplinController::exportWord");
    $routes->get("statusdisiplinpegawai", "User\\StatusDisiplinPegawaiController::rekapBulanan");
    $routes->post("getRekapBulananAjax", "User\\StatusDisiplinPegawaiController::getRekapBulananAjax");
    $routes->get("kirimlaporan", "User\\KirimLaporanController::uploadFile");
    $routes->post("kirimlaporan/getLaporanAjax", "User\\KirimLaporanController::getLaporanAjax");
    $routes->post("kirimlaporan/add", "User\\KirimLaporanController::addFile");
    $routes->post("kirimlaporan/delete", "User\\KirimLaporanController::deleteFile");
    $routes->get("getFile/(:any)", "User\\KirimLaporanController::getFile/$1");
    $routes->post("kirimlaporan/hide", "User\\KirimLaporanController::hideLaporanFromUser");
    $routes->get("notifikasiuser", "User\NotifikasiUserController::notifikasi");
    $routes->post("notifikasiuser/getNotifikasiAjax", "User\NotifikasiUserController::getNotifikasiAjax");
    $routes->post("notifikasiuser/mark-read", "User\NotifikasiUserController::markNotificationAsRead");
    $routes->post("notifikasiuser/delete-all", "User\NotifikasiUserController::deleteAllNotifications");
    $routes->get("profil_user", "User\\ProfilUserController::profil");
    $routes->post("profil_user/update", "User\\ProfilUserController::updateProfil");
    $routes->post("profil_user/update_foto", "User\\ProfilUserController::updateFotoProfil");
    $routes->post("profil_user/update_password", "User\\ProfilUserController::updatePassword");
    $routes->post("input_pegawai/update", "UserController::updatePegawai");
    $routes->get("input_pegawai/delete/(:num)", "UserController::deletePegawai/$1");
    $routes->post("input_tanda_tangan/add", "UserController::addTandaTangan");
    $routes->post("input_tanda_tangan/update", "UserController::updateTandaTangan");
    $routes->get("input_tanda_tangan/delete/(:num)", "UserController::deleteTandaTangan/$1");
    $routes->post("import_pegawai", "UserController::importPegawai");
    $routes->get("inputdisiplin", "User\\InputDisiplinController::inputKedisiplinanTabel");
    $routes->post("inputdisiplin/save", "User\\InputDisiplinController::saveKedisiplinanTabel");
    $routes->post('inputdisiplin/save_semua', 'User\\InputDisiplinController::saveKedisiplinanTabelSemua');
    $routes->post('inputdisiplin/save_batch', 'User\\KelolaDisiplinController::saveKedisiplinanBatch');
    $routes->post("getPegawaiAjax", "User\\DaftarPegawaiController::getPegawaiAjax");
    $routes->get('getRekapBulananAjax', 'UserController::getRekapBulananAjax');
    $routes->post('hapus_kedisiplinan_periode', 'UserController::hapusKedisiplinanPeriode');
    $routes->post('getPegawaiKedisiplinanAjax', 'User\\InputDisiplinController::getPegawaiKedisiplinanAjax');
    $routes->get("input_tanda_tangan/edit_gambar/(:num)", "User\InputTandaTanganUserController::editTandaTanganGambar/$1");
    $routes->get('kelola_hukuman_disiplin', 'User\KelolaHukumanDisiplinController::index');
    $routes->post('kelola_hukuman_disiplin/addHukumanDisiplin', 'User\KelolaHukumanDisiplinController::addHukumanDisiplin');
    $routes->post('kelola_hukuman_disiplin/delete/(:num)', 'User\KelolaHukumanDisiplinController::delete/$1');
    $routes->match(['get', 'post'], 'kelola_hukuman_disiplin/exportPdf', 'User\KelolaHukumanDisiplinController::exportHukumanDisiplinPdfUser');
    $routes->match(['get', 'post'], 'kelola_hukuman_disiplin/exportWord', 'User\KelolaHukumanDisiplinController::exportHukumanDisiplinWordUser');
    $routes->post('kelola_hukuman_disiplin/searchPegawaiAjax', 'User\KelolaHukumanDisiplinController::searchPegawaiAjax');
    $routes->post('kelola_hukuman_disiplin/getHukumanDisiplinAjaxDataTables', 'User\KelolaHukumanDisiplinController::getHukumanDisiplinAjaxDataTables');
    $routes->match(['get', 'post'], 'kelola_hukuman_disiplin/getFile/(:any)', 'User\KelolaHukumanDisiplinController::getFile/$1');
});

$routes->get('user/getFile/(:any)', 'UserController::getFile/$1');
$routes->get('user/searchPegawaiAjax', 'User\\DaftarPegawaiController::searchPegawaiAjax');

// API Routes untuk Laporan Kedisiplinan
$routes->group('api', ['namespace' => 'App\\Controllers\\Api'], function ($routes) {
    $routes->options('(:any)', static function () {
        return service('response')->setStatusCode(204);
    }, ['filter' => 'cors']);
    
    $routes->get('laporan', 'ApiController::getLaporan', ['filter' => ['cors', 'apiKey']]);
    $routes->get('laporan/(:num)', 'ApiController::getLaporanById/$1', ['filter' => ['cors', 'apiKey']]);
    $routes->get('laporan/file/(:num)', 'ApiController::getLaporanFile/$1', ['filter' => ['cors', 'apiKey']]);
    $routes->get('laporan/arsip', 'ApiController::getArsipLaporan', ['filter' => ['cors', 'apiKey']]);
    
    // API Pegawai & Tracking
    $routes->get('pegawai', 'ApiController::getPegawai', ['filter' => ['cors', 'apiKey']]);
    $routes->get('pegawai/(:segment)', 'ApiController::getPegawaiTracking/$1', ['filter' => ['cors', 'apiKey']]);
    // Alias untuk penulisan "pegwai" sesuai kebutuhan dokumen sebelumnya
    $routes->get('pegwai', 'ApiController::getPegawai', ['filter' => ['cors', 'apiKey']]);
    $routes->get('pegwai/(:segment)', 'ApiController::getPegawaiTracking/$1', ['filter' => ['cors', 'apiKey']]);
    
    // API Routes untuk Laporan berdasarkan Kategori
    $routes->get('laporan/disiplin', 'ApiController::getLaporanByKategori/disiplin', ['filter' => ['cors', 'apiKey']]);
    $routes->get('laporan/apel', 'ApiController::getLaporanByKategori/apel', ['filter' => ['cors', 'apiKey']]);
    $routes->get('laporan/(:segment)/file/(:num)', 'ApiController::getLaporanFileByKategori/$1/$2', ['filter' => ['cors', 'apiKey']]);
    $routes->get('laporan/(:segment)/link/(:num)', 'ApiController::getLaporanLinkByKategori/$1/$2', ['filter' => ['cors', 'apiKey']]);
    
    // API Route untuk link tanpa kategori
    $routes->get('laporan/link/(:num)', 'ApiController::getLaporanLink/$1', ['filter' => ['cors', 'apiKey']]);
});

// API Routes untuk Notifikasi Realtime
$routes->group('api', ['namespace' => 'App\\Controllers\\Api', 'filter' => 'authFilter'], function ($routes) {
    $routes->get('notifications/count', 'ApiController::getNotificationCount');
    $routes->get('notifications/latest', 'ApiController::getLatestNotifications');
});
