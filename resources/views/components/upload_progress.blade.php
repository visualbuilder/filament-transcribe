<div class="space-y-2">
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-x-3">
            <span class="size-8 flex justify-center items-center border border-gray-200 text-gray-500 rounded-lg dark:border-neutral-700 dark:text-neutral-500">
                <svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12-9H3v18h18V3z" />
                </svg>
            </span>
            <div>
                <p class="text-sm font-medium text-gray-800 dark:text-white" x-text="uploadFileName"></p>
                <p class="text-xs text-gray-500 dark:text-neutral-500" x-text="uploadFileSize"></p>
            </div>
        </div>
    </div>
    <div class="flex items-center gap-x-3 whitespace-nowrap">
        <div class="flex w-full h-2 bg-gray-200 rounded-full overflow-hidden dark:bg-neutral-700">
            <div class="flex flex-col justify-center rounded-full overflow-hidden upload-progress-bar text-xs text-white text-center whitespace-nowrap transition duration-500" x-bind:style="`width: ${uploadProgress}%`"></div>
        </div>
        <div class="w-6 text-end">
            <span class="text-sm text-gray-800 dark:text-white" x-text="`${uploadProgress}%`"></span>
        </div>
    </div>
</div>
