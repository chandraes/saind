@extends('layouts.doc-layout')
@section('title', 'SPK')
@section('content')
<div class="container-fluid text-center">
    <u><strong style="font-size: 14pt">SURAT PERINTAH KERJA</strong></u><br>
    Nomor : {{sprintf("%03d", $data->nomor)}}/SPK/SAIND-{{$data->nama_singkatan}}/MIP-BP/{{$data->tahun}}
</div>
<br>
<div class="container-fluid text-10">
    <p>
        Saya bertanda tangan dibawah ini :
    </p>
    <table>
        <tr>
            <td style="width: 170px">Nama</td>
            <td style="width: 10px">:</td>
            <td><strong>MEDY ANDIKA, ST</strong></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td><strong>Direktur Utama</strong></td>
        </tr>
        <tr>
            <td>Nama Perusahaan</td>
            <td>:</td>
            <td><strong>PT. SUMATERA ALAM INDOPRIMA</strong></td>
        </tr>
        <tr>
            <td>Alamat Perusahaan</td>
            <td>:</td>
            <td><strong>Jl. Enim Ni 108 Komp Remiling Azzuri RT/RW, 001/001 Kel. Tungkal, Kec. Muara Enim, Kab. Muara Enim, Sumatera Selatan, 31311</strong></td>
        </tr>
        <tr>
            <td>No Handphone</td>
            <td>:</td>
            <td><strong>0813-6639-5268</strong></td>
        </tr>
        <tr>
            <td>Alamat Email</td>
            <td>:</td>
            <td><strong>pt.sumateraalamindoprima@gmail.com</strong></td>
        </tr>
    </table>
</div>
<br>
<div class="container-fluid text-10">
    <p>
        Untuk selanjutnya disebut <strong>PIHAK PERTAMA</strong> sebagai <strong>PEMBERI KERJA</strong> dan dengan ini
        memberikan perintah kerja kepada :
    </p>
    <table>
        <tr>
            <td style="width: 170px">Nama</td>
            <td style="width: 10px">:</td>
            <td><strong>{{$data->vendor->nama}}</strong></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td><strong>{{$data->vendor->jabatan}}</strong></td>
        </tr>
        <tr>
            <td>Nama Perusahaan</td>
            <td>:</td>
            <td><strong>{{$data->vendor->perusahaan}}</strong></td>
        </tr>
        <tr>
            <td>Alamat Perusahaan</td>
            <td>:</td>
            <td style="text-align: justify"><strong>{{$data->vendor->alamat}}</strong></td>
        </tr>
        <tr>
            <td>No Handphone</td>
            <td>:</td>
            <td><strong>{{$data->vendor->no_hp}}</strong></td>
        </tr>
        <tr>
            <td>Alamat Email</td>
            <td>:</td>
            <td><strong>{{$data->vendor->email}}</strong></td>
        </tr>
    </table>
