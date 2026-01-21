<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>DantePOS | Business Management System</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<style>
/* HARD RESET */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html, body {
    height: 100%;
    overflow: hidden;
}

body {
    background: #f8fafc;
    font-family: Inter, sans-serif;
}

/* VIEWPORT WRAPPER */
.viewport {
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 20px; /* horizontal margin */
}

/* CONTAINER */
.container-wrapper {
    max-width: 1200px;
    width: 100%;
}

/* TOP */
.top-bar img {
    height: 34px;
    margin-bottom: 10px;
}

/* CTA */
.top-cta {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 16px;
    border-radius: 999px;
    background: linear-gradient(135deg,#6d28d9,#4f46e5);
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    margin-bottom: 8px;
}

/* TITLES */
h1 {
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 4px;
    text-align: center;
}

.subtitle {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 16px;
    text-align: center;
}

/* FLEX ROW */
.row-wrapper {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    gap: 24px;
    flex-wrap: wrap;
}

/* CARD */
.card-box {
    background: #fff;
    border-radius: 14px;
    padding: 16px;
    box-shadow: 0 12px 30px rgba(0,0,0,.08);
}

/* DEMO */
.demo-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.demo-item {
    background: #f1f5f9;
    border-radius: 10px;
    padding: 10px 6px;
    text-align: center;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.demo-item:hover {
    transform: scale(1.05);
}

.demo-icon {
    font-size: 22px;
    margin-bottom: 2px;
}

/* LOGIN */
.login-logo img {
    height: 28px;
}

.login-title {
    font-size: 15px;
    font-weight: 700;
}

.login-sub {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 8px;
}

.form-control {
    height: 38px;
    font-size: 13px;
    background: #eef4ff;
    border: none;
}

.input-group-text {
    background: #eef4ff;
    border: none;
    cursor: pointer;
}

.login-btn {
    height: 40px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    background: #5b2d82;
    border: none;
    color: #fff;
}

.forgot-link {
    font-size: 12px;
    color: #6d28d9;
    text-decoration: none;
}

/* ALERTS */
.alert {
    font-size: 13px;
    padding: 6px 10px;
    margin-bottom: 12px;
    border-radius: 6px;
}
</style>
</head>

<body>

<div class="viewport">
<div class="container-wrapper">

    <!-- LOGO -->
    <div class="top-bar text-center">
        <img src="images/logo.png">
    </div>

    <!-- CTA -->
    <div class="text-center mb-3">
        <a class="top-cta">
            <i class="fa fa-chart-line"></i> Manage Your Business Smarter
        </a>
    </div>

    <h1>One Platform. Total Control.</h1>
    <p class="subtitle">Sales, inventory, customers & insights.</p>

    <div class="row-wrapper">

        <!-- DEMO -->
        <div class="col-lg-6 col-md-12">
            <div class="card-box">

                <div class="demo-header">
                    <i class="fa-solid fa-bullseye"></i>
                    <div>
                        <h6 class="mb-0 fw-bold">Quick Demo Access</h6>
                        <small class="text-muted">Select your industry to try the demo</small>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="demo-item"> <div class="demo-icon">🏪</div> Retail & Supermarket </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="demo-item"> <div class="demo-icon">💊</div> Pharmacy </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="demo-item"> <div class="demo-icon">🍽️</div> Restaurant </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="demo-item"> <div class="demo-icon">📱</div> Electronics </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="demo-item"> <div class="demo-icon">🛠️</div> Repair Shop </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="demo-item"> <div class="demo-icon">🔧</div> Hardware </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="demo-item"> <div class="demo-icon">👗</div> Fashion & Apparel </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="demo-item"> <div class="demo-icon">🛠️</div> Service Business </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="demo-item"> <div class="demo-icon">🏋️</div> Gym & Fitness </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="demo-item"> <div class="demo-icon">🏨</div> Hotel & Lodge </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- LOGIN -->
        <div class="col-lg-4 col-md-12">
            <div class="card-box">

                <div class="text-center mb-1 login-logo">
                    <img src="images/logo.png">
                </div>

                <div class="text-center mb-2">
                    <div class="login-title">System Login</div>
                    <div class="login-sub">Secure access</div>
                </div>

                <!-- Laravel Notifications -->
                @if(session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <!-- Laravel login form -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-2">
                        <input class="form-control" type="text" name="email" placeholder="Email" value="{{ old('email') }}" required>
                    </div>

                    <div class="input-group mb-2">
                        <input id="password" type="password" class="form-control" name="password" placeholder="Password" required>
                        <span class="input-group-text" onclick="togglePassword()">
                            <i id="eyeIcon" class="fa fa-eye"></i>
                        </span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="small"><input type="checkbox" name="remember"> Remember</label>
                        <a class="forgot-link" href="{{ route('password.request') }}">Forgot?</a>
                    </div>

                    <button type="submit" class="login-btn w-100">Login</button>
                </form>

            </div>
        </div>

    </div>
</div>
</div>

<script>
function togglePassword(){
    let p=document.getElementById("password");
    let e=document.getElementById("eyeIcon");
    if(p.type==="password"){
        p.type="text";
        e.className="fa fa-eye-slash";
    } else {
        p.type="password";
        e.className="fa fa-eye";
    }
}
</script>

</body>
</html>
