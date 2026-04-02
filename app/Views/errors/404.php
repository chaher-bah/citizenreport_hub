<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            color: white;
        }
        .error-container {
            text-align: center;
            padding: 2rem;
        }
        h1 {
            font-size: 8rem;
            margin: 0;
            opacity: 0.5;
        }
        h2 {
            font-size: 2rem;
            margin: 1rem 0;
        }
        p {
            opacity: 0.8;
            margin-bottom: 2rem;
        }
        a {
            color: white;
            background: rgba(255,255,255,0.2);
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            transition: background 0.2s;
        }
        a:hover {
            background: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404</h1>
        <h2>Page Not Found</h2>
        <p>The page you're looking for doesn't exist.</p>
        <a href="<?= BASE_URL ?>/">Go Home</a>
    </div>
</body>
</html>
