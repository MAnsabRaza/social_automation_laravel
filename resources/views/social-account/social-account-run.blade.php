<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Logging in to {{ ucfirst($account->platform) }}...</title>
    <style>
        body {
            font-family: Arial;
            text-align: center;
            background: #1877f2;
            color: white;
            padding-top: 15%;
        }

        .spinner {
            font-size: 50px;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <h1>Logging in as {{ $account->account_username }}</h1>
    <p>Please wait, opening {{ ucfirst($account->platform) }}...</p>
    <div class="spinner">Loading...</div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Inject saved cookies
            @if(!empty($cookies))
                @foreach($cookies as $cookie)
                    document.cookie = "{{ $cookie['name'] }}={{ $cookie['value'] }}; domain={{ $cookie['domain'] ?? '.facebook.com' }}; path={{ $cookie['path'] ?? '/' }}; expires={{ date('D, d M Y H:i:s \G\M\T', $cookie['expiry'] ?? time() + 31536000) }}; SameSite=Lax";
                @endforeach
                endforeach
            @endif

            // Redirect to platform after injecting cookies
            setTimeout(() => {
                window.location.href = "{{ $url }}";
            }, 1500);
        });
    </script>
</body>

</html>