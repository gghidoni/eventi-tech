{{-- CFP --}}
<section class="mb-5 border border-gray-600 rounded-sm p-3 space-y-4">
    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-500">
        <input type="checkbox" class="rounded border-gray-600 bg-transparent" wire:model.live="has_cfp">
        <span>{{ __('dashboard.events.fields.has_cfp') }}</span>
    </label>
    @error('has_cfp')
        <span class="text-pink text-xs">{{ $message }}</span>
    @enderror

    @if ($has_cfp)
        <div>
            <label for="cfp_mode" class="block text-sm font-medium mb-1 text-gray-500">Tipo CFP</label>
            <select id="cfp_mode" class="input-et select-et" wire:model.live="cfp_mode">
                <option value="external">Esterna</option>
                <option value="internal">Interna</option>
            </select>
            @error('cfp_mode') <span class="text-pink text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="cfp_status" class="block text-sm font-medium mb-1 text-gray-500">{{ __('dashboard.events.fields.cfp_status') }}</label>
            <select id="cfp_status" name="cfp_status" class="input-et select-et" wire:model="cfp_status">
                @foreach ($cfpStatuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('cfp_status') <span class="text-pink text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="cfp_title" class="block text-sm font-medium mb-1 text-gray-500">Titolo CFP</label>
            <input id="cfp_title" type="text" class="input-et" wire:model="cfp_title" placeholder="CFP - titolo evento">
            @error('cfp_title') <span class="text-pink text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="cfp_description" class="block text-sm font-medium mb-1 text-gray-500">Descrizione CFP</label>
            <textarea id="cfp_description" rows="4" class="textarea-et" wire:model="cfp_description"></textarea>
            @error('cfp_description') <span class="text-pink text-xs">{{ $message }}</span> @enderror
        </div>

        @if ($cfp_mode === 'external')
            <div>
                <label for="cfp_external_url" class="block text-sm font-medium mb-1 text-gray-500">{{ __('dashboard.events.fields.cfp_external_url') }}</label>
                <input type="text" id="cfp_external_url" name="cfp_external_url" class="input-et" wire:model="cfp_external_url" />
                @error('cfp_external_url') <span class="text-pink text-xs">{{ $message }}</span> @enderror
            </div>
        @endif

        <div>
            <label for="cfp_opens_at" class="block text-sm font-medium mb-1 text-gray-500">{{ __('dashboard.events.fields.cfp_opens_at') }}</label>
            <input id="cfp_opens_at" type="text" placeholder="gg-mm-aaaa hh:mm" class="input-et" wire:model="cfp_opens_at">
            @error('cfp_opens_at') <span class="text-pink text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="cfp_closes_at" class="block text-sm font-medium mb-1 text-gray-500">{{ __('dashboard.events.fields.cfp_closes_at') }}</label>
            <input id="cfp_closes_at" type="text" placeholder="gg-mm-aaaa hh:mm" class="input-et" wire:model="cfp_closes_at">
            @error('cfp_closes_at') <span class="text-pink text-xs">{{ $message }}</span> @enderror
        </div>

        @if ($cfp_mode === 'internal')
            <div class="border-t border-gray-600 pt-4 space-y-4">
                <div>
                    <h2 class="font-anta text-lg">Campi candidatura</h2>
                    <p class="text-xs text-gray-500 mt-1">Titolo e abstract sono sempre richiesti. Puoi partire da un template della community e modificarlo qui.</p>
                </div>

                @if ($this->cfpTemplates()->isNotEmpty())
                    <div>
                        <label for="cfp_template_id" class="block text-sm font-medium mb-1 text-gray-500">Template CFP</label>
                        <select id="cfp_template_id" class="input-et select-et" wire:model="cfp_template_id">
                            <option value="">Nessun template</option>
                            @foreach ($this->cfpTemplates() as $template)
                                <option value="{{ $template->id }}">{{ $template->title }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="flex items-center space-x-2 text-cyan underline mt-2 text-sm" wire:click="applyCfpTemplate">
                            <span>Usa template</span>
                        </button>
                    </div>
                @else
                    <p class="text-xs text-gray-500">Nessun template CFP salvato per questa community. Puoi creare i campi da zero.</p>
                @endif

                <div class="space-y-3">
                    @foreach ($cfp_fields as $index => $field)
                        <div class="border border-gray-600 rounded-sm p-3 space-y-3" wire:key="event-cfp-field-{{ $index }}">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-anta text-sm text-gray-300">Campo {{ $index + 1 }}</span>
                                <button type="button" class="text-pink underline text-xs" wire:click="removeCfpField({{ $index }})">Rimuovi</button>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1 text-gray-500">Label</label>
                                <input type="text" class="input-et" wire:model="cfp_fields.{{ $index }}.label">
                                @error('cfp_fields.'.$index.'.label') <span class="text-pink text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1 text-gray-500">Key</label>
                                <input type="text" class="input-et" wire:model="cfp_fields.{{ $index }}.key" placeholder="generata dalla label se vuota">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1 text-gray-500">Tipo</label>
                                <select class="input-et select-et" wire:model.live="cfp_fields.{{ $index }}.type">
                                    @foreach ($cfpFieldTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <label class="flex items-center gap-2 text-sm text-gray-300">
                                <input type="checkbox" class="rounded border-gray-600 bg-transparent" wire:model="cfp_fields.{{ $index }}.required">
                                <span>Obbligatorio</span>
                            </label>

                            <div>
                                <label class="block text-sm font-medium mb-1 text-gray-500">Placeholder</label>
                                <input type="text" class="input-et" wire:model="cfp_fields.{{ $index }}.placeholder">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1 text-gray-500">Help text</label>
                                <input type="text" class="input-et" wire:model="cfp_fields.{{ $index }}.help_text">
                            </div>

                            @if (in_array($field['type'] ?? '', ['select', 'multiselect'], true))
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-gray-500">Opzioni</label>
                                    <textarea rows="4" class="textarea-et" wire:model="cfp_fields.{{ $index }}.options_text" placeholder="Una opzione per riga"></textarea>
                                    @error('cfp_fields.'.$index.'.options_text') <span class="text-pink text-xs">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <button type="button" class="flex items-center space-x-2 text-cyan underline text-sm" wire:click="addCfpField">
                    <span>Aggiungi campo</span>
                </button>

                <p class="text-xs text-gray-500">Se modifichi i campi di un template salvato, il template originale resta invariato e verra creato automaticamente un nuovo template per questa configurazione.</p>
            </div>
        @endif
    @endif
</section>
