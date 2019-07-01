@if ( Storage::disk( 'local' )->exists( 'public/menu-01.html' ) == true )
<ul class="m-menu__nav  m-menu__nav--dropdown-submenu-arrow ">
			<li class="m-menu__section">
				<h4 class="m-menu__section-text">
					DEPARTMENTS
				</h4>
				<i class="m-menu__section-icon flaticon-more-v3"></i>
			</li>
	<li class="m-menu__item " aria-haspopup="true"  m-menu-link-redirect="1">
		<a  href="inner2.html" class="m-menu__link ">
			<i class="m-menu__link-bullet m-menu__link-bullet--dot">
				<span></span>
			</i>
			<span class="m-menu__link-text">
				Finance
			</span>
		</a>
	</li>

	<li class="m-menu__item  m-menu__item--submenu" aria-haspopup="true"  m-menu-submenu-toggle="hover" m-menu-link-redirect="1">
		<a  href="javascript:;" class="m-menu__link m-menu__toggle">
			<i class="m-menu__link-bullet m-menu__link-bullet--dot">
				<span></span>
			</i>
			<span class="m-menu__link-title">
				<span class="m-menu__link-wrap">
					<span class="m-menu__link-text">
						Support
					</span>
					<span class="m-menu__link-badge">
						<span class="m-badge m-badge--danger">
							23
						</span>
					</span>
				</span>
			</span>
			<i class="m-menu__ver-arrow la la-angle-right"></i>
		</a>
		<div class="m-menu__submenu ">
			<span class="m-menu__arrow"></span>
			<ul class="m-menu__subnav">
				<li class="m-menu__item " aria-haspopup="true"  m-menu-link-redirect="1">
					<a  href="inner.html" class="m-menu__link ">
						<i class="m-menu__link-bullet m-menu__link-bullet--dot">
							<span></span>
						</i>
						<span class="m-menu__link-text">
							Reports
						</span>
					</a>
				</li>
				<li class="m-menu__item  m-menu__item--parent" aria-haspopup="true"  m-menu-link-redirect="1">
					<span class="m-menu__link">
						<span class="m-menu__link-title">
							<span class="m-menu__link-wrap">
								<span class="m-menu__link-text">
									Support
								</span>
								<span class="m-menu__link-badge">
									<span class="m-badge m-badge--danger">
										23
									</span>
								</span>
							</span>
						</span>
					</span>
				</li>
				<li class="m-menu__item " aria-haspopup="true"  m-menu-link-redirect="1">
					<a  href="inner.html" class="m-menu__link ">
						<i class="m-menu__link-bullet m-menu__link-bullet--dot">
							<span></span>
						</i>
						<span class="m-menu__link-text">
							Reports
						</span>
					</a>
				</li>
				<li class="m-menu__item  m-menu__item--submenu" aria-haspopup="true"  m-menu-submenu-toggle="hover" m-menu-link-redirect="1">
					<a  href="javascript:;" class="m-menu__link m-menu__toggle">
						<i class="m-menu__link-bullet m-menu__link-bullet--dot">
							<span></span>
						</i>
						<span class="m-menu__link-text">
							Cases
						</span>
						<i class="m-menu__ver-arrow la la-angle-right"></i>
					</a>
					<div class="m-menu__submenu ">
						<span class="m-menu__arrow"></span>
						<ul class="m-menu__subnav">
							<li class="m-menu__item " aria-haspopup="true"  m-menu-link-redirect="1">
								<a  href="inner.html" class="m-menu__link ">
									<i class="m-menu__link-bullet m-menu__link-bullet--line">
										<span></span>
									</i>
									<span class="m-menu__link-title">
										<span class="m-menu__link-wrap">
											<span class="m-menu__link-text">
												Pending
											</span>
										</span>
									</span>
								</a>
							</li>
							<li class="m-menu__item " aria-haspopup="true"  m-menu-link-redirect="1">
								<a  href="inner.html" class="m-menu__link ">
									<i class="m-menu__link-bullet m-menu__link-bullet--line">
										<span></span>
									</i>
									<span class="m-menu__link-title">
										<span class="m-menu__link-wrap">
											<span class="m-menu__link-text">
												Urgent
											</span>
											<span class="m-menu__link-badge">
												<span class="m-badge m-badge--danger">
													6
												</span>
											</span>
										</span>
									</span>
								</a>
							</li>
							<li class="m-menu__item " aria-haspopup="true"  m-menu-link-redirect="1">
								<a  href="inner.html" class="m-menu__link ">
									<i class="m-menu__link-bullet m-menu__link-bullet--line">
										<span></span>
									</i>
									<span class="m-menu__link-title">
										<span class="m-menu__link-wrap">
											<span class="m-menu__link-text">
												Done
											</span>
											<span class="m-menu__link-badge">
												<span class="m-badge m-badge--success">
													2
												</span>
											</span>
										</span>
									</span>
								</a>
							</li>
							<li class="m-menu__item " aria-haspopup="true"  m-menu-link-redirect="1">
								<a  href="inner.html" class="m-menu__link ">
									<i class="m-menu__link-bullet m-menu__link-bullet--line">
										<span></span>
									</i>
									<span class="m-menu__link-title">
										<span class="m-menu__link-wrap">
											<span class="m-menu__link-text">
												Archive
											</span>
											<span class="m-menu__link-badge">
												<span class="m-badge m-badge--info m-badge--wide">
													245
												</span>
											</span>
										</span>
									</span>
								</a>
							</li>
						</ul>
					</div>
				</li>
				<li class="m-menu__item " aria-haspopup="true"  m-menu-link-redirect="1">
					<a  href="inner.html" class="m-menu__link ">
						<i class="m-menu__link-bullet m-menu__link-bullet--dot">
							<span></span>
						</i>
						<span class="m-menu__link-text">
							Clients
						</span>
					</a>
				</li>
				<li class="m-menu__item " aria-haspopup="true"  m-menu-link-redirect="1">
					<a  href="inner.html" class="m-menu__link ">
						<i class="m-menu__link-bullet m-menu__link-bullet--dot">
							<span></span>
						</i>
						<span class="m-menu__link-text">
							Audit
						</span>
					</a>
				</li>
			</ul>
		</div>
	</li>
</ul>
@endif