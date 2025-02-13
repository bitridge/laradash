<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laravel Application Installer</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .requirement-item {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .requirement-success {
            background-color: #d4edda;
            color: #155724;
        }
        .requirement-error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .btn-primary {
            background-color: #4a90e2;
            border-color: #357abd;
        }
        .btn-primary:hover {
            background-color: #357abd;
            border-color: #2e6da4;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4">
            <h1>Laravel Application Installer</h1>
            <p class="lead">This installer will help you set up your application</p>
        </div>
    </div>
</body>
</html>