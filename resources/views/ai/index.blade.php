@extends('layouts.app')

@section('title', 'Mainteo IA — Copilote GMAO')

@section('content')
<div class="ai-page-wrapper">
    <!-- Overlay Mobile pour la barre latérale des discussions -->
    <div class="ai-mobile-overlay" id="aiMobileOverlay" onclick="closeAiSidebar()"></div>

    <!-- 1. BARRE LATÉRALE : Discussions & Historique -->
    <aside class="ai-conversations-sidebar" id="aiConversationsSidebar">
        <div class="ai-sidebar-top">
            <button type="button" class="btn-new-chat" onclick="startNewChat()">
                <i class="fa-solid fa-plus"></i>
                <span>Nouvelle discussion</span>
            </button>
            <button type="button" class="btn-close-sidebar-mobile" onclick="closeAiSidebar()" title="Fermer le menu">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="ai-sidebar-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="aiConvSearch" placeholder="Rechercher une discussion..." oninput="filterConversations()">
        </div>

        <div class="ai-conversations-list" id="aiConversationsList">
            @forelse($conversations as $c)
                <div class="ai-conv-item" id="conv-item-{{ $c->id }}" onclick="loadConversation({{ $c->id }})">
                    <div class="ai-conv-icon">
                        <i class="fa-regular fa-message"></i>
                    </div>
                    <div class="ai-conv-info">
                        <div class="ai-conv-title">{{ $c->titre ?: 'Discussion du ' . $c->created_at->format('d/m H:i') }}</div>
                        <div class="ai-conv-snippet">{{ Str::limit($c->lastMessage?->content ?? 'Aucun message', 38) }}</div>
                    </div>
                    <button type="button" class="btn-delete-conv" onclick="deleteConversation(event, {{ $c->id }})" title="Supprimer">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            @empty
                <div class="ai-conv-empty" id="aiEmptyConvs">
                    <i class="fa-regular fa-comments"></i>
                    <p>Aucune discussion enregistrée pour le moment.</p>
                </div>
            @endforelse
        </div>

        <div class="ai-sidebar-footer">
            <div class="ai-user-badge">
                <div class="user-avatar-circle">
                    {{ strtoupper(substr(Auth::user()->prenom ?? 'U', 0, 1) . substr(Auth::user()->nom ?? 'S', 0, 1)) }}
                </div>
                <div class="user-details">
                    <span class="user-name">{{ Auth::user()->prenom }} {{ Auth::user()->nom }}</span>
                    <span class="user-role">{{ ucfirst(str_replace('_', ' ', Auth::user()->type_utilisateur)) }}</span>
                </div>
            </div>
        </div>
    </aside>

    <!-- 2. ZONE PRINCIPALE DE CONVERSATION -->
    <main class="ai-chat-area">
        <!-- En-tête du Chat -->
        <header class="ai-chat-header">
            <div class="ai-header-left">
                <button type="button" class="btn-toggle-sidebar" onclick="toggleAiSidebar()" title="Historique des discussions">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="ai-bot-avatar">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span class="pulse-status"></span>
                </div>
                <div>
                    <h1 class="ai-header-title">Mainteo IA <span class="ai-badge-pro">COPILOTE</span></h1>
                    <p class="ai-header-subtitle">
                        <span class="dot-online"></span> Base de données Soutarah connectée en temps réel
                    </p>
                </div>
            </div>

            <div class="ai-header-actions">
                <button type="button" class="btn-action-outline" onclick="startNewChat()" title="Nouvelle conversation">
                    <i class="fa-solid fa-rotate-right"></i>
                    <span class="d-none d-sm-inline">Réinitialiser</span>
                </button>
            </div>
        </header>

        <!-- Flux des messages avec défilement -->
        <div class="ai-messages-scroll" id="aiMessagesScroll">
            <!-- Écran d'accueil / Suggestions par défaut -->
            <div class="ai-welcome-hero" id="aiWelcomeHero">
                <div class="welcome-icon-box">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <h2>Bonjour {{ Auth::user()->prenom ?? 'Cher collaborateur' }} ! 👋</h2>
                <p class="welcome-text">
                    Je suis <strong>Mainteo IA</strong>, votre assistant d'exploitation technique et GMAO. J'ai accès en temps réel aux données complètes de <strong>Soutarah</strong> (équipes, membres, demandes, interventions, parcs d'équipements et clients).
                </p>

                <div class="quick-prompts-grid">
                    <div class="quick-prompt-card" onclick="sendQuickPrompt('Donne-moi un tableau complet des équipes et de tous leurs techniciens avec leurs rôles et numéros de téléphone.')">
                        <div class="prompt-icon">👥</div>
                        <div class="prompt-content">
                            <strong>Tableau des équipes</strong>
                            <span>Tous les membres, rôles et coordonnées directes</span>
                        </div>
                        <i class="fa-solid fa-arrow-right prompt-arrow"></i>
                    </div>

                    <div class="quick-prompt-card" onclick="sendQuickPrompt('Quelles sont les demandes d\'intervention urgentes ou en cours de traitement ?')">
                        <div class="prompt-icon">🚨</div>
                        <div class="prompt-content">
                            <strong>Demandes urgentes</strong>
                            <span>Priorités, statuts et zones VIP en attente</span>
                        </div>
                        <i class="fa-solid fa-arrow-right prompt-arrow"></i>
                    </div>

                    <div class="quick-prompt-card" onclick="sendQuickPrompt('Donne-moi un état des lieux des dépannages récents et des équipements sous contrat.')">
                        <div class="prompt-icon">🛠️</div>
                        <div class="prompt-content">
                            <strong>Dépannages & Équipements</strong>
                            <span>Pannes en cours, marques et affectations</span>
                        </div>
                        <i class="fa-solid fa-arrow-right prompt-arrow"></i>
                    </div>

                    <div class="quick-prompt-card" onclick="sendQuickPrompt('Fais-moi un bilan chiffré des demandes, maintenances et interventions actives.')">
                        <div class="prompt-icon">📊</div>
                        <div class="prompt-content">
                            <strong>Statistiques globales</strong>
                            <span>Indicateurs clés d\'activité et avancement</span>
                        </div>
                        <i class="fa-solid fa-arrow-right prompt-arrow"></i>
                    </div>
                </div>
            </div>

            <!-- Liste des messages dynamiques -->
            <div class="ai-messages-flow" id="aiMessagesFlow"></div>

            <!-- Indicateur de saisie / réflexion -->
            <div class="ai-typing-indicator" id="aiTypingIndicator" style="display: none;">
                <div class="typing-bot-avatar">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <div class="typing-bubble">
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                    <span class="typing-label">Mainteo IA analyse la base de données...</span>
                </div>
            </div>
        </div>

        <!-- 3. ZONE DE SAISIE FIXE EN BAS -->
        <footer class="ai-chat-input-bar">
            <div class="input-container-card">
                <textarea 
                    id="aiUserTextarea" 
                    rows="1" 
                    placeholder="Posez votre question à Mainteo IA (ex: 'Donne-moi le tableau des équipes', 'Quels sont les climatiseurs en panne ?')..." 
                    oninput="autoResizeTextarea(this)" 
                    onkeydown="handleTextareaKeydown(event)"></textarea>

                <div class="input-actions-bottom">
                    <div class="input-tips">
                        <span><kbd>Entrée</kbd> pour envoyer • <kbd>Maj</kbd> + <kbd>Entrée</kbd> pour saut de ligne</span>
                    </div>
                    <button type="button" class="btn-send-message" id="btnSendAi" onclick="submitAiMessage()" title="Envoyer le message">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </div>
            <div class="ai-disclaimer">
                <span>⚡ Mainteo IA utilise le modèle 120B en temps réel. Vérifiez les informations critiques sur vos ordres de travail.</span>
            </div>
        </footer>
    </main>
</div>

<style>
/* ══════════════════════════════════════════════════════════════════════ */
/* 🎨 STYLES MAINTEO IA — DESIGN COPILOTE ULTRA-MODERNE & RESPONSIVE     */
/* ══════════════════════════════════════════════════════════════════════ */

:root {
    --ai-emerald-50: #ecfdf5;
    --ai-emerald-100: #d1fae5;
    --ai-emerald-500: #10b981;
    --ai-emerald-600: #059669;
    --ai-emerald-700: #047857;
    --ai-emerald-800: #065f46;
    --ai-slate-50: #f8fafc;
    --ai-slate-100: #f1f5f9;
    --ai-slate-200: #e2e8f0;
    --ai-slate-300: #cbd5e1;
    --ai-slate-700: #334155;
    --ai-slate-800: #1e293b;
    --ai-slate-900: #0f172a;
}

/* Conteneur principal plein écran sous le header de l'application */
.ai-page-wrapper {
    display: flex;
    height: calc(100vh - 85px);
    margin: -1.5rem -1.5rem -2rem -1.5rem; /* Compense le padding du layout parent */
    background: #ffffff;
    overflow: hidden;
    position: relative;
}

@media (max-width: 991px) {
    .ai-page-wrapper {
        margin: -1rem;
        height: calc(100vh - 75px);
    }
}

/* ─── 1. BARRE LATÉRALE HISTORIQUE ─────────────────────────────────── */
.ai-conversations-sidebar {
    width: 310px;
    background: #f8fafc;
    border-right: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1050;
}

.ai-sidebar-top {
    padding: 1.25rem 1rem 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-new-chat {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    border: none;
    border-radius: 12px;
    padding: 0.75rem 1rem;
    font-weight: 700;
    font-size: 0.92rem;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-new-chat:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
}

.btn-close-sidebar-mobile {
    display: none;
    background: none;
    border: none;
    font-size: 1.25rem;
    color: #64748b;
    padding: 0.5rem;
    cursor: pointer;
}

.ai-sidebar-search {
    padding: 0 1rem 0.75rem 1rem;
    position: relative;
}

.ai-sidebar-search i {
    position: absolute;
    left: 1.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.85rem;
}

.ai-sidebar-search input {
    width: 100%;
    padding: 0.55rem 0.75rem 0.55rem 2.2rem;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    font-size: 0.85rem;
    outline: none;
    transition: border-color 0.2s;
}

.ai-sidebar-search input:focus {
    border-color: #10b981;
}

.ai-conversations-list {
    flex: 1;
    overflow-y: auto;
    padding: 0.25rem 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.ai-conv-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0.85rem;
    border-radius: 12px;
    cursor: pointer;
    background: #ffffff;
    border: 1px solid #f1f5f9;
    transition: all 0.2s ease;
    position: relative;
}

.ai-conv-item:hover {
    background: #f1f5f9;
    border-color: #e2e8f0;
}

.ai-conv-item.active {
    background: #ecfdf5;
    border-color: #a7f3d0;
}

.ai-conv-item.active .ai-conv-title {
    color: #065f46;
    font-weight: 700;
}

.ai-conv-item.active .ai-conv-icon {
    color: #059669;
}

.ai-conv-icon {
    color: #64748b;
    font-size: 1rem;
    flex-shrink: 0;
}

.ai-conv-info {
    flex: 1;
    min-width: 0;
}

.ai-conv-title {
    font-size: 0.86rem;
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ai-conv-snippet {
    font-size: 0.75rem;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 0.15rem;
}

.btn-delete-conv {
    background: none;
    border: none;
    color: #94a3b8;
    padding: 0.25rem;
    border-radius: 6px;
    font-size: 0.8rem;
    cursor: pointer;
    opacity: 0;
    transition: all 0.2s;
}

.ai-conv-item:hover .btn-delete-conv {
    opacity: 1;
}

.btn-delete-conv:hover {
    color: #ef4444;
    background: #fee2e2;
}

.ai-conv-empty {
    text-align: center;
    padding: 3rem 1rem;
    color: #94a3b8;
}

.ai-conv-empty i {
    font-size: 2.2rem;
    margin-bottom: 0.75rem;
    opacity: 0.6;
}

.ai-conv-empty p {
    font-size: 0.85rem;
    margin: 0;
}

.ai-sidebar-footer {
    padding: 0.85rem 1rem;
    border-top: 1px solid #e2e8f0;
    background: #ffffff;
}

.ai-user-badge {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #059669;
    color: white;
    font-weight: 700;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.user-details {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.user-name {
    font-size: 0.86rem;
    font-weight: 700;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-role {
    font-size: 0.72rem;
    color: #64748b;
    font-weight: 600;
}

/* ─── 2. ZONE DE CHAT PRINCIPALE ───────────────────────────────────── */
.ai-chat-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
    background: #ffffff;
    position: relative;
}

/* En-tête */
.ai-chat-header {
    height: 64px;
    padding: 0 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f5f9;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    z-index: 10;
}

.ai-header-left {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.btn-toggle-sidebar {
    display: none;
    background: #f1f5f9;
    border: none;
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    color: #475569;
    font-size: 1.1rem;
    cursor: pointer;
}

.ai-bot-avatar {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, #10b981 0%, #047857 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    position: relative;
    box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);
}

.pulse-status {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #22c55e;
    border: 2px solid #ffffff;
}

.ai-header-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ai-badge-pro {
    background: #ecfdf5;
    color: #059669;
    font-size: 0.65rem;
    font-weight: 800;
    padding: 0.15rem 0.5rem;
    border-radius: 9999px;
    border: 1px solid #a7f3d0;
    letter-spacing: 0.5px;
}

.ai-header-subtitle {
    margin: 0.15rem 0 0 0;
    font-size: 0.78rem;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.dot-online {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
    display: inline-block;
}

.btn-action-outline {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.45rem 0.85rem;
    font-size: 0.84rem;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.45rem;
    transition: all 0.2s;
}

.btn-action-outline:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #0f172a;
}

/* Zone de défilement des messages */
.ai-messages-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem 2rem;
    scroll-behavior: smooth;
    display: flex;
    flex-direction: column;
}

/* Écran d'accueil */
.ai-welcome-hero {
    max-width: 820px;
    margin: auto;
    text-align: center;
    padding: 2rem 1rem;
}

.welcome-icon-box {
    width: 68px;
    height: 68px;
    border-radius: 20px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    font-size: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem auto;
    box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4);
}

.ai-welcome-hero h2 {
    font-size: 1.65rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 0.65rem;
}

.welcome-text {
    font-size: 0.96rem;
    color: #64748b;
    line-height: 1.6;
    max-width: 620px;
    margin: 0 auto 2.2rem auto;
}

/* Grille de suggestions rapides */
.quick-prompts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    text-align: left;
}

.quick-prompt-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1.1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    cursor: pointer;
    transition: all 0.25s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
}

.quick-prompt-card:hover {
    border-color: #10b981;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.15);
}

.prompt-icon {
    font-size: 1.6rem;
    flex-shrink: 0;
}

.prompt-content {
    flex: 1;
    min-width: 0;
}

.prompt-content strong {
    display: block;
    font-size: 0.92rem;
    color: #0f172a;
    margin-bottom: 0.15rem;
}

.prompt-content span {
    display: block;
    font-size: 0.78rem;
    color: #64748b;
    line-height: 1.35;
}

.prompt-arrow {
    color: #cbd5e1;
    font-size: 0.85rem;
    transition: transform 0.2s, color 0.2s;
}

.quick-prompt-card:hover .prompt-arrow {
    color: #10b981;
    transform: translateX(3px);
}

/* Flux des messages */
.ai-messages-flow {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    max-width: 900px;
    width: 100%;
    margin: 0 auto;
}

.chat-bubble-row {
    display: flex;
    gap: 0.85rem;
    width: 100%;
    animation: fadeIn 0.25s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

.chat-bubble-row.user {
    justify-content: flex-end;
}

.bubble-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.85rem;
    font-weight: 700;
}

.bubble-avatar.bot {
    background: linear-gradient(135deg, #10b981 0%, #047857 100%);
    color: white;
}

.bubble-avatar.user {
    background: #0f172a;
    color: white;
}

.bubble-content-box {
    max-width: 82%;
    min-width: 60px;
}

.chat-bubble-row.user .bubble-content-box {
    text-align: right;
}

.bubble-text {
    padding: 0.9rem 1.2rem;
    border-radius: 16px;
    font-size: 0.93rem;
    line-height: 1.6;
    word-break: break-word;
    text-align: left;
}

.chat-bubble-row.bot .bubble-text {
    background: #f8fafc;
    color: #0f172a;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
}

.chat-bubble-row.user .bubble-text {
    background: #059669;
    color: #ffffff;
    border-bottom-right-radius: 4px;
    display: inline-block;
}

.bubble-meta {
    font-size: 0.72rem;
    color: #94a3b8;
    margin-top: 0.35rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.chat-bubble-row.user .bubble-meta {
    justify-content: flex-end;
}

.btn-copy-bubble {
    background: none;
    border: none;
    color: #94a3b8;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-copy-bubble:hover {
    color: #059669;
    background: #ecfdf5;
}

/* ══════════════════════════════════════════════════════════════════════ */
/* 📊 RENDU DES TABLEAUX MARKDOWN DANS LE CHAT                            */
/* ══════════════════════════════════════════════════════════════════════ */
.ai-table-responsive-wrapper {
    width: 100%;
    margin: 1rem 0;
    overflow-x: auto;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
    -webkit-overflow-scrolling: touch;
}

.ai-rendered-table {
    width: 100%;
    min-width: 580px;
    border-collapse: collapse;
    font-size: 0.88rem;
    text-align: left;
}

.ai-rendered-table th {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: #ffffff;
    font-weight: 700;
    padding: 10px 14px;
    border-bottom: 2px solid #065f46;
    white-space: nowrap;
}

.ai-rendered-table td {
    padding: 10px 14px;
    border-bottom: 1px solid #e2e8f0;
    color: #1e293b;
    vertical-align: top;
}

.ai-rendered-table tbody tr:nth-child(even) {
    background-color: #f8fafc;
}

.ai-rendered-table tbody tr:hover {
    background-color: #ecfdf5;
}

/* Indicateur de saisie */
.ai-typing-indicator {
    display: flex;
    gap: 0.85rem;
    align-items: center;
    max-width: 900px;
    margin: 0.5rem auto 0 auto;
    width: 100%;
}

.typing-bot-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #10b981 0%, #047857 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.typing-bubble {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 0.75rem 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.typing-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #10b981;
    animation: typingBlink 1.4s infinite ease-in-out both;
}

.typing-dot:nth-child(1) { animation-delay: -0.32s; }
.typing-dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes typingBlink {
    0%, 80%, 100% { transform: scale(0); opacity: 0.4; }
    40% { transform: scale(1); opacity: 1; }
}

.typing-label {
    font-size: 0.82rem;
    color: #64748b;
    font-weight: 500;
    margin-left: 0.4rem;
}

/* ─── 3. BARRE DE SAISIE FIXE EN BAS ───────────────────────────────── */
.ai-chat-input-bar {
    padding: 0.75rem 1.5rem 1rem 1.5rem;
    background: #ffffff;
    border-top: 1px solid #f1f5f9;
}

.input-container-card {
    max-width: 900px;
    margin: 0 auto;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 16px;
    padding: 0.75rem 1rem 0.6rem 1rem;
    box-shadow: 0 4px 15px -2px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
}

.input-container-card:focus-within {
    border-color: #10b981;
    box-shadow: 0 4px 20px -2px rgba(16, 185, 129, 0.2);
}

.input-container-card textarea {
    width: 100%;
    border: none;
    outline: none;
    background: transparent;
    font-size: 0.95rem;
    color: #0f172a;
    resize: none;
    max-height: 160px;
    min-height: 24px;
    line-height: 1.5;
    font-family: inherit;
}

.input-actions-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 0.45rem;
    padding-top: 0.4rem;
    border-top: 1px solid #f8fafc;
}

.input-tips {
    font-size: 0.72rem;
    color: #94a3b8;
}

.input-tips kbd {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 0.1rem 0.35rem;
    font-family: inherit;
    font-size: 0.7rem;
    color: #475569;
}

.btn-send-message {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #10b981;
    color: #ffffff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    transition: all 0.2s;
    flex-shrink: 0;
}

.btn-send-message:hover:not(:disabled) {
    background: #059669;
    transform: scale(1.05);
}

.btn-send-message:disabled {
    background: #cbd5e1;
    cursor: not-allowed;
    transform: none;
}

.ai-disclaimer {
    text-align: center;
    margin-top: 0.4rem;
    font-size: 0.72rem;
    color: #94a3b8;
}

/* ─── 4. ADAPTATIONS RESPONSIVE MOBILE & TABLETTE ──────────────────── */
@media (max-width: 991px) {
    .btn-toggle-sidebar {
        display: inline-flex;
    }

    .ai-conversations-sidebar {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        transform: translateX(-100%);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .ai-conversations-sidebar.open {
        transform: translateX(0);
    }

    .btn-close-sidebar-mobile {
        display: block;
    }

    .ai-mobile-overlay {
        display: none;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(2px);
        z-index: 1040;
    }

    .ai-mobile-overlay.active {
        display: block;
    }

    .quick-prompts-grid {
        grid-template-columns: 1fr;
    }

    .ai-messages-scroll {
        padding: 1rem;
    }

    .bubble-content-box {
        max-width: 92%;
    }

    .input-tips {
        display: none;
    }
}
</style>

<script>
/* ══════════════════════════════════════════════════════════════════════ */
/* 🧠 LOGIQUE JAVASCRIPT DE LA PAGE MAINTEO IA                            */
/* ══════════════════════════════════════════════════════════════════════ */

let currentConversationId = null;
let isGenerating = false;

// Token CSRF Laravel
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

// 1. Gestion de la barre latérale sur mobile
function toggleAiSidebar() {
    const sidebar = document.getElementById('aiConversationsSidebar');
    const overlay = document.getElementById('aiMobileOverlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('active');
}

function closeAiSidebar() {
    document.getElementById('aiConversationsSidebar').classList.remove('open');
    document.getElementById('aiMobileOverlay').classList.remove('active');
}

// 2. Redimensionnement automatique du Textarea
function autoResizeTextarea(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 160) + 'px';
}

function handleTextareaKeydown(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        submitAiMessage();
    }
}

// 3. Nouvelle discussion
function startNewChat() {
    currentConversationId = null;
    document.getElementById('aiMessagesFlow').innerHTML = '';
    document.getElementById('aiWelcomeHero').style.display = 'block';
    
    // Déselectionner la liste
    document.querySelectorAll('.ai-conv-item').forEach(el => el.classList.remove('active'));
    
    const textarea = document.getElementById('aiUserTextarea');
    textarea.value = '';
    textarea.focus();
    autoResizeTextarea(textarea);
    
    closeAiSidebar();
}

// 4. Suggestions rapides d'un clic
function sendQuickPrompt(promptText) {
    const textarea = document.getElementById('aiUserTextarea');
    textarea.value = promptText;
    submitAiMessage();
}

// 5. Envoi d'un message
function submitAiMessage() {
    if (isGenerating) return;

    const textarea = document.getElementById('aiUserTextarea');
    const userText = textarea.value.trim();
    if (!userText) return;

    // Masquer le hero d'accueil
    document.getElementById('aiWelcomeHero').style.display = 'none';

    // Afficher le message utilisateur dans le flux
    appendMessageToFlow('user', userText);

    // Vider le champ
    textarea.value = '';
    autoResizeTextarea(textarea);

    // Activer l'indicateur de frappe
    isGenerating = true;
    document.getElementById('btnSendAi').disabled = true;
    const typingIndicator = document.getElementById('aiTypingIndicator');
    typingIndicator.style.display = 'flex';
    scrollToBottom();

    // Requête vers l'API
    fetch('{{ route("ai.chat") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            message: userText,
            conversation_id: currentConversationId
        })
    })
    .then(async response => {
        const data = await response.json();
        typingIndicator.style.display = 'none';
        isGenerating = false;
        document.getElementById('btnSendAi').disabled = false;

        if (response.ok && data.success) {
            currentConversationId = data.conversation_id;
            appendMessageToFlow('bot', data.ai_message.content);
            refreshConversationsList();
        } else {
            const errorMsg = data.error || data.message || "Erreur de communication avec le service IA.";
            appendMessageToFlow('bot', "⚠️ " + errorMsg);
        }
        scrollToBottom();
    })
    .catch(err => {
        typingIndicator.style.display = 'none';
        isGenerating = false;
        document.getElementById('btnSendAi').disabled = false;
        appendMessageToFlow('bot', "⚠️ Erreur réseau : impossible de joindre le serveur. Veuillez réessayer.");
        scrollToBottom();
    });
}

