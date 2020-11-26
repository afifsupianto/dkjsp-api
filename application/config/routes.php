<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/** DKJPS API Mobile **/

//Auth
$route['api/auth/input-peserta'] = 'AuthApiController/daftarPeserta';
$route['api/auth/input-keluarga-binaan'] = 'AuthApiController/daftarKeluargaBinaan';
$route['api/auth/input-panitia'] = 'AuthApiController/daftarPanitia';
$route['api/auth/login'] = 'AuthApiController/prosesLogin';

//Master Data
$route['api/master/data-provinsi'] = 'MasterApiController/showDaftarProvinsi';
$route['api/master/data-provinsi-institusi'] = 'MasterApiController/showDaftarProvinsiInstitusi';
$route['api/master/form-kota/(:any)'] = 'MasterApiController/showDaftarKota';
$route['api/master/form-kecamatan/(:any)'] = 'MasterApiController/showDaftarKecamatan';
$route['api/master/form-desa/(:any)'] = 'MasterApiController/showDaftarDesa';

$route['api/home/data-home'] = 'HomeApiController/homeData';

//Transactional Data
$route['api/transactional/perbarui-referal'] = 'TransactionalApiController/perbaruiReferal';

//Peserta atau Kader
$route['api/peserta/home-peserta'] = 'PesertaApiController/homePeserta';
$route['api/peserta/cari-peserta'] = 'PesertaApiController/cariPeserta';
$route['api/peserta/dashboard'] = 'PesertaApiController/dashboardPelatihanPeserta';
$route['api/peserta/menu-materi'] = 'PesertaApiController/menuMateri';
$route['api/peserta/lihat-materi'] = 'PesertaApiController/lihatMateri';
$route['api/peserta/detail-materi'] = 'PesertaApiController/detailMateri';
$route['api/peserta/lihat-konten-subbab'] = 'PesertaApiController/lihatKontenSubbab';
$route['api/peserta/daftar-laporan'] = 'PesertaApiController/daftarLaporan';
$route['api/peserta/lihat-keluarga-peserta'] = 'PesertaApiController/lihatKeluargaPeserta';
$route['api/peserta/lihat-daftar-binaan'] = 'PesertaApiController/lihatDaftarBinaan';
$route['api/peserta/laporan-pembelajaran'] = 'PesertaApiController/laporanPembelajaran';

//Keluarga Binaan
$route['api/keluarga-binaan/data-keluarga-binaan'] = 'KeluargaBinaanApiController/getDataKeluargaBinaan';
$route['api/keluarga-binaan/gabung-kader'] = 'KeluargaBinaanApiController/gabungKader';
$route['api/keluarga-binaan/detail-keluarga'] = 'keluargaBinaanApiController/detailKeluarga';
$route['api/keluarga-binaan/detail-kader'] = 'keluargaBinaanApiController/detailKader';
$route['api/keluarga-binaan/updated-kader'] = 'KeluargaBinaanApiController/updatekaderByPeserta';
$route['api/keluarga-binaan/updated-by-peserta'] = 'KeluargaBinaanApiController/updateKeluargaBinaanByPeserta';
$route['api/keluarga-binaan/dashboard'] = 'KeluargaBinaanApiController/dashboard';
$route['api/keluarga-binaan/home'] = 'KeluargaBinaanApiController/home';
$route['api/keluarga-binaan/home-kader'] = 'KeluargaBinaanApiController/homeKader';
$route['api/keluarga-binaan/menu'] = 'KeluargaBinaanApiController/menu';
$route['api/keluarga-binaan/cari-relawan'] = 'KeluargaBinaanApiController/cariRelawan';
$route['api/keluarga-binaan/gabung-keluargabinaan'] = 'KeluargaBinaanApiController/gabungKeluargaBinaan';
$route['api/keluarga-binaan/keluar-keluargabinaan'] = 'KeluargaBinaanApiController/keluarKeluargaBinaan';

//Kelas
$route['api/kelas/input-kelas'] = 'KelasApiController/masukkanKelas';
$route['api/kelas/gabung-kelas'] = 'KelasApiController/gabungKelas';
$route['api/kelas/cari-kelas'] = 'KelasApiController/cariKelas';
$route['api/kelas/keluar-kelas'] = 'KelasApiController/keluarKelas';
$route['api/kelas/submit-presensi'] = 'KelasApiController/submitPresensi';
$route['api/kelas/submit-topik-selesai'] = 'KelasApiController/submitTopikSelesai';
$route['api/kelas/data-kode-referal'] = 'KelasApiController/dataKodeReferal';
$route['api/kelas/perbarui-referal'] = 'KelasApiController/perbaruiReferal';
$route['api/kelas/data-test'] = 'KelasApiController/dataTest';
$route['api/kelas/submit-test'] = 'KelasApiController/submitTest';

//Skrining
$route['api/skrining/data-skrining'] = 'SkriningApiController/dataSkrining';
$route['api/skrining/submit-skrining'] = 'SkriningApiController/submitSkrining';

//Aktivitas
$route['api/aktivitas/data-aktivitas'] = 'AktivitasApiController/dataAktivitas';
$route['api/aktivitas/submit-aktivitas'] = 'AktivitasApiController/submitAktivitas';
$route['api/aktivitas/aktivitas-peserta'] = 'AktivitasApiController/aktivitasPeserta';
$route['api/aktivitas/my-aktivitas'] = 'AktivitasApiController/myAktivitas';
$route['api/aktivitas/detail-laporan'] = 'AktivitasApiController/detailLaporan';


/** DKJPS API WEB **/

//Dashboard
$route['api/admin/dashboard'] = 'AdminApiController/getAdminDashboard';

//Master Data
$route['api/master/detail-pelatihan'] = 'MasterApiController/showDetailMasterPelatihan';
$route['api/master/input-pelatihan'] = 'MasterApiController/masukkanMasterPelatihan';
$route['api/master/edit-pelatihan'] = 'MasterApiController/editMasterPelatihan';
$route['api/master/hapus-pelatihan'] = 'MasterApiController/hapusMasterPelatihan';
$route['api/master/list-pelatihan'] = 'MasterApiController/showSemuaMasterPelatihan';

$route['api/master/detail-materi'] = 'MasterApiController/showDetailMasterMateri';
$route['api/master/input-materi'] = 'MasterApiController/masukkanMasterMateri';
$route['api/master/edit-materi'] = 'MasterApiController/editMasterMateri';
$route['api/master/hapus-materi'] = 'MasterApiController/hapusMasterMateri';
$route['api/master/list-materi'] = 'MasterApiController/showSemuaMasterMateri';

$route['api/user/list-admin'] = 'UserApiController/showSemuaUserAdmin';
$route['api/user/list-operator'] = 'UserApiController/showSemuaUserOperator';
$route['api/user/list-pemateri'] = 'UserApiController/showSemuaUserPemateri';
$route['api/user/list-panitia'] = 'UserApiController/showSemuaUserPanitia';
$route['api/user/list-peserta'] = 'UserApiController/showSemuaUserPeserta';
$route['api/user/list-kader'] = 'UserApiController/showSemuaUserKader';
$route['api/user/list-keluarga-binaan'] = 'UserApiController/showSemuaUserKeluargaBinaan';
$route['api/user/cek-data'] = 'UserApiController/cekData';
$route['api/user/ubah-profil'] = 'UserApiController/ubahProfil';
$route['api/user/ganti-password'] = 'UserApiController/gantiPassword';
