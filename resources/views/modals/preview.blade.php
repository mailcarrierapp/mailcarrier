<div x-data="{
    size: 'desktop',
    breakpoints: {
        mobile: {
            label: 'Mobile',
            icon: 'heroicon-o-device-mobile',
            width: '320px',
            height: '568px',
        },
        tablet: {
            label: 'Tablet',
            icon: 'heroicon-o-device-tablet',
            width: '768px',
            height: '1024px',
        },
        desktop: {
            label: 'Desktop',
            icon: 'heroicon-o-desktop-computer',
            width: null,
            height: '50vh',
        },
    },
}">
    <div class="text-center">
        <div class="inline-flex rounded-lg bg-slate-100 dark:bg-gray-900/50 p-0.5" role="tablist" aria-orientation="horizontal">
            <template x-for="[key, breakpoint] in Object.entries(breakpoints)">
                <button
                    class="flex items-center rounded-md py-[0.4375rem] pl-2 pr-2 text-sm font-semibold lg:pr-3"
                    x-bind:class="size === key && 'bg-white dark:bg-gray-700/50 shadow'"
                    role="tab"
                    type="button"
                    x-bind:aria-selected="size === key"
                    x-on:click="size = key">
                    <!-- Icon -->
                    <x-heroicon-o-device-mobile
                        x-show="key === 'mobile'"
                        class="h-5 w-5 flex-none"
                        x-bind:class="size === key ? 'stroke-primary-500' : 'stroke-slate-600 dark:stroke-slate-400'" />
                    <x-heroicon-o-device-tablet
                        x-show="key === 'tablet'"
                        class="h-5 w-5 flex-none"
                        x-bind:class="size === key ? 'stroke-primary-500' : 'stroke-slate-600 dark:stroke-slate-400'" />
                    <x-heroicon-o-desktop-computer
                        x-show="key === 'desktop'"
                        class="h-5 w-5 flex-none"
                        x-bind:class="size === key ? 'stroke-primary-500' : 'stroke-slate-600 dark:stroke-slate-400'" />
                    <span
                        class="sr-only lg:not-sr-only lg:ml-2"
                        x-bind:class="size === key ? 'text-slate-900 dark:text-primary-500' : 'text-slate-600 dark:text-slate-400'"
                        x-text="breakpoint.label"></span>
                </button>
            </template>
        </div>
    </div>

    <div
        class="mt-4 mx-auto bg-white rounded-lg border p-3"
        x-bind:style="{
            width: breakpoints[size].width,
            height: breakpoints[size].height,
        }">
        <iframe src="{{ route('preview.log', ['log' => $id]) }}" class="border-0 w-full h-full"></iframe>
    </div>
</div>
