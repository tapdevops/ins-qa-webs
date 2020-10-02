<style>
tr > td, tr > th {
    border: 1px solid #000000;
}
tr > th {
    text-align: center;
}
</style>
<table>
	<tr rowspan="2" class="mythead">
		<th style="background-color: #c00000;color:#ffffff;" >Tanggal</th>
		<th style="background-color: #c00000;color:#ffffff;" >Sampling NIK</th>
		<th style="background-color: #c00000;color:#ffffff;" >Sampling NAMA</th>
		<th style="background-color: #c00000;color:#ffffff;" >NIK Krani Buah</th>
		<th style="background-color: #c00000;color:#ffffff;" >Nama Krani Buah</th>
		<th style="background-color: #c00000;color:#ffffff;" >Jml Sampling-MATCH</th>
		<th style="background-color: #c00000;color:#ffffff;" >Kuantitas-MATCH</th>
		<th style="background-color: #c00000;color:#ffffff;" >Total Sampling MS</th>
		<th style="background-color: #c00000;color:#ffffff;" >Selisih MS</th>
		<th style="background-color: #002060;color:#ffffff;" >% Akurasi Kuantitas</th>
		<th style="background-color: #002060;color:#ffffff;" >% Akurasi Kualitas MS</th>
	</tr>
	<?php
	    $i=0;
		$count_sampling_match = 0;
		$count_kuantitas_match = 0;
		$total_sampling_ms = 0;
		$selisih_ms = 0;
		$print = 0;
		array_multisort($data);
		foreach ($data as $key => $value){ 
			$j=$i+1;
			if ($value['ebcc_nik_kerani_buah']!=""){
				$akurasi_sampling_ebcc = $value['akurasi_sampling_ebcc'];
				$akurasi_kuantitas = $value['akurasi_kuantitas'];
				
				/* if (isset($data[$j])) {
					print '<tr><td colspan="5">'.$value['ebcc_nik_kerani_buah'].'</td>';
					print '<td colspan="6">'.$data[$j]['ebcc_nik_kerani_buah'].'</td></tr>';
					//print '<tr><td colspan="11">'..'</td></tr>';
				} */ 
				
				if ($akurasi_sampling_ebcc == 'MATCH'){
					$count_sampling_match = $count_sampling_match + 1;
				}
				if ($akurasi_kuantitas == 'MATCH'){
					$count_kuantitas_match = $count_kuantitas_match + 1;
					$total_sampling_ms = $total_sampling_ms + $value['val_jml_ms'];
					$selisih_ms = $selisih_ms + $value['akurasi_kualitas_ms'];
				}
				if (isset($data[$j])) {
					if ($value['summary_krani'] != $data[$j]['summary_krani'] ){
						$print = 1;
					} 
				}
				else {
					$print = 1;
				}
				if ($print == 1 and !empty ($value['ebcc_nik_kerani_buah'])){
					$per_akurasi_kuantitas = ($count_sampling_match > 0) ? round($count_kuantitas_match /$count_sampling_match*100) : 0;
					$per_akurasi_kulitas_ms = ($total_sampling_ms > 0) ? round(($total_sampling_ms - $selisih_ms) / $total_sampling_ms *100) : 0;
					print '<tr>';
					print '<td>'.$value['val_date_time'].'</td>';
					print '<td>'.$value['val_nik_validator'].'</td>';
					print '<td>'.$value['val_nama_validator'].'</td>';
					print '<td>'.$value['ebcc_nik_kerani_buah'].'</td>';
					print '<td>'.$value['ebcc_nama_kerani_buah'].'</td>';
					print '<td>'.$count_sampling_match.'</td>';
					print '<td>'.$count_kuantitas_match.'</td>';
					print '<td>'.$total_sampling_ms.'</td>';
					print '<td>'.$selisih_ms.'</td>';
					print '<td>'.$per_akurasi_kuantitas.'</td>';
					//print '<td>'.$value['ebcc_nik_kerani_buah'].'</td>';
					print '<td>'.$per_akurasi_kulitas_ms.'</td>';
					/* if (isset($data[$j])){
						print '<td>'.$data[$j]['ebcc_nik_kerani_buah'].'</td>';
					}
					else{
						print '<td>'.'-'.'</td>';
					}
					print '</tr>'; */
					$count_sampling_match = 0;
					$count_kuantitas_match = 0;
					$total_sampling_ms = 0;
					$selisih_ms = 0;
					$per_akurasi_kuantitas = 0;
					$per_akurasi_kulitas_ms = 0;
					$print = 0;
				}
			}
			$i++;
		}  
	?>
</table>


