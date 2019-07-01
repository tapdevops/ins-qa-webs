@extends( 'layouts.default.page-normal-main' )
@section( 'title', 'Report' )

@section( 'subheader' )
	<ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
		<li class="m-nav__item">
			<a href="{{ url( '/report' ) }}" class="m-nav__link">
				<span class="m-nav__link-text">
					Report
				</span>
			</a>
		</li>
	</ul>
@endsection

@section( 'content' )
	<div class="row">
		<div class="col-md-8">
			<div class="row">
				<div class="col-md-4">
					<div class="m-input-icon m-input-icon--left">
						<input type="text" class="form-control m-input m-input--solid" placeholder="Search..." id="generalSearch">
						<span class="m-input-icon__icon m-input-icon__icon--left">
							<span>
								<i class="la la-search"></i>
							</span>
						</span>
					</div>
				</div>
				<div class="col-md-4">
					<!--a href="{{ url( '/modules/user-authorization' ) }}" style="color:inherit;" class="btn btn-default btn-block"><i class="fa fa-lock"></i> User Authorization</a-->
				</div>
				<div class="col-md-4"></div>
			</div>
		</div>
		
		<div class="col-md-4 m--align-right">
			<a href="{{ url( '/master-user/create' ) }}" class="btn btn-focus m-btn m-btn--custom m-btn--icon m-btn--air m-btn--pill">
				<span>
					<i class="fa fa-plus"></i>
					<span>Tambah</span>
				</span>
			</a>
			<div class="m-separator m-separator--dashed d-xl-none"></div>
		</div>
	</div>

	<table class="m-datatable" id="html_table" width="100%" style="margin-top:20px;">
		<thead>
			<tr>
				<th>Auth Code</th>
				<th>NIK</th>
				<th>Nama</th>
				<th>Job Desc</th>
				<th>User Role</th>
				<th>Location</th>
				<th>Ref Role</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			
		</tbody>
	</table>
@endsection

@section( 'scripts' )
	<script type="text/javascript">

		var base_url = "{{ url( '' ) }}";
		/*
		var datatable = {
			init: function() {
				var e;
				e = $(".m-datatable").mDatatable({
					data: {
						saveState: {
							cookie: !1
						}
					},
					search: {
						input: $( "#generalSearch" )
					},
					columns: [
					{
						field: "Module Code",
						title: "Actions",
						width: 150,
						sortable: !1,
						overflow: "visible",
						template: function(e, a, i) {
							return '<span style="font-family: \'Courier New\';">' + e['Module Code'] + '</span>'
						}
					}, {
						field: "Actions",
						width: 110,
						title: "Actions",
						sortable: !1,
						overflow: "visible",
						
						//template: function(e, a, i) {
						//	return '\t\t\t\t\t\t<div class="dropdown ' + (i.getPageSize() - a <= 4 ? "dropup" : "") + '">\t\t\t\t\t\t\t<a href="#" class="btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="dropdown"><i class="la la-ellipsis-h"></i></a>\t\t\t\t\t\t  \t<div class="dropdown-menu dropdown-menu-right">\t\t\t\t\t\t    \t<a class="dropdown-item" href="' + base_url + '/modules/user-authorization/' + e['Auth Code'] + '"><i class="fa fa-lock"></i> User Authorization' + '</a>\t\t\t\t\t\t <a class="dropdown-item" href="' + base_url + '/modules/user-authorization/' + e['Auth Code'] + '"><i class="fa fa-lock"></i> User Authorization' + '</a>\t\t\t\t\t\t    \t</div>\t\t\t\t\t\t</div>\t\t\t\t\t\t<a href="#" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View "><i class="la la-edit"></i></a>\t\t\t\t\t'
						//}
						
						template: function(e, a, i) {
							return '\t\t\t\t\t\t<a href="' + base_url + '/user/edit/' + e['Auth Code'] + '" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View "><i class="la la-edit"></i></a>\t\t\t\t\t'
						}
					}],

				}), $("#m_form_status").on("change", function() {
					e.search($(this).val().toLowerCase(), "Status")
				}), $("#m_form_type").on("change", function() {
					e.search($(this).val().toLowerCase(), "Type")
				}), $("#m_form_status, c#m_form_type").selectpicker()
			}
		};
		*/

		jQuery(document).ready(function() {
			datatable.init()
		});
	</script>
@endsection