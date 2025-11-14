<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign Up | Create Account</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Toastify -->
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

  <style>
    /* Floating Label Animation */
    .input-group {
      position: relative;
      margin-bottom: 1.5rem;
    }
    .input-group input {
      width: 100%;
      padding: 1rem 1rem 0.5rem;
      border: 1px solid #d1d5db;
      border-radius: 0.5rem;
      font-size: 1rem;
      outline: none;
      transition: all 0.3s ease;
      background: transparent;
    }
    .input-group input:focus {
      border-color: #1d4ed8;
      box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
    }
    .input-group label {
      position: absolute;
      top: 1rem;
      left: 1rem;
      font-size: 1rem;
      color: #6b7280;
      pointer-events: none;
      transition: all 0.3s ease;
      transform-origin: 0 0;
    }
    .input-group input:focus ~ label,
    .input-group input:not(:placeholder-shown) ~ label {
      top: 0.5rem;
      left: 1rem;
      font-size: 0.75rem;
      color: #1d4ed8;
      font-weight: 500;
    }

    /* Back Button Hover */
    #backBtn:hover p {
      color: #1d4ed8;
    }
  </style>
</head>
<body class="bg-gray-50">
  <div class="flex min-h-screen">
    <!-- Left Side - Full Background Image -->
    <div class="hidden lg:block w-full lg:w-1/2 bg-cover bg-center bg-no-repeat relative"
         style="background-image: url('{{ asset('assets/images/home.jpg') }}');">
      <div class="absolute inset-0 bg-gradient-to-br from-black/70 to-black/40"></div>
      <div class="relative z-10 flex flex-col items-center justify-center h-full text-white px-12">
        <h1 class="text-5xl font-bold mb-6 leading-tight">Welcome to Your Future</h1>
        <p class="text-xl text-center max-w-md leading-relaxed">
          Join thousands of users and unlock powerful tools to grow your ideas, business, and creativity.
        </p>
      </div>
    </div>

    <!-- Right Side - Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-16">
      <div class="w-full max-w-md">
        <!-- Back Button -->
        <div class="flex items-center gap-2 cursor-pointer mb-8" id="backBtn">
          <span class="text-2xl text-gray-600">←</span>
          <p class="text-lg text-gray-700 font-medium">Back</p>
        </div>

        <!-- Title -->
        <h2 class="text-5xl font-extrabold text-center bg-gradient-to-r from-blue-900 to-indigo-600 bg-clip-text text-transparent">
          SIGN UP
        </h2>
        <p class="text-center text-gray-600 mt-3 text-lg">Create your account to get started!</p>

        <!-- Form -->
        <form method="POST" action="{{ route('createUser') }}" class="mt-10" id="signupForm">
          @csrf

          <!-- Username -->
          <div class="input-group">
            <input type="text" id="name" name="name" placeholder=" " required />
            <label for="name">Username</label>
          </div>

          <!-- Email -->
          <div class="input-group">
            <input type="email" id="email" name="email" placeholder=" " required />
            <label for="email">Email Address</label>
          </div>

          <!-- Password -->
          <div class="input-group relative">
            <input type="password" id="password" name="password" placeholder=" " required />
            <label for="password">Password</label>
            <span id="togglePassword"
                  class="absolute right-3 top-5 text-gray-500 cursor-pointer text-lg">
              <i class="fa-solid fa-eye" id="eyeIcon"></i>
            </span>
          </div>

          <!-- Submit Button -->
          <button type="submit"
                  class="w-full bg-gradient-to-r from-blue-900 to-indigo-700 text-white py-4 rounded-xl font-semibold text-lg 
                         hover:from-blue-800 hover:to-indigo-600 transform transition-all duration-200 
                         hover:scale-[1.02] shadow-lg hover:shadow-xl">
            Create Account
          </button>
        </form>

        <!-- Divider -->
        <div class="flex items-center gap-4 my-8">
          <div class="flex-grow border-t border-gray-300"></div>
          <p class="text-sm font-semibold text-gray-600">Or continue with</p>
          <div class="flex-grow border-t border-gray-300"></div>
        </div>

        <!-- Login Link -->
        <p class="text-center text-gray-600">
          Already have an account?
          <a href="{{ route('showSignUp') }}" class="text-blue-600 font-semibold hover:underline">
            Log In
          </a>
        </p>
      </div>
    </div>
  </div>

  <script>
    // Toggle Password Visibility
    $('#togglePassword').on('click', function () {
      const input = $('#password');
      const eyeIcon = $('#eyeIcon');
      if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
      } else {
        input.attr('type', 'password');
        eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
      }
    });

    // Form Validation with Toastify
    $("#signupForm").submit(function (e) {
      e.preventDefault();
      const name = $("#name");
      const email = $("#email");
      const password = $("#password");
      let valid = true;

      [name, email, password].forEach(function (field) {
        if (!field.val().trim()) {
          field.addClass("border-red-500").removeClass("border-gray-300");
          Toastify({
            text: field.siblings('label').text() + " is required",
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "linear-gradient(to right, #ff5f6d, #ff9966)"
          }).showToast();
          valid = false;
        } else {
          field.removeClass("border-red-500").addClass("border-gray-300");
        }
      });

      if (valid) {
        this.submit();
      }
    });

    // Laravel Session Messages
    @if(session('success'))
      Toastify({
        text: "{{ session('success') }}",
        duration: 4000,
        gravity: "top",
        position: "right",
        backgroundColor: "#4BB543"
      }).showToast();
    @endif

    @if(session('error'))
      Toastify({
        text: "{{ session('error') }}",
        duration: 4000,
        gravity: "top",
        position: "right",
        backgroundColor: "#FF0000"
      }).showToast();
    @endif
  </script>
</body>
</html>