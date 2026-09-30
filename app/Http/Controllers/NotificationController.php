<?php

namespace App\Http\Controllers;

use App\Models\InterventionNotification;
use App\Events\InterventionNotificationCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $notifications = InterventionNotification::where('user_id', $user->id)
            ->with(['intervention', 'demande', 'maintenance'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $nonLuesCount = InterventionNotification::where('user_id', $user->id)
            ->where('statut', 'non_lu')
            ->count();
        
        return view('notifications.index', compact('notifications', 'nonLuesCount'));
    }
    
    public function markAsRead($id)
    {
        $notification = InterventionNotification::findOrFail($id);
        
        // Vérifier que la notification appartient à l'utilisateur
        if ($notification->user_id != Auth::id()) {
            return redirect()->route('notifications.index')->with('error', 'Cette notification ne vous appartient pas.');
        }
        
        $notification->statut = 'lu';
        $notification->save();
        
        // Rediriger vers la demande, l'intervention ou la maintenance
        if ($notification->maintenance_id) {
            return redirect()->route('maintenances.show', $notification->maintenance_id);
        } elseif ($notification->demande_id) {
            return redirect()->route('demandes.show', $notification->demande_id);
        } elseif ($notification->intervention_id) {
            return redirect()->route('depannages.show', $notification->intervention_id);
        }
        
        return redirect()->route('notifications.index');
    }
    
    public function markAllAsRead(Request $request = null)
    {
        InterventionNotification::where('user_id', Auth::id())
            ->where('statut', 'non_lu')
            ->update([
                'statut' => 'lu',
                'updated_at' => now()
            ]);
        
        if (request()->wantsJson() || request()->ajax() || request()->isJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }
    
    /**
     * Créer une notification
     * @param int $userId - ID de l'utilisateur à notifier
     * @param int|null $interventionId - ID de l'intervention (optionnel)
     * @param string $type - Type de notification
     * @param string $message - Message principal (titre)
     * @param string|int|null $demandeIdOrDescription - Peut être demande_id (int) OU description complémentaire (string)
     */
    public static function create($userId, $interventionId, $type, $message, $demandeIdOrDescription = null)
    {
        // Déterminer si le 5ème paramètre est un demande_id (int) ou une description (string)
        $demandeId = null;
        $fullMessage = $message;
        
        if ($demandeIdOrDescription !== null) {
            if (is_numeric($demandeIdOrDescription)) {
                // C'est un demande_id
                $demandeId = (int)$demandeIdOrDescription;
            } else {
                // C'est une description complémentaire, on la combine avec le message
                $fullMessage = $message . ' - ' . $demandeIdOrDescription;
            }
        }
        
        $notification = InterventionNotification::create([
            'user_id' => $userId,
            'intervention_id' => $interventionId,
            'demande_id' => $demandeId,
            'type' => $type,
            'message' => $fullMessage,
            'statut' => 'non_lu',
        ]);

        try {
            $event = new InterventionNotificationCreated($notification);
            if (function_exists('broadcast')) {
                $broadcast = broadcast($event);
                if (method_exists($broadcast, 'toOthers')) {
                    $broadcast->toOthers();
                }
            }
        } catch (\Throwable $e) {
        }

        return $notification;
    }
    
    /**
     * Vérifier s'il y a de nouvelles notifications (pour polling)
     */
    public function checkNew(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'has_new'       => false,
                'count'         => 0,
                'notifications' => []
            ]);
        }

        $lastNotifId = $request->input('last_notif_id');
        $totalUnread = InterventionNotification::where('user_id', $user->id)
            ->where('statut', 'non_lu')
            ->count();

        // Récupérer les notifications non lues les plus récentes (max 15)
        $unreadQuery = InterventionNotification::where('user_id', $user->id)
            ->where('statut', 'non_lu')
            ->orderBy('id', 'desc');

        $unreadNotifications = (clone $unreadQuery)->take(15)->get();

        // Déterminer s'il y a de nouvelles notifications par rapport à lastNotifId
        $hasNew = false;
        if ($lastNotifId && is_numeric($lastNotifId) && (int)$lastNotifId > 0) {
            $hasNew = InterventionNotification::where('user_id', $user->id)
                ->where('statut', 'non_lu')
                ->where('id', '>', (int)$lastNotifId)
                ->exists();
        } else {
            $hasNew = $totalUnread > 0;
        }

        return response()->json([
            'has_new'       => $hasNew,
            'count'         => $totalUnread,
            'notifications' => $unreadNotifications->map(function($notif) {
                return [
                    'id'                   => $notif->id,
                    'type'                 => $notif->type,
                    'message'              => $notif->message,
                    'demande_id'           => $notif->demande_id,
                    'intervention_id'      => $notif->intervention_id,
                    'maintenance_id'       => $notif->maintenance_id,
                    'created_at'           => $notif->created_at->diffForHumans(),
                    'created_at_timestamp' => $notif->created_at->toIso8601String(),
                ];
            })
        ]);
    }
}
