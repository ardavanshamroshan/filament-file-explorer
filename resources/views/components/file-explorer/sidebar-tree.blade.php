@props([
    'nodes' => [],
    'currentId' => null,
    'depth' => 0,
])

@foreach ($nodes as $node)
    @php
        $id = (int) ($node['id'] ?? 0);
        $name = (string) ($node['name'] ?? '');
        $children = $node['children'] ?? [];
        $isPrimary = (bool) ($node['primary'] ?? false);
        $isActive = (int) $currentId === $id;
        $hasChildren = count($children) > 0;
        $pad = 8 + ($depth * 12);
        $defaultOpen = ($isPrimary || $isActive || $depth < 1) ? 'true' : 'false';
    @endphp
    <div class="fe-side-node" wire:key="side-{{ $id }}-{{ $depth }}">
        <div
            @class([
                'fe-side-row group flex items-center gap-0.5 rounded-md',
                'fe-side-row--active' => $isActive,
                'fe-side-row--primary' => $isPrimary,
            ])
            style="padding-inline-start: {{ $pad }}px"
        >
            @if ($hasChildren)
                <button
                    type="button"
                    class="fe-side-chevron"
                    title="Expand / Collapse"
                    @click.stop.prevent="$store.feUi.toggle({{ $id }}, {{ $defaultOpen }})"
                >
                    <span
                        class="fe-side-chevron-icon inline-flex"
                        :class="$store.feUi.isOpen({{ $id }}, {{ $defaultOpen }}) ? 'fe-side-chevron-icon--open' : ''"
                    >
                        @svg('heroicon-o-chevron-right', 'h-3.5 w-3.5')
                    </span>
                </button>
            @else
                <span class="inline-block w-5 shrink-0"></span>
            @endif
            <button
                type="button"
                @class([
                    'fe-side-item',
                    'fe-side-item--primary' => $isPrimary,
                ])
                title="{{ $name }}"
                wire:click="navigateToFolder({{ $id }})"
                data-fe-drop-folder="{{ $id }}"
                :class="{ 'fe-side-item--drop': $store.feDrag.dropTargetId === {{ $id }} }"
            >
                @if ($isPrimary)
                    @svg('heroicon-o-folder-open', 'h-4 w-4 shrink-0 text-teal-600 dark:text-teal-400')
                @else
                    @svg('heroicon-o-folder', 'h-3.5 w-3.5 shrink-0 text-zinc-500 dark:text-zinc-400')
                @endif
                <span @class(['truncate', 'font-semibold' => $isPrimary])>{{ $name }}</span>
            </button>
        </div>
        @if ($hasChildren)
            <div
                class="fe-side-children"
                x-show="$store.feUi.isOpen({{ $id }}, {{ $defaultOpen }})"
                x-cloak
            >
                <x-filament-file-explorer::file-explorer.sidebar-tree
                    :nodes="$children"
                    :current-id="$currentId"
                    :depth="$depth + 1"
                />
            </div>
        @endif
    </div>
@endforeach
