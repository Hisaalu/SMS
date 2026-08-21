<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f7f9f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { max-width: 450px; width: 100%; padding: 2.5rem; text-align: center; background: white; border-radius: 1rem; box-shadow: 0 2px 16px rgba(0,0,0,0.05); border: 1px solid #EFF3F4; }
        .error-icon { font-size: 4rem; color: #F4212E; }
        h1 { font-size: 6rem; font-weight: 700; margin: 0; color: #F4212E; }
        .btn-primary { background: #1D9BF0; border: none; padding: 0.6rem 2rem; border-radius: 0.5rem; color: white; text-decoration: none; display: inline-block; }
        .btn-primary:hover { opacity: 0.9; color: white; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <h1>500</h1>
        <h2>Server Error</h2>
        <p class="text-muted">Something went wrong. Please try again later.</p>
        <a href="/NexaT/dashboard" class="btn-primary mt-2">Go to Dashboard</a>
    </div>
</body>
</html>