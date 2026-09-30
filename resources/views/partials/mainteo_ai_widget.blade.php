@auth
@if(!request()->routeIs('ai.*'))
<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- 🤖 WIDGET FLOTTANT MAINTEO IA AVEC HISTORIQUE ET SÉCURITÉ PAR RÔLE   -->
<!-- ══════════════════════════════════════════════════════════════════════ -->

<style>
/* ─── Bouton Flottant ────────────────────────────────────────────── */
.mainteo-ai-trigger {
    position: fixed;
    bottom: 25px;
    right: 25px;
    z-index: 99998;
    background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
    color: #ffffff;
    border: none;
    border-radius: 50px;
    padding: 12px 22px 12px 16px;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 0.3px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.5), 0 8px 10px -6px rgba(16, 185, 129, 0.3);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.mainteo-ai-trigger:hover {
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 15px 30px -5px rgba(16, 185, 129, 0.6), 0 10px 12px -5px rgba(16, 185, 129, 0.4);
    color: #ffffff;
}

.mainteo-ai-trigger .ai-icon-badge {
    width: 34px;
    height: 34px;
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(4px);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    position: relative;
}

.mainteo-ai-trigger .online-dot {
    position: absolute;
    bottom: 0px;
    right: 0px;
    width: 10px;
    height: 10px;
    background-color: #34d399;
    border: 2px solid #059669;
    border-radius: 50%;
}

/* ─── Fenêtre de Chat Flottante ───────────────────────────────────── */
.mainteo-ai-card {
    position: fixed;
    bottom: 90px;
    right: 25px;
    width: min(520px, calc(100vw - 30px));
    height: 620px;
    max-height: calc(100vh - 110px);
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.25), 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    z-index: 99999;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    opacity: 0;
    transform: translateY(20px) scale(0.95);
    pointer-events: none;
    border: 1px solid rgba(0, 0, 0, 0.08);
}

.mainteo-ai-card.active {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: auto;
}

/* En-tête de la fenêtre */
.ai-card-header {
    background: linear-gradient(135deg, #065f46 0%, #047857 100%);
    color: #ffffff;
    padding: 16px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.ai-header-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.ai-avatar {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.3);
}

.ai-header-title {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    line-height: 1.2;
}

.ai-header-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    background: rgba(255, 255, 255, 0.25);
    padding: 2px 8px;
    border-radius: 12px;
    margin-top: 3px;
    letter-spacing: 0.2px;
}

.ai-header-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}

.ai-btn-icon {
    background: rgba(255, 255, 255, 0.15);
    border: none;
    color: #ffffff;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 13px;
    transition: background 0.2s ease;
}

.ai-btn-icon:hover {
    background: rgba(255, 255, 255, 0.3);
}

/* ─── Panneau Historique (tiroir) ─────────────────────────────────── */
.ai-history-drawer {
    position: absolute;
    top: 68px;
    bottom: 0;
    left: 0;
    width: 100%;
    background: #f8fafc;
    z-index: 10;
    transform: translateX(-100%);
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    padding: 16px;
}

.ai-history-drawer.open {
    transform: translateX(0);
}

.ai-history-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
    font-weight: 700;
    color: #1e293b;
    font-size: 14px;
}

.ai-history-list {
    flex: 1;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.ai-history-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.2s ease;
}

.ai-history-item:hover {
    border-color: #10b981;
    background: #f0fdf4;
}

.ai-history-item.active {
    border-color: #10b981;
    background: #ecfdf5;
}

.ai-history-title {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 260px;
}

.ai-history-time {
    font-size: 11px;
    color: #64748b;
}

.ai-history-del-btn {
    border: none;
    background: transparent;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px;
    border-radius: 6px;
    font-size: 12px;
}

.ai-history-del-btn:hover {
    color: #ef4444;
    background: #fee2e2;
}