</div>
<br>
<div class="container-fluid text-10">
    <p style="text-align: justify">
        Untuk selanjutnya disebut <strong>PIHAK KEDUA</strong> sebagai <strong>PENERIMA KERJA</strong> ataupun Kontraktor
        transportir. Adapun lokasi pekerjaan, mekanisme pembayaran, support operasional dan syarat
        ketentuan berlaku adalah sebagai berikut
    </p>
    <ol type="1" style="font-weight: bold">
        <li>
            Lokasi Pekerjaan & Mekanisme Pembayaran <br>
            @foreach ($customer as $c)
            <span style="font-weight: normal">{{$c->nama}} ({{$c->singkatan}})</span>
            <table class="table">
                <thead>
                    <tr>
                        <th class="table-pdf text-center align-middle" rowspan="2">No</th>
                        <th class="table-pdf text-center align-middle" colspan="2">Rute</th>
                        <th class="table-pdf text-center align-middle" rowspan="2">Jarak<br>(Km)</th>
                        <th class="table-pdf text-center align-middle" rowspan="2">Jadwal Kerja</th>
                    </tr>
                    <tr>
                        <th class="table-pdf text-center align-middle">Muatan</th>
                        <th class="table-pdf text-center align-middle">Bongkar</th>
                    </tr>
                </thead>
                <tbody>
                    @if($c->singkatan == 'MIP')
                    <tr>
                        <td class="text-pdf table-pdf text-center">1</td>
                        <td class="text-pdf table-pdf text-center">IS 107</td>
                        <td class="text-pdf table-pdf text-center">Port</td>
                        <td class="text-pdf table-pdf text-center">107</td>
                        <td class="text-pdf table-pdf text-center">24 Jam</td>
                    </tr>
                    <tr>
                        <td class="text-pdf table-pdf text-center">2</td>
                        <td class="text-pdf table-pdf text-center">IS 107</td>
                        <td class="text-pdf table-pdf text-center">IS 36</td>
                        <td class="text-pdf table-pdf text-center">71</td>
                        <td class="text-pdf table-pdf text-center">24 Jam</td>
                    </tr>
                    <tr>
                        <td class="text-pdf table-pdf text-center">3</td>
                        <td class="text-pdf table-pdf text-center">MIP</td>
                        <td class="text-pdf table-pdf text-center">IS 36</td>
                        <td class="text-pdf table-pdf text-center">97,85</td>
                        <td class="text-pdf table-pdf text-center">Malam Hari</td>
                    </tr>
                    <tr>
                        <td class="text-pdf table-pdf text-center">4</td>
                        <td class="text-pdf table-pdf text-center">MIP</td>
                        <td class="text-pdf table-pdf text-center">Port</td>
                        <td class="text-pdf table-pdf text-center">133,85</td>
                        <td class="text-pdf table-pdf text-center">Malam Hari</td>
                    </tr>
                    <tr>
                        <td class="text-pdf table-pdf text-center">5</td>
                        <td class="text-pdf table-pdf text-center">MIP</td>
                        <td class="text-pdf table-pdf text-center">IS 107</td>
                        <td class="text-pdf table-pdf text-center">26,85</td>
                        <td class="text-pdf table-pdf text-center">Malam Hari</td>
                    </tr>
                    @elseif($c->singkatan == 'BP')<tr>
                        <td class="text-pdf table-pdf text-center">1</td>
                        <td class="text-pdf table-pdf text-center">BP</td>
                        <td class="text-pdf table-pdf text-center">IS 36</td>
                        <td class="text-pdf table-pdf text-center">78,6</td>
                        <td class="text-pdf table-pdf text-center">24 Jam</td>
                    </tr>
                    <tr>
                        <td class="text-pdf table-pdf text-center">2</td>
                        <td class="text-pdf table-pdf text-center">BP</td>
                        <td class="text-pdf table-pdf text-center">Port</td>
                        <td class="text-pdf table-pdf text-center">114</td>
                        <td class="text-pdf table-pdf text-center">24 Jam</td>
                    </tr>
                    <tr>
                        <td class="text-pdf table-pdf text-center">3</td>
                        <td class="text-pdf table-pdf text-center">IS 107</td>
                        <td class="text-pdf table-pdf text-center">IS 36</td>
                        <td class="text-pdf table-pdf text-center">71</td>
                        <td class="text-pdf table-pdf text-center">24 Jam</td>
                    </tr>
                    <tr>
                        <td class="text-pdf table-pdf text-center">4</td>
                        <td class="text-pdf table-pdf text-center">IS 107</td>
                        <td class="text-pdf table-pdf text-center">Port</td>
                        <td class="text-pdf table-pdf text-center">107</td>
                        <td class="text-pdf table-pdf text-center">24 Jam</td>
                    </tr>
                    @endif
                </tbody>
            </table>
            @endforeach
        </li>
    </ol>
</div>
<div class="page-break"></div>
<br><br><br>
<table class="table text-10">
    <tr>
        <td class="table-pdf"></td>
        <td class="table-pdf"><strong>{{strtoupper($data->pembayaran)}}</strong></td>
        <td class="table-pdf"></td>
        <td class="table-pdf"></td>
    </tr>
    <tr>
        <td class="table-pdf"></td>
        <td class="table-pdf">* Harga :</td>
        @foreach ($data->vendor->vendor_bayar as $v)
        <td class="table-pdf">Rp. @if ($data->pembayaran == $data->pembayaran)
            {{$v->harga_kesepakatan}}</td>
        @endif
        @endforeach
    </tr>
    <tr>
        <td class="table-pdf"></td>
        <td class="table-pdf">* Nominal :</td>
        <td class="table-pdf">Rp. 30.000.000 INDEX 28</td>
        <td class="table-pdf">Rp. 30.000.000 INDEX 28</td>
    </tr>
    <tr>
        <td class="table-pdf"></td>
        <td class="table-pdf"></td>
        <td class="table-pdf">Rp. 25.000.000 INDEX 26</td>
        <td class="table-pdf">Rp. 25.000.000 INDEX 26</td>
    </tr>
    <tr>
        <td class="table-pdf"></td>
        <td class="table-pdf">* Tahun Unit Kendaraan :</td>
        <td class="table-pdf">Minimal tahun 2016</td>
        <td class="table-pdf">Minimal tahun 2016</td>
    </tr>
