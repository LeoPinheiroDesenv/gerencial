<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home') }}">
                <i class="fas fa-home"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>

        <!-- Menu Tecnospeed -->
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#tecnospeed" role="button" aria-expanded="false" aria-controls="tecnospeed">
                <i class="fas fa-file-invoice"></i>
                <span class="menu-title">Tecnospeed</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="tecnospeed">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('tecnospeed.config.show') }}">
                            Configurações
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('tecnospeed.nfes.index') }}">
                            NFes
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- Menu Plug4Market -->
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#plug4market" role="button" aria-expanded="false" aria-controls="plug4market">
                <i class="fas fa-plug"></i>
                <span class="menu-title">Plug4Market</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="plug4market">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('plug4market.products.index') }}">
                            <i class="fas fa-box"></i>
                            <span class="menu-title">Produtos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('plug4market.categories.index') }}">
                            <i class="fas fa-tags"></i>
                            <span class="menu-title">Categorias</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('plug4market.orders.index') }}">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="menu-title">Pedidos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('plug4market.settings.index') }}">
                            <i class="fas fa-cog"></i>
                            <span class="menu-title">Configurações</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- Outros menus do sistema -->
        @if(auth()->user()->hasRole('admin'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('users.index') }}">
                    <i class="fas fa-users"></i>
                    <span class="menu-title">Usuários</span>
                </a>
            </li>
        @endif

        <li class="nav-item">
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <i class="fas fa-user"></i>
                <span class="menu-title">Perfil</span>
            </a>
        </li>
    </ul>
</nav> 