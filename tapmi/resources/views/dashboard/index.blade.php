@extends( 'layouts.default.page-normal-main' )
@section( 'title', 'Dashboard' )
@section( 'content' )
	<!--div class="row">
		<div class="col-sm-12">
			<div class="page-title-box">
				<div class="btn-group float-right">
					<ol class="breadcrumb hide-phone p-0 m-0">
						<li class="breadcrumb-item"><a href="#">Annex</a></li>
						<li class="breadcrumb-item"><a href="#">Pages</a></li>
						<li class="breadcrumb-item active">starter</li>
					</ol>
				</div>
				<h4 class="page-title">starter</h4>
			</div>
		</div>
	</div-->
@endsection
@section( 'scripts' )
	<script type="text/javascript">
		MobileInspection.set_active_menu( '{{ $active_menu }}' );
	</script>
@endsection
