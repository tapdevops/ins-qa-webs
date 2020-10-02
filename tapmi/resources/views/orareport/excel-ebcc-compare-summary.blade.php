<style>
tr > td, tr > th {
    border: 1px solid #000000;
}
tr > th {
    text-align: center;
}
</style>
<table>
	<tr class="mythead">
		<th style="background-color: #c00000;color:#ffffff;">Tanggal</th>
		<th style="background-color: #c00000;color:#ffffff;">Sampling NIK</th>
		<th style="background-color: #c00000;color:#ffffff;">Sampling NAMA</th>
		<th style="background-color: #c00000;color:#ffffff;">Total Sampling</th>
		<th style="background-color: #c00000;color:#ffffff;">Jml Sampling MATCH</th>
		<th style="background-color: #002060;color:#ffffff;">% Akurasi Sampling EBCC</th>
	</tr>
	<?php
	    $i=0;
		$count_sampling = 0;
		$count_sampling_match = 0;
		$print = 0;
		foreach ($data as $key => $value){ 
			$j=$i+1;
			$count_sampling = $count_sampling + 1;
			$akurasi_sampling_ebcc = $value['akurasi_sampling_ebcc'];
			if ($akurasi_sampling_ebcc == 'MATCH'){
				$count_sampling_match = $count_sampling_match + 1;
			}
			if (isset($data[$j])) {
				if ($value['val_date_time'].$value['val_nik_validator'] != $data[$j]['val_date_time'].$data[$j]['val_nik_validator']){
					$print = 1;
				} 
			}
			else {
				$print = 1;
			}
			//print '<tr><td conspan="6">'.$akurasi_sampling_ebcc.'</td></tr>';
			if ($print == 1){
				$akurasi_sampling = round($count_sampling_match / $count_sampling * 100) ;
				print '<tr>';
				print '<td>'.$value['val_date_time'].'</td>';
				print '<td>'.$value['val_nik_validator'].'</td>';
				print '<td>'.$value['val_nama_validator'].'</td>';
				print '<td style="text-align: right;">'.$count_sampling.'</td>';
				print '<td style="text-align: right;">'.$count_sampling_match.'</td>';
				print '<td style="text-align: right;">'.$akurasi_sampling.'%</td>';
				print '</tr>';
				$count_sampling = 0;
				$count_sampling_match =0;
				$print = 0;
			}	 
			/* print '<tr>';
			print '<td colspan="5">'. $value['summary_key'].'</td>';
			if (isset($data[$j])){
				print '<td> '.$data[$j]['summary_key'].'</td>';
			}
			print '</tr>'; */
			$i++;
		}  
	?>
</table>


