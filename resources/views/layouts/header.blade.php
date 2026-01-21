<nav class="main-header navbar navbar-expand navbar-gray navbar-dark">

    <!-- LEFT NAVBAR -->
    <ul class="navbar-nav">

        <!-- Sidebar toggle -->
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>

        <!-- Welcome -->
        <li class="nav-item d-none d-md-inline-block ml-3">
            <span class="nav-link text-white font-weight-bold">
                Welcome {{ Auth::user()->name ?? 'User' }} 👋
            </span>
        </li>

        <!-- Role badge -->
        <li class="nav-item d-none d-md-inline-block">
            <span class="badge badge-info mt-2">
                {{ ucfirst(Auth::user()->role ?? 'User') }}
            </span>
        </li>

    </ul>

    <!-- RIGHT NAVBAR -->
    <ul class="navbar-nav ml-auto">

    <!-- POS Location Display -->
<li class="nav-item d-none d-md-inline-block">
    <span class="form-control form-control-sm mt-1 bg-dark text-white border-0 text-center">
        Matiliku
    </span>
</li>


        </li>

        <!-- Date -->
        <li class="nav-item d-none d-md-inline-block">
            <span class="nav-link text-white">
                <i class="far fa-calendar-alt"></i>
                {{ \Carbon\Carbon::now()->format('d/m/Y') }}
            </span>
        </li>

        <!-- Live Clock -->
        <li class="nav-item d-none d-md-inline-block">
            <span class="nav-link text-white" id="liveClock">
                <i class="far fa-clock"></i> --:--:--
            </span>
        </li>

        <!-- Notifications -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <span class="badge badge-danger navbar-badge">3</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <span class="dropdown-item dropdown-header">3 Notifications</span>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-receipt mr-2"></i> New Sale
                </a>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-box mr-2"></i> Stock Low
                </a>
            </div>
        </li>

        <!-- USER DROPDOWN WITH PHOTO -->
        <li class="nav-item dropdown user-menu">
            <a class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown" href="#">

                <img src="{{ Auth::user()->photo 
                            ? asset('storage/' . Auth::user()->photo) 
                            : asset('images/avatar5.png') }}"
                     class="user-image img-circle elevation-2"
                     alt="User Image">

                <span class="d-none d-md-inline text-white ml-2">
                    {{ Auth::user()->name ?? 'User' }}
                </span>
            </a>

            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">

                <!-- User image -->
                <li class="user-header bg-dark">
                    <img src="{{ Auth::user()->photo 
                                ? asset('storage/' . Auth::user()->photo) 
                                : asset('images/avatar5.png') }}"
                         class="img-circle elevation-2"
                         alt="User Image">

                    <p>
                        {{ Auth::user()->name ?? 'User' }}
                        <small>{{ ucfirst(Auth::user()->role ?? 'User') }}</small>
                    </p>
                </li>

                <!-- Menu Footer -->
                <li class="user-footer">
                    <a href="{{ url('/user-profile') }}" class="btn btn-default btn-flat">
                        Profile
                    </a>
                    <a href="{{ url('/logout') }}"
                       class="btn btn-danger btn-flat float-right"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Logout
                    </a>

                    <form id="logout-form" action="{{ url('/logout') }}" method="get" style="display:none;">
                        @csrf
                    </form>
                </li>
            </ul>
        </li>

    </ul>
</nav>

<!-- LIVE CLOCK -->
<script>
    function updateClock() {
        const now = new Date();
        document.getElementById('liveClock').innerHTML =
            '<i class="far fa-clock"></i> ' + now.toLocaleTimeString();
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
