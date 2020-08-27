<table>
	<tr>
        <th style="text-align:center;">NIK</th>
        <th style="text-align:center;">Fullname</th>
        <th style="text-align:center;">Job Desc</th>
        <th style="text-align:center;">Start Date</th>
        <th style="text-align:center;">End date</th>
	</tr>
	@if ( count( $master_user ) > 0 )
		@foreach ( $master_user as $q )
			<tr>
                <td style="text-align:left;">{{  $q['EMPLOYEE_NIK'] }}</td>
                <td style="text-align:left;">{{  $q['EMPLOYEE_FULLNAME'] }}</td>
                <td style="text-align:left;">{{  $q['EMPLOYEE_POSITION'] }}</td>
                <td style="text-align:left;">{{  $q['START_DATE'] }}</td>
                <td style="text-align:left;">{{  $q['END_DATE'] }}</td>
			</tr>
		@endforeach
	@endif
</table>


