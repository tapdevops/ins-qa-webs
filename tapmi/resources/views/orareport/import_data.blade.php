@extends( 'layouts.default.page-normal-main' )
@section( 'title', 'Upload' )

@section( 'subheader' )
<ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
	<li class="m-nav__item">
		<a href="{{ url( 'upload' ) }}" class="m-nav__link">
			<span class="m-nav__link-text">
				Upload
			</span>
		</a>
	</li>
	<li class="m-nav__separator">
		-
	</li>
	<li class="m-nav__item">
		<a href="{{ url( 'upload' ) }}" class="m-nav__link">
			<span class="m-nav__link-text">
				Upload Realm
			</span>
		</a>
	</li>
</ul>
@endsection

@section( 'content' )
	<form action="/upload" method="POST" enctype="multipart/form-data">
		{{ csrf_field() }}

		<div class="form-group">
			<b>File Realm</b><br/>
			<input type="file" name="file" accept=".json">
		</div>

		<!--<div class="form-group">
			<b>Keterangan</b>
			<textarea class="form-control" name="keterangan"></textarea>
		</div>-->

		<input type="submit" value="Upload" class="btn btn-primary">
	</form>
@endsection

@section( 'scripts' )
 <script>
    var msg = '{{Session::get('alert')}}';
    var exist = '{{Session::has('alert')}}';
    if(exist){
      alert(msg);
    }
  </script>
@endsection  