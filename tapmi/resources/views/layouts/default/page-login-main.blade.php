<!DOCTYPE html><html lang="en" >
	<head>
		<meta charset="utf-8" />
		<title>Mobile Inspection | @yield( 'title' )</title>
		<meta name="description" content="Latest updates and statistic charts">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
		<script>
			WebFont.load({
				google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},
				active: function() {
					sessionStorage.fonts = true;
				}
			});
		</script>
		<link href="{{ url( 'assets/default-template/assets/vendors/base/vendors.bundle.css' ) }}" rel="stylesheet" type="text/css" />
		<link href="{{ url( 'assets/default-template/assets/demo/default/base/style.bundle.css' ) }}" rel="stylesheet" type="text/css" />
		<link rel="stylesheet" type="text/css" href="{{ url( 'assets/vendor/jquery-loading-overlay/style.min.css' ) }}">
		<link rel="shortcut icon" href="../../../assets/demo/default/media/img/logo/favicon.ico" />
	</head>
	<body  class="m--skin- m-header--fixed m-header--fixed-mobile m-aside-left--enabled m-aside-left--skin-dark m-aside-left--offcanvas m-footer--push m-aside--offcanvas-default">
		@yield( 'content' )
	<script src="{{ url( 'assets/default-template/assets/vendors/base/vendors.bundle.js' ) }}" type="text/javascript"></script>
	<script type="text/javascript" src="{{ url( 'assets/default-template/8/app/base/scripts.bundle.js' ) }}"></script>
	<script type="text/javascript" src="{{ url( 'assets/vendor/jquery-loading-overlay/script.min.js' ) }}"></script>
	<!--script src="{{ url( 'js/login.js' ) }}" type="text/javascript"></script-->
	@yield( 'script' )
	</body>
</html>