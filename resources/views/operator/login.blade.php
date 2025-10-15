<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Operator</title>
    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --bg-light: #f5f6fa;
            --text-dark: #2f3542;
            --border-color: #dcdde1;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: var(--bg-light);
            position: relative;
            overflow: hidden;
        }

        /* Background Pattern */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(52,152,219,0.05) 0%, rgba(52,152,219,0) 70%);
            z-index: 0;
            animation: rotate 60s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .login-container {
            background: white;
            padding: 35px 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            width: 400px;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        h2 {
            color: var(--text-dark);
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        label {
            display: block;
            color: var(--text-dark);
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            color: var(--text-dark);
            background: white;
        }

        input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 4px rgba(52,152,219,0.1);
        }

        button {
            width: 100%;
            padding: 16px;
            margin-top: 15px;
            background: transparent;
            border: 2px solid var(--primary);
            border-radius: 30px;
            color: var(--primary);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.5px;
            z-index: 1;
        }

        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--primary);
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s ease;
            z-index: -1;
        }

        button:hover {
            color: white;
            box-shadow: 0 5px 15px rgba(52,152,219,0.2);
        }

        button:hover::before {
            transform: scaleX(1);
            transform-origin: left;
        }

        button:active {
            transform: translateY(2px);
            box-shadow: 0 2px 8px rgba(52,152,219,0.1);
        }

        .error {
            background: rgba(231,76,60,0.1);
            color: #e74c3c;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            border: 1px solid rgba(231,76,60,0.2);
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .login-container {
                width: 90%;
                padding: 25px;
                margin: 20px;
            }
        }

        /* Login Header Style */
        .login-header {
            margin-bottom: 25px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-header">
            <h2>Login Operator</h2>
        </div>

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('operator.login.post') }}">
            @csrf

            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="Masukkan email anda">
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Masukkan password anda">
            </div>

            <button type="submit">
                Masuk ke Dashboard
            </button>
        </form>
    </div>

</body>
</html>