// 6. Ajout d'une bulle au flux
function appendMessageToFlow(role, rawContent) {
    const flow = document.getElementById('aiMessagesFlow');
    const timeNow = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    const row = document.createElement('div');
    row.className = `chat-bubble-row ${role}`;

    const formattedContent = (role === 'bot') ? parseMarkdownToHtml(rawContent) : escapeHtml(rawContent).replace(/\n/g, '<br>');

    if (role === 'bot') {
        row.innerHTML = `
            <div class="bubble-avatar bot">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
            <div class="bubble-content-box">
                <div class="bubble-text">${formattedContent}</div>
                <div class="bubble-meta">
                    <span>${timeNow}</span>
                    <button type="button" class="btn-copy-bubble" onclick="copyTextToClipboard(this)" title="Copier la réponse">
                        <i class="fa-regular fa-copy"></i> Copier
                    </button>
                </div>
            </div>
        `;
    } else {
        row.innerHTML = `
            <div class="bubble-content-box">
                <div class="bubble-text">${formattedContent}</div>
                <div class="bubble-meta">
                    <span>${timeNow}</span>
                </div>
            </div>
            <div class="bubble-avatar user">
                {{ strtoupper(substr(Auth::user()->prenom ?? 'U', 0, 1)) }}
            </div>
        `;
    }

    flow.appendChild(row);
    scrollToBottom();
}

