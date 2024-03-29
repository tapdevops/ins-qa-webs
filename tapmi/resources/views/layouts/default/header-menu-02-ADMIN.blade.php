<ul id="master-menu" class="m-menu__nav  m-menu__nav--submenu-arrow ">
    <li id="_0201000000_" class="m-menu__item   m-menu__item--submenu m-menu__item--tabs" m-menu-submenu-toggle="tab"
        aria-haspopup="true"><a href="javascript:;" class="m-menu__link m-menu__toggle"><span
                class="m-menu__link-text">Dashboard</span><i class="m-menu__hor-arrow la la-angle-down"></i><i
                class="m-menu__ver-arrow la la-angle-right"></i></a>
        <div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--left m-menu__submenu--tabs"><span
                class="m-menu__arrow m-menu__arrow--adjust"></span>
            <ul class="m-menu__subnav">
                <li class="m-menu__item " m-menu-link-redirect="1" aria-haspopup="true"><a
                        href={{URL::to('/dashboard"')}} class="m-menu__link "><i
                            class="m-menu__link-icon fa fa-dashboard"></i><span
                            class="m-menu__link-text">Dashboard</span></a></li>
            </ul>
        </div>
    </li>
    <li id="_0203000000_" class="m-menu__item   m-menu__item--submenu m-menu__item--tabs" m-menu-submenu-toggle="tab"
        aria-haspopup="true"><a href="javascript:;" class="m-menu__link m-menu__toggle"><span
                class="m-menu__link-text">Report</span><i class="m-menu__hor-arrow la la-angle-down"></i><i
                class="m-menu__ver-arrow la la-angle-right"></i></a>
        <div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--left m-menu__submenu--tabs"><span
                class="m-menu__arrow m-menu__arrow--adjust"></span>
            <ul class="m-menu__subnav">
                <li class="m-menu__item " m-menu-link-redirect="1" aria-haspopup="true"><a
                        href={{URL::to('/report-oracle/download')}} class="m-menu__link "><i
                            class="m-menu__link-icon fa fa-cloud-download"></i><span
                            class="m-menu__link-text">Download</span></a></li>
            </ul>
        </div>
    </li>
    <li id="_0205000000_" class="m-menu__item   m-menu__item--submenu m-menu__item--tabs" m-menu-submenu-toggle="tab"
        aria-haspopup="true"><a href="javascript:;" class="m-menu__link m-menu__toggle"><span
                class="m-menu__link-text">User</span><i class="m-menu__hor-arrow la la-angle-down"></i><i
                class="m-menu__ver-arrow la la-angle-right"></i></a>
        <div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--left m-menu__submenu--tabs"><span
                class="m-menu__arrow m-menu__arrow--adjust"></span>
            <ul class="m-menu__subnav">
                <li class="m-menu__item " m-menu-link-redirect="1" aria-haspopup="true"><a
                        href={{ URL::to('/user') }} class="m-menu__link "><i
                            class="m-menu__link-icon fa fa-th-list"></i><span class="m-menu__link-text">Data</span></a>
                </li>
                <li class="m-menu__item " m-menu-link-redirect="1" aria-haspopup="true"><a
                        href={{ URL::to('/user/create') }} class="m-menu__link "><i
                            class="m-menu__link-icon fa fa-plus"></i><span class="m-menu__link-text">Tambah
                            User</span></a></li>
            </ul>
        </div>
    </li>
	<li id="_0206000000_" class="m-menu__item   m-menu__item--submenu m-menu__item--tabs" m-menu-submenu-toggle="tab"
        aria-haspopup="true"><a href="javascript:;" class="m-menu__link m-menu__toggle"><span
                class="m-menu__link-text">Upload</span><i class="m-menu__hor-arrow la la-angle-down"></i><i
                class="m-menu__ver-arrow la la-angle-right"></i></a>
        <div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--left m-menu__submenu--tabs"><span
                class="m-menu__arrow m-menu__arrow--adjust"></span>
            <ul class="m-menu__subnav">
                <li class="m-menu__item " m-menu-link-redirect="1" aria-haspopup="true"><a
                        href={{URL::to('/upload')}} class="m-menu__link "><i
                            class="m-menu__link-icon fa fa-cloud-upload"></i><span
                            class="m-menu__link-text">Upload Realm</span></a></li>
				<li class="m-menu__item " m-menu-link-redirect="1" aria-haspopup="true"><a
                        href={{ URL::to('/upload/photo') }} class="m-menu__link "><i
                            class="m-menu__link-icon fa fa-zip"></i><span 
							class="m-menu__link-text">Upload Photo</span></a></li>			
            </ul>
        </div>
    </li>
	<li id="_0207000000_" class="m-menu__item   m-menu__item--submenu m-menu__item--tabs" m-menu-submenu-toggle="tab"
        aria-haspopup="true"><a href="javascript:;" class="m-menu__link m-menu__toggle"><span
                class="m-menu__link-text">Master Data</span><i class="m-menu__hor-arrow la la-angle-down"></i><i
                class="m-menu__ver-arrow la la-angle-right"></i></a>
        <div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--left m-menu__submenu--tabs"><span
                class="m-menu__arrow m-menu__arrow--adjust"></span>
            <ul class="m-menu__subnav">
                <li class="m-menu__item " m-menu-link-redirect="1" aria-haspopup="true"><a
                        href={{URL::to('/master/category-finding')}} class="m-menu__link "><i
                            class="m-menu__link-icon fa fa-database"></i><span
                            class="m-menu__link-text">Category Finding</span></a></li>	
            </ul>
        </div>
    </li>
</ul>
