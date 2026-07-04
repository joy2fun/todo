<div class="mobile-quick-links">
    <style>
        .mobile-quick-links {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .mobile-quick-links a {
            color: rgb(107 114 128); /* text-gray-500 */
            transition: color 0.2s;
        }

        .dark .mobile-quick-links a {
            color: rgb(156 163 175); /* text-gray-400 */
        }

        .mobile-quick-links a:hover {
            color: rgb(55 65 81); /* text-gray-700 */
        }

        .dark .mobile-quick-links a:hover {
            color: rgb(229 231 235); /* text-gray-200 */
        }

        @media (min-width: 1024px) {
            .mobile-quick-links {
                display: none !important;
            }
        }
    </style>

    <a href="/" title="Home">
        <x-filament::icon
            icon="heroicon-o-check-circle"
            style="width: 1.5rem; height: 1.5rem;"
        />
    </a>
</div>