// 7. Parseur Markdown vers HTML avec Tableaux Stables
function parseMarkdownToHtml(text) {
    if (!text) return '';

    // Détection et conversion des Tableaux Markdown
    const lines = text.split('\n');
    let inTable = false;
    let tableHtml = '';
    let processedLines = [];

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i].trim();
        const isTableLine = line.startsWith('|') && line.endsWith('|');

        if (isTableLine) {
            const isSeparator = /^\|(\s*[-:]+[-| :]*)\|$/.test(line);

            if (!inTable) {
                // Début d'un nouveau tableau
                inTable = true;
                tableHtml = '<div class="ai-table-responsive-wrapper"><table class="ai-rendered-table"><thead><tr>';
                const headers = line.split('|').slice(1, -1);
                headers.forEach(h => {
                    tableHtml += `<th>${escapeHtml(h.trim())}</th>`;
                });
                tableHtml += '</tr></thead><tbody>';
            } else if (isSeparator) {
                // Ligne de séparation |---|---| (on l'ignore)
                continue;
            } else {
                // Ligne de données
                tableHtml += '<tr>';
                const cells = line.split('|').slice(1, -1);
                cells.forEach(c => {
                    tableHtml += `<td>${escapeHtml(c.trim())}</td>`;
                });
                tableHtml += '</tr>';
            }
        } else {
            if (inTable) {
                tableHtml += '</tbody></table></div>';
                processedLines.push(tableHtml);
                inTable = false;
                tableHtml = '';
            }
            processedLines.push(line);
        }
    }

    if (inTable) {
        tableHtml += '</tbody></table></div>';
        processedLines.push(tableHtml);
    }

    let result = processedLines.join('\n');

    // Gras **texte**
    result = result.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    // Italique *texte*
    result = result.replace(/\*(.*?)\*/g, '<em>$1</em>');
    // Listes à puces
    result = result.replace(/^\s*-\s+(.*)$/gm, '<li style="margin-left: 1.2rem;">$1</li>');
    // Saut de ligne
    result = result.replace(/\n\n/g, '<br><br>');

    return result;
}

