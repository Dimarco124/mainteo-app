<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiAssistantController extends Controller
{
    protected GeminiService $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    /**
     * Affiche la page principale dédiée à Mainteo IA (vue complète responsive)
     */
    public function index()
    {
        $conversations = AiConversation::where('user_id', Auth::id())
            ->with(['lastMessage'])
            ->orderBy('updated_at', 'desc')
            ->take(30)
            ->get();

        return view('ai.index', compact('conversations'));
    }

    /**
     * Récupère la liste des conversations de l'utilisateur connecté
     */
    public function getConversations()
    {
        try {
            $conversations = AiConversation::where('user_id', Auth::id())
                ->with(['lastMessage'])
                ->orderBy('updated_at', 'desc')
                ->take(20)
                ->get()
                ->map(function ($c) {
                    return [
                        'id'           => $c->id,
                        'titre'        => $c->titre ?: 'Discussion du ' . $c->created_at->format('d/m/Y H:i'),
                        'last_message' => Str::limit($c->lastMessage?->content ?? '', 50),
                        'created_at'   => $c->created_at->diffForHumans(),
                    ];
                });

            return response()->json([
                'success'       => true,
                'conversations' => $conversations,
            ]);
        } catch (\Throwable $e) {
            Log::error('Erreur getConversations: ' . $e->getMessage());
            return response()->json([
                'success'       => true,
                'conversations' => [],
            ]);
        }
    }

    /**
     * Récupère les messages d'une conversation
     */
    public function getMessages($id)
    {
        try {
            $conversation = AiConversation::where('id', $id)
                ->where('user_id', Auth::id())
                ->with(['messages'])
                ->firstOrFail();

            return response()->json([
                'success'      => true,
                'conversation' => [
                    'id'    => $conversation->id,
                    'titre' => $conversation->titre,
                ],
                'messages'     => $conversation->messages->map(function ($m) {
                    return [
                        'id'         => $m->id,
                        'role'       => $m->role,
                        'content'    => $m->content,
                        'created_at' => $m->created_at->format('H:i'),
                    ];
                }),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Discussion introuvable.',
            ], 404);
        }
    }

    /**
     * Envoie un message et reçoit la réponse de Mainteo IA
     */
    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'message'         => 'required|string|max:2000',
                'conversation_id' => 'nullable|integer',
            ]);

            $user = Auth::user();
            $messageText = trim($request->input('message'));
            $conversationId = $request->input('conversation_id');

            // Récupérer ou créer la conversation
            $conversation = null;
            if ($conversationId) {
                $conversation = AiConversation::where('id', $conversationId)
                    ->where('user_id', $user->id)
                    ->first();
            }

            if (!$conversation) {
                $conversation = AiConversation::create([
                    'user_id' => $user->id,
                    'titre'   => Str::limit($messageText, 40),
                ]);
            }

            // Sauvegarder le message de l'utilisateur
            $userAiMessage = AiMessage::create([
                'conversation_id' => $conversation->id,
                'user_id'         => $user->id,
                'role'            => 'user',
                'content'         => $messageText,
            ]);

            // Récupérer l'historique récent de la conversation
            $history = [];
            try {
                $history = AiMessage::where('conversation_id', $conversation->id)
                    ->where('id', '!=', $userAiMessage->id)
                    ->orderBy('created_at', 'desc')
                    ->take(8)
                    ->get()
                    ->reverse()
                    ->map(function ($m) {
                        return [
                            'role'    => $m->role,
                            'content' => $m->content,
                        ];
                    })
                    ->values()
                    ->toArray();
            } catch (\Throwable $th) {
                Log::warning('Erreur chargement historique messages: ' . $th->getMessage());
            }

            // Appel au service Gemini (ou fallback interne)
            $aiResponseText = $this->gemini->chat($user, $messageText, $history);

            // Sauvegarder la réponse de l'assistant
            $assistantAiMessage = AiMessage::create([
                'conversation_id' => $conversation->id,
                'user_id'         => $user->id,
                'role'            => 'assistant',
                'content'         => $aiResponseText,
            ]);

            // Mettre à jour la date de conversation
            $conversation->touch();

            return response()->json([
                'success'         => true,
                'conversation_id' => $conversation->id,
                'user_message'    => [
                    'id'         => $userAiMessage->id,
                    'role'       => 'user',
                    'content'    => $userAiMessage->content,
                    'created_at' => $userAiMessage->created_at->format('H:i'),
                ],
                'ai_message'      => [
                    'id'         => $assistantAiMessage->id,
                    'role'       => 'assistant',
                    'content'    => $assistantAiMessage->content,
                    'created_at' => $assistantAiMessage->created_at->format('H:i'),
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('Erreur AiAssistant sendMessage: ' . $e->getMessage() . ' à la ligne ' . $e->getLine());

            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprime une conversation et ses messages
     */
    public function deleteConversation($id)
    {
        try {
            $conversation = AiConversation::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $conversation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Conversation supprimée avec succès.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
