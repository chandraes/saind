@extends('layouts.doc')
@section('title', 'Kontrak')
@section('content')
<div class="container-fluid" style="margin-left: 40%">
    <br><br><br>
    <strong>
        Hari {{$data->hari}}, Tanggal {{$data->tanggal_string}}
        <br><br><br><br><br><br><br>
        PT. SUMATERA ALAM INDOPRIMA <br>
        (<i>Perusahaan</i>)
        <br><br><br><br><br>
        DAN
        <br><br><br><br><br>
        {{$data->vendor->tipe == 'perusahaan' ? $data->vendor->perusahaan : $data->vendor->nama}} <br>
        (<i>{{ucfirst($data->vendor->tipe)}}</i>)
        <br><br><br><br><br><br><br>
        PERJANJIAN KONTRAK JASA ANGKUTAN <br>
        BATUBARA LOKASI
        @foreach ($customer as $c)
        {{$c->singkatan}}
        @if (!$loop->last)
        &
        @endif
        @endforeach
        <br><br><br><br><br>
        NO. {{$data->nomor}}/PK-JAB/SAIND-{{$data->nama_singkatan}}/{{$data->tahun}}
    </strong>
</div>
<div class="page-break"></div>
<div class="row text-pdf">
    <center>
        <h2>
            PERJANJIAN KONTRAK JASA<br>ANGKUTAN BATUBARA
        </h2>
    </center>
</div>
<div class="row text-pdf">
    <center>
        Nomor : {{$data->nomor}}/PK-JAB/SAIND-{{$data->nama_singkatan}}/{{$data->tahun}}
    </center>
</div>
<br>
<div class="row text-pdf">
    <p class="text-pdf">
        Pada hari ini, {{$data->hari}} tanggal {{$data->hari_angka}} bulan {{$data->bulan}} tahun {{$data->tahun}},
        telah disepakati sebuah Perjanjian Kontrak Jasa Angkutan Batubara oleh Para Pihak diantaranya, sebagai berikut :
        <br><br>
        <strong>PT. SUMATERA ALAM INDOPRIMA</strong> suatu Perseroan Terbatas yang didirikan dan tunduk pada Hukum Negara
        Republik Indonesia, berkedudukan di Jl. Enim No 108 Komp Remiling Azzuri RT/RW, 001/001 Kel. Tungkal, Kec. Muara Enim,
        Kab. Muara Enim, Sumatera Selatan, (31311), Indonesia dalam hal ini diwakili oleh <strong>MEDY ANDIKA, ST</strong> bertindak
        dalam kedudukannya selaku <strong>DIREKTUR UTAMA</strong> dari dan atas nama Perseroan, untuk selanjutnya Bertindak untuk dan atas nama
        pribadi atau Perusahaan dan selanjutnya juga disebut sebagai <strong>PIHAK PERTAMA</strong>
        <br><br>
        <strong>Dan</strong>
        <br><br>
            @if ($data->vendor->tipe == 'perusahaan')
            <strong>{{strtoupper($data->vendor->perusahaan)}}</strong> suatu {{ucfirst($data->vendor->tipe)}} dengan NPWP {{$data->vendor->npwp}}
                yang selanjutnya diwakilkan oleh <strong>{{$data->vendor->nama}}</strong> selaku {{$data->vendor->jabatan}}
            @else
                <strong>{{$data->vendor->nama}}</strong> suatu {{ucfirst($data->vendor->tipe)}} dengan NIK {{$data->vendor->npwp}}
            @endif
             dan tunduk pada
        Hukum Negara Republik Indonesia, berkedudukan di {{$data->vendor->alamat}} selanjutnya bertindak untuk dan atas nama {{$data->vendor->tipe == 'perorangan' ? 'pribadi' : 'perusahaan'}}
        dan selanjutnya juga disebut sebagai <strong>PIHAK KEDUA</strong>
        <br><br>
        (PIHAK PERTAMA dan PIHAK KEDUA untuk selanjutnya secara bersama-sama disebut sebagai <strong>"PARA PIHAK"</strong> dan
        secara individu sebagai <strong>"PIHAK"</strong>)
        <br><br>
        PARA PIHAK DENGAN INI MENERANGKAN TERLEBIH DAHULU BAHWA :
        <br>
    </p>
</div>
<div class="container-fluid">
    <ol type="a" class="text-pdf">
        <li>Dengan tunduk kepada ketentuan - ketentuan dan persyaratan yang terdapat dalam Perjanjian,
            <strong>PIHAK PERTAMA</strong> bermaksud menggunakan jasa <strong>PIHAK KEDUA</strong>
            untuk melakukan pengangkutan Batubara.
        </li>
        <li>
            Batubara yang akan diangkut dari area muat yang terletak di daerah Kabupaten Muara Enim
            dan Kabupaten Lahat, Sumatera Selatan (Selanjutnya disebut sebagai "Area tempat Muat dan bongkar") dengan
            Jarak Bervariasi Yang telah ditentukan ataupun ditetapkan kemudian oleh <strong>PIHAK PERTAMA.</strong>
        </li>
        <li>
            <strong>PIHAK KEDUA</strong> setuju untuk mengangkut Batubara sekaligus melaksanakan Jasa Angkutan
            Batubara terkait sebagaimana tersebut diatas dan dalam Perjanjian ini untuk kepentingan <strong>PIHAK PERTAMA</strong>,
            dengan tunduk pada ketentuan-ketentuan dan persyaratan-persyaratan dalam perjanjian ini.
        </li>
    </ol>
</div>
<br>
<div class="container-fluid">
    <p class="text-pdf">
        Oleh karena itu, <strong>PARA PIHAK</strong> dengan ini menyetujui hal-hal sebagai berikut :
    </p>
</div>
<div class="page-break"></div>
<div class="container-fluid text-center">
    <h3>PASAL 1<br>DEFINISI - DEFINISI</h3>
