<!DOCTYPE html>
<html>
<head>
    <title>MoU {{ $customer->customer_name }}</title>
	<style>
		.text-center {
		  text-align: center;
		}
		
		body {
		   padding: 0;
		}

		/*body {
		  margin-top: 100px;
		  margin-bottom: 100px;
		  margin-right: 100px;
		  margin-left: 100px;
		}*/
		
		.column {
		  float: left;
		  width: 50%;
		}

		.row:after {
		  content: "";
		  display: table;
		  clear: both;
		}
		
		table, td, th {
		  border: 1px solid black;
		  text-align: left;
		}

		table {
		  width: 100%;
		  border-collapse: collapse;
		}
		
		p.no-pad {
            padding: 0px;
        }
	</style>
</head>
<body>
    <p class="text-center no-pad">
		<b>PERJANJIAN KERJASAMA</b><br>
		<b>DALAM BIDANG</b><br>
		<b>JASA PENGIRIMAN BARANG</b><br>
		<b>NOMOR : {{ $mou_number }} </b>
	</p>
	
	<p class="text-center no-pad">
		<b>ANTARA</b><br>
		<b>PT. LAJU KILAU EKSPRESS</b><br>
		<b>DAN</b><br>
		<b>{{ $customer->customer_name }} </b>
	</p>
	
	<p class="text-center no-pad">
		Pada hari ini {{ $hari }} tanggal {{ $tgl_bilang }} bulan {{ $bulan }} tahun {{ $tahun_bilang }}  ({{ $tgl }} {{ $bulan }} {{ $tahun }}), yang bertanda tangan dibawah ini  : 
	</p>
	
	<div class="row">
	  <div class="column">
		1. Olipa Kosbardeny SE
	  </div>
	  <div class="column">
		Manager <b>PT. Laju Kilau Ekspress</b> berkedudukan di Jl Raya Kebayunan Tapos No 18 Kel. Tapos Kec Cimanggis Depok 16457, dalam hal ini bertindak untuk dan atas nama PT. Laju Kilau Ekspress, yang selanjutnya disebut <b>PIHAK PERTAMA</b>
	  </div>
	</div>
	<br>
	<div class="row">
	  <div class="column">
		2. {{ $pic[0]->name }}
	  </div>
	  <div class="column">
		Manager <b>{{ $customer->customer_name }}</b> berkedudukan di {{ $customer->address }}, dalam hal ini bertindak untuk dan atas nama {{ $customer->customer_name }}, yang selanjutnya disebut <b>PIHAK KEDUA</b> 
	  </div>
	</div>
	
	<p class="text-center no-pad">
		Kedua belah pihak menerangkan dengan ini sepakat membuat Perjanjian Kerjasama dengan ketentuan dan syarat – syarat tersebut dibawah ini :
	</p>
	
	<p class="text-center no-pad">
		Pasal 1<br>
		MAKSUD DAN TUJUAN
	</p>
	
	<p class="no-pad">
	1.	Pihak Pertama dan Pihak Kedua sepakat untuk mengadakan kerjasama dalam  dibidang jasa pengiriman barang Domestic & Import via darat, laut dan udara masing – masing sesuai dengan tujuannya.<br><br>
	2.	Dalam kerjasama ini kedua belah pihak akan melaksanakan kewajiban dan haknya dengan sebaik – baiknya sesuai dengan ketentuan yang telah disepakati bersama dalam perjanjian ini agar tidak ada pihak yang dirugikan dan saling menjaga nama baik serta reputasi masing – masing Perusahaan.<br>
	</p>
	
	<p class="text-center no-pad">
		Pasal 2<br>
		LINGKUP PERJANJIAN
	</p>
	
	<p class="no-pad">
	1.	Dalam perjanjian ini Pihak Pertama akan mengirimkan barang  Pihak Kedua dengan mengunakan kapal laut, trucking atau pesawat udara.<br><br>
	2.	Pihak Kedua menjamin kebenaran atas isi dari barang – barang yang dikirim sesuai dengan data, baik ukuran maupun berat  dari barang – barang tersebut.<br>
	</p>
	
	<p class="text-center no-pad">
		Pasal 3<br>
		KETENTUAN TARIF DAN PEMBAYARAN
	</p>
	
	<p class="no-pad">
	1. Tarif pengiriman adalah sebagaimana tercantum dalam daftar lampiran yang merupakan bagian tak terpisahkan dari perjanjian ini . Dengan catatan :
	Tarif dapat berubah setiap saat sesuai dengan ketentuan penerbangan , ketentuan pelayaran & ketentuan kebijakan dari Pemerintah yang lainnya.Tarif berlaku untuk kesepakatan bersama mengenai ketentuan Minimum Kg pengiriman dan ketentuan Volumetric barang. 
	<br><br>
	2. Pihak Pertama  akan menerima  pembayaran dari Pihak Kedua berupa  bilyet  giro ( BG ), atau dalam bentuk cash transfer. Dan pembayaran dari Pihak Kedua kepada Pihak Pertama akan dilakukan 2 minggu / 14 hari setelah Pihak Pertama menyerahkan Faktur / 
	Invoice kepada Pihak Kedua.  <br><br>
	3.	Pihak Pertama  akan menyerahkan surat tanda terima ( STT ) yang sudah ditanda tangani oleh  penerima barang ditujuan kepada Pihak Kedua sebagai bukti bahwa barang tersebut telah diterima.<br>
	</p>
	
	<p class="text-center no-pad">
		Pasal 4<br>
		JAMINAN  KEBERANGKATAN DAN KEHILANGAN
	</p>
	
	<p class="no-pad">
	1.	Pihak Pertama akan menjamin keberangkatan barang Pihak Kedua pada  kesempatan pertama.atau sesuai dengan Limit Time, atau sesuai dengan schedule pelayaran, penerbangan dan trucking.<br><br>
	2.	Pihak Pertama akan bertanggung jawab atas kehilangan atau kekurangan barang Pihak Kedua, maka Pihak Pertama  akan mengganti barang tersebut sesuai dengan kesepakatan. ( penggantian ganti rugi maksimal 3x biaya pengiriman dari barang yang hilang atau rusak tersebut )<br><br>
	3.	Pihak Pertama tidak bertanggung jawab atas kekurangan atau kerusakan barang apabila kondisi packing atau colly diterima dalam keadaan baik ( tidak rusak atau cacat ). <br><br>
	4.	Pihak Pertama akan melepaskan segala tanggung jawab apabila terjadi Force Majuer: seperti Bencana Alam, Kecelakaan Transportasi, Wabah Epedemi  dan Akibat perang.<br>
	</p>
	
	<p class="text-center no-pad">
		Pasal 5<br>
		PERSELISIHAN
	</p>
	
	<p class="no-pad">
	1.	Perselisihan yang timbul diantara kedua belah pihak dalam perjanjian ini pada pada prinsipnya akan diselesaikan secara damai guna mencapai musyawarah untuk mufakat.<br><br>
	2.	Apabila tidak dapat diselesaikan secara damai seperti ayat 1 pasal ini, maka kedua belah pihak sepakat menyelesaikan masalah tersebut dan tunduk kepada keputusan pengadilan Jakarta Pusat dan biaya pengadilan akan ditanggung oleh masing – masing pihak dengan jumlah yang sama.<br>
	</p>
	
	<p class="text-center no-pad">
		Pasal 6<br>
		PENUTUP
	</p>
	
	<p class="no-pad">
	1.	Surat perjanjian ini dibuat dalam rangkap dua masing – masing mempunyai kekuatan hukum yang sama, satu untuk Pihak Pertama, satu lainnya untuk Pihak Kedua dan setelah dibubuhi meterai secukupnya kemudian ditanda tangani kedua belah pihak.<br>
	</p>
	
	<p class="text-center no-pad">
		Dibuat di : Depok <br>
		Tanggal : {{ $tgl }} {{ $bulan }} {{ $tahun }}
	</p>
	
	<div class="row">
	  <div class="column">
		PIHAK PERTAMA
	  </div>
	  <div class="column">
		PIHAK KEDUA
	  </div>
	</div>
	<div class="row">
	  <div class="column">
		PT. LAJU KILAU EKSPRESS
	  </div>
	  <div class="column">
      {{ $customer->customer_name }}
	  </div>
	</div>
	
	<br><br><br><br><br><br>
	
	<div class="row">
	  <div class="column">
		OLIPIA KOSBARDENY SE
	  </div>
	  <div class="column">
        {{ $pic[0]->name }}
	  </div>
	</div>
	<div class="row">
	  <div class="column">
		MANAGER
	  </div>
	  <div class="column">
		MANAGER
	  </div>
	</div>
	
</body>
</html>