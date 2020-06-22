<table>
	<tr>
		@if ( !empty( $head ) )
			@foreach($head as $hd)
				<th style="text-align: left;back">{{ $hd['forexcel'] }}</th>
			@endforeach
		@endif
		<!-- 
		<th style="text-align: left;back">VAL_EBCC_CODE</th>
		<th style="text-align: left;back">VAL_WERKS</th>
		<th style="text-align: left;back">VAL_NIK_VALIDATOR</th>
		<th style="text-align: left;back">VAL_NAMA_VALIDATOR</th>
		<th style="text-align: left;back">VAL_JABATAN_VALIDATOR</th>
		<th style="text-align: left;back">VAL_DATE_TIME</th>
		<th style="text-align: left;back">VAL_AFD_CODE</th>
		<th style="text-align: left;back">VAL_BLOCK_CODE</th>
		<th style="text-align: left;back">VAL_BLOCK_NAME</th>
		<th style="text-align: left;back">VAL_TPH_CODE</th>
		<th style="text-align: left;back">VAL_DELIVERY_TICKET</th>
		<th style="text-align: left;back">VAL_JML_BM</th>
		<th style="text-align: left;back">VAL_JML_BK</th>
		<th style="text-align: left;back">VAL_JML_MS</th>
		<th style="text-align: left;back">VAL_JML_OR</th>
		<th style="text-align: left;back">VAL_JML_BB</th>
		<th style="text-align: left;back">VAL_JML_JK</th>
		<th style="text-align: left;back">VAL_JML_BA</th>
		<th style="text-align: left;back">VAL_JML_BRD</th>
		<th style="text-align: left;back">VAL_JJG_PANEN</th>
		<th style="text-align: left;back">EBCC_ID_RENCANA</th>
		<th style="text-align: left;back">EBCC_NO_BCC</th>
		<th style="text-align: left;back">EBCC_WERKS</th>
		<th style="text-align: left;back">EBCC_NIK_KERANI_BUAH</th>
		<th style="text-align: left;back">EBCC_NAMA_KERANI_BUAH</th>
		<th style="text-align: left;back">EBCC_JABATAN_KERANI_BUAH</th>
		<th style="text-align: left;back">EBCC_DATE_TIME</th>
		<th style="text-align: left;back">EBCC_AFD_CODE</th>
		<th style="text-align: left;back">EBCC_BLOCK_CODE</th>
		<th style="text-align: left;back">EBCC_BLOCK_NAME</th>
		<th style="text-align: left;back">EBCC_TPH_CODE</th>
		<th style="text-align: left;back">EBCC_JML_BM</th>
		<th style="text-align: left;back">EBCC_JML_BK</th>
		<th style="text-align: left;back">EBCC_JML_MS</th>
		<th style="text-align: left;back">EBCC_JML_OR</th>
		<th style="text-align: left;back">EBCC_JML_BB</th>
		<th style="text-align: left;back">EBCC_JML_JK</th>
		<th style="text-align: left;back">EBCCJML_BA</th>
		<th style="text-align: left;back">EBCC_JML_BRD</th>
		<th style="text-align: left;back">EBCC_JJG_PANEN</th>
		-->
		
		
	</tr>
	@if ( !empty( $data ) )
		<?php 
			foreach ( $data as $dt ){
				echo '<tr>';
				$dt = (array) $dt;
				$tmp[] = $dt;
				foreach($head as $hd){
					echo '<td style="text-align: left;">'.$dt[ $hd['original'] ].'</td>';
				}
				echo '</tr>';
			}
		?>
	@endif
</table>