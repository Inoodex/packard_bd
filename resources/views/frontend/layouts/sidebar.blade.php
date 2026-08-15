<!-- Sidebar -->
@php
    $user = auth()->user();
    $isSuperAdmin = $user && $user->hasRole('Super Admin');
@endphp

<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul class="sidebar-vertical">

                <!-- Dashboard -->
                <li>
                    <a href="{{ route('index') }}">
                        <i class="fe fe-grid"></i><span> Dashboard</span>
                    </a>
                </li>

                <!-- Product Management -->
                @if ($isSuperAdmin || $user->can('Product Management'))
                    <li class="menu-title"><span>Product Management</span></li>
                    <li>
                        <a href="{{ route('products.index') }}"
                            class="{{ Route::currentRouteName() == 'products.index' ? 'active' : '' }}">
                            <i class="fe fe-package"></i> <span> Product List</span>
                        </a>
                    </li>
                @endif

                <!-- Quotation Management -->
                @if ($isSuperAdmin || $user->can('Quotation Management'))
                    <li class="menu-title"><span>Quotation Management</span></li>
                    <li>
                        <a href="{{ route('quotations.index') }}"
                            class="{{ Route::currentRouteName() == 'quotations.index' ? 'active' : '' }}">
                            <i class="fe fe-plus-circle"></i> <span>Quotation Generate</span>
                        </a>
                    </li>
                @endif

                <!-- Client Management -->
                @if ($isSuperAdmin || $user->can('Client Management'))
                    <li class="menu-title"><span>Client Management</span></li>
                    <li>
                        <a href="{{ route('clients.index') }}"
                            class="{{ Route::currentRouteName() == 'clients.index' ? 'active' : '' }}">
                            <i class="fe fe-package"></i> <span>Clients List</span>
                        </a>
                    </li>
                @endif

                {{-- Company Details --}}
                @if ($isSuperAdmin || $user->can('Company Management'))
                    <li class="menu-title"><span>Company Management</span></li>
                    <li>
                        <a href="{{ route('company.index') }}"
                            class="{{ Route::currentRouteName() == 'company.index' ? 'active' : '' }}">
                            <i class="fe fe-home"></i> <span>Company Details</span>
                        </a>
                    </li>
                @endif

                <!-- Administration / Authorization -->
                @if ($isSuperAdmin || $user->can('Administration'))
                    <li class="menu-title"><span>Authorization</span></li>
                    <li>
                        <a href="{{ route('permission.index') }}"
                            class="{{ Route::currentRouteName() == 'permission.index' ? 'active' : '' }}">
                            <i class="fe fe-lock"></i> <span>Permissions</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('role.index') }}"
                            class="{{ Route::currentRouteName() == 'role.index' ? 'active' : '' }}">
                            <i class="fe fe-shield"></i> <span>Roles</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('users.index') }}"
                            class="{{ Route::currentRouteName() == 'users.index' ? 'active' : '' }}">
                            <i class="fe fe-user"></i> <span>Users</span>
                        </a>
                    </li>
                @endif

            </ul>
        </div>
    </div>
</div>
<!-- /Sidebar -->
