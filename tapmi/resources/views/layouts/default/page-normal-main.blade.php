<!DOCTYPE html>
<html lang="en" >
	<head>
		<meta charset="utf-8" />
		<title>Mobile Inspection | @yield( 'title' )</title>
		<meta name="description" content="Blank inner page examples">
		<meta name="csrf-token" content="{{ csrf_token() }}" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
		<script>
			WebFont.load({
				google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700","Asap+Condensed:500"]},
				active: function() {
					sessionStorage.fonts = true;
				}
			});
		</script>
		<link href="{{ url( 'assets/default-template/assets/vendors/custom/fullcalendar/fullcalendar.bundle.css' ) }}" rel="stylesheet" type="text/css" />
		<link href="{{ url( 'assets/default-template/assets/vendors/base/vendors.bundle.css' ) }}" rel="stylesheet" type="text/css" />
		<link href="{{ url( 'assets/default-template/8/app/base/style.bundle.css' ) }}" rel="stylesheet" type="text/css" />
		<link rel="shortcut icon" href="{{ url( 'assets/favicon.ico' ) }}" />
		<link rel="stylesheet" type="text/css" href="{{ url( 'assets/vendor/jquery-loading-overlay/style.min.css' ) }}">
			@yield('style')
	</head>
	<body style="background-image: url('{{url('assets/background.jpg')}}')"  class="m-page--fluid m-page--loading-enabled m-page--loading m-header--fixed m-header--fixed-mobile m-footer--push m-aside--offcanvas-default"  >

		<div class="m-page-loader m-page-loader--base">
			<div class="m-blockui">
				<span>
					Please wait...
				</span>
				<span>
					<div class="m-loader m-loader--brand"></div>
				</span>
			</div>
		</div>

		<div class="m-grid m-grid--hor m-grid--root m-page">
			<div id="page">
				@include( 'layouts.default.page-normal-header' )
				<div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver-desktop m-grid--desktop m-page__container m-body">
					<button class="m-aside-left-close m-aside-left-close--skin-light" id="m_aside_left_close_btn">
						<i class="la la-close"></i>
					</button>
					<div class="m-grid__item m-grid__item--fluid m-wrapper">
						<div class="m-subheader ">
							<div class="d-flex align-items-center">
								<div class="mr-auto">
									<h3 class="m-subheader__title m-subheader__title--separator">
										@yield( 'title' )
									</h3>
									@yield( 'subheader' )
								</div>
								<div>@yield( 'dropdown-page' )</div>
							</div>
						</div>
						<div class="m-content" id="contents">
							@yield( 'content' )
						</div>
					</div>
				</div>
				@include( 'layouts.default.page-normal-footer' )
			</div>
			@include( 'layouts.default.page-normal-quick-sidebar' )
		</div>
		<div id="m_scroll_top" class="m-scroll-top">
			<i class="la la-arrow-up"></i>
		</div>
		<!--ul class="m-nav-sticky" style="margin-top: 30px;">
			<li class="m-nav-sticky__item" data-toggle="m-tooltip" title="Purchase" data-placement="left">
				<a href="javascript:;" target="_blank">
					<i class="la la-cart-arrow-down"></i>
				</a>
			</li>
			<li class="m-nav-sticky__item" data-toggle="m-tooltip" title="Documentation" data-placement="left">
				<a href="javascript:;" target="_blank">
					<i class="la la-code-fork"></i>
				</a>
			</li>
			<li class="m-nav-sticky__item" data-toggle="m-tooltip" title="Support" data-placement="left">
				<a href="javascript:;" target="_blank">
					<i class="la la-life-ring"></i>
				</a>
			</li>
		</ul-->

		<script src="{{ url( 'assets/default-template/assets/vendors/base/vendors.bundle.js' ) }}" type="text/javascript"></script>
		<script type="text/javascript" src="{{ url( 'assets/default-template/8/app/base/scripts.bundle.js' ) }}"></script>
		<!--script src="{{ url( 'assets/default-template/assets/base/scripts.bundle.js' ) }}" type="text/javascript"></script-->
		<script src="{{ url( 'assets/default-template/assets/vendors/custom/fullcalendar/fullcalendar.bundle.js' ) }}" type="text/javascript"></script>
		<script src="{{ url( 'assets/default-template/assets/app/js/dashboard.js' ) }}" type="text/javascript"></script>
		<script src="{{ url( 'assets/default-template/assets/custom/components/forms/widgets/select2.js' ) }}" type="text/javascript"></script>
		<script src="{{ url( 'assets/default-template/assets/custom/components/forms/widgets/bootstrap-switch.js' ) }}" type="text/javascript"></script>
		<script type="text/javascript" src="{{ url( 'assets/vendor/jquery-loading-overlay/script.min.js' ) }}"></script>
		<script type="text/javascript" src="{{ url( 'assets/mobile-inspection.js' ) }}"></script>
		@yield( 'scripts' )
		<script>
			$(window).on('load', function() {
				$('body').removeClass('m-page--loading');         
			});
		</script>
	</body>
</html>