</div>
<div class="container-fluid mt-3">
    <p class="text-pdf">
        Kecuali ditunjukkan sebaliknya, seluruh rujukan terhadap Pasal berarti Pasal dari Perjanjian ini,
        kecuali disebutkan sebaliknya, seluruh rujukan terhadap perjanjian-perjanjian atau perjanjian-perjanjian
        tersebut berikut seluruh lampiran-lampirannya, dan perubahannya dari waktu ke waktu.
    </p>
</div>
<br>
<div class="container-fluid text-center">
    <h3>PASAL 2<br>POKOK DASAR IKATAN PERJANJIAN</h3>
</div>
<div class="container-fluid mt-3">
    <p class="text-pdf">
        <strong>PIHAK PERTAMA</strong> dengan ini setuju memberikan pekerjaan Jasa Angkutan Batubara kepada
        <strong>PIHAK KEDUA</strong> dan <strong>PIHAK KEDUA</strong> setuju dari <strong>PIHAK PERTAMA.</strong>
    </p>
</div>
<br>
<div class="container-fluid text-center">
    <h3>
        PASAL 3<br>
        RUANG LINGKUP KERJA
    </h3>
</div>
<div class="container-fluid mt-3">
    <ol type="1" class="text-pdf">
        <li>
            Jasa angkutan atau pekerjaan yang diberikan oleh <strong>PIHAK PERTAMA</strong> ke <strong>PIHAK KEDUA</strong>,
            adalah untuk mengangkut Batubara milik <strong>PIHAK PERTAMA</strong> atau milik perusahaan yang
            berafiliasi dengan <strong>PIHAK PERTAMA</strong> untuk kegiatan pengangkutan Batubara di daerah
            Kabupaten Muara Enim dan Kabupaten Lahat, Sumatera Selatan (Selanjutnya disebut sebagai
            "Area tempat Muat dan Bongkar") dengan jarak bervariasi yang telah ditentukan ataupun ditetapkan
            kemudian oleh <strong>PIHAK PERTAMA</strong>.
        </li>
        <li>
            Wilayah operasi Pekerjaan berada dalam pengawasan langsung/tidak langsung oleh <strong>PIHAK PERTAMA</strong>
            dan tidak melanggar legalitas/hukum dalam bentuk apapun.
        </li>
    </ol>
</div>
<br>
<div class="container-fluid text-center">
    <h3>
        PASAL 4<br>
        JUMLAH VOLUME
    </h3>
</div>
<div class="container-fluid">
    <ol class="text-pdf" type="1">
        <li>
            <strong>PIHAK KEDUA</strong> berkewajiban untuk setiap saat siap dan sanggup mengangkut batubara dari
            pemuatan yang telah disetujui di Area muat ke Tempat Bongkar yang sudah ditentukan oleh
            <strong>PIHAK PERTAMA</strong> dengan jumlah volume yang tidak terduga, sebagaimana ditentukan oleh
            <strong>PIHAK PERTAMA</strong>.
        </li>
        <li>
            <strong>PIHAK PERTAMA</strong> akan menyiapkan Batubara yang kiranya untuk dikerjakan oleh <strong>PIHAK KEDUA</strong>
            yang dimana <strong>PIHAK KEDUA</strong> berkewajiban untuk mengangkut Batubara dari pemuatan yang telah ditentukan
            di Area muat ke Tempat Bongkar yang sudah ditentukan oleh <strong>PIHAK PERTAMA</strong> atau yang tertera di pasal 3 ayat 1.
        </li>
    </ol>
</div>
<br>
<div class="container-fluid text-center">
    <h3>
        PASAL 5<br>
        OBJEK PERJANJIAN
    </h3>
</div>
<div class="container-fluid">
    <p class="text-pdf">
        Dalam perjanjian ini <strong>PIHAK KEDUA</strong> menyediakan Unit Dump Truck dengan minimal index 26m<sup>3</sup>
        dan dengan tahun minimal 2016 dalam kondisi baik, layak jalan, dan dilengkapi perlatan yang cukup,
        serta surat-surat kendaraan lengkap untuk pekerjaan Jasa Angkutan Batubara yang diberikan oleh <strong>PIHAK PERTAMA</strong>.
    </p>
</div>
<div class="page-break"></div>
<div class="container-fluid text-center">
    <h3>
        PASAL 6<br>
        AREA DAN JAM KERJA
    </h3>
