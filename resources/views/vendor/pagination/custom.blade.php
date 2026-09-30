@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" style="font-family: 'Varela Round', sans-serif;">
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            {{-- Liens de pagination --}}
            <div style="display: flex; gap: 0.3rem; align-items: center; justify-content: center; flex-wrap: wrap;">
                {{-- Bouton Previous --}}
                @if ($paginator->onFirstPage())
                    <span style="padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; background: #f8fafc; color: #cbd5e1; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 600; cursor: not-allowed;">
                        ←
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" 
                       style="padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; background: #fff; color: #059669; border-radius: 0.5rem; text-decoration: none; font-size: 0.8rem; font-weight: 600; transition: all 0.2s;">
                        ←
                    </a>
                @endif

                {{-- Numéros de page avec limitation pour mobile --}}
                @php
                    $start = max(1, $paginator->currentPage() - 2);
                    $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
                @endphp

                {{-- Première page si pas visible --}}
                @if ($start > 1)
                    <a href="{{ $paginator->url(1) }}" 
                       style="padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; background: #fff; color: #475569; border-radius: 0.5rem; text-decoration: none; font-size: 0.8rem; font-weight: 600; min-width: 35px; text-align: center; transition: all 0.2s;">
                        1
                    </a>
                    @if ($start > 2)
                        <span style="padding: 0.5rem 0.5rem; color: #94a3b8; font-size: 0.8rem; font-weight: 600;">...</span>
                    @endif
                @endif

                {{-- Pages autour de la page actuelle --}}
                @for ($page = $start; $page <= $end; $page++)
                    @if ($page == $paginator->currentPage())
                        <span style="padding: 0.5rem 0.75rem; border: 2px solid #059669; background: #059669; color: #fff; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 700; min-width: 35px; text-align: center;">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $paginator->url($page) }}" 
                           style="padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; background: #fff; color: #475569; border-radius: 0.5rem; text-decoration: none; font-size: 0.8rem; font-weight: 600; min-width: 35px; text-align: center; transition: all 0.2s;">
                            {{ $page }}
                        </a>
                    @endif
                @endfor

                {{-- Dernière page si pas visible --}}
                @if ($end < $paginator->lastPage())
                    @if ($end < $paginator->lastPage() - 1)
                        <span style="padding: 0.5rem 0.5rem; color: #94a3b8; font-size: 0.8rem; font-weight: 600;">...</span>
                    @endif
                    <a href="{{ $paginator->url($paginator->lastPage()) }}" 
                       style="padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; background: #fff; color: #475569; border-radius: 0.5rem; text-decoration: none; font-size: 0.8rem; font-weight: 600; min-width: 35px; text-align: center; transition: all 0.2s;">
                        {{ $paginator->lastPage() }}
                    </a>
                @endif

                {{-- Bouton Next --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" 
                       style="padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; background: #fff; color: #059669; border-radius: 0.5rem; text-decoration: none; font-size: 0.8rem; font-weight: 600; transition: all 0.2s;">
                        →
                    </a>
                @else
                    <span style="padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; background: #f8fafc; color: #cbd5e1; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 600; cursor: not-allowed;">
                        →
                    </span>
                @endif
            </div>

            {{-- Info texte (caché sur mobile) --}}
            <div style="color: #64748b; font-size: 0.8rem; text-align: center;">
                <span class="pagination-info">
                    Affichage de <strong style="color: #059669;">{{ $paginator->firstItem() }}</strong> à 
                    <strong style="color: #059669;">{{ $paginator->lastItem() }}</strong> sur 
                    <strong style="color: #059669;">{{ $paginator->total() }}</strong> résultats
                </span>
            </div>
        </div>
    </nav>

    <style>
        /* Hover effects pour la pagination */
        nav[aria-label="Pagination Navigation"] a:hover {
            background: #f0fdf4 !important;
            border-color: #059669 !important;
            transform: translateY(-1px);
        }

        /* Responsive : cacher le texte info sur mobile */
        @media (max-width: 640px) {
            .pagination-info {
                display: none;
            }
        }
    </style>
@endif
