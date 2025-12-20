<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <div class="app-sidebar-logo px-6 mt-2" id="kt_app_sidebar_logo">
        <a href="{{ route('inicio.index') }}" class="m-auto">
            <img alt="Logo" src="{{ asset('assets/media/logo-unia.webp') }}" class="h-50px app-sidebar-logo-default" />
        </a>
    </div>
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer" data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px" data-kt-scroll-save-state="true">
                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="true">
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('inicio.index') ? 'active' : '' }}" href="{{ route('inicio.index') }}">
                            <span class="menu-icon">
                                <i class="ki-outline ki-burger-menu-2 fs-2"></i>
                            </span>
                            <span class="menu-title">Inicio</span>
                        </a>
                    </div>
                    <div class="menu-item pt-5">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Módulos</span>
                        </div>
                    </div>
                    @if(count($menusPermitidos) > 0)
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ request()->routeIs('seguridad.*') ? 'here show' : '' }}">

                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ki-outline ki-key fs-2"></i>
                            </span>
                            <span class="menu-title">Seguridad</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            @if(isset($menusPermitidos['MENÚ']))
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs($menusPermitidos['MENÚ']['routePattern'].'.*') || request()->routeIs($menusPermitidos['MENÚ']['routePattern']) ? 'active' : '' }}" href="{{ route($menusPermitidos['MENÚ']['ruta']) }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{ $menusPermitidos['MENÚ']['nombre'] }}</span>
                                </a>
                            </div>
                            @endif

                            @if(isset($menusPermitidos['USUARIOS']))
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs($menusPermitidos['USUARIOS']['routePattern'].'.*') || request()->routeIs($menusPermitidos['USUARIOS']['routePattern']) ? 'active' : '' }}" href="{{ route($menusPermitidos['USUARIOS']['ruta']) }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{ $menusPermitidos['USUARIOS']['nombre'] }}</span>
                                </a>
                            </div>
                            @endif

                            @if(isset($menusPermitidos['ROLES']))
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs($menusPermitidos['ROLES']['routePattern'].'.*') || request()->routeIs($menusPermitidos['ROLES']['routePattern']) ? 'active' : '' }}" href="{{ route($menusPermitidos['ROLES']['ruta']) }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{ $menusPermitidos['ROLES']['nombre'] }}</span>
                                </a>
                            </div>
                            @endif

                            @if(isset($menusPermitidos['PERSONAS']))
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs($menusPermitidos['PERSONAS']['routePattern'].'.*') || request()->routeIs($menusPermitidos['PERSONAS']['routePattern']) ? 'active' : '' }}" href="{{ route($menusPermitidos['PERSONAS']['ruta']) }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{ $menusPermitidos['PERSONAS']['nombre'] }}</span>
                                </a>
                            </div>
                            @endif

                            @if(isset($menusPermitidos['CATALOGO']))
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs($menusPermitidos['CATALOGO']['routePattern'].'.*') || request()->routeIs($menusPermitidos['CATALOGO']['routePattern']) ? 'active' : '' }}" href="{{ route($menusPermitidos['CATALOGO']['ruta']) }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{ $menusPermitidos['CATALOGO']['nombre'] }}</span>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ request()->routeIs('documentos.*') ? 'here show' : '' }}">

                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ki-outline ki-key fs-2"></i>
                            </span>
                            <span class="menu-title">Documentos</span>
                            <span class="menu-arrow"></span>
                        </span>

                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('documentos.documento.*') || request()->routeIs('documentos.documento.index') ? 'active' : '' }}" href="{{ route('documentos.documento.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Mis documentos</span>
                                </a>
                            </div>

                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('documentos.pendientes.*') || request()->routeIs('documentos.pendientes.index') ? 'active' : '' }}" href="{{ route('documentos.pendientes.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>

                                    <span class="menu-title">Pendientes</span>

                                    <livewire:components.navegacion.sidebar-badge />
                                </a>
                            </div>

                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('documentos.historial.*') || request()->routeIs('documentos.historial.index') ? 'active' : '' }}" href="{{ route('documentos.historial.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Historial</span>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