</div>
<div class="container-fluid">
    @foreach ($customer as $c)
    {{$c->nama}} ({{$c->singkatan}}) <br>
    <table class="table table-pdf">
        <thead>
            <tr>
                <th class="text-pdf table-pdf text-center align-middle" rowspan="2">No</th>
                <th class="text-pdf table-pdf text-center" colspan="2">Rute</th>
                <th class="text-pdf table-pdf text-center align-middle" rowspan="2">Jarak<br>(Km)</th>
                <th class="text-pdf table-pdf text-center align-middle" rowspan="2">Harga<br>(Km/Ton)</th>
                <th class="text-pdf table-pdf text-center align-middle" rowspan="2">Jadwal Kerja</th>
            </tr>
            <tr>
                <th class="text-pdf table-pdf text-center">Muatan</th>
                <th class="text-pdf table-pdf text-center">Bongkar</th>
            </tr>
        </thead>
        <tbody>
            @if($c->singkatan == 'MIP')
            <tr>
                <td class="text-pdf table-pdf text-center">1</td>
                <td class="text-pdf table-pdf text-center">IS 107</td>
                <td class="text-pdf table-pdf text-center">Port</td>
                <td class="text-pdf table-pdf text-center">107</td>
                <td class="text-pdf table-pdf text-center">Menyesuaikan</td>
                <td class="text-pdf table-pdf text-center">24 Jam</td>
            </tr>
            <tr>
                <td class="text-pdf table-pdf text-center">2</td>
                <td class="text-pdf table-pdf text-center">IS 107</td>
                <td class="text-pdf table-pdf text-center">IS 36</td>
                <td class="text-pdf table-pdf text-center">71</td>
                <td class="text-pdf table-pdf text-center">Menyesuaikan</td>
                <td class="text-pdf table-pdf text-center">24 Jam</td>
            </tr>
            <tr>
                <td class="text-pdf table-pdf text-center">3</td>
                <td class="text-pdf table-pdf text-center">MIP</td>
                <td class="text-pdf table-pdf text-center">IS 36</td>
                <td class="text-pdf table-pdf text-center">97,85</td>
                <td class="text-pdf table-pdf text-center">Menyesuaikan</td>
                <td class="text-pdf table-pdf text-center">Malam Hari</td>
            </tr>
            <tr>
                <td class="text-pdf table-pdf text-center">4</td>
                <td class="text-pdf table-pdf text-center">MIP</td>
                <td class="text-pdf table-pdf text-center">Port</td>
                <td class="text-pdf table-pdf text-center">133,85</td>
                <td class="text-pdf table-pdf text-center">Menyesuaikan</td>
                <td class="text-pdf table-pdf text-center">Malam Hari</td>
            </tr>
            <tr>
                <td class="text-pdf table-pdf text-center">5</td>
                <td class="text-pdf table-pdf text-center">MIP</td>
                <td class="text-pdf table-pdf text-center">IS 107</td>
                <td class="text-pdf table-pdf text-center">26,85</td>
                <td class="text-pdf table-pdf text-center">Menyesuaikan</td>
                <td class="text-pdf table-pdf text-center">Malam Hari</td>
            </tr>
            @elseif($c->singkatan == 'BP')<tr>
                <td class="text-pdf table-pdf text-center">1</td>
                <td class="text-pdf table-pdf text-center">BP</td>
                <td class="text-pdf table-pdf text-center">IS 36</td>
                <td class="text-pdf table-pdf text-center">78,6</td>
                <td class="text-pdf table-pdf text-center">Menyesuaikan</td>
                <td class="text-pdf table-pdf text-center">24 Jam</td>
            </tr>
            <tr>
                <td class="text-pdf table-pdf text-center">2</td>
                <td class="text-pdf table-pdf text-center">BP</td>
                <td class="text-pdf table-pdf text-center">Port</td>
                <td class="text-pdf table-pdf text-center">114</td>
                <td class="text-pdf table-pdf text-center">Menyesuaikan</td>
                <td class="text-pdf table-pdf text-center">24 Jam</td>
            </tr>
            <tr>
                <td class="text-pdf table-pdf text-center">3</td>
                <td class="text-pdf table-pdf text-center">IS 107</td>
                <td class="text-pdf table-pdf text-center">IS 36</td>
                <td class="text-pdf table-pdf text-center">71</td>
                <td class="text-pdf table-pdf text-center">Menyesuaikan</td>
                <td class="text-pdf table-pdf text-center">24 Jam</td>
            </tr>
            <tr>
                <td class="text-pdf table-pdf text-center">4</td>
                <td class="text-pdf table-pdf text-center">IS 107</td>
                <td class="text-pdf table-pdf text-center">Port</td>
                <td class="text-pdf table-pdf text-center">107</td>
                <td class="text-pdf table-pdf text-center">Menyesuaikan</td>
                <td class="text-pdf table-pdf text-center">24 Jam</td>
            </tr>
            @endif
        </tbody>
    </table>
    @endforeach
</div>
<br>
<div class="container-fluid text-center">
    <h3>
        PASAL 7<br>
        MEKANISME PEMBAYARAN
    </h3>