// 8. Chargement d'une conversation passée
function loadConversation(convId) {
    if (isGenerating) return;
    currentConversationId = convId;

    // Mettre en surbrillance
    document.querySelectorAll('.ai-conv-item').forEach(el => el.classList.remove('active'));
    document.getElementById(`conv-item-${convId}`)?.classList.add('active');

    // Masquer le hero
    document.getElementById('aiWelcomeHero').style.display = 'none';
    const flow = document.getElementById('aiMessagesFlow');
    flow.innerHTML = '<div class="text-center text-muted py-4"><i class="fa-solid fa-spinner fa-spin"></i> Chargement des échanges...</div>';

    closeAiSidebar();

    fetch(`{{ url('ai/conversations') }}/${convId}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        flow.innerHTML = '';
        if (data.success && data.messages) {
            data.messages.forEach(msg => {
                appendMessageToFlow(msg.role === 'assistant' ? 'bot' : 'user', msg.content);
            });
            scrollToBottom();
        }
    })
    .catch(err => {
        flow.innerHTML = '<div class="text-danger text-center py-4">Erreur de chargement des messages.</div>';
    });
}

// 9. Suppression d'une conversation
function deleteConversation(event, convId) {
    event.stopPropagation();
    if (!confirm("Voulez-vous supprimer cette discussion ?")) return;

    fetch(`{{ url('ai/conversations') }}/${convId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`conv-item-${convId}`)?.remove();
            if (currentConversationId === convId) {
                startNewChat();
            }
        }
    });
}

