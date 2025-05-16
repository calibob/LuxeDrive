<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Mode simulation déjà défini dans config.php

/**
 * Crée une session de paiement simulée (sans appeler l'API Stripe réelle)
 * 
 * @param int $reservation_id ID de la réservation
 * @param float $amount Montant à payer
 * @param string $customer_email Email du client
 * @return array Données de la session simulée
 */
function createStripeCheckoutSession($reservation_id, $amount, $customer_email) {
    global $pdo;
    
    try {
        // Récupérer les informations de la réservation
        $stmt = $pdo->prepare("
            SELECT r.*, v.brand, v.model, v.image 
            FROM reservations r
            JOIN vehicles v ON r.vehicle_id = v.id
            WHERE r.id = ?
        ");
        $stmt->execute([$reservation_id]);
        $reservation = $stmt->fetch();
        
        if (!$reservation) {
            return ['error' => 'Réservation introuvable'];
        }
        
        // Simuler une session de paiement
        $session_id = 'sim_' . md5($reservation_id . time() . rand(1000, 9999));
        
        // URLs de redirection
        $success_url = APP_URL . '/client/payment_success.php?session_id=' . $session_id . '&reservation_id=' . $reservation_id;
        $cancel_url = APP_URL . '/client/payment_cancel.php?reservation_id=' . $reservation_id;
        
        // En mode simulation, rediriger directement vers la page de paiement simulée
        return [
            'id' => $session_id,
            'url' => 'payment_simulation.php?session_id=' . $session_id . '&reservation_id=' . $reservation_id . '&amount=' . $amount . '&email=' . urlencode($customer_email)
        ];
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

/**
 * Vérifie le statut d'une session de paiement simulée
 * 
 * @param string $session_id ID de la session simulée
 * @return array Informations sur la session
 */
function checkStripeSession($session_id) {
    try {
        // Vérifier si l'ID de session commence par 'sim_' (session simulée)
        if (strpos($session_id, 'sim_') === 0) {
            // Extraire l'ID de réservation de la requête GET
            $reservation_id = isset($_GET['reservation_id']) ? (int)$_GET['reservation_id'] : 0;
            
            if (!$reservation_id) {
                return ['error' => 'ID de réservation manquant'];
            }
            
            // Générer un ID de transaction fictif
            $payment_intent = 'pi_sim_' . substr(md5($session_id), 0, 24);
            
            // Récupérer le montant de la réservation
            global $pdo;
            $stmt = $pdo->prepare("SELECT total_price FROM reservations WHERE id = ?");
            $stmt->execute([$reservation_id]);
            $amount = $stmt->fetchColumn();
            
            if (!$amount) {
                $amount = 0;
            }
            
            // Enregistrer le paiement dans la base de données
            savePayment($reservation_id, $amount, 'stripe_simulation', $payment_intent);
            
            // Mettre à jour le statut de la réservation
            updateReservationPaymentStatus($reservation_id, 'paid');
            
            return [
                'success' => true,
                'reservation_id' => $reservation_id,
                'amount' => $amount,
                'payment_intent' => $payment_intent
            ];
        }
        
        // Si ce n'est pas une session simulée, renvoyer une erreur
        return ['error' => 'Session de paiement non valide'];
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

/**
 * Enregistre un paiement dans la base de données
 * 
 * @param int $reservation_id ID de la réservation
 * @param float $amount Montant du paiement
 * @param string $payment_method Méthode de paiement (stripe, paypal, etc.)
 * @param string $transaction_id ID de la transaction
 * @return bool Succès de l'opération
 */
function savePayment($reservation_id, $amount, $payment_method, $transaction_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO payments (reservation_id, amount, payment_method, transaction_id, status)
            VALUES (?, ?, ?, ?, 'completed')
        ");
        
        return $stmt->execute([
            $reservation_id,
            $amount,
            $payment_method,
            $transaction_id
        ]);
    } catch (\Exception $e) {
        error_log('Erreur lors de l\'enregistrement du paiement: ' . $e->getMessage());
        return false;
    }
}

/**
 * Met à jour le statut de paiement d'une réservation
 * 
 * @param int $reservation_id ID de la réservation
 * @param string $status Nouveau statut (unpaid, paid, refunded)
 * @return bool Succès de l'opération
 */
function updateReservationPaymentStatus($reservation_id, $status) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE reservations 
            SET payment_status = ?, 
                status = CASE WHEN ? = 'paid' THEN 'confirmed' ELSE status END
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $status,
            $status,
            $reservation_id
        ]);
    } catch (\Exception $e) {
        error_log('Erreur lors de la mise à jour du statut de paiement: ' . $e->getMessage());
        return false;
    }
}

/**
 * Formate une date pour l'affichage
 * 
 * @param string $date Date au format SQL (YYYY-MM-DD)
 * @return string Date formatée (JJ/MM/YYYY)
 */
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}
?>
