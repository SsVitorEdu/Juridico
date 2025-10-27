<?php
// Arquivo: C:\xampp\htdocs\simulador_login.php

session_start();

// Se um tipo de login foi escolhido via link...
if (isset($_GET['login_as'])) {
    if ($_GET['login_as'] === 'gestor') {
        $_SESSION['gestor_id'] = 1;
        $_SESSION['nome_gestor'] = 'Vitor Eduardo Lima da Rocha';
        $_SESSION['nivel_acesso'] = 'gestor';
    } 
    elseif ($_GET['login_as'] === 'juridico') {
        $_SESSION['gestor_id'] = 2;
        $_SESSION['nome_gestor'] = 'Analista Jurídico';
        $_SESSION['nivel_acesso'] = 'juridico';
    }

    // Após definir a sessão, redireciona para o nosso "porteiro"
    header('Location: juridico/index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Simulador de Login</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f0f0f0; }
        .container { text-align: center; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        a { display: inline-block; padding: 15px 30px; margin: 10px; background-color: #0e4b8c; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Simulador de Login</h2>
        <p>Escolha com qual perfil você quer entrar no módulo jurídico:</p>
        <a href="simulador_login.php?login_as=gestor">Entrar como Gestor</a>
        <a href="simulador_login.php?login_as=juridico">Entrar como Jurídico</a>
    </div>
</body>
</html>