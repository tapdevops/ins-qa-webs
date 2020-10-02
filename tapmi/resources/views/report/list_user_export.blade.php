<table>
	<tr>
        <th style="text-align:center;">NIK</th>
        <th style="text-align:center;">Fullname</th>
        <th style="text-align:center;">Job Desc</th>
        <th style="text-align:center;">Start Date</th>
        <th style="text-align:center;">End date</th>
        <th style="text-align:center;">Status</th>
	</tr>
	@if ( count( $master_user ) > 0 )
                <?php //dd($master_user); ?>
		@foreach ($master_user as $key => $q)
			<tr>
                                <td style="text-align:left;">{{  $q['employee_nik'] }}</td>
                                <td style="text-align:left;">{{  $q['employee_fullname'] }}</td>
                                <td style="text-align:left;">{{  $q['employee_position'] }}</td>
                                <td style="text-align:left;">{{  $q['start_date'] }}</td>
                                <td style="text-align:left;">{{  $q['end_date'] }}</td>
                                @if($q['end_date'] == "9999-12-31 00:00:00")
                                        <td style="text-align:left;">Active</td>
                                @else
                                        <td style="text-align:left;">Inactive</td>
                                @endif
			</tr>
		@endforeach
	@endif
</table>


