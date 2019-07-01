@extends( 'layouts.default.page-normal-main' )
@section( 'title', 'User Authorization' )

@section( 'content' )
	<table>
		<tr>
			<tr>
				<th>Module Code</th>
				<th>Module Name</th>
				@foreach ( $parameter->data as $q )
					<th>{{ $q->DESC }}</th>
				@endforeach
			</tr>
		</tr>
	</table>
	<table class="m-datatable" id="html_table" width="100%">
		<thead>
			<tr>
				<th>Module Code</th>
				<th>Module Name</th>
				@foreach ( $parameter->data as $q )
					<th>{{ $q->DESC }}</th>
				@endforeach
			</tr>
		</thead>
		<tbody>
			@foreach ( $modules->data as $q )
				<tr>
					<td>{{ $q->MODULE_CODE }}</td>
					<td>{{ $q->MODULE_NAME }}</td>
					@foreach ( $parameter->data as $z )
						<td>
							<span class="m-switch m-switch--outline m-switch--icon-check m-switch--brand">
								<label>
									<input type="checkbox" checked="checked" name="" onclick="return update( '{{ $q->MODULE_CODE }}', '{{ $z->DESC }}' )">
									<span></span>
								</label>
							</span>
						</td>
					@endforeach
				</tr>
			@endforeach
		</tbody>
	</table>
@endsection

@section( 'scripts' )
	<script type="text/javascript">
		var DatatableHtmlTableDemo = {
		init: function() {
			var e;
			e = $(".m-datatable").mDatatable({
				data: {
					saveState: {
						cookie: !1
					}
				},
				search: {
					input: $("#generalSearch")
				}
			}), $("#m_form_status").on("change", function() {
				e.search($(this).val().toLowerCase(), "Status")
			}), $("#m_form_type").on("change", function() {
				e.search($(this).val().toLowerCase(), "Type")
			}), $("#m_form_status, c#m_form_type").selectpicker()
		}
	};
	jQuery(document).ready(function() {
		DatatableHtmlTableDemo.init()
	});
	</script>
@endsection