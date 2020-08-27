<table>
	<tr>
		<th style="text-align:center;">Auth Code</th>
        <th style="text-align:center;">NIK</th>
        <th style="text-align:center;">Nama</th>
        <th style="text-align:center;">Job Desc</th>
        <th style="text-align:center;">User Role</th>
        <th style="text-align:center;">Location</th>
        <th style="text-align:center;">Ref Role</th>
        <th style="text-align:center;">APK Version</th>
        <th style="text-align:center;">APK Date</th>
	</tr>
	@if ( count( $master_user ) > 0 )
		@foreach ( $master_user as $q )
			<tr>
				<td style="text-align:left;">{{  $q['USER_AUTH_CODE'] }}</td>
                <td style="text-align:left;">{{  $q['EMPLOYEE_NIK'] }}</td>
                <td style="text-align:left;">{{  $q['FULLNAME'] }}</td>
                <td style="text-align:left;">{{  $q['JOB'] }}</td>
                <td style="text-align:left;">{{  $q['USER_ROLE'] }}</td>
                <td style="text-align:left;">{{  $q['LOCATION_CODE'] }}</td>
                <td style="text-align:left;">{{  $q['REF_ROLE'] }}</td>
                <td style="text-align:left;">{{  $q['APK_VERSION'] }}</td>
                <td style="text-align:left;">{{  $q['APK_DATE'] }}</td>
			</tr>
		@endforeach
	@endif
</table>