</table>
<div class="container-fluid text-10">
    <ol type="1" start="2" style="font-weight: bold">
        <li>
            Support Operasional :
        </li>
    </ol>
    <table class="table" style="font-weight: normal">
        <thead>
            <tr>
                <th class="table-pdf text-center align-middle">No</th>
                <th class="table-pdf text-center align-middle">Uraian</th>
                <th class="table-pdf text-center align-middle">MIP/BPS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="table-pdf text-center">1</td>
                <td class="table-pdf">Biaya :</td>
                <td class="table-pdf">Rp. 1.500.000/Bulan/unit</td>
            </tr>
            <tr>
                <td class="table-pdf text-center">2</td>
                <td class="table-pdf">Fasilitas meliputi :</td>
                <td class="table-pdf">* Free Jasa Mekanik</td>
            </tr>
            <tr>
                <td class="table-pdf text-center"></td>
                <td class="table-pdf"></td>
                <td class="table-pdf">* Pool dan workshop (Free)</td>
            </tr>
            <tr>
                <td class="table-pdf text-center"></td>
                <td class="table-pdf"></td>
                <td class="table-pdf">* Keamanan (Pool)</td>
            </tr>
        </tbody>
    </table>
</div>
<div class="container-fluid text-10">
    <ol type="1" start="3" style="font-weight: bold">
        <li>
            Rekening Pembayaran : <br>
            <table style="font-weight: normal">
            <tr>
                <td style="width: 120px">Nama Bank</td>
                <td style="width: 10px">:</td>
                <td>{{$data->vendor->bank}}</td>
            </tr>
            <tr>
                <td>No Rekening</td>
                <td>:</td>
                <td>{{$data->vendor->no_rekening}}</td>
            </tr>
            <tr>
                <td>Nama Rekening</td>
                <td>:</td>
                <td>{{$data->vendor->nama_rekening}}</td>
            </tr>
            </table><br>
        </li>
        <li>
            Masa Berlaku : <br>
            1 (Satu) Tahun dan akan di review per 3 bulan <br>
            Note : Harga tidak mengikat, apabila ada perubahan harga maka PIHAK PERTAMA akan
            memberitahukan kepada PIHAK KEDUA. <br><br>
        </li>
        <li>
            Syarat Ketentuan Berlaku
        </li>
    </ol>
