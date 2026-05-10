<?php
// ecobiteweb/Controller/MailController.php

require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailController {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->setupMail();
    }
    
    private function setupMail() {
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'rayensouissi321@gmail.com';
        $this->mail->Password = 'eooy tsrb eeiu wsty';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
        $this->mail->setFrom('rayensouissi321@gmail.com', 'AllergieCare');
        $this->mail->isHTML(true);
        $this->mail->CharSet = 'UTF-8';
    }
    
    public function sendAllergieEmail($destinataire, $nomProche, $allergie, $traitements, $messagePerso = '') {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($destinataire);
            $this->mail->Subject = '📧 ' . $nomProche . ' vous a partagé une fiche allergie';
            
            $body = $this->generateEmailContent($nomProche, $allergie, $traitements, $messagePerso);
            $this->mail->Body = $body;
            $this->mail->AltBody = strip_tags($body);
            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Erreur email: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    private function generateEmailContent($nomProche, $allergie, $traitements, $messagePerso) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Fiche allergie</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; background: #f9fafb; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
                .content { padding: 30px; background: white; }
                .allergie-card { background: #f0fdf4; border-left: 4px solid #10b981; padding: 20px; margin: 20px 0; border-radius: 8px; }
                .traitement-item { background: #f3f4f6; padding: 15px; margin: 15px 0; border-radius: 8px; }
                .message-perso { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; }
                .footer { background: #f3f4f6; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; }
                .gravite { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; }
                .gravite-grave { background: #fee2e2; color: #dc2626; }
                .gravite-moyenne { background: #fed7aa; color: #ea580c; }
                .gravite-faible { background: #d1fae5; color: #059669; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>📋 Fiche allergie médicale</h1>
                    <p><?= htmlspecialchars($nomProche) ?> a pensé à vous</p>
                </div>
                <div class="content">
                    <?php if (!empty($messagePerso)): ?>
                        <div class="message-perso">
                            <strong>💬 Message :</strong><br>
                            <?= nl2br(htmlspecialchars($messagePerso)) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="allergie-card">
                        <h2 style="color: #059669;">🌿 <?= htmlspecialchars($allergie['nom']) ?></h2>
                        <?php
                            $gravite = strtolower($allergie['gravite'] ?? 'non définie');
                            $graviteClass = match($gravite) {
                                'grave' => 'gravite-grave',
                                'moyenne' => 'gravite-moyenne',
                                'faible' => 'gravite-faible',
                                default => ''
                            };
                        ?>
                        <span class="gravite <?= $graviteClass ?>">⚠️ Gravité : <?= htmlspecialchars($allergie['gravite'] ?? 'Non définie') ?></span>
                        
                        <h3>📝 Description</h3>
                        <p><?= nl2br(htmlspecialchars($allergie['description'] ?? 'Aucune description')) ?></p>
                        
                        <h3>🤧 Symptômes</h3>
                        <p><?= nl2br(htmlspecialchars($allergie['symptomes'] ?? 'Aucun symptôme')) ?></p>
                    </div>
                    
                    <h3>💊 Traitements associés</h3>
                    <?php if (count($traitements) > 0): ?>
                        <?php foreach ($traitements as $i => $t): ?>
                            <div class="traitement-item">
                                <strong><?= $i+1 ?> . <?= htmlspecialchars($t['nom_traitement']) ?></strong>
                                <?php if (!empty($t['conseils'])): ?>
                                    <p><strong>📌 Conseils :</strong> <?= nl2br(htmlspecialchars($t['conseils'])) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($t['interdiction'])): ?>
                                    <p><strong>🚫 Interdictions :</strong> <?= nl2br(htmlspecialchars($t['interdiction'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun traitement répertorié pour cette allergie.</p>
                    <?php endif; ?>
                    
                    <p style="font-size: 12px; color: #6b7280; margin-top: 20px;">
                        Ces informations sont fournies à titre indicatif.<br>
                        Consultez toujours un professionnel de santé.
                    </p>
                </div>
                <div class="footer">
                    <p>Email envoyé via AllergieCare</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
?>