/* ─── Zone des Messages (Corps) ──────────────────────────────────── */
.ai-card-body {
    flex: 1;
    overflow-y: auto;
    padding: 18px 16px;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.ai-bubble-row {
    display: flex;
    gap: 10px;
    max-width: 90%;
}

.ai-bubble-row.user {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.ai-bubble-row.assistant {
    align-self: flex-start;
    max-width: 96%;
}

.ai-bubble {
    padding: 12px 16px;
    border-radius: 16px;
    font-size: 13.5px;
    line-height: 1.5;
    word-break: break-word;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.ai-bubble-row.user .ai-bubble {
    background: #059669;
    color: #ffffff;
    border-bottom-right-radius: 4px;
}

.ai-bubble-row.assistant .ai-bubble {
    background: #ffffff;
    color: #1e293b;
    border-bottom-left-radius: 4px;
    border: 1px solid #e2e8f0;
}

.ai-bubble-avatar {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
    margin-top: 4px;
}

.ai-bubble-avatar.bot {
    background: #e0f2fe;
    color: #0284c7;
}

.ai-bubble-avatar.usr {
    background: #ecfdf5;
    color: #059669;
}

.ai-bubble-time {
    font-size: 10px;
    opacity: 0.7;
    margin-top: 4px;
    text-align: right;
}

/* Styles des tableaux et éléments riches dans les bulles */
.ai-table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin: 10px 0;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    max-width: 100%;
}

.ai-bubble table {
    width: 100%;
    min-width: 260px;
    border-collapse: collapse;
    font-size: 12px;
    text-align: left;
}

.ai-bubble th {
    background: #047857;
    color: #ffffff;
    padding: 8px 11px;
    font-weight: 600;
    white-space: nowrap;
    border-bottom: 2px solid #065f46;
}

.ai-bubble td {
    padding: 7px 11px;
    border-bottom: 1px solid #e2e8f0;
    color: #1e293b;
    vertical-align: middle;
}

.ai-bubble tr:nth-child(even) td {
    background: #f8fafc;
}

.ai-bubble tr:hover td {
    background: #ecfdf5;
}

.ai-bubble blockquote {
    border-left: 3px solid #10b981;
    margin: 8px 0;
    padding: 4px 10px;
    background: rgba(16, 185, 129, 0.08);
    border-radius: 0 6px 6px 0;
    font-size: 12.5px;
    color: #065f46;
}

.ai-bubble ul {
    margin: 6px 0 6px 18px;
    padding: 0;
}

.ai-bubble li {
    margin-bottom: 3px;
}


/* Suggestions rapides */
.ai-suggestions-box {
    margin-top: 8px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.ai-suggestion-chip {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #0f172a;
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 500;
    cursor: pointer;
    text-align: left;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.ai-suggestion-chip:hover {
    background: #ecfdf5;
    border-color: #10b981;
    color: #065f46;
    transform: translateX(3px);
}

/* Indicateur de frappe (Typing indicator) */
.ai-typing-indicator {
    display: none;
    align-items: center;
    gap: 5px;
    padding: 10px 14px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    width: fit-content;
    border-bottom-left-radius: 4px;
}

.ai-typing-dot {
    width: 6px;
    height: 6px;
    background: #10b981;
    border-radius: 50%;
    animation: aiTypingBounce 1.4s infinite ease-in-out both;
}

.ai-typing-dot:nth-child(1) { animation-delay: -0.32s; }
.ai-typing-dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes aiTypingBounce {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
}

/* ─── Pied de page (Saisie) ──────────────────────────────────────── */
.ai-card-footer {
    padding: 12px 14px;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.ai-input-field {
    flex: 1;
    border: 1px solid #cbd5e1;
    border-radius: 24px;
    padding: 10px 16px;
    font-size: 13.5px;
    outline: none;
    transition: border 0.2s ease;
    background: #f8fafc;
}

.ai-input-field:focus {
    border-color: #10b981;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}

.ai-send-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: #059669;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    cursor: pointer;
    transition: transform 0.2s ease, background 0.2s ease;
    flex-shrink: 0;
}

.ai-send-btn:hover {
    background: #047857;
    transform: scale(1.06);
}

.ai-send-btn:disabled {
    background: #cbd5e1;
    cursor: not-allowed;
    transform: none;
}
</style>

<!-- ─── 1. BOUTON FLOTTANT FIXE EN BAS À DROITE ──────────────────────── -->
<button type="button" class="mainteo-ai-trigger" id="mainteoAiTrigger" onclick="toggleMainteoAi()">
    <div class="ai-icon-badge">
        ✨
        <span class="online-dot"></span>
    </div>
    <span>Mainteo IA</span>
</button>

<!-- ─── 2. FENÊTRE DE CHAT MODERNE ───────────────────────────────────── -->
<div class="mainteo-ai-card" id="mainteoAiCard">
    <!-- En-tête -->
    <div class="ai-card-header">
        <div class="ai-header-info">
            <div class="ai-avatar">🤖</div>
            <div>
                <h4 class="ai-header-title">Mainteo IA</h4>
                <span class="ai-header-badge">
                    @php
                        $roleName = match(Auth::user()->type_utilisateur) {
                            'admin'                => 'Administrateur',
                            'superviseur_soutarah' => 'Sup. Soutarah',
                            'superviseur_client'   => 'Sup. Client',
                            'chef technicien'      => 'Chef Équipe',
                            'technicien'           => 'Technicien',
                            'demandeur'            => 'Demandeur',
                            default                => ucfirst(Auth::user()->type_utilisateur)
                        };
                    @endphp
                    {{ $roleName }} • Connecté
                </span>
            </div>
        </div>
        <div class="ai-header-actions">
            <a href="{{ route('ai.index') }}" class="ai-btn-icon" title="Ouvrir en plein écran" style="text-decoration: none; color: inherit; display: inline-flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-up-right-and-down-left-from-center"></i>
            </a>
            <button type="button" class="ai-btn-icon" title="Historique des conversations" onclick="toggleAiHistory()">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </button>
            <button type="button" class="ai-btn-icon" title="Nouvelle conversation" onclick="startNewAiConversation()">
                <i class="fa-solid fa-plus"></i>
            </button>
            <button type="button" class="ai-btn-icon" title="Fermer" onclick="toggleMainteoAi()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>

    <!-- Tiroir Historique -->
    <div class="ai-history-drawer" id="aiHistoryDrawer">
        <div class="ai-history-header">
            <span>📜 Vos discussions passées</span>
            <button type="button" class="btn btn-sm btn-link text-muted p-0" onclick="toggleAiHistory()">Fermer</button>
        </div>
        <div class="ai-history-list" id="aiHistoryList">
            <div class="text-center text-muted small py-4">Chargement de l'historique...</div>
        </div>
    </div>

    <!-- Corps des messages -->
    <div class="ai-card-body" id="aiMessagesContainer">
        <!-- Message de bienvenue initial -->
        <div class="ai-bubble-row assistant">
            <div class="ai-bubble-avatar bot"><i class="fa-solid fa-robot"></i></div>
            <div class="ai-bubble">
                Bonjour <strong>{{ Auth::user()->prenom ?? Auth::user()->nom }}</strong> ! Je suis <strong>Mainteo IA</strong>, votre assistant intelligent chez Soutarah.
                <br><br>
                J'ai accès à vos informations autorisées en temps réel. Que souhaitez-vous savoir ?

                <!-- Suggestions rapides contextuelles selon le rôle -->
                <div class="ai-suggestions-box">
                    @if(in_array(Auth::user()->type_utilisateur, ['technicien', 'chef technicien']))
                        <button type="button" class="ai-suggestion-chip" onclick="sendAiPrompt('Quelles sont mes interventions prévues ?')">
                            🛠️ <span>Quelles sont mes interventions prévues ?</span>
                        </button>
                        <button type="button" class="ai-suggestion-chip" onclick="sendAiPrompt('Aide-moi à rédiger un rapport d intervention')">
                            ✍️ <span>Aide-moi à rédiger un rapport d'intervention</span>
                        </button>
                        <button type="button" class="ai-suggestion-chip" onclick="sendAiPrompt('Comment diagnostiquer un problème de climatisation ?')">
                            ❄️ <span>Conseil diagnostic froid / climatisation</span>
                        </button>
                    @elseif(Auth::user()->type_utilisateur === 'demandeur')
                        <button type="button" class="ai-suggestion-chip" onclick="sendAiPrompt('Où en sont mes demandes en cours ?')">
                            📋 <span>Où en sont mes demandes en cours ?</span>
                        </button>
                        <button type="button" class="ai-suggestion-chip" onclick="sendAiPrompt('Mon climatiseur coule de l eau, que faire ?')">
                            ⚠️ <span>Mon climatiseur coule de l'eau, que faire ?</span>
                        </button>
                    @else
                        <button type="button" class="ai-suggestion-chip" onclick="sendAiPrompt('Fais-moi un résumé de la situation actuelle des interventions')">
                            📊 <span>Résumé de la situation des interventions</span>
                        </button>
                        <button type="button" class="ai-suggestion-chip" onclick="sendAiPrompt('Y a-t-il des demandes VIP urgentes à traiter ?')">
                            🚨 <span>Y a-t-il des demandes VIP urgentes ?</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Indicateur de chargement / frappe -->
        <div class="ai-bubble-row assistant ai-typing-indicator" id="aiTypingIndicator">
            <div class="ai-bubble-avatar bot"><i class="fa-solid fa-robot"></i></div>
            <div style="display: flex; gap: 4px; align-items: center;">
                <div class="ai-typing-dot"></div>
                <div class="ai-typing-dot"></div>
                <div class="ai-typing-dot"></div>
            </div>
        </div>
    </div>

    <!-- Pied de page (Saisie) -->
    <div class="ai-card-footer">
        <input type="text" 
               id="aiUserInput" 
               class="ai-input-field" 
               placeholder="Posez votre question à Mainteo IA..." 
               autocomplete="off" 
               onkeydown="if(event.key === 'Enter') handleAiSend()">
        <button type="button" id="aiSendBtn" class="ai-send-btn" onclick="handleAiSend()">
            <i class="fa-solid fa-paper-plane"></i>
        </button>
    </div>
</div>

<!-- ─── 3. LOGIQUE JAVASCRIPT DU WIDGET ──────────────────────────────── -->
<script>
let currentAiConversationId = null;
let initialAiMessagesHtml = null;

function saveInitialAiState() {
    if (!initialAiMessagesHtml) {
        const container = document.getElementById('aiMessagesContainer');
        if (container) {
            initialAiMessagesHtml = container.innerHTML;
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    saveInitialAiState();
});

function toggleMainteoAi() {
    saveInitialAiState();
    const card = document.getElementById('mainteoAiCard');
    if (!card) return;
    card.classList.toggle('active');
    if (card.classList.contains('active')) {
        document.getElementById('aiUserInput')?.focus();
        // Si aucune conversation n'est chargée, charger l'historique en cache
        loadAiConversationsList();
    }
}

function toggleAiHistory() {
    const drawer = document.getElementById('aiHistoryDrawer');
    if (drawer) {
        drawer.classList.toggle('open');
        if (drawer.classList.contains('open')) {
            loadAiConversationsList();
        }
    }
}

function startNewAiConversation() {
    currentAiConversationId = null;
    const container = document.getElementById('aiMessagesContainer');
    const drawer = document.getElementById('aiHistoryDrawer');
    if (drawer) drawer.classList.remove('open');

    // Retirer la surbrillance active dans l'historique
    document.querySelectorAll('.ai-history-item').forEach(el => el.classList.remove('active'));

    // Réinitialiser le corps des messages avec le message d'accueil initial SANS recharger la page
    saveInitialAiState();
    if (container && initialAiMessagesHtml) {
        container.innerHTML = initialAiMessagesHtml;
    }

    const input = document.getElementById('aiUserInput');
    if (input) {
        input.value = '';
        input.focus();
    }
}

function sendAiPrompt(text) {
    const input = document.getElementById('aiUserInput');
    if (input) {
        input.value = text;
        handleAiSend();
    }
}

function handleAiSend() {
    const input = document.getElementById('aiUserInput');
    const btn   = document.getElementById('aiSendBtn');
    const text  = input?.value?.trim();

    if (!text) return;

    // Désactiver la saisie pendant l'envoi
    input.value = '';
    input.disabled = true;
    btn.disabled = true;

    // 1. Ajouter immédiatement la bulle de l'utilisateur
    appendAiMessage('user', text, new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}));

    // 2. Afficher l'indicateur de frappe
    const typing = document.getElementById('aiTypingIndicator');
    if (typing) {
        typing.style.display = 'flex';
        scrollAiMessagesToBottom();
    }

    // 3. Envoyer la requête au backend
    fetch('{{ route("ai.chat") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            message: text,
            conversation_id: currentAiConversationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (typing) typing.style.display = 'none';

        if (data.success) {
            currentAiConversationId = data.conversation_id;
            appendAiMessage('assistant', data.ai_message.content, data.ai_message.created_at);
        } else {
            appendAiMessage('assistant', "Une erreur est survenue lors de la communication avec l'assistant. Veuillez réessayer.", "");
        }
    })
    .catch(error => {
        console.error('Erreur Mainteo IA:', error);
        if (typing) typing.style.display = 'none';
        appendAiMessage('assistant', "Désolé, impossible de joindre le serveur. Vérifiez votre connexion internet.", "");
    })
    .finally(() => {
        input.disabled = false;
        btn.disabled = false;
        input.focus();
    });
}

function appendAiMessage(role, content, time) {
    const container = document.getElementById('aiMessagesContainer');
    const typing = document.getElementById('aiTypingIndicator');
    if (!container) return;

    const row = document.createElement('div');
    row.className = `ai-bubble-row ${role}`;

    const avatar = document.createElement('div');
    avatar.className = `ai-bubble-avatar ${role === 'user' ? 'usr' : 'bot'}`;
    avatar.innerHTML = role === 'user' ? '<i class="fa-solid fa-user"></i>' : '<i class="fa-solid fa-robot"></i>';

    const bubble = document.createElement('div');
    bubble.className = 'ai-bubble';

    if (role === 'user') {
        bubble.innerHTML = escapeHtml(content).replace(/\n/g, '<br>');
    } else {
        bubble.innerHTML = formatAiMarkdown(content);
    }

    if (time) {
        const timeDiv = document.createElement('div');
        timeDiv.className = 'ai-bubble-time';
        timeDiv.textContent = time;
        bubble.appendChild(timeDiv);
    }

    row.appendChild(avatar);
    row.appendChild(bubble);

    if (typing) {
        container.insertBefore(row, typing);
    } else {
        container.appendChild(row);
    }

    scrollAiMessagesToBottom();
}

/**
 * Convertisseur Markdown complet : Tableaux HTML, Listes, Citations, Gras, Italique, Code
 */
function formatAiMarkdown(text) {
    if (!text) return '';
    const lines = text.split(/\r?\n/);
    let html = '';
    let inTable = false;
    let tableHeaders = [];
    let tableAlignments = [];
    let tableRows = [];
    let inList = false;
    let listItems = [];
    let inQuote = false;
    let quoteLines = [];

    function flushList() {
        if (inList) {
            html += '<ul style="margin: 6px 0 6px 18px; padding: 0;">' + 
                listItems.map(item => '<li style="margin-bottom: 3px;">' + formatInline(item) + '</li>').join('') + 
                '</ul>';
            listItems = [];
            inList = false;
        }
    }

    function flushQuote() {
        if (inQuote) {
            html += '<blockquote>' + quoteLines.map(l => formatInline(l)).join('<br>') + '</blockquote>';
            quoteLines = [];
            inQuote = false;
        }
    }

    function flushTable() {
        if (inTable) {
            let tHtml = '<div class="ai-table-wrapper"><table>';
            if (tableHeaders.length > 0) {
                tHtml += '<thead><tr>';
                tableHeaders.forEach((h, i) => {
                    const align = tableAlignments[i] || 'left';
                    tHtml += `<th style="text-align:${align};">${formatInline(h)}</th>`;
                });
                tHtml += '</tr></thead>';
            }
            if (tableRows.length > 0) {
                tHtml += '<tbody>';
                tableRows.forEach(row => {
                    tHtml += '<tr>';
                    row.forEach((cell, i) => {
                        const align = tableAlignments[i] || 'left';
                        tHtml += `<td style="text-align:${align};">${formatInline(cell)}</td>`;
                    });
                    tHtml += '</tr>';
                });
                tHtml += '</tbody>';
            }
            tHtml += '</table></div>';
            html += tHtml;

            inTable = false;
            tableHeaders = [];
            tableAlignments = [];
            tableRows = [];
        }
    }

    function isTableRow(line) {
        const trimmed = line.trim();
        return trimmed.includes('|') && !trimmed.startsWith('```');
    }

    function isTableSeparator(line) {
        const trimmed = line.trim();
        if (!trimmed.includes('|')) return false;
        const inner = trimmed.replace(/^[|]/, '').replace(/[|]$/, '').replace(/\s/g, '');
        return /^[:\-|]+$/.test(inner) && inner.includes('-');
    }

    function parseCells(line) {
        const trimmed = line.trim();
        let content = trimmed;
        if (content.startsWith('|')) content = content.slice(1);
        if (content.endsWith('|')) content = content.slice(0, -1);
        return content.split('|').map(c => c.trim());
    }

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        const trimmed = line.trim();

        // 1. Détection des tableaux
        if (isTableRow(trimmed)) {
            flushList();
            flushQuote();

            if (!inTable) {
                if (i + 1 < lines.length && isTableSeparator(lines[i + 1])) {
                    inTable = true;
                    tableHeaders = parseCells(trimmed);
                    const sepCells = parseCells(lines[i + 1].trim());
                    tableAlignments = sepCells.map(c => {
                        if (c.startsWith(':') && c.endsWith(':')) return 'center';
                        if (c.endsWith(':')) return 'right';
                        return 'left';
                    });
                    i++; // Sauter la ligne de séparation
                    continue;
                } else {
                    inTable = true;
                    tableRows.push(parseCells(trimmed));
                    continue;
                }
            } else {
                tableRows.push(parseCells(trimmed));
                continue;
            }
        } else {
            flushTable();
        }

        // 2. Détection des citations Markdown (> texte)
        if (trimmed.startsWith('>')) {
            flushList();
            inQuote = true;
            quoteLines.push(trimmed.replace(/^>\s?/, ''));
            continue;
        } else {
            flushQuote();
        }

        // 3. Détection des listes (- item ou * item)
        if (/^[-*]\s+/.test(trimmed)) {
            inList = true;
            listItems.push(trimmed.replace(/^[-*]\s+/, ''));
            continue;
        } else {
            flushList();
        }

        // 4. Titres Markdown (### Titre)
        if (/^#{1,4}\s+/.test(trimmed)) {
            const titleText = trimmed.replace(/^#{1,4}\s+/, '');
            html += `<div style="font-weight: 700; color: #065f46; margin: 8px 0 4px; font-size: 13.5px;">${formatInline(titleText)}</div>`;
            continue;
        }

        // 5. Saut de ligne / espace
        if (trimmed === '') {
            html += '<div style="height: 6px;"></div>';
            continue;
        }

        // 6. Paragraphe régulier
        html += `<p style="margin: 4px 0;">${formatInline(line)}</p>`;
    }

    flushTable();
    flushList();
    flushQuote();

    return html;
}

function formatInline(text) {
    if (!text) return '';
    return text
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/`([^`]+)`/g, '<code style="background: #f1f5f9; color: #047857; padding: 2px 5px; border-radius: 4px; font-size: 11.5px; font-family: monospace;">$1</code>');
}

function scrollAiMessagesToBottom() {
    const container = document.getElementById('aiMessagesContainer');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

function loadAiConversationsList() {
    fetch('{{ route("ai.conversations.index") }}')
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById('aiHistoryList');
            if (!list) return;

            if (data.conversations && data.conversations.length > 0) {
                list.innerHTML = '';
                data.conversations.forEach(c => {
                    const item = document.createElement('div');
                    item.className = `ai-history-item ${currentAiConversationId === c.id ? 'active' : ''}`;
                    item.innerHTML = `
                        <div style="flex: 1;" onclick="loadAiConversationMessages(${c.id})">
                            <div class="ai-history-title">${escapeHtml(c.titre)}</div>
                            <div class="ai-history-time">${c.created_at}</div>
                        </div>
                        <button type="button" class="ai-history-del-btn" title="Supprimer" onclick="deleteAiConversation(event, ${c.id})">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    `;
                    list.appendChild(item);
                });
            } else {
                list.innerHTML = '<div class="text-center text-muted small py-4">Aucune discussion enregistrée.</div>';
            }
        })
        .catch(err => console.error('Erreur chargement historique:', err));
}

function loadAiConversationMessages(id) {
    currentAiConversationId = id;
    toggleAiHistory(); // Fermer le tiroir

    fetch(`{{ url('/') }}/ai/conversations/${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.messages) {
                const container = document.getElementById('aiMessagesContainer');
                const typing = document.getElementById('aiTypingIndicator');
                if (!container) return;

                // Vider les anciens messages sauf typing indicator
                container.innerHTML = '';
                if (typing) container.appendChild(typing);

                data.messages.forEach(m => {
                    appendAiMessage(m.role, m.content, m.created_at);
                });
            }
        })
        .catch(err => console.error('Erreur chargement conversation:', err));
}

function deleteAiConversation(e, id) {
    e.stopPropagation();
    if (!confirm('Supprimer cette discussion ?')) return;

    fetch(`{{ url('/') }}/ai/conversations/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (currentAiConversationId === id) {
                startNewAiConversation();
            } else {
                loadAiConversationsList();
            }
        }
    })
    .catch(err => console.error('Erreur suppression:', err));
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endif
@endauth
