<div
    id="kt_app_sidebar"
    class="app-sidebar flex-column"
    data-kt-drawer="true"
    data-kt-drawer-name="app-sidebar"
    data-kt-drawer-activate="{default: true, lg: false}"
    data-kt-drawer-overlay="true"
    data-kt-drawer-width="225px"
    data-kt-drawer-direction="start"
    data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle"
>
    <div class="app-sidebar-logo px-6 mt-2" id="kt_app_sidebar_logo">
        <a href="{{ route('inicio.index') }}" class="m-auto">
            <img alt="Logo" src="{{ asset('assets/media/logo-unia.webp') }}" class="h-50px app-sidebar-logo-default" />
        </a>
    </div>
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <div
                id="kt_app_sidebar_menu_scroll"
                class="scroll-y my-5 mx-3"
                data-kt-scroll="true"
                data-kt-scroll-activate="true"
                data-kt-scroll-height="auto"
                data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                data-kt-scroll-wrappers="#kt_app_sidebar_menu"
                data-kt-scroll-offset="5px"
                data-kt-scroll-save-state="true"
            >
                <div
                    class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6"
                    id="#kt_app_sidebar_menu"
                    data-kt-menu="true"
                    data-kt-menu-expand="false"
                >
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
                    <div
                        data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ request()->routeIs('seguridad.*') ? 'here show' : '' }}"
                    >
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ki-outline ki-key fs-2"></i>
                            </span>
                            <span class="menu-title">Seguridad</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a
                                    class="menu-link {{ request()->routeIs('seguridad.usuario.*') || request()->routeIs('seguridad.usuario') ? 'active' : '' }}"
                                    href="{{ route('seguridad.usuario.index') }}"
                                >
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Usuarios</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a
                                    class="menu-link {{ request()->routeIs('seguridad.rol.*') || request()->routeIs('seguridad.rol') ? 'active' : '' }}"
                                    href="{{ route('seguridad.rol.index') }}"
                                >
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Roles</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