</div>
<div class="container-fluid">
    <ol class="text-pdf" type="1">
        <li>
            Sistem Pembayaran
            <br>
            <table class="table table-pdf">
                <thead>
                    <tr>
                        <th class="text-pdf table-pdf text-center">No</th>
                        <th class="text-pdf table-pdf text-center">Uraian</th>
                        @foreach ($customer as $c)
                        <th class="text-pdf table-pdf text-center">{{ $c->singkatan }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-pdf table-pdf text-center">1</td>
                        <td class="text-pdf table-pdf"><strong>OPNAME</strong></td>
                        @foreach ($customer as $c)
                        <td class="text-pdf table-pdf text-center"></td>
                        @endforeach
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-pdf table-pdf">* Harga :</td>
                        @foreach ($customer as $c)
                        <td class="text-pdf table-pdf">Rp. {{number_format($c->harga_opname, 0, ',', '.')}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-pdf table-pdf">* Jangka Waktu/Tempo :</td>
                        @foreach ($customer as $c)
                        <td class="text-pdf table-pdf">1 (Satu) minggu</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-pdf table-pdf">* Pembayaran :</td>
                        @foreach ($customer as $c)
                        <td class="text-pdf table-pdf">Setiap hari senin</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-pdf table-pdf">* Sistem uang jalan :</td>
                        @foreach ($customer as $c)
                        <td class="text-pdf table-pdf">Dikasih uang jalan</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-pdf table-pdf">* Nominal :</td>
                        @foreach ($customer as $c)
                        <td class="text-pdf table-pdf">Disesuaikan</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="text-center table-pdf text-pdf">2</td>
                        <td class="table-pdf text-pdf">TITIPAN</td>
                        @foreach ($customer as $c)
                        <td class="text-pdf table-pdf text-center"></td>
                        @endforeach
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-pdf table-pdf">* Harga :</td>
                        @foreach ($customer as $c)
                        <td class="text-pdf table-pdf">Rp. {{number_format($c->harga_titipan, 0, ',', '.')}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-pdf table-pdf">* Sistem uang jalan :</td>
                        @foreach ($customer as $c)
                        <td class="text-pdf table-pdf">Tanpa uang jalan</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-pdf table-pdf">* Nominal :</td>
                        @foreach ($customer as $c)
                        <td class="text-pdf table-pdf">Rp. 30.000.000 INDEX 28</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-pdf table-pdf"></td>
                        @foreach ($customer as $c)
                        <td class="text-pdf table-pdf">Rp. 25.000.000 INDEX 26</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-pdf table-pdf">* Tahun Unit Kendaraan :</td>
                        @foreach ($customer as $c)
                        <td class="text-pdf table-pdf">Minimal tahun 2016</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </li>
        <li>
            Harga Jasa Angkutan Batubara di atas sewaktu-waktu dapat berubah berdasarkan Kesepakatan <strong>PARA PIHAK.</strong>
        </li>
        <li>
            Harga yang sudah disepakati bersama dalam perjanjian ini akan terikat dalam <strong>Surat Perintah Kerja (SPK)</strong>
        </li>
    </ol>
</div>
<div class="page-break"></div>
<div class="container-fluid text-center">
    <h3>
        PASAL 8<br>
        MEKANISME UANG JALAN
    </h3>
</div>
<div class="container-fluid">
    <table class="text-pdf table-pdf table mt-2">
        <thead>
            <tr>
                <th class="text-pdf table-pdf text-center">No</th>
                <th class="text-pdf table-pdf text-center">Uraian</th>
                <th class="text-pdf table-pdf text-center">
                    @foreach ($customer as $c)
                    {{ $c->singkatan }}
                    @if (!$loop->last)
                    &
                    @endif
                    @endforeach
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center table-pdf">1</td>
                <td class="table-pdf">Nilai uang jalan :</td>
                <td class="table-pdf">Nilai uang jalan mengikuti vendor</td>
            </tr>
            @foreach ($data->vendor->vendor_uang_jalan as $item)
            <tr>
                <td class="table-pdf text-center">
                    @if ($loop->first)
                    2
                    @endif
                </td>
                <td class="table-pdf">
                    @if ($loop->first)
                    Nilai maksimal uang jalan :
                    @endif
                </td>
                <td class="table-pdf">* {{$item->rute->nama}} : Rp. {{number_format($item->hk_uang_jalan, 0, ',', '.')}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="container-fluid text-center">
    <h3>
        PASAL 9<br>
        MEKANISME UANG JALAN
    </h3>
</div>
<div class="container-fluid mt-3">
    <table class="table">
        <thead>
            <tr>
                <th class="text-pdf table-pdf text-center">No</th>
                <th class="text-pdf table-pdf text-center">Uraian</th>
                <th class="text-pdf table-pdf text-center">
                    @foreach ($customer as $c)
                    {{ $c->singkatan }}
                    @if (!$loop->last)
                    &
                    @endif
                    @endforeach
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-pdf table-pdf text-center">1</td>
                <td class="text-pdf table-pdf">Bersifat wajib untuk seluruh vendor :</td>
                <td class="text-pdf table-pdf">Ya</td>
            </tr>
            <tr>
                <td class="text-pdf table-pdf text-center">2</td>
                <td class="text-pdf table-pdf">Biaya :</td>
                <td class="text-pdf table-pdf">Rp. 1.500.000/Bulan/unit</td>
            </tr>
            <tr>
                <td class="text-pdf table-pdf text-center" rowspan="3">3</td>
                <td class="text-pdf table-pdf" rowspan="3">Fasilitas meliputi :</td>
                <td class="text-pdf table-pdf">* Free Jasa Mekanik</td>
            </tr>
            <tr>
                <td class="text-pdf table-pdf">* Pool dan workshop (Free)</td>
            </tr>
            <tr>
                <td class="text-pdf table-pdf">* Keamanan (Pool)</td>
            </tr>
        </tbody>
    </table>
</div>
<div class="container-fluid text-center">
    <h3>
        PASAL 10<br>
        SYARAT DAN KETENTUAN BERLAKU
    </h3>
</div>
<div class="container-fluid">
    <ol type="1" class="text-pdf">
        <li>Harga belum termasuk PPH 2%</li>
        <li>Harga sudah termasuk biaya bongkar - pasang terpal</li>
        <li>Harga sudah termasuk dengan bahan bakan minyak (BBM)</li>
        <li>Mobilisasi dan Demobilisasi ditanggung oleh <strong>PIHAK KEDUA</strong></li>
        <li>Wajib Commissioning dan biaya dibebankan ke <strong>PIHAK KEDUA</strong></li>
        <li>
            <strong>PIHAK KEDUA</strong> wajib memasang GPS yang disediakan oleh <strong>PIHAK PERTAMA</strong> dan biaya dibebankan ke
            <strong>PIHAK KEDUA</strong> sebesar Rp. 1.500.000/unit
        </li>
        <li>
            Biaya rutin bulanan pulsa kuota GPS di tanggung oleh <strong>PIHAK PERTAMA</strong>
        </li>
        <li>
            Biaya BBM storing dibebankan kepada <strong>PIHAK KEDUA</strong> sesuai jarak (one way) :
            <table>
                <tr>
                    <td class="text-center">-</td>
                    <td>MIP/BP - km 110</td>
                    <td>=</td>
                    <td>Rp 100.000</td>
                </tr>
                <tr>
                    <td class="text-center">-</td>
                    <td>Km 110 - km 74</td>
                    <td>=</td>
                    <td>Rp 300.000</td>
                </tr>
                <tr>
                    <td class="text-center">-</td>
                    <td>Km 74 - km 36</td>
                    <td>=</td>
                    <td>Rp 500.000</td>
                </tr>
                <tr>
                    <td class="text-center">-</td>
                    <td>Km 36 - Port</td>
                    <td>=</td>
                    <td>Rp 650.000</td>
                </tr>
            </table>
        </li>
        <li>
            Penambahan biaya Rp 200.000 / hari akan dibebankan kepada <strong>PIHAK KEDUA</strong> (apabila
            perbaikan unit yang dilakukan oleh mekanik <strong>PIHAK PERTAMA</strong> memakan waktu lebih dari 1 (satu) hari).
        </li>
        <li>
            Biaya BBM Storing, sparepart, oli, ban, dll :
            <ol type="a" class="text-pdf">
                <li>Titipan : akan ditambahkan di Nominal Titipan (Optional)</li>
                <li>Opname : akan di potong di tagihan (Optional)</li>
            </ol>
        </li>
    </ol>
</div>
<div class="page-break"></div>
<div class="container-fluid text-center">
    <h3>
        PASAL 11<br>
        HAK DAN KEWAJIBAN
    </h3>
</div>
<div class="container-fluid">
    <ol class="text-pdf" style="font-weight: bold">
        <li>
            HAK PARA PIHAK <br><br>
            <ol type="a" class="text-pdf" style="font-weight: normal">
                <li>
                    <strong>PIHAK PERTAMA</strong> dan pihak-pihak yang ditunjuknya/kuasanya mempunyai hak untuk
                    memeriksa unit kendaraan dengan terlebih dahulu melakukan pemberitahuan yang wajar kepada
                    <strong>PIHAK KEDUA</strong>.
                </li>
                <li>
                    <strong>PIHAK KEDUA</strong> berhak atas setiap pembayaran dengan jumlah dan batas waktu yang telah
                    disepakati bersama sebagaimana diatur dalam perjanjian ini atau tertera di pasal.
                </li>
            </ol>
            <br>
        </li>
        <li>
            KEWAJIBAN PARA PIHAK <br><br>
            <ol type="a">
                <li>
                    KEWAJIBAN PIHAK PERTAMA <br>
                    <ul style="font-weight: normal; list-style-type:disc">
                        <li>Menyiapkan batubara pada area yang sudah ditentukan.</li>
                        <li>Melakukan pembayaran sesuai dengan acuan Pasal 7.</li>
                        <li>Bekerja sama untuk membuat laporan volume, merawat & menjaga unit demi kelancaran operasional untuk armada
                            <strong>PIHAK KEDUA</strong>
                        </li>
                    </ul>
                    <br>
                </li>
                <li>
                    KEWAJIBAN PIHAK KEDUA <br>
                    <ul style="font-weight: normal; list-style-type:disc">
                        <li>Menyediakan pasokan Bahan Bakar Minyak sesuai standar PERTAMINA.</li>
                        <li>Menjamin keselamatan Driver dan Crew</li>
                        <li>Melaksanakan pekerjaan sebagaimana diatur dalam perjanjian ini secara berkelanjutan dan tidak
                            terputus dari Area pekerjaan yang sudah di tentukan oleh <strong>PIHAK PERTAMA</strong>
                        </li>
                        <li>Menyediakan unit kendaraan yang siap dan layak pakai.</li>
                        <li>Menjamin STNK dan SIM Driver.</li>
                        <li>
                            Bertanggun jawab terhadap masalah yang berhubungan seperti pengangkutan/transportasi
                            dan biaya di jalan (jika ada) pada setiap titik sehubungan dengan pengiriman batubara
                            secara aman dari Area pekerjaan yang sudah ditentukan.
                        </li>
                        <li>
                            Menyediakan personil yang terlatih dan mempunyai kualifikasi untuk melaksanakan di dalam
                            ruang lingkup pekerjaan.
                        </li>
                        <li>
                            Membayar seluruh gaji dan fasilitas-fasilitas tambahakn yang terutang kepada karyawannya
                            sehubungan dengan pelaksanaan kewajiban - kewajiban <strong>PIHAK KEDUA</strong> berdasarkan
                            Perjanjian ini dan mengatur perolehan semua izin, surat-surat, dokumen-dokumen serta
                            persetujuan lain yang dipersyaratkan oleh Pemerintah Republik Indonesia bagi karyawannya
                            agar dapat melaksanakan kewajiban <strong>PIHAK KEDUA</strong> berdasarkan perjanjian ini.
                        </li>
                        <li>
                            Membayar biaya support operasional sebesar Rp 1.500.000 perbulan/unit.
                        </li>
                        <li>
                            Melaksanakan jasa - jasanya dengan baik dan efisien dan akan membebaskan <strong>PIHAK PERTAMA</strong>
                            dari klaim yang timbul dari kegagalan pelaksanaannya ataupun kegagalan pelaksanaan karyawannya.
                        </li>
                        <li>
                            Memberikan laporan harian mengenai kondisi unit armada, jumlah angkutan seperti Tonase (MT)
                            dan laporan Slip (<i>Delivery Order</i>).
                        </li>
                        <li>
                            Apabila terjadi kecelakaan kerja ringan ataupun berat dan yang berhubungan dengan pihak lainnya
                            maka menjadi tanggung jawab <strong>PIHAK KEDUA</strong>.
                        </li>
                    </ul>
                </li>
            </ol>
        </li>
    </ol>
</div>
<div class="page-break"></div>
<div class="container-fluid text-center">
    <h3>
        PASAL 12<br>
        JANGKA WAKTU
    </h3>
</div>
<div class="container-fluid">
    <p class="text-pdf">
        Jangka waktu Jasa Angkutan Batubara dalam perjanjian kontrak ini adalah 1 (Satu) Tahun dan akan dievaluasi per 3 bulan oleh <strong>PIHAK PERTAMA.</strong>
    </p>
</div>
<br>
<div class="container-fluid text-center">
    <h3>
        PASAL 13<br>
        MATA UANG & METODE PEMBAYARAN
    </h3>
</div>
<div class="container-fluid">
    <p class="text-pdf">
        Seluruh pembayaran yang dilakukan berdasarkan Perjanjian ini dilakukan dalam Rupiah dan
        dibayarkan dengan menggunakan transfer ke rekening bank <strong>PIHAK KEDUA</strong> sebagaimana
        tercantum dalam setiap tagihan.
    </p>
    <table class="text-pdf">
        <tr>
            <td class="text-pdf" style="width: 100px">Nama Bank</td>
            <td class="text-pdf text-center" style="width: 20px" >:</td>
            <td class="text-pdf">{{$data->vendor->bank}}</td>
        </tr>
        <tr>
            <td class="text-pdf" style="width: 100px">No. Rekening</td>
            <td class="text-pdf text-center" style="width: 20px">:</td>
            <td class="text-pdf">{{$data->vendor->no_rekening}}</td>
        </tr>
        <tr>
            <td class="text-pdf" style="width: 100px">Atas Nama</td>
            <td class="text-pdf text-center" style="width: 20px">:</td>
            <td class="text-pdf">{{$data->vendor->nama_rekening}}</td>
        </tr>
    </table>
</div>
<br>
<div class="container-fluid text-center">
    <h3>
        PASAL 14<br>
        KEADAAN KAHAR
    </h3>
</div>
<div class="container-fluid mt-3">
    <ol class="text-pdf" type="1">
        <li>
            Istilah "keadaan kahar" berarti setiap kejadian yang secara wajar berada diluar kendala dari
            kesalahan atau kelalaian dari Pihak yang terkena dampak Keadaan Kahar, dan dimana telah
            dilakukan suatu tindakan yang hati-hati atau terjadinya ekspansi yang wajar, Pihak tersebut tidak
            dapat mencegah ataupun mengatasi suatu Keadaan Kahar, dengan tanpa mempertimbangkan apakah kejadian tersebut dapat diramalkan, seperti tindakan musuh masyarakat,
            pemberontakan, kerusuhan, pemogokan, penutupan, sengketa perburuhan, kekurangan tenaga kerja, kebakaran,
            ledakan, tanah longsor, gempa bumi, badai, banjir, kerusakan yang berpengaruh besar atau
            signifikan, gangguan atau keterlambatan dari Pengiriman, Blokade, ketidakmampuan mendapatkan izin atau persetujuan dari institusi
            Pemerintahan maupun tindakan pejabat sipil atau militer, dan sebab lain dengan jenis atau karakter yang disebutkan disini
            baik yang dapat diramalkan atau tidak, secara keseluruhan atau sebagian yang menghalangi pembangunan,
            persiapan, pengangkutan, pemuatan atau transportasi Material oleh <strong>PIHAK PERTAMA</strong> atau
            penerimaan dan pengangkutannya oleh <strong>PIHAK KEDUA</strong>
            <br><br>
        </li>
        <li>
            Pada saat terjadinya Keadaan Kahar, Pihak yang terkena dampak harus melakukan pemberitahuan kepada pihak lainnya dalam jangka waktu 3 (tiga)
            hari sejak peristiwa keadaan kahar tersebut terjadi disertai dengan keterangan sebanyak mungkin dan berkewajiban secara langsung memberitahukan kepada pihak lainnya.
            Setelah Keadaan Kahar berakhir, Pihak yang terkena dampak diharuskan untuk segera melaksanakan kewajiban yang tertunda.
        </li>
    </ol>
</div>
<div class="container-fluid text-center">
    <h3>
        PASAL 15<br>
        FORCE MAJEURE
    </h3>
</div>
<div class="container-fluid mt-3">
    <ol type="1" class="text-pdf">
        <li>
            Yang dimaksud Force Majeure dalam perjanjian ini adalah segala peristiwa yang terjadi diluar
            kekuasaan <strong>PARA PIHAK</strong> terhadap operasional Armada tersebut selama masa pelaksanaan
            kewajiban salah satu Pihak yang terpengaruh oleh peristiwa itu dan yang secara wajar tidak
            dapat dikuasainya, adapun peristiwa yang dimaksud yaitu : <br>
            <ol type="a">
                <li>Bencana alam</li>
                <li>Kebakaran, Perang, demonstrasi, huru hara, pemberontakan, pemogokan dan epidemic</li>
                <li>Adanya larangan dari pemerintah kabupaten</li>
            </ol>
        </li>
        <li>
            Dalam hal ini <strong>PARA PIHAK</strong> tidak dapat melaksanakan kewajiban berdasarkan perjanjian ini
            baik sebagian maupun keseluruhan karena terjadi force majeure, maka segala kegagalan atau
            keterlambatan tersebut tidak dapat dianggap kesalahan Para Pihak sehingga <strong>PARA PIHAK</strong>
            tidak dapat dikenakan sanksi/denda.
        </li>
        <li>
            Dalam hal terjadi force majeure, maka <strong>PARA PIHAK</strong> yang mengalami keadaan force majeure
            berkewajiban untuk memberitahukan secara tertulis kepada <strong>PIHAK LAINNYA</strong>, selambat-lambatnya
            2 x 24 jam terhitung sejak terjadinya keadaan force majeure tersebut untuk
            diselesaikan secara musyawarah mufakat.
        </li>
    </ol>
</div>
<div class="container-fluid text-center">
    <h3>
        PASAL 16<br>
        PENGAKHIRAN
    </h3>
</div>
<div class="container-fluid mt-3">
    <ol class="text-pdf" type="1">
        <li>
            <strong>PIHAK PERTAMA</strong> dapat menangguhkan atau mengakhiri Perjanjian ini kapanpun dengan
            pemberitahuan tertulis kepada <strong>PIHAK KEDUA</strong>, tanpa dikenakan penalty dan tanpa
            mengesampingkan hak-hak pemulihan-pemulihan lain <strong>PIHAK PERTAMA</strong> sebagaimana
            diberikan oleh hukum atau berdasarkan perjanjian ini apabila : <br>
            <ol type="a">
                <li>
                    <strong>PIHAK KEDUA</strong> gagal melaksanakan secara baik kewajiban-kewajiban kepada <strong>PIHAK PERTAMA</strong>
                    berdasarkan perjanjian ini dan kegagalan tersebut tidak diperbaiki dalam
                    jangka waktu 30 (tiga puluh) hari setelah dikirimkan pemberitahuan tertulis kepada <strong>PIHAK KEDUA</strong>
                    yang menyatakan penyebab dari kegagalan tersebut.
                </li>
            </ol>
        </li>
        <li>
            <strong>PIHAK KEDUA</strong> dapat menangguhkan atau mengakhiri Perjanjian ini kapanpun dengan
            pemberitahuan tertulis kepada <strong>PIHAK PERTAMA</strong>, tanpa dikenakan penalty atau
            mengesampingkan hak-hak pemulihan-pemulihan lain yang dimiliki oeh <strong>PIHAK KEDUA</strong>
            sebagaiman diberikan oleh hukum atau berdasarkan Perjanjian ini, apabila : <br>
            <ol type="a">
                <li>
                    <strong>PIHAK PERTAMA</strong> gagal dalam melaksanakan secara baik kewajiban-kewajibannya
                    kepada <strong>PIHAK KEDUA</strong> berdasarkan Perjanjian ini dan kegagalan tersebut tidak diperbaiki
                    dalam jangka waktu 30 (tiga puluh) hari setelah pengiriman sebuah pemberitahuan tertulis
                    yang menyatakan sifat dari kegagalan tersebut.
                </li>
                <li>
                    Adanya pembatasan dari <strong>PIHAK KETIGA</strong> seperti dari Pemerintah Lokal/Pusat atau
                    masyarakat yang tidak dapat diselesaikan dalam jangka waktu 30 (tiga puluh) hari.
                </li>
                <li>
                    Kadaluarsa atau pengakhiran dari Perjanjian ini tidak akan membebaskan masing-masing
                    Pihak dari kewajiban pembayaran kepada Pihak lainnya yang timbul sebelum tanggal
                    kadaluarsa atau pengakhiran.
                </li>
            </ol>
        </li>
        <li>
            <strong>PARA PIHAK</strong> dengan ini harus patuh dengan ketentuan Pasal 1266 dan 1267 dari Kitab
            Undang-Undang Hukup Perdata dan Patuh dengan ketentuan Pasal 372 KUHP dari Kitab Undang-Undang
            di Indonesia sejauh diperlukan penetapan pengadilan untuk mengakhiri Perjanjian ini.
        </li>
    </ol>
</div>
<div class="container-fluid text-center">
    <h3>
        PASAL 17<br>
        HUKUM YANG BERLAKU
    </h3>
</div>
<div class="container-fluid mt-3">
    <p class="text-pdf">
        Perjanjian ini merupakan hukum dan berlaku serta mengikat bagi <strong>PARA PIHAK</strong> dan ketentuan-ketentuan
        dari Perjanjian ini diatur dan diinterpretasikan sesuai dengan Hukum Republik Indonesia.
    </p>
</div>
<div class="container-fluid text-center">
    <h3>
        PASAL 18<br>
        PENYELESAIAN SENGKETA
    </h3>
</div>
<div class="container-fluid mt-3">
    <ol class="text-pdf" type="1">
        <li>
            Hal-hal yang tak terduga. <strong>PARA PIHAK</strong> menyadari bahwa hal-hal yang tidak terduga mungkin
            timbul di kemudian hari yang tidak dapat diperkirakan pada saat penandatanganan Perjanjian ini,
            dan untuk itu <strong>PARA PIHAK</strong> sepakat bahwa akan senantiasa berusaha sebaik-baiknya untuk
            menyelesaikan setiap masalah yang timbul karena hal-hal yang tidak terduga tersebut dengan
            semangat kerjasama dan saling pengertian.
        </li>
        <li>
            Penyelesaian Perselisihan. Apabila terjadi sengketa antara <strong>PARA PIHAK</strong> berkenaan dengan
            pelaksanaan Perjanjian ini dapat diselesaikan dengan musyawarah untuk mufakat, apabila upaya tersebut tetap tidak menemui
            jalan keluar maka <strong>PARA PIHAK</strong> sepakat untuk menyelesaikannya
            dan memilih domisili hukumnya di Pengadilan Negeri Muara Enim atau Lahat.
        </li>
        <li>
            <strong>PARA PIHAK</strong> sepakat dalam hal terjadi pembatalan Perjanjian akan mengabaikan Pasal 1266
            dan 1267 Kitab Undang-Undang Hukum Perdata (KUH Perdata) serta Patuh dengan ketentuan Pasal 372 Kitab Undang-Undang Hukum
            Pidana (KUHP) Republik Indonesia sepanjang mengenai dipersyaratkannya suatu putusan hakim untuk melakukan pembatalan Perjanjian.
        </li>
    </ol>
</div>
<div class="container-fluid text-center">
    <h3>
        PASAL 19<br>
        PENGALIHAN
    </h3>
</div>
<div class="container-fluid mt-3">
    <p class="text-pdf">
        Tidak satupun dari <strong>PARA PIHAK</strong> mempunyai hak untuk mengalihkan atau dengan cara lain
        memindahkan hak-haknya, kepemilikan-kepemilikannya, kepentingan-kepentingan atau kewajiban-kewajibannya
        berdasarkan Perjanjian ini atau perjanjian manapun lainnya yang dimaksudkan oleh
        Perjanjian ini tanpa persetujuan tertulis terlebih dahulu dari pihak lainnya (dimana persetujuan tersebut
        sewajarnya tidak ditahan atau dihambat) dan setiap pengalihan atau pemindahan yang bertentangan dengan Pasal ini dianggap batal demi
        hukum dan tidak mempunyai kekuatan hukum apapun.
    </p>
</div>
<div class="container-fluid text-center">
    <h3>
        PASAL 20<br>
        PERUBAHAN DAN PENGESAMPINGAN
    </h3>
</div>
<div class="container-fluid mt-3">
    <ol class="text-pdf" type="1">
        <li>
            Setiap penambahan dan perubahan terhadap Perjanjian ini wajib dilakukan dengan instrumen tertulis
            yang disepakati dan ditandatangani oleh <strong>PARA PIHAK</strong>.
        </li>
        <li>
            Setiap penambahan dan perubahan terhadap Perjanjian ini hanya akan dapat dilaksanakan dengan persetujuan
            tertulis <strong>PARA PIHAK</strong> terhadap penambahan maupun perubahan tersebut.
        </li>
    </ol>
</div>
<div class="container-fluid text-center">
    <h3>
        PASAL 21<br>
        KETERPISAHAN
    </h3>
</div>
<div class="container-fluid mt-3">
    <p class="text-pdf">
        Apabila setiap ketentuan, janji, prasyarat atau pasal manapun dalam Perjanjian ini dianggap tidak sah
        ataupun tidak dapat dilaksanakan, maka ketentuan lainnya dari Perjanjian ini, dan pelaksanaan dari ketentuan,
        janji, prasyarat ataupun pasal terhadap orang atau keadaan selain dari hal yang dinyatakan
        tidak sah atau tidak dapat dilaksanakan, akan tetap sah serta dapat dilaksanakan sepanjang
        diperbolehkan hukum, kecuali ketentuan, janji, prasyarat atau pasal tersebut dalam perjanjian ini
        adalah bagian yang material dari Perjanjian maka <strong>PARA PIHAK</strong> akan menggunakan usaha terbaik
        mereka untuk menyetujui revisi atau penggantian atas ketentuan tersebut.
    </p>
</div>
<div class="container-fluid text-center">
    <h3>
        PASAL 22<br>
        KESELURUHAN KESEPAKATAN
    </h3>
</div>
<div class="container-fluid mt-3">
    <p class="text-pdf">
        Perjanjian ini meliputi seluruh kesepakatan, janji-janji, dan pengertian - pengertian dari <strong>PARA PIHAK</strong>
        tentang segala hal yang diatur dalam Perjanjian, dan menggantikan seluruh diskusi-diskusi,
        kesepakatan dan pengertian-pengertian yang telah ada sebelumnya di antara <strong>PARA PIHAK</strong>
        tentang segala hal yang diatur dalam Perjanjian ini.
    </p>
</div>
<div class="container-fluid text-center">
    <h3>
        PASAL 23<br>
        ADDENDUM
    </h3>
</div>
<div class="container-fluid mt-3">
    <p class="text-pdf">
        <strong>PARA PIHAK</strong> setuju bahwa lampiran, addendum/perjanjian terpisah, amandemen akan
        ditandatangani untuk hal-hal yang tidak dibahas dalam Perjanjian ini, dan merupakan bagian yang
        tidak terpisah dari Perjanjian ini.
    </p>
</div>
<div class="page-break"></div>
<div class="container-fluid text-center">
    <h3>
        PASAL 24<br>
        SALINAN - SALINAN
    </h3>
</div>
<div class="container-fluid mt-3">
    <p class="text-pdf">
        Perjanjian ini dapat ditandatangani oleh <strong>PARA PIHAK</strong> dalam beberapa salinan, yang mana
        masing-masing salinan dianggap sebagai dokumen asli dari Perjanjian
    </p>
</div>
<div class="container-fluid text-center">
    <h3>
        PASAL 25<br>
        PENUTUP
    </h3>
</div>
<div class="container-fluid mt-3">
    <ol type="1" class="text-pdf">
        <li>Kontrak ini beserta lampiran-lampirannya merupakan satu kesatuan yang tidak dapat dipisahkan.</li>
        <li>
            Kontrak ini dibuat rangkap 2 (dua), ditandatangani diatas materai secukupnya, masing-masing
            rangkap memiliki kekuatan hukum yang sama dan masing-masing pihak memegang satu rangkap.
        </li>
    </ol>
</div>
<div class="container-fluid">
    <p class="text-pdf">
        Demikianlah, Perjanjian ini ditandatangani <strong>PARA PIHAK</strong> oleh pejabat-pejabatnya yang telah
        diberikan kewenangan secara patut pada tanggal sebagaimana disebutkan diatas.
    </p>
    <br>
    <div class="row-pdf">
        <div class="column-pdf">
            Bertindak untuk dan atas nama <br>
            <strong>PIHAK PERTAMA</strong> <br>
            <strong>PT. SUMATERA ALAM INDOPRIMA</strong> <br><br><br><br>
            <strong><u>MEDY ANDIKA, ST</u></strong><br>
            DIREKTUR UTAMA
        </div>
        <div class="column-pdf" style="margin-left: 100px">
            Bertindak untuk dan atas nama <br>
            <strong>PIHAK KEDUA</strong>
            <br><br><br><br><br>
            <strong><u>{{ $data->vendor->nama }}</u></strong><br>
            {{ $data->vendor->jabatan }}
        </div>
    </div>
</div>
<br>
<div class="container-fluid">
    <div class="row-pdf">
        <div class="column-4">
            <strong>Saksi PIHAK 1</strong><br><br>
            1. <br><br>
            2.
        </div>
        <div class="column-4">
            <strong>Tanda Tangan</strong>
            <br><br>
            <u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u> <br><br>
            <u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>
        </div>
        <div class="column-4">
            <strong>Saksi PIHAK 2</strong><br><br>
            1. <br><br>
            2.
        </div>
        <div class="column-4">
            <strong>Tanda Tangan</strong>
            <br><br>
            <u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u><br><br>
            <u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>
        </div>
    </div>
</div>
@endsection



