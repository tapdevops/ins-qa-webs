<table>
	<tr>
		<td><b>Periode : {{ $periode }}</b></td>
	</tr>
	<tr>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Estate</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Afd</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Block Code</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Block Name</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Kelas Block Bulan ini</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Total Nilai / Jumlah</b></td>
		<td colspan="6" style="text-align:center;color: #FFF; background-color: #043077;"><b>Kelas Block 6 Bulan Sebelumnya</b></td>

	</tr>
	<tr>
		<td style="background-color: #043077;"></td>
		<td style="background-color: #043077;"></td>
		<td style="background-color: #043077;"></td>
		<td style="background-color: #043077;"></td>
		<td style="background-color: #043077;"></td>
		<td style="background-color: #043077;"></td>
		<?php
			for ( $i = 1; $i <= 6; $i++ ) {
				print '<td style="text-align:center;"><b>'.date( 'M Y', strtotime( $periode." - ".$i." month" ) ).'</b></td>';
			}
		?>
	</tr>
	<?php
		if ( !empty( $report_data ) ) {
			$i = 0;
			foreach ( $report_data as $dt ) {
				print '<tr>';
				print '<td style="text-align:center;">EST_NAME</td>';
				print '<td style="text-align:center;">'.$dt['AFD_CODE'].'</td>';
				print '<td style="text-align:center;">'.$dt['BLOCK_CODE'].'</td>';
				print '<td style="text-align:center;">'.$dt['BLOCK_NAME'].'</td>';
				print '<td style="text-align:center;">'.$dt['NILAI_01'].'</td>';
				print '<td style="text-align:center;"></td>';
				print '<td style="text-align:center;">'.$dt['NILAI_02'].'</td>';
				print '<td style="text-align:center;">'.$dt['NILAI_03'].'</td>';
				print '<td style="text-align:center;">'.$dt['NILAI_04'].'</td>';
				print '<td style="text-align:center;">'.$dt['NILAI_05'].'</td>';
				print '<td style="text-align:center;">'.$dt['NILAI_06'].'</td>';
				print '</tr>';

				$i++;
			}
		}
	?>
</table>