// 10. Actualisation de la liste des conversations
function refreshConversationsList() {
    fetch('{{ route("ai.conversations.index") }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.conversations) {
            const list = document.getElementById('aiConversationsList');
            let html = '';
            data.conversations.forEach(c => {
                const isActive = (c.id === currentConversationId) ? 'active' : '';
                html += `
                    <div class="ai-conv-item ${isActive}" id="conv-item-${c.id}" onclick="loadConversation(${c.id})">
                        <div class="ai-conv-icon"><i class="fa-regular fa-message"></i></div>
                        <div class="ai-conv-info">
                            <div class="ai-conv-title">${escapeHtml(c.titre)}</div>
                            <div class="ai-conv-snippet">${escapeHtml(c.last_message || 'Discussion')}</div>
                        </div>
                        <button type="button" class="btn-delete-conv" onclick="deleteConversation(event, ${c.id})" title="Supprimer">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                `;
            });
            list.innerHTML = html;
        }
    });
}

// 11. Filtrage des discussions dans la sidebar
function filterConversations() {
    const q = document.getElementById('aiConvSearch').value.toLowerCase();
    document.querySelectorAll('.ai-conv-item').forEach(item => {
        const title = item.querySelector('.ai-conv-title')?.textContent.toLowerCase() || '';
        const snippet = item.querySelector('.ai-conv-snippet')?.textContent.toLowerCase() || '';
        if (title.includes(q) || snippet.includes(q)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// 12. Utilitaires
function scrollToBottom() {
    const scrollContainer = document.getElementById('aiMessagesScroll');
    scrollContainer.scrollTop = scrollContainer.scrollHeight;
}

function copyTextToClipboard(btn) {
    const bubbleText = btn.closest('.bubble-content-box').querySelector('.bubble-text').innerText;
    navigator.clipboard.writeText(bubbleText).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check text-success"></i> Copié !';
        setTimeout(() => { btn.innerHTML = originalHtml; }, 2000);
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection
