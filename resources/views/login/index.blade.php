<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem Etos Kerja</title>

  <!-- favicon -->
  <link rel="icon" type="image/jpg" href="/asset/img/logo2.jpg?v=20260521">

  <!-- CSS -->
  <link rel="stylesheet" href="/css/style.css?v=20260521">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
  @php $active = session('active', 'login'); @endphp
  <main class="auth-page">
    <section class="auth-panel {{ $active }}-state" id="form-container">
      <a class="brand-badge" href="{{ route('login') }}" aria-label="SEKP">
        <img src="/asset/img/logos.png" alt="SEKP">
        <h4>SEKP</h4>
      </a>

      <form class="auth-form" id="register-form" action="{{ route('register.process') }}" method="POST">
        @csrf

        <h1>Sign Up</h1>
        <p class="form-subtitle">Create your account to start using website SEKP</p>

        <div class="input-group-label">
          <label>Name</label>
          <div class="input-field-wrapper">
            <input type="text" name="nama" value="{{ old('nama') }}" required>
            @error('nama')
              <div class="form-error">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="input-group-label">
          <label>Email</label>
          <div class="input-field-wrapper">
            <input type="email" name="email" value="{{ old('email') }}" required>
            @error('email')
              <div class="form-error">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="input-group-label">
          <label>Password</label>
          <div class="input-field-wrapper">
            <input type="password" name="password" required>
            <i class="fa-solid fa-eye-slash password-icon" aria-hidden="true"></i>
            @error('password')
              <div class="form-error">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <button type="submit" class="btn-submit">Sign up</button>

        <p class="switch-text">Already have an account? <a href="#" id="switch-to-login-bottom">Sign In</a></p>
      </form>

      <form class="auth-form" id="login-form" action="{{ route('login.process') }}" method="POST">
        @csrf

        <h1>Sign In</h1>
        <p class="form-subtitle">Sign In untuk masuk ke website SEKP</p>

        <div class="input-group-label">
          <label>Email</label>
          <div class="input-field-wrapper">
            <input type="email" name="email" value="{{ old('email') }}" required>
            @error('email')
              <div class="form-error">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="input-group-label">
          <label>Password</label>
          <div class="input-field-wrapper">
            <input type="password" name="password" required>
            <i class="fa-solid fa-eye-slash password-icon" aria-hidden="true"></i>
            @error('password')
              <div class="form-error">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="remember-me">
          <label class="remember-check">
            <input type="checkbox" name="remember">
            <span>Remember me</span>
          </label>
          <a href="#" class="forgot-link">Forgot Password</a>
        </div>

        <button id="btn_signin" type="submit" class="btn-submit">Sign In</button>


        <p class="switch-text">Don't have an account? <a href="#" id="switch-to-register-bottom">Sign up</a></p>
      </form>
    </section>

    <aside class="hero-panel">
      <div class="hero-content">
        <img class="hero-logo" src="/asset/img/logos.png" alt="SEKP">
        <h2 class="welcome-text" id="main-welcome-text">Welcome!</h2>
        <p class="tagline">Use these awesome forms to Sign In or create new account in your project for free.</p>
      </div>
    </aside>
  </main>

  <script src="/js/script.js?v=20260521"></script>

</body>

</html>