</div>
<div class="container-fluid text-10">
    <ol type="1">
        <li>
            Harga Belum termasuk PPH 2%
        </li>
        <li>
            Harga sudah termasuk biaya bongkar - pasang terpal
        </li>
        <li>Harga sudah termasuk dengan bahan bakan minyak (BBM)</li>
        <li>Pemilik unit wajib Pengawasan dan Pengurusan Administrasi</li>
        <li>Mobilisasi dan Demobilisasi ditanggung oleh pemilik unit</li>
        <li>Wajib Commissioning dan biaya dibebankan ke pemilik unit</li>
        <li>Pemilik unit wajib memasang GPS yang disediakan oleh perusahaan dan biaya dibebankan ke pemilik unit sebesar Rp. 1.500.000/unit</li>
        <li>Biaya rutin bulanan pulsa kuota GPS di tanggung oleh Perusahaan</li>
        <li>
            Biaya BBM storing dibebankan kepada Pemilik Unit sesuai jarak (one way) : <br>
            <table>
                <tr>
                    <td style="width: 10px">-</td>
                    <td style="width: 130px">MIP/BP - km 110</td>
                    <td style="width: 20px">=</td>
                    <td>Rp 100.000</td>
                </tr>
                <tr>
                    <td>-</td>
                    <td>Km 110 - km 74</td>
                    <td>=</td>
                    <td>Rp 300.000</td>
                </tr>
                <tr>
                    <td>-</td>
                    <td>Km 74 - km 36</td>
                    <td>=</td>
                    <td>Rp 500.000</td>
                </tr>
                <tr>
                    <td>-</td>
                    <td>Km 36 - Port</td>
                    <td>=</td>
                    <td>Rp 650.000</td>
                </tr>
            </table>
        </li>
        <li>
            Penambahan biaya Rp 200.000 / hari akan dibebankan kepada Pemilik Unit (apabila perbaikan unit yang dilakukan oleh
            mekanik perusahaan memakan waktu lebih dari 1 hari).
        </li>
        <li>Biaya BBM Storing, sparepart, oli, ban, dll akan di potong di tagihan (Optional)</li>
        <li>Indek Bak Dump Truck ±28 Up Tahun 2016 Up</li>
        <li>Jumlah Unit dan jam kerja harus mengikuti sesuai kebutuhan <strong>PIHAK PERTAMA</strong></li>
        <li>
            <strong>PIHAK KEDUA</strong> wajib menjaminkan dokumen berupa STNK dan SIM Driver kepada <strong>PIHAK PERTAMA</strong>
        </li>
        <div class="page-break"></div><br><br><br><br>
        <li>
            Penambahan dan pengurangan Unit Dump Truck terlebih dahulu mendapatkan persetujuan <strong>PIHAK PERTAMA</strong>
            dan Harus Lulus induksi <i>Commissioning</i>.
        </li>
        <li>
            Unit Dump Truck <strong>PIHAK KEDUA</strong> tidak boleh pindah ke Kontraktor Hauling lain dan atau ke
            lokasi tambang lain sebelum mendapat persetujuan dari <strong>PIHAK PERTAMA</strong>.
        </li>
        <li>
            <strong>PIHAK KEDUA</strong> berkewajiban membayar biaya support operasional sebesar Rp 1.500.000 perbulan/unit kepada <strong>PIHAK PERTAMA</strong>.
        </li>
        <li>
            <strong>PIHAK KEDUA</strong> dilarang melakukan tindakan yang bisa menghambat kegiatan operasional angkutan
            jika terdapat kendala keterlambatan pembayaran angkutan.
        </li>
        <li>
            <strong>PIHAK KEDUA</strong> wajib bertanggung jawab secara penuh terhadap seluruh pekerjaan di lingkup
            kerja yang ditunjuk <strong>PIHAK PERTAMA</strong>.
        </li>
        <li>
            <strong>PIHAK KEDUA</strong> Wajib memenuhi Target angkutan sesuai dengan yang telah diberikan oleh <strong>PIHAK PERTAMA</strong>
            secara bertahap.
        </li>
        <li>
            Apabila dalam pelaksanaan terdapat perubahan - perubahan secara teknis, maka akan diatur dan dituangkan dalam bentuk SPK addendum yang akan diberitahukan <strong>PIHAK PERTAMA</strong>
        </li>
        <li>
            <strong>PIHAK KEDUA</strong> harus menyediakan pengawas Lapangan di Area
        </li>
        <li>Pengawas Pekerjaan : Lokasi kerja dan akan dibantu juga oleh <strong>PIHAK PERTAMA.</strong></li>
        <li>Tanggal Mulai setelah unit di Commissioning</li>
    </ol>
</div>
<div class="container-fluid text-pdf">
    <p>
        Demikian Surat Perintah Kerja ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
    </p>
    <table>
        <tr>
            <td style="width: 100px">Ditanda tangani di</td>
            <td style="width: 10px">:</td>
            <td>Muara Enim</td>
        </tr>
        <tr>
            <td>Hari dan Tanggal</td>
            <td>:</td>
            <td>{{$data->tanggal_indo}}</td>
        </tr>
    </table>
</div>
<br>
<div class="row-pdf text-pdf">
    <div class="column-pdf">
        <strong>PIHAK PERTAMA</strong><br>
        <strong>PT.SUMATERA ALAM INDOPRIMA</strong>
        <br><br><br><br>
        <strong><u>MEDY ANDIKA, ST</u></strong><br>
        Direktur Utama
    </div>
    <div class="column-pdf">
        <strong>PIHAK KEDUA</strong><br>
        <strong>{{$data->vendor->perusahaan}}</strong>
        <br><br><br><br>
        <strong><u>{{$data->vendor->nama}}</u></strong><br>
        {{$data->vendor->jabatan}}
    </div>
</div>
<br>
<div class="container-fluid text-pdf">
    Tembusan: <br>
    <i>
    <ol type="1">
        <li>Komisaris PT.SAIND</li>
        <li>Jajaran Devan Direksi PT. SAIND</li>
        <li>Pemilik Unit</li>
        <li>Arsip</li>
    </ol>
    </i>
</div>

@endsection
